<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement('ALTER TABLE wa_message_logs MODIFY message_id VARCHAR(255) DEFAULT NULL');
    }
    
    public function down()
    {
        DB::statement('ALTER TABLE wa_message_logs MODIFY message_id VARCHAR(64) DEFAULT NULL');
    }
};
