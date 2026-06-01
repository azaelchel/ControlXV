@extends('layouts.app')

@section('title', 'Invitados')
@section('heading', 'Invitados')
@section('subheading', 'Módulo para registrar a las personas invitadas dentro de cada familia o grupo confirmado')

@section('content')
    <style>
        .cx-hero { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 18px; }
        .cx-hero .card { padding: 16px; display: flex; gap: 12px; align-items: center; }
        .cx-hero .ico { width: 42px; height: 42px; border-radius: 11px; display: grid; place-items: center; font-size: 18px; flex-shrink: 0; color: white; }
        .cx-hero .ico.purple { background: linear-gradient(135deg, #c693ea, #8f55be); }
        .cx-hero .ico.blue { background: linear-gradient(135deg, #92c2e8, #4a8cc9); }
        .cx-hero .ico.amber { background: linear-gradient(135deg, #f0c674, #c69440); }
        .cx-hero .ico.pink { background: linear-gradient(135deg, #f0a5c5, #d8527f); }
        .cx-hero .ico.green { background: linear-gradient(135deg, #aedca0, #5fa657); }
        .cx-hero .info .lbl { font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 700; color: var(--muted); }
        .cx-hero .info .val { font-size: 22px; font-weight: 800; line-height: 1; color: var(--text); margin-top: 4px; }

        .cx-pending-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px; }
        .cx-pending-stat { padding: 12px 14px; background: #fff3f7; border: 1px solid #f0c4d2; border-radius: 10px; }
        .cx-pending-stat.ok { background: #ecf7e9; border-color: #c6e4be; }
        .cx-pending-stat .lbl { font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 700; color: var(--muted); }
        .cx-pending-stat .val { font-size: 20px; font-weight: 800; color: #d8527f; margin-top: 4px; }
        .cx-pending-stat.ok .val { color: #5fa657; }

        .cx-filter-grid { display: grid; grid-template-columns: 1.5fr 1fr 1fr auto; gap: 12px; align-items: end; }
        .cx-filter-grid label { display: block; font-size: 12px; font-weight: 600; color: var(--muted); margin-bottom: 4px; }
        .cx-filter-grid input, .cx-filter-grid select { width: 100%; box-sizing: border-box; height: 42px; }
    </style>

    <div x-data="{ showCompanionModal: false, showEditCompanionModal: {{ $editingCompanion ? 'true' : 'false' }} }">

    <div class="card" style="margin-bottom: 18px; background: linear-gradient(135deg, var(--primary) 0%, #6b3b9e 100%); color: white; border: none;">
        <div class="inline" style="justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div>
                <div style="font-size: 12px; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 600;">Personas individuales</div>
                <h3 style="margin: 4px 0 0 0; font-size: 20px;">Registro de invitados</h3>
                <div style="font-size: 12px; opacity: 0.85; margin-top: 4px;">Solo familias con estatus Confirmado pueden recibir invitados</div>
            </div>
            <div class="inline" style="gap: 10px;">
                <button class="btn" style="background: white; color: var(--primary-dark);" type="button" @click="showCompanionModal = true">+ Dar de alta invitado</button>
                <a class="btn" style="background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.3);" href="{{ route('companions.export', request()->query()) }}">📥 Exportar reporte</a>
            </div>
        </div>
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

            <form method="post" action="{{ route('companions.store') }}" data-preserve-table="companions-table">
                @csrf
                <input type="hidden" name="return_to" value="{{ route('companions.index', request()->query()) }}#companions-table-section">
                @include('companions._batch_fields')
                <div class="inline" style="margin-top: 18px;">
                    <button class="btn" type="submit">Guardar invitados</button>
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
                    <a class="btn ghost" href="{{ route('companions.index', request()->except('edit')) }}#companions-table-section">Cerrar</a>
                </div>

                <form method="post" action="{{ route('companions.update', $editingCompanion) }}" data-preserve-table="companions-table">
                    @csrf
                    @method('put')
                    <input type="hidden" name="return_to" value="{{ route('companions.index', request()->except('edit')) }}#companions-table-section">
                    @include('companions._fields', ['companion' => $editingCompanion])
                    <div class="inline" style="margin-top: 18px;">
                        <button class="btn" type="submit">Guardar cambios</button>
                        <a class="btn secondary" href="{{ route('companions.index', request()->except('edit')) }}#companions-table-section">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="cx-hero">
        <div class="card">
            <div class="ico purple">📋</div>
            <div class="info"><div class="lbl">Registros</div><div class="val">{{ number_format($summary['records']) }}</div></div>
        </div>
        <div class="card">
            <div class="ico blue">👨‍👩‍👧</div>
            <div class="info"><div class="lbl">Familias</div><div class="val">{{ number_format($summary['guest_groups']) }}</div></div>
        </div>
        <div class="card">
            <div class="ico green">👤</div>
            <div class="info"><div class="lbl">Adultos</div><div class="val">{{ number_format($summary['adults']) }}</div></div>
        </div>
        <div class="card">
            <div class="ico amber">🧒</div>
            <div class="info"><div class="lbl">Adolescentes</div><div class="val">{{ number_format($summary['adolescents']) }}</div></div>
        </div>
        <div class="card">
            <div class="ico pink">👶</div>
            <div class="info"><div class="lbl">Niños</div><div class="val">{{ number_format($summary['children']) }}</div></div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 18px;">
        <div class="section-kicker">Control de faltantes</div>
        <h3 class="section-title">Confirmados pendientes por registrar</h3>
        <div class="small" style="margin-top: 6px; line-height: 1.6;">
            Compara cuántas personas confirmó cada familia contra los invitados ya capturados. Marca lo que falta y lo que sobra (si hubo error en el tipo).
        </div>

        <div style="margin-top: 14px;">
            <div class="cx-pending-grid">
                <div class="cx-pending-stat {{ $pendingSummary['groups'] == 0 ? 'ok' : '' }}">
                    <div class="lbl">Familias pendientes</div>
                    <div class="val">{{ number_format($pendingSummary['groups']) }}</div>
                </div>
                <div class="cx-pending-stat {{ $pendingSummary['people'] == 0 ? 'ok' : '' }}">
                    <div class="lbl">Personas faltantes</div>
                    <div class="val">{{ number_format($pendingSummary['people']) }}</div>
                </div>
                <div class="cx-pending-stat {{ $pendingSummary['adults'] == 0 ? 'ok' : '' }}">
                    <div class="lbl">Adultos por registrar</div>
                    <div class="val">{{ number_format($pendingSummary['adults']) }}</div>
                </div>
                <div class="cx-pending-stat {{ $pendingSummary['adolescents'] == 0 ? 'ok' : '' }}">
                    <div class="lbl">Adolescentes por registrar</div>
                    <div class="val">{{ number_format($pendingSummary['adolescents']) }}</div>
                </div>
                <div class="cx-pending-stat {{ $pendingSummary['children'] == 0 ? 'ok' : '' }}">
                    <div class="lbl">Niños por registrar</div>
                    <div class="val">{{ number_format($pendingSummary['children']) }}</div>
                </div>
            </div>

            @if ($pendingSummary['extra_people'] > 0)
                <div style="margin-top: 14px;">
                    <div class="small" style="margin-bottom: 8px; font-weight: 600; color: #d8527f;">⚠ Sobrantes o mal clasificados</div>
                    <div class="cx-pending-grid">
                        <div class="cx-pending-stat">
                            <div class="lbl">Personas sobrantes</div>
                            <div class="val">{{ number_format($pendingSummary['extra_people']) }}</div>
                        </div>
                        <div class="cx-pending-stat">
                            <div class="lbl">Adultos sobrantes</div>
                            <div class="val">{{ number_format($pendingSummary['extra_adults']) }}</div>
                        </div>
                        <div class="cx-pending-stat">
                            <div class="lbl">Adolescentes sobrantes</div>
                            <div class="val">{{ number_format($pendingSummary['extra_adolescents']) }}</div>
                        </div>
                        <div class="cx-pending-stat">
                            <div class="lbl">Niños sobrantes</div>
                            <div class="val">{{ number_format($pendingSummary['extra_children']) }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="table-wrap" style="margin-top: 18px; max-height: 320px; overflow: auto;">
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
        <form method="get" class="cx-filter-grid">
            <div>
                <label for="search">Buscar</label>
                <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nombre o grupo…">
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
            <div class="inline" style="gap: 6px;">
                <button class="btn" type="submit">Filtrar</button>
                <a class="btn secondary" href="{{ route('companions.index') }}">Limpiar</a>
                @if ($editingCompanion)
                    <a class="btn ghost" href="{{ route('companions.index', request()->except('edit')) }}">Cerrar edición</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card" id="companions-table-section">
        <div class="table-wrap">
            <table id="companions-table" data-datatable="companions">
                <thead>
                    <tr>
                        <th>Familia o grupo</th>
                        <th>Nombre del invitado</th>
                        <th>Tipo</th>
                        <th>Género</th>
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
                                    <a class="btn secondary icon-btn" href="{{ route('companions.index', $rowEditQuery) }}#companions-table-section" data-preserve-table="companions-table" title="Editar invitado" aria-label="Editar invitado">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                        </svg>
                                    </a>
                                    <form method="post" action="{{ route('companions.destroy', $companion) }}" data-preserve-table="companions-table"
                                        data-confirm-title="¿Desactivar este invitado?"
                                        data-confirm-text="El registro dejará de mostrarse en el listado actual."
                                        data-confirm-button="Sí, desactivar"
                                        data-confirm-color="#d8527f"
                                        data-confirm-icon="warning">
                                        @csrf
                                        @method('delete')
                                        <input type="hidden" name="return_to" value="{{ route('companions.index', request()->query()) }}#companions-table-section">
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

    <script>
        (() => {
            const builder = document.querySelector('[data-companion-batch-builder]');

            if (!builder) {
                return;
            }

            const profileSource = document.getElementById('companion-profiles-json');
            const profiles = profileSource ? JSON.parse(profileSource.textContent || '{}') : {};
            const select = builder.querySelector('[data-companion-group-select]');
            const rowsContainer = builder.querySelector('[data-companion-rows]');
            const emptyState = builder.querySelector('[data-companion-empty-state]');
            const summary = builder.querySelector('[data-companion-profile-summary]');
            const oldEntries = @json(old('entries', []));

            const availableTypes = @json($types);

            const buildRow = (slot, oldEntry = {}, index = 0) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'card';
                wrapper.style.padding = '16px';

                const sex = oldEntry.sex || '';
                const notes = oldEntry.notes || '';
                const name = oldEntry.name || '';
                const currentType = oldEntry.type || slot.type;
                const notesVisible = notes !== '';

                const typeOptions = availableTypes.map(t =>
                    `<option value="${t}" ${currentType === t ? 'selected' : ''}>${t}${t === slot.type ? ' (sugerido)' : ''}</option>`
                ).join('');

                wrapper.innerHTML = `
                    <div style="display:grid; grid-template-columns: 1.3fr .8fr 1fr auto; gap: 12px; align-items: end;">
                        <div>
                            <label>Nombre del invitado</label>
                            <input type="text" name="entries[${index}][name]" value="${name.replace(/"/g, '&quot;')}" placeholder="Captura el nombre" />
                        </div>
                        <div>
                            <label>Tipo</label>
                            <select name="entries[${index}][type]" data-type-select>
                                ${typeOptions}
                            </select>
                        </div>
                        <div>
                            <label>Género</label>
                            <select name="entries[${index}][sex]">
                                <option value="" ${sex === '' ? 'selected' : ''}>Sin definir</option>
                                <option value="Hombre" ${sex === 'Hombre' ? 'selected' : ''}>Hombre</option>
                                <option value="Mujer" ${sex === 'Mujer' ? 'selected' : ''}>Mujer</option>
                            </select>
                        </div>
                        <div>
                            <label style="opacity:0;">Más</label>
                            <button type="button" class="btn secondary small" data-toggle-notes>${notesVisible ? '−' : '+'}</button>
                        </div>
                    </div>
                    <div class="small" style="margin-top: 10px; color:#8a6aa8;" data-child-note>
                        Niño será considerado hasta 9 años de edad.
                    </div>
                    <div data-notes-row style="margin-top: 12px; display:${notesVisible ? 'block' : 'none'};">
                        <label>Observaciones</label>
                        <textarea name="entries[${index}][notes]" rows="2" placeholder="Opcional">${notes}</textarea>
                    </div>
                    <div class="small" style="margin-top: 10px; color:#8a6aa8;">
                        Registro sugerido: <strong>${slot.label}</strong>
                    </div>
                `;

                const typeSelect = wrapper.querySelector('[data-type-select]');
                const childNote = wrapper.querySelector('[data-child-note]');
                const updateChildNote = () => {
                    childNote.style.display = typeSelect.value === 'Niño' ? 'block' : 'none';
                };
                typeSelect.addEventListener('change', updateChildNote);
                updateChildNote();

                return wrapper;
            };

            const render = () => {
                const value = select.value;
                const profile = profiles[value];
                rowsContainer.innerHTML = '';

                if (!profile) {
                    summary.textContent = 'Selecciona una familia o grupo para ver cuántos invitados faltan por registrar.';
                    emptyState.style.display = '';
                    return;
                }

                summary.innerHTML = `
                    <strong>Confirmados:</strong> ${profile.confirmed_total} (${profile.expected_breakdown || 'sin detalle'})<br>
                    <strong>Ya registrados:</strong> ${profile.registered_total} (${profile.registered_breakdown || 'sin invitados registrados'})<br>
                    <strong>Pendientes:</strong> ${profile.missing_total} (${profile.missing_breakdown || 'sin pendientes'})
                `;

                if (!profile.pending_slots || profile.pending_slots.length === 0) {
                    emptyState.style.display = '';
                    emptyState.textContent = 'Esta familia o grupo ya no tiene registros pendientes por capturar.';
                    return;
                }

                emptyState.style.display = 'none';

                profile.pending_slots.forEach((slot, index) => {
                    rowsContainer.appendChild(buildRow(slot, oldEntries[index] || {}, index));
                });
            };

            select.addEventListener('change', render);
            rowsContainer.addEventListener('click', (event) => {
                const button = event.target.closest('[data-toggle-notes]');

                if (!button) {
                    return;
                }

                const card = button.closest('.card');
                const notesRow = card?.querySelector('[data-notes-row]');

                if (!notesRow) {
                    return;
                }

                const isHidden = notesRow.style.display === 'none';
                notesRow.style.display = isHidden ? 'block' : 'none';
                button.textContent = isHidden ? '−' : '+';
            });
            render();
        })();
    </script>
@endsection
