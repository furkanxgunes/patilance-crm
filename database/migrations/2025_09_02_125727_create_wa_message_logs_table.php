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
        Schema::create('wa_message_logs', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('direction', 16)->index(); // inbound|outbound|status
            $table->string('wa_id', 32)->nullable()->index(); // alıcı/çıkarıcı wa id
            $table->string('message_id', 64)->nullable()->index();
            $table->string('type', 32)->nullable(); // text|image|status|...
            $table->text('body')->nullable();
            $table->string('status', 32)->nullable(); // sent|delivered|read|failed
            $table->json('raw')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_message_logs');
    }
};
