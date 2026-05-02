<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_announcement_acks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('acknowledged_at')->useCurrent();
            $table->timestamps();

            $table->unique(['staff_announcement_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_announcement_acks');
    }
};

