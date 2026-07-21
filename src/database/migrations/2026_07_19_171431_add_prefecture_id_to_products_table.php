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
            $table->foreignId('prefecture_id')->nullable()->after('origin')->constrained();
        });

        foreach (DB::table('prefectures')->pluck('id', 'name') as $name => $id) {
            DB::table('products')->where('origin', $name)->update(['prefecture_id' => $id]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('origin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('origin')->nullable()->after('is_active');
        });

        foreach (DB::table('prefectures')->pluck('name', 'id') as $id => $name) {
            DB::table('products')->where('prefecture_id', $id)->update(['origin' => $name]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('prefecture_id');
        });
    }
};
