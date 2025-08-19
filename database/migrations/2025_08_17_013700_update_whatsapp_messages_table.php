<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First, we need to drop the foreign key constraints
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['appointment_id']);
        });

        // Then modify the type column
        DB::statement("ALTER TABLE whatsapp_messages MODIFY COLUMN `type` ENUM(
            'appointment_created',
            'appointment_scheduled',
            'appointment_checked_in',
            'appointment_completed',
            'appointment_cancelled',
            'appointment_updated',
            'appointment_reminder',
            'checkin_confirmation',
            'checkout_confirmation',
            'manual'
        ) DEFAULT 'manual'");

        // Re-add the foreign key constraints
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('set null');
        });
    }

    public function down()
    {
        // Revert back to the original enum values if needed
        // Note: This is a simplified version, you might need to adjust based on your needs
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['appointment_id']);
        });

        DB::statement("ALTER TABLE whatsapp_messages MODIFY COLUMN `type` ENUM(
            'appointment_created',
            'appointment_reminder',
            'checkin_confirmation',
            'checkout_confirmation',
            'manual'
        ) DEFAULT 'manual'");

        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('set null');
        });
    }
};
