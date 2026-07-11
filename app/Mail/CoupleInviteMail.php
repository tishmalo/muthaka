<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CoupleInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $inviter,
        public readonly string $inviteCode,
        public readonly ?string $expiresAt,
    ) {
    }

    public function build(): self
    {
        return $this->subject("{$this->inviter->name} invited you to Muthaka")
            ->text('emails.couple-invite');
    }
}

