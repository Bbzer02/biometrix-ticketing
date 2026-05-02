@extends('layouts.app')

@section('title', 'Appearance')

@section('content')
    <div class="space-y-6">
        <div class="content-header">
            <h1 class="text-2xl font-semibold text-slate-900 sm:text-3xl">Appearance</h1>
            <p class="mt-1 text-sm text-slate-500">Preview how the helpdesk looks in light, dark, or system mode.</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/80">
                <h2 class="text-base font-semibold text-slate-900">Theme</h2>
                <p class="mt-0.5 text-sm text-slate-500">Choose how the app looks. Dark mode uses a dark background.</p>
            </div>
            <div class="p-6">
                <div class="grid gap-3 sm:grid-cols-3" role="radiogroup" aria-label="Theme">
                    {{-- Light --}}
                    <label class="theme-option group flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white px-3.5 py-3 cursor-pointer transition-all hover:bg-slate-50 hover:shadow has-[:checked]:border-blue-500 has-[:checked]:ring-2 has-[:checked]:ring-blue-500/20">
                        <input type="radio" name="theme" value="light" class="sr-only peer">
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span class="text-sm font-medium text-slate-700">Light</span>
                        </div>
                        <div class="mt-1 h-16 w-full rounded-xl border border-slate-200 bg-gradient-to-b from-slate-50 to-white flex overflow-hidden">
                            {{-- Dark sidebar (matches app) --}}
                            <div class="w-2/7 min-w-[22%] bg-slate-900 flex flex-col">
                                <div class="h-4 bg-slate-950/90 border-b border-slate-800/80"></div>
                                <div class="flex-1 px-1.5 py-1 flex flex-col gap-1.5">
                                    <div class="h-2 rounded-full bg-slate-700/90 w-3/4"></div>
                                    <div class="h-2 rounded-full bg-slate-700/60 w-2/3"></div>
                                    <div class="h-2 rounded-full bg-slate-700/40 w-4/5"></div>
                                </div>
                            </div>
                            {{-- Light main content --}}
                            <div class="flex-1 bg-gradient-to-b from-slate-50 to-white border-l border-slate-200/80 flex flex-col">
                                <div class="h-4 bg-white/90 border-b border-slate-200/80"></div>
                                <div class="flex-1 flex gap-1.5 p-1.5">
                                    <div class="w-1/3 rounded-lg bg-white border border-slate-200/80"></div>
                                    <div class="flex-1 rounded-lg bg-slate-50 border border-slate-200/80"></div>
                                </div>
                            </div>
                        </div>
                    </label>
                    {{-- Dark --}}
                    <label class="theme-option group flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white px-3.5 py-3 cursor-pointer transition-all hover:bg-slate-50 hover:shadow has-[:checked]:border-blue-500 has-[:checked]:ring-2 has-[:checked]:ring-blue-500/20">
                        <input type="radio" name="theme" value="dark" class="sr-only peer">
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                            <span class="text-sm font-medium text-slate-700">Dark</span>
                        </div>
                        <div class="mt-1 h-16 w-full rounded-xl border border-slate-800 bg-gradient-to-b from-slate-900 to-slate-950 flex overflow-hidden">
                            {{-- Dark sidebar (same as app) --}}
                            <div class="w-2/7 min-w-[22%] bg-slate-950 flex flex-col">
                                <div class="h-4 bg-slate-950 border-b border-slate-800/80"></div>
                                <div class="flex-1 px-1.5 py-1 flex flex-col gap-1.5">
                                    <div class="h-2 rounded-full bg-slate-700/90 w-3/4"></div>
                                    <div class="h-2 rounded-full bg-slate-700/70 w-2/3"></div>
                                    <div class="h-2 rounded-full bg-slate-700/60 w-4/5"></div>
                                </div>
                            </div>
                            {{-- Dark main content --}}
                            <div class="flex-1 bg-gradient-to-b from-slate-900 to-slate-950 border-l border-slate-800/80 flex flex-col">
                                <div class="h-4 bg-slate-900/90 border-b border-slate-800/80"></div>
                                <div class="flex-1 flex gap-1.5 p-1.5">
                                    <div class="w-1/3 rounded-lg bg-slate-800 border border-slate-700"></div>
                                    <div class="flex-1 rounded-lg bg-slate-900 border border-slate-700"></div>
                                </div>
                            </div>
                        </div>
                    </label>
                    {{-- System --}}
                    <label class="theme-option group flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white px-3.5 py-3 cursor-pointer transition-all hover:bg-slate-50 hover:shadow has-[:checked]:border-blue-500 has-[:checked]:ring-2 has-[:checked]:ring-blue-500/20">
                        <input type="radio" name="theme" value="system" class="sr-only peer">
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span class="text-sm font-medium text-slate-700">System</span>
                        </div>
                        <div class="mt-1 h-16 w-full rounded-xl border border-slate-200 bg-gradient-to-r from-slate-950 via-slate-800 to-white flex overflow-hidden">
                            {{-- Dark sidebar (always dark) --}}
                            <div class="w-2/7 min-w-[22%] bg-slate-950 flex flex-col">
                                <div class="h-4 bg-slate-950 border-b border-slate-800/80"></div>
                                <div class="flex-1 px-1.5 py-1 flex flex-col gap-1.5">
                                    <div class="h-2 rounded-full bg-slate-700/90 w-3/4"></div>
                                    <div class="h-2 rounded-full bg-slate-700/70 w-2/3"></div>
                                    <div class="h-2 rounded-full bg-slate-700/60 w-4/5"></div>
                                </div>
                            </div>
                            {{-- Split main content: dark + light to hint system --}}
                            <div class="flex-1 flex">
                                <div class="w-1/2 flex flex-col bg-gradient-to-b from-slate-900 to-slate-950 border-l border-slate-800/80">
                                    <div class="h-4 bg-slate-900/90 border-b border-slate-800/80"></div>
                                    <div class="flex-1 flex gap-1.5 p-1.5">
                                        <div class="w-1/3 rounded-lg bg-slate-800 border border-slate-700"></div>
                                        <div class="flex-1 rounded-lg bg-slate-900 border border-slate-700"></div>
                                    </div>
                                </div>
                                <div class="w-1/2 flex flex-col bg-gradient-to-b from-slate-50 to-white border-l border-slate-200/80">
                                    <div class="h-4 bg-white/90 border-b border-slate-200/80"></div>
                                    <div class="flex-1 flex gap-1.5 p-1.5">
                                        <div class="w-1/3 rounded-lg bg-white border border-slate-200/80"></div>
                                        <div class="flex-1 rounded-lg bg-slate-50 border border-slate-200/80"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

    </div>
@endsection
