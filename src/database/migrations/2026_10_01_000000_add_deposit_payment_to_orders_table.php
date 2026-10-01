<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Deposit (part of the total paid up front via PayPay) for each order.
     * Orders placed before deposits existed keep payment_status
     * "not_required" and a zero deposit.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('deposit_amount')->default(0)->after('total_amount');
            $table->string('payment_status', 20)->default('not_required')->after('deposit_amount')->index();
            $table->string('payment_reference', 64)->nullable()->unique()->after('payment_status');
            $table->string('payment_code_id', 128)->nullable()->after('payment_reference');
            $table->string('payment_id', 64)->nullable()->after('payment_code_id');
            $table->timestamp('paid_at')->nullable()->after('payment_id');
            $table->timestamp('refunded_at')->nullable()->after('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['payment_reference']);
            $table->dropIndex(['payment_status']);
            $table->dropColumn(['deposit_amount', 'payment_status', 'payment_reference', 'payment_code_id', 'payment_id', 'paid_at', 'refunded_at']);
        });
    }
};
