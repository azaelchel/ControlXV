<?php

namespace App\Http\Controllers;

use App\Models\ConfirmedTable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ConfirmedTableController extends Controller
{
    public function index(Request $request): View
    {
        $query = ConfirmedTable::query()->latest('id');

        if ($request->filled('table')) {
            $query->where('table_number', $request->string('table'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('guest_group', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        return view('tables.index', [
            'tables' => $query->paginate(20)->withQueryString(),
            'summary' => [
                'records' => (int) ConfirmedTable::count(),
                'people' => (int) ConfirmedTable::sum('total_people'),
                'assigned' => (int) ConfirmedTable::sum('assigned_seats'),
                'available' => (int) ConfirmedTable::sum('available_seats'),
            ],
            'tableNumbers' => ConfirmedTable::query()
                ->select('table_number')
                ->whereNotNull('table_number')
                ->where('table_number', '!=', '')
                ->distinct()
                ->orderBy('table_number')
                ->pluck('table_number'),
            'filters' => $request->only(['table', 'search']),
        ]);
    }
}
