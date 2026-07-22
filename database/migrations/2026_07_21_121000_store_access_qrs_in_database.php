<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("guests", function (Blueprint $table) {
            if (! Schema::hasColumn("guests", "access_qr_mime")) {
                $table->string("access_qr_mime")->nullable()->after("notes");
            }

            if (! Schema::hasColumn("guests", "access_qr_data")) {
                $table->longText("access_qr_data")->nullable()->after("access_qr_mime");
            }
        });

        if (Schema::hasColumn("guests", "access_qr_path")) {
            Schema::table("guests", function (Blueprint $table) {
                $table->dropColumn("access_qr_path");
            });
        }
    }

    public function down(): void
    {
        Schema::table("guests", function (Blueprint $table) {
            if (! Schema::hasColumn("guests", "access_qr_path")) {
                $table->string("access_qr_path")->nullable()->after("notes");
            }
        });
    }
};
