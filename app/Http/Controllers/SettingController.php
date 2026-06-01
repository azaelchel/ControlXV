<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = Setting::orderBy('group')->orderBy('position')->orderBy('id')->get();
        $groups   = $settings->groupBy('group');

        $groupLabels = [
            'evento'  => 'Datos del evento',
            'enlaces' => 'Enlaces y vigencia',
            'general' => 'General',
        ];

        return view('settings.index', compact('groups', 'groupLabels'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'settings'   => ['required', 'array'],
            'settings.*' => ['nullable', 'string'],
        ]);

        foreach ($data['settings'] as $key => $value) {
            Setting::where('key', $key)->update(['value' => $value]);
        }

        Setting::forgetCache();

        return redirect()
            ->route('settings.index')
            ->with('status', 'Configuración guardada.');
    }
}
