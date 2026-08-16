<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MedicineController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SawRecommendationController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::put('auth/profile', [AuthController::class, 'updateProfile']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::get('dashboard', DashboardController::class);

        Route::apiResource('users', UserController::class)->except(['show']);
        Route::apiResource('medicines', MedicineController::class);
        Route::apiResource('purchases', PurchaseController::class);
        Route::patch('purchases/{purchase}/details/{purchaseDetail}/return', [PurchaseController::class, 'markReturn']);
        Route::apiResource('suppliers', SupplierController::class);
        Route::apiResource('sales', SaleController::class);

        Route::get('reports/daily', [ReportController::class, 'daily']);
        Route::get('reports/monthly', [ReportController::class, 'monthly']);
        Route::get('reports/yearly', [ReportController::class, 'yearly']);

        Route::get('saw/restock-ranking', SawRecommendationController::class);
    });
});
