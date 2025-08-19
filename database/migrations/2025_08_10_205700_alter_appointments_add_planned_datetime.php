<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Add planned_at for scheduled appointment datetime
            $table->dateTime('planned_at')->after('pet_id');
            // Change existing dates to datetime and make nullable until check-in/out
            $table->dateTime('checkin_at')->nullable()->change();
            $table->dateTime('checkout_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Revert column types
            $table->date('checkin_at')->change();
            $table->date('checkout_at')->nullable()->change();
            $table->dropColumn('planned_at');
        });
    }
};
