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
@include('finances._assets')

    <div class="card ministrip" style="margin-bottom:16px;">
        <div class="it">Costo total <b class="money">{{ $m($totals['cost']) }}</b></div>
        <div class="it">Pagado <b class="money" style="color:#3f9e6b;">{{ $m($totals['paid']) }}</b></div>
        <div class="it">Falta pagar <b class="money" style="color:#c0392b;">{{ $m($totals['to_pay']) }}</b></div>
    </div>

    <div class="ftoolbar">
        <div class="filters">
            <input type="search" class="search" placeholder="🔍 Buscar concepto, proveedor…" data-table-filter="expenses-table">
            <select data-status-filter="expenses-table">
                <option value="">Todos los estados</option>
                <option value="Pendiente">Pendiente</option>
                <option value="Parcial">Parcial</option>
                <option value="Pagado">Pagado</option>
                <option value="Vencido">Vencido</option>
            </select>
        </div>
        <button class="btn" type="button" data-modal-open="new-expense">＋ Nuevo gasto</button>
    </div>

    <div class="ftable-wrap">
        <table class="ftable" id="expenses-table">
            <thead>
                <tr>
                    <th data-sort="text">Concepto</th>
                    <th data-sort="num" class="num">Total</th>
                    <th data-sort="num" class="num">Pagado</th>
                    <th data-sort="num" class="num">Resta</th>
                    <th data-sort="text">Estado</th>
                    <th data-sort="num">Vence</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($expenses as $expense)
                    @php
                        $paid = $expense->paidAmount();
                        $rem = $expense->remaining();
                        $pct = (float) $expense->total_amount > 0 ? (int) round(($paid / (float) $expense->total_amount) * 100) : 0;
                        $st = $expense->status();
                    @endphp
                    <tr data-status="{{ $st }}">
                        <td>
                            <strong style="color:#43275b;">{{ $expense->name }}</strong>
                            <div class="sub">{{ $expense->category ?: 'Sin categoría' }}@if ($expense->provider) · {{ $expense->provider }}@endif</div>
                        </td>
                        <td class="num money" data-val="{{ $expense->total_amount }}">{{ $m($expense->total_amount) }}</td>
                        <td class="num money" data-val="{{ $paid }}" style="color:#3f9e6b;">{{ $m($paid) }}</td>
                        <td class="num money" data-val="{{ $rem }}" style="color:#c0392b;">{{ $m($rem) }}</td>
                        <td><span class="pill {{ $statusClass($st) }}">{{ $st }}</span><div class="fbar" style="width:80px; margin-top:5px;"><span class="mid" style="width:{{ $pct }}%;"></span></div></td>
                        <td data-val="{{ $expense->due_date?->format('Y-m-d') ?? '' }}">{{ $expense->due_date?->format('d/m/Y') ?? '—' }}</td>
                        <td>
                            <div class="rowact">
                                <button class="btn small secondary" type="button" data-modal-open="payments-{{ $expense->id }}">Abonos ({{ $expense->payments->count() }})</button>
                                <button class="btn small secondary" type="button" data-modal-open="edit-expense-{{ $expense->id }}">Editar</button>
                                <form method="post" action="{{ route('finances.expenses.destroy', $expense) }}"
                                    data-confirm-title="¿Eliminar el gasto {{ $expense->name }}?" data-confirm-text="Se ocultará junto con sus abonos. No se borra físicamente." data-confirm-button="Sí, eliminar" data-confirm-color="#d8527f" data-confirm-icon="warning">
                                    @csrf @method('delete')
                                    <button class="btn small danger" type="submit">✕</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="ftable-empty">Aún no hay gastos. Agrega el primero con “＋ Nuevo gasto”.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ===== Modales ===== --}}

    {{-- Nuevo gasto --}}
    <div class="modal" id="new-expense">
        <div class="modal-box">
            <div class="modal-head"><h3>Nuevo gasto</h3><button class="modal-x" type="button" data-modal-close>&times;</button></div>
            <form method="post" action="{{ route('finances.expenses.store') }}">
                @csrf
                @include('finances._expense_fields', ['expense' => null, 'categories' => $categories])
                <div class="modal-actions">
                    <button class="btn secondary" type="button" data-modal-close>Cancelar</button>
                    <button class="btn" type="submit">Guardar gasto</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($expenses as $expense)
        {{-- Editar gasto --}}
        <div class="modal" id="edit-expense-{{ $expense->id }}">
            <div class="modal-box">
                <div class="modal-head"><h3>Editar gasto</h3><button class="modal-x" type="button" data-modal-close>&times;</button></div>
                <form method="post" action="{{ route('finances.expenses.update', $expense) }}">
                    @csrf @method('put')
                    @include('finances._expense_fields', ['expense' => $expense, 'categories' => $categories])
                    <div class="modal-actions">
                        <button class="btn secondary" type="button" data-modal-close>Cancelar</button>
                        <button class="btn" type="submit">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Abonos --}}
        <div class="modal" id="payments-{{ $expense->id }}">
            <div class="modal-box lg">
                <div class="modal-head"><h3>Abonos · {{ $expense->name }}</h3><button class="modal-x" type="button" data-modal-close>&times;</button></div>

                <div class="ministrip" style="padding:0 0 14px; gap:20px;">
                    <div class="it">Total <b class="money">{{ $m($expense->total_amount) }}</b></div>
                    <div class="it">Pagado <b class="money" style="color:#3f9e6b;">{{ $m($expense->paidAmount()) }}</b></div>
                    <div class="it">Resta <b class="money" style="color:#c0392b;">{{ $m($expense->remaining()) }}</b></div>
                </div>

                <form method="post" action="{{ route('finances.payments.store', $expense) }}">
                    @csrf
                    <div class="grid2">
                        <div class="field"><label>Monto del abono</label><input name="amount" type="number" step="0.01" min="0.01" required></div>
                        <div class="field"><label>Fecha</label><input name="paid_on" type="date" value="{{ now()->toDateString() }}"></div>
                        <div class="field"><label>Método (opcional)</label><input name="method" placeholder="Efectivo, transferencia…"></div>
                        <div class="field" style="display:flex; align-items:end;"><button class="btn" type="submit" style="width:100%;">Registrar abono</button></div>
                    </div>
                </form>

                <h4 style="margin:14px 0 8px; color:#6b5a7e;">Historial de abonos</h4>
                @forelse ($expense->payments as $p)
                    <div class="inline" style="justify-content:space-between; padding:8px 0; border-bottom:1px solid #f3eefa; gap:8px;">
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
        </div>
    @endforeach
@endsection
