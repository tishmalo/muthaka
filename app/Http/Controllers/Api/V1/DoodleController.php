<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Media\DoodleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class DoodleController extends Controller
{
    public function __construct(private readonly DoodleService $doodles)
    {
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|file',
            'duration' => 'nullable|integer|min:1|max:3600',
            'stroke_count' => 'nullable|integer|min:0',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        try {
            $doodle = $this->doodles->send($request->user(), $request->file('image'), $validator->validated());

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
}
