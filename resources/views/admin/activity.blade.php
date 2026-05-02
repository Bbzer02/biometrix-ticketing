@extends('layouts.app')

@section('title', 'Logins & activity')

@section('content')
    <div class="space-y-8">
        {{-- Active sessions (who is logged in now) --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm sm:overflow-hidden">
            <div class="border-b border-slate-200 bg-white px-4 py-4 sm:px-6">
                <h1 class="text-xl font-semibold text-slate-900 sm:text-2xl">Active logins</h1>
                <p class="mt-1 text-sm text-slate-500">Users currently logged in (multiple windows/devices allowed).</p>
            </div>
            <div class="p-4 sm:px-6 overflow-x-auto audit-trail-table-wrap">
                @if (count($activeSessions) > 0)
                    <table id="active-sessions-table" class="display" style="width:100%">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>IP</th>
                                <th>Device / browser</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($activeSessions as $session)
                                <tr>
                                    <td>{{ $session->user ? $session->user->name : '—' }}</td>
                                    <td>{{ $session->user ? $session->user->getRoleLabel() : '—' }}</td>
                                    <td><span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">Online</span></td>
                                    <td>{{ $session->ip_address ?? '—' }}</td>
                                    <td title="{{ $session->user_agent }}">{{ Str::limit($session->user_agent, 50) ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="px-4 py-8 text-center text-slate-500 sm:px-6">
                        @if (!\Illuminate\Support\Facades\Schema::hasTable('sessions') || config('session.driver') !== 'database')
                            <p>Active sessions are shown when <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">SESSION_DRIVER=database</code> and the <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">sessions</code> table exists.</p>
                        @else
                            <p>No active sessions right now.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Login / logout history --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm sm:overflow-hidden">
            <div class="border-b border-slate-200 bg-white px-4 py-4 sm:px-6">
                <h2 class="text-lg font-semibold text-slate-900">Login & logout history</h2>
                <p class="mt-1 text-sm text-slate-500">Last 20 events. Older records are auto-deleted (20 per user max).</p>
            </div>
            <div class="p-4 sm:px-6 overflow-x-auto audit-trail-table-wrap">
                <table id="login-history-table" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Event</th>
                            <th>Time</th>
                            <th>IP</th>
                            <th>Device / browser</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($loginLogs as $log)
                            <tr>
                                <td>{{ $log->user->name ?? '—' }}</td>
                                <td>
                                    @if ($log->event === \App\Models\LoginLog::EVENT_LOGIN)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">In</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">Out</span>
                                    @endif
                                </td>
                                <td>{{ $log->created_at->format('M j, Y g:i A') }}</td>
                                <td>{{ $log->ip_address ?? '—' }}</td>
                                <td title="{{ $log->user_agent }}">{{ Str::limit($log->user_agent, 50) ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">No login or logout events yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
