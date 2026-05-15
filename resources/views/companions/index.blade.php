@extends('layouts.app')

@section('title', 'Invitados')
@section('heading', 'Invitados')
@section('subheading', 'Módulo para registrar a las personas invitadas dentro de cada familia o grupo confirmado')

@php
    $editQuery = request()->query();
@endphp

@section('content')
    <div x-data="{ showCompanionModal: false, showEditCompanionModal: {{ $editingCompanion ? 'true' : 'false' }} }">
    <div class="toolbar">
        <div class="inline">
            <button class="btn secondary" type="button" @click="showCompanionModal = true">Dar de alta invitado</button>
            <a class="btn" href="{{ route('companions.export', request()->query()) }}">Reporte de invitados</a>
        </div>
        <div class="small">Solo se pueden registrar invitados para familias o grupos con estatus `Confirmado`.</div>
    </div>

    <div class="modal-overlay" x-cloak x-show="showCompanionModal" x-transition.opacity @keydown.escape.window="showCompanionModal = false" @click.self="showCompanionModal = false">
        <div class="modal-panel">
            <div class="inline" style="justify-content: space-between; margin-bottom: 18px;">
                <div>
                    <div class="section-kicker">Alta rápida en modal</div>
                    <h3 class="section-title">Nuevo invitado</h3>
                </div>
                <button class="btn ghost" type="button" @click="showCompanionModal = false">Cerrar</button>
            </div>

            <form method="post" action="{{ route('companions.store') }}">
                @csrf
                @include('companions._fields', ['companion' => new \App\Models\Companion()])
                <div class="inline" style="margin-top: 18px;">
                    <button class="btn" type="submit">Guardar invitado</button>
                    <button class="btn secondary" type="button" @click="showCompanionModal = false">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    @if ($editingCompanion)
        <div class="modal-overlay" x-cloak x-show="showEditCompanionModal" x-transition.opacity @keydown.escape.window="showEditCompanionModal = false" @click.self="showEditCompanionModal = false">
            <div class="modal-panel">
                <div class="inline" style="justify-content: space-between; margin-bottom: 18px;">
                    <div>
                        <div class="section-kicker">Edición en modal</div>
                        <h3 class="section-title">Editar invitado</h3>
                    </div>
                    <a class="btn ghost" href="{{ route('companions.index', request()->except('edit')) }}">Cerrar</a>
                </div>

                <form method="post" action="{{ route('companions.update', $editingCompanion) }}">
                    @csrf
                    @method('put')
                    @include('companions._fields', ['companion' => $editingCompanion])
                    <div class="inline" style="margin-top: 18px;">
                        <button class="btn" type="submit">Guardar cambios</button>
                        <a class="btn secondary" href="{{ route('companions.index', request()->except('edit')) }}">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="card" style="margin-bottom: 18px;">
        <div class="table-wrap">
            <div style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; width: 100%;">
                <div class="muted-box"><strong>Registros</strong><br>{{ $summary['records'] }}</div>
                <div class="muted-box"><strong>Familia / grupo</strong><br>{{ $summary['guest_groups'] }}</div>
                <div class="muted-box"><strong>Adultos</strong><br>{{ $summary['adults'] }}</div>
                <div class="muted-box"><strong>Adolescentes</strong><br>{{ $summary['adolescents'] }}</div>
                <div class="muted-box"><strong>Niños</strong><br>{{ $summary['children'] }}</div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 18px;">
        <div class="section-kicker">Control de faltantes</div>
        <h3 class="section-title">Confirmados pendientes por registrar</h3>
        <div class="small" style="margin-top: 10px;">
            Aquí se compara lo confirmado por cada familia o grupo contra los invitados ya registrados en este módulo. Si algo se capturó con tipo incorrecto, también se marca para que sepas qué falta y qué sobra.
        </div>

        <div class="table-wrap" style="margin-top: 16px;">
            <div style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; width: 100%;">
                <div class="muted-box"><strong>Familias / grupos pendientes</strong><br>{{ $pendingSummary['groups'] }}</div>
                <div class="muted-box"><strong>Invitados pendientes</strong><br>{{ $pendingSummary['people'] }}</div>
                <div class="muted-box"><strong>Adultos por registrar</strong><br>{{ $pendingSummary['adults'] }}</div>
                <div class="muted-box"><strong>Adolescentes por registrar</strong><br>{{ $pendingSummary['adolescents'] }}</div>
                <div class="muted-box"><strong>Niños por registrar</strong><br>{{ $pendingSummary['children'] }}</div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; width: 100%; margin-top: 12px;">
                <div class="muted-box"><strong>Invitados sobrantes o mal clasificados</strong><br>{{ $pendingSummary['extra_people'] }}</div>
                <div class="muted-box"><strong>Adultos sobrantes</strong><br>{{ $pendingSummary['extra_adults'] }}</div>
                <div class="muted-box"><strong>Adolescentes sobrantes</strong><br>{{ $pendingSummary['extra_adolescents'] }}</div>
                <div class="muted-box"><strong>Niños sobrantes</strong><br>{{ $pendingSummary['extra_children'] }}</div>
            </div>
        </div>

        <div class="table-wrap" style="margin-top: 18px;">
            <table style="min-width: 100%;">
                <thead>
                    <tr>
                        <th>Familia o grupo</th>
                        <th>Confirmados</th>
                        <th>Registrados</th>
                        <th>Faltan</th>
                        <th>Sobran</th>
                        <th>Detalle de ajuste</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendingRegistrations as $pending)
                        <tr>
                            <td>{{ $pending['group_name'] }}</td>
                            <td>{{ $pending['confirmed_total'] }}</td>
                            <td>{{ $pending['registered_total'] }}</td>
                            <td><strong>{{ $pending['missing_total'] }}</strong></td>
                            <td><strong>{{ $pending['extra_total'] }}</strong></td>
                            <td>
                                <div><strong>Confirmado:</strong> {{ $pending['expected_breakdown'] !== '' ? $pending['expected_breakdown'] : 'Sin personas confirmadas' }}</div>
                                <div><strong>Registrado:</strong> {{ $pending['registered_breakdown'] !== '' ? $pending['registered_breakdown'] : 'Sin invitados registrados' }}</div>
                                @if ($pending['missing_breakdown'] !== '')
                                    <div>Faltan: {{ $pending['missing_breakdown'] }}</div>
                                @endif
                                @if ($pending['extra_breakdown'] !== '')
                                    <div>Sobran: {{ $pending['extra_breakdown'] }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">No hay faltantes. Todo lo confirmado ya quedó registrado correctamente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="margin-bottom: 18px;">
        <form method="get" class="form-grid">
            <div>
                <label for="search">Buscar</label>
                <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nombre o grupo">
            </div>
            <div>
                <label for="group">Familia o grupo</label>
                <select id="group" name="group">
                    <option value="">Todos</option>
                    @foreach ($groups as $value)
                        <option value="{{ $value }}" @selected(($filters['group'] ?? '') === $value)>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="type">Tipo</label>
                <select id="type" name="type">
                    <option value="">Todos</option>
                    @foreach ($types as $value)
                        <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="inline" style="align-self: end;">
                <button class="btn" type="submit">Filtrar</button>
                <a class="btn secondary" href="{{ route('companions.index') }}">Limpiar</a>
                @if ($editingCompanion)
                    <a class="btn ghost" href="{{ route('companions.index', request()->except('edit')) }}">Cerrar edición</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table id="companions-table" data-datatable="companions">
                <thead>
                    <tr>
                        <th>Familia o grupo</th>
                        <th>Nombre del invitado</th>
                        <th>Tipo</th>
                        <th>Sexo</th>
                        <th>Observaciones</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($companions as $companion)
                        @php
                            $rowEditQuery = array_merge(request()->query(), ['edit' => $companion->id]);
                        @endphp
                        <tr>
                            <td>{{ $companion->invited_group }}</td>
                            <td>{{ $companion->name }}</td>
                            <td>{{ $companion->type ?: '—' }}</td>
                            <td>{{ $companion->sex ?: '—' }}</td>
                            <td>{{ $companion->notes ?: '—' }}</td>
                            <td>
                                <div class="inline">
                                    <a class="btn secondary icon-btn" href="{{ route('companions.index', $rowEditQuery) }}" title="Editar invitado" aria-label="Editar invitado">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                        </svg>
                                    </a>
                                    <form method="post" action="{{ route('companions.destroy', $companion) }}"
                                        data-confirm-title="¿Desactivar este invitado?"
                                        data-confirm-text="El registro dejará de mostrarse en el listado actual."
                                        data-confirm-button="Sí, desactivar"
                                        data-confirm-color="#d8527f"
                                        data-confirm-icon="warning">
                                        @csrf
                                        @method('delete')
                                        <button class="btn danger icon-btn" type="submit" title="Desactivar invitado" aria-label="Desactivar invitado">
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
                        <tr><td colspan="6" class="empty">No hay invitados cargados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>
@endsection
