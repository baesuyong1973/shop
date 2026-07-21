<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['個', '箱', 'kg'] as $name) {
            Unit::firstOrCreate(['name' => $name]);
        }
    }
}
