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
    Schema::create('pets', function (Blueprint $table) {
        $table->id();
        
        // İLİŞKİ KURULUYOR: Bu pet hangi müşteriye ait?
        $table->foreignId('customer_id')->constrained()->onDelete('cascade');
        
        $table->string('name'); // Pet'in adı
        $table->string('species'); // Tür (Kedi, Köpek)
        $table->string('breed')->nullable(); // Irk (Siyam, Golden Retriever)
        $table->string('chip_no')->nullable(); // Chip No
        $table->integer('age')->nullable(); // Yaş
        $table->string('gender'); // Cinsiyet (Erkek, Dişi)
        $table->decimal('weight_kg', 5, 2)->nullable(); // Kilo (kg)
        $table->text('medical_notes')->nullable(); // Alerjiler, kronik hastalıklar vb.
        $table->text('allergies')->nullable(); 
        $table->text('veterinarian_info')->nullable(); 
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        Schema::dropIfExists('pets');
    }
};
