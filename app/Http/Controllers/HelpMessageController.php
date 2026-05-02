<?php

namespace App\Http\Controllers;

use App\Events\HelpMessageSent;
use App\Models\HelpMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HelpMessageController extends Controller
{
    public function myMessages(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $messages = HelpMessage::query()
            ->with('sender:id,name,role')
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhere('recipient_id', $user->id);
            })
            ->orderBy('created_at')
            ->limit(100)
            ->get()
            ->map(fn ($m) => [
                'id'         => $m->id,
                'body'       => $m->body,
                'sender_id'  => $m->sender_id,
                'sender'     => $m->sender?->name ?? 'Support',
                'is_mine'    => $m->sender_id === $user->id,
                'created_at' => $m->created_at?->diffForHumans(),
            ]);
        $user->update(['last_help_read_at' => now()]);
        return response()->json(['messages' => $messages]);
    }

    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user->isAdmin() && ! $user->isFrontDesk() && ! ($user->role === User::ROLE_EMPLOYEE && ! $user->isItStaff())) {
            abort(403);
        }
        $data = $request->validate([
            'body'         => ['required', 'string', 'min:2', 'max:5000'],
            'recipient_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);
        $recipientId = null;
        if ($user->isAdmin() && ! empty($data['recipient_id'])) {
            $recipientId = $data['recipient_id'];
        }
        $message = HelpMessage::create([
            'sender_id'    => $user->id,
            'recipient_id' => $recipientId,
            'body'         => $data['body'],
        ]);
        try {
            $message->refresh()->load('sender:id,name,role');
            broadcast(new HelpMessageSent($message));
        } catch (\Throwable $e) {}
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('success', 'Message sent.');
    }

    public function compact(): Response
    {
        $user = Auth::user();
        if (! $user->isAdmin() && ! $user->isFrontDesk() && ! ($user->role === User::ROLE_EMPLOYEE && ! $user->isItStaff())) {
            abort(403);
        }
        return response()->view('help.partials.compact', [], 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function modal(): Response
    {
        $user = Auth::user();
        if (! $user->isAdmin() && ! $user->isFrontDesk() && ! ($user->role === User::ROLE_EMPLOYEE && ! $user->isItStaff())) {
            abort(403);
        }
        $messages = HelpMessage::query()
            ->with('sender:id,name,role')
            ->where('sender_id', $user->id)
            ->orderBy('created_at')
            ->limit(200)
            ->get();
        return response()->view('help.partials.modal', compact('messages'), 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function itInbox(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user->isItStaff()) abort(403);
        $messages = HelpMessage::query()
            ->with('sender:id,name,role')
            ->whereHas('sender', fn ($q) => $q->where('role', User::ROLE_ADMIN))
            ->orderBy('created_at')
            ->limit(200)
            ->get()
            ->map(fn ($m) => [
                'id'         => $m->id,
                'body'       => $m->body,
                'sender'     => $m->sender?->name ?? 'Admin',
                'created_at' => $m->created_at?->format('M j, Y g:i A'),
                'is_new'     => $user->last_help_read_at === null || $m->created_at > $user->last_help_read_at,
            ]);
        return response()->json(['messages' => $messages]);
    }

    public function itMarkRead(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user->isItStaff()) abort(403);
        $user->update(['last_help_read_at' => now()]);
        return response()->json(['ok' => true]);
    }

    public function itUnreadCount(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user->isItStaff()) abort(403);
        $query = HelpMessage::query()
            ->join('users as s', 's.id', '=', 'help_messages.sender_id')
            ->whereNotIn('s.role', [User::ROLE_IT_STAFF]);
        if ($user->last_help_read_at) {
            $query->where('help_messages.created_at', '>', $user->last_help_read_at);
        }
        return response()->json(['count' => $query->count()]);
    }

    public function conversation(): Response
    {
        $user = Auth::user();
        if ($user->isAdmin()) {
            $messages = HelpMessage::query()
                ->with('sender:id,name,role', 'recipient:id,name,role')
                ->orderBy('created_at')
                ->limit(200)
                ->get();
            $senders = User::query()
                ->whereIn('id', HelpMessage::query()->select('sender_id'))
                ->where('role', '!=', User::ROLE_ADMIN)
                ->orderBy('name')
                ->get(['id', 'name', 'role']);
            $user->update(['last_help_read_at' => now()]);
            return response()->view('help.partials.conversation', compact('messages', 'senders'), 200, ['Content-Type' => 'text/html; charset=UTF-8']);
        }
        if ($user->isItStaff()) {
            $messages = HelpMessage::query()
                ->with('sender:id,name,role')
                ->orderBy('created_at')
                ->limit(200)
                ->get();
        } else {
            $messages = HelpMessage::query()
                ->with('sender:id,name,role')
                ->where(function ($q) use ($user) {
                    $q->where('sender_id', $user->id)
                      ->orWhere(function ($q2) use ($user) {
                          $q2->where('recipient_id', $user->id)
                             ->whereHas('sender', fn ($s) => $s->whereIn('role', [User::ROLE_IT_STAFF, User::ROLE_ADMIN]));
                      });
                })
                ->orderBy('created_at')
                ->limit(200)
                ->get();
        }
        $user->update(['last_help_read_at' => now()]);
        return response()->view('help.partials.conversation', compact('messages'), 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function unreadCount(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $query = HelpMessage::query()
            ->join('users as s', 's.id', '=', 'help_messages.sender_id');

        if ($user->isAdmin()) {
            // Admin sees replies from IT staff addressed to them
            $query->where('s.role', User::ROLE_IT_STAFF)
                  ->where(function ($q) use ($user) {
                      $q->whereNull('help_messages.recipient_id')
                        ->orWhere('help_messages.recipient_id', $user->id);
                  });
        } elseif ($user->isItStaff()) {
            $query->whereNotIn('s.role', [User::ROLE_IT_STAFF]);
        } else {
            $query->where('help_messages.recipient_id', $user->id)
                  ->whereIn('s.role', [User::ROLE_IT_STAFF, User::ROLE_ADMIN]);
        }

        if ($user->last_help_read_at) {
            $query->where('help_messages.created_at', '>', $user->last_help_read_at);
        }

        return response()->json(['count' => $query->count()]);
    }

    public function markRead(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $user->update(['last_help_read_at' => now()]);
        return response()->json(['ok' => true]);
    }

    public function adminConversation(): Response
    {
        $user = Auth::user();
        if (! $user->isAdmin()) abort(403);
        $messages = HelpMessage::query()
            ->with('sender:id,name,role')
            ->orderBy('created_at')
            ->limit(200)
            ->get();
        return response()->view('help.partials.admin-conversation', compact('messages'), 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function adminSent(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user->isAdmin()) abort(403);
        $messages = HelpMessage::query()
            ->where('sender_id', $user->id)
            ->orderBy('created_at')
            ->limit(200)
            ->get()
            ->map(fn ($m) => [
                'id'         => $m->id,
                'body'       => $m->body,
                'created_at' => $m->created_at?->format('M j, Y g:i A'),
            ]);
        return response()->json(['messages' => $messages]);
    }

    // IT Staff: list threads (one per sender)
    public function itThreads(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user->isItStaff()) abort(403);

        // Single query: latest message time + body per thread (non-IT-staff senders)
        $latest = HelpMessage::query()
            ->selectRaw('hm.sender_id, hm.created_at as last_at, hm.body as last_body')
            ->from('help_messages as hm')
            ->join('users as u', 'u.id', '=', 'hm.sender_id')
            ->whereNotIn('u.role', [User::ROLE_IT_STAFF])
            ->whereRaw('hm.created_at = (
                SELECT MAX(hm2.created_at) FROM help_messages hm2
                WHERE hm2.sender_id = hm.sender_id OR hm2.recipient_id = hm.sender_id
            )')
            ->get()
            ->keyBy('sender_id');

        if ($latest->isEmpty()) {
            return response()->json(['threads' => []]);
        }

        // Single query: unread counts
        $unreadCounts = HelpMessage::query()
            ->selectRaw('sender_id, COUNT(*) as cnt')
            ->whereIn('sender_id', $latest->keys())
            ->when($user->last_help_read_at, fn ($q) => $q->where('created_at', '>', $user->last_help_read_at))
            ->groupBy('sender_id')
            ->pluck('cnt', 'sender_id');

        $threads = User::query()
            ->whereIn('id', $latest->keys())
            ->get(['id', 'name', 'role'])
            ->map(function ($u) use ($latest, $unreadCounts) {
                $meta = $latest->get($u->id);
                return [
                    'sender_id'    => $u->id,
                    'name'         => $u->name,
                    'role'         => $u->role,
                    'last_at'      => $meta ? \Carbon\Carbon::parse($meta->last_at)->format('M j, g:i A') : '',
                    'unread'       => (int) ($unreadCounts->get($u->id) ?? 0),
                    'last_message' => $meta ? \Illuminate\Support\Str::limit($meta->last_body ?? '', 50) : '',
                ];
            })
            ->sortByDesc(fn ($t) => $latest->get($t['sender_id'])?->last_at ?? '')
            ->values();

        return response()->json(['threads' => $threads]);
    }

    public function itThread(User $userId): \Illuminate\Http\JsonResponse
    {
        $authUser = Auth::user();
        if (! $authUser->isItStaff()) abort(403);

        $messages = HelpMessage::query()
            ->with('sender:id,name,role')
            ->where(function ($q) use ($userId) {
                // Messages from this user
                $q->where('sender_id', $userId->id)
                  // Replies targeted specifically to this user
                  ->orWhere('recipient_id', $userId->id)
                  // Broadcast messages from admin/IT staff (recipient_id = null) — visible in all threads
                  ->orWhere(function ($q2) use ($userId) {
                      $q2->whereNull('recipient_id')
                         ->where('sender_id', '!=', $userId->id)
                         ->whereHas('sender', fn ($s) => $s->whereIn('role', [User::ROLE_IT_STAFF, User::ROLE_ADMIN]));
                  });
            })
            ->orderBy('created_at')
            ->limit(200)
            ->get()
            ->map(fn ($m) => [
                'id'         => $m->id,
                'body'       => $m->body,
                'sender_id'  => $m->sender_id,
                'sender'     => $m->sender?->name ?? 'Unknown',
                'created_at' => $m->created_at?->format('M j, Y g:i A'),
            ]);

        return response()->json(['messages' => $messages]);
    }

    public function itReply(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user->isItStaff()) abort(403);
        $data = $request->validate([
            'body'         => ['required', 'string', 'min:1', 'max:5000'],
            'recipient_id' => ['required', 'integer', 'exists:users,id'],
        ]);
        $message = HelpMessage::create([
            'sender_id'    => $user->id,
            'recipient_id' => $data['recipient_id'],
            'body'         => $data['body'],
        ]);
        try {
            $message->refresh()->load('sender:id,name,role');
            broadcast(new HelpMessageSent($message));
        } catch (\Throwable $e) {}
        return response()->json(['ok' => true]);
    }
}