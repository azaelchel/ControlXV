<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanionRequest;
use App\Models\Companion;
use App\Models\Guest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CompanionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Companion::query()->latest('id');
        $editId = $request->integer('edit');

        if ($request->filled('group')) {
            $query->where('invited_group', $request->string('group'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('invited_group', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $editingCompanion = null;

        if ($editId > 0) {
            $editingCompanion = Companion::find($editId);
        }

        $guestProfiles = $this->buildConfirmedGuestProfiles();

        $pendingRegistrations = $guestProfiles
            ->map(function (array $profile) {
                if ($profile['missing_total'] === 0 && $profile['extra_total'] === 0) {
                    return null;
                }

                return $profile;
            })
            ->filter()
            ->sortByDesc(fn (array $row) => $row['missing_total'] + $row['extra_total'])
            ->values();

        return view('companions.index', [
            'companions' => $query->limit(500)->get(),
            'summary' => [
                'records' => (int) Companion::count(),
                'guest_groups' => (int) Companion::query()->distinct('invited_group')->count('invited_group'),
                'adults' => (int) Companion::where('type', 'Adulto')->count(),
                'adolescents' => (int) Companion::where('type', 'Adolescente')->count(),
                'children' => (int) Companion::where('type', 'Niño')->count(),
            ],
            'groups' => Companion::query()->select('invited_group')->distinct()->orderBy('invited_group')->pluck('invited_group'),
            'types' => Companion::query()->select('type')->distinct()->whereNotNull('type')->where('type', '!=', '')->orderBy('type')->pluck('type'),
            'sexes' => ['Hombre', 'Mujer'],
            'guestProfiles' => $guestProfiles,
            'guestOptions' => $guestProfiles
                ->filter(fn (array $profile) => $profile['missing_total'] > 0)
                ->keys()
                ->values(),
            'filters' => $request->only(['group', 'type', 'search']),
            'editingCompanion' => $editingCompanion,
            'pendingRegistrations' => $pendingRegistrations,
            'pendingSummary' => [
                'groups' => (int) $pendingRegistrations->count(),
                'people' => (int) $pendingRegistrations->sum('missing_total'),
                'adults' => (int) $pendingRegistrations->sum('missing_adults'),
                'adolescents' => (int) $pendingRegistrations->sum('missing_adolescents'),
                'children' => (int) $pendingRegistrations->sum('missing_children'),
                'extra_people' => (int) $pendingRegistrations->sum('extra_total'),
                'extra_adults' => (int) $pendingRegistrations->sum('extra_adults'),
                'extra_adolescents' => (int) $pendingRegistrations->sum('extra_adolescents'),
                'extra_children' => (int) $pendingRegistrations->sum('extra_children'),
            ],
        ]);
    }

    public function create(): View
    {
        $guestProfiles = $this->buildConfirmedGuestProfiles();

        return view('companions.create', [
            'companion' => new Companion(),
            'guestOptions' => $guestProfiles
                ->filter(fn (array $profile) => $profile['missing_total'] > 0)
                ->keys()
                ->values(),
            'guestProfiles' => $guestProfiles,
            'types' => ['Adulto', 'Adolescente', 'Niño'],
            'sexes' => ['Hombre', 'Mujer'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invited_group' => ['required', 'string', 'max:120'],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.name' => ['nullable', 'string', 'max:180'],
            'entries.*.type' => ['required', 'string', 'in:Adulto,Adolescente,Niño'],
            'entries.*.sex' => ['nullable', 'string', 'max:60'],
            'entries.*.notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $profile = $this->buildConfirmedGuestProfiles()->get($validated['invited_group']);

        if (! $profile) {
            return redirect()
                ->back()
                ->withErrors(['invited_group' => 'Solo puedes registrar invitados para familias o grupos confirmados.'])
                ->withInput();
        }

        $entries = collect($validated['entries'])
            ->map(fn (array $entry) => [
                'name' => trim((string) ($entry['name'] ?? '')),
                'type' => (string) $entry['type'],
                'sex' => $entry['sex'] ?? null,
                'notes' => $entry['notes'] ?? null,
            ])
            ->filter(fn (array $entry) => $entry['name'] !== '')
            ->values();

        if ($entries->isEmpty()) {
            return redirect()
                ->back()
                ->withErrors(['entries' => 'Captura al menos un invitado pendiente.'])
                ->withInput();
        }

        $submittedByType = [
            'Adulto' => (int) $entries->where('type', 'Adulto')->count(),
            'Adolescente' => (int) $entries->where('type', 'Adolescente')->count(),
            'Niño' => (int) $entries->where('type', 'Niño')->count(),
        ];

        $allowedByType = [
            'Adulto' => $profile['missing_adults'],
            'Adolescente' => $profile['missing_adolescents'],
            'Niño' => $profile['missing_children'],
        ];

        foreach ($submittedByType as $type => $count) {
            if ($count > $allowedByType[$type]) {
                return redirect()
                    ->back()
                    ->withErrors(['entries' => "Se intentaron registrar más {$type}s de los pendientes para esta familia o grupo."])
                    ->withInput();
            }
        }

        $entries->each(function (array $entry) use ($validated) {
            Companion::create([
                'invited_group' => $validated['invited_group'],
                'name' => $entry['name'],
                'type' => $entry['type'],
                'sex' => $entry['sex'],
                'notes' => $entry['notes'],
            ]);
        });

        return redirect()
            ->route('companions.index')
            ->with('status', 'Invitados creados correctamente.');
    }

    public function edit(Companion $companion): View
    {
        $guestProfiles = $this->buildConfirmedGuestProfiles();

        return view('companions.edit', [
            'companion' => $companion,
            'guestOptions' => $guestProfiles
                ->filter(fn (array $profile) => $profile['missing_total'] > 0 || $profile['group_name'] === $companion->invited_group)
                ->keys()
                ->values(),
            'guestProfiles' => $guestProfiles,
            'types' => ['Adulto', 'Adolescente', 'Niño'],
            'sexes' => ['Hombre', 'Mujer'],
        ]);
    }

    public function update(StoreCompanionRequest $request, Companion $companion): RedirectResponse
    {
        $companion->update($request->validated());

        return redirect()
            ->route('companions.index')
            ->with('status', 'Invitado actualizado correctamente.');
    }

    public function destroy(Companion $companion): RedirectResponse
    {
        $companion->update(['active' => false]);

        return redirect()
            ->route('companions.index')
            ->with('status', 'Invitado desactivado correctamente.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $query = Companion::query()->latest('id');

        if ($request->filled('group')) {
            $query->where('invited_group', $request->string('group'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('invited_group', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $companions = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Invitados');

        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'Reporte de Invitados - XV');
        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', 'Listado exportado del módulo de invitados');

        $headers = ['Familia o grupo', 'Nombre del invitado', 'Tipo', 'Sexo', 'Observaciones'];
        foreach ($headers as $index => $header) {
            $column = chr(65 + $index);
            $sheet->setCellValue("{$column}4", $header);
        }

        $row = 5;
        foreach ($companions as $companion) {
            $sheet->setCellValue("A{$row}", $companion->invited_group);
            $sheet->setCellValue("B{$row}", $companion->name);
            $sheet->setCellValue("C{$row}", $companion->type);
            $sheet->setCellValue("D{$row}", $companion->sex);
            $sheet->setCellValue("E{$row}", $companion->notes);
            $row++;
        }

        $lastDataRow = max(5, $row - 1);

        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8F55BE']],
        ]);
        $sheet->getStyle('A2:E2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 11, 'color' => ['rgb' => '5F4C70']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle("A4:E4")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '4A2F60']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEDCFB']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D8C5EA']]],
        ]);
        $sheet->getStyle("A4:E{$lastDataRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E8DCF2']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->freezePane('A5');
        $sheet->setAutoFilter("A4:E{$lastDataRow}");

        foreach (range('A', 'E') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $tempPath = storage_path('app/reporte_invitados_xv.xlsx');
        (new Xlsx($spreadsheet))->save($tempPath);

        return response()->download(
            $tempPath,
            'reporte_invitados_xv.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    private function buildConfirmedGuestProfiles()
    {
        $confirmedGuests = Guest::query()
            ->where('status', 'Confirmado')
            ->orderBy('name')
            ->get(['name', 'adults', 'adolescents', 'children']);

        $registeredByGuest = Companion::query()
            ->select('invited_group')
            ->selectRaw("SUM(CASE WHEN type = 'Adulto' THEN 1 ELSE 0 END) as adults")
            ->selectRaw("SUM(CASE WHEN type = 'Adolescente' THEN 1 ELSE 0 END) as adolescents")
            ->selectRaw("SUM(CASE WHEN type = 'Niño' THEN 1 ELSE 0 END) as children")
            ->groupBy('invited_group')
            ->get()
            ->keyBy('invited_group');

        return $confirmedGuests->mapWithKeys(function (Guest $guest) use ($registeredByGuest) {
            $registered = $registeredByGuest->get($guest->name);

            $expectedAdults = (int) $guest->adults;
            $expectedAdolescents = (int) $guest->adolescents;
            $expectedChildren = (int) $guest->children;

            $registeredAdults = (int) ($registered->adults ?? 0);
            $registeredAdolescents = (int) ($registered->adolescents ?? 0);
            $registeredChildren = (int) ($registered->children ?? 0);

            $adultDelta = $expectedAdults - $registeredAdults;
            $adolescentDelta = $expectedAdolescents - $registeredAdolescents;
            $childDelta = $expectedChildren - $registeredChildren;

            $missingAdults = max(0, $adultDelta);
            $missingAdolescents = max(0, $adolescentDelta);
            $missingChildren = max(0, $childDelta);
            $extraAdults = max(0, -$adultDelta);
            $extraAdolescents = max(0, -$adolescentDelta);
            $extraChildren = max(0, -$childDelta);

            $pendingSlots = collect()
                ->merge($missingAdults > 0 ? collect(range(1, $missingAdults))->map(fn (int $index) => [
                    'slot' => 'adulto-'.$index,
                    'type' => 'Adulto',
                    'label' => 'Adulto '.$index,
                ]) : collect())
                ->merge($missingAdolescents > 0 ? collect(range(1, $missingAdolescents))->map(fn (int $index) => [
                    'slot' => 'adolescente-'.$index,
                    'type' => 'Adolescente',
                    'label' => 'Adolescente '.$index,
                ]) : collect())
                ->merge($missingChildren > 0 ? collect(range(1, $missingChildren))->map(fn (int $index) => [
                    'slot' => 'nino-'.$index,
                    'type' => 'Niño',
                    'label' => 'Niño '.$index,
                ]) : collect())
                ->values()
                ->all();

            return [
                $guest->name => [
                    'group_name' => $guest->name,
                    'missing_total' => $missingAdults + $missingAdolescents + $missingChildren,
                    'extra_total' => $extraAdults + $extraAdolescents + $extraChildren,
                    'missing_adults' => $missingAdults,
                    'missing_adolescents' => $missingAdolescents,
                    'missing_children' => $missingChildren,
                    'extra_adults' => $extraAdults,
                    'extra_adolescents' => $extraAdolescents,
                    'extra_children' => $extraChildren,
                    'registered_total' => $registeredAdults + $registeredAdolescents + $registeredChildren,
                    'confirmed_total' => $expectedAdults + $expectedAdolescents + $expectedChildren,
                    'expected_breakdown' => implode(', ', array_filter([
                        $expectedAdults > 0 ? $expectedAdults . ' adulto' . ($expectedAdults === 1 ? '' : 's') : null,
                        $expectedAdolescents > 0 ? $expectedAdolescents . ' adolescente' . ($expectedAdolescents === 1 ? '' : 's') : null,
                        $expectedChildren > 0 ? $expectedChildren . ' niño' . ($expectedChildren === 1 ? '' : 's') : null,
                    ])),
                    'registered_breakdown' => implode(', ', array_filter([
                        $registeredAdults > 0 ? $registeredAdults . ' adulto' . ($registeredAdults === 1 ? '' : 's') : null,
                        $registeredAdolescents > 0 ? $registeredAdolescents . ' adolescente' . ($registeredAdolescents === 1 ? '' : 's') : null,
                        $registeredChildren > 0 ? $registeredChildren . ' niño' . ($registeredChildren === 1 ? '' : 's') : null,
                    ])),
                    'missing_breakdown' => implode(', ', array_filter([
                        $missingAdults > 0 ? $missingAdults . ' adulto' . ($missingAdults === 1 ? '' : 's') : null,
                        $missingAdolescents > 0 ? $missingAdolescents . ' adolescente' . ($missingAdolescents === 1 ? '' : 's') : null,
                        $missingChildren > 0 ? $missingChildren . ' niño' . ($missingChildren === 1 ? '' : 's') : null,
                    ])),
                    'extra_breakdown' => implode(', ', array_filter([
                        $extraAdults > 0 ? $extraAdults . ' adulto' . ($extraAdults === 1 ? '' : 's') : null,
                        $extraAdolescents > 0 ? $extraAdolescents . ' adolescente' . ($extraAdolescents === 1 ? '' : 's') : null,
                        $extraChildren > 0 ? $extraChildren . ' niño' . ($extraChildren === 1 ? '' : 's') : null,
                    ])),
                    'pending_slots' => $pendingSlots,
                ],
            ];
        });
    }
}
