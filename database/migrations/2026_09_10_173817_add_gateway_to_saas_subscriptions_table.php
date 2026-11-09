<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('central')->table('saas_subscriptions', function (Blueprint $table) {
            if (!Schema::connection('central')->hasColumn('saas_subscriptions', 'gateway')) {
                $table->string('gateway')->nullable()->after('billing_cycle');
            }
            if (!Schema::connection('central')->hasColumn('saas_subscriptions', 'ends_at')) {
                $table->timestamp('ends_at')->nullable()->after('trial_ends_at');
            }
            if (!Schema::connection('central')->hasColumn('saas_subscriptions', 'starts_at')) {
                $table->timestamp('starts_at')->nullable()->after('ends_at');
            }
            if (!Schema::connection('central')->hasColumn('saas_subscriptions', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('starts_at');
            }
        });
    }

    public function down()
    {
        Schema::connection('central')->table('saas_subscriptions', function (Blueprint $table) {
            $columns = ['gateway', 'ends_at', 'starts_at', 'cancelled_at'];
            $existing = [];
            foreach ($columns as $col) {
                if (Schema::connection('central')->hasColumn('saas_subscriptions', $col)) {
                    $existing[] = $col;
                }
            }
            if (!empty($existing)) {
                Schema::connection('central')->dropColumns('saas_subscriptions', $existing);
            }
        });
    }
};