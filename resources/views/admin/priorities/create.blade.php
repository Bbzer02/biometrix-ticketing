@extends('layouts.app')

@section('title', 'New priority')

@section('content')
    <div class="rounded-2xl border border-blue-200 bg-blue-50/40 p-6 shadow-sm sm:p-8 dark:border-blue-400/40 dark:bg-blue-500/10">
        <h1 class="mb-6 text-xl font-semibold text-slate-900 sm:text-2xl">New priority</h1>

        <form action="{{ route('admin.priorities.store') }}" method="post" class="space-y-5">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="key" class="label-ticket">Key</label>
                    <input type="text" name="key" id="key" value="{{ old('key') }}" required class="input-ticket" placeholder="e.g. normal">
                    @error('key')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="label" class="label-ticket">Label</label>
                    <input type="text" name="label" id="label" value="{{ old('label') }}" required class="input-ticket" placeholder="e.g. Normal">
                    @error('label')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="description" class="label-ticket">Description (optional)</label>
                <textarea name="description" id="description" rows="4" class="input-ticket min-h-[100px] resize-y">{{ old('description') }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="sort_order" class="label-ticket">Sort order</label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="input-ticket">
                    @error('sort_order')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="pt-6">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="active" value="1" {{ old('active', 1) ? 'checked' : '' }}>
                        Active
                    </label>
                    @error('active')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:gap-4 sm:items-center">
                <button type="submit" class="cursor-pointer transition-all bg-gray-700 text-white px-6 py-2 rounded-lg border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold inline-flex items-center">
                    Save
                </button>
                <a href="{{ route('admin.priorities.index') }}" class="inline-flex min-h-[44px] items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection

