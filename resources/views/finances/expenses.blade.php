@extends('layouts.app')

@section('title', 'Gastos')
@section('heading', 'Gastos y proveedores')
@section('subheading', 'Lo que se contrata y se paga. Registra cada gasto, sus abonos y mira cuánto resta y a quién se le debe.')

@section('content')
@php
    $m = fn ($v) => '$' . number_format((float) $v, 2);
    $statusClass = fn ($s) => match ($s) {
        'Pagado' => 'status-confirmado',
        'Parcial' => 'status-pendiente',
        'Vencido' => 'status-no-asistira',
        default => 'status-default',
    };
@endphp
@include('finances._styles')

    <div class="finance-nav">
        <a class="btn secondary" href="{{ route('finances.index') }}">📊 Resumen</a>
        <a class="btn secondary" href="{{ route('finances.sponsors') }}">🎁 Padrinos</a>
        <a class="btn secondary" href="{{ route('finances.own') }}">🙋 Mis aportaciones</a>
    </div>

    <div class="card ministrip" style="margin-bottom:16px;">
        <div class="it">Costo total <b class="money">{{ $m($totals['cost']) }}</b></div>
        <div class="it">Pagado <b class="money" style="color:#3f9e6b;">{{ $m($totals['paid']) }}</b></div>
        <div class="it">Falta pagar <b class="money" style="color:#c0392b;">{{ $m($totals['to_pay']) }}</b></div>
    </div>

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

                <details>
                    <summary class="btn small secondary">Editar</summary>
                    <form method="post" action="{{ route('finances.expenses.update', $expense) }}" style="margin-top:10px;">
                        @csrf @method('put')
                        @include('finances._expense_fields', ['expense' => $expense, 'categories' => $categories])
                        <button class="btn small" type="submit" style="margin-top:8px;">Guardar cambios</button>
                    </form>
                </details>

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
@endsection
