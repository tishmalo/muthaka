<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreDoodleRequest;
use App\Services\Media\DoodleService;
use Illuminate\Http\Request;
use Throwable;

class DoodleController extends Controller
{
    public function __construct(private readonly DoodleService $doodles)
    {
    }

    public function store(StoreDoodleRequest $request)
    {
        try {
            $doodle = $this->doodles->send($request->user(), $request->file('image'), $request->validated());

            return ApiResponse::success(['doodle' => $doodle], 'Doodle sent successfully', 201);
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), null, $e->getCode() >= 400 ? $e->getCode() : 400);
        }
    }

    public function index(Request $request)
    {
        try {
            return ApiResponse::paginated($this->doodles->history($request->user(), (int) $request->query('per_page', 20)));
        } catch (Throwable $e) {
            return ApiResponse::forbidden($e->getMessage());
        }
    }

    public function unseen(Request $request)
    {
        return ApiResponse::success(['doodles' => $this->doodles->unseen($request->user())]);
    }

    public function markSeen(Request $request, string $id)
    {
        try {
            $this->doodles->markSeen($request->user(), $id);

            return ApiResponse::success(null, 'Doodle marked as seen');
        } catch (Throwable $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /** Streams the raw image bytes (media disk is private — no public URL exists). */
    public function image(Request $request, string $id)
    {
        try {
            $path = $this->doodles->resolveImage($request->user(), $id);
        } catch (Throwable) {
            $path = null;
        }

        if (!$path) {
            return ApiResponse::notFound('Image not found');
        }

        return response()->file($path);
    }
}

