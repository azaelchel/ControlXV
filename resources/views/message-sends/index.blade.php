@extends('layouts.app')

@section('title', 'Envíos')
@section('heading', 'Envíos de mensajes')
@section('subheading', 'Historial completo de mensajes preparados y su respuesta correspondiente')

@section('content')
    <div class="grid cols-4" style="margin-bottom: 18px;">
        <div class="card metric">
            <div class="label">Enviados hoy</div>
            <div class="value">{{ number_format($stats['today']) }}</div>
            <div class="small" style="margin-top: 4px;">Mensajes preparados hoy</div>
        </div>
        <div class="card metric">
            <div class="label">Esta semana</div>
            <div class="value">{{ number_format($stats['week']) }}</div>
            <div class="small" style="margin-top: 4px;">Desde el lunes</div>
        </div>
        <div class="card metric">
            <div class="label">Respondieron hoy</div>
            <div class="value">{{ number_format($stats['responded_today']) }}</div>
            <div class="small" style="margin-top: 4px;">A través de su link</div>
        </div>
        <div class="card metric">
            <div class="label">Pendientes</div>
            <div class="value">{{ number_format($stats['pending']) }}</div>
            <div class="small" style="margin-top: 4px;">Familias con envío sin respuesta</div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 18px;">
        <div class="inline" style="justify-content: space-between; align-items: center;">
            <div>
                <div class="section-kicker">Acciones</div>
                <h3 class="section-title">Preparar un nuevo envío</h3>
                <p class="small" style="margin-top: 4px;">Selecciona una plantilla y las familias a las que quieres mandarla.</p>
            </div>
            <a href="{{ route('message-sends.create') }}" class="btn">+ Nuevo envío</a>
        </div>
    </div>

    <div class="card" style="margin-bottom: 18px;">
        <form method="get" action="{{ route('message-sends.index') }}" class="inline" style="gap: 14px; align-items: end; flex-wrap: wrap;">
            <div>
                <label>Estatus de la familia</label>
                <select name="status">
                    <option value="">Todos</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected($statusFilter === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Plantilla</label>
                <select name="template_id">
                    <option value="">Todas</option>
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}" @selected($templateFilter === $template->id)>{{ $template->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button class="btn secondary" type="submit">Filtrar</button>
                @if ($statusFilter || $templateFilter)
                    <a href="{{ route('message-sends.index') }}" class="btn secondary">Limpiar</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card">
        <div class="section-kicker">Histórico</div>
        <h3 class="section-title">Mensajes enviados</h3>
        <div class="table-wrap" style="margin-top: 14px;">
            <table style="min-width: 100%;">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Familia</th>
                        <th>Plantilla</th>
                        <th>Link</th>
                        <th>Estado del link</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sends as $send)
                        <tr>
                            <td>{{ $send->sent_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>
                                <strong>{{ $send->guest?->name ?? '—' }}</strong>
                                <div class="small">{{ $send->guest?->status }}</div>
                            </td>
                            <td>{{ $send->template?->name ?? '—' }}</td>
                            <td>
                                @if ($send->publicLink)
                                    <a href="{{ route('guest-review.show', ['guest' => $send->guest, 'token' => $send->publicLink->token]) }}" target="_blank" class="small">Abrir</a>
                                @else
                                    <span class="small">—</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $link = $send->publicLink;
                                    $stateLabel = '—';
                                    $stateClass = 'status-default';
                                    if ($link) {
                                        if ($link->responded_at) { $stateLabel = 'Respondido'; $stateClass = 'status-confirmado'; }
                                        elseif ($link->closed_reason === 'cancelled') { $stateLabel = 'Cancelado'; $stateClass = 'status-no-asistira'; }
                                        elseif ($link->isExpired()) { $stateLabel = 'Vencido'; $stateClass = 'status-no-asistira'; }
                                        elseif ($link->opened_at) { $stateLabel = 'Abierto'; $stateClass = 'status-pendiente'; }
                                        else { $stateLabel = 'Sin abrir'; $stateClass = 'status-considerado'; }
                                    }
                                @endphp
                                <span class="pill {{ $stateClass }}">{{ $stateLabel }}</span>
                            </td>
                            <td>
                                <form method="post" action="{{ route('message-sends.destroy', $send) }}"
                                    data-confirm-title="¿Eliminar este envío del histórico?"
                                    data-confirm-text="Solo se borra el registro de envío. El link y los datos de la familia quedan intactos."
                                    data-confirm-button="Sí, eliminar"
                                    data-confirm-color="#d8527f">
                                    @csrf
                                    @method('delete')
                                    <button class="btn danger" type="submit">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">No hay envíos todavía.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sends->hasPages())
            <div style="margin-top: 14px;">{{ $sends->links() }}</div>
        @endif
    </div>
@endsection
