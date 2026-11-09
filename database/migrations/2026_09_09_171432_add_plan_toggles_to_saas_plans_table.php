<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('saas_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('saas_plans', 'is_active')) {
                $table->boolean('is_active')->default(1)->after('id');
            }
            if (!Schema::hasColumn('saas_plans', 'is_private')) {
                $table->boolean('is_private')->default(0)->after('is_active');
            }
            if (!Schema::hasColumn('saas_plans', 'is_trial')) {
                $table->boolean('is_trial')->default(0)->after('is_private');
            }
            if (!Schema::hasColumn('saas_plans', 'trial_days')) {
                $table->integer('trial_days')->default(14)->after('is_trial');
            }
            if (!Schema::hasColumn('saas_plans', 'limits')) {
                $table->json('limits')->nullable()->after('features');
            }
        });
    }

    public function down()
    {
        Schema::table('saas_plans', function (Blueprint $table) {
            $columns = ['is_private', 'is_trial', 'trial_days', 'limits'];
            $existing = [];
            foreach ($columns as $col) {
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