<?php

namespace Database\Seeders;

use App\Models\Did;
use Illuminate\Database\Seeder;

class DidSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Did::create([
            'number' => '+611300784081',
            'name' => 'Test 1',
            'description' => 'Test 1',
        ]);
        Did::create([
            'number' => '+61744298674',
            'name' => 'Test 2',
            'description' => 'Test 2',
        ]);
        Did::create([
            'number' => '+61748005097',
            'name' => 'Test 3',
            'description' => 'Test 3',
        ]);
        Did::create([
            'number' => '+61748017012',
            'name' => 'Test 4',
            'description' => 'Test 4',
        ]);
    }
}
