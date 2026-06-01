@extends('layouts.app')

@section('title', 'Familias o grupos')
@section('heading', 'Familias o grupos')
@section('subheading', 'Módulo principal para dar de alta familias o grupos, revisar su información, actualizar estatus y exportar el listado cuando haga falta.')

@section('content')
    <style>
        .gx-hero { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 18px; }
        .gx-hero .card { padding: 16px; display: flex; gap: 12px; align-items: center; }
        .gx-hero .ico { width: 42px; height: 42px; border-radius: 11px; display: grid; place-items: center; font-size: 18px; flex-shrink: 0; color: white; }
        .gx-hero .ico.purple { background: linear-gradient(135deg, #c693ea, #8f55be); }
        .gx-hero .ico.blue { background: linear-gradient(135deg, #92c2e8, #4a8cc9); }
        .gx-hero .ico.amber { background: linear-gradient(135deg, #f0c674, #c69440); }
        .gx-hero .ico.green { background: linear-gradient(135deg, #aedca0, #5fa657); }
        .gx-hero .info .lbl { font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 700; color: var(--muted); }
        .gx-hero .info .val { font-size: 22px; font-weight: 800; line-height: 1; color: var(--text); margin-top: 4px; }

        .gx-filter-grid { display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr auto; gap: 12px; align-items: end; }
        .gx-filter-grid label { display: block; font-size: 12px; font-weight: 600; color: var(--muted); margin-bottom: 4px; }
        .gx-filter-grid input, .gx-filter-grid select { width: 100%; box-sizing: border-box; height: 42px; }
    </style>

    <div x-data="{ showGuestModal: false, showEditGuestModal: {{ $editingGuest ? 'true' : 'false' }} }">
    <div class="card" style="margin-bottom: 18px; background: linear-gradient(135deg, var(--primary) 0%, #6b3b9e 100%); color: white; border: none;">
        <div class="inline" style="justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div>
                <div style="font-size: 12px; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 600;">Gestión</div>
                <h3 style="margin: 4px 0 0 0; font-size: 20px;">Familias y grupos invitados</h3>
            </div>
            <div class="inline" style="gap: 10px;">
                <button class="btn" style="background: white; color: var(--primary-dark);" type="button" @click="showGuestModal = true">+ Agregar familia</button>
                <a class="btn" style="background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.3);" href="{{ route('guests.export', request()->query()) }}" data-filter-export="#guest-filters-form" data-datatable-context="guests">📥 Exportar reporte</a>
            </div>
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

            <form method="post" action="{{ route('guests.store') }}" data-preserve-table="guests-table">
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

                <form method="post" action="{{ route('guests.update', $editingGuest) }}" data-preserve-table="guests-table">
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

    <div class="gx-hero">
        <div class="card">
            <div class="ico purple">📋</div>
            <div class="info">
                <div class="lbl">Registros</div>
                <div class="val">{{ number_format($summary['records']) }}</div>
            </div>
        </div>
        <div class="card">
            <div class="ico blue">👤</div>
            <div class="info">
                <div class="lbl">Adultos</div>
                <div class="val">{{ number_format($summary['adults']) }}</div>
            </div>
        </div>
        <div class="card">
            <div class="ico amber">🧒</div>
            <div class="info">
                <div class="lbl">Adolescentes y niños</div>
                <div class="val">{{ number_format($summary['adolescents'] + $summary['children']) }}</div>
            </div>
        </div>
        <div class="card">
            <div class="ico green">👥</div>
            <div class="info">
                <div class="lbl">Total personas</div>
                <div class="val">{{ number_format($summary['total_people']) }}</div>
            </div>
        </div>
    </div>

    <div class="grid cols-4" style="margin-bottom: 18px;" data-status-summary data-summary-total="{{ $summary['total_people'] }}">
        @foreach ($statusSummary as $status => $count)
            @php
                $statusClass = match ($status) {
                    'Confirmado' => 'status-confirmado',
                    'No asistirá' => 'status-no-asistira',
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
                <div class="value" data-category-net-key="{{ $category }}">{{ number_format($totals['without_rejected']) }}</div>
                <div class="small" style="margin-top: 6px;">Sin declinados</div>
                <div class="metric-subvalue">
                    <strong data-category-net-percent-key="{{ $category }}">{{ $summary['total_people'] > 0 ? number_format(($totals['without_rejected'] / $summary['total_people']) * 100, 1) : '0.0' }}%</strong>
                    <span>del total</span>
                </div>
                <div class="metric-net">
                    <span style="font-size: 11px;">Total general</span>
                    <strong data-category-key="{{ $category }}">{{ number_format($totals['total']) }}</strong>
                    <em data-category-percent-key="{{ $category }}">{{ $summary['total_people'] > 0 ? number_format(($totals['total'] / $summary['total_people']) * 100, 1) : '0.0' }}%</em>
                </div>
                <div style="margin-top: 10px;">
                    <span class="pill {{ $categoryClass }}">{{ $category }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card" style="margin-bottom: 18px;">
        <form method="get" class="gx-filter-grid" id="guest-filters-form">
            <div>
                <label for="search">Buscar</label>
                <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nombre, teléfono o padrino…">
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
                <label for="category">Categoría</label>
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
            <div class="inline" style="gap: 6px;">
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
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($guests as $guest)
                        @php
                            $statusClass = match ($guest->status) {
                                'Confirmado' => 'status-confirmado',
                                'No asistirá' => 'status-no-asistira',
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
                                <form id="{{ $quickFormId }}" method="post" action="{{ route('guests.quick-update', $guest) }}" data-ajax-submit data-autosave-form>
                                    @csrf
                                    @method('patch')
                                </form>
                                <div class="inline">
                                    <a class="btn secondary icon-btn" href="{{ route('guests.index', $rowEditQuery) }}#guests-table-section" data-preserve-table="guests-table" title="Editar familia o grupo" aria-label="Editar familia o grupo">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                        </svg>
                                    </a>
                                    <form method="post" action="{{ route('guests.destroy', $guest) }}" data-preserve-table="guests-table"
                                        data-confirm-title="¿Desactivar esta familia o grupo?"
                                        data-confirm-text="También se desactivarán sus invitados relacionados, si existen."
                                        data-confirm-button="Sí, desactivar"
                                        data-confirm-color="#d8527f"
                                        data-confirm-icon="warning">
                                        @csrf
                                        @method('delete')
                                        <input type="hidden" name="return_to" value="{{ route('guests.index', request()->query()) }}#guests-table-section">
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
                            <td colspan="9" class="empty">Aún no hay familias o grupos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>

@endsection
