<?php

namespace App\Console\Commands;

use App\Models\Companion;
use App\Models\ConfirmedTable;
use App\Models\Guest;
use App\Support\CatalogOptions;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportGuestsFromExcel extends Command
{
    protected $signature = 'guests:import-excel {--path=/Users/pj/Library/Mobile Documents/com~apple~CloudDocs/Invitados Zu control.xlsx}';

    protected $description = 'Importa invitados, acompañantes y mesas confirmadas desde el Excel actual';

    public function handle(): int
    {
        $path = (string) $this->option('path');

        if (! is_file($path)) {
            $this->error("No se encontró el archivo: {$path}");

            return self::FAILURE;
        }

        $spreadsheet = IOFactory::load($path);
        $guestSheet = $spreadsheet->getSheetByName('Control');
        $companionSheet = $spreadsheet->getSheetByName('Acompañantes');
        $tableSheet = $spreadsheet->getSheetByName('Mesas Confirmados');

        if (! $guestSheet) {
            $this->error('No existe la hoja Control en el archivo.');

            return self::FAILURE;
        }

        Guest::withoutGlobalScope('active')->update(['active' => false]);
        Companion::withoutGlobalScope('active')->update(['active' => false]);
        ConfirmedTable::withoutGlobalScope('active')->update(['active' => false]);

        $guestImported = 0;
        $companionImported = 0;
        $tableImported = 0;

        foreach ($guestSheet->toArray(null, false, false, false) as $index => $row) {
            if ($index === 0) {
                continue;
            }

            $group = trim((string) ($row[0] ?? ''));
            $name = trim((string) ($row[2] ?? ''));

            if ($group === '' || $name === '') {
                continue;
            }

            Guest::create([
                'group_name' => $group,
                'prefix' => $this->nullable($row[1] ?? null),
                'name' => $name,
                'category' => trim((string) ($row[3] ?? '')),
                'status' => trim((string) ($row[4] ?? '')),
                'phone' => $this->digitsOnly($row[5] ?? null),
                'adults' => (int) ($row[6] ?? 0),
                'adolescents' => (int) ($row[7] ?? 0),
                'children' => (int) ($row[8] ?? 0),
                'sponsor' => $this->nullable($row[10] ?? null),
                'whatsapp_2_months' => $this->nullable($row[11] ?? null),
                'whatsapp_1_month' => $this->nullable($row[12] ?? null),
                'whatsapp_15_days' => $this->nullable($row[13] ?? null),
                'notes' => $this->nullable($row[14] ?? null),
                'active' => true,
            ]);

            $guestImported++;
        }

        if ($companionSheet) {
            foreach ($companionSheet->toArray(null, false, false, false) as $index => $row) {
                if ($index === 0) {
                    continue;
                }

                $group = trim((string) ($row[0] ?? ''));
                $name = trim((string) ($row[1] ?? ''));

                if ($group === '' || $name === '') {
                    continue;
                }

                $isConfirmed = Guest::query()
                    ->where('name', $group)
                    ->where('status', 'Confirmado')
                    ->exists();

                if (! $isConfirmed) {
                    continue;
                }

                Companion::create([
                    'invited_group' => $group,
                    'name' => $name,
                    'type' => $this->nullable($row[2] ?? null),
                    'sex' => $this->nullable($row[3] ?? null),
                    'notes' => $this->nullable($row[4] ?? null),
                    'active' => true,
                ]);

                $companionImported++;
            }
        }

        if ($tableSheet) {
            foreach ($tableSheet->toArray(null, false, false, false) as $index => $row) {
                if ($index === 0) {
                    continue;
                }

                $tableNumber = $this->nullable($row[0] ?? null);
                $group = $this->nullable($row[1] ?? null);

                if ($tableNumber === null && $group === null) {
                    continue;
                }

                ConfirmedTable::create([
                    'table_number' => $tableNumber,
                    'guest_group' => $group,
                    'phone' => $this->digitsOnly($row[2] ?? null),
                    'total_people' => (int) ($row[3] ?? 0),
                    'assigned_seats' => (int) ($row[4] ?? 0),
                    'available_seats' => (int) ($row[5] ?? 0),
                    'notes' => $this->nullable($row[6] ?? null),
                    'active' => true,
                ]);

                $tableImported++;
            }
        }

        CatalogOptions::syncDefaults();

        $this->info("Importación completada: {$guestImported} invitados, {$companionImported} acompañantes y {$tableImported} mesas/filas confirmadas.");

        return self::SUCCESS;
    }

    private function nullable(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function digitsOnly(mixed $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits === '' ? null : $digits;
    }
}
