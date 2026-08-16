<?php

namespace App\Http\Controllers\Api;

use App\Application\UseCases\Saw\CalculateRestockRanking;
use App\Http\Controllers\Controller;
use App\Http\Requests\SawRankingRequest;
use App\Http\Resources\SawRecommendationResource;
use Illuminate\Http\JsonResponse;

class SawRecommendationController extends Controller
{
    public function __invoke(SawRankingRequest $request, CalculateRestockRanking $useCase): JsonResponse
    {
        $ranking = $useCase->execute($request->date('start_date'), $request->date('end_date'));

        return response()->json([
            'meta' => [
                'weights' => $useCase->weights(),
                'start_date' => ($request->date('start_date') ?? now()->subDays(30))->toDateString(),
                'end_date' => ($request->date('end_date') ?? now())->toDateString(),
            ],
            'data' => SawRecommendationResource::collection($ranking),
        ]);
    }
}
