<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $name = config('app.name', 'Default Shop');

        $shopId = DB::table('shops')->insertGetId([
            'name' => $name,
            'slug' => Str::slug($name) ?: 'default',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->update(['shop_id' => $shopId]);
        DB::table('orders')->update(['shop_id' => $shopId]);

        DB::table('admins')->update([
            'role' => 'super_admin',
            'shop_id' => null,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('products')->update(['shop_id' => null]);
        DB::table('orders')->update(['shop_id' => null]);
        DB::table('admins')->update(['role' => 'shop_admin', 'shop_id' => null]);
        DB::table('shops')->delete();
    }
};
