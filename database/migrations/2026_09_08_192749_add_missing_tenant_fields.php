<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('saas_tenants', function (Blueprint $table) {
            // Add phone if it doesn't exist
            if (!Schema::hasColumn('saas_tenants', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            // Add language if it doesn't exist
            if (!Schema::hasColumn('saas_tenants', 'language')) {
                $table->string('language')->default('en')->after('phone');
            }
            // Add billing_cycle if it doesn't exist
            if (!Schema::hasColumn('saas_tenants', 'billing_cycle')) {
                $table->string('billing_cycle')->default('monthly')->after('language');
            }
        });
    }

    public function down()
    {
        Schema::table('saas_tenants', function (Blueprint $table) {
            $columns = ['phone', 'language', 'billing_cycle'];
            $existing = [];
            foreach($columns as $col) {
                if (Schema::hasColumn('saas_tenants', $col)) {
                    $existing[] = $col;
                }
            }
            if (!empty($existing)) {
                Schema::dropColumns('saas_tenants', $existing);
            }
        });
    }
};