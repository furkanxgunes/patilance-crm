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
    Schema::create('customers', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Müşteri Adı Soyadı
        $table->string('email')->unique(); // E-posta (Benzersiz olmalı)
        $table->string('phone')->nullable(); // Telefon Numarası
        $table->text('address')->nullable(); // Adres Bilgisi
        $table->text('notes')->nullable(); // Müşteri hakkında özel notlar
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
