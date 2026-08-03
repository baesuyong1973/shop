<?php

use App\Models\Shop;
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
        Schema::create('shop_locales', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Shop::class)->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->timestamps();

            $table->unique(['shop_id', 'locale']);
        });

        $now = now();

        $rows = Shop::pluck('id')->flatMap(fn (int $shopId) => [
            ['shop_id' => $shopId, 'locale' => 'ja', 'created_at' => $now, 'updated_at' => $now],
            ['shop_id' => $shopId, 'locale' => 'en', 'created_at' => $now, 'updated_at' => $now],
        ])->all();

        if ($rows !== []) {
            DB::table('shop_locales')->insert($rows);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_locales');
    }
};
