<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('message_templates')
            ->where('link_mode', 'access_qr')
            ->update([
                'name' => 'Envío de QR de acceso anterior',
                'description' => 'Plantilla anterior del pase digital. Se conserva solo para histórico.',
                'active' => false,
                'updated_at' => now(),
            ]);

        DB::table('message_templates')
            ->where('link_mode', 'access_qr_v2')
            ->update([
                'name' => 'Envío de QR de acceso',
                'description' => 'Para confirmados: envía el pase digital principal con QR de acceso.',
                'kicker' => 'QR de acceso',
                'position' => 12,
                'active' => true,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('message_templates')
            ->where('link_mode', 'access_qr')
            ->update([
                'name' => 'Envío de QR de acceso',
                'description' => 'Para confirmados: envía el link personalizado donde solo podrán ver su QR de acceso, ubicaciones y mesas de regalos.',
                'active' => true,
                'updated_at' => now(),
            ]);

        DB::table('message_templates')
            ->where('link_mode', 'access_qr_v2')
            ->update([
                'name' => 'Envío de QR de acceso V2',
                'description' => 'Para confirmados: envía una segunda propuesta visual del pase digital con QR de acceso.',
                'kicker' => 'QR de acceso V2',
                'position' => 13,
                'updated_at' => now(),
            ]);
    }
};
