<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('segment_service_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('segment_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('discount_percent', 5, 2)->default(0); // örn: 10 = %10 indirim
            $table->timestamps();
    
            $table->unique(['segment_id', 'service_id']); // her hizmet için segment bazlı tek kayıt
            $table->softDeletes();

        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('segment_service_discounts');
    }
    
};
