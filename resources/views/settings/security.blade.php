@extends('layouts.app')

@section('title', 'Security')

@section('content')
    <div class="space-y-6">
        <div class="content-header">
            <h1 class="text-2xl font-semibold text-slate-900 sm:text-3xl">Security</h1>
            <p class="mt-1 text-sm text-slate-500">Manage how you sign in to the helpdesk.</p>
        </div>

        @if(auth()->user()->isAdmin())
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/80">
                <h2 class="text-base font-semibold text-slate-900">Change password</h2>
                <p class="mt-0.5 text-sm text-slate-500">Use a strong password of at least 8 characters.</p>
            </div>
            <div class="p-6">
                <div class="max-w-md space-y-4">
                    @if (session('success_password'))
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ session('success_password') }}
                        </div>
                    @endif
                    @if (session('error_password'))
                        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ session('error_password') }}
                        </div>
                    @endif
                    @if ($errors->has('current_password') || $errors->has('password'))
                        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ $errors->first('current_password') ?: $errors->first('password') }}
                        </div>
                    @endif
                    <form action="{{ route('profile.password') }}" method="post" class="space-y-4">
                        @csrf
                        @method('PUT')
                        @if(!empty(auth()->user()->password))
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-slate-700 mb-1">Current password</label>
                            <input type="password" name="current_password" id="current_password"
                                   class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                   autocomplete="current-password" placeholder="Your current password">
                        </div>
                        @endif
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-slate-700 mb-1">New password</label>
                            <input type="password" name="password" id="new_password" required
                                   class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                   autocomplete="new-password" placeholder="Min 8 characters" minlength="8">
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirm new password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                   class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                   autocomplete="new-password" placeholder="Confirm new password">
                        </div>
                        <button type="submit" class="inline-flex min-h-[44px] items-center justify-center rounded-xl bg-slate-700 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 transition-colors">
                            Update password
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @else
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/80">
                <h2 class="text-base font-semibold text-slate-900">Password</h2>
                <p class="mt-0.5 text-sm text-slate-500">Password changes are managed by your administrator.</p>
            </div>
            <div class="p-6">
                <p class="text-sm text-slate-600">If you wish to change your password, contact your administrator.</p>
            </div>
        </div>
        @endif

        {{-- Emergency email — all users including admin --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/80">
                <h2 class="text-base font-semibold text-slate-900">Recovery email</h2>
                <p class="mt-0.5 text-sm text-slate-500">Your personal Gmail or any email you own. If you forget your password, the admin will send a reset link here.</p>
            </div>
            <div class="p-6">
                <div class="max-w-md space-y-4">
                    @if(session('success_emergency'))
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ session('success_emergency') }}
                        </div>
                    @endif
                    @if($errors->has('emergency_email'))
                        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ $errors->first('emergency_email') }}
                        </div>
                    @endif
                    <form action="{{ route('profile.update') }}" method="post">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="name" value="{{ auth()->user()->name }}">
                        <div class="space-y-4">
                            <div>
                                <label for="emergency_email" class="block text-sm font-medium text-slate-700 mb-1">
                                    Recovery email (e.g. Gmail)
                                </label>
                                <input type="email" name="emergency_email" id="emergency_email"
                                       value="{{ old('emergency_email', auth()->user()->emergency_email) }}"
                                       class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                       placeholder="yourname@gmail.com">
                                <p class="mt-1.5 text-xs text-slate-400">
                                    This is a personal email you own (e.g. Gmail). When the admin approves your reset request, a link will be sent here so you can set a new password.
                                </p>
                            </div>
                            <button type="submit" class="inline-flex min-h-[44px] items-center justify-center rounded-xl bg-slate-700 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 transition-colors">
                                Save recovery email
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
