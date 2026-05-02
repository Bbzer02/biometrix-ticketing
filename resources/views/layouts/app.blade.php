@php
    $navKey = 'none';
    $reqPath = request()->path(); // e.g. "home", "tickets", "tickets/5", "users", etc.
    if ($reqPath === 'home') {
        $navKey = 'home';
    } elseif (str_starts_with($reqPath, 'tickets')) {
        $navKey = request()->boolean('mine') ? 'tickets-mine' : 'tickets';
    } elseif (str_starts_with($reqPath, 'users')) {
        $navKey = 'users';
    } elseif (str_starts_with($reqPath, 'admin/staff-announcements')) {
        $navKey = 'announcements';
    } elseif (str_starts_with($reqPath, 'admin/audit-trail')) {
        $navKey = 'audit';
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <script>
        (function () {
            function prefersDark() {
                return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            }
            function resolveTheme() {
                try {
                    var t = localStorage.getItem('theme');
                    if (t === 'light') return 'light';
                    if (t === 'dark') return 'dark';
                    return prefersDark() ? 'dark' : 'light';
                } catch (e) {
                    return 'light';
                }
            }
            window.applyTheme = function (options) {
                var root = document.documentElement;
                var shouldAnimate = !(options && options.instant);
                if (shouldAnimate) root.classList.add('theme-transitioning');
                root.classList.toggle('dark', resolveTheme() === 'dark');
                if (typeof window.initDashboardCharts === 'function') {
                    window.setTimeout(function () {
                        try { window.initDashboardCharts(); } catch (e) {}
                    }, shouldAnimate ? 40 : 0);
                }
                if (shouldAnimate) {
                    window.setTimeout(function () {
                        root.classList.remove('theme-transitioning');
                    }, 520);
                }
            };
            window.syncThemeRadios = function () {};
            window.applyTheme({ instant: true });
            if (window.matchMedia) {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
                    try {
                        var t = localStorage.getItem('theme');
                        if (t === 'light' || t === 'dark') return;
                        window.applyTheme();
                    } catch (e) {}
                });
            }
        })();
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>@yield('title', config('app.name')) — IT Helpdesk</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('main-logo-icon.png') }}?v={{ now()->timestamp }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('main-logo-icon.png') }}?v={{ now()->timestamp }}">
    <link rel="apple-touch-icon" href="{{ asset('main-logo-icon.png') }}?v={{ now()->timestamp }}">
    <link rel="preload" as="image" href="{{ asset('image/bstc-logo.png') }}" fetchpriority="high">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* iOS glass: body gradient so glass panels have depth */
        body.bg-glass-page {
            background-color: #f7f5ff;
            background-image:
                radial-gradient(ellipse 80% 60% at 10% 10%, rgba(180, 150, 255, 0.40) 0%, transparent 60%),
                radial-gradient(ellipse 70% 55% at 90% 15%, rgba(130, 195, 255, 0.38) 0%, transparent 55%),
                radial-gradient(ellipse 60% 50% at 80% 85%, rgba(100, 220, 230, 0.30) 0%, transparent 55%),
                radial-gradient(ellipse 65% 45% at 20% 80%, rgba(210, 140, 255, 0.28) 0%, transparent 55%),
                radial-gradient(ellipse 40% 40% at 50% 48%, rgba(190, 180, 255, 0.22) 0%, transparent 55%);
            background-attachment: fixed;
        }
        html.dark body.bg-glass-page {
            background-color: #07080f;
            background-image:
                radial-gradient(ellipse 80% 60% at 10% 10%, rgba(59, 36, 120, 0.55) 0%, transparent 60%),
                radial-gradient(ellipse 70% 55% at 90% 15%, rgba(20, 70, 130, 0.50) 0%, transparent 55%),
                radial-gradient(ellipse 60% 50% at 80% 85%, rgba(15, 90, 100, 0.40) 0%, transparent 55%),
                radial-gradient(ellipse 65% 45% at 20% 80%, rgba(80, 20, 100, 0.35) 0%, transparent 55%),
                radial-gradient(ellipse 40% 40% at 50% 48%, rgba(30, 30, 80, 0.30) 0%, transparent 55%);
            background-attachment: fixed;
            color: #e2e8f0;
        }
        html.theme-transitioning,
        html.theme-transitioning *,
        html.theme-transitioning *::before,
        html.theme-transitioning *::after {
            transition-property: background-color, color, border-color, fill, stroke, box-shadow !important;
            transition-duration: 0.45s !important;
            transition-timing-function: ease !important;
        }
        /* Header: frosted glass */
        header.sticky { -webkit-backdrop-filter: blur(24px) saturate(180%); backdrop-filter: blur(24px) saturate(180%); background: rgba(255,255,255,0.72); border-color: rgba(226,232,240,0.8); }
        html.dark header.sticky { background: rgba(30,41,59,0.78); border-color: rgba(51,65,85,0.8); }
        html.dark body .sticky.border-b { border-color: rgba(51,65,85,0.8); }
        #app-main-content { background: rgba(255,255,255,0.38); }
        html.dark #app-main-content { background: rgba(10,13,22,0.42); color: #f1f5f9; }
        html.dark #app-main-content .content-header h1,
        html.dark #app-main-content .content-header p,
        html.dark #app-main-content .content-header span { color: #f1f5f9 !important; }
        html.dark #app-main-content .dashboard-chart-card { background-color: rgba(255,255,255,0.05) !important; border-color: rgba(255,255,255,0.1) !important; color: #f1f5f9 !important; }
        html.dark #app-main-content .dashboard-stat-card {
            background: linear-gradient(140deg, #0f1b3d 0%, #0a1633 50%, #081127 100%) !important;
            border-color: rgba(96, 165, 250, 0.36) !important;
            color: #e2e8f0 !important;
            box-shadow:
                0 0 0 1px rgba(59, 130, 246, 0.15),
                0 14px 36px -18px rgba(37, 99, 235, 0.85) !important;
        }
        /* Queue status (first chart): blue neon glow — overrides flat chart rule above */
        html.dark #app-main-content .dashboard-chart-card.dashboard-status-card {
            border-color: rgba(96, 165, 250, 0.38) !important;
            box-shadow:
                0 0 0 1px rgba(59, 130, 246, 0.22),
                0 0 28px rgba(59, 130, 246, 0.45),
                0 0 52px rgba(37, 99, 235, 0.22),
                0 14px 36px -18px rgba(37, 99, 235, 0.78) !important;
        }
        #app-main-content .dashboard-chart-card.dashboard-status-card {
            box-shadow:
                0 0 0 1px rgba(59, 130, 246, 0.12),
                0 12px 36px -14px rgba(37, 99, 235, 0.28),
                0 25px 50px -20px rgba(148, 163, 184, 0.35);
        }
        #ticket-quick-view-body { -ms-overflow-style: none; scrollbar-width: none; }
        #ticket-quick-view-body::-webkit-scrollbar { width: 0; height: 0; display: none; }
        #it-help-badge:empty, #it-help-badge-rail:empty { display: none !important; }
    </style>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @auth
    <script>window.csrfToken = "{{ csrf_token() }}";</script>
    @endauth
    @auth
    {{-- Global DataTables assets for all authenticated pages that use tables --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.dataTables.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" defer></script>
    <script src="https://cdn.datatables.net/2.3.7/js/dataTables.js" defer></script>
    @endauth
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>tailwind.config = { darkMode: 'class' };</script>
        <style>
            html { scroll-behavior: smooth; }
            .swift-transition { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
            .swift-transition-slow { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
            .badge-ticket { display: inline-flex; align-items: center; border-radius: 0.5rem; padding: 0.125rem 0.625rem; font-size: 0.75rem; font-weight: 500; }
            .badge-ticket.badge-open, .badge-ticket.badge-new { background: #dbeafe; color: #1e40af; }
            .badge-ticket.badge-in_progress { background: #fef3c7; color: #92400e; }
            .badge-ticket.badge-waiting_on_user { background: #e0e7ff; color: #3730a3; }
            .badge-ticket.badge-resolved { background: #d1fae5; color: #065f46; }
            .badge-ticket.badge-closed { background: #e2e8f0; color: #475569; }
            .badge-ticket.badge-cancelled { background: #fecaca; color: #991b1b; }
            .input-ticket { margin-top: 0.25rem; display: block; width: 100%; border-radius: 0.75rem; border: 1px solid #cbd5e1; background: #ffffff; color: #0f172a; padding: 0.625rem 1rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
            .input-ticket:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.2); }
            .label-ticket { display: block; font-size: 0.875rem; font-weight: 500; color: #334155; }
            .pagination { display: flex; flex-wrap: wrap; gap: 0.5rem; justify-content: center; align-items: center; margin-top: 1.5rem; }
            .pagination a, .pagination span { display: inline-flex; align-items: center; justify-content: center; min-width: 2.25rem; height: 2.25rem; padding: 0 0.75rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 500; }
            .pagination a { background: #fff; border: 1px solid #e2e8f0; color: #334155; }
            .pagination a:hover { background: #f8fafc; border-color: #cbd5e1; }
            .pagination span[aria-current="page"] { background: #1e293b; color: #fff; border: 1px solid #1e293b; }
            .pagination .disabled span { background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; }
            .category-btn:has(input:checked) { border-color: #2563eb; background: #eff6ff; color: #1d4ed8; }
            /* Sidebar — two-panel iOS style */
            .sidebar-panel { display: flex; flex-direction: row; align-items: flex-start; height: 100vh; position: sticky; top: 0; background: transparent; border-right: none; overflow: visible; }
            .sidebar-rail { width: 4.5rem; min-width: 4.5rem; display: flex; flex-direction: column; align-items: center; background: rgba(24,34,54,0.94); border: 1px solid rgba(148,163,184,0.24); border-radius: 1rem; margin: 0.625rem 0 0.625rem 0.625rem; box-shadow: 0 8px 28px rgba(2,6,23,0.35), 0 2px 8px rgba(2,6,23,0.25); z-index: 2; height: calc(100vh - 1.25rem); flex-shrink: 0; overflow: hidden; align-self: flex-start; }
            .sidebar-rail-logo { height: 3.25rem; width: 100%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border-bottom: 1px solid rgba(148,163,184,0.2); transition: visibility 0.2s ease; }
            .sidebar-rail-nav { flex: 1; display: flex; flex-direction: column; align-items: center; padding: 0.5rem 0; overflow-y: auto; width: 100%; }
            .sidebar-rail-icon { display: flex; align-items: center; justify-content: center; width: 3rem; height: 3rem; border-radius: 1.25rem; color: rgba(226,232,240,0.68); transition: background 0.18s, color 0.18s; cursor: pointer; position: relative; border: none; background: transparent; flex-shrink: 0; }
            .sidebar-rail-icon:hover { background: rgba(148,163,184,0.16); color: #ffffff; }
            .sidebar-rail-icon.active { background: rgba(59,130,246,0.24); color: #dbeafe; box-shadow: inset 0 1px 0 rgba(255,255,255,0.2); }
            /* Label panel — floating rounded card */
            .sidebar-label-panel { display: flex; flex-direction: column; width: 11.5rem; background: rgba(31,41,64,0.94); backdrop-filter: blur(24px) saturate(180%); -webkit-backdrop-filter: blur(24px) saturate(180%); border: 1px solid rgba(148,163,184,0.22); border-radius: 1rem; margin: 0.625rem 0 0.625rem 0.5rem; box-shadow: 0 8px 28px rgba(2,6,23,0.35); overflow: hidden; transition: width 0.26s cubic-bezier(0.4,0,0.2,1), opacity 0.2s ease, margin 0.26s ease; opacity: 1; height: calc(100vh - 1.25rem); align-self: flex-start; }
            .sidebar-panel.collapsed .sidebar-label-panel { width: 0; opacity: 0; margin-left: 0; pointer-events: none; }
            /* Label panel brand: visible only when expanded */
            .sidebar-panel.collapsed #sidebar-label-brand { opacity: 0 !important; }
            .sidebar-label-nav { flex: 1; overflow-y: auto; padding: 0.5rem 0; }
            .sidebar-nav { display: flex; flex-direction: column; }
            .sidebar-nav-wrap { position: relative; }
            .sidebar-link { display: flex; align-items: center; font-size: 0.875rem; font-weight: 500; color: rgba(226,232,240,0.82); padding: 0 0.875rem; height: 3rem; border-radius: 0.875rem; margin: 0 0.375rem; width: calc(100% - 0.75rem); box-sizing: border-box; transition: background 0.18s, color 0.18s; white-space: nowrap; }
            .sidebar-link:hover { background: rgba(148,163,184,0.16); color: #ffffff; }
            .sidebar-link.active, .sidebar-link.active:hover { background: rgba(59,130,246,0.24); color: #dbeafe; font-weight: 600; box-shadow: inset 0 1px 0 rgba(255,255,255,0.2); }
            html.dark .sidebar-rail { background: rgba(18,22,30,0.97); border-color: rgba(255,255,255,0.09); box-shadow: 0 8px 32px rgba(0,0,0,0.45), 0 2px 8px rgba(0,0,0,0.3); }
            html.dark .sidebar-rail-logo { border-bottom-color: rgba(255,255,255,0.08); }
            html.dark .sidebar-rail-icon { color: rgba(255,255,255,0.45); }
            html.dark .sidebar-rail-icon:hover { background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.9); }
            html.dark .sidebar-rail-icon.active { background: rgba(255,255,255,0.18); color: #ffffff; box-shadow: inset 0 1px 0 rgba(255,255,255,0.15); }
            html.dark .sidebar-label-panel { background: rgba(28,33,46,0.97); border-color: rgba(255,255,255,0.09); box-shadow: 0 8px 32px rgba(0,0,0,0.45); }
            html.dark .sidebar-link { color: rgba(255,255,255,0.6); }
            html.dark .sidebar-link:hover { background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.9); }
            html.dark .sidebar-link.active, html.dark .sidebar-link.active:hover { background: rgba(255,255,255,0.14); color: #ffffff; box-shadow: inset 0 1px 0 rgba(255,255,255,0.1); }
            /* Logo chip: dark logo disk reads against navy sidebar */
            .sidebar-brand-logo-wrap {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                padding: 4px;
                border-radius: 0.625rem;
                background: rgba(255, 255, 255, 0.98);
                box-shadow: 0 0 0 1px rgba(15, 23, 42, 0.12), 0 2px 8px rgba(0, 0, 0, 0.18);
            }
            .sidebar-brand-logo-wrap--header { padding: 3px; border-radius: 0.5rem; }
            .sidebar-brand-logo-wrap img { display: block; border-radius: 0.375rem; }
            html.dark .sidebar-brand-logo-wrap {
                background: #cbd5e1;
                box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.22), 0 2px 10px rgba(0, 0, 0, 0.45);
            }
        </style>
    @endif
</head>
<body class="min-h-screen antialiased transition-colors duration-200 bg-glass-page">
@auth
@php
    session()->forget(['post_login_target', 'post_login_swal_text']);
@endphp
@endauth
    <div class="flex min-h-screen">
    {{-- Two-panel iOS-style sidebar: icon rail (always visible) + label panel (slides in/out) --}}
    <aside class="sidebar-drawer fixed left-0 top-0 bottom-0 z-50 flex flex-col shrink-0 lg:static">
        <div class="sidebar-panel" id="sidebar-panel">

            {{-- RAIL: narrow icon column, always visible --}}
            <div class="sidebar-rail" id="sidebar-rail">
                {{-- Logo: only visible when sidebar is collapsed --}}
                <div class="sidebar-rail-logo" id="sidebar-rail-logo" style="visibility:hidden">
                    <a href="{{ route('home') }}" id="sidebar-rail-brand" class="flex items-center justify-center text-white no-underline" aria-label="Toggle sidebar">
                        <span class="sidebar-brand-logo-wrap"><img src="{{ asset('image/bstc-logo.png') }}" alt="Biometrix" class="h-9 w-9 shrink-0 object-contain" width="179" height="172" fetchpriority="high" decoding="async"></span>
                    </a>
                </div>
                {{-- Rail nav icons --}}
                <nav class="sidebar-rail-nav" aria-label="Main navigation icons">
                    <a href="{{ route('home') }}" class="sidebar-rail-icon" data-nav="home" data-tooltip="Dashboard">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </a>
                    @auth
                    <a href="{{ route('tickets.index') }}" class="sidebar-rail-icon" data-nav="tickets" data-tooltip="{{ auth()->user()->role === \App\Models\User::ROLE_EMPLOYEE && !auth()->user()->isItStaff() && !auth()->user()->isAdmin() ? 'Tickets' : 'All tickets' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </a>
                    @if(auth()->user()->isFrontDesk() && !auth()->user()->isAdmin())
                    <a href="{{ route('tickets.index', ['mine' => 1]) }}" class="sidebar-rail-icon" data-nav="tickets-mine" data-tooltip="My logged tickets">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </a>
                    @endif
                    @if(auth()->user()->isItStaff() && !auth()->user()->isAdmin())
                    <button type="button" id="it-help-request-btn-rail" class="sidebar-rail-icon relative" data-tooltip="Help request" aria-label="Help request">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span id="it-help-badge-rail" class="hidden absolute -top-0.5 -right-0.5 inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-500 px-0.5 text-[9px] font-bold text-white"></span>
                    </button>
                    @endif
                    @if(auth()->user()->isItStaff() || auth()->user()->isAdmin())
                    <button type="button" id="it-categories-btn-rail" class="sidebar-rail-icon" data-tooltip="Customize form" aria-label="Customize form">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 012-2z"/></svg>
                    </button>
                    @endif
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('users.index') }}" class="sidebar-rail-icon" data-nav="users" data-tooltip="Users">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </a>
                    <a href="{{ route('admin.staff-announcements.index') }}" class="sidebar-rail-icon" data-nav="announcements" data-tooltip="Staff announcements">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.4 5.6a10.15 10.15 0 01.2 14.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 8.2a6.1 6.1 0 010 7.6"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882A2.117 2.117 0 0113.117 3.765h1.18a1 1 0 01.97.757l.91 3.64a1 1 0 01-.242.915l-3.7 3.7A2.117 2.117 0 0111 14.3V5.882z"/></svg>
                    </a>
                    <a href="{{ route('admin.audit-trail.index') }}" class="sidebar-rail-icon" data-nav="audit" data-tooltip="Audit trail">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </a>
                    @endif
                    @endauth
                </nav>
                {{-- Collapse toggle at bottom of rail --}}
                <div class="py-3 border-t border-white/10 flex items-center justify-center w-full">
                    <button type="button" id="sidebar-collapse-btn" class="sidebar-rail-icon group" aria-label="Expand sidebar" title="Expand sidebar">
                        {{-- Animated sidebar toggle icon --}}
                        <span id="sidebar-toggle-icon" style="display:flex;align-items:center;justify-content:center;width:1.25rem;height:1.25rem;position:relative">
                            {{-- Mini sidebar representation --}}
                            <span class="sidebar-toggle-wrap" style="display:flex;gap:2px;align-items:stretch;height:14px;width:18px">
                                {{-- Rail bar --}}
                                <span class="sidebar-toggle-rail" style="width:4px;background:rgba(255,255,255,.5);border-radius:2px;flex-shrink:0;transition:width .3s ease"></span>
                                {{-- Content lines --}}
                                <span style="display:flex;flex-direction:column;justify-content:center;gap:2px;flex:1">
                                    <span class="sidebar-toggle-line1" style="height:2px;background:rgba(255,255,255,.35);border-radius:1px;transition:width .3s ease .05s,opacity .3s ease"></span>
                                    <span class="sidebar-toggle-line2" style="height:2px;background:rgba(255,255,255,.25);border-radius:1px;width:70%;transition:width .3s ease .1s,opacity .3s ease"></span>
                                    <span class="sidebar-toggle-line3" style="height:2px;background:rgba(255,255,255,.2);border-radius:1px;width:50%;transition:width .3s ease .15s,opacity .3s ease"></span>
                                </span>
                                {{-- Arrow indicator --}}
                                <span class="sidebar-toggle-arrow" style="display:flex;align-items:center;justify-content:center;width:6px;flex-shrink:0;transition:transform .3s ease">
                                    <svg style="width:6px;height:8px;color:rgba(255,255,255,.6)" fill="none" stroke="currentColor" viewBox="0 0 6 10"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M1 1l4 4-4 4"/></svg>
                                </span>
                            </span>
                        </span>
                    </button>
                </div>
                <style>
                /* Collapsed state: lines shrink, arrow flips */
                .sidebar-panel.collapsed ~ * #sidebar-collapse-btn .sidebar-toggle-line1,
                #sidebar-panel.collapsed ~ * #sidebar-collapse-btn .sidebar-toggle-line1 { width: 0; opacity: 0; }
                /* Arrow flip when expanded */
                #sidebar-panel:not(.collapsed) #sidebar-collapse-btn .sidebar-toggle-arrow { transform: rotate(180deg); }
                /* Hover: pulse the rail */
                #sidebar-collapse-btn:hover .sidebar-toggle-rail { background: rgba(255,255,255,.8); }
                #sidebar-collapse-btn:hover .sidebar-toggle-arrow svg { color: rgba(255,255,255,.9); }
                </style>
            </div>

            {{-- LABEL PANEL: slides out next to the rail --}}
            <div class="sidebar-label-panel" id="sidebar-label-panel">
                {{-- Brand header: logo + app name, shown when expanded --}}
                <a href="{{ route('home') }}" style="height:3.25rem;flex-shrink:0;border-bottom:1px solid rgba(255,255,255,0.08);display:flex;flex-direction:row;align-items:center;padding:0 0.875rem;gap:0.5rem;text-decoration:none;overflow:hidden;white-space:nowrap;opacity:1;transition:opacity 0.2s;cursor:pointer;" id="sidebar-label-brand" aria-label="Toggle sidebar">
                    <span class="sidebar-brand-logo-wrap sidebar-brand-logo-wrap--header"><img src="{{ asset('image/bstc-logo.png') }}" alt="Biometrix" width="179" height="172" style="height:2rem;width:2rem;object-fit:contain;flex-shrink:0;" fetchpriority="high" decoding="async"></span>
                    <span style="color:#fff;font-weight:600;font-size:0.875rem;line-height:1.2;white-space:nowrap;display:block;">IT Helpdesk</span>
                </a>
                <nav class="flex-1 overflow-y-auto sidebar-label-nav" aria-label="Main navigation labels">
                <div class="sidebar-nav-wrap">
                <div class="sidebar-nav">
                    <a href="{{ route('home') }}" class="sidebar-link sidebar-nav-item" data-nav="home" data-no-loading><span class="sidebar-text">Dashboard</span></a>
                    @auth
                    @if(auth()->user()->role === \App\Models\User::ROLE_EMPLOYEE && !auth()->user()->isItStaff() && !auth()->user()->isAdmin())
                    <a href="{{ route('tickets.index') }}" class="sidebar-link sidebar-nav-item" data-nav="tickets" data-no-loading><span class="sidebar-text">Tickets</span></a>
                    @else
                    <a href="{{ route('tickets.index') }}" class="sidebar-link sidebar-nav-item" data-nav="tickets" data-no-loading><span class="sidebar-text">All tickets</span></a>
                    @if(auth()->user()->isItStaff() && !auth()->user()->isAdmin())
                    <button type="button" id="it-help-request-btn" class="sidebar-link sidebar-nav-item relative" aria-label="Help request"><span class="sidebar-text">Help request</span><span id="it-help-badge" class="hidden ml-auto shrink-0 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"></span></button>
                    @endif
                    @if(auth()->user()->isItStaff() || auth()->user()->isAdmin())
                    <button type="button" id="it-categories-btn" class="sidebar-link sidebar-nav-item" aria-label="Customize form"><span class="sidebar-text">Customize form</span></button>
                    @endif
                    @if(auth()->user()->isFrontDesk() && !auth()->user()->isAdmin())
                    <a href="{{ route('tickets.index', ['mine' => 1]) }}" class="sidebar-link sidebar-nav-item" data-nav="tickets-mine" data-no-loading><span class="sidebar-text">My logged tickets</span></a>
                    @endif
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('users.index') }}" class="sidebar-link sidebar-nav-item" data-nav="users" data-no-loading><span class="sidebar-text">Users</span></a>
                    <a href="{{ route('admin.staff-announcements.index') }}" class="sidebar-link sidebar-nav-item" data-nav="announcements" data-no-loading><span class="sidebar-text">Staff announcements</span></a>
                    <a href="{{ route('admin.audit-trail.index') }}" class="sidebar-link sidebar-nav-item" data-nav="audit"><span class="sidebar-text">Audit trail</span></a>
                    @endif
                    @endif
                    @endauth
                </div>
                @auth
                <form id="logout-form" action="{{ route('logout') }}" method="post" class="hidden">@csrf</form>
                @endauth
                </div>
                </nav>
            </div>{{-- end .sidebar-label-panel --}}

        </div>{{-- end .sidebar-panel --}}
    </aside>
    <div id="sidebar-mobile-backdrop" class="sidebar-mobile-backdrop" aria-hidden="true"></div>

    {{-- Main content area --}}
    <div id="app-shell-main" class="flex-1 flex flex-col min-h-screen min-w-0 ml-[5.75rem] lg:ml-0">
        {{-- Top header bar --}}
        <header id="app-top-header" class="sticky top-0 z-30 flex items-center justify-between gap-4 bg-white/80 px-4 py-2.5 shadow-lg sm:px-6 lg:px-8 dark:bg-slate-800/85 backdrop-blur-xl backdrop-saturate-150 mx-3 mt-3 rounded-2xl border border-slate-200/70 dark:border-slate-700/60">
            {{-- Search: only on Dashboard and All tickets (both in DOM so SPA can toggle) --}}
            @auth
            @php
                $showHeaderSearch = request()->routeIs('home');
                $headerSearchLinks = [
                    ['title' => 'Dashboard', 'url' => route('home'), 'keywords' => 'home dashboard overview'],
                    ['title' => 'Settings', 'url' => route('settings'), 'keywords' => 'settings preferences appearance'],
                    ['title' => 'Security', 'url' => route('settings.security'), 'keywords' => 'security password account'],
                    ['title' => 'Profile', 'url' => route('profile.edit'), 'keywords' => 'profile account user'],
                    ['title' => 'All Tickets', 'url' => route('tickets.index'), 'keywords' => 'tickets issues requests'],
                ];
                if (auth()->user()?->isFrontDesk() && !auth()->user()?->isAdmin()) {
                    $headerSearchLinks[] = ['title' => 'My Logged Tickets', 'url' => route('tickets.index', ['mine' => 1]), 'keywords' => 'front desk my tickets logged'];
                }
                if (auth()->user()?->isAdmin()) {
                    $headerSearchLinks[] = ['title' => 'Users', 'url' => route('users.index'), 'keywords' => 'users accounts admin'];
                    $headerSearchLinks[] = ['title' => 'Staff Announcements', 'url' => route('admin.staff-announcements.index'), 'keywords' => 'announcement message staff'];
                    $headerSearchLinks[] = ['title' => 'Audit Trail', 'url' => route('admin.audit-trail.index'), 'keywords' => 'audit logs activity'];
                    $headerSearchLinks[] = ['title' => 'Admin Settings', 'url' => route('admin.settings'), 'keywords' => 'admin settings configuration'];
                }
            @endphp
            <div class="flex-1 flex min-w-0" id="header-search-wrap">
                <form action="{{ route('home') }}" method="get" class="flex-1 max-w-xl mx-auto lg:mx-0 lg:max-w-md {{ $showHeaderSearch ? '' : 'hidden' }}" id="header-search-form" role="search">
                    <label for="header-search" class="sr-only">Search pages and tickets</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="search" name="q" id="header-search" value="{{ request('q') }}"
                               class="block w-full rounded-2xl border border-slate-300 bg-slate-50/50 py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder-slate-500 transition-colors focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-700/50 dark:text-slate-100 dark:placeholder-slate-400 dark:focus:border-blue-400 dark:focus:bg-slate-700 dark:focus:ring-blue-400/20"
                               placeholder="Search pages or tickets…" autocomplete="off">
                        {{-- Live search dropdown --}}
                        <div id="header-search-dropdown"
                             class="absolute left-0 right-0 top-full mt-1 z-50 hidden rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-600 dark:bg-slate-800 overflow-hidden">
                        </div>
                    </div>
                </form>
                <div class="flex-1 {{ $showHeaderSearch ? 'hidden' : '' }}" id="header-search-spacer" aria-hidden="true"></div>
            </div>
            @else
            <div class="flex-1"></div>
            @endauth
            <div class="flex items-center gap-2 shrink-0">
                @auth
                @php
                    $user = auth()->user();

                    // Performance: don't run DB queries in the layout.
                    // We fetch badge + dropdown content asynchronously via /notifications/header after page paint.
                    $isEmployee = $user && $user->role === \App\Models\User::ROLE_EMPLOYEE && ! $user->isItStaff() && ! $user->isAdmin();
                    $notificationBadgeMode = $isEmployee ? 'open' : 'notifications';
                    $notificationBadgeCount = 0;
                    $blockClearNotifications = false;
                @endphp
                <div class="relative">
                    <button type="button"
                            id="header-notifications-button"
                            data-badge-count="{{ $notificationBadgeCount }}"
                            data-badge-mode="{{ $notificationBadgeMode }}"
                            class="relative inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50/80 text-slate-500 shadow-sm transition-all hover:bg-slate-100 hover:text-slate-700 hover:shadow-md dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-300 dark:hover:bg-slate-700 {{ $notificationBadgeCount > 0 ? 'header-bell-has-unread' : '' }}"
                            aria-label="Notifications"
                            aria-expanded="false">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if($notificationBadgeCount > 0)
                            <span class="header-badge absolute -top-0.5 -right-0.5 inline-flex min-h-[1.15rem] min-w-[1.15rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white shadow-sm">
                                {{ $notificationBadgeCount }}
                            </span>
                        @endif
                    </button>
                    <div id="header-notifications-panel"
                         class="absolute right-0 z-40 mt-2 w-80 origin-top-right rounded-2xl border border-slate-200/80 bg-white/95 py-2 text-sm shadow-xl ring-1 ring-black/5 backdrop-blur-md opacity-0 translate-y-1 scale-95 pointer-events-none transition ease-out duration-150 dark:border-slate-600/80 dark:bg-slate-800/95"
                         data-open="0">
                        <div class="px-3.5 pb-1.5 flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            <span>Notifications</span>
                            <button type="button"
                                    id="header-notifications-clear"
                                    data-block-clear="{{ $blockClearNotifications ? '1' : '0' }}"
                                    class="text-[11px] font-semibold tracking-wide text-slate-400 hover:text-red-600">
                                Clear
                            </button>
                        </div>
                        <div class="max-h-64 overflow-y-auto" id="header-notifications-list">
                            <div class="px-3.5 py-3 text-xs text-slate-500 dark:text-slate-300">
                                Loading…
                            </div>
                        </div>
                        @if(auth()->user()->isAdmin() || auth()->user()->isFrontDesk() || auth()->user()->isItStaff() || auth()->user()->role === \App\Models\User::ROLE_EMPLOYEE)
                        <div class="border-t border-slate-200/70 dark:border-slate-700/70 px-3.5 py-2">
                            <button type="button" id="see-all-notifications-btn"
                               class="flex w-full items-center justify-center gap-1.5 rounded-lg py-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-slate-700/60 transition-colors">
                                See all notifications
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                        @else
                        <div class="border-t border-slate-200/70 dark:border-slate-700/70 px-3.5 py-2 hidden">
                            {{-- See all notifications button removed --}}
                        </div>
                        @endif
                    </div>
                </div>
                @if(auth()->user()->isAdmin())
                {{-- Password reset requests badge (admin only) --}}
                <div class="relative">
                    <button type="button" id="pw-reset-btn"
                            class="relative inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50/80 text-slate-500 shadow-sm transition-all hover:bg-slate-100 hover:text-slate-700 hover:shadow-md dark:border-slate-600 dark:bg-slate-700/60 dark:text-slate-300 dark:hover:bg-slate-700"
                            aria-label="Password reset requests" aria-expanded="false">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        <span id="pw-reset-badge" class="absolute -top-0.5 -right-0.5 hidden min-h-[1.15rem] min-w-[1.15rem] items-center justify-center rounded-full bg-orange-500 px-1 text-[10px] font-semibold text-white shadow-sm"></span>
                    </button>
                    <div id="pw-reset-panel"
                         class="absolute right-0 z-40 mt-2 w-80 origin-top-right rounded-2xl border border-slate-200/80 bg-white/95 py-2 text-sm shadow-xl ring-1 ring-black/5 backdrop-blur-md opacity-0 translate-y-1 scale-95 pointer-events-none transition ease-out duration-150 dark:border-slate-600/80 dark:bg-slate-800/95"
                         data-open="0">
                        <div class="px-3.5 pb-1.5 flex items-center text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            <span>Password Reset Requests</span>
                        </div>
                        <div class="max-h-72 overflow-y-auto" id="pw-reset-list">
                            <div class="px-3.5 py-3 text-xs text-slate-500 dark:text-slate-300">Loading…</div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="relative">
                    <button type="button" id="header-profile-button" class="flex items-center gap-3 rounded-full border border-slate-200 bg-slate-50/80 px-2.5 py-1.5 text-left shadow-sm transition-all hover:bg-slate-100 hover:shadow-md dark:border-slate-600 dark:bg-slate-700/60 dark:hover:bg-slate-700" aria-haspopup="true" aria-expanded="false">
                        @if(auth()->user()->avatar_url)
                            <img id="header-avatar" src="{{ auth()->user()->avatar_url }}?v={{ auth()->user()->updated_at?->timestamp ?? time() }}" alt="" class="h-9 w-9 rounded-full object-cover shrink-0 border border-slate-200 dark:border-slate-600">
                        @else
                            <div id="header-avatar-placeholder" class="h-9 w-9 rounded-full bg-slate-600 flex items-center justify-center text-white text-sm font-semibold shrink-0">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="hidden sm:block text-left leading-tight">
                            <p id="header-profile-name" class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ auth()->user()->getRoleLabel() }}</p>
                        </div>
                        <svg class="ml-1 h-4 w-4 text-slate-400 transition-transform duration-150 sm:block hidden" id="header-profile-chevron" fill="none" viewBox="0 0 20 20" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 8l4 4 4-4" />
                        </svg>
                    </button>
                    <div id="header-profile-menu" class="absolute right-0 z-40 mt-2 w-56 origin-top-right rounded-2xl border border-slate-200/80 bg-white/95 py-1.5 text-sm shadow-xl ring-1 ring-black/5 backdrop-blur-md focus:outline-none dark:border-slate-600/80 dark:bg-slate-800/95 opacity-0 translate-y-1 scale-95 pointer-events-none transition ease-out duration-150" data-open="0">
                        <div class="px-3.5 py-2.5 flex items-center gap-2.5 border-b border-slate-100/90 dark:border-slate-700/80">
                            @if(auth()->user()->avatar_url)
                                <img id="header-menu-avatar" src="{{ auth()->user()->avatar_url }}?v={{ auth()->user()->updated_at?->timestamp ?? time() }}" alt="" class="h-9 w-9 rounded-full object-cover border border-slate-200/80 dark:border-slate-600/80">
                            @else
                                <div id="header-menu-avatar-placeholder" class="h-9 w-9 rounded-full bg-slate-600 flex items-center justify-center text-white text-sm font-semibold">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900 dark:text-slate-50">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ auth()->user()->getRoleLabel() }}</p>
                            </div>
                        </div>
                        <div class="py-1">
                            <button type="button" class="js-open-profile-modal flex w-full items-center gap-2 px-3.5 py-2 text-left text-slate-700 hover:bg-slate-50/90 dark:text-slate-100 dark:hover:bg-slate-700/80" data-tab="profile">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </span>
                                <span>My profile</span>
                            </button>
                            <button type="button" id="js-open-admin-settings-modal" class="flex w-full items-center gap-2 px-3.5 py-2 text-left text-slate-700 hover:bg-slate-50/90 dark:text-slate-100 dark:hover:bg-slate-700/80">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10M7 17h10"/></svg>
                                </span>
                                <span>Settings</span>
                            </button>
                        </div>
                        <div class="my-1 border-t border-slate-100 dark:border-slate-700/80"></div>
                        <button type="button" class="js-logout-button flex w-full items-center gap-2 px-3.5 py-2 text-left text-red-600 hover:bg-red-50/80 dark:text-red-400 dark:hover:bg-red-900/30">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-red-50 text-red-600 dark:bg-red-900/40 dark:text-red-300">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </span>
                            <span>Log out</span>
                        </button>
                    </div>
                </div>
                @else
                <a href="{{ route('login') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">Log in</a>
                @endauth
            </div>
        </header>

        {{-- Page content (replaced on slide navigation) --}}
        <main id="app-main-content" class="flex-1 p-4 sm:p-6 lg:p-8">
            @if(session('success'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm dark:border-red-800 dark:bg-red-900/30 dark:text-red-200" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    </div>

    @stack('modals')

    {{-- Global toast notifications --}}
    <div id="app-toast-root" class="app-toast-root"></div>

    @auth
    @if(auth()->user()->role !== \App\Models\User::ROLE_IT_STAFF)
    {{-- Help FAB: bottom-right corner, admin/front-desk/employee only --}}
    <div id="help-fab-root"></div>
    <script>
    (function(){
        var STORE   = @json(route('help.store'));
        var MSGS    = @json(route('help.messages'));
        var UNREAD  = @json(route('help.unread-count'));
        var MARK    = @json(route('help.mark-read'));
        var MY_ID   = @json(auth()->id());
        var IS_ADMIN = @json(auth()->user()->isAdmin());

        /* ── Build DOM ── */
        var style = document.createElement('style');
        style.textContent = [
            '#hfab{position:fixed;bottom:1rem;right:1rem;z-index:2147483640;display:flex;flex-direction:column;align-items:flex-end;gap:.5rem}',
            '#hfab-btn{width:3.25rem;height:3.25rem;border-radius:50%;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;background:#1e293b;box-shadow:0 4px 16px rgba(0,0,0,.35);position:relative;flex-shrink:0}',
            '#hfab-btn svg{width:1.4rem;height:1.4rem;color:#fff;pointer-events:none}',
            '#hfab-badge{position:absolute;top:-3px;right:-3px;min-width:1.1rem;height:1.1rem;background:#22c55e;border-radius:9999px;font-size:9px;font-weight:700;color:#fff;display:flex;align-items:center;justify-content:center;padding:0 3px}',
            '#hfab-badge.hidden{display:none}',
            '#hfab-popup{width:22rem;background:#fff;border-radius:1rem;border:1px solid rgba(0,0,0,.08);box-shadow:0 16px 48px rgba(0,0,0,.18);overflow:hidden;display:none;flex-direction:column;max-height:480px}',
            '#hfab-popup.open{display:flex}',
            '#hfab-hdr{background:#0f172a;padding:.75rem 1rem;display:flex;align-items:center;justify-content:space-between;flex-shrink:0}',
            '#hfab-msgs{flex:1;overflow-y:auto;padding:.75rem 1rem;display:flex;flex-direction:column;gap:.5rem;background:#f8fafc;min-height:100px}',
            '#hfab-reply-bar{border-top:1px solid #e2e8f0;padding:.5rem .75rem;display:flex;gap:.5rem;align-items:flex-end;background:#fff;flex-shrink:0}',
            '#hfab-send-wrap{padding:.85rem 1rem 1rem}',
            '#hfab-sov{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;padding:20px;text-align:center;background:#fff;border-radius:1rem;opacity:0;transition:opacity .25s;pointer-events:none;z-index:10}',
            'html.dark #hfab-popup{background:#0f172a!important;border-color:rgba(255,255,255,.12)!important;box-shadow:0 16px 48px rgba(0,0,0,.5)!important}',
            'html.dark #hfab-msgs{background:#0f172a!important}',
            'html.dark #hfab-reply-bar{border-top-color:#334155!important;background:#0f172a!important}',
            'html.dark #hfab-rbody{background:#1e293b!important;color:#f8fafc!important;border-color:#475569!important}',
            'html.dark #hfab-send-wrap{background:#0f172a!important}',
            'html.dark #hfab-body{background:#1e293b!important;color:#f8fafc!important;border-color:#475569!important}',
            'html.dark #hfab-body::placeholder,html.dark #hfab-rbody::placeholder{color:#94a3b8!important;opacity:1}',
            'html.dark #hfab-sov{background:#0f172a!important}',
            'html.dark #hfab-sov p{color:#e2e8f0!important}',
            'html.dark #hfab-sov p:last-child{color:#94a3b8!important}',
        ].join('');
        document.head.appendChild(style);

        var wrap = document.createElement('div');
        wrap.id = 'hfab';
        wrap.innerHTML = [
            '<div id="hfab-popup" role="dialog" aria-label="Message IT Staff" style="position:relative">',
              '<div id="hfab-hdr">',
                '<div>',
                  '<div style="font-size:.7rem;color:#fff" id="hfab-hsub">Need help?</div>',
                  '<div style="font-size:.875rem;font-weight:600;color:#fff">Message IT</div>',
                '</div>',
                '<button id="hfab-close" style="background:none;border:none;cursor:pointer;color:rgba(255,255,255,.8);display:flex;align-items:center;justify-content:center;width:2rem;height:2rem;border-radius:.5rem" aria-label="Close">',
                  '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:1.1rem;height:1.1rem"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
                '</button>',
              '</div>',
              '<div id="hfab-convo" style="display:none;flex-direction:column;flex:1;min-height:0;overflow:hidden">',
                '<div id="hfab-msgs"></div>',
                '<div id="hfab-reply-bar">',
                  '<textarea id="hfab-rbody" rows="1" placeholder="Reply..." style="flex:1;border-radius:.75rem;border:1px solid #cbd5e1;background:#f8fafc;color:#0f172a;padding:.5rem .75rem;font-size:.875rem;resize:none;outline:none;max-height:80px;font-family:inherit"></textarea>',
                  '<button id="hfab-rsend" style="cursor:pointer;background:#24b4fb;color:#fff;border:2px solid #24b4fb;border-bottom:4px solid #0071e2;border-radius:.75rem;padding:.4rem .75rem;font-size:.875rem;font-weight:600;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .15s">',
                    '<svg fill="none" stroke="#fff" viewBox="0 0 24 24" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>',
                  '</button>',
                '</div>',
              '</div>',
              '<div id="hfab-send-wrap">',
                '<textarea id="hfab-body" rows="3" placeholder="Describe your issue..." style="width:100%;border-radius:.75rem;border:1px solid #cbd5e1;background:#fff;color:#0f172a;padding:.625rem .875rem;font-size:.875rem;resize:vertical;min-height:90px;outline:none;box-sizing:border-box;font-family:inherit"></textarea>',
                '<div style="display:flex;justify-content:flex-end;margin-top:.5rem">',
                  '<button id="hfab-submit" style="cursor:pointer;background:#24b4fb;color:#fff;border:2px solid #24b4fb;border-bottom:4px solid #0071e2;border-radius:.75rem;padding:.5rem 1.25rem;font-size:.875rem;font-weight:600;display:inline-flex;align-items:center;gap:.375rem;transition:all .15s">Send</button>',
                '</div>',
              '</div>',
              '<div id="hfab-sov">',
                '<div style="width:44px;height:44px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center">',
                  '<svg fill="none" stroke="#16a34a" viewBox="0 0 24 24" style="width:22px;height:22px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>',
                '</div>',
                '<p style="font-size:.875rem;font-weight:700;color:#1e293b;margin:0">Sent!</p>',
                '<p style="font-size:.75rem;color:#64748b;margin:0">IT staff will get back to you.</p>',
              '</div>',
            '</div>',
            '<button id="hfab-btn" aria-label="Message IT Staff">',
              '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z"/></svg>',
              '<span id="hfab-badge" class="hidden"></span>',
            '</button>',
        ].join('');
        document.body.appendChild(wrap);

        /* ── Refs ── */
        var popup  = document.getElementById('hfab-popup');
        var btn    = document.getElementById('hfab-btn');
        var close  = document.getElementById('hfab-close');
        var badge  = document.getElementById('hfab-badge');
        var hsub   = document.getElementById('hfab-hsub');
        var convo  = document.getElementById('hfab-convo');
        var msgs   = document.getElementById('hfab-msgs');
        var sov    = document.getElementById('hfab-sov');
        var sendW  = document.getElementById('hfab-send-wrap');
        var body   = document.getElementById('hfab-body');
        var submit = document.getElementById('hfab-submit');
        var rbody  = document.getElementById('hfab-rbody');
        var rsend  = document.getElementById('hfab-rsend');

        /* ── Badge ── */
        function setBadge(n){ n>0?(badge.textContent=n,badge.classList.remove('hidden')):badge.classList.add('hidden'); }
        function poll(){ fetch(UNREAD,{credentials:'same-origin',headers:{'Accept':'application/json'}}).then(function(r){return r.ok?r.json():null}).then(function(d){if(d)setBadge(d.count)}).catch(function(){}); }
        function markRead(){ if(!window.csrfToken)return; fetch(MARK,{method:'POST',credentials:'same-origin',headers:{'X-CSRF-TOKEN':window.csrfToken,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}}).then(function(){setBadge(0)}).catch(function(){}); }
        poll(); setInterval(poll,30000);

        /* ── Real-time: new reply from IT ── */
        if(window.Echo){ try{ window.Echo.private('user.'+MY_ID).listen('.HelpMessageSent',function(d){
            if(String(d.sender_id)===String(MY_ID))return;
            if(popup.classList.contains('open')){ loadMsgs(true); markRead(); }
            else { poll(); }
        }); }catch(e){} }
        // Hook into raw Pusher for private channel (Reverb setup — no window.Echo)
        (function tryBindUserChannel(){
            if(window.__reverbPusher){
                try{
                    var ch=window.__reverbPusher.subscribe('private-user.'+MY_ID);
                    ch.bind('.HelpMessageSent',function(d){
                        if(String(d.sender_id)===String(MY_ID))return;
                        if(popup.classList.contains('open')){ loadMsgs(true); markRead(); }
                        else { poll(); }
                    });
                }catch(e){}
            } else {
                setTimeout(tryBindUserChannel, 200);
            }
        })();
        /* ── Helpers ── */
        function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
        function showSendForm(){ convo.style.display='none'; sendW.style.display='block'; hsub.textContent='Need help?'; }
        function showConvo(list){
            sendW.style.display='none'; convo.style.display='flex'; hsub.textContent='Your conversation';
            var dm=document.documentElement.classList.contains('dark');
            var h=''; list.forEach(function(m){
                var mineBubble='background:'+(dm?'#475569':'#374151')+';color:#fff';
                var otherBubble=dm
                    ? 'background:#1e293b;border:1px solid #334155;color:#e2e8f0;box-shadow:none'
                    : 'background:#fff;border:1px solid #e2e8f0;color:#1e293b;box-shadow:0 1px 3px rgba(0,0,0,.06)';
                h+='<div style="display:flex;justify-content:'+(m.is_mine?'flex-end':'flex-start')+'">'
                 +'<div style="max-width:85%">'
                 +'<div style="font-size:10px;color:#94a3b8;margin-bottom:2px;text-align:'+(m.is_mine?'right':'left')+'">'+esc(m.is_mine?'You':m.sender)+' &middot; '+esc(m.created_at)+'</div>'
                 +'<div style="border-radius:1rem;padding:.4rem .75rem;font-size:.875rem;white-space:pre-wrap;'+(m.is_mine?mineBubble:otherBubble)+'">'+esc(m.body)+'</div>'
                 +'</div></div>';
            });
            msgs.innerHTML=h; msgs.scrollTop=msgs.scrollHeight;
        }

        /* ── Load messages & decide view ── */
        function loadMsgs(hadUnread){
            // If no unread, always show send form
            if(!hadUnread){ showSendForm(); return; }
            // Has unread — fetch to get the latest reply
            fetch(MSGS,{credentials:'same-origin',headers:{'Accept':'application/json'}})
            .then(function(r){return r.ok?r.json():null})
            .then(function(d){
                if(!d){ showSendForm(); return; }
                var list=d.messages||[];
                if(!list.length){ showSendForm(); return; }
                // Find last message not from me
                var lastReply=null;
                for(var i=list.length-1;i>=0;i--){ if(!list[i].is_mine){lastReply=list[i];break;} }
                if(lastReply){
                    showReplyNotice(lastReply, list);
                } else {
                    if(IS_ADMIN){ showSendForm(); }
                    else { showConvo(list); }
                }
            }).catch(function(){ showSendForm(); });
        }

        /* ── Reply notice: "IT replied — Close or Reply" ── */
        function showReplyNotice(lastMsg, allMsgs){
            sendW.style.display='none'; convo.style.display='none';
            var existing=document.getElementById('hfab-notice');
            if(existing)existing.remove();
            var notice=document.createElement('div');
            notice.id='hfab-notice';
            notice.style.cssText='padding:1rem;display:flex;flex-direction:column;gap:.75rem';
            var dm=document.documentElement.classList.contains('dark');
            var boxOuter=dm
                ? 'background:rgba(22,101,52,.35);border:1px solid rgba(74,222,128,.35);border-radius:.75rem;padding:.75rem'
                : 'background:#f0fdf4;border:1px solid #bbf7d0;border-radius:.75rem;padding:.75rem';
            var titleC=dm?'#86efac':'#16a34a';
            var bodyC=dm?'#e2e8f0':'#1e293b';
            var closeBg=dm?'#334155':'#f1f5f9';
            var closeFg=dm?'#e2e8f0':'#475569';
            var closeBr=dm?'#475569':'#cbd5e1';
            notice.innerHTML=
                '<div style="'+boxOuter+'">'
                +'<div style="font-size:.7rem;font-weight:600;color:'+titleC+';text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem">Reply from IT Staff</div>'
                +'<div style="font-size:.875rem;color:'+bodyC+';white-space:pre-wrap">'+esc(lastMsg.body)+'</div>'
                +'<div style="font-size:.7rem;color:#94a3b8;margin-top:.25rem">'+esc(lastMsg.created_at)+'</div>'
                +'</div>'
                +'<div style="display:flex;gap:.5rem">'
                +'<button id="hfab-notice-reply" style="flex:1;cursor:pointer;background:#374151;color:#fff;border:none;border-bottom:4px solid #4ade80;border-radius:.75rem;padding:.5rem;font-size:.875rem;font-weight:600;transition:all .15s">Reply</button>'
                +'<button id="hfab-notice-close" style="flex:1;cursor:pointer;background:'+closeBg+';color:'+closeFg+';border:none;border-bottom:4px solid '+closeBr+';border-radius:.75rem;padding:.5rem;font-size:.875rem;font-weight:600;transition:all .15s">Close</button>'
                +'</div>';
            popup.insertBefore(notice, document.getElementById('hfab-sov'));
            hsub.textContent='New reply';
            document.getElementById('hfab-notice-reply').addEventListener('click',function(){
                notice.remove();
                if(IS_ADMIN){
                    // Admin replies via send form
                    showSendForm();
                } else {
                    showConvo(allMsgs); setTimeout(function(){rbody.focus();},100);
                }
            });
            document.getElementById('hfab-notice-close').addEventListener('click',function(){
                notice.remove(); closePopup();
            });
        }

        /* ── Open / Close ── */
        function openPopup(){
            var hadUnread = !badge.classList.contains('hidden') && parseInt(badge.textContent||'0',10) > 0;
            popup.classList.add('open');
            setBadge(0);
            markRead();
            loadMsgs(hadUnread);
        }
        function closePopup(){
            popup.classList.remove('open');
            sov.style.opacity='0'; sov.style.pointerEvents='none';
            var n=document.getElementById('hfab-notice'); if(n)n.remove();
            showSendForm();
        }
        btn.addEventListener('click', function(e){ e.stopPropagation(); popup.classList.contains('open')?closePopup():openPopup(); });
        close.addEventListener('click', function(e){ e.stopPropagation(); closePopup(); });
        document.addEventListener('keydown', function(e){ if(e.key==='Escape')closePopup(); });
        document.addEventListener('click', function(e){
            if (!popup.classList.contains('open')) return;
            if (wrap.contains(e.target)) return;
            closePopup();
        });

        /* ── Send first message ── */
        submit.addEventListener('click', function(){
            var msg=body.value.trim(); if(!msg)return;
            body.value=''; sov.style.opacity='1'; sov.style.pointerEvents='auto';
            var fd=new FormData(); fd.append('body',msg); fd.append('_token',window.csrfToken||'');
            fetch(STORE,{method:'POST',credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':window.csrfToken||'','Accept':'application/json'},body:fd})
            .then(function(){
                // Always close after send — admin sees send form, others see convo on next open
                setTimeout(function(){ sov.style.opacity='0'; sov.style.pointerEvents='none'; closePopup(); },1400);
            })
            .catch(function(){ sov.style.opacity='0'; sov.style.pointerEvents='none'; body.value=msg; if(window.showAppToast)window.showAppToast('Could not send.'); });
        });

        /* ── Reply (in conversation view) ── */
        function sendReply(){
            var msg=rbody.value.trim(); if(!msg||rsend.disabled)return;
            rbody.value=''; rsend.disabled=true;
            var fd=new FormData(); fd.append('body',msg); fd.append('_token',window.csrfToken||'');
            fetch(STORE,{method:'POST',credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':window.csrfToken||'','Accept':'application/json'},body:fd})
            .then(function(){ closePopup(); })
            .catch(function(){ rbody.value=msg; if(window.showAppToast)window.showAppToast('Could not send.'); })
            .finally(function(){ rsend.disabled=false; });
        }
        rsend.addEventListener('click', sendReply);
        rbody.addEventListener('keydown', function(e){ if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();sendReply();} });
        rbody.addEventListener('input', function(){ this.style.height='auto'; this.style.height=Math.min(this.scrollHeight,80)+'px'; });
    })();
    </script>
    @endif
    @endauth

    @if(auth()->check() && auth()->user()->isAdmin())
    @php
        $announcementSelectableUsers = \App\Models\User::query()
            ->where('role', '!=', \App\Models\User::ROLE_ADMIN)
            ->orderBy('name')
            ->get(['id', 'name', 'role']);
    @endphp
    {{-- New Staff Announcement Modal --}}
    <div id="new-announcement-modal" class="fixed inset-0 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="z-index:2147483647" aria-modal="true" role="dialog">
        <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white shadow-2xl overflow-hidden">
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">New staff announcement</h2>
                    <p class="mt-0.5 text-xs text-slate-500">It will appear on staff dashboards until they mark it done.</p>
                </div>
                <button type="button" id="announcement-modal-close"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 text-lg leading-none"
                        aria-label="Close">&times;</button>
            </div>
            <form id="new-announcement-form" action="{{ route('admin.staff-announcements.store') }}" method="post">
                @csrf
                <div class="max-h-[80vh] overflow-y-auto p-5 space-y-4">
                    <div id="announcement-modal-error" class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>
                    <div>
                        <label for="ann_title" class="block text-xs font-semibold text-slate-600 mb-1">Title</label>
                        <input type="text" name="title" id="ann_title" required
                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                               placeholder="Announcement title">
                    </div>
                    <div>
                        <label for="ann_body" class="block text-xs font-semibold text-slate-600 mb-1">Message</label>
                        <textarea name="body" id="ann_body" rows="4" required
                                  class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 resize-y"
                                  placeholder="Describe what you need them to do or finish."></textarea>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="ann_audience" class="block text-xs font-semibold text-slate-600 mb-1">Who should see this?</label>
                            <select name="audience" id="ann_audience" required
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                <option value="all">All staff</option>
                                <option value="{{ \App\Models\StaffAnnouncement::AUDIENCE_SELECTED_USERS }}">Selected users</option>
                                <option value="{{ \App\Models\User::ROLE_EMPLOYEE }}">Employees only</option>
                                <option value="{{ \App\Models\User::ROLE_FRONT_DESK }}">Front Desk only</option>
                                <option value="{{ \App\Models\User::ROLE_IT_STAFF }}">IT Staff only</option>
                            </select>
                        </div>
                        <div>
                            <label for="ann_priority" class="block text-xs font-semibold text-slate-600 mb-1">Priority</label>
                            <select name="priority" id="ann_priority" required
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                <option value="low">Low — minor request</option>
                                <option value="normal" selected>Normal — workaround available</option>
                                <option value="major">Major — business impacted</option>
                                <option value="critical">Critical — service down</option>
                            </select>
                        </div>
                    </div>
                    <div id="ann_users_wrap" class="hidden">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Select users</label>
                        <div id="ann_selected_user_ids" class="max-h-44 overflow-y-auto rounded-xl border border-slate-200 bg-white p-2 space-y-1 dark:border-slate-700 dark:bg-slate-900/60">
                            @foreach($announcementSelectableUsers as $u)
                                <label class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-slate-800 transition-colors hover:bg-slate-50 dark:text-slate-100 dark:hover:bg-slate-800/70">
                                    <input type="checkbox" name="selected_user_ids[]" value="{{ $u->id }}" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-500 dark:bg-slate-900 dark:checked:bg-blue-500 dark:checked:border-blue-500">
                                    <span class="text-slate-700 dark:text-slate-100">{{ $u->name }} ({{ \App\Models\User::roleLabel($u->role) }})</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-1 text-[11px] text-slate-500">Tick one or more users.</p>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" id="announcement-modal-cancel"
                                class="cursor-pointer transition-all bg-white text-slate-700 px-5 py-2 rounded-lg border border-slate-300 hover:bg-slate-50 text-sm font-semibold">
                            Cancel
                        </button>
                        <button type="submit" id="announcement-modal-submit"
                                class="cursor-pointer transition-all bg-gray-700 text-white px-5 py-2 rounded-lg border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold inline-flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Send announcement
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if(auth()->check() && auth()->user()->isAdmin())
    {{-- Generic Delete Confirm Modal --}}
    <div id="index-delete-modal" class="fixed inset-0 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="z-index:2147483647" aria-modal="true" role="dialog">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white shadow-2xl p-6">
            <h2 id="index-delete-modal-title" class="text-lg font-semibold text-slate-900">Confirm delete</h2>
            <p id="index-delete-modal-message" class="mt-2 text-sm text-slate-600">This action cannot be undone.</p>
            <div class="mt-5 flex flex-wrap gap-3 justify-end">
                <button type="button" id="index-delete-modal-cancel"
                        class="inline-flex min-h-10 items-center justify-center rounded-[0.9em] border-2 border-[#24b4fb] bg-[#24b4fb] px-4 py-2.5 text-sm font-semibold text-white transition-all duration-200 ease-in-out hover:-translate-y-[1px] hover:bg-[#0071e2] active:translate-y-[1px]">
                    Cancel
                </button>
                <button type="button" id="index-delete-modal-confirm"
                        class="inline-flex min-h-10 items-center gap-2 justify-center rounded-[0.9em] border-2 border-red-500 bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition-all duration-200 ease-in-out hover:-translate-y-[1px] hover:bg-red-700 active:translate-y-[1px]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16"/></svg>
                    Delete
                </button>
            </div>
        </div>
    </div>

    {{-- Audit Trail Modal --}}
    <div id="audit-modal" class="fixed inset-0 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="z-index:2147483647" aria-modal="true" role="dialog">
        <div class="w-full max-w-3xl rounded-2xl border border-slate-200 bg-white shadow-2xl overflow-hidden">
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur">
                <div>
                    <h2 id="audit-modal-title" class="text-base font-semibold text-slate-900">Ticket activity</h2>
                    <p id="audit-modal-subtitle" class="mt-0.5 text-xs text-slate-500"></p>
                </div>
                <div class="flex items-center gap-2">
                    <a id="audit-modal-download" href="#" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download
                    </a>
                    <a id="audit-modal-print" href="#" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print
                    </a>
                    <button type="button" id="audit-modal-close"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 text-lg leading-none"
                            aria-label="Close">&times;</button>
                </div>
            </div>
            <div class="audit-modal-body-scroll max-h-[75vh] overflow-y-auto">
                <div id="audit-modal-loading" class="flex items-center justify-center py-16 text-sm text-slate-500">
                    <svg class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                    Loading…
                </div>
                <div id="audit-modal-content" class="hidden">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Ticket</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Action</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Details</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 whitespace-nowrap">Date & time</th>
                            </tr>
                        </thead>
                        <tbody id="audit-modal-tbody" class="divide-y divide-slate-100"></tbody>
                    </table>
                    <div id="audit-modal-empty" class="hidden px-4 py-10 text-center text-sm text-slate-500">No ticket actions recorded yet.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add User Modal --}}
    <div id="add-user-modal" class="fixed inset-0 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="z-index:2147483647" aria-modal="true" role="dialog">
        <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white shadow-2xl overflow-hidden">
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Add user</h2>
                    <p class="mt-0.5 text-xs text-slate-500">The user will set their password on first login.</p>
                </div>
                <button type="button" id="add-user-close" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 text-lg leading-none" aria-label="Close">&times;</button>
            </div>
            <form id="add-user-form" action="{{ route('users.store') }}" method="post">
                @csrf
                <div class="max-h-[80vh] overflow-y-auto p-5 space-y-4">
                    <div id="add-user-error" class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                        <input type="text" name="name" id="add-user-name" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input type="email" name="email" id="add-user-email" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                        <select name="role" id="add-user-role" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            <option value="{{ \App\Models\User::ROLE_EMPLOYEE }}">Employee</option>
                            <option value="{{ \App\Models\User::ROLE_FRONT_DESK }}">Front Desk</option>
                            <option value="{{ \App\Models\User::ROLE_IT_STAFF }}">IT Staff</option>
                            <option value="{{ \App\Models\User::ROLE_ADMIN }}">Admin</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" id="add-user-cancel" class="cursor-pointer bg-white text-slate-700 px-5 py-2 rounded-lg border border-slate-300 hover:bg-slate-50 text-sm font-semibold">Cancel</button>
                        <button type="submit" id="add-user-submit" class="cursor-pointer transition-all bg-gray-700 text-white px-5 py-2 rounded-lg border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold inline-flex items-center gap-2">Create user</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit User Modal --}}
    <div id="edit-user-modal" class="fixed inset-0 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="z-index:2147483647" aria-modal="true" role="dialog">
        <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white shadow-2xl overflow-hidden">
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Edit user</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Update email, role or password.</p>
                </div>
                <button type="button" id="edit-user-close" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 text-lg leading-none" aria-label="Close">&times;</button>
            </div>
            <form id="edit-user-form" method="post">
                @csrf
                @method('PUT')
                <div class="max-h-[80vh] overflow-y-auto p-5 space-y-4">
                    <div id="edit-user-error" class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input type="email" name="email" id="edit-user-email" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                        <select name="role" id="edit-user-role" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            <option value="{{ \App\Models\User::ROLE_EMPLOYEE }}">Employee</option>
                            <option value="{{ \App\Models\User::ROLE_FRONT_DESK }}">Front Desk</option>
                            <option value="{{ \App\Models\User::ROLE_IT_STAFF }}">IT Staff</option>
                            <option value="{{ \App\Models\User::ROLE_ADMIN }}">Admin</option>
                        </select>
                    </div>
                    <div id="edit-user-password-section" class="hidden pt-2 border-t border-slate-200 space-y-3">
                        <p class="text-sm font-semibold text-slate-900">Password (admin only)</p>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="clear_password" id="edit-user-clear-password" value="1" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                            <span class="text-sm text-slate-700">Clear password (user must set it on next login)</span>
                        </label>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">New password</label>
                            <input type="password" name="new_password" id="edit-user-new-password" minlength="8" autocomplete="new-password" placeholder="Leave blank to keep current" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Confirm new password</label>
                            <input type="password" name="new_password_confirmation" id="edit-user-new-password-confirm" minlength="8" autocomplete="new-password" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" id="edit-user-cancel" class="cursor-pointer bg-white text-slate-700 px-5 py-2 rounded-lg border border-slate-300 hover:bg-slate-50 text-sm font-semibold">Cancel</button>
                        <button type="submit" id="edit-user-submit" class="cursor-pointer transition-all bg-gray-700 text-white px-5 py-2 rounded-lg border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold inline-flex items-center gap-2">Save changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
    @auth
    <div id="profile-settings-modal" class="fixed inset-0 z-[9998] hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm" aria-modal="true" role="dialog" aria-labelledby="profile-settings-title">
        <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white text-slate-900 shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur">
                <div class="min-w-0">
                    <h2 id="profile-settings-title" class="truncate text-base font-semibold text-slate-900">Profile</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Profile settings</p>
                </div>
                <button type="button" class="js-close-profile-modal inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50" aria-label="Close">
                    &times;
                </button>
            </div>

            <div class="border-b border-slate-100 bg-slate-50/50 px-5 py-2">
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="profile-tab-btn inline-flex items-center rounded-xl border px-3 py-1.5 text-xs font-semibold" data-tab="profile">Profile</button>
                </div>
            </div>

            <div class="max-h-[80vh] overflow-y-auto p-4 sm:p-6">
                {{-- Profile tab --}}
                <div class="profile-tab-panel" data-tab="profile">
                    <form action="{{ route('profile.update') }}" method="post" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="profile_picture_data" id="modal_profile_picture_data" value="">

                        <div class="flex flex-col items-center gap-4 text-center">
                            <div class="shrink-0">
                                <button type="button" id="modal-avatar-trigger" title="Click to change profile picture"
                                        class="relative h-32 w-32 sm:h-36 sm:w-36 rounded-full overflow-hidden border-2 border-slate-200 hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all group">
                                    @if(auth()->user()->avatar_url)
                                        <img id="modal-avatar-preview" src="{{ auth()->user()->avatar_url }}?v={{ auth()->user()->updated_at?->timestamp ?? time() }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        <div id="modal-avatar-placeholder" class="h-full w-full bg-slate-600 flex items-center justify-center text-white text-3xl font-semibold">
                                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                </button>
                                <input type="file" name="profile_picture" id="modal_profile_picture" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" class="sr-only">
                            </div>
                            <div class="w-full">
                                <p class="text-sm font-medium text-slate-700">Profile picture</p>
                                <p class="text-xs text-slate-500 mt-0.5">Click the avatar to upload a new photo</p>
                                @if(auth()->user()->profile_picture)
                                    <label class="mt-2 inline-flex items-center justify-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="remove_profile_picture" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        <span class="text-sm text-slate-600">Remove current picture</span>
                                    </label>
                                @endif
                            </div>
                        </div>

                        <div>
                            <label for="modal_profile_name" class="block text-sm font-medium text-slate-700">Name</label>
                            <input type="text" name="name" id="modal_profile_name" value="{{ old('name', auth()->user()->name) }}" required
                                   style="margin-top:0.25rem;display:block;width:100%;border-radius:0.75rem;border:1px solid #cbd5e1;background:#ffffff !important;color:#0f172a !important;padding:0.625rem 1rem;box-shadow:0 1px 2px rgba(0,0,0,0.05);font-size:0.875rem;">
                        </div>

                        <div class="flex justify-end gap-2">
                            <button type="button" class="js-close-profile-modal cursor-pointer transition-all bg-white text-slate-700 px-5 py-2 rounded-lg border-slate-300 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-slate-200 shadow-slate-200 active:shadow-none text-sm font-semibold inline-flex items-center justify-center">Close</button>
                            <button type="submit" class="cursor-pointer transition-all bg-gray-700 text-white px-5 py-2 rounded-lg border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold inline-flex items-center justify-center">Save</button>
                        </div>
                    </form>
                </div>

                {{-- Appearance + Security live in the Settings modal now --}}
            </div>
        </div>
    </div>

    {{-- Crop modal for header profile picture adjust-before-save --}}
    <div id="modal-profile-crop-modal" class="fixed inset-0 z-[10000] hidden" aria-modal="true" role="dialog" aria-labelledby="modal-profile-crop-title">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" id="modal-profile-crop-backdrop"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900">
                <div class="p-4 border-b border-slate-200 dark:border-slate-700">
                    <h2 id="modal-profile-crop-title" class="text-lg font-semibold text-slate-900 dark:text-slate-100">Adjust profile picture</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Drag to reposition, scroll to zoom. Square crop will be used.</p>
                </div>
                <div class="p-4">
                    <div class="max-h-[60vh] overflow-hidden rounded-lg bg-slate-100 dark:bg-slate-800">
                        <img id="modal-profile-crop-image" src="" alt="Crop preview" class="block max-w-full max-h-[50vh]">
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 justify-end p-4 border-t border-slate-200 dark:border-slate-700">
                    <button type="button" id="modal-profile-crop-cancel" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</button>
                    <button type="button" id="modal-profile-crop-use-original" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Use original</button>
                    <button type="button" id="modal-profile-crop-apply" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700">Use cropped</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Admin Settings Modal --}}
    <div id="admin-settings-modal" class="fixed inset-0 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="z-index:9997" aria-modal="true" role="dialog" aria-labelledby="admin-settings-title">
        <div class="w-full max-w-4xl rounded-2xl border border-slate-200 bg-white shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            {{-- Header --}}
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-white/95 px-5 py-4 shrink-0">
                <div class="min-w-0">
                    <h2 id="admin-settings-title" class="text-base font-semibold text-slate-900">Settings</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Appearance and account security</p>
                </div>
                <button type="button" id="admin-settings-close"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50"
                        aria-label="Close">&times;</button>
            </div>
            {{-- Tabs --}}
            <div class="border-b border-slate-100 bg-slate-50/50 px-5 py-2 shrink-0">
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="adm-set-tab inline-flex items-center rounded-xl border px-3 py-1.5 text-xs font-semibold" data-tab="appearance">Appearance</button>
                    <button type="button" class="adm-set-tab inline-flex items-center rounded-xl border px-3 py-1.5 text-xs font-semibold" data-tab="security">Security</button>
                </div>
            </div>
            {{-- Body --}}
            <div class="flex-1 overflow-y-auto p-5">
                <div class="adm-set-panel" data-tab="appearance">
                    @include('partials.settings-appearance', ['closeButtonClass' => 'js-admin-settings-inner-close'])
                </div>
                <div class="adm-set-panel hidden" data-tab="security">
                    @include('partials.settings-security', ['closeButtonClass' => 'js-admin-settings-inner-close'])
                </div>
            </div>
        </div>
    </div>

    @if(session('first_login_tour') && auth()->user() && auth()->user()->isAdmin())
    <div id="first-login-password-modal" class="fixed inset-0 z-[9998] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="first-login-modal-title" style="animation: first-login-fadeIn 0.2s ease-out;">
        <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white shadow-2xl p-6 dark:bg-slate-800 dark:border-slate-600" style="animation: first-login-scaleIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);">
            <h2 id="first-login-modal-title" class="text-lg font-semibold text-slate-900 dark:text-slate-100">Set your password</h2>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Please put your new password and press Okay. You will then be directed to your profile.</p>
            <form action="{{ route('profile.password') }}" method="post" id="first-login-password-form" class="mt-4 space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label for="first-login-password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">New password</label>
                    <input type="password" name="password" id="first-login-password" required minlength="8" autocomplete="new-password" placeholder="Min 8 characters" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                </div>
                <div>
                    <label for="first-login-password-confirm" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Confirm password</label>
                    <input type="password" name="password_confirmation" id="first-login-password-confirm" required minlength="8" autocomplete="new-password" placeholder="Confirm" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                </div>
                @if($errors->has('password') || $errors->has('current_password'))
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $errors->first('password') ?: $errors->first('current_password') }}</p>
                @endif
                <div class="pt-1 flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                        Okay
                    </button>
                </div>
            </form>
        </div>
    </div>
    <style>
        @keyframes first-login-fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes first-login-scaleIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    </style>
    @endif

    @if((auth()->user()->isItStaff() && !auth()->user()->isAdmin()) || auth()->user()->isAdmin())
    {{-- Help Request Modal (IT Staff only) — messenger style --}}
    <div id="it-help-request-modal" class="fixed inset-0 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="z-index:2147483647" aria-modal="true" role="dialog" aria-labelledby="it-help-request-title">
        <div class="w-full max-w-lg rounded-2xl border border-blue-900/70 bg-[#0b1020] shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
            {{-- Header --}}
            <div class="flex items-center justify-between gap-3 border-b border-blue-800/70 bg-[#0f172a]/95 px-5 py-4 shrink-0">
                <div class="flex items-center gap-2 min-w-0">
                    <button type="button" id="it-help-back-btn" class="hidden mr-1 inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-300 hover:bg-blue-900/40" aria-label="Back">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <div class="min-w-0">
                        <h2 id="it-help-request-title" class="text-base font-semibold text-slate-100">Help Request</h2>
                        <p id="it-help-subtitle" class="mt-0.5 text-xs text-slate-300">Select a user to view their messages</p>
                    </div>
                </div>
                <button type="button" id="it-help-request-close"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-blue-700 text-slate-200 hover:bg-blue-900/40"
                        aria-label="Close">&times;</button>
            </div>
            {{-- Thread list --}}
            <div id="it-help-threads-view" class="flex-1 overflow-y-auto divide-y divide-blue-900/50 bg-[#0b1020]">
                <div class="flex items-center justify-center py-8 text-sm text-slate-400">No help requests yet.</div>
            </div>
            {{-- Conversation view --}}
            <div id="it-help-convo-view" class="hidden flex-col flex-1 min-h-0" style="min-height:0">
                <div id="it-help-messages" class="flex-1 overflow-y-auto px-4 py-4 space-y-3 bg-[#0b1020]" style="min-height:200px;max-height:380px"></div>
                <div class="border-t border-blue-800/70 bg-[#0f172a] px-4 py-3 shrink-0">
                    <div id="it-help-reply-success" class="hidden mb-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs text-emerald-800">Sent.</div>
                    <div id="it-help-reply-error" class="hidden mb-2 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs text-red-700"></div>
                    <form id="it-help-reply-form" class="flex gap-2 items-end">
                        @csrf
                        <input type="hidden" id="it-help-reply-recipient" name="recipient_id" value="">
                        <textarea id="it-help-reply-body" name="body" rows="2"
                            class="flex-1 block rounded-xl border border-blue-700 bg-[#111a33] px-3 py-2 text-sm text-slate-100 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 resize-none"
                            placeholder="Reply…" maxlength="5000"></textarea>
                        <button type="submit"
                                class="cursor-pointer inline-flex items-center gap-1.5 shrink-0 rounded-[0.9em] border-2 border-[#24b4fb] bg-[#24b4fb] px-4 py-2 text-sm font-semibold text-white transition-all duration-200 ease-in-out hover:bg-[#0071e2] hover:border-[#0071e2] focus:outline-none focus:ring-2 focus:ring-[#24b4fb]/50 focus:ring-offset-1 active:translate-y-[1px] disabled:opacity-60">
                            Send
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
    <script>
    (function() {
        var modal        = document.getElementById('it-help-request-modal');
        var openBtn      = document.getElementById('it-help-request-btn');
        var closeBtn     = document.getElementById('it-help-request-close');
        var backBtn      = document.getElementById('it-help-back-btn');
        var threadsView  = document.getElementById('it-help-threads-view');
        var convoView    = document.getElementById('it-help-convo-view');
        var messagesEl   = document.getElementById('it-help-messages');
        var replyForm    = document.getElementById('it-help-reply-form');
        var replyBody    = document.getElementById('it-help-reply-body');
        var replyRecip   = document.getElementById('it-help-reply-recipient');
        var replySuccess = document.getElementById('it-help-reply-success');
        var replyError   = document.getElementById('it-help-reply-error');
        var titleEl      = document.getElementById('it-help-request-title');
        var subtitleEl   = document.getElementById('it-help-subtitle');
        var badge        = document.getElementById('it-help-badge');
        var threadsUrl   = @json(route('it.help.threads'));
        var threadUrl    = @json(url('it/help-thread'));
        var replyUrl     = @json(route('it.help.reply'));
        var markUrl      = @json(route('it.help.mark-read'));
        var countUrl     = @json(route('it.help.unread-count'));
        var csrf         = window.csrfToken || (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        var currentUserId = null;
        var currentUserName = '';
        var threadsCache = null;
        var threadCache = {};

        function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

        function normalizeUnreadCount(count) {
            var n = parseInt(count, 10);
            return Number.isFinite(n) && n > 0 ? n : 0;
        }

        function updateBadge(count) {
            if (!badge) return;
            var n = normalizeUnreadCount(count);
            if (n > 0) {
                badge.textContent = n > 99 ? '99+' : String(n);
                badge.classList.remove('hidden');
            } else {
                badge.textContent = '';
                badge.classList.add('hidden');
            }
        }

        function pollUnread() {
            if (!badge) return; // Admins don't have the IT help badge/button mounted.
            fetch(countUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                .then(function(r) { return r.ok ? r.json() : null; })
                .then(function(d) { if (d) updateBadge(d.count); })
                .catch(function() {});
        }

        function showThreads() {
            currentUserId = null; currentUserName = '';
            threadsView.classList.remove('hidden');
            convoView.classList.add('hidden'); convoView.classList.remove('flex');
            backBtn.classList.add('hidden');
            titleEl.textContent = 'Help Request';
            subtitleEl.textContent = 'Messages from users';
        }

        function renderThreads(threads) {
            var dark = true;
            if (!threads.length) {
                threadsView.innerHTML = '<div class="flex items-center justify-center py-10 text-sm text-slate-400">No help requests yet.</div>';
                return;
            }
            var html = '';
            threads.forEach(function(t) {
                var initials = t.name.split(' ').map(function(w){return w[0]||'';}).join('').substring(0,2).toUpperCase();
                var hasUnread = t.unread > 0;
                var avatarBg = hasUnread ? (dark ? 'background:#1d4ed8' : 'background:#1e293b') : (dark ? 'background:#334155' : 'background:#64748b');
                var rowClass = dark
                    ? 'it-thread-btn w-full flex items-center gap-3 px-4 py-3.5 hover:bg-blue-900/30 text-left transition-colors border-b border-blue-900/50 last:border-0'
                    : 'it-thread-btn w-full flex items-center gap-3 px-4 py-3.5 hover:bg-slate-50 text-left transition-colors border-b border-slate-100 last:border-0';
                var nameClass = hasUnread
                    ? (dark ? 'font-bold text-slate-100' : 'font-bold text-slate-900')
                    : (dark ? 'font-medium text-slate-200' : 'font-medium text-slate-700');
                var timeClass = dark ? 'text-[10px] text-slate-400 shrink-0' : 'text-[10px] text-slate-400 shrink-0';
                var msgClass = hasUnread
                    ? (dark ? 'text-slate-200 font-medium' : 'text-slate-700 font-medium')
                    : (dark ? 'text-slate-400' : 'text-slate-400');
                html += '<button type="button" class="' + rowClass + '" data-uid="' + t.sender_id + '" data-name="' + escHtml(t.name) + '">' +
                    // Avatar with online-style indicator
                    '<div class="relative shrink-0">' +
                        '<div style="width:2.75rem;height:2.75rem;border-radius:50%;' + avatarBg + ';display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8125rem;font-weight:700">' + escHtml(initials) + '</div>' +
                        (hasUnread ? '<span style="position:absolute;bottom:0;right:0;width:.75rem;height:.75rem;background:#22c55e;border-radius:50%;border:2px solid ' + (dark ? '#0b1020' : '#fff') + '"></span>' : '') +
                    '</div>' +
                    // Content
                    '<div class="flex-1 min-w-0">' +
                        '<div class="flex items-center justify-between gap-2 mb-0.5">' +
                            '<span class="text-sm ' + nameClass + ' truncate">' + escHtml(t.name) + '</span>' +
                            '<span class="' + timeClass + '">' + escHtml(t.last_at) + '</span>' +
                        '</div>' +
                        '<div class="flex items-center justify-between gap-2">' +
                            '<span class="text-xs ' + msgClass + ' truncate">' + escHtml(t.last_message || 'No messages yet') + '</span>' +
                            (hasUnread ? '<span style="min-width:1.25rem;height:1.25rem;background:#ef4444;border-radius:9999px;display:inline-flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;color:#fff;padding:0 4px;flex-shrink:0">' + t.unread + '</span>' : '') +
                        '</div>' +
                    '</div>' +
                '</button>';
            });
            threadsView.innerHTML = html;
            threadsView.querySelectorAll('.it-thread-btn').forEach(function(btn) {
                btn.addEventListener('click', function() { openThread(btn.dataset.uid, btn.dataset.name); });
            });
        }

        function loadThreads(force) {
            // Use cache only if it has data and not forced
            if (threadsCache && threadsCache.length > 0 && !force) {
                renderThreads(threadsCache);
                return;
            }
            // Skeleton loading — looks instant
            var skeletonHtml = '';
            for (var i = 0; i < 3; i++) {
                skeletonHtml += '<div style="display:flex;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid #f1f5f9">' +
                    '<div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:sk-shimmer 1.2s infinite;flex-shrink:0"></div>' +
                    '<div style="flex:1;display:flex;flex-direction:column;gap:6px">' +
                        '<div style="height:10px;background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:sk-shimmer 1.2s infinite;border-radius:4px;width:' + (60 + i * 10) + '%"></div>' +
                        '<div style="height:8px;background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:sk-shimmer 1.2s infinite ' + (i * 0.1) + 's;border-radius:4px;width:' + (40 + i * 8) + '%"></div>' +
                    '</div>' +
                '</div>';
            }
            threadsView.innerHTML = '<style>@keyframes sk-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}</style>' + skeletonHtml;
            fetch(threadsUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                .then(function(r) { return r.ok ? r.json() : Promise.reject(); })
                .then(function(data) {
                    threadsCache = data.threads || [];
                    renderThreads(threadsCache);
                })
                .catch(function() {
                    threadsView.innerHTML = '<div class="flex items-center justify-center py-8 text-sm text-red-400">Could not load.</div>';
                });
        }

        function renderMessages(msgs, userId) {
            var dark = true;
            if (!msgs.length) {
                messagesEl.innerHTML = '<div class="flex items-center justify-center py-6 text-sm text-slate-400">No messages yet.</div>';
                return;
            }
            var html = '';
            msgs.forEach(function(m) {
                var isMine = (String(m.sender_id) !== String(userId));
                var bubbleClass = isMine
                    ? (dark ? 'bg-blue-700 text-white' : 'bg-slate-800 text-white')
                    : (dark ? 'bg-[#111a33] border border-blue-800/70 text-slate-100 shadow-sm' : 'bg-white border border-slate-200 text-slate-800 shadow-sm');
                html += '<div class="flex ' + (isMine ? 'justify-end' : 'justify-start') + '">' +
                    '<div class="max-w-[80%]">' +
                        '<div class="text-[10px] text-slate-400 mb-0.5 ' + (isMine ? 'text-right' : '') + '">' + escHtml(m.sender) + ' · ' + escHtml(m.created_at) + '</div>' +
                        '<div class="rounded-2xl px-3 py-2 text-sm whitespace-pre-wrap leading-snug ' + bubbleClass + '">' + escHtml(m.body) + '</div>' +
                    '</div>' +
                '</div>';
            });
            messagesEl.innerHTML = html;
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }

        function openThread(userId, userName, force) {
            currentUserId = userId; currentUserName = userName;
            replyRecip.value = userId;
            titleEl.textContent = userName;
            subtitleEl.textContent = 'Conversation';
            threadsView.classList.add('hidden');
            convoView.classList.remove('hidden'); convoView.classList.add('flex');
            backBtn.classList.remove('hidden');
            replyBody.focus();

            // Use cache if available and not forced
            if (threadCache[userId] && !force) {
                renderMessages(threadCache[userId], userId);
                return;
            }
            messagesEl.innerHTML = '<div class="flex items-center justify-center py-6 text-sm text-slate-400">Loading…</div>';
            fetch(threadUrl + '/' + userId, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                .then(function(r) { return r.ok ? r.json() : Promise.reject(); })
                .then(function(data) {
                    threadCache[userId] = data.messages || [];
                    renderMessages(threadCache[userId], userId);
                })
                .catch(function() {
                    messagesEl.innerHTML = '<div class="flex items-center justify-center py-6 text-sm text-red-400">Could not load.</div>';
                });
        }

        function openModal() {
            modal.classList.remove('hidden'); modal.classList.add('flex');
            showThreads();
            loadThreads(true); // always fetch fresh on open
            fetch(markUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
                .then(function() { pollUnread(); })
                .catch(function(){});
            updateBadge(0);
        }

        function closeModal() {
            modal.classList.add('hidden'); modal.classList.remove('flex');
            showThreads();
        }

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (backBtn) backBtn.addEventListener('click', function() { showThreads(); loadThreads(); });
        modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });

        // Also open via rail button
        var openBtnRail = document.getElementById('it-help-request-btn-rail');
        if (openBtnRail) openBtnRail.addEventListener('click', openModal);

        // Expose for notification bell click-through
        window._itHelpOpenThread = function(userId, userName) {
            openModal();
            if (userId) openThread(userId, userName);
        };

        var replyBtnOrig = replyForm.querySelector('[type=submit]').innerHTML;
        replyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            replySuccess.classList.add('hidden'); replyError.classList.add('hidden');
            var body = replyBody.value.trim();
            if (!body || !currentUserId) return;
            var btn = replyForm.querySelector('[type=submit]');
            btn.disabled = true;
            btn.innerHTML = '<svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="40" stroke-dashoffset="15" opacity=".3"/><path stroke="currentColor" stroke-linecap="round" stroke-width="3" d="M12 2a10 10 0 0 1 10 10"/></svg>';
            fetch(replyUrl, {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ body: body, recipient_id: currentUserId })
            })
            .then(function(r) { return r.ok ? r.json() : r.json().then(function(d) { throw d; }); })
            .then(function() {
                replyBody.value = '';
                btn.disabled = false;
                btn.innerHTML = replyBtnOrig;
                // Invalidate caches and reload thread live
                delete threadCache[currentUserId];
                threadsCache = null;
                openThread(currentUserId, currentUserName, true);
                replySuccess.classList.remove('hidden');
                setTimeout(function(){ replySuccess.classList.add('hidden'); }, 2000);
            })
            .catch(function(d) {
                btn.disabled = false;
                btn.innerHTML = replyBtnOrig;
                replyError.textContent = (d && d.message) ? d.message : 'Could not send.';
                replyError.classList.remove('hidden');
            });
        });

        pollUnread();
        setInterval(pollUnread, 60000);

        function bindHelpMessageSent(channel) {
            channel.bind('.HelpMessageSent', function(data) {
                // Invalidate caches
                threadsCache = null;
                if (data && data.sender_id) delete threadCache[String(data.sender_id)];

                if (modal.classList.contains('hidden')) {
                    var current = parseInt((badge && badge.textContent) || '0', 10) || 0;
                    updateBadge(current + 1);
                } else if (currentUserId && String(data.sender_id) === String(currentUserId)) {
                    openThread(currentUserId, currentUserName, true);
                } else {
                    loadThreads(true);
                }
            });
        }

        if (window.Echo) {
            try { bindHelpMessageSent(window.Echo.private('it-staff')); } catch(e) {}
        }
        // Use raw Pusher if Echo not available (Reverb setup)
        function tryBindReverb() {
            if (window.__reverbPusher) {
                try {
                    var ch = window.__reverbPusher.subscribe('private-it-staff');
                    bindHelpMessageSent(ch);
                } catch(e) {}
            } else {
                setTimeout(tryBindReverb, 200);
            }
        }
        if (!window.Echo) tryBindReverb();
    })();
    </script>

    {{-- qa-help-form global submit handler removed: quick action FAB now handles its own submit inline --}}

    {{-- Customize Categories & Priorities Modal (IT Staff + Admin) --}}
    <div id="it-categories-modal" class="fixed inset-0 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="z-index:10020" aria-modal="true" role="dialog" aria-labelledby="it-categories-title">
        <div class="w-full max-w-4xl rounded-2xl border border-slate-200 bg-white shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-white/95 px-5 py-4 shrink-0">
                <div class="min-w-0">
                    <h2 id="it-categories-title" class="text-base font-semibold text-slate-900">Customize Form</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Manage ticket categories</p>
                </div>
                <button type="button" id="it-categories-close"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50"
                        aria-label="Close">&times;</button>
            </div>
            <div class="flex-1 overflow-y-auto">
                <div class="grid grid-cols-1 divide-y divide-slate-100 md:grid-cols-1">
                    {{-- Categories column --}}
                    <div class="flex flex-col">
                        <div class="border-b border-slate-100 bg-slate-50/60 px-4 py-3 shrink-0">
                            <p class="text-xs font-semibold text-slate-700 uppercase tracking-wide mb-2">Categories</p>
                            <div id="it-cat-msg" class="hidden mb-2 rounded-lg px-3 py-2 text-xs font-medium"></div>
                            <form id="it-cat-form" class="space-y-2.5">
                                <input type="hidden" id="it-cat-id" value="">
                                <label for="it-cat-name" class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Category name</label>
                                <input type="text" id="it-cat-name" name="name" maxlength="255" required
                                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                    placeholder="Category name">
                                <label for="it-cat-desc" class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Description (optional)</label>
                                <input type="text" id="it-cat-desc" name="description" maxlength="2000"
                                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                    placeholder="Description (optional)">
                                <div class="flex items-center justify-end gap-2 pt-1">
                                    <button type="submit" id="it-cat-save"
                                            class="inline-flex min-w-28 items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60">Save</button>
                                    <button type="button" id="it-cat-cancel-edit"
                                            class="hidden inline-flex min-w-24 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                                </div>
                            </form>
                        </div>
                        <div class="flex-1 overflow-y-auto" id="it-cat-list">
                            <div class="flex items-center justify-center py-8 text-sm text-slate-400">Loading…</div>
                        </div>
                    </div>
                    @if(false)
                        {{-- Priorities column --}}
                        <div class="flex flex-col">
                            <div class="border-b border-slate-100 bg-slate-50/60 px-4 py-3 shrink-0">
                                <p class="text-xs font-semibold text-slate-700 uppercase tracking-wide mb-2">Priority Levels</p>
                                <div id="it-pri-msg" class="hidden mb-2 rounded-lg px-3 py-2 text-xs font-medium"></div>
                                <form id="it-pri-form" class="flex flex-col gap-2">
                                    <input type="hidden" id="it-pri-id" value="">
                                    <input type="text" id="it-pri-label" name="label" maxlength="60" required
                                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                        placeholder="Priority label (e.g. High)">
                                    <div class="flex gap-2 items-center">
                                        <input type="number" id="it-pri-order" name="sort_order" min="0" max="999"
                                            class="w-24 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                            placeholder="Order">
                                        <label class="flex items-center gap-1.5 text-sm text-slate-700 cursor-pointer select-none">
                                            <input type="checkbox" id="it-pri-active" name="active" checked
                                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                            Active
                                        </label>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit" id="it-pri-save"
                                                class="flex-1 inline-flex justify-center items-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60">Save</button>
                                        <button type="button" id="it-pri-cancel-edit"
                                                class="hidden inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                                    </div>
                                </form>
                            </div>
                            <div class="flex-1 overflow-y-auto" id="it-pri-list">
                                <div class="flex items-center justify-center py-8 text-sm text-slate-400">Loading…</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <script>
    (function() {
        var modal    = document.getElementById('it-categories-modal');
        var openBtn  = document.getElementById('it-categories-btn');
        var openBtnRail = document.getElementById('it-categories-btn-rail');
        var closeBtn = document.getElementById('it-categories-close');
        var csrf     = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '';
        if (!modal) return;

        function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
        function escAttr(s) { return String(s).replace(/"/g,'&quot;'); }
        function showMsg(el, text, ok) {
            el.textContent = text;
            el.className = 'mb-2 rounded-lg px-3 py-2 text-xs font-medium ' + (ok ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200');
            el.classList.remove('hidden');
            setTimeout(function() { el.classList.add('hidden'); }, 3000);
        }

        // ── Categories ──────────────────────────────────────────────────────
        var catList     = document.getElementById('it-cat-list');
        var catForm     = document.getElementById('it-cat-form');
        var catId       = document.getElementById('it-cat-id');
        var catName     = document.getElementById('it-cat-name');
        var catDesc     = document.getElementById('it-cat-desc');
        var catSave     = document.getElementById('it-cat-save');
        var catCancel   = document.getElementById('it-cat-cancel-edit');
        var catMsg      = document.getElementById('it-cat-msg');
        var catIndexUrl = @json(route('it.categories.index'));
        var catStoreUrl = @json(route('it.categories.store'));
        function catUrl(id) { return catIndexUrl.replace(/\/it\/categories.*/, '/it/categories/' + id); }

        function resetCatForm() { catId.value=''; catForm.reset(); catCancel.classList.add('hidden'); }

        function renderCats(cats) {
            if (!cats.length) { catList.innerHTML = '<div class="flex items-center justify-center py-8 text-sm text-slate-400">No categories yet.</div>'; return; }
            var html = '<table class="w-full text-sm"><thead><tr class="border-b border-slate-100 text-xs text-slate-500 uppercase tracking-wide"><th class="px-4 py-2.5 text-left">Name</th><th class="px-4 py-2.5 text-right">Actions</th></tr></thead><tbody>';
            cats.forEach(function(c) {
                html += '<tr class="border-b border-slate-100 hover:bg-slate-50/40">' +
                    '<td class="px-4 py-3 font-medium text-slate-900">' + escHtml(c.name) + '</td>' +
                    '<td class="px-4 py-3 text-right"><div class="inline-flex items-center gap-1.5">' +
                        '<button type="button" class="it-cat-edit-btn inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200" data-id="' + c.id + '" data-name="' + escAttr(c.name) + '" data-desc="' + escAttr(c.description||'') + '" title="Edit"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>' +
                        '<button type="button" class="it-cat-del-btn inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-700 hover:bg-red-100" data-id="' + c.id + '" title="Delete"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/></svg></button>' +
                    '</div></td></tr>';
            });
            catList.innerHTML = html + '</tbody></table>';
        }

        function loadCats() {
            catList.innerHTML = '<div class="flex items-center justify-center py-8 text-sm text-slate-400">Loading…</div>';
            fetch(catIndexUrl, { credentials:'same-origin', headers:{'Accept':'application/json'} })
                .then(function(r){return r.json();}).then(renderCats)
                .catch(function(){catList.innerHTML='<div class="flex items-center justify-center py-8 text-sm text-red-400">Could not load.</div>';});
        }

        catForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var id=catId.value, name=catName.value.trim(), desc=catDesc.value.trim();
            if (!name) return;
            catSave.disabled=true;
            fetch(id?catUrl(id):catStoreUrl, { method:id?'PUT':'POST', credentials:'same-origin',
                headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},
                body:JSON.stringify({name:name,description:desc||null}) })
            .then(function(r){return r.ok?r.json():r.json().then(function(d){throw d;});})
            .then(function(){showMsg(catMsg,id?'Updated.':'Added.',true);resetCatForm();loadCats();})
            .catch(function(d){showMsg(catMsg,(d&&d.message)||'Error.',false);})
            .finally(function(){catSave.disabled=false;});
        });

        catList.addEventListener('click', function(e) {
            var eb=e.target.closest('.it-cat-edit-btn'), db=e.target.closest('.it-cat-del-btn');
            if (eb) { catId.value=eb.dataset.id; catName.value=eb.dataset.name; catDesc.value=eb.dataset.desc; catCancel.classList.remove('hidden'); catName.focus(); }
            if (db) {
                window.showDeleteConfirm('Delete this category?', 'This cannot be undone.', function() {
                    fetch(catUrl(db.dataset.id),{method:'DELETE',credentials:'same-origin',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf}})
                    .then(function(r){return r.ok?r.json():Promise.reject();})
                    .then(function(){showMsg(catMsg,'Deleted.',true);loadCats();})
                    .catch(function(){showMsg(catMsg,'Could not delete.',false);});
                });
            }
        });
        catCancel.addEventListener('click', resetCatForm);

        @if(false)
        // ── Priorities ──────────────────────────────────────────────────────
        var priList     = document.getElementById('it-pri-list');
        var priForm     = document.getElementById('it-pri-form');
        var priId       = document.getElementById('it-pri-id');
        var priLabel    = document.getElementById('it-pri-label');
        var priOrder    = document.getElementById('it-pri-order');
        var priActive   = document.getElementById('it-pri-active');
        var priSave     = document.getElementById('it-pri-save');
        var priCancel   = document.getElementById('it-pri-cancel-edit');
        var priMsg      = document.getElementById('it-pri-msg');
        var priIndexUrl = @json(route('it.priorities.index'));
        var priStoreUrl = @json(route('it.priorities.store'));
        function priUrl(id) { return priIndexUrl.replace(/\/it\/priorities.*/, '/it/priorities/' + id); }

        function resetPriForm() { priId.value=''; priForm.reset(); priActive.checked=true; priCancel.classList.add('hidden'); }

        function renderPris(pris) {
            if (!pris.length) { priList.innerHTML='<div class="flex items-center justify-center py-8 text-sm text-slate-400">No priorities yet.</div>'; return; }
            var html='<table class="w-full text-sm"><thead><tr class="border-b border-slate-100 text-xs text-slate-500 uppercase tracking-wide"><th class="px-4 py-2 text-left">Label</th><th class="px-4 py-2 text-center">Order</th><th class="px-4 py-2 text-center">Status</th><th class="px-4 py-2 text-right">Actions</th></tr></thead><tbody>';
            pris.forEach(function(p) {
                var badge=p.active?'<span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-800">Active</span>':'<span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500">Inactive</span>';
                html+='<tr class="border-b border-slate-100 hover:bg-slate-50/60">' +
                    '<td class="px-4 py-2.5 font-medium text-slate-900">'+escHtml(p.label)+'</td>' +
                    '<td class="px-4 py-2.5 text-center text-slate-500">'+p.sort_order+'</td>' +
                    '<td class="px-4 py-2.5 text-center">'+badge+'</td>' +
                    '<td class="px-4 py-2.5 text-right"><div class="inline-flex gap-1">' +
                        '<button type="button" class="it-pri-edit-btn inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200" data-id="'+p.id+'" data-label="'+escAttr(p.label)+'" data-order="'+p.sort_order+'" data-active="'+(p.active?'1':'0')+'" title="Edit"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>' +
                        '<button type="button" class="it-pri-del-btn inline-flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-700 hover:bg-red-100" data-id="'+p.id+'" title="Delete"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/></svg></button>' +
                    '</div></td></tr>';
            });
            priList.innerHTML = html + '</tbody></table>';
        }

        function loadPris() {
            priList.innerHTML='<div class="flex items-center justify-center py-8 text-sm text-slate-400">Loading…</div>';
            fetch(priIndexUrl,{credentials:'same-origin',headers:{'Accept':'application/json'}})
                .then(function(r){return r.json();}).then(renderPris)
                .catch(function(){priList.innerHTML='<div class="flex items-center justify-center py-8 text-sm text-red-400">Could not load.</div>';});
        }

        priForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var id=priId.value, label=priLabel.value.trim(), order=priOrder.value, active=priActive.checked;
            if (!label) return;
            priSave.disabled=true;
            fetch(id?priUrl(id):priStoreUrl,{method:id?'PUT':'POST',credentials:'same-origin',
                headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},
                body:JSON.stringify({label:label,sort_order:order?parseInt(order):0,active:active})})
            .then(function(r){return r.ok?r.json():r.json().then(function(d){throw d;});})
            .then(function(){showMsg(priMsg,id?'Updated.':'Added.',true);resetPriForm();loadPris();})
            .catch(function(d){showMsg(priMsg,(d&&d.message)||'Error.',false);})
            .finally(function(){priSave.disabled=false;});
        });

        priList.addEventListener('click', function(e) {
            var eb=e.target.closest('.it-pri-edit-btn'), db=e.target.closest('.it-pri-del-btn');
            if (eb) { priId.value=eb.dataset.id; priLabel.value=eb.dataset.label; priOrder.value=eb.dataset.order; priActive.checked=eb.dataset.active==='1'; priCancel.classList.remove('hidden'); priLabel.focus(); }
            if (db) {
                window.showDeleteConfirm('Delete this priority?', 'This cannot be undone.', function() {
                    fetch(priUrl(db.dataset.id),{method:'DELETE',credentials:'same-origin',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf}})
                    .then(function(r){return r.ok?r.json():Promise.reject();})
                    .then(function(){showMsg(priMsg,'Deleted.',true);loadPris();})
                    .catch(function(){showMsg(priMsg,'Could not delete.',false);});
                });
            }
        });
        priCancel.addEventListener('click', resetPriForm);
        @endif

        // ── Modal open/close ─────────────────────────────────────────────────
        function open() {
            var panel = document.getElementById('sidebar-panel');
            var collapseBtn = document.getElementById('sidebar-collapse-btn');
            if (window.matchMedia('(max-width: 1023px)').matches) {
                if (panel) panel.classList.add('collapsed');
                document.body.classList.remove('mobile-sidebar-open');
                document.body.classList.add('customize-modal-open');
                if (collapseBtn) {
                    collapseBtn.setAttribute('aria-label', 'Expand sidebar');
                    collapseBtn.setAttribute('title', 'Expand sidebar');
                }
            }
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            resetCatForm();
            loadCats();
        }
        function close() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('customize-modal-open');
        }

        if (openBtn) openBtn.addEventListener('click', open);
        if (openBtnRail) openBtnRail.addEventListener('click', open);
        if (closeBtn) closeBtn.addEventListener('click', close);
        modal.addEventListener('click', function(e) { if (e.target===modal) close(); });
        document.addEventListener('keydown', function(e) { if (e.key==='Escape' && !modal.classList.contains('hidden')) close(); });
    })();
    </script>
    @endif

    @if(auth()->user()->isAdmin())
    {{-- All Notifications Modal (Admin only) --}}
    <div id="all-notifications-modal" class="fixed inset-0 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="z-index:2147483647" aria-modal="true" role="dialog" aria-labelledby="all-notifications-modal-title">
        <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white text-slate-900 shadow-2xl overflow-hidden flex flex-col max-h-[85vh] dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur shrink-0 dark:border-slate-700 dark:bg-slate-900/95">
                <div class="min-w-0">
                    <h2 id="all-notifications-modal-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">All Notifications</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Recent ticket and system activity</p>
                </div>
                <button type="button" id="all-notifications-modal-close"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800"
                        aria-label="Close">&times;</button>
            </div>
            <div class="border-b border-slate-100 bg-white px-5 py-2 shrink-0 dark:border-slate-700 dark:bg-slate-800/70">
                <div class="flex flex-wrap gap-2" id="all-notifications-tabs">
                    @php
                        $adminTabBase = 'notif-tab-btn inline-flex items-center rounded-xl border px-3 py-1.5 text-xs font-semibold cursor-pointer transition-colors';
                        $adminTabOn  = 'border-blue-500 bg-blue-100 text-blue-900 ring-2 ring-blue-300 shadow-sm dark:border-blue-400/70 dark:bg-blue-500/25 dark:text-blue-100 dark:ring-blue-500/40';
                        $adminTabOff = 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800';
                    @endphp
                    <button type="button" class="{{ $adminTabBase }} {{ $adminTabOff }}" data-category="" aria-pressed="false">All</button>
                    <button type="button" class="{{ $adminTabBase }} {{ $adminTabOn }}" data-category="tickets" aria-pressed="true">Ticket events</button>
                    <button type="button" class="{{ $adminTabBase }} {{ $adminTabOff }}" data-category="auth" aria-pressed="false">Login / Logout</button>
                    <button type="button" class="{{ $adminTabBase }} {{ $adminTabOff }}" data-category="system" aria-pressed="false">System alerts</button>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto" id="all-notifications-modal-body">
                <div class="flex items-center justify-center py-12 text-sm text-slate-500 dark:text-slate-400">Loading…</div>
            </div>
        </div>
    </div>
    <script>
    (function() {
        var modal     = document.getElementById('all-notifications-modal');
        var closeBtn  = document.getElementById('all-notifications-modal-close');
        var body      = document.getElementById('all-notifications-modal-body');
        var tabs      = document.getElementById('all-notifications-tabs');
        var openBtn   = document.getElementById('see-all-notifications-btn');
        var baseUrl   = @json(route('notifications.header'));
        var currentCategory = '';
        var currentPage = 1;
        var pendingRequestController = null;
        var requestSequence = 0;
        var hasMore = false;

        var tabOn  = 'border-blue-500 bg-blue-100 text-blue-900 ring-2 ring-blue-300 shadow-sm dark:border-blue-400/70 dark:bg-blue-500/25 dark:text-blue-100 dark:ring-blue-500/40';
        var tabOff = 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800';

        function setActiveTab(cat) {
            currentCategory = cat;
            tabs.querySelectorAll('.notif-tab-btn').forEach(function(btn) {
                var on = btn.getAttribute('data-category') === cat;
                btn.className = btn.className.replace(/border-blue-500 bg-blue-100 text-blue-900 ring-2 ring-blue-300 shadow-sm dark:border-blue-400\/70 dark:bg-blue-500\/25 dark:text-blue-100 dark:ring-blue-500\/40|border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/g, '').trim();
                btn.className += ' ' + (on ? tabOn : tabOff);
                btn.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
        }

        function buildUrl(cat, page) {
            var url = baseUrl + '?perPage=20&page=' + page;
            if (cat) url += '&category=' + encodeURIComponent(cat);
            return url;
        }

        function load(cat, page, append) {
            requestSequence += 1;
            var requestId = requestSequence;
            if (pendingRequestController) {
                pendingRequestController.abort();
            }
            pendingRequestController = new AbortController();
            fetch(buildUrl(cat, page), {
                signal: pendingRequestController.signal,
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.ok ? r.json() : Promise.reject(); })
            .then(function(data) {
                if (requestId !== requestSequence) return;
                hasMore = data.hasMore || false;
                currentPage = page;
                var parser = new DOMParser();
                var doc = parser.parseFromString(data.html || '', 'text/html');
                var items = doc.querySelectorAll('.header-notification-item');
                if (!append) body.innerHTML = '';
                var sentinel = document.getElementById('notif-load-more');
                if (sentinel) sentinel.remove();
                if (items.length === 0 && !append) {
                    body.innerHTML = '<div class="flex items-center justify-center py-12 text-sm text-slate-500 dark:text-slate-400">No notifications found.</div>';
                    return;
                }
                var fragment = document.createDocumentFragment();
                items.forEach(function(el) {
                    var wrapper = document.createElement('div');
                    wrapper.innerHTML = el.outerHTML;
                    fragment.appendChild(wrapper.firstChild);
                });
                body.appendChild(fragment);
                if (hasMore) {
                    var loadMore = document.createElement('button');
                    loadMore.id = 'notif-load-more';
                    loadMore.type = 'button';
                    loadMore.className = 'w-full py-3 text-xs font-semibold text-blue-600 hover:text-blue-700 hover:bg-blue-50 transition-colors border-t border-slate-100 dark:border-slate-700 dark:text-blue-300 dark:hover:bg-slate-800';
                    loadMore.textContent = 'Load more';
                    loadMore.addEventListener('click', function() { load(currentCategory, currentPage + 1, true); });
                    body.appendChild(loadMore);
                }
            })
            .catch(function() {
                if (requestId !== requestSequence) return;
                if (!append) body.innerHTML = '<div class="flex items-center justify-center py-12 text-sm text-red-400">Could not load notifications.</div>';
            })
            .finally(function() {});
        }

        function open() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setActiveTab('tickets');
            load('tickets', 1, false);
        }

        function close() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        if (openBtn) openBtn.addEventListener('click', function(e) { e.stopPropagation(); open(); });
        if (closeBtn) closeBtn.addEventListener('click', close);
        modal.addEventListener('click', function(e) { if (e.target === modal) close(); });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) close();
        });
        tabs.addEventListener('click', function(e) {
            var btn = e.target.closest('.notif-tab-btn');
            if (!btn) return;
            setActiveTab(btn.getAttribute('data-category'));
            load(currentCategory, 1, false);
        });
    })();
    </script>

    {{-- Admin Settings Modal Script --}}
    <script>
    (function() {
        var modal    = document.getElementById('admin-settings-modal');
        var openBtn  = document.getElementById('js-open-admin-settings-modal');
        var closeBtn = document.getElementById('admin-settings-close');
        if (!modal || !openBtn) return;
        if (modal.dataset.bound === '1') return;
        modal.dataset.bound = '1';

        var tabs   = modal.querySelectorAll('.adm-set-tab');
        var panels = modal.querySelectorAll('.adm-set-panel');
        function setTab(name) {
            tabs.forEach(function(t) {
                var active = t.getAttribute('data-tab') === name;
                t.classList.toggle('bg-slate-900', active);
                t.classList.toggle('text-white', active);
                t.classList.toggle('border-slate-900', active);
                t.classList.toggle('bg-white', !active);
                t.classList.toggle('text-slate-600', !active);
                t.classList.toggle('border-slate-200', !active);
            });
            panels.forEach(function(p) {
                p.classList.toggle('hidden', p.getAttribute('data-tab') !== name);
            });
            if (typeof window.syncThemeRadios === 'function') window.syncThemeRadios();
        }
        tabs.forEach(function(t) {
            t.addEventListener('click', function() { setTab(t.getAttribute('data-tab')); });
        });

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTab('appearance');
        }
        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        openBtn.addEventListener('click', function() {
            var dropdown = document.getElementById('account-dropdown');
            if (dropdown) { dropdown.classList.add('hidden'); dropdown.classList.remove('flex'); }
            openModal();
        });
        closeBtn.addEventListener('click', closeModal);
        modal.querySelectorAll('.js-admin-settings-inner-close').forEach(function(b) {
            b.addEventListener('click', closeModal);
        });
        modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
        });
    })();
    </script>
    @endif

    {{-- Admin Settings Modal Script (all roles) --}}
    <script>
    (function() {
        var modal    = document.getElementById('admin-settings-modal');
        var openBtn  = document.getElementById('js-open-admin-settings-modal');
        var closeBtn = document.getElementById('admin-settings-close');
        if (!modal || !openBtn || !closeBtn) return;
        if (modal.dataset.bound === '1') return;
        modal.dataset.bound = '1';

        var tabs   = modal.querySelectorAll('.adm-set-tab');
        var panels = modal.querySelectorAll('.adm-set-panel');
        function setTab(name) {
            tabs.forEach(function(t) {
                var active = t.getAttribute('data-tab') === name;
                t.classList.toggle('bg-slate-900', active);
                t.classList.toggle('text-white', active);
                t.classList.toggle('border-slate-900', active);
                t.classList.toggle('bg-white', !active);
                t.classList.toggle('text-slate-600', !active);
                t.classList.toggle('border-slate-200', !active);
            });
            panels.forEach(function(p) {
                p.classList.toggle('hidden', p.getAttribute('data-tab') !== name);
            });
            if (typeof window.syncThemeRadios === 'function') window.syncThemeRadios();
        }

        tabs.forEach(function(t) {
            t.addEventListener('click', function() { setTab(t.getAttribute('data-tab')); });
        });

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTab('appearance');
        }
        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        openBtn.addEventListener('click', function() {
            var dropdown = document.getElementById('account-dropdown');
            if (dropdown) { dropdown.classList.add('hidden'); dropdown.classList.remove('flex'); }
            openModal();
        });
        closeBtn.addEventListener('click', closeModal);
        modal.querySelectorAll('.js-admin-settings-inner-close').forEach(function(b) {
            b.addEventListener('click', closeModal);
        });
        modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
        });
    })();
    </script>

    @if(auth()->user()->isFrontDesk() && !auth()->user()->isAdmin())
    {{-- All Notifications Modal (Front Desk only) --}}
    <div id="all-notifications-modal" class="fixed inset-0 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="z-index:2147483647" aria-modal="true" role="dialog" aria-labelledby="all-notifications-modal-title">
        <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white text-slate-900 shadow-2xl overflow-hidden flex flex-col max-h-[85vh] dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur shrink-0 dark:border-slate-700 dark:bg-slate-900/95">
                <div class="min-w-0">
                    <h2 id="all-notifications-modal-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">All Notifications</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Recent ticket and system activity</p>
                </div>
                <button type="button" id="all-notifications-modal-close"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800"
                        aria-label="Close">&times;</button>
            </div>

            {{-- Category filter tabs --}}
            <div class="border-b border-slate-100 bg-white px-5 py-2 shrink-0 dark:border-slate-700 dark:bg-slate-800/70">
                <div class="flex flex-wrap gap-2" id="all-notifications-tabs">
                    @php
                        $tabBase = 'notif-tab-btn inline-flex items-center rounded-xl border px-3 py-1.5 text-xs font-semibold cursor-pointer transition-colors';
                        $tabOn  = 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-500/40 dark:bg-blue-500/20 dark:text-blue-200';
                        $tabOff = 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800';
                    @endphp
                    <button type="button" class="{{ $tabBase }} {{ $tabOff }}" data-category="">All</button>
                    <button type="button" class="{{ $tabBase }} {{ $tabOn }}" data-category="tickets">Ticket events</button>
                    <button type="button" class="{{ $tabBase }} {{ $tabOff }}" data-category="auth">Login / Logout</button>
                    <button type="button" class="{{ $tabBase }} {{ $tabOff }}" data-category="system">System alerts</button>
                </div>
            </div>

            {{-- Content --}}
            <div class="flex-1 overflow-y-auto" id="all-notifications-modal-body">
                <div class="flex items-center justify-center py-12 text-sm text-slate-500 dark:text-slate-400">Loading…</div>
            </div>
        </div>
    </div>
    <script>
    (function() {
        var modal     = document.getElementById('all-notifications-modal');
        var closeBtn  = document.getElementById('all-notifications-modal-close');
        var body      = document.getElementById('all-notifications-modal-body');
        var tabs      = document.getElementById('all-notifications-tabs');
        var openBtn   = document.getElementById('see-all-notifications-btn');
        var baseUrl   = @json(route('notifications.header'));
        var currentCategory = '';
        var currentPage = 1;
        var pendingRequestController = null;
        var requestSequence = 0;
        var hasMore = false;

        var tabOn  = 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-500/40 dark:bg-blue-500/20 dark:text-blue-200';
        var tabOff = 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800';

        function setActiveTab(cat) {
            currentCategory = cat;
            tabs.querySelectorAll('.notif-tab-btn').forEach(function(btn) {
                var on = btn.getAttribute('data-category') === cat;
                btn.className = btn.className.replace(/border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-500\/40 dark:bg-blue-500\/20 dark:text-blue-200|border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/g, '').trim();
                btn.className += ' ' + (on ? tabOn : tabOff);
            });
        }

        function buildUrl(cat, page) {
            var url = baseUrl + '?perPage=20&page=' + page;
            if (cat) url += '&category=' + encodeURIComponent(cat);
            return url;
        }

        function renderItem(note) {
            var time = note.created_at_human || '';
            var body = note.body || '';
            var ticket = note.ticket_number || '';
            var kind = note.kind || 'system';
            var kindBadge = {
                ticket: 'bg-blue-100 text-blue-700',
                auth:   'bg-slate-100 text-slate-600',
                system: 'bg-amber-100 text-amber-700',
            }[kind] || 'bg-slate-100 text-slate-600';
            var kindLabel = { ticket: 'Ticket', auth: 'Auth', system: 'System' }[kind] || kind;

            var ticketPart = ticket
                ? '<span class="font-semibold text-slate-800">' + ticket + '</span> · '
                : '';

            return '<div class="flex items-start gap-3 px-5 py-3.5 border-b border-slate-100 last:border-0 hover:bg-slate-50/60 transition-colors">' +
                '<span class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500">' +
                    '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>' +
                '</span>' +
                '<div class="min-w-0 flex-1">' +
                    '<div class="flex flex-wrap items-center gap-1.5">' +
                        ticketPart +
                        '<span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold ' + kindBadge + '">' + kindLabel + '</span>' +
                        '<span class="ml-auto shrink-0 text-[11px] text-slate-400">' + time + '</span>' +
                    '</div>' +
                    '<p class="mt-1 text-xs text-slate-600 leading-relaxed">' + body + '</p>' +
                '</div>' +
            '</div>';
        }

        function load(cat, page, append) {
            requestSequence += 1;
            var requestId = requestSequence;
            if (pendingRequestController) {
                pendingRequestController.abort();
            }
            pendingRequestController = new AbortController();

            fetch(buildUrl(cat, page), {
                signal: pendingRequestController.signal,
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.ok ? r.json() : Promise.reject(); })
            .then(function(data) {
                if (requestId !== requestSequence) return;
                hasMore = data.hasMore || false;
                currentPage = page;

                // Parse the HTML from the header endpoint and extract notification items
                var parser = new DOMParser();
                var doc = parser.parseFromString(data.html || '', 'text/html');
                var items = doc.querySelectorAll('.header-notification-item');

                if (!append) body.innerHTML = '';

                // Remove load-more sentinel if exists
                var sentinel = document.getElementById('notif-load-more');
                if (sentinel) sentinel.remove();

                if (items.length === 0 && !append) {
                    body.innerHTML = '<div class="flex items-center justify-center py-12 text-sm text-slate-500 dark:text-slate-400">No notifications found.</div>';
                    return;
                }

                // Re-render items from parsed HTML
                var fragment = document.createDocumentFragment();
                items.forEach(function(el) {
                    var wrapper = document.createElement('div');
                    wrapper.innerHTML = el.outerHTML;
                    fragment.appendChild(wrapper.firstChild);
                });
                body.appendChild(fragment);

                if (hasMore) {
                    var loadMore = document.createElement('button');
                    loadMore.id = 'notif-load-more';
                    loadMore.type = 'button';
                    loadMore.className = 'w-full py-3 text-xs font-semibold text-blue-600 hover:text-blue-700 hover:bg-blue-50 transition-colors border-t border-slate-100 dark:border-slate-700 dark:text-blue-300 dark:hover:bg-slate-800';
                    loadMore.textContent = 'Load more';
                    loadMore.addEventListener('click', function() {
                        load(currentCategory, currentPage + 1, true);
                    });
                    body.appendChild(loadMore);
                }
            })
            .catch(function() {
                if (requestId !== requestSequence) return;
                if (!append) body.innerHTML = '<div class="flex items-center justify-center py-12 text-sm text-red-400">Could not load notifications.</div>';
            })
            .finally(function() {});
        }

        function open() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setActiveTab('tickets');
            load('tickets', 1, false);
        }

        function close() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        if (openBtn) openBtn.addEventListener('click', function(e) { e.stopPropagation(); open(); });
        if (closeBtn) closeBtn.addEventListener('click', close);
        modal.addEventListener('click', function(e) { if (e.target === modal) close(); });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) close();
        });

        tabs.addEventListener('click', function(e) {
            var btn = e.target.closest('.notif-tab-btn');
            if (!btn) return;
            var cat = btn.getAttribute('data-category');
            setActiveTab(cat);
            load(cat, 1, false);
        });
    })();
    </script>
    @endif

    @if(auth()->user()->role === \App\Models\User::ROLE_EMPLOYEE && !auth()->user()->isAdmin())
    {{-- All Notifications Modal (Employee only) --}}
    <div id="all-notifications-modal" class="fixed inset-0 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="z-index:2147483647" aria-modal="true" role="dialog" aria-labelledby="all-notifications-modal-title">
        <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white text-slate-900 shadow-2xl overflow-hidden flex flex-col max-h-[85vh] dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur shrink-0 dark:border-slate-700 dark:bg-slate-900/95">
                <div class="min-w-0">
                    <h2 id="all-notifications-modal-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">All Notifications</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Recent ticket and system activity</p>
                </div>
                <button type="button" id="all-notifications-modal-close"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800"
                        aria-label="Close">&times;</button>
            </div>
            <div class="border-b border-slate-100 bg-white px-5 py-2 shrink-0 dark:border-slate-700 dark:bg-slate-800/70">
                <div class="flex flex-wrap gap-2" id="all-notifications-tabs">
                    @php
                        $empTabBase = 'notif-tab-btn inline-flex items-center rounded-xl border px-3 py-1.5 text-xs font-semibold cursor-pointer transition-colors';
                        $empTabOn  = 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-500/40 dark:bg-blue-500/20 dark:text-blue-200';
                        $empTabOff = 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800';
                    @endphp
                    <button type="button" class="{{ $empTabBase }} {{ $empTabOff }}" data-category="">All</button>
                    <button type="button" class="{{ $empTabBase }} {{ $empTabOn }}" data-category="tickets">Ticket events</button>
                    <button type="button" class="{{ $empTabBase }} {{ $empTabOff }}" data-category="auth">Login / Logout</button>
                    <button type="button" class="{{ $empTabBase }} {{ $empTabOff }}" data-category="system">System alerts</button>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto" id="all-notifications-modal-body">
                <div class="flex items-center justify-center py-12 text-sm text-slate-500 dark:text-slate-400">Loading…</div>
            </div>
        </div>
    </div>
    <script>
    (function() {
        var modal     = document.getElementById('all-notifications-modal');
        var closeBtn  = document.getElementById('all-notifications-modal-close');
        var body      = document.getElementById('all-notifications-modal-body');
        var tabs      = document.getElementById('all-notifications-tabs');
        var openBtn   = document.getElementById('see-all-notifications-btn');
        var baseUrl   = @json(route('notifications.header'));
        var currentCategory = '';
        var currentPage = 1;
        var pendingRequestController = null;
        var requestSequence = 0;
        var hasMore = false;

        var tabOn  = 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-500/40 dark:bg-blue-500/20 dark:text-blue-200';
        var tabOff = 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800';

        function setActiveTab(cat) {
            currentCategory = cat;
            tabs.querySelectorAll('.notif-tab-btn').forEach(function(btn) {
                var on = btn.getAttribute('data-category') === cat;
                btn.className = btn.className.replace(/border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-500\/40 dark:bg-blue-500\/20 dark:text-blue-200|border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/g, '').trim();
                btn.className += ' ' + (on ? tabOn : tabOff);
            });
        }

        function buildUrl(cat, page) {
            var url = baseUrl + '?perPage=20&page=' + page;
            if (cat) url += '&category=' + encodeURIComponent(cat);
            return url;
        }

        function load(cat, page, append) {
            requestSequence += 1;
            var requestId = requestSequence;
            if (pendingRequestController) {
                pendingRequestController.abort();
            }
            pendingRequestController = new AbortController();
            fetch(buildUrl(cat, page), {
                signal: pendingRequestController.signal,
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.ok ? r.json() : Promise.reject(); })
            .then(function(data) {
                if (requestId !== requestSequence) return;
                hasMore = data.hasMore || false;
                currentPage = page;
                var parser = new DOMParser();
                var doc = parser.parseFromString(data.html || '', 'text/html');
                var items = doc.querySelectorAll('.header-notification-item');
                if (!append) body.innerHTML = '';
                var sentinel = document.getElementById('notif-load-more');
                if (sentinel) sentinel.remove();
                if (items.length === 0 && !append) {
                    body.innerHTML = '<div class="flex items-center justify-center py-12 text-sm text-slate-500 dark:text-slate-400">No notifications found.</div>';
                    return;
                }
                var fragment = document.createDocumentFragment();
                items.forEach(function(el) {
                    var wrapper = document.createElement('div');
                    wrapper.innerHTML = el.outerHTML;
                    fragment.appendChild(wrapper.firstChild);
                });
                body.appendChild(fragment);
                if (hasMore) {
                    var loadMore = document.createElement('button');
                    loadMore.id = 'notif-load-more';
                    loadMore.type = 'button';
                    loadMore.className = 'w-full py-3 text-xs font-semibold text-blue-600 hover:text-blue-700 hover:bg-blue-50 transition-colors border-t border-slate-100 dark:border-slate-700 dark:text-blue-300 dark:hover:bg-slate-800';
                    loadMore.textContent = 'Load more';
                    loadMore.addEventListener('click', function() { load(currentCategory, currentPage + 1, true); });
                    body.appendChild(loadMore);
                }
            })
            .catch(function() {
                if (requestId !== requestSequence) return;
                if (!append) body.innerHTML = '<div class="flex items-center justify-center py-12 text-sm text-red-400">Could not load notifications.</div>';
            })
            .finally(function() {});
        }

        function open() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setActiveTab('tickets');
            load('tickets', 1, false);
        }

        function close() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        if (openBtn) openBtn.addEventListener('click', function(e) { e.stopPropagation(); open(); });
        if (closeBtn) closeBtn.addEventListener('click', close);
        modal.addEventListener('click', function(e) { if (e.target === modal) close(); });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) close();
        });
        tabs.addEventListener('click', function(e) {
            var btn = e.target.closest('.notif-tab-btn');
            if (!btn) return;
            setActiveTab(btn.getAttribute('data-category'));
            load(currentCategory, 1, false);
        });
    })();
    </script>
    @endif

    @if(auth()->user()->isItStaff() && !auth()->user()->isAdmin())
    {{-- All Notifications Modal (IT Staff only) --}}
    <div id="all-notifications-modal" class="fixed inset-0 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="z-index:2147483647" aria-modal="true" role="dialog" aria-labelledby="all-notifications-modal-title">
        <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white text-slate-900 shadow-2xl overflow-hidden flex flex-col max-h-[85vh] dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur shrink-0 dark:border-slate-700 dark:bg-slate-900/95">
                <div class="min-w-0">
                    <h2 id="all-notifications-modal-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">All Notifications</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Recent ticket and system activity</p>
                </div>
                <button type="button" id="all-notifications-modal-close"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800"
                        aria-label="Close">&times;</button>
            </div>
            <div class="border-b border-slate-100 bg-white px-5 py-2 shrink-0 dark:border-slate-700 dark:bg-slate-800/70">
                <div class="flex flex-wrap gap-2" id="all-notifications-tabs">
                    @php
                        $itTabBase = 'notif-tab-btn inline-flex items-center rounded-xl border px-3 py-1.5 text-xs font-semibold cursor-pointer transition-colors';
                        $itTabOn  = 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-500/40 dark:bg-blue-500/20 dark:text-blue-200';
                        $itTabOff = 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800';
                    @endphp
                    <button type="button" class="{{ $itTabBase }} {{ $itTabOff }}" data-category="">All</button>
                    <button type="button" class="{{ $itTabBase }} {{ $itTabOn }}" data-category="tickets">Ticket events</button>
                    <button type="button" class="{{ $itTabBase }} {{ $itTabOff }}" data-category="auth">Login / Logout</button>
                    <button type="button" class="{{ $itTabBase }} {{ $itTabOff }}" data-category="system">System alerts</button>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto" id="all-notifications-modal-body">
                <div class="flex items-center justify-center py-12 text-sm text-slate-500 dark:text-slate-400">Loading…</div>
            </div>
        </div>
    </div>
    <script>
    (function() {
        var modal     = document.getElementById('all-notifications-modal');
        var closeBtn  = document.getElementById('all-notifications-modal-close');
        var body      = document.getElementById('all-notifications-modal-body');
        var tabs      = document.getElementById('all-notifications-tabs');
        var openBtn   = document.getElementById('see-all-notifications-btn');
        var baseUrl   = @json(route('notifications.header'));
        var currentCategory = '';
        var currentPage = 1;
        var pendingRequestController = null;
        var requestSequence = 0;
        var hasMore = false;

        var tabOn  = 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-500/40 dark:bg-blue-500/20 dark:text-blue-200';
        var tabOff = 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800';

        function setActiveTab(cat) {
            currentCategory = cat;
            tabs.querySelectorAll('.notif-tab-btn').forEach(function(btn) {
                var on = btn.getAttribute('data-category') === cat;
                btn.className = btn.className.replace(/border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-500\/40 dark:bg-blue-500\/20 dark:text-blue-200|border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/g, '').trim();
                btn.className += ' ' + (on ? tabOn : tabOff);
            });
        }

        function buildUrl(cat, page) {
            var url = baseUrl + '?perPage=20&page=' + page;
            if (cat) url += '&category=' + encodeURIComponent(cat);
            return url;
        }

        function load(cat, page, append) {
            requestSequence += 1;
            var requestId = requestSequence;
            if (pendingRequestController) {
                pendingRequestController.abort();
            }
            pendingRequestController = new AbortController();
            fetch(buildUrl(cat, page), {
                signal: pendingRequestController.signal,
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.ok ? r.json() : Promise.reject(); })
            .then(function(data) {
                if (requestId !== requestSequence) return;
                hasMore = data.hasMore || false;
                currentPage = page;
                var parser = new DOMParser();
                var doc = parser.parseFromString(data.html || '', 'text/html');
                var items = doc.querySelectorAll('.header-notification-item');
                if (!append) body.innerHTML = '';
                var sentinel = document.getElementById('notif-load-more');
                if (sentinel) sentinel.remove();
                if (items.length === 0 && !append) {
                    body.innerHTML = '<div class="flex items-center justify-center py-12 text-sm text-slate-500 dark:text-slate-400">No notifications found.</div>';
                    return;
                }
                var fragment = document.createDocumentFragment();
                items.forEach(function(el) {
                    var wrapper = document.createElement('div');
                    wrapper.innerHTML = el.outerHTML;
                    fragment.appendChild(wrapper.firstChild);
                });
                body.appendChild(fragment);
                if (hasMore) {
                    var loadMore = document.createElement('button');
                    loadMore.id = 'notif-load-more';
                    loadMore.type = 'button';
                    loadMore.className = 'w-full py-3 text-xs font-semibold text-blue-600 hover:text-blue-700 hover:bg-blue-50 transition-colors border-t border-slate-100 dark:border-slate-700 dark:text-blue-300 dark:hover:bg-slate-800';
                    loadMore.textContent = 'Load more';
                    loadMore.addEventListener('click', function() { load(currentCategory, currentPage + 1, true); });
                    body.appendChild(loadMore);
                }
            })
            .catch(function() {
                if (requestId !== requestSequence) return;
                if (!append) body.innerHTML = '<div class="flex items-center justify-center py-12 text-sm text-red-400">Could not load notifications.</div>';
            })
            .finally(function() {});
        }

        function open() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setActiveTab('tickets');
            load('tickets', 1, false);
        }

        function close() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        if (openBtn) openBtn.addEventListener('click', function(e) { e.stopPropagation(); open(); });
        if (closeBtn) closeBtn.addEventListener('click', close);
        modal.addEventListener('click', function(e) { if (e.target === modal) close(); });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) close();
        });
        tabs.addEventListener('click', function(e) {
            var btn = e.target.closest('.notif-tab-btn');
            if (!btn) return;
            setActiveTab(btn.getAttribute('data-category'));
            load(currentCategory, 1, false);
        });
    })();
    </script>
    @endif

    <script>
    (function() {
        var modal = document.getElementById('profile-settings-modal');
        if (!modal) return;
        var title = document.getElementById('profile-settings-title');
        var closeBtns = modal.querySelectorAll('.js-close-profile-modal');
        var tabBtns = modal.querySelectorAll('.profile-tab-btn');
        var panels = modal.querySelectorAll('.profile-tab-panel');

        function allowedProfileTabs() {
            return Array.prototype.map.call(panels, function(p) { return p.getAttribute('data-tab'); });
        }
        function normalizeProfileTab(tab) {
            var t = tab || 'profile';
            var allowed = allowedProfileTabs();
            return allowed.indexOf(t) >= 0 ? t : 'profile';
        }

        function setActiveTab(tab) {
            var t = normalizeProfileTab(tab);
            panels.forEach(function(p) {
                p.classList.toggle('hidden', p.getAttribute('data-tab') !== t);
            });
            tabBtns.forEach(function(b) {
                var on = b.getAttribute('data-tab') === t;
                b.className = 'profile-tab-btn inline-flex items-center rounded-xl border px-3 py-1.5 text-xs font-semibold ' +
                    (on ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50');
            });
            if (title) title.textContent = t.charAt(0).toUpperCase() + t.slice(1);
            if (typeof window.syncThemeRadios === 'function') window.syncThemeRadios();
        }

        function open(tab) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setActiveTab(tab || 'profile');
        }

        function close() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.addEventListener('click', function(e) {
            var btn = e.target.closest ? e.target.closest('.js-open-profile-modal') : null;
            if (!btn) return;
            e.preventDefault();
            open(btn.getAttribute('data-tab') || 'profile');
        }, true);

        closeBtns.forEach(function(b) { b.addEventListener('click', close); });
        modal.addEventListener('click', function(e) { if (e.target === modal) close(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && !modal.classList.contains('hidden')) close(); });

        tabBtns.forEach(function(b) {
            b.addEventListener('click', function() { setActiveTab(b.getAttribute('data-tab')); });
        });

        // Auto-open when redirected from old pages (?profile_tab=...)
        try {
            var params = new URLSearchParams(window.location.search || '');
            var t = params.get('profile_tab');
            if (t) {
                open(normalizeProfileTab(t));
            }
        } catch (e) {}

        // Avatar click-to-upload with adjust-before-save crop step
        var avatarTrigger = document.getElementById('modal-avatar-trigger');
        var avatarInput = document.getElementById('modal_profile_picture');
        var avatarDataInput = document.getElementById('modal_profile_picture_data');
        var cropModal = document.getElementById('modal-profile-crop-modal');
        var cropBackdrop = document.getElementById('modal-profile-crop-backdrop');
        var cropImage = document.getElementById('modal-profile-crop-image');
        var cropCancel = document.getElementById('modal-profile-crop-cancel');
        var cropApply = document.getElementById('modal-profile-crop-apply');
        var cropUseOriginal = document.getElementById('modal-profile-crop-use-original');
        var currentFileForOriginal = null;
        var modalCropper = null;

        function setModalAvatar(src) {
            var preview = document.getElementById('modal-avatar-preview');
            var placeholder = document.getElementById('modal-avatar-placeholder');
            if (preview) {
                preview.src = src;
            } else if (placeholder) {
                var img = document.createElement('img');
                img.id = 'modal-avatar-preview';
                img.src = src;
                img.className = 'h-full w-full object-cover';
                placeholder.replaceWith(img);
            }
        }

        function openCropModal(file) {
            if (!cropModal || !cropImage || typeof Cropper === 'undefined') return false;
            currentFileForOriginal = file;
            cropImage.src = URL.createObjectURL(file);
            cropModal.classList.remove('hidden');
            if (modalCropper) modalCropper.destroy();
            modalCropper = new Cropper(cropImage, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.82,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false
            });
            return true;
        }

        function closeCropModal(keepFile) {
            if (cropModal) cropModal.classList.add('hidden');
            if (modalCropper) { modalCropper.destroy(); modalCropper = null; }
            if (cropImage && cropImage.src && cropImage.src.indexOf('blob:') === 0) URL.revokeObjectURL(cropImage.src);
            if (cropImage) cropImage.src = '';
            if (!keepFile && avatarInput) avatarInput.value = '';
            currentFileForOriginal = null;
        }

        function useOriginalImage() {
            if (!currentFileForOriginal) return;
            if (avatarDataInput) avatarDataInput.value = '';
            var src = URL.createObjectURL(currentFileForOriginal);
            setModalAvatar(src);
            var removeCheckbox = document.querySelector('input[name="remove_profile_picture"]');
            if (removeCheckbox) removeCheckbox.checked = false;
            closeCropModal(true);
        }

        function applyCroppedImage() {
            if (!modalCropper) return;
            var canvas = modalCropper.getCroppedCanvas({ width: 400, height: 400, imageSmoothingEnabled: true, imageSmoothingQuality: 'high' });
            if (!canvas) return;
            var dataUrl = canvas.toDataURL('image/jpeg', 0.9);
            if (avatarDataInput) avatarDataInput.value = dataUrl;
            setModalAvatar(dataUrl);
            var removeCheckbox = document.querySelector('input[name="remove_profile_picture"]');
            if (removeCheckbox) removeCheckbox.checked = false;
            closeCropModal(true);
        }

        if (avatarTrigger && avatarInput) {
            avatarTrigger.addEventListener('click', function() { avatarInput.click(); });
            avatarInput.addEventListener('change', function() {
                var file = avatarInput.files && avatarInput.files[0];
                if (!file) return;
                if (!file.type.match(/^image\/(jpeg|png|gif|webp)$/i)) { alert('Please choose a JPEG, PNG, GIF or WebP image.'); avatarInput.value = ''; return; }
                if (file.size > 2 * 1024 * 1024) { alert('Image must be under 2 MB.'); avatarInput.value = ''; return; }
                if (!openCropModal(file)) {
                    var reader = new FileReader();
                    reader.onload = function(ev) { setModalAvatar(ev.target.result); };
                    reader.readAsDataURL(file);
                }
            });
        }
        if (cropCancel) cropCancel.addEventListener('click', function() { closeCropModal(false); });
        if (cropBackdrop) cropBackdrop.addEventListener('click', function() { closeCropModal(false); });
        if (cropApply) cropApply.addEventListener('click', applyCroppedImage);
        if (cropUseOriginal) cropUseOriginal.addEventListener('click', useOriginalImage);

        // AJAX submit for profile form — no full page reload
        var profileForm = modal.querySelector('.profile-tab-panel[data-tab="profile"] form');
        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var saveBtn = profileForm.querySelector('button[type="submit"]');
                var origHtml = saveBtn ? saveBtn.innerHTML : '';
                if (saveBtn) { saveBtn.disabled = true; saveBtn.innerHTML = '<svg class="h-4 w-4 animate-spin mr-1" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="40" stroke-dashoffset="15" opacity=".3"/><path stroke="currentColor" stroke-linecap="round" stroke-width="3" d="M12 2a10 10 0 0 1 10 10"/></svg>Saving…'; }

                var fd = new FormData(profileForm);
                fetch(profileForm.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': window.csrfToken || '' },
                    body: fd
                })
                .then(function(r) { return r.ok ? r.json() : r.json().then(function(d){ throw d; }); })
                .then(function(data) {
                    // Update header name if changed
                    var nameInput = profileForm.querySelector('[name="name"]');
                    if (nameInput) {
                        var headerName = document.getElementById('header-profile-name');
                        if (headerName) headerName.textContent = nameInput.value;
                    }
                    // Update header/menu avatar immediately after save.
                    (function syncHeaderAvatar() {
                        var displayName = (nameInput && nameInput.value) ? String(nameInput.value).trim() : '';
                        var initial = displayName ? displayName.charAt(0).toUpperCase() : '{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}';
                        var hasNewFile = !!(avatarInput && avatarInput.files && avatarInput.files.length > 0);
                        var removeChecked = !!profileForm.querySelector('input[name="remove_profile_picture"]:checked');
                        var modalPreview = document.getElementById('modal-avatar-preview');
                        var nextSrc = (modalPreview && modalPreview.getAttribute('src')) ? modalPreview.getAttribute('src') : '';
                        var useImage = !!nextSrc && (!removeChecked || hasNewFile);

                        var headerAvatar = document.getElementById('header-avatar');
                        var headerPlaceholder = document.getElementById('header-avatar-placeholder');
                        var menuAvatar = document.getElementById('header-menu-avatar');
                        var menuPlaceholder = document.getElementById('header-menu-avatar-placeholder');

                        function withCacheBust(src) {
                            if (!src) return src;
                            // Do not append query params to data/blob URLs (breaks image rendering).
                            if (src.indexOf('data:') === 0 || src.indexOf('blob:') === 0) return src;
                            return src + (src.indexOf('?') === -1 ? '?v=' : '&v=') + Date.now();
                        }

                        function ensureHeaderImage(src) {
                            if (!headerAvatar) {
                                headerAvatar = document.createElement('img');
                                headerAvatar.id = 'header-avatar';
                                headerAvatar.alt = '';
                                headerAvatar.className = 'h-9 w-9 rounded-full object-cover shrink-0 border border-slate-200 dark:border-slate-600';
                                if (headerPlaceholder && headerPlaceholder.parentNode) headerPlaceholder.parentNode.replaceChild(headerAvatar, headerPlaceholder);
                            }
                            headerAvatar.src = withCacheBust(src);
                            if (headerPlaceholder && headerPlaceholder.parentNode) headerPlaceholder.parentNode.removeChild(headerPlaceholder);
                        }

                        function ensureHeaderPlaceholder(letter) {
                            if (headerAvatar && headerAvatar.parentNode) headerAvatar.parentNode.removeChild(headerAvatar);
                            if (!headerPlaceholder) {
                                headerPlaceholder = document.createElement('div');
                                headerPlaceholder.id = 'header-avatar-placeholder';
                                headerPlaceholder.className = 'h-9 w-9 rounded-full bg-slate-600 flex items-center justify-center text-white text-sm font-semibold shrink-0';
                                var btn = document.getElementById('header-profile-button');
                                if (btn) btn.insertBefore(headerPlaceholder, btn.firstChild);
                            }
                            headerPlaceholder.textContent = letter;
                        }

                        function ensureMenuImage(src) {
                            if (!menuAvatar) {
                                menuAvatar = document.createElement('img');
                                menuAvatar.id = 'header-menu-avatar';
                                menuAvatar.alt = '';
                                menuAvatar.className = 'h-9 w-9 rounded-full object-cover border border-slate-200/80 dark:border-slate-600/80';
                                if (menuPlaceholder && menuPlaceholder.parentNode) menuPlaceholder.parentNode.replaceChild(menuAvatar, menuPlaceholder);
                            }
                            menuAvatar.src = withCacheBust(src);
                            if (menuPlaceholder && menuPlaceholder.parentNode) menuPlaceholder.parentNode.removeChild(menuPlaceholder);
                        }

                        function ensureMenuPlaceholder(letter) {
                            if (menuAvatar && menuAvatar.parentNode) menuAvatar.parentNode.removeChild(menuAvatar);
                            if (!menuPlaceholder) {
                                menuPlaceholder = document.createElement('div');
                                menuPlaceholder.id = 'header-menu-avatar-placeholder';
                                menuPlaceholder.className = 'h-9 w-9 rounded-full bg-slate-600 flex items-center justify-center text-white text-sm font-semibold';
                                var menuWrap = document.getElementById('header-profile-menu');
                                var targetRow = menuWrap ? menuWrap.firstElementChild : null;
                                if (targetRow) targetRow.insertBefore(menuPlaceholder, targetRow.firstChild);
                            }
                            menuPlaceholder.textContent = letter;
                        }

                        if (useImage) {
                            ensureHeaderImage(nextSrc);
                            ensureMenuImage(nextSrc);
                        } else {
                            ensureHeaderPlaceholder(initial);
                            ensureMenuPlaceholder(initial);
                        }
                    })();
                    close();
                    if (window.showAppToast) window.showAppToast(data.message || 'Profile saved.');
                })
                .catch(function(err) {
                    var msg = (err && err.errors) ? Object.values(err.errors).flat().join(' ') : (err && err.message) || 'Could not save.';
                    if (window.showAppToast) window.showAppToast(msg);
                })
                .finally(function() {
                    if (saveBtn) { saveBtn.disabled = false; saveBtn.innerHTML = origHtml; }
                });
            });
        }
    })();
    </script>
    @endauth

    <script>
        (function () {
            function fadeOutAndRemove(element, done) {
                if (!element) { if (done) done(); return; }
                element.style.transition = 'opacity .24s ease, transform .24s ease';
                element.style.opacity = '0';
                element.style.transform = 'translateY(-6px) scale(.985)';
                setTimeout(function () {
                    if (element && element.parentNode) element.parentNode.removeChild(element);
                    if (done) done();
                }, 250);
            }

            document.addEventListener('click', function (e) {
                var form = e.target.closest('form.swift-confirm-delete');
                if (!form) return;
                if (e.target.type === 'submit' || e.target.closest('button[type="submit"]')) {
                    e.preventDefault();
                    var message = form.getAttribute('data-confirm-message') || 'Are you sure you want to delete this?';

                    var overlay = document.createElement('div');
                    overlay.className = 'fixed inset-0 z-[9999] flex items-center justify-center bg-black/40';
                    overlay.setAttribute('aria-modal', 'true');
                    overlay.setAttribute('role', 'dialog');

                    var isDark = document.documentElement.classList.contains('dark');
                    var box = document.createElement('div');
                    box.className = isDark
                        ? 'max-w-sm w-full rounded-2xl bg-[#0b1020] shadow-xl border border-blue-900/70 p-5 text-slate-100'
                        : 'max-w-sm w-full rounded-2xl bg-white shadow-xl border border-slate-200 p-5 text-slate-800';
                    box.innerHTML =
                        '<h2 class=\"text-base font-semibold mb-2' + (isDark ? ' text-slate-100' : '') + '\">Confirm delete</h2>' +
                        '<p class=\"text-sm mb-4 ' + (isDark ? 'text-slate-300' : 'text-slate-600') + '\">' + message + '</p>' +
                        '<div class=\"flex justify-end gap-2\">' +
                        '  <button type=\"button\" class=\"swift-cancel px-4 py-2 rounded-xl border text-sm font-medium ' +
                        (isDark
                            ? 'border-blue-700 text-slate-200 hover:bg-blue-900/40'
                            : 'border-slate-300 text-slate-700 hover:bg-slate-50') +
                        '\">Cancel</button>' +
                        '  <button type=\"button\" class=\"swift-ok px-4 py-2 rounded-xl bg-red-600 text-sm font-medium text-white hover:bg-red-700\">Delete</button>' +
                        '</div>';

                    overlay.appendChild(box);
                    document.body.appendChild(overlay);

                    overlay.querySelector('.swift-cancel').addEventListener('click', function () {
                        overlay.remove();
                    });
                    overlay.querySelector('.swift-ok').addEventListener('click', function () {
                        overlay.remove();
                        var row = form.closest('tr') || form.closest('[data-delete-row]');
                        var csrf = window.csrfToken || (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
                        fetch(form.action, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            },
                            body: new FormData(form)
                        })
                        .then(function (r) {
                            var ctype = (r.headers.get('content-type') || '').toLowerCase();
                            if (ctype.indexOf('application/json') !== -1) {
                                return r.json().catch(function () { return {}; }).then(function (d) {
                                    return { ok: r.ok, data: d };
                                });
                            }
                            // If endpoint does not support JSON, fall back to normal submit.
                            if (!r.ok) throw new Error('Delete failed');
                            return { ok: true, data: {} };
                        })
                        .then(function (res) {
                            if (!res.ok) throw new Error((res.data && res.data.message) || 'Delete failed');
                            if (window.showAppToast) window.showAppToast((res.data && res.data.message) ? res.data.message : 'Deleted.');
                            fadeOutAndRemove(row, function () {
                                if (typeof window.refreshMainContentNow === 'function') window.refreshMainContentNow();
                                else if (typeof window.refreshMainContent === 'function') window.refreshMainContent(true);
                                if (typeof window.refreshHeaderNotifications === 'function') window.refreshHeaderNotifications();
                                if (typeof window.refreshNotificationsPage === 'function') window.refreshNotificationsPage();
                            });
                        })
                        .catch(function () {
                            form.submit();
                        });
                    });
                }
            }, true);
        })();
    </script>
    @auth
    <script>
        (function () {
            document.addEventListener('click', function (e) {
                var btn = e.target.closest ? e.target.closest('.js-logout-button') : null;
                if (!btn) return;
                e.preventDefault();
                var form = document.getElementById('logout-form');
                if (!form) return;

                var overlay = document.createElement('div');
                overlay.className = 'fixed inset-0 z-[9999] flex items-center justify-center bg-black/40';

                var isDark = document.documentElement.classList.contains('dark');
                var box = document.createElement('div');
                box.className = isDark
                    ? 'max-w-sm w-full rounded-2xl bg-[#0b1020] shadow-xl border border-blue-900/70 p-5 text-slate-100'
                    : 'max-w-sm w-full rounded-2xl bg-white shadow-xl border border-slate-200 p-5 text-slate-800';
                box.innerHTML =
                    '<h2 class="text-base font-semibold mb-2">Log out?</h2>' +
                    '<p class="text-sm ' + (isDark ? 'text-slate-300' : 'text-slate-600') + ' mb-4">Are you sure you want to log out?</p>' +
                    '<div class="flex justify-end gap-2">' +
                    '  <button type="button" class="swift-cancel px-4 py-2 rounded-xl border text-sm font-medium ' +
                    (isDark
                        ? 'border-blue-700 text-slate-200 hover:bg-blue-900/40'
                        : 'border-slate-300 text-slate-700 hover:bg-slate-50') +
                    '">Cancel</button>' +
                    '  <button type="button" class="swift-ok px-4 py-2 rounded-xl bg-red-600 text-sm font-medium text-white hover:bg-red-700">Log out</button>' +
                    '</div>';

                overlay.appendChild(box);
                document.body.appendChild(overlay);

                overlay.querySelector('.swift-cancel').addEventListener('click', function () {
                    overlay.remove();
                });
                overlay.querySelector('.swift-ok').addEventListener('click', function () {
                    overlay.remove();
                    form.submit();
                });
            }, true);
        })();
    </script>
    @endauth
    @auth
    <script>
        window.userSidebarCollapsed = @json(auth()->user()->sidebar_collapsed);
        window.settingsSidebarUrl = @json(route('settings.sidebar'));
        window.csrfToken = @json(csrf_token());
        window.headerNotificationsUrl = @json(route('notifications.header'));
    </script>
    @endauth
    <script>
        (function() {
            var STORAGE_KEY = 'sidebar-collapsed';
            var panel = document.getElementById('sidebar-panel');
            var collapseBtn = document.getElementById('sidebar-collapse-btn');
            var labelBrandBtn = document.getElementById('sidebar-label-brand');
            var railBrandBtn = document.getElementById('sidebar-rail-brand');
            var mobileBackdrop = document.getElementById('sidebar-mobile-backdrop');
            var toggleArrow = collapseBtn && collapseBtn.querySelector('.sidebar-toggle-arrow');
            var toggleLines = collapseBtn && collapseBtn.querySelectorAll('.sidebar-toggle-line1,.sidebar-toggle-line2,.sidebar-toggle-line3');
            function isCollapsed() { return panel && panel.classList.contains('collapsed'); }
            function isMobileViewport() { return window.matchMedia('(max-width: 1023px)').matches; }
            var railLogo = document.getElementById('sidebar-rail-logo') || document.querySelector('.sidebar-rail-logo');
            function syncMobileSidebarState() {
                var openOnMobile = isMobileViewport() && !isCollapsed();
                document.body.classList.toggle('mobile-sidebar-open', openOnMobile);
                if (mobileBackdrop) mobileBackdrop.setAttribute('aria-hidden', openOnMobile ? 'false' : 'true');
            }
            function setCollapsed(collapsed, persistToServer) {
                if (!panel) return;
                panel.classList.toggle('collapsed', collapsed);
                // Animate the toggle icon
                if (toggleArrow) toggleArrow.style.transform = collapsed ? 'rotate(0deg)' : 'rotate(180deg)';
                if (toggleLines) toggleLines.forEach(function(l, i) {
                    l.style.opacity = collapsed ? '0' : '1';
                    l.style.width = collapsed ? '0' : (i === 0 ? '100%' : i === 1 ? '70%' : '50%');
                });
                // Show rail logo only when collapsed, hide when expanded
                if (railLogo) { railLogo.style.visibility = collapsed ? 'visible' : 'hidden'; }
                // Rail badge only visible when collapsed
                if (typeof syncItHelpBadge === 'function') syncItHelpBadge();
                if (collapseBtn) {
                    collapseBtn.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
                    collapseBtn.setAttribute('title', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
                }
                syncMobileSidebarState();
                try { localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0'); } catch (e) {}
                if (typeof window.updateSidebarGlider === 'function') setTimeout(window.updateSidebarGlider, 280);
                if (persistToServer && typeof window.settingsSidebarUrl === 'string' && window.settingsSidebarUrl && typeof window.csrfToken === 'string') {
                    fetch(window.settingsSidebarUrl, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({ collapsed: collapsed })
                    }).catch(function() {});
                }
            }
            function initCollapse() {
                if (!panel) return;
                var collapsed = false;
                if (typeof window.userSidebarCollapsed === 'boolean') {
                    collapsed = window.userSidebarCollapsed;
                } else {
                    try { var stored = localStorage.getItem(STORAGE_KEY); collapsed = stored === '1'; } catch (e) {}
                }
                if (isMobileViewport()) collapsed = true;
                setCollapsed(collapsed, false);
            }
            if (collapseBtn) {
                collapseBtn.addEventListener('click', function() { setCollapsed(!isCollapsed(), true); });
            }
            function handleBrandToggle(e) {
                if (!panel) return;
                e.preventDefault();
                setCollapsed(!isCollapsed(), true);
            }
            if (labelBrandBtn) {
                labelBrandBtn.addEventListener('click', handleBrandToggle);
            }
            if (railBrandBtn) {
                railBrandBtn.addEventListener('click', handleBrandToggle);
            }
            if (mobileBackdrop) {
                mobileBackdrop.addEventListener('click', function() {
                    if (!isCollapsed()) setCollapsed(true, false);
                });
            }
            if (panel) {
                panel.addEventListener('click', function(e) {
                    if (!isMobileViewport()) return;
                    if (isCollapsed()) return;
                    if (e.target.closest('#sidebar-collapse-btn')) return;
                    var navTap = e.target.closest('.sidebar-link, .sidebar-rail-icon, a[href]');
                    if (!navTap) return;
                    setTimeout(function() { setCollapsed(true, false); }, 40);
                });
            }
            window.addEventListener('resize', function() {
                syncMobileSidebarState();
            });
            initCollapse();

            // ── Active state: PHP emits the current nav key, JS applies it ──
            (function() {
                var currentKey = '{{ $navKey }}';
                document.querySelectorAll('[data-nav]').forEach(function(el) {
                    var active = el.dataset.nav === currentKey;
                    el.classList.toggle('active', active);
                    if (active) el.setAttribute('aria-current', 'page');
                    else        el.removeAttribute('aria-current');
                });
                if (typeof updateSidebarGlider === 'function') updateSidebarGlider();
            })();
            // Wire IT staff rail buttons to the same handlers as label panel buttons
            var helpRail = document.getElementById('it-help-request-btn-rail');
            var helpLabel = document.getElementById('it-help-request-btn');
            var catRail = document.getElementById('it-categories-btn-rail');
            var catLabel = document.getElementById('it-categories-btn');
            if (helpRail && helpLabel) {
                helpRail.addEventListener('click', function() { helpLabel.click(); });
            }
            if (catRail && catLabel) {
                catRail.addEventListener('click', function() { catLabel.click(); });
            }
            // Sync IT help badge between rail and label panel
            var badgeLabel = document.getElementById('it-help-badge');
            var badgeRail = document.getElementById('it-help-badge-rail');
            function syncItHelpBadge() {
                if (!badgeLabel || !badgeRail) return;
                badgeRail.textContent = badgeLabel.textContent;
                badgeRail.className = badgeLabel.className.replace('it-help-badge', 'it-help-badge-rail');
                var hasCount = (badgeLabel.textContent || '').trim().length > 0 && !badgeLabel.classList.contains('hidden');
                if (!hasCount) {
                    badgeRail.classList.add('hidden');
                }
                // Rail badge only visible when sidebar is collapsed
                if (!isCollapsed()) { badgeRail.style.display = 'none'; }
                else { badgeRail.style.display = ''; }
            }
            if (badgeLabel && badgeRail) {
                var observer = new MutationObserver(syncItHelpBadge);
                observer.observe(badgeLabel, { attributes: true, childList: true, characterData: true, subtree: true });
                syncItHelpBadge();
            }
        })();
        // Sidebar rail tooltips — animated preview cards when collapsed
        (function() {
            var panel = document.getElementById('sidebar-panel');
            function isCollapsed() { return panel && panel.classList.contains('collapsed'); }
            function allowHoverTips() {
                if (window.matchMedia('(max-width: 1023px)').matches) return false;
                if (window.matchMedia('(hover: none)').matches) return false;
                if (window.matchMedia('(pointer: coarse)').matches) return false;
                return true;
            }

            // Preview definitions per tooltip key
            var previews = {
                'Dashboard': {
                    color: '#3b82f6',
                    html: '<div style="display:flex;flex-direction:column;gap:4px">' +
                        '<div style="display:flex;gap:3px">' +
                            '<div class="tip-anim-pulse" style="flex:1;height:18px;background:#dbeafe;border-radius:4px;display:flex;align-items:center;justify-content:center"><span style="font-size:9px;color:#1d4ed8;font-weight:700">9</span></div>' +
                            '<div class="tip-anim-pulse" style="flex:1;height:18px;background:#fef3c7;border-radius:4px;animation-delay:.2s;display:flex;align-items:center;justify-content:center"><span style="font-size:9px;color:#92400e;font-weight:700">4</span></div>' +
                        '</div>' +
                        '<div style="height:3px;background:#e2e8f0;border-radius:2px;overflow:hidden"><div class="tip-anim-bar" style="height:100%;width:60%;background:#3b82f6;border-radius:2px"></div></div>' +
                        '<div style="height:3px;background:#e2e8f0;border-radius:2px;overflow:hidden"><div class="tip-anim-bar" style="height:100%;width:35%;background:#f59e0b;border-radius:2px;animation-delay:.3s"></div></div>' +
                    '</div>'
                },
                'All tickets': {
                    color: '#6366f1',
                    html: '<div style="display:flex;flex-direction:column;gap:3px">' +
                        '<div class="tip-anim-slide" style="height:8px;background:#e0e7ff;border-radius:3px;display:flex;align-items:center;padding:0 4px;gap:3px"><div style="width:4px;height:4px;background:#6366f1;border-radius:50%"></div><div style="flex:1;height:2px;background:#c7d2fe;border-radius:1px"></div></div>' +
                        '<div class="tip-anim-slide" style="height:8px;background:#fef3c7;border-radius:3px;display:flex;align-items:center;padding:0 4px;gap:3px;animation-delay:.15s"><div style="width:4px;height:4px;background:#f59e0b;border-radius:50%"></div><div style="flex:1;height:2px;background:#fde68a;border-radius:1px"></div></div>' +
                        '<div class="tip-anim-slide" style="height:8px;background:#d1fae5;border-radius:3px;display:flex;align-items:center;padding:0 4px;gap:3px;animation-delay:.3s"><div style="width:4px;height:4px;background:#10b981;border-radius:50%"></div><div style="flex:1;height:2px;background:#a7f3d0;border-radius:1px"></div></div>' +
                    '</div>'
                },
                'Tickets': {
                    color: '#6366f1',
                    html: '<div style="display:flex;flex-direction:column;gap:3px">' +
                        '<div class="tip-anim-slide" style="height:8px;background:#e0e7ff;border-radius:3px;display:flex;align-items:center;padding:0 4px;gap:3px"><div style="width:4px;height:4px;background:#6366f1;border-radius:50%"></div><div style="flex:1;height:2px;background:#c7d2fe;border-radius:1px"></div></div>' +
                        '<div class="tip-anim-slide" style="height:8px;background:#fef3c7;border-radius:3px;display:flex;align-items:center;padding:0 4px;gap:3px;animation-delay:.2s"><div style="width:4px;height:4px;background:#f59e0b;border-radius:50%"></div><div style="flex:1;height:2px;background:#fde68a;border-radius:1px"></div></div>' +
                    '</div>'
                },
                'My logged tickets': {
                    color: '#8b5cf6',
                    html: '<div style="display:flex;flex-direction:column;gap:3px">' +
                        '<div class="tip-anim-slide" style="height:8px;background:#ede9fe;border-radius:3px;display:flex;align-items:center;padding:0 4px;gap:3px"><div style="width:4px;height:4px;background:#8b5cf6;border-radius:50%"></div><div style="flex:1;height:2px;background:#ddd6fe;border-radius:1px"></div></div>' +
                        '<div class="tip-anim-slide" style="height:8px;background:#ede9fe;border-radius:3px;display:flex;align-items:center;padding:0 4px;gap:3px;animation-delay:.2s"><div style="width:4px;height:4px;background:#8b5cf6;border-radius:50%"></div><div style="flex:1;height:2px;background:#ddd6fe;border-radius:1px"></div></div>' +
                    '</div>'
                },
                'Users': {
                    color: '#f59e0b',
                    html: '<div style="display:flex;gap:3px;align-items:flex-end">' +
                        '<div class="tip-anim-pulse" style="width:12px;height:12px;background:#fde68a;border-radius:50%;display:flex;align-items:center;justify-content:center"><span style="font-size:8px">👤</span></div>' +
                        '<div class="tip-anim-pulse" style="width:12px;height:12px;background:#fde68a;border-radius:50%;display:flex;align-items:center;justify-content:center;animation-delay:.2s"><span style="font-size:8px">👤</span></div>' +
                        '<div class="tip-anim-pulse" style="width:12px;height:12px;background:#fde68a;border-radius:50%;display:flex;align-items:center;justify-content:center;animation-delay:.4s"><span style="font-size:8px">👤</span></div>' +
                        '<div style="margin-left:2px;font-size:9px;color:#92400e;font-weight:700;align-self:center">4</div>' +
                    '</div>'
                },
                'Staff announcements': {
                    color: '#ec4899',
                    html: '<div style="display:flex;flex-direction:column;gap:3px">' +
                        '<div class="tip-anim-slide" style="height:9px;background:#fce7f3;border-radius:3px;padding:0 4px;display:flex;align-items:center;gap:2px"><div class="tip-anim-pulse" style="width:4px;height:4px;background:#ec4899;border-radius:50%"></div><div style="flex:1;height:2px;background:#fbcfe8;border-radius:1px"></div></div>' +
                        '<div style="height:9px;background:#fce7f3;border-radius:3px;padding:0 4px;display:flex;align-items:center;gap:2px;opacity:.5"><div style="width:4px;height:4px;background:#ec4899;border-radius:50%"></div><div style="flex:1;height:2px;background:#fbcfe8;border-radius:1px"></div></div>' +
                    '</div>'
                },
                'Audit trail': {
                    color: '#64748b',
                    html: '<div style="display:flex;flex-direction:column;gap:2px">' +
                        '<div class="tip-anim-slide" style="display:flex;gap:3px;align-items:center"><div style="width:3px;height:3px;background:#22c55e;border-radius:50%"></div><div style="flex:1;height:2px;background:#e2e8f0;border-radius:1px"></div><div style="font-size:8px;color:#64748b">✓</div></div>' +
                        '<div class="tip-anim-slide" style="display:flex;gap:3px;align-items:center;animation-delay:.15s"><div style="width:3px;height:3px;background:#3b82f6;border-radius:50%"></div><div style="flex:1;height:2px;background:#e2e8f0;border-radius:1px"></div><div style="font-size:8px;color:#64748b">✓</div></div>' +
                        '<div class="tip-anim-slide" style="display:flex;gap:3px;align-items:center;animation-delay:.3s"><div style="width:3px;height:3px;background:#f59e0b;border-radius:50%"></div><div style="flex:1;height:2px;background:#e2e8f0;border-radius:1px"></div><div style="font-size:8px;color:#64748b">✓</div></div>' +
                    '</div>'
                },
                'Help request': {
                    color: '#ef4444',
                    html: '<div style="display:flex;flex-direction:column;gap:3px">' +
                        '<div style="display:flex;gap:3px"><div style="flex:1;height:8px;background:#fee2e2;border-radius:3px;padding:0 3px;display:flex;align-items:center"><div style="flex:1;height:2px;background:#fca5a5;border-radius:1px"></div></div></div>' +
                        '<div style="display:flex;justify-content:flex-end"><div class="tip-anim-pulse" style="width:20px;height:8px;background:#ef4444;border-radius:3px;display:flex;align-items:center;justify-content:center"><span style="font-size:7px;color:#fff;font-weight:700">Reply</span></div></div>' +
                    '</div>'
                },
                'Customize form': {
                    color: '#8b5cf6',
                    html: '<div style="display:flex;flex-direction:column;gap:3px">' +
                        '<div style="display:flex;gap:2px"><div class="tip-anim-pulse" style="flex:1;height:6px;background:#ede9fe;border-radius:2px"></div><div class="tip-anim-pulse" style="flex:1;height:6px;background:#ddd6fe;border-radius:2px;animation-delay:.2s"></div></div>' +
                        '<div style="display:flex;gap:2px"><div class="tip-anim-pulse" style="flex:1;height:6px;background:#ddd6fe;border-radius:2px;animation-delay:.1s"></div><div class="tip-anim-pulse" style="flex:1;height:6px;background:#ede9fe;border-radius:2px;animation-delay:.3s"></div></div>' +
                    '</div>'
                }
            };

            var tip = null;
            var hideTimer = null;

            function createTip(label, rect) {
                if (tip) tip.remove();
                var def = previews[label];
                tip = document.createElement('div');
                tip.style.cssText = 'position:fixed;z-index:2147483647;pointer-events:none;' +
                    'background:#fff;border:1px solid #e2e8f0;border-radius:10px;' +
                    'box-shadow:0 8px 24px rgba(0,0,0,.15);padding:8px 10px;min-width:90px;' +
                    'opacity:0;transform:translateX(-4px);transition:opacity .15s ease,transform .15s ease;';
                var accentColor = def ? def.color : '#1e293b';
                tip.innerHTML =
                    '<div style="font-size:10px;font-weight:700;color:' + accentColor + ';margin-bottom:5px;letter-spacing:.02em">' + label + '</div>' +
                    (def ? def.html : '') +
                    '<style>' +
                    '@keyframes tip-pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(1.15)}}' +
                    '@keyframes tip-slide{0%{transform:translateX(-3px);opacity:.6}50%{transform:translateX(0);opacity:1}100%{transform:translateX(-3px);opacity:.6}}' +
                    '@keyframes tip-bar{0%{width:10%}60%{width:100%}100%{width:10%}}' +
                    '.tip-anim-pulse{animation:tip-pulse 1.8s ease-in-out infinite}' +
                    '.tip-anim-slide{animation:tip-slide 2s ease-in-out infinite}' +
                    '.tip-anim-bar{animation:tip-bar 2s ease-in-out infinite}' +
                    '</style>';
                document.body.appendChild(tip);
                // Position to the right of the icon
                var top = rect.top + rect.height / 2;
                var left = rect.right + 10;
                tip.style.top = '0px'; tip.style.left = '0px';
                requestAnimationFrame(function() {
                    var h = tip.offsetHeight;
                    tip.style.top = Math.max(8, Math.min(window.innerHeight - h - 8, top - h / 2)) + 'px';
                    tip.style.left = left + 'px';
                    tip.style.opacity = '1';
                    tip.style.transform = 'translateX(0)';
                });
            }

            function hideTip() {
                if (tip) {
                    tip.style.opacity = '0';
                    tip.style.transform = 'translateX(-4px)';
                    var t = tip;
                    setTimeout(function() { if (t.parentNode) t.parentNode.removeChild(t); }, 150);
                    tip = null;
                }
            }

            document.addEventListener('mouseover', function(e) {
                if (!allowHoverTips()) return;
                var link = e.target.closest('[data-tooltip]');
                if (!link) return;
                if (!isCollapsed()) return;
                clearTimeout(hideTimer);
                var label = link.getAttribute('data-tooltip');
                if (!label) return;
                createTip(label, link.getBoundingClientRect());
            });
            document.addEventListener('mouseout', function(e) {
                if (!allowHoverTips()) return;
                var link = e.target.closest('[data-tooltip]');
                if (!link) return;
                hideTimer = setTimeout(hideTip, 80);
            });
            document.addEventListener('click', function() {
                if (!allowHoverTips()) hideTip();
            });
        })();
        // Header live search — global pages + tickets (dashboard only)
        (function() {
            var input = document.getElementById('header-search');
            var dropdown = document.getElementById('header-search-dropdown');
            if (!input || !dropdown) return;
            var form = document.getElementById('header-search-form');
            var staticLinks = @json($headerSearchLinks ?? []);

            var allTickets = [];
            var activeIndex = -1;
            var lastResults = [];

            var statusColors = {
                open: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                in_progress: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
                resolved: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                closed: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                cancelled: 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-300',
            };
            var statusLabels = {
                open: 'Open', in_progress: 'In Progress', resolved: 'Resolved',
                closed: 'Closed', cancelled: 'Cancelled'
            };

            // Preload tickets once for quick matching.
            setTimeout(function() {
                fetch('{{ route('tickets.search') }}?q=&all=1', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                }).then(function(r) { return r.json(); }).then(function(data) {
                    allTickets = data;
                }).catch(function() {});
            }, 300);

            function show() { dropdown.classList.remove('hidden'); }
            function hide() { dropdown.classList.add('hidden'); activeIndex = -1; }

            // Highlight matching substring in text
            function highlight(text, q) {
                if (!q) return escHtml(text);
                var idx = text.toLowerCase().indexOf(q.toLowerCase());
                if (idx === -1) return escHtml(text);
                return escHtml(text.slice(0, idx)) +
                    '<mark class="bg-yellow-200 dark:bg-yellow-600/50 text-inherit rounded px-0">' +
                    escHtml(text.slice(idx, idx + q.length)) + '</mark>' +
                    escHtml(text.slice(idx + q.length));
            }

            function escHtml(s) {
                return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
            }

            function scoreTicket(t, q) {
                var ql = q.toLowerCase();
                var numL = t.ticket_number.toLowerCase();
                var titL = t.title.toLowerCase();
                if (numL.includes(ql)) return 2;
                if (titL.includes(ql)) return 1;
                // fuzzy: all chars of q appear in order in title
                var ci = 0;
                for (var i = 0; i < titL.length && ci < ql.length; i++) {
                    if (titL[i] === ql[ci]) ci++;
                }
                return ci === ql.length ? 0.5 : 0;
            }
            function scoreLink(p, q) {
                var ql = q.toLowerCase();
                var t = (p.title || '').toLowerCase();
                var k = (p.keywords || '').toLowerCase();
                if (t.includes(ql)) return 3;
                if (k.includes(ql)) return 1.5;
                return 0;
            }

            function filterAndRender(q) {
                if (!q) { hide(); return; }
                var pageMatches = staticLinks
                    .map(function(p) { return { kind: 'page', item: p, s: scoreLink(p, q) }; })
                    .filter(function(x) { return x.s > 0; })
                    .sort(function(a, b) { return b.s - a.s; })
                    .slice(0, 6);
                var ticketMatches = allTickets
                    .map(function(t) { return { kind: 'ticket', item: t, s: scoreTicket(t, q) }; })
                    .filter(function(x) { return x.s > 0; })
                    .sort(function(a, b) { return b.s - a.s; })
                    .slice(0, 6);
                var items = pageMatches.concat(ticketMatches).slice(0, 10);
                lastResults = items;
                activeIndex = -1;

                if (!items.length) {
                    dropdown.innerHTML = '<p class="px-4 py-3 text-sm text-slate-400 dark:text-slate-500">No results found.</p>';
                    show(); return;
                }

                dropdown.innerHTML = items.map(function(x, i) {
                    if (x.kind === 'page') {
                        var p = x.item;
                        var pTitle = highlight(p.title || '', q);
                        return '<button type="button" data-kind="page" data-url="' + escHtml(p.url || '#') + '" data-idx="' + i + '" class="search-result-item w-full text-left flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-colors">' +
                            '<span class="shrink-0 text-[11px] font-medium px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200">Page</span>' +
                            '<span class="flex-1 text-sm text-slate-800 dark:text-slate-100 truncate">' + pTitle + '</span>' +
                            '</button>';
                    }
                    var t = x.item;
                    var color = statusColors[t.status] || statusColors.closed;
                    var label = statusLabels[t.status] || t.status;
                    var numHtml = highlight(t.ticket_number, q);
                    var titleHtml = highlight(t.title, q);
                    return '<button type="button" data-kind="ticket" data-modal-url="' + t.url + '" data-idx="' + i + '" class="search-result-item w-full text-left flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-colors">' +
                        '<span class="shrink-0 text-xs font-mono text-slate-400 dark:text-slate-500 w-20">' + numHtml + '</span>' +
                        '<span class="flex-1 text-sm text-slate-800 dark:text-slate-100 truncate">' + titleHtml + '</span>' +
                        '<span class="shrink-0 text-[11px] font-medium px-2 py-0.5 rounded-full ' + color + '">' + label + '</span>' +
                        '</button>';
                }).join('');
                show();
            }

            function setActive(idx) {
                var items = dropdown.querySelectorAll('.search-result-item');
                items.forEach(function(el, i) {
                    el.classList.toggle('bg-slate-50', i === idx);
                    el.classList.toggle('dark:bg-slate-700/60', i === idx);
                });
                activeIndex = idx;
            }

            input.addEventListener('input', function() {
                var raw = input.value;
                if (/^\d+$/.test(raw)) { input.value = 'TKT-' + raw; }
                filterAndRender(input.value.trim());
            });

            input.addEventListener('keydown', function(e) {
                var items = dropdown.querySelectorAll('.search-result-item');
                if (e.key === 'ArrowDown') {
                    e.preventDefault(); setActive(Math.min(activeIndex + 1, items.length - 1));
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault(); setActive(Math.max(activeIndex - 1, 0));
                } else if (e.key === 'Enter' && activeIndex >= 0) {
                    e.preventDefault();
                    if (items[activeIndex]) items[activeIndex].click();
                } else if (e.key === 'Enter' && items.length > 0) {
                    e.preventDefault();
                    items[0].click();
                } else if (e.key === 'Escape') {
                    hide();
                }
            });
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    var first = dropdown.querySelector('.search-result-item');
                    if (first) first.click();
                });
            }

            document.addEventListener('click', function(e) {
                if (!input.contains(e.target) && !dropdown.contains(e.target)) hide();
            });

            // Open modal when clicking a search result
            dropdown.addEventListener('click', function(e) {
                var btn = e.target.closest('.search-result-item');
                if (!btn) return;
                hide();
                input.value = '';
                var kind = btn.getAttribute('data-kind') || 'ticket';
                if (kind === 'page') {
                    var href = btn.getAttribute('data-url');
                    if (href) window.location.href = href;
                    return;
                }
                var url = btn.getAttribute('data-modal-url');
                if (!url) return;
                var fakeBtn = document.createElement('button');
                fakeBtn.className = 'ticket-quick-view-btn';
                fakeBtn.setAttribute('data-url', url);
                fakeBtn.style.display = 'none';
                document.body.appendChild(fakeBtn);
                fakeBtn.click();
                document.body.removeChild(fakeBtn);
            });

            input.addEventListener('focus', function() {
                if (input.value.trim().length >= 1 && lastResults.length) show();
            });
        })();
        (function() {
            var mainEl = document.getElementById('app-main-content');
            if (!mainEl) return;
            var cache = {};
            var cacheKeys = [];
            var cacheMax = 25;
            var prefetchTimer = null;
            var pendingLoad = null;
            var loadingTimer = null;
            var currentController = null;
            var currentLoadHref = null;
            function cachePut(href, parsed) {
                if (cache[href]) return;
                while (cacheKeys.length >= cacheMax && cacheKeys.length > 0) {
                    var old = cacheKeys.shift();
                    delete cache[old];
                }
                cacheKeys.push(href);
                cache[href] = parsed;
            }
            function setLoading(show) {
                if (!mainEl) return;
                // Visual loading overlay disabled for snappier feel; only timer bookkeeping remains.
                if (!show && loadingTimer) { clearTimeout(loadingTimer); loadingTimer = null; }
            }
            function updateSidebarActive() {
                var path = window.location.pathname.replace(/^\//, ''); // e.g. "home", "tickets", "tickets/5"
                var search = window.location.search || '';
                var hasMine = search.indexOf('mine=1') !== -1;
                var key = 'none';
                if (path === 'home') {
                    key = 'home';
                } else if (path.indexOf('tickets') === 0) {
                    key = hasMine ? 'tickets-mine' : 'tickets';
                } else if (path.indexOf('users') === 0) {
                    key = 'users';
                } else if (path.indexOf('admin/staff-announcements') === 0) {
                    key = 'announcements';
                } else if (path.indexOf('admin/audit-trail') === 0) {
                    key = 'audit';
                }
                document.querySelectorAll('[data-nav]').forEach(function(el) {
                    var active = el.dataset.nav === key;
                    el.classList.toggle('active', active);
                    if (active) el.setAttribute('aria-current', 'page');
                    else        el.removeAttribute('aria-current');
                });
                if (typeof updateSidebarGlider === 'function') updateSidebarGlider();
            }
            function updateSidebarGlider() {
                var nav = document.getElementById('sidebar-nav-root');
                var wrap = document.querySelector('.sidebar-nav-wrap');
                var glider = document.getElementById('sidebar-glider');
                var active = document.querySelector('.sidebar-link.active');
                if (!wrap || !glider) return;
                if (!active) {
                    glider.style.top = '';
                    glider.style.height = '';
                    glider.style.display = 'none';
                    return;
                }
                var wrapRect = wrap.getBoundingClientRect();
                var activeRect = active.getBoundingClientRect();
                var scrollTop = nav ? nav.scrollTop : 0;
                var top = activeRect.top - wrapRect.top + scrollTop;
                var height = activeRect.height;
                glider.style.display = '';
                glider.style.top = top + 'px';
                glider.style.height = height + 'px';
            }
            function updateHeaderSearchVisibility(href) {
                var form = document.getElementById('header-search-form');
                var spacer = document.getElementById('header-search-spacer');
                if (!form || !spacer) return;
                try {
                    var url = new URL(href, window.location.origin);
                    var path = url.pathname.replace(/\/$/, '') || '/';
                    var showSearch = (path === '/home');
                    form.classList.toggle('hidden', !showSearch);
                    spacer.classList.toggle('hidden', showSearch);
                } catch (e) {}
            }
            function destroyDataTablesIn(el) {
                if (!el || typeof jQuery === 'undefined' || !jQuery.fn.dataTable || !jQuery.fn.dataTable.isDataTable) return;
                el.querySelectorAll('table.display[id]').forEach(function(table) {
                    try {
                        if (jQuery.fn.dataTable.isDataTable(table)) jQuery(table).DataTable().destroy();
                    } catch (err) {}
                });
            }
            window.destroyDataTablesIn = destroyDataTablesIn;
            function applyContent(content, title, href, pushState) {
                setLoading(false);
                if (pushState) history.pushState({ url: href }, '', href);
                destroyDataTablesIn(mainEl);
                mainEl.innerHTML = content;
                document.title = title;
                updateSidebarActive();
                updateHeaderSearchVisibility(href);
                if (typeof window.syncThemeRadios === 'function') window.syncThemeRadios();
                currentLoadHref = null;
                requestAnimationFrame(function() {
                    if (typeof window.initDataTablesInContent === 'function') window.initDataTablesInContent();
                    if (typeof window.initAdminDashboardChartsIfPresent === 'function') window.initAdminDashboardChartsIfPresent();
                    if (typeof window.initDashboardCharts === 'function') window.initDashboardCharts();
                });
            }
            function parseResponse(html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var newMain = doc.getElementById('app-main-content');
                var newTitle = doc.querySelector('title');
                if (!newMain) return null;
                return { content: newMain.innerHTML, title: newTitle ? newTitle.textContent : document.title };
            }
            function loadPage(href, pushState) {
                if (!href || href.indexOf(window.location.origin) !== 0) return;
                if (currentController) { currentController.abort(); currentController = null; }
                currentLoadHref = href;
                var cached = cache[href];
                if (cached) {
                    applyContent(cached.content, cached.title, href, pushState);
                    return;
                }
                var p = pendingLoad && pendingLoad.href === href ? pendingLoad.promise : null;
                if (p) {
                    pendingLoad = null;
                    loadingTimer = setTimeout(function() { setLoading(true); }, 0);
                    p.then(function(parsed) {
                        if (parsed && currentLoadHref === href) {
                            cachePut(href, parsed);
                            applyContent(parsed.content, parsed.title, href, pushState);
                        } else if (!parsed) { setLoading(false); window.location.href = href; }
                    }).catch(function() { if (currentLoadHref === href) { setLoading(false); window.location.href = href; } });
                    return;
                }
                loadingTimer = setTimeout(function() { setLoading(true); }, 0);
                currentController = new AbortController();
                var signal = currentController.signal;
                fetch(href, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, credentials: 'same-origin', signal: signal })
                    .then(function(r) {
                        if (r.redirected) {
                            setLoading(false);
                            window.location.href = r.url;
                            return null;
                        }
                        if (!r.ok) throw new Error(r.status);
                        return r.text();
                    })
                    .then(function(html) {
                        if (!html || currentLoadHref !== href) return;
                        var parsed = parseResponse(html);
                        if (!parsed) { setLoading(false); window.location.href = href; return; }
                        cachePut(href, parsed);
                        applyContent(parsed.content, parsed.title, href, pushState);
                    })
                    .catch(function(err) {
                        if (err && err.name === 'AbortError') return;
                        setLoading(false);
                        window.location.href = href;
                    });
            }
            function prefetch(href) {
                if (!href || href.indexOf(window.location.origin) !== 0 || cache[href]) return;
                try { if (new URL(href).pathname.indexOf('/logout') !== -1) return; } catch (err) {}
                fetch(href, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, credentials: 'same-origin' })
                    .then(function(r) { if (r.redirected || !r.ok) throw new Error(); return r.text(); })
                    .then(function(html) {
                        var parsed = parseResponse(html);
                        if (parsed) cachePut(href, parsed);
                    })
                    .catch(function() {});
            }
            function startLoadOnMouseDown(href) {
                if (!href || href.indexOf(window.location.origin) !== 0 || cache[href]) return;
                try { if (new URL(href).pathname.indexOf('/logout') !== -1) return; } catch (err) {}
                if (pendingLoad && pendingLoad.href === href) return;
                pendingLoad = { href: href, promise: fetch(href, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, credentials: 'same-origin' })
                    .then(function(r) { if (r.redirected || !r.ok) throw new Error(); return r.text(); })
                    .then(function(html) { var p = parseResponse(html); if (p) cachePut(href, p); return p; })
                    .catch(function() { return null; }) };
            }
            document.addEventListener('click', function(e) {
                var trigger = e.target.closest('.quick-actions-fab-trigger');
                if (trigger) {
                    var wrap = trigger.closest('.quick-actions-fab-wrap');
                    if (wrap) {
                        e.preventDefault();
                        // Popup-only FAB: delegate to the popup open logic
                        var popupOnlyBtn = wrap.querySelector('.qa-popup-only-url');
                        if (popupOnlyBtn) {
                            var popupUrl = popupOnlyBtn.getAttribute('data-popup-only-url');
                            var popupEl = wrap.querySelector('.quick-actions-inline-popup');
                            var popupBodyEl = wrap.querySelector('[id$="-popup-body"]');
                            // Delegate to local script if available
                            if (typeof wrap._qaOpenPopup === 'function' || typeof wrap._qaClosePopup === 'function') {
                                if (popupEl && popupEl.classList.contains('is-open')) {
                                    if (typeof wrap._qaClosePopup === 'function') wrap._qaClosePopup();
                                } else {
                                    if (typeof wrap._qaOpenPopup === 'function') wrap._qaOpenPopup(popupUrl);
                                }
                                return;
                            }
                            if (popupEl && popupEl.classList.contains('is-open')) {
                                popupEl.classList.remove('is-open');
                                popupEl.setAttribute('aria-hidden', 'true');
                                wrap.classList.remove('is-open');
                            } else if (popupUrl && popupEl && popupBodyEl) {
                                wrap.classList.add('is-open');
                                popupEl.classList.add('is-open');
                                popupEl.setAttribute('aria-hidden', 'false');
                                // Clear badge immediately on open
                                var fabBadgeEl = wrap.querySelector('.qa-fab-badge');
                                if (fabBadgeEl) fabBadgeEl.classList.add('hidden');
                                popupBodyEl.innerHTML = '<div class="text-sm text-slate-500 py-4 text-center">Loading…</div>';
                                fetch(popupUrl + '?_=' + Date.now(), { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html', 'Cache-Control': 'no-cache' } })
                                    .then(function(r) { return r.ok ? r.text() : Promise.reject(); })
                                    .then(function(html) { popupBodyEl.innerHTML = html; })
                                    .catch(function() { popupBodyEl.innerHTML = '<div class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">Could not load.</div>'; });
                            }
                            return;
                        }
                    }
                    return;
                }
                var backdrop = e.target.closest('.quick-actions-fab-backdrop');
                if (backdrop) {
                    var wrap = document.querySelector('.quick-actions-fab-wrap.is-open');
                    if (wrap) {
                        wrap.classList.remove('is-open');
                        var t = wrap.querySelector('.quick-actions-fab-trigger');
                        if (t) t.setAttribute('aria-expanded', 'false');
                    }
                    return;
                }
                var a = e.target.closest('a[href]');
                if (!a || a.target === '_blank' || a.hasAttribute('download')) return;
                if (a.hasAttribute('data-full-reload')) return;
                if (a.getAttribute('href') === '#' || a.getAttribute('href') === '' || (a.getAttribute('href') || '').indexOf('javascript:') === 0) return;
                var href = a.href;
                if (!href || href.indexOf(window.location.origin) !== 0) return;
                if (a.closest('form')) return;
                try { if (new URL(href).pathname.indexOf('/logout') !== -1) return; } catch (err) {}
                e.preventDefault();
                loadPage(href, true);
            }, true);
            document.addEventListener('mousedown', function(e) {
                var a = e.target.closest('a[href]');
                if (!a || a.target === '_blank' || a.hasAttribute('download') || a.hasAttribute('data-full-reload')) return;
                var href = a.href;
                if (!href || href.indexOf(window.location.origin) !== 0) return;
                try { if (new URL(href).pathname.indexOf('/logout') !== -1) return; } catch (err) {}
                startLoadOnMouseDown(href);
            }, true);
            document.addEventListener('mouseover', function(e) {
                var a = e.target.closest('a[href]');
                if (!a || a.target === '_blank' || a.hasAttribute('download') || a.hasAttribute('data-full-reload')) return;
                var href = a.href;
                if (!href || href.indexOf(window.location.origin) !== 0) return;
                try { if (new URL(href).pathname.indexOf('/logout') !== -1) return; } catch (err) {}
                prefetch(href);
            }, true);
            document.addEventListener('focus', function(e) {
                var a = e.target.closest('a[href]');
                if (!a || a.target === '_blank' || a.hasAttribute('download') || a.hasAttribute('data-full-reload')) return;
                var href = a.href;
                if (!href || href.indexOf(window.location.origin) !== 0) return;
                try { if (new URL(href).pathname.indexOf('/logout') !== -1) return; } catch (err) {}
                prefetch(href);
            }, true);
            window.addEventListener('popstate', function() {
                loadPage(window.location.href, false);
            });
            function prefetchSidebarLinks() {
                var links = document.querySelectorAll('.sidebar-panel a[href]');
                for (var i = 0; i < links.length; i++) {
                    var a = links[i];
                    if (a.target === '_blank' || a.hasAttribute('download') || a.hasAttribute('data-full-reload')) continue;
                    var href = a.href;
                    if (!href || href.indexOf(window.location.origin) !== 0) continue;
                    try { if (new URL(href).pathname.indexOf('/logout') !== -1) continue; } catch (err) {}
                    prefetch(href);
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() { setTimeout(prefetchSidebarLinks, 350); });
            } else {
                setTimeout(prefetchSidebarLinks, 350);
            }
            // Aggressively prefetch high-traffic account pages so they open faster
            @auth
            var accountPrefetch = [
                @json(route('settings')),
                @json(route('settings.security')),
                @json(route('profile.edit'))
            ];
            accountPrefetch.forEach(function(href) {
                try { prefetch(href); } catch (e) {}
            });
            @endauth
            window.updateSidebarGlider = updateSidebarGlider;
            updateSidebarGlider();
            setTimeout(function() { updateSidebarGlider(); }, 350);
        })();
        (function() {
            var mainEl = document.getElementById('app-main-content');
            if (!mainEl) return;
            var refreshTimer = null;
            var lastRefreshAt = 0;
            function getRefreshSeconds() {
                var el = mainEl.querySelector('[data-auto-refresh]');
                if (!el) return 0;
                var n = parseInt(el.getAttribute('data-auto-refresh'), 10);
                return (n > 0 && n <= 300) ? n : 0;
            }
            function showUpdatedToast() {
                var existing = document.getElementById('auto-refresh-toast');
                if (existing) existing.remove();
                var t = document.createElement('div');
                t.id = 'auto-refresh-toast';
                t.setAttribute('role', 'status');
                t.className = 'fixed top-6 right-6 z-[100] rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-lg dark:bg-slate-800 dark:border-slate-600 dark:text-slate-200';
                t.textContent = 'Updated just now';
                document.body.appendChild(t);
                setTimeout(function() {
                    t.style.opacity = '0';
                    t.style.transition = 'opacity 0.3s ease';
                    setTimeout(function() { t.remove(); }, 320);
                }, 2000);
            }
            function doRefresh(force, options) {
                options = options || {};
                if (!force && document.visibilityState !== 'visible') return;
                if (!force) {
                    var active = document.activeElement;
                    if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT' || active.isContentEditable)) return;
                }
                // Prevent duplicate back-to-back refreshes from overlapping poll + broadcast triggers.
                if (!options.bypassCooldown && Date.now() - lastRefreshAt < 1200) return;
                var href = window.location.href;
                if (href.indexOf('/logout') !== -1) return;
                lastRefreshAt = Date.now();
                var url = href + (href.indexOf('?') === -1 ? '?' : '&') + '_=' + Date.now();
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, credentials: 'same-origin', cache: 'no-store' })
                    .then(function(r) { if (!r.ok) throw new Error(); return r.text(); })
                    .then(function(html) {
                        var doc = new DOMParser().parseFromString(html, 'text/html');
                        var newMain = doc.getElementById('app-main-content');
                        var newTitle = doc.querySelector('title');
                        if (!newMain) return; // redirect or error — keep current content
                        if (typeof window.destroyDataTablesIn === 'function') window.destroyDataTablesIn(mainEl);
                        mainEl.innerHTML = newMain.innerHTML;
                        if (newTitle) document.title = newTitle.textContent;
                        if (typeof window.updateSidebarGlider === 'function') window.updateSidebarGlider();
                        if (typeof window.syncThemeRadios === 'function') window.syncThemeRadios();
                        // Only show toast on explicit force refresh (not polling-triggered)
                        if (force && typeof showUpdatedToast === 'function') showUpdatedToast();
                        if (typeof window.refreshHeaderNotifications === 'function') window.refreshHeaderNotifications();
                        requestAnimationFrame(function() {
                            if (typeof window.initDataTablesInContent === 'function') window.initDataTablesInContent();
                            if (typeof window.initAdminDashboardChartsIfPresent === 'function') window.initAdminDashboardChartsIfPresent();
                            if (typeof window.initDashboardCharts === 'function') window.initDashboardCharts();
                        });
                    })
                    .catch(function() {});
            }
            function scheduleNext() {
                var sec = getRefreshSeconds();
                if (sec <= 0) {
                    if (refreshTimer) { clearInterval(refreshTimer); refreshTimer = null; }
                    return;
                }
                if (!refreshTimer) {
                    refreshTimer = setInterval(function() {
                        if (getRefreshSeconds() <= 0) return;
                        doRefresh();
                    }, sec * 1000);
                }
            }
            document.addEventListener('DOMContentLoaded', function() {
                scheduleNext();
            });
            if (document.readyState !== 'loading') scheduleNext();
            window.refreshMainContent = function(force) { doRefresh(!!force); };
            window.refreshMainContentNow = function() { doRefresh(true, { bypassCooldown: true }); };
        })();
    </script>
    @auth
    {{-- Polling fallback: when on tickets page, poll for list changes so new tickets appear without reload (even if Reverb is off) --}}
    <script>
        (function() {
            var url = @json(route('tickets.updated-at'));
            var lastKnown = null;
            var pollInterval = 5000; // faster ticket sync for all roles when websocket is unavailable
            function isTicketsPage() {
                var p = window.location.pathname;
                return p === '/tickets' || p === '/home' || p === '/';
            }
            function poll() {
                if (!isTicketsPage()) return;
                fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                    .then(function(r) { return r.ok ? r.json() : Promise.reject(); })
                    .then(function(data) {
                        var t = data.updated_at || 0;
                        // Only refresh if we already had a known value AND it changed
                        if (lastKnown !== null && t !== lastKnown && isTicketsPage()) {
                            if (typeof window.refreshMainContentNow === 'function') window.refreshMainContentNow();
                            else if (typeof window.refreshMainContent === 'function') window.refreshMainContent(true);
                            if (typeof window.refreshHeaderNotifications === 'function') window.refreshHeaderNotifications();
                        }
                        lastKnown = t;
                    })
                    .catch(function() {});
            }
            // Prime state immediately so first remote change is not missed.
            poll();
            setTimeout(function run() {
                if (document.visibilityState === 'visible') poll();
                setTimeout(run, pollInterval);
            }, pollInterval);
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible') poll();
            });
        })();
    </script>
    {{-- Notification bell live poll — all roles, every 30s regardless of page --}}
    <script>
        (function() {
            var notifUrl = window.headerNotificationsUrl || null;
            if (!notifUrl) return;
            var pollInterval = 30000;
            function pollNotif() {
                if (typeof window.refreshHeaderNotifications === 'function') {
                    window.refreshHeaderNotifications();
                }
            }
            setTimeout(function run() {
                if (document.visibilityState === 'visible') pollNotif();
                setTimeout(run, pollInterval);
            }, pollInterval);
            // Also refresh when tab becomes visible again after being hidden
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible') pollNotif();
            });
        })();
    </script>
    {{-- Staff announcement-specific fallback: keep dashboard banner in sync even without websocket --}}
    <script>
        (function() {
            var url = @json(route('staff-announcements.version'));
            var lastVersion = null;
            var pollInterval = 3000; // faster banner sync for announcement add/delete
            function isAnnouncementsAwarePage() {
                var p = window.location.pathname;
                return p === '/home' || p === '/' || p.indexOf('/admin/staff-announcements') === 0;
            }
            function poll() {
                if (!isAnnouncementsAwarePage()) return;
                fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                    .then(function(r) { return r.ok ? r.json() : Promise.reject(); })
                    .then(function(data) {
                        var v = (data && data.version) ? String(data.version) : '0';
                        if (lastVersion !== null && v !== lastVersion && isAnnouncementsAwarePage()) {
                            if (typeof window.refreshMainContentNow === 'function') window.refreshMainContentNow();
                            else if (typeof window.refreshMainContent === 'function') window.refreshMainContent(true);
                            if (typeof window.refreshHeaderNotifications === 'function') window.refreshHeaderNotifications();
                        }
                        lastVersion = v;
                    })
                    .catch(function() {});
            }
            poll();
            setTimeout(function run() {
                if (document.visibilityState === 'visible') poll();
                setTimeout(run, pollInterval);
            }, pollInterval);
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible') poll();
            });
        })();
    </script>
    @if(config('broadcasting.default') === 'reverb' && config('broadcasting.connections.reverb.key'))
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js" crossorigin="anonymous"></script>
    <script>
        (function initReverb() {
            if (typeof Pusher === 'undefined') {
                setTimeout(initReverb, 50);
                return;
            }
            var key = @json(config('broadcasting.connections.reverb.key'));
            var host = @json(config('broadcasting.connections.reverb.options.host') ?: '127.0.0.1');
            var port = @json(config('broadcasting.connections.reverb.options.port') ?: 8080);
            var scheme = @json(config('broadcasting.connections.reverb.options.scheme') ?: 'http');
            var useTLS = scheme === 'https';
            if (!key) return;
            try {
                var pusher = new Pusher(key, {
                    cluster: 'mt1',
                    wsHost: host || '127.0.0.1',
                    wsPort: port || 8080,
                    wssPort: port || 8080,
                    forceTLS: useTLS,
                    enabledTransports: ['ws', 'wss'],
                    authEndpoint: '/broadcasting/auth',
                    auth: { headers: { 'X-CSRF-TOKEN': window.csrfToken || '' } }
                });
                window.__reverbPusher = pusher;
                var channel = pusher.subscribe('tickets');
                function onTicketsUpdated(data) {
                    if (typeof window.refreshMainContentNow === 'function') window.refreshMainContentNow();
                    else if (typeof window.refreshMainContent === 'function') window.refreshMainContent(true);
                    if (typeof window.refreshHeaderNotifications === 'function') window.refreshHeaderNotifications();
                    if (typeof window.refreshNotificationsPage === 'function') window.refreshNotificationsPage();
                    // If ticket modal is open, refresh it too
                    var qvModal = document.getElementById('ticket-quick-view-modal');
                    if (qvModal && !qvModal.classList.contains('hidden') && typeof window.__ticketModalRefresh === 'function') {
                        window.__ticketModalRefresh();
                    }
                }
                channel.bind('TicketsUpdated', onTicketsUpdated);
                channel.bind('.TicketsUpdated', onTicketsUpdated);
            } catch (e) { console.warn('Reverb connection failed:', e); }
        })();
    </script>
    @endif
    @endauth
    @stack('scripts')

    @auth
    {{-- Chart.js for dashboard charts (needed for AJAX navigation too) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
    <script>
    (function() {
        function getChartsData() {
            var el = document.getElementById('dashboard-charts-json');
            if (!el) return null;
            try { return JSON.parse(el.textContent || el.innerText || '{}'); } catch (e) { return null; }
        }

        function destroyExisting() {
            if (!window.__dashboardChartInstances) window.__dashboardChartInstances = [];
            window.__dashboardChartInstances.forEach(function(ch) { try { ch && ch.destroy && ch.destroy(); } catch (e) {} });
            window.__dashboardChartInstances = [];
        }

        function ensureChartJs(ready) {
            if (typeof Chart !== 'undefined') { ready(); return; }
            var tries = 0;
            (function wait() {
                tries++;
                if (typeof Chart !== 'undefined') return ready();
                if (tries > 40) return; // ~4s max
                setTimeout(wait, 100);
            })();
        }

        function setupResizeObserver() {
            if (window.__dashboardChartsResizeObserver) return;
            if (typeof ResizeObserver === 'undefined') return;
            window.__dashboardChartsResizeObserver = new ResizeObserver(function() {
                if (!window.__dashboardChartInstances) return;
                window.__dashboardChartInstances.forEach(function(ch) { try { ch && ch.resize && ch.resize(); } catch (e) {} });
            });
            document.querySelectorAll('.dashboard-chart-card').forEach(function(card) {
                try { window.__dashboardChartsResizeObserver.observe(card); } catch (e) {}
            });
        }

        window.initDashboardCharts = function() {
            var data = getChartsData();
            if (!data) return;
            var monthlyCtx = document.getElementById('chart-monthly');
            var categoryCtx = document.getElementById('chart-category');
            if (!monthlyCtx || !categoryCtx) return;

            ensureChartJs(function() {
                destroyExisting();

                // Theme-aware chart colors for both light and dark modes.
                var isDark = document.documentElement.classList.contains('dark');
                var gridColor   = isDark ? 'rgba(148,163,184,0.22)' : 'rgba(148,163,184,0.35)';
                var tickColor   = isDark ? 'rgba(226,232,240,0.75)' : 'rgba(51,65,85,0.85)';
                var ringEmpty   = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(148,163,184,0.22)';
                var segmentBorder = isDark ? 'rgba(15,23,42,0.92)' : 'rgba(255,255,255,0.95)';
                var tooltipBg   = 'rgba(15,23,42,0.92)';

                // Status infographic is now rendered as inline SVG in dashboard-charts.blade.php.

                // Monthly line
                var monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                var backendLabels = (data.monthly && data.monthly.labels) ? data.monthly.labels : [];
                var backendData = (data.monthly && data.monthly.data) ? data.monthly.data : [];
                var now = new Date();
                var year = now.getFullYear();
                var currentMonthIndex = now.getMonth();
                var realByKey = {};
                for (var i = 0; i < backendLabels.length; i++) { realByKey[backendLabels[i]] = Number(backendData[i]) || 0; }
                var labels = monthNames.slice(0, currentMonthIndex + 1);
                var series = [];
                for (var m = 0; m <= currentMonthIndex; m++) {
                    var key = year + '-' + String(m + 1).padStart(2, '0');
                    series.push(realByKey[key] !== undefined ? realByKey[key] : (30 + m * 5));
                }
                var thisMonthIndex = series.length - 1;
                var currentVal = series[thisMonthIndex] != null ? Number(series[thisMonthIndex]) : 0;
                var prevVal = thisMonthIndex > 0 ? Number(series[thisMonthIndex - 1]) : null;
                var trendPct = prevVal !== null && prevVal > 0 ? Math.round((currentVal - prevVal) / prevVal * 100) : null;
                var valueEl = document.getElementById('monthly-ticker-value');
                var trendEl = document.getElementById('monthly-ticker-trend');
                if (valueEl) valueEl.textContent = currentVal;
                if (trendEl) {
                    if (trendPct === null) { trendEl.textContent = ''; trendEl.className = 'text-sm font-semibold tabular-nums'; }
                    else { var isUp = trendPct >= 0; trendEl.textContent = (isUp ? '\u2191 ' : '\u2193 ') + Math.abs(trendPct) + '% vs last month'; trendEl.className = 'text-sm font-semibold tabular-nums ' + (isUp ? 'text-emerald-600' : 'text-red-600'); }
                }
                var secondaryData = series.map(function(v) { return Math.round(Number(v) * 0.45); });
                var thisMonthPlugin = { id: 'thisMonthLine', afterDraw: function(chart) {
                    if (!chart.scales.x || thisMonthIndex < 0) return;
                    var x = chart.scales.x.getPixelForValue(labels[thisMonthIndex], thisMonthIndex);
                    if (x < chart.chartArea.left || x > chart.chartArea.right) return;
                    var c = chart.ctx;
                    c.save();
                    c.strokeStyle = 'rgba(59, 130, 246, 0.7)';
                    c.lineWidth = 2;
                    c.setLineDash([8, 6]);
                    c.beginPath();
                    c.moveTo(x, chart.chartArea.top);
                    c.lineTo(x, chart.chartArea.bottom);
                    c.stroke();
                    c.setLineDash([]);
                    c.font = '600 10px system-ui, sans-serif';
                    c.fillStyle = '#3b82f6';
                    c.textAlign = 'center';
                    c.fillText('This month', x, chart.chartArea.top - 6);
                    c.restore();
                }};
                window.__dashboardChartInstances.push(new Chart(monthlyCtx, {
                    type: 'line',
                    data: { labels: labels, datasets: [
                        { label: 'Tickets', data: series, borderColor: '#3b82f6', backgroundColor: 'transparent', borderWidth: 2, fill: false, tension: 0.35, pointBackgroundColor: '#3b82f6', pointBorderColor: isDark ? '#1e293b' : '#fff', pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6 },
                        { label: 'Comparison', data: secondaryData, borderColor: '#475569', backgroundColor: 'transparent', borderWidth: 1.5, borderDash: [6, 4], fill: false, tension: 0.35, pointBackgroundColor: '#475569', pointBorderColor: isDark ? 'rgba(255,255,255,0.15)' : '#ffffff', pointBorderWidth: 1, pointRadius: 3, pointHoverRadius: 5 }
                    ]},
                    options: { responsive: true, maintainAspectRatio: false, layout: { padding: { top: 18 } }, animation: { duration: 1600, delay: 400, easing: 'easeOutQuart' }, scales: { x: { grid: { color: gridColor }, ticks: { maxRotation: 45, minRotation: 45, color: tickColor, font: { size: 11 } } }, y: { beginAtZero: true, precision: 0, grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 11 } } } }, plugins: { legend: { display: false }, tooltip: { backgroundColor: tooltipBg, padding: 10 }, thisMonthLine: {} } },
                    plugins: [thisMonthPlugin]
                }));

                // Category bars
                var catLabels = (data.category && data.category.labels) ? data.category.labels : [];
                var catData = (data.category && data.category.data) ? data.category.data : [];
                var palette = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1', '#14b8a6'];
                var backgroundColor = catLabels.map(function(_, i) { return palette[i % palette.length]; });
                window.__dashboardChartInstances.push(new Chart(categoryCtx, {
                    type: 'bar',
                    data: { labels: catLabels, datasets: [{ label: 'Tickets', data: catData, backgroundColor: backgroundColor, borderRadius: 6, maxBarThickness: 32, borderSkipped: false }] },
                    options: { responsive: true, maintainAspectRatio: false, animation: { duration: 1400, delay: 300, easing: 'easeOutQuart' }, scales: { x: { grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 11 }, maxRotation: 45, minRotation: 45 } }, y: { beginAtZero: true, precision: 0, grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 11 } } } }, plugins: { legend: { display: false }, tooltip: { backgroundColor: tooltipBg, padding: 10 } } }
                }));

                setupResizeObserver();
                // Extra nudge after layout changes (sidebar collapse, DataTables, etc.)
                setTimeout(function() {
                    if (!window.__dashboardChartInstances) return;
                    window.__dashboardChartInstances.forEach(function(ch) { try { ch && ch.resize && ch.resize(); } catch (e) {} });
                }, 120);
            });
        };

        function initSoon() {
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    if (typeof window.initDashboardCharts === 'function') window.initDashboardCharts();
                });
            });
        }

        // Ensure charts render on initial full page load (first-login case).
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initSoon);
        } else {
            initSoon();
        }
    })();
    </script>
    @endauth

    <script>
    (function() {
        // Global toast helper
        var toastRoot = document.getElementById('app-toast-root');
        window.showAppToast = function(message, options) {
            if (!toastRoot) return;
            options = options || {};
            var toast = document.createElement('div');
            toast.className = 'app-toast';

            var iconWrap = document.createElement('div');
            iconWrap.className = 'app-toast-icon';
            iconWrap.innerHTML = '<svg class="h-4 w-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>';

            var textWrap = document.createElement('div');
            textWrap.textContent = message || 'New ticket activity';

            var closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'app-toast-close';
            closeBtn.innerHTML = '&times;';
            closeBtn.addEventListener('click', function() {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            });

            toast.appendChild(iconWrap);
            toast.appendChild(textWrap);
            toast.appendChild(closeBtn);

            toastRoot.appendChild(toast);

            setTimeout(function() {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, options.duration || 3600);
        };
    })();
    </script>
    <script>
    (function() {
        function fadeOutAnnouncementCard(form) {
            if (!form) return;
            var card = form.closest('[data-announcement-card]');
            if (!card) return;
            card.style.transition = 'opacity .24s ease, transform .24s ease';
            card.style.opacity = '0';
            card.style.transform = 'translateY(-6px) scale(.985)';
            setTimeout(function() {
                if (card && card.parentNode) card.parentNode.removeChild(card);
            }, 250);
        }

        // Staff announcement acknowledge submit via AJAX so all roles stay live without full reload.
        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (!form || !form.classList || !form.classList.contains('js-announcement-ack-form')) return;
            e.preventDefault();

            var btn = form.querySelector('.js-announcement-ack-btn') || form.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.dataset.prevText = btn.textContent;
                btn.textContent = 'Saving...';
            }

            var csrf = window.csrfToken || (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
            fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: new FormData(form)
            })
            .then(function(r) { return r.json().then(function(d) { if (!r.ok) throw d; return d; }); })
            .then(function(data) {
                fadeOutAnnouncementCard(form);
                if (window.showAppToast) window.showAppToast(data.message || 'Announcement acknowledged.');
                setTimeout(function() {
                    if (typeof window.refreshMainContentNow === 'function') window.refreshMainContentNow();
                    else if (typeof window.refreshMainContent === 'function') window.refreshMainContent(true);
                    if (typeof window.refreshHeaderNotifications === 'function') window.refreshHeaderNotifications();
                    if (typeof window.refreshNotificationsPage === 'function') window.refreshNotificationsPage();
                }, 260);
            })
            .catch(function(err) {
                var msg = (err && err.message) ? err.message : 'Could not acknowledge announcement.';
                if (window.showAppToast) window.showAppToast(msg);
            })
            .finally(function() {
                if (!btn) return;
                btn.disabled = false;
                btn.textContent = btn.dataset.prevText || 'Done';
            });
        }, true);
    })();
    </script>

    @auth
    {{-- Submit ticket modal (form rendered inline – no fetch needed) --}}
    @if(auth()->user()->isAdmin() || auth()->user()->isFrontDesk())
    @php
        $__stCats  = $_submitTicketCategories ?? collect();
        $__stPrios = $_submitTicketPriorities ?? collect();
        $__stUser  = auth()->user();
        $__stSources = [];
        if ($__stUser->isAdmin() || $__stUser->isFrontDesk()) {
            $__stSources[] = \App\Models\Ticket::SOURCE_PHONE;
            $__stSources[] = \App\Models\Ticket::SOURCE_WALK_IN;
        }
        if ($__stUser->isAdmin()) {
            $__stSources[] = \App\Models\Ticket::SOURCE_SELF_SERVICE;
        }
        $__stDefault = in_array(\App\Models\Ticket::SOURCE_SELF_SERVICE, $__stSources)
            ? \App\Models\Ticket::SOURCE_SELF_SERVICE
            : ($__stSources[0] ?? \App\Models\Ticket::SOURCE_PHONE);
    @endphp
    <div id="submit-ticket-modal" class="fixed inset-0 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="z-index:2147483647" aria-modal="true" role="dialog" aria-labelledby="submit-ticket-modal-title">
        <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white text-slate-900 shadow-2xl overflow-hidden dark:border-blue-800 dark:bg-[#0b1020] dark:text-slate-100">
            <div class="sticky top-0 z-10 flex items-center justify-between gap-3 border-b border-slate-200 bg-white px-5 py-4 backdrop-blur dark:border-blue-800 dark:bg-[#0f172a]">
                <div class="min-w-0">
                    <h2 id="submit-ticket-modal-title" class="truncate text-base font-semibold text-slate-900 dark:text-blue-50">Create ticket</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-blue-200/80">Create a new support ticket</p>
                </div>
                <button type="button" id="submit-ticket-modal-close" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-blue-700 dark:text-blue-100 dark:hover:bg-blue-900/50" aria-label="Close">&times;</button>
            </div>
            <div class="submit-ticket-modal-body-scroll max-h-[80vh] overflow-y-auto p-5 sm:p-6 dark:bg-[#0b1020]">
                <form id="submit-ticket-form" action="{{ route('tickets.store') }}" method="post" class="space-y-4 dark:[color-scheme:dark]">
                    @csrf
                    <input type="hidden" name="from_modal" value="1">
                    <div id="submit-ticket-error" class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

                    @if(count($__stSources) > 1)
                    <div>
                        <label for="st_source" class="mb-1 block text-xs font-medium text-slate-600 dark:text-blue-100">Source</label>
                        <select name="source" id="st_source" class="input-ticket bg-white text-slate-900 dark:border-blue-700 dark:bg-[#111a33] dark:text-slate-100 dark:placeholder:text-blue-200/60" required>
                            @if(in_array(\App\Models\Ticket::SOURCE_PHONE, $__stSources))
                                <option value="phone" {{ $__stDefault === 'phone' ? 'selected' : '' }}>Phone Call</option>
                            @endif
                            @if(in_array(\App\Models\Ticket::SOURCE_WALK_IN, $__stSources))
                                <option value="walk_in" {{ $__stDefault === 'walk_in' ? 'selected' : '' }}>Walk-in</option>
                            @endif
                            @if(in_array(\App\Models\Ticket::SOURCE_SELF_SERVICE, $__stSources))
                                <option value="self_service" {{ $__stDefault === 'self_service' ? 'selected' : '' }}>Self-service</option>
                            @endif
                        </select>
                    </div>
                    @else
                        <input type="hidden" name="source" value="{{ $__stSources[0] ?? 'self_service' }}">
                    @endif

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="st_requester_name" class="mb-1 block text-xs font-medium text-slate-600 dark:text-blue-100">Requester name <span class="text-red-500">*</span></label>
                            <input type="text" name="requester_name" id="st_requester_name" required class="input-ticket bg-white text-slate-900 placeholder-slate-400 dark:border-blue-700 dark:bg-[#111a33] dark:text-slate-100 dark:placeholder:text-blue-200/60" placeholder="Full name">
                        </div>
                        <div>
                            <label for="st_country_code" class="mb-1 block text-xs font-medium text-slate-600 dark:text-blue-100">Contact number <span class="text-red-500">*</span></label>
                            <input type="hidden" name="requester_phone" id="st_requester_phone" value="">
                            <div class="flex items-center rounded-xl border border-slate-300 bg-white px-2 py-1.5 text-slate-900 shadow-sm focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-200 dark:border-blue-700 dark:bg-[#101a33] dark:text-blue-50 dark:focus-within:border-blue-500 dark:focus-within:ring-blue-500/30">
                                <div class="relative shrink-0">
                                    <input type="hidden" id="st_country_code" value="+63">
                                    <button type="button" id="st_country_picker_btn" class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-blue-100 dark:hover:bg-[#0f172a]" aria-haspopup="listbox" aria-expanded="false">
                                        <span id="st_country_picker_value">PH</span>
                                        <span aria-hidden="true">▼</span>
                                    </button>
                                    <div id="st_country_dropdown" class="absolute left-0 top-[calc(100%+0.35rem)] z-30 hidden w-[19rem] rounded-xl border border-slate-200 bg-white p-2 shadow-xl dark:border-blue-700 dark:bg-[#111a33]">
                                        <input type="text" id="st_country_search" class="input-ticket mb-2 bg-white text-slate-900 placeholder-slate-400 dark:border-blue-700 dark:bg-[#0f172a] dark:text-slate-100 dark:placeholder:text-blue-200/60" placeholder="Search country or code">
                                        <div id="st_country_list" class="max-h-56 overflow-y-auto"></div>
                                    </div>
                                </div>
                                <span class="mx-1 h-6 w-px bg-slate-200 dark:bg-blue-800/70"></span>
                                <span id="st_country_code_prefix" class="shrink-0 text-base font-semibold text-slate-400 dark:text-blue-200/70">+63</span>
                                <input type="tel" inputmode="numeric" pattern="[0-9]*" id="st_requester_phone_local" maxlength="15" required class="ml-1 min-w-0 flex-1 border-0 bg-transparent p-0 text-base font-medium text-slate-800 placeholder-slate-400 outline-none focus:ring-0 dark:text-blue-50 dark:placeholder:text-blue-200/60" placeholder="9XXXXXXXXX">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="st_title" class="mb-1 block text-xs font-medium text-slate-600 dark:text-blue-100">Subject <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="st_title" required class="input-ticket bg-white text-slate-900 placeholder-slate-400 dark:border-blue-700 dark:bg-[#111a33] dark:text-slate-100 dark:placeholder:text-blue-200/60" placeholder="Brief description of the issue">
                    </div>

                    <div>
                        <label for="st_location" class="mb-1 block text-xs font-medium text-slate-600 dark:text-blue-100">Location <span class="text-red-500">*</span></label>
                        <input type="text" name="location" id="st_location" required class="input-ticket bg-white text-slate-900 placeholder-slate-400 dark:border-blue-700 dark:bg-[#111a33] dark:text-slate-100 dark:placeholder:text-blue-200/60" placeholder="Room / building / floor">
                    </div>

                    <div>
                        <label for="st_scheduled_for" class="mb-1 block text-xs font-medium text-slate-600 dark:text-blue-100">Scheduled date &amp; time <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="scheduled_for" id="st_scheduled_for" required class="input-ticket bg-white text-slate-900 dark:border-blue-700 dark:bg-[#111a33] dark:text-slate-100">
                    </div>

                    <div>
                        <label for="st_description" class="mb-1 block text-xs font-medium text-slate-600 dark:text-blue-100">Description (optional)</label>
                        <textarea name="description" id="st_description" rows="4" class="input-ticket min-h-[100px] resize-y bg-white text-slate-900 placeholder-slate-400 dark:border-blue-700 dark:bg-[#111a33] dark:text-slate-100 dark:placeholder:text-blue-200/60" placeholder="Describe the issue in detail…"></textarea>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="st_category_id" class="mb-1 block text-xs font-medium text-slate-600 dark:text-blue-100">Category <span class="text-red-500">*</span></label>
                            <select name="category_id" id="st_category_id" required class="input-ticket bg-white text-slate-900 dark:border-blue-700 dark:bg-[#111a33] dark:text-slate-100">
                                <option value="">— Choose category —</option>
                                @foreach($__stCats as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="st_priority" class="mb-1 block text-xs font-medium text-slate-600 dark:text-blue-100">Priority <span class="text-red-500">*</span></label>
                            <select name="priority" id="st_priority" required class="input-ticket bg-white text-slate-900 dark:border-blue-700 dark:bg-[#111a33] dark:text-slate-100">
                                @foreach($__stPrios as $p)
                                    <option value="{{ $p->key }}" {{ $p->key === 'normal' ? 'selected' : '' }}>
                                        {{ $p->label }}{{ $p->description ? ' – ' . $p->description : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-2 dark:border-blue-800/70">
                        <button type="button" id="submit-ticket-cancel"
                                class="cursor-pointer transition-all bg-gray-700 text-white px-5 py-2 rounded-xl border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold inline-flex items-center justify-center min-h-10 disabled:opacity-60">
                            Cancel
                        </button>
                        <button type="submit" id="submit-ticket-submit"
                                class="cursor-pointer transition-all bg-gray-700 text-white px-5 py-2 rounded-xl border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold inline-flex items-center justify-center min-h-10 disabled:opacity-60">
                            Create ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
    (function() {
        var modal   = document.getElementById('submit-ticket-modal');
        var form    = document.getElementById('submit-ticket-form');
        var errBox  = document.getElementById('submit-ticket-error');
        var closeBtn = document.getElementById('submit-ticket-modal-close');
        var cancelBtn = document.getElementById('submit-ticket-cancel');
        var submitBtn = document.getElementById('submit-ticket-submit');
        if (!modal || !form) return;

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            var first = form.querySelector('input:not([type=hidden]), select, textarea');
            if (first) setTimeout(function() { first.focus(); }, 50);
            // Enforce digits-only on local phone field
            var phoneEl = document.getElementById('st_requester_phone');
            var phoneLocalEl = document.getElementById('st_requester_phone_local');
            if (phoneLocalEl && !phoneLocalEl.__phoneGuard) {
                phoneLocalEl.__phoneGuard = true;
                phoneLocalEl.addEventListener('input', function() {
                    phoneLocalEl.value = (phoneLocalEl.value || '').replace(/\D/g, '').slice(0, 15);
                    updatePhonePreview();
                });
                phoneLocalEl.addEventListener('paste', function() {
                    setTimeout(function() {
                        phoneLocalEl.value = (phoneLocalEl.value || '').replace(/\D/g, '').slice(0, 15);
                        updatePhonePreview();
                    }, 0);
                });
                phoneLocalEl.addEventListener('keydown', function(e) {
                    var allowed = ['Backspace','Delete','ArrowLeft','ArrowRight','ArrowUp','ArrowDown','Tab','Home','End'];
                    if (allowed.indexOf(e.key) !== -1 || (e.ctrlKey || e.metaKey)) return;
                    if (!/^\d$/.test(e.key)) e.preventDefault();
                });
            }
            if (phoneEl && !phoneEl.__countryInit) {
                phoneEl.__countryInit = true;
                initCountryCodes();
                updatePhonePreview();
            }
        }
        function flagFromIso2(iso2) {
            if (!iso2 || iso2.length !== 2) return '';
            return iso2.toUpperCase().replace(/./g, function(ch) {
                return String.fromCodePoint(127397 + ch.charCodeAt(0));
            });
        }
        function fallbackCountries() {
            return [
                { iso2: 'PH', name: 'Philippines', dial: '+63' },
                { iso2: 'US', name: 'United States', dial: '+1' },
                { iso2: 'GB', name: 'United Kingdom', dial: '+44' },
                { iso2: 'AU', name: 'Australia', dial: '+61' },
                { iso2: 'CA', name: 'Canada', dial: '+1' },
                { iso2: 'SG', name: 'Singapore', dial: '+65' },
                { iso2: 'JP', name: 'Japan', dial: '+81' },
                { iso2: 'KR', name: 'South Korea', dial: '+82' },
                { iso2: 'IN', name: 'India', dial: '+91' },
                { iso2: 'DE', name: 'Germany', dial: '+49' },
                { iso2: 'FR', name: 'France', dial: '+33' },
                { iso2: 'AE', name: 'United Arab Emirates', dial: '+971' },
            ];
        }
        function updatePhonePreview() {
            var countryEl = document.getElementById('st_country_code');
            var phoneLocalEl = document.getElementById('st_requester_phone_local');
            var phoneEl = document.getElementById('st_requester_phone');
            var previewEl = document.getElementById('st_phone_preview');
            if (!countryEl || !phoneLocalEl || !phoneEl) return;

            var dial = countryEl.value || '';
            var local = (phoneLocalEl.value || '').replace(/\D/g, '');
            // Normalize PH local format: allow users typing 09XXXXXXXXX, store as +63 9XXXXXXXXX.
            if (dial === '+63' && local.length === 11 && local.indexOf('0') === 0) {
                local = local.slice(1);
                phoneLocalEl.value = local;
            }
            var full = local ? (dial + ' ' + local) : '';
            phoneEl.value = full;

            if (previewEl) {
                previewEl.textContent = full ? ('Will save as: ' + full) : 'Choose country and enter local number.';
            }
        }
        var allCountries = [];
        var selectedCountry = null;
        function getCountryShortLabel(c) {
            return (c.iso2 || '').toUpperCase();
        }
        function setSelectedCountry(c) {
            if (!c) return;
            selectedCountry = c;
            var countryHidden = document.getElementById('st_country_code');
            var valueEl = document.getElementById('st_country_picker_value');
            var prefixEl = document.getElementById('st_country_code_prefix');
            if (countryHidden) countryHidden.value = c.dial;
            if (valueEl) valueEl.textContent = getCountryShortLabel(c);
            if (prefixEl) prefixEl.textContent = c.dial;
            updatePhonePreview();
        }
        function closeCountryDropdown() {
            var dd = document.getElementById('st_country_dropdown');
            var btn = document.getElementById('st_country_picker_btn');
            if (dd) dd.classList.add('hidden');
            if (btn) btn.setAttribute('aria-expanded', 'false');
        }
        function openCountryDropdown() {
            var dd = document.getElementById('st_country_dropdown');
            var btn = document.getElementById('st_country_picker_btn');
            var search = document.getElementById('st_country_search');
            if (dd) dd.classList.remove('hidden');
            if (btn) btn.setAttribute('aria-expanded', 'true');
            if (search) setTimeout(function() { search.focus(); }, 0);
        }
        function filterCountryList(query) {
            var q = (query || '').trim().toLowerCase();
            if (!q) return allCountries;
            return allCountries.filter(function(c) {
                return c.name.toLowerCase().indexOf(q) !== -1
                    || c.iso2.toLowerCase().indexOf(q) !== -1
                    || c.dial.toLowerCase().indexOf(q) !== -1;
            });
        }
        function renderCountryList(items) {
            var listEl = document.getElementById('st_country_list');
            if (!listEl) return;
            if (!items.length) {
                listEl.innerHTML = '<div class="px-2 py-1.5 text-xs text-slate-500 dark:text-blue-200/70">No country found.</div>';
                return;
            }
            listEl.innerHTML = items.map(function(c) {
                return '<button type="button" data-iso2="' + c.iso2 + '" class="st-country-item flex w-full items-center justify-between rounded-lg px-2 py-1.5 text-left text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-100 dark:hover:bg-[#0f172a]">'
                    + '<span>' + flagFromIso2(c.iso2) + ' ' + c.name + '</span>'
                    + '<span class="text-xs text-slate-500 dark:text-blue-200/70">' + c.dial + '</span>'
                    + '</button>';
            }).join('');
        }
        function wireCountryPicker() {
            var btn = document.getElementById('st_country_picker_btn');
            var search = document.getElementById('st_country_search');
            var listEl = document.getElementById('st_country_list');
            if (!btn || !search || !listEl || btn.__countryPickerWired) return;
            btn.__countryPickerWired = true;
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var isOpen = btn.getAttribute('aria-expanded') === 'true';
                if (isOpen) closeCountryDropdown();
                else openCountryDropdown();
            });
            search.addEventListener('input', function() {
                renderCountryList(filterCountryList(search.value));
            });
            listEl.addEventListener('click', function(e) {
                var item = e.target.closest('.st-country-item');
                if (!item) return;
                var iso2 = item.getAttribute('data-iso2');
                var picked = allCountries.find(function(c) { return c.iso2 === iso2; });
                setSelectedCountry(picked);
                closeCountryDropdown();
            });
            document.addEventListener('click', function(e) {
                var pickerWrap = btn.closest('.relative');
                if (pickerWrap && !pickerWrap.contains(e.target)) closeCountryDropdown();
            });
        }
        function renderCountryOptions(countries) {
            allCountries = countries || [];
            wireCountryPicker();
            renderCountryList(allCountries);
            var defaultCountry = allCountries.find(function(c) { return c.iso2 === 'PH'; }) || allCountries[0] || null;
            setSelectedCountry(defaultCountry);
        }
        function initCountryCodes() {
            var url = 'https://restcountries.com/v3.1/all?fields=cca2,name,idd';
            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(function(r) { return r.ok ? r.json() : Promise.reject(); })
                .then(function(data) {
                    var countries = (data || [])
                        .map(function(c) {
                            var root = c && c.idd ? c.idd.root : null;
                            var suffixes = c && c.idd ? c.idd.suffixes : null;
                            if (!root || !suffixes || !suffixes.length) return null;
                            return {
                                iso2: (c.cca2 || '').toUpperCase(),
                                name: (c.name && c.name.common) ? c.name.common : c.cca2,
                                dial: String(root + suffixes[0]).replace(/\s+/g, ''),
                            };
                        })
                        .filter(function(c) { return c && /^\+\d+$/.test(c.dial); })
                        .sort(function(a, b) { return a.name.localeCompare(b.name); });
                    if (!countries.length) countries = fallbackCountries();
                    renderCountryOptions(countries);
                    updatePhonePreview();
                })
                .catch(function() {
                    renderCountryOptions(fallbackCountries());
                    updatePhonePreview();
                });
        }
        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            form.reset();
            updatePhonePreview();
            if (errBox) { errBox.classList.add('hidden'); errBox.textContent = ''; }
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Create ticket'; }
        }

        // Open via sidebar button
        document.addEventListener('click', function(e) {
            var btn = e.target.closest ? e.target.closest('.ticket-create-modal-btn') : null;
            if (!btn) return;
            e.preventDefault();
            openModal();
        }, true);

        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
        });

        form.addEventListener('submit', function(e) {
            updatePhonePreview();
            var hiddenPhone = document.getElementById('st_requester_phone');
            var localPhone = document.getElementById('st_requester_phone_local');
            var normalizedPhone = hiddenPhone ? String(hiddenPhone.value || '').replace(/\s+/g, ' ').trim() : '';
            if (hiddenPhone) hiddenPhone.value = normalizedPhone;
            if (!normalizedPhone || !/^\+\d{1,4}\s?\d{6,15}$/.test(normalizedPhone)) {
                e.preventDefault();
                if (errBox) {
                    errBox.textContent = 'Please enter a valid contact number with country code.';
                    errBox.classList.remove('hidden');
                }
                if (localPhone) localPhone.focus();
                return;
            }
            e.preventDefault();
            if (!window.csrfToken) return;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting…';
            if (errBox) { errBox.classList.add('hidden'); errBox.textContent = ''; }

            fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                },
                body: new FormData(form)
            })
            .then(function(r) {
                return r.ok ? r.json() : r.json().catch(function(){ return {}; }).then(function(x){ throw x; });
            })
            .then(function(data) {
                closeModal();
                // Show success popup
                var sm = document.getElementById('ticket-create-success-modal');
                if (sm) {
                    var num = document.getElementById('ticket-create-success-number');
                    var view = document.getElementById('ticket-create-success-view');
                    if (num) num.textContent = data.ticket_number ? ('Ticket ' + data.ticket_number + ' created.') : 'Ticket created.';
                    if (view && data.modalUrl) view.setAttribute('data-url', data.modalUrl);
                    sm.classList.remove('hidden');
                    sm.classList.add('flex');
                }
                if (typeof window.refreshMainContentNow === 'function') window.refreshMainContentNow();
                else if (typeof window.refreshMainContent === 'function') window.refreshMainContent(true);
            })
            .catch(function(err) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create ticket';
                var msg = (err && err.message) ? err.message : null;
                if (!msg && err && err.errors) {
                    try {
                        var k = Object.keys(err.errors)[0];
                        if (k && err.errors[k][0]) msg = err.errors[k][0];
                    } catch(x) {}
                }
                if (errBox) {
                    errBox.textContent = msg || 'Could not submit ticket. Please try again.';
                    errBox.classList.remove('hidden');
                }
            });
        });
    })();
    </script>
    @endif

    {{-- Global ticket quick view / edit modal (works with AJAX navigation) --}}
    <div id="ticket-quick-view-modal" class="fixed inset-0 hidden items-center justify-center p-2 sm:p-4 bg-black/50 backdrop-blur-sm" style="z-index:2147483647" aria-modal="true" role="dialog" aria-labelledby="ticket-quick-view-title">
        <div class="relative w-full max-w-3xl rounded-2xl border border-blue-900/70 bg-[#0b1020] text-slate-100 shadow-2xl overflow-hidden max-h-[95vh] flex flex-col">
            <div class="sticky top-0 z-10 flex items-center justify-between gap-3 border-b border-blue-800/70 bg-[#0f172a]/95 px-5 py-4 backdrop-blur shrink-0">
                <div class="min-w-0">
                    <h2 id="ticket-quick-view-title" class="truncate text-base font-semibold text-slate-900">Ticket</h2>
                    <p id="ticket-quick-view-subtitle" class="mt-0.5 text-xl font-bold text-slate-100">Ticket Details</p>
                </div>
                <button type="button" id="ticket-quick-view-close" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50" aria-label="Close">
                    &times;
                </button>
            </div>
            <div id="ticket-quick-view-body" class="flex-1 overflow-y-auto bg-[#0b1020] p-4 sm:p-6">
                <div class="text-sm text-slate-500">Loading…</div>
            </div>
        </div>
    </div>

    {{-- Ticket submitted popup --}}
    <div id="ticket-create-success-modal" class="fixed inset-0 z-[2147483647] hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm" aria-modal="true" role="dialog" aria-labelledby="ticket-create-success-title">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white text-slate-900 shadow-2xl overflow-hidden">
            <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-5 py-4">
                <div class="min-w-0">
                    <h2 id="ticket-create-success-title" class="truncate text-base font-semibold text-slate-900">Ticket submitted</h2>
                    <p class="mt-0.5 text-xs text-slate-500" id="ticket-create-success-subtitle">Your ticket was submitted successfully.</p>
                </div>
                <button type="button" id="ticket-create-success-close" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50" aria-label="Close">&times;</button>
            </div>
            <div class="px-5 py-4">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
                    <div class="font-semibold" id="ticket-create-success-number">Ticket created.</div>
                    <div class="mt-1 text-emerald-700">You can open it now or keep working.</div>
                </div>
                <div class="mt-4 flex flex-wrap items-center justify-end gap-2">
                    <button type="button" id="ticket-create-success-stay" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Close</button>
                    <a href="#" id="ticket-create-success-view" class="inline-flex min-h-10 items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" data-url="">View ticket</a>
                </div>
            </div>
        </div>
    </div>
    {{-- Row actions: radial icons above ⋮ (floating, no row shift); collapsed = dots only, expanded = arc + ✕ on trigger --}}
    <script>
    (function() {
        if (window.__ticketActionsMenuInit) return;
        window.__ticketActionsMenuInit = true;

        var RADIAL_STAGGER_MS = 32;
        var RADIAL_FLY_MS = 480;
        /* Gap between trigger top and radial flyout bottom (px) */
        var RADIAL_ABOVE_TRIGGER_GAP_PX = 14;
        /* Keep radial shell centered on the trigger */
        var RADIAL_SHELL_NUDGE_RIGHT_PX = 0;

        function clearTicketRadialShellFixed(shell) {
            if (!shell) return;
            shell.classList.remove('ticket-radial-shell-fixed');
            ['position', 'left', 'top', 'right', 'bottom', 'transform', 'translate', 'margin', 'transform-origin'].forEach(function(p) {
                shell.style.removeProperty(p);
            });
        }

        function syncTicketRadialShellFixed(wrap) {
            var shell = wrap.querySelector('.ticket-actions-radial-shell');
            var trig = wrap.querySelector('.ticket-actions-menu-trigger');
            if (!shell || !trig || !wrap.classList.contains('is-expanded')) return;
            if (!shell.classList.contains('ticket-actions-radial-shell')) return;
            var r = trig.getBoundingClientRect();
            if (r.width < 1 || r.height < 1) return;
            var h = shell.offsetHeight;
            var swM = shell.offsetWidth;
            if (h < 12 || swM < 8) {
                requestAnimationFrame(function() {
                    syncTicketRadialShellFixed(wrap);
                });
                return;
            }
            var sw = swM;
            var centerX = r.left + (r.width / 2);
            // Anchor flyout above trigger so arc does not overlap row text.
            var anchorY = r.top - RADIAL_ABOVE_TRIGGER_GAP_PX;
            anchorY = Math.max(anchorY, h + 8);
            centerX += RADIAL_SHELL_NUDGE_RIGHT_PX;
            shell.classList.add('ticket-radial-shell-fixed');
            shell.style.setProperty('position', 'fixed', 'important');
            shell.style.setProperty('left', Math.round(centerX) + 'px', 'important');
            shell.style.setProperty('top', Math.round(anchorY) + 'px', 'important');
            shell.style.setProperty('right', 'auto', 'important');
            shell.style.setProperty('bottom', 'auto', 'important');
            shell.style.setProperty('margin', '0', 'important');
            shell.style.setProperty('translate', 'none', 'important');
            shell.style.setProperty('transform', 'translate(-50%, -100%)', 'important');
        }

        function syncAllExpandedRadialShells() {
            document.querySelectorAll('.ticket-actions-menu.is-expanded').forEach(syncTicketRadialShellFixed);
        }

        function layoutTicketRadialTrack(track) {
            var items = track.querySelectorAll('.ticket-actions-radial-item');
            var n = items.length;
            if (!n) return;
            var itemSize = 36;
            var gapPx = 12;
            var targetChord = itemSize + gapPx;
            var rMaxHard = 48;
            var rMinHard = 32;
            var maxTrackW = 196;
            var pad = 12;

            var r;
            if (n === 1) {
                r = 40;
            } else {
                var deltaTheta = Math.PI / (n - 1);
                var s = Math.sin(deltaTheta / 2);
                r = s > 1e-6 ? targetChord / (2 * s) : targetChord;
            }
            r = Math.max(rMinHard, Math.min(r, rMaxHard));
            var rFit = Math.max(rMinHard, (maxTrackW - itemSize - pad * 2) / 2);
            r = Math.min(r, rFit);

            var neededW = Math.ceil(2 * r + itemSize + pad * 2);
            var neededH = Math.ceil(r + itemSize + pad);
            track.style.width = neededW + 'px';
            track.style.minWidth = neededW + 'px';
            track.style.height = neededH + 'px';
            track.style.minHeight = neededH + 'px';

            var w = track.offsetWidth;
            var h = track.offsetHeight;
            var cx = w / 2;
            var cy = h;
            for (var i = 0; i < n; i++) {
                var theta = n === 1 ? Math.PI / 2 : Math.PI - (Math.PI * i / (n - 1));
                var x = Math.round(cx + r * Math.cos(theta) - itemSize / 2);
                var y = Math.round(cy - r * Math.sin(theta) - itemSize / 2);
                var el = items[i];
                el.style.position = 'absolute';
                el.style.left = x + 'px';
                el.style.top = y + 'px';
                el.style.right = 'auto';
                el.style.bottom = 'auto';
                el.style.margin = '0';
                el.style.setProperty('--bee-tx', (cx - x - itemSize / 2) + 'px');
                el.style.setProperty('--bee-ty', (cy - y - itemSize / 2) + 'px');
            }
        }

        function applyRadialOpenAnimation(track) {
            layoutTicketRadialTrack(track);
            var menuWrap = track.closest('.ticket-actions-menu');
            if (menuWrap) syncTicketRadialShellFixed(menuWrap);
            track.classList.remove('is-radial-items-out');
            var items = track.querySelectorAll('.ticket-actions-radial-item');
            items.forEach(function(el) {
                el.style.transitionDelay = '0ms';
            });
            void track.offsetHeight;
            items.forEach(function(el, i) {
                el.style.transitionDelay = (i * RADIAL_STAGGER_MS) + 'ms';
            });
            requestAnimationFrame(function() {
                track.classList.add('is-radial-items-out');
                var menu = track.closest('.ticket-actions-menu');
                if (menu) {
                    requestAnimationFrame(function() {
                        syncTicketRadialShellFixed(menu);
                    });
                }
            });
        }

        function clearTicketRadialItemLayout(track) {
            if (!track) return;
            track.classList.remove('is-radial-items-out');
            ['width', 'min-width', 'height', 'min-height'].forEach(function(p) {
                track.style.removeProperty(p);
            });
            track.querySelectorAll('.ticket-actions-radial-item').forEach(function(el) {
                ['position', 'left', 'top', 'right', 'bottom', 'margin', 'transition-delay'].forEach(function(p) {
                    el.style.removeProperty(p);
                });
                el.style.removeProperty('--bee-tx');
                el.style.removeProperty('--bee-ty');
            });
        }

        function finishRadialClose(wrap, icons, trig, dots, collapseIcon, track) {
            wrap.classList.remove('is-expanded');
            clearTicketRadialShellFixed(icons);
            icons.classList.add('invisible', 'pointer-events-none', 'opacity-0');
            icons.classList.remove('visible', 'pointer-events-auto', 'opacity-100');
            clearTicketRadialItemLayout(track);
            track.querySelectorAll('.ticket-actions-radial-item').forEach(function(el) {
                el.style.removeProperty('transition-delay');
            });
            trig.setAttribute('aria-expanded', 'false');
            if (dots) dots.classList.remove('hidden');
            if (collapseIcon) collapseIcon.classList.add('hidden');
        }

        function startRadialClose(wrap, icons, trig, dots, collapseIcon, track) {
            if (wrap._radialCloseT) {
                clearTimeout(wrap._radialCloseT);
                wrap._radialCloseT = null;
            }
            var items = track.querySelectorAll('.ticket-actions-radial-item');
            var n = items.length;
            if (!n) {
                finishRadialClose(wrap, icons, trig, dots, collapseIcon, track);
                return;
            }
            layoutTicketRadialTrack(track);
            syncTicketRadialShellFixed(wrap);
            items.forEach(function(el, i) {
                el.style.transitionDelay = ((n - 1 - i) * RADIAL_STAGGER_MS) + 'ms';
            });
            track.classList.remove('is-radial-items-out');
            var wait = RADIAL_FLY_MS + (n - 1) * RADIAL_STAGGER_MS + 40;
            wrap._radialCloseT = setTimeout(function() {
                wrap._radialCloseT = null;
                finishRadialClose(wrap, icons, trig, dots, collapseIcon, track);
            }, wait);
        }

        function setTicketActionsExpanded(wrap, expanded) {
            var icons = wrap.querySelector('.ticket-actions-inline');
            var trig = wrap.querySelector('.ticket-actions-menu-trigger');
            var dots = trig && trig.querySelector('.ticket-actions-menu-icon-dots');
            var collapseIcon = trig && trig.querySelector('.ticket-actions-menu-icon-collapse');
            if (!icons || !trig) return;
            var isRadial = icons.classList && icons.classList.contains('ticket-actions-radial-shell');
            if (expanded) {
                if (wrap._radialCloseT) {
                    clearTimeout(wrap._radialCloseT);
                    wrap._radialCloseT = null;
                }
                wrap.classList.add('is-expanded');
                if (isRadial) {
                    icons.classList.remove('invisible', 'pointer-events-none', 'opacity-0');
                    icons.classList.add('visible', 'pointer-events-auto', 'opacity-100');
                    var trackOpen = icons.querySelector('.ticket-actions-radial-track');
                    if (trackOpen) {
                        requestAnimationFrame(function() {
                            applyRadialOpenAnimation(trackOpen);
                        });
                    }
                } else {
                    icons.classList.remove('hidden');
                    icons.classList.add('inline-flex');
                }
                trig.setAttribute('aria-expanded', 'true');
                if (dots) dots.classList.add('hidden');
                if (collapseIcon) collapseIcon.classList.remove('hidden');
            } else {
                if (isRadial) {
                    var trackClose = icons.querySelector('.ticket-actions-radial-track');
                    if (trackClose && trackClose.querySelectorAll('.ticket-actions-radial-item').length) {
                        startRadialClose(wrap, icons, trig, dots, collapseIcon, trackClose);
                    } else {
                        wrap.classList.remove('is-expanded');
                        clearTicketRadialShellFixed(icons);
                        icons.classList.add('invisible', 'pointer-events-none', 'opacity-0');
                        icons.classList.remove('visible', 'pointer-events-auto', 'opacity-100');
                        clearTicketRadialItemLayout(trackClose);
                        trig.setAttribute('aria-expanded', 'false');
                        if (dots) dots.classList.remove('hidden');
                        if (collapseIcon) collapseIcon.classList.add('hidden');
                    }
                } else {
                    wrap.classList.remove('is-expanded');
                    icons.classList.add('hidden');
                    icons.classList.remove('inline-flex');
                    trig.setAttribute('aria-expanded', 'false');
                    if (dots) dots.classList.remove('hidden');
                    if (collapseIcon) collapseIcon.classList.add('hidden');
                }
            }
        }

        function closeAllTicketActionMenus() {
            document.querySelectorAll('.ticket-actions-menu.is-expanded').forEach(function(w) {
                setTicketActionsExpanded(w, false);
            });
        }
        window.closeAllTicketActionMenus = closeAllTicketActionMenus;

        document.addEventListener('click', function(e) {
            var trig = e.target.closest && e.target.closest('.ticket-actions-menu-trigger');
            if (!trig) return;
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            var wrap = trig.closest('.ticket-actions-menu');
            if (!wrap) return;
            var wasOpen = wrap.classList.contains('is-expanded');
            document.querySelectorAll('.ticket-actions-menu.is-expanded').forEach(function(w) {
                if (w !== wrap) setTicketActionsExpanded(w, false);
            });
            setTicketActionsExpanded(wrap, !wasOpen);
        }, true);

        document.addEventListener('click', function(e) {
            if (e.target.closest && e.target.closest('.ticket-actions-menu')) return;
            closeAllTicketActionMenus();
        }, false);

        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') return;
            if (!document.querySelector('.ticket-actions-menu.is-expanded')) return;
            e.stopImmediatePropagation();
            closeAllTicketActionMenus();
        }, true);

        document.addEventListener('submit', function(e) {
            if (e.target && e.target.closest && e.target.closest('.ticket-actions-inline')) {
                closeAllTicketActionMenus();
            }
        }, true);

        document.addEventListener('click', function(e) {
            var maps = e.target.closest && e.target.closest('a.ticket-actions-maps-link');
            if (!maps || !maps.closest('.ticket-actions-radial-shell')) return;
            closeAllTicketActionMenus();
        }, true);

        var radialResizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(radialResizeTimer);
            radialResizeTimer = setTimeout(function() {
                document.querySelectorAll('.ticket-actions-menu.is-expanded .ticket-actions-radial-track').forEach(function(track) {
                    if (track.classList.contains('is-radial-items-out')) {
                        layoutTicketRadialTrack(track);
                    }
                });
                syncAllExpandedRadialShells();
            }, 100);
        });
        window.addEventListener('scroll', function() {
            syncAllExpandedRadialShells();
        }, true);
    })();
    </script>
    <script>
    (function() {
        if (window.__ticketModalInit) return;

        var modal = document.getElementById('ticket-quick-view-modal');
        var body = document.getElementById('ticket-quick-view-body');
        var closeBtn = document.getElementById('ticket-quick-view-close');
        var modalTitle = document.getElementById('ticket-quick-view-title');
        var modalSubtitle = document.getElementById('ticket-quick-view-subtitle');
        if (!modal || !body) return;
        window.__ticketModalInit = true;

        var loading = false;
        var lastUrl = null;
        var lastMode = 'view';
        var cache = {};
        var inFlight = {};
        var cacheOrder = [];
        var CACHE_MAX = 25;

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            // Always start at top so header/info cards are visible.
            body.scrollTop = 0;
        }
        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function wirePhoneSanitizers(root) {
            if (!root) return;
            var inputs = root.querySelectorAll('.js-ticket-phone');
            inputs.forEach(function(el) {
                function allowOnlyNumbers() {
                    var v = (el.value || '').replace(/\D/g, '');
                    if (v.length > 11) v = v.slice(0, 11);
                    if (el.value !== v) el.value = v;
                }
                el.addEventListener('input', allowOnlyNumbers);
                el.addEventListener('paste', function() { setTimeout(allowOnlyNumbers, 0); });
            });
        }

        function cachePut(url, html) {
            if (!url || typeof html !== 'string') return;
            if (!cache[url]) {
                cacheOrder.push(url);
                while (cacheOrder.length > CACHE_MAX) {
                    var old = cacheOrder.shift();
                    if (old) delete cache[old];
                }
            }
            cache[url] = { html: html, t: Date.now() };
        }

        function prefetch(url) {
            if (!url) return;
            if (cache[url]) return;
            if (inFlight[url]) return;

            var controller = (typeof AbortController !== 'undefined') ? new AbortController() : null;
            inFlight[url] = { controller: controller, startedAt: Date.now() };

            fetch(url, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                signal: controller ? controller.signal : undefined
            })
                .then(function(r) { return r.ok ? r.text() : Promise.reject(); })
                .then(function(html) { cachePut(url, html); })
                .catch(function() {})
                .finally(function() { delete inFlight[url]; });
        }

        var currentFetch = null;

        function setQuickModalHeader(mode) {
            if (!modalTitle || !modalSubtitle) return;
            if (mode === 'edit') {
                modalTitle.textContent = 'Edit Ticket';
                modalSubtitle.textContent = 'Ticket Information';
                modalSubtitle.classList.remove('hidden');
                return;
            }
            if (mode === 'status') {
                modalTitle.textContent = 'Status Update';
                modalSubtitle.textContent = 'Status Update';
                modalSubtitle.classList.remove('hidden');
                return;
            }
            modalTitle.textContent = 'Ticket';
            modalSubtitle.textContent = 'Ticket Details';
            modalSubtitle.classList.remove('hidden');
        }

        function load(url, mode) {
            if (!url) return;
            if (typeof window.closeAllTicketActionMenus === 'function') window.closeAllTicketActionMenus();
            // Abort any in-flight fetch
            if (currentFetch) { currentFetch.abort(); currentFetch = null; }
            loading = false;
            lastUrl = url;
            lastMode = (mode === 'edit') ? 'edit' : (mode === 'status' ? 'status' : 'view');
            if (url.indexOf('/status-modal') !== -1) lastMode = 'status';
            if (url.indexOf('/edit-modal') !== -1) lastMode = 'edit';
            setQuickModalHeader(lastMode);
            openModal();

            // Show cached content immediately if available
            if (cache[url] && cache[url].html) {
                body.innerHTML = cache[url].html;
                body.scrollTop = 0;
                wirePhoneSanitizers(body);
                // Silently refresh in background
                var ctrl = new (typeof AbortController !== 'undefined' ? AbortController : function(){ this.signal=null; this.abort=function(){}; })();
                currentFetch = ctrl;
                fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, signal: ctrl.signal })
                    .then(function(r) { return (r.ok && !r.redirected) ? r.text() : Promise.reject(); })
                    .then(function(html) {
                        cachePut(url, html);
                        if (lastUrl === url && !modal.classList.contains('hidden')) {
                            body.innerHTML = html;
                            body.scrollTop = 0;
                            wirePhoneSanitizers(body);
                        }
                    })
                    .catch(function() {})
                    .finally(function() { currentFetch = null; });
                return;
            }

            // Show skeleton while loading
            body.innerHTML =
                '<div class="space-y-4">' +
                '  <div class="flex items-center justify-between gap-3">' +
                '    <div class="min-w-0 flex-1">' +
                '      <div class="h-4 w-40 rounded bg-slate-200 animate-pulse"></div>' +
                '      <div class="mt-2 h-6 w-full max-w-[36rem] rounded bg-slate-200 animate-pulse"></div>' +
                '    </div>' +
                '    <div class="flex justify-end">' +
                '      <div class="h-9 w-9 shrink-0 rounded-lg bg-slate-200 animate-pulse"></div>' +
                '    </div>' +
                '  </div>' +
                '  <div class="flex flex-wrap gap-2">' +
                '    <div class="h-6 w-20 rounded bg-slate-200 animate-pulse"></div>' +
                '    <div class="h-6 w-28 rounded bg-slate-200 animate-pulse"></div>' +
                '    <div class="h-6 w-32 rounded bg-slate-200 animate-pulse"></div>' +
                '  </div>' +
                '  <div class="grid gap-2 sm:grid-cols-2">' +
                '    <div class="h-16 rounded bg-slate-100 border border-slate-200 animate-pulse"></div>' +
                '    <div class="h-16 rounded bg-slate-100 border border-slate-200 animate-pulse"></div>' +
                '    <div class="h-16 rounded bg-slate-100 border border-slate-200 animate-pulse"></div>' +
                '    <div class="h-16 rounded bg-slate-100 border border-slate-200 animate-pulse"></div>' +
                '  </div>' +
                '  <div class="h-28 rounded bg-slate-100 border border-slate-200 animate-pulse"></div>' +
                '  <div class="space-y-2">' +
                '    <div class="h-4 w-32 rounded bg-slate-200 animate-pulse"></div>' +
                '    <div class="h-14 rounded bg-slate-100 border border-slate-200 animate-pulse"></div>' +
                '    <div class="h-14 rounded bg-slate-100 border border-slate-200 animate-pulse"></div>' +
                '  </div>' +
                '</div>';

            var ctrl2 = new (typeof AbortController !== 'undefined' ? AbortController : function(){ this.signal=null; this.abort=function(){}; })();
            currentFetch = ctrl2;
            var fetchUrl = url;

            function doFetch(attempt) {
                fetch(fetchUrl, {
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                    signal: ctrl2.signal
                })
                    .then(function(r) {
                        if (!r.ok || r.redirected) return Promise.reject(r.status || 'redirect');
                        return r.text();
                    })
                    .then(function(html) {
                        if (lastUrl !== fetchUrl) return;
                        cachePut(fetchUrl, html);
                        body.innerHTML = html;
                        body.scrollTop = 0;
                        wirePhoneSanitizers(body);
                    })
                    .catch(function(err) {
                        if (err && err.name === 'AbortError') return;
                        if (lastUrl !== fetchUrl) return;
                        // Auto-retry once after a short delay before giving up
                        if (attempt < 2) {
                            setTimeout(function() { if (lastUrl === fetchUrl) doFetch(attempt + 1); }, 600);
                            return;
                        }
                        body.innerHTML =
                            '<div class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">' +
                            '<div class="font-semibold">Could not load ticket details.</div>' +
                            '<div class="mt-1">Server/database not responding.</div>' +
                            '<button type="button" class="ticket-quick-view-retry mt-3 inline-flex min-h-10 items-center justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700">Retry now</button>' +
                            '</div>';
                    })
                    .finally(function() { currentFetch = null; loading = false; });
            }
            doFetch(1);
        }

        // Open modal (View/Edit/Create/Help) using event delegation (works for injected HTML)
        document.addEventListener('click', function(e) {
            var btn = e.target.closest ? e.target.closest('.ticket-quick-view-btn, .ticket-edit-modal-btn, .ticket-status-modal-btn, .help-modal-btn') : null;
            if (!btn) return;
            var url = btn.getAttribute('data-url');
            if (!url) return;
            e.preventDefault();
            var mode = btn.classList.contains('ticket-edit-modal-btn') ? 'edit' : (btn.classList.contains('ticket-status-modal-btn') ? 'status' : 'view');
            load(url, mode);
        }, true);

        // Prefetch for snappy open
        document.addEventListener('mouseenter', function(e) {
            var btn = e.target.closest ? e.target.closest('.ticket-quick-view-btn, .ticket-edit-modal-btn, .ticket-status-modal-btn, .help-modal-btn') : null;
            if (!btn) return;
            prefetch(btn.getAttribute('data-url'));
        }, true);
        // Also prefetch on mousedown — starts fetch before click fires
        document.addEventListener('mousedown', function(e) {
            var btn = e.target.closest ? e.target.closest('.ticket-quick-view-btn, .ticket-edit-modal-btn, .ticket-status-modal-btn, .help-modal-btn') : null;
            if (!btn) return;
            prefetch(btn.getAttribute('data-url'));
        }, true);
        document.addEventListener('focusin', function(e) {
            var btn = e.target.closest ? e.target.closest('.ticket-quick-view-btn, .ticket-edit-modal-btn, .ticket-status-modal-btn, .help-modal-btn') : null;
            if (!btn) return;
            prefetch(btn.getAttribute('data-url'));
        });
        document.addEventListener('touchstart', function(e) {
            var btn = e.target.closest ? e.target.closest('.ticket-quick-view-btn, .ticket-edit-modal-btn, .ticket-status-modal-btn, .help-modal-btn') : null;
            if (!btn) return;
            prefetch(btn.getAttribute('data-url'));
        }, { passive: true, capture: true });

        document.addEventListener('click', function(e) {
            var retry = e.target.closest ? e.target.closest('.ticket-quick-view-retry') : null;
            if (!retry) return;
            e.preventDefault();
            if (lastUrl) load(lastUrl, lastMode);
        }, true);

        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });
        document.addEventListener('click', function(e) {
            var closeInside = e.target.closest ? e.target.closest('.ticket-modal-close') : null;
            if (!closeInside) return;
            e.preventDefault();
            closeModal();
        }, true);

        function openTicketCreateSuccess(ticketNumber, viewUrl) {
            var m = document.getElementById('ticket-create-success-modal');
            if (!m) return;
            var num = document.getElementById('ticket-create-success-number');
            var sub = document.getElementById('ticket-create-success-subtitle');
            var view = document.getElementById('ticket-create-success-view');
            if (num) num.textContent = ticketNumber ? ('Ticket ' + ticketNumber + ' created.') : 'Ticket created.';
            if (sub) sub.textContent = 'Your ticket was submitted successfully.';
            if (view && viewUrl) view.setAttribute('data-url', viewUrl);
            m.classList.remove('hidden');
            m.classList.add('flex');
        }
        function closeTicketCreateSuccess() {
            var m = document.getElementById('ticket-create-success-modal');
            if (!m) return;
            m.classList.add('hidden');
            m.classList.remove('flex');
        }
        document.addEventListener('click', function(e) {
            var m = document.getElementById('ticket-create-success-modal');
            if (!m || m.classList.contains('hidden')) return;
            if (e.target === m) closeTicketCreateSuccess();
        });
        document.addEventListener('click', function(e) {
            var btn = e.target.closest ? e.target.closest('#ticket-create-success-close, #ticket-create-success-stay') : null;
            if (!btn) return;
            e.preventDefault();
            closeTicketCreateSuccess();
        }, true);
        document.addEventListener('click', function(e) {
            var btn = e.target.closest ? e.target.closest('#ticket-create-success-view') : null;
            if (!btn) return;
            e.preventDefault();
            var url = btn.getAttribute('data-url');
            closeTicketCreateSuccess();
            if (url) load(url, 1);
        }, true);
        document.addEventListener('keydown', function(e) {
            var m = document.getElementById('ticket-create-success-modal');
            if (!m || m.classList.contains('hidden')) return;
            if (e.key === 'Escape') closeTicketCreateSuccess();
        });

        // Save edit form via AJAX
        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (!form || !(form instanceof HTMLFormElement)) return;
            if (modal.classList.contains('hidden')) return;
            if (!form.classList.contains('ajax-ticket-edit-form')) return;
            if (typeof window.csrfToken !== 'string' || !window.csrfToken) return;
            e.preventDefault();

            var submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.dataset.prevText = submitBtn.textContent || '';
                submitBtn.textContent = 'Saving...';
            }

            fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                },
                body: new FormData(form)
            })
                .then(function(r) { return r.ok ? r.json() : r.json().catch(function() { return {}; }).then(function(x){ throw x; }); })
                .then(function(data) {
                    if (typeof window.showAppToast === 'function') window.showAppToast((data && data.message) ? data.message : 'Saved');
                    closeModal();
                    if (typeof window.refreshMainContentNow === 'function') window.refreshMainContentNow();
                    else if (typeof window.refreshMainContent === 'function') window.refreshMainContent(true);
                })
                .catch(function(err) {
                    if (typeof window.showAppToast === 'function') window.showAppToast((err && err.message) ? err.message : 'Could not save');
                })
                .finally(function() {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = submitBtn.dataset.prevText || 'Save changes';
                    }
                });
        }, true);

        // Save status-only form via AJAX
        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (!form || !(form instanceof HTMLFormElement)) return;
            if (modal.classList.contains('hidden')) return;
            if (!form.classList.contains('ajax-ticket-status-form')) return;
            if (typeof window.csrfToken !== 'string' || !window.csrfToken) return;
            e.preventDefault();

            var submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.dataset.prevText = submitBtn.textContent || '';
                submitBtn.textContent = 'Updating...';
            }

            fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                },
                body: new FormData(form)
            })
                .then(function(r) { return r.ok ? r.json() : r.json().catch(function() { return {}; }).then(function(x){ throw x; }); })
                .then(function(data) {
                    if (typeof window.showAppToast === 'function') window.showAppToast((data && data.message) ? data.message : 'Status updated.');
                    closeModal();
                    if (typeof window.refreshMainContentNow === 'function') window.refreshMainContentNow();
                    else if (typeof window.refreshMainContent === 'function') window.refreshMainContent(true);
                })
                .catch(function(err) {
                    if (typeof window.showAppToast === 'function') window.showAppToast((err && err.message) ? err.message : 'Could not update status');
                })
                .finally(function() {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = submitBtn.dataset.prevText || 'Update status';
                    }
                });
        }, true);

        // Create ticket via AJAX (modal)
        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (!form || !(form instanceof HTMLFormElement)) return;
            if (modal.classList.contains('hidden')) return;
            if (!form.classList.contains('ajax-ticket-create-form')) return;
            if (typeof window.csrfToken !== 'string' || !window.csrfToken) return;
            e.preventDefault();

            var submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.dataset.prevText = submitBtn.textContent || '';
                submitBtn.textContent = 'Submitting...';
            }

            fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                },
                body: new FormData(form)
            })
                .then(function(r) { return r.ok ? r.json() : r.json().catch(function() { return {}; }).then(function(x){ throw x; }); })
                .then(function(data) {
                    closeModal();
                    var ticketNumber = data && (data.ticket_number || data.ticketNumber);
                    var viewUrl = data && (data.modalUrl || data.viewUrl || data.redirectUrl);
                    openTicketCreateSuccess(ticketNumber, viewUrl);
                    if (typeof window.refreshMainContentNow === 'function') window.refreshMainContentNow();
                    else if (typeof window.refreshMainContent === 'function') window.refreshMainContent(true);
                })
                .catch(function(err) {
                    var msg = (err && err.message) ? err.message : null;
                    if (!msg && err && err.errors) {
                        try {
                            var firstKey = Object.keys(err.errors)[0];
                            if (firstKey && err.errors[firstKey] && err.errors[firstKey][0]) msg = err.errors[firstKey][0];
                        } catch (e) {}
                    }
                    if (typeof window.showAppToast === 'function') window.showAppToast(msg || 'Could not submit ticket.');
                })
                .finally(function() {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = submitBtn.dataset.prevText || 'Create ticket';
                    }
                });
        }, true);

        // If ticket acceptance happens inside the modal, refresh modal content afterwards.
        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (!form || !(form instanceof HTMLFormElement)) return;
            if (modal.classList.contains('hidden')) return;
            if (!form.classList.contains('ajax-accept-ticket-form')) return;
            setTimeout(function() { if (lastUrl) load(lastUrl, 1); }, 900);
        }, true);

        // Clear cached HTML on header refreshes.
        if (!window.__ticketModalWrappedRefresh) {
            window.__ticketModalWrappedRefresh = true;
            var prevRefresh = window.refreshHeaderNotifications;
            window.refreshHeaderNotifications = function() {
                cache = {};
                cacheOrder = [];
                if (typeof prevRefresh === 'function') prevRefresh();
            };
        }
    })();
    </script>
    @endauth

    <script>
    (function() {
        var lang = {
            search: 'Search:',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'No entries',
            infoFiltered: '(filtered from _MAX_ total entries)',
            paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' }
        };
        window.initDataTablesInContent = function() {
            if (typeof jQuery === 'undefined' || !jQuery.fn.DataTable) return;
            document.querySelectorAll('table.display[id]').forEach(function(table) {
                if (jQuery.fn.dataTable && jQuery.fn.dataTable.isDataTable && jQuery.fn.dataTable.isDataTable(table)) return;
                var hasData = table.querySelector('tbody tr') && !table.querySelector('tbody tr td[colspan]');
                if (!hasData) return;
                var id = table.getAttribute('id');
                var colCount = table.querySelector('thead tr').querySelectorAll('th').length;
                var orderCol = 0;
                var orderDir = 'asc';
                if (id === 'tickets-table') {
                    orderCol = colCount - 2;
                    orderDir = 'desc';
                } else if (id === 'login-history-table') {
                    orderCol = 2;
                    orderDir = 'desc';
                } else if (id === 'audit-trail-actions-table') {
                    orderCol = 3;
                    orderDir = 'desc';
                } else if (id === 'admin-notifications-table') {
                    orderCol = 3;
                    orderDir = 'desc';
                }
                var pageLen = (id === 'recent-tickets-datatable') ? 5 : 10;
                var lenMenu;
                if (id === 'recent-tickets-datatable') {
                    lenMenu = [[5, 10, 25, -1], [5, 10, 25, 'All']];
                } else if (id === 'audit-trail-actions-table') {
                    lenMenu = [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']];
                } else {
                    lenMenu = [[10, 25, 50, -1], [10, 25, 50, 'All']];
                }
                var dtOptions = {
                    order: [[ orderCol, orderDir ]],
                    pageLength: pageLen,
                    lengthMenu: lenMenu,
                    language: lang
                };
                if (id === 'tickets-table') {
                    dtOptions.searching = true;
                    dtOptions.scrollX = true;
                    dtOptions.autoWidth = false;
                    dtOptions.language = Object.assign({}, lang, {
                        search: '',
                        searchPlaceholder: 'Search tickets...'
                    });
                    dtOptions.columnDefs = [
                        { targets: '_all', searchable: false },
                        { targets: [0, 1], searchable: true } // Ticket # and Title only
                    ];
                }
                jQuery(table).DataTable(dtOptions);
                if (id === 'tickets-table' || id === 'recent-tickets-datatable') {
                    jQuery(table).on('draw.dt', function() {
                        if (typeof window.closeAllTicketActionMenus === 'function') window.closeAllTicketActionMenus();
                    });
                }
            });
        };
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() { window.initDataTablesInContent(); });
        } else {
            window.initDataTablesInContent();
        }
    })();
    </script>
    <script>
    (function() {
        var bellBtn = document.getElementById('header-notifications-button');
        var bellPanel = document.getElementById('header-notifications-panel');
        var notifList = document.getElementById('header-notifications-list');
        var clearBtn = document.getElementById('header-notifications-clear');
        var headerNotifUrl = window.headerNotificationsUrl || null;
        var badgeEl = bellBtn ? bellBtn.querySelector('.header-badge') : null;
        var badgeCount = bellBtn ? parseInt(bellBtn.getAttribute('data-badge-count') || '0', 10) || 0 : 0;
        var badgeMode = bellBtn ? (bellBtn.getAttribute('data-badge-mode') || 'notifications') : 'notifications';
        var currentPage = 0;
        var loadingMore = false;
        var perPage = 10;
        var panelEverOpened = false;

        function updateBadge(newCount) {
            // When badge represents global Open tickets for employees, don't mutate it client-side.
            if (badgeMode !== 'notifications') return;
            badgeCount = Math.max(0, newCount);
            if (badgeEl && badgeCount === 0) {
                badgeEl.parentNode.removeChild(badgeEl);
                badgeEl = null;
                bellBtn.classList.remove('header-bell-has-unread');
            } else if (badgeEl && badgeCount > 0) {
                badgeEl.textContent = String(badgeCount);
            }
        }

        function loadHeaderPage(nextPage) {
            if (!bellBtn || !headerNotifUrl || !notifList) return;
            if (loadingMore) return;
            loadingMore = true;
            fetch(headerNotifUrl + '?page=' + encodeURIComponent(String(nextPage)) + '&perPage=' + encodeURIComponent(String(perPage)), {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function (r) {
                    if (r.status === 401 || r.status === 419) {
                        // Session expired — reload page
                        window.location.reload();
                        return Promise.reject('session');
                    }
                    return r.ok ? r.json() : Promise.reject('server-' + r.status);
                })
                .then(function (data) {
                    applyHeaderData(data);
                    currentPage = nextPage;
                })
                .catch(function (reason) {
                    if (reason === 'session') return;
                    if (nextPage === 1) {
                        notifList.innerHTML = '<div class="px-3.5 py-3 text-xs text-slate-500 dark:text-slate-300">No notifications yet.</div>';
                    }
                })
                .finally(function () { loadingMore = false; });
        }

        function applyHeaderData(data) {
            if (!bellBtn || !data) return;

            var newMode = data.badgeMode || 'notifications';
            var newCount = parseInt(data.badgeCount || 0, 10) || 0;
            var page = parseInt(data.page || 1, 10) || 1;
            var hasMore = !!data.hasMore;

            badgeMode = newMode;
            bellBtn.setAttribute('data-badge-mode', newMode);
            bellBtn.setAttribute('data-badge-count', String(newCount));

            if (clearBtn && typeof data.blockClear !== 'undefined') {
                clearBtn.setAttribute('data-block-clear', data.blockClear ? '1' : '0');
            }
            if (notifList && typeof data.html === 'string') {
                if (page > 1) {
                    notifList.insertAdjacentHTML('beforeend', data.html);
                } else {
                    notifList.innerHTML = data.html;
                }
                notifList.setAttribute('data-has-more', hasMore ? '1' : '0');
            }

            // Only update badge if panel has never been opened (once opened = treated as read)
            if (!panelEverOpened) {
                if (newCount > 0) {
                    if (!badgeEl) {
                        badgeEl = document.createElement('span');
                        badgeEl.className = 'header-badge absolute -top-0.5 -right-0.5 inline-flex min-h-[1.15rem] min-w-[1.15rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white shadow-sm';
                        bellBtn.appendChild(badgeEl);
                    }
                    badgeCount = newCount;
                    badgeEl.textContent = String(newCount);
                    bellBtn.classList.add('header-bell-has-unread');
                } else {
                    badgeCount = 0;
                    if (badgeEl && badgeEl.parentNode) {
                        badgeEl.parentNode.removeChild(badgeEl);
                        badgeEl = null;
                    }
                    bellBtn.classList.remove('header-bell-has-unread');
                }
            }
        }

        if (bellBtn && bellPanel) {
            function setNotifOpen(open) {
                if (open) {
                    bellPanel.classList.remove('opacity-0', 'translate-y-1', 'scale-95', 'pointer-events-none');
                    bellPanel.classList.add('opacity-100', 'translate-y-0', 'scale-100', 'pointer-events-auto');
                    bellPanel.setAttribute('data-open', '1');
                    bellBtn.setAttribute('aria-expanded', 'true');
                    if (currentPage === 0) loadHeaderPage(1);
                    // Mark all as read when bell is opened
                    panelEverOpened = true;
                    updateBadge(0);
                    fetch('{{ route('notifications.seen') }}', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'X-CSRF-TOKEN': window.csrfToken || '', 'Accept': 'application/json' }
                    }).catch(function(){});
                } else {
                    bellPanel.classList.add('opacity-0', 'translate-y-1', 'scale-95', 'pointer-events-none');
                    bellPanel.classList.remove('opacity-100', 'translate-y-0', 'scale-100', 'pointer-events-auto');
                    bellPanel.setAttribute('data-open', '0');
                    bellBtn.setAttribute('aria-expanded', 'false');
                }
            }
            function toggleNotif() {
                var isOpen = bellPanel.getAttribute('data-open') === '1';
                setNotifOpen(!isOpen);
            }
            bellBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleNotif();
            });
            document.addEventListener('click', function(e) {
                if (bellPanel.getAttribute('data-open') === '1' && !bellBtn.contains(e.target) && !bellPanel.contains(e.target)) {
                    setNotifOpen(false);
                }
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    setNotifOpen(false);
                }
            });

            if (notifList) {
                notifList.addEventListener('scroll', function() {
                    if (notifList.getAttribute('data-has-more') !== '1') return;
                    if (loadingMore) return;
                    var nearBottom = (notifList.scrollTop + notifList.clientHeight) >= (notifList.scrollHeight - 36);
                    if (!nearBottom) return;
                    loadHeaderPage((currentPage || 1) + 1);
                });
            }
        }

        if (clearBtn && notifList && bellBtn) {
            clearBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (clearBtn.getAttribute('data-block-clear') === '1') {
                    alert('You have open tickets waiting in the queue.');
                    return;
                }
                notifList.innerHTML = '<div class="px-3.5 py-3 text-xs text-slate-500 dark:text-slate-300">No notifications yet.</div>';
                updateBadge(0);
            });
        }

        // Expose a small helper so the notifications page can also adjust the badge if needed
        window.updateHeaderNotificationBadge = function(delta) {
            if (!bellBtn) return;
            updateBadge(badgeCount + delta);
        };

        // Live refresh for header notifications (used by polling + Reverb)
        window.refreshHeaderNotifications = function() {
            if (!bellBtn || !headerNotifUrl) return;
            // Reset paging and reload first page.
            try { if (notifList) notifList.scrollTop = 0; } catch (e) {}
            if (notifList) notifList.setAttribute('data-has-more', '0');
            currentPage = 0;
            loadHeaderPage(1);
        };

        // Load badge + first items immediately (faster perceived load).
        if (bellBtn && headerNotifUrl) {
            if (currentPage === 0) window.refreshHeaderNotifications();
        }
    })();
    </script>
    @if(auth()->user()->isAdmin())
    <script>
    (function() {
        var btn   = document.getElementById('pw-reset-btn');
        var panel = document.getElementById('pw-reset-panel');
        var list  = document.getElementById('pw-reset-list');
        var badge = document.getElementById('pw-reset-badge');
        if (!btn || !panel) return;

        var csrf = window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '';

        var pollTimer = null;

        function setPanelOpen(open) {
            if (open) {
                panel.classList.remove('opacity-0','translate-y-1','scale-95','pointer-events-none');
                panel.classList.add('opacity-100','translate-y-0','scale-100','pointer-events-auto');
                panel.setAttribute('data-open','1');
                btn.setAttribute('aria-expanded','true');
                loadRequests();
                if (!pollTimer) pollTimer = setInterval(pollBadge, 10000);
            } else {
                panel.classList.add('opacity-0','translate-y-1','scale-95','pointer-events-none');
                panel.classList.remove('opacity-100','translate-y-0','scale-100','pointer-events-auto');
                panel.setAttribute('data-open','0');
                btn.setAttribute('aria-expanded','false');
                if (pollTimer) {
                    clearInterval(pollTimer);
                    pollTimer = null;
                }
            }
        }

        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            setPanelOpen(panel.getAttribute('data-open') !== '1');
        });
        document.addEventListener('click', function(e) {
            if (panel.getAttribute('data-open') === '1' && !btn.contains(e.target) && !panel.contains(e.target)) {
                setPanelOpen(false);
            }
        });

        function loadRequests() {
            if (!list) return;
            list.innerHTML = '<div class="px-3.5 py-3 text-xs text-slate-500 dark:text-slate-300">Loading…</div>';
            fetch('{{ route('admin.password-reset-requests') }}', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.ok ? r.json() : Promise.reject(); })
            .then(function(data) {
                renderRequests(data.requests || []);
                updateBadge(data.count || 0);
            })
            .catch(function() {
                list.innerHTML = '<div class="px-3.5 py-3 text-xs text-red-500">Could not load requests.</div>';
            });
        }

        function updateBadge(count) {
            if (!badge) return;
            if (count > 0) {
                badge.textContent = String(count);
                badge.classList.remove('hidden');
                badge.classList.add('inline-flex');
            } else {
                badge.classList.add('hidden');
                badge.classList.remove('inline-flex');
            }
        }

        function renderRequests(requests) {
            if (!list) return;
            if (!requests.length) {
                list.innerHTML = '<div class="px-3.5 py-3 text-xs text-slate-500 dark:text-slate-300">No pending requests.</div>';
                return;
            }
            var html = '';
            requests.forEach(function(r) {
                html +=
                    '<div class="px-3.5 py-2.5 border-b border-slate-100 dark:border-slate-700/60 last:border-0" id="pwr-item-' + r.id + '">' +
                    '<p class="text-xs font-semibold text-slate-800 dark:text-slate-100">' + escHtml(r.user_name) + '</p>' +
                    '<p class="text-[11px] text-slate-500 dark:text-slate-400">Account: ' + escHtml(r.user_email) + '</p>' +
                    '<p class="text-[11px] text-emerald-600 dark:text-emerald-400">Reset link → ' + escHtml(r.emergency_email || '(no emergency email set)') + '</p>' +
                    '<p class="text-[11px] text-slate-400 dark:text-slate-500">' + escHtml(r.created_at) + '</p>' +
                    '<div class="mt-1.5 flex gap-2">' +
                    '<button onclick="pwrAction(' + r.id + ',\'approve\')" class="flex-1 rounded-lg bg-emerald-500 px-2 py-1 text-[11px] font-semibold text-white hover:bg-emerald-600 transition-colors">Approve</button>' +
                    '<button onclick="pwrAction(' + r.id + ',\'ignore\')" class="flex-1 rounded-lg bg-slate-200 dark:bg-slate-600 px-2 py-1 text-[11px] font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-500 transition-colors">Ignore</button>' +
                    '</div>' +
                    '</div>';
            });
            list.innerHTML = html;
        }

        function escHtml(str) {
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        window.pwrAction = function(id, action) {
            var url = action === 'approve'
                ? '{{ url('admin/password-reset-requests') }}/' + id + '/approve'
                : '{{ url('admin/password-reset-requests') }}/' + id + '/ignore';
            var item = document.getElementById('pwr-item-' + id);
            if (item) item.style.opacity = '0.5';
            fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (item) item.remove();
                if (typeof window.showAppToast === 'function') {
                    window.showAppToast((data && data.message) ? data.message : (action === 'approve' ? 'Approved. The email reset link was sent.' : 'Request ignored.'));
                }
                // Refresh badge
                loadRequests();
            })
            .catch(function() {
                if (item) item.style.opacity = '1';
                if (typeof window.showAppToast === 'function') {
                    window.showAppToast('Action failed. Please try again.');
                }
            });
        };

        // Poll every 60s for new requests
        function pollBadge() {
            fetch('{{ route('admin.password-reset-requests') }}', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.ok ? r.json() : Promise.reject(); })
            .then(function(data) { updateBadge(data.count || 0); })
            .catch(function() {});
        }
        pollBadge();
        setInterval(pollBadge, 10000);
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) pollBadge();
        });
    })();
    </script>
    @endif
    <script>
    (function() {
        var header = document.getElementById('app-top-header');
        if (header) header.classList.remove('header-hidden-on-scroll');

        var btn = document.getElementById('header-profile-button');
        var menu = document.getElementById('header-profile-menu');
        var chevron = document.getElementById('header-profile-chevron');
        if (btn && menu) {
            function setOpen(open) {
                if (open) {
                    menu.classList.remove('opacity-0', 'translate-y-1', 'scale-95', 'pointer-events-none');
                    menu.classList.add('opacity-100', 'translate-y-0', 'scale-100', 'pointer-events-auto');
                    menu.setAttribute('data-open', '1');
                    btn.setAttribute('aria-expanded', 'true');
                    if (chevron) chevron.classList.add('rotate-180');
                } else {
                    menu.classList.add('opacity-0', 'translate-y-1', 'scale-95', 'pointer-events-none');
                    menu.classList.remove('opacity-100', 'translate-y-0', 'scale-100', 'pointer-events-auto');
                    menu.setAttribute('data-open', '0');
                    btn.setAttribute('aria-expanded', 'false');
                    if (chevron) chevron.classList.remove('rotate-180');
                }
            }
            function toggleMenu() {
                var isOpen = menu.getAttribute('data-open') === '1';
                setOpen(!isOpen);
            }
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleMenu();
            });
            document.addEventListener('click', function(e) {
                if (menu.getAttribute('data-open') === '1' && !btn.contains(e.target) && !menu.contains(e.target)) {
                    setOpen(false);
                }
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    setOpen(false);
                }
            });
        }

        // Global handler for notifications table and dropdown (works even with DataTables / dynamic content)
        document.addEventListener('click', function(e) {
            var headerItem = e.target.closest('.header-notification-item');
            if (headerItem && typeof window.updateHeaderNotificationBadge === 'function') {
                if (headerItem.dataset.read !== '1') {
                    headerItem.dataset.read = '1';
                    headerItem.classList.add('header-notification-item-read');
                    var label = headerItem.querySelector('.header-notification-status-label');
                    if (label) label.textContent = 'Read';
                    window.updateHeaderNotificationBadge(-1);
                }
                // If it's a help message notification, open the help modal on that thread
                var helpBtn = e.target.closest('.header-help-msg-btn');
                if (helpBtn) {
                    e.preventDefault();
                    var senderId = helpBtn.getAttribute('data-sender-id');
                    var senderName = helpBtn.getAttribute('data-sender-name');
                    // Close bell panel
                    var bellPanel = document.getElementById('header-notifications-panel');
                    if (bellPanel) { bellPanel.setAttribute('data-open','0'); bellPanel.classList.add('opacity-0','translate-y-1','scale-95','pointer-events-none'); bellPanel.classList.remove('opacity-100','translate-y-0','scale-100','pointer-events-auto'); }
                    // Open IT help modal on the right thread
                    var itModal = document.getElementById('it-help-request-modal');
                    if (itModal && senderId && typeof window._itHelpOpenThread === 'function') {
                        window._itHelpOpenThread(senderId, senderName);
                    } else if (itModal) {
                        var openBtn = document.getElementById('it-help-request-btn');
                        if (openBtn) openBtn.click();
                    }
                }
                return;
            }

            var viewLink = e.target.closest('.notification-view-link');
            var deleteBtn = e.target.closest('.notification-delete-btn');
            if (!viewLink && !deleteBtn) return;

            var row = e.target.closest('tr.notification-row');
            if (!row) return;

            if (viewLink) {
                // Mark as read visually when clicking View
                if (row.dataset.read !== '1') {
                    row.dataset.read = '1';
                    row.classList.remove('notification-row-unread');
                    row.classList.add('notification-row-read');
                    if (typeof window.updateHeaderNotificationBadge === 'function') {
                        window.updateHeaderNotificationBadge(-1);
                    }
                }
                return; // allow normal navigation
            }

            if (deleteBtn) {
                // Delete this notification row from the table; treat as read if it wasn't already
                if (row.dataset.read !== '1') {
                    row.dataset.read = '1';
                    row.classList.remove('notification-row-unread');
                    row.classList.add('notification-row-read');
                    if (typeof window.updateHeaderNotificationBadge === 'function') {
                        window.updateHeaderNotificationBadge(-1);
                    }
                }
                if (row.parentNode) {
                    row.parentNode.removeChild(row);
                }
            }
        });
    })();
    </script>
    <script>
    (function() {
        // AJAX accept ticket (no full reload). Works with normal + DataTables-rendered forms.
        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (!form || !(form instanceof HTMLFormElement)) return;
            if (!form.classList.contains('ajax-accept-ticket-form')) return;
            if (typeof window.csrfToken !== 'string' || !window.csrfToken) return;

            e.preventDefault();

            var submitBtn = form.querySelector('button[type="submit"]');
            var spinnerSvg = '<svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>';
            var checkSvg   = '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = spinnerSvg;
                submitBtn.title = 'Accepting…';
            }

            fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                },
                body: new FormData(form)
            })
                .then(function(r) { return r.ok ? r.json() : r.json().catch(function() { return {}; }).then(function(x){ throw x; }); })
                .then(function(data) {
                    if (typeof window.showAppToast === 'function') {
                        window.showAppToast((data && data.message) ? data.message : 'Ticket accepted');
                    }
                    if (typeof window.refreshMainContentNow === 'function') window.refreshMainContentNow();
                    else if (typeof window.refreshMainContent === 'function') window.refreshMainContent(true);
                    if (typeof window.refreshHeaderNotifications === 'function') window.refreshHeaderNotifications();
                    if (typeof window.refreshNotificationsPage === 'function') window.refreshNotificationsPage();
                })
                .catch(function(err) {
                    if (typeof window.showAppToast === 'function') {
                        window.showAppToast((err && err.message) ? err.message : 'Could not accept ticket');
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = checkSvg;
                        submitBtn.title = 'Accept';
                    }
                });
        }, true);
    })();
    </script>
    <script>
    (function() {
        // AJAX close ticket (Resolved -> Closed) from tickets index.
        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (!form || !(form instanceof HTMLFormElement)) return;
            if (!form.classList.contains('ajax-close-ticket-form')) return;
            if (typeof window.csrfToken !== 'string' || !window.csrfToken) return;

            e.preventDefault();

            var submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.dataset.prevText = submitBtn.textContent || '';
                submitBtn.textContent = 'Closing...';
            }

            fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                },
                body: new FormData(form)
            })
                .then(function(r) { return r.ok ? r.json() : r.json().catch(function() { return {}; }).then(function(x){ throw x; }); })
                .then(function(data) {
                    if (typeof window.showAppToast === 'function') {
                        window.showAppToast((data && data.message) ? data.message : 'Ticket closed');
                    }
                    if (typeof window.refreshMainContentNow === 'function') window.refreshMainContentNow();
                    else if (typeof window.refreshMainContent === 'function') window.refreshMainContent(true);
                    if (typeof window.refreshHeaderNotifications === 'function') window.refreshHeaderNotifications();
                    if (typeof window.refreshNotificationsPage === 'function') window.refreshNotificationsPage();
                })
                .catch(function(err) {
                    if (typeof window.showAppToast === 'function') {
                        window.showAppToast((err && err.message) ? err.message : 'Could not close ticket');
                    }
                })
                .finally(function() {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = submitBtn.dataset.prevText || 'Close';
                    }
                });
        }, true);
    })();
    </script>
    @auth
    <script>
    {{-- Staff Announcements: modal open/close/submit/delete — global so SPA nav works --}}
    (function() {
        function annModal()     { return document.getElementById('new-announcement-modal'); }        function annForm()      { return document.getElementById('new-announcement-form'); }
        function annErrBox()    { return document.getElementById('announcement-modal-error'); }
        function annSubmitBtn() { return document.getElementById('announcement-modal-submit'); }
        function annAudience()  { return document.getElementById('ann_audience'); }
        function annUsersWrap() { return document.getElementById('ann_users_wrap'); }
        function annUsersSel()  { return document.querySelectorAll('#ann_selected_user_ids input[name="selected_user_ids[]"]'); }

        function syncAnnouncementAudienceUi() {
            var audience = annAudience();
            var wrap = annUsersWrap();
            var users = annUsersSel();
            if (!audience || !wrap || !users.length) return;
            var useSelectedUsers = audience.value === '{{ \App\Models\StaffAnnouncement::AUDIENCE_SELECTED_USERS }}';
            wrap.classList.toggle('hidden', !useSelectedUsers);
            if (!useSelectedUsers) {
                Array.prototype.forEach.call(users, function (input) { input.checked = false; });
            }
        }

        function hasSelectedAnnouncementUsers() {
            var users = annUsersSel();
            if (!users.length) return false;
            return Array.prototype.some.call(users, function (input) { return !!input.checked; });
        }

        function openAnnModal() {
            var m = annModal(); if (!m) return;
            var f = annForm(); if (f) f.reset();
            var e = annErrBox(); if (e) e.classList.add('hidden');
            syncAnnouncementAudienceUi();
            m.classList.remove('hidden'); m.classList.add('flex');
            setTimeout(function(){ var t = document.getElementById('ann_title'); if (t) t.focus(); }, 50);
        }
        function closeAnnModal() {
            var m = annModal(); if (!m) return;
            m.classList.add('hidden'); m.classList.remove('flex');
        }

        document.addEventListener('click', function(e) {
            if (e.target.closest('#new-announcement-btn')) { e.stopPropagation(); openAnnModal(); return; }
            if (e.target.closest('#announcement-modal-close') || e.target.closest('#announcement-modal-cancel')) { closeAnnModal(); return; }
            var m = annModal();
            if (m && !m.classList.contains('hidden') && e.target === m) closeAnnModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') { var m = annModal(); if (m && !m.classList.contains('hidden')) closeAnnModal(); }
        });

        document.addEventListener('change', function(e) {
            if (e.target && e.target.id === 'ann_audience') syncAnnouncementAudienceUi();
        });

        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (!form || form.id !== 'new-announcement-form') return;
            e.preventDefault();
            var errBox = annErrBox(), submitBtn = annSubmitBtn();
            if (errBox) errBox.classList.add('hidden');
            if (annAudience() && annAudience().value === '{{ \App\Models\StaffAnnouncement::AUDIENCE_SELECTED_USERS }}' && !hasSelectedAnnouncementUsers()) {
                if (errBox) {
                    errBox.textContent = 'Please select at least one user.';
                    errBox.classList.remove('hidden');
                }
                return;
            }
            if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Sending…'; }
            var csrf = window.csrfToken || (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
            fetch(form.action, {
                method: 'POST', credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: new FormData(form)
            })
            .then(function(r) { return r.json().then(function(d) { if (!r.ok) throw d; return d; }); })
            .then(function(data) {
                closeAnnModal();
                if (window.showAppToast) window.showAppToast(data.message || 'Announcement sent.');
                if (typeof window.refreshMainContentNow === 'function') window.refreshMainContentNow();
                else if (typeof window.refreshMainContent === 'function') window.refreshMainContent(true);
                if (typeof window.refreshHeaderNotifications === 'function') window.refreshHeaderNotifications();
                if (typeof window.refreshNotificationsPage === 'function') window.refreshNotificationsPage();
            })
            .catch(function(err) {
                if (errBox) {
                    var msg = (err && err.message) ? err.message : 'Could not send announcement.';
                    if (err && err.errors) msg = Object.values(err.errors).flat().join(' ');
                    errBox.textContent = msg; errBox.classList.remove('hidden');
                }
            })
            .finally(function() {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Send announcement';
                }
            });
        });

        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.ann-delete-btn');
            if (!btn) return;
            var url = btn.dataset.url;
            var csrf = window.csrfToken || (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
            // Use the styled delete modal
            var modal = document.getElementById('index-delete-modal');
            var titleEl = document.getElementById('index-delete-modal-title');
            var msgEl = document.getElementById('index-delete-modal-message');
            if (titleEl) titleEl.textContent = 'Delete this announcement?';
            if (msgEl) msgEl.textContent = 'This cannot be undone.';
            if (modal) {
                modal.classList.remove('hidden'); modal.classList.add('flex');
                modal._annBtn = btn; modal._annUrl = url; modal._annCsrf = csrf;
                modal._mode = 'announcement';
            }
        });
    })();
    </script>
    @if(auth()->check() && auth()->user()->isAdmin())
    <script>
    (function() {
        function fadeOutAndRemove(element, done) {
            if (!element) { if (done) done(); return; }
            element.style.transition = 'opacity .24s ease, transform .24s ease';
            element.style.opacity = '0';
            element.style.transform = 'translateY(-6px) scale(.985)';
            setTimeout(function() {
                if (element && element.parentNode) element.parentNode.removeChild(element);
                if (done) done();
            }, 250);
        }

        // ── Ticket delete modal ──
        var delModal = document.getElementById('index-delete-modal');
        var delCurrentForm = null;

        // Global helper: showDeleteConfirm(title, message, onConfirm)
        window.showDeleteConfirm = function(title, message, onConfirm) {
            if (!delModal) { if (onConfirm) onConfirm(); return; }
            var titleEl = document.getElementById('index-delete-modal-title');
            var msgEl = document.getElementById('index-delete-modal-message');
            if (titleEl) titleEl.textContent = title || 'Confirm delete';
            if (msgEl) msgEl.textContent = message || 'This action cannot be undone.';
            delModal._mode = 'custom';
            delModal._customCallback = onConfirm;
            delCurrentForm = null;
            delModal.classList.remove('hidden'); delModal.classList.add('flex');
        };

        if (delModal) {
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('.index-delete-ticket-btn');
                if (btn) {
                    e.stopPropagation();
                    if (typeof window.closeAllTicketActionMenus === 'function') window.closeAllTicketActionMenus();
                    delCurrentForm = document.getElementById(btn.getAttribute('data-form-id'));
                    if (delCurrentForm) { delModal.classList.remove('hidden'); delModal.classList.add('flex'); }
                    return;
                }
                if (e.target.closest('#index-delete-modal-cancel') || e.target === delModal) {
                    delModal.classList.add('hidden'); delModal.classList.remove('flex'); delCurrentForm = null; delModal._mode = null; delModal._customCallback = null;
                }
                var confirmBtn = e.target.closest('#index-delete-modal-confirm');
                if (confirmBtn) {
                    // Custom callback mode (categories, priorities, etc.)
                    if (delModal._mode === 'custom') {
                        var cb = delModal._customCallback;
                        delModal.classList.add('hidden'); delModal.classList.remove('flex');
                        delModal._mode = null; delModal._customCallback = null;
                        if (cb) cb();
                        return;
                    }
                    // Announcement delete mode
                    if (delModal._mode === 'announcement') {
                        var annBtn = delModal._annBtn;
                        var annUrl = delModal._annUrl;
                        var annCsrf = delModal._annCsrf;
                        delModal.classList.add('hidden'); delModal.classList.remove('flex');
                        delModal._mode = null;
                        if (!annBtn || !annUrl) return;
                        annBtn.disabled = true;
                        fetch(annUrl, {
                            method: 'DELETE', credentials: 'same-origin',
                            headers: { 'X-CSRF-TOKEN': annCsrf, 'Accept': 'application/json' }
                        })
                        .then(function(r) { return r.ok ? r.json() : Promise.reject(); })
                        .then(function(data) {
                            if (window.showAppToast) window.showAppToast(data.message || 'Deleted.');
                            var row = annBtn.closest('tr');
                            fadeOutAndRemove(row, function() {
                                if (typeof window.refreshMainContentNow === 'function') window.refreshMainContentNow();
                                else if (typeof window.refreshMainContent === 'function') window.refreshMainContent(true);
                                if (typeof window.refreshHeaderNotifications === 'function') window.refreshHeaderNotifications();
                                if (typeof window.refreshNotificationsPage === 'function') window.refreshNotificationsPage();
                            });
                        })
                        .catch(function() { annBtn.disabled = false; if (window.showAppToast) window.showAppToast('Could not delete.'); });
                        return;
                    }
                    // Ticket delete mode
                    if (!delCurrentForm) return;
                    confirmBtn.disabled = true; confirmBtn.textContent = 'Deleting…';
                    var csrf = window.csrfToken || (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
                    fetch(delCurrentForm.action, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: new FormData(delCurrentForm)
                    })
                    .then(function(r) { return r.json().catch(function() { return {}; }); })
                    .then(function(data) {
                        var ticketRow = delCurrentForm ? delCurrentForm.closest('tr') : null;
                        delModal.classList.add('hidden'); delModal.classList.remove('flex'); delCurrentForm = null;
                        if (window.showAppToast) window.showAppToast((data && data.message) ? data.message : 'Ticket deleted.');
                        fadeOutAndRemove(ticketRow, function() {
                            if (typeof window.refreshMainContentNow === 'function') window.refreshMainContentNow();
                            else if (typeof window.refreshMainContent === 'function') window.refreshMainContent(true);
                            if (typeof window.refreshHeaderNotifications === 'function') window.refreshHeaderNotifications();
                            if (typeof window.refreshNotificationsPage === 'function') window.refreshNotificationsPage();
                        });
                    })
                    .catch(function() {
                        confirmBtn.disabled = false; confirmBtn.textContent = 'Delete';
                        if (window.showAppToast) window.showAppToast('Could not delete ticket.');
                    });
                }
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !delModal.classList.contains('hidden')) {
                    delModal.classList.add('hidden'); delModal.classList.remove('flex'); delCurrentForm = null; delModal._mode = null; delModal._customCallback = null;
                }
            });
        }

        // ── Audit trail modal ──
        var auditModal = document.getElementById('audit-modal');
        if (auditModal) {
            var auditBadges = {
                blue:'bg-blue-100 text-blue-800',
                emerald:'bg-emerald-100 text-emerald-800',
                amber:'bg-amber-100 text-amber-800',
                indigo:'bg-indigo-100 text-indigo-800',
                cyan:'bg-cyan-100 text-cyan-800',
                rose:'bg-rose-100 text-rose-800',
                slate:'bg-slate-100 text-slate-700'
            };
            function openAuditModal(url) {
                var loading = document.getElementById('audit-modal-loading');
                var content = document.getElementById('audit-modal-content');
                var tbody   = document.getElementById('audit-modal-tbody');
                var empty   = document.getElementById('audit-modal-empty');
                if (loading) loading.classList.remove('hidden');
                if (content) content.classList.add('hidden');
                if (tbody)   tbody.innerHTML = '';
                auditModal.classList.remove('hidden'); auditModal.classList.add('flex');
                var requestUrl = (url.indexOf('?') === -1 ? url + '?ajax=1' : url + '&ajax=1');
                fetch(requestUrl, { credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'} })
                .then(function(r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.text().then(function(t) {
                        try { return JSON.parse(t); } catch (e) { throw new Error('Invalid JSON response'); }
                    });
                })
                .then(function(data) {
                    var t = document.getElementById('audit-modal-title');
                    var s = document.getElementById('audit-modal-subtitle');
                    var dl = document.getElementById('audit-modal-download');
                    var pr = document.getElementById('audit-modal-print');
                    if (t) t.textContent = 'User activity — ' + data.user.name;
                    if (s) s.textContent = data.user.email;
                    if (dl) dl.href = data.download_url;
                    if (pr) pr.href = data.print_url;
                    var entries = Array.isArray(data.entries) ? data.entries : (Array.isArray(data.comments) ? data.comments : []);
                    if (!entries.length) {
                        if (empty) { empty.textContent = 'No user actions recorded yet.'; empty.classList.remove('hidden'); }
                    } else {
                        if (empty) empty.classList.add('hidden');
                        entries.forEach(function(c) {
                            var tr = document.createElement('tr');
                            var ticketCell = c.ticket_url
                                ? '<a href="'+c.ticket_url+'" class="font-medium text-blue-600 hover:text-blue-700">'+c.ticket_number+'</a><span class="block truncate max-w-[10rem] text-slate-500 text-xs">'+(c.ticket_title||'')+'</span>'
                                : '<span class="text-slate-400">Auth event</span>';
                            var badge = auditBadges[c.badge] || auditBadges.slate;
                            var details = c.details || c.body || '';
                            var body = details.length > 80 ? details.substring(0,80)+'…' : details;
                            tr.innerHTML = '<td class="px-4 py-3">'+ticketCell+'</td>'
                                +'<td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium '+badge+'">'+c.type+'</span></td>'
                                +'<td class="px-4 py-3 text-slate-600 text-xs max-w-xs" title="'+details.replace(/"/g,'&quot;')+'">'+body+'</td>'
                                +'<td class="px-4 py-3 text-slate-500 text-xs whitespace-nowrap">'+(c.date_formatted || c.date || '')+'</td>';
                            if (tbody) tbody.appendChild(tr);
                        });
                    }
                    if (loading) loading.classList.add('hidden');
                    if (content) content.classList.remove('hidden');
                })
                .catch(function() {
                    if (loading) loading.classList.add('hidden');
                    if (content) content.classList.remove('hidden');
                    if (empty) { empty.textContent = 'Failed to load data.'; empty.classList.remove('hidden'); }
                });
            }
            function closeAuditModal() { auditModal.classList.add('hidden'); auditModal.classList.remove('flex'); }
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('.audit-view-btn');
                if (btn) { e.stopPropagation(); openAuditModal(btn.dataset.url); return; }
                if (e.target.closest('#audit-modal-close') || e.target === auditModal) closeAuditModal();
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !auditModal.classList.contains('hidden')) closeAuditModal();
            });
        }

        // ── Add / Edit User modals ──
        var addModal = document.getElementById('add-user-modal');
        var editModal = document.getElementById('edit-user-modal');

        function closeAddModal() { if (addModal) { addModal.classList.add('hidden'); addModal.classList.remove('flex'); } }
        function closeEditModal() { if (editModal) { editModal.classList.add('hidden'); editModal.classList.remove('flex'); } }

        document.addEventListener('click', function(e) {
            // Add user open
            if (e.target.closest('#add-user-btn')) {
                e.stopPropagation();
                var f = document.getElementById('add-user-form'); if (f) f.reset();
                var err = document.getElementById('add-user-error'); if (err) err.classList.add('hidden');
                if (addModal) { addModal.classList.remove('hidden'); addModal.classList.add('flex'); }
                setTimeout(function(){ var n=document.getElementById('add-user-name'); if(n) n.focus(); }, 50);
                return;
            }
            if (e.target.closest('#add-user-close') || e.target.closest('#add-user-cancel') || e.target === addModal) { closeAddModal(); return; }

            // Edit user open
            var editBtn = e.target.closest('.user-edit-btn');
            if (editBtn) {
                e.stopPropagation();
                var d = editBtn.dataset;
                var ef = document.getElementById('edit-user-form');
                if (ef) ef.action = d.userUpdateUrl;
                var em = document.getElementById('edit-user-email'); if (em) em.value = d.userEmail;
                var r = document.getElementById('edit-user-role'); if (r) r.value = d.userRole;
                var np = document.getElementById('edit-user-new-password'); if (np) np.value = '';
                var nc = document.getElementById('edit-user-new-password-confirm'); if (nc) nc.value = '';
                var cp = document.getElementById('edit-user-clear-password'); if (cp) cp.checked = false;
                var pw = document.getElementById('edit-user-password-section');
                if (pw) pw.classList.toggle('hidden', d.userIsSelf === '1' || d.userHasPassword !== '1');
                var ee = document.getElementById('edit-user-error'); if (ee) ee.classList.add('hidden');
                if (editModal) { editModal.classList.remove('hidden'); editModal.classList.add('flex'); }
                setTimeout(function(){ var eei=document.getElementById('edit-user-email'); if(eei) eei.focus(); }, 50);
                return;
            }
            if (e.target.closest('#edit-user-close') || e.target.closest('#edit-user-cancel') || e.target === editModal) { closeEditModal(); return; }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') return;
            if (addModal && !addModal.classList.contains('hidden')) closeAddModal();
            if (editModal && !editModal.classList.contains('hidden')) closeEditModal();
        });

        // Add user submit
        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (!form) return;
            if (form.id === 'add-user-form') {
                e.preventDefault();
                var err = document.getElementById('add-user-error');
                var btn = document.getElementById('add-user-submit');
                if (err) err.classList.add('hidden');
                if (btn) { btn.disabled = true; btn.textContent = 'Creating…'; }
                fetch(form.action, { method:'POST', credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':window.csrfToken||'','Accept':'application/json'}, body:new FormData(form) })
                .then(function(r){ return r.ok ? r.json() : r.json().catch(function(){return{};}).then(function(x){throw x;}); })
                .then(function(d){ closeAddModal(); if(window.showAppToast) window.showAppToast(d.message||'User created.'); setTimeout(function(){location.reload();},800); })
                .catch(function(ex){ if(err){ var m=(ex&&ex.message)?ex.message:'Could not create user.'; if(ex&&ex.errors) m=Object.values(ex.errors).flat().join(' '); err.textContent=m; err.classList.remove('hidden'); } })
                .finally(function(){ if(btn){btn.disabled=false;btn.textContent='Create user';} });
            }
            if (form.id === 'edit-user-form') {
                e.preventDefault();
                var err = document.getElementById('edit-user-error');
                var btn = document.getElementById('edit-user-submit');
                if (err) err.classList.add('hidden');
                if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }
                var fd = new FormData(form); fd.set('_method','PUT');
                fetch(form.action, { method:'POST', credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':window.csrfToken||'','Accept':'application/json'}, body:fd })
                .then(function(r){ return r.ok ? r.json() : r.json().catch(function(){return{};}).then(function(x){throw x;}); })
                .then(function(d){ closeEditModal(); if(window.showAppToast) window.showAppToast(d.message||'User updated.'); setTimeout(function(){location.reload();},800); })
                .catch(function(ex){ if(err){ var m=(ex&&ex.message)?ex.message:'Could not save changes.'; if(ex&&ex.errors) m=Object.values(ex.errors).flat().join(' '); err.textContent=m; err.classList.remove('hidden'); } })
                .finally(function(){ if(btn){btn.disabled=false;btn.textContent='Save changes';} });
            }
        });
    })();
    </script>
    @endif
    @endauth
</body>
</html>


