<?php

namespace App\Http\Controllers\Api;

use App\Application\UseCases\Dashboard\GetDashboard;
use App\Http\Controllers\Controller;
use App\Http\Resources\MedicineResource;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(GetDashboard $useCase): JsonResponse
    {
        $dashboard = $useCase->execute();

        return response()->json([
            'data' => [
                'stats' => $dashboard['stats'],
                'sales_trend' => $dashboard['sales_trend'],
                'notifications' => [
                    'stok_menipis' => MedicineResource::collection($dashboard['notifications']['stok_menipis']),
                    'expired_dekat' => MedicineResource::collection($dashboard['notifications']['expired_dekat']),
                ],
                'activities' => $dashboard['activities'],
            ],
        ]);
    }
}
