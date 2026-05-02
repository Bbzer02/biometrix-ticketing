@extends('layouts.app')

@section('title', 'Add user')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h1 class="mb-6 text-xl font-semibold text-slate-900 sm:text-2xl">Add user</h1>

        <form action="{{ route('users.store') }}" method="post" class="space-y-5">
            @csrf
            <div>
                <label for="name" class="label-ticket">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="input-ticket" placeholder="Full name">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="label-ticket">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required class="input-ticket" placeholder="email@example.com">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-slate-500">The user will set their password on first login (email-only sign-in, then set password).</p>
            </div>
            <div>
                <label for="role" class="label-ticket">Role</label>
                <select name="role" id="role" required class="input-ticket">
                    @foreach ($roles as $value => $label)
                        <option value="{{ $value }}" {{ old('role') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('role')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-slate-500">
                    Choose what this user can do: Employee (submit & track tickets), Front Desk (log calls/walk-ins), IT Staff (resolve tickets), Admin (manage users & settings).
                </p>
            </div>
            <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:gap-4">
                <button
                    type="submit"
                    class="cursor-pointer transition-all bg-gray-700 text-white px-6 py-2 rounded-lg border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold inline-flex items-center justify-center"
                >
                    Create user
                </button>
                <a href="{{ route('users.index') }}" class="inline-flex min-h-[44px] items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
