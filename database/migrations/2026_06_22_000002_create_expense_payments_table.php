<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained('expenses')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('paid_on')->nullable();
            $table->string('method')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['expense_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_payments');
    }
};
