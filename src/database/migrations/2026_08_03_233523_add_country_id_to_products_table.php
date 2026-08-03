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
            $table->foreignId('country_id')->nullable()->after('prefecture_id')->constrained()->nullOnDelete();
        });

        // All existing products only ever had a Japanese prefecture, so
        // backfill their country to Japan (creating it if the countries
        // seeder hasn't run yet).
        $japanId = DB::table('countries')->where('name', '日本')->value('id');

        if ($japanId === null) {
            $now = now();
            $japanId = DB::table('countries')->insertGetId([
                'name' => '日本',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('products')->whereNotNull('prefecture_id')->update(['country_id' => $japanId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('country_id');
        });
    }
};
