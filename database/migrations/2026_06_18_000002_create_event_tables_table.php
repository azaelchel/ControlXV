<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_tables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('capacity')->default(10);
            $table->string('table_type')->nullable();
            $table->string('shape')->nullable();
            $table->boolean('is_principal')->default(false);
            $table->text('notes')->nullable();
            $table->integer('position_x')->nullable();
            $table->integer('position_y')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_tables');
    }
};
