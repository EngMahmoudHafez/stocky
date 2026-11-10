<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('material', 100)->nullable()->index();
            $table->string('season', 100)->nullable()->index();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('color', 100)->nullable();
            $table->string('size', 100)->nullable();
            $table->index(['product_id', 'color', 'size']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('size_guide_id')->nullable()->index();
        });

        DB::table('product_variants')->select('id', 'name')->chunkById(500, function ($variants) {
            foreach ($variants as $variant) {
                if (preg_match('/^\s*([^\/]+?)\s+\/\s+([^\/]+?)\s*$/u', $variant->name ?? '', $parts)
                    && mb_strlen($parts[1]) <= 100 && mb_strlen($parts[2]) <= 100) {
                    DB::table('product_variants')->where('id', $variant->id)->update([
                        'color' => trim($parts[1]), 'size' => trim($parts[2]),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['size_guide_id']);
            $table->dropColumn('size_guide_id');
        });
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'color', 'size']);
            $table->dropColumn(['color', 'size']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['material']);
            $table->dropIndex(['season']);
            $table->dropColumn(['material', 'season']);
        });
    }
};
