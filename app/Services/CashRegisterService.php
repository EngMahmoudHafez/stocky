<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Models\CashRegister;
use App\Models\CashRegisterHandover;
use App\Models\PaymentMethod;
use App\Models\PaymentSale;
use App\Models\PaymentSaleReturns;
use App\Models\TransferMoney;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashRegisterService
{
    public function __construct(private CashRegisterAccess $access) {}

    public function open(User $user, array $opening): CashRegister
    {
        abort_unless($this->access->warehouseIds($user)->contains((int) $opening['warehouse_id']), 403);

        return DB::transaction(function () use ($user, $opening): CashRegister {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $account = Account::whereNull('deleted_at')->lockForUpdate()->findOrFail($opening['account_id']);
            $this->require($account->account_type === 'cashier' && $account->cashier_id === $user->id, 'drawer');
            $this->require(! CashRegister::where('status', 'open')->where(fn ($query) => $query->where('user_id', $user->id)->orWhere('account_id', $account->id))->exists(), 'already_open');
            $this->require(round($account->balance, 2) === round((float) $opening['opening_balance'], 2), 'opening_mismatch');

            return CashRegister::create(['user_id' => $user->id, 'warehouse_id' => $opening['warehouse_id'],
                'account_id' => $account->id, 'opening_balance' => $account->balance,
                'opened_at' => now(), 'status' => 'open', 'notes' => $opening['notes'] ?? null]);
        }, 3);
    }

    /** @return array{account_id: ?int, cash_register_id: ?int, is_cash: bool} */
    public function paymentContext(User $user, int $warehouseId, PaymentMethod $method, ?int $accountId): array
    {
        $register = null;
        if ($method->is_cash) {
            $register = CashRegister::where('user_id', $user->id)->where('warehouse_id', $warehouseId)
                ->where('status', 'open')->lockForUpdate()->first();
            $this->require($register !== null && $register->account_id !== null, 'open_first');
            $this->require($accountId === null || $accountId === $register->account_id, 'account_mismatch');
            $accountId = $register->account_id;
        }
        $this->require($accountId !== null || strtolower(trim($method->name)) === 'wallet', 'payment_account');
        if ($accountId !== null) {
            $account = Account::whereNull('deleted_at')->lockForUpdate()->findOrFail($accountId);
            $this->require($method->is_cash || $account->account_type !== 'cashier', 'noncash_drawer');
            $this->require(! $method->is_cash || ($account->account_type === 'cashier' && $account->cashier_id === $user->id), 'drawer');
        }

        return ['account_id' => $accountId, 'cash_register_id' => $register?->id, 'is_cash' => (bool) $method->is_cash];
    }

    /** Called inside the sale transaction, after paymentContext has locked the drawer. */
    public function postSalePayment(array $attributes, array $context): PaymentSale
    {
        $payment = PaymentSale::create(array_merge($attributes, $context));
        if ($payment->account_id !== null) {
            Account::whereKey($payment->account_id)->increment('balance', $payment->montant);
        }

        return $payment;
    }

    public function postRefundPayment(array $attributes, array $context): PaymentSaleReturns
    {
        $amount = round((float) $attributes['montant'], 2);
        if ($context['cash_register_id'] !== null) {
            $register = CashRegister::findOrFail($context['cash_register_id']);
            $summary = $this->summary($register);
            $this->require($amount <= $summary['expected_cash'] && $amount <= $summary['account_balance'], 'insufficient');
        }
        $payment = PaymentSaleReturns::create(array_merge($attributes, $context));
        if ($payment->account_id !== null) {
            Account::whereKey($payment->account_id)->decrement('balance', $amount);
        }

        return $payment;
    }

    public function assertUnlinkedPayment(PaymentSale|PaymentSaleReturns $payment): void
    {
        $this->require($payment->cash_register_id === null, 'linked_payment');
    }

    public function assertSaleMutable(int $saleId): void
    {
        $this->require(! PaymentSale::where('sale_id', $saleId)->whereNotNull('cash_register_id')->exists(), 'linked_payment');
    }

    public function assertReturnMutable(int $returnId): void
    {
        $this->require(! PaymentSaleReturns::where('sale_return_id', $returnId)->whereNotNull('cash_register_id')->exists(), 'linked_payment');
    }

    public function summary(CashRegister $register): array
    {
        $closed = $register->status === 'closed' && $register->cash_summary_version === 1;
        $received = $closed ? $register->total_sales : $this->cashPayments($register, 'payment_sales', 'sales', 'sale_id');
        $refunded = $closed ? $register->cash_refunds : $this->cashPayments($register, 'payment_sale_returns', 'sale_returns', 'sale_return_id');

        return ['cash_received' => $received, 'cash_refunded' => $refunded,
            'cash_out' => (float) $register->cash_out, 'cash_in' => (float) $register->cash_in,
            'expected_cash' => round($register->opening_balance + $received - $refunded + $register->cash_in - $register->cash_out, 2),
            'account_balance' => $register->account_id === null ? null : (float) Account::whereKey($register->account_id)->value('balance')];
    }

    private function cashPayments(CashRegister $register, string $table, string $document, string $foreignKey): float
    {
        return round((float) DB::table("$table as payments")
            ->join("$document as documents", 'documents.id', '=', "payments.$foreignKey")
            ->leftJoin('payment_methods as methods', 'methods.id', '=', 'payments.payment_method_id')
            ->whereNull('payments.deleted_at')->whereNull('documents.deleted_at')
            ->where(fn ($query) => $query->where('payments.is_cash', true)
                ->orWhere(fn ($legacy) => $legacy->whereNull('payments.is_cash')->where('methods.is_cash', true)))
            ->where(fn ($query) => $query->where('payments.cash_register_id', $register->id)
                ->orWhere(fn ($legacy) => $legacy->whereNull('payments.cash_register_id')
                    ->where('payments.user_id', $register->user_id)->where('documents.warehouse_id', $register->warehouse_id)
                    ->when($register->account_id !== null, fn ($bound) => $bound->where('payments.account_id', $register->account_id))
                    ->whereBetween('payments.created_at', [$register->opened_at, ($register->closed_at ?? now())->endOfSecond()->format('Y-m-d H:i:s.u')])))
            ->sum('payments.montant'), 2);
    }

    public function handover(User $actor, array $handover): CashRegisterHandover
    {
        return DB::transaction(function () use ($actor, $handover): CashRegisterHandover {
            $register = CashRegister::lockForUpdate()->findOrFail($handover['register_id']);
            $this->access->authorize($actor, $register);
            $existing = CashRegisterHandover::where('request_uuid', $handover['request_uuid'])->first();
            if ($existing !== null) {
                $this->require($this->sameHandover($existing, $actor, $handover), 'duplicate_mismatch');

                return $existing;
            }
            $this->require($register->status === 'open' && $register->account_id !== null, 'closed');
            $receiver = $this->access->receivers($register->warehouse_id)->find($handover['receiver_id']);
            $this->require($receiver !== null && $receiver->id !== $register->user_id, 'receiver');
            $this->require((int) $handover['to_account_id'] !== $register->account_id, 'different_accounts');

            return $this->postHandover($actor, $register, $receiver, $handover);
        }, 3);
    }

    private function postHandover(User $actor, CashRegister $register, User $receiver, array $handover): CashRegisterHandover
    {
        $accounts = Account::whereNull('deleted_at')->whereIn('id', [$register->account_id, $handover['to_account_id']])
            ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
        $source = $accounts->get($register->account_id);
        $destination = $accounts->get((int) $handover['to_account_id']);
        $this->require($source !== null && $destination !== null && $destination->account_type === 'treasury', 'treasury');
        $this->require($source->account_type === 'cashier' && $source->cashier_id === $register->user_id, 'drawer');
        $amount = round((float) $handover['amount'], 2);
        $expected = $this->summary($register)['expected_cash'];
        $this->require($amount > 0 && $amount <= $source->balance && $amount <= $expected, 'insufficient');
        $source->decrement('balance', $amount);
        $destination->increment('balance', $amount);
        $register->increment('cash_out', $amount);
        $transfer = TransferMoney::create(['date' => now()->toDateString(), 'from_account_id' => $source->id,
            'to_account_id' => $destination->id, 'amount' => $amount]);

        return CashRegisterHandover::create(['request_uuid' => $handover['request_uuid'], 'cash_register_id' => $register->id,
            'transfer_money_id' => $transfer->id, 'created_by' => $actor->id, 'receiver_id' => $receiver->id,
            'receiver_name' => $receiver->username, 'amount' => $amount,
            'expected_cash_after' => round($expected - $amount, 2), 'notes' => $handover['notes'] ?? null]);
    }

    private function sameHandover(CashRegisterHandover $receipt, User $actor, array $handover): bool
    {
        $transfer = TransferMoney::findOrFail($receipt->transfer_money_id);

        return (int) $receipt->created_by === $actor->id && (int) $receipt->cash_register_id === (int) $handover['register_id']
            && (int) $receipt->receiver_id === (int) $handover['receiver_id'] && $transfer->to_account_id === (int) $handover['to_account_id']
            && $receipt->amount === round((float) $handover['amount'], 2) && ($receipt->notes ?? '') === ($handover['notes'] ?? '');
    }

    public function close(User $actor, int $registerId, array $closing): CashRegister
    {
        return DB::transaction(function () use ($actor, $registerId, $closing): CashRegister {
            $register = CashRegister::lockForUpdate()->findOrFail($registerId);
            $this->access->authorize($actor, $register);
            $this->require($register->status === 'open', 'closed');
            $summary = $this->summary($register);
            $register->fill(['total_sales' => $summary['cash_received'], 'cash_refunds' => $summary['cash_refunded'],
                'closing_balance' => $closing['counted_cash'], 'difference' => round($closing['counted_cash'] - $summary['expected_cash'], 2),
                'status' => 'closed', 'closed_at' => now(), 'cash_summary_version' => 1,
                'notes' => trim(($register->notes ?? '')."\n".($closing['notes'] ?? ''))])->save();

            return $register;
        }, 3);
    }

    private function require(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['cash_register' => __('cashier.'.$message)]);
        }
    }

    public function assertNoOpenDrawerMovement(array $accountIds): void
    {
        $this->require(! CashRegister::where('status', 'open')->whereIn('account_id', $accountIds)->exists(), 'active_account');
    }

    public function lockGeneralAccounts(array $accountIds): void
    {
        Account::whereIn('id', array_filter($accountIds))->orderBy('id')->lockForUpdate()->get();
        $this->assertNoOpenDrawerMovement($accountIds);
    }
}
