@extends('layouts.app')

@section('title', 'Mesas confirmadas')
@section('heading', 'Mesas confirmadas')
@section('subheading', 'Control separado para mesas, asientos asignados y espacios disponibles')

@section('content')
    <div class="grid cols-4" style="margin-bottom: 18px;">
        <div class="card metric"><div class="label">Filas cargadas</div><div class="value">{{ $summary['records'] }}</div></div>
        <div class="card metric"><div class="label">Total personas</div><div class="value">{{ $summary['people'] }}</div></div>
        <div class="card metric"><div class="label">Asientos asignados</div><div class="value">{{ $summary['assigned'] }}</div></div>
        <div class="card metric"><div class="label">Disponibles</div><div class="value">{{ $summary['available'] }}</div></div>
    </div>

    <div class="card" style="margin-bottom: 18px;">
        <form method="get" class="form-grid">
            <div>
                <label for="search">Buscar</label>
                <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Invitado, grupo o teléfono">
            </div>
            <div>
                <label for="table">Mesa</label>
                <select id="table" name="table">
                    <option value="">Todas</option>
                    @foreach ($tableNumbers as $value)
                        <option value="{{ $value }}" @selected(($filters['table'] ?? '') === $value)>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="inline" style="align-self: end;">
                <button class="btn" type="submit">Filtrar</button>
                <a class="btn secondary" href="{{ route('tables.index') }}">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No. mesa</th>
                        <th>Invitado o grupo</th>
                        <th>Teléfono</th>
                        <th>Total personas</th>
                        <th>Asignados</th>
                        <th>Disponibles</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tables as $row)
                        <tr>
                            <td>{{ $row->table_number ?: '—' }}</td>
                            <td>{{ $row->guest_group ?: '—' }}</td>
                            <td>{{ $row->phone ?: '—' }}</td>
                            <td>{{ $row->total_people }}</td>
                            <td>{{ $row->assigned_seats }}</td>
                            <td>{{ $row->available_seats }}</td>
                            <td>{{ $row->notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">No hay mesas confirmadas cargadas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 16px;">{{ $tables->links() }}</div>
    </div>
@endsection
