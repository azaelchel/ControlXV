<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('own_contributions', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('contributed_on')->nullable();
            $table->string('concept')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('own_contributions');
    }
};
