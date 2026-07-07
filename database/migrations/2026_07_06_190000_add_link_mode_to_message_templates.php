<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            // Fuerza el modo del link para esta plantilla (ej. 'last_chance').
            // null = se deriva del estatus del invitado (comportamiento normal).
            $table->string('link_mode')->nullable()->after('includes_link');
        });
    }

    public function down(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->dropColumn('link_mode');
        });
    }
};
