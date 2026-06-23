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
@include('finances._assets')

    <div class="card ministrip" style="margin-bottom:16px;">
        <div class="it">Comprometido <b class="money">{{ $m($totals['pledged']) }}</b></div>
        <div class="it">Recibido <b class="money" style="color:#3f9e6b;">{{ $m($totals['given']) }}</b></div>
        <div class="it">Por recibir <b class="money" style="color:#c0392b;">{{ $m($totals['pledge_remaining']) }}</b></div>
    </div>

    <div class="ftoolbar">
        <div class="filters">
            <input type="search" class="search" placeholder="🔍 Buscar padrino…" data-table-filter="sponsors-table">
            <select data-status-filter="sponsors-table">
                <option value="">Todos los estados</option>
                <option value="Pendiente">Pendiente</option>
                <option value="Parcial">Parcial</option>
                <option value="Completado">Completado</option>
            </select>
        </div>
        <button class="btn" type="button" data-modal-open="new-support" @disabled($padrinoOptions->isEmpty())>＋ Nuevo apoyo</button>
    </div>

    @if ($padrinoOptions->isEmpty())
        <div class="card"><p class="small" style="margin:0;">No hay invitados con padrino asignado. Primero marca el campo “Padrino” en algún invitado para poder registrar su apoyo.</p></div>
    @else
    <div class="ftable-wrap">
        <table class="ftable" id="sponsors-table">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th data-sort="text">Padrino</th>
                    <th data-sort="num" class="num">Comprometido</th>
                    <th data-sort="num" class="num">Dado</th>
                    <th data-sort="num" class="num">Falta</th>
                    <th data-sort="text">Estado</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($supports as $s)
                    @php
                        $given = $s->givenAmount();
                        $rem = $s->remaining();
                        $pct = (float) $s->pledged_amount > 0 ? (int) round(($given / (float) $s->pledged_amount) * 100) : 0;
                        $st = $s->status();
                    @endphp
                    <tr data-status="{{ $st }}">
                        <td class="rownum" style="color:#9b8ab0;">{{ $loop->iteration }}</td>
                        <td>
                            <strong style="color:#43275b;">{{ $s->guest?->name ?? '—' }}</strong>
                            <div class="sub">{{ $s->concept ?: ($s->guest?->sponsor ? 'Padrino de '.$s->guest->sponsor : 'Apoyo económico') }}</div>
                        </td>
                        <td class="num money" data-val="{{ $s->pledged_amount }}">{{ $m($s->pledged_amount) }}</td>
                        <td class="num money" data-val="{{ $given }}" style="color:#3f9e6b;">{{ $m($given) }}</td>
                        <td class="num money" data-val="{{ $rem }}" style="color:#c0392b;">{{ $m($rem) }}</td>
                        <td><span class="pill {{ $statusClass($st) }}">{{ $st }}</span><div class="fbar" style="width:80px; margin-top:5px;"><span class="ok" style="width:{{ $pct }}%;"></span></div></td>
                        <td>
                            <div class="rowact">
                                <button class="btn small secondary" type="button" data-modal-open="contrib-{{ $s->id }}">Aportaciones ({{ $s->contributions->count() }})</button>
                                <button class="btn small secondary" type="button" data-modal-open="edit-support-{{ $s->id }}">Editar</button>
                                <form method="post" action="{{ route('finances.supports.destroy', $s) }}"
                                    data-confirm-title="¿Eliminar este apoyo?" data-confirm-text="Se ocultará junto con sus aportaciones. No se borra físicamente." data-confirm-button="Sí, eliminar" data-confirm-color="#d8527f" data-confirm-icon="warning">
                                    @csrf @method('delete')
                                    <button class="btn small danger" type="submit">✕</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="ftable-empty">Aún no hay apoyos registrados. Agrega el primero con “＋ Nuevo apoyo”.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    {{-- ===== Modales ===== --}}

    @if ($padrinoOptions->isNotEmpty())
    <div class="modal" id="new-support">
        <div class="modal-box">
            <div class="modal-head"><h3>Nuevo apoyo de padrino</h3><button class="modal-x" type="button" data-modal-close>&times;</button></div>
            <form method="post" action="{{ route('finances.supports.store') }}">
                @csrf
                @include('finances._support_fields', ['support' => null, 'padrinoOptions' => $padrinoOptions])
                <div class="modal-actions">
                    <button class="btn secondary" type="button" data-modal-close>Cancelar</button>
                    <button class="btn" type="submit">Guardar apoyo</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @foreach ($supports as $s)
        <div class="modal" id="edit-support-{{ $s->id }}">
            <div class="modal-box">
                <div class="modal-head"><h3>Editar apoyo</h3><button class="modal-x" type="button" data-modal-close>&times;</button></div>
                <form method="post" action="{{ route('finances.supports.update', $s) }}">
                    @csrf @method('put')
                    @include('finances._support_fields', ['support' => $s, 'padrinoOptions' => $padrinoOptions])
                    <div class="modal-actions">
                        <button class="btn secondary" type="button" data-modal-close>Cancelar</button>
                        <button class="btn" type="submit">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal" id="contrib-{{ $s->id }}">
            <div class="modal-box lg">
                <div class="modal-head"><h3>Aportaciones · {{ $s->guest?->name }}</h3><button class="modal-x" type="button" data-modal-close>&times;</button></div>

                <div class="ministrip" style="padding:0 0 14px; gap:20px;">
                    <div class="it">Comprometido <b class="money">{{ $m($s->pledged_amount) }}</b></div>
                    <div class="it">Dado <b class="money" style="color:#3f9e6b;">{{ $m($s->givenAmount()) }}</b></div>
                    <div class="it">Falta <b class="money" style="color:#c0392b;">{{ $m($s->remaining()) }}</b></div>
                </div>

                <form method="post" action="{{ route('finances.contributions.store', $s) }}">
                    @csrf
                    <div class="grid2">
                        <div class="field"><label>Monto de la aportación</label><input name="amount" type="number" step="0.01" min="0.01" required></div>
                        <div class="field"><label>Fecha</label><input name="given_on" type="date" value="{{ now()->toDateString() }}"></div>
                    </div>
                    <div class="modal-actions" style="margin-top:6px;"><button class="btn" type="submit">Registrar aportación</button></div>
                </form>

                <h4 style="margin:14px 0 8px; color:#6b5a7e;">Historial de aportaciones</h4>
                @forelse ($s->contributions as $c)
                    <div class="inline" style="justify-content:space-between; padding:8px 0; border-bottom:1px solid #f3eefa; gap:8px;">
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
        </div>
    @endforeach
@endsection
