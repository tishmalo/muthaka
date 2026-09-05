<?php

use App\Models\CoupleUser;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('couple.{id}', function ($user, $id) {
    return CoupleUser::where('user_id', $user->id)
        ->where('couple_id', $id)
        ->where('status', 'active')
        ->exists();
});
