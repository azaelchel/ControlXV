<?php

namespace App\Console\Commands;

use App\Models\CatalogItem;
use App\Models\Companion;
use App\Models\ConfirmedTable;
use App\Models\Guest;
use App\Models\User;
use Illuminate\Console\Command;

class ExportSystemData extends Command
{
    protected $signature = 'controlxv:export-data
        {--path= : Ruta destino del JSON}
        {--with-users : Incluir usuarios del sistema}';

    protected $description = 'Exporta la base del sistema a un JSON portable para migrar entre entornos.';

    public function handle(): int
    {
        $path = $this->option('path')
            ? (string) $this->option('path')
            : storage_path('app/exports/controlxv-export-'.now()->format('Ymd-His').'.json');

        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $payload = [
            'meta' => [
                'app' => config('app.name'),
                'exported_at' => now()->toIso8601String(),
                'with_users' => (bool) $this->option('with-users'),
            ],
            'catalog_items' => CatalogItem::withoutGlobalScope('active')
                ->orderBy('type')
                ->orderBy('position')
                ->orderBy('id')
                ->get()
                ->map(fn (CatalogItem $item) => $item->getAttributes())
                ->values(),
            'guests' => Guest::withoutGlobalScope('active')
                ->orderBy('id')
                ->get()
                ->map(fn (Guest $guest) => $guest->getAttributes())
                ->values(),
            'companions' => Companion::withoutGlobalScope('active')
                ->orderBy('id')
                ->get()
                ->map(fn (Companion $companion) => $companion->getAttributes())
                ->values(),
            'confirmed_tables' => ConfirmedTable::withoutGlobalScope('active')
                ->orderBy('id')
                ->get()
                ->map(fn (ConfirmedTable $table) => $table->getAttributes())
                ->values(),
        ];

        if ($this->option('with-users')) {
            $payload['users'] = User::query()
                ->orderBy('id')
                ->get()
                ->map(fn (User $user) => $user->getAttributes())
                ->values();
        }

        file_put_contents(
            $path,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $this->info('Exportación lista.');
        $this->line("Archivo: {$path}");
        $this->table(
            ['Sección', 'Registros'],
            [
                ['Catálogos', count($payload['catalog_items'])],
                ['Familias o grupos', count($payload['guests'])],
                ['Invitados', count($payload['companions'])],
                ['Mesas confirmadas', count($payload['confirmed_tables'])],
                ['Usuarios', count($payload['users'] ?? [])],
            ]
        );

        return self::SUCCESS;
    }
}
