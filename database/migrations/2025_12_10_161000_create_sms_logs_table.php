<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            
            // WhatsApp mesajı ile ilişki
            $table->unsignedBigInteger('wa_message_id')->nullable();
            $table->foreign('wa_message_id')
                  ->references('id')
                  ->on('wa_message_logs')
                  ->onDelete('set null');
            
            // SMS bilgileri
            $table->string('phone', 20);
            $table->text('message');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            
            // Yanıt ve hata bilgileri
            $table->json('response')->nullable();
            $table->string('error_message')->nullable();
            
            // Zaman damgaları
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            // Performans için indexler
            $table->index('status');
            $table->index('wa_message_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms_logs');
    }
};
