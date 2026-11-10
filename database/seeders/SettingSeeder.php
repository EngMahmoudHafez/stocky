<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert some stuff
        DB::table('settings')->insert(
            [
                'id' => 1,
                'email' => 'admin@example.com',
                'currency_id' => Currency::retail()->id,
                'client_id' => 1,
                'sms_gateway' => 1,
                'point_to_amount_rate' => 1,
                'is_invoice_footer' => 0,
                'invoice_footer' => null,
                'warehouse_id' => null,
                'CompanyName' => 'Inventory',
                'CompanyPhone' => '6315996770',
                'CompanyAdress' => '3618 Abia Martin Drive',
                'footer' => '© M.H · Implemented by M.H',
                'developed_by' => 'M.H',
                'logo' => 'iraqi-demo-mark.png',
                'app_name' => 'Inventory',
                'page_title_suffix' => 'Inventory',
                'favicon' => 'iraqi-demo-mark.png',
                'default_language' => 'en',
                'quotation_with_stock' => 1,
                'show_language' => 1,
                'default_tax' => 0,
            ]

        );
    }
}
