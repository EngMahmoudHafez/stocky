<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CashRegisterHandover;
use App\Models\Sale;
use App\Models\User;
use App\Services\CashRegisterAccess;
use App\Services\CashRegisterService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashierTreasuryController extends BaseController
{
    public function __construct(private CashRegisterAccess $access, private CashRegisterService $registers) {}

    private function actor(Request $request): User
    {
        $user = $request->user('api');
        if (! $this->access->managesTreasury($user)) {
            $this->authorizeForUser($user, 'Sales_pos', Sale::class);
        }

        return $user;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $this->actor($request);
        $filters = $request->validate(['register_id' => ['nullable', 'integer']]);
        $registers = $this->access->visible($user)->with(['user:id,username', 'warehouse:id,name', 'account:id,account_name'])
            ->orderByRaw("CASE WHEN status = 'open' THEN 0 ELSE 1 END")->orderByDesc('id')->limit(30)->get();
        $selected = isset($filters['register_id']) ? $registers->firstWhere('id', $filters['register_id'])
            : ($registers->where('user_id', $user->id)->where('status', 'open')->first() ?? $registers->first());
        abort_if(isset($filters['register_id']) && $selected === null, 403);
        $warehouseIds = $this->access->warehouseIds($user);

        return response()->json([
            'registers' => $registers, 'current_register' => $selected,
            'summary' => $selected ? $this->registers->summary($selected) : null,
            'warehouses' => DB::table('warehouses')->whereIn('id', $warehouseIds)->get(['id', 'name']),
            'cashier_accounts' => Account::whereNull('deleted_at')->where('account_type', 'cashier')
                ->where('cashier_id', $user->id)->get(['id', 'account_name', 'balance']),
            'treasuries' => Account::whereNull('deleted_at')->where('account_type', 'treasury')->get(['id', 'account_name']),
            'receivers' => $selected ? $this->access->receivers($selected->warehouse_id)->where('id', '<>', $selected->user_id)->get(['id', 'username']) : [],
            'handovers' => $selected ? $this->history($selected->id) : [],
            'can_open' => $user->roles()->whereHas('permissions', fn ($query) => $query->where('name', 'Pos_view'))->exists(),
            'can_configure_accounts' => $user->roles()->whereHas('permissions', fn ($query) => $query->where('name', 'account'))->exists(),
        ]);
    }

    private function history(int $registerId): Collection
    {
        return CashRegisterHandover::query()->join('transfer_money', 'transfer_money.id', '=', 'cash_register_handovers.transfer_money_id')
            ->join('accounts as source', 'source.id', '=', 'transfer_money.from_account_id')
            ->join('accounts as destination', 'destination.id', '=', 'transfer_money.to_account_id')
            ->where('cash_register_id', $registerId)->orderByDesc('cash_register_handovers.id')->limit(30)
            ->get(['cash_register_handovers.*', 'source.account_name as from_account', 'destination.account_name as to_account']);
    }

    public function handover(Request $request): JsonResponse
    {
        $actor = $this->actor($request);
        $data = $request->validate([
            'register_id' => ['required', 'integer', 'exists:cash_registers,id'],
            'to_account_id' => ['required', 'integer', 'exists:accounts,id'],
            'receiver_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:999999999999.99'],
            'request_uuid' => ['required', 'uuid'], 'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        return response()->json(['success' => true, 'receipt' => $this->registers->handover($actor, $data)]);
    }

    public function close(Request $request): JsonResponse
    {
        $actor = $this->actor($request);
        $data = $request->validate(['register_id' => ['required', 'integer', 'exists:cash_registers,id'],
            'counted_cash' => ['required', 'numeric', 'min:0', 'decimal:0,2', 'max:999999999999.99'],
            'notes' => ['nullable', 'string', 'max:1000']]);

        return response()->json(['success' => true, 'register' => $this->registers->close($actor, $data['register_id'], $data)]);
    }
}
