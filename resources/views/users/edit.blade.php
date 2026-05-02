@extends('layouts.app')

@section('title', 'Edit user')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h1 class="mb-6 text-xl font-semibold text-slate-900 sm:text-2xl">Edit user</h1>

        <form action="{{ route('users.update', $user) }}" method="post" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="label-ticket">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="input-ticket">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="label-ticket">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="input-ticket">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="role" class="label-ticket">Role</label>
                <select name="role" id="role" required class="input-ticket">
                    @foreach ($roles as $value => $label)
                        <option value="{{ $value }}" {{ old('role', $user->role) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('role')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mt-4 max-w-md rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-900">
                New users set their password on first login (email-only sign-in, then set password). Only after they have set their first password can an administrator change or reset it here.
            </div>

            @if ($user->id !== auth()->id())
                <div class="pt-4 border-t border-slate-200 mt-6">
                    <h2 class="mb-3 text-sm font-semibold text-slate-900">Password (admin only)</h2>
                    @if ($user->hasPasswordSet())
                        <label class="inline-flex items-center gap-2 cursor-pointer mb-3">
                            <input type="checkbox" name="clear_password" value="1" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                            <span class="text-sm text-slate-700">Clear password so user must set it on next login (treat as new user)</span>
                        </label>
                        <p class="mb-3 text-xs text-slate-500">Or set a new password below. Leave both unchecked/blank to keep the current password.</p>
                        <div class="space-y-3">
                            <div>
                                <label for="new_password" class="label-ticket">New password</label>
                                <input type="password" name="new_password" id="new_password" class="input-ticket" minlength="8" autocomplete="new-password" placeholder="Leave blank to keep current or use &quot;Clear password&quot; above">
                            </div>
                            <div>
                                <label for="new_password_confirmation" class="label-ticket">Confirm new password</label>
                                <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="input-ticket" minlength="8" autocomplete="new-password">
                            </div>
                            @error('new_password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @else
                        <p class="text-sm text-slate-600">This user has not set a password yet. They must sign in with their email and set their first password. After that, you can change or reset their password here.</p>
                    @endif
                </div>
            @endif
            <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:gap-4">
                <button type="submit" class="cursor-pointer transition-all bg-gray-700 text-white px-6 py-2 rounded-lg border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold">
                    Save changes
                </button>
                <a href="{{ route('users.index') }}" class="inline-flex min-h-[44px] items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </form>

        @if ($user->id !== auth()->id())
            <div class="mt-8 border-t border-slate-200 pt-8">
                <form action="{{ route('users.destroy', $user) }}" method="post" class="inline swift-confirm-delete" data-confirm-message="Remove this user? This cannot be undone.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-medium text-red-700 transition-colors hover:bg-red-100">
                        Remove user
                    </button>
                </form>
            </div>
        @else
            <p class="mt-8 border-t border-slate-200 pt-8 text-sm text-slate-500">You cannot remove your own account.</p>
        @endif
    </div>
@endsection
