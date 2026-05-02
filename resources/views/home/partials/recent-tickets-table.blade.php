<div class="recent-tickets-card rounded-2xl border border-slate-200 bg-white shadow-sm overflow-visible">
    <div class="recent-tickets-header border-b border-slate-200 bg-slate-50/80 px-4 py-4 sm:px-6 flex items-center justify-between">
        <h2 class="recent-tickets-title text-lg font-semibold text-slate-900">{{ $title ?? 'Recent tickets' }}</h2>
        <a href="{{ $viewAllUrl ?? route('tickets.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">{{ $viewAllText ?? 'View all →' }}</a>
    </div>
    <div class="overflow-x-auto">
        @php $displayTickets = isset($tickets) ? $tickets : ($recentTickets ?? collect()); @endphp
        @if($displayTickets->isNotEmpty())
        <table id="recent-tickets-datatable" class="recent-tickets-table display min-w-full divide-y divide-slate-200" style="width:100%">
            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Category</th>
                    <th>Status</th>
                    @if(!empty($showSubmitter))
                    <th>Submitted by</th>
                    @endif
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($displayTickets as $ticket)
                <tr>
                    <td>
                        <span class="font-medium text-slate-900">#{{ $ticket->ticket_number }}</span>
                        <span class="block text-sm text-slate-500 truncate max-w-[12rem]">{{ $ticket->title }}</span>
                    </td>
                    <td>{{ $ticket->category->name ?? '—' }}</td>
                    <td>
                        @include('tickets.partials.status-pill', [
                            'status' => $ticket->status,
                            'label' => $ticket->statusLabel(),
                        ])
                    </td>
                    @if(!empty($showSubmitter))
                    <td>{{ $ticket->submitter->name ?? '—' }}</td>
                    @endif
                    <td class="ticket-actions-td overflow-visible align-middle">
                        @php
                            $canAccept = auth()->user() && (auth()->user()->role === \App\Models\User::ROLE_EMPLOYEE || auth()->user()->isAdmin());
                        @endphp
                        <div class="ticket-actions-menu relative inline-flex justify-end">
                            <div id="ticket-act-inline-{{ $ticket->id }}" class="ticket-actions-radial-shell ticket-actions-inline pointer-events-none invisible absolute bottom-full left-1/2 z-[2147483000] mb-0 w-max max-w-[calc(100vw-2rem)] -translate-x-1/2 opacity-0 origin-bottom transition-opacity duration-300 ease-out" role="group" aria-label="Ticket actions">
                                <div class="ticket-actions-radial-track relative min-h-[3rem] min-w-[3rem]">
                                <button type="button"
                                        class="ticket-actions-radial-item ticket-quick-view-btn inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-200/60 hover:bg-blue-100 active:bg-blue-200 touch-manipulation dark:bg-blue-950/60 dark:text-blue-200 dark:ring-blue-500/30 dark:hover:bg-blue-950/80"
                                        data-url="{{ route('tickets.modal', $ticket) }}"
                                        title="View details"
                                        aria-label="View details">
                                    <span class="sr-only">View details</span>
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </button>
                                @php
                                    $mapsSearchQuery = filled($ticket->location)
                                        ? $ticket->location
                                        : trim($ticket->ticket_number . ' ' . $ticket->title);
                                @endphp
                                <a href="https://www.google.com/maps/search/?api=1&amp;query={{ rawurlencode($mapsSearchQuery) }}"
                                   class="ticket-actions-radial-item ticket-actions-maps-link inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full touch-manipulation hover:brightness-95 active:brightness-90"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   title="{{ filled($ticket->location) ? 'Open location in Google Maps' : 'Search Google Maps (ticket — add address in Edit for an exact place)' }}"
                                   aria-label="Open Google Maps for this ticket">
                                    <span class="sr-only">Google Maps</span>
                                    <svg class="ticket-maps-pin-svg h-[1.15rem] w-[1.15rem] shrink-0 overflow-visible" viewBox="0 0 24 24" aria-hidden="true"><path class="maps-pin-body" fill="#c5221f" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle class="maps-pin-dot" fill="#ffffff" cx="12" cy="9" r="1.35"/></svg>
                                </a>
                                @if($canAccept && $ticket->status === 'open' && $ticket->assignee_id === null)
                                    <form action="{{ route('tickets.accept', $ticket) }}" method="post" class="ticket-actions-radial-item ajax-accept-ticket-form m-0 inline-flex shrink-0 border-0 p-0" role="none">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-700 shadow-sm ring-1 ring-emerald-200/60 hover:bg-emerald-100 active:bg-emerald-200 touch-manipulation dark:bg-emerald-950/60 dark:text-emerald-200 dark:ring-emerald-500/30 dark:hover:bg-emerald-950/80"
                                                title="Accept ticket"
                                                aria-label="Accept ticket">
                                            <span class="sr-only">Accept ticket</span>
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    </form>
                                @endif
                                </div>
                            </div>
                            <button type="button"
                                    class="ticket-actions-menu-trigger relative inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-slate-200/90 bg-gradient-to-b from-white to-slate-50 text-slate-700 shadow-md shadow-slate-900/10 ring-1 ring-slate-200/60 ring-inset ring-white/50 hover:border-slate-300 hover:from-slate-50 hover:to-slate-100 hover:text-slate-900 active:scale-[0.96] touch-manipulation dark:border-slate-600 dark:from-slate-700 dark:to-slate-800 dark:text-slate-100 dark:shadow-black/25 dark:ring-white/10 dark:hover:from-slate-600 dark:hover:to-slate-800"
                                    aria-expanded="false"
                                    aria-controls="ticket-act-inline-{{ $ticket->id }}"
                                    title="Actions"
                                    aria-label="Open or close ticket actions">
                                <svg class="ticket-actions-menu-icon-dots h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A1.5 1.5 0 015.5 4h4A1.5 1.5 0 0111 5.5v4A1.5 1.5 0 019.5 11h-4A1.5 1.5 0 014 9.5v-4zM13 4h6a1 1 0 011 1v5a1 1 0 01-1 1h-6a1 1 0 01-1-1V5a1 1 0 011-1zM4 14.5A1.5 1.5 0 015.5 13h4a1.5 1.5 0 011.5 1.5V19A1.5 1.5 0 019.5 20h-4A1.5 1.5 0 014 18.5v-4zM14 13h6a1 1 0 011 1v6a1 1 0 01-1 1h-6a1 1 0 01-1-1v-6a1 1 0 011-1z"/></svg>
                                <svg class="ticket-actions-menu-icon-collapse hidden h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 8l8 8M16 8l-8 8"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="px-4 py-12 text-center text-slate-500 sm:px-6">
            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="mt-2">No tickets yet.</p>
            @if(isset($emptyActionUrl) && $emptyActionUrl)
            <a href="{{ $emptyActionUrl }}" class="mt-3 inline-block text-sm font-medium text-blue-600 hover:text-blue-700" data-full-reload>{{ $emptyActionText ?? 'Create first ticket →' }}</a>
            @elseif(!(auth()->user()->role === \App\Models\User::ROLE_EMPLOYEE && !auth()->user()->isItStaff() && !auth()->user()->isAdmin()))
            <a href="{{ route('tickets.create') }}" class="mt-3 inline-block text-sm font-medium text-blue-600 hover:text-blue-700" data-full-reload>Create first ticket →</a>
            @endif
        </div>
        @endif
    </div>
</div>
