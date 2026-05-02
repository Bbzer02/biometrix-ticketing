<div class="space-y-4 text-slate-100">
    <div class="rounded-xl border border-blue-800/70 bg-[#111a33] px-4 py-3">
        <div class="min-w-0">
            <div class="text-xs text-slate-300">Update status</div>
            <div class="truncate text-base font-semibold text-slate-100">{{ $ticket->ticket_number }} — {{ $ticket->title }}</div>
        </div>
    </div>

    <form action="{{ route('tickets.update', $ticket) }}" method="post" class="space-y-4 ajax-ticket-status-form">
        @csrf
        @method('PUT')

        <div>
            <label for="modal_status_{{ $ticket->id }}" class="text-xs font-medium text-slate-300">Status</label>
            <select name="status" id="modal_status_{{ $ticket->id }}" required class="input-ticket bg-white text-slate-900">
                @foreach($allowedStatuses as $key => $label)
                    <option value="{{ $key }}" {{ $ticket->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-2 pt-2">
            <button type="button"
                    class="ticket-modal-close cursor-pointer transition-all bg-white text-slate-700 px-5 py-2 rounded-lg border-slate-300 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-slate-200 shadow-slate-200 active:shadow-none text-sm font-semibold inline-flex items-center justify-center min-h-10 touch-manipulation">
                Cancel
            </button>
            <button type="submit"
                    class="cursor-pointer inline-flex items-center justify-center min-h-10 rounded-[0.9em] border-2 border-[#24b4fb] bg-[#24b4fb] px-5 py-2 text-sm font-semibold text-white transition-all duration-200 ease-in-out hover:bg-[#0071e2] hover:border-[#0071e2] focus:outline-none focus:ring-2 focus:ring-[#24b4fb]/50 focus:ring-offset-1 active:translate-y-[1px] touch-manipulation">
                Update status
            </button>
        </div>
    </form>
</div>
