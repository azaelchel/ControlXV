<?php

namespace App\Http\Controllers;

use App\Models\CatalogItem;
use App\Support\CatalogOptions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        CatalogOptions::syncDefaults();

        $catalogs = [];
        $labels = CatalogOptions::labelMap();
        $activeType = (string) $request->query('type', array_key_first($labels));

        foreach ($labels as $type => $label) {
            $catalogs[] = [
                'type' => $type,
                'label' => $label,
                'items' => CatalogItem::query()
                    ->where('type', $type)
                    ->orderBy('position')
                    ->orderBy('value')
                    ->get(),
            ];
        }

        return view('catalogs.index', [
            'catalogs' => $catalogs,
            'activeType' => array_key_exists($activeType, $labels) ? $activeType : array_key_first($labels),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'max:80'],
            'value' => ['required', 'string', 'max:120'],
        ]);

        $maxPosition = (int) CatalogItem::query()->where('type', $data['type'])->max('position');
        $catalogItem = CatalogItem::withoutGlobalScope('active')
            ->firstOrNew(['type' => $data['type'], 'value' => trim($data['value'])]);

        if (! $catalogItem->exists) {
            $catalogItem->position = $maxPosition + 1;
        }

        $catalogItem->active = true;
        $catalogItem->save();

        return redirect()
            ->route('catalogs.index', ['type' => $data['type']])
            ->with('status', 'Catálogo actualizado: valor agregado.');
    }

    public function update(Request $request, CatalogItem $catalog): RedirectResponse
    {
        $data = $request->validate([
            'value' => ['required', 'string', 'max:120'],
        ]);

        $catalog->update([
            'value' => trim($data['value']),
        ]);

        return redirect()
            ->route('catalogs.index', ['type' => $catalog->type])
            ->with('status', 'Catálogo actualizado correctamente.');
    }

    public function destroy(CatalogItem $catalog): RedirectResponse
    {
        $type = $catalog->type;
        $catalog->update(['active' => false]);

        return redirect()
            ->route('catalogs.index', ['type' => $type])
            ->with('status', 'Valor desactivado del catálogo.');
    }
}
