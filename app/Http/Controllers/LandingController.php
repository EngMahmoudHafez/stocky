<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
   public function index(\Illuminate\Http\Request $r)
{
    $cmsFile = storage_path('app/cms-settings.json');
    $cms = is_file($cmsFile) ? (json_decode(file_get_contents($cmsFile), true) ?: []) : [];

    $settingsFile = storage_path('app/super-settings.json');
    $settings = is_file($settingsFile) ? (json_decode(file_get_contents($settingsFile), true) ?: []) : [];

    // Check if logo and favicon exist on disk
    $settings['logo_path'] = file_exists(public_path('images/super/settings/logo.png'))
        ? '/images/super/settings/logo.png'
        : null;

    $settings['favicon_path'] = file_exists(public_path('images/super/settings/favicon.ico'))
        ? '/images/super/settings/favicon.ico'
        : null;

    $settings = array_merge([
        'app_name'     => 'Stocky SaaS',
        'company_name' => 'Stocky Inc.',
        'email'        => 'contact@example.com',
        'phone'        => '',
        'website'      => '',
        'address'      => '',
        'dashboard_footer_text' => '© ' . date('Y') . ' Stocky Inc. — All rights reserved.',
    ], $settings);

    $template = $cms['landing_template'] ?? 'default';
    if (is_array($template)) $template = $template['landing_template'] ?? 'default';
    $template = is_string($template) ? $template : 'default';

    if ($r->filled('_preview') && $r->session()->get('saas_super_admin')) {
        $template = $r->query('_preview');
    }

    $view = "landing.$template";
    if (!view()->exists($view)) $view = 'landing.default';

    return view($view, ['cms' => $cms, 'settings' => $settings]);
}}