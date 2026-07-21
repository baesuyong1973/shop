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
        $slug = Str::slug($name) ?: 'default';

        $shop = DB::table('shops')->where('slug', $slug)->first();

        if (! $shop) {
            return;
        }

        $inUse = DB::table('products')->where('shop_id', $shop->id)->exists()
            || DB::table('orders')->where('shop_id', $shop->id)->exists()
            || DB::table('admins')->where('shop_id', $shop->id)->exists();

        if (! $inUse) {
            DB::table('shops')->where('id', $shop->id)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
