<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            // Vigencia (días) del link para esta plantilla. null = usa el default
            // según el modo (last_chance → last_chance_validity_days, resto → link_validity_days).
            $table->integer('link_validity_days')->nullable()->after('link_mode');
        });
    }

    public function down(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->dropColumn('link_validity_days');
        });
    }
};
