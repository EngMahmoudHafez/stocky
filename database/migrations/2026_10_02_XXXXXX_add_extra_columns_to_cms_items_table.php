<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cms_items', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_items', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('cms_items', 'color')) {
                $table->string('color')->nullable()->default('green')->after('icon');
            }
            if (!Schema::hasColumn('cms_items', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_active');
            }
        });
    }

    public function down()
    {
        Schema::table('cms_items', function (Blueprint $table) {
            $table->dropColumn(['description', 'color', 'sort_order']);
        });
    }
};
