<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpensePayment;
use App\Models\Guest;
use App\Models\OwnContribution;
use App\Models\SponsorContribution;
use App\Models\SponsorSupport;
use App\Support\CatalogOptions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    /** Totales del panorama (sumas directas; el global scope ya filtra activos). */
    private function totals(): array
    {
        $cost = (float) Expense::sum('total_amount');
        $paid = (float) ExpensePayment::sum('amount');
        $own = (float) OwnContribution::sum('amount');
        $pledged = (float) SponsorSupport::sum('pledged_amount');
        $given = (float) SponsorContribution::sum('amount');

        $gathered = $own + $given; // dinero realmente reunido (propio + recibido de padrinos)

        return [
            'cost' => $cost,
            'paid' => $paid,
            'to_pay' => max(0, $cost - $paid),
            'paid_percent' => $cost > 0 ? (int) round(($paid / $cost) * 100) : 0,

            'own' => $own,
            'pledged' => $pledged,
            'given' => $given,
            'pledge_remaining' => max(0, $pledged - $given),
            'given_percent' => $pledged > 0 ? (int) round(($given / $pledged) * 100) : 0,

            'gathered' => $gathered,
            'gathered_percent' => $cost > 0 ? (int) round(($gathered / $cost) * 100) : 0,
            'to_gather' => max(0, $cost - $gathered),
            'balance' => $gathered - $paid, // saldo en mano (reunido menos pagado)
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

    public function own(): View
    {
        $contributions = OwnContribution::query()
            ->orderByDesc('contributed_on')
            ->orderByDesc('id')
            ->get();

        return view('finances.own', [
            'contributions' => $contributions,
            'totals' => $this->totals(),
        ]);
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

    // ---------- Mis aportaciones (dinero propio) ----------

    public function storeOwn(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'contributed_on' => ['nullable', 'date'],
            'concept' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        OwnContribution::create([
            'amount' => $data['amount'],
            'contributed_on' => $data['contributed_on'] ?? now()->toDateString(),
            'concept' => $data['concept'] ?? null,
            'notes' => $data['notes'] ?? null,
            'active' => true,
        ]);

        return back()->with('status', 'Aportación registrada.');
    }

    public function destroyOwn(OwnContribution $ownContribution): RedirectResponse
    {
        $ownContribution->update(['active' => false]);

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
