<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('help_messages', function (Blueprint $table) {
            $table->foreignId('recipient_id')->nullable()->after('sender_id')
                  ->constrained('users')->nullOnDelete();
            $table->index(['recipient_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('help_messages', function (Blueprint $table) {
            $table->dropForeign(['recipient_id']);
            $table->dropIndex(['recipient_id', 'created_at']);
            $table->dropColumn('recipient_id');
        });
    }
};
