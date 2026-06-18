<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\PublicGuestLink;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublicGuestLinkService
{
    public function activeLinkFor(Guest $guest): ?PublicGuestLink
    {
        $link = $guest->publicLinks()->where('is_current', true)->first();

        if (! $link) {
            return null;
        }

        if ($link->isExpired() || $link->closed_reason === 'cancelled' || $link->responded_at !== null) {
            return null;
        }

        return $link;
    }

    public function ensureLinkFor(Guest $guest): ?PublicGuestLink
    {
        if (! $guest->canGeneratePublicLink()) {
            return null;
        }

        if ($existing = $this->activeLinkFor($guest)) {
            return $existing;
        }

        return $this->generateLinkFor($guest);
    }

    public function generateLinkFor(Guest $guest): ?PublicGuestLink
    {
        if (! $guest->canGeneratePublicLink()) {
            return null;
        }

        return DB::transaction(function () use ($guest) {
            PublicGuestLink::query()
                ->where('guest_id', $guest->id)
                ->where('is_current', true)
                ->get()
                ->each(function (PublicGuestLink $link) {
                    $link->update([
                        'is_current' => false,
                        'closed_reason' => $link->responded_at
                            ? ($link->closed_reason ?: 'responded')
                            : ($link->isExpired() ? 'expired' : 'replaced'),
                    ]);
                });

            $days = Setting::getInt('link_validity_days', 7);

            $link = PublicGuestLink::create([
                'guest_id'     => $guest->id,
                'token'        => Str::random(48),
                'mode'         => $guest->status === 'Invitacion Enviada' ? 'invitation' : 'validation',
                'generated_at' => now(),
                'expires_at'   => now()->addDays($days),
                'is_current'   => true,
            ]);

            $guest->update([
                'public_link_token'         => $link->token,
                'public_link_mode'          => $link->mode,
                'public_link_response'      => null,
                'public_link_generated_at'  => $link->generated_at,
                'public_link_expires_at'    => $link->expires_at,
                'public_link_opened_at'     => null,
                'public_link_responded_at'  => null,
            ]);

            return $link;
        });
    }

    public function linkUrl(Guest $guest, PublicGuestLink $link): string
    {
        return route('guest-review.show', ['guest' => $guest, 'token' => $link->token], absolute: true);
    }
}
