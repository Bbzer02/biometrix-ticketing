<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Normalize legacy/invalid status values to keep ticket workflows stable.
        DB::table('tickets')
            ->whereNotIn('status', ['open', 'in_progress', 'resolved', 'closed', 'cancelled'])
            ->update(['status' => 'open']);

        // Normalize invalid source values to a safe default.
        DB::table('tickets')
            ->whereNotIn('source', ['self_service', 'phone', 'walk_in'])
            ->update(['source' => 'self_service']);
    }

    public function down(): void
    {
        // Data normalization migration is intentionally irreversible.
    }
};

