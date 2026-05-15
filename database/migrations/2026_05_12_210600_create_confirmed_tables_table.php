<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('confirmed_tables', function (Blueprint $table) {
            $table->id();
            $table->string('table_number')->nullable();
            $table->string('guest_group')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedInteger('total_people')->default(0);
            $table->unsignedInteger('assigned_seats')->default(0);
            $table->integer('available_seats')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('confirmed_tables');
    }
};
