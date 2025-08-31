<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_scheduled_and_sent_at_to_whatsapp_messages_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('whatsapp_messages', 'scheduled_at')) {
                $table->dateTime('scheduled_at')->nullable()->after('status')->index();
            }
            if (!Schema::hasColumn('whatsapp_messages', 'sent_at')) {
                $table->dateTime('sent_at')->nullable()->after('scheduled_at')->index();
            }
            // performans için önerilen indeksler
            $table->index(['appointment_id', 'type']);
            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_messages', 'scheduled_at')) $table->dropColumn('scheduled_at');
            if (Schema::hasColumn('whatsapp_messages', 'sent_at')) $table->dropColumn('sent_at');
        });
    }
};
