@php
    $user = auth()->user();
@endphp

<div class="space-y-4 text-slate-100">
    <div class="rounded-xl border border-blue-800/70 bg-[#111a33] px-4 py-3">
        <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="text-xs text-slate-300">Edit ticket</div>
            <div class="truncate text-base font-semibold text-slate-100">{{ $ticket->ticket_number }} — {{ $ticket->title }}</div>
        </div>

    </div>
    </div>

    <form action="{{ route('tickets.update', $ticket) }}" method="post" class="space-y-4 ajax-ticket-edit-form">
        @csrf
        @method('PUT')

        <div>
            <label for="modal_title_{{ $ticket->id }}" class="text-xs font-medium text-slate-300">Subject</label>
            <input type="text" name="title" id="modal_title_{{ $ticket->id }}" value="{{ old('title', $ticket->title) }}" required class="input-ticket bg-white text-slate-900 placeholder-slate-400">
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="modal_requester_name_{{ $ticket->id }}" class="text-xs font-medium text-slate-300">Requester name</label>
                <input type="text" name="requester_name" id="modal_requester_name_{{ $ticket->id }}" value="{{ old('requester_name', $ticket->requester_name) }}" class="input-ticket bg-white text-slate-900 placeholder-slate-400">
            </div>

            <div>
                <label for="modal_requester_email_{{ $ticket->id }}" class="text-xs font-medium text-slate-300">Requester email</label>
                <input type="email" name="requester_email" id="modal_requester_email_{{ $ticket->id }}" value="{{ old('requester_email', $ticket->requester_email) }}" class="input-ticket bg-white text-slate-900 placeholder-slate-400">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="modal_requester_phone_{{ $ticket->id }}" class="text-xs font-medium text-slate-300">Contact number</label>
                <input type="tel" inputmode="tel" name="requester_phone" id="modal_requester_phone_{{ $ticket->id }}" value="{{ old('requester_phone', $ticket->requester_phone) }}" maxlength="24" class="input-ticket bg-white text-slate-900 placeholder-slate-400" placeholder="+63 9123456789">
            </div>

            <div>
                <label for="modal_location_{{ $ticket->id }}" class="text-xs font-medium text-slate-300">Location</label>
                <input type="text" name="location" id="modal_location_{{ $ticket->id }}" value="{{ old('location', $ticket->location) }}" class="input-ticket bg-white text-slate-900 placeholder-slate-400">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="modal_scheduled_for_{{ $ticket->id }}" class="text-xs font-medium text-slate-300">Scheduled date &amp; time</label>
                <input type="datetime-local" name="scheduled_for" id="modal_scheduled_for_{{ $ticket->id }}" value="{{ old('scheduled_for', $ticket->scheduled_for?->format('Y-m-d\TH:i')) }}" class="input-ticket bg-white text-slate-900 placeholder-slate-400">
            </div>

            <div>
                <label for="modal_category_id_{{ $ticket->id }}" class="text-xs font-medium text-slate-300">Category</label>
                <select name="category_id" id="modal_category_id_{{ $ticket->id }}" required class="input-ticket bg-white text-slate-900">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $ticket->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="modal_priority_{{ $ticket->id }}" class="text-xs font-medium text-slate-300">Priority</label>
                <select name="priority" id="modal_priority_{{ $ticket->id }}" required class="input-ticket bg-white text-slate-900">
                    @foreach(($priorities ?? collect()) as $p)
                        <option value="{{ $p->key }}" {{ old('priority', $ticket->priority) === $p->key ? 'selected' : '' }}>
                            {{ $p->label }}{{ $p->description ? ' – ' . $p->description : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label for="modal_description_{{ $ticket->id }}" class="text-xs font-medium text-slate-300">Description</label>
            <textarea name="description" id="modal_description_{{ $ticket->id }}" required rows="5" class="input-ticket min-h-[120px] resize-y bg-white text-slate-900 placeholder-slate-400">{{ old('description', $ticket->description) }}</textarea>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-2 pt-2">
            <button type="button"
                    class="ticket-modal-close cursor-pointer transition-all bg-white text-slate-700 px-5 py-2 rounded-lg border-slate-300 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-slate-200 shadow-slate-200 active:shadow-none text-sm font-semibold inline-flex items-center justify-center min-h-10 touch-manipulation">
                Cancel
            </button>
            <button type="submit"
                    class="cursor-pointer inline-flex items-center justify-center min-h-10 rounded-[0.9em] border-2 border-[#24b4fb] bg-[#24b4fb] px-5 py-2 text-sm font-semibold text-white transition-all duration-200 ease-in-out hover:bg-[#0071e2] hover:border-[#0071e2] focus:outline-none focus:ring-2 focus:ring-[#24b4fb]/50 focus:ring-offset-1 active:translate-y-[1px] touch-manipulation">
                Save changes
            </button>
        </div>
    </form>
</div>

