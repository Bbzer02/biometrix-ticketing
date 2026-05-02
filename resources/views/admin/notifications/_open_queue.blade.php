@if(isset($openQueue) && $openQueue->isNotEmpty())
    <table id="employee-open-queue-table" class="display" style="width:100%">
        <thead>
        <tr>
            <th>Ticket #</th>
            <th>Title</th>
            <th>Category</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($openQueue as $ticket)
            <tr>
                <td>{{ $ticket->ticket_number }}</td>
                <td>{{ \Illuminate\Support\Str::limit($ticket->title, 60) }}</td>
                <td>{{ $ticket->category->name ?? 'Uncategorized' }}</td>
                <td>{{ $ticket->created_at->format('M j, Y g:i A') }}</td>
                <td>
                    <a href="{{ route('tickets.show', $ticket) }}"
                       class="text-sm font-medium text-blue-600 hover:text-blue-700">
                        View
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <div class="px-4 py-8 text-center text-slate-500 sm:px-6">
        No open tickets waiting in the queue right now.
    </div>
@endif

