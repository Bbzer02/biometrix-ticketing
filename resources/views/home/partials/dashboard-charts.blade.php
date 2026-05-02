{{-- Shared activity charts: same design as admin (gauge + line + bar). Used by Admin, IT Staff, Front Desk, Employee. --}}
@php
    $charts = $charts ?? null;
    $gaugeTitle = $gaugeTitle ?? 'Ticket status';
    $lineTitle = $lineTitle ?? 'Tickets per month';
    $barTitle = $barTitle ?? 'Top categories';
@endphp
@if(!empty($charts))
<div class="mt-4 grid gap-4 lg:grid-cols-3">
    <div class="dashboard-chart-card dashboard-status-card group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white p-4 shadow-lg shadow-slate-200/50 backdrop-blur-xl transition-shadow duration-200 dark:border-blue-400/35 dark:bg-gradient-to-br dark:from-white/[0.09] dark:via-white/[0.04] dark:to-blue-950/20 dark:shadow-[0_0_0_1px_rgba(59,130,246,0.15),0_14px_36px_-18px_rgba(37,99,235,0.85),0_0_40px_rgba(59,130,246,0.35)] dark:hover:border-blue-300/50 dark:hover:shadow-[0_0_0_1px_rgba(96,165,250,0.22),0_18px_44px_-18px_rgba(59,130,246,0.9),0_0_48px_rgba(59,130,246,0.4)]">
        <div class="pointer-events-none absolute -right-16 -top-16 h-40 w-40 rounded-full bg-blue-400/10 blur-3xl dark:bg-blue-500/20"></div>
        <div class="pointer-events-none absolute -bottom-12 -left-10 h-32 w-32 rounded-full bg-indigo-500/5 blur-2xl dark:bg-indigo-400/10"></div>
        <div class="relative mb-3 flex items-center justify-between">
            <h2 class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-600 dark:text-blue-100/90">{{ $gaugeTitle }}</h2>
            <span class="status-card-pill inline-flex items-center rounded-full border border-slate-200/80 bg-slate-100/90 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-600 shadow-sm dark:border-white/10 dark:bg-white/[0.08] dark:text-blue-200/80 dark:shadow-none">By status</span>
        </div>
        @php
            $statusData = $charts['status']['data'] ?? [0, 0, 0, 0, 0];
            $openCount = (int) ($statusData[0] ?? 0);
            $inProgress = (int) ($statusData[1] ?? 0);
            $resolved = (int) ($statusData[2] ?? 0);
            $closed = (int) ($statusData[3] ?? 0);
            $cancelled = (int) ($statusData[4] ?? 0);
            $total = max(0, $openCount + $inProgress + $resolved + $closed + $cancelled);
            $pct = static function (int $v, int $t): int {
                if ($t <= 0) return 0;
                return (int) round(($v / $t) * 100);
            };
            $pctInProgress = $pct($inProgress, $total);
            $pctResolved = $pct($resolved, $total);
            $pctClosed = $pct($closed, $total);
            $pctCancelled = $pct($cancelled, $total);
            $dash = static function (int $pctVal, int $radius): string {
                $circ = 2 * pi() * $radius;
                $filled = $circ * ($pctVal / 100);
                $remain = max($circ - $filled, 0);
                return number_format($filled, 1, '.', '') . ' ' . number_format($remain, 1, '.', '');
            };
            $arcPoint = static function (float $cx, float $cy, float $r, float $angleDeg): array {
                $rad = deg2rad($angleDeg);
                return [
                    'x' => $cx + $r * cos($rad),
                    'y' => $cy + $r * sin($rad),
                ];
            };
            $cx = 340.0; $cy = 238.0;
            $startDeg = -90.0;
            $ipEnd = $arcPoint($cx, $cy, 55.0, $startDeg + (360.0 * $pctInProgress / 100.0));
            $rsEnd = $arcPoint($cx, $cy, 95.0, $startDeg + (360.0 * $pctResolved / 100.0));
            $clEnd = $arcPoint($cx, $cy, 135.0, $startDeg + (360.0 * $pctClosed / 100.0));
            $cnEnd = $arcPoint($cx, $cy, 175.0, $startDeg + (360.0 * $pctCancelled / 100.0));
            // Start/end badges share the same spot when % is 0; for small % they sit too close — skip duplicate.
            $badgeSep = static function (float $sx, float $sy, float $ex, float $ey): float {
                return hypot($ex - $sx, $ey - $sy);
            };
            $minBadgeSep = 38.0;
            $ipSx = $cx; $ipSy = $cy - 55.0;
            $rsSx = $cx; $rsSy = $cy - 95.0;
            $clSx = $cx; $clSy = $cy - 135.0;
            $cnSx = $cx; $cnSy = $cy - 175.0;
            $ipSep = $badgeSep($ipSx, $ipSy, $ipEnd['x'], $ipEnd['y']);
            $rsSep = $badgeSep($rsSx, $rsSy, $rsEnd['x'], $rsEnd['y']);
            $clSep = $badgeSep($clSx, $clSy, $clEnd['x'], $clEnd['y']);
            $cnSep = $badgeSep($cnSx, $cnSy, $cnEnd['x'], $cnEnd['y']);
            $showIpStart = ($pctInProgress > 0 && $ipSep >= $minBadgeSep) || $pctInProgress <= 0;
            $showIpEnd = $pctInProgress > 0;
            $showRsStart = ($pctResolved > 0 && $rsSep >= $minBadgeSep) || $pctResolved <= 0;
            $showRsEnd = $pctResolved > 0;
            $showClStart = ($pctClosed > 0 && $clSep >= $minBadgeSep) || $pctClosed <= 0;
            $showClEnd = $pctClosed > 0;
            $showCnStart = ($pctCancelled > 0 && $cnSep >= $minBadgeSep) || $pctCancelled <= 0;
            $showCnEnd = $pctCancelled > 0;
        @endphp
        <div class="status-multi-ring relative mx-auto mt-1.5 flex w-full max-w-[280px] justify-center overflow-visible sm:max-w-[300px]">
            <svg class="status-ring-svg" width="100%" viewBox="120 20 440 450" role="img" style="transform:scale(1.0);transform-origin:center;">
              <title>Status Ring Infographic with Icon Badges</title>
              <desc>Concentric ring chart synced to live ticket counts.</desc>
              <defs>
                <filter id="status-ring-glow" x="-40%" y="-40%" width="180%" height="180%">
                  <feGaussianBlur in="SourceGraphic" stdDeviation="2.2" result="b"/>
                  <feMerge>
                    <feMergeNode in="b"/>
                    <feMergeNode in="SourceGraphic"/>
                  </feMerge>
                </filter>
              </defs>

              <circle cx="{{ $cx }}" cy="{{ $cy }}" r="175" fill="none" stroke="#CFE8FF" stroke-width="34" stroke-opacity="0.34"/>
              <circle cx="{{ $cx }}" cy="{{ $cy }}" r="135" fill="none" stroke="#C8F3E2" stroke-width="34" stroke-opacity="0.34"/>
              <circle cx="{{ $cx }}" cy="{{ $cy }}" r="95"  fill="none" stroke="#FFE2AE" stroke-width="34" stroke-opacity="0.34"/>
              <circle cx="{{ $cx }}" cy="{{ $cy }}" r="55"  fill="none" stroke="#DED9FF" stroke-width="34" stroke-opacity="0.52"/>

              <circle cx="{{ $cx }}" cy="{{ $cy }}" r="55"  fill="none" stroke="#9F95FF" stroke-width="34" stroke-dasharray="{{ $dash($pctInProgress, 55) }}" stroke-linecap="round" transform="rotate(-90 {{ $cx }} {{ $cy }})" filter="url(#status-ring-glow)"/>
              <circle cx="{{ $cx }}" cy="{{ $cy }}" r="95"  fill="none" stroke="#FFB347" stroke-width="34" stroke-dasharray="{{ $dash($pctResolved, 95) }}" stroke-linecap="round" transform="rotate(-90 {{ $cx }} {{ $cy }})" filter="url(#status-ring-glow)"/>
              <circle cx="{{ $cx }}" cy="{{ $cy }}" r="135" fill="none" stroke="#33C79A" stroke-width="34" stroke-dasharray="{{ $dash($pctClosed, 135) }}" stroke-linecap="round" transform="rotate(-90 {{ $cx }} {{ $cy }})" filter="url(#status-ring-glow)"/>
              <circle cx="{{ $cx }}" cy="{{ $cy }}" r="175" fill="none" stroke="#4FA8FF" stroke-width="34" stroke-dasharray="{{ $dash($pctCancelled, 175) }}" stroke-linecap="round" transform="rotate(-90 {{ $cx }} {{ $cy }})" filter="url(#status-ring-glow)"/>

              <circle cx="{{ $cx }}" cy="{{ $cy }}" r="34" fill="#D85A30"/>
              <text font-family="sans-serif" font-size="17" font-weight="600" x="{{ $cx }}" y="{{ $cy - 4 }}" text-anchor="middle" dominant-baseline="central" fill="#fff">{{ $openCount }}</text>
              <text font-family="sans-serif" font-size="10" x="{{ $cx }}" y="{{ $cy + 12 }}" text-anchor="middle" dominant-baseline="central" fill="#fff" opacity="0.85">items</text>

              <!-- Start icon badges (hidden when arc is short & end badge is shown instead) -->
              @if($showIpStart)
              <circle cx="{{ $ipSx }}" cy="{{ $ipSy }}" r="13" fill="#fff" stroke="#7F77DD" stroke-width="2"/>
              <circle cx="{{ $ipSx }}" cy="{{ $ipSy }}" r="7" fill="none" stroke="#7F77DD" stroke-width="1.5"/>
              <line x1="{{ $ipSx }}" y1="{{ $ipSy }}" x2="{{ $ipSx }}" y2="{{ $ipSy - 5 }}" stroke="#7F77DD" stroke-width="1.5" stroke-linecap="round"/>
              <line x1="{{ $ipSx }}" y1="{{ $ipSy }}" x2="{{ $ipSx + 4 }}" y2="{{ $ipSy }}" stroke="#7F77DD" stroke-width="1.5" stroke-linecap="round"/>
              @endif

              @if($showRsStart)
              <circle cx="{{ $rsSx }}" cy="{{ $rsSy }}" r="13" fill="#fff" stroke="#EF9F27" stroke-width="2"/>
              <path d="M{{ $rsSx - 6 }} {{ $rsSy }} L{{ $rsSx - 2 }} {{ $rsSy + 4 }} L{{ $rsSx + 6 }} {{ $rsSy - 4 }}" fill="none" stroke="#EF9F27" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
              @endif

              @if($showClStart)
              <circle cx="{{ $clSx }}" cy="{{ $clSy }}" r="13" fill="#fff" stroke="#1D9E75" stroke-width="2"/>
              <rect x="{{ $clSx - 5 }}" y="{{ $clSy + 1 }}" width="10" height="7" rx="1.8" fill="#1D9E75"/>
              <path d="M{{ $clSx - 4 }} {{ $clSy + 1 }} Q{{ $clSx - 4 }} {{ $clSy - 4 }} {{ $clSx }} {{ $clSy - 4 }} Q{{ $clSx + 4 }} {{ $clSy - 4 }} {{ $clSx + 4 }} {{ $clSy + 1 }}" fill="none" stroke="#1D9E75" stroke-width="1.6" stroke-linecap="round"/>
              @endif

              @if($showCnStart)
              <circle cx="{{ $cnSx }}" cy="{{ $cnSy }}" r="13" fill="#fff" stroke="#378ADD" stroke-width="2"/>
              <path d="M{{ $cnSx - 6 }} {{ $cnSy - 6 }} L{{ $cnSx + 6 }} {{ $cnSy + 6 }} M{{ $cnSx + 6 }} {{ $cnSy - 6 }} L{{ $cnSx - 6 }} {{ $cnSy + 6 }}" fill="none" stroke="#378ADD" stroke-width="1.9" stroke-linecap="round"/>
              @endif

              <!-- End icon badges -->
              @if($showIpEnd)
              <circle cx="{{ number_format($ipEnd['x'], 1, '.', '') }}" cy="{{ number_format($ipEnd['y'], 1, '.', '') }}" r="13" fill="#fff" stroke="#7F77DD" stroke-width="2"/>
              <circle cx="{{ number_format($ipEnd['x'], 1, '.', '') }}" cy="{{ number_format($ipEnd['y'], 1, '.', '') }}" r="7" fill="none" stroke="#7F77DD" stroke-width="1.5"/>
              <line x1="{{ number_format($ipEnd['x'], 1, '.', '') }}" y1="{{ number_format($ipEnd['y'], 1, '.', '') }}" x2="{{ number_format($ipEnd['x'], 1, '.', '') }}" y2="{{ number_format($ipEnd['y'] - 5, 1, '.', '') }}" stroke="#7F77DD" stroke-width="1.5" stroke-linecap="round"/>
              <line x1="{{ number_format($ipEnd['x'], 1, '.', '') }}" y1="{{ number_format($ipEnd['y'], 1, '.', '') }}" x2="{{ number_format($ipEnd['x'] + 4, 1, '.', '') }}" y2="{{ number_format($ipEnd['y'], 1, '.', '') }}" stroke="#7F77DD" stroke-width="1.5" stroke-linecap="round"/>
              @endif

              @if($showRsEnd)
              <circle cx="{{ number_format($rsEnd['x'], 1, '.', '') }}" cy="{{ number_format($rsEnd['y'], 1, '.', '') }}" r="13" fill="#fff" stroke="#EF9F27" stroke-width="2"/>
              <path d="M{{ number_format($rsEnd['x'] - 6, 1, '.', '') }} {{ number_format($rsEnd['y'], 1, '.', '') }} L{{ number_format($rsEnd['x'] - 2, 1, '.', '') }} {{ number_format($rsEnd['y'] + 4, 1, '.', '') }} L{{ number_format($rsEnd['x'] + 6, 1, '.', '') }} {{ number_format($rsEnd['y'] - 4, 1, '.', '') }}" fill="none" stroke="#EF9F27" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
              @endif

              @if($showClEnd)
              <circle cx="{{ number_format($clEnd['x'], 1, '.', '') }}" cy="{{ number_format($clEnd['y'], 1, '.', '') }}" r="13" fill="#fff" stroke="#1D9E75" stroke-width="2"/>
              <rect x="{{ number_format($clEnd['x'] - 5, 1, '.', '') }}" y="{{ number_format($clEnd['y'] + 1, 1, '.', '') }}" width="10" height="7" rx="1.8" fill="#1D9E75"/>
              <path d="M{{ number_format($clEnd['x'] - 4, 1, '.', '') }} {{ number_format($clEnd['y'] + 1, 1, '.', '') }} Q{{ number_format($clEnd['x'] - 4, 1, '.', '') }} {{ number_format($clEnd['y'] - 4, 1, '.', '') }} {{ number_format($clEnd['x'], 1, '.', '') }} {{ number_format($clEnd['y'] - 4, 1, '.', '') }} Q{{ number_format($clEnd['x'] + 4, 1, '.', '') }} {{ number_format($clEnd['y'] - 4, 1, '.', '') }} {{ number_format($clEnd['x'] + 4, 1, '.', '') }} {{ number_format($clEnd['y'] + 1, 1, '.', '') }}" fill="none" stroke="#1D9E75" stroke-width="1.6" stroke-linecap="round"/>
              @endif

              @if($showCnEnd)
              <circle cx="{{ number_format($cnEnd['x'], 1, '.', '') }}" cy="{{ number_format($cnEnd['y'], 1, '.', '') }}" r="13" fill="#fff" stroke="#378ADD" stroke-width="2"/>
              <path d="M{{ number_format($cnEnd['x'] - 6, 1, '.', '') }} {{ number_format($cnEnd['y'] - 6, 1, '.', '') }} L{{ number_format($cnEnd['x'] + 6, 1, '.', '') }} {{ number_format($cnEnd['y'] + 6, 1, '.', '') }} M{{ number_format($cnEnd['x'] + 6, 1, '.', '') }} {{ number_format($cnEnd['y'] - 6, 1, '.', '') }} L{{ number_format($cnEnd['x'] - 6, 1, '.', '') }} {{ number_format($cnEnd['y'] + 6, 1, '.', '') }}" fill="none" stroke="#378ADD" stroke-width="1.9" stroke-linecap="round"/>
              @endif

              <text class="status-pct-label" font-family="sans-serif" font-size="12" font-weight="600" x="410" y="167" fill="#6D63D8">{{ $pctInProgress }}%</text>
              <text class="status-pct-label" font-family="sans-serif" font-size="12" font-weight="600" x="449" y="127" fill="#C3842F">{{ $pctResolved }}%</text>
              <text class="status-pct-label" font-family="sans-serif" font-size="12" font-weight="600" x="489" y="87" fill="#149171">{{ $pctClosed }}%</text>
              <text class="status-pct-label" font-family="sans-serif" font-size="12" font-weight="600" x="367" y="47" fill="#2F76BB">{{ $pctCancelled }}%</text>
            </svg>
        </div>
        <p class="relative mt-2 text-center text-[10px] font-medium tracking-wide text-slate-500 dark:text-blue-200/70">Share of tickets</p>
        <div class="status-legend relative mt-2 flex flex-wrap justify-center gap-x-2.5 gap-y-1.5 rounded-xl border border-slate-200/70 bg-slate-50/90 px-2.5 py-2 text-[10px] font-medium text-slate-600 shadow-inner shadow-white/40 dark:border-white/[0.07] dark:bg-white/[0.04] dark:text-slate-200 dark:shadow-none">
            <span class="inline-flex items-center gap-1.5">
                <span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-[#D85A30] text-[8px] font-bold text-white">01</span>
                {{ $charts['status']['labels'][0] ?? 'Open' }}
            </span>
            <span class="inline-flex items-center gap-1.5">
                <svg class="h-4 w-4" viewBox="0 0 20 20" aria-hidden="true"><circle cx="10" cy="10" r="9" fill="#fff" stroke="#7F77DD" stroke-width="2"/><circle cx="10" cy="10" r="5" fill="none" stroke="#7F77DD" stroke-width="1.5"/><line x1="10" y1="10" x2="10" y2="6.5" stroke="#7F77DD" stroke-width="1.5" stroke-linecap="round"/><line x1="10" y1="10" x2="13" y2="10" stroke="#7F77DD" stroke-width="1.5" stroke-linecap="round"/></svg>
                {{ $charts['status']['labels'][1] ?? 'In progress' }}
            </span>
            <span class="inline-flex items-center gap-1.5">
                <svg class="h-4 w-4" viewBox="0 0 20 20" aria-hidden="true"><circle cx="10" cy="10" r="9" fill="#fff" stroke="#EF9F27" stroke-width="2"/><path d="M6 10 L9 13 L14 7.8" fill="none" stroke="#EF9F27" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ $charts['status']['labels'][2] ?? 'Resolved' }}
            </span>
            <span class="inline-flex items-center gap-1.5">
                <svg class="h-4 w-4" viewBox="0 0 20 20" aria-hidden="true"><circle cx="10" cy="10" r="9" fill="#fff" stroke="#1D9E75" stroke-width="2"/><rect x="7" y="10" width="6" height="4.5" rx="1" fill="#1D9E75"/><path d="M8 10 Q8 7.3 10 7.3 Q12 7.3 12 10" fill="none" stroke="#1D9E75" stroke-width="1.4" stroke-linecap="round"/></svg>
                {{ $charts['status']['labels'][3] ?? 'Closed' }}
            </span>
            <span class="inline-flex items-center gap-1.5">
                <svg class="h-4 w-4" viewBox="0 0 20 20" aria-hidden="true"><circle cx="10" cy="10" r="9" fill="#fff" stroke="#378ADD" stroke-width="2"/><path d="M6.7 6.7 L13.3 13.3 M13.3 6.7 L6.7 13.3" fill="none" stroke="#378ADD" stroke-width="1.8" stroke-linecap="round"/></svg>
                {{ $charts['status']['labels'][4] ?? 'Cancelled' }}
            </span>
        </div>
    </div>
    <div class="dashboard-chart-card dashboard-chart-card-2 flex min-h-[236px] flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-lg backdrop-blur-xl dark:border-blue-500/40 dark:bg-white/5">
        <div class="mb-1 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white/90">{{ $lineTitle }}</h2>
            <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-500/20 dark:text-blue-400">Trend</span>
        </div>
        <div id="monthly-ticker" class="mb-2 flex items-baseline justify-between rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 dark:border-blue-500/40 dark:bg-white/5">
            <div class="flex items-baseline gap-2">
                <span class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-white/50">This month</span>
                <span id="monthly-ticker-value" class="text-xl font-bold tabular-nums text-slate-900 dark:text-white">—</span>
                <span class="text-sm text-slate-500 dark:text-white/50">tickets</span>
            </div>
            <div id="monthly-ticker-trend" class="text-sm font-semibold tabular-nums"></div>
        </div>
        <div class="mt-1 flex-1 min-h-[150px]">
            <canvas id="chart-monthly" class="h-full w-full"></canvas>
        </div>
    </div>
    <div class="dashboard-chart-card dashboard-chart-card-3 flex min-h-[236px] flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-lg backdrop-blur-xl dark:border-blue-500/40 dark:bg-white/5">
        <div class="mb-1 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white/90">{{ $barTitle }}</h2>
            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400">Load</span>
        </div>
        <p class="mb-2 text-xs text-slate-500 dark:text-white/40">A bar chart provides a way of showing data values represented as vertical bars.</p>
        <div class="mt-1 flex-1 min-h-[162px]">
            <canvas id="chart-category" class="h-full w-full"></canvas>
        </div>
    </div>
</div>
<script type="application/json" id="dashboard-charts-json">{!! json_encode($charts) !!}</script>
{{-- Charts are initialized globally in the layout (so it works with AJAX navigation). --}}
@endif
