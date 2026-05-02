@php
    $user = auth()->user();
@endphp

<div class="space-y-5 rounded-xl border border-blue-800/60 bg-[#0b1020] p-4 sm:p-5">
    <div class="space-y-3">
        <h2 class="text-xl font-semibold leading-tight text-slate-100">{{ $ticket->ticket_number }} — {{ $ticket->title }}</h2>
        <div class="flex flex-wrap items-center gap-2">
            @include('tickets.partials.status-pill', ['status' => $ticket->status, 'label' => $ticket->statusLabel()])
            @include('tickets.partials.priority-pill', ['priority' => $ticket->priority])
            <span class="inline-flex items-center rounded-lg border border-blue-800/60 bg-[#111a33] px-2.5 py-1 text-xs font-medium text-slate-200">
                Category: {{ $ticket->category->name ?? '—' }}
            </span>
            <span class="inline-flex items-center rounded-lg border border-blue-800/60 bg-[#111a33] px-2.5 py-1 text-xs font-medium text-slate-200">
                Source: {{ $ticket->source === 'self_service' ? 'System' : 'Call-logged' }}
            </span>
        </div>
    </div>

    <section class="rounded-xl border border-blue-800/70 bg-[#111a33] p-4">
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-blue-200/80">Ticket Information</h3>
        <dl class="grid gap-x-8 gap-y-3 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Requester</dt>
                <dd class="mt-1 text-base text-slate-100">{{ $ticket->requesterDisplay() }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Contact Number</dt>
                <dd class="mt-1 text-base text-slate-100">{{ $ticket->requester_phone ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Created</dt>
                <dd class="mt-1 text-base text-slate-100">{{ $ticket->created_at->format('M j, Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Scheduled For</dt>
                <dd class="mt-1 text-base text-slate-100">{{ $ticket->scheduled_for ? $ticket->scheduled_for->format('M j, Y H:i') : '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Assigned To</dt>
                <dd class="mt-1 text-base text-slate-100">{{ $ticket->assignee?->name ?? '—' }}</dd>
            </div>
            @if($user?->isAdmin())
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Accepted By</dt>
                <dd class="mt-1 text-base text-slate-100">{{ $ticket->acceptedBy?->name ?? $ticket->assignee?->name ?? '—' }}</dd>
            </div>
            @endif
        </dl>
    </section>

    @if($ticket->location)
    @php $encodedLocation = urlencode($ticket->location); $officeOrigin = urlencode('Biometrix Systems & Trading Corp., Consolacion, Cebu'); @endphp
    <section class="rounded-xl border border-blue-800/70 bg-[#111a33] p-4">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-blue-200/80">Location</h3>
        <p class="mt-2 text-base text-slate-100">{{ $ticket->location }}</p>
        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm">
            <a href="https://www.google.com/maps/search/?api=1&query={{ $encodedLocation }}" target="_blank" rel="noopener" class="font-medium text-blue-300 hover:text-blue-200">Open in Google Maps</a>
            <a href="https://www.google.com/maps/dir/?api=1&origin={{ $officeOrigin }}&destination={{ $encodedLocation }}" target="_blank" rel="noopener" class="font-medium text-blue-300 hover:text-blue-200">Get directions</a>
        </div>
    </section>
    @endif

    <section class="rounded-xl border border-blue-800/70 bg-[#111a33] p-4">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-blue-200/80">Description</h3>
        <p class="mt-2 whitespace-pre-wrap text-base leading-relaxed text-slate-100">{{ $ticket->description }}</p>
    </section>

    @if($ticket->resolution_notes)
    <section class="rounded-xl border border-blue-800/70 bg-[#111a33] p-4">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-blue-200/80">Resolution Notes</h3>
        <p class="mt-2 whitespace-pre-wrap text-base leading-relaxed text-slate-100">{{ $ticket->resolution_notes }}</p>
    </section>
    @endif
</div>
