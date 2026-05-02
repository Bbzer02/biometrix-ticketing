@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    @php
        $user = auth()->user();
        $isEmployee = $user && $user->role === \App\Models\User::ROLE_EMPLOYEE && ! $user->isItStaff() && ! $user->isAdmin();
    @endphp

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm sm:overflow-hidden">
        <div class="border-b border-slate-200 bg-white px-4 py-4 sm:px-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 sm:text-2xl">Notifications</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Recent activity. Use filters to switch between ticket events and login/logout events.
                    </p>
                </div>
            </div>
            <p class="mt-1 text-sm text-slate-500">
                Recent ticket activity from the audit trail. Each row represents a system event (create, accept, status change, cancel, close).
            </p>
        </div>
        <div class="border-b border-slate-100 bg-slate-50/50 px-4 py-2 sm:px-6">
            @php
                $activeCategory = $category ?? request('category');
                $activeCategory = $activeCategory ? strtolower($activeCategory) : '';
                $btnBase = 'inline-flex items-center gap-1.5 rounded-xl border px-3 py-1.5 text-xs font-semibold transition-colors';
                $btnOn = 'border-blue-200 bg-blue-50 text-blue-700';
                $btnOff = 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50';
            @endphp
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route(request()->routeIs('admin.notifications.index') ? 'admin.notifications.index' : 'notifications.index') }}"
                   class="{{ $btnBase }} {{ $activeCategory === '' ? $btnOn : $btnOff }}"
                   data-no-loading>
                    All
                </a>
                <a href="{{ route(request()->routeIs('admin.notifications.index') ? 'admin.notifications.index' : 'notifications.index', ['category' => 'tickets']) }}"
                   class="{{ $btnBase }} {{ $activeCategory === 'tickets' ? $btnOn : $btnOff }}"
                   data-no-loading>
                    Ticket notifications
                </a>
                <a href="{{ route(request()->routeIs('admin.notifications.index') ? 'admin.notifications.index' : 'notifications.index', ['category' => 'auth']) }}"
                   class="{{ $btnBase }} {{ $activeCategory === 'auth' ? $btnOn : $btnOff }}"
                   data-no-loading>
                    Login / Logout
                </a>
                <a href="{{ route(request()->routeIs('admin.notifications.index') ? 'admin.notifications.index' : 'notifications.index', ['category' => 'system']) }}"
                   class="{{ $btnBase }} {{ $activeCategory === 'system' ? $btnOn : $btnOff }}"
                   data-no-loading>
                    System alerts
                </a>
            </div>
        </div>
        <div class="p-4 sm:px-6 overflow-x-auto audit-trail-table-wrap">
            <table id="admin-notifications-table" class="display" style="width:100%">
                <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Action</th>
                    <th>By</th>
                    <th>Date &amp; time</th>
                    <th>Details</th>
                    <th>View</th>
                </tr>
                </thead>
                <tbody id="notifications-table-body">
                @include('admin.notifications._rows', ['notifications' => $notifications])
                </tbody>
            </table>
        </div>
    </div>

    @if(auth()->check() && auth()->user()->isAdmin())
        {{-- View notification modal --}}
        <div id="view-notification-modal" class="fixed inset-0 z-[9999] hidden">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="w-full max-w-xl rounded-2xl bg-white shadow-xl ring-1 ring-slate-200">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                        <h2 class="text-base font-semibold text-slate-900">Notification details</h2>
                        <button type="button" id="view-notification-close"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100"
                                aria-label="Close">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="px-5 py-4 space-y-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Type</p>
                            <p id="view-notification-kind" class="mt-1 text-sm text-slate-800"></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">By</p>
                            <p id="view-notification-user" class="mt-1 text-sm text-slate-800"></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Date &amp; time</p>
                            <p id="view-notification-date" class="mt-1 text-sm text-slate-800"></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Details</p>
                            <p id="view-notification-body" class="mt-1 text-sm text-slate-800 whitespace-pre-wrap"></p>
                        </div>
                    </div>
                    <div class="flex justify-end border-t border-slate-200 px-5 py-3">
                        <button type="button" id="view-notification-cancel"
                                class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Edit notification modal (admin only) --}}
        <div id="edit-notification-modal" class="fixed inset-0 z-[9999] hidden">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="w-full max-w-xl rounded-2xl bg-white shadow-xl ring-1 ring-slate-200">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                        <h2 class="text-base font-semibold text-slate-900">Edit notification</h2>
                        <button type="button" id="edit-notification-close"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100"
                                aria-label="Close">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form id="edit-notification-form" class="px-5 py-4">
                        <input type="hidden" name="id" id="edit-notification-id" value="">
                        <label for="edit-notification-body" class="label-ticket">Details</label>
                        <textarea id="edit-notification-body" name="body" rows="6"
                                  class="input-ticket resize-y"
                                  placeholder="Notification text..."></textarea>
                        <div class="mt-4 flex items-center justify-end gap-2">
                            <button type="button" id="edit-notification-cancel"
                                    class="cursor-pointer transition-all bg-white text-slate-700 px-5 py-2 rounded-lg border-slate-300 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-slate-200 shadow-slate-200 active:shadow-none text-sm font-semibold inline-flex items-center justify-center">
                                Cancel
                            </button>
                            <button type="submit" id="edit-notification-save"
                                    class="cursor-pointer transition-all bg-gray-700 text-white px-5 py-2 rounded-lg border-green-400 border-b-[4px] hover:brightness-110 hover:-translate-y-[1px] hover:border-b-[6px] active:border-b-[2px] active:brightness-90 active:translate-y-[2px] hover:shadow-xl hover:shadow-green-300 shadow-green-300 active:shadow-none text-sm font-semibold inline-flex items-center justify-center">
                                Save changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
    <script>
        (function() {
            var tbody = document.getElementById('notifications-table-body');
            if (!tbody) return;

            var tableUrl = @json(route('notifications.table'));
            var updateUrlTemplate = @json(route('admin.notifications.update', ['notification' => '__ID__']));

            function markRowRead(row) {
                if (!row || row.dataset.read === '1') return;
                row.dataset.read = '1';
                row.classList.remove('notification-row-unread');
                row.classList.add('notification-row-read');
            }

            // No JS-only click handling here; handled globally in layout to work with DataTables/navigation.

            function applyTableHtml(html) {
                if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.dataTable && jQuery.fn.dataTable.isDataTable) {
                    var table = document.getElementById('admin-notifications-table');
                    if (table && jQuery.fn.dataTable.isDataTable(table)) {
                        var dt = jQuery(table).DataTable();
                        dt.clear();
                        var rows = jQuery(html);
                        dt.rows.add(rows);
                        dt.draw(false);
                        return;
                    }
                }
                tbody.innerHTML = html;
            }

            window.refreshNotificationsPage = function() {
                fetch(tableUrl, {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(function (r) { return r.ok ? r.json() : Promise.reject(); })
                    .then(function (data) {
                        if (data && typeof data.notificationsHtml === 'string') {
                            applyTableHtml(data.notificationsHtml);
                        }
                    })
                    .catch(function () {});
            };

            // View notification modal wiring
            var viewModal = document.getElementById('view-notification-modal');
            var viewCloseBtn = document.getElementById('view-notification-close');
            var viewCancelBtn = document.getElementById('view-notification-cancel');

            function openViewModal(payload) {
                if (!viewModal || !payload) return;
                document.getElementById('view-notification-kind').textContent = payload.kind || '—';
                document.getElementById('view-notification-user').textContent = payload.user || 'System';
                document.getElementById('view-notification-date').textContent = payload.created_at || '—';
                document.getElementById('view-notification-body').textContent = payload.body || '—';
                viewModal.classList.remove('hidden');
            }
            function closeViewModal() {
                if (!viewModal) return;
                viewModal.classList.add('hidden');
            }

            if (viewModal) {
                viewModal.addEventListener('click', function(e) {
                    if (e.target === viewModal || e.target === viewModal.firstElementChild) closeViewModal();
                });
            }
            if (viewCloseBtn) viewCloseBtn.addEventListener('click', closeViewModal);
            if (viewCancelBtn) viewCancelBtn.addEventListener('click', closeViewModal);

            document.addEventListener('click', function(e) {
                var viewBtn = e.target.closest && e.target.closest('.notification-view-detail-btn');
                if (!viewBtn) return;
                try {
                    var payload = JSON.parse(viewBtn.getAttribute('data-notification-view') || '{}');
                    openViewModal(payload);
                } catch (err) {}
            });

            // Admin-only edit modal wiring
            var modal = document.getElementById('edit-notification-modal');            var closeBtn = document.getElementById('edit-notification-close');
            var cancelBtn = document.getElementById('edit-notification-cancel');
            var form = document.getElementById('edit-notification-form');
            var idInput = document.getElementById('edit-notification-id');
            var bodyInput = document.getElementById('edit-notification-body');
            var saveBtn = document.getElementById('edit-notification-save');

            function openModal(payload) {
                if (!modal || !payload) return;
                idInput.value = String(payload.id || '');
                bodyInput.value = String(payload.body || '');
                modal.classList.remove('hidden');
                setTimeout(function() { bodyInput && bodyInput.focus && bodyInput.focus(); }, 0);
            }
            function closeModal() {
                if (!modal) return;
                modal.classList.add('hidden');
            }

            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal || e.target === modal.firstElementChild) closeModal();
                });
            }
            if (closeBtn) closeBtn.addEventListener('click', closeModal);
            if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') { closeModal(); closeViewModal(); }
            });

            document.addEventListener('click', function(e) {
                var editBtn = e.target.closest && e.target.closest('.notification-edit-btn');
                if (!editBtn) return;
                try {
                    var payload = JSON.parse(editBtn.getAttribute('data-notification') || '{}');
                    openModal(payload);
                } catch (err) {}
            });

            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (typeof window.csrfToken !== 'string' || !window.csrfToken) return;
                    var id = idInput ? idInput.value : '';
                    if (!id) return;

                    var url = (updateUrlTemplate || '').replace('__ID__', encodeURIComponent(id));
                    if (!url) return;

                    if (saveBtn) {
                        saveBtn.disabled = true;
                        saveBtn.dataset.prevText = saveBtn.textContent || '';
                        saveBtn.textContent = 'Saving...';
                    }

                    fetch(url, {
                        method: 'PUT',
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
                                window.showAppToast((data && data.message) ? data.message : 'Saved');
                            }
                            closeModal();
                            if (typeof window.refreshNotificationsPage === 'function') window.refreshNotificationsPage();
                        })
                        .catch(function(err) {
                            if (typeof window.showAppToast === 'function') {
                                window.showAppToast((err && err.message) ? err.message : 'Could not save');
                            }
                        })
                        .finally(function() {
                            if (saveBtn) {
                                saveBtn.disabled = false;
                                saveBtn.textContent = saveBtn.dataset.prevText || 'Save changes';
                            }
                        });
                });
            }
        })();
    </script>
@endsection

