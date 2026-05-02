<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserAuditController extends Controller
{
    /**
     * Display the audit trail index: all users (DataTables).
     */
    public function index(): View
    {
        $users = User::query()
            ->select('id', 'name', 'email', 'role')
            ->orderBy('name')
            ->get();

        return view('admin.audit-trail.index', compact('users'));
    }

    /**
     * Show a user's ticket activity (DataTables or JSON for modal).
     */
    public function show(User $user, \Illuminate\Http\Request $request): View|\Illuminate\Http\JsonResponse
    {
        $entries = $this->buildAuditEntries($user, 300);

        if ($request->expectsJson() || $request->ajax() || $request->boolean('ajax')) {
            return response()->json([
                'user' => ['name' => $user->name, 'email' => $user->email],
                'download_url' => route('admin.audit-trail.download', $user),
                'print_url'    => route('admin.audit-trail.print', $user),
                'entries' => $entries,
            ]);
        }

        return view('admin.audit-trail.show', compact('user', 'entries'));
    }

    /**
     * Download ticket activity as HTML (looks like DataTables, printable).
     */
    public function download(User $user): Response
    {
        $entries = $this->buildAuditEntries($user, 1000);
        $html = view('admin.audit-trail.download-html', compact('user', 'entries'))->render();
        $filename = sprintf('audit-%s-%s-%s.html', $user->id, Str::slug($user->name), now()->format('Y-m-d'));

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Open ticket activity in new tab for printing (same layout, no file download).
     */
    public function print(User $user): Response
    {
        $entries = $this->buildAuditEntries($user, 1000);

        return response()->view('admin.audit-trail.download-html', compact('user', 'entries'), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    private function buildAuditEntries(User $user, int $limit): Collection
    {
        $ticketEntries = TicketComment::where('user_id', $user->id)
            ->with('ticket:id,ticket_number,title')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function (TicketComment $comment) {
                $type = 'System';
                $badge = 'slate';
                if ($comment->type === TicketComment::TYPE_SYSTEM) {
                    if (str_contains($comment->body, 'Ticket created')) {
                        $type = 'Created';
                        $badge = 'blue';
                    } elseif (str_contains($comment->body, 'accepted')) {
                        $type = 'Accepted';
                        $badge = 'emerald';
                    } elseif (str_contains($comment->body, 'Status changed')) {
                        $type = 'Status update';
                        $badge = 'amber';
                    }
                } else {
                    $type = 'Comment';
                    $badge = 'indigo';
                }

                return [
                    'ticket_number' => $comment->ticket?->ticket_number,
                    'ticket_title' => $comment->ticket?->title,
                    'ticket_url' => $comment->ticket ? route('tickets.show', $comment->ticket) : null,
                    'type' => $type,
                    'badge' => $badge,
                    'details' => $comment->body,
                    'date' => $comment->created_at,
                ];
            });

        $authEntries = LoginLog::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function (LoginLog $log) {
                $isLogin = $log->event === LoginLog::EVENT_LOGIN;

                return [
                    'ticket_number' => null,
                    'ticket_title' => null,
                    'ticket_url' => null,
                    'type' => $isLogin ? 'Login' : 'Logout',
                    'badge' => $isLogin ? 'cyan' : 'rose',
                    'details' => sprintf(
                        '%s from IP %s',
                        $isLogin ? 'Signed in' : 'Signed out',
                        $log->ip_address ?: 'unknown'
                    ),
                    'date' => $log->created_at,
                ];
            });

        return $ticketEntries
            ->concat($authEntries)
            ->sortByDesc('date')
            ->take($limit)
            ->values()
            ->map(function (array $entry) {
                $entry['date_formatted'] = optional($entry['date'])->format('M j, Y g:i A');
                return $entry;
            });
    }
}
