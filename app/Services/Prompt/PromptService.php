<?php

namespace App\Services\Prompt;

use App\Broadcasting\CoupleBroadcaster;
use App\Models\Couple;
use App\Models\Prompt;
use App\Models\PromptAnswer;
use App\Models\User;
use App\Services\Support\CoupleContextService;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use RuntimeException;

class PromptService
{
    public function __construct(
        private readonly CoupleContextService $couples,
        private readonly CoupleBroadcaster $broadcast,
    ) {}

    public function daily(User $user): array
    {
        $couple = $this->couples->requireActiveCouple($user);

        $prompt = Prompt::active()
            ->daily()
            ->where(function ($query) {
                $query->whereDate('scheduled_date', today())
                    ->orWhereNull('scheduled_date');
            })
            ->orderByRaw('scheduled_date IS NULL')
            ->oldest('id')
            ->first();

        return [
            'prompt' => $prompt,
            'streak_days' => $this->streakDays($couple),
        ];
    }

    public function answer(User $user, int $promptId, string $answer, ?int $reaction = null): PromptAnswer
    {
        $couple = $this->couples->requireActiveCouple($user);
        $prompt = Prompt::active()->find($promptId);

        if (! $prompt) {
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

            $this->broadcast->updated($couple->id, 'prompt');

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

    /**
     * Couple-wide prompt streak: consecutive calendar days (ending today, or
     * yesterday so the streak isn't lost before today's answer) on which the
     * couple recorded at least one answer.
     */
    private function streakDays(Couple $couple): int
    {
        $days = PromptAnswer::where('couple_id', $couple->id)
            ->selectRaw('DISTINCT DATE(answered_at) as day')
            ->orderByDesc('day')
            ->pluck('day')
            ->map(fn ($day) => (string) $day)
            ->all();

        if ($days === []) {
            return 0;
        }

        $newest = new CarbonImmutable($days[0]);
        $today = CarbonImmutable::today();
        if (! $newest->isSameDay($today) && ! $newest->isSameDay($today->subDay())) {
            return 0;
        }

        $streak = 1;
        for ($i = 1; $i < count($days); $i++) {
            $prev = new CarbonImmutable($days[$i - 1]);
            $current = new CarbonImmutable($days[$i]);
            if ($prev->subDay()->isSameDay($current)) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }
}
