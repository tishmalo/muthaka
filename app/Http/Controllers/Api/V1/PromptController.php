<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Prompt\PromptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class PromptController extends Controller
{
    public function __construct(private readonly PromptService $prompts)
    {
    }

    public function daily(Request $request)
    {
        try {
            return ApiResponse::success(['prompt' => $this->prompts->daily($request->user())]);
        } catch (Throwable $e) {
            return ApiResponse::forbidden($e->getMessage());
        }
    }

    public function answer(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'answer' => 'required|string|max:5000',
            'reaction' => 'nullable|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        try {
            $answer = $this->prompts->answer($request->user(), $id, $request->answer, $request->reaction);

            return ApiResponse::success(['answer' => $answer], 'Prompt answered successfully', 201);
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function history(Request $request)
    {
        try {
            return ApiResponse::paginated($this->prompts->history($request->user(), (int) $request->query('per_page', 20)));
        } catch (Throwable $e) {
            return ApiResponse::forbidden($e->getMessage());
        }
    }
}
