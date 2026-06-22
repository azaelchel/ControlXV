@extends('layouts.app')

@section('title', 'Padrinos')
@section('heading', 'Apoyos de padrinos')
@section('subheading', 'Cuánto apoyará cada padrino, cuánto ha dado y cuánto falta por recibir.')

@section('content')
@php
    $m = fn ($v) => '$' . number_format((float) $v, 2);
    $statusClass = fn ($s) => match ($s) {
        'Completado' => 'status-confirmado',
        'Parcial' => 'status-pendiente',
        default => 'status-default',
    };
@endphp
@include('finances._styles')

    <div class="finance-nav">
        <a class="btn secondary" href="{{ route('finances.index') }}">📊 Resumen</a>
        <a class="btn secondary" href="{{ route('finances.expenses') }}">💰 Gastos</a>
        <a class="btn secondary" href="{{ route('finances.own') }}">🙋 Mis aportaciones</a>
    </div>

    <div class="card ministrip" style="margin-bottom:16px;">
        <div class="it">Comprometido <b class="money">{{ $m($totals['pledged']) }}</b></div>
        <div class="it">Recibido <b class="money" style="color:#3f9e6b;">{{ $m($totals['given']) }}</b></div>
        <div class="it">Por recibir <b class="money" style="color:#c0392b;">{{ $m($totals['pledge_remaining']) }}</b></div>
    </div>

    <details class="card" style="margin-bottom: 16px;">
        <summary style="cursor:pointer; font-weight:700;">➕ Registrar apoyo de padrino</summary>
        @if ($padrinoOptions->isEmpty())
            <p class="small" style="margin-top:10px;">No hay invitados con padrino asignado. Primero marca el campo "Padrino" en algún invitado.</p>
        @else
            <form method="post" action="{{ route('finances.supports.store') }}" style="margin-top:12px;">
                @csrf
                @include('finances._support_fields', ['support' => null, 'padrinoOptions' => $padrinoOptions])
                <button class="btn" type="submit" style="margin-top:10px;">Guardar apoyo</button>
            </form>
        @endif
    </details>

    @forelse ($supports as $s)
        @php
            $given = $s->givenAmount();
            $rem = $s->remaining();
            $pct = (float) $s->pledged_amount > 0 ? (int) round(($given / (float) $s->pledged_amount) * 100) : 0;
            $st = $s->status();
        @endphp
        <div class="fcard">
            <div class="frow">
                <div>
                    <strong style="color:#43275b;">{{ $s->guest?->name ?? '—' }}</strong>
                    <span class="pill {{ $statusClass($st) }}" style="margin-left:6px;">{{ $st }}</span>
                    <div class="fmini">{{ $s->concept ?: ($s->guest?->sponsor ? 'Padrino de '.$s->guest->sponsor : 'Apoyo económico') }}</div>
                </div>
                <div><div class="ftag">Comprometido</div><div class="money">{{ $m($s->pledged_amount) }}</div></div>
                <div><div class="ftag">Dado</div><div class="money" style="color:#3f9e6b;">{{ $m($given) }}</div></div>
                <div><div class="ftag">Falta</div><div class="money" style="color:#c0392b;">{{ $m($rem) }}</div></div>
                <div style="text-align:right;">
                    <div class="fbar" style="width:120px; margin-left:auto;"><span class="ok" style="width: {{ $pct }}%;"></span></div>
                    <div class="fmini">{{ $pct }}% recibido</div>
                </div>
            </div>

            <div class="inline" style="margin-top:12px; gap:8px; flex-wrap:wrap;">
                <details>
                    <summary class="btn small">＋ Aportación</summary>
                    <form method="post" action="{{ route('finances.contributions.store', $s) }}" class="inline" style="margin-top:10px; gap:8px; flex-wrap:wrap; align-items:end;">
                        @csrf
                        <div><label class="small">Monto</label><input name="amount" type="number" step="0.01" min="0.01" required style="width:130px;"></div>
                        <div><label class="small">Fecha</label><input name="given_on" type="date" value="{{ now()->toDateString() }}" style="width:160px;"></div>
                        <button class="btn small" type="submit">Registrar aportación</button>
                    </form>
                </details>

                <details>
                    <summary class="btn small secondary">📜 Aportaciones ({{ $s->contributions->count() }})</summary>
                    <div style="margin-top:10px;">
                        @forelse ($s->contributions as $c)
                            <div class="inline" style="justify-content:space-between; padding:5px 0; border-bottom:1px solid #f3eefa; gap:8px;">
                                <span class="small">{{ $c->given_on?->format('d/m/Y') ?? '—' }} · <strong class="money">{{ $m($c->amount) }}</strong></span>
                                <form method="post" action="{{ route('finances.contributions.destroy', $c) }}"
                                    data-confirm-title="¿Eliminar esta aportación?" data-confirm-text="Se descontará del total recibido." data-confirm-button="Sí, eliminar" data-confirm-color="#d8527f">
                                    @csrf @method('delete')
                                    <button class="btn small danger" type="submit">✕</button>
                                </form>
                            </div>
                        @empty
                            <p class="small" style="margin:0; color:#9b8ab0;">Sin aportaciones todavía.</p>
                        @endforelse
                    </div>
                </details>

                <details>
                    <summary class="btn small secondary">Editar</summary>
                    <form method="post" action="{{ route('finances.supports.update', $s) }}" style="margin-top:10px;">
                        @csrf @method('put')
                        @include('finances._support_fields', ['support' => $s, 'padrinoOptions' => $padrinoOptions])
                        <button class="btn small" type="submit" style="margin-top:8px;">Guardar cambios</button>
                    </form>
                </details>

                <form method="post" action="{{ route('finances.supports.destroy', $s) }}"
                    data-confirm-title="¿Eliminar este apoyo?" data-confirm-text="Se ocultará junto con sus aportaciones. No se borra físicamente." data-confirm-button="Sí, eliminar" data-confirm-color="#d8527f" data-confirm-icon="warning">
                    @csrf @method('delete')
                    <button class="btn small danger" type="submit">Eliminar</button>
                </form>
            </div>
        </div>
    @empty
        <div class="card"><p class="small" style="margin:0;">Aún no hay apoyos de padrinos registrados.</p></div>
    @endforelse
@endsection
