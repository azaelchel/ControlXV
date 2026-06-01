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
        ];

        foreach ($settings as $data) {
            Setting::updateOrCreate(['key' => $data['key']], $data);
        }
    }
}
