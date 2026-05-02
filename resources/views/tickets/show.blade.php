@extends('layouts.app')

@section('title', $ticket->ticket_number)

@section('content')
    <div class="space-y-6">
        {{-- Back + header --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-4 flex flex-wrap items-center gap-3">
                <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back
                </a>
                @php
                    $canManageTicket = auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isItStaff() || auth()->user()->isFrontDesk());
                @endphp
                @if($canManageTicket)
                    <a href="{{ route('tickets.edit', $ticket) }}"
                       class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
                       aria-label="Edit"
                       title="Edit">
                        <span class="sr-only">Edit ticket</span>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>
                    <button type="button" id="delete-ticket-btn"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-red-200 bg-red-50 text-red-700 shadow-sm transition-colors hover:bg-red-100"
                            aria-label="Delete"
                            title="Delete">
                        <span class="sr-only">Delete ticket</span>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11v6"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 11v6"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16"/></svg>
                    </button>
                @endif
                @php
                    $canAccept = auth()->user() && (auth()->user()->role === \App\Models\User::ROLE_EMPLOYEE || auth()->user()->isAdmin()) && $ticket->status === 'open' && $ticket->assignee_id === null;
                @endphp
                @if($canAccept)
                    <form action="{{ route('tickets.accept', $ticket) }}" method="post" class="inline ajax-accept-ticket-form">
                        @csrf
                        <button type="submit"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 active:bg-emerald-200 touch-manipulation"
                                aria-label="Accept"
                                title="Accept">
                            <span class="sr-only">Accept</span>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </button>
                    </form>
                @endif
            </div>
            <h1 class="text-xl font-semibold text-slate-900 sm:text-2xl">{{ $ticket->ticket_number }} — {{ $ticket->title }}</h1>
            <div class="mt-4 flex flex-wrap items-center gap-2 gap-y-1">
                @include('tickets.partials.status-pill', ['status' => $ticket->status, 'label' => $ticket->statusLabel()])
                @include('tickets.partials.priority-pill', ['priority' => $ticket->priority])
                <span class="text-sm text-slate-500">Category: <strong class="text-slate-700">{{ $ticket->category->name }}</strong></span>
                <span class="text-sm text-slate-500">Source: <strong class="text-slate-700">{{ $ticket->source === 'self_service' ? 'System' : 'Call-logged' }}</strong></span>
            </div>
            <dl class="mt-4 grid gap-2 text-sm sm:grid-cols-2">
                <div><dt class="font-medium text-slate-500">Requester</dt><dd class="text-slate-900">{{ $ticket->requesterDisplay() }}</dd></div>
                @if($ticket->requester_phone)
                    <div><dt class="font-medium text-slate-500">Contact number</dt><dd class="text-slate-900">{{ $ticket->requester_phone }}</dd></div>
                @endif
                <div><dt class="font-medium text-slate-500">Created</dt><dd class="text-slate-900">{{ $ticket->created_at->format('M j, Y H:i') }}</dd></div>
                @if($ticket->scheduled_for)
                    <div><dt class="font-medium text-slate-500">Scheduled for</dt><dd class="text-slate-900">{{ $ticket->scheduled_for->format('M j, Y H:i') }}</dd></div>
                @endif
                @if($ticket->location)
                    @php
                        $encodedLocation = urlencode($ticket->location);
                        $officeOrigin = urlencode('Biometrix Systems & Trading Corp., Consolacion, Cebu');
                    @endphp
                    <div class="sm:col-span-2">
                        <dt class="font-medium text-slate-500">Location</dt>
                        <dd class="text-slate-900">
                            {{ $ticket->location }}
                            <span class="mt-1 block space-x-3 text-sm">
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $encodedLocation }}"
                                   target="_blank" rel="noopener"
                                   class="font-medium text-blue-600 hover:text-blue-800">
                                    Open in Google Maps
                                </a>
                                <a href="https://www.google.com/maps/dir/?api=1&origin={{ $officeOrigin }}&destination={{ $encodedLocation }}"
                                   target="_blank" rel="noopener"
                                   class="font-medium text-blue-600 hover:text-blue-800">
                                    Get directions
                                </a>
                            </span>
                        </dd>
                    </div>
                @endif
                @if($ticket->assignee)
                    <div><dt class="font-medium text-slate-500">Assigned to</dt><dd class="text-slate-900">{{ $ticket->assignee->name }}</dd></div>
                @endif
                @if(auth()->user()?->isAdmin() && ($ticket->acceptedBy || $ticket->assignee))
                    <div><dt class="font-medium text-slate-500">Accepted by</dt><dd class="text-slate-900">{{ $ticket->acceptedBy?->name ?? $ticket->assignee?->name ?? '—' }}</dd></div>
                @endif
            </dl>

            {{-- Assignee quick actions: Mark as Done / Cancel (when in progress) --}}
            @if($ticket->status === 'in_progress' && auth()->id() && $ticket->assignee_id === auth()->id())
                <div class="mt-6 border-t border-slate-200 pt-6">
                    <h2 class="mb-3 text-base font-semibold text-slate-900">Update ticket</h2>
                    <div class="flex flex-wrap items-center gap-3">
                        <form action="{{ route('tickets.update', $ticket) }}" method="post" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="{{ \App\Models\Ticket::STATUS_RESOLVED }}">
                            <button type="submit" class="cursor-pointer transition-all bg-gray-700 text-white px-5 py-2 rounded-lg border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold">
                                Mark as Done
                            </button>
                        </form>
                        <form id="cancel-ticket-form" action="{{ route('tickets.update', $ticket) }}" method="post" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="{{ \App\Models\Ticket::STATUS_CANCELLED }}">
                            <button type="submit" class="cursor-pointer transition-all bg-slate-500 text-white px-5 py-2 rounded-lg border-slate-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] text-sm font-semibold">
                                Cancel ticket
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Quick close (Resolved -> Closed) for IT Staff / Admin --}}
            @if($ticket->status === \App\Models\Ticket::STATUS_RESOLVED && (auth()->user()?->isItStaff() || auth()->user()?->isAdmin()))
                <div class="mt-6 border-t border-slate-200 pt-6">
                    <h2 class="mb-3 text-base font-semibold text-slate-900">Finalize ticket</h2>
                    <div class="flex flex-wrap items-center gap-3">
                        <form action="{{ route('tickets.update', $ticket) }}" method="post" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="{{ \App\Models\Ticket::STATUS_CLOSED }}">
                            <button type="submit" class="cursor-pointer transition-all bg-gray-700 text-white px-5 py-2 rounded-lg border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold">
                                Close ticket
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Status workflow (IT Staff / Admin only) --}}
            @if($ticket->status !== 'closed' && $ticket->status !== 'cancelled' && (auth()->user()?->isItStaff() || auth()->user()?->isAdmin()))
                <div class="mt-6 border-t border-slate-200 pt-6">
                    <h2 class="mb-3 text-base font-semibold text-slate-900">Update status</h2>
                    <form action="{{ route('tickets.update', $ticket) }}" method="post" class="max-w-md space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="status" class="label-ticket">Status</label>
                            <select name="status" id="status" required class="input-ticket">
                                @foreach(\App\Models\Ticket::statusLabels() as $value => $label)
                                    <option value="{{ $value }}" {{ $ticket->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="resolution_notes" class="label-ticket">Resolution notes (optional)</label>
                            <textarea name="resolution_notes" id="resolution_notes" rows="3" placeholder="How it was resolved..." class="input-ticket min-h-[80px] resize-y">{{ old('resolution_notes', $ticket->resolution_notes) }}</textarea>
                        </div>
                        <button
                            type="submit"
                            class="cursor-pointer transition-all bg-gray-700 text-white px-6 py-2 rounded-lg border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold"
                        >
                            Update status
                        </button>
                    </form>
                </div>
            @endif

            <div class="mt-6 border-t border-slate-200 pt-6">
                <h2 class="mb-2 text-base font-semibold text-slate-900">Description</h2>
                <p class="whitespace-pre-wrap text-slate-700">{{ $ticket->description }}</p>
            </div>
            @if($ticket->resolution_notes && $ticket->status !== 'closed')
                <div class="mt-4">
                    <h2 class="mb-2 text-base font-semibold text-slate-900">Resolution notes</h2>
                    <p class="whitespace-pre-wrap text-slate-700">{{ $ticket->resolution_notes }}</p>
                </div>
            @endif
        </div>

        {{-- Comments & history --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="mb-4 text-base font-semibold text-slate-900">Comments & history</h2>

            <form action="{{ route('tickets.comments.store', $ticket) }}" method="post" class="mb-6">
                @csrf
                <label for="comment_body" class="label-ticket">Add a comment</label>
                <textarea name="body" id="comment_body" rows="3" required placeholder="Type your comment..." class="input-ticket mt-1 min-h-[80px] resize-y"></textarea>
                @error('body')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                <button type="submit" class="mt-3 cursor-pointer transition-all bg-gray-700 text-white px-6 py-2 rounded-lg border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold">
                    Post comment
                </button>
            </form>

            <ul class="divide-y divide-slate-200">
                @forelse($ticket->comments as $comment)
                    <li class="py-4 first:pt-0">
                        <div class="flex flex-wrap items-center gap-2 gap-y-0.5">
                            <span class="font-medium text-slate-900">{{ $comment->authorDisplay() }}</span>
                            @if($comment->type === 'system')
                                <span class="badge-ticket badge-in_progress text-[10px]">history</span>
                            @endif
                            <span class="text-xs text-slate-500">{{ $comment->created_at->format('M j, Y H:i') }}</span>
                        </div>
                        <p class="mt-1 whitespace-pre-wrap text-sm text-slate-700">{{ $comment->body }}</p>
                    </li>
                @empty
                    <li class="py-4 text-sm text-slate-500">No comments yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Cancel ticket: swift popup (this page only, does not affect layout/sidebar) --}}
    <div id="cancel-ticket-modal" class="fixed inset-0 z-[9998] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm hidden" aria-modal="true" role="dialog" aria-labelledby="cancel-ticket-modal-title">
        <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white shadow-2xl p-6 animate-in fade-in zoom-in duration-200" style="animation: cancelTicketFadeIn 0.2s ease-out;">
            <h2 id="cancel-ticket-modal-title" class="text-lg font-semibold text-slate-900">Cancel this ticket?</h2>
            <p class="mt-2 text-sm text-slate-600">This will mark the ticket as cancelled. You can’t undo this.</p>
            <div class="mt-5 flex flex-wrap gap-3 justify-end">
                <button type="button" id="cancel-ticket-modal-keep" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Keep ticket
                </button>
                <button type="button" id="cancel-ticket-modal-confirm" class="inline-flex items-center justify-center rounded-xl bg-slate-600 px-4 py-2.5 text-sm font-medium !text-white hover:bg-slate-700">
                    Yes, cancel ticket
                </button>
            </div>
        </div>
    </div>
    <style>
        @keyframes cancelTicketFadeIn { from { opacity: 0; transform: scale(0.96); } to { opacity: 1; transform: scale(1); } }
    </style>
    @if($canManageTicket ?? false)
    <div id="delete-ticket-modal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm hidden" aria-modal="true" role="dialog" aria-labelledby="delete-ticket-modal-title">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white shadow-2xl p-6">
            <h2 id="delete-ticket-modal-title" class="text-lg font-semibold text-slate-900">Delete this ticket?</h2>
            <p class="mt-2 text-sm text-slate-600">This will permanently remove the ticket. Enter an admin password to confirm.</p>
            <form id="delete-ticket-form" action="{{ route('tickets.destroy', $ticket) }}" method="post" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label for="admin_password" class="block text-sm font-medium text-slate-700">Admin password</label>
                    <input type="password" name="admin_password" id="admin_password" required autocomplete="current-password" placeholder="Enter admin password" class="mt-1 block w-full rounded-xl border border-slate-300 py-2 px-3 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="flex flex-wrap gap-3 justify-end pt-2">
                    <button type="button" id="delete-ticket-modal-cancel" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700">Delete ticket</button>
                </div>
            </form>
        </div>
    </div>
    @endif
    <script>
        (function() {
            var form = document.getElementById('cancel-ticket-form');
            var modal = document.getElementById('cancel-ticket-modal');
            var btnKeep = document.getElementById('cancel-ticket-modal-keep');
            var btnConfirm = document.getElementById('cancel-ticket-modal-confirm');
            if (!form || !modal) return;
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                modal.classList.remove('hidden');
            });
            function closeModal() { modal.classList.add('hidden'); }
            btnKeep.addEventListener('click', closeModal);
            btnConfirm.addEventListener('click', function() {
                closeModal();
                form.submit();
            });
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeModal();
            });
        })();
        (function() {
            var btn = document.getElementById('delete-ticket-btn');
            var modal = document.getElementById('delete-ticket-modal');
            var cancelBtn = document.getElementById('delete-ticket-modal-cancel');
            if (!btn || !modal) return;
            btn.addEventListener('click', function() {
                modal.classList.remove('hidden');
                document.getElementById('admin_password')?.focus();
            });
            cancelBtn.addEventListener('click', function() {
                modal.classList.add('hidden');
            });
            modal.addEventListener('click', function(e) {
                if (e.target === modal) modal.classList.add('hidden');
            });
        })();
    </script>
@endsection
