<?php

namespace App\Http\Controllers;

use App\Events\TicketsUpdated;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketComment;
use App\Models\TicketPriority;
use App\Models\User;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $query = Ticket::query()
            ->with([
                'category:id,name,slug',
                'submitter:id,name,role,profile_picture,updated_at',
                'assignee:id,name,role,profile_picture,updated_at',
                'acceptedBy:id,name,role,profile_picture,updated_at',
            ])
            ->select(['id','ticket_number','title','status','priority','category_id','submitter_id','assignee_id','accepted_by_id','created_at','updated_at','scheduled_for','assigned_at'])
            ->latest();

        // Front Desk (non-Admin): by default see all tickets; with ?mine=1 see only tickets they logged
        if (auth()->user()?->isFrontDesk() && ! auth()->user()?->isAdmin()) {
            if ($request->boolean('mine')) {
                $query->where('submitter_id', auth()->id());
            }
        }

        $requestedStatus = is_string($request->status) ? strtolower(trim($request->status)) : null;

        // Employee (non-IT, non-Admin): "Tickets" = combined (open queue + my assigned); with status filter = that status for (my assigned + open unassigned)
        $isEmployee = auth()->user()?->role === \App\Models\User::ROLE_EMPLOYEE && ! auth()->user()?->isItStaff() && ! auth()->user()?->isAdmin();
        if ($isEmployee) {
            if ($request->filled('status') && in_array($requestedStatus, Ticket::allowedStatuses(), true)) {
                $query->where('status', $requestedStatus)
                    ->where(function ($q) {
                        $q->where('assignee_id', auth()->id())
                            ->orWhereNull('assignee_id');
                    });
            } else {
                $query->where(function ($q) {
                    $q->where('assignee_id', auth()->id())
                        ->orWhere(function ($q2) {
                            $q2->where('status', Ticket::STATUS_OPEN)->whereNull('assignee_id');
                        });
                });
            }
        }

        if (! $isEmployee && $request->filled('status') && in_array($requestedStatus, Ticket::allowedStatuses(), true)) {
            $query->where('status', $requestedStatus);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('ticket_number', 'like', $term);
            });
        }

        $tickets = $query->paginate(15)->withQueryString();

        $showMineOnly = auth()->user()?->isFrontDesk() && ! auth()->user()?->isAdmin() && $request->boolean('mine');

        return view('tickets.index', compact('tickets', 'showMineOnly'));
    }

    public function create(Request $request): View
    {
        // Ensure default categories exist (avoid doing writes every time the form is opened).
        $seedKey = 'ticket_categories_seeded_v1';
        $seeded = Cache::get($seedKey);
        if (! $seeded) {
            $slugs = ['technical-support', 'repair', 'installation'];
            $existing = TicketCategory::query()->whereIn('slug', $slugs)->count();
            if ($existing < count($slugs)) {
                $defaults = [
                    ['name' => 'Technical Support', 'slug' => 'technical-support', 'description' => 'General troubleshooting and support'],
                    ['name' => 'Repair', 'slug' => 'repair', 'description' => 'Fix or replace broken equipment'],
                    ['name' => 'Installation', 'slug' => 'installation', 'description' => 'Install and configure hardware or software'],
                ];
                foreach ($defaults as $d) {
                    TicketCategory::firstOrCreate(['slug' => $d['slug']], $d);
                }
            }
            Cache::put($seedKey, 1, 86400);
        }

        $categories = Cache::remember('ticket_categories_list', 300, function () {
            return TicketCategory::whereIn('slug', ['technical-support', 'repair', 'installation'])->orderBy('name')->get();
        });
        $priorities = Cache::remember('ticket_priorities_list', 300, function () {
            return TicketPriority::query()->where('active', true)->orderBy('sort_order')->orderBy('id')->get();
        });
        $source = $request->get('source', Ticket::SOURCE_SELF_SERVICE);

        if (in_array($source, [Ticket::SOURCE_PHONE, Ticket::SOURCE_WALK_IN], true) && ! auth()->user()?->isFrontDesk()) {
            abort(403, 'Only Front Desk can log tickets from calls or walk-ins.');
        }
        if ($source === Ticket::SOURCE_SELF_SERVICE && auth()->user()?->isFrontDesk() && ! auth()->user()?->isAdmin()) {
            abort(403, 'Front Desk can only log tickets from calls or walk-ins. Use Log (phone) or Log (walk-in).');
        }
        if ($source === Ticket::SOURCE_SELF_SERVICE && auth()->user()?->isItStaff() && ! auth()->user()?->isAdmin()) {
            abort(403, 'IT Staff resolve and update tickets; they cannot submit new tickets.');
        }
        if ($source === Ticket::SOURCE_SELF_SERVICE && auth()->user()?->role === \App\Models\User::ROLE_EMPLOYEE && ! auth()->user()?->isItStaff() && ! auth()->user()?->isAdmin()) {
            abort(403, 'Employees cannot submit new tickets. Accept tickets from the dashboard or Accepted tickets list.');
        }

        return view('tickets.create', compact('categories', 'priorities', 'source'));
    }

    /**
     * Lightweight HTML for "Create ticket" modal (no full layout).
     */
    public function createModal(Request $request): Response
    {
        // Ensure default categories exist (avoid doing writes every time the form is opened).
        $seedKey = 'ticket_categories_seeded_v1';
        $seeded = Cache::get($seedKey);
        if (! $seeded) {
            $slugs = ['technical-support', 'repair', 'installation'];
            $existing = TicketCategory::query()->whereIn('slug', $slugs)->count();
            if ($existing < count($slugs)) {
                $defaults = [
                    ['name' => 'Technical Support', 'slug' => 'technical-support', 'description' => 'General troubleshooting and support'],
                    ['name' => 'Repair', 'slug' => 'repair', 'description' => 'Fix or replace broken equipment'],
                    ['name' => 'Installation', 'slug' => 'installation', 'description' => 'Install and configure hardware or software'],
                ];
                foreach ($defaults as $d) {
                    TicketCategory::firstOrCreate(['slug' => $d['slug']], $d);
                }
            }
            Cache::put($seedKey, 1, 86400);
        }

        $user = auth()->user();
        if (! $user) abort(403);

        // Who can open the create modal?
        // - Admin: yes
        // - Front Desk: yes (phone/walk-in only)
        // - IT staff: no
        // - Employee: no
        if ($user->isItStaff() && ! $user->isAdmin()) {
            abort(403, 'IT Staff cannot submit new tickets.');
        }
        if ($user->role === \App\Models\User::ROLE_EMPLOYEE && ! $user->isItStaff() && ! $user->isAdmin()) {
            abort(403, 'Employees cannot submit new tickets.');
        }

        $categories = Cache::remember('ticket_categories_list', 300, function () {
            return TicketCategory::whereIn('slug', ['technical-support', 'repair', 'installation'])->orderBy('name')->get();
        });
        $priorities = Cache::remember('ticket_priorities_list', 300, function () {
            return TicketPriority::query()->where('active', true)->orderBy('sort_order')->orderBy('id')->get();
        });

        $allowedSources = [Ticket::SOURCE_SELF_SERVICE];
        if ($user->isAdmin() || $user->isFrontDesk()) {
            $allowedSources = [Ticket::SOURCE_PHONE, Ticket::SOURCE_WALK_IN];
            if ($user->isAdmin()) {
                $allowedSources[] = Ticket::SOURCE_SELF_SERVICE;
            }
        }

        return response()->view('tickets.partials.modal-create', compact('categories', 'priorities', 'allowedSources'), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $isModalCreate = $request->boolean('from_modal');
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => ($isModalCreate ? 'required' : 'nullable') . '|string|max:255',
            'scheduled_for' => ($isModalCreate ? 'required' : 'nullable') . '|date',
            'category_id' => 'required|exists:ticket_categories,id',
            'priority' => 'required|string|exists:ticket_priorities,key',
            'source' => 'required|in:self_service,phone,walk_in',
            'requester_name' => ($isModalCreate ? 'required' : 'nullable') . '|string|max:255',
            'requester_email' => 'nullable|email',
            'requester_phone' => ($isModalCreate ? 'required' : 'nullable') . '|string|max:24|regex:/^\+[0-9]{1,4}\s?[0-9]{6,15}$/',
        ], [
            'requester_phone.regex' => 'Contact number must be valid international format (example: +63 9123456789).',
        ]);

        if (in_array($validated['source'], [Ticket::SOURCE_PHONE, Ticket::SOURCE_WALK_IN], true) && ! auth()->user()?->isFrontDesk()) {
            abort(403, 'Only Front Desk can log tickets from calls or walk-ins.');
        }
        if ($validated['source'] === Ticket::SOURCE_SELF_SERVICE && auth()->user()?->isFrontDesk() && ! auth()->user()?->isAdmin()) {
            abort(403, 'Front Desk can only log tickets from calls or walk-ins.');
        }
        if ($validated['source'] === Ticket::SOURCE_SELF_SERVICE && auth()->user()?->isItStaff() && ! auth()->user()?->isAdmin()) {
            abort(403, 'IT Staff cannot submit new tickets.');
        }
        if ($validated['source'] === Ticket::SOURCE_SELF_SERVICE && auth()->user()?->role === \App\Models\User::ROLE_EMPLOYEE && ! auth()->user()?->isItStaff() && ! auth()->user()?->isAdmin()) {
            abort(403, 'Employees cannot submit new tickets.');
        }

        // DB column is non-nullable; keep description optional in UI by storing empty string when omitted.
        $validated['description'] = $validated['description'] ?? '';

        $ticket = new Ticket($validated);
        $ticket->ticket_number = Ticket::generateTicketNumber();
        $ticket->status = Ticket::STATUS_OPEN;
        if (auth()->id()) {
            $ticket->submitter_id = auth()->id();
        }
        $ticket->save();

        $ticket->comments()->create([
            'user_id' => auth()->id(),
            'type' => TicketComment::TYPE_SYSTEM,
            'body' => 'Ticket created (' . ($validated['source'] === 'self_service' ? 'system' : 'call-logged') . ').',
        ]);

        $this->broadcastTicketsUpdated('New ticket ' . $ticket->ticket_number . ' submitted.');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ticket_number' => $ticket->ticket_number,
                'redirectUrl'   => route('tickets.index'),
                'modalUrl'      => route('tickets.modal', $ticket),
                'message'       => 'Ticket ' . $ticket->ticket_number . ' has been created.',
            ]);
        }

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket ' . $ticket->ticket_number . ' has been created.');
    }

    public function show(Ticket $ticket): \Illuminate\Http\RedirectResponse
    {
        // Full show page removed — redirect to index; modal is the canonical view
        return redirect()->route('tickets.index');
    }

    /**
     * Lightweight HTML for "View" modal (no full layout).
     */
    public function modal(Ticket $ticket): Response
    {
        $ticket->load([
            'category:id,name',
            'submitter:id,name,role',
            'assignee:id,name,role',
            'acceptedBy:id,name,role',
            'comments' => fn ($q) => $q->with('user:id,name,role')->latest()->limit(100),
        ]);

        // Cache the rendered HTML for 30s — fast repeat opens, invalidated on ticket update
        $cacheKey = 'ticket_modal_' . $ticket->id . '_' . ($ticket->updated_at?->timestamp ?? 0);
        $html = \Illuminate\Support\Facades\Cache::remember($cacheKey, 30, function () use ($ticket) {
            return view('tickets.partials.modal-show', compact('ticket'))->render();
        });

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    /**
     * Lightweight HTML for "Edit" modal (no full layout).
     */
    public function editModal(Ticket $ticket): Response
    {
        $user = auth()->user();
        $isFrontDeskOwner = $user && $user->isFrontDesk() && !$user->isAdmin() && $ticket->submitter_id === $user->id;
        if (! $user || (! $user->isAdmin() && ! $user->isItStaff() && ! $isFrontDeskOwner)) {
            abort(403, 'You do not have permission to edit this ticket.');
        }

        $ticket->load(['category', 'submitter', 'assignee', 'acceptedBy']);
        $categories = Cache::remember('ticket_categories_list', 60, fn () =>
            TicketCategory::orderBy('name')->get()
        );
        $priorities = Cache::remember('ticket_priorities_list', 60, fn () =>
            TicketPriority::where('active', true)->orderBy('sort_order')->orderBy('id')->get()
        );

        return response()->view('tickets.partials.modal-edit', compact('ticket', 'categories', 'priorities'), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    /**
     * Lightweight HTML for "Update Status" modal (no full layout).
     */
    public function statusModal(Ticket $ticket): Response
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        $isAssignee = $ticket->assignee_id === $user->id;
        $canUpdateAsAssignee = $isAssignee && in_array($user->role, [\App\Models\User::ROLE_EMPLOYEE, \App\Models\User::ROLE_IT_STAFF], true);
        $canUpdateAsStaff = $user->isItStaff() || $user->isAdmin();
        if (! $canUpdateAsStaff && ! $canUpdateAsAssignee) {
            abort(403, 'Only the assignee (employee/IT staff) or Admin can update this ticket.');
        }
        if ($ticket->assignee_id === null || $ticket->status === Ticket::STATUS_OPEN) {
            abort(403, 'Status can only be updated after the ticket is accepted.');
        }

        $allStatuses = Ticket::statusLabels();
        $allowedStatuses = $allStatuses;
        if ($canUpdateAsAssignee && ! $canUpdateAsStaff) {
            $allowedStatuses = array_intersect_key($allStatuses, array_flip([
                Ticket::STATUS_RESOLVED,
                Ticket::STATUS_CANCELLED,
            ]));
        }

        return response()->view('tickets.partials.modal-status', compact('ticket', 'allowedStatuses'), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    public function edit(Ticket $ticket): View|RedirectResponse
    {
        $user = auth()->user();
        $isFrontDeskOwner = $user && $user->isFrontDesk() && !$user->isAdmin() && $ticket->submitter_id === $user->id;
        if (! $user || (! $user->isAdmin() && ! $user->isItStaff() && ! $isFrontDeskOwner)) {
            abort(403, 'You do not have permission to edit this ticket.');
        }
        $ticket->load('category');
        $categories = Cache::remember('ticket_categories_list', 60, fn () =>
            TicketCategory::orderBy('name')->get()
        );
        $priorities = Cache::remember('ticket_priorities_list', 60, fn () =>
            TicketPriority::where('active', true)->orderBy('sort_order')->orderBy('id')->get()
        );

        return view('tickets.edit', compact('ticket', 'categories', 'priorities'));
    }

    public function accept(Request $request, Ticket $ticket): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }
        // Only Employee or Admin can accept tickets
        if (! in_array($user->role, [\App\Models\User::ROLE_EMPLOYEE], true) && ! $user->isAdmin()) {
            abort(403, 'Only employees or admins can accept tickets.');
        }
        if ($ticket->status !== Ticket::STATUS_OPEN) {
            return redirect()->route('tickets.index')
                ->with('error', 'Only open tickets can be accepted.');
        }
        if ($ticket->assignee_id !== null) {
            return redirect()->route('tickets.index')
                ->with('error', 'This ticket is already assigned.');
        }

        $ticket->assignee_id = $user->id;
        $ticket->accepted_by_id = $user->id;
        $ticket->assigned_at = now();
        $ticket->status = Ticket::STATUS_IN_PROGRESS;
        $ticket->save();

        $ticket->comments()->create([
            'user_id' => $user->id,
            'type' => TicketComment::TYPE_SYSTEM,
            'body' => $user->name . ' accepted this ticket. Status set to In Progress.',
        ]);

        $this->broadcastTicketsUpdated();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'You are now working on this ticket. Status set to In Progress.',
                'redirectUrl' => route('tickets.index'),
            ]);
        }

        return redirect()->route('tickets.index')
            ->with('success', 'You are now working on this ticket. Status set to In Progress.');
    }

    /**
     * Quick close: Resolved -> Closed (Admin/IT Staff only).
     */
    public function close(Request $request, Ticket $ticket): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        if (! $user || (! $user->isAdmin() && ! $user->isItStaff())) {
            abort(403);
        }

        if ($ticket->status !== Ticket::STATUS_RESOLVED) {
            return response()->json(['message' => 'Only resolved tickets can be closed.'], 422);
        }

        $ticket->status = Ticket::STATUS_CLOSED;
        $ticket->closed_at = $ticket->closed_at ?? now();
        $ticket->save();

        $ticket->comments()->create([
            'user_id' => $user->id,
            'type' => TicketComment::TYPE_SYSTEM,
            'body' => 'Ticket closed by ' . $user->name . '.',
        ]);

        $this->broadcastTicketsUpdated();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => 'Ticket closed.', 'status' => $ticket->status]);
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket closed.');
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        // Full ticket edit (from edit form): Admin and IT Staff always; Front Desk only for their own tickets
        if ($request->filled('title')) {
            $isFrontDeskOwner = $user->isFrontDesk() && !$user->isAdmin() && $ticket->submitter_id === $user->id;
            if (! $user->isAdmin() && ! $user->isItStaff() && ! $isFrontDeskOwner) {
                abort(403, 'You do not have permission to edit this ticket.');
            }
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'location' => 'nullable|string|max:255',
                'scheduled_for' => 'nullable|date',
                'category_id' => 'required|exists:ticket_categories,id',
                'priority' => 'required|string|exists:ticket_priorities,key',
                'requester_name' => 'nullable|string|max:255',
                'requester_email' => 'nullable|email',
                'requester_phone' => 'nullable|string|max:24|regex:/^\+[0-9]{1,4}\s?[0-9]{6,15}$/',
            ], [
                'requester_phone.regex' => 'Contact number must be valid international format (example: +63 9123456789).',
            ]);
            $ticket->fill($validated);
            $ticket->save();
            $ticket->comments()->create([
                'user_id' => $user->id,
                'type' => TicketComment::TYPE_SYSTEM,
                'body' => 'Ticket details updated by ' . $user->name . '.',
            ]);
            $this->broadcastTicketsUpdated();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'ok' => true,
                    'message' => 'Ticket updated.',
                    'redirectUrl' => route('tickets.index'),
                ]);
            }

            return redirect()->route('tickets.index')->with('success', 'Ticket updated.');
        }

        // Status update (from show page)
        $isAssignee = $ticket->assignee_id === $user->id;
        $canUpdateAsAssignee = $isAssignee && in_array($user->role, [\App\Models\User::ROLE_EMPLOYEE, \App\Models\User::ROLE_IT_STAFF], true);
        $canUpdateAsStaff = $user->isItStaff() || $user->isAdmin();

        if (! $canUpdateAsStaff && ! $canUpdateAsAssignee) {
            abort(403, 'Only the assignee (employee/IT staff) or Admin can update this ticket.');
        }
        if ($ticket->assignee_id === null || $ticket->status === Ticket::STATUS_OPEN) {
            $msg = 'Status can only be updated after the ticket is accepted.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $msg], 422);
            }
            return redirect()->route('tickets.index')->with('error', $msg);
        }

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed,cancelled',
            'resolution_notes' => 'nullable|string',
        ]);

        // Assignee (employee) may only set status to Done (resolved) or Cancelled
        if ($canUpdateAsAssignee && ! $canUpdateAsStaff) {
            if (! in_array($validated['status'], [Ticket::STATUS_RESOLVED, Ticket::STATUS_CANCELLED], true)) {
                return redirect()->route('tickets.index')
                    ->with('error', 'You can only mark this ticket as Done or Cancelled.');
            }
        }

        $oldStatus = $ticket->status;
        $ticket->status = $validated['status'];
        if ($validated['status'] === Ticket::STATUS_RESOLVED && ! $ticket->resolved_at) {
            $ticket->resolved_at = now();
        }
        if (in_array($validated['status'], [Ticket::STATUS_CLOSED, Ticket::STATUS_CANCELLED], true)) {
            $ticket->closed_at = $ticket->closed_at ?? now();
        }
        if (! empty($validated['resolution_notes'])) {
            $ticket->resolution_notes = $validated['resolution_notes'];
        }
        $ticket->save();

        $labels = Ticket::statusLabels();
        $ticket->comments()->create([
            'user_id' => $user->id,
            'type' => TicketComment::TYPE_SYSTEM,
            'body' => 'Status changed from ' . ($labels[$oldStatus] ?? $oldStatus) . ' to ' . ($labels[$validated['status']] ?? $validated['status']) . '.',
        ]);

        $this->broadcastTicketsUpdated();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'Ticket updated.',
                'status' => $ticket->status,
            ]);
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket updated.');
    }

    public function storeComment(Request $request, Ticket $ticket): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $validated = $request->validate(['body' => 'required|string|max:5000']);

        $ticket->comments()->create([
            'user_id' => auth()->id(),
            'type' => TicketComment::TYPE_COMMENT,
            'body' => $validated['body'],
        ]);

        $this->broadcastTicketsUpdated();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'Comment added.']);
        }

        return redirect()->route('tickets.index')->with('success', 'Comment added.');
    }

    public function destroy(Request $request, Ticket $ticket): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Only admins can delete tickets.');
        }

        $num = $ticket->ticket_number;
        $ticket->delete();
        $this->broadcastTicketsUpdated();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => 'Ticket ' . $num . ' has been deleted.']);
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket ' . $num . ' has been deleted.');
    }
    /**
     * Live search — returns up to 8 matching tickets as JSON for the header search dropdown.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->get('q', ''));
        $all = $request->boolean('all');

        if (!$all && strlen($q) < 1) {
            return response()->json([]);
        }

        $user = auth()->user();

        $query = Ticket::query()
            ->select(['id', 'ticket_number', 'title', 'status', 'priority'])
            ->latest()
            ->limit($all ? 500 : 8);

        if (!$all && $q !== '') {
            $term = '%' . $q . '%';
            $query->where(function ($sq) use ($term) {
                $sq->where('title', 'like', $term)
                   ->orWhere('ticket_number', 'like', $term)
                   ->orWhere('description', 'like', $term);
            });
        }

        // Apply same visibility rules as index
        if ($user->role === \App\Models\User::ROLE_EMPLOYEE && ! $user->isItStaff() && ! $user->isAdmin()) {
            $query->where(function ($q2) use ($user) {
                $q2->where('assignee_id', $user->id)
                   ->orWhere(function ($q3) {
                       $q3->where('status', Ticket::STATUS_OPEN)->whereNull('assignee_id');
                   });
            });
        }

        $tickets = $query->get()->map(fn ($t) => [
            'id'            => $t->id,
            'ticket_number' => $t->ticket_number,
            'title'         => $t->title,
            'status'        => $t->status,
            'url'           => route('tickets.modal', $t),
        ]);

        return response()->json($tickets);
    }

    /**
     * Broadcast TicketsUpdated so other clients can refresh. Fails silently if Reverb is not running.
     * Also bumps cache key so polling fallback can detect changes.
     */
    private function broadcastTicketsUpdated(string $message = 'Tickets updated'): void
    {
        Cache::put('tickets_list_updated_at', now()->timestamp, 3600);
        try {
            event(new TicketsUpdated($message));
        } catch (\Throwable $e) {
            // Reverb not running — polling fallback will still refresh clients
        }
    }
}
