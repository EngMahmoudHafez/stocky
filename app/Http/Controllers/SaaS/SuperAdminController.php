<?php

namespace App\Http\Controllers\SaaS;

use App\Http\Controllers\Controller;
use App\Models\SaaS\{Tenant, Plan, Subscription};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    private function check(Request $r)
    {
        if (!$r->session()->get('saas_super_admin')) {
            $r->session()->forget([
                'saas_super_admin',
                'saas_master_admin',
                'saas_super_admin_id',
                'saas_super_admin_name',
                'saas_super_admin_email',
            ]);

            abort(redirect('/super/login'));
        }

        $r->session()->put('saas_super_admin_last_activity', now()->timestamp);
    }

    public function login()
    {
        $settingsFile = storage_path('app/super-settings.json');
        $settings = is_file($settingsFile) ? (json_decode(file_get_contents($settingsFile), true) ?: []) : [];

        $settings = array_merge([
            'app_name'     => 'BizTrack ERP',
            'company_name' => 'BizTrack ERP',
        ], $settings);

        return view('saas.super-login', compact('settings'));
    }

    public function authenticate(Request $r)
    {
        $r->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email    = (string) $r->email;
        $password = (string) $r->password;

        $adminEmail    = (string) env('SAAS_ADMIN_EMAIL', '');
        $adminPassword = (string) env('SAAS_ADMIN_PASSWORD', '');

        if (hash_equals($adminEmail, $email) && hash_equals($adminPassword, $password)) {
            $r->session()->put('saas_super_admin', true);
            $r->session()->put('saas_super_admin_name', 'Super Admin');
            $r->session()->put('saas_super_admin_email', $email);
            $r->session()->put('saas_super_admin_id', 0);

            $primary = \App\Models\SaaS\SuperAdmin::where('email', $adminEmail)->first();
            if (!$primary) {
                \App\Models\SaaS\SuperAdmin::create([
                    'name'       => 'Super Admin',
                    'email'      => $adminEmail,
                    'password'   => Hash::make($adminPassword),
                    'role'       => 'Super Admin',
                    'is_primary' => 1,
                    'is_active'  => 1,
                ]);
            }

            return redirect('/super');
        }

        $admin = \App\Models\SaaS\SuperAdmin::where('email', $email)
                    ->where('is_active', 1)
                    ->first();

        if ($admin && Hash::check($password, $admin->password)) {
            $r->session()->put('saas_super_admin', true);
            $r->session()->put('saas_super_admin_id', $admin->id);
            $r->session()->put('saas_super_admin_name', $admin->name);
            $r->session()->put('saas_super_admin_email', $admin->email);

            return redirect('/super');
        }

        return back()
            ->withInput($r->only('email'))
            ->withErrors(['email' => 'Invalid credentials.']);
    }

    public function logout(Request $r)
    {
        $r->session()->forget('saas_super_admin');
        return redirect('/super/login');
    }

    public function dashboard(Request $r)
    {
        $this->check($r);
        return view('saas.dashboard', [
            'tenants'       => Tenant::count(),
            'active'        => Tenant::whereIn('status', ['active', 'trial'])->count(),
            'pending'       => Tenant::where('status', 'pending')->count(),
            'plans'         => Plan::where('is_active', 1)->count(),
            'subscriptions' => Subscription::count(),
        ]);
    }

    public function tenants(Request $r)
    {
        $this->check($r);
        return view('saas.tenants', [
            'tenants' => Tenant::with('plan')->latest()->get(),
            'plans'   => Plan::where('is_active', 1)->get(),
        ]);
    }

    private function connectionArray(Tenant $tenant)
    {
        return [
            'driver'    => 'mysql',
            'host'      => $tenant->database_host     ?: '127.0.0.1',
            'port'      => $tenant->database_port     ?: 3306,
            'database'  => $tenant->database_name,
            'username'  => $tenant->database_username ?: env('TENANT_DB_USERNAME', env('DB_USERNAME', '')),
            'password'  => $tenant->database_password ?: env('TENANT_DB_PASSWORD', env('DB_PASSWORD', '')),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
        ];
    }

    private function syncPlanModulesToTenants(Plan $plan)
    {
        $tenants = Tenant::where('plan_id', $plan->id)->get();
        if ($tenants->isEmpty()) return;

        $planFeatures = is_array($plan->features)
            ? $plan->features
            : (json_decode($plan->features ?? '[]', true) ?: []);
        $allAccess = in_array('*', $planFeatures, true);

        $moduleToPlanFeature = [
            'Store' => 'store', 'hrm' => 'hrm', 'recruit' => 'recruit',
            'meeting' => 'meetings', 'marketing' => 'marketing', 'accounting' => 'accounting',
            'EWallet' => 'e_wallet', 'commissions' => 'commissions', 'promotions' => 'promotions',
            'woocommerce_settings' => 'woocommerce', 'shopify' => 'shopify', 'documents' => 'documents',
            'subscription_product' => 'subscription_product', 'manufacturing' => 'manufacturing',
            'assets' => 'assets', 'projects' => 'projects', 'bookings' => 'bookings',
            'service' => 'service_maintenance', 'fleet' => 'fleet', 'hospital' => 'hospital',
            'school' => 'school',
        ];

        $oldDefault = config('database.default');
        foreach ($tenants as $tenant) {
            try {
                config(['database.connections.tenant' => $this->connectionArray($tenant)]);
                DB::purge('tenant');
                DB::setDefaultConnection('tenant');

                $setting = DB::table('settings')->whereNull('deleted_at')->first();
                if ($setting) {
                    $flags = [];
                    foreach ($moduleToPlanFeature as $moduleKey => $featureKey) {
                        $flags[$moduleKey] = $allAccess || in_array($featureKey, $planFeatures, true);
                    }
                    DB::table('settings')->where('id', $setting->id)->update([
                        'module_flags' => json_encode($flags),
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to sync plan flags to tenant {$tenant->slug}: " . $e->getMessage());
            } finally {
                DB::setDefaultConnection($oldDefault);
                config(['database.default' => $oldDefault]);
            }
        }
    }

    // ================================================================
    // CREATE TENANT
    // ================================================================
    public function createTenant(Request $r)
    {
        set_time_limit(0);
        $this->check($r);

        // Shared-hosting mode: the database and user must already exist.
        $data = $r->validate([
            'name'             => 'required|string|max:191',
            'slug'             => 'required|alpha_dash|max:30|unique:saas_tenants,slug',
            'email'            => 'required|email|max:191',
            'phone'            => 'nullable|max:191',
            'password'         => 'required|min:8',
            'language'         => 'nullable|string|max:10',
            'plan_id'          => 'required|exists:saas_plans,id',
            'billing_cycle'    => 'required|in:monthly,yearly',
            'database_host'    => 'required|string|max:191',
            'database_name'    => ['required', 'string', 'max:191', 'regex:/^[A-Za-z0-9_]+$/'],
            'database_port'    => 'required|integer|between:1,65535',
            'database_username'=> ['required', 'string', 'max:191', 'regex:/^[A-Za-z0-9_@.-]+$/'],
            'database_password'=> 'nullable|string|max:255',
        ]);

        $plainPassword = $data['password'];
        unset($data['password']);

        $dbHost = trim($data['database_host']);
        $dbName = trim($data['database_name']);
        $dbPort = (int) $data['database_port'];
        $dbUser = trim($data['database_username']);
        $dbPass = (string) ($data['database_password'] ?? '');
        $billingCycle = $data['billing_cycle'];

        // Test credentials and require a fresh/empty database before saving anything.
        try {
            $pdo = new \PDO(
                "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
                $dbUser,
                $dbPass,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_TIMEOUT => 8]
            );
            $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_NUM);
            if (count($tables) > 0) {
                throw new \RuntimeException('The selected database is not empty. Create a new empty database for this tenant; existing tables will not be deleted.');
            }
            $pdo = null;
        } catch (\Throwable $e) {
            \Log::warning('Manual tenant DB connection check failed: ' . $e->getMessage());
            return back()->withInput()->withErrors([
                'database_name' => 'Could not use this database. Verify the host, database name, port, username and password, and ensure the database already exists and is empty. Details: ' . $e->getMessage(),
            ]);
        }

        $plan = Plan::findOrFail($data['plan_id']);
        $baseDomain = strtolower(trim((string) env('SAAS_BASE_DOMAIN', parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost')));
        $data['status'] = 'trial';
        $data['trial_ends_at'] = now()->addDays((int) $plan->trial_days);
        $data['database_host'] = $dbHost;
        $data['database_name'] = $dbName;
        $data['database_port'] = $dbPort;
        $data['database_username'] = $dbUser;
        $data['database_password'] = $dbPass;
        $data['domain'] = $data['slug'] . '.' . $baseDomain;
        unset($data['billing_cycle']);

        $tenant = null;
        try {
            $tenant = Tenant::create($data);

            Subscription::create([
                'tenant_id'     => $tenant->id,
                'plan_id'       => $tenant->plan_id,
                'status'        => 'trial',
                'billing_cycle' => $billingCycle,
                'amount'        => $billingCycle === 'yearly' ? $plan->yearly_price : $plan->monthly_price,
                'trial_ends_at' => $tenant->trial_ends_at,
            ]);

            $this->migrateTenant($tenant);

            // Seed Farsi language + translations for the new tenant
            $this->seedFarsiLanguage($tenant);

            $this->createTenantAdminUser($tenant, $data['name'], $data['email'], $plainPassword);
            $this->syncPlanModulesToTenants($plan);
        } catch (\Throwable $e) {
            \Log::error('Manual tenant setup failed: ' . $e->getMessage());
            // Remove central records created during this attempt, but never drop the manually-created DB.
            if ($tenant) {
                Subscription::where('tenant_id', $tenant->id)->delete();
                $tenant->delete();
            }
            return back()->withInput()->withErrors(['database_name' => 'Tenant setup failed: ' . $e->getMessage()]);
        }

        return redirect('/super/tenants')->with('success', 'Tenant created successfully. Database connection verified. Tenant admin: ' . $data['email']);
    }

    private function createTenantAdminUser(Tenant $tenant, string $name, string $email, string $plainPassword): void
    {
        $old = config('database.default');

        try {
            config(['database.connections.tenant' => $this->connectionArray($tenant)]);
            DB::purge('tenant');
            DB::setDefaultConnection('tenant');

            if (!\Schema::connection('tenant')->hasTable('users')) {
                throw new \RuntimeException('users table missing after migration.');
            }

            $cols = \DB::getSchemaBuilder()->getColumnListing('users');

            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            $placeholderEmails = [$email, 'admin@admin.com', 'admin@example.com', 'owner@example.com'];
            $placeholderUserIds = DB::table('users')->whereIn('email', $placeholderEmails)->pluck('id')->all();

            if (!empty($placeholderUserIds)) {
                if (\Schema::connection('tenant')->hasTable('role_user')) {
                    DB::table('role_user')->whereIn('user_id', $placeholderUserIds)->delete();
                }
                if (\Schema::connection('tenant')->hasTable('user_warehouse')) {
                    DB::table('user_warehouse')->whereIn('user_id', $placeholderUserIds)->delete();
                }
                if (\Schema::connection('tenant')->hasTable('user_login_sessions')) {
                    DB::table('user_login_sessions')->whereIn('user_id', $placeholderUserIds)->delete();
                }
                DB::table('users')->whereIn('id', $placeholderUserIds)->delete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $row = [
                'email'      => $email,
                'password'   => Hash::make($plainPassword),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (in_array('name', $cols)) {
                $row['name'] = $name;
            } elseif (in_array('firstname', $cols)) {
                $row['firstname'] = $name;
                if (in_array('lastname', $cols)) $row['lastname'] = '';
            } elseif (in_array('first_name', $cols)) {
                $row['first_name'] = $name;
                if (in_array('last_name', $cols)) $row['last_name'] = '';
            }

            if (in_array('username', $cols))          $row['username'] = $email;
            if (in_array('is_active', $cols))         $row['is_active'] = 1;
            if (in_array('user_type', $cols))         $row['user_type'] = 'owner';
            if (in_array('role', $cols))              $row['role'] = 'owner';
            if (in_array('email_verified_at', $cols)) $row['email_verified_at'] = now();
            if (in_array('statut', $cols))            $row['statut'] = 1;
            if (in_array('status', $cols))            $row['status'] = 1;

            $userId = DB::table('users')->insertGetId($row);

            if (\Schema::connection('tenant')->hasTable('roles')) {
                $ownerRole = DB::table('roles')->where('id', 1)->orWhere('name', 'Owner')->first();
                if ($ownerRole) {
                    if (in_array('role_id', $cols)) {
                        DB::table('users')->where('id', $userId)->update(['role_id' => $ownerRole->id]);
                    }
                    if (\Schema::connection('tenant')->hasTable('role_user')) {
                        DB::table('role_user')->insertOrIgnore([
                            'user_id' => $userId,
                            'role_id' => $ownerRole->id,
                        ]);
                    }
                }
            }

            \Log::info("Tenant admin user created for {$tenant->slug}: {$email} (user id {$userId})");

        } catch (\Exception $e) {
            \Log::error("Failed to create tenant admin user for {$tenant->slug}: " . $e->getMessage());
            throw $e;
        } finally {
            DB::setDefaultConnection($old);
            config(['database.default' => $old]);
            DB::purge('tenant');
        }
    }

    /**
     * Automatically install Farsi language + translations
     * into every newly created tenant.
     *
     * Copies the Farsi rows from the central (master) `translations`
     * table into the tenant's `languages` and `translations` tables.
     */
    private function seedFarsiLanguage(Tenant $tenant): void
    {
        $oldDefault = config('database.default');
        // Capture the central connection name BEFORE we switch to tenant
        $centralConnection = $oldDefault ?: 'mysql';

        try {

            // ---------------------------------------------------------
            // Connect to tenant database
            // ---------------------------------------------------------
            config([
                'database.connections.tenant' => $this->connectionArray($tenant),
            ]);

            DB::purge('tenant');
            DB::reconnect('tenant');

            DB::setDefaultConnection('tenant');

            config([
                'database.default' => 'tenant',
            ]);

            // ---------------------------------------------------------
            // Make sure required tables exist
            // ---------------------------------------------------------
            if (!\Schema::connection('tenant')->hasTable('languages')) {
                \Log::warning("languages table missing for tenant {$tenant->slug}");
                return;
            }

            if (!\Schema::connection('tenant')->hasTable('translations')) {
                \Log::warning("translations table missing for tenant {$tenant->slug}");
                return;
            }

            // =========================================================
            // 1. CREATE FARSI LANGUAGE
            // =========================================================

            $language = DB::connection('tenant')
                ->table('languages')
                ->where('locale', 'fa')
                ->first();

            if (!$language) {

                DB::connection('tenant')
                    ->table('languages')
                    ->insert([
                        'name'       => 'farsi',
                        'locale'     => 'fa',
                        'is_default' => 0,
                        'is_active'  => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                \Log::info("Farsi language created for tenant {$tenant->slug}");
            } else {

                // Make sure an existing Farsi language is active.
                DB::connection('tenant')
                    ->table('languages')
                    ->where('id', $language->id)
                    ->update([
                        'is_active'  => 1,
                        'updated_at' => now(),
                    ]);
            }

            // =========================================================
            // 2. COPY FARSI TRANSLATIONS FROM CENTRAL
            // =========================================================

            // Guard: central translations table must exist
            if (!\Schema::connection($centralConnection)->hasTable('translations')) {
                \Log::warning("Central translations table missing; skipping Farsi seed for {$tenant->slug}.");
                return;
            }

            $farsiTranslations = DB::connection($centralConnection)
                ->table('translations')
                ->where('locale', 'fa')
                ->get([
                    'locale',
                    'key',
                    'value',
                    'is_default',
                    'is_customized',
                    'created_at',
                    'updated_at',
                ]);

            if ($farsiTranslations->isEmpty()) {
                \Log::warning("No Farsi translations found in central database.");
                return;
            }

            $inserted = 0;

            foreach ($farsiTranslations as $translation) {

                $exists = DB::connection('tenant')
                    ->table('translations')
                    ->where('locale', 'fa')
                    ->where('key', $translation->key)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::connection('tenant')
                    ->table('translations')
                    ->insert([
                        'locale'        => 'fa',
                        'key'           => $translation->key,
                        'value'         => $translation->value,
                        'is_default'    => $translation->is_default ?? 0,
                        'is_customized' => $translation->is_customized ?? 0,
                        'created_at'    => $translation->created_at ?? now(),
                        'updated_at'    => $translation->updated_at ?? now(),
                    ]);

                $inserted++;
            }

            \Log::info(
                "Farsi installed for tenant {$tenant->slug}. " .
                "{$inserted} translation records inserted."
            );

        } catch (\Throwable $e) {

            \Log::error(
                "Failed to install Farsi for tenant {$tenant->slug}: " .
                $e->getMessage()
            );

            throw $e;

        } finally {

            DB::setDefaultConnection($oldDefault);

            config([
                'database.default' => $oldDefault,
            ]);

            DB::purge('tenant');
        }
    }

    /**
     * Import a phpMyAdmin SQL dump using PDO so shared hosting does not need
     * the mysql command-line binary or shell_exec().
     */
    private function importSqlDump(\PDO $pdo, string $file): void
    {
        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new \RuntimeException("Unable to read tenant schema dump: {$file}");
        }

        $statement = '';
        $length = strlen($sql);
        $quote = null;
        $lineComment = false;
        $blockComment = false;
        $escaped = false;

        for ($i = 0; $i < $length; $i++) {
            $ch = $sql[$i];
            $next = $i + 1 < $length ? $sql[$i + 1] : '';

            if ($lineComment) {
                if ($ch === "\n") {
                    $lineComment = false;
                    $statement .= "\n";
                }
                continue;
            }

            if ($blockComment) {
                if ($ch === '*' && $next === '/') {
                    $blockComment = false;
                    $i++;
                }
                continue;
            }

            if ($quote !== null) {
                $statement .= $ch;
                if ($escaped) {
                    $escaped = false;
                } elseif ($ch === '\\' && $quote !== '`') {
                    $escaped = true;
                } elseif ($ch === $quote) {
                    if ($next === $quote) {
                        $statement .= $next;
                        $i++;
                    } else {
                        $quote = null;
                    }
                }
                continue;
            }

            if (($ch === '-' && $next === '-' && ($i + 2 >= $length || ctype_space($sql[$i + 2]))) ||
                $ch === '#') {
                $lineComment = true;
                if ($ch === '-') $i++;
                continue;
            }

            if ($ch === '/' && $next === '*') {
                $blockComment = true;
                $i++;
                continue;
            }

            if ($ch === "'" || $ch === '"' || $ch === '`') {
                $quote = $ch;
                $statement .= $ch;
                continue;
            }

            if ($ch === ';') {
                $trimmed = trim($statement);
                if ($trimmed !== '') {
                    $pdo->exec($trimmed);
                }
                $statement = '';
                continue;
            }

            $statement .= $ch;
        }

        $trimmed = trim($statement);
        if ($trimmed !== '') {
            $pdo->exec($trimmed);
        }
    }

    private function migrateTenant(Tenant $tenant)
    {
        $old = config('database.default');

        try {
            $central = config('database.connections.mysql');
            $dbName  = $tenant->database_name;

            if (!$dbName) {
                throw new \RuntimeException('Tenant has no database_name.');
            }

            $centralUser = $central['username'];
            $centralPass = $central['password'];

            $provisionMode = strtolower((string) env('TENANT_DB_PROVISION_MODE', 'auto'));

            if ($provisionMode !== 'manual') {
                // VPS mode: the configured MySQL account must be allowed to create databases.
                $rootPdo = new \PDO(
                    "mysql:host={$central['host']};port={$central['port']}",
                    $centralUser,
                    $centralPass,
                    [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
                );
                $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                // Optional only for installations where the DB account has GRANT privileges.
                if (env('TENANT_DB_GRANT_PRIVILEGES', false)) {
                    $tenantUser = (string) $tenant->database_username;
                    $quotedUser = str_replace("'", "\\'", $tenantUser);
                    $rootPdo->exec("GRANT ALL PRIVILEGES ON `{$dbName}`.* TO '{$quotedUser}'@'localhost'");
                    $rootPdo->exec("GRANT ALL PRIVILEGES ON `{$dbName}`.* TO '{$quotedUser}'@'127.0.0.1'");
                }
                $rootPdo = null;
            }

            config(['database.connections.tenant' => $this->connectionArray($tenant)]);
            DB::purge('tenant');
            DB::setDefaultConnection('tenant');

            $existing = DB::connection('tenant')->select('SHOW TABLES');
            if ($provisionMode === 'manual' && count($existing) > 0) {
                throw new \RuntimeException('The manually configured tenant database must be empty. No existing tables were changed.');
            }

            // Preserve the previous automatic VPS behavior, but never erase tables in manual mode.
            if ($provisionMode !== 'manual') {
                DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=0');
                $key = 'Tables_in_' . $dbName;
                foreach ($existing as $row) {
                    $tbl = $row->$key ?? array_values((array) $row)[0];
                    DB::connection('tenant')->statement("DROP TABLE IF EXISTS `{$tbl}`");
                }
                DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=1');
            }

            $schemaFile = storage_path('app/stocky_tenants_database/stocky.sql');
            if (!is_file($schemaFile)) {
                throw new \RuntimeException("Tenant schema dump missing at {$schemaFile}.");
            }

            \Log::info("Tenant {$tenant->slug}: importing schema through the tenant PDO connection.");
            $tenantPdo = DB::connection('tenant')->getPdo();
            $this->importSqlDump($tenantPdo, $schemaFile);
            $tenantPdo = null;

            DB::purge('tenant');
            DB::reconnect('tenant');
            $row = DB::connection('tenant')
                ->select("SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = ?", [$dbName]);
            $imported = (int) ($row[0]->c ?? 0);

            if ($imported < 100) {
                throw new \RuntimeException("Schema import produced only {$imported} tables; expected ~278.");
            }

            \Log::info("Tenant {$tenant->slug}: {$imported} tables imported successfully.");

            try {
                \Artisan::call('db:seed', [
                    '--database' => 'tenant',
                    '--class'    => 'Database\\Seeders\\DatabaseSeeder',
                    '--force'    => true,
                ]);
                \Log::info("Tenant {$tenant->slug}: seeded. " . trim(\Artisan::output()));
            } catch (\Throwable $e) {
                \Log::warning("Tenant seeder failed for {$tenant->slug}: " . $e->getMessage());
            }

        } finally {
            DB::setDefaultConnection($old);
            config(['database.default' => $old]);
            DB::purge('tenant');
        }
    }

    // ================================================================
    // PLANS
    // ================================================================
    public function plans(Request $r)
    {
        $this->check($r);
        $plans = Plan::withCount('tenants')->latest()->get();
        return view('saas.plans', compact('plans'));
    }

    public function createPlanForm(Request $r)
    {
        $this->check($r);
        return view('saas.plans-create');
    }

    public function storePlan(Request $r)
    {
        $this->check($r);

        $d = $r->validate([
            'name' => 'required',
            'slug' => 'required|alpha_dash|unique:saas_plans,slug',
            'price' => 'required|numeric|min:0',
            'yearly_price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'is_private' => 'nullable|boolean',
            'is_trial' => 'nullable|boolean',
            'trial_days' => 'nullable|integer',
            'limits' => 'nullable|array',
            'features' => 'nullable|array',
        ]);

        $d['is_active']  = $r->has('is_active') ? 1 : 0;
        $d['is_private'] = $r->has('is_private') ? 1 : 0;
        $d['is_trial']   = $r->has('is_trial') ? 1 : 0;

        $d['monthly_price'] = $d['price'];
        unset($d['price']);

        $d['limits']   = json_encode($d['limits'] ?? []);
        $d['features'] = json_encode($d['features'] ?? []);

        $plan = Plan::create($d);
        $this->syncPlanModulesToTenants($plan);

        return redirect('/super/plans')->with('success', 'Plan created successfully!');
    }

    public function editPlanForm(Request $r, Plan $plan)
    {
        $this->check($r);
        return view('saas.plans-create', compact('plan'));
    }

    public function updatePlan(Request $r, Plan $plan)
    {
        $this->check($r);

        $r->merge([
            'is_active'  => $r->has('is_active') ? 1 : 0,
            'is_private' => $r->has('is_private') ? 1 : 0,
            'is_trial'   => $r->has('is_trial') ? 1 : 0,
        ]);

        $d = $r->validate([
            'name' => 'required',
            'slug' => 'required|alpha_dash|unique:saas_plans,slug,' . $plan->id,
            'price' => 'required|numeric|min:0',
            'yearly_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'is_private' => 'boolean',
            'is_trial' => 'boolean',
            'trial_days' => 'nullable|integer',
            'limits' => 'nullable|array',
            'features' => 'nullable|array',
        ]);

        $d['monthly_price'] = $d['price'];
        unset($d['price']);

        $d['limits']   = json_encode($d['limits'] ?? []);
        $d['features'] = json_encode($d['features'] ?? []);

        $plan->update($d);
        $this->syncPlanModulesToTenants($plan);

        return redirect('/super/plans')->with('success', 'Plan updated successfully!');
    }

    // ================================================================
    // TENANT CRUD — edit/update/login-as
    // ================================================================
    public function createTenantForm(Request $r)
    {
        $this->check($r);
        $plans = Plan::where('is_active', 1)->get();
        return view('saas.tenant-create', compact('plans'));
    }

    public function editTenant(Request $r, Tenant $tenant)
    {
        $this->check($r);
        $plans = Plan::where('is_active', 1)->get();
        return view('saas.tenant-create', compact('tenant', 'plans'));
    }

    public function showTenant(Request $r, Tenant $tenant)
    {
        $this->check($r);

        $tenant->load(['plan', 'subscriptions']);

        $ownerStatus = null;
        $old = config('database.default');
        try {
            config(['database.connections.tenant' => $this->connectionArray($tenant)]);
            DB::purge('tenant');
            DB::setDefaultConnection('tenant');

            if (\Schema::connection('tenant')->hasTable('users')) {
                $ownerStatus = DB::connection('tenant')
                    ->table('users')
                    ->where('email', $tenant->email)
                    ->whereNull('deleted_at')
                    ->value('statut');
            }
        } catch (\Throwable $e) {
            \Log::warning("Could not read owner status for {$tenant->slug}: " . $e->getMessage());
        } finally {
            DB::setDefaultConnection($old);
            config(['database.default' => $old]);
            DB::purge('tenant');
        }

        return view('saas.tenant-show', compact('tenant', 'ownerStatus'));
    }

    public function loginAsTenant(Request $r, Tenant $tenant)
    {
        $this->check($r);
        $base   = env('SAAS_BASE_DOMAIN', parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost');
        $domain = $tenant->domain ?: ($tenant->slug . '.' . $base);
        $scheme = env('SAAS_TENANT_SCHEME', $r->isSecure() ? 'https' : 'http');
        return redirect()->away($scheme . '://' . $domain . '/login');
    }

    public function updateTenant(Request $r, Tenant $tenant)
    {
        $this->check($r);

        $data = $r->validate([
            'name'          => 'required|max:191',
            'slug'          => 'required|alpha_dash|unique:saas_tenants,slug,' . $tenant->id,
            'email'         => 'nullable|email',
            'phone'         => 'nullable|max:191',
            'language'      => 'nullable|max:10',
            'billing_cycle' => 'required|in:monthly,yearly',
            'password'      => 'nullable|min:8',
            'plan_id'       => 'required|exists:saas_plans,id',
        ]);

        $newPassword    = $data['password'] ?? null;
        unset($data['password']);
        $oldTenantEmail = $tenant->email;
        $newTenantEmail = !empty($data['email']) ? $data['email'] : $oldTenantEmail;

        $tenant->update($data);

        $selectedPlan        = Plan::findOrFail($data['plan_id']);
        $currentSubscription = Subscription::on('central')
            ->where('tenant_id', $tenant->id)
            ->latest('id')
            ->first();
        if ($currentSubscription) {
            $currentSubscription->update([
                'plan_id'       => $selectedPlan->id,
                'billing_cycle' => $data['billing_cycle'],
                'amount'        => $data['billing_cycle'] === 'yearly'
                    ? $selectedPlan->yearly_price
                    : $selectedPlan->monthly_price,
            ]);
        }

        if (!empty($newPassword) || !empty($data['name']) || !empty($data['email'])) {
            try {
                $old = config('database.default');
                config(['database.connections.tenant' => $this->connectionArray($tenant)]);
                DB::purge('tenant');
                DB::setDefaultConnection('tenant');

                $adminUser = \DB::table('users')->where('email', $oldTenantEmail)->first();
                if (!$adminUser && $newTenantEmail !== $oldTenantEmail) {
                    $adminUser = \DB::table('users')->where('email', $newTenantEmail)->first();
                }
                $updateData = [];

                if (!empty($newPassword)) {
                    $updateData['password'] = Hash::make($newPassword);
                }

                $tenantUserColumns = \DB::getSchemaBuilder()->getColumnListing('users');

                if ($newTenantEmail && in_array('email', $tenantUserColumns) && $newTenantEmail !== $oldTenantEmail) {
                    $updateData['email'] = $newTenantEmail;
                }

                if (in_array('name', $tenantUserColumns)) {
                    $updateData['name'] = $data['name'];
                } elseif (in_array('firstname', $tenantUserColumns)) {
                    $updateData['firstname'] = $data['name'];
                } elseif (in_array('first_name', $tenantUserColumns)) {
                    $updateData['first_name'] = $data['name'];
                }

                if (in_array('statut', $tenantUserColumns)) {
                    $updateData['statut'] = 1;
                }

                if ($adminUser) {
                    \DB::table('users')->where('id', $adminUser->id)->update($updateData);
                } else {
                    $insertData = [
                        'email'      => $newTenantEmail,
                        'password'   => Hash::make($newPassword ?: 'password'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    if (in_array('name', $tenantUserColumns)) {
                        $insertData['name'] = $data['name'];
                    } elseif (in_array('firstname', $tenantUserColumns)) {
                        $insertData['firstname'] = $data['name'];
                        $insertData['lastname']  = '';
                    } elseif (in_array('first_name', $tenantUserColumns)) {
                        $insertData['first_name'] = $data['name'];
                        $insertData['last_name']  = '';
                    }
                    if (in_array('statut', $tenantUserColumns)) {
                        $insertData['statut'] = 1;
                    }
                    \DB::table('users')->insert($insertData);
                }

                DB::setDefaultConnection($old);
                config(['database.default' => $old]);

            } catch (\Exception $e) {
                \Log::error('Failed to update tenant user password: ' . $e->getMessage());
                return back()->withInput()->withErrors(['password' => 'Could not update user password: ' . $e->getMessage()]);
            }
        }

        return redirect('/super/tenants')->with('success', 'Tenant updated successfully!');
    }

    // ================================================================
    // SUPER ADMINS
    // ================================================================
    public function admins(Request $r)
    {
        $this->check($r);
        $primaryEmail = env('SAAS_ADMIN_EMAIL', 'superadmin@example.com');
        $primary      = \App\Models\SaaS\SuperAdmin::where('email', $primaryEmail)->first();

        if (!$primary) {
            \App\Models\SaaS\SuperAdmin::create([
                'name'       => 'Super Admin',
                'email'      => $primaryEmail,
                'password'   => Hash::make(env('SAAS_ADMIN_PASSWORD', 'password')),
                'role'       => 'Super Admin',
                'is_primary' => 1,
                'is_active'  => 1,
            ]);
        }

        $admins = \App\Models\SaaS\SuperAdmin::orderBy('is_primary', 'desc')->latest()->get();
        return view('saas.admins', compact('admins'));
    }

    public function createAdmin(Request $r)
    {
        $this->check($r);
        $d = $r->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|unique:saas_super_admins,email',
            'password' => 'required|min:8',
        ]);

        \App\Models\SaaS\SuperAdmin::create([
            'name'       => $d['name'],
            'email'      => $d['email'],
            'password'   => Hash::make($d['password']),
            'role'       => 'Super Admin',
            'is_primary' => 0,
            'is_active'  => 1,
        ]);

        return back()->with('success', 'Super Admin created successfully!');
    }

    public function deleteAdmin(Request $r, \App\Models\SaaS\SuperAdmin $admin)
    {
        $this->check($r);
        if ($admin->is_primary) {
            return back()->withErrors(['error' => 'Cannot delete the primary Super Admin.']);
        }
        $admin->delete();
        return back()->with('success', 'Super Admin deleted successfully!');
    }

    public function updateAdmin(Request $r, \App\Models\SaaS\SuperAdmin $admin)
    {
        $this->check($r);
        $isPrimary = (bool) $admin->is_primary;

        $rules = [
            'name' => 'required|string|max:191',
            'password' => 'nullable|min:8',
        ];

        if (!$isPrimary) {
            $rules['email'] = 'required|email|unique:saas_super_admins,email,' . $admin->id;
        }

        $d      = $r->validate($rules);
        $update = ['name' => $d['name']];

        if (!$isPrimary) {
            $update['email'] = $d['email'];
        }

        if (!empty($d['password'])) {
            $update['password'] = Hash::make($d['password']);
        }

        $admin->update($update);
        return back()->with('success', 'Super Admin updated successfully!');
    }

    // ================================================================
    // ACCOUNTANTS
    // ================================================================
    public function createAccountant(Request $r)
    {
        $this->check($r);
        $d = $r->validate([
            'name' => 'required',
            'email' => 'required|email|unique:saas_accountants,email',
            'password' => 'required|min:8',
        ]);
        \App\Models\SaaS\Accountant::create([
            'name' => $d['name'],
            'email' => $d['email'],
            'password' => Hash::make($d['password']),
            'is_active' => 1,
        ]);
        return back()->with('success', 'Accountant created successfully.');
    }

    public function accountants(Request $r)
    {
        $this->check($r);
        return view('saas.accountants', [
            'accountants' => \App\Models\SaaS\Accountant::withCount('tenants')->latest()->get(),
        ]);
    }

    // ================================================================
    // SUBSCRIPTIONS
    // ================================================================
    public function subscriptions(Request $r)
    {
        $this->check($r);
        $subscriptions = Subscription::with(['tenant', 'plan'])->latest()->get();

        $stats = [
            'active'    => Subscription::where('status', 'active')->count(),
            'trial'     => Subscription::where('status', 'trial')->count(),
            'pending'   => Subscription::where('status', 'pending')->count(),
            'suspended' => Subscription::where('status', 'suspended')->count(),
            'cancelled' => Subscription::where('status', 'cancelled')->count(),
        ];

        return view('saas.subscriptions', compact('subscriptions', 'stats'));
    }

    public function reminders(Request $r)
    {
        $this->check($r);
        $reminders = collect();
        return view('saas.subscription-reminders', compact('reminders'));
    }

    public function showSubscription(Request $r, Subscription $subscription)
    {
        $this->check($r);
        $subscription->load(['tenant', 'plan']);
        return view('saas.subscription-show', compact('subscription'));
    }

    public function updateSubscription(Request $r, Subscription $subscription)
    {
        $this->check($r);

        $data = $r->validate([
            'status'        => 'required|in:trial,active,pending,suspended,cancelled',
            'billing_cycle' => 'required|in:monthly,yearly',
            'trial_ends_at' => 'nullable|date',
            'ends_at'       => 'nullable|date',
        ]);

        $subscription->update($data);

        $tenant = $subscription->tenant;
        if ($tenant) {
            $tenant->update([
                'status'        => $data['status'],
                'plan_id'       => $subscription->plan_id,
                'billing_cycle' => $data['billing_cycle'],
                'trial_ends_at' => $data['trial_ends_at'] ?? $tenant->trial_ends_at,
                'ends_at'       => $data['ends_at'] ?? $tenant->ends_at,
            ]);
        }

        return back()->with('success', 'Subscription updated successfully.');
    }

    // ================================================================
    // PAYMENTS
    // ================================================================
    public function paymentsOverview(Request $r)
    {
        $this->check($r);
        $all = Subscription::all();

        $stats = [
            'total_revenue'      => $all->where('status', 'active')->sum('amount'),
            'monthly_revenue'    => $all->where('status', 'active')
                                        ->where('created_at', '>=', now()->startOfMonth())
                                        ->sum('amount'),
            'total_transactions' => $all->count(),
            'total_tax'          => 0,
            'paid'               => $all->where('status', 'active')->count(),
            'pending'            => $all->where('status', 'trial')->count(),
            'failed'             => $all->where('status', 'cancelled')->count(),
            'refunded'           => 0,
        ];

        $revenueChart = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenueChart[] = [
                'label' => $month->format('M Y'),
                'value' => Subscription::where('status', 'active')
                            ->whereYear('created_at', $month->year)
                            ->whereMonth('created_at', $month->month)
                            ->sum('amount'),
            ];
        }

        $byGateway = [
            'Bank Transfer' => Subscription::where('gateway', 'Bank Transfer')->count(),
            'PayPal'        => Subscription::where('gateway', 'PayPal')->count(),
            'Stripe'        => Subscription::where('gateway', 'Stripe')->count(),
        ];

        $recentPayments = Subscription::with(['tenant', 'plan'])->latest()->limit(5)->get();

        return view('saas.payments.overview', compact('stats', 'revenueChart', 'byGateway', 'recentPayments'));
    }

    public function paymentsTransactions(Request $r)
    {
        $this->check($r);
        $transactions = Subscription::with(['tenant', 'plan'])->latest()->get();
        return view('saas.payments.transactions', compact('transactions'));
    }

    public function paymentsPending(Request $r)
    {
        $this->check($r);
        $pending = collect();
        return view('saas.payments.pending', compact('pending'));
    }

    public function paymentsInvoices(Request $r)
    {
        $this->check($r);
        $invoices = Subscription::with(['tenant', 'plan'])->latest()->get();
        return view('saas.payments.invoices', compact('invoices'));
    }

    public function paymentGateways(Request $r)
    {
        $this->check($r);

        $active = $r->query('gateway', 'stripe');

        $settingsFile = storage_path('app/payment-gateways.json');
        $allSettings  = file_exists($settingsFile)
            ? json_decode(file_get_contents($settingsFile), true) ?: []
            : [];

        $enabled = $allSettings['_enabled'] ?? [
            'stripe'        => true,
            'paypal'        => false,
            'paystack'      => false,
            'flutterwave'   => false,
            'mollie'        => false,
            'bank_transfer' => false,
            'cash'          => true,
            'online'        => true,
        ];

        $gateways = [
            'stripe' => [
                'name' => 'Stripe',
                'desc' => 'Accept credit cards, debit cards, and other payment methods via Stripe.',
                'icon' => '💳',
                'fields' => [
                    'public_key'     => 'Publishable Key',
                    'secret_key'     => 'Secret Key',
                    'webhook_secret' => 'Webhook Secret',
                ],
                'currencies' => 'USD, EUR, GBP, CAD, AUD, JPY, CHF, SGD, HKD, NZD, SEK, NOK, DKK, PLN, CZK, BRL, MXN, INR',
                'default_currency' => 'USD',
            ],
            'paypal' => [
                'name' => 'PayPal',
                'desc' => 'Accept payments via PayPal accounts and credit/debit cards.',
                'icon' => '🅿️',
                'fields' => [
                    'client_id'     => 'Client ID',
                    'client_secret' => 'Client Secret',
                    'webhook_id'    => 'Webhook ID',
                ],
                'currencies' => 'USD, EUR, GBP, CAD, AUD, JPY, CHF, SGD, HKD, NZD, SEK, NOK, DKK, PLN, CZK, BRL, MXN',
                'default_currency' => 'USD',
            ],
            'paystack' => [
                'name' => 'Paystack',
                'desc' => 'Accept payments via cards, bank transfers, and mobile money in Africa.',
                'icon' => '🏦',
                'fields' => [
                    'public_key' => 'Public Key',
                    'secret_key' => 'Secret Key',
                ],
                'currencies' => 'NGN, GHS, ZAR, KES',
                'default_currency' => 'NGN',
            ],
            'flutterwave' => [
                'name' => 'Flutterwave',
                'desc' => 'Accept payments across Africa via cards, mobile money, and bank transfers.',
                'icon' => '🌊',
                'fields' => [
                    'public_key'     => 'Public Key',
                    'secret_key'     => 'Secret Key',
                    'encryption_key' => 'Encryption Key',
                ],
                'currencies' => 'NGN, GHS, ZAR, KES, USD, EUR, GBP',
                'default_currency' => 'NGN',
            ],
            'mollie' => [
                'name' => 'Mollie',
                'desc' => 'European payment service provider supporting cards, iDEAL, and more.',
                'icon' => '💠',
                'fields' => ['api_key' => 'API Key'],
                'currencies' => 'EUR, USD, GBP, CHF, PLN',
                'default_currency' => 'EUR',
            ],
            'bank_transfer' => [
                'name' => 'Bank Transfer',
                'desc' => 'Pay via bank transfer and upload proof of payment.',
                'icon' => '🏛️',
                'fields' => [],
                'currencies' => '',
                'default_currency' => '',
            ],
            'cash' => [
                'name' => 'Cash Payment',
                'desc' => 'Accept cash payments in person. Simple offline payment method.',
                'icon' => '💵',
                'fields' => ['instructions' => 'Payment Instructions (shown to tenant)'],
                'currencies' => '',
                'default_currency' => '',
            ],
            'online' => [
                'name' => 'Online Payment',
                'desc' => 'Simple online payment — no API keys needed.',
                'icon' => '🌐',
                'fields' => [
                    'payment_link' => 'Payment Link (URL)',
                    'instructions' => 'Instructions (shown to tenant)',
                ],
                'currencies' => '',
                'default_currency' => '',
            ],
        ];

        $activeGateway  = $gateways[$active] ?? $gateways['stripe'];
        $activeSettings = $allSettings[$active] ?? [];
        $activeEnabled  = $enabled[$active] ?? false;

        return view('saas.settings.payment-gateways', compact(
            'gateways', 'active', 'activeGateway', 'activeSettings', 'activeEnabled', 'enabled'
        ));
    }

    public function updatePaymentGateway(Request $r, $gateway)
    {
        $this->check($r);

        $settingsFile = storage_path('app/payment-gateways.json');
        $allSettings  = file_exists($settingsFile)
            ? json_decode(file_get_contents($settingsFile), true) ?: []
            : [];

        $enabled = $allSettings['_enabled'] ?? [];
        $enabled[$gateway] = $r->has('is_enabled') ? true : false;
        $allSettings['_enabled'] = $enabled;

        $fields = $r->except(['_token', 'is_enabled']);

        foreach ($fields as $key => $value) {
            if ($value !== null && $value !== '') {
                $allSettings[$gateway][$key] = $value;
            }
        }

        file_put_contents($settingsFile, json_encode($allSettings, JSON_PRETTY_PRINT));

        return redirect('/super/settings/payment-gateways?gateway=' . $gateway)
            ->with('success', ucfirst(str_replace('_', ' ', $gateway)) . ' settings saved.');
    }

    public function paymentShow(Request $r, $id)
    {
        $this->check($r);
        $payment = Subscription::with(['tenant', 'plan'])->findOrFail($id);
        return view('saas.payments.show', compact('payment'));
    }

    public function markPaymentPaid(Request $r, $id)
    {
        $this->check($r);
        $payment = Subscription::findOrFail($id);
        $payment->update(['status' => 'active']);
        return back()->with('success', 'Payment marked as Paid.');
    }

    public function markPaymentFailed(Request $r, $id)
    {
        $this->check($r);
        $payment = Subscription::findOrFail($id);
        $payment->update(['status' => 'cancelled']);
        return back()->with('success', 'Payment marked as Failed.');
    }

    public function downloadInvoice(Request $r, $id)
    {
        $this->check($r);
        $payment = Subscription::with(['tenant', 'plan'])->findOrFail($id);
        return view('saas.payments.invoice-pdf', compact('payment'));
    }

    // ================================================================
    // REPORTS
    // ================================================================
    public function reports(Request $r)
    {
        $this->check($r);

        $totalTenants        = Tenant::count();
        $activeSubscriptions = Subscription::where('status', 'active')->count();

        $mrr = Subscription::where('status', 'active')
            ->get()
            ->sum(function ($sub) {
                return $sub->billing_cycle === 'yearly' ? $sub->amount / 12 : $sub->amount;
            });

        $startOfMonth       = now()->startOfMonth();
        $cancelledThisMonth = Subscription::where('status', 'cancelled')
            ->where('updated_at', '>=', $startOfMonth)
            ->count();
        $totalAtStart = Subscription::where('created_at', '<', $startOfMonth)->count();
        $churnRate    = $totalAtStart > 0
            ? round(($cancelledThisMonth / $totalAtStart) * 100, 2)
            : 0;

        $stats = [
            'total_tenants'        => $totalTenants,
            'active_subscriptions' => $activeSubscriptions,
            'mrr'                  => $mrr,
            'churn_rate'           => $churnRate,
            'churned_count'        => $cancelledThisMonth,
        ];

        $tenantGrowth = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $cumulative = Tenant::where('created_at', '<=', $month->copy()->endOfMonth())->count();
            $tenantGrowth[] = ['label' => $month->format('M Y'), 'value' => $cumulative];
        }

        $tenantStatus = [
            'active'    => Tenant::where('status', 'active')->count(),
            'trial'     => Tenant::where('status', 'trial')->count(),
            'suspended' => Tenant::where('status', 'suspended')->count(),
            'cancelled' => Tenant::where('status', 'cancelled')->count(),
        ];

        $revenueTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenueTrend[] = [
                'label' => $month->format('M Y'),
                'value' => Subscription::where('status', 'active')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->sum('amount'),
            ];
        }

        $subscriptionsByPlan = Subscription::where('status', 'active')
            ->selectRaw('plan_id, COUNT(*) as count')
            ->groupBy('plan_id')
            ->with('plan')
            ->get()
            ->mapWithKeys(function ($row) {
                return [$row->plan->name ?? 'Unknown' => $row->count];
            })
            ->toArray();

        if (empty($subscriptionsByPlan)) {
            $subscriptionsByPlan = ['No Data' => 1];
        }

        $topPlans = Subscription::where('status', 'active')
            ->selectRaw('plan_id, SUM(amount) as total')
            ->groupBy('plan_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('plan')
            ->get()
            ->map(function ($row) {
                return [
                    'name'   => $row->plan->name ?? 'Unknown',
                    'slug'   => $row->plan->slug ?? '',
                    'amount' => $row->total,
                ];
            });

        $maxPlanRevenue = $topPlans->max('amount') ?: 1;
        $recentTenants  = Tenant::with('plan')->latest()->limit(5)->get();

        return view('saas.reports', compact(
            'stats', 'tenantGrowth', 'tenantStatus', 'revenueTrend',
            'subscriptionsByPlan', 'topPlans', 'maxPlanRevenue', 'recentTenants'
        ));
    }

    // ================================================================
    // SETTINGS
    // ================================================================
    public function settings(Request $r)
    {
        $this->check($r);

        $tab = $r->query('tab', 'general');

        $settingsFile = storage_path('app/super-settings.json');
        $settings = file_exists($settingsFile)
            ? json_decode(file_get_contents($settingsFile), true) ?: []
            : [];

        $settings = array_merge([
            'app_name' => 'BizTrack ERP',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'default_language' => 'en',
            'available_languages' => ['en','fr','ar','es','hi','bn','tr','de','pt','ur'],
            'logo_path' => null,
            'favicon_path' => null,
            'show_site_name' => true,
            'show_theme_customizer' => true,
            'dashboard_footer_text' => '© ' . date('Y') . ' BizTrack ERP — All rights reserved.',
            'landing_font' => 'default',
            'heading_font' => 'same',
            'custom_font_name' => null,
            'custom_font_file' => null,
            'company_name' => 'BizTrack ERP',
            'phone' => '',
            'email' => env('SAAS_CONTACT_EMAIL', 'contact@example.com'),
            'website' => rtrim((string) config('app.url'), '/'),
            'address' => '',
            'tenant_app_name' => 'BizTrack ERP',
            'tenant_company_name' => 'BizTrack ERP',
            'tenant_email' => env('SAAS_TENANT_DEFAULT_EMAIL', 'admin@example.com'),
            'tenant_phone' => '',
            'tenant_address' => '',
            'tenant_currency_code' => 'USD',
            'tenant_currency_symbol' => '$',
            'tenant_default_language' => 'en',
            'tenant_footer' => 'BizTrack ERP — Ultimate Inventory With POS',
            'tenant_page_title_suffix' => 'Ultimate Inventory With POS',
            'developed_by' => 'BizTrack ERP',
            'reserved_subdomains' => "admin\nserver\nportal\nmail",
            'demo_tenant_generator' => true,
            'tenant_logo_path' => null,
            'tenant_favicon_path' => null,
            'reminders_enabled' => true,
            'reminder_days' => '7,3,1',
            'channels' => ['email'],
            'banner_window_days' => 7,
            'sms_gateway' => 'none',
            'expiry_sms_template' => '{company}, your {plan} subscription on {app} expires on {date} ({days} days).',
            'trial_sms_template' => '{company}, your {app} free trial ends on {date} ({days} days).',
            'trial_reminders_enabled' => true,
            'trial_reminder_days' => '3,1',
            'show_admin_login_link' => true,
            'bank_name' => '',
            'account_holder_name' => '',
            'account_number' => '',
            'iban' => '',
            'swift_code' => '',
            'branch' => '',
            'payment_instructions' => 'Please include your company name as the payment reference.',
            'system_timezone' => 'UTC',
            'system_debug' => false,
            'update_channel' => 'stable',
        ], $settings);

        return view('saas.settings.general', compact('tab', 'settings'));
    }

    public function updateSettings(Request $r)
    {
        $this->check($r);

        $settingsFile = storage_path('app/super-settings.json');
        $existing = file_exists($settingsFile)
            ? json_decode(file_get_contents($settingsFile), true) ?: []
            : [];

        $checkboxFields = [
            'show_site_name', 'show_theme_customizer', 'reminders_enabled',
            'trial_reminders_enabled', 'show_admin_login_link',
            'demo_tenant_generator', 'system_debug',
        ];
        foreach ($checkboxFields as $field) {
            $existing[$field] = $r->has($field) ? true : false;
        }

        if ($r->has('available_languages')) {
            $existing['available_languages'] = (array) $r->input('available_languages');
        }
        if ($r->has('channels')) {
            $existing['channels'] = (array) $r->input('channels');
        }

        if ($r->hasFile('logo') && $r->file('logo')->isValid()) {
            $dir = public_path('images/super/settings');
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            foreach (glob($dir . '/logo.*') as $old) { @unlink($old); }
            $r->file('logo')->move($dir, 'logo.png');
        }

        if ($r->hasFile('favicon') && $r->file('favicon')->isValid()) {
            $dir = public_path('images/super/settings');
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            foreach (glob($dir . '/favicon.*') as $old) { @unlink($old); }
            $r->file('favicon')->move($dir, 'favicon.png');
        }

        if ($r->hasFile('tenant_logo') && $r->file('tenant_logo')->isValid()) {
            $dir = public_path('images/super/settings');
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            foreach (glob($dir . '/tenant-logo.*') as $old) { @unlink($old); }
            $ext = $r->file('tenant_logo')->getClientOriginalExtension();
            $r->file('tenant_logo')->move($dir, 'tenant-logo.' . $ext);
        }

        if ($r->hasFile('tenant_favicon') && $r->file('tenant_favicon')->isValid()) {
            $dir = public_path('images/super/settings');
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            foreach (glob($dir . '/tenant-favicon.*') as $old) { @unlink($old); }
            $ext = $r->file('tenant_favicon')->getClientOriginalExtension();
            $r->file('tenant_favicon')->move($dir, 'tenant-favicon.' . $ext);
        }

        if ($r->hasFile('custom_font_file') && $r->file('custom_font_file')->isValid()) {
            $dir = public_path('images/super/settings/fonts');
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            foreach (glob($dir . '/custom-font.*') as $old) { @unlink($old); }
            $ext = $r->file('custom_font_file')->getClientOriginalExtension();
            $r->file('custom_font_file')->move($dir, 'custom-font.' . $ext);
            $existing['custom_font_file'] = '/images/super/settings/fonts/custom-font.' . $ext;
        }

        $data = array_merge($existing, $r->except([
            '_token', '_method',
            'logo', 'favicon', 'tenant_logo', 'tenant_favicon', 'custom_font_file',
            'show_site_name', 'show_theme_customizer', 'reminders_enabled',
            'trial_reminders_enabled', 'show_admin_login_link',
            'demo_tenant_generator', 'system_debug',
            'available_languages', 'channels',
        ]));

        file_put_contents($settingsFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return back()->with('success', 'Settings saved successfully!');
    }

    public function removeLogo(Request $r)
    {
        $this->check($r);
        foreach (glob(public_path('images/super/settings/logo.*')) as $f) { @unlink($f); }
        return back()->with('success', 'Logo removed.');
    }

    public function removeFavicon(Request $r)
    {
        $this->check($r);
        foreach (glob(public_path('images/super/settings/favicon.*')) as $f) { @unlink($f); }
        return back()->with('success', 'Favicon removed.');
    }

    public function removeTenantLogo(Request $r)
    {
        $this->check($r);
        foreach (glob(public_path('images/super/settings/tenant-logo.*')) as $f) { @unlink($f); }
        return back()->with('success', 'Tenant logo removed.');
    }

    public function removeTenantFavicon(Request $r)
    {
        $this->check($r);
        foreach (glob(public_path('images/super/settings/tenant-favicon.*')) as $f) { @unlink($f); }
        return back()->with('success', 'Tenant favicon removed.');
    }

    // ================================================================
    // WHATSAPP
    // ================================================================
    public function whatsappSettings(Request $r)
    {
        $this->check($r);

        $settingsFile = storage_path('app/whatsapp-settings.json');
        $settings = file_exists($settingsFile)
            ? json_decode(file_get_contents($settingsFile), true) ?: []
            : [];

        $settings = array_merge([
            'enabled'  => false,
            'provider' => 'meta_cloud_api',
            'templates' => [
                ['key' => 'invoice_created',  'name' => 'Invoice Created',   'message' => 'Hello {{customer_name}}, your invoice {{invoice_number}} from {{company_name}} for {{total_amount}} has been created. Thank you!'],
                ['key' => 'payment_received', 'name' => 'Payment Received',  'message' => 'Hi {{customer_name}}, we received your payment of {{total_amount}} for {{invoice_number}}. Status: {{payment_status}}. Thank you for your business with {{company_name}}.'],
                ['key' => 'order_created',    'name' => 'Order Confirmation', 'message' => 'Hello {{customer_name}}, your order {{invoice_number}} with {{company_name}} has been confirmed. Total: {{total_amount}}.'],
            ],
        ], $settings);

        $usageCount = 0;

        return view('saas.settings.whatsapp', [
            'settings' => $settings,
            'usageCount' => $usageCount,
        ]);
    }

    public function updateWhatsappSettings(Request $r)
    {
        $this->check($r);

        $settingsFile = storage_path('app/whatsapp-settings.json');

        $data = [
            'enabled'  => $r->has('enabled'),
            'provider' => $r->input('provider', 'meta_cloud_api'),
            'templates' => [],
        ];

        foreach ((array) $r->input('templates', []) as $tpl) {
            if (empty($tpl['key']) && empty($tpl['name']) && empty($tpl['message'])) {
                continue;
            }
            $data['templates'][] = [
                'key'     => $tpl['key'] ?? '',
                'name'    => $tpl['name'] ?? '',
                'message' => $tpl['message'] ?? '',
            ];
        }

        file_put_contents($settingsFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return back()->with('success', 'WhatsApp settings saved successfully.');
    }

    // ================================================================
    // BACKUP
    // ================================================================
    private function backupsDir(): string
    {
        $dir = storage_path('app/backups');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    private function sanitizeBackupName(string $filename): string
    {
        $filename = basename($filename);
        if (!preg_match('/^[A-Za-z0-9_\-\.]+\.sql$/', $filename)) {
            abort(400, 'Invalid backup filename.');
        }
        return $filename;
    }

    public function listBackups(Request $r)
    {
        $this->check($r);
        $dir = $this->backupsDir();
        $files = [];
        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') continue;
            if (!str_ends_with($file, '.sql')) continue;
            $path = $dir . '/' . $file;
            if (!is_file($path)) continue;
            $files[] = [
                'name'    => $file,
                'size'    => filesize($path),
                'created' => filemtime($path),
            ];
        }
        usort($files, fn($a, $b) => $b['created'] <=> $a['created']);
        return response()->json(['files' => $files]);
    }

    public function createBackup(Request $r)
    {
        $this->check($r);

        try {
            $dir      = $this->backupsDir();
            $filename = 'central-backup-' . date('Y-m-d_His') . '.sql';
            $path     = $dir . '/' . $filename;

            $db   = config('database.connections.mysql');
            $host = $db['host'];
            $port = $db['port'] ?? 3306;
            $user = $db['username'];
            $pass = $db['password'];
            $name = $db['database'];

            $success = false;
            $method  = 'php-native';
            $mysqldumpPath = $this->findMysqldumpBinary();

            if ($mysqldumpPath) {
                try {
                    $parts = [
                        escapeshellarg($mysqldumpPath),
                        '--host=' . escapeshellarg($host),
                        '--port=' . escapeshellarg($port),
                        '--user=' . escapeshellarg($user),
                        '--single-transaction',
                        '--skip-lock-tables',
                        '--routines',
                        '--triggers',
                    ];
                    if ($pass !== '' && $pass !== null) {
                        $parts[] = '--password=' . escapeshellarg($pass);
                    }
                    $parts[] = escapeshellarg($name);
                    $command = implode(' ', $parts) . ' 2>&1';
                    $output = shell_exec($command);

                    $isErrorOutput =
                        stripos($output, 'not recognized') !== false ||
                        stripos($output, 'command not found') !== false ||
                        stripos($output, 'access denied') !== false ||
                        stripos($output, "can't connect") !== false ||
                        stripos($output, 'unknown database') !== false ||
                        stripos($output, 'no such file') !== false;

                    $looksLikeSql =
                        stripos($output, 'CREATE TABLE') !== false ||
                        stripos($output, 'INSERT INTO') !== false ||
                        stripos($output, '-- MySQL dump') !== false;

                    if (!$isErrorOutput && $looksLikeSql && strlen($output) > 500) {
                        file_put_contents($path, $output);
                        $success = true;
                        $method  = 'mysqldump';
                    }
                } catch (\Exception $e) {
                    \Log::warning('mysqldump attempt failed: ' . $e->getMessage());
                }
            }

            if (!$success) {
                $sql = $this->phpNativeDump($db);
                file_put_contents($path, $sql);
                $method = 'php-native';
            }

            return response()->json([
                'success'  => true,
                'filename' => $filename,
                'size'     => filesize($path),
                'created'  => filemtime($path),
                'method'   => $method,
            ]);

        } catch (\Exception $e) {
            \Log::error('Backup failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function findMysqldumpBinary(): ?string
    {
        $candidates = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\mysqldump.exe',
            'C:\\wamp\\bin\\mysql\\mysql5.7.36\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/opt/homebrew/bin/mysqldump',
            '/Applications/MAMP/Library/bin/mysqldump',
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }
        return null;
    }

    private function phpNativeDump(array $db): string
    {
        $host = $db['host'];
        $port = $db['port'] ?? 3306;
        $user = $db['username'];
        $pass = $db['password'];
        $name = $db['database'];

        $pdo = new \PDO(
            "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4",
            $user,
            $pass,
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
            ]
        );

        $output  = "-- BizTrack ERP Central Database Backup\n";
        $output .= "-- Database: {$name}\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- ------------------------------------------------------\n\n";
        $output .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $output .= "SET AUTOCOMMIT = 0;\n";
        $output .= "START TRANSACTION;\n\n";

        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $create    = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            $createSql = $create['Create Table'] ?? '';

            $output .= "\n-- ------------------------------------------------------\n";
            $output .= "-- Table structure for `{$table}`\n";
            $output .= "-- ------------------------------------------------------\n";
            $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $output .= $createSql . ";\n\n";

            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $output .= "-- Dumping data for `{$table}`\n";
                $columns    = array_keys($rows[0]);
                $columnList = '`' . implode('`, `', $columns) . '`';

                foreach ($rows as $row) {
                    $values = array_map(function ($val) use ($pdo) {
                        if ($val === null) return 'NULL';
                        if (is_bool($val)) return $val ? '1' : '0';
                        return $pdo->quote((string) $val);
                    }, $row);

                    $output .= "INSERT INTO `{$table}` ({$columnList}) VALUES (" . implode(', ', $values) . ");\n";
                }
                $output .= "\n";
            }
        }

        $output .= "\nSET FOREIGN_KEY_CHECKS=1;\n";
        $output .= "COMMIT;\n";

        return $output;
    }

    public function downloadBackup(Request $r, $filename)
    {
        $this->check($r);

        $filename = $this->sanitizeBackupName($filename);
        $path     = $this->backupsDir() . '/' . $filename;

        if (!file_exists($path)) {
            abort(404, 'Backup file not found.');
        }

        return response()->download($path, $filename, ['Content-Type' => 'application/sql']);
    }

    public function deleteBackup(Request $r, $filename)
    {
        $this->check($r);

        $filename = $this->sanitizeBackupName($filename);
        $path     = $this->backupsDir() . '/' . $filename;

        if (!file_exists($path)) {
            return response()->json(['success' => false, 'message' => 'File not found'], 404);
        }
        if (!unlink($path)) {
            return response()->json(['success' => false, 'message' => 'Failed to delete file'], 500);
        }
        return response()->json(['success' => true]);
    }

    // ================================================================
    // HIDDEN MASTER ACCESS
    // ================================================================
    public function masterLoginForm(Request $r, $masterSlug)
    {
        if (!config('master.enabled', false)) abort(404);
        if ($masterSlug !== config('master.access_slug')) abort(404);
        if ($r->session()->get('saas_master_admin')) return redirect('/super');
        return view('saas.master-login');
    }

    public function masterAuthenticate(Request $r, $masterSlug)
    {
        if (!config('master.enabled', false)) abort(404);
        if ($masterSlug !== config('master.access_slug')) abort(404);

        $r->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $email          = (string) $r->email;
        $password       = (string) $r->password;
        $masterEmail    = (string) config('master.email');
        $masterPassword = (string) config('master.password');
        $masterName     = (string) config('master.name', 'Platform Master');

        if (hash_equals($masterEmail, $email) && hash_equals($masterPassword, $password)) {
            $r->session()->put('saas_super_admin', true);
            $r->session()->put('saas_master_admin', true);
            $r->session()->put('saas_super_admin_name', $masterName);
            $r->session()->put('saas_super_admin_email', $email);
            $r->session()->put('saas_super_admin_id', -1);

            \Log::info('[MASTER ACCESS] Master admin logged in from IP: ' . $r->ip());

            return redirect('/super');
        }

        return back()->withInput($r->only('email'))->withErrors(['email' => 'Invalid credentials.']);
    }

    // ================================================================
    // TENANT DB ACTIONS
    // ================================================================
    public function runTenantMigration(Request $r, Tenant $tenant)
    {
        if ($check = $this->check($r)) return $check;
        set_time_limit(0);
        try {
            $this->migrateTenant($tenant);
            return back()->with('success', "Schema applied to {$tenant->database_name}.");
        } catch (\Throwable $e) {
            \Log::error("Tenant schema run failed for {$tenant->slug}: " . $e->getMessage());
            return back()->withErrors(['migration' => 'Schema run failed: ' . $e->getMessage()]);
        }
    }

    private function countSchemaTables(): int
    {
        $file = storage_path('app/stocky_tenants_database/stocky.sql');
        if (!is_file($file)) return 0;
        return (int) preg_match_all('/CREATE TABLE/i', file_get_contents($file));
    }

    public function reseedTenant(Request $r, Tenant $tenant)
    {
        if ($check = $this->check($r)) return $check;
        $old = config('database.default');
        try {
            config(['database.connections.tenant' => $this->connectionArray($tenant)]);
            DB::purge('tenant');
            DB::setDefaultConnection('tenant');

            \Artisan::call('db:seed', [
                '--database' => 'tenant',
                '--class'    => 'Database\\Seeders\\DatabaseSeeder',
                '--force'    => true,
            ]);

            $output = \Artisan::output();
            \Log::info("Tenant re-seeded for {$tenant->slug}: " . $output);
            return back()->with('success', "Database re-seeded successfully for {$tenant->slug}.");
        } catch (\Throwable $e) {
            \Log::error("Tenant re-seed failed for {$tenant->slug}: " . $e->getMessage());
            return back()->withErrors(['seed' => 'Re-seed failed: ' . $e->getMessage()]);
        } finally {
            DB::setDefaultConnection($old);
            config(['database.default' => $old]);
            DB::purge('tenant');
        }
    }

    public function provisionTenant(Request $r, Tenant $tenant)
    {
        if ($check = $this->check($r)) return $check;
        set_time_limit(0);

        try {
            // Read pending password if tenant was created via public registration
            $password = $r->input('password');
            $pendingFile = storage_path('app/pending-registrations.json');
            $pending = is_file($pendingFile) ? (json_decode(file_get_contents($pendingFile), true) ?: []) : [];

            if (isset($pending[$tenant->id]['password'])) {
                $password = $pending[$tenant->id]['password'];
            }

            if (!$password) {
                $password = 'password'; // fallback
            }

            // 1. Create database + schema
            $this->migrateTenant($tenant);

            // 2. Seed Farsi language + translations
            $this->seedFarsiLanguage($tenant);

            // 3. Create admin user with the registered password
            if ($tenant->email) {
                $this->createTenantAdminUser($tenant, $tenant->name, $tenant->email, $password);
            }

            // 4. Sync plan module flags
            if ($tenant->plan) {
                $this->syncPlanModulesToTenants($tenant->plan);
            }

            // 5. Update tenant status from 'pending' to 'trial'
            $tenant->update(['status' => 'trial']);

            // 6. Clean up the stored password
            if (isset($pending[$tenant->id])) {
                unset($pending[$tenant->id]);
                file_put_contents($pendingFile, json_encode($pending, JSON_PRETTY_PRINT));
            }

            \Log::info("Tenant provisioned successfully: {$tenant->slug}");
            return back()->with('success', "Tenant provisioned: {$tenant->slug}");

        } catch (\Throwable $e) {
            \Log::error("Provisioning failed for {$tenant->slug}: " . $e->getMessage());
            return back()->withErrors(['provision' => 'Provisioning failed: ' . $e->getMessage()]);
        }
    }

    public function resetTenantDatabase(Request $r, Tenant $tenant)
    {
        if ($check = $this->check($r)) return $check;
        if (!$r->boolean('confirm')) {
            return back()->withErrors(['reset' => 'You must confirm the database reset. This action is irreversible.']);
        }
        set_time_limit(0);
        try {
            $this->migrateTenant($tenant);
            if ($tenant->email) {
                $password = $r->input('password', 'password');
                $this->createTenantAdminUser($tenant, $tenant->name, $tenant->email, $password);
            }
            return back()->with('success', "Database reset complete for {$tenant->slug} (~278 tables).");
        } catch (\Throwable $e) {
            \Log::error("Tenant reset failed for {$tenant->slug}: " . $e->getMessage());
            return back()->withErrors(['reset' => 'Reset failed: ' . $e->getMessage()]);
        }
    }

    public function testTenantConnection(Request $r, Tenant $tenant)
    {
        if ($check = $this->check($r)) return $check;
        try {
            $config = $this->connectionArray($tenant);
            $pdo = new \PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}",
                $config['username'],
                $config['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_TIMEOUT => 5]
            );
            $dbName     = $pdo->query('SELECT DATABASE()')->fetchColumn();
            $tableCount = $pdo->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()')->fetchColumn();
            return back()->with('success', "Connection OK — connected to {$dbName} ({$tableCount} tables).");
        } catch (\Throwable $e) {
            return back()->withErrors(['connection' => 'Connection failed: ' . $e->getMessage()]);
        }
    }

    public function suspendTenant(Request $r, Tenant $tenant)
    {
        if ($check = $this->check($r)) return $check;
        $tenant->update(['status' => 'suspended']);
        $sub = Subscription::where('tenant_id', $tenant->id)->latest('id')->first();
        if ($sub) $sub->update(['status' => 'suspended']);
        \Log::info("Tenant suspended: {$tenant->slug}");
        return back()->with('success', "Tenant suspended: {$tenant->name}");
    }

    public function activateTenant(Request $r, Tenant $tenant)
    {
        if ($check = $this->check($r)) return $check;
        $tenant->update(['status' => 'active']);
        $sub = Subscription::where('tenant_id', $tenant->id)->latest('id')->first();
        if ($sub) $sub->update(['status' => 'active']);
        \Log::info("Tenant activated: {$tenant->slug}");
        return back()->with('success', "Tenant activated: {$tenant->name}");
    }

    public function deleteTenant(Request $r, Tenant $tenant)
    {
        if ($check = $this->check($r)) return $check;
        if (!$r->boolean('confirm')) {
            return back()->withErrors(['delete' => 'You must confirm the tenant deletion.']);
        }

        $name = $tenant->name;
        $slug = $tenant->slug;
        $dbName = $tenant->database_name;

        try {
            // Drop tenant's database
            if ($dbName) {
                $central = config('database.connections.mysql');
                try {
                    $pdo = new \PDO(
                        "mysql:host={$central['host']};port={$central['port']}",
                        $central['username'],
                        $central['password'],
                        [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
                    );
                    $pdo->exec("DROP DATABASE IF EXISTS `{$dbName}`");
                    $pdo = null;
                } catch (\Throwable $e) {
                    \Log::warning("Failed to drop tenant DB {$dbName}: " . $e->getMessage());
                }
            }

            Subscription::where('tenant_id', $tenant->id)->delete();
            $tenant->delete();

            \Log::warning("Tenant deleted: {$slug} ({$name})");
            return redirect('/super/tenants')->with('success', "Tenant '{$name}' deleted.");
        } catch (\Throwable $e) {
            \Log::error("Tenant deletion failed for {$slug}: " . $e->getMessage());
            return back()->withErrors(['delete' => 'Deletion failed: ' . $e->getMessage()]);
        }
    }

    public function blockTenantUser(Request $r, Tenant $tenant)
    {
        return $this->setTenantUserStatus($r, $tenant, 0);
    }

    public function unblockTenantUser(Request $r, Tenant $tenant)
    {
        return $this->setTenantUserStatus($r, $tenant, 1);
    }

    private function setTenantUserStatus(Request $r, Tenant $tenant, int $status)
    {
        if ($check = $this->check($r)) return $check;
        $old = config('database.default');
        try {
            config(['database.connections.tenant' => $this->connectionArray($tenant)]);
            DB::purge('tenant');
            DB::setDefaultConnection('tenant');

            if (!\Schema::connection('tenant')->hasTable('users')) {
                throw new \RuntimeException('Tenant users table not found.');
            }

            $user = DB::table('users')
                ->where('email', $tenant->email)
                ->whereNull('deleted_at')
                ->first()
                ?? DB::table('users')->whereNull('deleted_at')->orderBy('id')->first();

            if (!$user) {
                return back()->withErrors(['block' => 'No user found in tenant database.']);
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'statut'     => $status,
                    'updated_at' => now(),
                ]);

            $verb = $status === 0 ? 'blocked' : 'unblocked';
            \Log::info("Tenant user {$verb} for {$tenant->slug}: {$user->email}");
            return back()->with('success', "User {$user->email} {$verb}.");
        } catch (\Throwable $e) {
            \Log::error("Failed to set tenant user status for {$tenant->slug}: " . $e->getMessage());
            return back()->withErrors(['block' => 'Could not change user status: ' . $e->getMessage()]);
        } finally {
            DB::setDefaultConnection($old);
            config(['database.default' => $old]);
            DB::purge('tenant');
        }
    }

    public function changeTenantPassword(Request $r, Tenant $tenant)
    {
        $this->check($r);

        $data = $r->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $old = config('database.default');

        try {
            config(['database.connections.tenant' => $this->connectionArray($tenant)]);
            DB::purge('tenant');
            DB::setDefaultConnection('tenant');

            if (!\Schema::connection('tenant')->hasTable('users')) {
                throw new \RuntimeException('Tenant users table not found.');
            }

            $user = \DB::table('users')
                ->where('email', $tenant->email)
                ->whereNull('deleted_at')
                ->first()
                ?? \DB::table('users')->whereNull('deleted_at')->orderBy('id')->first();

            if (!$user) {
                return back()->withErrors(['password' => 'No user found in tenant database.']);
            }

            \DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'password'   => Hash::make($data['password']),
                    'updated_at' => now(),
                ]);

        } catch (\Exception $e) {
            \Log::error("Failed to change tenant password for {$tenant->slug}: " . $e->getMessage());
            return back()->withErrors(['password' => 'Could not update password: ' . $e->getMessage()]);
        } finally {
            DB::setDefaultConnection($old);
            config(['database.default' => $old]);
        }

        return back()->with('success', 'Password updated successfully for ' . ($user->email ?? $tenant->email) . '.');
    }


    // ================================================================
    // CMS — Landing Page Editor
    // ================================================================
    private function cmsSettingsPath(): string
    {
        return storage_path('app/cms-settings.json');
    }

    private function cmsLoad(): array
    {
        $file = $this->cmsSettingsPath();
        if (!is_file($file)) {
            return [];
        }
        $json = file_get_contents($file);
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }

    private function cmsSave(array $data): void
    {
        $file = $this->cmsSettingsPath();
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0775, true);
        }
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function cmsSaveSection(string $section, array $payload): void
    {
        $all = $this->cmsLoad();
        $all[$section] = $payload;
        $this->cmsSave($all);
    }

    public function cmsDashboard(Request $r)
    {
        $this->check($r);
        return view('saas.cms.dashboard', ['cms' => $this->cmsLoad()]);
    }

    // ---------------- Landing layouts ----------------
    public function cmsLandingPages(Request $r)
    {
        $this->check($r);
        $cms = $this->cmsLoad();
        return view('saas.cms.landing-pages', [
            'cms'       => $cms,
            'activeTpl' => $cms['landing_template'] ?? 'default',
        ]);
    }

    public function cmsLandingPagesSave(Request $r)
    {
        $this->check($r);
        $data = $r->validate([
            'landing_template' => 'required|in:default,landing-two,landing-three,landing-four,landing-five,landing-six',
        ]);

        $cms = $this->cmsLoad();
        $cms['landing_template'] = $data['landing_template'];
        $this->cmsSave($cms);

        return back()->with('success', 'Landing layout updated.');
    }

    // ---------------- Hero ----------------
    public function cmsHero(Request $r)
    {
        $this->check($r);
        $cms = $this->cmsLoad();
        return view('saas.cms.hero', [
            'cms'   => $cms,
            'hero'  => $cms['hero'] ?? [],
        ]);
    }

    public function cmsHeroSave(Request $r)
    {
        $this->check($r);
        $data = $r->validate([
            'badge'             => 'nullable|string|max:191',
            'title'             => 'nullable|string|max:191',
            'subtitle'          => 'nullable|string|max:500',
            'cta_primary'       => 'nullable|string|max:100',
            'cta_primary_url'   => 'nullable|string|max:191',
            'cta_secondary'     => 'nullable|string|max:100',
            'cta_secondary_url' => 'nullable|string|max:191',
            'hero_image'        => 'nullable|string|max:500',
            'bullet_1'          => 'nullable|string|max:191',
            'bullet_2'          => 'nullable|string|max:191',
            'bullet_3'          => 'nullable|string|max:191',
        ]);
        $this->cmsSaveSection('hero', $data);
        return back()->with('success', 'Hero section saved.');
    }

    // ---------------- Features ----------------
    public function cmsFeatures(Request $r)
    {
        $this->check($r);
        $cms = $this->cmsLoad();
        return view('saas.cms.features', [
            'cms'      => $cms,
            'features' => $cms['features'] ?? ['title' => '', 'subtitle' => '', 'items' => []],
        ]);
    }

    public function cmsFeaturesSave(Request $r)
    {
        $this->check($r);
        $data = $r->validate([
            'title'               => 'nullable|string|max:191',
            'subtitle'            => 'nullable|string|max:500',
            'items'               => 'nullable|array',
            'items.*.icon'        => 'nullable|string|max:100',
            'items.*.title'       => 'nullable|string|max:191',
            'items.*.description' => 'nullable|string|max:500',
        ]);
        $this->cmsSaveSection('features', $data);
        return back()->with('success', 'Features saved.');
    }

    // ---------------- Pricing ----------------
    public function cmsPricing(Request $r)
    {
        $this->check($r);
        $cms = $this->cmsLoad();
        return view('saas.cms.pricing', [
            'cms'     => $cms,
            'pricing' => $cms['pricing'] ?? ['title' => '', 'subtitle' => '', 'note' => ''],
        ]);
    }

    public function cmsPricingSave(Request $r)
    {
        $this->check($r);

        $data = $r->validate([
            'title'              => 'nullable|string|max:191',
            'subtitle'           => 'nullable|string|max:500',
            'note'               => 'nullable|string|max:500',
            'show_monthly'       => 'nullable',
            'show_yearly'        => 'nullable',
            'load_from_database' => 'nullable',
            'show_section'       => 'nullable',
        ]);

        $this->cmsSaveSection('pricing', [
            'title'              => $data['title'] ?? '',
            'subtitle'           => $data['subtitle'] ?? '',
            'note'               => $data['note'] ?? '',
            'show_monthly'       => $r->has('show_monthly'),
            'show_yearly'        => $r->has('show_yearly'),
            'load_from_database' => $r->has('load_from_database'),
            'show_section'       => $r->has('show_section'),
        ]);

        return back()->with('success', 'Pricing section saved.');
    }

    // ---------------- Stats ----------------
    public function cmsStats(Request $r)
    {
        $this->check($r);
        return view('saas.cms.stats', ['cms' => $this->cmsLoad()]);
    }

    public function cmsStatsStore(Request $r)
    {
        $this->check($r);
        $data = $r->validate([
            'value'     => 'required|string|max:50',
            'title'     => 'required|string|max:191',
            'icon'      => 'nullable|string|max:100',
            'is_active' => 'nullable',
        ]);

        \App\Models\CmsItem::create([
            'section'    => 'stats',
            'value'      => $data['value'],
            'title'      => $data['title'],
            'icon'       => $data['icon'] ?? '',
            'is_active'  => $r->has('is_active'),
            'sort_order' => (int) \App\Models\CmsItem::section('stats')->max('sort_order') + 1,
        ]);

        return back()->with('success', 'Stat added.');
    }

    public function cmsStatsUpdate(Request $request, $id)
    {
        $item = \App\Models\CmsItem::findOrFail($id);
        $item->update([
            'value'     => $request->value,
            'title'     => $request->title,
            'icon'      => $request->icon,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Stat updated successfully.');
    }

    public function cmsStatsDestroy($id)
    {
        \App\Models\CmsItem::findOrFail($id)->delete();
        return back()->with('success', 'Stat deleted successfully.');
    }

    public function cmsStatsToggle($id)
    {
        $item = \App\Models\CmsItem::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);
        return back()->with('success', 'Stat visibility updated.');
    }

    public function cmsStatsSave(Request $r)
    {
        $this->check($r);
        $data = $r->validate([
            'items'         => 'nullable|array',
            'items.*.value' => 'nullable|string|max:50',
            'items.*.label' => 'nullable|string|max:191',
        ]);
        $this->cmsSaveSection('stats', $data);
        return back()->with('success', 'Stats saved.');
    }

    // ---------------- How it works ----------------
    public function cmsHowItWorks(Request $r)
    {
        $this->check($r);
        $cms = $this->cmsLoad();
        return view('saas.cms.how-it-works', [
            'cms' => $cms,
            'hiw' => $cms['how_it_works'] ?? ['title' => 'How It Works', 'subtitle' => ''],
        ]);
    }

    public function cmsHowItWorksSave(Request $r)
    {
        $this->check($r);
        $data = $r->validate([
            'title'    => 'nullable|string|max:191',
            'subtitle' => 'nullable|string|max:500',
        ]);
        $this->cmsSaveSection('how_it_works', $data);
        return back()->with('success', 'Section header saved.');
    }

    public function cmsHowItWorksStore(Request $r)
    {
        $this->check($r);
        $data = $r->validate([
            'title'       => 'required|string|max:191',
            'description' => 'nullable|string|max:500',
            'icon'        => 'nullable|string|max:100',
            'color'       => 'nullable|string|max:50',
            'is_active'   => 'nullable',
        ]);

        \App\Models\CmsItem::create([
            'section'     => 'how_it_works',
            'title'       => $data['title'],
            'description' => $data['description'] ?? '',
            'icon'        => $data['icon'] ?? '',
            'color'       => $data['color'] ?? 'green',
            'is_active'   => $r->has('is_active'),
            'sort_order'  => (int) \App\Models\CmsItem::section('how_it_works')->max('sort_order') + 1,
        ]);

        return back()->with('success', 'Step added.');
    }

    public function cmsHowItWorksUpdate(Request $r, $id)
    {
        $this->check($r);
        $item = \App\Models\CmsItem::findOrFail($id);
        $item->update([
            'title'       => $r->title,
            'description' => $r->description,
            'icon'        => $r->icon,
            'color'       => $r->color,
            'is_active'   => $r->has('is_active'),
        ]);

        return back()->with('success', 'Step updated.');
    }

    public function cmsHowItWorksDestroy(Request $r, $id)
    {
        $this->check($r);
        \App\Models\CmsItem::findOrFail($id)->delete();
        return back()->with('success', 'Step deleted.');
    }

    public function cmsHowItWorksToggle(Request $r, $id)
    {
        $this->check($r);
        $item = \App\Models\CmsItem::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);
        return back()->with('success', 'Step visibility updated.');
    }

    // ---------------- Testimonials ----------------
    public function cmsTestimonials(Request $r)
    {
        $this->check($r);
        $cms = $this->cmsLoad();
        return view('saas.cms.testimonials', [
            'cms'          => $cms,
            'testimonials' => $cms['testimonials'] ?? ['title' => '', 'subtitle' => '', 'items' => []],
        ]);
    }

    public function cmsTestimonialsSave(Request $r)
    {
        $this->check($r);
        $data = $r->validate([
            'title'          => 'nullable|string|max:191',
            'subtitle'       => 'nullable|string|max:500',
            'items'          => 'nullable|array',
            'items.*.quote'  => 'nullable|string|max:1000',
            'items.*.author' => 'nullable|string|max:191',
            'items.*.role'   => 'nullable|string|max:191',
            'items.*.avatar' => 'nullable|string|max:500',
        ]);
        $this->cmsSaveSection('testimonials', $data);
        return back()->with('success', 'Testimonials saved.');
    }

    // ---------------- FAQ ----------------
    public function cmsFaq(Request $r)
    {
        $this->check($r);
        $cms = $this->cmsLoad();
        return view('saas.cms.faq', [
            'cms' => $cms,
            'faq' => $cms['faq'] ?? ['title' => '', 'subtitle' => '', 'items' => []],
        ]);
    }

    public function cmsFaqSave(Request $r)
    {
        $this->check($r);
        $data = $r->validate([
            'title'            => 'nullable|string|max:191',
            'subtitle'         => 'nullable|string|max:500',
            'items'            => 'nullable|array',
            'items.*.question' => 'nullable|string|max:500',
            'items.*.answer'   => 'nullable|string|max:2000',
        ]);
        $this->cmsSaveSection('faq', $data);
        return back()->with('success', 'FAQ saved.');
    }

    // ---------------- CTA ----------------
    public function cmsCta(Request $r)
    {
        $this->check($r);
        $cms = $this->cmsLoad();
        return view('saas.cms.cta', [
            'cms' => $cms,
            'cta' => $cms['cta'] ?? [],
        ]);
    }

    public function cmsCtaSave(Request $r)
    {
        $this->check($r);
        $data = $r->validate([
            'title'       => 'nullable|string|max:191',
            'subtitle'    => 'nullable|string|max:500',
            'button_text' => 'nullable|string|max:100',
            'button_url'  => 'nullable|string|max:191',
        ]);
        $this->cmsSaveSection('cta', $data);
        return back()->with('success', 'CTA section saved.');
    }

    // ---------------- Footer ----------------
    public function cmsFooter(Request $r)
    {
        $this->check($r);
        $cms = $this->cmsLoad();
        return view('saas.cms.footer', [
            'cms'    => $cms,
            'footer' => $cms['footer'] ?? ['columns' => [], 'social' => [], 'copyright' => ''],
        ]);
    }

    public function cmsFooterSave(Request $r)
    {
        $this->check($r);
        $data = $r->validate([
            'copyright'          => 'nullable|string|max:500',
            'columns'            => 'nullable|array',
            'columns.*.heading'  => 'nullable|string|max:191',
            'columns.*.links'    => 'nullable|string',
            'social'             => 'nullable|array',
            'social.*.platform'  => 'nullable|string|max:50',
            'social.*.url'       => 'nullable|string|max:191',
        ]);
        $this->cmsSaveSection('footer', $data);
        return back()->with('success', 'Footer saved.');
    }

    // ---------------- SEO ----------------
    public function cmsSeo(Request $r)
    {
        $this->check($r);
        $cms = $this->cmsLoad();
        return view('saas.cms.seo', [
            'cms' => $cms,
            'seo' => $cms['seo'] ?? [],
        ]);
    }

    public function cmsSeoSave(Request $r)
    {
        $this->check($r);
        $data = $r->validate([
            'meta_title'       => 'nullable|string|max:191',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords'    => 'nullable|string|max:500',
            'og_title'         => 'nullable|string|max:191',
            'og_description'   => 'nullable|string|max:500',
            'og_image'         => 'nullable|string|max:500',
            'twitter_handle'   => 'nullable|string|max:100',
            'google_analytics' => 'nullable|string|max:100',
            'robots'           => 'nullable|string|max:100',
        ]);
        $this->cmsSaveSection('seo', $data);
        return back()->with('success', 'SEO settings saved.');
    }

    // ---------------- Privacy ----------------
    public function cmsPrivacy(Request $r)
    {
        $this->check($r);
        $cms = $this->cmsLoad();
        return view('saas.cms.privacy', [
            'cms'     => $cms,
            'privacy' => $cms['privacy'] ?? ['title' => 'Privacy Policy', 'body' => '', 'updated_at' => null],
        ]);
    }

    public function cmsPrivacySave(Request $r)
    {
        $this->check($r);
        $data = $r->validate([
            'title' => 'nullable|string|max:191',
            'body'  => 'nullable|string',
        ]);
        $data['updated_at'] = now()->toDateTimeString();
        $this->cmsSaveSection('privacy', $data);
        return back()->with('success', 'Privacy Policy saved.');
    }

    // ---------------- Terms ----------------
    public function cmsTerms(Request $r)
    {
        $this->check($r);
        $cms = $this->cmsLoad();
        return view('saas.cms.terms', [
            'cms'   => $cms,
            'terms' => $cms['terms'] ?? ['title' => 'Terms & Conditions', 'body' => '', 'updated_at' => null],
        ]);
    }

    public function cmsTermsSave(Request $r)
    {
        $this->check($r);
        $data = $r->validate([
            'title' => 'nullable|string|max:191',
            'body'  => 'nullable|string',
        ]);
        $data['updated_at'] = now()->toDateTimeString();
        $this->cmsSaveSection('terms', $data);
        return back()->with('success', 'Terms & Conditions saved.');
    }

    // ---------------- Translations ----------------
    public function cmsTranslations(Request $r)
    {
        $this->check($r);
        $cms = $this->cmsLoad();
        return view('saas.cms.translations', [
            'cms'          => $cms,
            'translations' => $cms['translations'] ?? ['en' => [], 'fr' => [], 'ar' => [], 'es' => [], 'hi' => [], 'bn' => [], 'tr' => [], 'de' => [], 'pt' => [], 'ur' => []],
        ]);
    }

    public function cmsTranslationsSave(Request $r)
    {
        $this->check($r);
        $data = $r->validate([
            'lang'  => 'required|in:en,fr,ar,es,hi,bn,tr,de,pt,ur',
            'key'   => 'required|string|max:191',
            'value' => 'nullable|string|max:2000',
        ]);

        $all = $this->cmsLoad();
        $all['translations'] = $all['translations'] ?? [];
        $all['translations'][$data['lang']] = $all['translations'][$data['lang']] ?? [];
        $all['translations'][$data['lang']][$data['key']] = $data['value'] ?? '';
        $this->cmsSave($all);

        return back()->with('success', "Translation saved for [{$data['lang']}] {$data['key']}.");
    }

    public function provisionAllPending(Request $r)
    {
        if ($check = $this->check($r)) return $check;
        set_time_limit(0);

        $pending = Tenant::where('status', 'pending')->get();
        if ($pending->isEmpty()) {
            return back()->with('success', 'No pending tenants to provision.');
        }

        $provisioned = 0;
        $failed = 0;
        $errors = [];

        foreach ($pending as $tenant) {
            try {
                $password = 'password';
                $pendingFile = storage_path('app/pending-registrations.json');
                $pendingRegs = is_file($pendingFile)
                    ? (json_decode(file_get_contents($pendingFile), true) ?: [])
                    : [];

                if (isset($pendingRegs[$tenant->id]['password'])) {
                    $password = $pendingRegs[$tenant->id]['password'];
                }

                $this->migrateTenant($tenant);

                // Seed Farsi language + translations
                $this->seedFarsiLanguage($tenant);

                if ($tenant->email) {
                    $this->createTenantAdminUser($tenant, $tenant->name, $tenant->email, $password);
                }
                if ($tenant->plan) {
                    $this->syncPlanModulesToTenants($tenant->plan);
                }
                $tenant->update(['status' => 'trial']);

                if (isset($pendingRegs[$tenant->id])) {
                    unset($pendingRegs[$tenant->id]);
                    file_put_contents($pendingFile, json_encode($pendingRegs, JSON_PRETTY_PRINT));
                }

                $provisioned++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = "{$tenant->slug}: " . $e->getMessage();
                \Log::error("Batch provisioning failed for {$tenant->slug}: " . $e->getMessage());
            }
        }

        $msg = "Provisioned: {$provisioned}";
        if ($failed > 0) $msg .= " · Failed: {$failed}";
        if (!empty($errors)) {
            return back()->with('success', $msg)->withErrors($errors);
        }
        return back()->with('success', $msg);
    }
}