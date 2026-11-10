<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\Transfer;
use App\Models\User;
use App\Models\UserWarehouse;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransferPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\Transfer  $transfer
     * @return mixed
     */
    public function view(User $user)
    {
        $permission = Permission::where('name', 'transfer_view')->first();

        return $user->hasRole($permission->roles);
    }

    /**
     * Determine whether the user can create models.
     *
     * @return mixed
     */
    public function create(User $user)
    {
        $permission = Permission::where('name', 'transfer_add')->first();

        return $user->hasRole($permission->roles);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Transfer  $transfer
     * @return mixed
     */
    public function update(User $user)
    {
        $permission = Permission::where('name', 'transfer_edit')->first();

        return $user->hasRole($permission->roles);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\Transfer  $transfer
     * @return mixed
     */
    public function delete(User $user)
    {
        $permission = Permission::where('name', 'transfer_delete')->first();

        return $user->hasRole($permission->roles);
    }

    public function Stock_Transfer_Report(User $user)
    {
        $permission = Permission::where('name', 'Stock_Transfer_Report')->first();

        return $user->hasRole($permission->roles);
    }

    public function check_record(User $user, $transfer)
    {
        return $user->id === $transfer->user_id;
    }

    public function receive(User $user, Transfer $transfer): bool
    {
        return $this->hasReceivePermission($user) && $this->warehouseAccess($user, $transfer->to_warehouse_id);
    }

    public function viewRecord(User $user, Transfer $transfer): bool
    {
        $destinationAccess = $this->warehouseAccess($user, $transfer->to_warehouse_id);

        return ($destinationAccess || $this->warehouseAccess($user, $transfer->from_warehouse_id))
            && ($user->hasRecordView() || $this->check_record($user, $transfer)
                || ($destinationAccess && $transfer->dispatched_at && $this->hasReceivePermission($user)));
    }

    public function hasReceivePermission(User $user): bool
    {
        $permission = Permission::where('name', 'transfer_receive')->whereNull('deleted_at')->first();

        return $permission && $user->hasRole($permission->roles);
    }

    public function warehouseAccess(User $user, int $warehouseId): bool
    {
        return (bool) $user->is_all_warehouses || UserWarehouse::where('user_id', $user->id)
            ->where('warehouse_id', $warehouseId)->exists();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\Transfer  $transfer
     * @return mixed
     */
    public function restore(User $user)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\Transfer  $transfer
     * @return mixed
     */
    public function forceDelete(User $user)
    {
        //
    }
}
