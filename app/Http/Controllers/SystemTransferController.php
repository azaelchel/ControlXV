<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

class SystemTransferController extends Controller
{
    public function edit(): View
    {
        $contactCount = Guest::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->where('active', true)
            ->where('status', '!=', 'No asistirá')
            ->count();

        return view('system-transfer.edit', compact('contactCount'));
    }

    public function exportContactsCsv(): Response
    {
        $guests = Guest::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->where('active', true)
            ->where('status', '!=', 'No asistirá')
            ->orderBy('name')
            ->get(['name', 'phone']);

        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['Name', 'Phone 1 - Type', 'Phone 1 - Value', 'Group Membership']);

        foreach ($guests as $guest) {
            fputcsv($output, ['XV - '.$guest->name, 'Mobile', $guest->phone, 'XV Zugeily']);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        $filename = 'contactos-xv-zugeily-'.now()->format('Ymd').'.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
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
