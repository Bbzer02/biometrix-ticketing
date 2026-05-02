<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 20)->unique();
            $table->string('title');
            $table->text('description');
            $table->foreignId('category_id')->constrained('ticket_categories')->cascadeOnDelete();
            $table->string('priority', 20)->default('normal'); // low, normal, major, critical
            $table->string('status', 30)->default('new'); // new, in_progress, waiting_on_user, resolved, closed
            $table->string('source', 20)->default('self_service'); // self_service, phone, walk_in
            $table->foreignId('submitter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('requester_name')->nullable(); // for phone/walk-in when no user
            $table->string('requester_email')->nullable();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
