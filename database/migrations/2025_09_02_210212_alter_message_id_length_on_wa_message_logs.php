<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('wa_message_logs', function (Blueprint $table) {
            $table->string('message_id', 191)->change();
        });
    }
    
    public function down()
    {
        Schema::table('wa_message_logs', function (Blueprint $table) {
            $table->string('message_id', 50)->change(); // eski haline dönüş
        });
    }
};
