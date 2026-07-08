@php
    $eventName = \App\Models\Setting::get('event_name', 'XV años de Zugeily');
    $eventDate = \App\Models\Setting::get('event_date', '');
    $m = fn ($v) => '$' . number_format((float) $v, 2);
    $t = $totals;
    $over = $guestOverage;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18mm 15mm 18mm; }
        /* dompdf: nada de * ni html{} (anulan @page). Reset por elemento. */
        body { margin: 0; padding: 0; font-family: 'DejaVu Sans', sans-serif; color: #3a2a4d; font-size: 11px; }
        h1, h2, p, div, table, td, th { margin: 0; padding: 0; }
        div, table, td, th { box-sizing: border-box; }

        .cover { text-align: center; border-bottom: 2px solid #c9a86a; padding-bottom: 12px; margin-bottom: 16px; }
        .cover .mono { letter-spacing: 5px; font-size: 9px; text-transform: uppercase; color: #a98c54; margin-bottom: 6px; }
        .cover h1 { font-family: 'DejaVu Serif', serif; font-size: 26px; font-weight: bold; color: #43275b; }
        .cover .when { font-size: 12px; color: #6b5a7e; font-style: italic; margin-top: 3px; }

        .sec { font-size: 12px; letter-spacing: 1.5px; text-transform: uppercase; color: #a98c54; margin: 16px 0 7px; }

        table.t { width: 100%; border-collapse: collapse; }
        table.t th, table.t td { padding: 6px 8px; border-bottom: 1px solid #ece2f6; text-align: left; }
        table.t thead th { background-color: #4a2f60; color: #fff; font-size: 9px; text-transform: uppercase; letter-spacing: .5px; }
        table.t td.n, table.t th.n { text-align: right; font-variant-numeric: tabular-nums; }
        .green { color: #1f9e6a; }
        .red { color: #c0392b; }

        /* Panorama (2 columnas de tarjetas) */
        table.pan { width: 100%; border-collapse: separate; border-spacing: 8px; margin: 0 -8px; }
        table.pan td { width: 25%; background-color: #faf6ff; border: 1px solid #ece2f6; border-radius: 8px; padding: 9px 11px; vertical-align: top; }
        .pan .lbl { font-size: 8.5px; text-transform: uppercase; letter-spacing: .5px; color: #8a72a4; }
        .pan .val { font-size: 15px; font-weight: bold; color: #43275b; margin-top: 3px; font-variant-numeric: tabular-nums; }
        .foot { text-align: center; font-size: 8px; color: #b3a3c4; letter-spacing: 1px; margin-top: 18px; }
        .empty { color: #b3a3c4; font-style: italic; padding: 8px; }
        .note { background-color: #fff9ec; border: 1px solid #f0d69f; border-radius: 8px; padding: 9px 11px; margin: 10px 0 14px; color: #5f4c70; }
        .note b { color: #43275b; }
    </style>
</head>
<body>
    <div class="cover">
        <div class="mono">Estado financiero</div>
        <h1>{{ $eventName }}</h1>
        @if ($eventDate)<div class="when">{{ $eventDate }}</div>@endif
    </div>

    {{-- Panorama --}}
    <table class="pan">
        <tr>
            <td><div class="lbl">Costo total</div><div class="val">{{ $m($t['cost']) }}</div></td>
            <td><div class="lbl">Pagado ({{ $t['paid_percent'] }}%)</div><div class="val green">{{ $m($t['paid']) }}</div></td>
            <td><div class="lbl">Falta pagar</div><div class="val red">{{ $m($t['to_pay']) }}</div></td>
            <td><div class="lbl">Aporte propio estimado</div><div class="val">{{ $m($t['own_estimate']) }}</div></td>
        </tr>
        <tr>
            <td><div class="lbl">Padrinos comprometido</div><div class="val">{{ $m($t['pledged']) }}</div></td>
            <td><div class="lbl">Padrinos recibido</div><div class="val green">{{ $m($t['given']) }}</div></td>
            <td><div class="lbl">Padrinos por recibir</div><div class="val red">{{ $m($t['pledge_remaining']) }}</div></td>
            <td></td>
        </tr>
    </table>

    @if ($over['total_extra_cost'] > 0)
        <div class="note">
            <b>Extra automático por invitados: {{ $m($over['total_extra_cost']) }}</b><br>
            {{ $over['confirmed_total'] }} confirmados contra {{ $over['included_guests'] }} incluidos; sobrecupo de {{ $over['extra_people'] }}.
            Platillos extra: {{ $over['extra_children'] }} niño{{ $over['extra_children']==1?'':'s' }} y {{ $over['extra_adult_like'] }} adulto/adolescente = {{ $m($over['extra_plate_cost']) }}.
            Tornamesa: {{ $over['required_turntable_people'] }} requeridos al {{ $over['turntable_percent'] }}%, {{ $over['included_turntable_people'] }} incluidos; extra {{ $over['extra_turntable_people'] }} = {{ $m($over['extra_turntable_cost']) }}.
        </div>
    @endif

    {{-- Gastos --}}
    <div class="sec">Gastos y proveedores</div>
    <table class="t">
        <thead>
            <tr>
                <th>Concepto</th><th>Categoría</th><th class="n">Total</th><th class="n">Pagado</th>
                <th class="n">Resta</th><th>Estado</th><th>Vence</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($expenses as $e)
                <tr>
                    <td>{{ $e->name }}@if ($e->provider)<br><span style="color:#9b8ab0;font-size:9px;">{{ $e->provider }}</span>@endif</td>
                    <td>{{ $e->category ?: '—' }}</td>
                    <td class="n">{{ $m($e->total_amount) }}</td>
                    <td class="n green">{{ $m($e->paidAmount()) }}</td>
                    <td class="n red">{{ $m($e->remaining()) }}</td>
                    <td>{{ $e->status() }}</td>
                    <td>{{ $e->due_date?->format('d/m/Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">Sin gastos registrados.</td></tr>
            @endforelse
            @if ($over['total_extra_cost'] > 0)
                <tr>
                    <td>Extra automático por invitados<br><span style="color:#9b8ab0;font-size:9px;">Calculado por confirmados</span></td>
                    <td>Extras automáticos</td>
                    <td class="n">{{ $m($over['total_extra_cost']) }}</td>
                    <td class="n green">{{ $m(0) }}</td>
                    <td class="n red">{{ $m($over['total_extra_cost']) }}</td>
                    <td>Pendiente</td>
                    <td>—</td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- Padrinos --}}
    <div class="sec">Apoyos de padrinos</div>
    <table class="t">
        <thead>
            <tr><th>Padrino</th><th>Concepto</th><th class="n">Comprometido</th><th class="n">Dado</th><th class="n">Falta</th><th>Estado</th></tr>
        </thead>
        <tbody>
            @forelse ($supports as $s)
                <tr>
                    <td>{{ $s->guest?->name ?? '—' }}</td>
                    <td>{{ $s->concept ?: ($s->guest?->sponsor ?? '—') }}</td>
                    <td class="n">{{ $m($s->pledged_amount) }}</td>
                    <td class="n green">{{ $m($s->givenAmount()) }}</td>
                    <td class="n red">{{ $m($s->remaining()) }}</td>
                    <td>{{ $s->status() }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">Sin apoyos registrados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="foot">{{ $eventName }} · Estado financiero generado el {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
