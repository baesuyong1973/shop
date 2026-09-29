<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Units with a shop_id are that shop's own additions; units without one
     * are shared by every shop and managed by super admins.
     */
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->dropUnique(['name']);
            $table->unique(['shop_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropUnique(['shop_id', 'name']);
            $table->dropConstrainedForeignId('shop_id');
            $table->unique('name');
        });
    }
};
