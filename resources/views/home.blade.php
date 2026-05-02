@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-8">
        {{-- Header (content-header = page title + subtitle, turns white in dark mode) --}}
        <div class="content-header flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 sm:text-3xl dark:text-white">Dashboard</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-white/50">Welcome back, {{ auth()->user()->name }}. <span class="font-medium text-slate-800 dark:text-white/70">{{ auth()->user()->getRoleLabel() }}</span></p>
            </div>
        </div>

        @if($announcements->isNotEmpty())
            <div class="pointer-events-none fixed right-4 top-24 z-50 flex w-[min(24rem,calc(100vw-2rem))] flex-col gap-2">
                @foreach($announcements as $announcement)
                    @php
                        $priorityTone = match ($announcement->priority) {
                            'critical' => [
                                'card' => 'border-rose-300/70 ring-rose-200/80 dark:border-rose-300/45 dark:ring-rose-300/25',
                                'icon' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
                                'meta' => 'text-rose-700/95 dark:text-rose-300/90',
                            ],
                            'major' => [
                                'card' => 'border-amber-300/70 ring-amber-200/80 dark:border-amber-300/45 dark:ring-amber-300/25',
                                'icon' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
                                'meta' => 'text-amber-700/95 dark:text-amber-300/90',
                            ],
                            'low' => [
                                'card' => 'border-emerald-300/70 ring-emerald-200/80 dark:border-emerald-300/45 dark:ring-emerald-300/25',
                                'icon' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
                                'meta' => 'text-emerald-700/95 dark:text-emerald-300/90',
                            ],
                            default => [
                                'card' => 'border-blue-300/70 ring-blue-200/80 dark:border-blue-300/45 dark:ring-blue-300/25',
                                'icon' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
                                'meta' => 'text-blue-700/95 dark:text-blue-300/90',
                            ],
                        };
                    @endphp
                    <div data-announcement-card class="pointer-events-auto rounded-2xl border bg-white/95 p-3 shadow-[0_10px_30px_-16px_rgba(15,23,42,0.5)] ring-1 backdrop-blur-md dark:bg-slate-900/90 {{ $priorityTone['card'] }}">
                        <div class="flex items-start gap-2.5">
                            <div class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg {{ $priorityTone['icon'] }}">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.13em] {{ $priorityTone['meta'] }}">Admin message · {{ \App\Models\StaffAnnouncement::audienceLabel($announcement->audience) }} · {{ strtoupper($announcement->priority) }}</p>
                                <p class="mt-1 text-sm font-semibold leading-tight text-slate-900 dark:text-slate-100">{{ $announcement->title }}</p>
                                <p class="mt-1 max-h-16 overflow-hidden text-xs leading-5 text-slate-600 dark:text-slate-300">{{ $announcement->body }}</p>
                            </div>
                            @if(auth()->check() && (!auth()->user()->isAdmin()))
                            <form method="post" action="{{ route('staff-announcements.ack', $announcement) }}" class="js-announcement-ack-form shrink-0">
                                @csrf
                                <button type="submit" class="js-announcement-ack-btn inline-flex items-center rounded-lg bg-emerald-600 px-2.5 py-1.5 text-[11px] font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1 dark:focus:ring-offset-slate-900">
                                    Done
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($dashboardRole === 'admin' && $stats)
            @include('home.partials.admin-dashboard')
        @elseif($dashboardRole === 'employee' && $stats)
            @include('home.partials.employee-dashboard')
        @elseif($dashboardRole === 'it_staff' && $stats)
            @include('home.partials.it-staff-dashboard')
        @elseif($dashboardRole === 'front_desk' && $stats)
            @include('home.partials.front-desk-dashboard')
        @else
            {{-- Fallback: minimal quick actions --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @if(auth()->user()->isFrontDesk())
                <a href="{{ route('tickets.create', ['source' => 'phone']) }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md" data-full-reload>
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div>
                            <h2 class="font-semibold text-slate-900">Log ticket (phone)</h2>
                            <p class="text-sm text-slate-500">Log a ticket from a call</p>
                        </div>
                    </div>
                </a>
                <a href="{{ route('tickets.create', ['source' => 'walk_in']) }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md" data-full-reload>
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <h2 class="font-semibold text-slate-900">Log (walk-in)</h2>
                            <p class="text-sm text-slate-500">Log a ticket from walk-in</p>
                        </div>
                    </div>
                </a>
                @elseif(auth()->user()->isItStaff())
                <a href="{{ route('tickets.index') }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <h2 class="font-semibold text-slate-900">All tickets</h2>
                            <p class="text-sm text-slate-500">Resolve and update ticket status</p>
                        </div>
                    </div>
                </a>
                @else
                @if(auth()->user()->isAdmin() || auth()->user()->isItStaff() || auth()->user()->isFrontDesk())
                <a href="{{ route('tickets.create') }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md" data-full-reload>
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <div>
                            <h2 class="font-semibold text-slate-900">Submit a ticket</h2>
                            <p class="text-sm text-slate-500">Create a new IT request</p>
                        </div>
                    </div>
                </a>
                @endif
                <a href="{{ route('tickets.index') }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <h2 class="font-semibold text-slate-900">Accepted tickets</h2>
                            <p class="text-sm text-slate-500">View and track tickets you accepted</p>
                        </div>
                    </div>
                </a>
                @endif
            </div>
        @endif
    </div>

    @if(session('login_success'))
    <script>
        (function() {
            var show = function() {
                if (!window.Swal) return;
                window.Swal.fire({
                    icon: 'success',
                    title: 'Login successful',
                    text: 'Welcome back, {{ session('login_success') }}.',
                    showConfirmButton: true,
                    confirmButtonText: 'Continue to dashboard',
                    allowOutsideClick: false,
                    allowEscapeKey: true,
                    background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#e2e8f0' : '#0f172a'
                });
            };

            if (!window.Swal) {
                var s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
                s.onload = show;
                document.head.appendChild(s);
            } else {
                show();
            }
        })();
    </script>
    @endif

@endsection
