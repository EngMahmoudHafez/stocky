<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizeForUser($request->user('api'), 'view', Currency::class);

        $pound = Currency::retail();
        $search = trim((string) $request->input('search', ''));
        $matches = $search === '' || mb_stripos($pound->name, $search) !== false
            || mb_stripos($pound->code, $search) !== false;
        $currencies = $matches && (int) $request->input('page', 1) === 1 ? [$pound] : [];

        return response()->json([
            'currencies' => $currencies,
            'totalRows' => (int) $matches,
            'default_currency_id' => $pound->id,
        ]);
    }
}
