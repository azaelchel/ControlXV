@extends('layouts.app')

@section('title', 'QR de accesos')
@section('heading', 'QR de accesos')
@section('subheading', 'Solo familias confirmadas. Carga la imagen QR que recibirá cada familia en su link personalizado.')

@section('content')
    <style>
        .access-toolbar { display: grid; grid-template-columns: 1.2fr 150px 170px auto; gap: 12px; align-items: end; margin: 0 0 14px; }
        .access-toolbar label { display: block; font-size: 12px; font-weight: 700; color: var(--muted); margin-bottom: 4px; }
        .access-toolbar input, .access-toolbar select { width: 100%; height: 40px; }
        .access-table { min-width: 1180px; }
        .access-table thead th { position: sticky; top: 0; z-index: 1; background: #fff; box-shadow: 0 1px 0 #e6dff1; }
        .access-table tbody tr:hover td { background: #faf6ff; }
        .access-table td { vertical-align: top; }
        .mesa-list { display: flex; gap: 5px; flex-wrap: wrap; }
        .mesa-chip { display: inline-flex; align-items: center; border-radius: 999px; padding: 3px 8px; background: #eef8f2; color: #256141; font-size: 11px; font-weight: 800; }
        .mesa-chip.warn { background: #fff1d8; color: #8b5b10; }
        .mesa-chip.empty { background: #eceff3; color: #55606f; }
        .split-detail { margin-top: 8px; padding: 8px 10px; border-radius: 10px; background: #fff8e8; border: 1px solid #ead8ad; color: #6f5218; font-size: 12px; line-height: 1.45; }
        .split-detail strong { color: var(--primary-dark); }
        .qr-upload-form { display: flex; align-items: center; gap: 8px; min-width: 300px; }
        .qr-file { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
        .qr-picker { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 36px; padding: 8px 12px; border-radius: 10px; border: 1px solid var(--border); background: #fff; color: var(--primary-dark); font-size: 12px; font-weight: 800; cursor: pointer; white-space: nowrap; box-shadow: 0 8px 18px rgba(122, 79, 168, .08); }
        .qr-picker:hover { border-color: var(--primary); background: #faf6ff; }
        .qr-file-name { max-width: 112px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--muted); font-size: 12px; }
        .qr-upload-form.has-file .qr-file-name { color: var(--primary-dark); font-weight: 700; }
        .qr-upload-form .btn[disabled] { opacity: .45; cursor: not-allowed; }
        .detail-modal-bg { position: fixed; inset: 0; background: rgba(25, 16, 35, .55); display: none; align-items: center; justify-content: center; z-index: 120; padding: 22px; }
        .detail-modal-bg.open { display: flex; }
        .detail-modal { width: min(760px, 100%); max-height: 92vh; overflow: auto; background: #fff; border-radius: 18px; border: 1px solid var(--border); box-shadow: 0 24px 70px rgba(39, 24, 55, .28); padding: 22px; }
        .detail-head { display: flex; justify-content: space-between; gap: 14px; align-items: start; margin-bottom: 14px; }
        .detail-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
        .detail-stat { background: #faf6ff; border: 1px solid #eee3f7; border-radius: 12px; padding: 10px 12px; }
        .detail-stat .label { color: var(--muted); font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; }
        .detail-stat .value { color: var(--primary-dark); font-size: 18px; font-weight: 900; margin-top: 2px; }
        .people-table { width: 100%; border-collapse: collapse; }
        .people-table th, .people-table td { text-align: left; padding: 9px 8px; border-bottom: 1px solid #f0e9fa; }
        .people-table th { color: var(--muted); font-size: 11px; text-transform: uppercase; letter-spacing: .05em; }
        @media (max-width: 760px) { .access-toolbar { grid-template-columns: 1fr; } .detail-grid { grid-template-columns: 1fr; } }
    </style>

    @php $accessTemplate = App\Models\MessageTemplate::where('name', 'Envío de QR de acceso')->first(); @endphp

    <div class="grid cols-3" style="margin-bottom: 18px;">
        <div class="card metric"><div class="label">Confirmados</div><div class="value">{{ number_format($summary['confirmed']) }}</div></div>
        <div class="card metric"><div class="label">Con QR cargado</div><div class="value">{{ number_format($summary['with_qr']) }}</div></div>
        <div class="card metric"><div class="label">Pendientes de QR</div><div class="value">{{ number_format(max(0, $summary['confirmed'] - $summary['with_qr'])) }}</div></div>
    </div>

    <div class="card" style="margin-bottom: 18px;">
        <div class="inline" style="justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 12px;">
            <div>
                <div class="section-kicker">Accesos</div>
                <h3 class="section-title" style="font-size: 20px;">Carga de QR por familia</h3>
            </div>
            @if ($accessTemplate)
                <a class="btn secondary" href="{{ route('message-sends.create', ['template_id' => $accessTemplate->id, 'status' => 'Confirmado']) }}">Preparar envío de QR</a>
            @endif
        </div>
        <form method="get" action="{{ route('access-qrs.index') }}" class="filter-row" style="grid-template-columns: 1.4fr 1fr 130px auto;">
            <div>
                <label>Buscar</label>
                <input type="text" name="q" value="{{ $search }}" placeholder="Nombre, prefijo, telefono o familia...">
            </div>
            <div>
                <label>Grupo</label>
                <select name="group">
                    <option value="">Todos</option>
                    @foreach ($groups as $option)
                        <option value="{{ $option }}" @selected($group === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Mostrar</label>
                <select name="per_page" onchange="this.form.submit()">
                    @foreach ([10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </div>
            <div class="inline" style="gap: 8px; align-items: end;">
                <button class="btn" type="submit">Filtrar</button>
                <a class="btn secondary" href="{{ route('access-qrs.index') }}">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 18px;">
        <form class="access-toolbar" method="get" action="{{ route('access-qrs.index') }}" data-quick-search-form>
            <input type="hidden" name="group" value="{{ $group }}">
            <input type="hidden" name="per_page" value="{{ $perPage }}">
            <div>
                <label>Busqueda rapida general</label>
                <input type="search" name="q" value="{{ $search }}" placeholder="Familia, invitado, mesa, telefono, padrino..." data-access-search>
            </div>
            <div>
                <label>Registros</label>
                <div style="height:40px; display:flex; align-items:center; color:var(--muted); font-size:13px;">
                    {{ $guests->firstItem() ?? 0 }}-{{ $guests->lastItem() ?? 0 }} de {{ $guests->total() }}
                </div>
            </div>
            <div>
                <label>Pagina</label>
                <div style="height:40px; display:flex; align-items:center; color:var(--muted); font-size:13px;">{{ $guests->currentPage() }} de {{ $guests->lastPage() }}</div>
            </div>
            <div class="inline" style="gap:8px; align-items:end; min-height:40px;">
                <button class="btn small" type="submit">Buscar</button>
                @if ($search !== '')
                    <a class="btn secondary small" href="{{ route('access-qrs.index', array_filter(['group' => $group, 'per_page' => $perPage])) }}">Limpiar</a>
                @endif
            </div>
        </form>

        <div class="table-wrap">
            <table class="access-table">
                <thead>
                    <tr>
                        <th>Familia / grupo</th>
                        <th>Contacto</th>
                        <th>Grupo</th>
                        <th>Mesa(s)</th>
                        <th>QR actual</th>
                        <th>Detalle</th>
                        <th style="width: 330px;">Cargar QR</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($guests as $guest)
                        @php
                            $detail = $detailsByGuest[$guest->name] ?? ['people' => collect(), 'tables' => collect(), 'is_divided' => false];
                            $tables = collect($detail['tables'] ?? []);
                            $people = collect($detail['people'] ?? []);
                            $peopleByTable = collect($detail['people_by_table'] ?? []);
                            $typeCounts = $people->groupBy('type')->map->count();
                            $detailPayload = [
                                'name' => $guest->name,
                                'prefix' => $guest->prefix,
                                'phone' => $guest->phone,
                                'group' => $guest->group_name,
                                'sponsor' => $guest->sponsor,
                                'tables' => $tables->values(),
                                'is_divided' => (bool) ($detail['is_divided'] ?? false),
                                'people' => $people->values(),
                                'people_by_table' => $peopleByTable,
                                'counts' => [
                                    'total' => $people->count(),
                                    'adultos' => (int) ($typeCounts['Adulto'] ?? 0),
                                    'adolescentes' => (int) ($typeCounts['Adolescente'] ?? 0),
                                    'ninos' => (int) ($typeCounts['Niño'] ?? 0),
                                ],
                            ];
                            $quickText = collect([$guest->name, $guest->prefix, $guest->phone, $guest->group_name, $guest->sponsor, $tables->implode(' ')])->filter()->implode(' ');
                        @endphp
                        <tr data-access-row data-quick="{{ Str::lower($quickText) }}">
                            <td>
                                <strong>{{ $guest->name }}</strong>
                                <div class="small">{{ $guest->prefix ?: 'Sin prefijo' }}</div>
                                @if ($guest->sponsor)
                                    <div style="margin-top: 4px;"><span class="pill status-considerado">👑 {{ $guest->sponsor }}</span></div>
                                @endif
                            </td>
                            <td>
                                <div>{{ $guest->phone ?: 'Sin telefono' }}</div>
                                <div class="small">{{ $guest->total_people }} persona(s)</div>
                            </td>
                            <td><span class="pill status-default">{{ $guest->group_name }}</span></td>
                            <td>
                                <div class="mesa-list">
                                    @if ($tables->isEmpty())
                                        <span class="mesa-chip empty">Sin mesa</span>
                                    @else
                                        @foreach ($tables as $table)
                                            <span class="mesa-chip {{ $detail['is_divided'] ? 'warn' : '' }}">{{ $table }}</span>
                                        @endforeach
                                    @endif
                                </div>
                                @if ($detail['is_divided'])
                                    <div class="split-detail">
                                        <strong>Grupo dividido:</strong>
                                        @foreach ($peopleByTable as $tableName => $tablePeople)
                                            <div>{{ $tableName }}: {{ collect($tablePeople)->pluck('name')->implode(', ') }}</div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if ($guest->access_qr_data)
                                    <span class="pill status-confirmado">QR cargado</span>
                                    <form method="post" action="{{ route('access-qrs.destroy', $guest) }}" style="display:inline; margin-left: 6px;"
                                        data-confirm-title="¿Eliminar QR de {{ $guest->name }}?"
                                        data-confirm-text="El link público quedará sin imagen QR hasta que cargues una nueva."
                                        data-confirm-button="Sí, eliminar"
                                        data-confirm-color="#d8527f">
                                        @csrf
                                        @method('delete')
                                        <button class="btn danger small" type="submit">Quitar</button>
                                    </form>
                                @else
                                    <span class="pill status-pendiente">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn secondary small" data-detail='@json($detailPayload, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP)'>Ver</button>
                            </td>
                            <td>
                                <form method="post" action="{{ route('access-qrs.store', $guest) }}" enctype="multipart/form-data" class="qr-upload-form" data-qr-upload-form>
                                    @csrf
                                    <input id="qr-{{ $guest->id }}" class="qr-file" type="file" name="qr" accept="image/png,image/jpeg,image/webp" required data-qr-file>
                                    <label class="qr-picker" for="qr-{{ $guest->id }}">▣ Elegir imagen</label>
                                    <span class="qr-file-name" data-qr-file-name>Sin archivo</span>
                                    <button class="btn small" type="submit" disabled data-qr-submit>Subir</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="small">No hay familias confirmadas con estos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding-top: 14px;">{{ $guests->links() }}</div>
    </div>

    <div class="detail-modal-bg" data-detail-modal>
        <div class="detail-modal">
            <div class="detail-head">
                <div>
                    <div class="section-kicker">Detalle</div>
                    <h3 class="section-title" style="margin:0;" data-detail-name>—</h3>
                    <div class="small" style="margin-top:4px;" data-detail-meta></div>
                </div>
                <button type="button" class="btn ghost" data-detail-close>✕</button>
            </div>
            <div data-detail-alert></div>
            <div data-detail-split></div>
            <div class="detail-grid">
                <div class="detail-stat"><div class="label">Personas</div><div class="value" data-detail-total>0</div></div>
                <div class="detail-stat"><div class="label">Mesa(s)</div><div class="value" data-detail-tables>—</div></div>
                <div class="detail-stat"><div class="label">Padrino</div><div class="value" data-detail-sponsor>—</div></div>
            </div>
            <table class="people-table">
                <thead><tr><th>Persona</th><th>Tipo</th><th>Mesa</th></tr></thead>
                <tbody data-detail-people></tbody>
            </table>
        </div>
    </div>

    <script>
        const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[char]));

        document.querySelectorAll('[data-qr-upload-form]').forEach((form) => {
            const input = form.querySelector('[data-qr-file]');
            const name = form.querySelector('[data-qr-file-name]');
            const submit = form.querySelector('[data-qr-submit]');

            input.addEventListener('change', () => {
                const file = input.files && input.files[0];
                form.classList.toggle('has-file', Boolean(file));
                name.textContent = file ? file.name : 'Sin archivo';
                submit.disabled = ! file;
            });
        });

        const search = document.querySelector('[data-access-search]');
        const quickForm = document.querySelector('[data-quick-search-form]');
        let searchTimer = null;
        search?.addEventListener('input', () => {
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(() => quickForm?.submit(), 450);
        });

        const modal = document.querySelector('[data-detail-modal]');
        const closeModal = () => modal.classList.remove('open');
        document.querySelector('[data-detail-close]')?.addEventListener('click', closeModal);
        modal?.addEventListener('click', (event) => { if (event.target === modal) closeModal(); });

        document.querySelectorAll('[data-detail]').forEach((button) => {
            button.addEventListener('click', () => {
                const data = JSON.parse(button.dataset.detail || '{}');
                const tables = data.tables || [];
                const people = data.people || [];
                const counts = data.counts || {};
                const peopleByTable = data.people_by_table || {};

                document.querySelector('[data-detail-name]').textContent = data.name || '—';
                document.querySelector('[data-detail-meta]').textContent = [data.prefix, data.phone, data.group].filter(Boolean).join(' · ');
                document.querySelector('[data-detail-total]').textContent = `${counts.total || 0} (${counts.adultos || 0} adulto, ${counts.adolescentes || 0} adolescente, ${counts.ninos || 0} niño)`;
                document.querySelector('[data-detail-tables]').textContent = tables.length ? tables.join(', ') : 'Sin mesa';
                document.querySelector('[data-detail-sponsor]').textContent = data.sponsor || '—';
                document.querySelector('[data-detail-alert]').innerHTML = data.is_divided
                    ? '<div class="split-detail" style="margin-bottom:12px; font-weight:700;">Grupo dividido en varias mesas. Cada invitado trae su mesa asignada.</div>'
                    : '';
                document.querySelector('[data-detail-split]').innerHTML = data.is_divided
                    ? `<div class="split-detail" style="margin-bottom:12px;">${Object.entries(peopleByTable).map(([table, tablePeople]) => `<div><strong>${escapeHtml(table)}:</strong> ${tablePeople.map((person) => escapeHtml(person.name)).join(', ')}</div>`).join('')}</div>`
                    : '';
                document.querySelector('[data-detail-people]').innerHTML = people.length
                    ? people.map((person) => `
                        <tr>
                            <td>${data.sponsor ? '👑 ' : ''}${escapeHtml(person.name)}</td>
                            <td><span class="pill status-default">${escapeHtml(person.type)}</span></td>
                            <td>${person.table ? `<span class="mesa-chip">${escapeHtml(person.table)}</span>` : '<span class="mesa-chip empty">Sin mesa</span>'}</td>
                        </tr>`).join('')
                    : '<tr><td colspan="3" class="small">No hay invitados registrados para esta familia.</td></tr>';

                modal.classList.add('open');
            });
        });
    </script>
@endsection
