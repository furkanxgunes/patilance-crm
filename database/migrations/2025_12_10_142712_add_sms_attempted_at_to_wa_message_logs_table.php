<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 public function up()
{
    Schema::table('wa_message_logs', function (Blueprint $table) {
        $table->timestamp('sms_attempted_at')->nullable()->after('status');
    });
}

public function down()
{
    Schema::table('wa_message_logs', function (Blueprint $table) {
        $table->dropColumn('sms_attempted_at');
    });
}
};
