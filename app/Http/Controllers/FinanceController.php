<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpensePayment;
use App\Models\Guest;
use App\Models\SponsorContribution;
use App\Models\SponsorSupport;
use App\Support\CatalogOptions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class FinanceController extends Controller
{
    /** Totales del panorama (sumas directas; el global scope ya filtra activos). */
    private function totals(): array
    {
        $cost = (float) Expense::sum('total_amount');
        $paid = (float) ExpensePayment::sum('amount');
        $pledged = (float) SponsorSupport::sum('pledged_amount');
        $given = (float) SponsorContribution::sum('amount');

        return [
            'cost' => $cost,
            'paid' => $paid,
            'to_pay' => max(0, $cost - $paid),
            'paid_percent' => $cost > 0 ? (int) round(($paid / $cost) * 100) : 0,

            'pledged' => $pledged,
            'given' => $given,
            'pledge_remaining' => max(0, $pledged - $given),
            'given_percent' => $pledged > 0 ? (int) round(($given / $pledged) * 100) : 0,

            // Lo que la familia debe cubrir aparte del apoyo comprometido de padrinos.
            'own_estimate' => max(0, $cost - $pledged),
        ];
    }

    // ---------- Vistas ----------

    public function index(): View
    {
        return view('finances.index', ['totals' => $this->totals()]);
    }

    public function expenses(): View
    {
        $expenses = Expense::query()
            ->with('payments')
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->orderBy('name')
            ->get();

        return view('finances.expenses', [
            'expenses' => $expenses,
            'categories' => CatalogOptions::values('expense_categories'),
            'paymentMethods' => CatalogOptions::values('payment_methods'),
            'totals' => $this->totals(),
        ]);
    }

    public function sponsors(): View
    {
        $supports = SponsorSupport::query()
            ->with(['guest', 'contributions'])
            ->get()
            ->sortBy(fn (SponsorSupport $s) => $s->guest?->name ?? '')
            ->values();

        $padrinoOptions = Guest::query()
            ->whereNotNull('sponsor')
            ->where('sponsor', '!=', '')
            ->orderBy('name')
            ->get(['id', 'name', 'sponsor']);

        return view('finances.sponsors', [
            'supports' => $supports,
            'padrinoOptions' => $padrinoOptions,
            'totals' => $this->totals(),
        ]);
    }

    // ---------- Exportaciones ----------

    /** Reúne todo el estado financiero para PDF/Excel. */
    private function gather(): array
    {
        return [
            'expenses' => Expense::with('payments')->orderBy('name')->get(),
            'supports' => SponsorSupport::with(['guest', 'contributions'])
                ->get()->sortBy(fn (SponsorSupport $s) => $s->guest?->name ?? '')->values(),
            'totals' => $this->totals(),
        ];
    }

    public function pdf(): Response
    {
        $data = $this->gather();

        $pdf = Pdf::loadView('finances.pdf', $data)->setPaper('a4', 'portrait');

        return $pdf->stream('estado-financiero-' . now()->format('Ymd') . '.pdf');
    }

    public function excel(): BinaryFileResponse
    {
        $data = $this->gather();
        $t = $data['totals'];

        $book = new Spreadsheet();
        $headFill = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8F55BE']];
        $headFont = ['bold' => true, 'color' => ['rgb' => 'FFFFFF']];
        $titleStyle = [
            'font' => ['bold' => true, 'size' => 15, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => $headFill,
        ];

        // ----- Hoja Resumen -----
        $r = $book->getActiveSheet();
        $r->setTitle('Resumen');
        $r->mergeCells('A1:B1');
        $r->setCellValue('A1', 'Estado financiero · XV');
        $r->getStyle('A1:B1')->applyFromArray($titleStyle);
        $rows = [
            ['Costo total', $t['cost']],
            ['Pagado a proveedores', $t['paid']],
            ['Falta pagar', $t['to_pay']],
            ['', ''],
            ['Padrinos comprometido', $t['pledged']],
            ['Padrinos recibido', $t['given']],
            ['Padrinos por recibir', $t['pledge_remaining']],
            ['', ''],
            ['Aporte propio estimado', $t['own_estimate']],
        ];
        $row = 3;
        foreach ($rows as [$label, $val]) {
            $r->setCellValue("A{$row}", $label);
            if ($label !== '') {
                $r->setCellValue("B{$row}", (float) $val);
                $r->getStyle("B{$row}")->getNumberFormat()->setFormatCode('"$"#,##0.00');
            }
            $row++;
        }
        $r->getColumnDimension('A')->setWidth(32);
        $r->getColumnDimension('B')->setWidth(18);

        // ----- Hoja Gastos -----
        $g = $book->createSheet();
        $g->setTitle('Gastos');
        $gh = ['Concepto', 'Categoría', 'Proveedor', 'Total', 'Pagado', 'Resta', 'Estado', 'Vence'];
        foreach ($gh as $i => $h) {
            $g->setCellValue(chr(65 + $i) . '1', $h);
        }
        $g->getStyle('A1:H1')->applyFromArray(['font' => $headFont, 'fill' => $headFill]);
        $gr = 2;
        foreach ($data['expenses'] as $e) {
            $g->setCellValue("A{$gr}", $e->name);
            $g->setCellValue("B{$gr}", $e->category);
            $g->setCellValue("C{$gr}", $e->provider);
            $g->setCellValue("D{$gr}", (float) $e->total_amount);
            $g->setCellValue("E{$gr}", $e->paidAmount());
            $g->setCellValue("F{$gr}", $e->remaining());
            $g->setCellValue("G{$gr}", $e->status());
            $g->setCellValue("H{$gr}", $e->due_date?->format('d/m/Y') ?? '');
            $gr++;
        }
        $g->getStyle("D2:F{$gr}")->getNumberFormat()->setFormatCode('"$"#,##0.00');
        foreach (range('A', 'H') as $c) {
            $g->getColumnDimension($c)->setAutoSize(true);
        }

        // ----- Hoja Padrinos -----
        $p = $book->createSheet();
        $p->setTitle('Padrinos');
        $ph = ['Padrino', 'Concepto', 'Comprometido', 'Dado', 'Falta', 'Estado'];
        foreach ($ph as $i => $h) {
            $p->setCellValue(chr(65 + $i) . '1', $h);
        }
        $p->getStyle('A1:F1')->applyFromArray(['font' => $headFont, 'fill' => $headFill]);
        $pr = 2;
        foreach ($data['supports'] as $s) {
            $p->setCellValue("A{$pr}", $s->guest?->name ?? '');
            $p->setCellValue("B{$pr}", $s->concept ?: ($s->guest?->sponsor ?? ''));
            $p->setCellValue("C{$pr}", (float) $s->pledged_amount);
            $p->setCellValue("D{$pr}", $s->givenAmount());
            $p->setCellValue("E{$pr}", $s->remaining());
            $p->setCellValue("F{$pr}", $s->status());
            $pr++;
        }
        $p->getStyle("C2:E{$pr}")->getNumberFormat()->setFormatCode('"$"#,##0.00');
        foreach (range('A', 'F') as $c) {
            $p->getColumnDimension($c)->setAutoSize(true);
        }

        $book->setActiveSheetIndex(0);
        $path = storage_path('app/estado-financiero-xv.xlsx');
        (new Xlsx($book))->save($path);

        return response()->download($path, 'estado-financiero-' . now()->format('Ymd') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ---------- Gastos ----------

    public function storeExpense(Request $request): RedirectResponse
    {
        Expense::create($this->validateExpense($request));

        return back()->with('status', 'Gasto registrado.');
    }

    public function updateExpense(Request $request, Expense $expense): RedirectResponse
    {
        $expense->update($this->validateExpense($request));

        return back()->with('status', 'Gasto actualizado.');
    }

    public function destroyExpense(Expense $expense): RedirectResponse
    {
        ExpensePayment::where('expense_id', $expense->id)->update(['active' => false]);
        $expense->update(['active' => false]);

        return back()->with('status', "Gasto \"{$expense->name}\" eliminado.");
    }

    // ---------- Abonos a gastos ----------

    public function storePayment(Request $request, Expense $expense): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_on' => ['nullable', 'date'],
            'method' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $expense->payments()->create([
            'amount' => $data['amount'],
            'paid_on' => $data['paid_on'] ?? now()->toDateString(),
            'method' => $data['method'] ?? null,
            'notes' => $data['notes'] ?? null,
            'active' => true,
        ]);

        return back()->with('status', 'Abono registrado.');
    }

    public function destroyPayment(ExpensePayment $expensePayment): RedirectResponse
    {
        $expensePayment->update(['active' => false]);

        return back()->with('status', 'Abono eliminado.');
    }

    // ---------- Apoyos de padrinos ----------

    public function storeSupport(Request $request): RedirectResponse
    {
        SponsorSupport::create($this->validateSupport($request));

        return back()->with('status', 'Apoyo de padrino registrado.');
    }

    public function updateSupport(Request $request, SponsorSupport $sponsorSupport): RedirectResponse
    {
        $sponsorSupport->update($this->validateSupport($request));

        return back()->with('status', 'Apoyo actualizado.');
    }

    public function destroySupport(SponsorSupport $sponsorSupport): RedirectResponse
    {
        SponsorContribution::where('sponsor_support_id', $sponsorSupport->id)->update(['active' => false]);
        $sponsorSupport->update(['active' => false]);

        return back()->with('status', 'Apoyo eliminado.');
    }

    // ---------- Aportaciones de padrinos ----------

    public function storeContribution(Request $request, SponsorSupport $sponsorSupport): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'given_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $sponsorSupport->contributions()->create([
            'amount' => $data['amount'],
            'given_on' => $data['given_on'] ?? now()->toDateString(),
            'notes' => $data['notes'] ?? null,
            'active' => true,
        ]);

        return back()->with('status', 'Aportación registrada.');
    }

    public function destroyContribution(SponsorContribution $sponsorContribution): RedirectResponse
    {
        $sponsorContribution->update(['active' => false]);

        return back()->with('status', 'Aportación eliminada.');
    }

    // ---------- Validaciones ----------

    private function validateExpense(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:80'],
            'provider' => ['nullable', 'string', 'max:120'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $data['category'] = $data['category'] ?? null;
        $data['provider'] = $data['provider'] ?? null;
        $data['due_date'] = $data['due_date'] ?? null;
        $data['notes'] = $data['notes'] ?? null;

        return $data;
    }

    private function validateSupport(Request $request): array
    {
        $data = $request->validate([
            'guest_id' => ['required', 'integer', 'exists:guests,id'],
            'concept' => ['nullable', 'string', 'max:120'],
            'pledged_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $data['concept'] = $data['concept'] ?? null;
        $data['notes'] = $data['notes'] ?? null;

        return $data;
    }
}
