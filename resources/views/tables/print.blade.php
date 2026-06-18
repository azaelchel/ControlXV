<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Distribución de mesas</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #2f2140; margin: 24px; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .meta { color: #776582; font-size: 12px; margin-bottom: 18px; }
        .tables { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
        .table { border: 1px solid #ccc; border-radius: 8px; padding: 12px; page-break-inside: avoid; }
        .table h2 { font-size: 15px; margin: 0 0 2px; }
        .table .sub { font-size: 11px; color: #776582; margin-bottom: 8px; }
        .table ol { margin: 0; padding-left: 20px; font-size: 12px; }
        .table li { padding: 1px 0; }
        .grp { color: #8a72a4; font-size: 10px; }
        .empty { color: #999; font-size: 11px; }
        .principal { border-color: #9e68c9; }
        .unassigned { margin-top: 18px; border-top: 1px dashed #ccc; padding-top: 12px; }
        .unassigned h2 { font-size: 14px; }
        @media print { .noprint { display: none; } body { margin: 10px; } }
    </style>
</head>
<body>
    <h1>Distribución de mesas — {{ \App\Models\Setting::get('event_name', 'XV años de Zugeily') }}</h1>
    <div class="meta">
        {{ \App\Models\Setting::get('event_date', '') }} ·
        {{ $summary['tables'] }} mesas · {{ $summary['seated'] }} sentados · {{ $summary['unassigned'] }} sin mesa
    </div>

    <button class="noprint btn" onclick="window.print()" style="margin-bottom: 14px; padding: 8px 16px; cursor: pointer;">🖨️ Imprimir</button>

    <div class="tables">
        @foreach ($tables as $table)
            <div class="table {{ $table->is_principal ? 'principal' : '' }}">
                <h2>{{ $table->is_principal ? '⭐ ' : '' }}{{ $table->name }}</h2>
                <div class="sub">
                    {{ $table->table_type ?: 'Sin tipo' }} · {{ $table->shape ?: 'Sin forma' }} ·
                    {{ $table->occupiedSeats() }}/{{ $table->capacity }} lugares
                    @if ($table->notes) · {{ $table->notes }} @endif
                </div>
                @php $seated = $table->assignments->filter(fn ($a) => $a->companion)->sortBy(fn ($a) => $a->companion->invited_group); @endphp
                @if ($seated->isEmpty())
                    <div class="empty">Mesa vacía</div>
                @else
                    <ol>
                        @foreach ($seated as $a)
                            <li>{{ $a->companion->name }} <span class="grp">· {{ $a->companion->invited_group }}</span></li>
                        @endforeach
                    </ol>
                @endif
            </div>
        @endforeach
    </div>

    @if ($unassigned->isNotEmpty())
        <div class="unassigned">
            <h2>Invitados sin mesa ({{ $summary['unassigned'] }})</h2>
            <ol>
                @foreach ($unassigned as $group => $people)
                    @foreach ($people as $person)
                        <li>{{ $person->name }} <span class="grp">· {{ $group }}</span></li>
                    @endforeach
                @endforeach
            </ol>
        </div>
    @endif
</body>
</html>
