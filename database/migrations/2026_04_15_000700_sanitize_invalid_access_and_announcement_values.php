<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Keep staff announcements safe from malformed/injected enum-like values.
        DB::table('staff_announcements')
            ->whereNotIn('status', ['open', 'acknowledged'])
            ->update(['status' => 'open']);

        DB::table('staff_announcements')
            ->whereNotIn('priority', ['low', 'normal', 'major', 'critical'])
            ->update(['priority' => 'normal']);

        DB::table('staff_announcements')
            ->whereNotIn('audience', ['all', 'selected_users', 'employee', 'front_desk', 'it_staff'])
            ->update(['audience' => 'all']);

        // Normalize access request status + email casing for duplicate protection.
        DB::table('access_requests')
            ->whereNotIn('status', ['pending', 'approved', 'ignored'])
            ->update(['status' => 'pending']);

        DB::statement('UPDATE access_requests SET email = LOWER(TRIM(email))');
    }

    public function down(): void
    {
        // Data normalization migration is intentionally irreversible.
    }
};

