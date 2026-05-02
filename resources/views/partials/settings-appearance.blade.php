@php
    $closeBtnClass = $closeButtonClass ?? 'js-close-profile-modal';
@endphp
<div class="space-y-5">
    <div>
        <div class="text-sm font-semibold text-slate-900 mb-1">Theme</div>
        <p class="text-xs text-slate-500 mb-4">Choose how IT Helpdesk looks on this device.</p>

        <div class="grid grid-cols-3 gap-3">
            {{-- Light --}}
            <label class="theme-option-card cursor-pointer group" data-value="light">
                <input type="radio" name="theme" value="light" class="sr-only">
                <div class="rounded-xl overflow-hidden border-2 border-transparent transition-all mb-2 shadow-sm" style="border-color:transparent">
                    <div style="background:#f1f5f9;padding:6px 6px 4px;border-radius:10px">
                        <div style="display:flex;gap:4px;height:56px">
                            <div style="width:14px;background:#1e293b;border-radius:4px;flex-shrink:0;display:flex;flex-direction:column;align-items:center;padding:4px 0;gap:3px">
                                <div style="width:6px;height:6px;background:rgba(255,255,255,.3);border-radius:50%"></div>
                                <div class="theme-anim-sidebar-item" style="width:8px;height:3px;background:#3b82f6;border-radius:2px"></div>
                                <div style="width:8px;height:3px;background:rgba(255,255,255,.2);border-radius:2px"></div>
                                <div style="width:8px;height:3px;background:rgba(255,255,255,.2);border-radius:2px"></div>
                            </div>
                            <div style="flex:1;background:#fff;border-radius:4px;padding:4px 3px;display:flex;flex-direction:column;gap:2px;overflow:hidden">
                                <div style="height:4px;background:#e2e8f0;border-radius:2px;width:70%"></div>
                                <div style="height:3px;background:#e2e8f0;border-radius:2px;width:50%"></div>
                                <div style="display:flex;gap:2px;margin-top:2px">
                                    <div class="theme-anim-card" style="flex:1;height:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:3px"></div>
                                    <div class="theme-anim-card" style="flex:1;height:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:3px;animation-delay:.15s"></div>
                                </div>
                                <div style="display:flex;align-items:center;gap:2px;margin-top:1px">
                                    <div class="theme-anim-pulse" style="width:5px;height:5px;background:#22c55e;border-radius:50%"></div>
                                    <div style="height:2px;background:#e2e8f0;border-radius:1px;flex:1"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-700">Light</span>
                    <span class="theme-check hidden h-4 w-4 rounded-full bg-blue-500 flex items-center justify-center">
                        <svg class="h-2.5 w-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </span>
                </div>
            </label>

            {{-- Dark --}}
            <label class="theme-option-card cursor-pointer group" data-value="dark">
                <input type="radio" name="theme" value="dark" class="sr-only">
                <div class="rounded-xl overflow-hidden border-2 border-transparent transition-all mb-2 shadow-sm" style="border-color:transparent">
                    <div style="background:#0f172a;padding:6px 6px 4px;border-radius:10px">
                        <div style="display:flex;gap:4px;height:56px">
                            <div style="width:14px;background:#1e293b;border-radius:4px;flex-shrink:0;border:1px solid rgba(255,255,255,.08);display:flex;flex-direction:column;align-items:center;padding:4px 0;gap:3px">
                                <div style="width:6px;height:6px;background:rgba(255,255,255,.25);border-radius:50%"></div>
                                <div class="theme-anim-sidebar-item" style="width:8px;height:3px;background:#3b82f6;border-radius:2px;animation-delay:.1s"></div>
                                <div style="width:8px;height:3px;background:rgba(255,255,255,.15);border-radius:2px"></div>
                                <div style="width:8px;height:3px;background:rgba(255,255,255,.15);border-radius:2px"></div>
                            </div>
                            <div style="flex:1;background:#1e293b;border-radius:4px;padding:4px 3px;display:flex;flex-direction:column;gap:2px;border:1px solid rgba(255,255,255,.06);overflow:hidden">
                                <div style="height:4px;background:#334155;border-radius:2px;width:70%"></div>
                                <div style="height:3px;background:#334155;border-radius:2px;width:50%"></div>
                                <div style="display:flex;gap:2px;margin-top:2px">
                                    <div class="theme-anim-card" style="flex:1;height:12px;background:#0f172a;border:1px solid rgba(255,255,255,.08);border-radius:3px;animation-delay:.05s"></div>
                                    <div class="theme-anim-card" style="flex:1;height:12px;background:#0f172a;border:1px solid rgba(255,255,255,.08);border-radius:3px;animation-delay:.2s"></div>
                                </div>
                                <div style="display:flex;align-items:center;gap:2px;margin-top:1px">
                                    <div class="theme-anim-pulse" style="width:5px;height:5px;background:#22c55e;border-radius:50%;animation-delay:.3s"></div>
                                    <div style="height:2px;background:#334155;border-radius:1px;flex:1"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-700">Dark</span>
                    <span class="theme-check hidden h-4 w-4 rounded-full bg-blue-500 flex items-center justify-center">
                        <svg class="h-2.5 w-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </span>
                </div>
            </label>

            {{-- System --}}
            <label class="theme-option-card cursor-pointer group" data-value="system">
                <input type="radio" name="theme" value="system" class="sr-only">
                <div class="rounded-xl overflow-hidden border-2 border-transparent transition-all mb-2 shadow-sm" style="border-color:transparent">
                    <div style="display:flex;height:64px;border-radius:10px;overflow:hidden">
                        <div style="flex:1;background:#f1f5f9;padding:5px 3px 5px 5px;display:flex;flex-direction:column;gap:2px">
                            <div style="height:4px;background:#e2e8f0;border-radius:2px;width:80%"></div>
                            <div class="theme-anim-sidebar-item" style="height:3px;background:#3b82f6;border-radius:2px;width:60%"></div>
                            <div style="height:3px;background:#e2e8f0;border-radius:2px;width:50%"></div>
                            <div style="height:10px;background:#fff;border-radius:3px;margin-top:2px;display:flex;align-items:center;padding:0 3px;gap:2px">
                                <div class="theme-anim-pulse" style="width:4px;height:4px;background:#22c55e;border-radius:50%"></div>
                                <div style="height:2px;background:#e2e8f0;border-radius:1px;flex:1"></div>
                            </div>
                        </div>
                        <div style="width:1px;background:linear-gradient(to bottom,transparent,#94a3b8,transparent)"></div>
                        <div style="flex:1;background:#0f172a;padding:5px 5px 5px 3px;display:flex;flex-direction:column;gap:2px">
                            <div style="height:4px;background:#334155;border-radius:2px;width:80%"></div>
                            <div class="theme-anim-sidebar-item" style="height:3px;background:#3b82f6;border-radius:2px;width:60%;animation-delay:.2s"></div>
                            <div style="height:3px;background:#334155;border-radius:2px;width:50%"></div>
                            <div style="height:10px;background:#1e293b;border-radius:3px;margin-top:2px;display:flex;align-items:center;padding:0 3px;gap:2px;border:1px solid rgba(255,255,255,.06)">
                                <div class="theme-anim-pulse" style="width:4px;height:4px;background:#22c55e;border-radius:50%;animation-delay:.4s"></div>
                                <div style="height:2px;background:#334155;border-radius:1px;flex:1"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-700">System</span>
                    <span class="theme-check hidden h-4 w-4 rounded-full bg-blue-500 flex items-center justify-center">
                        <svg class="h-2.5 w-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </span>
                </div>
            </label>
        </div>

        <style>
        @keyframes theme-pulse {
            0%,100%{opacity:1;transform:scale(1)}
            50%{opacity:.4;transform:scale(1.4)}
        }
        @keyframes theme-slide {
            0%,100%{transform:translateX(0);opacity:1}
            50%{transform:translateX(2px);opacity:.7}
        }
        @keyframes theme-card-pop {
            0%,100%{transform:scaleY(1)}
            50%{transform:scaleY(1.08)}
        }
        .theme-anim-pulse{animation:theme-pulse 2s ease-in-out infinite}
        .theme-anim-sidebar-item{animation:theme-slide 2.5s ease-in-out infinite}
        .theme-anim-card{animation:theme-card-pop 3s ease-in-out infinite}
        .theme-option-card:hover .theme-anim-pulse{animation-duration:1s}
        .theme-option-card:hover .theme-anim-sidebar-item{animation-duration:1.2s}
        </style>
    </div>

    <div class="flex justify-end">
        <button type="button" class="{{ $closeBtnClass }} cursor-pointer transition-all bg-white text-slate-700 px-5 py-2 rounded-lg border-slate-300 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] text-sm font-semibold inline-flex items-center justify-center">Close</button>
    </div>
