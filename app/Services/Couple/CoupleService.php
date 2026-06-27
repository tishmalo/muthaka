<?php

namespace App\Services\Couple;

use App\Contracts\Services\CoupleServiceInterface;
use App\Models\Couple;
use App\Models\CoupleInvite;
use App\Models\CoupleUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CoupleService implements CoupleServiceInterface
{
    public function createInvite(User $user, string $inviteePhone): array
    {
        // Check if user already has active couple
        if ($this->isInActiveCouple($user)) {
            throw new \Exception('You are already in a couple');
        }

        // Check if there's a pending invite
        $existingInvite = CoupleInvite::where('inviter_id', $user->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvite) {
            return [
                'invite_code' => $existingInvite->invite_code,
                'expires_at' => $existingInvite->expires_at,
            ];
        }

        $inviteCode = CoupleInvite::generateInviteCode();

        $invite = CoupleInvite::create([
            'inviter_id' => $user->id,
            'invitee_phone' => $inviteePhone,
            'invite_code' => $inviteCode,
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        return [
            'invite_code' => $invite->invite_code,
            'expires_at' => $invite->expires_at,
        ];
    }

    public function acceptInvite(User $user, string $inviteCode): Couple
    {
        $invite = CoupleInvite::where('invite_code', $inviteCode)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if (!$invite) {
            throw new \Exception('Invalid or expired invite code');
        }

        if ($invite->invitee_phone !== $user->phone_number) {
            throw new \Exception('This invite is not for you');
        }

        if ($this->isInActiveCouple($user)) {
            throw new \Exception('You are already in a couple');
        }

        $inviter = $invite->inviter;

        if ($this->isInActiveCouple($inviter)) {
            throw new \Exception('The inviter is already in a couple');
        }

        return DB::transaction(function () use ($user, $invite, $inviter) {
            // Create couple
            $couple = Couple::create([
                'partner_one_id' => $inviter->id,
                'partner_two_id' => $user->id,
                'status' => 'active',
                'connected_at' => now(),
            ]);

            // Create couple users
            CoupleUser::create([
                'couple_id' => $couple->id,
                'user_id' => $inviter->id,
                'role' => 'partner',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            CoupleUser::create([
                'couple_id' => $couple->id,
                'user_id' => $user->id,
                'role' => 'partner',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            // Update invite
            $invite->accept();

            // Create widget states
            // $this->widgetStateService->createForCouple($inviter);
            // $this->widgetStateService->createForCouple($user);

            return $couple;
        });
    }

    public function rejectInvite(User $user, string $inviteCode): void
    {
        $invite = CoupleInvite::where('invite_code', $inviteCode)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if (!$invite) {
            throw new \Exception('Invalid or expired invite code');
        }

        if ($invite->invitee_phone !== $user->phone_number) {
            throw new \Exception('This invite is not for you');
        }

        $invite->reject();
    }

    public function cancelInvite(User $user): void
    {
        $invite = CoupleInvite::where('inviter_id', $user->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if (!$invite) {
            throw new \Exception('No pending invite found');
        }

        $invite->update(['status' => 'canceled']);
    }

    public function disconnect(User $user, ?string $reason = null): void
    {
        $couple = $this->getActiveCouple($user);
        
        if (!$couple) {
            throw new \Exception('No active couple found');
        }

        DB::transaction(function () use ($couple, $reason) {
            $couple->disconnect($reason);
            
            // Update couple user statuses
            CoupleUser::where('couple_id', $couple->id)
                ->update([
                    'status' => 'left',
                    'left_at' => now(),
                ]);
        });
    }

    public function blockPartner(User $user): void
    {
        $couple = $this->getActiveCouple($user);
        
        if (!$couple) {
            throw new \Exception('No active couple found');
        }

        $couple->update(['status' => 'blocked']);
        
        // Update couple user statuses
        CoupleUser::where('couple_id', $couple->id)
            ->update([
                'status' => 'blocked',
                'left_at' => now(),
            ]);
    }

    public function getCoupleStatus(User $user): array
    {
        $couple = $this->getActiveCouple($user);
        
        if (!$couple) {
            return [
                'status' => 'none',
                'has_couple' => false,
            ];
        }

        $partner = $couple->getPartnerFor($user);

        return [
            'status' => $couple->status,
            'has_couple' => true,
            'couple_id' => $couple->id,
            'connected_at' => $couple->connected_at,
            'partner' => $partner ? [
                'id' => $partner->id,
                'name' => $partner->name,
                'avatar' => $partner->avatar,
                'online' => $partner->isOnline,
            ] : null,
        ];
    }

    public function getPartner(User $user): ?User
    {
        $couple = $this->getActiveCouple($user);
        return $couple ? $couple->getPartnerFor($user) : null;
    }

    public function isInActiveCouple(User $user): bool
    {
        return $this->getActiveCouple($user) !== null;
    }

    public function getActiveCouple(User $user): ?Couple
    {
        $coupleUser = CoupleUser::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('couple')
            ->first();

        return $coupleUser?->couple;
    }
}