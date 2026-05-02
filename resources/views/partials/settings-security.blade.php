@php
    $closeBtnClass = $closeButtonClass ?? 'js-close-profile-modal';
@endphp
<div class="space-y-4">
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <div class="text-sm font-semibold text-slate-900">Password</div>
        @if(!auth()->user()->isAdmin())
            <p class="mt-2 text-sm text-slate-500">Only administrators can change passwords. Contact your administrator.</p>
            <div class="flex justify-end mt-4">
                <button type="button" class="{{ $closeBtnClass }} cursor-pointer transition-all bg-white text-slate-700 px-5 py-2 rounded-lg border-slate-300 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] text-sm font-semibold inline-flex items-center justify-center">Close</button>
            </div>
        @else
            @if(empty(auth()->user()->password))
                <p class="mt-1 text-sm text-slate-500">Set your password.</p>
            @endif
            <form action="{{ route('profile.password') }}" method="post" class="mt-4 space-y-3">
                @csrf
                @method('PUT')
                @if(!empty(auth()->user()->password))
                    <div>
                        <label for="modal_current_password" class="block text-sm font-medium text-slate-700">Current password</label>
                        <input type="password" name="current_password" id="modal_current_password" autocomplete="current-password"
                               style="margin-top:0.25rem;display:block;width:100%;border-radius:0.75rem;border:1px solid #cbd5e1;background:#ffffff !important;color:#0f172a !important;padding:0.625rem 1rem;box-shadow:0 1px 2px rgba(0,0,0,0.05);font-size:0.875rem;">
                    </div>
                @endif
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="modal_new_password" class="block text-sm font-medium text-slate-700">New password</label>
                        <input type="password" name="password" id="modal_new_password" autocomplete="new-password"
                               style="margin-top:0.25rem;display:block;width:100%;border-radius:0.75rem;border:1px solid #cbd5e1;background:#ffffff !important;color:#0f172a !important;padding:0.625rem 1rem;box-shadow:0 1px 2px rgba(0,0,0,0.05);font-size:0.875rem;">
                    </div>
                    <div>
                        <label for="modal_new_password_confirmation" style="display:block;font-size:0.875rem;font-weight:500;color:#1e293b;">Confirm password</label>
                        <input type="password" name="password_confirmation" id="modal_new_password_confirmation" autocomplete="new-password"
                               style="margin-top:0.25rem;display:block;width:100%;border-radius:0.75rem;border:1px solid #cbd5e1;background:#ffffff !important;color:#0f172a !important;padding:0.625rem 1rem;box-shadow:0 1px 2px rgba(0,0,0,0.05);font-size:0.875rem;">
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="{{ $closeBtnClass }} cursor-pointer transition-all bg-white text-slate-700 px-5 py-2 rounded-lg border-slate-300 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-slate-200 shadow-slate-200 active:shadow-none text-sm font-semibold inline-flex items-center justify-center">Close</button>
                    <button type="submit" class="cursor-pointer transition-all bg-gray-700 text-white px-5 py-2 rounded-lg border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold inline-flex items-center justify-center">Save</button>
                </div>
            </form>
        @endif
    </div>
    @if(!auth()->user()->isAdmin())
    <div class="rounded-xl border border-slate-200 bg-white p-4 mt-4">
        <div class="text-sm font-semibold text-slate-900 mb-1">Recovery email</div>
        <p class="text-xs text-slate-500 mb-3">Your personal Gmail or any email you own. If you forget your password, the admin will send a reset link here.</p>
        <div id="modal-emergency-msg" style="display:none;border-radius:0.75rem;padding:0.5rem 0.875rem;font-size:0.8125rem;margin-bottom:0.75rem;"></div>
        <form id="modal-emergency-form" action="{{ route('profile.update') }}" method="post" class="space-y-3">
            @csrf
            @method('PUT')
            <input type="hidden" name="name" value="{{ auth()->user()->name }}">
            <div>
                <label for="modal_emergency_email" class="mb-1 block text-sm font-medium text-slate-700">Recovery email (e.g. Gmail)</label>
                <input type="email" name="emergency_email" id="modal_emergency_email"
                       value="{{ auth()->user()->emergency_email }}"
                       placeholder="yourname@gmail.com"
                       style="display:block;width:100%;border-radius:0.75rem;border:1px solid #cbd5e1;background:#ffffff;color:#0f172a;padding:0.625rem 1rem;font-size:0.875rem;">
            </div>
            <div class="flex justify-end gap-2">
                <button type="submit" id="modal-emergency-save" class="cursor-pointer transition-all bg-gray-700 text-white px-5 py-2 rounded-lg border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold inline-flex items-center justify-center">Save</button>
            </div>
        </form>
    </div>
    <script>
    (function() {
        var form = document.getElementById('modal-emergency-form');
        var msg  = document.getElementById('modal-emergency-msg');
        var btn  = document.getElementById('modal-emergency-save');
        var modal = document.getElementById('profile-settings-modal');
        if (!form) return;
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            btn.disabled = true; btn.textContent = 'Saving…';
            fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': window.csrfToken || '' },
                body: new FormData(form)
            })
            .then(function(r) { return r.ok ? r.json() : r.json().then(function(d){ throw d; }); })
            .then(function() {
                if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
                if (window.showAppToast) window.showAppToast('Recovery email saved.');
            })
            .catch(function(err) {
                msg.style.display = 'block';
                msg.style.background = 'rgba(239,68,68,0.1)';
                msg.style.border = '1px solid rgba(239,68,68,0.3)';
                msg.style.color = '#b91c1c';
                var m = (err && err.errors) ? Object.values(err.errors).flat().join(' ') : (err && err.message) || 'Could not save.';
                msg.textContent = m;
            })
            .finally(function() { btn.disabled = false; btn.textContent = 'Save'; });
        });
    })();
    </script>
    @endif
</div>
