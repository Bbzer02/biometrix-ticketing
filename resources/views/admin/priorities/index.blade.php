@extends('layouts.app')

@section('title', 'Priorities')

@section('content')
    <div class="rounded-2xl border border-blue-200 bg-blue-50/40 shadow-sm sm:overflow-hidden dark:border-blue-400/40 dark:bg-blue-500/10">
        <div class="border-b border-blue-200 bg-blue-50/50 px-4 py-4 sm:flex sm:items-center sm:justify-between sm:px-6 dark:border-blue-400/30 dark:bg-blue-500/10">
            <h1 class="text-xl font-semibold text-slate-900 sm:text-2xl">Priority levels</h1>
            <a href="{{ route('admin.priorities.create') }}"
               class="cursor-pointer transition-all bg-gray-700 text-white px-6 py-2 rounded-lg border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold inline-flex items-center">
                New priority
            </a>
        </div>

        <div class="p-4 sm:px-6 overflow-x-auto audit-trail-table-wrap">
            <table id="priorities-table" class="display tickets-table-admin" style="width:100%">
                <thead>
                <tr>
                    <th>Key</th>
                    <th>Label</th>
                    <th>Description</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($priorities as $p)
                    <tr>
                        <td class="font-medium text-slate-900">{{ $p->key }}</td>
                        <td class="text-slate-900">{{ $p->label }}</td>
                        <td class="text-slate-600">{{ \Illuminate\Support\Str::limit($p->description, 80) }}</td>
                        <td class="text-slate-600">{{ $p->sort_order }}</td>
                        <td>
                            @if($p->active)
                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">Active</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="table-action-icons">
                                <a href="{{ route('admin.priorities.edit', $p) }}"
                                   class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 active:bg-slate-300 touch-manipulation"
                                   aria-label="Edit"
                                   title="Edit">
                                    <span class="sr-only">Edit</span>
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <form action="{{ route('admin.priorities.destroy', $p) }}" method="post" class="inline swift-confirm-delete" data-confirm-message="Delete this priority?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 text-red-700 hover:bg-red-100 active:bg-red-200 touch-manipulation"
                                            aria-label="Delete"
                                            title="Delete">
                                        <span class="sr-only">Delete</span>
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11v6"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 11v6"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

