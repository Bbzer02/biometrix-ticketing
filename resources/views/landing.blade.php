@php
use App\Models\Ticket;

$activeStatuses = [Ticket::STATUS_OPEN, Ticket::STATUS_IN_PROGRESS];
$priorityUiMap = [
    Ticket::PRIORITY_CRITICAL => 'critical',
    Ticket::PRIORITY_MAJOR => 'high',
    Ticket::PRIORITY_NORMAL => 'medium',
    Ticket::PRIORITY_LOW => 'low',
];
$priorityLabelMap = [
    Ticket::PRIORITY_CRITICAL => 'CRITICAL',
    Ticket::PRIORITY_MAJOR => 'HIGH',
    Ticket::PRIORITY_NORMAL => 'MEDIUM',
    Ticket::PRIORITY_LOW => 'LOW',
];
$priorityVisualMap = [
    'critical' => ['iconKey' => 'fingerprint', 'iconBg' => 'var(--rose-soft)', 'statusColor' => 'var(--rose)'],
    'high' => ['iconKey' => 'lock', 'iconBg' => 'var(--amber-soft)', 'statusColor' => 'var(--amber)'],
    'medium' => ['iconKey' => 'chart', 'iconBg' => 'var(--blue-soft)', 'statusColor' => 'var(--blue)'],
    'low' => ['iconKey' => 'faceId', 'iconBg' => 'var(--emerald-soft)', 'statusColor' => 'var(--emerald)'],
];

$activeTickets = Ticket::query()
    ->whereIn('status', $activeStatuses)
    ->whereDate('created_at', now()->toDateString())
    ->latest()
    ->limit(5)
    ->get(['ticket_number', 'title', 'priority', 'created_at']);

$landingTicketData = $activeTickets->map(function (Ticket $ticket) use ($priorityUiMap, $priorityLabelMap, $priorityVisualMap) {
    $uiPriority = $priorityUiMap[$ticket->priority] ?? 'medium';
    $visual = $priorityVisualMap[$uiPriority] ?? $priorityVisualMap['medium'];

    return [
        'id' => $ticket->ticket_number ?: ('TKT-' . $ticket->id),
        'title' => $ticket->title ?: 'Untitled ticket',
        'pri' => $uiPriority,
        'priLabel' => $priorityLabelMap[$ticket->priority] ?? strtoupper((string) $ticket->priority),
        'time' => $ticket->created_at ? max(1, $ticket->created_at->diffInMinutes(now())) . 'm' : '0m',
        'iconKey' => $visual['iconKey'],
        'iconBg' => $visual['iconBg'],
        'statusColor' => $visual['statusColor'],
        'isPlaceholder' => false,
    ];
})->values();

while ($landingTicketData->count() < 5) {
    $landingTicketData->push([
        'id' => '—',
        'title' => 'No active ticket logged today',
        'pri' => 'low',
        'priLabel' => 'QUEUE',
        'time' => 'now',
        'iconKey' => 'ticket',
        'iconBg' => 'var(--surface)',
        'statusColor' => 'var(--text-muted)',
        'isPlaceholder' => true,
    ]);
}

$resolvedTicketsCount = Ticket::query()
    ->whereIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED])
    ->count();
$ticketVolume24h = Ticket::query()
    ->where('created_at', '>=', now()->subDay())
    ->count();
$criticalActiveCount = Ticket::query()
    ->whereIn('status', $activeStatuses)
    ->where('priority', Ticket::PRIORITY_CRITICAL)
    ->count();

$resolutionSamples = Ticket::query()
    ->whereNotNull('resolved_at')
    ->latest('resolved_at')
    ->limit(200)
    ->get(['created_at', 'resolved_at']);
$avgResolutionMinutes = round((float) $resolutionSamples
    ->map(fn (Ticket $ticket) => $ticket->created_at ? $ticket->created_at->diffInMinutes($ticket->resolved_at) : null)
    ->filter(fn ($minutes) => $minutes !== null && $minutes >= 0)
    ->avg(), 1);
$avgResolutionMinutes = $avgResolutionMinutes > 0 ? $avgResolutionMinutes : 0.0;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Biometrix — Intelligent Ticketing System</title>
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('main-logo-icon.png') }}?v={{ now()->timestamp }}">
<link rel="shortcut icon" type="image/png" href="{{ asset('main-logo-icon.png') }}?v={{ now()->timestamp }}">
<link rel="apple-touch-icon" href="{{ asset('main-logo-icon.png') }}?v={{ now()->timestamp }}">
<link rel="preload" as="image" href="{{ asset('image/bstc-logo.png') }}" fetchpriority="high">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&family=Syne:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
  --bg:#060609;--surface:#0c0c11;--card:#111116;--elevated:#17171e;
  --border:#1c1c25;--border-hover:#2a2a38;
  --text-primary:#f0f0f4;--text-secondary:#9d9db0;--text-muted:#636378;
  --emerald:#2d6bcf;--emerald-soft:#2d6bcf14;--emerald-med:#2d6bcf32;
  --teal:#2563eb;--teal-soft:#2563eb12;
  --blue:#6366f1;--blue-soft:#6366f112;--blue-med:#6366f130;
  --sky:#38bdf8;--sky-soft:#38bdf812;
  --amber:#f59e0b;--amber-soft:#f59e0b12;
  --rose:#f43f5e;--rose-soft:#f43f5e12;
  --violet:#8b5cf6;--violet-soft:#8b5cf612;
  --lime:#84cc16;--lime-soft:#84cc1612;
  --cyan:#06b6d4;--cyan-soft:#06b6d412;
  --white08:rgba(255,255,255,.08);--white04:rgba(255,255,255,.04);
}
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--text-secondary);font-family:'Outfit',sans-serif;line-height:1.6;overflow-x:hidden;-webkit-font-smoothing:antialiased}

/* Animated Background */
#bg-canvas{position:fixed;inset:0;z-index:0}
.grid-overlay{position:fixed;inset:0;z-index:1;pointer-events:none;
  background-image:linear-gradient(rgba(59,130,246,0.045) 1px, transparent 1px),linear-gradient(90deg, rgba(59,130,246,0.045) 1px, transparent 1px);
  background-size:48px 48px}
.blob{position:fixed;border-radius:50%;filter:blur(90px);pointer-events:none;z-index:1;animation:drift 9s ease-in-out infinite alternate}
.blob-1{width:520px;height:520px;top:-140px;left:-120px;background:rgba(59,130,246,0.13);animation-delay:0s}
.blob-2{width:400px;height:400px;bottom:-100px;right:-80px;background:rgba(16,185,129,0.09);animation-delay:-3.5s}
.blob-3{width:300px;height:300px;top:35%;left:60%;background:rgba(59,130,246,0.08);animation-delay:-6s}
@keyframes drift{from{transform:translate(0,0) scale(1)}to{transform:translate(24px,18px) scale(1.06)}}

