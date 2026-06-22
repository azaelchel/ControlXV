@extends('layouts.app')

@section('title', 'Finanzas')
@section('heading', 'Finanzas')
@section('subheading', 'Control de gastos del evento, abonos a proveedores y apoyos económicos de padrinos. Panorama real de cuánto se debe y cuánto se ha cubierto.')

@section('content')
@php
    $m = fn ($v) => '$' . number_format((float) $v, 2);
    $statusClass = fn ($s) => match ($s) {
        'Pagado', 'Completado' => 'status-confirmado',
        'Parcial' => 'status-pendiente',
        'Vencido' => 'status-no-asistira',
        default => 'status-default',
    };
@endphp
<style>
    .fbar { height: 10px; border-radius: 999px; background: #efe5f7; overflow: hidden; margin-top: 6px; }
    .fbar > span { display: block; height: 100%; }
    .fbar > span.ok { background: linear-gradient(90deg,#7bc59a,#3f9e6b); }
    .fbar > span.mid { background: linear-gradient(90deg,#b07fd8,#8a55be); }
    .money { font-variant-numeric: tabular-nums; }
    .frow { display: grid; grid-template-columns: 1.4fr repeat(3, .8fr) auto; gap: 12px; align-items: center; }
    @media (max-width: 820px) { .frow { grid-template-columns: 1fr; } }
    .ftag { font-size: 11px; color: #8a72a4; text-transform: uppercase; letter-spacing: .05em; }
    .fcard { border: 1px solid #e7dcf3; border-radius: 16px; padding: 16px; background: #fff; margin-bottom: 12px; }
    .fmini { font-size: 12px; color: #6b5a7e; }
    .section-tabs { display: flex; gap: 8px; margin: 22px 0 14px; flex-wrap: wrap; }
</style>

    {{-- ===== PANORAMA ===== --}}
    <div class="grid cols-4" style="margin-bottom: 14px;">
        <div class="card metric">
            <div class="label">Costo total</div>
            <div class="value money">{{ $m($totals['cost']) }}</div>
        </div>
        <div class="card metric">
            <div class="label">Pagado ({{ $totals['paid_percent'] }}%)</div>
            <div class="value money" style="color:#3f9e6b;">{{ $m($totals['paid']) }}</div>
            <div class="fbar"><span class="ok" style="width: {{ $totals['paid_percent'] }}%;"></span></div>
        </div>
        <div class="card metric">
            <div class="label">Falta pagar</div>
            <div class="value money" style="color:#c0392b;">{{ $m($totals['remaining']) }}</div>
        </div>
        <div class="card metric">
            <div class="label">Aporte propio estimado</div>
            <div class="value money">{{ $m($totals['own_estimate']) }}</div>
            <div class="fmini">Costo total − apoyo de padrinos</div>
        </div>
    </div>

    <div class="grid cols-3" style="margin-bottom: 8px;">
        <div class="card metric">
            <div class="label">Apoyo de padrinos comprometido</div>
            <div class="value money">{{ $m($totals['pledged']) }}</div>
        </div>
        <div class="card metric">
            <div class="label">Recibido de padrinos ({{ $totals['given_percent'] }}%)</div>
            <div class="value money" style="color:#3f9e6b;">{{ $m($totals['given']) }}</div>
            <div class="fbar"><span class="ok" style="width: {{ $totals['given_percent'] }}%;"></span></div>
        </div>
        <div class="card metric">
            <div class="label">Por recibir de padrinos</div>
            <div class="value money" style="color:#c0392b;">{{ $m($totals['pledge_remaining']) }}</div>
        </div>
    </div>

    {{-- ===== GASTOS ===== --}}
    <h2 class="section-tabs" style="font-size:18px; color:#43275b; margin-bottom:8px;">Gastos y proveedores</h2>

    <details class="card" style="margin-bottom: 16px;">
        <summary style="cursor:pointer; font-weight:700;">➕ Agregar gasto</summary>
        <form method="post" action="{{ route('finances.expenses.store') }}" style="margin-top:12px;">
            @csrf
            @include('finances._expense_fields', ['expense' => null, 'categories' => $categories])
            <button class="btn" type="submit" style="margin-top:10px;">Guardar gasto</button>
        </form>
    </details>

    @forelse ($expenses as $expense)
        @php
            $paid = $expense->paidAmount();
            $rem = $expense->remaining();
            $pct = (float) $expense->total_amount > 0 ? (int) round(($paid / (float) $expense->total_amount) * 100) : 0;
            $st = $expense->status();
        @endphp
        <div class="fcard">
            <div class="frow">
                <div>
                    <strong style="color:#43275b;">{{ $expense->name }}</strong>
                    <span class="pill {{ $statusClass($st) }}" style="margin-left:6px;">{{ $st }}</span>
                    <div class="fmini">
                        {{ $expense->category ?: 'Sin categoría' }}@if ($expense->provider) · {{ $expense->provider }}@endif
                        @if ($expense->due_date) · 📅 vence {{ $expense->due_date->format('d/m/Y') }}@endif
                    </div>
                </div>
                <div><div class="ftag">Total</div><div class="money">{{ $m($expense->total_amount) }}</div></div>
                <div><div class="ftag">Pagado</div><div class="money" style="color:#3f9e6b;">{{ $m($paid) }}</div></div>
                <div><div class="ftag">Resta</div><div class="money" style="color:#c0392b;">{{ $m($rem) }}</div></div>
                <div style="text-align:right;">
                    <div class="fbar" style="width:120px; margin-left:auto;"><span class="mid" style="width: {{ $pct }}%;"></span></div>
                    <div class="fmini">{{ $pct }}% pagado</div>
                </div>
            </div>

            <div class="inline" style="margin-top:12px; gap:8px; flex-wrap:wrap;">
                {{-- Registrar abono --}}
                <details>
                    <summary class="btn small">＋ Abono</summary>
                    <form method="post" action="{{ route('finances.payments.store', $expense) }}" class="inline" style="margin-top:10px; gap:8px; flex-wrap:wrap; align-items:end;">
                        @csrf
                        <div><label class="small">Monto</label><input name="amount" type="number" step="0.01" min="0.01" required style="width:130px;"></div>
                        <div><label class="small">Fecha</label><input name="paid_on" type="date" value="{{ now()->toDateString() }}" style="width:160px;"></div>
                        <div><label class="small">Método</label><input name="method" placeholder="Efectivo, transfer." style="width:160px;"></div>
                        <button class="btn small" type="submit">Registrar abono</button>
                    </form>
                </details>

                {{-- Ver abonos --}}
                <details>
                    <summary class="btn small secondary">📜 Abonos ({{ $expense->payments->count() }})</summary>
                    <div style="margin-top:10px;">
                        @forelse ($expense->payments as $p)
                            <div class="inline" style="justify-content:space-between; padding:5px 0; border-bottom:1px solid #f3eefa; gap:8px;">
                                <span class="small">{{ $p->paid_on?->format('d/m/Y') ?? '—' }} · <strong class="money">{{ $m($p->amount) }}</strong>@if ($p->method) · {{ $p->method }}@endif</span>
                                <form method="post" action="{{ route('finances.payments.destroy', $p) }}"
                                    data-confirm-title="¿Eliminar este abono?" data-confirm-text="Se descontará del total pagado." data-confirm-button="Sí, eliminar" data-confirm-color="#d8527f">
                                    @csrf @method('delete')
                                    <button class="btn small danger" type="submit">✕</button>
                                </form>
                            </div>
                        @empty
                            <p class="small" style="margin:0; color:#9b8ab0;">Sin abonos todavía.</p>
                        @endforelse
                    </div>
                </details>

                {{-- Editar --}}
                <details>
                    <summary class="btn small secondary">Editar</summary>
                    <form method="post" action="{{ route('finances.expenses.update', $expense) }}" style="margin-top:10px;">
                        @csrf @method('put')
                        @include('finances._expense_fields', ['expense' => $expense, 'categories' => $categories])
                        <button class="btn small" type="submit" style="margin-top:8px;">Guardar cambios</button>
                    </form>
                </details>

                {{-- Eliminar --}}
                <form method="post" action="{{ route('finances.expenses.destroy', $expense) }}"
                    data-confirm-title="¿Eliminar el gasto {{ $expense->name }}?" data-confirm-text="Se ocultará junto con sus abonos. No se borra físicamente." data-confirm-button="Sí, eliminar" data-confirm-color="#d8527f" data-confirm-icon="warning">
                    @csrf @method('delete')
                    <button class="btn small danger" type="submit">Eliminar</button>
                </form>
            </div>
        </div>
    @empty
        <div class="card"><p class="small" style="margin:0;">Aún no hay gastos registrados. Agrega el primero arriba.</p></div>
    @endforelse

    {{-- ===== APOYOS DE PADRINOS ===== --}}
    <h2 class="section-tabs" style="font-size:18px; color:#43275b; margin:28px 0 8px;">Apoyos de padrinos</h2>

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
