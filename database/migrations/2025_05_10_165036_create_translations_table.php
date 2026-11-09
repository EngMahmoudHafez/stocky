<?php

use Database\Seeders\TranslationSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the translations table with both original columns and the
     * is_customized flag (originally added by a follow-up migration).
     * Then seed translations from the TranslationSeeder.
     */
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->integer('id', true);
            $table->string('locale');                  // e.g. 'en', 'ar', 'fr'
            $table->string('key');                     // e.g. 'List_accounts'
            $table->text('value');                     // e.g. 'List of Accounts'
            $table->boolean('is_default')->default(false);
            $table->boolean('is_customized')->default(false);  // merged in from the follow-up migration
            $table->timestamps(6);

            $table->unique(['locale', 'key']);          // prevent duplicates
        });

        // Run the seeder here so a fresh tenant DB gets translations immediately.
        (new TranslationSeeder)->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};