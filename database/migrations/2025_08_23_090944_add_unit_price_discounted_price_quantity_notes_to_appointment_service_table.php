<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointment_service', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('discounted_price', 10, 2)->nullable();
            $table->integer('quantity')->default(1);
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('appointment_service', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'discounted_price', 'quantity', 'notes']);
        });
    }
};
