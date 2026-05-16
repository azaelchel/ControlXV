<?php

namespace App\Support;

use App\Models\Companion;
use App\Models\Guest;

class GuestCompanionSynchronizer
{
    public static function syncGuestCounts(Guest $guest): void
    {
        $counts = Companion::query()
            ->where('invited_group', $guest->name)
            ->selectRaw("SUM(CASE WHEN type = 'Adulto' THEN 1 ELSE 0 END) as adults")
            ->selectRaw("SUM(CASE WHEN type = 'Adolescente' THEN 1 ELSE 0 END) as adolescents")
            ->selectRaw("SUM(CASE WHEN type = 'Niño' THEN 1 ELSE 0 END) as children")
            ->first();

        $guest->update([
            'adults' => (int) ($counts->adults ?? 0),
            'adolescents' => (int) ($counts->adolescents ?? 0),
            'children' => (int) ($counts->children ?? 0),
        ]);
    }
}
