<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\CountdownServiceInterface;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCountdownRequest;
use App\Http\Requests\Api\V1\UpdateCountdownRequest;
use Illuminate\Http\Request;
use Throwable;

class CountdownController extends Controller
{
    public function __construct(private readonly CountdownServiceInterface $countdowns) {}

    public function store(StoreCountdownRequest $request)
    {
        try {
            $countdown = $this->countdowns->createCountdown($request->user(), $request->validated());

            return ApiResponse::success(['countdown' => $countdown], 'Countdown created successfully', 201);
        } catch (Throwable $e) {
            return ApiResponse::forbidden($e->getMessage());
        }
    }

    public function index(Request $request)
    {
        try {
            return ApiResponse::success([
                'countdowns' => $this->countdowns->listCountdowns($request->user()),
            ]);
        } catch (Throwable $e) {
            return ApiResponse::forbidden($e->getMessage());
        }
    }

    public function active(Request $request)
    {
        try {
            return ApiResponse::success([
                'countdowns' => $this->countdowns->listActiveCountdowns($request->user()),
            ]);
        } catch (Throwable $e) {
            return ApiResponse::forbidden($e->getMessage());
        }
    }

    public function update(UpdateCountdownRequest $request, string $id)
    {
        $countdown = $this->countdowns->updateCountdown($request->user(), $id, $request->validated());

        if (! $countdown) {
            return ApiResponse::notFound('Countdown not found');
        }

        return ApiResponse::success(['countdown' => $countdown], 'Countdown updated successfully');
    }

    public function destroy(Request $request, string $id)
    {
        if (! $this->countdowns->deleteCountdown($request->user(), $id)) {
            return ApiResponse::notFound('Countdown not found');
        }

        return ApiResponse::success(null, 'Countdown deleted successfully');
    }
}
