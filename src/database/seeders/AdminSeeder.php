<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Admin::where('email', 'admin@example.com')->exists()) {
            return;
        }

        $admin = new Admin();
        $admin->forceFill([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => Admin::ROLE_SUPER_ADMIN,
            'shop_id' => null,
        ]);
        $admin->save();
    }
}
