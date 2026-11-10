<?php

declare(strict_types=1);

namespace App\Support;

class IraqiBrand
{
    public const NAME = 'العراقي';

    public const LOGO = 'eleraky-icon-transparent.png';

    public const FAVICON = 'eleraky-favicon-transparent.png';

    /** @return array<string, string|int> */
    public static function administration(): array
    {
        return [
            'CompanyName' => self::NAME.' | ELERAKY', 'company_name_ar' => self::NAME,
            'app_name' => self::NAME.' | ELERAKY', 'page_title_suffix' => 'إدارة الفروع والمتجر',
            'logo' => self::LOGO, 'favicon' => self::FAVICON,
            'sidebar_logo_width' => 44, 'sidebar_logo_height' => 44, 'sidebar_show_logo' => 1,
            'login_bg_color' => '#214d35', 'login_hero_badge' => 'العراقي • بتاع جملة',
            'login_hero_title' => 'العراقي | ELERAKY',
            'login_hero_subtitle' => 'إدارة المبيعات والمخزون وحسابات الفروع والطلبات من مكان واحد.',
            'login_panel_title' => 'أهلًا بيك في العراقي',
            'login_panel_subtitle' => 'سجّل الدخول بحسابك للوصول إلى لوحة الإدارة.',
        ];
    }

    /** @return array<string, string> */
    public static function storefront(): array
    {
        return [
            'store_name' => self::NAME,
            'primary_color' => '#214d35', 'secondary_color' => '#ae8d53',
            'logo_path' => 'images/'.self::LOGO, 'favicon_path' => 'images/'.self::FAVICON,
            'hero_image_path' => 'images/store/iraqi-demo-hero.svg',
            'hero_title' => 'العراقي… بتاع جملة',
            'hero_subtitle' => 'تشكيلة العراقي من الملابس بألوان ومقاسات متنوعة. استعرض الموديلات واختار المناسب لك.',
            'seo_meta_title' => 'العراقي | ELERAKY — بتاع جملة',
            'seo_meta_description' => 'اكتشف تشكيلة ملابس العراقي — ELERAKY. تصفح الموديلات والألوان والمقاسات واطلب أونلاين.',
            'footer_text' => 'العراقي | ELERAKY • بتاع جملة • الزرقا، المنصورة، دمياط الجديدة',
        ];
    }
}
