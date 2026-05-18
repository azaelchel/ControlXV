<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_guest_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->string('token')->unique();
            $table->string('mode');
            $table->string('response')->nullable();
            $table->string('closed_reason')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamp('generated_at');
            $table->timestamp('expires_at');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_guest_links');
    }
};
