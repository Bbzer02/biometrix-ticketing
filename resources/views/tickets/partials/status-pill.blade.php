@php
    $status = $status ?? ($value ?? null);
    $label  = $label ?? ($statusLabel ?? null);

    $status = is_string($status) ? trim($status) : '';
    $label = is_string($label) ? trim($label) : '';
    if ($label === '') {
        $label = $status !== '' ? $status : '—';
    }

    $base = 'inline-flex items-center gap-2 rounded-lg px-2.5 py-0.5 text-xs font-semibold border';

    $classes = match ($status) {
        'open' => 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-500/30 dark:text-blue-100 dark:border-blue-300/60',
        'in_progress' => 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-500/30 dark:text-amber-100 dark:border-amber-300/60',
        'resolved' => 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-500/30 dark:text-emerald-100 dark:border-emerald-300/60',
        'cancelled' => 'bg-red-100 text-red-800 border-red-200 dark:bg-red-500/30 dark:text-red-100 dark:border-red-300/60',
        'closed' => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-700/35 dark:text-slate-200 dark:border-slate-500',
        default => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-700/35 dark:text-slate-200 dark:border-slate-500',
    };

    $dot = match ($status) {
        'open' => 'bg-blue-500',
        'in_progress' => 'bg-amber-500',
        'resolved' => 'bg-emerald-500',
        'cancelled' => 'bg-red-500',
        'closed' => 'bg-slate-500',
        default => 'bg-slate-500',
    };
@endphp

<span class="{{ $base }} {{ $classes }}">
    <span class="h-1.5 w-1.5 rounded-full {{ $dot }}"></span>
    <span>{{ $label }}</span>
</span>

