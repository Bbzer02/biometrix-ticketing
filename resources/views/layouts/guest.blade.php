<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sign in') — IT Helpdesk</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('main-logo-icon.png') }}?v={{ now()->timestamp }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('main-logo-icon.png') }}?v={{ now()->timestamp }}">
    <link rel="apple-touch-icon" href="{{ asset('main-logo-icon.png') }}?v={{ now()->timestamp }}">
    <style>
        /* Login page: video background + smoky glass card */
        .login-page-wrap {
            min-height: 100vh;
            width: 100%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: #000;
            overflow: hidden;
        }
        .login-video-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }
        .login-video-bg video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .login-video-overlay {
            position: absolute;
            inset: 0;
            background: transparent;
            z-index: 1;
        }
        .login-card {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: row;
            width: 100%;
            max-width: 920px;
            min-height: 480px;
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(22px) saturate(140%);
            -webkit-backdrop-filter: blur(22px) saturate(140%);
            border: none;
            border-radius: 1rem;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.35);
            overflow: hidden;
        }
        .login-card-left {
            flex: 0 0 48%;
            min-width: 0;
            position: relative;
            background: #0a0a0a;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            overflow: hidden;
        }
        .login-card-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 80% 50% at 50% 50%, rgba(84,172,191,0.15) 0%, transparent 50%),
                        radial-gradient(ellipse 60% 80% at 80% 20%, rgba(167,235,242,0.12) 0%, transparent 45%),
                        radial-gradient(ellipse 50% 60% at 20% 80%, rgba(38,101,140,0.18) 0%, transparent 45%);
            animation: bg-glow-shift 12s ease-in-out infinite;
            z-index: 0;
        }
        @keyframes bg-glow-shift {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.85; transform: scale(1.05); }
        }
        .login-welcome-video {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }
        .login-welcome-video-overlay {
            position: absolute;
            inset: 0;
            background: transparent;
            z-index: 1;
        }
        .login-welcome-wrap {
            position: relative;
            z-index: 2;
            padding: 1rem;
            max-width: 360px;
        }
        .login-welcome-text {
            font-size: clamp(1.5rem, 4vw, 2.25rem);
            font-weight: 700;
            color: #fff;
            text-align: center;
            line-height: 1.3;
            margin-bottom: 2rem;
            text-shadow: 0 0 20px rgba(167,235,242,0.3);
        }
        .login-tagline-wrap {
            min-height: 5.5rem;
            position: relative;
            margin: 0;
            padding-top: 0.5rem;
        }
        .login-tagline-slides {
            position: relative;
            width: 100%;
            min-height: 5rem;
        }
        .login-tagline-line {
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            font-size: 1.05rem;
            line-height: 1.6;
            text-align: center;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.5s ease, transform 0.5s ease;
            pointer-events: none;
        }
        .login-tagline-line:nth-child(1) { color: #A7EBF2; }
        .login-tagline-line:nth-child(2) { color: #54ACBF; }
        .login-tagline-line:nth-child(3) { color: #f59e0b; }
        .login-tagline-line:nth-child(4) { color: #a78bfa; }
        .login-tagline-line:nth-child(5) { color: #34d399; }
        .login-tagline-line.active {
            position: relative;
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
            animation: tagline-glow 2s ease-in-out infinite;
        }
        @keyframes tagline-glow {
            0%, 100% { text-shadow: 0 0 12px currentColor; }
            50% { text-shadow: 0 0 24px currentColor, 0 0 36px currentColor; }
        }
        .login-card-right {
            flex: 1;
            min-width: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 3rem;
            background: rgba(255, 255, 255, 0.35);
            color: #000;
            text-shadow: 0 0 8px rgba(255,255,255,0.9);
        }
        @media (max-width: 768px) {
            .login-card { flex-direction: column; min-height: auto; }
            .login-card-left { display: none; }
            .login-card-right { padding: 2rem; }
        }
        .login-form-panel {
            background: transparent;
            border-radius: 0;
            box-shadow: none;
            padding: 0;
            width: 100%;
            max-width: 100%;
        }
        .login-form-panel .label-guest {
            color: #000;
            text-shadow: 0 0 8px rgba(255,255,255,0.9);
        }
        .login-form-panel input[type="checkbox"]:focus { outline: none; box-shadow: 0 0 0 2px #26658C; }
        .login-form-panel .input-guest {
            margin-top: 0.5rem;
            display: block;
            width: 100%;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            background: #fff;
            padding: 1rem 1.25rem;
            font-size: 1.0625rem;
            color: #111827;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .login-form-panel .input-guest::placeholder { color: #9ca3af; }
        .login-form-panel .input-guest:focus {
            outline: none;
            border-color: #26658C;
            box-shadow: 0 0 0 3px rgba(38, 101, 140, 0.2);
        }
        .login-accent { color: #26658C; }
        .login-accent:hover { color: #023859; }
        /* Sign in button — Luna palette */
        .login-btn-primary {
            width: 100%;
            padding: 15px 20px;
            border: none;
            outline: none;
            background-color: #000;
            color: #fff;
            border-radius: 7px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.25s ease-out;
        }
        .login-btn-primary:hover {
            background-color: #111;
            transform: translateY(-3px);
        }
        .login-btn-primary .button-span {
            color: rgba(255,255,255,0.85);
        }
        .login-brand-inline {
            font-weight: 600;
            font-size: 1.125rem;
            color: #000;
            text-shadow: 0 0 8px rgba(255,255,255,0.9);
        }
        .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }
        /* New loading screen — From Uiverse.io by mrpumps31232 */
        #login-loading-screen {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: #0f0f0f;
            transition: opacity 0.35s ease-out;
        }
        #login-loading-screen.loaded {
            opacity: 0;
            pointer-events: none;
        }
        /* Slide-in from landing page */
        body.slide-in-from-landing {
            animation: slide-in-body 0.45s cubic-bezier(0.77, 0, 0.18, 1) forwards;
        }
        @keyframes slide-in-body {
            from { transform: translateX(6%); opacity: 0.6; }
            to   { transform: translateX(0);  opacity: 1; }
        }
        .loading-wave {
            width: 300px;
            height: 100px;
            display: flex;
            justify-content: center;
            align-items: flex-end;
        }
        .loading-bar {
            width: 20px;
            height: 10px;
            margin: 0 5px;
            background-color: #3498db;
            border-radius: 5px;
            animation: loading-wave-animation 1s ease-in-out infinite;
        }
        .loading-bar:nth-child(2) { animation-delay: 0.1s; }
        .loading-bar:nth-child(3) { animation-delay: 0.2s; }
        .loading-bar:nth-child(4) { animation-delay: 0.3s; }
        @keyframes loading-wave-animation {
            0% { height: 10px; }
            50% { height: 50px; }
            100% { height: 10px; }
        }
        #login-loading-status {
            margin-top: 1.5rem;
            font-size: 1.25rem;
            font-weight: 600;
            color: #3498db;
            text-align: center;
            min-height: 1.5em;
        }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=Exo+2:wght@400;500;600;700&family=Audiowide&family=Share+Tech+Mono&family=Inter:wght@400;500;600;700&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            .input-guest { margin-top: 0.375rem; display: block; width: 100%; border-radius: 0.75rem; border: 1px solid #e2e8f0; background: #fff; padding: 0.75rem 1rem; font-size: 1rem; }
            .input-guest:focus { outline: none; border-color: #0f172a; box-shadow: 0 0 0 3px rgba(15,23,42,0.12); }
            .input-guest::placeholder { color: #94a3b8; }
            .label-guest { display: block; font-size: 0.875rem; font-weight: 600; color: inherit; }
        </style>
    @endif
</head>
<body class="min-h-screen text-slate-800 antialiased">
    <div id="login-loading-screen" aria-live="polite" aria-label="Loading">
        <div class="loading-wave">
            <div class="loading-bar"></div>
            <div class="loading-bar"></div>
            <div class="loading-bar"></div>
            <div class="loading-bar"></div>
        </div>
        <p id="login-loading-status" aria-live="polite"></p>
    </div>
    {{-- Login: one card, welcome + form, over video background --}}
    <div class="login-page-wrap">
        <div class="login-video-bg" aria-hidden="true">
            <video autoplay muted loop playsinline>
                <source src="{{ asset('video/login-background.mp4') }}" type="video/mp4">
            </video>
            <div class="login-video-overlay"></div>
        </div>
        <div class="login-card">
            <aside class="login-card-left" aria-hidden="true">
                @if(file_exists(public_path('video/helpdesk.mp4')))
                <video class="login-welcome-video" autoplay muted loop playsinline>
                    <source src="{{ asset('video/helpdesk.mp4') }}" type="video/mp4">
                </video>
                <div class="login-welcome-video-overlay" aria-hidden="true"></div>
                @endif
                <div class="login-welcome-wrap">
                    <div class="login-welcome-text">Welcome to IT Helpdesk</div>
                    <div class="login-tagline-wrap">
                        <div class="login-tagline-slides">
                            <div class="login-tagline-line active">Our IT Helpdesk is here to provide</div>
                            <div class="login-tagline-line">reliable technical support,</div>
                            <div class="login-tagline-line">system assistance, and timely solutions</div>
                            <div class="login-tagline-line">to ensure smooth and uninterrupted</div>
                            <div class="login-tagline-line">digital operations.</div>
                        </div>
                    </div>
                </div>
            </aside>
            <main class="login-card-right">
                @yield('content')
            </main>
        </div>
    </div>
    <script>
        (function() {
            var loadingScreen = document.getElementById('login-loading-screen');
            var skipFromLogout = {{ request()->get('from') === 'logout' ? 'true' : 'false' }};
            var skipFromLanding = {{ request()->get('from') === 'landing' ? 'true' : 'false' }};
            if ((skipFromLogout || skipFromLanding) && loadingScreen) {
                loadingScreen.classList.add('loaded');
            }
            if (skipFromLanding) {
                document.body.classList.add('slide-in-from-landing');
            }
            var LOADING_DURATION_MS = 3000;
            var shownAt = Date.now();
            function hideLoading() {
                var elapsed = Date.now() - shownAt;
                var wait = Math.max(0, LOADING_DURATION_MS - elapsed);
                setTimeout(function() {
                    if (loadingScreen) loadingScreen.classList.add('loaded');
                }, wait);
            }
            if (!skipFromLogout && !skipFromLanding) {
                if (document.readyState === 'complete') hideLoading();
                else window.addEventListener('load', hideLoading);
            }

            var CREDENTIAL_CHECK_DURATION_MS = 3000;

            document.addEventListener('submit', function(e) {
                var form = e.target;
                if (!form || form.tagName !== 'FORM') return;
                var action = (form.getAttribute('action') || form.action || '').toLowerCase();
                if (action.indexOf('/login') === -1) return;

                e.preventDefault();
                var statusEl = document.getElementById('login-loading-status');
                if (statusEl) statusEl.textContent = '';
                if (loadingScreen) {
                    loadingScreen.classList.remove('loaded');
                    loadingScreen.style.display = 'flex';
                    loadingScreen.style.opacity = '1';
                }
                var checkStartedAt = Date.now();

                var formData = new FormData(form);
                var token = form.querySelector('input[name="_token"]');
                token = token ? token.value : '';

                function setStatus(text) {
                    if (statusEl) statusEl.textContent = text;
                }

                function afterMinimumDuration(callback) {
                    var elapsed = Date.now() - checkStartedAt;
                    var wait = Math.max(0, CREDENTIAL_CHECK_DURATION_MS - elapsed);
                    setTimeout(callback, wait);
                }

                function showError(msg) {
                    setStatus('Wrong credentials');
                    afterMinimumDuration(function() {
                        var message = msg || 'The provided credentials do not match our records.';
                        window.location.href = '/login?error=' + encodeURIComponent(message) + '&from=logout';
                    });
                }

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    credentials: 'same-origin'
                }).then(function(response) {
                    if (response.ok) {
                        setStatus('Login approved');
                        return response.json().then(function(data) {
                            var url = data && data.redirect ? data.redirect : '{{ route('home') }}';
                            afterMinimumDuration(function() {
                                window.location.href = url;
                            });
                        }).catch(function() {
                            afterMinimumDuration(function() {
                                window.location.href = '{{ route('home') }}';
                            });
                        });
                    }
                    if (response.status === 422) {
                        return response.json().then(function(data) {
                            showError(data.message || 'The provided credentials do not match our records.');
                        }).catch(function() {
                            showError('The provided credentials do not match our records.');
                        });
                    }
                    setStatus('Wrong credentials');
                    showError('Something went wrong. Please try again.');
                }).catch(function() {
                    setStatus('Wrong credentials');
                    showError('Network error. Please check your connection and try again.');
                });
            }, true);
        })();
    </script>
    <script>
        (function() {
            var slides = document.querySelectorAll('.login-tagline-line');
            if (slides.length > 1) {
                var idx = 0;
                setInterval(function() {
                    slides[idx].classList.remove('active');
                    idx = (idx + 1) % slides.length;
                    slides[idx].classList.add('active');
                }, 3500);
            }
        })();
    </script>
</body>
</html>


