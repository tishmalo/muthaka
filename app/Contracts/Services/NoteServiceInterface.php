<?php

namespace App\Contracts\Services;

use App\Models\NoteEvent;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface NoteServiceInterface
{
    public function sendNote(User $user, string $content): NoteEvent;
    public function getHistory(User $user, int $limit = 20): LengthAwarePaginator;
    public function getUnseen(User $user): array;
    public function markSeen(User $user, string $noteId): void;
    public function getLatestForPartner(User $user): ?NoteEvent;
}