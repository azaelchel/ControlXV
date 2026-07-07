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
Estimada Familia {nombre}, ¡buenas tardes!

Te escribimos del equipo de *Event Planner* de los *XV años de Zugeily*. ¡Ya falta poco más de un mes para el *{fecha_evento}* y la emoción está al máximo! 🎊

Para que todo esté perfecto ese día, te pedimos que revises y, si es necesario, actualices la información de tu grupo en tu enlace:

{link}

El enlace estará disponible por *{dias_vigencia} días*. Te agradecemos mucho tu respuesta a la brevedad para que podamos tenerlo todo coordinado a tiempo.

¡Nos vemos el *{fecha_evento}*! ✨
TXT,
            ],
            [
                'name'          => 'Última oportunidad (no contestaron)',
                'description'   => 'Último aviso con link para quienes no confirmaron. Da 2 días de plazo tras cerrar el registro.',
                'kicker'        => 'Última llamada',
                'includes_link' => true,
                'link_mode'     => 'last_chance',
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
{prefijo} {nombre}, esperamos que se encuentre muy bien. 💜

Le escribimos del equipo *{equipo}* de los *{evento}*. La fecha límite para confirmar asistencia fue el *30 de junio* y, al no recibir su confirmación, ya no nos fue posible reservar su lugar en el registro final. Entendemos que en esta ocasión no podrá acompañarnos, y lo respetamos con todo cariño.

Fue un gusto invitarle y esperamos coincidir en una próxima ocasión. ¡Un fuerte abrazo y hasta pronto! 🙌
TXT,
            ],
            [
                'name'          => 'Validación final de datos',
                'description'   => 'Para confirmados: reconfirmación de datos (2 días) + aviso de pases QR por WhatsApp.',
                'kicker'        => 'Validación final',
                'includes_link' => true,
                'position'      => 10,
                'content'       => <<<TXT
Estimada/o {prefijo} {nombre}, esperamos que se encuentre muy bien. 💜

Sabemos que ya confirmó su asistencia a los *{evento}* y eso nos llena de alegría. Antes de cerrar la lista final, necesitamos asegurarnos de que toda su información quede *correctamente registrada*.

Por favor, entre a su enlace personalizado y verifique que los datos de todas las personas que le acompañarán estén completos y bien escritos:

{link}

Este paso es *muy importante*: con esta información ya confirmada prepararemos sus *pases personalizados con código QR*, que le haremos llegar por este mismo medio (WhatsApp) unos días antes de la fiesta. Sin esta verificación no podremos generarlos.

Le pedimos revisarlo dentro de los próximos *2 días*. ¡Gracias por ser parte de este momento tan especial! ✨
TXT,
            ],
        ];

        foreach ($templates as $data) {
            MessageTemplate::withoutGlobalScope('active')
                ->updateOrCreate(['name' => $data['name']], $data);
        }
    }
}
