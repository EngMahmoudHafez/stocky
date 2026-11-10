<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = DB::table('settings')->whereNull('deleted_at');
        (clone $settings)->update([
            'developed_by' => 'M.H',
            'footer' => '© M.H · تنفيذ M.H',
            'login_footer_text' => '© M.H · تنفيذ M.H',
        ]);

        foreach (['app_name', 'CompanyName', 'page_title_suffix', 'invoice_footer'] as $field) {
            (clone $settings)->whereRaw("(LOWER($field) LIKE ? OR LOWER($field) LIKE ?)", ['%stocky%', '%stoky%'])
                ->update([$field => $field === 'invoice_footer' ? 'M.H' : 'Inventory']);
        }
        (clone $settings)->whereIn('logo', ['logo.png', 'logo-default.png'])->update(['logo' => 'iraqi-demo-mark.png']);
        (clone $settings)->where('favicon', 'favicon.ico')->update(['favicon' => 'iraqi-demo-mark.png']);
    }

    public function down(): void
    {
        // Earlier editable branding values cannot be reconstructed.
    }
};
