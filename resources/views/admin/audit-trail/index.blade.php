@extends('layouts.app')

@section('title', 'Audit trail')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm sm:overflow-hidden dark:border-blue-500/40 dark:bg-[#0b1020]">
        <div class="border-b border-slate-200 bg-transparent px-4 py-4 sm:px-6 dark:border-blue-500/20">
            <h1 class="text-xl font-semibold text-slate-900 sm:text-2xl dark:text-white">Audit trail</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-white/50">View who worked on tickets and when.</p>
        </div>
        <div class="audit-trail-table-wrap">
            @if ($users->isNotEmpty())
            <table id="audit-users-table" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th class="whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->getRoleLabel() }}</td>
                            <td class="whitespace-nowrap">
                                <div class="table-action-icons">
                                    <button type="button"
                                            class="audit-view-btn inline-flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600 text-white hover:bg-blue-500 active:bg-blue-700 touch-manipulation"
                                            aria-label="View audit trail" title="View audit trail"
                                            data-url="{{ route('admin.audit-trail.show', $user) }}">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="px-4 py-8 text-center text-slate-500 sm:px-6 dark:text-white/40">No users yet.</div>
            @endif
        </div>
    </div>
@endsection

@push('modals')
@endpush
@push('scripts')
@endpush
