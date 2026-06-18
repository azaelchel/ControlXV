<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_sends', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('message_sends', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
};
