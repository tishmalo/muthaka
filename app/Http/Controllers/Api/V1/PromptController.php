<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AnswerPromptRequest;
use App\Services\Prompt\PromptService;
use Illuminate\Http\Request;
use Throwable;

class PromptController extends Controller
{
    public function __construct(private readonly PromptService $prompts)
    {
    }

public function daily(Request $request)
    {
        try {
            return ApiResponse::success($this->prompts->daily($request->user()));
        } catch (Throwable $e) {
            return ApiResponse::forbidden($e->getMessage());
        }
    }

    public function answer(AnswerPromptRequest $request, int $id)
    {
        $data = $request->validated();

        try {
            $answer = $this->prompts->answer($request->user(), $id, $data['answer'], $data['reaction'] ?? null);

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

