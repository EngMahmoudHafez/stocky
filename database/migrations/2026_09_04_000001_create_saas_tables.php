<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('saas_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('monthly_price', 12, 2)->default(0);
            $table->decimal('yearly_price', 12, 2)->default(0);
            $table->unsignedInteger('max_users')->nullable();
            $table->unsignedInteger('max_clients')->nullable();
            $table->unsignedInteger('max_products')->nullable();
            $table->unsignedInteger('trial_days')->default(0);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('saas_tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->nullable()->unique();
            $table->string('database_name')->unique();
            $table->string('database_username')->nullable();
            $table->string('database_password')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('owner_name')->nullable();
            $table->foreignId('plan_id')->nullable()->constrained('saas_plans')->nullOnDelete();
            $table->enum('status', ['active','trial','pending','suspended','cancelled'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('saas_accountants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('saas_accountant_tenant', function (Blueprint $table) {
            $table->foreignId('accountant_id')->constrained('saas_accountants')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('saas_tenants')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['accountant_id', 'tenant_id']);
        });

        Schema::create('saas_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('saas_tenants')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('saas_plans')->restrictOnDelete();
            $table->enum('status', ['active','trial','pending','suspended','cancelled'])->default('trial');
            $table->enum('billing_cycle', ['monthly','yearly'])->default('monthly');
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_subscriptions');
        Schema::dropIfExists('saas_accountant_tenant');
        Schema::dropIfExists('saas_accountants');
        Schema::dropIfExists('saas_tenants');
        Schema::dropIfExists('saas_plans');
    }
};
