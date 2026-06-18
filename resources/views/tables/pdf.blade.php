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
        @page { margin: 30px 32px 40px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #3a2a4d; font-size: 11px; }
        .display { font-family: 'DejaVu Serif', serif; }

        /* Portada */
        .cover { text-align: center; margin-bottom: 22px; }
        .cover .mono { letter-spacing: 5px; font-size: 9px; text-transform: uppercase; color: #a98c54; margin-bottom: 6px; }
        .cover h1 { font-size: 30px; font-weight: bold; color: #43275b; }
        .cover .when { font-size: 12px; color: #6b5a7e; font-style: italic; margin-top: 4px; }
        .rule { width: 130px; margin: 10px auto 0; border-bottom: 2px solid #c9a86a; }
        .stats { margin-top: 12px; }
        .stats span { display: inline-block; padding: 0 14px; }
        .stats .n { font-family: 'DejaVu Serif', serif; font-size: 20px; font-weight: bold; color: #6b4a86; }
        .stats .l { font-size: 8px; text-transform: uppercase; letter-spacing: 1px; color: #9b8ab0; }

        /* Mesas */
        table.layout { width: 100%; border-collapse: separate; border-spacing: 0; }
        table.layout > tr > td { width: 50%; vertical-align: top; padding: 6px; }

        .mesa { border: 1px solid #e0d2f0; border-radius: 8px; }
        .mesa.principal { border: 1px solid #c9a86a; }
        .mesa-head { background-color: #4a2f60; color: #fff; padding: 8px 11px; border-radius: 7px 7px 0 0; }
        .mesa.principal .mesa-head { background-color: #6b4a86; }
        .mesa-head .nm { font-family: 'DejaVu Serif', serif; font-size: 15px; font-weight: bold; }
        .mesa-head .cap { float: right; font-size: 11px; font-weight: bold; color: #e6d7f5; padding-top: 3px; }
        .mesa-meta { font-size: 8px; letter-spacing: 1px; text-transform: uppercase; color: #9b7fbf; padding: 6px 11px 0; }
        .mesa-notes { font-size: 10px; color: #8a72a4; font-style: italic; padding: 2px 11px 0; }

        /* Sillas (puntos) */
        .seats { padding: 8px 11px 4px; }
        .dot { display: inline-block; width: 9px; height: 9px; border-radius: 50%; margin: 0 2px 2px 0; }
        .dot.on { background-color: #8a55be; }
        .dot.off { background-color: #fff; border: 1px solid #d3bfe8; }

        /* Lista de sentados */
        .guests { padding: 2px 11px 10px; }
        .guests .g { padding: 2px 0; border-bottom: 1px dotted #ece2f6; }
        .guests .num { color: #b79bd6; font-size: 9px; }
        .guests .grp { color: #a594b8; font-size: 9px; font-style: italic; }
        .freeline { padding: 4px 11px 10px; font-size: 9px; font-style: italic; color: #b79bd6; }
        .empty { padding: 8px 11px 12px; color: #b3a3c4; font-style: italic; }

        /* Apéndice pendientes */
        .appendix-title { font-size: 11px; letter-spacing: 2px; text-transform: uppercase; color: #a98c54; border-top: 1px solid #ece2f6; padding-top: 12px; margin: 16px 0 8px; }
        table.pend { width: 100%; border-collapse: separate; border-spacing: 0; }
        table.pend td { width: 33.33%; vertical-align: top; padding: 4px 8px; }
        .fam { font-weight: bold; color: #5f4c70; font-size: 10px; margin-bottom: 1px; }
        .fam .c { color: #a594b8; font-weight: normal; }
        .pers { font-size: 9.5px; color: #4a3a5c; padding-left: 6px; }

        .foot { text-align: center; font-size: 8px; color: #b3a3c4; letter-spacing: 1px; margin-top: 18px; }
    </style>
</head>
<body>
    {{-- Portada --}}
    <div class="cover">
        <div class="mono">Distribución de mesas</div>
        <h1 class="display">{{ $eventName }}</h1>
        @if ($eventDate)
            <div class="when">{{ $eventDate }}@if ($eventTime) · {{ $eventTime }} @endif</div>
        @endif
        <div class="rule"></div>
        <div class="stats">
            <span><span class="n">{{ $summary['tables'] }}</span><br><span class="l">Mesas</span></span>
            <span><span class="n">{{ $summary['seated'] }}</span><br><span class="l">Sentados</span></span>
            <span><span class="n">{{ $summary['capacity'] }}</span><br><span class="l">Lugares</span></span>
            <span><span class="n">{{ $summary['unassigned'] }}</span><br><span class="l">Pendientes</span></span>
        </div>
    </div>

    {{-- Mesas como tarjetas --}}
    @if ($tables->isEmpty())
        <p class="empty">Aún no hay mesas configuradas.</p>
    @else
        <table class="layout">
            @foreach ($rows as $pair)
                <tr>
                    @foreach ($pair as $table)
                        @php
                            $seated = $table->assignments->filter(fn ($a) => $a->companion)->sortBy(fn ($a) => $a->companion->invited_group)->values();
                            $occ = $seated->count();
                            $free = max(0, $table->capacity - $occ);
                        @endphp
                        <td>
                            <div class="mesa {{ $table->is_principal ? 'principal' : '' }}">
                                <div class="mesa-head">
                                    <span class="cap">{{ $occ }}/{{ $table->capacity }}</span>
                                    @if ($table->is_principal)<span style="font-size: 13px; color: #e8c87a;">★ </span>@endif<span class="nm">{{ $table->name }}</span>
                                </div>
                                <div class="mesa-meta">{{ $table->table_type ?: 'Sin tipo' }} · {{ $table->shape ?: 'Sin forma' }}</div>
                                @if ($table->notes)
                                    <div class="mesa-notes">{{ $table->notes }}</div>
                                @endif

                                <div class="seats">
                                    @for ($i = 0; $i < $table->capacity; $i++)
                                        <span class="dot {{ $i < $occ ? 'on' : 'off' }}"></span>
                                    @endfor
                                </div>

                                @if ($seated->isEmpty())
                                    <div class="empty">Mesa disponible</div>
                                @else
                                    <div class="guests">
                                        @foreach ($seated as $i => $a)
                                            <div class="g"><span class="num">{{ $i + 1 }}.</span> {{ $a->companion->name }} <span class="grp">· {{ $a->companion->invited_group }}</span></div>
                                        @endforeach
                                    </div>
                                    @if ($free > 0)
                                        <div class="freeline">+ {{ $free }} {{ $free === 1 ? 'lugar libre' : 'lugares libres' }}</div>
                                    @endif
                                @endif
                            </div>
                        </td>
                    @endforeach
                    @if ($pair->count() === 1)<td></td>@endif
                </tr>
            @endforeach
        </table>
    @endif

    {{-- Apéndice: pendientes por familia, en 3 columnas --}}
    @if ($unassigned->isNotEmpty())
        <div class="appendix-title">Pendientes por acomodar ({{ $summary['unassigned'] }})</div>
        <table class="pend">
            @foreach ($unassigned->chunk(3) as $rowGroups)
                <tr>
                    @foreach ($rowGroups as $group => $people)
                        <td>
                            <div class="fam">{{ $group }} <span class="c">({{ $people->count() }})</span></div>
                            @foreach ($people as $person)
                                <div class="pers">{{ $person->name }}</div>
                            @endforeach
                        </td>
                    @endforeach
                    @for ($k = $rowGroups->count(); $k < 3; $k++)<td></td>@endfor
                </tr>
            @endforeach
        </table>
    @endif

    <div class="foot">{{ $eventName }} · Generado el {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
