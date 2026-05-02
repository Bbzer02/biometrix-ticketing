@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm sm:overflow-hidden dark:border-blue-500/40 dark:bg-[#0b1020]">
        <div class="border-b border-slate-200 bg-white px-4 py-4 sm:flex sm:items-center sm:justify-between sm:px-6 dark:border-blue-500/30 dark:bg-[#0b1020]">
            <h1 class="text-xl font-semibold text-slate-900 sm:text-2xl dark:text-slate-50">Users</h1>
            <div class="mt-3 flex flex-wrap gap-2 sm:mt-0">
                <button type="button" id="add-user-btn"
                        class="mt-3 sm:mt-0 inline-flex cursor-pointer rounded-[0.9em] border-2 border-[#24b4fb] bg-[#24b4fb] px-4 py-3 text-base transition-all duration-200 ease-in-out hover:bg-[#0071e2] focus:outline-none focus:ring-2 focus:ring-[#24b4fb]/50 focus:ring-offset-1">
                    <span class="flex items-center justify-center gap-2 font-semibold text-white">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z" fill="currentColor"></path>
                        </svg>
                        Add user
                    </span>
                </button>
            </div>
        </div>
        <div class="p-4 sm:px-6 overflow-x-auto audit-trail-table-wrap">
            <table id="users-table" class="display tickets-table-admin" style="width:100%">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td class="font-medium text-slate-900 dark:text-slate-50">{{ $user->name }}</td>
                        <td class="text-slate-600 dark:text-slate-200">{{ $user->email }}</td>
                        <td class="text-slate-600 dark:text-slate-200">{{ $user->getRoleLabel() }}</td>
                        <td>
                            @if (!empty($onlineUserIds[$user->id]))
                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:border dark:border-emerald-500/30 dark:bg-emerald-950/60 dark:text-emerald-200">Online</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 dark:border dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">Offline</span>
                            @endif
                        </td>
                        <td>
                            <div class="table-action-icons">
                                <button type="button"
                                        class="user-edit-btn inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 active:bg-slate-300 touch-manipulation dark:border dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 dark:active:bg-slate-600"
                                        aria-label="Edit" title="Edit"
                                        data-user-id="{{ $user->id }}"
                                        data-user-name="{{ $user->name }}"
                                        data-user-email="{{ $user->email }}"
                                        data-user-role="{{ $user->role }}"
                                        data-user-has-password="{{ $user->hasPasswordSet() ? '1' : '0' }}"
                                        data-user-is-self="{{ $user->id === auth()->id() ? '1' : '0' }}"
                                        data-user-update-url="{{ route('users.update', $user) }}">
                                    <span class="sr-only">Edit</span>
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                @if ($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user) }}" method="post" class="inline swift-confirm-delete" data-confirm-message="Remove this user? This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 text-red-700 hover:bg-red-100 active:bg-red-200 touch-manipulation dark:border dark:border-red-500/30 dark:bg-red-950/50 dark:text-red-200 dark:hover:bg-red-950/70 dark:active:bg-red-950"
                                                aria-label="Delete" title="Delete">
                                            <span class="sr-only">Delete</span>
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11v6"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 11v6"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('modals')
@endpush

@push('scripts')
@endpush