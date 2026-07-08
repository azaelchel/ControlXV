<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $existing = DB::table('event_tables')
            ->where('name', 'MP')
            ->first();

        if ($existing) {
            DB::table('event_tables')
                ->where('id', $existing->id)
                ->update([
                    'capacity' => min((int) ($existing->capacity ?: 12), 12),
                    'table_type' => 'Principal',
                    'shape' => 'Rectangular',
                    'is_principal' => true,
                    'position_x' => $existing->position_x ?? 49,
                    'position_y' => $existing->position_y ?? 62,
                    'active' => true,
                    'updated_at' => $now,
                ]);

            return;
        }

        DB::table('event_tables')->insert([
            'name' => 'MP',
            'capacity' => 12,
            'table_type' => 'Principal',
            'shape' => 'Rectangular',
            'is_principal' => true,
            'notes' => 'Mesa principal',
            'position_x' => 49,
            'position_y' => 62,
            'active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('event_tables')
            ->where('name', 'MP')
            ->where('notes', 'Mesa principal')
            ->update([
                'active' => false,
                'updated_at' => now(),
            ]);
    }
};
