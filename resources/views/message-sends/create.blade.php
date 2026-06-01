@extends('layouts.app')

@section('title', 'Nuevo envío')
@section('heading', 'Preparar envío masivo')
@section('subheading', 'Elige una plantilla y selecciona las familias a las que se les enviará')

@section('content')
    <form method="post" action="{{ route('message-sends.prepare') }}">
        @csrf

        <div class="card" style="margin-bottom: 18px;">
            <div class="section-kicker">Paso 1</div>
            <h3 class="section-title">Plantilla a usar</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px; margin-top: 14px;">
                @foreach ($templates as $template)
                    <label style="display: block; padding: 14px; border: 2px solid {{ $defaultTemplateId === $template->id ? 'var(--primary)' : '#e6dff1' }}; border-radius: 12px; cursor: pointer; background: {{ $template->active ? '#fff' : '#fafafa' }};">
                        <input type="radio" name="message_template_id" value="{{ $template->id }}" @checked($defaultTemplateId === $template->id) required style="margin-right: 6px;">
                        <strong>{{ $template->name }}</strong>
                        @if (! $template->active)
                            <span class="pill status-default" style="margin-left: 6px;">Inactiva</span>
                        @endif
                        @if ($template->kicker)
                            <div class="small" style="margin-top: 4px;">{{ $template->kicker }}</div>
                        @endif
                        @if ($template->description)
                            <p class="small" style="margin-top: 6px; line-height: 1.5;">{{ $template->description }}</p>
                        @endif
                        <div class="small" style="margin-top: 6px;">
                            {{ $template->includes_link ? 'Incluye link de confirmación' : 'Sin link' }}
                        </div>
                    </label>
                @endforeach
            </div>
            @error('message_template_id')<div class="error" style="margin-top: 8px;">{{ $message }}</div>@enderror
        </div>

        <div class="card" style="margin-bottom: 18px;">
            <div class="inline" style="justify-content: space-between; align-items: end; gap: 14px; flex-wrap: wrap;">
                <div>
                    <div class="section-kicker">Paso 2</div>
                    <h3 class="section-title">Familias destinatarias</h3>
                    <p class="small" style="margin-top: 4px;">Filtra y marca a quiénes quieres incluir en este envío.</p>
                </div>
                <div class="inline" style="gap: 10px;">
                    <input type="text" id="filter-name" placeholder="Buscar por nombre…" style="min-width: 240px;">
                    <select id="filter-status">
                        <option value="">Todos los estatus</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(in_array($status, $defaultStatuses, true))>{{ $status }}</option>
                        @endforeach
                    </select>
                    <label style="display: inline-flex; align-items: center; gap: 6px;">
                        <input type="checkbox" id="filter-with-phone" checked>
                        <span class="small">Con teléfono</span>
                    </label>
                </div>
            </div>

            <div class="inline" style="margin-top: 14px; gap: 10px;">
                <button type="button" class="btn secondary" id="select-all">Seleccionar todos los visibles</button>
                <button type="button" class="btn secondary" id="deselect-all">Quitar selección</button>
                <span class="small" id="selection-count" style="margin-left: auto;">0 seleccionadas</span>
            </div>

            <div class="table-wrap" style="margin-top: 14px; max-height: 540px; overflow: auto;">
                <table style="min-width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>Familia</th>
                            <th>Estatus</th>
                            <th>Teléfono</th>
                            <th>Link activo</th>
                            <th>Último envío</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($guests as $guest)
                            @php
                                $hasPhone = ! empty($guest->phone);
                                $currentLink = $guest->currentPublicLink;
                                $linkState = '—';
                                $linkClass = 'status-default';
                                if ($currentLink) {
                                    if ($currentLink->responded_at) { $linkState = 'Respondido'; $linkClass = 'status-confirmado'; }
                                    elseif ($currentLink->closed_reason === 'cancelled') { $linkState = 'Cancelado'; $linkClass = 'status-no-asistira'; }
                                    elseif ($currentLink->isExpired()) { $linkState = 'Vencido'; $linkClass = 'status-no-asistira'; }
                                    elseif ($currentLink->opened_at) { $linkState = 'Abierto'; $linkClass = 'status-pendiente'; }
                                    else { $linkState = 'Activo'; $linkClass = 'status-considerado'; }
                                }
                                $lastSend = $guest->messageSends->first();
                            @endphp
                            <tr data-guest-row
                                data-name="{{ \Illuminate\Support\Str::lower($guest->name) }}"
                                data-status="{{ $guest->status }}"
                                data-with-phone="{{ $hasPhone ? '1' : '0' }}">
                                <td>
                                    <input type="checkbox" name="guest_ids[]" value="{{ $guest->id }}" data-guest-check {{ $hasPhone ? '' : 'disabled' }}>
                                </td>
                                <td>
                                    <strong>{{ $guest->name }}</strong>
                                    <div class="small">{{ $guest->group_name }}</div>
                                </td>
                                <td>{{ $guest->status }}</td>
                                <td>
                                    @if ($hasPhone)
                                        {{ $guest->phone }}
                                    @else
                                        <span class="small" style="color: var(--danger);">Sin teléfono</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="pill {{ $linkClass }}">{{ $linkState }}</span>
                                </td>
                                <td>
                                    @if ($lastSend)
                                        <div class="small">{{ $lastSend->sent_at?->format('d/m/Y H:i') }}</div>
                                        <div class="small">{{ $lastSend->template?->name ?? '—' }}</div>
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

        <div class="card">
            <div class="inline" style="justify-content: space-between;">
                <a href="{{ route('message-sends.index') }}" class="btn secondary">Cancelar</a>
                <button class="btn" type="submit">Preparar envío →</button>
            </div>
        </div>
    </form>

    <script>
        (function () {
            const filterName     = document.getElementById('filter-name');
            const filterStatus   = document.getElementById('filter-status');
            const filterPhone    = document.getElementById('filter-with-phone');
            const selectAllBtn   = document.getElementById('select-all');
            const deselectAllBtn = document.getElementById('deselect-all');
            const countLabel     = document.getElementById('selection-count');
            const rows           = Array.from(document.querySelectorAll('[data-guest-row]'));
            const checks         = Array.from(document.querySelectorAll('[data-guest-check]'));

            function applyFilters() {
                const nameQ  = filterName.value.toLowerCase().trim();
                const status = filterStatus.value;
                const onlyP  = filterPhone.checked;
                rows.forEach(row => {
                    const matchName   = !nameQ   || row.dataset.name.includes(nameQ);
                    const matchStatus = !status  || row.dataset.status === status;
                    const matchPhone  = !onlyP   || row.dataset.withPhone === '1';
                    row.style.display = (matchName && matchStatus && matchPhone) ? '' : 'none';
                });
            }

            function visibleChecks() {
                return checks.filter(c => c.closest('tr').style.display !== 'none' && !c.disabled);
            }

            function updateCount() {
                const n = checks.filter(c => c.checked).length;
                countLabel.textContent = n + ' seleccionada' + (n === 1 ? '' : 's');
            }

            filterName.addEventListener('input', applyFilters);
            filterStatus.addEventListener('change', applyFilters);
            filterPhone.addEventListener('change', applyFilters);

            selectAllBtn.addEventListener('click', () => {
                visibleChecks().forEach(c => c.checked = true);
                updateCount();
            });

            deselectAllBtn.addEventListener('click', () => {
                checks.forEach(c => c.checked = false);
                updateCount();
            });

            checks.forEach(c => c.addEventListener('change', updateCount));

            applyFilters();
            updateCount();
        })();
    </script>
@endsection
