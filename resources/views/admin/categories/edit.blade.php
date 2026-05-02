@extends('layouts.app')

@section('title', 'Edit category')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h1 class="mb-6 text-xl font-semibold text-slate-900 sm:text-2xl">Edit category</h1>

        <form action="{{ route('admin.categories.update', $category) }}" method="post" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="label-ticket">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required class="input-ticket">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="slug" class="label-ticket">Slug</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug', $category->slug) }}" required class="input-ticket">
                @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="description" class="label-ticket">Description (optional)</label>
                <textarea name="description" id="description" rows="4" class="input-ticket min-h-[100px] resize-y">{{ old('description', $category->description) }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:gap-4 sm:items-center">
                <button type="submit" class="cursor-pointer transition-all bg-gray-700 text-white px-6 py-2 rounded-lg border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold inline-flex items-center">
                    Save changes
                </button>
                <a href="{{ route('admin.categories.index') }}" class="inline-flex min-h-[44px] items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection

