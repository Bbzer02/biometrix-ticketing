<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $activeSessions = [];
        $sessionsTableExists = \Illuminate\Support\Facades\Schema::hasTable('sessions');

        if ($sessionsTableExists && config('session.driver') === 'database') {
            $sessionLifetime = (int) config('session.lifetime', 120);
            $minLastActivity = now()->subMinutes($sessionLifetime)->timestamp;

            $rows = DB::table('sessions')
                ->whereNotNull('user_id')
                ->where('last_activity', '>=', $minLastActivity)
                ->orderByDesc('last_activity')
                ->get();

            $userIds = $rows->pluck('user_id')->unique()->values()->all();
            $users = User::whereIn('id', $userIds)->get()->keyBy('id');

            foreach ($rows as $row) {
                $activeSessions[] = (object) [
                    'user' => $users->get($row->user_id),
                    'user_id' => $row->user_id,
                    'ip_address' => $row->ip_address,
                    'user_agent' => $row->user_agent,
                    'last_activity' => $row->last_activity,
                    'last_activity_at' => \Carbon\Carbon::createFromTimestamp($row->last_activity),
                ];
            }
        }

        $loginLogs = LoginLog::with('user')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('admin.activity', compact('activeSessions', 'loginLogs'));
    }
}
