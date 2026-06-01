@extends('layouts.app')

@section('title', 'Enviar mensaje masivo')
@section('heading', 'Enviar mensaje masivo')
@section('subheading', 'Tres pasos: elige plantilla, filtra y selecciona familias, prepara mensajes')

@section('content')
    <style>
        .step-card { margin-bottom: 18px; border-left: 4px solid var(--primary); }
        .step-num { display: inline-flex; width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: white; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; }
        .step-head { display: flex; gap: 12px; align-items: center; margin-bottom: 6px; }
        .step-hint { font-size: 13px; color: var(--muted); margin: 0 0 14px 44px; }

        .template-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px; }
        .template-pick {
            display: block;
            padding: 14px;
            border: 2px solid #e6dff1;
            border-radius: 12px;
            cursor: pointer;
            background: #fff;
            transition: border-color 0.15s, background 0.15s;
            position: relative;
        }
        .template-pick:has(input:checked) { border-color: var(--primary); background: #faf6ff; }
        .template-pick .template-body { display: flex; gap: 10px; align-items: flex-start; }
        .template-pick input[type="radio"] { width: auto; margin: 4px 0 0 0; flex-shrink: 0; }
        .template-pick .template-content { flex: 1; min-width: 0; }
        .template-pick.inactive { background: #fafafa; opacity: 0.65; }

        .filter-grid {
            display: grid;
            grid-template-columns: 1.6fr 1fr 1fr 1fr auto;
            gap: 12px;
            align-items: end;
            margin-bottom: 14px;
        }
        .filter-grid > div { min-width: 0; }
        .filter-grid label { display: block; font-size: 12px; font-weight: 600; color: var(--muted); margin-bottom: 4px; }
        .filter-grid input, .filter-grid select { width: 100%; box-sizing: border-box; height: 42px; }
        .filter-grid .phone-toggle { display: inline-flex; align-items: center; gap: 6px; height: 42px; padding: 0 12px; }
        .filter-grid input[type="checkbox"] { width: auto; height: auto; }

        .selection-bar {
            display: flex; gap: 10px; align-items: center;
            margin-bottom: 14px; padding: 12px 16px;
            background: #faf6ff; border-radius: 10px;
        }
        .selection-counter { margin-left: auto; font-weight: 700; color: var(--primary-dark); font-size: 15px; }

        .guest-table { min-width: 100%; }
        .guest-table thead { position: sticky; top: 0; background: white; z-index: 1; box-shadow: 0 1px 0 #e6dff1; }
        .guest-table input[type="checkbox"] { width: 18px; height: 18px; margin: 0; }
        .guest-table tr:hover td { background: #faf6ff; }
        .guest-table .row-disabled { opacity: 0.45; }
    </style>

    <form method="post" action="{{ route('message-sends.prepare') }}" id="bulk-form">
        @csrf

        {{-- PASO 1: PLANTILLA --}}
        <div class="card step-card">
            <div class="step-head">
                <span class="step-num">1</span>
                <h3 class="section-title" style="margin: 0;">¿Qué mensaje vas a enviar?</h3>
            </div>
            <p class="step-hint">Selecciona la plantilla. Su contenido se mostrará con los datos de cada familia ya sustituidos.</p>

            <div class="template-grid">
                @foreach ($templates as $template)
                    <label class="template-pick {{ $template->active ? '' : 'inactive' }}">
                        <div class="template-body">
                            <input type="radio" name="message_template_id" value="{{ $template->id }}" @checked($defaultTemplateId === $template->id) required>
                            <div class="template-content">
                                <strong>{{ $template->name }}</strong>
                                @if (! $template->active)
                                    <span class="pill status-default" style="margin-left: 6px; font-size: 11px;">Inactiva</span>
                                @endif
                                @if ($template->kicker)
                                    <div class="small" style="margin-top: 2px;">{{ $template->kicker }}</div>
                                @endif
                                @if ($template->description)
                                    <p class="small" style="margin: 6px 0 0 0; line-height: 1.45;">{{ $template->description }}</p>
                                @endif
                                <div class="small" style="margin-top: 8px;">
                                    {{ $template->hasLinkPlaceholder() ? '🔗 Incluye link' : '✉️ Sin link' }}
                                </div>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('message_template_id')<div class="error" style="margin-top: 8px;">{{ $message }}</div>@enderror
        </div>

        {{-- PASO 2: SELECCIÓN --}}
        <div class="card step-card">
            <div class="step-head">
                <span class="step-num">2</span>
                <h3 class="section-title" style="margin: 0;">¿A qué familias?</h3>
            </div>
            <p class="step-hint">Usa los filtros para acotar la lista, luego marca con las casillas.</p>

            <div class="filter-grid">
                <div>
                    <label>Buscar por nombre</label>
                    <input type="text" id="filter-name" placeholder="Escribe parte del nombre…">
                </div>
                <div>
                    <label>Estatus</label>
                    <select id="filter-status">
                        <option value="">Todos</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Categoría</label>
                    <select id="filter-category">
                        <option value="">Todas</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Grupo</label>
                    <select id="filter-group">
                        <option value="">Todos</option>
                        @foreach ($groups as $g)
                            <option value="{{ $g }}">{{ $g }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="phone-toggle">
                    <input type="checkbox" id="filter-with-phone" checked>
                    <span class="small">Con teléfono</span>
                </label>
            </div>

            <div class="selection-bar">
                <button type="button" class="btn secondary" id="select-all">Seleccionar todas las visibles</button>
                <button type="button" class="btn secondary" id="deselect-all">Quitar selección</button>
                <span class="selection-counter">
                    <span id="selection-count">0</span> seleccionada(s) · <span id="visible-count">0</span> visible(s)
                </span>
            </div>

            <div class="table-wrap" style="max-height: 520px; overflow: auto;">
                <table class="guest-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>Familia</th>
                            <th>Estatus</th>
                            <th>Categoría</th>
                            <th>Grupo</th>
                            <th>Teléfono</th>
                            <th>Link actual</th>
                            <th>Último envío</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($guests as $guest)
                            @php
                                $hasPhone = ! empty($guest->phone);
                                $currentLink = $guest->currentPublicLink;
                                $linkLabel = 'Sin link';
                                $linkClass = 'status-default';
                                if ($currentLink) {
                                    if ($currentLink->responded_at) { $linkLabel = 'Respondió'; $linkClass = 'status-confirmado'; }
                                    elseif ($currentLink->closed_reason === 'cancelled') { $linkLabel = 'Cancelado'; $linkClass = 'status-no-asistira'; }
                                    elseif ($currentLink->isExpired()) { $linkLabel = 'Vencido'; $linkClass = 'status-no-asistira'; }
                                    elseif ($currentLink->opened_at) { $linkLabel = 'Abierto'; $linkClass = 'status-pendiente'; }
                                    else { $linkLabel = 'Activo'; $linkClass = 'status-considerado'; }
                                }
                                $lastSend = $guest->messageSends->first();
                            @endphp
                            <tr data-guest-row
                                class="{{ $hasPhone ? '' : 'row-disabled' }}"
                                data-name="{{ \Illuminate\Support\Str::lower($guest->name) }}"
                                data-status="{{ $guest->status }}"
                                data-category="{{ $guest->category }}"
                                data-group="{{ $guest->group_name }}"
                                data-with-phone="{{ $hasPhone ? '1' : '0' }}">
                                <td><input type="checkbox" name="guest_ids[]" value="{{ $guest->id }}" data-guest-check {{ $hasPhone ? '' : 'disabled' }}></td>
                                <td><strong>{{ $guest->name }}</strong></td>
                                <td class="small">{{ $guest->status }}</td>
                                <td class="small">{{ $guest->category }}</td>
                                <td class="small">{{ $guest->group_name }}</td>
                                <td class="small">
                                    @if ($hasPhone)
                                        {{ $guest->phone }}
                                    @else
                                        <span style="color: var(--danger);">Sin teléfono</span>
                                    @endif
                                </td>
                                <td><span class="pill {{ $linkClass }}">{{ $linkLabel }}</span></td>
                                <td>
                                    @if ($lastSend)
                                        <div class="small"><strong>{{ $lastSend->template?->name ?? '—' }}</strong></div>
                                        <div class="small">{{ $lastSend->sent_at?->format('d/m/Y') }}</div>
                                    @else
                                        <span class="small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @error('guest_ids')<div class="error" style="margin-top: 8px;">{{ $message }}</div>@enderror
        </div>

        {{-- PASO 3: PREPARAR --}}
        <div class="card step-card">
            <div class="step-head">
                <span class="step-num">3</span>
                <h3 class="section-title" style="margin: 0;">Preparar mensajes</h3>
            </div>
            <p class="step-hint">Vas a generar los mensajes para <strong id="final-count">0</strong> familia(s). En la siguiente pantalla cada una tendrá su mensaje listo para copiar.</p>

            <div class="inline" style="gap: 10px; margin-left: 44px;">
                <a href="{{ route('message-sends.index') }}" class="btn secondary">Cancelar</a>
                <button class="btn" type="submit" id="submit-btn" disabled style="font-size: 15px;">Preparar mensajes →</button>
            </div>
        </div>
    </form>

    <script>
        (function () {
            const fName = document.getElementById('filter-name');
            const fStatus = document.getElementById('filter-status');
            const fCategory = document.getElementById('filter-category');
            const fGroup = document.getElementById('filter-group');
            const fPhone = document.getElementById('filter-with-phone');
            const selectAll = document.getElementById('select-all');
            const deselectAll = document.getElementById('deselect-all');
            const countLbl = document.getElementById('selection-count');
            const visLbl = document.getElementById('visible-count');
            const finalLbl = document.getElementById('final-count');
            const submitBtn = document.getElementById('submit-btn');
            const rows = Array.from(document.querySelectorAll('[data-guest-row]'));
            const checks = Array.from(document.querySelectorAll('[data-guest-check]'));

            function applyFilters() {
                const nameQ = fName.value.toLowerCase().trim();
                const status = fStatus.value;
                const cat = fCategory.value;
                const group = fGroup.value;
                const onlyP = fPhone.checked;
                let visible = 0;
                rows.forEach(row => {
                    const match = (!nameQ || row.dataset.name.includes(nameQ))
                               && (!status || row.dataset.status === status)
                               && (!cat || row.dataset.category === cat)
                               && (!group || row.dataset.group === group)
                               && (!onlyP || row.dataset.withPhone === '1');
                    row.style.display = match ? '' : 'none';
                    if (match) visible++;
                });
                visLbl.textContent = visible;
            }
            function visibleChecks() {
                return checks.filter(c => c.closest('tr').style.display !== 'none' && !c.disabled);
            }
            function updateCount() {
                const n = checks.filter(c => c.checked).length;
                countLbl.textContent = n;
                finalLbl.textContent = n;
                submitBtn.disabled = n === 0;
            }
            [fName].forEach(el => el.addEventListener('input', applyFilters));
            [fStatus, fCategory, fGroup, fPhone].forEach(el => el.addEventListener('change', applyFilters));
            selectAll.addEventListener('click', () => { visibleChecks().forEach(c => c.checked = true); updateCount(); });
            deselectAll.addEventListener('click', () => { checks.forEach(c => c.checked = false); updateCount(); });
            checks.forEach(c => c.addEventListener('change', updateCount));

            applyFilters();
            updateCount();
        })();
    </script>
@endsection
