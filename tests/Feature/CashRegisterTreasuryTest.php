<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CashRegister;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\CashRegisterService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CashRegisterTreasuryTest extends TestCase
{
    private User $cashier;

    private User $receiver;

    private Account $drawer;

    private Account $treasury;

    private CashRegisterService $registers;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['users', 'warehouses', 'roles', 'permissions'] as $table) {
            Schema::create($table, function (Blueprint $schema): void {
                $schema->increments('id');
                $schema->string('name')->nullable();
                $schema->string('username')->nullable();
                $schema->integer('statut')->default(1);
                $schema->boolean('is_all_warehouses')->default(true);
                $schema->boolean('record_view')->default(true);
                $schema->timestamps();
                $schema->softDeletes();
            });
        }
        foreach (['role_user' => ['role_id', 'user_id'], 'permission_role' => ['permission_id', 'role_id'], 'user_warehouse' => ['user_id', 'warehouse_id']] as $table => $columns) {
            Schema::create($table, function (Blueprint $schema) use ($columns): void {
                foreach ($columns as $column) {
                    $schema->integer($column);
                }
            });
        }
        Schema::create('accounts', function (Blueprint $schema): void {
            $schema->increments('id');
            $schema->string('account_name');
            $schema->decimal('balance', 15, 2)->default(0);
            $schema->timestamps();
            $schema->softDeletes();
        });
        Schema::create('payment_methods', function (Blueprint $schema): void {
            $schema->increments('id');
            $schema->string('name');
            $schema->timestamps();
            $schema->softDeletes();
        });
        foreach (['sales', 'sale_returns'] as $table) {
            Schema::create($table, function (Blueprint $schema): void {
                $schema->increments('id');
                $schema->integer('warehouse_id');
                $schema->timestamps();
                $schema->softDeletes();
            });
        }
        foreach (['payment_sales' => 'sale_id', 'payment_sale_returns' => 'sale_return_id'] as $table => $foreignKey) {
            Schema::create($table, function (Blueprint $schema) use ($foreignKey): void {
                $schema->increments('id');
                $schema->integer($foreignKey);
                $schema->integer('user_id');
                $schema->integer('account_id')->nullable();
                $schema->integer('payment_method_id');
                $schema->decimal('montant', 15, 2);
                $schema->timestamps();
                $schema->softDeletes();
            });
        }
        Schema::create('transfer_money', function (Blueprint $schema): void {
            $schema->increments('id');
            $schema->integer('from_account_id');
            $schema->integer('to_account_id');
            $schema->decimal('amount', 15, 2);
            $schema->date('date');
            $schema->timestamps();
            $schema->softDeletes();
        });
        require_once database_path('migrations/2025_10_20_000001_create_cash_registers_table.php');
        (new \CreateCashRegistersTable)->up();
        (require database_path('migrations/2026_11_10_000005_link_cash_registers_to_treasury.php'))->up();
        $this->cashier = User::forceCreate(['username' => 'Cashier'])->fresh();
        $this->receiver = User::forceCreate(['username' => 'Treasurer'])->fresh();
        DB::table('warehouses')->insert(['id' => 1, 'name' => 'Branch']);
        DB::table('roles')->insert(['id' => 1, 'name' => 'Finance']);
        DB::table('permissions')->insert(['id' => 1, 'name' => 'account']);
        DB::table('role_user')->insert(['role_id' => 1, 'user_id' => $this->receiver->id]);
        DB::table('permission_role')->insert(['role_id' => 1, 'permission_id' => 1]);
        $this->drawer = Account::create(['account_name' => 'Drawer', 'balance' => 100, 'account_type' => 'cashier', 'cashier_id' => $this->cashier->id]);
        $this->treasury = Account::create(['account_name' => 'Treasury', 'balance' => 0, 'account_type' => 'treasury']);
        PaymentMethod::create(['name' => 'Cash', 'is_cash' => true]);
        PaymentMethod::create(['name' => 'Card', 'is_cash' => false]);
        $this->registers = app(CashRegisterService::class);
    }

    public function test_cash_payment_without_selected_account_credits_bound_drawer_and_card_stays_out_of_cash(): void
    {
        $register = $this->open();
        $this->receive($register, 1, 250);
        $this->receive($register, 2, 400, $this->treasury->id);
        $this->assertSame(350.0, $this->drawer->fresh()->balance);
        $this->assertSame(400.0, $this->treasury->fresh()->balance);
        $this->assertSame(350.0, $this->registers->summary($register)['expected_cash']);
        $this->assertDatabaseHas('payment_sales', ['cash_register_id' => $register->id, 'account_id' => $this->drawer->id, 'is_cash' => 1]);
    }

    public function test_handover_updates_both_accounts_shift_and_receipt_once_even_when_retried(): void
    {
        $register = $this->open();
        $this->receive($register, 1, 250);
        $payload = $this->handoverData($register, 300);
        $first = $this->registers->handover($this->cashier, $payload);
        $this->registers->close($this->cashier, $register->id, ['counted_cash' => 50]);
        $retry = $this->registers->handover($this->cashier, $payload);
        $this->assertSame($first->id, $retry->id);
        $this->assertSame(50.0, $this->drawer->fresh()->balance);
        $this->assertSame(300.0, $this->treasury->fresh()->balance);
        $this->assertSame(300.0, $register->fresh()->cash_out);
        $this->assertSame(50.0, $this->registers->summary($register->fresh())['expected_cash']);
        $this->assertDatabaseHas('cash_register_handovers', ['receiver_id' => $this->receiver->id, 'created_by' => $this->cashier->id, 'transfer_money_id' => $first->transfer_money_id]);
        $this->assertDatabaseCount('transfer_money', 1);
    }

    public function test_excess_handover_rolls_back_all_balances_and_writes_no_receipt(): void
    {
        $register = $this->open();
        try {
            $this->registers->handover($this->cashier, $this->handoverData($register, 101));
            $this->fail('Excess handover accepted');
        } catch (ValidationException) {
            $this->assertSame(100.0, $this->drawer->fresh()->balance);
            $this->assertSame(0.0, $this->treasury->fresh()->balance);
            $this->assertSame(0.0, $register->fresh()->cash_out);
            $this->assertDatabaseCount('transfer_money', 0);
            $this->assertDatabaseCount('cash_register_handovers', 0);
        }
    }

    public function test_closing_uses_cash_receipts_less_refunds_and_handovers_without_card_or_unpaid_sales(): void
    {
        $register = $this->open();
        $this->receive($register, 1, 250);
        $this->receive($register, 2, 400, $this->treasury->id);
        DB::table('sales')->insert(['warehouse_id' => 1, 'created_at' => now()]);
        $returnId = DB::table('sale_returns')->insertGetId(['warehouse_id' => 1, 'created_at' => now()]);
        DB::transaction(function () use ($returnId): void {
            $context = $this->registers->paymentContext($this->cashier, 1, PaymentMethod::findOrFail(1), null);
            $this->registers->postRefundPayment(['sale_return_id' => $returnId, 'user_id' => $this->cashier->id,
                'payment_method_id' => 1, 'montant' => 20], $context);
        });
        $this->registers->handover($this->cashier, $this->handoverData($register, 300));
        $closed = $this->registers->close($this->cashier, $register->id, ['counted_cash' => 29, 'notes' => 'Counted']);
        $this->assertSame('closed', $closed->status);
        $this->assertSame(250.0, $closed->total_sales);
        $this->assertSame(20.0, $closed->cash_refunds);
        $this->assertSame(-1.0, $closed->difference);
        $this->assertSame(29.0, $closed->closing_balance);
    }

    public function test_changing_cash_method_classification_does_not_rewrite_recorded_shift_cash(): void
    {
        $register = $this->open();
        $this->receive($register, 1, 75);
        PaymentMethod::findOrFail(1)->update(['is_cash' => false]);
        $this->assertSame(175.0, $this->registers->summary($register)['expected_cash']);
    }

    public function test_reusing_receipt_uuid_with_different_amount_rejects_and_preserves_original_handover(): void
    {
        $register = $this->open();
        $payload = $this->handoverData($register, 30);
        $this->registers->handover($this->cashier, $payload);
        $payload['amount'] = 20;
        try {
            $this->registers->handover($this->cashier, $payload);
            $this->fail('Changed retry accepted');
        } catch (ValidationException) {
            $this->assertSame(70.0, $this->drawer->fresh()->balance);
            $this->assertSame(30.0, $this->treasury->fresh()->balance);
            $this->assertDatabaseCount('cash_register_handovers', 1);
        }
    }

    public function test_cashier_cannot_handover_another_cashiers_register(): void
    {
        $register = $this->open();
        $other = User::forceCreate(['username' => 'Other cashier']);
        $this->expectException(HttpException::class);
        $this->registers->handover($other, $this->handoverData($register, 20));
    }

    private function open(): CashRegister
    {
        return $this->registers->open($this->cashier, ['warehouse_id' => 1, 'account_id' => $this->drawer->id, 'opening_balance' => 100]);
    }

    private function receive(CashRegister $register, int $method, float $amount, ?int $accountId = null): void
    {
        DB::transaction(function () use ($method, $amount, $accountId): void {
            $context = $this->registers->paymentContext($this->cashier, 1, PaymentMethod::findOrFail($method), $accountId);
            $saleId = DB::table('sales')->insertGetId(['warehouse_id' => 1, 'created_at' => now()]);
            $this->registers->postSalePayment(['sale_id' => $saleId, 'user_id' => $this->cashier->id,
                'payment_method_id' => $method, 'montant' => $amount], $context);
        });
    }

    private function handoverData(CashRegister $register, float $amount): array
    {
        return ['register_id' => $register->id, 'to_account_id' => $this->treasury->id,
            'receiver_id' => $this->receiver->id, 'amount' => $amount, 'request_uuid' => (string) Str::uuid(), 'notes' => 'Delivered'];
    }
}
