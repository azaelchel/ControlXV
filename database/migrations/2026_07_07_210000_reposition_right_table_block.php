<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $positions = [
            'Mesa 13' => [61, 30],
            'Mesa 18' => [68, 30],
            'Mesa 19' => [76, 30],
            'Mesa 24' => [84, 30],
            'Mesa 14' => [61, 48],
            'Mesa 17' => [68, 48],
            'Mesa 20' => [76, 48],
            'Mesa 23' => [84, 48],
            'Mesa 15' => [61, 66],
            'Mesa 16' => [68, 66],
            'Mesa 21' => [76, 66],
            'Mesa 22' => [84, 66],
        ];

        foreach ($positions as $name => [$x, $y]) {
            DB::table('event_tables')
                ->where('name', $name)
                ->update([
                    'position_x' => $x,
                    'position_y' => $y,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        $positions = [
            'Mesa 13' => [57, 30],
            'Mesa 18' => [66, 30],
            'Mesa 19' => [75, 30],
            'Mesa 24' => [84, 30],
            'Mesa 14' => [57, 48],
            'Mesa 17' => [66, 48],
            'Mesa 20' => [75, 48],
            'Mesa 23' => [84, 48],
            'Mesa 15' => [57, 66],
            'Mesa 16' => [66, 66],
            'Mesa 21' => [75, 66],
            'Mesa 22' => [84, 66],
        ];

        foreach ($positions as $name => [$x, $y]) {
            DB::table('event_tables')
                ->where('name', $name)
                ->update([
                    'position_x' => $x,
                    'position_y' => $y,
                    'updated_at' => now(),
                ]);
        }
    }
};
