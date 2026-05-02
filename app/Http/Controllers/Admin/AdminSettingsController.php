<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index');
    }

    public function resetSystemData(Request $request): RedirectResponse
    {
        $request->validate([
            'confirmation' => ['required', 'in:RESET'],
        ], [
            'confirmation.in' => 'Type RESET to confirm this action.',
        ]);

        $ticketCount = Ticket::count();
        $commentCount = TicketComment::count();
        $loginLogCount = LoginLog::count();

        DB::transaction(function (): void {
            TicketComment::query()->delete();
            Ticket::query()->delete();
            LoginLog::query()->delete();
        });

        return redirect()
            ->route('admin.settings')
            ->with(
                'success',
                "System data cleared: {$ticketCount} ticket(s), {$commentCount} audit comment(s), and {$loginLogCount} login log(s)."
            );
    }
}
