<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Distance\DistanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class DistanceController extends Controller
{
    public function __construct(private readonly DistanceService $distance)
    {
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'place_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'is_sharing' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        try {
            $event = $this->distance->updateLocation($request->user(), $validator->validated());

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
