<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsor_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponsor_support_id')->constrained('sponsor_supports')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('given_on')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['sponsor_support_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsor_contributions');
    }
};
