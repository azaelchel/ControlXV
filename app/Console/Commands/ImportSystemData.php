<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportSystemData extends Command
{
    protected $signature = 'controlxv:import-data
        {path : Ruta del JSON exportado}
        {--with-users : Importar también usuarios}
        {--force : Confirmar que se reemplazarán los datos actuales}';

    protected $description = 'Importa un respaldo JSON del sistema a la base activa.';

    public function handle(): int
    {
        if (! $this->option('force')) {
            $this->warn('Este proceso reemplaza catálogos, familias o grupos, invitados y mesas en la base actual.');
            $this->line('Vuelve a correr el comando con --force cuando estés listo.');

            return self::INVALID;
        }

        $path = (string) $this->argument('path');

        if (! is_file($path)) {
            $this->error("No existe el archivo: {$path}");

            return self::FAILURE;
        }

        $payload = json_decode((string) file_get_contents($path), true);

        if (! is_array($payload)) {
            $this->error('El archivo no contiene un JSON válido.');

            return self::FAILURE;
        }

        foreach (['catalog_items', 'guests', 'companions', 'confirmed_tables'] as $section) {
            if (! array_key_exists($section, $payload) || ! is_array($payload[$section])) {
                $this->error("Falta la sección requerida: {$section}");

                return self::FAILURE;
            }
        }

        DB::transaction(function () use ($payload) {
            $this->truncateDomainTables($this->option('with-users') && isset($payload['users']));

            $this->bulkInsert('catalog_items', $payload['catalog_items']);
            $this->bulkInsert('guests', $payload['guests']);
            $this->bulkInsert('companions', $payload['companions']);
            $this->bulkInsert('confirmed_tables', $payload['confirmed_tables']);

            if ($this->option('with-users') && isset($payload['users']) && is_array($payload['users'])) {
                $this->bulkInsert('users', $payload['users']);
            }

            $this->resetAutoIncrement('catalog_items');
            $this->resetAutoIncrement('guests');
            $this->resetAutoIncrement('companions');
            $this->resetAutoIncrement('confirmed_tables');

            if ($this->option('with-users') && isset($payload['users']) && is_array($payload['users'])) {
                $this->resetAutoIncrement('users');
            }
        });

        $this->info('Importación completada.');
        $this->table(
            ['Sección', 'Registros'],
            [
                ['Catálogos', count($payload['catalog_items'])],
                ['Familias o grupos', count($payload['guests'])],
                ['Invitados', count($payload['companions'])],
                ['Mesas confirmadas', count($payload['confirmed_tables'])],
                ['Usuarios', $this->option('with-users') ? count($payload['users'] ?? []) : 'omitidos'],
            ]
        );

        return self::SUCCESS;
    }

    private function truncateDomainTables(bool $withUsers): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            if ($withUsers) {
                DB::statement('TRUNCATE TABLE companions, confirmed_tables, guests, catalog_items, users RESTART IDENTITY CASCADE');
            } else {
                DB::statement('TRUNCATE TABLE companions, confirmed_tables, guests, catalog_items RESTART IDENTITY CASCADE');
            }

            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            DB::table('companions')->delete();
            DB::table('confirmed_tables')->delete();
            DB::table('guests')->delete();
            DB::table('catalog_items')->delete();

            if ($withUsers) {
                DB::table('users')->delete();
            }

            DB::statement('DELETE FROM sqlite_sequence WHERE name IN (\'companions\', \'confirmed_tables\', \'guests\', \'catalog_items\''.($withUsers ? ', \'users\'' : '').')');
            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        throw new RuntimeException("Driver no soportado para importación: {$driver}");
    }

    private function bulkInsert(string $table, array $rows): void
    {
        if ($rows === []) {
            return;
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }

    private function resetAutoIncrement(string $table): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE((SELECT MAX(id) FROM {$table}), 1), (SELECT COUNT(*) > 0 FROM {$table}))");
        }
    }
}