/* Right-slide login modal */
.login-modal-backdrop{
  position:fixed;inset:0;z-index:220;background:rgba(2,6,12,.62);backdrop-filter:blur(4px);
  opacity:0;pointer-events:none;transition:opacity .25s ease;
}
.login-drawer{
  position:fixed;top:0;right:0;height:100vh;width:min(460px,100vw);z-index:230;
  background:rgba(8,12,18,.88);border-left:1px solid rgba(59,130,246,.2);
  transform:translateX(100%);transition:transform .3s ease;display:flex;align-items:center;justify-content:center;padding:18px;
}
.login-open .login-modal-backdrop{opacity:1;pointer-events:auto}
.login-open .login-drawer{transform:translateX(0)}
.login-close-btn{position:absolute;top:16px;right:16px}
.login-card{
  width:100%;max-width:390px;background:rgba(12,18,28,.85);border:1px solid rgba(59,130,246,.15);border-radius:20px;
  padding:30px 24px;backdrop-filter:blur(20px);box-shadow:0 0 0 1px rgba(59,130,246,.05),0 26px 70px rgba(0,0,0,.55);
}
.login-brand-row{
  display:flex;align-items:center;gap:10px;margin-bottom:16px;
}
.login-brand-logo{
  width:48px;height:48px;object-fit:contain;flex-shrink:0;display:block;border-radius:8px;
}
.login-brand-text{
  display:flex;flex-direction:column;line-height:1.1;
}
.login-brand-title{
  font-family:'Syne',sans-serif;color:#fff;font-size:18px;font-weight:700;
}
.login-brand-sub{
  font-size:11px;color:#93c5fd;letter-spacing:.06em;text-transform:uppercase;
}
.login-title{font-family:'Syne',sans-serif;color:#fff;font-size:22px;font-weight:700}
.login-sub{font-size:13px;color:#6B7A94;margin-top:4px}
.login-divider{display:flex;align-items:center;gap:12px;color:#6B7A94;font-size:12px;margin:18px 0}
.login-divider::before,.login-divider::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.07)}
.login-field{margin-bottom:14px}
.login-label{font-size:12px;color:#6B7A94;margin-bottom:6px}
.login-input{width:100%;padding:11px 12px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:#E8EDF5;outline:none}
.login-input:focus{border-color:rgba(96,165,250,.45);background:rgba(59,130,246,.06)}
.login-submit{width:100%;padding:12px;background:#3B82F6;border:0;border-radius:10px;color:#fff;font-family:'Syne',sans-serif;font-weight:600;cursor:pointer;margin-top:4px}
.login-account-hint{margin-top:16px;font-size:12px;line-height:1.5;color:#6B7A94;text-align:center}
.login-error{border-radius:10px;padding:10px 12px;background:rgba(239,68,68,.14);border:1px solid rgba(239,68,68,.35);color:#fca5a5;font-size:12px;margin-bottom:12px}
.login-forgot-wrap{margin-top:10px;border-radius:10px;padding:10px 12px;background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.3)}
.login-forgot-text{font-size:12px;color:#bfdbfe;line-height:1.5}
.login-forgot-btn{margin-top:8px;width:100%;padding:9px 12px;border-radius:9px;border:1px solid rgba(59,130,246,.45);background:rgba(59,130,246,.14);color:#dbeafe;font-size:12px;font-weight:600;cursor:pointer}
.login-forgot-btn:disabled{opacity:.65;cursor:not-allowed}
.login-forgot-msg{margin-top:8px;font-size:12px;color:#93c5fd;line-height:1.4}

/* New-user set-password modal (landing) */
.sp-modal-root{position:fixed;inset:0;z-index:250;display:flex;align-items:center;justify-content:center;padding:1rem}
.sp-modal-root .spm-backdrop{position:absolute;inset:0;background:rgba(2,6,12,.62);backdrop-filter:blur(4px)}
.sp-modal-card{
  position:relative;width:100%;max-width:min(28rem,calc(100vw - 2rem));border-radius:1rem;border:1px solid rgba(59,130,246,.2);
  background:rgba(12,18,28,.92);box-shadow:0 0 0 1px rgba(59,130,246,.06),0 24px 64px rgba(0,0,0,.55);
  backdrop-filter:blur(20px);overflow:hidden;color:#E8EDF5;
}
.spm-header{padding:1.15rem 1.25rem;border-bottom:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);text-align:center}
.spm-title{font-family:'Syne',sans-serif;font-size:1.2rem;font-weight:700;color:#fff}
.spm-sub{margin-top:.45rem;font-size:.8rem;color:#93a4bf}
.spm-body{padding:1.1rem 1.25rem 1.25rem}
.spm-error{border-radius:10px;padding:10px 12px;background:rgba(239,68,68,.14);border:1px solid rgba(239,68,68,.35);color:#fca5a5;font-size:12px;margin-bottom:12px}
.spm-field{margin-bottom:.75rem}
.spm-input{width:100%;padding:11px 12px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:#E8EDF5;outline:none}
.spm-input:focus{border-color:rgba(96,165,250,.45);background:rgba(59,130,246,.06)}
.spm-submit{width:100%;padding:12px;background:#3B82F6;border:0;border-radius:10px;color:#fff;font-family:'Syne',sans-serif;font-weight:600;cursor:pointer}
.spm-success{border-radius:10px;padding:10px 12px;background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.35);color:#bbf7d0;font-size:12px;margin-bottom:12px}

/* Post-login success modal (layout pattern like app modals: centered card, header band, body, primary CTA — landing palette) */
.post-login-modal-root{position:fixed;inset:0;z-index:240;display:flex;align-items:center;justify-content:center;padding:1rem}
.post-login-modal-root .plm-backdrop{position:absolute;inset:0;background:rgba(2,6,12,.55);backdrop-filter:blur(4px);pointer-events:none}
.post-login-modal-card{
  position:relative;width:100%;max-width:min(30rem,calc(100vw - 2rem));border-radius:1rem;border:1px solid rgba(59,130,246,.2);
  background:rgba(12,18,28,.92);box-shadow:0 0 0 1px rgba(59,130,246,.06),0 24px 64px rgba(0,0,0,.55);
  backdrop-filter:blur(20px);overflow:hidden;color:#E8EDF5;
}
.plm-header{
  display:flex;flex-direction:column;align-items:center;justify-content:center;gap:0;
  border-bottom:1px solid rgba(255,255,255,.08);padding:1.25rem 1.35rem 1.15rem;
  background:rgba(255,255,255,.03);text-align:center;
}
.plm-header-inner{width:100%}
.plm-title{font-family:'Syne',sans-serif;font-size:1.2rem;font-weight:700;color:#fff;margin:0;line-height:1.25}
.plm-sub{margin-top:.5rem;font-size:.8rem;color:#6B7A94;line-height:1.5}
.plm-body{padding:1.25rem 1.35rem 1.4rem;text-align:center}
.plm-body p{margin:0;font-size:.95rem;color:#9d9db0;line-height:1.55}
.plm-body p.plm-lead{margin:0;color:#E8EDF5;font-weight:600;font-size:1rem}
.plm-body p + p{margin-top:.5rem}
.plm-countdown{margin-top:.85rem;font-size:.78rem;color:#6B7A94;font-variant-numeric:tabular-nums}
.plm-countdown .plm-countdown-num{color:#93c5fd;font-weight:600}
.plm-actions{margin-top:1.1rem;display:flex;flex-wrap:wrap;justify-content:center;gap:.5rem}
/* Matches users/index "Add user" button: #24b4fb pill, white label + icon */
.plm-primary{
  display:inline-flex;align-items:center;justify-content:center;cursor:pointer;
  padding:.75rem 1rem;border-radius:.9em;border:2px solid #24b4fb;background:#24b4fb;
  font-family:'Syne',sans-serif;font-size:1rem;font-weight:600;color:#fff;text-decoration:none;
  transition:background-color .2s ease,border-color .2s ease,opacity .15s ease;
  box-shadow:none;
}
.plm-primary:hover{background:#0071e2;border-color:#0071e2}
.plm-primary:focus-visible{outline:none;box-shadow:0 0 0 2px rgba(36,180,251,.5)}
.plm-primary:active{opacity:.95}
.plm-primary-inner{display:flex;align-items:center;justify-content:center;gap:.5rem}
.plm-primary-icon{width:1.5rem;height:1.5rem;flex-shrink:0}

/* NAV */
nav{position:fixed;top:0;left:0;right:0;z-index:100;padding:0 clamp(1.5rem,4vw,4rem);height:80px;
  display:flex;align-items:center;justify-content:space-between;
  background:rgba(6,6,9,.88);backdrop-filter:blur(24px) saturate(1.5);border-bottom:1px solid var(--border)}
.brand{display:flex;align-items:center;gap:.6rem}
/* Logo chip — dark mark reads on near-black nav/footer (same idea as app sidebar) */
.landing-brand-logo-wrap{
  display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;
  padding:4px;border-radius:.625rem;
  background:#cbd5e1;
  box-shadow:0 0 0 1px rgba(255,255,255,.22),0 2px 10px rgba(0,0,0,.45);
}
.landing-brand-logo-wrap--nav{padding:1px;border-radius:6px}
.landing-brand-logo-wrap--footer{padding:3px;border-radius:5px}
.landing-brand-logo-wrap img{display:block}
.landing-brand-logo-wrap--nav .nav-brand-logo{width:24px;height:24px;border-radius:5px}
.landing-brand-logo-wrap--footer .footer-brand-logo{border-radius:4px}
.nav-brand-logo{width:24px;height:24px;object-fit:contain;flex-shrink:0;display:block}
.footer-brand-logo{width:22px;height:22px;object-fit:contain;flex-shrink:0;display:block}
.brand-name{font-weight:800;font-size:1.05rem;color:var(--text-primary);letter-spacing:.04em}
.brand-name span{color:var(--emerald)}
.nav-tag{font-family:'JetBrains Mono';font-size:.55rem;padding:.2rem .5rem;background:var(--emerald-soft);
  border:1px solid var(--emerald-med);border-radius:4px;color:var(--emerald);letter-spacing:.06em;margin-left:.4rem}
.nav-center{
  display:flex;gap:.22rem;padding:4px;background:rgba(12,12,17,.92);border:1px solid rgba(99,102,241,.22);
  border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.35),inset 0 0 0 1px rgba(255,255,255,.03)
}
.nav-center a{
  text-decoration:none;color:var(--text-muted);font-size:.76rem;font-weight:600;padding:.42rem .92rem;border-radius:7px;
  border:1px solid transparent;transition:all .25s;
}
.nav-center a:hover{color:var(--text-primary);background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.08)}
.nav-center a.active{
  color:#fff;background:linear-gradient(180deg,#3b82f6,#1d4ed8);
  border-color:rgba(59,130,246,.95);box-shadow:0 0 0 1px rgba(37,99,235,.55),0 10px 22px rgba(37,99,235,.45)
}
.nav-right{display:flex;align-items:center;gap:.5rem}
.btn-sm{padding:.38rem 1rem;border-radius:7px;font-size:.76rem;font-weight:600;cursor:pointer;font-family:inherit;transition:all .25s;border:none}
.nav-right .btn-sm{white-space:nowrap}
.btn-ghost{background:transparent;border:1px solid var(--border)!important;color:var(--text-secondary)}
.btn-ghost:hover{border-color:var(--border-hover)!important;color:var(--text-primary)}
.btn-brand{background:var(--emerald);color:#fff}
.btn-brand:hover{background:#1d5bb8;box-shadow:0 0 20px rgba(45,107,207,.3)}

/* HERO */
.hero{position:relative;z-index:1;padding:8.5rem clamp(1.5rem,4vw,4rem) 4rem;max-width:1280px;margin:0 auto;
  display:grid;grid-template-columns:1fr 1.15fr;gap:3rem;align-items:center}
.hero-content{max-width:500px}
.hero-pill{display:inline-flex;align-items:center;gap:.45rem;padding:.28rem .75rem .28rem .4rem;
  background:var(--surface);border:1px solid var(--border);border-radius:100px;
  font-size:.68rem;font-weight:500;color:var(--text-muted);margin-bottom:1.4rem}
.hero-pill .hp-dot{width:7px;height:7px;border-radius:50%;background:var(--emerald);position:relative}
.hero-pill .hp-dot::after{content:'';position:absolute;inset:-3px;border-radius:50%;border:1.5px solid var(--emerald);animation:pingRing 2s cubic-bezier(0,0,.2,1) infinite}
@keyframes pingRing{0%{transform:scale(1);opacity:.6}100%{transform:scale(2.2);opacity:0}}
.hero-pill span{color:var(--emerald)}
.hero h1{font-size:clamp(2.1rem,4.5vw,3.2rem);font-weight:900;color:var(--text-primary);line-height:1.08;letter-spacing:-.03em;margin-bottom:1.1rem}
.hero h1 em{font-style:normal;background:linear-gradient(135deg,var(--emerald),var(--teal));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero-desc{font-size:.95rem;color:var(--text-secondary);line-height:1.7;margin-bottom:1.8rem;max-width:420px}
.hero-actions{display:flex;gap:.6rem;align-items:center;margin-bottom:2rem}
.btn-lg{padding:.7rem 1.8rem;border:none;border-radius:10px;font-size:.85rem;font-weight:700;cursor:pointer;font-family:inherit;transition:all .3s}
.btn-primary{background:var(--emerald);color:#fff}
.btn-primary:hover{background:#1d5bb8;box-shadow:0 4px 24px rgba(45,107,207,.3);transform:translateY(-1px)}
.btn-secondary{background:transparent;border:1px solid var(--border);color:var(--text-secondary)}
.btn-secondary:hover{border-color:var(--border-hover);color:var(--text-primary)}
.hero-stats{display:flex;gap:2rem}
.hs-item .hs-val{font-size:1.3rem;font-weight:800;color:var(--text-primary)}
.hs-item .hs-label{font-size:.68rem;color:var(--text-muted);letter-spacing:.06em}

/* HERO TICKET PREVIEW */
.hero-visual{position:relative}
.ticket-preview{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:1.4rem;position:relative;overflow:hidden}
.ticket-preview::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,var(--emerald-med),transparent)}
.tp-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.2rem}
.tp-title{font-weight:700;font-size:.82rem;color:var(--text-primary)}
.tp-live{display:flex;align-items:center;gap:.3rem;font-family:'JetBrains Mono';font-size:.6rem;color:var(--emerald)}
.tp-live .ld{width:5px;height:5px;border-radius:50%;background:var(--emerald);animation:livePulse 1.5s ease infinite}
@keyframes livePulse{0%,100%{opacity:1}50%{opacity:.3}}

/* Ticket items */
.ticket-list{display:flex;flex-direction:column;gap:.5rem;margin-bottom:1.2rem}
.ticket-item{display:flex;align-items:center;gap:.65rem;padding:.7rem .85rem;background:var(--surface);
  border:1px solid var(--border);border-radius:10px;transition:all .3s;cursor:default}
.ticket-item:hover{border-color:var(--border-hover);background:var(--elevated)}
.ti-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.ti-body{flex:1;min-width:0}
.ti-title{font-size:.75rem;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ti-meta{font-size:.6rem;color:var(--text-muted);display:flex;align-items:center;gap:.5rem}
.ti-meta .dot{width:3px;height:3px;border-radius:50%;background:var(--text-muted)}
.ti-priority{font-family:'JetBrains Mono';font-size:.55rem;padding:.15rem .4rem;border-radius:4px;font-weight:600;letter-spacing:.04em}
.pri-critical{background:var(--rose-soft);color:var(--rose)}
.pri-high{background:var(--amber-soft);color:var(--amber)}
.pri-medium{background:var(--blue-soft);color:var(--blue)}
.pri-low{background:var(--emerald-soft);color:var(--emerald)}
.ti-status{width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.ti-time{font-family:'JetBrains Mono';font-size:.58rem;color:var(--text-muted);flex-shrink:0;width:42px;text-align:right}

/* Live chart in ticket preview */
.tp-chart{background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:.75rem}
.tp-chart-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem}
.tp-chart-head span{font-size:.6rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em}
.tp-chart-head .tc-val{font-size:.78rem;font-weight:700;color:var(--text-primary);font-family:'JetBrains Mono'}
#ticketChart{width:100%;height:60px;display:block}

/* Floating elements */
.float-el{position:absolute;padding:.4rem .7rem;background:var(--card);border:1px solid var(--border);
  border-radius:9px;font-size:.64rem;font-weight:500;color:var(--text-secondary);
  display:flex;align-items:center;gap:.35rem;box-shadow:0 8px 28px rgba(0,0,0,.5);z-index:2;white-space:nowrap}
.fe-1{top:-.8rem;right:-.5rem;animation:floatA 5s ease-in-out infinite}
.fe-2{bottom:4rem;left:-1.8rem;animation:floatB 6s ease-in-out infinite}
.fe-3{bottom:-.5rem;right:1.5rem;animation:floatA 5.5s .8s ease-in-out infinite}
.fe-4{top:4.5rem;left:-1.5rem;animation:floatB 4.5s .4s ease-in-out infinite}
.fe-5{top:2rem;right:-2rem;animation:floatA 6.5s .3s ease-in-out infinite}
@keyframes floatA{0%,100%{transform:translateY(0)}50%{transform:translateY(-7px)}}
@keyframes floatB{0%,100%{transform:translateY(0)}50%{transform:translateY(6px)}}

/* LIVE ICON MARQUEE */
.marquee-section{position:relative;z-index:1;padding:2.5rem 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border);overflow:hidden}
.marquee-label{text-align:center;font-size:.62rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.18em;margin-bottom:1.25rem}
.marquee-track{display:flex;width:max-content;gap:1rem}
.marquee-track.slide-left{animation:slideL 45s linear infinite}
.marquee-track.slide-right{animation:slideR 50s linear infinite}
.marquee-track:hover{animation-play-state:paused}
@keyframes slideL{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
@keyframes slideR{0%{transform:translateX(-50%)}100%{transform:translateX(0%)}}
.mq-card{display:flex;align-items:center;gap:.65rem;padding:.65rem 1.1rem;background:var(--card);
  border:1px solid var(--border);border-radius:10px;white-space:nowrap;transition:all .35s;flex-shrink:0}
.mq-card:hover{border-color:var(--emerald-med);background:var(--elevated);transform:translateY(-2px)}
.mq-icon{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.mq-card .mq-title{font-size:.76rem;font-weight:600;color:var(--text-primary)}
.mq-card .mq-sub{font-size:.6rem;color:var(--text-muted)}

/* FEATURES */
.features{position:relative;z-index:1;padding:6rem clamp(1.5rem,4vw,4rem);max-width:1280px;margin:0 auto;scroll-margin-top:6.2rem}
.section-header{text-align:center;margin-bottom:3.5rem}
.overline{font-size:.66rem;color:var(--emerald);text-transform:uppercase;letter-spacing:.18em;font-weight:600;margin-bottom:.55rem}
.section-header h2{font-size:clamp(1.6rem,3.2vw,2.3rem);font-weight:900;color:var(--text-primary);letter-spacing:-.02em;margin-bottom:.6rem}
.section-header p{color:var(--text-muted);max-width:440px;margin:0 auto;font-size:.88rem}
.f-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem}
.f-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:1.6rem;
  transition:all .4s cubic-bezier(.19,1,.22,1);position:relative;overflow:hidden}
.f-card::after{content:'';position:absolute;top:0;left:0;right:0;height:1px;
  background:linear-gradient(90deg,transparent,var(--emerald-med),transparent);opacity:0;transition:opacity .4s}
.f-card:hover{border-color:var(--border-hover);transform:translateY(-3px);box-shadow:0 14px 40px rgba(0,0,0,.4)}
.f-card:hover::after{opacity:1}
.f-head{display:flex;align-items:center;gap:.7rem;margin-bottom:.85rem}
.f-icon{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.f-card h3{font-size:.88rem;font-weight:700;color:var(--text-primary)}
.f-card p{font-size:.8rem;color:var(--text-muted);line-height:1.6}
.f-live{margin-top:1rem;height:44px;background:var(--surface);border:1px solid var(--border);border-radius:8px;overflow:hidden;position:relative}

/* ABOUT US */
.about-section{position:relative;z-index:1;padding:5rem clamp(1.5rem,4vw,4rem);border-top:1px solid var(--border);scroll-margin-top:5.5rem}
.about-inner{max-width:1280px;margin:0 auto;display:grid;grid-template-columns:1fr 1.15fr;gap:3rem;align-items:center}
.about-text .overline{color:var(--emerald)}
.about-text h2{font-size:clamp(1.5rem,3vw,2.1rem);font-weight:900;color:var(--text-primary);letter-spacing:-.02em;margin-bottom:.7rem;line-height:1.15}
.about-text p{color:var(--text-muted);font-size:.88rem;line-height:1.7;margin-bottom:1rem;max-width:440px}
.about-text p strong{color:var(--text-secondary);font-weight:600}
.about-text p:last-of-type{margin-bottom:0}
.about-highlights{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:1.35rem}
.about-tag{font-size:.65rem;padding:.38rem .75rem;border-radius:100px;border:1px solid var(--border);color:var(--text-secondary);background:var(--surface)}
.about-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:1.5rem 1.35rem;position:relative;overflow:hidden}
.about-card::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,var(--emerald-med),transparent)}
.about-card-title{font-weight:700;font-size:.85rem;color:var(--text-primary);margin-bottom:1rem}
.about-list{margin:0;padding:0;list-style:none}
.about-list li{display:flex;gap:.65rem;font-size:.8rem;color:var(--text-muted);line-height:1.55;margin-bottom:.8rem}
.about-list li:last-child{margin-bottom:0}
.about-list li::before{content:'';width:6px;height:6px;border-radius:50%;background:var(--emerald);margin-top:.42rem;flex-shrink:0;box-shadow:0 0 0 3px var(--emerald-soft)}

/* METRICS */
.metrics-section{position:relative;z-index:1;padding:4rem clamp(1.5rem,4vw,4rem);border-top:1px solid var(--border);scroll-margin-top:6.2rem}
.metrics-grid{max-width:1280px;margin:0 auto;display:grid;grid-template-columns:repeat(5,1fr);gap:1rem}
.m-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:1.3rem;text-align:center;transition:all .3s}
.m-card:hover{border-color:var(--border-hover)}
.m-icon{margin:0 auto .5rem;width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center}
.m-num{font-size:1.4rem;font-weight:800;color:var(--text-primary);margin-bottom:.15rem}
.m-label{font-size:.62rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.5rem}
.m-spark{height:20px;display:flex;align-items:flex-end;gap:1.5px;justify-content:center}
.m-spark .sp{width:3px;border-radius:1px 1px 0 0;transition:height .4s ease}

/* NETWORK CANVAS */
.network{position:relative;z-index:1;padding:5rem clamp(1.5rem,4vw,4rem);border-top:1px solid var(--border)}
.network-inner{max-width:1280px;margin:0 auto}
#networkCanvas{width:100%;height:200px;display:block;border-radius:14px;background:var(--card);border:1px solid var(--border)}

/* CTA */
.cta{position:relative;z-index:1;padding:5rem clamp(1.5rem,4vw,4rem)}
.cta-box{max-width:720px;margin:0 auto;text-align:center;padding:3rem 2.5rem;background:var(--card);
  border:1px solid var(--border);border-radius:20px;position:relative;overflow:hidden}
.cta-box::before{content:'';position:absolute;inset:-1px;border-radius:20px;padding:1px;
  background:linear-gradient(135deg,var(--emerald-med),transparent 40%,transparent 60%,var(--blue-med));
  -webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);
  -webkit-mask-composite:xor;mask-composite:exclude;pointer-events:none}
.cta-box h2{font-size:clamp(1.4rem,2.8vw,1.9rem);font-weight:900;color:var(--text-primary);margin-bottom:.5rem}
.cta-box p{color:var(--text-muted);margin-bottom:1.5rem;font-size:.88rem}
.cta-row{display:flex;gap:.4rem;max-width:360px;margin:0 auto}
.cta-input{flex:1;padding:.6rem .9rem;background:var(--surface);border:1px solid var(--border);border-radius:9px;
  color:var(--text-primary);font-size:.8rem;font-family:inherit;outline:none;transition:border-color .25s}
.cta-input::placeholder{color:var(--text-muted)}
.cta-input:focus{border-color:var(--emerald)}
.cta-feedback{margin-top:.6rem;font-size:.74rem;color:var(--text-muted);min-height:1.1rem}
.cta-feedback.ok{color:#86efac}
.cta-feedback.err{color:#fda4af}
#ctaParticles{position:absolute;inset:0;pointer-events:none;opacity:.25}

/* FOOTER */
footer{position:relative;z-index:1;padding:2rem clamp(1.5rem,4vw,4rem);border-top:1px solid var(--border);
  display:flex;justify-content:space-between;align-items:center;max-width:1280px;margin:0 auto}
footer .fl{font-size:.72rem;color:var(--text-muted);display:flex;align-items:center;gap:.5rem}
footer .fr{display:flex;gap:1.2rem}
footer .fr a{font-size:.72rem;color:var(--text-muted);text-decoration:none;transition:color .25s}
footer .fr a:hover{color:var(--text-secondary)}

/* ANIMATIONS */
.reveal{opacity:0;transform:translateY(20px);transition:all .65s cubic-bezier(.19,1,.22,1)}
.reveal.vis{opacity:1;transform:translateY(0)}
.rd1{transition-delay:.06s}.rd2{transition-delay:.12s}.rd3{transition-delay:.18s}
.rd4{transition-delay:.24s}.rd5{transition-delay:.3s}.rd6{transition-delay:.36s}

/* Live SVG animations */
.a-dash{stroke-dasharray:80;stroke-dashoffset:80;animation:aDash 2s ease forwards}
.a-dash-loop{stroke-dasharray:30;animation:aDashL 2.5s ease infinite}
.a-spin{animation:aSpin 10s linear infinite;transform-origin:center}
.a-spin-f{animation:aSpin 4s linear infinite;transform-origin:center}
.a-breathe{animation:aBreathe 3s ease-in-out infinite}
.a-blink{animation:aBlink 2s ease infinite}
.a-bounce{animation:aBounce 2s ease infinite}
.a-ping{animation:aPing 1.5s cubic-bezier(0,0,.2,1) infinite}
.a-wave{animation:aWave 2.5s ease-in-out infinite}
.a-scan{animation:aScan 3s ease-in-out infinite}
.a-ripple{animation:aRipple 2s ease infinite}
.a-slide{animation:aSlide 2s ease infinite}
.a-grow{animation:aGrow 2.5s ease-in-out infinite}
@keyframes aDash{to{stroke-dashoffset:0}}
@keyframes aDashL{0%{stroke-dashoffset:30}50%{stroke-dashoffset:0}100%{stroke-dashoffset:-30}}
@keyframes aSpin{to{transform:rotate(360deg)}}
@keyframes aBreathe{0%,100%{transform:scale(1);opacity:.6}50%{transform:scale(1.2);opacity:1}}
@keyframes aBlink{0%,100%{opacity:1}50%{opacity:.15}}
@keyframes aBounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-3px)}}
@keyframes aPing{75%,100%{transform:scale(2.5);opacity:0}}
@keyframes aWave{0%,100%{transform:rotate(0)}25%{transform:rotate(10deg)}75%{transform:rotate(-10deg)}}
@keyframes aScan{0%,100%{transform:translateY(-4px);opacity:.4}50%{transform:translateY(4px);opacity:1}}
@keyframes aRipple{0%{r:3;opacity:.8}100%{r:8;opacity:0}}
@keyframes aSlide{0%,100%{transform:translateX(0)}50%{transform:translateX(4px)}}
@keyframes aGrow{0%,100%{stroke-dashoffset:50}50%{stroke-dashoffset:0}}

@media(max-width:1024px){
  .f-grid{grid-template-columns:1fr 1fr}.metrics-grid{grid-template-columns:repeat(3,1fr)}
  .about-inner{grid-template-columns:1fr;gap:2.25rem}
}
@media(max-width:768px){
  .hero{grid-template-columns:1fr;text-align:center;padding-top:7rem}
  .hero-content{max-width:100%}.hero-desc{margin:0 auto 1.8rem}
  .hero-actions,.hero-stats{justify-content:center}
  .nav-center{display:none}.float-el{display:none}
  nav{height:68px;padding:0 .8rem}
  .landing-brand-logo-wrap--nav .nav-brand-logo{width:20px;height:20px}
  .brand-name{font-size:.9rem}
  .nav-tag{font-size:.5rem;padding:.16rem .42rem}
  .nav-right{gap:.35rem}
  .nav-right .btn-sm{padding:.32rem .68rem;font-size:.68rem}
  .hero h1{font-size:clamp(1.6rem,7.2vw,2.05rem)}
  .hero-desc{font-size:.84rem;line-height:1.55}
  .f-grid,.metrics-grid{grid-template-columns:1fr}
  .cta-row{flex-direction:column}
  footer{flex-direction:column;gap:.6rem;text-align:center}
  .about-highlights{justify-content:center}
  .about-text{text-align:center}
  .about-text p{margin-left:auto;margin-right:auto}
}
</style>
</head>
<body>
<canvas id="bg-canvas"></canvas>
<div class="grid-overlay"></div>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>
<div class="blob blob-3"></div>

<!-- NAV -->
<nav>
  <div class="brand">
    <span class="landing-brand-logo-wrap landing-brand-logo-wrap--nav" aria-hidden="true"><img src="{{ asset('image/bstc-logo.png') }}" alt="" class="nav-brand-logo" width="179" height="172" decoding="async" fetchpriority="high"></span>
    <span class="brand-name">BIOMETR<span>IX</span></span>
    <span class="nav-tag">TICKETING</span>
  </div>
  <div class="nav-center">
    <a href="#top" class="active">Dashboard</a><a href="#about">About Us</a><a href="#metrics">Analytics</a>
  </div>
  <div class="nav-right">
    <button class="btn-sm btn-ghost js-open-login" type="button">Sign In</button>
    <button class="btn-sm btn-brand js-open-login" type="button">Get Access</button>
  </div>
</nav>

<div class="login-modal-backdrop" id="login-backdrop"></div>
<aside class="login-drawer" id="login-drawer" aria-hidden="true">
  <button class="btn-sm btn-ghost login-close-btn" id="login-close" type="button">Close</button>
  <div class="login-card">
    <div class="login-brand-row">
      <span class="landing-brand-logo-wrap"><img src="{{ asset('image/bstc-logo.png') }}" alt="Biometrix Systems &amp; Trading Corp." class="login-brand-logo" width="179" height="172" decoding="async"></span>
      <div class="login-brand-text">
        <div class="login-brand-title">Welcome to IT Helpdesk</div>
        <div class="login-brand-sub">Biometrix Ticketing System</div>
      </div>
    </div>
    <div class="login-title">Sign in</div>
    <div class="login-sub">Access your secure workspace</div>
    <div class="login-divider">continue with credentials</div>
    @if ($errors->any())
      <div class="login-error">{{ $errors->first() }}</div>
    @endif
    @if (session('error'))
      <div class="login-error">{{ session('error') }}</div>
    @endif
    @if (request('error'))
      <div class="login-error">{{ request('error') }}</div>
    @endif
    {{-- Relative URL so POST stays on same host as the page (avoids 419 when APP_URL is localhost but you use 127.0.0.1) --}}
    <form action="{{ route('login', [], false) }}" method="post">
      @csrf
      <div class="login-field">
        <div class="login-label">Email address</div>
        <input class="login-input" type="email" name="email" value="{{ old('email') }}" placeholder="you@company.com" required autofocus>
      </div>
      <div class="login-field">
        <div class="login-label">Password</div>
        <input class="login-input" type="password" name="password" placeholder="••••••••" autocomplete="current-password">
      </div>
      <button class="login-submit" type="submit">Sign in to Biometrix</button>
    </form>
    <div id="login-forgot-wrap" class="login-forgot-wrap" @if(!(session('show_forgot_prompt') && session('forgot_email'))) style="display:none" @endif>
      <div class="login-forgot-text">Having trouble signing in? Request a password reset approval from your admin.</div>
      <button id="login-forgot-btn" class="login-forgot-btn" type="button" data-email="{{ session('forgot_email', old('email')) }}">Request password reset</button>
      <div id="login-forgot-msg" class="login-forgot-msg" aria-live="polite"></div>
    </div>
    <p class="login-account-hint">No account? Ask your administrator for access.</p>
  </div>
</aside>

@php
  $setPasswordEmail = request('email') ?: session('set_password_email');
  $showSetPasswordModal = request('set_password') === '1' && filled($setPasswordEmail);
  $resetToken = old('token') ?: session('reset_token');
  $showResetPasswordModal = (request('reset_password') === '1' || session()->has('reset_token') || old('token')) && filled($resetToken);
@endphp
@if($showSetPasswordModal)
<div id="set-password-modal" class="sp-modal-root" aria-modal="true" role="dialog" aria-labelledby="set-password-title">
  <div class="spm-backdrop" aria-hidden="true"></div>
  <div class="sp-modal-card">
    <div class="spm-header">
      <h2 id="set-password-title" class="spm-title">Set your password</h2>
      <p class="spm-sub">Create a password to sign in to your account.</p>
    </div>
    <div class="spm-body">
      @if ($errors->any())
        <div class="spm-error">{{ $errors->first() }}</div>
      @endif
      @if (session('error'))
        <div class="spm-error">{{ session('error') }}</div>
      @endif
      <form action="{{ route('auth.set-password', [], false) }}" method="post">
        @csrf
        <input type="hidden" name="email" value="{{ $setPasswordEmail }}">
        <div class="spm-field">
          <input class="spm-input" type="password" name="password" placeholder="New password" minlength="8" autocomplete="new-password" required autofocus>
        </div>
        <div class="spm-field">
          <input class="spm-input" type="password" name="password_confirmation" placeholder="Confirm password" autocomplete="new-password" required>
        </div>
        <button class="spm-submit" type="submit">Set password &amp; sign in</button>
      </form>
    </div>
  </div>
</div>
@endif

@if($showResetPasswordModal)
<div id="reset-password-modal" class="sp-modal-root" aria-modal="true" role="dialog" aria-labelledby="reset-password-title">
  <div class="spm-backdrop" aria-hidden="true"></div>
  <div class="sp-modal-card">
    <div class="spm-header">
      <h2 id="reset-password-title" class="spm-title">Reset your password</h2>
      <p class="spm-sub">Enter a new password, then sign in with your account.</p>
    </div>
    <div class="spm-body">
      @if (session('success') && old('token'))
        <div class="spm-success">{{ session('success') }}</div>
      @endif
      @if ($errors->has('token') || $errors->has('password') || $errors->has('password_confirmation'))
        <div class="spm-error">{{ $errors->first('token') ?: $errors->first('password') ?: $errors->first('password_confirmation') }}</div>
      @endif
      <form action="{{ route('password.reset.submit') }}" method="post">
        @csrf
        <input type="hidden" name="token" value="{{ $resetToken }}">
        <div class="spm-field">
          <input class="spm-input" type="password" name="password" placeholder="New password" minlength="8" autocomplete="new-password" required autofocus>
        </div>
        <div class="spm-field">
          <input class="spm-input" type="password" name="password_confirmation" placeholder="Confirm new password" autocomplete="new-password" required>
        </div>
        <button class="spm-submit" type="submit">Change password</button>
      </form>
    </div>
  </div>
</div>
@endif

@if(!empty($openPostLoginModal) && auth()->check() && session()->has('post_login_target'))
<div id="post-login-success-modal" class="post-login-modal-root" aria-modal="true" role="dialog" aria-labelledby="post-login-modal-title" data-redirect-url="{{ session('post_login_target') }}">
  <div class="plm-backdrop" aria-hidden="true"></div>
  <div class="post-login-modal-card">
    <div class="plm-header">
      <div class="plm-header-inner">
        <h2 id="post-login-modal-title" class="plm-title">Signed in successfully</h2>
        <p class="plm-sub">You can continue to your workspace when you are ready.</p>
      </div>
    </div>
    <div class="plm-body">
      <p class="plm-lead">Welcome back, {{ auth()->user()->name }}.</p>
      <p>{{ session('post_login_swal_text', "You're signed in.") }}</p>
      <p class="plm-countdown" id="plm-countdown" aria-live="polite">Redirecting in <span class="plm-countdown-num" id="plm-countdown-num">5</span>s…</p>
      <div class="plm-actions">
        <a class="plm-primary" id="plm-dashboard-link" href="{{ session('post_login_target') }}">
          <span class="plm-primary-inner">
            <svg class="plm-primary-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" fill="currentColor"/></svg>
            Go to dashboard
          </span>
        </a>
      </div>
    </div>
  </div>
</div>
@endif

<!-- HERO -->
<section class="hero" id="top">
  <div class="hero-content">
    <div class="hero-pill">
      <span class="hp-dot"></span>
      <span>System Active</span> · v3.8.1 Stable
    </div>
    <h1>Smart ticketing <em>by Biometrix Systems &amp; Trading Corp.</em></h1>
    <p class="hero-desc">Biometrix delivers AI-driven ticket management with biometric authentication, real-time triage, and predictive resolution — purpose-built for modern support teams.</p>
    <div class="hero-actions">
      <button class="btn-lg btn-primary js-scroll-bottom" type="button">Request Access</button>
      <button class="btn-lg btn-secondary">View Documentation</button>
    </div>
    <div class="hero-stats">
      <div class="hs-item"><div class="hs-val" id="hsTickets">{{ number_format($resolvedTicketsCount) }}</div><div class="hs-label">Tickets Resolved</div></div>
      <div class="hs-item"><div class="hs-val">99.9%</div><div class="hs-label">Auth Accuracy</div></div>
      <div class="hs-item"><div class="hs-val" id="hsTime">{{ number_format($avgResolutionMinutes, 1) }}m</div><div class="hs-label">Avg Resolution</div></div>
    </div>
  </div>

  <div class="hero-visual">
    <div class="ticket-preview">
      <div class="tp-top">
        <span class="tp-title">Active Tickets</span>
        <span class="tp-live"><span class="ld"></span> LIVE</span>
      </div>
      <div class="ticket-list" id="ticketList"></div>
      <div class="tp-chart">
        <div class="tp-chart-head">
          <span>Ticket Volume (24h)</span>
          <span class="tc-val" id="tcVal">{{ number_format($ticketVolume24h) }}</span>
        </div>
        <canvas id="ticketChart"></canvas>
      </div>
    </div>

    <!-- Floating live elements -->
    <div class="float-el fe-1">
      <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="var(--emerald)" stroke-width="1.3">
        <circle cx="8" cy="6" r="3"/><path d="M3 14c0-3 2.5-5 5-5s5 2 5 5" stroke-linecap="round" class="a-dash"/>
        <circle cx="8" cy="6" r="5" stroke-dasharray="2 2" class="a-spin"/>
      </svg>
      <span style="color:var(--emerald)">Bio-verified</span>
    </div>
    <div class="float-el fe-2">
      <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="var(--sky)" stroke-width="1.3">
        <rect x="2" y="2" width="12" height="12" rx="3"/><polyline points="5,9 7,11 11,6" class="a-dash"/>
      </svg>
      <span style="color:var(--sky)">SLA on track</span>
    </div>
    <div class="float-el fe-3">
      <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="var(--amber)" stroke-width="1.3">
        <circle cx="8" cy="8" r="5"/><path d="M8 5v3l2 1.5" class="a-dash"/>
        <circle cx="8" cy="8" r="1" fill="var(--amber)" class="a-blink"/>
      </svg>
      <span style="color:var(--amber)">Avg <span id="feAvg">{{ number_format($avgResolutionMinutes, 1) }}</span>m resolve</span>
    </div>
    <div class="float-el fe-4">
      <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="var(--violet)" stroke-width="1.3">
        <path d="M8 1L2 4v4c0 4 2.5 7.5 6 9 3.5-1.5 6-5 6-9V4L8 1z"/>
        <polyline points="6,8 7.5,9.5 10,7" class="a-dash"/>
      </svg>
      <span style="color:var(--violet)">Encrypted</span>
    </div>
    <div class="float-el fe-5">
      <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
        <circle cx="8" cy="8" r="5" stroke="var(--rose)" stroke-width="1.3"/>
        <circle cx="8" cy="8" r="2" fill="var(--rose)" class="a-breathe"/>
        <circle cx="8" cy="8" r="6.5" stroke="var(--rose)" stroke-width=".5" stroke-dasharray="2 3" class="a-spin"/>
      </svg>
      <span style="color:var(--rose)">{{ number_format($criticalActiveCount) }} critical</span>
    </div>
  </div>
</section>

<!-- MARQUEE ROW 1 -->
<section class="marquee-section">
  <div class="marquee-label">Powered by live biometric modules</div>
  <div class="marquee-track slide-left" id="mq1"></div>
  <div style="height:.75rem"></div>
  <div class="marquee-track slide-right" id="mq2"></div>
</section>

<!-- FEATURES -->
<section class="features" id="features">
  <div class="section-header reveal">
    <div class="overline">Core Capabilities</div>
    <h2>Ticketing, reimagined.</h2>
    <p>Six intelligent modules that work together to resolve tickets faster, smarter, and more securely.</p>
  </div>
  <div class="f-grid" id="fGrid"></div>
</section>

<!-- ABOUT US -->
<section class="about-section" id="about">
  <div class="about-inner">
    <div class="about-text reveal">
      <div class="overline">About Us</div>
      <h2>Built for teams who take<br>support seriously.</h2>
      <p><strong>Biometrix Systems &amp; Trading Corp.</strong> delivers intelligent ticketing and IT helpdesk tools that blend reliable operations with modern security—so your organization resolves issues faster and with full traceability.</p>
      <p>We focus on practical automation, clear workflows, and a platform your front desk and IT staff can trust every day.</p>
      <div class="about-highlights">
        <span class="about-tag">Consolacion, Cebu</span>
        <span class="about-tag">Enterprise-ready</span>
        <span class="about-tag">Secure by design</span>
      </div>
    </div>
    <div class="reveal">
      <div class="about-card">
        <div class="about-card-title">What we stand for</div>
        <ul class="about-list">
          <li>Customer-first support experiences, backed by structured ticketing.</li>
          <li>Transparency and audit-friendly activity across the workspace.</li>
          <li>Continuous improvement—shipping features that match real desk workflows.</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- METRICS -->
<section class="metrics-section" id="metrics">
  <div class="metrics-grid" id="metricsGrid"></div>
</section>

<!-- NETWORK -->
<section class="network reveal">
  <div class="network-inner">
    <canvas id="networkCanvas"></canvas>
  </div>
</section>

<!-- CTA -->
<section class="cta reveal" id="cta">
  <div class="cta-box">
    <canvas id="ctaParticles"></canvas>
    <div style="position:relative;z-index:1">
      <h2>Ready to upgrade your support?</h2>
      <p>Biometrix is available for enterprise teams. Request early access today.</p>
      <form class="cta-row" id="access-request-form">
        <input class="cta-input" id="access-request-email" type="email" placeholder="you@company.com" required>
        <button class="btn-sm btn-brand" id="access-request-submit" type="submit" style="padding:.6rem 1.4rem;font-size:.8rem">Request Access</button>
      </form>
      <div id="access-request-feedback" class="cta-feedback" aria-live="polite"></div>
    </div>
  </div>
</section>

<footer id="landing-bottom">
  <div class="fl">
    <span class="landing-brand-logo-wrap landing-brand-logo-wrap--footer" aria-hidden="true"><img src="{{ asset('image/bstc-logo.png') }}" alt="" class="footer-brand-logo" width="179" height="172" decoding="async"></span>
    © 2026 Biometrix Systems &amp; Trading Corp. All systems operational.
  </div>
  <div class="fr"><a href="#">Privacy</a><a href="#">Terms</a><a href="#">Status</a><a href="#">API Docs</a></div>
</footer>

<script>
/* Background: Particle network */
const bgCanvas=document.getElementById('bg-canvas');
const bgCtx=bgCanvas.getContext('2d');
let bgW,bgH,bgParticles=[];
function bgResize(){bgW=bgCanvas.width=window.innerWidth;bgH=bgCanvas.height=window.innerHeight;}
bgResize();
window.addEventListener('resize', bgResize);
function bgMkP(){return{x:Math.random()*bgW,y:Math.random()*bgH,vx:(Math.random()-0.5)*0.35,vy:(Math.random()-0.5)*0.35,r:Math.random()*1.8+0.4,a:Math.random()*0.55+0.1,hue:Math.random()>0.65?160:217};}
for(let i=0;i<120;i++)bgParticles.push(bgMkP());
function bgDraw(){
  bgCtx.clearRect(0,0,bgW,bgH);
  for(let i=0;i<bgParticles.length;i++){
    const p=bgParticles[i];
    p.x+=p.vx; p.y+=p.vy;
    if(p.x<0)p.x=bgW; if(p.x>bgW)p.x=0;
    if(p.y<0)p.y=bgH; if(p.y>bgH)p.y=0;
    bgCtx.beginPath();
    bgCtx.arc(p.x,p.y,p.r,0,Math.PI*2);
    bgCtx.fillStyle=`hsla(${p.hue},80%,65%,${p.a})`;
    bgCtx.fill();
    for(let j=i+1;j<bgParticles.length;j++){
      const q=bgParticles[j];
      const dx=p.x-q.x, dy=p.y-q.y;
      const d=Math.sqrt(dx*dx+dy*dy);
      if(d<100){
        bgCtx.beginPath();
        bgCtx.moveTo(p.x,p.y);
        bgCtx.lineTo(q.x,q.y);
        bgCtx.strokeStyle=`rgba(96,165,250,${0.07*(1-d/100)})`;
        bgCtx.lineWidth=0.5;
        bgCtx.stroke();
      }
    }
  }
  requestAnimationFrame(bgDraw);
}
bgDraw();

/* Login drawer controls */
(function(){
  const root=document.documentElement;
  const openBtns=document.querySelectorAll('.js-open-login');
  const closeBtn=document.getElementById('login-close');
  const backdrop=document.getElementById('login-backdrop');
  const open=()=>root.classList.add('login-open');
  const close=()=>root.classList.remove('login-open');
  openBtns.forEach(b=>b.addEventListener('click',open));
  if(closeBtn) closeBtn.addEventListener('click',close);
  if(backdrop) backdrop.addEventListener('click',close);
  document.addEventListener('keydown',e=>{if(e.key==='Escape') close();});
  const shouldOpen = @json($errors->any() || old('email') || request()->query('login') === '1' || session()->has('error')) && !document.getElementById('post-login-success-modal') && !document.getElementById('set-password-modal') && !document.getElementById('reset-password-modal');
  if(shouldOpen){ open(); }
})();

(function(){
  const forgotWrap=document.getElementById('login-forgot-wrap');
  const forgotBtn=document.getElementById('login-forgot-btn');
  const forgotMsg=document.getElementById('login-forgot-msg');
  if(!forgotWrap || !forgotBtn || !forgotMsg) return;

  forgotBtn.addEventListener('click', async function(){
    const email=(forgotBtn.getAttribute('data-email') || '').trim();
    if(!email){
      forgotMsg.textContent='Enter your email first, then try again.';
      return;
    }

    forgotBtn.disabled=true;
    forgotBtn.textContent='Sending request...';
    forgotMsg.textContent='';
    try{
      const res=await fetch(@json(route('password.reset.request')),{
        method:'POST',
        credentials:'same-origin',
        headers:{
          'Content-Type':'application/json',
          'Accept':'application/json',
          'X-Requested-With':'XMLHttpRequest',
          'X-CSRF-TOKEN':@json(csrf_token()),
        },
        body:JSON.stringify({email}),
      });
      const data=await res.json().catch(()=>({}));
      if(!res.ok){
        forgotMsg.textContent=data.message || 'Could not send password reset request.';
        return;
      }
      forgotMsg.textContent=data.message || 'Request sent. Wait for admin approval.';
    }catch(e){
      forgotMsg.textContent='Could not send password reset request.';
    }finally{
      forgotBtn.disabled=false;
      forgotBtn.textContent='Request password reset';
    }
  });
})();

@if(!empty($openPostLoginModal))
(function(){
  document.body.style.overflow='hidden';
  var root=document.getElementById('post-login-success-modal');
  var target=root&&root.getAttribute('data-redirect-url');
  var numEl=document.getElementById('plm-countdown-num');
  var link=document.getElementById('plm-dashboard-link');
  if(!target||!numEl) return;
  var secondsLeft=5;
  var tid=null;
  function go(){ if(tid!==null){ clearInterval(tid); tid=null; } window.location.href=target; }
  function tick(){
    secondsLeft--;
    if(secondsLeft<=0){ go(); return; }
    numEl.textContent=String(secondsLeft);
  }
  tid=setInterval(tick,1000);
  if(link) link.addEventListener('click',function(){ if(tid!==null){ clearInterval(tid); tid=null; } });
})();
@endif

// ===== LIVE SVG ICON LIBRARY =====
const icons = {
  fingerprint: (c) => `<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="${c}" stroke-width="1.3">
    <path d="M10 2C6.7 2 4 4.7 4 8v3" class="a-dash"/><path d="M16 8v2c0 1.5-.5 3-1.5 4" class="a-dash"/>
    <path d="M7 8c0-1.7 1.3-3 3-3s3 1.3 3 3v3" class="a-dash-loop"/>
    <circle cx="10" cy="14" r="1" fill="${c}" class="a-blink"/></svg>`,
  faceId: (c) => `<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="${c}" stroke-width="1.3">
    <path d="M3 7V4a1 1 0 011-1h3M13 3h3a1 1 0 011 1v3M17 13v3a1 1 0 01-1 1h-3M7 17H4a1 1 0 01-1-1v-3"/>
    <circle cx="8" cy="8.5" r="1" fill="${c}" class="a-blink"/><circle cx="12" cy="8.5" r="1" fill="${c}" class="a-blink"/>
    <path d="M8 12.5c0 0 1 1.5 2 1.5s2-1.5 2-1.5" stroke-linecap="round" class="a-dash"/></svg>`,
  shield: (c) => `<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="${c}" stroke-width="1.3">
    <path d="M10 1L3 4.5v4.5c0 4.5 3 8.7 7 10 4-1.3 7-5.5 7-10V4.5L10 1z"/>
    <polyline points="7,10 9,12 13,8" class="a-dash"/>
    <circle cx="10" cy="1" r="1" fill="${c}" class="a-blink"/></svg>`,
  ticket: (c) => `<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="${c}" stroke-width="1.3">
    <rect x="2" y="4" width="16" height="12" rx="2"/><path d="M2 9h16" stroke-dasharray="2 2"/>
    <circle cx="7" cy="12" r="1" fill="${c}" class="a-blink"/><line x1="10" y1="12" x2="15" y2="12" class="a-dash"/></svg>`,
  brain: (c) => `<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="${c}" stroke-width="1.3">
    <path d="M10 17V9" class="a-dash"/><path d="M6 9c-2 0-3-1.5-3-3s1.5-3 3-3c0-1.5 1.5-2.5 3-2.5" class="a-dash"/>
    <path d="M14 9c2 0 3-1.5 3-3s-1.5-3-3-3c0-1.5-1.5-2.5-3-2.5" class="a-dash"/>
    <circle cx="10" cy="9" r="2" stroke-dasharray="2 2" class="a-spin"/></svg>`,
  chart: (c) => `<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="${c}" stroke-width="1.3">
    <polyline points="2,15 6,9 10,11 14,5 18,8" class="a-dash"/>
    <circle cx="18" cy="8" r="1.5" fill="${c}" class="a-blink"/>
    <circle cx="14" cy="5" r="1.5" fill="${c}" class="a-breathe"/></svg>`,
  bell: (c) => `<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="${c}" stroke-width="1.3">
    <path d="M15 7A5 5 0 005 7c0 5-2 7-2 7h14s-2-2-2-7"/><circle cx="10" cy="17" r="1.5" fill="${c}" class="a-bounce"/>
    <circle cx="10" cy="3" r="1" fill="${c}"><animate attributeName="opacity" values=".3;1;.3" dur="1.2s" repeatCount="indefinite"/></circle></svg>`,
  lock: (c) => `<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="${c}" stroke-width="1.3">
    <rect x="4" y="9" width="12" height="8" rx="2"/><path d="M7 9V6a3 3 0 016 0v3" class="a-dash"/>
    <circle cx="10" cy="13" r="1.5" fill="${c}" class="a-breathe"/></svg>`,
  eye: (c) => `<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="${c}" stroke-width="1.3">
    <path d="M2 10s3-5 8-5 8 5 8 5-3 5-8 5-8-5-8-5z"/>
    <circle cx="10" cy="10" r="2.5" class="a-breathe"/><circle cx="10" cy="10" r="1" fill="${c}" class="a-blink"/></svg>`,
  zap: (c) => `<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="${c}" stroke-width="1.3">
    <polyline points="11,1 5,10 10,10 9,19 15,10 10,10" class="a-dash"/>
    <circle cx="10" cy="10" r="6" stroke-dasharray="3 4" class="a-spin" opacity=".3"/></svg>`,
  globe: (c) => `<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="${c}" stroke-width="1.3">
    <circle cx="10" cy="10" r="7"/><ellipse cx="10" cy="10" rx="3" ry="7" class="a-dash-loop"/>
    <line x1="3" y1="10" x2="17" y2="10"/><circle cx="15" cy="5" r="1" fill="${c}" class="a-blink"/></svg>`,
  cpu: (c) => `<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="${c}" stroke-width="1.3">
    <rect x="5" y="5" width="10" height="10" rx="2"/>
    <line x1="8" y1="2" x2="8" y2="5"/><line x1="12" y1="2" x2="12" y2="5"/>
    <line x1="8" y1="15" x2="8" y2="18"/><line x1="12" y1="15" x2="12" y2="18"/>
    <line x1="2" y1="8" x2="5" y2="8"/><line x1="2" y1="12" x2="5" y2="12"/>
    <line x1="15" y1="8" x2="18" y2="8"/><line x1="15" y1="12" x2="18" y2="12"/>
    <circle cx="10" cy="10" r="2" stroke-dasharray="2 2" class="a-spin"/></svg>`,
  scan: (c) => `<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="${c}" stroke-width="1.3">
    <path d="M3 7V4a1 1 0 011-1h3M13 3h3a1 1 0 011 1v3M17 13v3a1 1 0 01-1 1h-3M7 17H4a1 1 0 01-1-1v-3"/>
    <line x1="4" y1="10" x2="16" y2="10" class="a-scan" stroke-width="1.5"/>
    <circle cx="10" cy="10" r="3" stroke-dasharray="2 2" class="a-spin"/></svg>`,
  iris: (c) => `<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="${c}" stroke-width="1.3">
    <circle cx="10" cy="10" r="7"/><circle cx="10" cy="10" r="4" class="a-breathe"/>
    <circle cx="10" cy="10" r="1.5" fill="${c}" class="a-blink"/>
    <circle cx="10" cy="10" r="8.5" stroke-dasharray="2 4" class="a-spin" opacity=".4"/></svg>`,
  heartbeat: (c) => `<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="${c}" stroke-width="1.3">
    <polyline points="1,10 5,10 7,4 10,16 13,7 15,10 19,10" class="a-dash-loop"/>
    <circle cx="10" cy="10" r="1" fill="${c}" class="a-blink"/></svg>`,
  rocket: (c) => `<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="${c}" stroke-width="1.3">
    <path d="M10 15V5c0-2 2-4 4-4" class="a-dash"/><path d="M10 5c0-2-2-4-4-4" class="a-dash"/>
    <circle cx="10" cy="9" r="1.5" fill="${c}" class="a-breathe"/>
    <path d="M7 13l-2 4M13 13l2 4" class="a-wave"/></svg>`,
};

// ===== TICKET LIST =====
const ticketData = @json($landingTicketData);
document.getElementById('ticketList').innerHTML = ticketData.length ? ticketData.map(t => {
  const iconMarkup = typeof icons[t.iconKey] === 'function'
    ? icons[t.iconKey](t.statusColor)
    : icons.chart(t.statusColor);
  return (
  `<div class="ticket-item">
    <div class="ti-icon" style="background:${t.iconBg}">${iconMarkup}</div>
    <div class="ti-body"><div class="ti-title">${t.title}</div>
      <div class="ti-meta"><span style="font-family:'JetBrains Mono';font-size:.58rem;color:var(--text-muted)">${t.id}</span><span class="dot"></span><span class="ti-priority pri-${t.pri}">${t.priLabel}</span></div>
    </div>
    <div class="ti-time">${t.isPlaceholder ? '—' : `${t.time} ago`}</div>
    <div class="ti-status" style="background:${t.iconBg}">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><circle cx="6" cy="6" r="3" fill="${t.statusColor}" class="a-breathe"/><circle cx="6" cy="6" r="5" stroke="${t.statusColor}" stroke-width=".8" stroke-dasharray="2 2" class="a-spin"/></svg>
    </div>
  </div>`);
}).join('') : `<div class="ticket-item"><div class="ti-body"><div class="ti-title">No active tickets right now</div><div class="ti-meta"><span style="font-family:'JetBrains Mono';font-size:.58rem;color:var(--text-muted)">SYSTEM</span></div></div><div class="ti-time">now</div></div>`;

// ===== MARQUEE ROWS =====
const mq1Data = [
  {title:'Fingerprint Auth',sub:'Biometric login',icon:icons.fingerprint('var(--emerald)'),bg:'var(--emerald-soft)'},
  {title:'Face ID',sub:'Facial recognition',icon:icons.faceId('var(--sky)'),bg:'var(--sky-soft)'},
  {title:'Iris Scan',sub:'Retinal verify',icon:icons.iris('var(--violet)'),bg:'var(--violet-soft)'},
  {title:'AI Triage',sub:'Smart routing',icon:icons.brain('var(--blue)'),bg:'var(--blue-soft)'},
  {title:'Live Charts',sub:'Real-time data',icon:icons.chart('var(--emerald)'),bg:'var(--emerald-soft)'},
  {title:'Alert Engine',sub:'Instant notify',icon:icons.bell('var(--amber)'),bg:'var(--amber-soft)'},
  {title:'Vault Lock',sub:'End-to-end',icon:icons.lock('var(--rose)'),bg:'var(--rose-soft)'},
  {title:'Eye Track',sub:'Gaze analytics',icon:icons.eye('var(--cyan)'),bg:'var(--cyan-soft)'},
  {title:'Auto Resolve',sub:'ML powered',icon:icons.zap('var(--amber)'),bg:'var(--amber-soft)'},
  {title:'Global CDN',sub:'180+ nodes',icon:icons.globe('var(--teal)'),bg:'var(--teal-soft)'},
];
const mq2Data = [
  {title:'Ticket Engine',sub:'Smart queue',icon:icons.ticket('var(--blue)'),bg:'var(--blue-soft)'},
  {title:'Bio Scanner',sub:'Multi-modal',icon:icons.scan('var(--emerald)'),bg:'var(--emerald-soft)'},
  {title:'Edge Compute',sub:'Local processing',icon:icons.cpu('var(--violet)'),bg:'var(--violet-soft)'},
  {title:'Heartbeat',sub:'Health monitor',icon:icons.heartbeat('var(--rose)'),bg:'var(--rose-soft)'},
  {title:'Shield Guard',sub:'Zero-trust',icon:icons.shield('var(--sky)'),bg:'var(--sky-soft)'},
  {title:'Quick Deploy',sub:'CI/CD pipeline',icon:icons.rocket('var(--amber)'),bg:'var(--amber-soft)'},
  {title:'Fingerprint+',sub:'Enhanced auth',icon:icons.fingerprint('var(--teal)'),bg:'var(--teal-soft)'},
  {title:'Face Match',sub:'Identity verify',icon:icons.faceId('var(--emerald)'),bg:'var(--emerald-soft)'},
  {title:'Iris Deep',sub:'Advanced scan',icon:icons.iris('var(--blue)'),bg:'var(--blue-soft)'},
  {title:'Neural Net',sub:'Deep learning',icon:icons.brain('var(--violet)'),bg:'var(--violet-soft)'},
];
function buildMq(items){return items.map(d=>`<div class="mq-card"><div class="mq-icon" style="background:${d.bg}">${d.icon}</div><div><div class="mq-title">${d.title}</div><div class="mq-sub">${d.sub}</div></div></div>`).join('')}
document.getElementById('mq1').innerHTML = buildMq(mq1Data) + buildMq(mq1Data);
document.getElementById('mq2').innerHTML = buildMq(mq2Data) + buildMq(mq2Data);

// ===== FEATURE CARDS =====
const featureCards = [
  {title:'Biometric Authentication',desc:'Multi-modal identity verification — fingerprint, face, iris — with liveness detection and anti-spoofing.',
    color:'emerald',icon:icons.fingerprint('var(--emerald)'),graphType:'wave'},
  {title:'AI-Powered Triage',desc:'Every ticket is auto-classified, prioritized, and routed to the right team using natural language understanding.',
    color:'blue',icon:icons.brain('var(--blue)'),graphType:'bars'},
  {title:'Predictive Resolution',desc:'ML models analyze historical patterns to suggest solutions before an agent even opens the ticket.',
    color:'violet',icon:icons.zap('var(--violet)'),graphType:'wave'},
  {title:'Real-Time Analytics',desc:'Live dashboards, SLA tracking, team velocity, and resolution heatmaps updated every second.',
    color:'sky',icon:icons.chart('var(--sky)'),graphType:'bars'},
  {title:'Zero-Trust Security',desc:'End-to-end encryption, role-based access, audit trails, and SOC 2 Type II compliance built in.',
    color:'rose',icon:icons.shield('var(--rose)'),graphType:'wave'},
  {title:'Smart Escalation',desc:'Automatic escalation rules with context handoff. No ticket falls through the cracks, ever.',
    color:'amber',icon:icons.bell('var(--amber)'),graphType:'bars'},
];
document.getElementById('fGrid').innerHTML = featureCards.map((f,i) => {
  let graphHTML;
  if(f.graphType==='wave') graphHTML=`<canvas class="f-wave" data-color="${f.color}" style="width:100%;height:100%;display:block"></canvas>`;
  else{let b='';for(let j=0;j<18;j++)b+=`<div style="flex:1;height:${15+Math.random()*78}%;background:var(--${f.color}-soft);border-radius:2px 2px 0 0;transition:height .5s ease" class="fb"></div>`;
    graphHTML=`<div style="display:flex;align-items:flex-end;gap:1.5px;height:100%;width:100%;padding:4px 0">${b}</div>`;}
  return `<div class="f-card reveal rd${i%6+1}">
    <div class="f-head"><div class="f-icon" style="background:var(--${f.color}-soft)">${f.icon}</div><h3>${f.title}</h3></div>
    <p>${f.desc}</p><div class="f-live">${graphHTML}</div></div>`;
}).join('');

// Animate wave canvases
document.querySelectorAll('.f-wave').forEach(c=>{
  const ctx=c.getContext('2d');let off=Math.random()*200;
  const col=c.dataset.color;
  (function draw(){
    const w=c.width=c.offsetWidth*2,h=c.height=c.offsetHeight*2;
    ctx.clearRect(0,0,w,h);
    ctx.beginPath();ctx.moveTo(0,h);
    for(let x=0;x<=w;x+=3){const y=h/2+Math.sin((x+off)*.018)*h*.35+Math.sin((x+off)*.006)*h*.15;ctx.lineTo(x,y);}
    ctx.lineTo(w,h);ctx.closePath();ctx.fillStyle='rgba(255,255,255,.03)';ctx.fill();
    ctx.beginPath();for(let x=0;x<=w;x+=3){const y=h/2+Math.sin((x+off)*.018)*h*.35+Math.sin((x+off)*.006)*h*.15;x===0?ctx.moveTo(x,y):ctx.lineTo(x,y);}
    ctx.strokeStyle='rgba(255,255,255,.1)';ctx.lineWidth=1.5;ctx.stroke();
    off+=.8;requestAnimationFrame(draw);
  })();
});
setInterval(()=>{document.querySelectorAll('.f-live .fb').forEach(b=>{b.style.height=(15+Math.random()*78)+'%'})},2000);

// ===== METRICS =====
const metricsData=[
  {num:'148K',label:'Tickets Resolved',color:'emerald',icon:icons.ticket('var(--emerald)')},
  {num:'4.2m',label:'Avg Resolution',color:'sky',icon:icons.heartbeat('var(--sky)')},
  {num:'99.9%',label:'Auth Accuracy',color:'violet',icon:icons.fingerprint('var(--violet)')},
  {num:'< 12ms',label:'API Latency',color:'blue',icon:icons.zap('var(--blue)')},
  {num:'0',label:'Data Breaches',color:'rose',icon:icons.shield('var(--rose)')},
];
document.getElementById('metricsGrid').innerHTML = metricsData.map((m,i)=>{
  let sp='';for(let j=0;j<22;j++)sp+=`<div class="sp" style="height:${12+Math.random()*82}%;background:var(--${m.color}${j%2?'-soft':''})" data-c="${m.color}"></div>`;
  return `<div class="m-card reveal rd${i+1}"><div class="m-icon" style="background:var(--${m.color}-soft)">${m.icon}</div><div class="m-num">${m.num}</div><div class="m-label">${m.label}</div><div class="m-spark">${sp}</div></div>`;
}).join('');
setInterval(()=>{document.querySelectorAll('.m-spark .sp').forEach(s=>{s.style.height=(12+Math.random()*82)+'%'})},1600);

// ===== LIVE LINE CHART =====
const lc=document.getElementById('ticketChart'),lctx=lc.getContext('2d');
let ld=Array.from({length:50},()=>15+Math.random()*60);
function drawLC(){
  const w=lc.width=lc.offsetWidth*2,h=lc.height=lc.offsetHeight*2;
  lctx.clearRect(0,0,w,h);const step=w/(ld.length-1);
  const g=lctx.createLinearGradient(0,0,0,h);g.addColorStop(0,'rgba(45,107,207,.2)');g.addColorStop(1,'transparent');
  lctx.beginPath();lctx.moveTo(0,h);
  ld.forEach((v,i)=>{const x=i*step,y=h-(v/100)*h;if(i===0)lctx.lineTo(x,y);else{const px=(i-1)*step,py=h-(ld[i-1]/100)*h;const cx=(px+x)/2;lctx.bezierCurveTo(cx,py,cx,y,x,y);}});
  lctx.lineTo(w,h);lctx.closePath();lctx.fillStyle=g;lctx.fill();
  lctx.beginPath();ld.forEach((v,i)=>{const x=i*step,y=h-(v/100)*h;if(i===0)lctx.moveTo(x,y);else{const px=(i-1)*step,py=h-(ld[i-1]/100)*h;const cx=(px+x)/2;lctx.bezierCurveTo(cx,py,cx,y,x,y);}});
  lctx.strokeStyle='#1e3a5f';lctx.lineWidth=2;lctx.stroke();
  const ly=h-(ld[ld.length-1]/100)*h;
  lctx.beginPath();lctx.arc(w,ly,4,0,Math.PI*2);lctx.fillStyle='#1e3a5f';lctx.fill();
  lctx.beginPath();lctx.arc(w,ly,8,0,Math.PI*2);lctx.fillStyle='rgba(45,107,207,.25)';lctx.fill();
}
setInterval(()=>{ld.shift();ld.push(15+Math.random()*60);drawLC();
},700);drawLC();

// ===== NETWORK CANVAS =====
const nc=document.getElementById('networkCanvas'),nctx=nc.getContext('2d');
let nodes=[];
function initNet(){const w=nc.width=nc.offsetWidth*2,h=nc.height=nc.offsetHeight*2;
  nodes=Array.from({length:35},()=>({x:Math.random()*w,y:Math.random()*h,vx:(Math.random()-.5)*.4,vy:(Math.random()-.5)*.4,r:2+Math.random()*3,p:Math.random()*Math.PI*2}));}
function drawNet(){const w=nc.width,h=nc.height;nctx.clearRect(0,0,w,h);
  nodes.forEach((a,i)=>{nodes.forEach((b,j)=>{if(j<=i)return;const d=Math.hypot(a.x-b.x,a.y-b.y);
    if(d<160){nctx.beginPath();nctx.moveTo(a.x,a.y);nctx.lineTo(b.x,b.y);nctx.strokeStyle=`rgba(45,107,207,${(1-d/160)*.15})`;nctx.lineWidth=1;nctx.stroke();}});
    a.x+=a.vx;a.y+=a.vy;a.p+=.02;if(a.x<0||a.x>w)a.vx*=-1;if(a.y<0||a.y>h)a.vy*=-1;
    const gl=.35+Math.sin(a.p)*.25;
    nctx.beginPath();nctx.arc(a.x,a.y,a.r*2,0,Math.PI*2);nctx.fillStyle=`rgba(45,107,207,${gl*.1})`;nctx.fill();
    nctx.beginPath();nctx.arc(a.x,a.y,a.r,0,Math.PI*2);nctx.fillStyle=`rgba(45,107,207,${gl})`;nctx.fill();
  });
  const t=Date.now()*.001;
  for(let i=0;i<6;i++){const a=nodes[i%nodes.length],b=nodes[(i*5+7)%nodes.length];
    const pr=((t*(.25+i*.08))%1);const px=a.x+(b.x-a.x)*pr,py=a.y+(b.y-a.y)*pr;
    nctx.beginPath();nctx.arc(px,py,2,0,Math.PI*2);nctx.fillStyle='rgba(99,102,241,.7)';nctx.fill();
    nctx.beginPath();nctx.arc(px,py,5,0,Math.PI*2);nctx.fillStyle='rgba(99,102,241,.12)';nctx.fill();}
  requestAnimationFrame(drawNet);}
initNet();drawNet();window.addEventListener('resize',initNet);

// ===== CTA PARTICLES =====
const pc=document.getElementById('ctaParticles'),pctx=pc.getContext('2d');let pts=[];
function initP(){const w=pc.width=pc.offsetWidth*2,h=pc.height=pc.offsetHeight*2;
  pts=Array.from({length:35},()=>({x:Math.random()*w,y:Math.random()*h,vx:(Math.random()-.5)*.25,vy:(Math.random()-.5)*.25,r:1+Math.random()*1.2}));}
function drawP(){const w=pc.width,h=pc.height;pctx.clearRect(0,0,w,h);
  pts.forEach((p,i)=>{p.x+=p.vx;p.y+=p.vy;if(p.x<0||p.x>w)p.vx*=-1;if(p.y<0||p.y>h)p.vy*=-1;
    pctx.beginPath();pctx.arc(p.x,p.y,p.r,0,Math.PI*2);pctx.fillStyle='rgba(45,107,207,.35)';pctx.fill();
    pts.forEach((q,j)=>{if(j<=i)return;const d=Math.hypot(p.x-q.x,p.y-q.y);
      if(d<100){pctx.beginPath();pctx.moveTo(p.x,p.y);pctx.lineTo(q.x,q.y);pctx.strokeStyle=`rgba(45,107,207,${(1-d/100)*.1})`;pctx.lineWidth=1;pctx.stroke();}});
  });requestAnimationFrame(drawP);}
initP();drawP();

// ===== LANDING ACCESS REQUEST =====
(function () {
  const form = document.getElementById('access-request-form');
  const emailInput = document.getElementById('access-request-email');
  const submitBtn = document.getElementById('access-request-submit');
  const feedback = document.getElementById('access-request-feedback');
  if (!form || !emailInput || !submitBtn || !feedback) return;

  const endpoint = @json(route('access-request.store'));
  const csrf = @json(csrf_token());

  function setFeedback(message, type) {
    feedback.textContent = message || '';
    feedback.classList.remove('ok', 'err');
    if (type) feedback.classList.add(type);
  }

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    const email = emailInput.value.trim();
    if (!email) {
      setFeedback('Please enter your email.', 'err');
      return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';
    setFeedback('', null);

    try {
      const res = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ email }),
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        setFeedback(data.message || 'Unable to submit request right now.', 'err');
        return;
      }
      setFeedback(data.message || 'Request sent. Admin has been notified.', 'ok');
      form.reset();
    } catch (err) {
      setFeedback('Unable to submit request right now.', 'err');
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Request Access';
    }
  });
})();

// ===== REQUEST ACCESS QUICK SCROLL =====
(function () {
  const scrollBtns = document.querySelectorAll('.js-scroll-bottom');
  const bottomTarget = document.getElementById('landing-bottom') || document.body;
  if (!scrollBtns.length || !bottomTarget) return;
  scrollBtns.forEach((btn) => {
    btn.addEventListener('click', function () {
      const navEl = document.querySelector('nav');
      const navOffset = navEl ? navEl.offsetHeight + 12 : 92;
      const targetTop = bottomTarget.getBoundingClientRect().top + window.scrollY - navOffset;
      window.scrollTo({ top: Math.max(0, targetTop), behavior: 'smooth' });
    });
  });
})();

// ===== SCROLL REVEAL =====
const obs=new IntersectionObserver(e=>{e.forEach(en=>{if(en.isIntersecting)en.target.classList.add('vis')})},{threshold:.08});
document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));

// ===== NAV ACTIVE HIGHLIGHT =====
(function () {
  const navLinks = Array.from(document.querySelectorAll('.nav-center a[href^="#"]'));
  if (!navLinks.length) return;
  const sections = navLinks
    .map(link => {
      const target = document.querySelector(link.getAttribute('href'));
      return target ? { link, target } : null;
    })
    .filter(Boolean);
  if (!sections.length) return;

  function setActive(link) {
    navLinks.forEach(a => a.classList.remove('active'));
    if (link) link.classList.add('active');
  }
  let lockActiveUntil = 0;
  let lockedLink = null;

  // Keep nav state synced when opening with hash (e.g., /#about).
  const currentHash = window.location.hash;
  const hashMatch = navLinks.find(a => a.getAttribute('href') === currentHash);
  if (hashMatch) setActive(hashMatch);

  const navObserver = new IntersectionObserver((entries) => {
    if (Date.now() < lockActiveUntil) return;
    const visible = entries
      .filter(entry => entry.isIntersecting)
      .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
    if (!visible) return;
    const match = sections.find(item => item.target === visible.target);
    if (match) setActive(match.link);
  }, {
    root: null,
    threshold: [0.3, 0.5, 0.7],
    rootMargin: '-20% 0px -45% 0px',
  });

  sections.forEach(item => navObserver.observe(item.target));
  window.addEventListener('scroll', () => {
    if (!lockedLink || Date.now() >= lockActiveUntil) return;
    const targetSelector = lockedLink.getAttribute('href');
    const targetSection = document.querySelector(targetSelector);
    if (!targetSection) return;
    const navEl = document.querySelector('nav');
    const navOffset = navEl ? navEl.offsetHeight + 14 : 94;
    const targetTop = targetSection.getBoundingClientRect().top + window.scrollY - navOffset;
    if (Math.abs(window.scrollY - targetTop) < 10) {
      lockActiveUntil = 0;
      setActive(lockedLink);
      lockedLink = null;
    }
  }, { passive: true });
  navLinks.forEach(link => link.addEventListener('click', (e) => {
    const targetSelector = link.getAttribute('href');
    const targetSection = document.querySelector(targetSelector);
    if (!targetSection) return;
    e.preventDefault();
    lockActiveUntil = Date.now() + 900;
    lockedLink = link;
    setActive(link);
    history.pushState(null, '', targetSelector);
    const navEl = document.querySelector('nav');
    const navOffset = navEl ? navEl.offsetHeight + 14 : 94;
    const targetTop = targetSection.getBoundingClientRect().top + window.scrollY - navOffset;
    window.scrollTo({ top: Math.max(0, targetTop), behavior: 'smooth' });
  }));
})();
</script>
</body>
</html>


