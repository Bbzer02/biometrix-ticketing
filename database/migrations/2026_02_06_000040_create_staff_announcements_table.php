<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            // employee, front_desk, it_staff, all
            $table->string('audience', 20)->default('all');
            // low, normal, major, critical – reuse ticket priority semantics
            $table->string('priority', 20)->default('normal');
            $table->string('status', 20)->default('open'); // open, acknowledged
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_announcements');
    }
};

