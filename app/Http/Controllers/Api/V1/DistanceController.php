<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreDistanceRequest;
use App\Services\Distance\DistanceService;
use Illuminate\Http\Request;
use Throwable;

class DistanceController extends Controller
{
    public function __construct(private readonly DistanceService $distance)
    {
    }

    public function store(StoreDistanceRequest $request)
    {
        try {
            $event = $this->distance->updateLocation($request->user(), $request->validated());

            return ApiResponse::success(['distance' => $event], 'Location updated successfully', 201);
        } catch (Throwable $e) {
            return ApiResponse::forbidden($e->getMessage());
        }
    }

    public function show(Request $request)
    {
        try {
            return ApiResponse::success(['distance' => $this->distance->current($request->user())]);
        } catch (Throwable $e) {
            return ApiResponse::forbidden($e->getMessage());
        }
    }

    public function history(Request $request)
    {
        try {
            return ApiResponse::paginated($this->distance->history($request->user(), (int) $request->query('per_page', 20)));
        } catch (Throwable $e) {
            return ApiResponse::forbidden($e->getMessage());
        }
    }
}

