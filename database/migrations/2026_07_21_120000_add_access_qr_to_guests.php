<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        DB::table("message_templates")->updateOrInsert(
            ["name" => "Envío de QR de acceso"],
            [
                "description" => "Para confirmados: envía el link personalizado donde solo podrán ver su QR de acceso, ubicaciones y mesas de regalos.",
                "kicker" => "QR de acceso",
                "content" => "Estimada/o {prefijo} {nombre}, esperamos que se encuentre muy bien. 💜\n\nYa tenemos listo su acceso para los {evento}. Por favor abra su enlace personalizado para consultar su código QR de ingreso y la información importante del evento:\n\n{link}\n\nEn ese enlace también encontrará las ubicaciones de la misa y recepción, así como las mesas de regalos. Le pedimos tener su QR disponible el día del evento. ¡Nos vemos muy pronto! ✨",
                "includes_link" => true,
                "link_mode" => "access_qr",
                "link_validity_days" => 30,
                "position" => 12,
                "active" => true,
                "created_at" => now(),
                "updated_at" => now(),
            ]
        );
    }

    public function down(): void
    {
        Schema::table("guests", function (Blueprint $table) {
            $drop = array_values(array_filter(["access_qr_mime", "access_qr_data"], fn ($column) => Schema::hasColumn("guests", $column)));

            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });

        DB::table("message_templates")->where("name", "Envío de QR de acceso")->delete();
    }
};
