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
                'description'   => 'Para confirmados. Reconfirmación cálida + aviso de accesos QR.',
                'kicker'        => 'Reconfirmación + accesos',
                'includes_link' => true,
                'position'      => 2,
                'content'       => <<<TXT
{prefijo} {nombre}, ¡qué gusto saludarte! 🎊

Ya tenemos tu registro para los *{evento}* del *{fecha_evento}* y estamos a pocos días de vivir esta celebración juntos. 💜

Antes de cerrar la lista final, queremos confirmar contigo tus datos y asistencia para empezar a trabajar en tus *accesos personalizados*, que te enviaremos por este medio una semana antes del evento con tu *código QR* de entrada.

Solo necesitamos que revises tu enlace y validemos que todo esté correcto:

{link}

Tu enlace estará activo por *{dias_vigencia} días*. Te pedimos confirmarlo lo antes posible para asegurar tu lugar y el de tus acompañantes.

¡Gracias por ser parte de este momento tan especial! ✨
TXT,
            ],
            [
                'name'          => 'Última oportunidad (no contestaron)',
                'description'   => 'Último aviso con link para quienes no confirmaron. Da 2 días de plazo tras cerrar el registro.',
                'kicker'        => 'Última llamada',
                'includes_link' => true,
                'position'      => 3,
                'content'       => <<<TXT
Estimada/o {prefijo} {nombre}, esperamos que te encuentres muy bien. 💜

Te escribimos del equipo *{equipo}* de los *{evento}*, que se celebrarán el *{fecha_evento}*. Notamos que el registro para confirmar tu asistencia cerró el pasado *30 de junio* y aún no logramos recibir tu confirmación.

Sabemos que a veces el tiempo se nos va entre pendientes, por eso queremos darte una última oportunidad: tienes *2 días* más para confirmar a través de tu enlace personalizado 👇

{link}

Pasado este plazo ya no nos será posible incluirte en el registro final del evento. Nos encantaría contar contigo, así que te pedimos confirmar lo antes posible. 🙏

¡Gracias y esperamos verte en la celebración! ✨
TXT,
            ],
            [
                'name'          => 'Cierre para no contestaron',
                'description'   => 'Mensaje de despedida sutil para quienes no respondieron.',
                'kicker'        => 'Cierre amable',
                'includes_link' => false,
                'position'      => 4,
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
