<?php

namespace App\Http\Controllers;

use App\Models\Companion;
use App\Models\Guest;
use App\Models\PublicGuestLink;
use App\Support\GuestCompanionSynchronizer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicGuestReviewController extends Controller
{
    public function show(Request $request, Guest $guest, string $token): View
    {
        $publicLink = $this->resolveLink($guest, $token);

        if (! $publicLink->isExpired() && $publicLink->responded_at === null && ! $publicLink->opened_at) {
            $publicLink->forceFill(['opened_at' => now()])->save();

            if ($guest->public_link_token === $publicLink->token) {
                $guest->forceFill(['public_link_opened_at' => $publicLink->opened_at])->save();
            }
        }

        $companions = Companion::query()
            ->where('invited_group', $guest->name)
            ->orderBy('id')
            ->get();

        $rows = $this->buildRows($guest, $companions);

        return view('public.guest-review', [
            'guest' => $guest,
            'companions' => $companions,
            'rows' => $rows,
            'types' => ['Adulto', 'Adolescente', 'Niño'],
            'sexes' => ['Hombre', 'Mujer'],
            'updateUrl' => route('guest-review.update', ['guest' => $guest, 'token' => $token], absolute: false),
            'declineUrl' => route('guest-review.decline', ['guest' => $guest, 'token' => $token], absolute: false),
            'mode' => $publicLink->mode,
            'daysRemaining' => $publicLink->daysRemaining(),
            'isExpired' => $publicLink->isExpired(),
            'isLocked' => $publicLink->isLocked(),
            'publicLink' => $publicLink,
        ]);
    }

    public function update(Request $request, Guest $guest, string $token): RedirectResponse
    {
        $publicLink = $this->resolveLink($guest, $token);

        if ($publicLink->isLocked()) {
            return redirect()
                ->to(route('guest-review.show', ['guest' => $guest, 'token' => $token], absolute: false))
                ->with('status', 'Este enlace ya no está disponible para cambios.');
        }

        $validated = $request->validate([
            'rows' => ['nullable', 'array'],
            'rows.*.id' => ['nullable', 'integer'],
            'rows.*.name' => ['nullable', 'string', 'max:180'],
            'rows.*.type' => ['nullable', 'string', 'in:Adulto,Adolescente,Niño'],
            'rows.*.sex' => ['nullable', 'string', 'max:60'],
            'rows.*.delete' => ['nullable', 'boolean'],
        ]);

        $currentCompanions = Companion::query()
            ->where('invited_group', $guest->name)
            ->get()
            ->keyBy('id');

        $rows = collect($validated['rows'] ?? []);
        $errors = [];

        $keptRows = $rows
            ->filter(function (array $row) use (&$errors, $currentCompanions) {
                $markedForDelete = filter_var($row['delete'] ?? false, FILTER_VALIDATE_BOOL);

                if ($markedForDelete) {
                    return false;
                }

                $name = trim((string) ($row['name'] ?? ''));
                $type = trim((string) ($row['type'] ?? ''));
                $rowId = isset($row['id']) ? (int) $row['id'] : null;

                if ($rowId && ! $currentCompanions->has($rowId)) {
                    $errors[] = 'Se detectó un invitado inválido para esta familia o grupo.';

                    return false;
                }

                if ($name === '' && $type === '') {
                    return false;
                }

                if ($name === '') {
                    $errors[] = 'Todos los invitados visibles deben tener nombre o eliminarse antes de guardar.';
                }

                if ($type === '') {
                    $errors[] = 'Todos los invitados visibles deben tener tipo asignado.';
                }

                return true;
            })
            ->values();

        if ($errors !== []) {
            return redirect()
                ->back()
                ->withErrors(['rows' => collect($errors)->unique()->implode(' ')])
                ->withInput();
        }

        DB::transaction(function () use ($guest, $rows, $keptRows, $currentCompanions, $publicLink) {
            $keptIds = [];

            foreach ($keptRows as $row) {
                $payload = [
                    'invited_group' => $guest->name,
                    'name' => trim((string) $row['name']),
                    'type' => $row['type'],
                    'sex' => $row['sex'] ?: null,
                    'active' => true,
                ];

                $rowId = isset($row['id']) ? (int) $row['id'] : null;

                if ($rowId && $currentCompanions->has($rowId)) {
                    $companion = $currentCompanions->get($rowId);
                    $companion->update($payload);
                    $keptIds[] = $companion->id;
                    continue;
                }

                $companion = Companion::create($payload);
                $keptIds[] = $companion->id;
            }

            $rows
                ->filter(fn (array $row) => isset($row['id']) && filter_var($row['delete'] ?? false, FILTER_VALIDATE_BOOL))
                ->each(function (array $row) use ($currentCompanions) {
                    $companion = $currentCompanions->get((int) $row['id']);

                    if ($companion) {
                        $companion->update(['active' => false]);
                    }
                });

            $currentCompanions
                ->filter(fn (Companion $companion) => ! in_array($companion->id, $keptIds, true))
                ->each(fn (Companion $companion) => $companion->update(['active' => false]));

            GuestCompanionSynchronizer::syncGuestCounts($guest);

            $publicLink->update([
                'response' => $publicLink->mode === 'invitation' ? 'confirmed' : 'validated',
                'responded_at' => now(),
                'closed_reason' => 'responded',
            ]);

            $guest->update([
                'status' => $publicLink->mode === 'invitation' ? 'Confirmado' : $guest->status,
                'public_link_response' => $publicLink->mode === 'invitation' ? 'confirmed' : 'validated',
                'public_link_responded_at' => now(),
            ]);
        });

        return redirect()
            ->to(route('guest-review.show', ['guest' => $guest, 'token' => $token], absolute: false))
            ->with('status', $publicLink->mode === 'invitation'
                ? '¡Gracias por confirmar!'
                : 'Gracias por revisar tu información.');
    }

    public function decline(Request $request, Guest $guest, string $token): RedirectResponse
    {
        $publicLink = $this->resolveLink($guest, $token);

        if ($publicLink->isLocked()) {
            return redirect()
                ->to(route('guest-review.show', ['guest' => $guest, 'token' => $token], absolute: false))
                ->with('status', 'Este enlace ya no está disponible para cambios.');
        }

        DB::transaction(function () use ($guest, $publicLink) {
            Companion::query()
                ->where('invited_group', $guest->name)
                ->update(['active' => false]);

            $guest->update([
                'status' => 'No asistirá',
                'adults' => 0,
                'adolescents' => 0,
                'children' => 0,
                'public_link_response' => 'declined',
                'public_link_responded_at' => now(),
            ]);

            $publicLink->update([
                'response' => 'declined',
                'responded_at' => now(),
                'closed_reason' => 'responded',
            ]);
        });

        return redirect()
            ->to(route('guest-review.show', ['guest' => $guest, 'token' => $token], absolute: false))
            ->with('status', 'Gracias por avisarnos.');
    }

    private function buildRows(Guest $guest, $companions): array
    {
        $registeredAdults = (int) $companions->where('type', 'Adulto')->count();
        $registeredAdolescents = (int) $companions->where('type', 'Adolescente')->count();
        $registeredChildren = (int) $companions->where('type', 'Niño')->count();

        $missingAdults = max(0, (int) $guest->adults - $registeredAdults);
        $missingAdolescents = max(0, (int) $guest->adolescents - $registeredAdolescents);
        $missingChildren = max(0, (int) $guest->children - $registeredChildren);

        $rows = $companions->map(fn (Companion $companion) => [
            'id' => $companion->id,
                'name' => $companion->name,
                'type' => $companion->type,
                'sex' => $companion->sex,
                'existing' => true,
                'label' => 'Registrado',
            ])->values();

        $pendingRows = collect()
            ->merge($missingAdults > 0 ? collect(range(1, $missingAdults))->map(fn (int $index) => [
                'id' => null,
                'name' => '',
                'type' => 'Adulto',
                'sex' => '',
                'existing' => false,
                'label' => 'Pendiente adulto '.$index,
            ]) : collect())
            ->merge($missingAdolescents > 0 ? collect(range(1, $missingAdolescents))->map(fn (int $index) => [
                'id' => null,
                'name' => '',
                'type' => 'Adolescente',
                'sex' => '',
                'existing' => false,
                'label' => 'Pendiente adolescente '.$index,
            ]) : collect())
            ->merge($missingChildren > 0 ? collect(range(1, $missingChildren))->map(fn (int $index) => [
                'id' => null,
                'name' => '',
                'type' => 'Niño',
                'sex' => '',
                'existing' => false,
                'label' => 'Pendiente niño '.$index,
            ]) : collect())
            ->values();

        return $rows->concat($pendingRows)->values()->all();
    }

    private function resolveLink(Guest $guest, string $token): PublicGuestLink
    {
        $publicLink = PublicGuestLink::query()
            ->where('guest_id', $guest->id)
            ->where('token', $token)
            ->first();

        abort_unless($publicLink !== null, 403, 'Invalid link token.');

        return $publicLink;
    }
}
