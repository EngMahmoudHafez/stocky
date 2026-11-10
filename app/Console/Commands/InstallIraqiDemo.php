<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use RuntimeException;

class InstallIraqiDemo extends Command
{
    protected $signature = 'iraqi:install-demo';

    protected $description = 'Install the Iraqi brand demo data and activate the application';

    public function handle(): int
    {
        if (Storage::disk('public')->exists('installed')) {
            $this->error('The application is already marked as installed.');
            return self::FAILURE;
        }

        try {
            $this->runArtisan('migrate', ['--force' => true]);
            foreach (['users', 'settings', 'products', 'warehouses', 'clients'] as $table) {
                if (DB::table($table)->exists()) {
                    throw new RuntimeException("Table {$table} already has data. Installation stopped to protect it.");
                }
            }
            if (! is_file(Passport::keyPath('oauth-private.key')) || ! is_file(Passport::keyPath('oauth-public.key'))) {
                $this->runArtisan('passport:keys');
            }
            $this->runArtisan('db:seed', [
                '--class' => 'Database\\Seeders\\IraqiDemoSeeder',
                '--force' => true,
            ]);
            Storage::disk('public')->makeDirectory('');
            Storage::disk('public')->put('installed', 'OK');
            $this->runArtisan('optimize:clear');
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        }

        $this->info('Iraqi demo installed. Save the generated login credentials printed above.');
        return self::SUCCESS;
    }

    private function runArtisan(string $command, array $arguments = []): void
    {
        $exitCode = Artisan::call($command, $arguments);
        $output = Artisan::output();
        $this->output->write($output);

        if ($exitCode !== self::SUCCESS) {
            throw new RuntimeException("Command {$command} failed. See the output above.");
        }
    }
}
