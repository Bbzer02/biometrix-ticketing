@php
    $allowedSources = $allowedSources ?? [\App\Models\Ticket::SOURCE_SELF_SERVICE];
    $defaultSource = in_array(\App\Models\Ticket::SOURCE_SELF_SERVICE, $allowedSources, true)
        ? \App\Models\Ticket::SOURCE_SELF_SERVICE
        : ($allowedSources[0] ?? \App\Models\Ticket::SOURCE_PHONE);
@endphp

<div class="space-y-4 text-slate-900">
    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="text-xs text-slate-500">Submit ticket</div>
                <div class="truncate text-base font-semibold text-slate-900">Create a new ticket</div>
            </div>
        </div>
    </div>

    <form action="{{ route('tickets.store') }}" method="post" class="space-y-4 ajax-ticket-create-form">
        @csrf
        <input type="hidden" name="from_modal" value="1">

        <div>
            <label for="modal_source" class="text-xs font-medium text-slate-500">Source</label>
            <select name="source" id="modal_source" class="input-ticket bg-white text-slate-900" required>
                @if(in_array(\App\Models\Ticket::SOURCE_PHONE, $allowedSources, true))
                    <option value="phone" {{ old('source', $defaultSource) === 'phone' ? 'selected' : '' }}>Phone Call</option>
                @endif
                @if(in_array(\App\Models\Ticket::SOURCE_WALK_IN, $allowedSources, true))
                    <option value="walk_in" {{ old('source', $defaultSource) === 'walk_in' ? 'selected' : '' }}>Walk-in</option>
                @endif
                @if(in_array(\App\Models\Ticket::SOURCE_SELF_SERVICE, $allowedSources, true))
                    <option value="self_service" {{ old('source', $defaultSource) === 'self_service' ? 'selected' : '' }}>None</option>
                @endif
            </select>
            @error('source')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="modal_requester_name" class="text-xs font-medium text-slate-500">Requester name</label>
                <input type="text" name="requester_name" id="modal_requester_name" value="{{ old('requester_name') }}" required class="input-ticket bg-white text-slate-900 placeholder-slate-400">
                @error('requester_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="modal_requester_phone" class="text-xs font-medium text-slate-500">Contact number</label>
                <input type="tel" inputmode="tel" name="requester_phone" id="modal_requester_phone" value="{{ old('requester_phone') }}" maxlength="24" required class="input-ticket bg-white text-slate-900 placeholder-slate-400" placeholder="+63 9123456789">
                @error('requester_phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label for="modal_title" class="text-xs font-medium text-slate-500">Subject</label>
            <input type="text" name="title" id="modal_title" value="{{ old('title') }}" required class="input-ticket bg-white text-slate-900 placeholder-slate-400">
            @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="modal_location" class="text-xs font-medium text-slate-500">Location</label>
            <input type="text" name="location" id="modal_location" value="{{ old('location') }}" required class="input-ticket bg-white text-slate-900 placeholder-slate-400">
            @error('location')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="modal_scheduled_for" class="text-xs font-medium text-slate-500">Scheduled date &amp; time</label>
            <input type="datetime-local" name="scheduled_for" id="modal_scheduled_for" value="{{ old('scheduled_for') }}" required class="input-ticket bg-white text-slate-900 placeholder-slate-400">
            @error('scheduled_for')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="modal_description" class="text-xs font-medium text-slate-500">Description (optional)</label>
            <textarea name="description" id="modal_description" rows="5" class="input-ticket min-h-[120px] resize-y bg-white text-slate-900 placeholder-slate-400">{{ old('description') }}</textarea>
            @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="modal_category_id" class="text-xs font-medium text-slate-500">Category</label>
                <select name="category_id" id="modal_category_id" required class="input-ticket bg-white text-slate-900">
                    <option value="">— Choose category —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="modal_priority" class="text-xs font-medium text-slate-500">Priority</label>
                <select name="priority" id="modal_priority" required class="input-ticket bg-white text-slate-900">
                    @foreach(($priorities ?? collect()) as $p)
                        <option value="{{ $p->key }}" {{ old('priority', 'normal') === $p->key ? 'selected' : '' }}>
                            {{ $p->label }}{{ $p->description ? ' – ' . $p->description : '' }}
                        </option>
                    @endforeach
                </select>
                @error('priority')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-2 pt-2">
            <button type="button"
                    class="ticket-modal-close cursor-pointer transition-all bg-white text-slate-700 px-5 py-2 rounded-lg border-slate-300 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-slate-200 shadow-slate-200 active:shadow-none text-sm font-semibold inline-flex items-center justify-center min-h-10 touch-manipulation">
                Cancel
            </button>
            <button type="submit"
                    class="cursor-pointer transition-all bg-gray-700 text-white px-5 py-2 rounded-lg border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold inline-flex items-center justify-center min-h-10 touch-manipulation">
                Create ticket
            </button>
        </div>
    </form>
</div>

