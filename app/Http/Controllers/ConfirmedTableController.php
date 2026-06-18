<?php

namespace App\Http\Controllers;

use App\Models\Companion;
use App\Models\EventTable;
use App\Models\Guest;
use App\Models\TableAssignment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ConfirmedTableController extends Controller
{
    public const TYPES = ['Principal', 'Familiar', 'General', 'Niños', 'Jóvenes', 'Staff', 'Reservada'];

    public const SHAPES = ['Redonda', 'Rectangular', 'Cuadrada', 'Imperial'];

    public function index(Request $request): View
    {
        $data = $this->buildPlannerData($request->string('search')->toString());

        return view('tables.index', array_merge($data, [
            'types' => self::TYPES,
            'shapes' => self::SHAPES,
            'search' => $request->string('search')->toString(),
        ]));
    }

    public function print(Request $request): View
    {
        $data = $this->buildPlannerData('');

        return view('tables.print', $data);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTable($request);

        EventTable::create($validated);

        return redirect()->route('tables.index')->with('status', 'Mesa creada correctamente.');
    }

    public function update(Request $request, EventTable $eventTable): RedirectResponse
    {
        $validated = $this->validateTable($request);

        $eventTable->update($validated);

        return redirect()->route('tables.index')->with('status', 'Mesa actualizada correctamente.');
    }

    public function destroy(EventTable $eventTable): RedirectResponse
    {
        // Eliminación lógica: desactivamos la mesa y liberamos sus asignaciones.
        TableAssignment::where('event_table_id', $eventTable->id)->update(['active' => false]);
        $eventTable->update(['active' => false]);

        return redirect()->route('tables.index')
            ->with('status', "Mesa \"{$eventTable->name}\" eliminada. Sus invitados quedaron sin mesa.");
    }

    public function assign(Request $request, EventTable $eventTable): RedirectResponse
    {
        $validated = $request->validate([
            'companion_id' => ['required', 'integer'],
        ]);

        $companion = Companion::find($validated['companion_id']);

        if (! $companion) {
            return back()->with('status', 'No se encontró el invitado.');
        }

        // Si ya está sentado en esta mesa, no hay nada que hacer.
        $alreadyHere = TableAssignment::where('event_table_id', $eventTable->id)
            ->where('companion_id', $companion->id)
            ->exists();

        if ($alreadyHere) {
            return back()->with('status', "{$companion->name} ya está en esta mesa.");
        }

        if ($eventTable->availableSeats() < 1) {
            return back()->with('status', "La mesa \"{$eventTable->name}\" está llena. Aumenta la capacidad o usa otra mesa.");
        }

        $this->seatCompanion($eventTable, $companion);

        return back()->with('status', "{$companion->name} sentado en \"{$eventTable->name}\".");
    }

    public function assignGroup(Request $request, EventTable $eventTable): RedirectResponse
    {
        $validated = $request->validate([
            'group' => ['required', 'string'],
        ]);

        $assignedIds = TableAssignment::pluck('companion_id')->all();

        $pending = Companion::where('invited_group', $validated['group'])
            ->whereNotIn('id', $assignedIds)
            ->orderBy('name')
            ->get();

        if ($pending->isEmpty()) {
            return back()->with('status', 'Ese grupo ya no tiene invitados pendientes de mesa.');
        }

        $available = $eventTable->availableSeats();

        if ($available < 1) {
            return back()->with('status', "La mesa \"{$eventTable->name}\" está llena.");
        }

        $toSeat = $pending->take($available);

        foreach ($toSeat as $companion) {
            $this->seatCompanion($eventTable, $companion);
        }

        if ($pending->count() > $available) {
            $rest = $pending->count() - $available;

            return back()->with('status', "Se sentaron {$available} de {$pending->count()} de \"{$validated['group']}\". Faltan {$rest} por acomodar en otra mesa (grupo dividido).");
        }

        return back()->with('status', "Grupo \"{$validated['group']}\" sentado completo en \"{$eventTable->name}\".");
    }

    public function unassign(Request $request, EventTable $eventTable): RedirectResponse
    {
        $validated = $request->validate([
            'companion_id' => ['required', 'integer'],
        ]);

        TableAssignment::where('event_table_id', $eventTable->id)
            ->where('companion_id', $validated['companion_id'])
            ->update(['active' => false]);

        return back()->with('status', 'Invitado retirado de la mesa.');
    }

    /**
     * Sienta a un companion en una mesa garantizando una sola asignación activa.
     * (Mover = sentar en la nueva mesa; la anterior se libera automáticamente.)
     */
    private function seatCompanion(EventTable $table, Companion $companion): void
    {
        // Liberar cualquier asignación activa previa del invitado (en cualquier mesa).
        TableAssignment::where('companion_id', $companion->id)->update(['active' => false]);

        // Reactivar la fila existente para esta mesa o crear una nueva (nunca duplicar).
        $existing = TableAssignment::withoutGlobalScope('active')
            ->where('event_table_id', $table->id)
            ->where('companion_id', $companion->id)
            ->first();

        if ($existing) {
            $existing->update(['active' => true]);

            return;
        }

        TableAssignment::create([
            'event_table_id' => $table->id,
            'companion_id' => $companion->id,
            'active' => true,
        ]);
    }

    private function validateTable(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'table_type' => ['nullable', 'string', 'in:' . implode(',', self::TYPES)],
            'shape' => ['nullable', 'string', 'in:' . implode(',', self::SHAPES)],
            'is_principal' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['is_principal'] = $request->boolean('is_principal');
        $validated['table_type'] = $validated['table_type'] ?? null;
        $validated['shape'] = $validated['shape'] ?? null;
        $validated['notes'] = $validated['notes'] ?? null;

        return $validated;
    }

    /**
     * Arma todos los datos del planificador: mesas con sus invitados, invitados sin
     * mesa (solo companions activos de guests Confirmados activos), grupos divididos,
     * resumen de ocupación y resultados de búsqueda.
     */
    private function buildPlannerData(string $search): array
    {
        $tables = EventTable::query()
            ->with(['assignments.companion'])
            ->orderByDesc('is_principal')
            ->orderBy('name')
            ->get();

        // Companions elegibles: activos, de guests activos con status Confirmado.
        $confirmedNames = Guest::query()
            ->where('status', 'Confirmado')
            ->pluck('name');

        $eligible = Companion::query()
            ->whereIn('invited_group', $confirmedNames)
            ->orderBy('invited_group')
            ->orderBy('name')
            ->get();

        $assignedIds = TableAssignment::pluck('companion_id')->all();

        // Mapa companion_id => mesa (para "en qué mesa quedó" y grupos divididos).
        $companionTable = [];
        foreach ($tables as $table) {
            foreach ($table->assignments as $assignment) {
                if ($assignment->companion) {
                    $companionTable[$assignment->companion_id] = $table;
                }
            }
        }

        // Invitados sin mesa, agrupados por familia/grupo.
        $unassigned = $eligible
            ->whereNotIn('id', $assignedIds)
            ->groupBy('invited_group');

        // Detección de grupos divididos: mismo grupo repartido en >1 mesa.
        $groupTables = [];
        foreach ($tables as $table) {
            foreach ($table->assignments as $assignment) {
                $group = $assignment->companion?->invited_group;
                if ($group) {
                    $groupTables[$group][$table->id] = $table->name;
                }
            }
        }

        $dividedGroups = collect($groupTables)
            ->filter(fn (array $tablesForGroup) => count($tablesForGroup) > 1)
            ->map(fn (array $tablesForGroup) => array_values($tablesForGroup));

        // Búsqueda de persona: ¿en qué mesa quedó?
        $searchResults = collect();
        if ($search !== '') {
            $needle = mb_strtolower(trim($search));
            $searchResults = $eligible
                ->filter(fn (Companion $c) => str_contains(mb_strtolower($c->name), $needle)
                    || str_contains(mb_strtolower($c->invited_group), $needle))
                ->map(fn (Companion $c) => [
                    'name' => $c->name,
                    'group' => $c->invited_group,
                    'type' => $c->type,
                    'table' => $companionTable[$c->id]->name ?? null,
                ])
                ->values();
        }

        $totalCapacity = (int) $tables->sum('capacity');
        $totalSeated = count($assignedIds);
        $totalUnassigned = $eligible->whereNotIn('id', $assignedIds)->count();

        return [
            'tables' => $tables,
            'unassigned' => $unassigned,
            'dividedGroups' => $dividedGroups,
            'searchResults' => $searchResults,
            'summary' => [
                'tables' => $tables->count(),
                'capacity' => $totalCapacity,
                'seated' => $totalSeated,
                'unassigned' => $totalUnassigned,
            ],
        ];
    }
}
