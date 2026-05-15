<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('notes');
        });

        Schema::table('companions', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('notes');
        });

        Schema::table('confirmed_tables', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('notes');
        });

        Schema::table('catalog_items', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn('active');
        });

        Schema::table('companions', function (Blueprint $table) {
            $table->dropColumn('active');
        });

        Schema::table('confirmed_tables', function (Blueprint $table) {
            $table->dropColumn('active');
        });

        Schema::table('catalog_items', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
};
