<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Speed up employee open-queue queries: status=open AND assignee_id IS NULL ORDER BY created_at DESC
        Schema::table('tickets', function (Blueprint $table) {
            $table->index(['status', 'assignee_id', 'created_at'], 'tickets_status_assignee_created_at_idx');
        });

        // Speed up notifications/audit trail queries: type=system ORDER BY created_at DESC (+ joins by ticket_id/user_id)
        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->index(['type', 'created_at'], 'ticket_comments_type_created_at_idx');
            $table->index('ticket_id', 'ticket_comments_ticket_id_idx');
            $table->index('user_id', 'ticket_comments_user_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('tickets_status_assignee_created_at_idx');
        });

        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->dropIndex('ticket_comments_type_created_at_idx');
            $table->dropIndex('ticket_comments_ticket_id_idx');
            $table->dropIndex('ticket_comments_user_id_idx');
        });
    }
};

