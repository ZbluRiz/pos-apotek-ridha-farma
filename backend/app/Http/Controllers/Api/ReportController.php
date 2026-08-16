<?php

namespace App\Http\Controllers\Api;

use App\Application\UseCases\Reports\GenerateSalesReport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(private readonly GenerateSalesReport $useCase) {}

    public function daily(ReportRequest $request): JsonResponse
    {
        $date = $request->date('date') ?? now();

        return response()->json([
            'data' => $this->useCase->daily($date),
        ]);
    }

    public function monthly(ReportRequest $request): JsonResponse
    {
        $month = $request->integer('month', now()->month);
        $year = $request->integer('year', now()->year);

        return response()->json([
            'data' => $this->useCase->monthly($month, $year),
        ]);
    }

    public function yearly(ReportRequest $request): JsonResponse
    {
        $year = $request->integer('year', now()->year);

        return response()->json([
            'data' => $this->useCase->yearly($year),
        ]);
    }
}
