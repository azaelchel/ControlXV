<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpensePayment;
use App\Models\Guest;
use App\Models\SponsorContribution;
use App\Models\SponsorSupport;
use App\Support\CatalogOptions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index(): View
    {
        $expenses = Expense::query()
            ->with('payments')
            ->orderByRaw('due_date is null') // las que tienen fecha primero
            ->orderBy('due_date')
            ->orderBy('name')
            ->get();

        $supports = SponsorSupport::query()
            ->with(['guest', 'contributions'])
            ->get()
            ->sortBy(fn (SponsorSupport $s) => $s->guest?->name ?? '')
            ->values();

        // Padrinos disponibles: invitados activos con padrino asignado.
        $padrinoOptions = Guest::query()
            ->whereNotNull('sponsor')
            ->where('sponsor', '!=', '')
            ->orderBy('name')
            ->get(['id', 'name', 'sponsor']);

        $totalCost = (float) $expenses->sum('total_amount');
        $totalPaid = (float) $expenses->sum(fn (Expense $e) => $e->paidAmount());
        $totalRemaining = max(0, $totalCost - $totalPaid);

        $pledged = (float) $supports->sum('pledged_amount');
        $given = (float) $supports->sum(fn (SponsorSupport $s) => $s->givenAmount());
        $pledgeRemaining = max(0, $pledged - $given);

        return view('finances.index', [
            'expenses' => $expenses,
            'supports' => $supports,
            'padrinoOptions' => $padrinoOptions,
            'categories' => CatalogOptions::values('expense_categories'),
            'totals' => [
                'cost' => $totalCost,
                'paid' => $totalPaid,
                'remaining' => $totalRemaining,
                'paid_percent' => $totalCost > 0 ? (int) round(($totalPaid / $totalCost) * 100) : 0,
                'pledged' => $pledged,
                'given' => $given,
                'pledge_remaining' => $pledgeRemaining,
                'given_percent' => $pledged > 0 ? (int) round(($given / $pledged) * 100) : 0,
                // Lo que la familia debe cubrir aparte de lo que aportan los padrinos.
                'own_estimate' => max(0, $totalCost - $pledged),
            ],
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
