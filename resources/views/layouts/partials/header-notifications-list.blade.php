@forelse($headerNotifications as $note)
    @if($note->ticket)
    <button type="button"
       class="ticket-quick-view-btn header-notification-item w-full text-left flex flex-col gap-0.5 px-3.5 py-2 text-sm text-slate-700 hover:bg-slate-50/90 dark:text-slate-100 dark:hover:bg-slate-700/80"
       data-url="{{ route('tickets.modal', $note->ticket) }}">
    @elseif($note->event === 'help_message')
    <button type="button"
       class="header-notification-item header-help-msg-btn w-full text-left flex flex-col gap-0.5 px-3.5 py-2 text-sm text-slate-700 hover:bg-slate-50/90 dark:text-slate-100 dark:hover:bg-slate-700/80"
       data-sender-id="{{ $note->model->sender_id ?? '' }}"
       data-sender-name="{{ $note->user?->name ?? 'User' }}">
    @else
    <div class="header-notification-item flex flex-col gap-0.5 px-3.5 py-2 text-sm text-slate-700 dark:text-slate-100">
    @endif
        <div class="flex items-center justify-between gap-2">
            <span class="font-medium">
                @if($note->ticket)
                    {{ $note->ticket->ticket_number }}
                @elseif($note->event === 'help_message')
                    <span class="inline-flex items-center gap-1">
                        <svg class="h-3.5 w-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z"/></svg>
                        {{ $note->user?->name ?? 'User' }}
                    </span>
                @else
                    System
                @endif
            </span>
            <span class="shrink-0 text-[11px] text-slate-500 dark:text-slate-400">
                {{ $note->created_at->diffForHumans(null, null, true) }}
            </span>
        </div>
        <div class="mt-0.5 flex items-center justify-between gap-2 text-[11px] text-slate-500 dark:text-slate-400">
            <span class="inline-flex items-center gap-1">
                <span class="inline-flex h-1.5 w-1.5 rounded-full header-notification-dot"></span>
                <span class="header-notification-status-label">Unread</span>
            </span>
        </div>
        <div class="text-xs text-slate-500 dark:text-slate-300">
            {{ \Illuminate\Support\Str::limit($note->body, 80) }}
        </div>
    @if($note->ticket)
    </button>
    @elseif($note->event === 'help_message')
    </button>
    @else
    </div>
    @endif
@empty
    <div class="px-3.5 py-3 text-xs text-slate-500 dark:text-slate-300">
        No notifications yet.
    </div>
@endforelse

