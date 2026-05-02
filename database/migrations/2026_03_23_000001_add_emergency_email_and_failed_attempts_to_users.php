<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('emergency_email')->nullable()->after('email');
            $table->unsignedTinyInteger('failed_login_attempts')->default(0)->after('emergency_email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['emergency_email', 'failed_login_attempts']);
        });
    }
};
