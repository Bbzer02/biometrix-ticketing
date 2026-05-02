@extends('layouts.app')

@section('title', 'New staff announcement')

@section('content')
    <div class="max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h1 class="mb-4 text-2xl font-semibold text-slate-900">New staff announcement</h1>
        <p class="mb-6 text-sm text-slate-500">
            Send a message to Employees, Front Desk, or IT Staff. It will appear on their dashboard until they mark it as done.
        </p>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.staff-announcements.store') }}" method="post" class="space-y-5">
            @csrf

            <div>
                <label for="title" class="block text-sm font-medium text-slate-700">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                       class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="body" class="block text-sm font-medium text-slate-700">Message</label>
                <textarea name="body" id="body" rows="4" required
                          class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          placeholder="Describe what you need them to do or finish."></textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="audience" class="block text-sm font-medium text-slate-700">Audience</label>
                    <select name="audience" id="audience" required
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach($audiences as $value => $label)
                            <option value="{{ $value }}" {{ old('audience') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="priority" class="block text-sm font-medium text-slate-700">Priority</label>
                    <select name="priority" id="priority" required
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach($priorities as $value => $label)
                            <option value="{{ $value }}" {{ old('priority', 'normal') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="selected-users-wrap" class="{{ old('audience') === \App\Models\StaffAnnouncement::AUDIENCE_SELECTED_USERS ? '' : 'hidden' }}">
                <label for="selected_user_ids" class="block text-sm font-medium text-slate-700">Select users</label>
                <select name="selected_user_ids[]" id="selected_user_ids" multiple
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 min-h-[10rem]">
                    @foreach($selectableUsers as $u)
                        <option value="{{ $u->id }}" {{ collect(old('selected_user_ids', []))->contains($u->id) ? 'selected' : '' }}>
                            {{ $u->name }} ({{ \App\Models\User::roleLabel($u->role) }})
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">Hold Ctrl (or Cmd) to select multiple users.</p>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('admin.staff-announcements.index') }}"
                   class="cursor-pointer transition-all bg-white text-slate-700 px-5 py-2 rounded-lg border-slate-300 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-slate-200 shadow-slate-200 active:shadow-none text-sm font-semibold inline-flex items-center justify-center">
                    Cancel
                </a>
                <button type="submit"
                        class="cursor-pointer transition-all bg-gray-700 text-white px-5 py-2 rounded-lg border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold inline-flex items-center justify-center gap-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Send announcement
                </button>
            </div>
        </form>
    </div>
    <script>
        (function () {
            var audience = document.getElementById('audience');
            var wrap = document.getElementById('selected-users-wrap');
            var selected = document.getElementById('selected_user_ids');
            if (!audience || !wrap || !selected) return;
            function syncAudienceUi() {
                var useSelectedUsers = audience.value === '{{ \App\Models\StaffAnnouncement::AUDIENCE_SELECTED_USERS }}';
                wrap.classList.toggle('hidden', !useSelectedUsers);
                selected.required = useSelectedUsers;
                if (!useSelectedUsers) {
                    Array.prototype.forEach.call(selected.options, function (opt) { opt.selected = false; });
                }
            }
            audience.addEventListener('change', syncAudienceUi);
            syncAudienceUi();
        })();
    </script>
@endsection

