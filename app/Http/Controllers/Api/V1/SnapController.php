<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSnapRequest;
use App\Services\Media\SnapService;
use Illuminate\Http\Request;
use Throwable;

class SnapController extends Controller
{
    public function __construct(private readonly SnapService $snaps)
    {
    }

    public function store(StoreSnapRequest $request)
    {
        try {
            $snap = $this->snaps->send($request->user(), $request->file('image'), $request->validated());

            return ApiResponse::success(['snap' => $snap], 'Snap sent successfully', 201);
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function index(Request $request)
    {
        try {
            return ApiResponse::paginated($this->snaps->history($request->user(), (int) $request->query('per_page', 20)));
        } catch (Throwable $e) {
            return ApiResponse::forbidden($e->getMessage());
        }
    }

    public function unseen(Request $request)
    {
        return ApiResponse::success(['snaps' => $this->snaps->unseen($request->user())]);
    }

    public function view(Request $request, string $id)
    {
        try {
            return ApiResponse::success(['snap' => $this->snaps->view($request->user(), $id)]);
        } catch (Throwable $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
            $this->snaps->delete($request->user(), $id);

            return ApiResponse::success(null, 'Snap deleted successfully');
        } catch (Throwable $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }
}

