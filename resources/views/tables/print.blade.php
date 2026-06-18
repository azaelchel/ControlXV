@php
    $eventName = \App\Models\Setting::get('event_name', 'XV años de Zugeily');
    $eventDate = \App\Models\Setting::get('event_date', '');
    $eventTime = \App\Models\Setting::get('event_time', '');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Distribución de mesas — {{ $eventName }}</title>
    <style>
        @page { margin: 16mm 14mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Cormorant Garamond', Georgia, 'Times New Roman', serif;
            color: #3a2a4d; background: #fff; line-height: 1.5;
        }
        .sans { font-family: 'Helvetica Neue', Arial, sans-serif; }

        /* Portada / encabezado */
        .cover { text-align: center; padding: 18px 0 22px; border-bottom: 2px solid #c9a86a; margin-bottom: 26px; }
        .cover .mono { letter-spacing: .42em; font-size: 11px; text-transform: uppercase; color: #a98c54; font-family: 'Helvetica Neue', Arial, sans-serif; margin-bottom: 10px; }
        .cover h1 { font-size: 40px; font-weight: 600; color: #43275b; letter-spacing: .01em; line-height: 1.05; }
        .cover .flourish { color: #c9a86a; font-size: 20px; margin: 8px 0; letter-spacing: .3em; }
        .cover .when { font-size: 15px; color: #6b5a7e; font-style: italic; }
        .cover .stats { margin-top: 12px; font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 11px; color: #8a72a4; letter-spacing: .05em; }

        /* Rejilla de mesas */
        .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        .mesa { border: 1px solid #e7dcf3; border-radius: 14px; overflow: hidden; page-break-inside: avoid; background: #fff; }
        .mesa.principal { border-color: #c9a86a; box-shadow: inset 0 0 0 1px #efe0c2; }
        .mesa-head {
            background: linear-gradient(135deg, #4a2f60, #7a4fa8); color: #fff;
            padding: 10px 14px; display: flex; justify-content: space-between; align-items: baseline; gap: 8px;
        }
        .mesa.principal .mesa-head { background: linear-gradient(135deg, #6b4a86, #a07bbf); }
        .mesa-head .nm { font-size: 19px; font-weight: 600; }
        .mesa-head .cap { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 12px; font-weight: 700; background: rgba(255,255,255,.18); padding: 2px 9px; border-radius: 999px; }
        .mesa-meta { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 10px; letter-spacing: .12em; text-transform: uppercase; color: #b79bd6; padding: 6px 14px 0; }
        .mesa-notes { font-size: 12px; color: #8a72a4; font-style: italic; padding: 2px 14px 0; }
        ol.guests { list-style: none; padding: 8px 14px 14px; counter-reset: seat; }
        ol.guests li { counter-increment: seat; padding: 4px 0; border-bottom: 1px dotted #ece2f6; font-size: 14px; display: flex; gap: 8px; }
        ol.guests li:last-child { border-bottom: 0; }
        ol.guests li::before { content: counter(seat); font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 10px; color: #b79bd6; min-width: 16px; text-align: right; padding-top: 3px; }
        ol.guests .grp { color: #a594b8; font-size: 12px; font-style: italic; }
        .empty { padding: 10px 14px 14px; color: #b3a3c4; font-style: italic; font-size: 13px; }

        .sec-title { margin: 26px 0 12px; font-size: 14px; letter-spacing: .2em; text-transform: uppercase; color: #a98c54; font-family: 'Helvetica Neue', Arial, sans-serif; border-top: 1px solid #ece2f6; padding-top: 14px; }
        .unassigned { columns: 2; column-gap: 18px; font-size: 13px; }
        .unassigned div { break-inside: avoid; padding: 2px 0; }
        .unassigned .grp { color: #a594b8; font-style: italic; }

        .foot { margin-top: 26px; text-align: center; font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 10px; color: #b3a3c4; letter-spacing: .1em; }

        .toolbar { text-align: center; margin: 14px 0 22px; }
        .toolbar button { font-family: 'Helvetica Neue', Arial, sans-serif; background: #8a55be; color: #fff; border: 0; border-radius: 10px; padding: 10px 22px; font-size: 14px; cursor: pointer; }
        @media print { .toolbar { display: none; } body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()">🖨️ Imprimir o guardar como PDF</button>
    </div>

    <div class="cover">
        <div class="mono">Distribución de mesas</div>
        <h1>{{ $eventName }}</h1>
        <div class="flourish">❦</div>
        @if ($eventDate)
            <div class="when">{{ $eventDate }}@if ($eventTime) · {{ $eventTime }} @endif</div>
        @endif
        <div class="stats">{{ $summary['tables'] }} mesas &nbsp;·&nbsp; {{ $summary['seated'] }} personas sentadas &nbsp;·&nbsp; {{ $summary['unassigned'] }} sin mesa</div>
    </div>

    <div class="grid">
        @foreach ($tables as $table)
            @php $seated = $table->assignments->filter(fn ($a) => $a->companion)->sortBy(fn ($a) => $a->companion->invited_group); @endphp
            <div class="mesa {{ $table->is_principal ? 'principal' : '' }}">
                <div class="mesa-head">
                    <span class="nm">{{ $table->is_principal ? '★ ' : '' }}{{ $table->name }}</span>
                    <span class="cap">{{ $table->occupiedSeats() }}/{{ $table->capacity }}</span>
                </div>
                <div class="mesa-meta">{{ $table->table_type ?: 'Sin tipo' }} · {{ $table->shape ?: 'Sin forma' }}</div>
                @if ($table->notes)
                    <div class="mesa-notes">{{ $table->notes }}</div>
                @endif
                @if ($seated->isEmpty())
                    <div class="empty">Mesa disponible</div>
                @else
                    <ol class="guests">
                        @foreach ($seated as $a)
                            <li><span>{{ $a->companion->name }} <span class="grp">· {{ $a->companion->invited_group }}</span></span></li>
                        @endforeach
                    </ol>
                @endif
            </div>
        @endforeach
    </div>

    @if ($unassigned->isNotEmpty())
        <div class="sec-title">Invitados pendientes de mesa</div>
        <div class="unassigned">
            @foreach ($unassigned as $group => $people)
                @foreach ($people as $person)
                    <div>{{ $person->name }} <span class="grp">· {{ $group }}</span></div>
                @endforeach
            @endforeach
        </div>
    @endif

    <div class="foot">{{ $eventName }} · Generado el {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
