<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Role;
use App\Models\ShippingMethod;
use App\Models\StoreSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class IraqiDemoSeeder extends Seeder
{
    private const BRAND = 'براند العراقي';

    private const OWNER_EMAIL = 'manager@iraqi.example';

    private const LOGO = 'iraqi-demo-mark.png';

    public function run(): void
    {
        $accounts = [
            ['firstname' => 'محمد', 'lastname' => 'العراقي', 'username' => 'مدير العراقي', 'email' => self::OWNER_EMAIL, 'role' => 'Owner', 'label' => 'المدير', 'scope' => 'all'],
            ['firstname' => 'ندى', 'lastname' => 'علي', 'username' => 'كاشير العراقي', 'email' => 'cashier@iraqi.example', 'role' => 'Cashier', 'label' => 'الكاشير', 'scope' => 'cashier'],
            ['firstname' => 'يوسف', 'lastname' => 'حسن', 'username' => 'المدير المالي العراقي', 'email' => 'finance@iraqi.example', 'role' => 'Finance Manager', 'label' => 'المدير المالي', 'scope' => 'finance'],
        ];
        foreach ($accounts as &$account) {
            $account['password'] = Str::password(20, symbols: false);
        }
        unset($account);
        $credentials = array_map(fn ($account) => [
            'label' => $account['label'], 'email' => $account['email'], 'password' => $account['password'],
        ], $accounts);

        DB::transaction(function () use (&$accounts) {
            foreach (['settings', 'users', 'products', 'warehouses', 'clients'] as $table) {
                if (DB::table($table)->exists()) {
                    throw new RuntimeException('Import the clean demo schema into a new database before seeding.');
                }
            }

            $warehouseIds = $this->createWarehouses();
            $ownerId = $this->createRoleAccounts($accounts, $warehouseIds);
            $clientIds = $this->createCustomers();
            $this->configureAdministration($warehouseIds[0], $clientIds[0]);
            $this->configureStore($warehouseIds[0]);
            $this->callWith(IraqiDemoCatalogSeeder::class, compact('ownerId', 'warehouseIds', 'clientIds'));
        });

        Cache::forget('store_settings');
        foreach ($credentials as $account) {
            $this->command->line($account['label']." login: {$account['email']} / {$account['password']}");
        }
    }

    private function createRoleAccounts(array &$accounts, array $warehouseIds): int
    {
        $ownerId = 0;
        $permissions = DB::table('permissions')->whereNull('deleted_at')->pluck('id', 'name');
        foreach ($accounts as &$account) {
            $role = Role::create(['name' => $account['role'], 'label' => $account['label'], 'status' => 1]);
            $allowed = match ($account['scope']) {
                'all' => $permissions->values()->all(),
                'cashier' => $this->permissionIds($permissions, ['Pos_view', 'Sales_add', 'payment_sales_add', 'Customers_view', 'products_view']),
                'finance' => $this->permissionIds($permissions, ['Reports_', 'account', 'accounting_', 'payment_', 'deposit_', 'expense_', 'Sales_view', 'Purchases_view', 'Sale_Returns_view', 'Purchase_Returns_view']),
            };
            $role->permissions()->sync($allowed);
            $userId = DB::table('users')->insertGetId([
                'firstname' => $account['firstname'], 'lastname' => $account['lastname'], 'username' => $account['username'],
                'email' => $account['email'], 'password' => Hash::make($account['password']), 'role_id' => $role->id,
                'avatar' => 'no_avatar.png', 'phone' => '',
                'is_all_warehouses' => in_array($account['scope'], ['all', 'finance'], true),
                'record_view' => in_array($account['scope'], ['all', 'finance'], true),
            ]);
            DB::table('role_user')->insert(['user_id' => $userId, 'role_id' => $role->id]);
            $userWarehouses = match ($account['scope']) {
                'all', 'finance' => $warehouseIds,
                default => [$warehouseIds[1]],
            };
            DB::table('user_warehouse')->insert(array_map(fn ($warehouseId) => [
                'user_id' => $userId, 'warehouse_id' => $warehouseId,
            ], $userWarehouses));
            if ($account['scope'] === 'all') {
                $ownerId = $userId;
            }
        }
        unset($account);
        return $ownerId;
    }

    private function permissionIds($permissions, array $prefixes): array
    {
        return $permissions->filter(fn ($id, $name) => collect($prefixes)->contains(fn ($prefix) => str_starts_with(strtolower($name), strtolower($prefix))))->values()->all();
    }

    private function createWarehouses(): array
    {
        $locations = [
            'المخزن الرئيسي' => 'دمياط',
            'فرع الزرقا' => 'الزرقا',
            'فرع المنصورة' => 'المنصورة',
            'فرع دمياط الجديدة' => 'دمياط الجديدة',
        ];

        $warehouseIds = [];
        foreach ($locations as $name => $city) {
            $warehouseIds[] = DB::table('warehouses')->insertGetId([
                'name' => $name, 'city' => $city, 'country' => 'مصر',
            ]);
        }

        return $warehouseIds;
    }

    private function createCustomers(): array
    {
        $customers = [
            ['عميل الفرع', 'دمياط', 0],
            ['مريم خالد', 'المنصورة', 40],
            ['أحمد سامي', 'الزرقا', 80],
            ['سلمى ياسر', 'دمياط الجديدة', 60],
        ];

        $clientIds = [];
        foreach ($customers as $index => [$name, $city, $points]) {
            $clientIds[] = DB::table('clients')->insertGetId([
                'code' => 1 + $index, 'name' => $name, 'city' => $city, 'country' => 'مصر',
                'email' => "customer{$index}@iraqi.example", 'phone' => '', 'adresse' => '',
                'points' => $points, 'is_royalty_eligible' => $index > 0,
            ]);
        }

        return $clientIds;
    }

    private function configureAdministration(int $warehouseId, int $clientId): void
    {
        $currencyId = Currency::retail()->id;
        $disabledModules = ['recruit', 'meeting', 'EWallet', 'woocommerce_settings', 'shopify',
            'subscription_product', 'manufacturing', 'assets', 'projects', 'bookings', 'service', 'fleet',
            'hospital', 'school', '/realestate', '/products/vehicle-fitment', '/quotations', '/portal',
            '/store/invite-codes', '/store/quote-requests', '/store/popups'];

        DB::table('settings')->insert([
            'email' => self::OWNER_EMAIL, 'CompanyName' => self::BRAND, 'company_name_ar' => self::BRAND,
            'CompanyPhone' => '', 'CompanyAdress' => 'دمياط • الدقهلية، مصر',
            'app_name' => self::BRAND, 'page_title_suffix' => 'إدارة الفروع والمتجر',
            'footer' => '© M.H · تنفيذ M.H', 'developed_by' => 'M.H',
            'logo' => self::LOGO, 'favicon' => self::LOGO, 'default_language' => 'ar',
            'customize_button_visible' => 0,
            'login_bg_color' => '#152b2b', 'login_footer_text' => '© M.H · تنفيذ M.H',
            'login_hero_badge' => 'نظام تشغيل الفروع والمتجر', 'login_hero_title' => self::BRAND,
            'login_hero_subtitle' => 'إدارة المبيعات والمخزون والطلبات من مكان واحد.',
            'login_hero_feature_1' => 'الزرقا', 'login_hero_feature_2' => 'المنصورة',
            'login_hero_feature_3' => 'دمياط الجديدة', 'login_panel_title' => 'دخول الإدارة',
            'login_panel_subtitle' => 'سجّل الدخول إلى لوحة تحكم براند العراقي.',
            'login_btn_text' => 'تسجيل الدخول',
            'currency_id' => $currencyId, 'warehouse_id' => $warehouseId, 'client_id' => $clientId,
            'allow_overselling' => 0, 'module_flags' => json_encode(array_fill_keys($disabledModules, false)),
        ]);
        DB::table('languages')->where('is_default', true)->update(['is_default' => false]);
        DB::table('languages')->where('locale', 'ar')->update(['is_default' => true]);
        DB::table('pos_settings')->insert([
            'note_customer' => 'شكراً لتسوقكم من براند العراقي', 'show_zatca_qr' => 0,
            'allow_overselling' => 0, 'enable_customer_points' => 1,
        ]);
    }

    private function configureStore(int $warehouseId): void
    {
        StoreSetting::create([
            'store_name' => self::BRAND, 'enabled' => true, 'language' => 'ar', 'currency_code' => Currency::RETAIL_SYMBOL,
            'default_warehouse_id' => $warehouseId, 'warehouse_ids' => [$warehouseId],
            'allow_overselling' => false, 'hide_out_of_stock' => true,
            'registration_enabled' => true, 'require_email_verification' => false,
            'payment_cod_enabled' => true, 'payment_mobile_money_enabled' => false,
            'primary_color' => '#347869', 'secondary_color' => '#a96a48',
            'logo_path' => 'images/'.self::LOGO, 'favicon_path' => 'images/'.self::LOGO,
            'hero_image_path' => 'images/store/iraqi-demo-hero.svg',
            'hero_title' => 'أساسيات تلبسها كل يوم',
            'hero_subtitle' => 'تشكيلة براند العراقي من التيشيرتات والقمصان والبناطيل. اختار اللون والمقاس المناسبين لك.',
            'seo_meta_title' => 'براند العراقي | متجر الملابس',
            'seo_meta_description' => 'اكتشف تشكيلة براند العراقي وتسوق بالمقاس واللون المتاحين.',
            'topbar_text_left' => 'الزرقا • المنصورة • دمياط الجديدة',
            'topbar_text_right' => 'الدفع عند الاستلام',
            'footer_text' => 'براند العراقي • كتالوج تجريبي للعرض',
            'homepage_lineup' => [['type' => 'hero'], ['type' => 'collection', 'slug' => 'everyday-essentials'],
                ['type' => 'collection', 'slug' => 'winter-layers']],
        ]);

        $shipping = ShippingMethod::create(['name' => 'شحن داخل مصر', 'price' => 65, 'active' => true]);
        $shipping->regions()->createMany([['country' => 'مصر'], ['country' => 'Egypt']]);
    }
}
