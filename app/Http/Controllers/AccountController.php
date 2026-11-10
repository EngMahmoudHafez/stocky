<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CashRegister;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AccountController extends BaseController
{
    // -------------- Get All Account ---------------\\

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Account::class);
        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;

        // Check If User Has Permission View  All Records
        $Accounts = Account::where('deleted_at', '=', null)

            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('account_num', 'LIKE', "%{$request->search}%")
                        ->orWhere('account_name', 'LIKE', "%{$request->search}%");
                });
            });

        $totalRows = $Accounts->count();
        if ($perPage == '-1') {
            $perPage = $totalRows;
        }
        $account_data = $Accounts->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        $data = [];
        foreach ($account_data as $account) {

            $item['id'] = $account->id;
            $item['account_num'] = $account->account_num;
            $item['account_name'] = $account->account_name;
            $item['balance'] = $account->balance;
            $item['note'] = $account->note;
            $item['account_type'] = $account->account_type;
            $item['cashier_id'] = $account->cashier_id;

            $data[] = $item;
        }

        return response()->json([
            'accounts' => $data,
            'totalRows' => $totalRows,
            'cashiers' => DB::table('users')->whereNull('deleted_at')->where('statut', 1)->get(['id', 'username']),
        ]);

    }

    // -------------- Store New Account ---------------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', Account::class);

        request()->validate([
            'account_num' => 'required',
            'account_name' => 'required',
            'initial_balance' => 'required|numeric|min:0|decimal:0,2|max:999999999999.99',
        ]);

        $configuration = $this->cashConfiguration($request);
        Account::create(array_merge([
            'account_num' => $request['account_num'],
            'account_name' => $request['account_name'],
            'initial_balance' => $request['initial_balance'],
            'balance' => $request['initial_balance'],
            'note' => $request['note'],
        ], $configuration));

        return response()->json(['success' => true], 200);
    }

    // ------------ function show -----------\\

    public function show($id)
    {
        //

    }

    // -------------- Update Account ---------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', Account::class);
        DB::transaction(function () use ($request, $id): void {
            $Account = Account::lockForUpdate()->findOrFail($id);

            request()->validate([
                'account_num' => 'required',
                'account_name' => 'required',
            ]);

            $configuration = $this->cashConfiguration($request, $Account);
            abort_if(CashRegister::where('account_id', $Account->id)->where('status', 'open')->exists()
                && ($configuration['account_type'] !== $Account->account_type || $configuration['cashier_id'] !== $Account->cashier_id),
                422, __('cashier.active_account'));
            $Account->update(array_merge([
                'account_num' => $request['account_num'],
                'account_name' => $request['account_name'],
                'note' => $request['note'],
            ], $configuration));
        }, 3);

        return response()->json(['success' => true], 200);

    }

    // -------------- Delete Account ---------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Account::class);
        DB::transaction(function () use ($id): void {
            $Account = Account::lockForUpdate()->findOrFail($id);

            abort_if(CashRegister::where('account_id', $Account->id)->where('status', 'open')->exists(), 422, __('cashier.active_account'));

            $Account->update([
                'deleted_at' => Carbon::now(),
            ]);
        }, 3);

        return response()->json(['success' => true], 200);
    }

    // -------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Account::class);
        $selectedIds = $request->selectedIds;
        DB::transaction(function () use ($selectedIds): void {
            Account::whereIn('id', $selectedIds)->orderBy('id')->lockForUpdate()->get();
            abort_if(CashRegister::whereIn('account_id', $selectedIds)->where('status', 'open')->exists(), 422, __('cashier.active_account'));

            foreach ($selectedIds as $account_id) {
                $Account = Account::findOrFail($account_id);

                $Account->update([
                    'deleted_at' => Carbon::now(),
                ]);
            }
        }, 3);

        return response()->json(['success' => true], 200);
    }

    private function cashConfiguration(Request $request, ?Account $account = null): array
    {
        $input = $request->only(['account_type', 'cashier_id']) + [
            'account_type' => $account?->account_type ?? 'general', 'cashier_id' => $account?->cashier_id];
        $data = validator($input, [
            'account_type' => ['required', Rule::in(['general', 'cashier', 'treasury'])],
            'cashier_id' => ['nullable', 'required_if:account_type,cashier', 'integer',
                Rule::exists('users', 'id')->whereNull('deleted_at')->where('statut', 1)],
        ])->validate();

        return ['account_type' => $data['account_type'],
            'cashier_id' => $data['account_type'] === 'cashier' ? (int) $data['cashier_id'] : null];
    }
}
