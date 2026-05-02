<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User activity — {{ $user->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px; color: #333; background: #f1f5f9; margin: 0; padding: 24px; }
        .card { background: #fff; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.08); max-width: 980px; margin: 0 auto; overflow: hidden; }
        .card-top { padding: 20px 24px; border-bottom: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; }
        .entries-wrap { display: flex; align-items: center; gap: 8px; }
        .entries-wrap label { color: #475569; font-size: 14px; }
        .entries-wrap select { padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 14px; background: #fff; }
        .search-wrap { display: flex; align-items: center; gap: 8px; }
        .search-wrap label { color: #475569; font-size: 14px; font-weight: 500; }
        .search-wrap input { padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 14px; min-width: 200px; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th { text-align: left; padding: 12px 16px; background: #f1f5f9; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.03em; color: #475569; border-bottom: 2px solid #e2e8f0; }
        thead th:not(:last-child) { border-right: 1px solid #e2e8f0; }
        tbody td { padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr:hover { background: #f1f5f9; }
        .card-bottom { padding: 12px 24px; border-top: 1px solid #e2e8f0; background: #fafafa; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; font-size: 14px; color: #64748b; }
        .pagination { display: flex; align-items: center; gap: 4px; }
        .pagination button, .pagination span { padding: 6px 12px; border: 1px solid #cbd5e1; background: #fff; border-radius: 4px; font-size: 14px; cursor: pointer; color: #475569; }
        .pagination span.current { background: #3b82f6; color: #fff; border-color: #3b82f6; cursor: default; }
        .pagination button:hover:not(:disabled) { background: #f1f5f9; }
        .pagination button:disabled { opacity: 0.6; cursor: not-allowed; }
        .print-bar { text-align: center; padding: 16px; background: #f1f5f9; border-top: 1px solid #e2e8f0; }
        .print-bar button { padding: 10px 24px; background: #3b82f6; color: #fff; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; }
        .print-bar button:hover { background: #2563eb; }
        .title-block { margin-bottom: 24px; }
        .title-block h1 { font-size: 20px; margin: 0 0 4px 0; color: #0f172a; }
        .title-block p { margin: 0; font-size: 13px; color: #64748b; }
        @media print {
            body { background: #fff; padding: 0; }
            .card { box-shadow: none; border: 1px solid #ddd; }
            .print-bar { display: none !important; }
            .card-top .search-wrap input { border: 1px solid #ccc; }
        }
    </style>
</head>
<body>
    <div class="title-block">
        <h1>User activity — {{ $user->name }}</h1>
        <p>{{ $user->email }} · {{ $user->getRoleLabel() }} · Generated {{ now()->format('M j, Y g:i A') }}</p>
    </div>

    <div class="card">
        <div class="card-top">
            <div class="entries-wrap">
                <label for="entries">Show</label>
                <select id="entries">
                    <option value="10" {{ $entries->count() <= 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ $entries->count() > 10 && $entries->count() <= 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $entries->count() > 25 && $entries->count() <= 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $entries->count() > 50 && $entries->count() <= 100 ? 'selected' : '' }}>100</option>
                    <option value="-1" {{ $entries->count() > 100 ? 'selected' : '' }}>All</option>
                </select>
                <label>entries per page</label>
            </div>
            <div class="search-wrap">
                <label for="search">Search:</label>
                <input type="text" id="search" placeholder="Search table…">
            </div>
        </div>

        <div class="table-wrap">
            <table id="audit-table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>Date & time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td>
                                @if (!empty($entry['ticket_number']))
                                    {{ $entry['ticket_number'] }} — {{ Str::limit($entry['ticket_title'] ?? '', 40) }}
                                @else
                                    Auth event
                                @endif
                            </td>
                            <td>{{ $entry['type'] ?? 'System' }}</td>
                            <td>{{ Str::limit($entry['details'] ?? '', 80) }}</td>
                            <td>{{ $entry['date_formatted'] ?? '' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center;padding:24px;color:#64748b;">No user actions recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-bottom">
            <div class="info-text" id="info-text">Showing 1 to {{ $entries->count() ?: 0 }} of {{ $entries->count() }} entries</div>
            <div class="pagination">
                <span class="current">1</span>
            </div>
        </div>

        <div class="print-bar">
            <button type="button" onclick="window.print();">Print</button>
        </div>
    </div>

    <script>
    (function() {
        var table = document.getElementById('audit-table');
        var search = document.getElementById('search');
        var infoEl = document.getElementById('info-text');
        var total = {{ $entries->count() }};
        var rows = table && table.tBodies[0] ? table.tBodies[0].rows : [];
        var allRows = [];
        for (var i = 0; i < rows.length; i++) {
            if (rows[i].cells.length < 4) continue;
            allRows.push(rows[i]);
        }
        function filter() {
            var q = (search.value || '').toLowerCase();
            var visible = 0;
            allRows.forEach(function(row) {
                var text = row.textContent.toLowerCase();
                var show = q === '' || text.indexOf(q) !== -1;
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            if (infoEl) infoEl.textContent = 'Showing 1 to ' + visible + ' of ' + total + (q ? ' entries (filtered)' : ' entries');
        }
        if (search) search.addEventListener('input', filter);
    })();
    </script>
</body>
</html>
