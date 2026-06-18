@php
    $eventName = \App\Models\Setting::get('event_name', 'XV años de Zugeily');
    $eventDate = \App\Models\Setting::get('event_date', '');
    $eventTime = \App\Models\Setting::get('event_time', '');
    $rows = $tables->chunk(2);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 28px 30px 42px; }
        * { margin: 0; padding: 0; }
        body { font-family: 'DejaVu Serif', serif; color: #3a2a4d; font-size: 12px; }
        .sans { font-family: 'DejaVu Sans', sans-serif; }

        .cover { text-align: center; border-bottom: 2px solid #c9a86a; padding-bottom: 14px; margin-bottom: 20px; }
        .cover .mono { font-family: 'DejaVu Sans', sans-serif; letter-spacing: 4px; font-size: 9px; text-transform: uppercase; color: #a98c54; margin-bottom: 8px; }
        .cover h1 { font-size: 30px; font-weight: bold; color: #43275b; }
        .cover .flourish { color: #c9a86a; font-size: 15px; margin: 4px 0; }
        .cover .when { font-size: 13px; color: #6b5a7e; font-style: italic; }
        .cover .stats { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #8a72a4; letter-spacing: 1px; margin-top: 8px; }

        table.layout { width: 100%; border-collapse: separate; border-spacing: 0; }
        table.layout td { width: 50%; vertical-align: top; padding: 6px; }

        .mesa { border: 1px solid #e0d2f0; }
        .mesa.principal { border: 1px solid #c9a86a; }
        .mesa-head { background-color: #4a2f60; color: #ffffff; padding: 7px 10px; }
        .mesa.principal .mesa-head { background-color: #6b4a86; }
        .mesa-head .nm { font-size: 15px; font-weight: bold; }
        .mesa-head .cap { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #e6d7f5; }
        .mesa-meta { font-family: 'DejaVu Sans', sans-serif; font-size: 8px; letter-spacing: 1px; text-transform: uppercase; color: #9b7fbf; padding: 5px 10px 0; }
        .mesa-notes { font-size: 11px; color: #8a72a4; font-style: italic; padding: 1px 10px 0; }
        .guests { padding: 6px 10px 10px; }
        .guests .g { padding: 2px 0; border-bottom: 1px dotted #ece2f6; font-size: 12px; }
        .guests .n { color: #9b7fbf; font-family: 'DejaVu Sans', sans-serif; font-size: 9px; }
        .guests .grp { color: #a594b8; font-size: 10px; font-style: italic; }
        .empty { padding: 8px 10px 10px; color: #b3a3c4; font-style: italic; font-size: 11px; }

        .sec-title { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; color: #a98c54; border-top: 1px solid #ece2f6; padding-top: 10px; margin-top: 14px; margin-bottom: 6px; }
        .pending .p { font-size: 11px; padding: 1px 0; }
        .pending .grp { color: #a594b8; font-style: italic; }

        .foot { text-align: center; font-family: 'DejaVu Sans', sans-serif; font-size: 8px; color: #b3a3c4; letter-spacing: 1px; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="cover">
        <div class="mono">Distribución de mesas</div>
        <h1>{{ $eventName }}</h1>
        <div class="flourish">&#10086;</div>
        @if ($eventDate)
            <div class="when">{{ $eventDate }}@if ($eventTime) &middot; {{ $eventTime }} @endif</div>
        @endif
        <div class="stats">{{ $summary['tables'] }} MESAS &nbsp;&middot;&nbsp; {{ $summary['seated'] }} SENTADOS &nbsp;&middot;&nbsp; {{ $summary['unassigned'] }} SIN MESA</div>
    </div>

    <table class="layout">
        @foreach ($rows as $pair)
            <tr>
                @foreach ($pair as $table)
                    @php $seated = $table->assignments->filter(fn ($a) => $a->companion)->sortBy(fn ($a) => $a->companion->invited_group)->values(); @endphp
                    <td>
                        <div class="mesa {{ $table->is_principal ? 'principal' : '' }}">
                            <div class="mesa-head">
                                <span class="nm">{{ $table->is_principal ? '★ ' : '' }}{{ $table->name }}</span>
                                <span class="cap"> &nbsp; {{ $table->occupiedSeats() }}/{{ $table->capacity }}</span>
                            </div>
                            <div class="mesa-meta">{{ $table->table_type ?: 'Sin tipo' }} &middot; {{ $table->shape ?: 'Sin forma' }}</div>
                            @if ($table->notes)
                                <div class="mesa-notes">{{ $table->notes }}</div>
                            @endif
                            @if ($seated->isEmpty())
                                <div class="empty">Mesa disponible</div>
                            @else
                                <div class="guests">
                                    @foreach ($seated as $i => $a)
                                        <div class="g"><span class="n">{{ $i + 1 }}.</span> {{ $a->companion->name }} <span class="grp">· {{ $a->companion->invited_group }}</span></div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </td>
                @endforeach
                @if ($pair->count() === 1)
                    <td></td>
                @endif
            </tr>
        @endforeach
    </table>

    @if ($unassigned->isNotEmpty())
        <div class="sec-title">Invitados pendientes de mesa</div>
        <div class="pending">
            @foreach ($unassigned as $group => $people)
                @foreach ($people as $person)
                    <div class="p">{{ $person->name }} <span class="grp">· {{ $group }}</span></div>
                @endforeach
            @endforeach
        </div>
    @endif

    <div class="foot">{{ $eventName }} · Generado el {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
