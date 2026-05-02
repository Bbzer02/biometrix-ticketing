@php
    $p = $priority ?? ($value ?? null);
    $p = is_string($p) ? trim($p) : '';
    $key = strtolower($p);

    $base = 'inline-flex items-center px-0 py-0 text-sm font-semibold tracking-wide';

    $color = match ($key) {
        'critical', 'high' => '#ef4444',
        'major', 'med' => '#f97316',
        'normal', 'low' => '#10b981',
        default => '#94a3b8',
    };
@endphp

<span class="{{ $base }}" style="color: {{ $color }};">
    {{ $p !== '' ? $p : '—' }}
</span>

