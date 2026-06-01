<?php

namespace Database\Seeders;

use App\Models\MessageTemplate;
use Illuminate\Database\Seeder;

class MessageTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name'          => 'Confirmación de asistencia',
                'description'   => 'Primera invitación. Para quienes aún no confirman o tienes duda.',
                'kicker'        => 'Invitación inicial',
                'includes_link' => true,
                'position'      => 1,
                'content'       => <<<TXT
Estimada/o {prefijo} {nombre}, buenas tardes.

Te escribimos del equipo *{equipo}* de los *{evento}*, que se celebrarán el próximo *{fecha_evento}*. Nos ponemos en contacto para pedirte que confirmes tu asistencia a través de tu enlace personalizado:

{link}

En él podrás registrar los datos de quienes te acompañarán ese día. El enlace estará activo por *{dias_vigencia} días*, por lo que te pedimos responderlo a la brevedad.

¡Muchas gracias y esperamos verte en la celebración!
TXT,
            ],
            [
                'name'          => 'Recordatorio faltando 1 mes',
                'description'   => 'Para confirmados. Pedir que corroboren información.',
                'kicker'        => 'Corroborar información',
                'includes_link' => true,
                'position'      => 2,
                'content'       => <<<TXT
Estimada/o {prefijo} {nombre}, ¡buenas tardes!

Te escribimos del equipo de *{equipo}* de los *{evento}*. ¡Ya falta poco más de un mes para el *{fecha_evento}* y la emoción está al máximo! 🎊

Para que todo esté perfecto ese día, te pedimos que revises y, si es necesario, actualices la información de tu grupo en tu enlace:

{link}

El enlace estará disponible por *{dias_vigencia} días*. Te agradecemos mucho tu respuesta a la brevedad para que podamos tenerlo todo coordinado a tiempo.

¡Nos vemos el *{fecha_evento}*! ✨
TXT,
            ],
            [
                'name'          => 'Cierre para no contestaron',
                'description'   => 'Mensaje de despedida sutil para quienes no respondieron.',
                'kicker'        => 'Cierre amable',
                'includes_link' => false,
                'position'      => 3,
                'content'       => <<<TXT
Estimada/o {prefijo} {nombre}, esperamos que se encuentren muy bien.

Te escribimos del equipo de *{equipo}* de los *{evento}*, que se celebrarán el *{fecha_evento}*. Sabemos que los tiempos y compromisos no siempre coinciden, y queremos agradecerles por habernos tenido en mente.

Lamentamos no poder contar con su presencia ese día, pero esperamos coincidir en una próxima celebración. ¡Muchos saludos y hasta pronto! 🙌
TXT,
            ],
        ];

        foreach ($templates as $data) {
            MessageTemplate::withoutGlobalScope('active')
                ->updateOrCreate(['name' => $data['name']], $data);
        }
    }
}
