<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CashRegister;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CashRegisterAccess
{
    public function managesTreasury(User $user): bool
    {
        return $user->roles()->whereHas('permissions', fn (Builder $query) => $query->whereIn('name', ['account', 'transfer_money', 'accounting_dashboard'])
        )->exists();
    }

    public function warehouseIds(User $user): Collection
    {
        return DB::table('warehouses')->whereNull('deleted_at')
            ->when(! $user->is_all_warehouses, fn ($query) => $query->whereIn('id',
                DB::table('user_warehouse')->where('user_id', $user->id)->select('warehouse_id')))
            ->pluck('id');
    }

    public function authorize(User $user, CashRegister $register): void
    {
        abort_unless($user->id === $register->user_id || ($this->managesTreasury($user) && $user->hasRecordView()), 403);
        abort_unless($this->warehouseIds($user)->contains($register->warehouse_id), 403);
    }

    public function visible(User $user): Builder
    {
        return CashRegister::query()->whereIn('warehouse_id', $this->warehouseIds($user))
            ->when(! $this->managesTreasury($user) || ! $user->hasRecordView(),
                fn (Builder $query) => $query->where('user_id', $user->id));
    }

    public function receivers(int $warehouseId): Builder
    {
        return User::query()->whereNull('deleted_at')->where('statut', 1)
            ->whereHas('roles.permissions', fn (Builder $query) => $query->whereIn('name', ['account', 'transfer_money', 'accounting_dashboard']))
            ->where(fn (Builder $query) => $query->where('is_all_warehouses', 1)
                ->orWhereIn('id', DB::table('user_warehouse')->where('warehouse_id', $warehouseId)->select('user_id')));
    }
}
