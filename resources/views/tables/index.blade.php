@extends('layouts.app')

@section('title', 'Mesas — Consulta')
@section('heading', 'Mesas · Consulta')
@section('subheading', 'Cómo va quedando el acomodo. Busca a una persona o grupo para ver en qué mesa quedó.')

@section('content')
<style>
    .planner-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 18px; }
    .tviz { border: 1px solid #e7dcf3; border-radius: 18px; padding: 16px; background: #fff; box-shadow: 0 10px 30px rgba(122,79,168,.06); }
    .tviz.principal { border-color: #c693ea; background: linear-gradient(180deg,#fbf4ff, #fff); }
    .tviz-head { display: flex; justify-content: space-between; align-items: start; gap: 8px; margin-bottom: 12px; }
    .tviz-name { font-weight: 800; color: #43275b; font-size: 16px; }
    .tviz-sub { font-size: 11px; color: #8a72a4; text-transform: uppercase; letter-spacing: .06em; }
    .tviz-top {
        margin: 6px auto 14px; display: grid; place-items: center; text-align: center;
        width: 130px; height: 84px; color: #fff; font-weight: 800;
        background: linear-gradient(135deg, #b07fd8, #8a55be);
        border-radius: 16px;
    }
    .tviz-top.round { width: 110px; height: 110px; border-radius: 50%; }
    .tviz-top.square { width: 100px; height: 100px; border-radius: 14px; }
    .tviz-top small { display: block; font-weight: 600; font-size: 11px; opacity: .9; }
    .seats { display: flex; flex-wrap: wrap; gap: 5px; justify-content: center; margin-bottom: 12px; }
    .seat { width: 14px; height: 14px; border-radius: 50%; }
    .seat.filled { background: #8a55be; }
    .seat.empty { background: #fff; border: 1.5px dashed #d3bfe8; }
    .fill-ok .tviz-top { background: linear-gradient(135deg,#7bc59a,#3f9e6b); }
    .fill-mid .tviz-top { background: linear-gradient(135deg,#e6b364,#cf8f3f); }
    .fill-full .tviz-top { background: linear-gradient(135deg,#b07fd8,#8a55be); }
    .names { font-size: 12px; color: #5f4c70; }
    .names .grp { color: #9b8ab0; }
    .pbar { height: 12px; border-radius: 999px; background: #efe5f7; overflow: hidden; }
    .pbar > span { display: block; height: 100%; background: linear-gradient(90deg,#b07fd8,#8a55be); }
</style>

    {{-- Resumen + progreso --}}
    <div class="card" style="margin-bottom: 18px;">
        <div class="inline" style="justify-content: space-between; flex-wrap: wrap; gap: 14px;">
            <div>
                <div class="kicker" style="color:#9b67c8; font-weight:800; font-size:12px; letter-spacing:.12em;">PROGRESO DEL ACOMODO</div>
                <div style="font-size: 26px; font-weight: 800; color:#43275b;">
                    {{ $progress['seated'] }} / {{ $progress['eligible'] }}
                    <span style="font-size:15px; color:#8a72a4;">personas sentadas ({{ $progress['percent'] }}%)</span>
                </div>
            </div>
            <div class="inline" style="gap: 8px;">
                <a class="btn" href="{{ route('tables.manage') }}">🪑 Ir a asignaciones</a>
                <a class="btn secondary" href="{{ route('tables.print') }}" target="_blank">📄 Descargar PDF</a>
            </div>
        </div>
        <div class="pbar" style="margin-top: 14px;"><span style="width: {{ $progress['percent'] }}%;"></span></div>
        <div class="grid cols-4" style="margin-top: 16px;">
            <div class="metric"><div class="label">Mesas</div><div class="value">{{ $summary['tables'] }}</div></div>
            <div class="metric"><div class="label">Capacidad total</div><div class="value">{{ $summary['capacity'] }}</div></div>
            <div class="metric"><div class="label">Sentados</div><div class="value">{{ $summary['seated'] }}</div></div>
            <div class="metric"><div class="label">Sin mesa</div><div class="value">{{ $summary['unassigned'] }}</div></div>
        </div>
    </div>

    {{-- Buscador --}}
    <div class="card" style="margin-bottom: 18px;">
        <form method="get" class="inline" style="gap: 8px;">
            <input name="search" value="{{ $search }}" placeholder="Buscar persona, familia o grupo…" style="min-width: 300px;" autofocus>
            <button class="btn" type="submit">🔍 Buscar</button>
            @if ($search !== '')
                <a class="btn secondary" href="{{ route('tables.index') }}">Limpiar</a>
            @endif
        </form>
        @if ($search !== '')
            <div style="margin-top: 14px;">
                @forelse ($searchResults as $r)
                    <div class="inline" style="justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #efe5f7;">
                        <span><strong>{{ $r['name'] }}</strong> <span class="small">· {{ $r['group'] }} · {{ $r['type'] }}</span></span>
                        @if ($r['table'])
                            <span class="pill status-confirmado">🍽️ {{ $r['table'] }}</span>
                        @else
                            <span class="pill status-no-contesto">Sin mesa</span>
                        @endif
                    </div>
                @empty
                    <p class="small" style="margin: 0;">No se encontró ninguna persona o grupo confirmado con ese texto.</p>
                @endforelse
            </div>
        @endif
    </div>

    {{-- Aviso de grupos divididos --}}
    @if ($dividedGroups->isNotEmpty())
        <div class="card" style="margin-bottom: 18px; border-color: #f0d9a8; background: #fff8e9;">
            <strong>⚠️ Grupos divididos en varias mesas:</strong>
            <ul style="margin: 8px 0 0; padding-left: 18px;">
                @foreach ($dividedGroups as $group => $tableNames)
                    <li class="small"><strong>{{ $group }}</strong> → {{ implode(', ', $tableNames) }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Representación gráfica --}}
    @if ($tables->isEmpty())
        <div class="card">
            <p class="small" style="margin: 0;">Aún no hay mesas. Ve a <a href="{{ route('tables.manage') }}">asignaciones</a> para crear las primeras y empezar a acomodar.</p>
        </div>
    @else
        <div class="planner-grid">
            @foreach ($tables as $table)
                @php
                    $occupied = $table->occupiedSeats();
                    $ratio = $table->capacity > 0 ? $occupied / $table->capacity : 0;
                    $fill = $ratio >= 1 ? 'fill-full' : ($ratio >= 0.7 ? 'fill-mid' : 'fill-ok');
                    $shapeClass = match ($table->shape) {
                        'Redonda' => 'round',
                        'Cuadrada' => 'square',
                        default => '',
                    };
                @endphp
                <div class="tviz {{ $table->is_principal ? 'principal' : '' }} {{ $fill }}">
                    <div class="tviz-head">
                        <div>
                            <div class="tviz-name">{{ $table->is_principal ? '⭐ ' : '' }}{{ $table->name }}</div>
                            <div class="tviz-sub">{{ $table->table_type ?: 'Sin tipo' }} · {{ $table->shape ?: 'Sin forma' }}</div>
                        </div>
                        <span class="pill {{ $occupied >= $table->capacity ? 'status-no-asistira' : 'status-confirmado' }}">{{ $occupied }}/{{ $table->capacity }}</span>
                    </div>

                    <div class="tviz-top {{ $shapeClass }}">
                        <span>{{ $table->name }}<small>{{ $occupied }}/{{ $table->capacity }}</small></span>
                    </div>

                    {{-- sillas --}}
                    <div class="seats">
                        @for ($i = 0; $i < $table->capacity; $i++)
                            <span class="seat {{ $i < $occupied ? 'filled' : 'empty' }}"></span>
                        @endfor
                    </div>

                    {{-- nombres sentados --}}
                    <div class="names">
                        @php $seated = $table->assignments->filter(fn ($a) => $a->companion)->sortBy(fn ($a) => $a->companion->invited_group); @endphp
                        @forelse ($seated as $a)
                            <div>{{ $a->companion->name }} <span class="grp">· {{ $a->companion->invited_group }}</span></div>
                        @empty
                            <span class="grp">Mesa vacía</span>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
