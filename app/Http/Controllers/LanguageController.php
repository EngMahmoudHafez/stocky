<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Setting;
use App\Models\Translate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LanguageController extends Controller
{
    public function load_language(Request $request)
    {
        $languages = Language::supported()->where('is_active', true)->get(['name', 'locale', 'flag']);

        return response()->json($languages);

    }

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'translations_settings', Setting::class);

        return Language::supported()->orderBy('locale')->get();
    }

    public function setDefault(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'translations_settings', Setting::class);

        return $this->applyDefault(Language::supported()->findOrFail($id));
    }

    public function setDefaultByLocale(Request $request, $locale)
    {
        $this->authorizeForUser($request->user('api'), 'translations_settings', Setting::class);

        return $this->applyDefault(Language::supported()->where('locale', $locale)->firstOrFail());
    }

    private function applyDefault(Language $language)
    {
        DB::transaction(function () use ($language) {
            Language::query()->update(['is_default' => false]);
            Language::whereKey($language->id)->update(['is_default' => true]);
            Setting::whereNull('deleted_at')->update(['default_language' => $language->locale]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Default language set successfully.',
            'default_locale' => $language->locale,
        ]);
    }

    public function get_translate(Request $request, $locale)
    {
        $this->authorizeForUser($request->user('api'), 'translations_settings', Setting::class);

        $perPage = (int) $request->get('per_page', 100);
        $page = (int) $request->get('page', 1);
        $search = $request->get('search');

        // Step 1: Get all EN keys
        $enKeysQuery = Translate::where('locale', 'en');

        if ($search) {
            $enKeysQuery->where(function ($q) use ($search) {
                $q->where('key', 'like', "%$search%")
                    ->orWhere('value', 'like', "%$search%");
            });
        }

        $enKeys = $enKeysQuery->orderBy('id', 'desc')->get();
        $enKeyList = $enKeys->pluck('key')->toArray();

        // Step 2: Get all locale-specific keys (not in EN)
        $localeExtraKeys = Translate::where('locale', $locale)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('key', 'like', "%$search%")
                        ->orWhere('value', 'like', "%$search%");
                });
            })
            ->whereNotIn('key', $enKeyList)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'key' => $item->key,
                    'value' => $item->value,
                    'locale' => $item->locale,
                ];
            });

        // Step 3: Merge EN + values from locale
        $localeTranslations = Translate::where('locale', $locale)->pluck('value', 'key');

        $mergedFromEN = $enKeys->map(function ($item) use ($locale, $localeTranslations) {
            return [
                'id' => $item->id,
                'key' => $item->key,
                'value' => $localeTranslations[$item->key] ?? '',
                'locale' => $locale,
            ];
        });

        // Step 4: Combine all
        $combined = $mergedFromEN->merge($localeExtraKeys)->sortByDesc('id')->values();

        // Step 5: Paginate manually
        $total = $combined->count();
        $offset = ($page - 1) * $perPage;
        $paginated = $combined->slice($offset, $perPage)->values();

        $language = Language::where('locale', $locale)->first()->name;

        return response()->json([
            'data' => $paginated,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'language' => $language,
        ]);
    }

    public function updateOrInsert(Request $request, $locale)
    {
        $this->authorizeForUser($request->user('api'), 'translations_settings', Setting::class);

        $request->validate([
            'key' => 'required|string|max:255',
            'value' => 'nullable|string',
        ]);

        // Flag the row as client-edited so TranslationSeeder never overwrites it.
        $translation = Translate::updateOrCreate(
            ['locale' => $locale, 'key' => $request->key],
            ['value' => $request->value, 'is_customized' => 1]
        );

        return response()->json([
            'message' => 'Translation saved successfully.',
            'data' => $translation,
        ]);
    }

    public function delete_translate(Request $request, $locale, $key)
    {
        $this->authorizeForUser($request->user('api'), 'translations_settings', Setting::class);

        // Try to find the translation entry by locale and key
        $translation = Translate::where('locale', $locale)
            ->where('key', $key)
            ->first();

        if (! $translation) {
            return response()->json([
                'message' => 'Translation not found.',
            ], 404);
        }

        // Prevent deleting default/original keys
        if ($translation->is_default) {
            return response()->json([
                'message' => 'You cannot delete a default/original translation key.',
            ], 403);
        }

        // Delete the translation
        $translation->delete();

        return response()->json([
            'message' => 'Translation deleted successfully.',
        ]);
    }
}
