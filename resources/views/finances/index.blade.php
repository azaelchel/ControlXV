@extends('layouts.app')

@section('title', 'Resumen finanzas')
@section('heading', 'Resumen de finanzas')
@section('subheading', 'Panorama real: cuánto cuesta todo, cuánto se ha pagado y con cuánto se cuenta de los padrinos.')

@section('content')
@php $m = fn ($v) => '$' . number_format((float) $v, 2); @endphp
@include('finances._assets')

    <div class="inline" style="justify-content:flex-end; gap:8px; margin-bottom:14px; flex-wrap:wrap;">
        <a class="btn secondary" href="{{ route('finances.pdf') }}" target="_blank">📄 Descargar PDF</a>
        <a class="btn secondary" href="{{ route('finances.excel') }}">📊 Descargar Excel</a>
    </div>

    {{-- Pagos a proveedores --}}
    <div class="card" style="margin-bottom:14px;">
        <div class="kicker" style="color:#9b67c8; font-weight:800; font-size:12px; letter-spacing:.12em; margin-bottom:8px;">¿CUÁNTO CUESTA Y CUÁNTO HEMOS PAGADO?</div>
        <div class="grid cols-3">
            <div class="metric">
                <div class="label">Costo total del evento</div>
                <div class="value money">{{ $m($totals['cost']) }}</div>
            </div>
            <div class="metric">
                <div class="label">Pagado a proveedores ({{ $totals['paid_percent'] }}%)</div>
                <div class="value money" style="color:#3f9e6b;">{{ $m($totals['paid']) }}</div>
                <div class="fbar"><span class="mid" style="width: {{ min(100, $totals['paid_percent']) }}%;"></span></div>
            </div>
            <div class="metric">
                <div class="label">Falta pagar</div>
                <div class="value money" style="color:#c0392b;">{{ $m($totals['to_pay']) }}</div>
            </div>
        </div>
    </div>

    {{-- Apoyo de padrinos --}}
    <div class="card" style="margin-bottom:14px;">
        <div class="kicker" style="color:#9b67c8; font-weight:800; font-size:12px; letter-spacing:.12em; margin-bottom:8px;">¿CON CUÁNTO SE CUENTA DE LOS PADRINOS?</div>
        <div class="grid cols-3">
            <div class="metric">
                <div class="label">Comprometido en total</div>
                <div class="value money">{{ $m($totals['pledged']) }}</div>
            </div>
            <div class="metric">
                <div class="label">Recibido ({{ $totals['given_percent'] }}%)</div>
                <div class="value money" style="color:#3f9e6b;">{{ $m($totals['given']) }}</div>
                <div class="fbar"><span class="ok" style="width: {{ min(100, $totals['given_percent']) }}%;"></span></div>
            </div>
            <div class="metric">
                <div class="label">Por recibir</div>
                <div class="value money" style="color:#c0392b;">{{ $m($totals['pledge_remaining']) }}</div>
            </div>
        </div>
    </div>

    {{-- Aporte propio --}}
    <div class="card">
        <div class="grid cols-2">
            <div class="metric">
                <div class="label">Aporte propio estimado</div>
                <div class="value money">{{ $m($totals['own_estimate']) }}</div>
                <div class="fmini">Lo que cubre la familia aparte del apoyo de padrinos (costo total − comprometido de padrinos)</div>
            </div>
            <div class="metric">
                <div class="label">Falta pagar a proveedores</div>
                <div class="value money" style="color:#c0392b;">{{ $m($totals['to_pay']) }}</div>
                <div class="fmini">Independiente de quién ponga el dinero</div>
            </div>
        </div>
    </div>
@endsection
