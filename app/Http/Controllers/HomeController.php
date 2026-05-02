<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\StaffAnnouncement;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    /** Dashboard cache TTL in seconds (45s = fast repeat loads, still fresh). */
    private const DASHBOARD_CACHE_TTL = 45;

    public function __invoke(Request $request): View
    {
        $dashboardRole = null;
        $stats = null;
        $recentTickets = null;
        $user = auth()->user();
        $announcements = collect();
        $hasAnnouncementsTable = Schema::hasTable('staff_announcements');

        if (! $user) {
            return view('home', compact('dashboardRole', 'stats', 'recentTickets', 'announcements'));
        }

        $cacheKey = 'dashboard_' . $user->id;

        $cached = Cache::remember($cacheKey, self::DASHBOARD_CACHE_TTL, function () use ($user) {
            return $this->buildDashboardData($user);
        });

        $dashboardRole = $cached['role'];
        $stats = $cached['stats'];
        $recentTickets = $cached['recentTickets'];
        $charts = $cached['charts'] ?? null;

        if ($hasAnnouncementsTable) {
            $announcements = StaffAnnouncement::openForUser($user)->take(3)->get();
        }

        return view('home', compact('dashboardRole', 'stats', 'recentTickets', 'announcements', 'charts'));
    }

    /**
     * @return array{role: string|null, stats: array<string, int>, recentTickets: \Illuminate\Database\Eloquent\Collection}
     */
    private function buildDashboardData(User $user): array
    {
        $monthExpr = $this->monthBucketExpression('created_at');
        $updatedMonthExpr = $this->monthBucketExpression('updated_at');
        $openSubmitters = fn ($q) => $q->whereIn('role', [User::ROLE_ADMIN, User::ROLE_FRONT_DESK]);

        if ($user->isAdmin()) {
            $countsByStatus = Ticket::query()
                ->select('status', DB::raw('count(*) as c'))
                ->groupBy('status')
                ->pluck('c', 'status');

            // Monthly tickets (last 6 months)
            $monthlyRaw = Ticket::query()
                ->select(DB::raw($monthExpr . ' as ym'), DB::raw('count(*) as c'))
                ->groupBy('ym')
                ->orderBy('ym', 'asc')
                ->limit(6)
                ->get();

            $monthly = [
                'labels' => $monthlyRaw->pluck('ym')->toArray(),
                'data' => $monthlyRaw->pluck('c')->map(fn ($v) => (int) $v)->toArray(),
            ];

            // Category breakdown
            $categoryRaw = Ticket::query()
                ->leftJoin('ticket_categories', 'tickets.category_id', '=', 'ticket_categories.id')
                ->select(DB::raw('COALESCE(ticket_categories.name, "Uncategorized") as label'), DB::raw('count(*) as c'))
                ->groupBy('label')
                ->orderBy('c', 'desc')
                ->limit(8)
                ->get();

            $category = [
                'labels' => $categoryRaw->pluck('label')->toArray(),
                'data' => $categoryRaw->pluck('c')->map(fn ($v) => (int) $v)->toArray(),
            ];

            // Status distribution
            $statusLabels = Ticket::statusLabels();
            $status = [
                'labels' => array_values($statusLabels),
                'data' => [
                    (int) ($countsByStatus[Ticket::STATUS_OPEN] ?? 0),
                    (int) ($countsByStatus[Ticket::STATUS_IN_PROGRESS] ?? 0),
                    (int) ($countsByStatus[Ticket::STATUS_RESOLVED] ?? 0),
                    (int) ($countsByStatus[Ticket::STATUS_CLOSED] ?? 0),
                    (int) ($countsByStatus[Ticket::STATUS_CANCELLED] ?? 0),
                ],
            ];

            $total = (int) $countsByStatus->sum();
            $activity = [
                'outer' => $total > 0 ? round(100 * (int) ($countsByStatus[Ticket::STATUS_OPEN] ?? 0) / $total, 1) : 0,
                'middle' => $total > 0 ? round(100 * (int) ($countsByStatus[Ticket::STATUS_IN_PROGRESS] ?? 0) / $total, 1) : 0,
                'inner' => $total > 0 ? round(100 * ((int) ($countsByStatus[Ticket::STATUS_RESOLVED] ?? 0) + (int) ($countsByStatus[Ticket::STATUS_CLOSED] ?? 0)) / $total, 1) : 0,
            ];

            return [
                'role' => 'admin',
                'stats' => [
                    'total_tickets' => (int) $countsByStatus->sum(),
                    'open' => (int) ($countsByStatus[Ticket::STATUS_OPEN] ?? 0),
                    'in_progress' => (int) ($countsByStatus[Ticket::STATUS_IN_PROGRESS] ?? 0),
                    'resolved' => (int) ($countsByStatus[Ticket::STATUS_RESOLVED] ?? 0),
                    'closed' => (int) ($countsByStatus[Ticket::STATUS_CLOSED] ?? 0),
                    'total_users' => User::count(),
                ],
                'recentTickets' => Ticket::with(['category', 'submitter', 'assignee'])
                    ->where('status', Ticket::STATUS_OPEN)
                    ->whereHas('submitter', $openSubmitters)
                    ->latest()
                    ->take(8)
                    ->get(),
                'charts' => [
                    'status' => $status,
                    'monthly' => $monthly,
                    'category' => $category,
                    'activity' => $activity,
                ],
            ];
        }

        if ($user->isItStaff()) {
            $countsByStatus = Ticket::query()
                ->select('status', DB::raw('count(*) as c'))
                ->groupBy('status')
                ->pluck('c', 'status');
            $monthlyRaw = Ticket::query()
                ->select(DB::raw($updatedMonthExpr . ' as ym'), DB::raw('count(*) as c'))
                ->whereIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED])
                ->where('assignee_id', $user->id)
                ->groupBy('ym')
                ->orderBy('ym', 'asc')
                ->limit(6)
                ->get();
            $monthly = [
                'labels' => $monthlyRaw->pluck('ym')->toArray(),
                'data' => $monthlyRaw->pluck('c')->map(fn ($v) => (int) $v)->toArray(),
            ];
            $categoryRaw = Ticket::query()
                ->leftJoin('ticket_categories', 'tickets.category_id', '=', 'ticket_categories.id')
                ->where('assignee_id', $user->id)
                ->select(DB::raw('COALESCE(ticket_categories.name, "Uncategorized") as label'), DB::raw('count(*) as c'))
                ->groupBy('label')
                ->orderBy('c', 'desc')
                ->limit(8)
                ->get();
            $category = [
                'labels' => $categoryRaw->pluck('label')->toArray(),
                'data' => $categoryRaw->pluck('c')->map(fn ($v) => (int) $v)->toArray(),
            ];
            $statusLabels = Ticket::statusLabels();
            $status = [
                'labels' => array_values($statusLabels),
                'data' => [
                    (int) ($countsByStatus[Ticket::STATUS_OPEN] ?? 0),
                    (int) ($countsByStatus[Ticket::STATUS_IN_PROGRESS] ?? 0),
                    (int) ($countsByStatus[Ticket::STATUS_RESOLVED] ?? 0),
                    (int) ($countsByStatus[Ticket::STATUS_CLOSED] ?? 0),
                    (int) ($countsByStatus[Ticket::STATUS_CANCELLED] ?? 0),
                ],
            ];
            return [
                'role' => 'it_staff',
                'stats' => [
                    'assigned_to_me' => Ticket::where('assignee_id', $user->id)->count(),
                    'open' => (int) ($countsByStatus[Ticket::STATUS_OPEN] ?? 0),
                    'in_progress' => (int) ($countsByStatus[Ticket::STATUS_IN_PROGRESS] ?? 0),
                    'resolved' => (int) ($countsByStatus[Ticket::STATUS_RESOLVED] ?? 0),
                ],
                'recentTickets' => Ticket::with(['category', 'submitter', 'assignee'])
                    ->where('status', Ticket::STATUS_OPEN)
                    ->whereHas('submitter', $openSubmitters)
                    ->latest()
                    ->take(8)
                    ->get(),
                'charts' => [
                    'status' => $status,
                    'monthly' => $monthly,
                    'category' => $category,
                ],
            ];
        }

        if ($user->isFrontDesk()) {
            // Front desk dashboard should mirror visible ticket board (all tickets by default).
            $allTicketsQuery = Ticket::query();
            $myLoggedQuery = Ticket::where('submitter_id', $user->id);
            $countsByStatus = (clone $allTicketsQuery)->select('status', DB::raw('count(*) as c'))->groupBy('status')->pluck('c', 'status');
            $monthlyRaw = (clone $allTicketsQuery)
                ->select(DB::raw($monthExpr . ' as ym'), DB::raw('count(*) as c'))
                ->groupBy('ym')
                ->orderBy('ym', 'asc')
                ->limit(6)
                ->get();
            $monthly = [
                'labels' => $monthlyRaw->pluck('ym')->toArray(),
                'data' => $monthlyRaw->pluck('c')->map(fn ($v) => (int) $v)->toArray(),
            ];
            $categoryRaw = (clone $allTicketsQuery)
                ->leftJoin('ticket_categories', 'tickets.category_id', '=', 'ticket_categories.id')
                ->select(DB::raw('COALESCE(ticket_categories.name, "Uncategorized") as label'), DB::raw('count(*) as c'))
                ->groupBy('label')
                ->orderBy('c', 'desc')
                ->limit(8)
                ->get();
            $category = [
                'labels' => $categoryRaw->pluck('label')->toArray(),
                'data' => $categoryRaw->pluck('c')->map(fn ($v) => (int) $v)->toArray(),
            ];
            $statusLabels = Ticket::statusLabels();
            $status = [
                'labels' => array_values($statusLabels),
                'data' => [
                    (int) ($countsByStatus[Ticket::STATUS_OPEN] ?? 0),
                    (int) ($countsByStatus[Ticket::STATUS_IN_PROGRESS] ?? 0),
                    (int) ($countsByStatus[Ticket::STATUS_RESOLVED] ?? 0),
                    (int) ($countsByStatus[Ticket::STATUS_CLOSED] ?? 0),
                    (int) ($countsByStatus[Ticket::STATUS_CANCELLED] ?? 0),
                ],
            ];
            return [
                'role' => 'front_desk',
                'stats' => [
                    'logged_by_me' => (int) (clone $myLoggedQuery)->count(),
                    'open' => (int) ($countsByStatus[Ticket::STATUS_OPEN] ?? 0),
                    'in_progress' => (int) ($countsByStatus[Ticket::STATUS_IN_PROGRESS] ?? 0),
                    'resolved' => (int) ($countsByStatus[Ticket::STATUS_RESOLVED] ?? 0),
                ],
                'recentTickets' => Ticket::with(['category', 'submitter', 'assignee'])
                    ->where('status', Ticket::STATUS_OPEN)
                    ->whereHas('submitter', $openSubmitters)
                    ->latest()
                    ->take(8)
                    ->get(),
                'charts' => [
                    'status' => $status,
                    'monthly' => $monthly,
                    'category' => $category,
                ],
            ];
        }

        // Employee
        // For charts: focus on tickets assigned to this employee
        $myTicketsQuery = Ticket::where('assignee_id', $user->id);
        $myCountsByStatus = (clone $myTicketsQuery)->select('status', DB::raw('count(*) as c'))->groupBy('status')->pluck('c', 'status');
        $monthlyRaw = (clone $myTicketsQuery)
            ->select(DB::raw($updatedMonthExpr . ' as ym'), DB::raw('count(*) as c'))
            ->groupBy('ym')
            ->orderBy('ym', 'asc')
            ->limit(6)
            ->get();
        $monthly = [
            'labels' => $monthlyRaw->pluck('ym')->toArray(),
            'data' => $monthlyRaw->pluck('c')->map(fn ($v) => (int) $v)->toArray(),
        ];
        $categoryRaw = (clone $myTicketsQuery)
            ->leftJoin('ticket_categories', 'tickets.category_id', '=', 'ticket_categories.id')
            ->select(DB::raw('COALESCE(ticket_categories.name, "Uncategorized") as label'), DB::raw('count(*) as c'))
            ->groupBy('label')
            ->orderBy('c', 'desc')
            ->limit(8)
            ->get();
        $category = [
            'labels' => $categoryRaw->pluck('label')->toArray(),
            'data' => $categoryRaw->pluck('c')->map(fn ($v) => (int) $v)->toArray(),
        ];
        // For stats cards: show global counts (same as admin)
        $globalCountsByStatus = Ticket::query()
            ->select('status', DB::raw('count(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status');
        $statusLabels = Ticket::statusLabels();
        $status = [
            'labels' => array_values($statusLabels),
            'data' => [
                (int) ($globalCountsByStatus[Ticket::STATUS_OPEN] ?? 0),
                (int) ($globalCountsByStatus[Ticket::STATUS_IN_PROGRESS] ?? 0),
                (int) ($globalCountsByStatus[Ticket::STATUS_RESOLVED] ?? 0),
                (int) ($globalCountsByStatus[Ticket::STATUS_CLOSED] ?? 0),
                (int) ($globalCountsByStatus[Ticket::STATUS_CANCELLED] ?? 0),
            ],
        ];

        return [
            'role' => 'employee',
            'stats' => [
                'my_tickets' => (int) $myCountsByStatus->sum(),
                'open' => (int) ($globalCountsByStatus[Ticket::STATUS_OPEN] ?? 0),
                'in_progress' => (int) ($globalCountsByStatus[Ticket::STATUS_IN_PROGRESS] ?? 0),
                'resolved' => (int) ($globalCountsByStatus[Ticket::STATUS_RESOLVED] ?? 0),
                'closed' => (int) ($globalCountsByStatus[Ticket::STATUS_CLOSED] ?? 0),
            ],
            'recentTickets' => Ticket::with(['category', 'submitter', 'assignee'])
                ->where(function ($q) use ($user) {
                    $q->where('assignee_id', $user->id)
                        ->orWhere(function ($sub) {
                            $sub->where('status', Ticket::STATUS_OPEN)
                                ->whereNull('assignee_id');
                        });
                })
                ->latest()
                ->take(8)
                ->get(),
            'charts' => [
                'status' => $status,
                'monthly' => $monthly,
                'category' => $category,
            ],
        ];
    }

    private function monthBucketExpression(string $column): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return "strftime('%Y-%m', {$column})";
        }

        return "DATE_FORMAT({$column}, '%Y-%m')";
    }
}
