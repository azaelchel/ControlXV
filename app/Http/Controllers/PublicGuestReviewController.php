<?php

namespace App\Http\Controllers;

use App\Models\Companion;
use App\Models\Guest;
use App\Support\GuestCompanionSynchronizer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicGuestReviewController extends Controller
{
    public function show(Request $request, Guest $guest): View
    {
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
            'signedUpdateUrl' => \URL::signedRoute('guest-review.update', ['guest' => $guest], absolute: false),
            'signedDeclineUrl' => \URL::signedRoute('guest-review.decline', ['guest' => $guest], absolute: false),
        ]);
    }

    public function update(Request $request, Guest $guest): RedirectResponse
    {
        if ($guest->status === 'Rechazado') {
            return redirect()
                ->to(\URL::signedRoute('guest-review.show', ['guest' => $guest], absolute: false))
                ->with('status', 'Esta familia ya indicó que no podrá asistir. El formulario quedó bloqueado.');
        }

        $validated = $request->validate([
            'rows' => ['nullable', 'array'],
            'rows.*.id' => ['nullable', 'integer'],
            'rows.*.name' => ['nullable', 'string', 'max:180'],
            'rows.*.type' => ['nullable', 'string', 'in:Adulto,Adolescente,Niño'],
            'rows.*.sex' => ['nullable', 'string', 'max:60'],
            'rows.*.notes' => ['nullable', 'string', 'max:1000'],
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

        DB::transaction(function () use ($guest, $rows, $keptRows, $currentCompanions) {
            $keptIds = [];

            foreach ($keptRows as $row) {
                $payload = [
                    'invited_group' => $guest->name,
                    'name' => trim((string) $row['name']),
                    'type' => $row['type'],
                    'sex' => $row['sex'] ?: null,
                    'notes' => $row['notes'] ?: null,
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
        });

        return redirect()
            ->to(\URL::signedRoute('guest-review.show', ['guest' => $guest], absolute: false))
            ->with('status', 'La lista de invitados se actualizó correctamente. Gracias por revisar la información.');
    }

    public function decline(Request $request, Guest $guest): RedirectResponse
    {
        DB::transaction(function () use ($guest) {
            Companion::query()
                ->where('invited_group', $guest->name)
                ->update(['active' => false]);

            $guest->update([
                'status' => 'Rechazado',
                'adults' => 0,
                'adolescents' => 0,
                'children' => 0,
            ]);
        });

        return redirect()
            ->to(\URL::signedRoute('guest-review.show', ['guest' => $guest], absolute: false))
            ->with('status', 'Gracias por avisarnos. Se registró que esta familia no podrá asistir.');
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
            'notes' => $companion->notes,
            'existing' => true,
            'label' => 'Registrado',
        ])->values();

        $pendingRows = collect()
            ->merge($missingAdults > 0 ? collect(range(1, $missingAdults))->map(fn (int $index) => [
                'id' => null,
                'name' => '',
                'type' => 'Adulto',
                'sex' => '',
                'notes' => '',
                'existing' => false,
                'label' => 'Pendiente adulto '.$index,
            ]) : collect())
            ->merge($missingAdolescents > 0 ? collect(range(1, $missingAdolescents))->map(fn (int $index) => [
                'id' => null,
                'name' => '',
                'type' => 'Adolescente',
                'sex' => '',
                'notes' => '',
                'existing' => false,
                'label' => 'Pendiente adolescente '.$index,
            ]) : collect())
            ->merge($missingChildren > 0 ? collect(range(1, $missingChildren))->map(fn (int $index) => [
                'id' => null,
                'name' => '',
                'type' => 'Niño',
                'sex' => '',
                'notes' => '',
                'existing' => false,
                'label' => 'Pendiente niño '.$index,
            ]) : collect())
            ->values();

        return $rows->concat($pendingRows)->values()->all();
    }
}
