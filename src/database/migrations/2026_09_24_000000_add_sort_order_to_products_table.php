<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('shop_id');
        });

        // Seed sort_order so the display order stays unchanged right after
        // deploy (it previously matched created_at desc per shop).
        DB::table('products')
            ->select('id', 'shop_id')
            ->orderBy('shop_id')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('shop_id')
            ->each(function ($products) {
                foreach ($products->values() as $index => $product) {
                    DB::table('products')
                        ->where('id', $product->id)
                        ->update(['sort_order' => $index]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
