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
        // MySQL özel bir sintaks kullanılarak ENUM kolonunu değiştiriyoruz
        // Mevcut değerleri korumak ve yeni bir değer eklemek için kullanılır.
        DB::statement("ALTER TABLE whatsapp_messages CHANGE COLUMN `type` `type` ENUM(
            'appointment_created',
            'appointment_scheduled',
            'appointment_checked_in',
            'appointment_completed',
            'appointment_cancelled',
            'appointment_updated',
            'appointment_reminder',
            'checkin_confirmation',
            'checkout_confirmation',
            'manual',
            'appointment_payment_info' 
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Geri alma işlemi için, eklediğimiz değeri ENUM listesinden çıkarıyoruz.
        DB::statement("ALTER TABLE whatsapp_messages CHANGE COLUMN `type` `type` ENUM(
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
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
    }
};