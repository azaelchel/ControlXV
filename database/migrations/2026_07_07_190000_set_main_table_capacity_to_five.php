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
                'capacity' => 5,
                'table_type' => 'Principal',
                'is_principal' => true,
                'active' => true,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('event_tables')
            ->where('name', 'MP')
            ->update([
                'capacity' => 12,
                'updated_at' => now(),
            ]);
    }
};
