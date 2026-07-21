<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuestRequest;
use App\Models\Guest;
use App\Models\Companion;
use App\Models\TableAssignment;
use App\Models\PublicGuestLink;
use App\Services\PublicGuestLinkService;
use App\Support\CatalogOptions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GuestController extends Controller
{
    public function index(Request $request): View
    {
        $query = Guest::query()->latest('id');
        $editId = $request->integer('edit');
        $this->applyFilters($query, $request);

        $guests = $query
            ->with(['publicLinks' => fn ($links) => $links->latest('generated_at')])
            ->limit(500)
            ->get();

        $summary = [
            'records' => Guest::count(),
            'adults' => (int) Guest::sum('adults'),
            'adolescents' => (int) Guest::sum('adolescents'),
            'children' => (int) Guest::sum('children'),
            'total_people' => (int) Guest::selectRaw('COALESCE(SUM(adults + adolescents + children), 0) as total')->value('total'),
        ];

        $byGroup = Guest::query()
            ->select('group_name')
            ->selectRaw('COUNT(*) as records')
            ->selectRaw('SUM(adults) as adults')
            ->selectRaw('SUM(adolescents) as adolescents')
            ->selectRaw('SUM(children) as children')
            ->selectRaw('SUM(adults + adolescents + children) as total_people')
            ->groupBy('group_name')
            ->orderBy('group_name')
            ->get();

        $byStatus = Guest::query()
            ->select('status')
            ->selectRaw('COUNT(*) as records')
            ->selectRaw('SUM(adults) as adults')
            ->selectRaw('SUM(adolescents) as adolescents')
            ->selectRaw('SUM(children) as children')
            ->selectRaw('SUM(adults + adolescents + children) as total_people')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $editingGuest = null;

        if ($editId > 0) {
            $editingGuest = Guest::find($editId);
        }

        return view('guests.index', [
            'guests' => $guests,
            'summary' => $summary,
            'byGroup' => $byGroup,
            'byStatus' => $byStatus,
            'options' => CatalogOptions::all(),
            'categorySummary' => $this->categorySummary(),
            'statusSummary' => $this->statusSummary(),
            'filters' => $request->only(['group_name', 'category', 'status', 'search']),
            'editingGuest' => $editingGuest,
        ]);
    }

    public function create(): View
    {
        return view('guests.create', [
            'guest' => new Guest(),
            'options' => CatalogOptions::all(),
        ]);
    }

    public function store(StoreGuestRequest $request): RedirectResponse
    {
        $guest = Guest::create($request->validated());
        $this->syncCompanionsForGuest($guest);

        return redirect()
            ->to($request->input('return_to', route('guests.index')))
            ->with('status', 'Familia o grupo creada correctamente.');
    }

    public function edit(Guest $guest): View
    {
        return view('guests.edit', [
            'guest' => $guest,
            'options' => CatalogOptions::all(),
        ]);
    }

    public function update(StoreGuestRequest $request, Guest $guest): RedirectResponse
    {
        $originalName = $guest->name;
        $guest->update($request->validated());
        $this->syncCompanionsForGuest($guest, $originalName);

        return redirect()
            ->to($request->input('return_to', route('guests.index')))
            ->with('status', 'Familia o grupo actualizada correctamente.');
    }

    public function updateStatus(Request $request, Guest $guest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:Confirmado,No asistirá'],
        ]);

        $originalName = $guest->name;
        $guest->update([
            'status' => $validated['status'],
        ]);
        $this->syncCompanionsForGuest($guest, $originalName);

        return redirect()
            ->back()
            ->with('status', "Estatus de la familia o grupo actualizado a {$validated['status']}.");
    }

    public function generatePublicLink(Request $request, Guest $guest, PublicGuestLinkService $linkService): RedirectResponse
    {
        if (! $guest->canGeneratePublicLink()) {
            return redirect()
                ->to($request->input('return_to', route('guests.index')))
                ->with('status', 'Solo puedes generar links para familias con estatus Invitacion Enviada, Confirmado o No contesto.');
        }

        $publicLink = $linkService->generateLinkFor($guest);

        if (! $publicLink) {
            return redirect()
                ->to($request->input('return_to', route('guests.index')))
                ->with('status', 'No se pudo generar el link para esta familia o grupo.');
        }

        $vigencia = (int) round($publicLink->generated_at->diffInDays($publicLink->expires_at));

        return redirect()
            ->to($request->input('return_to', route('guests.index')))
            ->with('status', "Link generado correctamente. La vigencia es de {$vigencia} días.")
            ->with('generated_link_url', route('guest-review.show', ['guest' => $guest, 'token' => $publicLink->token], absolute: true))
            ->with('generated_link_guest_id', $guest->id);
    }

    public function cancelPublicLink(Request $request, Guest $guest): RedirectResponse
    {
        $currentLink = $guest->publicLinks()
            ->where('is_current', true)
            ->first();

        if (! $currentLink || $currentLink->responded_at !== null || $currentLink->isExpired()) {
            return redirect()
                ->to($request->input('return_to', route('guests.index')))
                ->with('status', 'No había un link vigente para cancelar.');
        }

        \DB::transaction(function () use ($guest, $currentLink) {
            $currentLink->update([
                'is_current' => false,
                'expires_at' => now(),
                'closed_reason' => 'cancelled',
            ]);

            $guest->update([
                'public_link_token' => $currentLink->token,
                'public_link_mode' => $currentLink->mode,
                'public_link_response' => null,
                'public_link_generated_at' => $currentLink->generated_at,
                'public_link_expires_at' => now(),
                'public_link_opened_at' => $currentLink->opened_at,
                'public_link_responded_at' => null,
            ]);
        });

        return redirect()
            ->to($request->input('return_to', route('guests.index')))
            ->with('status', 'Link cancelado correctamente.');
    }

    public function quickUpdate(Request $request, Guest $guest): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:40'],
            'status' => ['required', 'string', 'max:60'],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);

        $originalName = $guest->name;
        $guest->update([
            'category' => $validated['category'],
            'status' => $validated['status'],
            'phone' => preg_replace('/\D+/', '', (string) ($validated['phone'] ?? '')) ?: null,
        ]);
        $this->syncCompanionsForGuest($guest, $originalName);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Familia o grupo actualizada desde el listado.',
                'guest' => [
                    'id' => $guest->id,
                    'category' => $guest->category,
                    'status' => $guest->status,
                    'phone' => $guest->phone,
                ],
                'category_summary' => $this->categorySummary(),
                'status_summary' => $this->statusSummary(),
            ]);
        }

        return redirect()
            ->back()
            ->with('status', 'Familia o grupo actualizada desde el listado.');
    }

    public function destroy(Request $request, Guest $guest): RedirectResponse
    {
        $companionIds = Companion::query()
            ->where('invited_group', $guest->name)
            ->pluck('id');

        TableAssignment::whereIn('companion_id', $companionIds)->update(['active' => false]);

        Companion::query()
            ->whereIn('id', $companionIds)
            ->update(['active' => false]);
        $guest->update(['active' => false]);

        return redirect()
            ->to($request->input('return_to', route('guests.index')))
            ->with('status', 'Familia o grupo desactivada correctamente.');
    }

    public function import(): RedirectResponse
    {
        $exitCode = \Artisan::call('guests:import-excel');
        $output = trim(\Artisan::output());

        if ($exitCode !== 0) {
            return redirect()
                ->route('guests.index')
                ->with('status', 'La importación falló. Revisa la consola o el archivo de origen.');
        }

        return redirect()
            ->route('guests.index')
            ->with('status', $output !== '' ? $output : 'Importación completada.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $query = Guest::query()->latest('id');
        $this->applyFilters($query, $request);
        $guests = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Familias y grupos');

        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'Reporte de Familias o Grupos - XV');
        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A2', 'Listado exportado desde el sistema con el mismo enfoque del panel de familias o grupos');

        $headers = ['Grupo', 'Nombre', 'Categoría', 'Estatus', 'Teléfono', 'Adultos', 'Adolescentes', 'Niños', 'Total', 'Padrino'];
        $headerRow = 4;

        foreach ($headers as $index => $header) {
            $column = chr(65 + $index);
            $sheet->setCellValue("{$column}{$headerRow}", $header);
        }

        $row = 5;

        foreach ($guests as $guest) {
            $sheet->setCellValue("A{$row}", $guest->group_name);
            $sheet->setCellValue("B{$row}", $guest->display_name);
            $sheet->setCellValue("C{$row}", $guest->category);
            $sheet->setCellValue("D{$row}", $guest->status);
            $sheet->setCellValue("E{$row}", $guest->phone);
            $sheet->setCellValue("F{$row}", $guest->adults);
            $sheet->setCellValue("G{$row}", $guest->adolescents);
            $sheet->setCellValue("H{$row}", $guest->children);
            $sheet->setCellValue("I{$row}", $guest->total_people);
            $sheet->setCellValue("J{$row}", $guest->sponsor);
            $row++;
        }

        $lastDataRow = max(5, $row - 1);

        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8F55BE']],
        ]);

        $sheet->getStyle('A2:J2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 11, 'color' => ['rgb' => '5F4C70']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getStyle("A{$headerRow}:J{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '4A2F60']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEDCFB']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D8C5EA']]],
        ]);

        $sheet->getStyle("A{$headerRow}:J{$lastDataRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E8DCF2']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle("F5:I{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->freezePane('A5');
        $sheet->setAutoFilter("A{$headerRow}:J{$lastDataRow}");

        foreach (range('A', 'J') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $tempPath = storage_path('app/reporte_familias_grupos_xv.xlsx');
        (new Xlsx($spreadsheet))->save($tempPath);

        return response()->download(
            $tempPath,
            'reporte_familias_grupos_xv.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('group_name')) {
            $query->where('group_name', $request->string('group_name'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $needle = '%' . strtolower($search) . '%';
            $query->where(function ($builder) use ($needle) {
                $builder
                    ->whereRaw('LOWER(name) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(prefix) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(phone) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(group_name) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(sponsor) LIKE ?', [$needle]);
            });
        }
    }

    private function syncCompanionsForGuest(Guest $guest, ?string $originalName = null): void
    {
        $originalName ??= $guest->name;
        $groupNames = array_values(array_unique([$originalName, $guest->name]));

        if ($guest->status !== "Confirmado") {
            $companionIds = Companion::query()
                ->withoutGlobalScope("active")
                ->whereIn("invited_group", $groupNames)
                ->pluck("id");

            TableAssignment::query()
                ->whereIn("companion_id", $companionIds)
                ->update(["active" => false]);

            Companion::query()
                ->withoutGlobalScope("active")
                ->whereIn("id", $companionIds)
                ->update(["active" => false]);

            return;
        }

        Companion::query()
            ->withoutGlobalScope("active")
            ->whereIn("invited_group", $groupNames)
            ->update([
                "invited_group" => $guest->name,
                "active" => true,
            ]);
    }

    private function statusSummary(): array
    {
        $statuses = CatalogOptions::all()['statuses'];
        $counts = Guest::query()
            ->select('status')
            ->selectRaw('COALESCE(SUM(adults + adolescents + children), 0) as status_people_total')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->status => (int) $row->status_people_total]);

        return collect($statuses)
            ->mapWithKeys(fn (string $status) => [$status => (int) ($counts[$status] ?? 0)])
            ->all();
    }

    private function categorySummary(): array
    {
        $categories = CatalogOptions::all()['categories'];
        $counts = Guest::query()
            ->select('category')
            ->selectRaw('COALESCE(SUM(adults + adolescents + children), 0) as category_people_total')
            ->selectRaw("COALESCE(SUM(CASE WHEN status != 'No asistirá' THEN adults + adolescents + children ELSE 0 END), 0) as category_people_without_rejected")
            ->groupBy('category')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->category => [
                'total' => (int) $row->category_people_total,
                'without_rejected' => (int) $row->category_people_without_rejected,
            ]]);

        return collect($categories)
            ->mapWithKeys(fn (string $category) => [$category => $counts[$category] ?? [
                'total' => 0,
                'without_rejected' => 0,
            ]])
            ->all();
    }
}
