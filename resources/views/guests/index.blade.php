@extends('layouts.app')

@section('title', 'Familias o grupos')
@section('heading', 'Familias o grupos')
@section('subheading', 'Módulo principal para dar de alta familias o grupos, revisar su información, actualizar estatus y exportar el listado cuando haga falta.')

@section('content')
    <div x-data="{ showGuestModal: false, showEditGuestModal: {{ $editingGuest ? 'true' : 'false' }} }">
    <div class="toolbar">
        <div class="inline">
            <button class="btn secondary" type="button" @click="showGuestModal = true">Agregar familia o grupo</button>
            <a class="btn" href="{{ route('guests.export', request()->query()) }}">Reporte de familias o grupos</a>
        </div>
    </div>

    <div class="modal-overlay" x-cloak x-show="showGuestModal" x-transition.opacity @keydown.escape.window="showGuestModal = false" @click.self="showGuestModal = false">
        <div class="modal-panel">
            <div class="inline" style="justify-content: space-between; margin-bottom: 18px;">
                <div>
                    <div class="section-kicker">Alta rápida en modal</div>
                    <h3 class="section-title">Nueva familia o grupo</h3>
                </div>
                <button class="btn ghost" type="button" @click="showGuestModal = false">Cerrar</button>
            </div>

            <form method="post" action="{{ route('guests.store') }}">
                @csrf
                <input type="hidden" name="return_to" value="{{ route('guests.index', request()->query()) }}#guests-table-section">
                @include('guests._fields', ['guest' => new \App\Models\Guest()])
                <div class="inline" style="margin-top: 18px;">
                    <button class="btn" type="submit">Guardar familia o grupo</button>
                    <button class="btn secondary" type="button" @click="showGuestModal = false">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    @if ($editingGuest)
        <div class="modal-overlay" x-cloak x-show="showEditGuestModal" x-transition.opacity @keydown.escape.window="showEditGuestModal = false" @click.self="showEditGuestModal = false">
            <div class="modal-panel">
                <div class="inline" style="justify-content: space-between; margin-bottom: 18px;">
                    <div>
                        <div class="section-kicker">Edición en modal</div>
                        <h3 class="section-title">Editar familia o grupo</h3>
                    </div>
                    <a class="btn ghost" href="{{ route('guests.index', request()->except('edit')) }}#guests-table-section">Cerrar</a>
                </div>

                <form method="post" action="{{ route('guests.update', $editingGuest) }}">
                    @csrf
                    @method('put')
                    <input type="hidden" name="return_to" value="{{ route('guests.index', request()->except('edit')) }}#guests-table-section">
                    @include('guests._fields', ['guest' => $editingGuest])
                    <div class="inline" style="margin-top: 18px;">
                        <button class="btn" type="submit">Guardar cambios</button>
                        <a class="btn secondary" href="{{ route('guests.index', request()->except('edit')) }}#guests-table-section">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="grid cols-4" style="margin-bottom: 18px;">
        <div class="card metric">
            <div class="label">Registros</div>
            <div class="value">{{ number_format($summary['records']) }}</div>
        </div>
        <div class="card metric">
            <div class="label">Adultos</div>
            <div class="value">{{ number_format($summary['adults']) }}</div>
        </div>
        <div class="card metric">
            <div class="label">Adolescentes y niños</div>
            <div class="value">{{ number_format($summary['adolescents'] + $summary['children']) }}</div>
        </div>
        <div class="card metric">
            <div class="label">Total personas</div>
            <div class="value">{{ number_format($summary['total_people']) }}</div>
        </div>
    </div>

    <div class="grid cols-4" style="margin-bottom: 18px;" data-status-summary data-summary-total="{{ $summary['total_people'] }}">
        @foreach ($statusSummary as $status => $count)
            @php
                $statusClass = match ($status) {
                    'Confirmado' => 'status-confirmado',
                    'Rechazado' => 'status-rechazado',
                    'Considerado' => 'status-considerado',
                    'Invitacion Enviada' => 'status-invitacion-enviada',
                    'Pendiente' => 'status-pendiente',
                    'No contesto' => 'status-no-contesto',
                    'Por definir' => 'status-por-definir',
                    default => 'status-default',
                };
            @endphp
            <div
                class="card metric status-summary-card"
                data-status-card
                data-status-value="{{ $status }}"
                role="button"
                tabindex="0"
                aria-pressed="false">
                <div class="label">{{ $status }}</div>
                <div class="value" data-status-key="{{ $status }}">{{ number_format($count) }}</div>
                <div class="small" style="margin-top: 6px;">Invitados</div>
                <div class="metric-subvalue">
                    <strong data-status-percent-key="{{ $status }}">{{ $summary['total_people'] > 0 ? number_format(($count / $summary['total_people']) * 100, 1) : '0.0' }}%</strong>
                    <span>del total</span>
                </div>
                <div style="margin-top: 10px;">
                    <span class="pill {{ $statusClass }}">{{ $status }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid cols-2" style="margin-bottom: 18px;" data-category-summary data-summary-total="{{ $summary['total_people'] }}">
        @foreach ($categorySummary as $category => $totals)
            @php
                $categoryClass = $category === 'Real' ? 'category-real' : 'category-probable';
            @endphp
            <div
                class="card metric category-summary-card"
                data-category-card
                data-category-value="{{ $category }}"
                role="button"
                tabindex="0"
                aria-pressed="false">
                <div class="label">{{ $category }}</div>
                <div class="value" data-category-key="{{ $category }}">{{ number_format($totals['total']) }}</div>
                <div class="small" style="margin-top: 6px;">Invitados</div>
                <div class="metric-subvalue">
                    <strong data-category-percent-key="{{ $category }}">{{ $summary['total_people'] > 0 ? number_format(($totals['total'] / $summary['total_people']) * 100, 1) : '0.0' }}%</strong>
                    <span>del total</span>
                </div>
                <div class="metric-net">
                    <span>Sin rechazados</span>
                    <strong data-category-net-key="{{ $category }}">{{ number_format($totals['without_rejected']) }}</strong>
                    <em data-category-net-percent-key="{{ $category }}">{{ $summary['total_people'] > 0 ? number_format(($totals['without_rejected'] / $summary['total_people']) * 100, 1) : '0.0' }}%</em>
                </div>
                <div style="margin-top: 10px;">
                    <span class="pill {{ $categoryClass }}">{{ $category }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card" style="margin-bottom: 18px;">
        <form method="get" class="form-grid">
            <div>
                <label for="search">Buscar</label>
                <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nombre, telefono o padrino">
            </div>
            <div>
                <label for="group_name">Grupo</label>
                <select id="group_name" name="group_name">
                    <option value="">Todos</option>
                    @foreach ($options['groups'] as $value)
                        <option value="{{ $value }}" @selected(($filters['group_name'] ?? '') === $value)>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="category">Categoria</label>
                <select id="category" name="category">
                    <option value="">Todas</option>
                    @foreach ($options['categories'] as $value)
                        <option value="{{ $value }}" @selected(($filters['category'] ?? '') === $value)>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status">Estatus</label>
                <select id="status" name="status">
                    <option value="">Todos</option>
                    @foreach ($options['statuses'] as $value)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="full inline">
                <button class="btn" type="submit">Filtrar</button>
                <a class="btn secondary" href="{{ route('guests.index') }}">Limpiar</a>
                @if ($editingGuest)
                    <a class="btn ghost" href="{{ route('guests.index', request()->except('edit')) }}">Cerrar edición</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card" id="guests-table-section">
        <div class="inline" style="justify-content: space-between; margin-bottom: 12px;">
            <div>
                <h3 style="margin: 0 0 4px;">Listado de familias o grupos</h3>
            </div>
            <div class="small">Puedes mostrar hasta 500 registros si lo necesitas.</div>
        </div>

        <div class="table-wrap">
            <table id="guests-table" data-datatable="guests">
                <thead>
                    <tr>
                        <th>Grupo</th>
                        <th>Nombre</th>
                        <th>Categoria</th>
                        <th>Estatus</th>
                        <th>Telefono</th>
                        <th>Conteo</th>
                        <th>Total</th>
                        <th>Padrino</th>
                        <th>Seguimiento</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($guests as $guest)
                        @php
                            $statusClass = match ($guest->status) {
                                'Confirmado' => 'status-confirmado',
                                'Rechazado' => 'status-rechazado',
                                'Considerado' => 'status-considerado',
                                'Invitacion Enviada' => 'status-invitacion-enviada',
                                'Pendiente' => 'status-pendiente',
                                'No contesto' => 'status-no-contesto',
                                'Por definir' => 'status-por-definir',
                                default => 'status-default',
                            };
                            $rowEditQuery = array_merge(request()->query(), ['edit' => $guest->id]);
                            $quickFormId = 'quick-update-'.$guest->id;
                        @endphp
                        <tr data-status-current="{{ $guest->status }}" data-category-current="{{ $guest->category }}">
                            <td>{{ $guest->group_name }}</td>
                            <td>{{ $guest->display_name }}</td>
                            <td>
                                <select
                                    form="{{ $quickFormId }}"
                                    name="category"
                                    class="inline-edit-select"
                                    data-native-select
                                    data-inline-tone="category">
                                    @foreach ($options['categories'] as $value)
                                        <option value="{{ $value }}" @selected($guest->category === $value)>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select
                                    form="{{ $quickFormId }}"
                                    name="status"
                                    class="inline-edit-select"
                                    data-native-select
                                    data-inline-tone="status">
                                    @foreach ($options['statuses'] as $value)
                                        <option value="{{ $value }}" @selected($guest->status === $value)>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input
                                    form="{{ $quickFormId }}"
                                    type="text"
                                    name="phone"
                                    class="inline-edit-input"
                                    value="{{ $guest->phone }}"
                                    placeholder="Sin teléfono">
                            </td>
                            <td>{{ $guest->adults }}/{{ $guest->adolescents }}/{{ $guest->children }}</td>
                            <td>{{ $guest->total_people }}</td>
                            <td>{{ $guest->sponsor ?: '—' }}</td>
                            <td>
                                <div class="small">2m: {{ $guest->whatsapp_2_months ?: '—' }}</div>
                                <div class="small">1m: {{ $guest->whatsapp_1_month ?: '—' }}</div>
                                <div class="small">15d: {{ $guest->whatsapp_15_days ?: '—' }}</div>
                            </td>
                            <td>
                                <form id="{{ $quickFormId }}" method="post" action="{{ route('guests.quick-update', $guest) }}" data-ajax-submit data-autosave-form>
                                    @csrf
                                    @method('patch')
                                </form>
                                <div class="inline">
                                    <a class="btn success icon-btn" href="{{ \Illuminate\Support\Facades\URL::signedRoute('guest-review.show', ['guest' => $guest]) }}" target="_blank" rel="noopener" title="Abrir enlace público de revisión" aria-label="Abrir enlace público de revisión">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M15 3h6v6"/>
                                            <path d="M10 14 21 3"/>
                                            <path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/>
                                        </svg>
                                    </a>
                                    <a class="btn secondary icon-btn" href="{{ route('guests.index', $rowEditQuery) }}#guests-table-section" title="Editar familia o grupo" aria-label="Editar familia o grupo">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                        </svg>
                                    </a>
                                    <form method="post" action="{{ route('guests.destroy', $guest) }}"
                                        data-confirm-title="¿Desactivar esta familia o grupo?"
                                        data-confirm-text="También se desactivarán sus invitados relacionados, si existen."
                                        data-confirm-button="Sí, desactivar"
                                        data-confirm-color="#d8527f"
                                        data-confirm-icon="warning">
                                        @csrf
                                        @method('delete')
                                        <button class="btn danger icon-btn" type="submit" title="Desactivar familia o grupo" aria-label="Desactivar familia o grupo">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 6h18"/>
                                                <path d="M8 6V4h8v2"/>
                                                <path d="M19 6l-1 14H6L5 6"/>
                                                <path d="M10 11v6"/>
                                                <path d="M14 11v6"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="empty">Aún no hay familias o grupos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>
@endsection
