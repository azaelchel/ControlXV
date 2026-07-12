<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('message_templates')
            ->where('name', 'Validación final de datos')
            ->update([
                'link_mode' => 'validation',
                'link_validity_days' => 2,
                'updated_at' => now(),
            ]);

        $validationTemplateIds = DB::table('message_templates')
            ->where('name', 'Validación final de datos')
            ->pluck('id');

        if ($validationTemplateIds->isEmpty()) {
            return;
        }

        $linkIds = DB::table('message_sends')
            ->whereIn('message_template_id', $validationTemplateIds)
            ->whereNotNull('public_guest_link_id')
            ->pluck('public_guest_link_id');

        if ($linkIds->isEmpty()) {
            return;
        }

        DB::table('public_guest_links')
            ->whereIn('id', $linkIds)
            ->update([
                'mode' => 'validation',
            ]);

        $tokens = DB::table('public_guest_links')
            ->whereIn('id', $linkIds)
            ->pluck('token');

        if ($tokens->isEmpty()) {
            return;
        }

        DB::table('guests')
            ->whereIn('public_link_token', $tokens)
            ->update([
                'public_link_mode' => 'validation',
            ]);
    }

    public function down(): void
    {
        DB::table('message_templates')
            ->where('name', 'Validación final de datos')
            ->update([
                'link_mode' => null,
                'updated_at' => now(),
            ]);
    }
};
