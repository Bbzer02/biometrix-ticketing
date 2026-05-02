@forelse($notifications as $notification)
    <tr class="notification-row notification-row-unread" data-read="0">
        <td>
            @if(($notification->ticket ?? null))
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-2 w-2 rounded-full notification-status-dot"></span>
                    <div>
                        <a href="{{ route('tickets.modal', $notification->ticket) }}"
                           class="ticket-quick-view-btn font-medium text-blue-600 hover:text-blue-700"
                           data-url="{{ route('tickets.modal', $notification->ticket) }}"
                           onclick="event.preventDefault()">
                            {{ $notification->ticket->ticket_number }}
                        </a>
                        <div class="text-xs text-slate-500">
                            {{ \Illuminate\Support\Str::limit($notification->ticket->title, 60) }}
                        </div>
                    </div>
                </div>
            @else
                <span class="text-xs text-slate-500">—</span>
            @endif
        </td>
        <td>
            @php
                $kind = $notification->kind ?? 'ticket';
                $body = (string) ($notification->body ?? '');
            @endphp
            @if ($kind === 'auth')
                @if (($notification->event ?? null) === \App\Models\LoginLog::EVENT_LOGIN)
                    Login
                @elseif (($notification->event ?? null) === \App\Models\LoginLog::EVENT_LOGOUT)
                    Logout
                @else
                    Auth
                @endif
            @elseif (str_contains($body, 'Ticket created'))
                Created
            @elseif (str_contains($body, 'accepted'))
                Accepted
            @elseif (str_contains($body, 'Status changed'))
                Status update
            @elseif (str_contains($body, 'cancelled') || str_contains($body, 'canceled'))
                Cancelled
            @elseif (str_contains($body, 'closed'))
                Closed
            @else
                System
            @endif
        </td>
        <td>
            @if(($notification->kind ?? 'ticket') === 'ticket' && isset($notification->model) && method_exists($notification->model, 'authorDisplay'))
                {{ $notification->model->authorDisplay() }}
            @else
                {{ $notification->user?->name ?? 'System' }}
            @endif
            @if($notification->user)
                <span class="block text-xs text-slate-500">
                    {{ $notification->user->getRoleLabel() }}
                </span>
            @endif
        </td>
        <td>{{ $notification->created_at->format('M j, Y g:i A') }}</td>
        <td>{{ \Illuminate\Support\Str::limit($notification->body, 120) }}</td>
        <td class="notifications-actions-cell">
            @if(($notification->ticket ?? null))
                <div class="flex items-center gap-1">
                    <button type="button"
                            class="ticket-quick-view-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600 transition-colors"
                            title="View ticket"
                            data-url="{{ route('tickets.modal', $notification->ticket) }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <button type="button"
                                class="notification-edit-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600 transition-colors"
                                title="Edit notification"
                                data-notification='@json(["id" => $notification->id, "body" => $notification->body])'>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                    @endif
                    <button type="button"
                            class="notification-delete-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:border-red-300 hover:bg-red-50 hover:text-red-600 transition-colors"
                            title="Delete notification">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            @else
                @php
                    $viewPayload = [
                        'id'         => $notification->id,
                        'body'       => $notification->body,
                        'kind'       => $notification->kind ?? 'system',
                        'created_at' => $notification->created_at->format('M j, Y g:i A'),
                        'user'       => $notification->user?->name ?? 'System',
                    ];
                @endphp
                <div class="flex items-center gap-1">
                    <button type="button"
                            class="notification-view-detail-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600 transition-colors"
                            title="View details"
                            data-notification-view='@json($viewPayload)'>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="px-4 py-8 text-center text-slate-500">
            No notifications yet.
        </td>
    </tr>
@endforelse

