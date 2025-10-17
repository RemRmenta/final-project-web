<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesTableSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Water Leak', 'description' => 'Leaks or pipe bursts'],
            ['name' => 'Billing Concern', 'description' => 'Issues about billing or payment'],
            ['name' => 'New Connection', 'description' => 'Request for new water connection'],
            ['name' => 'Maintenance', 'description' => 'Regular maintenance or cleaning'],
        ];

        foreach ($categories as $c) {
            Category::firstOrCreate(['name' => $c['name']], $c);
        }
    }
}
