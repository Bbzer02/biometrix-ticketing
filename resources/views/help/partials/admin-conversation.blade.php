@php
    /** @var \Illuminate\Support\Collection $messages */
    $admin = auth()->user();

    // Unread reply = latest IT staff message after admin's last_help_read_at
    $unreadReply = $messages->filter(function($m) use ($admin) {
        if (!$m->sender || $m->sender->isAdmin()) return false;
        if ($admin->last_help_read_at && $m->created_at <= $admin->last_help_read_at) return false;
        return true;
    })->last();
@endphp

@if($unreadReply)
{{-- ── STATE: unread reply from IT staff ── --}}
<div class="space-y-3 text-slate-900" id="qa-reply-view">
    <div class="-mx-4 -mt-4 rounded-t-2xl bg-slate-900 px-4 py-3 text-white">
        <div class="flex items-start justify-between gap-2">
            <div>
                <div class="text-xs/5 text-white/70">Reply from {{ $unreadReply->sender?->name ?? 'IT Staff' }}</div>
                <div class="text-sm font-semibold text-white">New message</div>
            </div>
            <button type="button" class="qa-help-close inline-flex h-8 w-8 items-center justify-center rounded-lg text-white/80 hover:bg-white/10" aria-label="Close">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-800 whitespace-pre-wrap leading-snug">{{ $unreadReply->body }}</div>
    <div class="text-[10px] text-slate-400">{{ $unreadReply->created_at?->format('M j, Y g:i A') }}</div>

    <div class="flex gap-2 pt-1">
        <button type="button" id="qa-reply-btn"
                class="flex-1 inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
            Reply
        </button>
        <button type="button" class="qa-help-close flex-1 inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            Close
        </button>
    </div>
</div>

<div class="space-y-3 text-slate-900 hidden" id="qa-send-view">
    <div class="-mx-4 -mt-4 rounded-t-2xl bg-slate-900 px-4 py-3 text-white">
        <div class="flex items-start justify-between gap-2">
            <div>
                <div class="text-xs/5 text-white/70">Admin</div>
                <div class="text-sm font-semibold text-white">Message IT Staff</div>
            </div>
            <button type="button" class="qa-help-close inline-flex h-8 w-8 items-center justify-center rounded-lg text-white/80 hover:bg-white/10" aria-label="Close">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
    <form class="qa-help-form space-y-2" action="{{ route('help.store') }}" method="post">
        @csrf
        <textarea name="body" rows="3" required
                  placeholder="Send a message to IT staff…"
                  class="input-ticket min-h-[96px] resize-y"></textarea>
        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                Send
            </button>
        </div>
    </form>
</div>

<script>
(function() {
    var replyBtn = document.getElementById('qa-reply-btn');
    var replyView = document.getElementById('qa-reply-view');
    var sendView  = document.getElementById('qa-send-view');
    if (replyBtn && replyView && sendView) {
        replyBtn.addEventListener('click', function() {
            replyView.classList.add('hidden');
            sendView.classList.remove('hidden');
        });
    }
})();
</script>

@else
{{-- ── STATE: no unread reply — show send form ── --}}
<div class="space-y-3 text-slate-900">
    <div class="-mx-4 -mt-4 rounded-t-2xl bg-slate-900 px-4 py-3 text-white">
        <div class="flex items-start justify-between gap-2">
            <div>
                <div class="text-xs/5 text-white/70">Admin</div>
                <div class="text-sm font-semibold text-white">Message IT Staff</div>
            </div>
            <button type="button" class="qa-help-close inline-flex h-8 w-8 items-center justify-center rounded-lg text-white/80 hover:bg-white/10" aria-label="Close">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
    <form class="qa-help-form space-y-2" action="{{ route('help.store') }}" method="post">
        @csrf
        <label for="qa_admin_help_body" class="sr-only">Message</label>
        <textarea name="body" id="qa_admin_help_body" rows="3" required
                  placeholder="Send a message to IT staff…"
                  class="input-ticket min-h-[96px] resize-y"></textarea>
        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                Send
            </button>
        </div>
    </form>
</div>
@endif
