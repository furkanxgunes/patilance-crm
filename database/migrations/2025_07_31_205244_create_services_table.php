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
    Schema::create('services', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Hizmetin Adı, örn: "Standart Tıraş"
        $table->text('description')->nullable(); // Hizmet Açıklaması (isteğe bağlı)
        $table->string('category'); // Kategori, örn: "Bakım", "Konaklama"
        $table->decimal('base_price', 8, 2); // Temel Fiyat, örn: 350.00
        $table->integer('duration_minutes')->nullable(); // Tahmini Süre (dakika cinsinden)
        $table->timestamps(); // created_at ve updated_at sütunlarını otomatik ekler
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
