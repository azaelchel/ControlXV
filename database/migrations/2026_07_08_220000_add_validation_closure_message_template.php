<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('message_templates')->updateOrInsert(
            ['name' => 'Cierre por validación no recibida'],
            [
                'description' => 'Mensaje final para confirmados que no respondieron la validación final.',
                'kicker' => 'Cierre de validación',
                'includes_link' => false,
                'link_mode' => null,
                'link_validity_days' => null,
                'position' => 11,
                'active' => true,
                'content' => <<<'TXT'
Estimada/o {prefijo} {nombre}, esperamos que se encuentre muy bien. 💜

Le escribimos del equipo *{equipo}* de los *{evento}*. En días pasados enviamos la validación final de asistencia y datos para poder cerrar la logística del evento y preparar los accesos correspondientes.

Como no recibimos la validación dentro del plazo indicado, entendemos que en esta ocasión no será posible que nos acompañen. Con mucho aprecio procederemos a liberar esos lugares para el cierre final de la lista.

Agradecemos mucho haber considerado la invitación. Será un gusto coincidir en una próxima ocasión. ¡Un abrazo y hasta pronto! ✨
TXT,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('message_templates')
            ->where('name', 'Cierre por validación no recibida')
            ->update([
                'active' => false,
                'updated_at' => now(),
            ]);
    }
};
