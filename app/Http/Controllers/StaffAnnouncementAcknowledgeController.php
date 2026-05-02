<?php

namespace App\Http\Controllers;

use App\Events\TicketsUpdated;
use App\Models\StaffAnnouncement;
use App\Models\StaffAnnouncementAck;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffAnnouncementAcknowledgeController extends Controller
{
    public function __invoke(Request $request, StaffAnnouncement $announcement): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        // Only intended audience (or admin) can acknowledge
        $isTargetUser = $announcement->audience === StaffAnnouncement::AUDIENCE_SELECTED_USERS
            ? $announcement->targetUsers()->where('users.id', $user->id)->exists()
            : in_array($announcement->audience, ['all', $user->role], true);

        if (! $isTargetUser && ! $user->isAdmin()) {
            abort(403);
        }

        // Record who acknowledged it
        StaffAnnouncementAck::firstOrCreate(
            [
                'staff_announcement_id' => $announcement->id,
                'user_id' => $user->id,
            ],
            [
                'acknowledged_at' => now(),
            ]
        );

        $expectedCount = (int) $announcement->expectedAudienceUsers()->count();
        $ackCount = (int) $announcement->acknowledgements()->count();

        if ($expectedCount > 0 && $ackCount >= $expectedCount && $announcement->status !== StaffAnnouncement::STATUS_ACKNOWLEDGED) {
            $announcement->status = StaffAnnouncement::STATUS_ACKNOWLEDGED;
            $announcement->acknowledged_at = now();
            $announcement->save();
        }

        Cache::put('tickets_list_updated_at', now()->timestamp, 3600);
        try {
            event(new TicketsUpdated('Staff announcement acknowledged.'));
        } catch (\Throwable $e) {
            // Do not block acknowledgement when broadcaster is unreachable.
        }

        $message = 'Marked admin message as done. Admin will see everything is good.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }
}

