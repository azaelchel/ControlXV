<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->string('public_link_token')->nullable()->after('active');
            $table->string('public_link_mode')->nullable()->after('public_link_token');
            $table->string('public_link_response')->nullable()->after('public_link_mode');
            $table->timestamp('public_link_generated_at')->nullable()->after('public_link_response');
            $table->timestamp('public_link_expires_at')->nullable()->after('public_link_generated_at');
            $table->timestamp('public_link_opened_at')->nullable()->after('public_link_expires_at');
            $table->timestamp('public_link_responded_at')->nullable()->after('public_link_opened_at');
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn([
                'public_link_token',
                'public_link_mode',
                'public_link_response',
                'public_link_generated_at',
                'public_link_expires_at',
                'public_link_opened_at',
                'public_link_responded_at',
            ]);
        });
    }
};
