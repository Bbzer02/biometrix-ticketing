@extends('layouts.app')

@section('title', 'Audit trail — ' . $user->name)

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-slate-900 dark:text-white sm:text-2xl">Ticket activity — {{ $user->name }}</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-white">Who worked on tickets and when. All actions by {{ $user->email }}.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.audit-trail.download', $user) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download
                </a>
                <a href="{{ route('admin.audit-trail.print', $user) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print
                </a>
                <a href="{{ route('admin.audit-trail.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                    Back to audit trail
                </a>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm sm:overflow-hidden">
            <div class="border-b border-slate-200 px-4 py-4 sm:px-6">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">User activity</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Ticket actions plus sign-in/sign-out events. Use the search bar and pagination below. Showing last 300 actions.</p>
            </div>
            <div class="overflow-x-auto p-4 sm:px-6 audit-trail-table-wrap">
                <table id="audit-trail-actions-table" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>Date & time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($entries as $entry)
                            <tr>
                                <td>
                                    @if (!empty($entry['ticket_number']))
                                        @if (!empty($entry['ticket_url']))
                                            <a href="{{ $entry['ticket_url'] }}" class="font-medium text-blue-600 hover:text-blue-700">{{ $entry['ticket_number'] }}</a>
                                        @else
                                            <span class="font-medium text-slate-700">{{ $entry['ticket_number'] }}</span>
                                        @endif
                                        <span class="block truncate max-w-[12rem] text-slate-500" title="{{ $entry['ticket_title'] ?? '' }}">{{ Str::limit($entry['ticket_title'] ?? '', 30) }}</span>
                                    @else
                                        <span class="text-slate-400">Auth event</span>
                                    @endif
                                </td>
                                <td>
                                    @php($badge = $entry['badge'] ?? 'slate')
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium
                                        @if ($badge === 'blue') bg-blue-100 text-blue-800
                                        @elseif ($badge === 'emerald') bg-emerald-100 text-emerald-800
                                        @elseif ($badge === 'amber') bg-amber-100 text-amber-800
                                        @elseif ($badge === 'indigo') bg-indigo-100 text-indigo-800
                                        @elseif ($badge === 'cyan') bg-cyan-100 text-cyan-800
                                        @elseif ($badge === 'rose') bg-rose-100 text-rose-800
                                        @else bg-slate-100 text-slate-700
                                        @endif">{{ $entry['type'] ?? 'System' }}</span>
                                </td>
                                <td><span title="{{ $entry['details'] ?? '' }}">{{ Str::limit($entry['details'] ?? '', 80) }}</span></td>
                                <td>{{ $entry['date_formatted'] ?? '' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">No user actions recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
