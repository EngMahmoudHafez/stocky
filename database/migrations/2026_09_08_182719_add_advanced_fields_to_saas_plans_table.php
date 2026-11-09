<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('saas_plans', function (Blueprint $table) {
            // Only add columns if they do not exist
            if (!Schema::hasColumn('saas_plans', 'is_popular')) {
                $table->boolean('is_popular')->default(0)->after('is_active');
            }
            if (!Schema::hasColumn('saas_plans', 'is_free')) {
                $table->boolean('is_free')->default(0)->after('is_popular');
            }
            if (!Schema::hasColumn('saas_plans', 'products_limit')) {
                $table->integer('products_limit')->nullable()->after('yearly_price');
            }
            if (!Schema::hasColumn('saas_plans', 'users_limit')) {
                $table->integer('users_limit')->nullable()->after('products_limit');
            }
            if (!Schema::hasColumn('saas_plans', 'warehouses_limit')) {
                $table->integer('warehouses_limit')->nullable()->after('users_limit');
            }
            if (!Schema::hasColumn('saas_plans', 'customers_limit')) {
                $table->integer('customers_limit')->nullable()->after('warehouses_limit');
            }
            if (!Schema::hasColumn('saas_plans', 'suppliers_limit')) {
                $table->integer('suppliers_limit')->nullable()->after('customers_limit');
            }
            if (!Schema::hasColumn('saas_plans', 'whatsapp_limit')) {
                $table->integer('whatsapp_limit')->nullable()->after('suppliers_limit');
            }
            
            // Checks if 'features' exists, if not, adds it!
            if (!Schema::hasColumn('saas_plans', 'features')) {
                $table->json('features')->nullable()->after('whatsapp_limit');
            }
        });
    }

    public function down()
    {
        Schema::table('saas_plans', function (Blueprint $table) {
            $columns = ['is_popular', 'is_free', 'products_limit', 'users_limit', 'warehouses_limit', 'customers_limit', 'suppliers_limit', 'whatsapp_limit', 'features'];
            
            // Drop only columns that actually exist
            $existing = [];
            foreach($columns as $col) {
                if (Schema::hasColumn('saas_plans', $col)) {
                    $existing[] = $col;
                }
            }
            
            if (!empty($existing)) {
                Schema::dropColumns('saas_plans', $existing);
            }
        });
    }
};