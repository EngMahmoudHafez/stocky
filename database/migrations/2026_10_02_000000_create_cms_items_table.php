<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_items', function (Blueprint $table) {
            $table->id();
            $table->string('section', 50)->index();   // features, stats, steps, testimonials, faq, footer_columns, social, ...
            $table->string('title')->nullable();      // title / question / author / heading / platform
            $table->string('subtitle')->nullable();   // role / description / label
            $table->text('body')->nullable();         // description / answer / quote / links
            $table->string('icon')->nullable();       // bootstrap icon class
            $table->string('color', 30)->nullable();
            $table->string('image')->nullable();
            $table->string('value')->nullable();      // stat value / button url
            $table->string('url')->nullable();
            $table->string('rating', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_items');
    }
};
