{{-- Front Desk: charts (top), stats, quick actions --}}
@if(!empty($charts))
    @include('home.partials.dashboard-charts', [
        'charts' => $charts,
        'gaugeTitle' => 'Status (tickets I logged)',
        'lineTitle' => 'Tickets I logged per month',
        'barTitle' => 'Categories I logged',
    ])
@endif

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="dashboard-stat-card group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/95 p-5 text-slate-900 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md dark:border-blue-400/35 dark:bg-gradient-to-br dark:from-[#0f1b3d] dark:via-[#0a1633] dark:to-[#081127] dark:text-slate-100 dark:shadow-[0_0_0_1px_rgba(59,130,246,0.15),0_14px_36px_-18px_rgba(37,99,235,0.85)] dark:hover:border-blue-300/55 dark:hover:shadow-[0_0_0_1px_rgba(96,165,250,0.26),0_18px_44px_-18px_rgba(59,130,246,0.95)]">
        <div class="pointer-events-none absolute -right-8 -top-10 h-24 w-24 rounded-full bg-slate-400/15 blur-2xl"></div>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-600 dark:text-blue-100/70">Tickets I logged</p>
                <p class="mt-1 text-3xl font-bold leading-none text-slate-900 dark:text-blue-100">{{ $stats['logged_by_me'] }}</p>
            </div>
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-slate-300/35 bg-slate-400/10 text-slate-100 shadow-[0_0_16px_rgba(148,163,184,0.32)]">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
        </div>
    </div>
    <div class="dashboard-stat-card group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/95 p-5 text-slate-900 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md dark:border-blue-400/35 dark:bg-gradient-to-br dark:from-[#0f1b3d] dark:via-[#0a1633] dark:to-[#081127] dark:text-slate-100 dark:shadow-[0_0_0_1px_rgba(59,130,246,0.15),0_14px_36px_-18px_rgba(37,99,235,0.85)] dark:hover:border-blue-300/55 dark:hover:shadow-[0_0_0_1px_rgba(96,165,250,0.26),0_18px_44px_-18px_rgba(59,130,246,0.95)]">
        <div class="pointer-events-none absolute -right-8 -top-10 h-24 w-24 rounded-full bg-sky-400/20 blur-2xl"></div>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-600 dark:text-blue-100/70">Open</p>
                <p class="mt-1 text-3xl font-bold leading-none text-sky-600 dark:text-sky-300">{{ $stats['open'] }}</p>
            </div>
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-sky-300/40 bg-sky-400/15 text-sky-200 shadow-[0_0_18px_rgba(56,189,248,0.45)]">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="dashboard-stat-card group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/95 p-5 text-slate-900 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md dark:border-blue-400/35 dark:bg-gradient-to-br dark:from-[#0f1b3d] dark:via-[#0a1633] dark:to-[#081127] dark:text-slate-100 dark:shadow-[0_0_0_1px_rgba(59,130,246,0.15),0_14px_36px_-18px_rgba(37,99,235,0.85)] dark:hover:border-blue-300/55 dark:hover:shadow-[0_0_0_1px_rgba(96,165,250,0.26),0_18px_44px_-18px_rgba(59,130,246,0.95)]">
        <div class="pointer-events-none absolute -right-8 -top-10 h-24 w-24 rounded-full bg-amber-400/18 blur-2xl"></div>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-600 dark:text-blue-100/70">In progress</p>
                <p class="mt-1 text-3xl font-bold leading-none text-amber-600 dark:text-amber-300">{{ $stats['in_progress'] }}</p>
            </div>
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-amber-300/40 bg-amber-400/15 text-amber-100 shadow-[0_0_18px_rgba(251,191,36,0.4)]">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
        </div>
    </div>
    <div class="dashboard-stat-card group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/95 p-5 text-slate-900 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md dark:border-blue-400/35 dark:bg-gradient-to-br dark:from-[#0f1b3d] dark:via-[#0a1633] dark:to-[#081127] dark:text-slate-100 dark:shadow-[0_0_0_1px_rgba(59,130,246,0.15),0_14px_36px_-18px_rgba(37,99,235,0.85)] dark:hover:border-blue-300/55 dark:hover:shadow-[0_0_0_1px_rgba(96,165,250,0.26),0_18px_44px_-18px_rgba(59,130,246,0.95)]">
        <div class="pointer-events-none absolute -right-8 -top-10 h-24 w-24 rounded-full bg-emerald-400/18 blur-2xl"></div>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-600 dark:text-blue-100/70">Resolved</p>
                <p class="mt-1 text-3xl font-bold leading-none text-emerald-600 dark:text-emerald-300">{{ $stats['resolved'] }}</p>
            </div>
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-emerald-300/40 bg-emerald-400/15 text-emerald-100 shadow-[0_0_18px_rgba(52,211,153,0.42)]">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
</div>


