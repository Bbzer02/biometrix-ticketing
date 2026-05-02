@extends('layouts.app')

@section('title', 'Admin settings')

@section('content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm sm:overflow-hidden">
            <div class="border-b border-slate-200 bg-white px-4 py-4 sm:px-6">
                <h1 class="text-xl font-semibold text-slate-900 sm:text-2xl">Settings</h1>
                <p class="mt-1 text-sm text-slate-500">Theme and account security are in the Settings dialog from your profile menu.</p>
            </div>
            <div class="px-4 py-6 sm:px-6">
                <button type="button" id="admin-settings-page-open"
                        class="cursor-pointer transition-all bg-gray-700 text-white px-6 py-2.5 rounded-lg border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold inline-flex items-center">
                    Open Settings
                </button>
            </div>
        </div>

        <div class="rounded-2xl border border-red-200 bg-white shadow-sm sm:overflow-hidden">
            <div class="border-b border-red-200 bg-red-50/60 px-4 py-4 sm:px-6">
                <h2 class="text-lg font-semibold text-red-900">Danger zone</h2>
                <p class="mt-1 text-sm text-red-700">
                    Use this only when preparing a brand-new system handover.
                    It permanently deletes all tickets and audit trail history.
                </p>
            </div>
            <form method="POST" action="{{ route('admin.settings.reset-system-data') }}" class="space-y-4 px-4 py-6 sm:px-6" onsubmit="return confirm('This will permanently delete all tickets and audit trail records. Continue?');">
                @csrf
                <label for="reset-confirmation" class="block text-sm font-medium text-slate-700">
                    Type <span class="font-semibold">RESET</span> to confirm
                </label>
                <input
                    id="reset-confirmation"
                    name="confirmation"
                    type="text"
                    required
                    autocomplete="off"
                    placeholder="RESET"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200 sm:max-w-xs"
                >
                @error('confirmation')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300"
                >
                    Delete tickets and audit trail
                </button>
            </form>
        </div>
    </div>
    <script>
    (function() {
        var b = document.getElementById('admin-settings-page-open');
        var m = document.getElementById('js-open-admin-settings-modal');
        if (b && m) b.addEventListener('click', function() { m.click(); });
    })();
    </script>
@endsection
