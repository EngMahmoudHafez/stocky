<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('clients')) {
            return;
        }

        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'points')) {
                $table->integer('points')->default(0)->after('tax_number');
            }
            if (! Schema::hasColumn('clients', 'is_royalty_eligible')) {
                $table->boolean('is_royalty_eligible')->default(0)->after('points');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('clients')) {
            return;
        }

        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'is_royalty_eligible')) {
                $table->dropColumn('is_royalty_eligible');
            }
            if (Schema::hasColumn('clients', 'points')) {
                $table->dropColumn('points');
            }
        });
    }
};