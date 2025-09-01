<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // önce unique'i kaldır
            $table->dropUnique(['email']);

            // sütunu kaldır
            $table->dropColumn('email');
        });

        Schema::table('customers', function (Blueprint $table) {
            // tekrar nullable + unique olarak ekle
            $table->string('email', 255)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn('email');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('email', 255)->unique();
        });
    }
};
