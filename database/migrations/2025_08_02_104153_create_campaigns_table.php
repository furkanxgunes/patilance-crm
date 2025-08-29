<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->boolean('is_active')->default(true);
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('discount_value', 10, 2);
            $table->integer('max_uses')->nullable()->comment('Maximum number of times this campaign can be used');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('campaign_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
            
            $table->unique(['campaign_id', 'service_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('campaign_service');
        Schema::dropIfExists('campaigns');
    }
};
