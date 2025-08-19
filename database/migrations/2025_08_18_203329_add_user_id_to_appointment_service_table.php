<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 // Migration dosyası içeriği
public function up()
{
    Schema::table('appointment_service', function (Blueprint $table) {
        $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
    });
}

public function down()
{
    Schema::table('appointment_service', function (Blueprint $table) {
        $table->dropForeign(['user_id']);
        $table->dropColumn('user_id');
    });
}
};
