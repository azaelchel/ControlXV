@extends('layouts.app')

@section('title', 'Resumen finanzas')
@section('heading', 'Resumen de finanzas')
@section('subheading', 'Panorama real: cuánto se ha reunido, cuánto se ha pagado y cuánto falta para cubrir los XV.')

@section('content')
@php $m = fn ($v) => '$' . number_format((float) $v, 2); @endphp
@include('finances._assets')

    {{-- Dinero reunido vs costo --}}
    <div class="card" style="margin-bottom:14px;">
        <div class="kicker" style="color:#9b67c8; font-weight:800; font-size:12px; letter-spacing:.12em; margin-bottom:8px;">¿CUÁNTO HEMOS REUNIDO?</div>
        <div class="grid cols-4">
            <div class="metric">
                <div class="label">Costo total del evento</div>
                <div class="value money">{{ $m($totals['cost']) }}</div>
            </div>
            <div class="metric">
                <div class="label">Reunido ({{ $totals['gathered_percent'] }}%)</div>
                <div class="value money" style="color:#3f9e6b;">{{ $m($totals['gathered']) }}</div>
                <div class="fbar"><span class="ok" style="width: {{ min(100, $totals['gathered_percent']) }}%;"></span></div>
                <div class="fmini">Mis aportaciones + recibido de padrinos</div>
            </div>
            <div class="metric">
                <div class="label">Falta por reunir</div>
                <div class="value money" style="color:#c0392b;">{{ $m($totals['to_gather']) }}</div>
            </div>
            <div class="metric">
                <div class="label">Saldo en mano</div>
                <div class="value money">{{ $m($totals['balance']) }}</div>
                <div class="fmini">Reunido − pagado a proveedores</div>
            </div>
        </div>
    </div>

    {{-- Pagos a proveedores --}}
    <div class="card" style="margin-bottom:14px;">
        <div class="kicker" style="color:#9b67c8; font-weight:800; font-size:12px; letter-spacing:.12em; margin-bottom:8px;">¿CUÁNTO HEMOS PAGADO?</div>
        <div class="grid cols-3">
            <div class="metric">
                <div class="label">Pagado a proveedores ({{ $totals['paid_percent'] }}%)</div>
                <div class="value money" style="color:#3f9e6b;">{{ $m($totals['paid']) }}</div>
                <div class="fbar"><span class="mid" style="width: {{ min(100, $totals['paid_percent']) }}%;"></span></div>
            </div>
            <div class="metric">
                <div class="label">Falta pagar</div>
                <div class="value money" style="color:#c0392b;">{{ $m($totals['to_pay']) }}</div>
            </div>
            <div class="metric">
                <div class="label">Costo total</div>
                <div class="value money">{{ $m($totals['cost']) }}</div>
            </div>
        </div>
    </div>

    {{-- Detalle de ingresos --}}
    <div class="card">
        <div class="kicker" style="color:#9b67c8; font-weight:800; font-size:12px; letter-spacing:.12em; margin-bottom:8px;">¿DE DÓNDE VIENE EL DINERO?</div>
        <div class="grid cols-4">
            <div class="metric">
                <div class="label">Mis aportaciones</div>
                <div class="value money">{{ $m($totals['own']) }}</div>
            </div>
            <div class="metric">
                <div class="label">Padrinos comprometido</div>
                <div class="value money">{{ $m($totals['pledged']) }}</div>
            </div>
            <div class="metric">
                <div class="label">Padrinos recibido</div>
                <div class="value money" style="color:#3f9e6b;">{{ $m($totals['given']) }}</div>
            </div>
            <div class="metric">
                <div class="label">Padrinos por recibir</div>
                <div class="value money" style="color:#c0392b;">{{ $m($totals['pledge_remaining']) }}</div>
            </div>
        </div>
    </div>
@endsection
