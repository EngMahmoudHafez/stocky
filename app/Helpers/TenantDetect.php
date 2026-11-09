<?php
namespace App\Helpers;

use App\Models\SaaS\Tenant;

class TenantDetect
{
    public static function current(): ?Tenant
    {
        $host = strtolower(request()->getHost());
        $base = strtolower(trim(env('SAAS_BASE_DOMAIN', 'localhost')));

        // Exact base domain → central admin
        if ($host === $base || $host === 'www.' . $base) {
            return null;
        }

        // Strip .base
        if (str_ends_with($host, '.' . $base)) {
            $sub = substr($host, 0, -strlen('.' . $base));
        } else {
            // Custom domain (not on the base)
            $sub = null;
        }

        // Skip reserved subdomains
        if ($sub && in_array($sub, ['www', 'admin', 'app', 'api', 'super'])) {
            return null;
        }

        return Tenant::where(function ($q) use ($sub, $host) {
            if ($sub) $q->where('slug', $sub);
            $q->orWhere('domain', $host);
        })->first();
    }
}