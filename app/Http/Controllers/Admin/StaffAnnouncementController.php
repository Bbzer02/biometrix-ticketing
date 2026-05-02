<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Events\TicketsUpdated;
use App\Models\StaffAnnouncement;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffAnnouncementController extends Controller
{
    public function index(): View
    {
        $announcements = StaffAnnouncement::with(['acknowledgements.user', 'creator', 'targetUsers'])
            ->withCount('acknowledgements')
            ->latest()
            ->get();

        $audienceTotals = [];
        $audienceUsers = [];
        foreach ($announcements as $announcement) {
            $users = $announcement->expectedAudienceUsers()
                ->orderBy('name')
                ->pluck('name');
            $audienceTotals[$announcement->id] = $users->count();
            $audienceUsers[$announcement->id] = $users;
        }

        $audiences = [
            'all'                  => 'All staff',
            StaffAnnouncement::AUDIENCE_SELECTED_USERS => 'Selected users',
            User::ROLE_EMPLOYEE    => 'Employees',
            User::ROLE_FRONT_DESK  => 'Front Desk',
            User::ROLE_IT_STAFF    => 'IT Staff',
        ];

        $priorities = [
            'low'      => 'Low – minor request',
            'normal'   => 'Normal – workaround available',
            'major'    => 'Major – business impacted',
            'critical' => 'Critical – service down',
        ];

        return view('admin.staff-announcements.index', compact('announcements', 'audienceTotals', 'audienceUsers', 'audiences', 'priorities'));
    }

    public function create(): View
    {
        $audiences = [
            'all' => 'All staff',
            StaffAnnouncement::AUDIENCE_SELECTED_USERS => 'Selected users',
            User::ROLE_EMPLOYEE => 'Employees',
            User::ROLE_FRONT_DESK => 'Front Desk',
            User::ROLE_IT_STAFF => 'IT Staff',
        ];
        $selectableUsers = User::query()
            ->where('role', '!=', User::ROLE_ADMIN)
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        $priorities = [
            'low' => 'Low – minor request',
            'normal' => 'Normal – workaround available',
            'major' => 'Major – business impacted',
            'critical' => 'Critical – service down',
        ];

        return view('admin.staff-announcements.create', compact('audiences', 'priorities', 'selectableUsers'));
    }

    public function store(Request $request): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:2000',
            'audience' => 'required|in:all,' . StaffAnnouncement::AUDIENCE_SELECTED_USERS . ',' . implode(',', [
                User::ROLE_EMPLOYEE,
                User::ROLE_FRONT_DESK,
                User::ROLE_IT_STAFF,
            ]),
            'priority' => 'required|in:low,normal,major,critical',
            'selected_user_ids' => ['required_if:audience,' . StaffAnnouncement::AUDIENCE_SELECTED_USERS, 'array', 'min:1'],
            'selected_user_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where(function ($q) {
                    $q->where('role', '!=', User::ROLE_ADMIN);
                }),
            ],
        ]);

        $validated['created_by'] = $request->user()->id;

        $announcement = StaffAnnouncement::create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'audience' => $validated['audience'],
            'priority' => $validated['priority'],
            'created_by' => $validated['created_by'],
        ]);

        if (($validated['audience'] ?? null) === StaffAnnouncement::AUDIENCE_SELECTED_USERS) {
            $announcement->targetUsers()->sync(array_values(array_unique($validated['selected_user_ids'] ?? [])));
        }
        $this->broadcastAnnouncementSync('Staff announcement sent.');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => 'Staff announcement sent.']);
        }

        return redirect()->route('admin.staff-announcements.index')
            ->with('success', 'Staff announcement sent.');
    }

    public function destroy(StaffAnnouncement $staffAnnouncement): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $staffAnnouncement->delete();
        $this->broadcastAnnouncementSync('Staff announcement deleted.');

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['message' => 'Announcement deleted.']);
        }

        return redirect()->route('admin.staff-announcements.index')->with('success', 'Announcement deleted.');
    }

    private function broadcastAnnouncementSync(string $message = 'Staff announcements updated'): void
    {
        Cache::put('tickets_list_updated_at', now()->timestamp, 3600);
        try {
            event(new TicketsUpdated($message));
        } catch (\Throwable $e) {
            // Keep announcement CRUD flow working even if broadcasting is unavailable.
        }
    }
}

