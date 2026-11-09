<?php

namespace App\Http\Controllers;

use App\Services\DashboardOverview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardOverviewController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'period' => 'nullable|in:week,month',
            'warehouse_id' => 'nullable|integer|min:1',
        ]);

        $overview = new DashboardOverview(
            $request->user(),
            ($filters['period'] ?? 'week') === 'month' ? 30 : 7,
            isset($filters['warehouse_id']) ? (int) $filters['warehouse_id'] : null,
        );

        return response()->json($overview->snapshot());
    }
}
