<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_table_id')->constrained('event_tables')->cascadeOnDelete();
            $table->foreignId('companion_id')->constrained('companions')->cascadeOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['event_table_id', 'active']);
            $table->index(['companion_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_assignments');
    }
};
