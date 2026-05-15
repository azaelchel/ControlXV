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
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->string('group_name');
            $table->string('prefix')->nullable();
            $table->string('name');
            $table->string('category');
            $table->string('status');
            $table->string('phone')->nullable();
            $table->unsignedSmallInteger('adults')->default(0);
            $table->unsignedSmallInteger('adolescents')->default(0);
            $table->unsignedSmallInteger('children')->default(0);
            $table->string('sponsor')->nullable();
            $table->string('whatsapp_2_months')->nullable();
            $table->string('whatsapp_1_month')->nullable();
            $table->string('whatsapp_15_days')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
