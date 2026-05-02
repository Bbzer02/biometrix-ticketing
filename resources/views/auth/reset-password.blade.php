@extends('layouts.guest')

@section('title', 'Set new password')

@section('content')
<div class="login-form-panel">
    <div class="flex items-center justify-center gap-4 mb-4">
        <img src="{{ asset('image/bstc-logo.png') }}" alt="" class="h-16 w-16 shrink-0 object-contain rounded-xl" width="179" height="172" aria-hidden="true">
        <span class="login-brand-inline">IT Helpdesk</span>
    </div>
    <h1 class="text-2xl font-bold mb-1 text-center" style="color:#000;">Set new password</h1>
    <p class="text-sm mb-6 text-center" style="color:#555;">Choose a strong password of at least 8 characters.</p>

    @if($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('password.reset.submit') }}" method="post" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label for="password" class="label-guest">New password</label>
            <input type="password" name="password" id="password" required minlength="8"
                   class="input-guest" placeholder="Min 8 characters" autocomplete="new-password">
        </div>
        <div>
            <label for="password_confirmation" class="label-guest">Confirm password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                   class="input-guest" placeholder="Confirm new password" autocomplete="new-password">
        </div>
        <button type="submit" class="login-btn-primary">Save password</button>
    </form>
</div>
@endsection
