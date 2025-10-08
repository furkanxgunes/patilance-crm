<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->boolean('send_notification_payment_status')->nullable()->after('send_notification_checkout');
            $table->boolean('payment_status')->nullable()->after('send_notification_payment_status');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('send_notification_payment_status');
            $table->dropColumn('payment_status');
        });
    }
};
