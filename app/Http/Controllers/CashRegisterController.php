<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\Models\UserWarehouse;
use App\Models\Warehouse;
use App\Services\CashRegisterAccess;
use App\Services\CashRegisterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashRegisterController extends BaseController
{
    public function openRegister(Request $request, CashRegisterService $registers)
    {
        $this->authorizeForUser($request->user('api'), 'Sales_pos', Sale::class);

        $data = $request->validate([
            'warehouse_id' => 'required|integer|exists:warehouses,id',
            'account_id' => 'required|integer|exists:accounts,id',
            'opening_balance' => 'required|numeric|min:0|decimal:0,2',
            'notes' => 'nullable|string',
        ]);

        $register = $registers->open($request->user('api'), $data);

        return response()->json(['success' => true, 'register' => $register]);
    }

    public function getCurrentRegister(Request $request, $userId)
    {
        $this->authorizeForUser($request->user('api'), 'Sales_pos', Sale::class);

        $warehouseId = $request->query('warehouse_id');
        $query = CashRegister::with('user', 'warehouse')
            ->where('user_id', $userId)
            ->where('status', 'open');
        // If a specific warehouse is selected, filter; otherwise return the latest open register across warehouses
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        $register = $query->orderByDesc('id')->first();
        if ($register) {
            app(CashRegisterAccess::class)->authorize($request->user('api'), $register);
        }

        return response()->json(['success' => true, 'register' => $register]);
    }

    public function cashInOut(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'Sales_pos', Sale::class);

        $data = $request->validate([
            'register_id' => 'required|integer|exists:cash_registers,id',
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $register = CashRegister::findOrFail($data['register_id']);
        app(CashRegisterAccess::class)->authorize($request->user('api'), $register);
        abort_if($register->account_id !== null, 422, __('cashier.active_account'));
        if ($register->status !== 'open') {
            return response()->json(['success' => false, 'message' => 'Register is closed'], 409);
        }

        if ($data['type'] === 'in') {
            $register->cash_in = ($register->cash_in ?? 0) + $data['amount'];
        } else {
            $register->cash_out = ($register->cash_out ?? 0) + $data['amount'];
        }
        if (! empty($data['notes'])) {
            $register->notes = trim($register->notes."\n".'['.Carbon::now()->toDateTimeString().'] Cash '.$data['type'].': '.number_format($data['amount'], 2));
        }
        $register->save();

        return response()->json(['success' => true, 'register' => $register]);
    }

    public function closeRegister(Request $request, CashRegisterService $registers)
    {
        $this->authorizeForUser($request->user('api'), 'Sales_pos', Sale::class);

        $data = $request->validate([
            'register_id' => 'required|integer|exists:cash_registers,id',
            'counted_cash' => 'required|numeric|min:0|decimal:0,2',
            'closing_balance' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $register = $registers->close($request->user('api'), $data['register_id'], $data);

        return response()->json([
            'success' => true,
            'register' => $register,
            'summary' => [
                'expected_cash' => round($register->closing_balance - $register->difference, 2),
                'counted_cash' => $register->closing_balance,
                'difference' => $register->difference,
            ],
        ]);
    }

    public function report(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'cash_register_report', Sale::class);

        // Normalize date filter to Y-m-d (avoid timezone/invalid input issues)
        $today = Carbon::today();
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->toDateString()
            : $today->copy()->subDays(6)->toDateString();
        $to = $request->filled('to')
            ? Carbon::parse($request->to)->toDateString()
            : $today->toDateString();
        if ($from > $to) {
            $from = $to;
        }

        // Pagination + Sorting (align with Report_Sales)
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField ?: 'opened_at';
        $dir = $request->SortType ?: 'desc';

        $allowedSorts = ['opened_at', 'closed_at', 'opening_balance', 'closing_balance', 'cash_in', 'cash_out', 'total_sales', 'difference', 'status', 'warehouse_id', 'user_id'];
        if (! in_array($order, $allowedSorts, true)) {
            $order = 'opened_at';
        }

        $user = Auth::user();
        // New way: Check user's record_view field (user-level boolean)
        // Backward compatibility: If record_view is null, fall back to role permission check
        $view_records = $user->hasRecordView();
        $is_all_warehouses = $user->is_all_warehouses;
        // If the user is restricted, fetch their assigned warehouse IDs once and reuse below.
        if (! $is_all_warehouses) {
            $warehouse_ids = UserWarehouse::where('user_id', $user->id)
                ->pluck('warehouse_id')
                ->toArray();
        }

        $query = CashRegister::with(['user:id,firstname,lastname,username', 'warehouse:id,name'])
            ->where(function ($q) use ($view_records) {
                if (! $view_records) {
                    return $q->where('user_id', Auth::user()->id);
                }
            });
        if (! $is_all_warehouses) {
            $query->whereIn('warehouse_id', $warehouse_ids);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $query->whereDate('opened_at', '>=', $from);
        $query->whereDate('opened_at', '<=', $to);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('status', 'like', "%$search%")
                    ->orWhere('opening_balance', 'like', "%$search%")
                    ->orWhere('closing_balance', 'like', "%$search%")
                    ->orWhere('cash_in', 'like', "%$search%")
                    ->orWhere('cash_out', 'like', "%$search%")
                    ->orWhere('total_sales', 'like', "%$search%")
                    ->orWhere('difference', 'like', "%$search%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('firstname', 'like', "%$search%")
                            ->orWhere('lastname', 'like', "%$search%")
                            ->orWhere('username', 'like', "%$search%");
                    })
                    ->orWhereHas('warehouse', function ($wq) use ($search) {
                        $wq->where('name', 'like', "%$search%");
                    });
            });
        }

        $totalRows = $query->count();
        if ($perPage == '-1') {
            $perPage = $totalRows;
        }

        $items = $query->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        $data = [];
        foreach ($items as $r) {
            $item['id'] = $r->id;
            $item['user_id'] = $r->user_id;
            $item['warehouse_id'] = $r->warehouse_id;
            $item['cashier_firstname'] = optional($r->user)->firstname;
            $item['cashier_lastname'] = optional($r->user)->lastname;
            $item['cashier_username'] = optional($r->user)->username;
            $item['cashier_name'] = optional($r->user)->username;
            $item['warehouse_name'] = optional($r->warehouse)->name;
            $item['opened_at'] = optional($r->opened_at)->format('Y-m-d H:i:s');
            $item['closed_at'] = $r->closed_at ? optional($r->closed_at)->format('Y-m-d H:i:s') : null;
            $item['status'] = $r->status;
            $item['opening_balance'] = number_format((float) $r->opening_balance, 2, '.', '');
            $item['cash_in'] = number_format((float) $r->cash_in, 2, '.', '');
            $item['cash_out'] = number_format((float) $r->cash_out, 2, '.', '');
            $item['cash_refunds'] = number_format((float) $r->cash_refunds, 2, '.', '');
            $item['total_sales'] = number_format((float) $r->total_sales, 2, '.', '');
            $item['closing_balance'] = is_null($r->closing_balance) ? null : number_format((float) $r->closing_balance, 2, '.', '');
            $item['difference'] = is_null($r->difference) ? null : number_format((float) $r->difference, 2, '.', '');
            $data[] = $item;
        }

        // Users & Warehouses for filters (mirror sales report)
        $users = User::where('deleted_at', '=', null)->get(['id', 'username', 'firstname', 'lastname']);

        $user_auth = auth()->user();
        if ($user_auth->is_all_warehouses) {
            $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);
        } else {
            $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
            $warehouses = Warehouse::where('deleted_at', '=', null)->whereIn('id', $warehouses_id)->get(['id', 'name']);
        }

        return response()->json([
            'totalRows' => $totalRows,
            'registers' => $data,
            'users' => $users,
            'warehouses' => $warehouses,
        ]);
    }
}
