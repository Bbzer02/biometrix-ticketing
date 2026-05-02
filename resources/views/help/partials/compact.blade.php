<div class="-mx-4 -mt-4 rounded-t-2xl bg-slate-900 px-4 py-3 text-white mb-3">
    <div class="flex items-center justify-between gap-2">
        <div>
            <div class="text-xs text-white/60">Need help?</div>
            <div class="text-sm font-semibold">Message IT</div>
        </div>
        <button type="button" class="qa-help-close inline-flex h-8 w-8 items-center justify-center rounded-lg text-white/70 hover:bg-white/10" aria-label="Close">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</div>
<div id="qa-send-view" class="space-y-2">
    <form class="qa-help-form space-y-2" action="{{ route('help.store') }}" method="post">
        @csrf
        <label for="qa_help_body" class="sr-only">Message</label>
        <textarea name="body" id="qa_help_body" rows="3" required
                  placeholder="Describe the issue…"
                  class="input-ticket min-h-[88px] resize-y"></textarea>
        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                Send
            </button>
        </div>
    </form>
</div>
<div id="qa-sent-view" class="hidden space-y-3 text-center py-2">
    <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 mx-auto">
        <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    </div>
    <p class="text-sm font-semibold text-slate-900">Message sent!</p>
    <p class="text-xs text-slate-500 leading-relaxed">IT team will review and reply. You'll see a badge here when they respond.</p>
    <button type="button" class="qa-help-close mt-1 inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 w-full">
        Close
    </button>
</div>