</div>

<script>
(function() {
    function getTheme(){ try{ return localStorage.getItem('theme'); }catch(e){ return null; } }
    function closeThemeModal(fromEl) {
        var modal = fromEl && fromEl.closest ? fromEl.closest('[aria-modal="true"]') : null;
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    function markSelected(val) {
        document.querySelectorAll('.theme-option-card').forEach(function(card) {
            var isSelected = card.getAttribute('data-value') === val;
            card.classList.toggle('selected', isSelected);
            var check = card.querySelector('.theme-check');
            var border = card.querySelector('.rounded-xl.overflow-hidden');
            if (check) check.classList.toggle('hidden', !isSelected);
            if (border) {
                border.style.borderColor = isSelected ? '#3b82f6' : 'transparent';
                border.style.boxShadow = isSelected ? '0 0 0 1px #3b82f6' : '';
            }
        });
    }
    var current = getTheme() || 'system';
    markSelected(current);
    document.querySelectorAll('.theme-option-card').forEach(function(card) {
        card.addEventListener('click', function() {
            var val = card.getAttribute('data-value');
            var radio = card.querySelector('input[type=radio]');
            if (radio) radio.checked = true;
            markSelected(val);
            closeThemeModal(card);
            if (typeof window.applyTheme === 'function') {
                try { localStorage.setItem('theme', val); } catch(e) {}
                window.requestAnimationFrame(function() {
                    window.applyTheme();
                });
            }
        });
    });
    if (typeof window.syncThemeRadios === 'function') {
        var orig = window.syncThemeRadios;
        window.syncThemeRadios = function() { orig(); markSelected(getTheme()||'system'); };
    }
})();
</script>
