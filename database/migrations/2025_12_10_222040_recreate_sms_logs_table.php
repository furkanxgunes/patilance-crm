<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    // Önce mevcut tabloyu sil
    Schema::dropIfExists('sms_logs');
    
    // Yeni tabloyu oluştur
    Schema::create('sms_logs', function (Blueprint $table) {
        $table->id();
        $table->string('wa_message_id', 191); // WhatsApp mesaj ID'si
        $table->string('phone');              // Telefon numarası
        $table->text('message');              // Gönderilen mesaj
        $table->string('status');             // Durum (pending, sent, failed)
        $table->text('response')->nullable(); // API yanıtı
        $table->timestamps();
        
        // Eğer foreign key kullanacaksanız:
        // $table->foreign('wa_message_id')->references('message_id')->on('wa_message_logs');
    });
}

public function down()
{
    Schema::dropIfExists('sms_logs');
}
};
