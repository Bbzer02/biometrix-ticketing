<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Align status: "new" -> "open" (workflow: Open → In Progress → Resolved → Closed)
        DB::table('tickets')->where('status', 'new')->update(['status' => 'open']);
        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE tickets MODIFY status VARCHAR(30) NOT NULL DEFAULT 'open'");
        }

        Schema::create('ticket_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20)->default('comment'); // comment, system
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_comments');
        DB::table('tickets')->where('status', 'open')->update(['status' => 'new']);
        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE tickets MODIFY status VARCHAR(30) NOT NULL DEFAULT 'new'");
        }
    }
};
