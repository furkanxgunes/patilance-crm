<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->integer('send_notification_checkin')->nullable()->after('send_notification');
            $table->integer('send_notification_checkout')->nullable()->after('send_notification_checkin');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['send_notification_checkin', 'send_notification_checkout']);
        });
    }
};
