<?php

namespace Database\Seeders;

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

    private const OWNER_EMAIL = 'owner@iraqi.example';

    private const LOGO = 'iraqi-demo-mark.png';

    public function run(): void
    {
        if (! app()->isLocal()) {
            throw new RuntimeException('The Iraqi demo requires a local environment.');
        }

        $password = Str::password(20, symbols: false);

        DB::transaction(function () use ($password) {
            foreach (['settings', 'users', 'products', 'warehouses', 'clients'] as $table) {
                if (DB::table($table)->exists()) {
                    throw new RuntimeException('Import the clean demo schema into a new database before seeding.');
                }
            }

            $ownerId = $this->createOwner($password);
            $warehouseIds = $this->createWarehouses();
            $clientIds = $this->createCustomers();
            $this->configureAdministration($warehouseIds[0], $clientIds[0]);
            $this->configureStore($warehouseIds[0]);
            $this->callWith(IraqiDemoCatalogSeeder::class, compact('ownerId', 'warehouseIds', 'clientIds'));
        });

        Cache::forget('store_settings');
        $this->command->info('Demo administrator: '.self::OWNER_EMAIL);
        $this->command->info('Demo password: '.$password);
    }

    private function createOwner(string $password): int
    {
        $role = Role::create(['name' => 'Owner', 'label' => 'المالك والمدير العام', 'status' => 1]);
        $this->assignOwnerPermissions($role);

        $ownerId = DB::table('users')->insertGetId([
            'firstname' => 'محمد', 'lastname' => 'العراقي', 'username' => 'محمد العراقي',
            'email' => self::OWNER_EMAIL, 'password' => Hash::make($password), 'role_id' => $role->id,
            'avatar' => 'no_avatar.png', 'phone' => '', 'is_all_warehouses' => 1, 'record_view' => 1,
        ]);
        DB::table('role_user')->insert(['user_id' => $ownerId, 'role_id' => $role->id]);

        return $ownerId;
    }

    private function assignOwnerPermissions(Role $role): void
    {
        $excludedPrefixes = '/^(hms_|school_|mrp_|fleet_|asset_|project_|service_|checklist_|customer_maintenance_|recruit_|meeting|shopify_|realestate_|kitchen_display_|serial_numbers|batch_|webhooks_|knowledge_base_|Quotations_|edit_.*_quotation)/i';
        $excluded = ['assets', 'tasks', 'projects', 'bookings', 'trays', 'quickbooks_settings',
            'woocommerce_settings', 'subscription_product', 'ewallet', 'report_warranty',
            'expiry_report', 'Batch_Register_Report', 'view_batches', 'AI_Reports',
            'sales_3d_dashboard', 'Subscribers_view'];

        $permissionIds = DB::table('permissions')->whereNull('deleted_at')->pluck('id', 'name')
            ->reject(fn ($id, $name) => preg_match($excludedPrefixes, $name) || in_array($name, $excluded, true));
        $role->permissions()->attach($permissionIds->values()->all());
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
        $currencyId = DB::table('currencies')->insertGetId(['code' => 'EGP', 'name' => 'جنيه مصري', 'symbol' => 'ج.م']);
        $disabledModules = ['recruit', 'meeting', 'EWallet', 'woocommerce_settings', 'shopify',
            'subscription_product', 'manufacturing', 'assets', 'projects', 'bookings', 'service', 'fleet',
            'hospital', 'school', '/realestate', '/products/vehicle-fitment', '/quotations', '/portal',
            '/store/invite-codes', '/store/quote-requests', '/store/popups'];

        DB::table('settings')->insert([
            'email' => self::OWNER_EMAIL, 'CompanyName' => self::BRAND, 'company_name_ar' => self::BRAND,
            'CompanyPhone' => '', 'CompanyAdress' => 'دمياط • الدقهلية، مصر',
            'app_name' => self::BRAND, 'page_title_suffix' => 'إدارة الفروع والمتجر',
            'footer' => 'براند العراقي • نسخة العرض', 'developed_by' => self::BRAND,
            'logo' => self::LOGO, 'favicon' => self::LOGO, 'default_language' => 'ar',
            'login_bg_color' => '#152b2b', 'login_footer_text' => 'براند العراقي • نسخة العرض',
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
            'store_name' => self::BRAND, 'enabled' => true, 'language' => 'ar', 'currency_code' => 'ج.م',
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
