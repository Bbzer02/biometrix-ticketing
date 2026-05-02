@extends('layouts.guest')

@section('title', 'Set your password')

@section('content')
    <div class="login-form-panel">
        <div class="flex items-center justify-center gap-4 mb-4">
            <img src="{{ asset('image/bstc-logo.png') }}" alt="" class="h-16 w-16 shrink-0 object-contain rounded-xl" width="179" height="172" aria-hidden="true">
            <span class="login-brand-inline">IT Helpdesk</span>
        </div>

        <h1 class="text-3xl font-bold mb-1 text-center" style="color: #000; text-shadow: 0 0 8px rgba(255,255,255,0.9);">Set your password</h1>
        <p class="text-base mb-6 text-center" style="color: #000; text-shadow: 0 0 6px rgba(255,255,255,0.85);">Welcome, {{ $user->name }}. Create a password to sign in.</p>

        @if (session('error'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('auth.set-password') }}" method="post" class="space-y-5">
            @csrf
            <input type="hidden" name="email" value="{{ $user->email }}">
            <div>
                <label for="password" class="sr-only">Password</label>
                <input type="password" name="password" id="password" required autofocus
                    class="input-guest" placeholder="New password" minlength="8" autocomplete="new-password">
            </div>
            <div>
                <label for="password_confirmation" class="sr-only">Confirm password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                    class="input-guest" placeholder="Confirm password" autocomplete="new-password">
            </div>
            <button type="submit" class="login-btn-primary">
                Set password & sign in
            </button>
        </form>

        <p class="mt-4 text-center text-sm" style="color: #000; text-shadow: 0 0 6px rgba(255,255,255,0.85);">
            <a href="{{ route('login') }}" class="font-medium underline hover:no-underline">Back to sign in</a>
        </p>
    </div>
@endsection
