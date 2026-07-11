{{ $inviter->name }} invited you to connect on Muthaka.

Your invite code is {{ $inviteCode }}.
@if ($expiresAt)
This code expires on {{ $expiresAt }}.
@endif

