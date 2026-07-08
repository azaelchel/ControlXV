<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('event_tables')
            ->where('name', 'MP')
            ->update([
                'position_x' => 49,
                'position_y' => 70,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('event_tables')
            ->where('name', 'MP')
            ->update([
                'position_x' => 49,
                'position_y' => 62,
                'updated_at' => now(),
            ]);
    }
};
