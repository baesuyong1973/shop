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
        Schema::create('site_locales', function (Blueprint $table) {
            $table->id();
            $table->string('locale')->unique();
            $table->timestamps();
        });

        DB::table('site_locales')->insert([
            ['locale' => 'ja', 'created_at' => now(), 'updated_at' => now()],
            ['locale' => 'zh', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_locales');
    }
};
