@extends('layouts.app')

@section('title', 'Mis aportaciones')
@section('heading', 'Mis aportaciones')
@section('subheading', 'El dinero que tú o la familia ponen para el evento (aparte de los padrinos).')

@section('content')
@php $m = fn ($v) => '$' . number_format((float) $v, 2); @endphp
@include('finances._styles')

    <div class="finance-nav">
        <a class="btn secondary" href="{{ route('finances.index') }}">📊 Resumen</a>
        <a class="btn secondary" href="{{ route('finances.expenses') }}">💰 Gastos</a>
        <a class="btn secondary" href="{{ route('finances.sponsors') }}">🎁 Padrinos</a>
    </div>

    <div class="card ministrip" style="margin-bottom:16px;">
        <div class="it">Total aportado por mí <b class="money" style="color:#3f9e6b;">{{ $m($totals['own']) }}</b></div>
        <div class="it">Reunido en total <b class="money">{{ $m($totals['gathered']) }}</b><span class="fmini">mío + padrinos</span></div>
        <div class="it">Falta por reunir <b class="money" style="color:#c0392b;">{{ $m($totals['to_gather']) }}</b></div>
    </div>

    <details class="card" style="margin-bottom: 16px;" open>
        <summary style="cursor:pointer; font-weight:700;">➕ Registrar aportación mía</summary>
        <form method="post" action="{{ route('finances.own.store') }}" class="inline" style="margin-top:12px; gap:10px; flex-wrap:wrap; align-items:end;">
            @csrf
            <div><label class="small">Monto</label><input name="amount" type="number" step="0.01" min="0.01" required style="width:150px;"></div>
            <div><label class="small">Fecha</label><input name="contributed_on" type="date" value="{{ now()->toDateString() }}" style="width:160px;"></div>
            <div><label class="small">Concepto (opcional)</label><input name="concept" placeholder="Ej. Anticipo salón" style="width:220px;"></div>
            <button class="btn" type="submit">Registrar</button>
        </form>
    </details>

    <div class="card">
        <h3 style="margin:0 0 10px;">Historial de mis aportaciones</h3>
        @forelse ($contributions as $c)
            <div class="inline" style="justify-content:space-between; padding:10px 0; border-bottom:1px solid #f3eefa; gap:8px;">
                <div>
                    <strong class="money" style="color:#3f9e6b;">{{ $m($c->amount) }}</strong>
                    <span class="fmini">· {{ $c->contributed_on?->format('d/m/Y') ?? '—' }}@if ($c->concept) · {{ $c->concept }}@endif</span>
                </div>
                <form method="post" action="{{ route('finances.own.destroy', $c) }}"
                    data-confirm-title="¿Eliminar esta aportación?" data-confirm-text="Se descontará del total aportado." data-confirm-button="Sí, eliminar" data-confirm-color="#d8527f">
                    @csrf @method('delete')
                    <button class="btn small danger" type="submit">✕</button>
                </form>
            </div>
        @empty
            <p class="small" style="margin:0;">Aún no has registrado aportaciones. Agrega la primera arriba.</p>
        @endforelse
    </div>
@endsection
