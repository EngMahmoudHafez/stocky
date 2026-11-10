<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\StoreSetting;
use App\Support\IraqiBrand;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ApplyIraqiBrand extends Command
{
    protected $signature = 'iraqi:apply-brand';

    protected $description = 'Apply the ELERAKY identity to an existing Iraqi installation';

    public function handle(): int
    {
        $settings = DB::table('settings')->whereNull('deleted_at')
            ->whereIn('CompanyName', ['براند العراقي', IraqiBrand::NAME, IraqiBrand::NAME.' | ELERAKY']);
        $store = StoreSetting::query()->whereIn('store_name', ['براند العراقي', IraqiBrand::NAME]);
        if (! $settings->exists() || ! $store->exists()) {
            $this->error('No existing Iraqi installation found. No data was changed.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($settings, $store): void {
            $settings->update(IraqiBrand::administration());
            $store->update(IraqiBrand::storefront());
            DB::table('brands')->where('name', 'براند العراقي')->update(['name' => IraqiBrand::NAME]);
            DB::table('companies')->where('name', 'براند العراقي')->update(['name' => IraqiBrand::NAME]);
            DB::table('pos_settings')->where('note_customer', 'شكراً لتسوقكم من براند العراقي')
                ->update(['note_customer' => 'شكراً لتسوقكم من العراقي | ELERAKY']);
        });
        Cache::forget('store_settings');
        $this->info('ELERAKY branding applied. Existing accounts, passwords and transactions were preserved.');

        return self::SUCCESS;
    }
}
