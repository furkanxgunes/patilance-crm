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
        Schema::table('pets', function (Blueprint $table) {
            $table->text('appearance')->nullable()->after('gender');
            $table->text('special_marks')->nullable()->after('appearance');
            $table->text('habits_toilet')->nullable()->after('special_marks');
            $table->text('vaccines')->nullable()->after('habits_toilet');
            $table->json('medications')->nullable()->after('vaccines');

            // Eski alan kaldırılıyor
            $table->dropColumn('medical_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            // Geri alma: kaldırılan alanı geri ekle, yeni alanları kaldır
            $table->dropColumn(['appearance', 'special_marks', 'habits_toilet', 'vaccines', 'medications']);
            $table->text('medical_notes')->nullable();
        });
    }
};
