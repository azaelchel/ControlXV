@extends('layouts.app')

@section('title', 'Resumen finanzas')
@section('heading', 'Resumen de finanzas')
@section('subheading', 'Panorama real: cuánto cuesta, cuánto se ha pagado, qué pagos vienen y con cuánto se cuenta de los padrinos.')

@section('content')
@php
    $m = fn ($v) => '$' . number_format((float) $v, 2);
    $t = $totals;
    $paidPct = min(100, $t['paid_percent']);
    $givenPct = min(100, $t['given_percent']);
    // Cobertura del costo: parte de padrinos vs aporte propio
    $cost = max(0.01, $t['cost']);
    $cover = min(100, (int) round(($t['pledged'] / $cost) * 100));
@endphp
@include('finances._assets')
<style>
    .donut { width: 132px; height: 132px; border-radius: 50%; display: grid; place-items: center; flex-shrink: 0; }
    .donut .hole { width: 96px; height: 96px; background: #fff; border-radius: 50%; display: grid; place-items: center; text-align: center; }
    .donut .pct { font-size: 24px; font-weight: 800; color: #43275b; line-height: 1; }
    .donut .sub { font-size: 11px; color: #8a72a4; margin-top: 3px; }
    .hero { display: flex; gap: 22px; align-items: center; flex-wrap: wrap; }
    .hero .nums { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; flex: 1; min-width: 260px; }
    .bignum .l { font-size: 12px; color: #8a72a4; text-transform: uppercase; letter-spacing: .05em; }
    .bignum .v { font-size: 22px; font-weight: 800; color: #43275b; margin-top: 3px; }
    .stack { height: 26px; border-radius: 8px; overflow: hidden; display: flex; background: #efe5f7; }
    .stack > span { display: block; height: 100%; }
    .legend2 { display: flex; gap: 18px; flex-wrap: wrap; margin-top: 10px; font-size: 12px; color: #6b5a7e; }
    .legend2 i { width: 11px; height: 11px; border-radius: 3px; display: inline-block; margin-right: 5px; vertical-align: middle; }
    .catrow { display: grid; grid-template-columns: 150px 1fr 120px; gap: 12px; align-items: center; padding: 7px 0; }
    @media (max-width: 720px) { .catrow { grid-template-columns: 1fr; gap: 4px; } }
    .duerow { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f1eaf8; }
</style>

    <div class="inline" style="justify-content:flex-end; gap:8px; margin-bottom:14px; flex-wrap:wrap;">
        <a class="btn secondary" href="{{ route('finances.pdf') }}" target="_blank">📄 Descargar PDF</a>
        <a class="btn secondary" href="{{ route('finances.excel') }}">📊 Descargar Excel</a>
    </div>

    {{-- HERO: pagos --}}
    <div class="card" style="margin-bottom:16px;">
        <div class="hero">
            <div class="donut" style="background: conic-gradient(#3f9e6b {{ $paidPct }}%, #ecdff7 0);">
                <div class="hole">
                    <div><div class="pct">{{ $paidPct }}%</div><div class="sub">pagado</div></div>
                </div>
            </div>
            <div class="nums">
                <div class="bignum"><div class="l">Costo total</div><div class="v money">{{ $m($t['cost']) }}</div></div>
                <div class="bignum"><div class="l">Pagado</div><div class="v money" style="color:#3f9e6b;">{{ $m($t['paid']) }}</div></div>
                <div class="bignum"><div class="l">Falta pagar</div><div class="v money" style="color:#c0392b;">{{ $m($t['to_pay']) }}</div></div>
            </div>
        </div>
    </div>

    <div class="grid cols-2" style="gap:16px; margin-bottom:16px;">
        {{-- Cobertura del costo --}}
        <div class="card">
            <div class="kicker" style="color:#9b67c8; font-weight:800; font-size:12px; letter-spacing:.12em; margin-bottom:10px;">¿CÓMO SE CUBRE EL COSTO?</div>
            <div class="stack">
                <span style="width: {{ $cover }}%; background: linear-gradient(90deg,#7bc59a,#3f9e6b);"></span>
                <span style="width: {{ 100 - $cover }}%; background: linear-gradient(90deg,#b07fd8,#8a55be);"></span>
            </div>
            <div class="legend2">
                <span><i style="background:#3f9e6b;"></i>Padrinos (comprometido) {{ $m($t['pledged']) }}</span>
                <span><i style="background:#8a55be;"></i>Aporte propio estimado {{ $m($t['own_estimate']) }}</span>
            </div>
            <div class="fmini" style="margin-top:10px;">Del costo total ({{ $m($t['cost']) }}), los padrinos cubren cerca del {{ $cover }}%.</div>
        </div>

        {{-- Apoyo de padrinos --}}
        <div class="card">
            <div class="kicker" style="color:#9b67c8; font-weight:800; font-size:12px; letter-spacing:.12em; margin-bottom:10px;">APOYO DE PADRINOS</div>
            <div class="hero">
                <div class="donut" style="width:108px; height:108px; background: conic-gradient(#8a55be {{ $givenPct }}%, #ecdff7 0);">
                    <div class="hole" style="width:78px; height:78px;">
                        <div><div class="pct" style="font-size:19px;">{{ $givenPct }}%</div><div class="sub">recibido</div></div>
                    </div>
                </div>
                <div style="flex:1; min-width:160px;">
                    <div class="bignum" style="margin-bottom:8px;"><div class="l">Comprometido</div><div class="v money" style="font-size:18px;">{{ $m($t['pledged']) }}</div></div>
                    <div class="bignum" style="margin-bottom:8px;"><div class="l">Recibido</div><div class="v money" style="font-size:18px; color:#3f9e6b;">{{ $m($t['given']) }}</div></div>
                    <div class="bignum"><div class="l">Por recibir</div><div class="v money" style="font-size:18px; color:#c0392b;">{{ $m($t['pledge_remaining']) }}</div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid cols-2" style="gap:16px;">
        {{-- Próximos pagos --}}
        <div class="card">
            <div class="kicker" style="color:#9b67c8; font-weight:800; font-size:12px; letter-spacing:.12em; margin-bottom:6px;">PRÓXIMOS PAGOS PENDIENTES</div>
            @forelse ($upcoming as $e)
                @php
                    $due = $e->due_date;
                    $days = $due ? (int) now()->startOfDay()->diffInDays($due->copy()->startOfDay(), false) : null;
                @endphp
                <div class="duerow">
                    <div>
                        <strong style="color:#43275b;">{{ $e->name }}</strong>
                        <div class="fmini">
                            @if ($due)
                                @if ($days < 0) <span style="color:#c0392b;">Vencido hace {{ abs($days) }} día{{ abs($days)==1?'':'s' }}</span>
                                @elseif ($days === 0) <span style="color:#c0392b;">Vence hoy</span>
                                @else <span style="color:{{ $days <= 7 ? '#b0843f' : '#8a72a4' }};">Vence en {{ $days }} día{{ $days==1?'':'s' }} · {{ $due->format('d/m/Y') }}</span>
                                @endif
                            @else Sin fecha límite @endif
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div class="money" style="font-weight:700; color:#c0392b;">{{ $m($e->remaining()) }}</div>
                        <div class="fmini">de {{ $m($e->total_amount) }}</div>
                    </div>
                </div>
            @empty
                <p class="small" style="margin:8px 0 0; color:#9b8ab0;">🎉 No hay pagos pendientes.</p>
            @endforelse
            <div style="margin-top:12px;"><a class="btn small secondary" href="{{ route('finances.expenses') }}">Ver todos los gastos →</a></div>
        </div>

        {{-- Gasto por categoría --}}
        <div class="card">
            <div class="kicker" style="color:#9b67c8; font-weight:800; font-size:12px; letter-spacing:.12em; margin-bottom:6px;">GASTO POR CATEGORÍA</div>
            @forelse ($byCategory as $c)
                @php $cp = $c['total'] > 0 ? (int) round(($c['paid'] / $c['total']) * 100) : 0; @endphp
                <div class="catrow">
                    <div style="font-weight:600; color:#43275b;">{{ $c['category'] }}</div>
                    <div class="fbar"><span class="mid" style="width:{{ $cp }}%;"></span></div>
                    <div style="text-align:right;" class="money fmini"><b style="color:#43275b;">{{ $m($c['total']) }}</b><br>{{ $cp }}% pagado</div>
                </div>
            @empty
                <p class="small" style="margin:8px 0 0; color:#9b8ab0;">Aún no hay gastos registrados.</p>
            @endforelse
        </div>
    </div>
@endsection
