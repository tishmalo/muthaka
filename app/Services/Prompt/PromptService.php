<?php

namespace App\Services\Prompt;

use App\Events\CoupleUpdated;
use App\Models\Prompt;
use App\Models\PromptAnswer;
use App\Models\User;
use App\Services\Support\CoupleContextService;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use RuntimeException;

class PromptService
{
    public function __construct(private readonly CoupleContextService $couples)
    {
    }

    public function daily(User $user): ?Prompt
    {
        $this->couples->requireActiveCouple($user);

        return Prompt::active()
            ->daily()
            ->where(function ($query) {
                $query->whereDate('scheduled_date', today())
                    ->orWhereNull('scheduled_date');
            })
            ->orderByRaw('scheduled_date IS NULL')
            ->oldest('id')
            ->first();
    }

    public function answer(User $user, int $promptId, string $answer, ?int $reaction = null): PromptAnswer
    {
        $couple = $this->couples->requireActiveCouple($user);
        $prompt = Prompt::active()->find($promptId);

        if (!$prompt) {
            throw new RuntimeException('Prompt not found');
        }

        try {
            $answer = PromptAnswer::create([
                'prompt_id' => $prompt->id,
                'couple_id' => $couple->id,
                'user_id' => $user->id,
                'answer' => $answer,
                'reaction' => $reaction,
                'answered_at' => now(),
            ]);

            broadcast(new CoupleUpdated($couple->id, 'prompt'));

            return $answer;
        } catch (QueryException $e) {
            throw new RuntimeException('Prompt already answered');
        }
    }

    public function history(User $user, int $limit = 20): LengthAwarePaginator
    {
        $couple = $this->couples->requireActiveCouple($user);

        return PromptAnswer::where('couple_id', $couple->id)
            ->with(['prompt', 'user:id,name,avatar'])
            ->latest('answered_at')
            ->paginate(min(max($limit, 1), 100));
    }
}
