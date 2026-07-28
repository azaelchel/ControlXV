@extends('layouts.app')

@section('title', 'QR de accesos')
@section('heading', 'QR de accesos')
@section('subheading', 'Solo familias confirmadas. Carga la imagen QR que recibirá cada familia en su link personalizado.')

@section('content')
    <style>
        .access-toolbar { display: grid; grid-template-columns: 1.2fr 150px 170px auto; gap: 12px; align-items: end; margin: 0 0 14px; }
        .access-toolbar label { display: block; font-size: 12px; font-weight: 700; color: var(--muted); margin-bottom: 4px; }
        .access-toolbar input, .access-toolbar select { width: 100%; height: 40px; }
        .access-table { min-width: 1660px; table-layout: fixed; }
        .access-table thead th { position: sticky; top: 0; z-index: 1; background: #fff; box-shadow: 0 1px 0 #e6dff1; }
        .access-table tbody tr:hover td { background: #faf6ff; }
        .access-table td { vertical-align: top; overflow-wrap: anywhere; }
        .access-table .col-family { width: 150px; }
        .access-table .col-contact { width: 150px; }
        .access-table .col-group { width: 130px; }
        .access-table .col-tables { width: 130px; }
        .access-table .col-qr { width: 210px; }
        .access-table .col-link { width: 150px; }
        .access-table .col-send { width: 240px; }
        .access-table .col-detail { width: 100px; }
        .access-table .col-upload { width: 330px; }
        .mesa-list { display: flex; gap: 5px; flex-wrap: wrap; }
        .mesa-chip { display: inline-flex; align-items: center; border-radius: 999px; padding: 3px 8px; background: #eef8f2; color: #256141; font-size: 11px; font-weight: 800; }
        .mesa-chip.warn { background: #fff1d8; color: #8b5b10; }
        .mesa-chip.empty { background: #eceff3; color: #55606f; }
        .split-badge { display: inline-flex; margin-top: 6px; border-radius: 999px; padding: 3px 8px; background: #fff8e8; border: 1px solid #ead8ad; color: #7a5818; font-size: 11px; font-weight: 800; }
        .split-detail { margin-bottom: 12px; padding: 10px 12px; border-radius: 12px; background: #fff8e8; border: 1px solid #ead8ad; color: #6f5218; font-size: 12px; line-height: 1.45; }
        .split-detail strong { color: var(--primary-dark); }
        .detail-modal { width: min(620px, calc(100vw - 28px)); max-height: 86vh; overflow: hidden; display: flex; flex-direction: column; background: #fff; border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 24px 70px rgba(39, 24, 55, .28); padding: 18px; }
        .detail-body { overflow: auto; padding-right: 4px; }
        .table-breakdown { display: grid; gap: 8px; margin-bottom: 12px; }
        .table-breakdown-card { border: 1px solid #eee3f7; background: #fcf9ff; border-radius: 12px; padding: 10px 12px; }
        .table-breakdown-title { display: flex; align-items: center; justify-content: space-between; gap: 8px; color: var(--primary-dark); font-size: 13px; font-weight: 900; margin-bottom: 6px; }
        .person-chips { display: flex; flex-wrap: wrap; gap: 5px; }
        .person-chip { border-radius: 999px; padding: 4px 8px; background: #fff; border: 1px solid #eee3f7; color: #4b3a5f; font-size: 12px; font-weight: 700; }
        .qr-upload-form { display: grid; grid-template-columns: 150px minmax(74px, 1fr) 70px; align-items: center; gap: 8px; min-width: 300px; }
        .qr-file { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
        .qr-picker { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 36px; padding: 8px 12px; border-radius: 10px; border: 1px solid var(--border); background: #fff; color: var(--primary-dark); font-size: 12px; font-weight: 800; cursor: pointer; white-space: nowrap; box-shadow: 0 8px 18px rgba(122, 79, 168, .08); }
        .qr-picker:hover { border-color: var(--primary); background: #faf6ff; }
        .qr-file-name { max-width: 112px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--muted); font-size: 12px; }
        .qr-upload-form.has-file .qr-file-name { color: var(--primary-dark); font-weight: 700; }
        .qr-upload-form .btn[disabled] { opacity: .45; cursor: not-allowed; }
        .detail-modal-bg { position: fixed; inset: 0; background: rgba(25, 16, 35, .55); display: none; align-items: center; justify-content: center; z-index: 120; padding: 22px; }
        .detail-modal-bg.open { display: flex; }
        .qr-preview-box { width: min(430px, calc(100vw - 28px)); background:#fff; border:1px solid var(--border); border-radius:18px; box-shadow:0 24px 70px rgba(39,24,55,.28); padding:18px; }
        .qr-preview-frame { margin-top:12px; border:1px solid #eee3f7; border-radius:16px; background:#faf6ff; padding:14px; display:grid; place-items:center; min-height:260px; }
        .qr-preview-frame img { display:block; width:min(100%, 330px); max-height:62vh; object-fit:contain; background:#fff; border-radius:10px; box-shadow:0 10px 28px rgba(40,25,60,.12); }
        .qr-preview-loading { color:var(--muted); font-size:13px; font-weight:700; }
        .access-search-count { color: var(--muted); font-size: 12px; margin: -4px 0 10px; display: none; }
        .access-search-count.show { display: block; }
        .detail-head { display: flex; justify-content: space-between; gap: 14px; align-items: start; margin-bottom: 12px; }
        .detail-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; margin-bottom: 12px; }
        .detail-stat { background: #faf6ff; border: 1px solid #eee3f7; border-radius: 10px; padding: 9px 10px; }
        .detail-stat .label { color: var(--muted); font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; }
        .detail-stat .value { color: var(--primary-dark); font-size: 16px; font-weight: 900; margin-top: 2px; overflow-wrap: anywhere; }
        .people-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .people-table th, .people-table td { text-align: left; padding: 7px 8px; border-bottom: 1px solid #f0e9fa; }
        .people-table th { color: var(--muted); font-size: 11px; text-transform: uppercase; letter-spacing: .05em; }
        .qr-current-actions { display: grid; gap: 7px; align-items: start; }
        .qr-current-actions .btn { width: max-content; max-width: 100%; }
        .qr-current-actions form { display: block; margin: 0; }
        .qr-send-actions { display: flex; flex-direction: column; align-items: flex-start; gap: 7px; min-width: 190px; }
        .qr-send-actions .inline { gap: 7px; flex-wrap: wrap; }
        .qr-send-note { color: var(--muted); font-size: 11px; line-height: 1.35; }
        .qr-send-ok { color: #256141; font-size: 11px; font-weight: 800; display: none; }
        .qr-send-ok.show { display: block; }
        @media (max-width: 760px) {
            .access-toolbar { grid-template-columns: 1fr; }
            .detail-grid { grid-template-columns: 1fr; }
            .access-table { min-width: 1540px; }
            .qr-upload-form { grid-template-columns: 1fr; min-width: 220px; }
            .qr-picker, .qr-upload-form .btn { width: 100%; }
            .qr-file-name { max-width: 100%; }
        }
    </style>


    <div class="grid cols-3" style="margin-bottom: 18px;">
        <div class="card metric"><div class="label">Confirmados</div><div class="value">{{ number_format($summary['confirmed']) }}</div></div>
        <div class="card metric"><div class="label">Con QR cargado</div><div class="value">{{ number_format($summary['with_qr']) }}</div></div>
        <div class="card metric"><div class="label">Links generados</div><div class="value">{{ number_format($summary['with_link']) }}</div></div>
        <div class="card metric"><div class="label">Links abiertos</div><div class="value">{{ number_format($summary['opened']) }}</div></div>
    </div>

    <div class="card" style="margin-bottom: 18px;">
        <div style="margin-bottom: 12px;">
            <div class="section-kicker">Accesos</div>
            <h3 class="section-title" style="font-size: 20px;">Carga y envio de QR por familia</h3>
        </div>
        <form method="get" action="{{ route('access-qrs.index') }}" class="filter-row" style="grid-template-columns: 1fr 190px 130px auto;">
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
                <label>Mensaje QR</label>
                <select name="message_state" onchange="this.form.submit()">
                    <option value="" @selected($messageState === '')>Todos</option>
                    <option value="opened" @selected($messageState === 'opened')>Abrieron link</option>
                    <option value="unopened" @selected($messageState === 'unopened')>No han abierto</option>
                    <option value="not_generated" @selected($messageState === 'not_generated')>Sin link generado</option>
                    <option value="no_qr" @selected($messageState === 'no_qr')>Sin QR cargado</option>
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
            <input type="hidden" name="message_state" value="{{ $messageState }}">
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
                    <a class="btn secondary small" href="{{ route('access-qrs.index', array_filter(['group' => $group, 'message_state' => $messageState, 'per_page' => $perPage])) }}">Limpiar</a>
                @endif
            </div>
        </form>

        <div class="table-wrap">
            <div class="access-search-count" data-access-filter-count></div>
            <table class="access-table" data-access-table>
                <thead>
                    <tr>
                        <th class="col-family">Familia / grupo</th>
                        <th class="col-contact">Contacto</th>
                        <th class="col-group">Grupo</th>
                        <th class="col-tables">Mesa(s)</th>
                        <th class="col-qr">QR actual</th>
                        <th class="col-link">Link</th>
                        <th class="col-send">Envio WhatsApp</th>
                        <th class="col-detail">Detalle</th>
                        <th class="col-upload">Cargar QR</th>
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
                            $qrLink = $guest->publicLinks->first();
                            $qrStatusLabel = ! $guest->access_qr_data
                                ? 'Sin QR'
                                : ($qrLink ? ($qrLink->opened_at ? 'Abierto' : 'Generado sin abrir') : 'Sin link');
                            $qrStatusClass = ! $guest->access_qr_data
                                ? 'status-pendiente'
                                : ($qrLink ? ($qrLink->opened_at ? 'status-confirmado' : 'status-considerado') : 'status-default');
                            $qrStatusMeta = $qrLink
                                ? ($qrLink->opened_at ? 'Abrió '.$qrLink->opened_at->format('d/m/Y H:i') : 'Vence '.$qrLink->expires_at?->format('d/m/Y H:i'))
                                : ($guest->access_qr_data ? 'Pendiente de generar envio' : 'Carga imagen QR primero');
                            $quickText = collect([$guest->name, $guest->prefix, $guest->phone, $guest->group_name, $guest->sponsor, $qrStatusLabel, $tables->implode(' '), $people->pluck('name')->implode(' '), $people->pluck('type')->implode(' ')])->filter()->implode(' ');
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
                                    <span class="split-badge">Dividido</span>
                                @endif
                            </td>
                            <td>
                                <div class="qr-current-actions">
                                    @if ($guest->access_qr_data)
                                        <span class="pill status-confirmado">QR cargado</span>
                                        <button type="button" class="btn secondary small" data-qr-preview-url="{{ route('access-qrs.preview', $guest) }}" data-qr-preview-name="{{ $guest->name }}">Vista previa</button>
                                        <form method="post" action="{{ route('access-qrs.destroy', $guest) }}"
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
                                </div>
                            </td>
                            <td data-link-status-cell>
                                <span class="pill {{ $qrStatusClass }}" data-link-status>{{ $qrStatusLabel }}</span>
                                <div class="small" style="margin-top:5px;" data-link-meta>{{ $qrStatusMeta }}</div>
                            </td>
                            <td>
                                <div class="qr-send-actions" data-qr-send-actions>
                                    @if (! $guest->access_qr_data)
                                        <span class="pill status-pendiente">Carga QR primero</span>
                                        <div class="qr-send-note">El envio se habilita cuando exista imagen QR.</div>
                                    @else
                                        <button type="button" class="btn small {{ $qrLink ? 'secondary' : '' }}" data-qr-send-open data-message-url="{{ route('access-qrs.message', $guest) }}" data-csrf="{{ csrf_token() }}">
                                            {{ $qrLink ? 'Reintentar WhatsApp' : 'Generar y enviar WA' }}
                                        </button>
                                        <div class="inline" data-qr-after-actions style="{{ $qrLink ? '' : 'display:none;' }}">
                                            <button type="button" class="btn secondary small" data-qr-copy data-message-url="{{ route('access-qrs.message', $guest) }}" data-csrf="{{ csrf_token() }}">Copiar mensaje</button>
                                        </div>
                                        <div class="qr-send-ok" data-qr-send-ok>Mensaje copiado y envio registrado.</div>
                                    @endif
                                </div>
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
                        <tr><td colspan="9" class="small">No hay familias confirmadas con estos filtros.</td></tr>
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
            <div class="detail-body">
                <div data-detail-alert></div>
                <div class="detail-grid">
                    <div class="detail-stat"><div class="label">Personas</div><div class="value" data-detail-total>0</div></div>
                    <div class="detail-stat"><div class="label">Mesa(s)</div><div class="value" data-detail-tables>—</div></div>
                    <div class="detail-stat"><div class="label">Padrino</div><div class="value" data-detail-sponsor>—</div></div>
                </div>
                <div data-detail-split></div>
                <table class="people-table">
                    <thead><tr><th>Persona</th><th>Tipo</th><th>Mesa</th></tr></thead>
                    <tbody data-detail-people></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="detail-modal-bg" data-qr-preview-modal>
        <div class="qr-preview-box">
            <div class="detail-head" style="margin-bottom:0;">
                <div>
                    <div class="section-kicker">Vista previa QR</div>
                    <h3 class="section-title" style="margin:0;" data-qr-preview-title>—</h3>
                    <div class="small" style="margin-top:4px;">Imagen cargada para el pase digital.</div>
                </div>
                <button type="button" class="btn ghost" data-qr-preview-close>✕</button>
            </div>
            <div class="qr-preview-frame">
                <div class="qr-preview-loading" data-qr-preview-loading>Cargando imagen…</div>
                <img src="" alt="Vista previa del QR" data-qr-preview-img style="display:none;">
            </div>
        </div>
    </div>

    <script>
        const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[char]));

        async function copyQrText(text) {
            try {
                await navigator.clipboard.writeText(text);
                return true;
            } catch (e) {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.setAttribute('readonly', 'readonly');
                ta.style.position = 'fixed';
                ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                return true;
            }
        }

        async function resolveQrMessage(button) {
            const response = await fetch(button.dataset.messageUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': button.dataset.csrf,
                    'Accept': 'application/json',
                },
            });
            const data = await response.json().catch(() => ({}));
            if (! response.ok || data.ok === false) {
                throw new Error(data.message || 'No se pudo preparar el mensaje.');
            }
            return data;
        }

        function updateQrRow(button, data) {
            const row = button.closest('[data-access-row]');
            row?.querySelector('[data-link-status]')?.classList.remove('status-default', 'status-pendiente', 'status-confirmado', 'status-considerado', 'status-no-asistira');
            const status = row?.querySelector('[data-link-status]');
            const meta = row?.querySelector('[data-link-meta]');
            const ok = row?.querySelector('[data-qr-send-ok]');
            const after = row?.querySelector('[data-qr-after-actions]');
            const openButton = row?.querySelector('[data-qr-send-open]');

            if (status) {
                status.textContent = data.status_label || 'Generado sin abrir';
                status.classList.add(data.status_class || 'status-considerado');
            }
            if (meta) meta.textContent = data.status_meta || '';
            if (after) after.style.display = '';
            if (openButton) {
                openButton.textContent = data.whatsapp_url ? 'Reintentar WhatsApp' : 'Sin telefono: copiar';
                openButton.classList.add('secondary');
            }
            if (ok) {
                ok.classList.add('show');
                window.setTimeout(() => ok.classList.remove('show'), 2600);
            }
        }

        document.addEventListener('click', async (event) => {
            const openButton = event.target.closest('[data-qr-send-open]');
            const copyButton = event.target.closest('[data-qr-copy]');
            if (! openButton && ! copyButton) return;

            const button = openButton || copyButton;
            const originalText = button.textContent;
            const openingWhatsApp = Boolean(openButton);
            let popup = null;

            if (openingWhatsApp) {
                popup = window.open('', '_blank');
            }

            button.disabled = true;
            button.textContent = openingWhatsApp ? 'Preparando...' : 'Copiando...';

            try {
                const data = await resolveQrMessage(button);
                await copyQrText(data.message || '');
                updateQrRow(button, data);

                if (openingWhatsApp && data.whatsapp_url) {
                    if (popup) {
                        popup.location.href = data.whatsapp_url;
                    } else {
                        window.location.href = data.whatsapp_url;
                    }
                } else if (popup) {
                    popup.close();
                }

                button.textContent = openingWhatsApp ? (data.whatsapp_url ? 'Reintentar WhatsApp' : 'Sin telefono: copiar') : 'Copiado';
                if (! openingWhatsApp) {
                    window.setTimeout(() => { button.textContent = originalText; }, 1500);
                }
            } catch (error) {
                if (popup) popup.close();
                alert(error.message);
                button.textContent = originalText;
            } finally {
                button.disabled = false;
            }
        });

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
        const accessRows = [...document.querySelectorAll('[data-access-row]')];
        const filterCount = document.querySelector('[data-access-filter-count]');
        let accessSearchTimer = null;
        const submittedSearch = (search?.defaultValue || '').trim();
        const applyAccessFilter = () => {
            const needle = (search?.value || '').trim().toLowerCase();
            let visible = 0;

            accessRows.forEach((row) => {
                const match = needle === '' || (row.dataset.quick || '').includes(needle);
                row.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            if (filterCount) {
                filterCount.classList.toggle('show', needle !== '');
                filterCount.textContent = `Buscando en todos los registros... coincidencias visibles: ${visible} de ${accessRows.length}`;
            }
        };
        const submitGlobalAccessSearch = () => {
            if (!quickForm || !search) return;
            const current = search.value.trim();
            if (current === submittedSearch) return;
            accessRows.forEach((row) => row.style.display = '');
            quickForm.submit();
        };
        search?.addEventListener('input', () => {
            applyAccessFilter();
            window.clearTimeout(accessSearchTimer);
            accessSearchTimer = window.setTimeout(submitGlobalAccessSearch, 420);
        });
        quickForm?.addEventListener('submit', () => {
            window.clearTimeout(accessSearchTimer);
            accessRows.forEach((row) => row.style.display = '');
        });
        applyAccessFilter();

        const qrPreviewModal = document.querySelector('[data-qr-preview-modal]');
        const qrPreviewImg = document.querySelector('[data-qr-preview-img]');
        const qrPreviewLoading = document.querySelector('[data-qr-preview-loading]');
        const closeQrPreview = () => {
            qrPreviewModal?.classList.remove('open');
            if (qrPreviewImg) {
                qrPreviewImg.removeAttribute('src');
                qrPreviewImg.style.display = 'none';
            }
        };
        document.querySelector('[data-qr-preview-close]')?.addEventListener('click', closeQrPreview);
        qrPreviewModal?.addEventListener('click', (event) => { if (event.target === qrPreviewModal) closeQrPreview(); });
        document.querySelectorAll('[data-qr-preview-url]').forEach((button) => {
            button.addEventListener('click', () => {
                document.querySelector('[data-qr-preview-title]').textContent = button.dataset.qrPreviewName || 'QR cargado';
                if (qrPreviewLoading) qrPreviewLoading.style.display = '';
                if (qrPreviewImg) {
                    qrPreviewImg.style.display = 'none';
                    qrPreviewImg.onload = () => {
                        if (qrPreviewLoading) qrPreviewLoading.style.display = 'none';
                        qrPreviewImg.style.display = '';
                    };
                    qrPreviewImg.src = button.dataset.qrPreviewUrl;
                }
                qrPreviewModal?.classList.add('open');
            });
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
                document.querySelector('[data-detail-split]').innerHTML = Object.entries(peopleByTable).length
                    ? `<div class="table-breakdown">${Object.entries(peopleByTable).map(([table, tablePeople]) => `
                        <div class="table-breakdown-card">
                            <div class="table-breakdown-title"><span>${escapeHtml(table)}</span><span>${tablePeople.length}</span></div>
                            <div class="person-chips">${tablePeople.map((person) => `<span class="person-chip">${escapeHtml(person.name)}</span>`).join('')}</div>
                        </div>`).join('')}</div>`
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
