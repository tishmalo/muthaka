<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreNoteRequest;
use App\Services\Note\NoteService;
use Illuminate\Http\Request;
use Throwable;

class NoteController extends Controller
{
    public function __construct(private readonly NoteService $notes)
    {
    }

    public function store(StoreNoteRequest $request)
    {
        $data = $request->validated();

        try {
            $note = $this->notes->sendNote($request->user(), $data['content']);

            return ApiResponse::success(['note' => $note], 'Note sent successfully', 201);
        } catch (Throwable $e) {
            return ApiResponse::forbidden($e->getMessage());
        }
    }

    public function index(Request $request)
    {
        try {
            return ApiResponse::paginated($this->notes->getHistory($request->user(), (int) $request->query('per_page', 20)));
        } catch (Throwable $e) {
            return ApiResponse::forbidden($e->getMessage());
        }
    }

    public function unseen(Request $request)
    {
        return ApiResponse::success(['notes' => $this->notes->getUnseen($request->user())]);
    }

    public function markSeen(Request $request, string $noteId)
    {
        try {
            $this->notes->markSeen($request->user(), $noteId);

            return ApiResponse::success(null, 'Note marked as seen');
        } catch (Throwable $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }
}

