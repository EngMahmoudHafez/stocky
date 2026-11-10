<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table): void {
            $table->string('account_type', 20)->default('general');
            $table->unsignedInteger('cashier_id')->nullable()->index();
        });
        Schema::table('payment_methods', function (Blueprint $table): void {
            $table->boolean('is_cash')->default(false);
        });
        DB::table('payment_methods')->whereIn(DB::raw('LOWER(TRIM(name))'), ['cash', 'cash payment', 'نقدي', 'نقدى', 'نقداً', 'نقدا', 'كاش'])
            ->update(['is_cash' => true]);
        Schema::table('cash_registers', function (Blueprint $table): void {
            $table->integer('account_id')->nullable()->index();
            $table->decimal('cash_refunds', 15, 2)->default(0);
            $table->unsignedTinyInteger('cash_summary_version')->nullable();
        });
        foreach (['payment_sales', 'payment_sale_returns'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedBigInteger('cash_register_id')->nullable()->index();
                $table->boolean('is_cash')->nullable();
            });
        }
        Schema::create('cash_register_handovers', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->uuid('request_uuid')->unique();
            $table->unsignedBigInteger('cash_register_id')->index();
            $table->integer('transfer_money_id')->unique();
            $table->integer('created_by');
            $table->integer('receiver_id');
            $table->string('receiver_name');
            $table->decimal('amount', 15, 2);
            $table->decimal('expected_cash_after', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('cash_register_id')->references('id')->on('cash_registers')->restrictOnDelete();
            $table->foreign('transfer_money_id')->references('id')->on('transfer_money')->restrictOnDelete();
            $table->foreign('receiver_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
        });
        if (Schema::hasTable('translations')) {
            foreach (['ar' => 'يومية الكاشير والتوريد', 'en' => 'Cashier shifts and treasury'] as $locale => $label) {
                DB::table('translations')->updateOrInsert(['locale' => $locale, 'key' => 'Cashier_Treasury'], ['value' => $label]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_register_handovers');
        foreach (['payment_sales', 'payment_sale_returns'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropIndex(['cash_register_id']);
                $table->dropColumn(['cash_register_id', 'is_cash']);
            });
        }
        Schema::table('cash_registers', function (Blueprint $table): void {
            $table->dropIndex(['account_id']);
            $table->dropColumn(['account_id', 'cash_refunds', 'cash_summary_version']);
        });
        Schema::table('payment_methods', fn (Blueprint $table) => $table->dropColumn('is_cash'));
        Schema::table('accounts', function (Blueprint $table): void {
            $table->dropIndex(['cashier_id']);
            $table->dropColumn(['account_type', 'cashier_id']);
        });
    }
};
