<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;

class ModuleSettingsController extends Controller
{
    /**
     * Check if this is a SaaS tenant (has a plan assigned).
     * If yes, all module management features are locked down.
     */
    private function isSaasTenant(Request $request): bool
    {
        $saasTenant = $request->attributes->get('saasTenant');
        if (!$saasTenant) {
            // Try session as fallback
            $saasTenant = session('saas_tenant');
        }
        return $saasTenant && $saasTenant->plan;
    }

    /**
     * Get the current state of all modules, forcibly overridden by the SaaS Plan.
     * BLOCKED for SaaS tenants — only Super Admin can access this.
     */
    public function get_modules_info(Request $request)
    {
        // ==========================================================
        // BLOCK SAAS TENANTS — Only Super Admin can view modules page
        // ==========================================================
        if ($this->isSaasTenant($request)) {
            return response()->json([
                'message' => 'Module management is restricted to Super Admin only.'
            ], 403);
        }
        // ==========================================================

        // Get the modules allowed by the SaaS Plan (injected by ResolveSaaSTenant)
        $saasModules = $request->attributes->get('saas_modules', []);
        $hasAllAccess = empty($saasModules) || in_array('*', $saasModules);

        // Normalize the plan's features to lowercase-without-separators for matching
        $normalizedPlanFeatures = array_map(function ($m) {
            return strtolower(str_replace(['_', '-', ' '], '', $m));
        }, $saasModules);

        $modules = Module::all();
        $result = [];
        $moduleFlags = [];

        foreach ($modules as $name => $module) {
            $moduleJson = json_decode(File::get($module->getPath() . '/module.json'), true);
            
            // Default status from disk
            $status = $module->isEnabled();
            
            if (!$hasAllAccess) {
                $normalizedName = strtolower(str_replace(['_', '-', ' '], '', $name));

                if (!in_array($normalizedName, $normalizedPlanFeatures)) {
                    $status = false;
                }
            }

            $flagKey = strtolower(str_replace(['_', '-', ' '], '_', $name));
            $moduleFlags[$flagKey] = $status;

            $result[] = [
                'module_name'     => $name,
                'current_version' => $moduleJson['version'] ?? '1.0.0',
                'status'          => $status,
            ];
        }

        if (!$hasAllAccess) {
            try {
                $setting = DB::table('settings')->whereNull('deleted_at')->first();
                if ($setting) {
                    DB::table('settings')->where('id', $setting->id)->update([
                        'module_flags' => json_encode($moduleFlags)
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to save module flags: ' . $e->getMessage());
            }
        }

        return response()->json($result);
    }

    /**
     * Toggle a module. BLOCKED for SaaS tenants.
     */
    public function update_status_module(Request $request)
    {
        // ==========================================================
        // BLOCK SAAS TENANTS — Only Super Admin can toggle modules
        // ==========================================================
        if ($this->isSaasTenant($request)) {
            return response()->json([
                'message' => 'Module management is restricted to Super Admin only.'
            ], 403);
        }
        // ==========================================================

        $request->validate([
            'name'   => 'required|string',
            'status' => 'required',
        ]);

        $saasModules = $request->attributes->get('saas_modules', []);
        $hasAllAccess = empty($saasModules) || in_array('*', $saasModules);
        $normalizedPlanFeatures = array_map(function ($m) {
            return strtolower(str_replace(['_', '-', ' '], '', $m));
        }, $saasModules);

        $normalizedName = strtolower(str_replace(['_', '-', ' '], '', $request->name));

        if (!$hasAllAccess && !in_array($normalizedName, $normalizedPlanFeatures)) {
            return response()->json(['message' => 'This module is not available on your plan.'], 403);
        }

        $module = Module::find($request->name);

        if (!$module) {
            return response()->json(['message' => 'Module not found'], 404);
        }

        if ($request->status) {
            $module->enable();
        } else {
            $module->disable();
        }

        return response()->json(['message' => 'Module status updated']);
    }

    public function upload_module(Request $request)
    {
        // ==========================================================
        // BLOCK SAAS TENANTS — Only Super Admin can upload modules
        // ==========================================================
        if ($this->isSaasTenant($request)) {
            return response()->json([
                'message' => 'Module upload is restricted to Super Admin only.'
            ], 403);
        }
        // ==========================================================

        $request->validate([
            'module_zip' => 'required|file|mimes:zip',
        ]);

        $file = $request->file('module_zip');
        $modulesPath = base_path('Modules');

        if (!File::isDirectory($modulesPath)) {
            File::makeDirectory($modulesPath, 0755, true);
        }

        $tmpPath = storage_path('app/tmp_module_' . time());
        File::makeDirectory($tmpPath, 0755, true);

        $zip = new \ZipArchive;
        if ($zip->open($file->getRealPath()) === true) {
            $zip->extractTo($tmpPath);
            $zip->close();
        } else {
            File::deleteDirectory($tmpPath);
            return response()->json(['message' => 'Failed to extract zip'], 422);
        }

        $extracted = File::directories($tmpPath);
        $sourceDir = count($extracted) === 1 ? $extracted[0] : $tmpPath;

        if (!File::exists($sourceDir . '/module.json')) {
            if (File::exists($tmpPath . '/module.json')) {
                $sourceDir = $tmpPath;
            } else {
                File::deleteDirectory($tmpPath);
                return response()->json(['message' => 'Invalid module: module.json not found'], 422);
            }
        }

        $moduleJson = json_decode(File::get($sourceDir . '/module.json'), true);
        $moduleName = $moduleJson['name'] ?? null;

        if (!$moduleName) {
            File::deleteDirectory($tmpPath);
            return response()->json(['message' => 'Invalid module.json'], 422);
        }

        $targetPath = $modulesPath . '/' . $moduleName;

        if (File::isDirectory($targetPath)) {
            File::deleteDirectory($targetPath);
        }

        File::moveDirectory($sourceDir, $targetPath);
        File::deleteDirectory($tmpPath);

        $module = Module::find($moduleName);
        if ($module) {
            $module->enable();
        }

        return response()->json(['message' => 'Module uploaded successfully']);
    }
}