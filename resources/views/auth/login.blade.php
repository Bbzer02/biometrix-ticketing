<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Biometrix - Sign In</title>
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('main-logo-icon.png') }}?v={{ now()->timestamp }}">
<link rel="shortcut icon" type="image/png" href="{{ asset('main-logo-icon.png') }}?v={{ now()->timestamp }}">
<link rel="apple-touch-icon" href="{{ asset('main-logo-icon.png') }}?v={{ now()->timestamp }}">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root { --blue:#3B82F6;--blue-bright:#60A5FA;--blue-dim:rgba(59,130,246,0.15);--green:#10B981;--amber:#F59E0B;--red:#EF4444;--bg:#080C12;--card:rgba(12,18,28,0.85);--border:rgba(59,130,246,0.15);--text:#E8EDF5;--muted:#6B7A94;--input-bg:rgba(255,255,255,0.04);}
  html, body {height:100%;width:100%;background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;overflow:hidden;}
  #bg-canvas {position:fixed;inset:0;z-index:0;pointer-events:none;}
  .grid-overlay {position:fixed;inset:0;z-index:1;background-image:linear-gradient(rgba(59,130,246,0.04) 1px, transparent 1px),linear-gradient(90deg, rgba(59,130,246,0.04) 1px, transparent 1px);background-size:48px 48px;pointer-events:none;}
  .blob {position:fixed;border-radius:50%;filter:blur(80px);pointer-events:none;z-index:1;animation:drift 8s ease-in-out infinite alternate;}
  .blob-1 {width:480px;height:480px;top:-120px;left:-100px;background:rgba(59,130,246,0.12);}
  .blob-2 {width:360px;height:360px;bottom:-80px;right:-60px;background:rgba(16,185,129,0.08);animation-delay:-3s;}
  .blob-3 {width:280px;height:280px;top:40%;left:55%;background:rgba(59,130,246,0.07);animation-delay:-5s;}
  @keyframes drift {from {transform:translate(0,0) scale(1);}to {transform:translate(20px, 15px) scale(1.05);}}
  .page {position:relative;z-index:10;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px;}
  .container {display:flex;gap:64px;align-items:center;max-width:1060px;width:100%;}
  .left {flex:1;min-width:0;}
  .logo {display:flex;align-items:center;gap:10px;margin-bottom:48px;}
  .logo-icon {width:40px;height:40px;border-radius:10px;background:var(--blue-dim);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;overflow:hidden;padding:3px;}
  .logo-icon img {width:100%;height:100%;object-fit:contain;display:block;}
  .logo-wordmark {font-family:'Syne',sans-serif;font-size:18px;font-weight:700;letter-spacing:0.06em;color:var(--text);}
  .logo-tag {font-size:10px;font-weight:500;letter-spacing:0.12em;color:var(--blue);background:var(--blue-dim);border:1px solid var(--border);border-radius:4px;padding:2px 7px;text-transform:uppercase;margin-left:4px;}
  .headline {font-family:'Syne',sans-serif;font-size:clamp(38px, 5vw, 58px);font-weight:800;line-height:1.08;color:#fff;margin-bottom:20px;}
  .headline .accent {color:var(--blue-bright);}
  .sub {font-size:15px;color:var(--muted);line-height:1.7;max-width:380px;margin-bottom:40px;}
  .status-row {display:flex;gap:10px;flex-wrap:wrap;}
  .chip {display:flex;align-items:center;gap:7px;padding:7px 14px;border-radius:9999px;border:1px solid;font-size:12px;font-weight:500;backdrop-filter:blur(8px);}
  .chip-dot {width:7px;height:7px;border-radius:50%;}
  .chip-dot.pulse {animation:pulse 2s ease-in-out infinite;}
  @keyframes pulse {0%,100% {opacity:1;box-shadow:0 0 0 0 currentColor;}50% {opacity:.7;box-shadow:0 0 0 4px transparent;}}
  .chip.green{color:var(--green);border-color:rgba(16,185,129,0.25);background:rgba(16,185,129,0.06);}
  .chip.blue{color:var(--blue-bright);border-color:rgba(96,165,250,0.25);background:rgba(59,130,246,0.07);}
  .chip.amber{color:var(--amber);border-color:rgba(245,158,11,0.25);background:rgba(245,158,11,0.07);}
  .chip .chip-dot {background:currentColor;}
  .card {width:380px;flex-shrink:0;background:var(--card);border:1px solid var(--border);border-radius:20px;padding:36px 32px;backdrop-filter:blur(24px);box-shadow:0 0 0 1px rgba(59,130,246,0.05), 0 32px 80px rgba(0,0,0,0.6);position:relative;overflow:hidden;}
  .card::before {content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(96,165,250,0.5),transparent);}
  .card-header {margin-bottom:28px;}
  .card-title {font-family:'Syne',sans-serif;font-size:22px;font-weight:700;margin-bottom:6px;}
  .card-sub {font-size:13px;color:var(--muted);}
  .bio-badge {display:flex;align-items:center;gap:10px;background:rgba(59,130,246,0.08);border:1px solid rgba(59,130,246,0.2);border-radius:12px;padding:11px 14px;margin-bottom:24px;cursor:pointer;transition:background 0.2s;}
  .bio-badge:hover {background:rgba(59,130,246,0.14);}
  .bio-icon {width:36px;height:36px;border-radius:8px;background:rgba(59,130,246,0.15);display:flex;align-items:center;justify-content:center;position:relative;}
  .bio-icon svg {width:18px;height:18px;}
  .bio-ring {position:absolute;inset:-3px;border-radius:10px;border:1.5px solid rgba(96,165,250,0.5);animation:bio-spin 3s linear infinite;border-top-color:transparent;border-right-color:transparent;}
  @keyframes bio-spin {to {transform:rotate(360deg);}}
  .bio-text {flex:1;}
  .bio-label {font-size:13px;font-weight:500;color:var(--blue-bright);}
  .bio-desc {font-size:11px;color:var(--muted);margin-top:1px;}
  .bio-status {font-size:11px;color:var(--green);display:flex;align-items:center;gap:4px;}
  .bio-dot {width:5px;height:5px;border-radius:50%;background:var(--green);animation:pulse 1.5s ease-in-out infinite;}
  .divider {display:flex;align-items:center;gap:12px;color:var(--muted);font-size:12px;margin-bottom:20px;}
  .divider::before,.divider::after {content:'';flex:1;height:1px;background:rgba(255,255,255,0.07);}
  .field {margin-bottom:16px;}
  .field-label {font-size:12px;font-weight:500;color:var(--muted);margin-bottom:6px;letter-spacing:0.03em;}
  .input-wrap {position:relative;}
  .input-wrap svg {position:absolute;left:13px;top:50%;transform:translateY(-50%);opacity:0.4;width:16px;height:16px;}
  .field input {width:100%;padding:11px 14px 11px 38px;background:var(--input-bg);border:1px solid rgba(255,255,255,0.08);border-radius:10px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:14px;outline:none;transition:border-color 0.2s, background 0.2s;}
  .field input::placeholder {color:rgba(255,255,255,0.2);}
  .field input:focus {border-color:rgba(96,165,250,0.45);background:rgba(59,130,246,0.06);}
  .btn-primary {width:100%;padding:12px;background:var(--blue);border:none;border-radius:10px;color:#fff;font-family:'Syne',sans-serif;font-size:14px;font-weight:600;letter-spacing:0.03em;cursor:pointer;margin-top:4px;margin-bottom:18px;position:relative;overflow:hidden;transition:background 0.2s, transform 0.1s;}
  .btn-primary::after {content:'';position:absolute;inset:0;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.12),transparent);transform:translateX(-100%);transition:transform 0.5s;}
  .btn-primary:hover {background:#2563EB;}
  .btn-primary:hover::after {transform:translateX(100%);}
  .btn-primary:active {transform:scale(0.98);}
  .signup-row {text-align:center;font-size:13px;color:var(--muted);}
  .signup-row a {color:var(--blue);text-decoration:none;font-weight:500;}
  .signup-row a:hover {text-decoration:underline;}
  .float-icons {position:fixed;inset:0;z-index:2;pointer-events:none;overflow:hidden;}
  .fi {position:absolute;opacity:0;animation:float-up linear infinite;}
  .fi svg {display:block;}
  @keyframes float-up {0%{opacity:0;transform:translateY(0) rotate(0deg);}10%{opacity:1;}90%{opacity:.6;}100%{opacity:0;transform:translateY(-100vh) rotate(120deg);}}
  .card-scan {position:absolute;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,rgba(96,165,250,0.6),transparent);top:0;animation:scan-down 4s ease-in-out infinite;pointer-events:none;}
  @keyframes scan-down {0%,100%{top:0;opacity:0;}10%{opacity:1;}90%{opacity:.3;}50%{top:100%;}}
  .stats {display:flex;gap:32px;margin-top:44px;}
  .stat-val {font-family:'Syne',sans-serif;font-size:22px;font-weight:800;color:#fff;}
  .stat-lbl {font-size:12px;color:var(--muted);margin-top:2px;}
  .error-box {border-radius:10px;padding:10px 12px;background:rgba(239,68,68,0.14);border:1px solid rgba(239,68,68,0.35);color:#fca5a5;font-size:12px;margin-bottom:14px;}
  @media (max-width: 820px) {.container{flex-direction:column;gap:32px;}.left{text-align:center;}.status-row{justify-content:center;}.sub{margin:0 auto 32px;}.stats{justify-content:center;}.card{width:100%;max-width:400px;}}
</style>
</head>
<body>
<canvas id="bg-canvas"></canvas>
<div class="grid-overlay"></div>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>
<div class="blob blob-3"></div>
<div class="float-icons" id="float-icons"></div>
<div class="page">
  <div class="container">
    <div class="left">
      <div class="logo">
        <div class="logo-icon"><img src="{{ asset('image/bstc-logo.png') }}" alt="" width="179" height="172" decoding="async" aria-hidden="true"></div>
        <span class="logo-wordmark">BIOMETRIX</span><span class="logo-tag">TICKETING</span>
      </div>
      <h1 class="headline">Smart ticketing<br><span class="accent">by Biometrix Systems &amp; Trading Corp.</span></h1>
      <p class="sub">AI-driven ticket management with biometric authentication, real-time triage, and predictive resolution - purpose-built for modern support teams.</p>
      <div class="status-row">
        <div class="chip green"><div class="chip-dot pulse"></div>System Active - v3.8.1</div>
        <div class="chip blue"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>Bio-verified</div>
        <div class="chip amber"><div class="chip-dot"></div>SLA on track</div>
      </div>
    </div>
    <div class="card">
      <div class="card-scan"></div>
      <div class="card-header"><div class="card-title">Sign in</div><div class="card-sub">Access your secure workspace</div></div>
      <div class="divider">continue with credentials</div>

      @if (session('success'))
        <div class="error-box" style="background:rgba(16,185,129,0.12);border-color:rgba(16,185,129,0.35);color:#86efac;">{{ session('success') }}</div>
      @endif
      @if ($errors->any())
        <div class="error-box">{{ $errors->first() }}</div>
      @endif
      @if (request('error'))
        <div class="error-box">{{ request('error') }}</div>
      @endif

      <form action="{{ route('login', [], false) }}" method="post">
        @csrf
        <div class="field">
          <div class="field-label">Email address</div>
          <div class="input-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="you@company.com" autocomplete="email" required autofocus>
          </div>
        </div>
        <div class="field">
          <div class="field-label">Password</div>
          <div class="input-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input type="password" name="password" placeholder="••••••••" autocomplete="current-password">
          </div>
        </div>
        <button class="btn-primary" type="submit" onclick="handleLogin(this)">Sign in to Biometrix</button>
      </form>
      <div class="signup-row">New to Biometrix? <a href="#">Request access</a></div>
    </div>
  </div>
</div>
<script>
const canvas=document.getElementById('bg-canvas');const ctx=canvas.getContext('2d');let W,H,particles=[];
function resize(){W=canvas.width=window.innerWidth;H=canvas.height=window.innerHeight;}resize();window.addEventListener('resize',resize);
function mkParticle(){return{x:Math.random()*W,y:Math.random()*H,vx:(Math.random()-0.5)*0.3,vy:(Math.random()-0.5)*0.3,r:Math.random()*1.5+0.5,a:Math.random()*0.5+0.1,hue:Math.random()>0.7?160:217};}
for(let i=0;i<90;i++)particles.push(mkParticle());
function drawParticles(){ctx.clearRect(0,0,W,H);particles.forEach(p=>{p.x+=p.vx;p.y+=p.vy;if(p.x<0)p.x=W;if(p.x>W)p.x=0;if(p.y<0)p.y=H;if(p.y>H)p.y=0;ctx.beginPath();ctx.arc(p.x,p.y,p.r,0,Math.PI*2);ctx.fillStyle=`hsla(${p.hue},80%,65%,${p.a})`;ctx.fill();});for(let i=0;i<particles.length;i++){for(let j=i+1;j<particles.length;j++){const dx=particles[i].x-particles[j].x;const dy=particles[i].y-particles[j].y;const d=Math.sqrt(dx*dx+dy*dy);if(d<90){ctx.beginPath();ctx.moveTo(particles[i].x,particles[i].y);ctx.lineTo(particles[j].x,particles[j].y);ctx.strokeStyle=`rgba(96,165,250,${0.06*(1-d/90)})`;ctx.lineWidth=0.5;ctx.stroke();}}}requestAnimationFrame(drawParticles);}drawParticles();
const iconSVGs=[`<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgba(96,165,250,0.5)" stroke-width="1.5" stroke-linecap="round"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2z"/><path d="M8 12a4 4 0 0 0 8 0"/><circle cx="12" cy="12" r="1" fill="rgba(96,165,250,0.5)"/></svg>`,`<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgba(16,185,129,0.45)" stroke-width="1.5" stroke-linecap="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>`,`<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(96,165,250,0.4)" stroke-width="1.5" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>`];
const container=document.getElementById('float-icons');function spawnIcon(){const el=document.createElement('div');el.className='fi';el.innerHTML=iconSVGs[Math.floor(Math.random()*iconSVGs.length)];const dur=10+Math.random()*16;const delay=Math.random()*-20;el.style.cssText=`left:${Math.random()*100}%;bottom:-40px;animation:float-up ${dur}s ${delay}s linear infinite;`;container.appendChild(el);}for(let i=0;i<22;i++)spawnIcon();
function handleLogin(btn){btn.textContent='Authenticating...';btn.style.background='#1D4ED8';setTimeout(()=>{btn.textContent='✓ Verified';btn.style.background='#059669';setTimeout(()=>{btn.textContent='Sign in to Biometrix';btn.style.background='';},2000);},1400);}
</script>
</body>
</html>


