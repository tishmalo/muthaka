<?php

use App\Models\CoupleInvite;
use App\Models\Snap;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tuko:cleanup-expired-invites', function () {
    $count = CoupleInvite::where('status', 'pending')
        ->where('expires_at', '<=', now())
        ->update(['status' => 'expired']);

    $this->info("Expired {$count} invite(s).");
})->purpose('Mark expired couple invites');

Artisan::command('tuko:cleanup-expired-snaps', function () {
    $count = 0;

    Snap::where('expires_at', '<=', now())->chunkById(100, function ($snaps) use (&$count) {
        foreach ($snaps as $snap) {
            Storage::disk('local')->delete(array_filter([$snap->image_path, $snap->thumbnail_path]));
            $snap->delete();
            $count++;
        }
    });

    $this->info("Deleted {$count} expired snap(s).");
})->purpose('Delete expired snaps and media files');
