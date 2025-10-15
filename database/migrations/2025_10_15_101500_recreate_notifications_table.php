<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Yeni geçici bir tablo oluştur
        Schema::create('notifications_temp', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
        
        // Eğer veri taşımak isterseniz, burada verileri kopyalayabilirsiniz
        // Ancak UUID dönüşümü gerekecektir
        
        // Eski tabloyu sil
        Schema::dropIfExists('notifications');
        
        // Yeni tabloyu asıl ismine taşı
        Schema::rename('notifications_temp', 'notifications');
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        
        // Eski yapıyı geri yükle
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }
};
