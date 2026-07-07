<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key'         => 'event_name',
                'value'       => 'XV años de Zugeily',
                'label'       => 'Nombre del evento',
                'group'       => 'evento',
                'type'        => 'text',
                'helper_text' => 'Aparece cuando uses {evento} en una plantilla.',
                'position'    => 1,
            ],
            [
                'key'         => 'event_date',
                'value'       => '1 de agosto de 2026',
                'label'       => 'Fecha del evento (texto)',
                'group'       => 'evento',
                'type'        => 'text',
                'helper_text' => 'Reemplaza {fecha_evento}. Escríbela tal cual la quieres ver.',
                'position'    => 2,
            ],
            [
                'key'         => 'team_name',
                'value'       => 'Event Planner',
                'label'       => 'Nombre del equipo o planner',
                'group'       => 'evento',
                'type'        => 'text',
                'helper_text' => 'Reemplaza {equipo}.',
                'position'    => 3,
            ],
            [
                'key'         => 'link_validity_days',
                'value'       => '7',
                'label'       => 'Días de vigencia del link',
                'group'       => 'enlaces',
                'type'        => 'number',
                'helper_text' => 'Reemplaza {dias_vigencia} y define cuánto dura el link generado.',
                'position'    => 1,
            ],
            [
                'key'         => 'last_chance_validity_days',
                'value'       => '2',
                'label'       => 'Días de vigencia del link de última oportunidad',
                'group'       => 'enlaces',
                'type'        => 'number',
                'helper_text' => 'Vigencia del link para quienes están en estatus "No contesto" (última llamada).',
                'position'    => 2,
            ],
            [
                'key'         => 'registration_closed_date',
                'value'       => '30 de junio',
                'label'       => 'Fecha en que cerró el registro',
                'group'       => 'enlaces',
                'type'        => 'text',
                'helper_text' => 'Se muestra en el aviso de última oportunidad del portal.',
                'position'    => 3,
            ],
            [
                'key'         => 'event_time',
                'value'       => '3:30 PM',
                'label'       => 'Hora de la recepción',
                'group'       => 'evento',
                'type'        => 'text',
                'helper_text' => 'Reemplaza {hora_evento} y se muestra en el agradecimiento del portal.',
                'position'    => 4,
            ],
            [
                'key'         => 'access_info_text',
                'value'       => 'Tus accesos se enviarán por este medio 1 semana antes del evento con un código QR.',
                'label'       => 'Nota sobre accesos / QR',
                'group'       => 'evento',
                'type'        => 'text',
                'helper_text' => 'Reemplaza {accesos} y se muestra en el agradecimiento del portal.',
                'position'    => 5,
            ],
        ];

        foreach ($settings as $data) {
            Setting::updateOrCreate(['key' => $data['key']], $data);
        }
    }
}
