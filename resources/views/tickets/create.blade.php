@extends('layouts.app')

@section('title', $source === 'phone' ? 'Log ticket (phone)' : ($source === 'walk_in' ? 'Log ticket (walk-in)' : 'Submit ticket'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h1 class="mb-6 text-xl font-semibold text-slate-900 sm:text-2xl">
            @if($source === 'phone')
                Log ticket (phone call)
            @elseif($source === 'walk_in')
                Log ticket (walk-in)
            @else
                Submit a ticket
            @endif
        </h1>

        <form action="{{ route('tickets.store') }}" method="post" class="space-y-5">
            @csrf
            <input type="hidden" name="source" value="{{ $source }}">

            <div>
                <label for="requester_name" class="label-ticket">Requester name (optional)</label>
                <input
                    type="text"
                    name="requester_name"
                    id="requester_name"
                    value="{{ old('requester_name') }}"
                    placeholder="Person to contact about this ticket"
                    class="input-ticket"
                >
                @error('requester_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="requester_phone" class="label-ticket">Contact number</label>
                <input type="tel" inputmode="tel" autocomplete="tel" name="requester_phone" id="requester_phone" value="{{ old('requester_phone') }}" placeholder="+63 9123456789 (optional)" maxlength="24" class="input-ticket">
                @error('requester_phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="title" class="label-ticket">Subject</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required placeholder="Brief subject" class="input-ticket">
                @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="location" class="label-ticket">Location</label>
                <input type="text" name="location" id="location" value="{{ old('location') }}" placeholder="e.g. 3F – HR Office, Meeting room 2" class="input-ticket">
                @error('location')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-slate-500">
                    This text will be used to open Google Maps or Waze for driving directions (e.g. building, floor, room).
                </p>
            </div>

            <div>
                <label for="scheduled_for" class="label-ticket">Scheduled date & time (optional)</label>
                <input
                    type="datetime-local"
                    name="scheduled_for"
                    id="scheduled_for"
                    value="{{ old('scheduled_for') }}"
                    class="input-ticket"
                >
                @error('scheduled_for')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="description" class="label-ticket">Description</label>
                <textarea name="description" id="description" required placeholder="Describe the issue..." rows="5" class="input-ticket min-h-[120px] resize-y">{{ old('description') }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="category_id" class="label-ticket">Category</label>
                <select name="category_id" id="category_id" required class="input-ticket">
                    <option value="">— Choose category —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="priority" class="label-ticket">Priority</label>
                <select name="priority" id="priority" required class="input-ticket">
                    @foreach(($priorities ?? collect()) as $p)
                        <option value="{{ $p->key }}" {{ old('priority', 'normal') === $p->key ? 'selected' : '' }}>
                            {{ $p->label }}{{ $p->description ? ' – ' . $p->description : '' }}
                        </option>
                    @endforeach
                </select>
                @error('priority')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:gap-4 sm:items-center">
                <button type="submit" class="cursor-pointer transition-all bg-gray-700 text-white px-6 py-2 rounded-lg border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold inline-flex items-center">
                    Create ticket
                </button>
                <a href="{{ route('tickets.index') }}" class="inline-flex min-h-[44px] items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection

