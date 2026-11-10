<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        Currency::whereNull('deleted_at')->firstOrCreate(
            ['code' => Currency::RETAIL_CODE],
            ['name' => 'جنيه مصري', 'symbol' => Currency::RETAIL_SYMBOL],
        );
    }
}
