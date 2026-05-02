<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\HelpMessage;
use App\Models\AccessRequest;
use App\Models\TicketComment;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Build the base query for role-aware notifications.
     *
     * Admin / IT / Front Desk: see all system events.
     * Employee: see system events only for tickets they submitted or are assigned to,
     *           limited to "new/open" style events.
     */
    private function notificationsQuery(User $user)
    {
        $query = TicketComment::query()
            ->where('type', TicketComment::TYPE_SYSTEM)
            ->with([
                'ticket:id,ticket_number,title,submitter_id,assignee_id',
                'user:id,name,role',
            ]);

        if (! $user->isAdmin() && ! $user->isItStaff() && ! $user->isFrontDesk()) {
            $query->whereHas('ticket', function ($q) use ($user) {
                $q->where('submitter_id', $user->id)
                    ->orWhere('assignee_id', $user->id);
            });

            $query->where(function ($q) {
                $q->where('body', 'like', 'Ticket created (%')
                    ->orWhere('body', 'like', 'Status changed from % to Open.%');
            });
        }

        return $query;
    }

    /**
     * Build the base query for login/logout events.
     *
     * Admin: see all login/logout events.
     * Others: see only their own events.
     */
    private function loginLogsQuery(User $user)
    {
        $query = LoginLog::query()->with('user:id,name,role');

        // Staff roles can monitor auth activity; employees see only their own.
        if (! $user->isAdmin() && ! $user->isFrontDesk() && ! $user->isItStaff()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    /**
     * Combine ticket system events + login/logout events into a single list.
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function buildUnifiedNotifications(User $user, ?string $category = null, int $limit = 150)
    {
        $category = $category ? strtolower($category) : null;
        $items = collect();

        if ($category === null || $category === 'tickets') {
            $ticketEvents = $this->notificationsQuery($user)
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            foreach ($ticketEvents as $note) {
                $items->push((object) [
                    'kind' => 'ticket',
                    'id' => $note->id,
                    'ticket' => $note->ticket,
                    'user' => $note->user,
                    'body' => $note->body,
                    'created_at' => $note->created_at,
                    'model' => $note,
                ]);
            }
        }

        if ($category === null || $category === 'auth') {
            $loginEvents = $this->loginLogsQuery($user)
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            foreach ($loginEvents as $log) {
                $label = $log->event === LoginLog::EVENT_LOGIN ? 'logged in' : 'logged out';
                $name = $log->user?->name ?? 'User';
                $body = "{$name} {$label}.";

                $items->push((object) [
                    'kind' => 'auth',
                    'id' => $log->id,
                    'ticket' => null,
                    'user' => $log->user,
                    'body' => $body,
                    'created_at' => $log->created_at,
                    'event' => $log->event,
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent,
                    'model' => $log,
                ]);
            }
        }

        // Future: system alerts category
        if ($category === null || $category === 'system') {
            if ($user->isAdmin() && Schema::hasTable('access_requests')) {
                $accessRequests = AccessRequest::query()
                    ->where('status', 'pending')
                    ->latest('created_at')
                    ->limit(50)
                    ->get();

                foreach ($accessRequests as $request) {
                    $items->push((object) [
                        'kind' => 'system',
                        'id' => 'access_request_' . $request->id,
                        'ticket' => null,
                        'user' => null,
                        'body' => 'Access request from ' . $request->email,
                        'created_at' => $request->created_at,
                        'event' => 'access_request',
                        'model' => $request,
                    ]);
                }
            }

            // Help/Message notifications: show only messages relevant to this user
            if (! Schema::hasTable('help_messages')) {
                return $items
                    ->sortByDesc(function ($x) { return $x->created_at; })
                    ->take($limit)
                    ->values();
            }

            $helpQuery = HelpMessage::query()
                ->with('sender:id,name,role')
                ->where('help_messages.created_at', '>', now()->subDays(30));

            if ($user->isAdmin()) {
                // Admin sees IT staff replies addressed to them
                $helpQuery->join('users as hs', 'hs.id', '=', 'help_messages.sender_id')
                          ->where('hs.role', User::ROLE_IT_STAFF)
                          ->where(function ($q) use ($user) {
                              $q->whereNull('help_messages.recipient_id')
                                ->orWhere('help_messages.recipient_id', $user->id);
                          })
                          ->select('help_messages.*')
                          ->orderByDesc('help_messages.created_at');
            } elseif ($user->isItStaff()) {
                // IT staff sees messages from non-IT-staff
                $helpQuery->join('users as hs', 'hs.id', '=', 'help_messages.sender_id')
                          ->whereNotIn('hs.role', [User::ROLE_IT_STAFF])
                          ->select('help_messages.*')
                          ->orderByDesc('help_messages.created_at');
            } else {
                // Employee/front desk sees replies addressed to them
                $helpQuery->where('help_messages.recipient_id', $user->id)
                          ->join('users as hs', 'hs.id', '=', 'help_messages.sender_id')
                          ->whereIn('hs.role', [User::ROLE_IT_STAFF, User::ROLE_ADMIN])
                          ->select('help_messages.*')
                          ->orderByDesc('help_messages.created_at');
            }

            $help = $helpQuery->limit(50)->get();
            foreach ($help as $msg) {
                $senderName = $msg->sender?->name ?? 'User';
                $items->push((object) [
                    'kind' => 'system',
                    'id' => $msg->id,
                    'ticket' => null,
                    'user' => $msg->sender,
                    'body' => 'Help message from ' . $senderName . ': ' . $msg->body,
                    'created_at' => $msg->created_at,
                    'event' => 'help_message',
                    'model' => $msg,
                ]);
            }
        }

        return $items
            ->sortByDesc(function ($x) { return $x->created_at; })
            ->take($limit)
            ->values();
    }

    /**
     * Show recent ticket activity backed by the audit trail.
     *
     * Admin / IT / Front Desk: see all system events.
     * Employee: see system events only for tickets they submitted or are assigned to.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $category = request()->query('category');
        $notifications = $this->buildUnifiedNotifications($user, $category, 150);

        return view('admin.notifications.index', compact('notifications', 'category'));
    }

    public function markSeen(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $user->last_notification_seen_at = now();
        $user->save();
        return response()->json(['ok' => true]);
    }

    /**
     * Lightweight endpoint for live header notifications (JSON).
     */
    public function header(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $isEmployee = $user->role === User::ROLE_EMPLOYEE && ! $user->isItStaff() && ! $user->isAdmin();
        $perPage = max(1, min(25, (int) request()->query('perPage', 10)));
        $page = max(1, (int) request()->query('page', 1));
        $offset = ($page - 1) * $perPage;
        $lastSeen = $user->last_notification_seen_at;
        $category = strtolower((string) request()->query('category', ''));

        // Employee users keep the open-queue experience for "All"/"Ticket events",
        // but category-specific tabs must use the unified notifications feed.
        if ($isEmployee && ($category === '' || $category === 'tickets')) {
            $openTicketsTotal = Ticket::query()
                ->where('status', Ticket::STATUS_OPEN)
                ->whereNull('assignee_id')
                ->count();

            $openTickets = Ticket::query()
                ->select('id', 'ticket_number', 'title', 'created_at')
                ->where('status', Ticket::STATUS_OPEN)
                ->whereNull('assignee_id')
                ->orderByDesc('created_at')
                ->skip($offset)
                ->limit($perPage)
                ->get();

            // Badge = new open tickets since last seen
            $badgeCount = $lastSeen
                ? Ticket::where('status', Ticket::STATUS_OPEN)->whereNull('assignee_id')->where('created_at', '>', $lastSeen)->count()
                : $openTicketsTotal;

            $badgeMode = 'open';
            $blockClear = $openTicketsTotal > 0;
            $hasMore = ($offset + $openTickets->count()) < $openTicketsTotal;

            $html = view('layouts.partials.header-open-tickets-list', [
                'openTickets' => $openTickets,
                'openTicketsShown' => $openTickets->count(),
                'openTicketsTotal' => $openTicketsTotal,
                'includeFooter' => $page === 1,
            ])->render();
        } else {
            // Fast paths for high-frequency tab switches.
            if ($category === 'tickets') {
                $base = $this->notificationsQuery($user);
                $total = (clone $base)->count();
                $rows = (clone $base)
                    ->orderByDesc('created_at')
                    ->skip($offset)
                    ->limit($perPage)
                    ->get();

                $headerNotifications = $rows->map(function (TicketComment $note) {
                    return (object) [
                        'kind' => 'ticket',
                        'id' => $note->id,
                        'ticket' => $note->ticket,
                        'user' => $note->user,
                        'body' => $note->body,
                        'created_at' => $note->created_at,
                        'model' => $note,
                    ];
                })->values();

                $badgeCount = $lastSeen
                    ? (clone $base)->where('created_at', '>', $lastSeen)->count()
                    : $total;
            } elseif ($category === 'auth') {
                $base = $this->loginLogsQuery($user);
                $total = (clone $base)->count();
                $rows = (clone $base)
                    ->orderByDesc('created_at')
                    ->skip($offset)
                    ->limit($perPage)
                    ->get();

                $headerNotifications = $rows->map(function (LoginLog $log) {
                    $label = $log->event === LoginLog::EVENT_LOGIN ? 'logged in' : 'logged out';
                    $name = $log->user?->name ?? 'User';
                    return (object) [
                        'kind' => 'auth',
                        'id' => $log->id,
                        'ticket' => null,
                        'user' => $log->user,
                        'body' => "{$name} {$label}.",
                        'created_at' => $log->created_at,
                        'event' => $log->event,
                        'ip_address' => $log->ip_address,
                        'user_agent' => $log->user_agent,
                        'model' => $log,
                    ];
                })->values();

                $badgeCount = $lastSeen
                    ? (clone $base)->where('created_at', '>', $lastSeen)->count()
                    : $total;
            } else {
                $all = $this->buildUnifiedNotifications($user, $category !== '' ? $category : null, 250);
                $total = $all->count();
                $headerNotifications = $all->slice($offset, $perPage)->values();

                // Badge = items newer than last seen
                $badgeCount = $lastSeen
                    ? $all->filter(fn($n) => $n->created_at > $lastSeen)->count()
                    : $total;
            }

            $badgeMode = 'notifications';
            $blockClear = false;
            $hasMore = ($offset + $headerNotifications->count()) < $total;

            $html = view('layouts.partials.header-notifications-list', [
                'headerNotifications' => $headerNotifications,
            ])->render();
        }

        return response()->json([
            'badgeMode'   => $badgeMode,
            'badgeCount'  => $badgeCount,
            'blockClear'  => $blockClear,
            'html'        => $html,
            'page'        => $page,
            'perPage'     => $perPage,
            'hasMore'     => $hasMore ?? false,
        ]);
    }

    /**
     * JSON endpoint to refresh the notifications table content without a reload.
     * Used by Reverb broadcast handler (and can also be used by polling fallback).
     */
    public function table(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $category = request()->query('category');
        $notifications = $this->buildUnifiedNotifications($user, $category, 150);

        $notificationsHtml = view('admin.notifications._rows', [
            'notifications' => $notifications,
        ])->render();

        return response()->json([
            'notificationsHtml' => $notificationsHtml,
            'count' => $notifications->count(),
        ]);
    }

    /**
     * Admin-only: edit a system notification (TicketComment) body.
     */
    public function update(Request $request, TicketComment $notification): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless($user && $user->isAdmin(), 403);

        if ($notification->type !== TicketComment::TYPE_SYSTEM) {
            return response()->json(['message' => 'Only system notifications can be edited.'], 422);
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $notification->body = $data['body'];
        $notification->save();

        return response()->json([
            'message' => 'Notification updated.',
            'id' => $notification->id,
        ]);
    }
}

