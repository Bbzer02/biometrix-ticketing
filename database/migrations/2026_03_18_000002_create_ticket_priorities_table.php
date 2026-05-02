<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('key', 30)->unique(); // low, normal, major, critical (admin-managed)
            $table->string('label', 60);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        DB::table('ticket_priorities')->insert([
            ['key' => 'low', 'label' => 'Low', 'description' => 'Minor request', 'sort_order' => 10, 'active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'normal', 'label' => 'Normal', 'description' => 'Workaround available', 'sort_order' => 20, 'active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'major', 'label' => 'Major', 'description' => 'Business impacted', 'sort_order' => 30, 'active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'critical', 'label' => 'Critical', 'description' => 'Service down', 'sort_order' => 40, 'active' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_priorities');
    }
};

