@php
    $eventName = \App\Models\Setting::get('event_name', 'XV años de Zugeily');
    $eventDate = \App\Models\Setting::get('event_date', '');
    $eventTime = \App\Models\Setting::get("event_time", "");
    $generatedAt = now()->format("d/m/Y H:i");
    $occupiedTables = $tables
        ->filter(fn ($table) => $table->assignments->contains(fn ($assignment) => $assignment->companion))
        ->values();
    $rows = $occupiedTables->chunk(3);
    $initials = function ($name) {
        $parts = preg_split('/\s+/', trim((string) $name));
        $a = mb_substr($parts[0] ?? '', 0, 1);
        $b = isset($parts[1]) ? mb_substr($parts[1], 0, 1) : '';
        return mb_strtoupper($a . $b);
    };
    $typeCls = fn ($t) => match ($t) {
        'Adulto' => 'adulto', 'Adolescente' => 'adolescente', 'Niño' => 'nino', default => 'otro',
    };
    $typeHex = fn ($t) => match ($t) {
        'Adulto' => '#6d28b8', 'Adolescente' => '#1f9e6a', 'Niño' => '#2f7de1', default => '#8a8a96',
    };
    $tableNum = fn ($name) => preg_replace('/\D+/', '', (string) $name) ?: $name;
    $horizontalTables = ['1', '3', '5', '7', '9', '11', '13', '15', '17', '19', '21', '23'];
    $tableBox = function ($table) use ($tableNum, $horizontalTables) {
        if ($table->is_principal) {
            return ['w' => 30, 'h' => 34, 'ml' => -15, 'mt' => -17, 'cls' => 'principal'];
        }

        if (in_array($tableNum($table->name), $horizontalTables, true)) {
            return ['w' => 34, 'h' => 22, 'ml' => -17, 'mt' => -11, 'cls' => 'horizontal'];
        }

        return ['w' => 24, 'h' => 36, 'ml' => -12, 'mt' => -18, 'cls' => 'vertical'];
    };
    $mapFill = function ($occ, $cap) {
        if ($cap > 0 && $occ > $cap) return 'sobre';
        if ($occ === 0) return 'libre';
        if ($cap > 0 && $occ >= $cap) return 'llena';
        if ($cap > 0 && $occ >= $cap - 2) return 'casi';
        return 'parcial';
    };
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 12mm 12mm 16mm; }
        /* OJO dompdf: NO usar selector universal (*) ni resetear html{} — eso anula
           el @page margin y el contenido se pega al borde. Reset por elemento. */
        body { margin: 0; padding: 0; font-family: 'DejaVu Sans', sans-serif; color: #3a2a4d; font-size: 11px; }
        h1, p, div, table, td, ol, ul, li { margin: 0; padding: 0; }
        div, table, td { box-sizing: border-box; }
        .display { font-family: 'DejaVu Serif', serif; }

        /* Portada */
        .cover { text-align: center; margin-bottom: 6px; }
        .cover .mono { letter-spacing: 4px; font-size: 8px; text-transform: uppercase; color: #a98c54; margin-bottom: 3px; }
        .cover h1 { font-size: 20px; font-weight: bold; color: #43275b; }
        .cover .when { font-size: 10px; color: #6b5a7e; font-style: italic; margin-top: 2px; }
        .rule { width: 110px; margin: 5px auto 0; border-bottom: 2px solid #c9a86a; }
        .stats { margin-top: 5px; }
        .stats span { display: inline-block; padding: 0 13px; }
        .stats .n { font-family: 'DejaVu Serif', serif; font-size: 16px; font-weight: bold; color: #6b4a86; }
        .stats .l { font-size: 8px; text-transform: uppercase; letter-spacing: 1px; color: #9b8ab0; }
        .legend { text-align: center; margin-top: 12px; font-size: 9px; color: #6b5a7e; }
        .legend span { display: inline-block; padding: 0 8px; }
        .legend i { display: inline-block; width: 9px; height: 9px; border-radius: 50%; margin-right: 3px; vertical-align: middle; }

        /* Mapa */
        .map-title { margin: 6px 0 4px; font-size: 9px; letter-spacing: 2px; text-transform: uppercase; color: #a98c54; font-weight: bold; text-align: center; }
        .venue { position: relative; width: 100%; height: 108mm; border: 1px solid #d8c9eb; border-radius: 8px; background-color: #fbf8ff; margin-bottom: 5px; }
        .zone { position: absolute; text-align: center; font-size: 8px; font-weight: bold; letter-spacing: .6px; text-transform: uppercase; color: #6b5a7e; border: 1px dashed #cdbbe6; border-radius: 5px; background-color: #fff; padding-top: 5px; }
        .zone.pista { background-color: #eadcf6; color: #5b3a86; border: 1px solid #cdbbe6; font-size: 11px; padding-top: 24px; }
        .zone.pasillo { background-color: #f6f2fb; color: #9985b3; }
        .zone.entrance { border-radius: 16px; padding-top: 26px; }
        .cocina { position: absolute; font-size: 8px; font-weight: bold; color: #6b5a7e; }
        .map-table { position: absolute; text-align: center; border-radius: 6px; color: #fff; border: 1px solid #fff; }
        .map-table .num { display: block; font-size: 13px; font-weight: bold; line-height: 1; padding-top: 5px; }
        .map-table .occ { display: block; font-size: 7.5px; font-weight: bold; margin-top: 2px; }
        .map-table .sponsor-count { display: block; font-size: 7px; font-weight: bold; color: #ffe6a7; margin-top: 1px; }
        .map-table.horizontal .num { padding-top: 4px; }
        .map-table.principal .num { font-size: 14px; padding-top: 6px; }
        .map-table.libre { background-color: #cbb8e4; color: #4a2f6b; border-color: #d9cbed; }
        .map-table.parcial { background-color: #8f5dc5; }
        .map-table.casi { background-color: #d9a44d; }
        .map-table.llena { background-color: #6dad81; }
        .map-table.sobre { background-color: #c95550; }
        .map-table.principal { background-color: #5a3a7e; border: 1px solid #c9a86a; }
        .map-legend { text-align: center; font-size: 8px; color: #6b5a7e; margin-bottom: 4px; }
        .map-legend span { display: inline-block; padding: 0 7px; }
        .map-legend i { display: inline-block; width: 9px; height: 9px; border-radius: 2px; margin-right: 3px; vertical-align: middle; }

        .page-break { page-break-before: always; }
        .section-title-pdf { font-size: 12px; letter-spacing: 2px; text-transform: uppercase; color: #a98c54; font-weight: bold; margin-bottom: 8px; text-align: center; }
        .section-note { text-align: center; color: #8a72a4; font-size: 9px; margin: -4px 0 8px; }

        /* Mesas */
        table.layout { width: 100%; border-collapse: separate; border-spacing: 0; }
        table.layout > tr > td { width: 33.33%; vertical-align: top; padding: 4px; }

        .mesa { border: 1px solid #e0d2f0; border-radius: 8px; }
        .mesa.principal { border: 1px solid #c9a86a; }
        .mesa-head { background-color: #4a2f60; color: #fff; padding: 6px 9px; border-radius: 7px 7px 0 0; }
        .mesa.principal .mesa-head { background-color: #6b4a86; }
        .mesa-head .nm { font-family: 'DejaVu Serif', serif; font-size: 13px; font-weight: bold; }
        .mesa-head .cap { float: right; font-size: 10px; font-weight: bold; color: #e6d7f5; padding-top: 2px; }
        .mesa-meta { font-size: 8px; letter-spacing: 1px; text-transform: uppercase; color: #9b7fbf; padding: 6px 11px 0; }
        .mesa-notes { font-size: 10px; color: #8a72a4; font-style: italic; padding: 2px 11px 0; }

        /* Sillas con iniciales y color por tipo.
           dompdf no centra texto con line-height en inline-block; se usa una
           celda de tabla con vertical-align:middle (sí confiable en dompdf). */
        .seats { padding: 7px 9px 4px; }
        .seat { display: inline-block; width: 20px; height: 20px; border-radius: 50%; margin: 0 2px 3px 0; }
        .seat.off { background-color: #fff; border: 1px dashed #d3bfe8; }
        .seat.adulto { background-color: #6d28b8; }
        .seat.adolescente { background-color: #1f9e6a; }
        .seat.nino { background-color: #2f7de1; }
        .seat.otro { background-color: #8a8a96; }
        table.seatc { width: 20px; height: 20px; border-spacing: 0; border-collapse: collapse; }
        table.seatc td { width: 20px; height: 20px; text-align: center; vertical-align: middle; color: #fff; font-size: 8px; font-weight: bold; line-height: 1; padding: 0; }

        /* Lista de sentados (nombre coloreado por tipo) */
        .guests { padding: 2px 9px 8px; }
        .guests .g { padding: 1px 0; border-bottom: 1px dotted #ece2f6; font-weight: bold; font-size: 9px; }
        .guests .gnum { display: inline-block; min-width: 13px; color: #8a72a4; font-size: 8px; font-weight: bold; }
        .guests .sponsor-mark { color: #9d6b12; font-size: 9px; font-weight: bold; }
        .guests .grp { color: #a594b8; font-size: 9px; font-style: italic; font-weight: normal; }
        .freeline { padding: 4px 11px 10px; font-size: 9px; font-style: italic; color: #b79bd6; }
        .empty { padding: 8px 11px 12px; color: #b3a3c4; font-style: italic; }

        /* Apéndice pendientes */
        .appendix-title { font-size: 11px; letter-spacing: 2px; text-transform: uppercase; color: #a98c54; border-top: 1px solid #ece2f6; padding-top: 12px; margin: 16px 0 8px; }
        table.pend { width: 100%; border-collapse: separate; border-spacing: 0; }
        table.pend td { width: 33.33%; vertical-align: top; padding: 4px 8px; }
        .fam { font-weight: bold; color: #5f4c70; font-size: 10px; margin-bottom: 1px; }
        .fam .c { color: #a594b8; font-weight: normal; }
        .pers { font-size: 9.5px; color: #4a3a5c; padding-left: 6px; }
        .pers .pnum { color: #b79bd6; font-size: 8px; }

        .page-footer { position: fixed; left: 0; right: 0; bottom: -8mm; text-align: center; font-size: 8px; color: #9b8ab0; letter-spacing: .5px; }
        .page-footer .page-number:after { content: "Pagina " counter(page) " de " counter(pages); }
    </style>
</head>
<body>
    <div class="page-footer">{{ $eventName }} · Generado el {{ $generatedAt }} · <span class="page-number"></span></div>
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
            <span><span class="n">{{ $summary['capacity'] }}</span><br><span class="l">Capacidad máx.</span></span>
            <span><span class="n">{{ $summary['unassigned'] }}</span><br><span class="l">Pendientes</span></span>
        </div>
        <div class="legend">
            <span><i style="background:#6d28b8;"></i>Adulto</span>
            <span><i style="background:#1f9e6a;"></i>Adolescente</span>
            <span><i style="background:#2f7de1;"></i>Niño</span>
        </div>
    </div>

    {{-- Croquis del salón --}}
    <div class="map-title">Mapa del salón</div>
    <div class="map-legend">
        <span><i style="background:#cbb8e4;"></i>Vacía</span>
        <span><i style="background:#8f5dc5;"></i>Ocupándose</span>
        <span><i style="background:#d9a44d;"></i>Casi llena</span>
        <span><i style="background:#6dad81;"></i>Completa</span>
        <span><i style="background:#c95550;"></i>Sobrecupo</span>
        <span><i style="background:#5a3a7e;"></i>Principal</span>
        <span>♛ Padrino</span>
    </div>
    <div class="venue">
        <div class="zone entrance" style="left:0.6%; top:33%; width:3.2%; height:26%;">Entrada</div>
        <div class="zone" style="left:4%; top:4%; width:33%; height:10%;">Pasillo trasero</div>
        <div class="zone" style="left:40%; top:1%; width:17%; height:13%;">Música</div>
        <div class="zone" style="left:59%; top:4%; width:30%; height:10%;">Acceso a baños</div>
        <div class="cocina" style="left:91%; top:5%;">Cocina</div>
        <div class="zone pista" style="left:40%; top:24%; width:17%; height:30%;">Pista</div>
        <div class="zone pasillo" style="left:4%; top:85%; width:85%; height:9%;">Pasillo</div>

        @foreach ($tables as $table)
            @php
                $occ = $table->occupiedSeats();
                $cap = (int) $table->capacity;
                $box = $tableBox($table);
                $cls = $table->is_principal ? 'principal' : $mapFill($occ, $cap);
                $sponsorCount = $table->assignments
                    ->filter(fn ($a) => $a->companion && trim((string) ($sponsorByGuest[$a->companion->invited_group] ?? '')) !== '')
                    ->count();
            @endphp
            <div class="map-table {{ $box['cls'] }} {{ $cls }}"
                style="left: {{ $table->position_x ?? 50 }}%; top: {{ $table->position_y ?? 50 }}%; width: {{ $box['w'] }}px; height: {{ $box['h'] }}px; margin-left: {{ $box['ml'] }}px; margin-top: {{ $box['mt'] }}px;">
                <span class="num">{{ $tableNum($table->name) }}</span>
                <span class="occ">{{ $occ }}/{{ $cap }}</span>
                @if ($sponsorCount > 0)
                    <span class="sponsor-count">♛ {{ $sponsorCount }}</span>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Mesas como tarjetas --}}
    <div class="page-break"></div>
    <div class="section-title-pdf">Detalle por mesa</div>
    <div class="section-note">Se muestran solo mesas con invitados sentados; las mesas vacías se omiten.</div>
    @if ($occupiedTables->isEmpty())
        <p class="empty">Aún no hay mesas ocupadas.</p>
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
                                    @foreach ($seated as $a)
                                        <span class="seat {{ $typeCls($a->companion->type) }}"><table class="seatc"><tr><td>{{ $initials($a->companion->name) }}</td></tr></table></span>
                                    @endforeach
                                    @for ($i = 0; $i < $free; $i++)
                                        <span class="seat off"></span>
                                    @endfor
                                </div>

                                @if ($seated->isEmpty())
                                    <div class="empty">Mesa disponible</div>
                                @else
                                    <div class="guests">
                                        @foreach ($seated as $index => $a)
                                            @php $sponsor = trim((string) ($sponsorByGuest[$a->companion->invited_group] ?? '')); @endphp
                                            <div class="g" style="color: {{ $typeHex($a->companion->type) }};"><span class="gnum">{{ $index + 1 }}.</span> @if ($sponsor !== '')<span class="sponsor-mark">♛</span> @endif{{ $a->companion->name }} <span class="grp">· {{ $a->companion->invited_group }}@if ($sponsor !== '') · Padrino: {{ $sponsor }}@endif</span></div>
                                        @endforeach
                                    </div>
                                    @if ($free > 0)
                                        <div class="freeline">+ {{ $free }} {{ $free === 1 ? 'lugar libre' : 'lugares libres' }}</div>
                                    @endif
                                @endif
                            </div>
                        </td>
                    @endforeach
                    @for ($k = $pair->count(); $k < 3; $k++)<td></td>@endfor
                </tr>
            @endforeach
        </table>
    @endif

    {{-- Apéndice: pendientes por familia, en 3 columnas, numerados --}}
    @if ($unassigned->isNotEmpty())
        @php $pn = 0; @endphp
        <div class="appendix-title">Pendientes por acomodar ({{ $summary['unassigned'] }})</div>
        <table class="pend">
            @foreach ($unassigned->chunk(3) as $rowGroups)
                <tr>
                    @foreach ($rowGroups as $group => $people)
                        <td>
                            <div class="fam">{{ $group }} <span class="c">({{ $people->count() }})</span></div>
                            @foreach ($people as $person)
                                <div class="pers" style="color: {{ $typeHex($person->type) }};"><span class="pnum">{{ ++$pn }}.</span> {{ $person->name }}</div>
                            @endforeach
                        </td>
                    @endforeach
                    @for ($k = $rowGroups->count(); $k < 3; $k++)<td></td>@endfor
                </tr>
            @endforeach
        </table>
    @endif

</body>
</html>
