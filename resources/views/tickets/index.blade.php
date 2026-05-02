@extends('layouts.app')

@section('title', isset($showMineOnly) && $showMineOnly ? 'My logged tickets' : (auth()->user() && auth()->user()->role === \App\Models\User::ROLE_EMPLOYEE && !auth()->user()->isItStaff() && !auth()->user()->isAdmin() ? 'Tickets' : 'All tickets'))

@section('content')
    @php
        $canManageTicket = auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isItStaff());
        $isFrontDeskOnly = auth()->user() && auth()->user()->isFrontDesk() && !auth()->user()->isAdmin();
        $frontDeskUserId = $isFrontDeskOnly ? auth()->id() : null;
    @endphp
    <div class="tickets-page-card rounded-2xl border border-slate-200 bg-white shadow-sm overflow-visible dark:border-blue-500/40 dark:bg-[#0b1020]">
        @php
            $baseRouteParams = request()->except(['status', 'page']);
            $status = request('status', '');
            $isAdmin = auth()->user()?->isAdmin();
        @endphp
        <div class="tickets-page-header border-b border-slate-200 bg-transparent px-4 py-4 sm:px-6 dark:border-blue-500/20">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
                    <h1 class="text-xl font-semibold text-slate-900 sm:text-2xl dark:text-slate-50">{{ isset($showMineOnly) && $showMineOnly ? 'My logged tickets' : (auth()->user() && auth()->user()->role === \App\Models\User::ROLE_EMPLOYEE && !auth()->user()->isItStaff() && !auth()->user()->isAdmin() ? 'Tickets' : 'All tickets') }}</h1>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <form method="GET" action="{{ route('tickets.index') }}" class="flex flex-wrap items-center gap-2">
                        @foreach($baseRouteParams as $k => $v)
                            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                        @endforeach
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-slate-600 dark:text-slate-300">Status</span>
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-50 text-slate-700 border border-slate-200 dark:bg-white/5 dark:text-slate-200 dark:border-white/10 {{ $isAdmin ? 'dark:bg-blue-500/10 dark:text-blue-200 dark:border-blue-500/40' : '' }}" aria-hidden="true">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18l-7 8v6l-4 2v-8L3 4z"/>
                                </svg>
                            </span>
                        </div>

                        <div class="relative">
                            <select
                                name="status"
                                class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-blue-500/30 dark:bg-[#0b1020] dark:text-slate-100 {{ $isAdmin ? 'dark:border-blue-400/80 dark:focus:ring-blue-400/30' : '' }}"
                                onchange="this.form.submit()"
                                aria-label="Filter by status"
                            >
                                <option value="" {{ $status === '' ? 'selected' : '' }}>All</option>
                                <option value="open" {{ $status === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>In progress</option>
                                <option value="resolved" {{ $status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="closed" {{ $status === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                    </form>

                    @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isFrontDesk()))
                        <button type="button" class="ticket-create-modal-btn inline-flex cursor-pointer rounded-[0.9em] border-2 border-[#24b4fb] bg-[#24b4fb] px-4 py-3 text-base transition-all duration-200 ease-in-out hover:bg-[#0071e2] focus:outline-none focus:ring-2 focus:ring-[#24b4fb]/50 focus:ring-offset-1">
                            <span class="flex items-center justify-center gap-2 font-semibold text-white">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z" fill="currentColor"></path>
                                </svg>
                                Create ticket
                            </span>
                        </button>
                    @endif

                </div>
            </div>
        </div>

        @if(request()->filled('q') || request()->filled('status') || request()->filled('priority') || (isset($showMineOnly) && $showMineOnly))
        <div class="tickets-page-filters flex flex-wrap items-center gap-2 border-b border-slate-200 bg-transparent px-4 py-2 sm:px-6 dark:border-blue-500/20">
            <span class="text-sm text-slate-600 dark:text-slate-300">Filter:</span>
            @if(request()->filled('q'))
            <span class="inline-flex rounded-lg bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">Search: "{{ request('q') }}"</span>
            @endif
            @if(request()->filled('status'))
            <span class="badge-ticket badge-{{ request('status') }}">{{ ucfirst(str_replace('_', ' ', request('status'))) }}</span>
            @endif
            @if(request()->filled('priority'))
            <span class="inline-flex rounded-lg bg-slate-200 px-2.5 py-0.5 text-xs font-medium text-slate-700">{{ request('priority') }}</span>
            @endif
            @if(isset($showMineOnly) && $showMineOnly)
            <span class="inline-flex rounded-lg bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">My logged only</span>
            @endif
            <a href="{{ route('tickets.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Clear filter</a>
        </div>
        @endif

        <div class="p-4 sm:px-6 overflow-x-auto audit-trail-table-wrap tickets-table-scroll-wrap">
            <table id="tickets-table" class="display tickets-table-admin" style="width:100%">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Requester</th>
                        <th>Accepted by</th>
                        <th>Time accepted</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $canAccept = auth()->user() && (auth()->user()->role === \App\Models\User::ROLE_EMPLOYEE || auth()->user()->isAdmin());
                    @endphp
                    @forelse($tickets as $ticket)
                        @php
                            $u = auth()->user();
                            $canUpdateStatus = $u
                                && $ticket->assignee_id !== null
                                && $ticket->status !== \App\Models\Ticket::STATUS_OPEN
                                && (
                                    $u->isAdmin()
                                    || $u->isItStaff()
                                    || ($ticket->assignee_id === $u->id && in_array($u->role, [\App\Models\User::ROLE_EMPLOYEE, \App\Models\User::ROLE_IT_STAFF], true))
                                );
                        @endphp
                        <tr>
                            <td class="font-medium text-blue-600 dark:text-blue-400">{{ $ticket->ticket_number }}</td>
                            <td title="{{ $ticket->title }}">{{ $ticket->title }}</td>
                            <td>{{ $ticket->category->name }}</td>
                            <td>
                                @include('tickets.partials.priority-pill', ['priority' => $ticket->priority])
                            </td>
                            <td>
                                @include('tickets.partials.status-pill', ['status' => $ticket->status, 'label' => $ticket->statusLabel()])
                            </td>
                            <td title="{{ $ticket->requesterDisplay() }}">
                                <div class="inline-flex items-center gap-2">
                                    @if($ticket->submitter?->avatar_url)
                                        <img src="{{ $ticket->submitter->avatar_url }}?v={{ $ticket->submitter->updated_at?->timestamp ?? time() }}"
                                             alt=""
                                             class="h-7 w-7 rounded-full object-cover border border-slate-200 dark:border-slate-600">
                                    @else
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-600 text-[11px] font-semibold text-white">
                                            {{ strtoupper(substr($ticket->requesterDisplay(), 0, 1)) }}
                                        </span>
                                    @endif
                                    <span>{{ $ticket->requesterDisplay() }}</span>
                                </div>
                            </td>
                            @php
                                $acceptedUser = $ticket->acceptedBy ?: $ticket->assignee;
                                $acceptedName = $acceptedUser?->name ?? '—';
                            @endphp
                            <td title="{{ $acceptedName }}">
                                <div class="inline-flex items-center gap-2">
                                    @if($acceptedUser?->avatar_url)
                                        <img src="{{ $acceptedUser->avatar_url }}?v={{ $acceptedUser->updated_at?->timestamp ?? time() }}"
                                             alt=""
                                             class="h-7 w-7 rounded-full object-cover border border-slate-200 dark:border-slate-600">
                                    @else
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-600 text-[11px] font-semibold text-white">
                                            {{ $acceptedName !== '—' ? strtoupper(substr($acceptedName, 0, 1)) : '—' }}
                                        </span>
                                    @endif
                                    <span>{{ $acceptedName }}</span>
                                </div>
                            </td>
                            <td>{{ $ticket->assigned_at ? $ticket->assigned_at->format('M j, Y H:i') : '—' }}</td>
                            <td>{{ $ticket->created_at->format('M j, Y H:i') }}</td>
                            <td class="ticket-actions-td overflow-visible align-middle">
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

                                        @if($canManageTicket || ($isFrontDeskOnly && $ticket->submitter_id === $frontDeskUserId))
                                            <button type="button"
                                                    class="ticket-actions-radial-item ticket-edit-modal-btn inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-700 shadow-sm ring-1 ring-slate-200/80 hover:bg-slate-200 active:bg-slate-300 touch-manipulation dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-600 dark:hover:bg-slate-700"
                                                    data-url="{{ route('tickets.edit-modal', $ticket) }}"
                                                    title="Edit ticket"
                                                    aria-label="Edit ticket">
                                                <span class="sr-only">Edit ticket</span>
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </button>
                                        @endif

                                        @if($canUpdateStatus)
                                            <button type="button"
                                                    class="ticket-actions-radial-item ticket-status-modal-btn inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-700 shadow-sm ring-1 ring-amber-200/70 hover:bg-amber-100 active:bg-amber-200 touch-manipulation dark:bg-amber-950/60 dark:text-amber-200 dark:ring-amber-500/30 dark:hover:bg-amber-900/70"
                                                    data-url="{{ route('tickets.status-modal', $ticket) }}"
                                                    title="Update status"
                                                    aria-label="Update status">
                                                <span class="sr-only">Update status</span>
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11A9 9 0 1112 21v-3"/></svg>
                                            </button>
                                        @endif

                                        @if($canManageTicket && $ticket->status === \App\Models\Ticket::STATUS_RESOLVED)
                                            <form action="{{ route('tickets.close', $ticket) }}" method="post" class="ticket-actions-radial-item ajax-close-ticket-form m-0 inline-flex shrink-0 border-0 p-0" role="none">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-700 shadow-sm ring-1 ring-emerald-200/60 hover:bg-emerald-100 active:bg-emerald-200 touch-manipulation dark:bg-emerald-950/60 dark:text-emerald-200 dark:ring-emerald-500/30 dark:hover:bg-emerald-950/80"
                                                        title="Close ticket"
                                                        aria-label="Close ticket">
                                                    <span class="sr-only">Close ticket</span>
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/></svg>
                                                </button>
                                            </form>
                                        @endif

                                        @if(auth()->user()?->isAdmin())
                                            <button type="button"
                                                    class="ticket-actions-radial-item index-delete-ticket-btn inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-700 shadow-sm ring-1 ring-red-200/60 hover:bg-red-100 active:bg-red-200 touch-manipulation dark:bg-red-600 dark:text-white dark:ring-red-400/80 dark:hover:bg-red-500"
                                                    data-form-id="delete-form-{{ $ticket->id }}"
                                                    title="Delete"
                                                    aria-label="Delete ticket">
                                                <span class="sr-only">Delete</span>
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11v6"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 11v6"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16"/></svg>
                                            </button>
                                            <form id="delete-form-{{ $ticket->id }}" action="{{ route('tickets.destroy', $ticket) }}" method="post" class="hidden">
                                                @csrf
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
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-sm text-slate-500">
                                @if(isset($showMineOnly) && $showMineOnly)
                                    No tickets you logged yet. <a href="{{ route('tickets.create', ['source' => 'phone']) }}" class="font-medium text-blue-600 hover:text-blue-700" data-full-reload>Log ticket (phone)</a> or <a href="{{ route('tickets.create', ['source' => 'walk_in']) }}" class="font-medium text-blue-600 hover:text-blue-700" data-full-reload>walk-in</a>
                                @else
                                    No tickets yet. @if(auth()->user()->isAdmin())<a href="{{ route('tickets.create') }}" class="font-medium text-blue-600 hover:text-blue-700" data-full-reload>Create one</a>@endif
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-blue-500/20 bg-transparent px-4 py-3 sm:px-6">
            {{ $tickets->links() }}
        </div>
    </div>
@endsection

@push('modals')
@endpush

@push('scripts')
@endpush
