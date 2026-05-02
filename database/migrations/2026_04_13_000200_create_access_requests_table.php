<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_requests', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->index(['status', 'created_at'], 'access_requests_status_created_idx');
            $table->index(['email', 'status'], 'access_requests_email_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_requests');
    }
};

