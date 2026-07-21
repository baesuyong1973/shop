<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        $placedId = DB::table('order_statuses')->insertGetId([
            'key' => 'placed',
            'label' => '注文確定',
            'sort_order' => 1,
            'is_initial' => true,
            'is_void' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $cancelledId = DB::table('order_statuses')->insertGetId([
            'key' => 'cancelled',
            'label' => 'キャンセル',
            'sort_order' => 2,
            'is_initial' => false,
            'is_void' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $handedOverId = DB::table('order_statuses')->insertGetId([
            'key' => 'handed_over',
            'label' => 'お渡し済み',
            'sort_order' => 3,
            'is_initial' => false,
            'is_void' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('order_status_transitions')->insert([
            ['from_status_id' => $placedId, 'to_status_id' => $cancelledId, 'created_at' => $now, 'updated_at' => $now],
            ['from_status_id' => $placedId, 'to_status_id' => $handedOverId, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('order_statuses')->whereIn('key', ['placed', 'cancelled', 'handed_over'])->delete();
    }
};
