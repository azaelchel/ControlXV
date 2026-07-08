<?php

namespace Database\Seeders;

use App\Models\EventTable;
use Illuminate\Database\Seeder;

class EventTableSeeder extends Seeder
{
    /**
     * 25 mesas ubicadas según el croquis del salón (La Cúpula).
     * position_x / position_y son porcentajes (0-100) sobre el plano.
     * Cada mesa: capacidad máxima de 12 personas.
     */
    public function run(): void
    {
        // [número => [x%, y%]] — bloque izquierdo, bloque derecho y mesa 25.
        $layout = [
            12 => [8, 30],  7 => [17, 30],  6 => [26, 30],  1 => [35, 30],
            11 => [8, 48],  8 => [17, 48],  5 => [26, 48],  2 => [35, 48],
            10 => [8, 66],  9 => [17, 66],  4 => [26, 66],  3 => [35, 66],

            13 => [61, 30], 18 => [68, 30], 19 => [76, 30], 24 => [84, 30],
            14 => [61, 48], 17 => [68, 48], 20 => [76, 48], 23 => [84, 48],
            15 => [61, 66], 16 => [68, 66], 21 => [76, 66], 22 => [84, 66],

            25 => [92, 48],
        ];

        foreach ($layout as $num => [$x, $y]) {
            EventTable::withoutGlobalScope('active')->updateOrCreate(
                ['name' => 'Mesa ' . $num],
                [
                    'capacity'     => 12,
                    'table_type'   => 'General',
                    'shape'        => 'Rectangular',
                    'is_principal' => false,
                    'position_x'   => $x,
                    'position_y'   => $y,
                    'active'       => true,
                ]
            );
        }

        EventTable::withoutGlobalScope('active')->updateOrCreate(
            ['name' => 'MP'],
            [
                'capacity'     => 5,
                'table_type'   => 'Principal',
                'shape'        => 'Rectangular',
                'is_principal' => true,
                'notes'        => 'Mesa principal',
                'position_x'   => 49,
                'position_y'   => 70,
                'active'       => true,
            ]
        );
    }
}
