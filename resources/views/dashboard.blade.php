@extends('layouts.app')

@section('title', 'Resumen general')
@section('heading', 'Resumen general')
@section('subheading', 'Vista ejecutiva del evento: avance de confirmaciones, distribución y métricas clave')

@section('content')
    <style>
        /* HERO superior con número grande + comparativa */
        .top-hero { display: grid; grid-template-columns: 1.4fr 1fr; gap: 18px; margin-bottom: 18px; }
        @media (max-width: 980px) { .top-hero { grid-template-columns: 1fr; } }

        .confirm-card {
            padding: 28px;
            background: linear-gradient(135deg, var(--primary) 0%, #6b3b9e 100%);
            color: white;
            border-radius: 18px;
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 28px;
            align-items: center;
        }
        .confirm-card::after {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(255,255,255,0.1), transparent 70%);
            border-radius: 50%;
        }
        .confirm-card .donut { position: relative; z-index: 1; }
        .confirm-card .donut svg { transform: rotate(-90deg); }
        .confirm-card .donut .label-center {
            position: absolute; inset: 0;
            display: grid; place-items: center;
            text-align: center; line-height: 1.1;
        }
        .confirm-card .donut .label-center .big { font-size: 32px; font-weight: 800; }
        .confirm-card .donut .label-center .lbl { font-size: 11px; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.4px; font-weight: 600; }

        .confirm-card .info .top-label { font-size: 11px; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.4px; font-weight: 700; }
        .confirm-card .info h3 { margin: 6px 0 14px 0; font-size: 24px; font-weight: 800; }
        .confirm-card .info .row-mini { display: flex; gap: 18px; flex-wrap: wrap; }
        .confirm-card .info .row-mini > div { line-height: 1.2; }
        .confirm-card .info .row-mini .v { font-size: 22px; font-weight: 800; }
        .confirm-card .info .row-mini .l { font-size: 11px; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.4px; font-weight: 600; }

        /* Side card de "siguiente paso" */
        .next-step-card { padding: 24px; display: flex; flex-direction: column; gap: 14px; }
        .next-step-card .ico-circle { width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, #f0c674, #c69440); display: grid; place-items: center; font-size: 26px; color: white; box-shadow: 0 10px 22px rgba(198, 148, 64, 0.3); }
        .next-step-card .title { font-size: 16px; font-weight: 700; }
        .next-step-card .desc { font-size: 13px; color: var(--muted); line-height: 1.5; }
        .next-step-card.alert .ico-circle { background: linear-gradient(135deg, #f0a5c5, #d8527f); box-shadow: 0 10px 22px rgba(216, 82, 127, 0.3); }
        .next-step-card.ok .ico-circle { background: linear-gradient(135deg, #aedca0, #5fa657); box-shadow: 0 10px 22px rgba(95, 166, 87, 0.3); }

        /* KPIs grid */
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 18px; }
        .kpi-card { padding: 18px; }
        .kpi-card .head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .kpi-card .head .lbl { font-size: 11px; text-transform: uppercase; letter-spacing: 0.4px; font-weight: 700; color: var(--muted); }
        .kpi-card .head .ico { width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center; color: white; font-size: 16px; }
        .kpi-card .head .ico.p { background: linear-gradient(135deg, #c693ea, #8f55be); }
        .kpi-card .head .ico.b { background: linear-gradient(135deg, #92c2e8, #4a8cc9); }
        .kpi-card .head .ico.g { background: linear-gradient(135deg, #aedca0, #5fa657); }
        .kpi-card .head .ico.y { background: linear-gradient(135deg, #f0c674, #c69440); }
        .kpi-card .val { font-size: 30px; font-weight: 800; line-height: 1; color: var(--text); }
        .kpi-card .sub { font-size: 12px; color: var(--muted); margin-top: 6px; }

        /* Barra de estatus */
        .status-bars { display: flex; flex-direction: column; gap: 4px; margin-top: 12px; }
        .status-bar-row { display: grid; grid-template-columns: 180px 1fr 130px; gap: 12px; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0e9fa; }
        .status-bar-row:last-child { border-bottom: none; }
        .status-bar-row .name { display: flex; align-items: center; gap: 8px; }
        .status-bar-row .name .dot { width: 10px; height: 10px; border-radius: 50%; }
        .status-bar-row .name strong { font-size: 14px; }
        .status-bar-row .name .micro { font-size: 11px; color: var(--muted); margin-top: 2px; }
        .status-bar-row .bar { height: 10px; background: #f0e9fa; border-radius: 999px; overflow: hidden; position: relative; }
        .status-bar-row .bar > div { height: 100%; border-radius: 999px; transition: width 0.4s ease; }
        .status-bar-row .num { text-align: right; }
        .status-bar-row .num strong { font-weight: 800; font-size: 15px; }
        .status-bar-row .num .pct { font-size: 11px; color: var(--muted); margin-left: 4px; }

        /* Top grupos */
        .grp-row { display: grid; grid-template-columns: 28px 1fr auto auto; gap: 10px; align-items: center; padding: 10px 12px; border-bottom: 1px solid #f0e9fa; }
        .grp-row:last-child { border-bottom: none; }
        .grp-row:hover { background: #faf6ff; }
        .grp-row .rank { width: 28px; height: 28px; border-radius: 8px; background: var(--primary-soft); color: var(--primary-dark); display: grid; place-items: center; font-weight: 800; font-size: 12px; }
        .grp-row .name { font-weight: 600; }
        .grp-row .name .micro { font-size: 11px; color: var(--muted); }
        .grp-row .people { font-weight: 700; color: var(--text); }
        .grp-row .pad { font-size: 11px; color: var(--muted); }

        /* Real vs Probable */
        .cat-split { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 14px; }
        .cat-split .side { padding: 18px; border-radius: 12px; }
        .cat-split .side.real { background: linear-gradient(135deg, #eef7e9, #c8e8c0); }
        .cat-split .side.prob { background: linear-gradient(135deg, #faf6ff, #e6dff1); }
        .cat-split .lbl { font-size: 12px; text-transform: uppercase; letter-spacing: 0.4px; font-weight: 700; color: var(--muted); }
        .cat-split .big { font-size: 36px; font-weight: 800; line-height: 1; margin-top: 6px; color: var(--text); }
        .cat-split .sub { font-size: 12px; color: var(--muted); margin-top: 4px; }
        .cat-split .pct-bar { height: 6px; background: rgba(0,0,0,0.05); border-radius: 999px; margin-top: 14px; overflow: hidden; }
        .cat-split .pct-bar > div { height: 100%; border-radius: 999px; }
        .cat-split .real .pct-bar > div { background: #5fa657; }
        .cat-split .prob .pct-bar > div { background: var(--primary); }

        /* Demografía */
        .demo-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 8px; }
        .demo-cell { padding: 12px 14px; background: #faf6ff; border-radius: 10px; text-align: center; }
        .demo-cell .lbl { font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 600; color: var(--muted); }
        .demo-cell .val { font-size: 22px; font-weight: 800; color: var(--text); margin-top: 4px; line-height: 1; }
    </style>

    @php
        $totalPct = $summary['total_people'] > 0 ? round(($summary['confirmed_total_people'] / $summary['total_people']) * 100) : 0;
        $realPctConfirm = $summary['real_total_people'] > 0 ? round(($summary['real_confirmed_total_people'] / $summary['real_total_people']) * 100) : 0;

        $rejectedTotal = (int) optional($byStatus->firstWhere('status', 'No asistirá'))->total_people;
        $pendingTotal  = $summary['total_people'] - $summary['confirmed_total_people'] - $rejectedTotal;
        $realPendingTotal = $summary['real_total_people'] - $summary['real_confirmed_total_people'] - $summary['real_rejected_total_people'];

        // Personas realmente a considerar en planeación (total menos los que declinaron)
        $consideredReal = $summary['real_total_people'] - $summary['real_rejected_total_people'];
        $consideredTotal = $summary['total_people'] - $rejectedTotal;

        $diff = $companionsSummary['difference_vs_confirmed_people'];

        // Donut math
        $circumference = 2 * pi() * 60;
        $totalOffset = $circumference - ($circumference * $totalPct / 100);
        $realOffset = $circumference - ($circumference * $realPctConfirm / 100);

        $statusColors = [
            'Confirmado' => '#5fa657',
            'No asistirá' => '#d8527f',
            'Considerado' => '#c69440',
            'Invitacion Enviada' => '#4a8cc9',
            'Pendiente' => '#b88c1f',
            'No contesto' => '#8a8a8a',
            'Por definir' => '#a163c4',
        ];

        $statusIcons = [
            'Confirmado' => '✓',
            'No asistirá' => '✕',
            'Considerado' => '🤔',
            'Invitacion Enviada' => '📤',
            'Pendiente' => '⏳',
            'No contesto' => '🔕',
            'Por definir' => '❓',
        ];

        $totalForStatus = max(1, $byStatus->sum('total_people'));
        $topGroups = $byGroup->sortByDesc('total_people')->take(8);
        $realData = $byCategory->firstWhere('category', 'Real');
        $probData = $byCategory->firstWhere('category', 'Probable');
        $realPct = $summary['total_people'] > 0 ? round((($realData?->total_people ?? 0) / $summary['total_people']) * 100) : 0;
        $probPct = $summary['total_people'] > 0 ? round((($probData?->total_people ?? 0) / $summary['total_people']) * 100) : 0;
    @endphp

    {{-- HERO: dos donuts (Reales = principal, Total = secundario) + siguiente paso --}}
    <div class="top-hero">
        <div class="confirm-card" style="grid-template-columns: 1fr; gap: 0; padding: 0;">
            <div style="padding: 22px 28px 18px; border-bottom: 1px solid rgba(255,255,255,0.15);">
                <div style="font-size: 11px; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.4px; font-weight: 700;">Avance del evento</div>
                <h3 style="margin: 4px 0 0 0; font-size: 22px; font-weight: 800;">Tasa de confirmación</h3>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0;">
                {{-- DONUT REALES (PRINCIPAL) --}}
                <div style="padding: 24px 22px; display: flex; gap: 20px; align-items: center; position: relative;">
                    <span style="position: absolute; top: 12px; right: 12px; background: #ffd54a; color: #5a3e00; font-size: 10px; font-weight: 800; padding: 3px 8px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.3px;">⭐ Principal</span>
                    <div class="donut" style="width: 130px; height: 130px; flex-shrink: 0;">
                        <svg width="130" height="130" viewBox="0 0 160 160">
                            <circle cx="80" cy="80" r="60" fill="none" stroke="rgba(255,255,255,0.18)" stroke-width="16"/>
                            <circle cx="80" cy="80" r="60" fill="none" stroke="#ffd54a" stroke-width="16"
                                    stroke-dasharray="{{ $circumference }}"
                                    stroke-dashoffset="{{ $realOffset }}"
                                    stroke-linecap="round"/>
                        </svg>
                        <div class="label-center">
                            <div>
                                <div class="big" style="font-size: 28px;">{{ $realPctConfirm }}%</div>
                                <div class="lbl">Reales</div>
                            </div>
                        </div>
                    </div>
                    <div style="line-height: 1.4; min-width: 0;">
                        <div style="font-size: 11px; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.4px; font-weight: 600;">Categoría Real</div>
                        <div style="font-size: 22px; font-weight: 800; margin-top: 4px;">
                            {{ number_format($summary['real_confirmed_total_people']) }}<span style="opacity: 0.6; font-weight: 600;"> / {{ number_format($summary['real_total_people']) }}</span>
                        </div>
                        <div style="margin-top: 8px; padding: 8px 10px; background: rgba(255, 213, 74, 0.25); border-radius: 8px; border-left: 3px solid #ffd54a; font-size: 12px; font-weight: 600;">
                            📋 Para planeación: <strong style="font-size: 16px;">{{ number_format($summary['real_confirmed_total_people']) }}</strong>
                            <div style="font-size: 10px; opacity: 0.85; font-weight: 400; margin-top: 2px;">Personas con estatus Confirmado</div>
                        </div>

                        @php
                            $realOtherStatuses = $realByStatus->filter(fn ($r) => $r->status !== 'Confirmado' && (int) $r->people_sum > 0);
                        @endphp
                        @if ($realOtherStatuses->isNotEmpty())
                            <div style="font-size: 12px; opacity: 0.9; margin-top: 8px;">
                                @foreach ($realOtherStatuses as $r)
                                    <div style="display: flex; justify-content: space-between; gap: 8px; padding: 2px 0;">
                                        <span>{{ $statusIcons[$r->status] ?? '•' }} {{ $r->status }}</span>
                                        <strong>{{ number_format($r->people_sum) }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- DONUT TOTAL (SECUNDARIO) --}}
                <div style="padding: 24px 22px; display: flex; gap: 20px; align-items: center; background: rgba(0,0,0,0.08);">
                    <div class="donut" style="width: 130px; height: 130px; flex-shrink: 0;">
                        <svg width="130" height="130" viewBox="0 0 160 160">
                            <circle cx="80" cy="80" r="60" fill="none" stroke="rgba(255,255,255,0.18)" stroke-width="16"/>
                            <circle cx="80" cy="80" r="60" fill="none" stroke="white" stroke-width="16"
                                    stroke-dasharray="{{ $circumference }}"
                                    stroke-dashoffset="{{ $totalOffset }}"
                                    stroke-linecap="round"/>
                        </svg>
                        <div class="label-center">
                            <div>
                                <div class="big" style="font-size: 28px;">{{ $totalPct }}%</div>
                                <div class="lbl">Total</div>
                            </div>
                        </div>
                    </div>
                    <div style="line-height: 1.4; min-width: 0;">
                        <div style="font-size: 11px; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.4px; font-weight: 600;">Reales + Probables</div>
                        <div style="font-size: 22px; font-weight: 800; margin-top: 4px;">
                            {{ number_format($summary['confirmed_total_people']) }}<span style="opacity: 0.6; font-weight: 600;"> / {{ number_format($summary['total_people']) }}</span>
                        </div>
                        <div style="margin-top: 8px; padding: 8px 10px; background: rgba(255, 255, 255, 0.15); border-radius: 8px; border-left: 3px solid white; font-size: 12px; font-weight: 600;">
                            📋 Para planeación: <strong style="font-size: 16px;">{{ number_format($summary['confirmed_total_people']) }}</strong>
                            <div style="font-size: 10px; opacity: 0.85; font-weight: 400; margin-top: 2px;">Personas con estatus Confirmado</div>
                        </div>

                        @php
                            $totalOtherStatuses = $byStatus->filter(fn ($r) => $r->status !== 'Confirmado' && (int) $r->total_people > 0);
                        @endphp
                        @if ($totalOtherStatuses->isNotEmpty())
                            <div style="font-size: 12px; opacity: 0.9; margin-top: 8px;">
                                @foreach ($totalOtherStatuses as $r)
                                    <div style="display: flex; justify-content: space-between; gap: 8px; padding: 2px 0;">
                                        <span>{{ $statusIcons[$r->status] ?? '•' }} {{ $r->status }}</span>
                                        <strong>{{ number_format($r->total_people) }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @php
            $alertClass = 'next-step-card';
            $alertIcon = '✓';
            $alertTitle = 'Todo en orden';
            $alertDesc = 'No hay acciones críticas pendientes en este momento.';

            // Los Reales son la prioridad
            if ($realPendingTotal > 0) {
                $alertClass = 'next-step-card alert';
                $alertIcon = '⭐';
                $alertTitle = 'Foco en los Reales';
                $alertDesc = 'Faltan ' . number_format($realPendingTotal) . ' personas Reales por confirmar. Son la prioridad — manda mensajes o haz seguimiento.';
            } elseif ($pendingTotal > 0) {
                $alertClass = 'next-step-card alert';
                $alertIcon = '📤';
                $alertTitle = 'Seguimiento a probables';
                $alertDesc = 'Los Reales ya respondieron. Aún hay ' . number_format($pendingTotal) . ' personas en categoría Probable sin responder.';
            }

            if ($diff !== 0) {
                $alertClass = 'next-step-card alert';
                $alertIcon = '⚠';
                $alertTitle = 'Datos por reconciliar';
                if ($diff > 0) {
                    $alertDesc = 'Hay ' . $diff . ' invitados registrados más que personas confirmadas. Revisa el módulo de Invitados.';
                } else {
                    $alertDesc = 'Faltan ' . abs($diff) . ' invitados por registrar para igualar a los confirmados.';
                }
            }

            if ($realPctConfirm >= 95 && $diff === 0) {
                $alertClass = 'next-step-card ok';
                $alertTitle = '¡Reales casi al 100%!';
                $alertDesc = 'Más del 95% de los Reales confirmados y los datos cuadran. Excelente avance.';
            }
        @endphp
        <div class="card {{ $alertClass }}">
            <div class="ico-circle">{{ $alertIcon }}</div>
            <div>
                <div class="title">{{ $alertTitle }}</div>
                <div class="desc">{{ $alertDesc }}</div>
            </div>
            <div style="margin-top: auto;">
                <a href="{{ route('message-sends.index') }}" class="btn secondary" style="width: 100%; justify-content: center;">Ir a Mensajes →</a>
            </div>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="kpi-grid">
        <div class="card kpi-card">
            <div class="head">
                <div class="lbl">Familias / grupos</div>
                <div class="ico p">👨‍👩‍👧</div>
            </div>
            <div class="val">{{ number_format($summary['records']) }}</div>
            <div class="sub">{{ number_format($summary['real_records']) }} reales · {{ number_format($summary['records'] - $summary['real_records']) }} probables</div>
        </div>
        <div class="card kpi-card">
            <div class="head">
                <div class="lbl">Personas (esperadas)</div>
                <div class="ico b">👥</div>
            </div>
            <div class="val">{{ number_format($summary['total_people']) }}</div>
            <div class="sub">Adultos {{ $summary['adults'] }} · Adol. {{ $summary['adolescents'] }} · Niños {{ $summary['children'] }}</div>
        </div>
        <div class="card kpi-card">
            <div class="head">
                <div class="lbl">Personas confirmadas</div>
                <div class="ico g">✓</div>
            </div>
            <div class="val">{{ number_format($summary['confirmed_total_people']) }}</div>
            <div class="sub">de {{ number_format($summary['confirmed_records']) }} familias confirmadas</div>
        </div>
        <div class="card kpi-card">
            <div class="head">
                <div class="lbl">Padrinos</div>
                <div class="ico y">👑</div>
            </div>
            <div class="val">{{ number_format($summary['sponsors']) }}</div>
            <div class="sub">Asignaciones especiales del evento</div>
        </div>
    </div>

    {{-- Distribución por estatus con barras --}}
    <div class="card" style="margin-bottom: 18px;">
        <div class="section-kicker">Distribución por estatus</div>
        <h3 class="section-title">Estado actual de las respuestas</h3>
        <div class="status-bars">
            @foreach ($byStatus as $row)
                @php
                    $pct = $totalForStatus > 0 ? round(($row->total_people / $totalForStatus) * 100, 1) : 0;
                    $color = $statusColors[$row->status] ?? '#9e68c9';
                @endphp
                <div class="status-bar-row">
                    <div class="name">
                        <span class="dot" style="background: {{ $color }};"></span>
                        <div>
                            <strong>{{ $row->status }}</strong>
                            <div class="micro">{{ $row->records }} registro{{ $row->records === 1 ? '' : 's' }}</div>
                        </div>
                    </div>
                    <div class="bar"><div style="width: {{ $pct }}%; background: {{ $color }};"></div></div>
                    <div class="num"><strong>{{ number_format($row->total_people) }}</strong><span class="pct">{{ $pct }}%</span></div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Real vs Probable --}}
    <div class="card" style="margin-bottom: 18px;">
        <div class="section-kicker">Categorías</div>
        <h3 class="section-title">Reales vs probables</h3>
        <div class="cat-split">
            <div class="side real">
                <div class="lbl">Categoría Real</div>
                <div class="big">{{ number_format($realData?->total_people ?? 0) }}</div>
                <div class="sub">{{ number_format($realData?->records ?? 0) }} familias · {{ number_format($realData?->sponsors ?? 0) }} padrinos</div>
                <div class="pct-bar"><div style="width: {{ $realPct }}%;"></div></div>
                <div class="sub" style="margin-top: 6px; font-weight: 600;">{{ $realPct }}% del total</div>
            </div>
            <div class="side prob">
                <div class="lbl">Categoría Probable</div>
                <div class="big">{{ number_format($probData?->total_people ?? 0) }}</div>
                <div class="sub">{{ number_format($probData?->records ?? 0) }} familias · {{ number_format($probData?->sponsors ?? 0) }} padrinos</div>
                <div class="pct-bar"><div style="width: {{ $probPct }}%;"></div></div>
                <div class="sub" style="margin-top: 6px; font-weight: 600;">{{ $probPct }}% del total</div>
            </div>
        </div>
    </div>

    {{-- Top grupos + Composición --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px;">
        <div class="card">
            <div class="section-kicker">Top grupos</div>
            <h3 class="section-title">Familias con más invitados</h3>
            <div style="margin-top: 12px;">
                @forelse ($topGroups as $i => $row)
                    <div class="grp-row">
                        <div class="rank">{{ $i + 1 }}</div>
                        <div class="name">
                            {{ $row->group_name }}
                            <div class="micro">{{ $row->records }} registro{{ $row->records === 1 ? '' : 's' }}</div>
                        </div>
                        <div class="people">{{ number_format($row->total_people) }} 👥</div>
                        @if ($row->sponsors > 0)
                            <div class="pad">{{ $row->sponsors }} 👑</div>
                        @else
                            <div class="pad">—</div>
                        @endif
                    </div>
                @empty
                    <div class="empty" style="padding: 20px;">No hay datos aún.</div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="section-kicker">Demografía</div>
            <h3 class="section-title">Composición de invitados registrados confirmados reales</h3>
            <div style="margin: 14px 0;">
                <div class="small">Base: <strong>{{ $companionsSummary['scope'] }}</strong> · Total: <strong>{{ number_format($companionsSummary['total']) }}</strong> invitados individuales</div>
            </div>
            <div class="demo-grid">
                <div class="demo-cell"><div class="lbl">Hombres</div><div class="val">{{ number_format($companionsSummary['men']) }}</div></div>
                <div class="demo-cell"><div class="lbl">Mujeres</div><div class="val">{{ number_format($companionsSummary['women']) }}</div></div>
                <div class="demo-cell"><div class="lbl">Adultos</div><div class="val">{{ number_format($companionsSummary['adults']) }}</div></div>
                <div class="demo-cell"><div class="lbl">Adolescentes</div><div class="val">{{ number_format($companionsSummary['adolescents']) }}</div></div>
                <div class="demo-cell"><div class="lbl">Niños</div><div class="val">{{ number_format($companionsSummary['children']) }}</div></div>
            </div>
            <div style="margin-top: 14px; padding: 12px 14px; background: #faf6ff; border-radius: 10px; font-size: 12px; line-height: 1.6; color: var(--muted);">
                <strong>Adultos:</strong> {{ $companionsSummary['adult_men'] }}H · {{ $companionsSummary['adult_women'] }}M<br>
                <strong>Adolescentes:</strong> {{ $companionsSummary['teen_men'] }}H · {{ $companionsSummary['teen_women'] }}M<br>
                <strong>Niños:</strong> {{ $companionsSummary['child_men'] }}H · {{ $companionsSummary['child_women'] }}M
            </div>
        </div>
    </div>

@endsection
