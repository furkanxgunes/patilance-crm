<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            // Add new text column for medications notes
            $table->text('medications_text')->nullable()->after('vaccines');
            // Drop old JSON medications column if exists
            if (Schema::hasColumn('pets', 'medications')) {
                $table->dropColumn('medications');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            // Restore JSON medications and drop text column
            if (Schema::hasColumn('pets', 'medications_text')) {
                $table->dropColumn('medications_text');
            }
            $table->json('medications')->nullable();
        });
    }
};
