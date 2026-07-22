<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('message_templates')->updateOrInsert(
            ['name' => 'Envío de QR de acceso V2'],
            [
                'description' => 'Para confirmados: envía una segunda propuesta visual del pase digital con QR de acceso.',
                'kicker' => 'QR de acceso V2',
                'content' => "Estimada/o {prefijo} {nombre}, esperamos que se encuentre muy bien. 💜\n\nLe compartimos su enlace personalizado para consultar su código QR de ingreso e información importante del evento:\n\n{link}\n\nNos vemos muy pronto. ✨",
                'includes_link' => true,
                'link_mode' => 'access_qr_v2',
                'link_validity_days' => 30,
                'position' => 13,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('message_templates')->where('name', 'Envío de QR de acceso V2')->delete();
    }
};
