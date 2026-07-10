<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\MoodType;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Mood\MoodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class MoodController extends Controller
{
    public function __construct(private readonly MoodService $moods)
    {
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mood_type' => ['required', 'string', Rule::in(array_column(MoodType::cases(), 'value'))],
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        try {
            $mood = $this->moods->sendMood($request->user(), $request->mood_type, $request->notes);

            return ApiResponse::success(['mood' => $mood], 'Mood sent successfully', 201);
        } catch (Throwable $e) {
            return ApiResponse::forbidden($e->getMessage());
        }
    }

    public function index(Request $request)
    {
        try {
            return ApiResponse::paginated($this->moods->getHistory($request->user(), (int) $request->query('per_page', 20)));
        } catch (Throwable $e) {
            return ApiResponse::forbidden($e->getMessage());
        }
    }

    public function unseen(Request $request)
    {
        return ApiResponse::success(['moods' => $this->moods->getUnseen($request->user())]);
    }

    public function markSeen(Request $request)
    {
        $this->moods->markSeen($request->user());

        return ApiResponse::success(null, 'Moods marked as seen');
    }
}
