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
    Schema::create('service_breed_prices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');
        $table->foreignId('breed_id')->nullable()->constrained()->onDelete('set null');
        $table->decimal('price', 10, 2);
        $table->timestamps();

        $table->unique(['service_id', 'breed_id']); // Aynı hizmet + ırk kombinasyonu tek olsun
        $table->softDeletes();

    });
}       

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_breed_prices');
    }
};
