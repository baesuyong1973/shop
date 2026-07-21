<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Shop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Admin::where('email', 'admin@example.com')->exists()) {
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

        $shopAdmins = [
            ['email' => 'shop1@example.com', 'shop_name' => 'Shop1', 'shop_slug' => 'shop1'],
            ['email' => 'shop2@example.com', 'shop_name' => 'Shop2', 'shop_slug' => 'shop2'],
        ];

        foreach ($shopAdmins as $data) {
            if (Admin::where('email', $data['email'])->exists()) {
                continue;
            }

            $shop = Shop::firstOrCreate(
                ['slug' => $data['shop_slug']],
                ['name' => $data['shop_name']]
            );

            $shopAdmin = new Admin();
            $shopAdmin->forceFill([
                'name' => $data['shop_name'].' Admin',
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => Admin::ROLE_SHOP_ADMIN,
                'shop_id' => $shop->id,
            ]);
            $shopAdmin->save();
        }
    }
}
