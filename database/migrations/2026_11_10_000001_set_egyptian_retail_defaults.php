<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $poundId = DB::table('currencies')->where('code', 'EGP')->whereNull('deleted_at')->orderBy('id')->value('id')
                ?? DB::table('currencies')->insertGetId(['code' => 'EGP', 'name' => 'جنيه مصري', 'symbol' => 'ج.م']);
            DB::table('currencies')->where('id', $poundId)->update(['symbol' => 'ج.م']);
            DB::table('settings')->whereNull('deleted_at')->update(['currency_id' => $poundId]);
            DB::table('store_settings')->update(['currency_code' => 'ج.م']);

            $this->enableSupportedLanguages();
        });
    }

    private function enableSupportedLanguages(): void
    {
        $supported = ['ar', 'en'];
        DB::table('languages')->insertOrIgnore([
            ['locale' => 'ar', 'name' => 'العربية', 'flag' => 'sa.svg'],
            ['locale' => 'en', 'name' => 'English', 'flag' => 'gb.svg'],
        ]);
        DB::table('languages')->whereNotIn('locale', $supported)->update(['is_active' => 0, 'is_default' => 0]);
        DB::table('languages')->whereIn('locale', $supported)->update(['is_active' => 1]);
        DB::table('settings')->where(function ($query) use ($supported) {
            $query->whereNotIn('default_language', $supported)->orWhereNull('default_language');
        })->update(['default_language' => 'ar']);
        DB::table('store_settings')->where(function ($query) use ($supported) {
            $query->whereNotIn('language', $supported)->orWhereNull('language');
        })->update(['language' => 'ar']);

        $default = DB::table('settings')->whereNull('deleted_at')->orderBy('id')->value('default_language') ?? 'ar';
        DB::table('languages')->whereIn('locale', $supported)->update(['is_default' => 0]);
        DB::table('languages')->where('locale', $default)->update(['is_default' => 1]);
    }

    public function down(): void
    {
        // Previous defaults cannot be reconstructed. Existing currency and translation rows are retained.
    }
};
