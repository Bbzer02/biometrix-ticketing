<?php

namespace Database\Seeders;

use App\Models\TicketCategory;
use Illuminate\Database\Seeder;

class TicketCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Technical Support', 'slug' => 'technical-support', 'description' => 'General troubleshooting and technical support'],
            ['name' => 'Repair', 'slug' => 'repair', 'description' => 'Fix or replace broken equipment'],
            ['name' => 'Installation', 'slug' => 'installation', 'description' => 'Install and configure hardware or software'],
        ];

        foreach ($categories as $cat) {
            TicketCategory::firstOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }
    }
}
