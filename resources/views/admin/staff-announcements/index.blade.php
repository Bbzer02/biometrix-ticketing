@extends('layouts.app')

@section('title', 'Staff announcements')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Staff announcements</h1>
            <p class="mt-1 text-sm text-slate-500">Send messages to Employees, Front Desk, or IT Staff. When they mark it done, status becomes "All good".</p>
        </div>
        <button type="button" id="new-announcement-btn"
                class="cursor-pointer transition-all bg-gray-700 text-white px-5 py-2 rounded-lg border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold inline-flex items-center gap-2">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New announcement
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-blue-500/40 dark:bg-[#0b1020]">
        <div class="p-4 sm:px-6 overflow-x-auto audit-trail-table-wrap tickets-table-scroll-wrap">
        <table id="staff-announcements-table" class="display tickets-table-admin" style="width:100%">
            <thead class="bg-slate-50 dark:bg-white/5">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-white/70">Title</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-white/70">Audience</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-white/70">Users</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-white/70">From</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-white/70">Priority</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-white/70">Marked done by</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-white/70">Status</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-white/70">Created</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-white/70"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-blue-500/10">
                @forelse($announcements as $a)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-semibold text-slate-900 dark:text-white">{{ $a->title }}</div>
                            <div class="mt-0.5 line-clamp-2 text-xs text-slate-500 dark:text-white/50">{{ $a->body }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-white/70">
                            {{ \App\Models\StaffAnnouncement::audienceLabel($a->audience) }}
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-600 dark:text-white/70">
                            @php $targetUsers = $audienceUsers[$a->id] ?? collect(); @endphp
                            @if($targetUsers->isNotEmpty())
                                <div class="flex flex-wrap gap-1">
                                    @foreach($targetUsers->take(3) as $name)
                                        <span class="inline-flex rounded-full bg-slate-200 px-2 py-0.5 text-[11px] font-medium text-slate-700 dark:bg-white/10 dark:text-white/70">{{ $name }}</span>
                                    @endforeach
                                    @if($targetUsers->count() > 3)
                                        <span class="inline-flex rounded-full bg-slate-200 px-2 py-0.5 text-[11px] font-medium text-slate-600 dark:bg-white/10 dark:text-white/50">+{{ $targetUsers->count() - 3 }} more</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-slate-400 dark:text-white/30">No users found</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-white/70">
                            {{ $a->creator?->name ?? 'Unknown user' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full bg-slate-200 px-2.5 py-0.5 text-xs font-medium text-slate-700 dark:bg-white/10 dark:text-white/70">
                                {{ ucfirst($a->priority) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-600 dark:text-white/70">
                            @if($a->acknowledgements_count > 0)
                                @php $names = $a->acknowledgements->pluck('user.name')->filter()->values(); @endphp
                                <div class="flex flex-wrap gap-1">
                                    @foreach($names->take(3) as $name)
                                        <span class="inline-flex rounded-full bg-slate-200 px-2 py-0.5 text-[11px] font-medium text-slate-700 dark:bg-white/10 dark:text-white/70">{{ $name }}</span>
                                    @endforeach
                                    @if($names->count() > 3)
                                        <span class="inline-flex rounded-full bg-slate-200 px-2 py-0.5 text-[11px] font-medium text-slate-600 dark:bg-white/10 dark:text-white/50">+{{ $names->count() - 3 }} more</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-slate-400 dark:text-white/30">No one yet</span>
                            @endif
                        </td>
                        @php
                            $totalExpected = $audienceTotals[$a->id] ?? null;
                            $ackCount = $a->acknowledgements_count;
                        @endphp
                        <td class="px-4 py-3">
                            @if($totalExpected && $ackCount >= $totalExpected && $totalExpected > 0)
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                    <span class="mr-1 h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Done
                                </span>
                            @elseif($ackCount > 0)
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700">
                                    <span class="mr-1 h-1.5 w-1.5 rounded-full bg-blue-500"></span>In progress ({{ $ackCount }})
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                                    <span class="mr-1 h-1.5 w-1.5 rounded-full bg-amber-500"></span>Waiting
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500 dark:text-white/50" data-order="{{ $a->created_at?->timestamp ?? 0 }}">{{ $a->created_at?->diffForHumans() }}</td>
                        <td class="px-4 py-3">
                            <button type="button"
                                    class="ann-delete-btn inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/20 dark:hover:text-red-300"
                                    data-id="{{ $a->id }}"
                                    data-url="{{ route('admin.staff-announcements.destroy', $a) }}"
                                    title="Delete announcement" aria-label="Delete">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-white/40">No staff announcements yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
@endsection


