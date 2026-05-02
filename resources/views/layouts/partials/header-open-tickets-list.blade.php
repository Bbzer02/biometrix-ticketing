@php
    $shown = isset($openTicketsShown) ? (int) $openTicketsShown : (isset($openTickets) ? $openTickets->count() : 0);
    $total = isset($openTicketsTotal) ? (int) $openTicketsTotal : $shown;
    $includeFooter = isset($includeFooter) ? (bool) $includeFooter : true;
@endphp

@forelse($openTickets as $ticket)
    <button type="button"
       class="ticket-quick-view-btn header-notification-item w-full text-left flex flex-col gap-0.5 px-3.5 py-2 text-sm text-slate-700 hover:bg-slate-50/90 dark:text-slate-100 dark:hover:bg-slate-700/80"
       data-url="{{ route('tickets.modal', $ticket) }}">
        <div class="flex items-center justify-between gap-2">
            <span class="font-medium">{{ $ticket->ticket_number }}</span>
            <span class="shrink-0 text-[11px] text-slate-500 dark:text-slate-400">
                {{ $ticket->created_at->diffForHumans(null, null, true) }}
            </span>
        </div>
        <div class="mt-0.5 flex items-center justify-between gap-2 text-[11px] text-slate-500 dark:text-slate-400">
            <span class="inline-flex items-center gap-1">
                <span class="inline-flex h-1.5 w-1.5 rounded-full header-notification-dot"></span>
                <span class="header-notification-status-label">Open</span>
            </span>
        </div>
        <div class="text-xs text-slate-500 dark:text-slate-300">
            {{ \Illuminate\Support\Str::limit($ticket->title, 80) }}
        </div>
    </button>
@empty
    <div class="px-3.5 py-3 text-xs text-slate-500 dark:text-slate-300">
        No open tickets right now.
    </div>
@endforelse

@if($includeFooter && $total > $shown)
    <div class="px-3.5 pt-1 pb-2 text-[11px] text-slate-500 dark:text-slate-400">
        Showing {{ $shown }} of {{ $total }} open tickets.
    </div>
@endif

