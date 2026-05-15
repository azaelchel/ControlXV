<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SystemTransferController extends Controller
{
    public function edit(): View
    {
        return view('system-transfer.edit');
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'backup_file' => ['required', 'file', 'mimes:json,txt'],
            'with_users' => ['nullable', 'boolean'],
        ]);

        $uploadedFile = $request->file('backup_file');
        $storedPath = $uploadedFile->storeAs(
            'imports',
            'controlxv-import-'.now()->format('Ymd-His').'.json'
        );

        $arguments = [
            'path' => storage_path('app/private/'.$storedPath),
            '--force' => true,
        ];

        if ((bool) ($validated['with_users'] ?? false)) {
            $arguments['--with-users'] = true;
        }

        $exitCode = Artisan::call('controlxv:import-data', $arguments);
        $output = trim(Artisan::output());

        if ($exitCode !== 0) {
            return redirect()
                ->route('system-transfer.edit')
                ->withErrors(['backup_file' => $output !== '' ? $output : 'No se pudo importar el respaldo.']);
        }

        return redirect()
            ->route('system-transfer.edit')
            ->with('status', $output !== '' ? $output : 'Respaldo importado correctamente.');
    }
}
