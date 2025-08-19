<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 // database/migrations/[timestamp]_create_whatsapp_messages_table.php

public function up()
{
    Schema::create('whatsapp_messages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('customer_id')->constrained()->onDelete('cascade');
        $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
        $table->enum('type', [
            'appointment_created',
            'appointment_reminder',
            'checkin_confirmation',
            'checkout_confirmation',
            'manual'
        ]);
        $table->text('content');
        $table->enum('status', [
            'pending',
            'sent',
            'delivered',
            'read',
            'failed'
        ])->default('pending');
        $table->json('metadata')->nullable();
        $table->timestamp('scheduled_at')->nullable();
        $table->timestamp('sent_at')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
