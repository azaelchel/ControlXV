@extends('layouts.app')

@section('title', 'Mensajes')
@section('heading', 'Control de mensajes a invitados')
@section('subheading', 'Panorama de cada familia con acciones rápidas para enviar, re-copiar y dar seguimiento')

@section('content')
    <style>
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; margin-bottom: 18px; }
        .stat-card { padding: 16px; }
        .stat-card .label { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.4px; font-weight: 700; }
        .stat-card .value { font-size: 26px; font-weight: 800; color: var(--primary-dark); margin-top: 4px; line-height: 1; }
        .stat-card .desc { font-size: 12px; color: var(--muted); margin-top: 6px; }
        .stat-card.alert .value { color: #d8527f; }

        .filter-row { display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr auto; gap: 12px; align-items: end; }
        .filter-row > div { min-width: 0; }
        .filter-row label { display: block; font-size: 12px; font-weight: 600; color: var(--muted); margin-bottom: 4px; }
        .filter-row input, .filter-row select { width: 100%; box-sizing: border-box; height: 42px; }

        .msg-table { min-width: 100%; }
        .msg-table thead th { position: sticky; top: 0; background: white; z-index: 1; box-shadow: 0 1px 0 #e6dff1; }
        .msg-table tbody tr:hover td { background: #faf6ff; }
        .msg-table td { vertical-align: top; padding: 12px 8px; }
        .msg-table .row-actions { display: flex; gap: 4px; flex-wrap: wrap; }
        .msg-table .icon-btn { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; }
        .msg-table .icon-btn[disabled] { opacity: 0.35; cursor: not-allowed; }

        .pagination-wrap { display: flex; justify-content: space-between; align-items: center; margin-top: 14px; }
        .pagination-wrap .pagination { display: inline-flex; gap: 2px; list-style: none; padding: 0; margin: 0; }
        .pagination-wrap .pagination li a, .pagination-wrap .pagination li span { display: inline-block; padding: 6px 12px; border: 1px solid #e6dff1; border-radius: 8px; color: var(--text); text-decoration: none; font-size: 13px; }
        .pagination-wrap .pagination li.active span { background: var(--primary); color: white; border-color: var(--primary); }
        .pagination-wrap .pagination li.disabled span { color: #c2c8d0; }

        /* Modal */
        .modal-bg { position: fixed; inset: 0; background: rgba(20,15,30,0.55); display: none; align-items: center; justify-content: center; z-index: 100; padding: 20px; }
        .modal-bg.open { display: flex; }
        .modal-box { background: white; border-radius: 16px; max-width: 720px; width: 100%; max-height: 92vh; overflow: auto; padding: 24px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
    </style>

    {{-- Stats --}}
    <div class="stat-grid">
        <div class="card stat-card">
            <div class="label">Esperando respuesta</div>
            <div class="value">{{ number_format($stats['waiting']) }}</div>
            <div class="desc">Links activos sin contestar</div>
        </div>
        <div class="card stat-card">
            <div class="label">Ya respondieron</div>
            <div class="value">{{ number_format($stats['responded']) }}</div>
            <div class="desc">A través de su link</div>
        </div>
        <div class="card stat-card alert">
            <div class="label">Necesitan reenvío</div>
            <div class="value">{{ number_format($stats['needs_resend']) }}</div>
            <div class="desc">Familias activas con link vencido</div>
        </div>
        <div class="card stat-card">
            <div class="label">Enviados hoy</div>
            <div class="value">{{ number_format($stats['today']) }}</div>
            <div class="desc">{{ number_format($stats['week']) }} esta semana</div>
        </div>
    </div>

    {{-- Acciones rápidas --}}
    <div class="card" style="margin-bottom: 18px; background: linear-gradient(135deg, var(--primary) 0%, #6b3b9e 100%); color: white; border: none;">
        <div class="inline" style="justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div>
                <div style="font-size: 12px; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 600;">Acciones</div>
                <h3 style="margin: 4px 0 0 0; font-size: 20px;">Generar y enviar mensajes</h3>
            </div>
            <div class="inline" style="gap: 10px; align-items: center;">
                <label class="inline" style="gap: 6px; align-items: center; color: white; font-size: 13px; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); border-radius: 8px; padding: 0 10px; height: 42px;" title="Elige si el botón de enviar abre la app de WhatsApp o WhatsApp Web">
                    Abrir con
                    <select id="wa-open-mode" style="height: 30px; border-radius: 6px; border: none; padding: 0 6px; color: var(--primary-dark); font-weight: 600;">
                        <option value="app">App</option>
                        <option value="web">Web</option>
                    </select>
                </label>
                <a href="{{ route('message-sends.create') }}" class="btn" style="background: white; color: var(--primary-dark);">+ Envío masivo</a>
                <a href="{{ route('message-sends.history') }}" class="btn" style="background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.3);">Historial</a>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card" style="margin-bottom: 18px;">
        <form method="get" action="{{ route('message-sends.index') }}" class="filter-row">
            <div>
                <label>Buscar por nombre</label>
                <input type="text" name="q" value="{{ $nameQuery }}" placeholder="Escribe parte del nombre…">
            </div>
            <div>
                <label>Estatus</label>
                <select name="status">
                    <option value="">Todos</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected($statusFilter === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Grupo</label>
                <select name="group">
                    <option value="">Todos</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group }}" @selected($groupFilter === $group)>{{ $group }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Estado del link</label>
                <select name="link_state">
                    <option value="">Todos</option>
                    @foreach ($linkStates as $key => $label)
                        <option value="{{ $key }}" @selected($linkStateFilter === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="inline" style="gap: 6px;">
                <button class="btn secondary" type="submit">Filtrar</button>
                @if ($statusFilter || $linkStateFilter || $groupFilter || $nameQuery)
                    <a href="{{ route('message-sends.index') }}" class="btn secondary">Limpiar</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabla --}}
    <div class="card">
        <div class="inline" style="justify-content: space-between; align-items: end; margin-bottom: 14px;">
            <div>
                <div class="section-kicker">Vista por familia</div>
                <h3 class="section-title">{{ number_format($rows->total()) }} familia(s) en total · página {{ $rows->currentPage() }} de {{ $rows->lastPage() }}</h3>
            </div>
            <form method="get" action="{{ route('message-sends.index') }}" style="display: inline;">
                @foreach (request()->except(['per_page', 'page']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <label style="display: inline-flex; align-items: center; gap: 6px;">
                    <span class="small">Filas:</span>
                    <select name="per_page" onchange="this.form.submit()" style="width: auto; height: 36px;">
                        @foreach ([10, 25, 50, 100, 200] as $size)
                            <option value="{{ $size }}" @selected((int) request('per_page', 25) === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </label>
            </form>
        </div>

        <div class="table-wrap" style="max-height: 640px; overflow: auto;">
            <table class="msg-table">
                <thead>
                    <tr>
                        <th>Familia / Grupo</th>
                        <th>Estatus</th>
                        <th>Teléfono</th>
                        <th>Estado del link</th>
                        <th>Vence</th>
                        <th>Último mensaje</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php
                            $guest = $row['guest'];
                            $link = $row['link'];
                            $state = $row['state'];
                            $lastSend = $row['last_send'];
                            $phoneIntl = $row['phone_intl'];
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $guest->name }}</strong>
                                <div class="small">{{ $guest->group_name }}</div>
                                @if ($row['sends_count'] > 0)
                                    <div class="small" style="margin-top: 4px;">📨 {{ $row['sends_count'] }} envío(s)</div>
                                @endif
                            </td>
                            <td class="small">{{ $guest->status }}</td>
                            <td class="small">
                                @if ($guest->phone)
                                    {{ $guest->phone }}
                                @else
                                    <span style="color: var(--danger);">Sin tel</span>
                                @endif
                            </td>
                            <td>
                                <span class="pill {{ $state['class'] }}">{{ $state['label'] }}</span>
                                @if ($link)
                                    <a href="{{ route('guest-review.show', ['guest' => $guest, 'token' => $link->token]) }}" target="_blank" class="small" style="display:block; margin-top: 4px;">Abrir link →</a>
                                @endif
                            </td>
                            <td class="small">
                                @if ($link && ! $link->responded_at && ! $link->isExpired() && $link->closed_reason !== 'cancelled')
                                    {{ $link->expires_at->format('d/m/Y') }}
                                    <div>({{ $link->daysRemaining() }} día{{ $link->daysRemaining() === 1 ? '' : 's' }})</div>
                                @elseif ($link?->expires_at)
                                    {{ $link->expires_at->format('d/m/Y') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if ($lastSend)
                                    <div class="small"><strong>{{ $lastSend->template?->name ?? 'Manual' }}</strong></div>
                                    <div class="small">{{ $lastSend->sent_at?->format('d/m/Y H:i') }}</div>
                                @else
                                    <span class="small">Sin enviar</span>
                                @endif
                            </td>
                            <td>
                                <div class="row-actions">
                                    {{-- Enviar / reenviar con plantilla --}}
                                    <button type="button" class="btn icon-btn" data-quick-send
                                        data-guest-id="{{ $guest->id }}"
                                        data-guest-name="{{ $guest->name }}"
                                        data-phone="{{ $phoneIntl }}"
                                        data-default-template="{{ $lastSend?->message_template_id }}"
                                        {{ ! $guest->phone ? 'disabled' : '' }}
                                        title="Enviar mensaje (elegir plantilla)">📤</button>

                                    {{-- Copiar último mensaje enviado --}}
                                    @if ($lastSend)
                                        <button type="button" class="btn secondary icon-btn" data-copy-existing
                                            data-message="{{ $lastSend->rendered_message }}"
                                            title="Copiar el último mensaje enviado">📋</button>
                                    @endif

                                    {{-- Ver invitados registrados --}}
                                    @if ($row['companion_count'] > 0)
                                        <button type="button" class="btn secondary icon-btn" data-view-companions
                                            data-guest-id="{{ $guest->id }}"
                                            data-count="{{ $row['companion_count'] }}"
                                            title="Ver los {{ $row['companion_count'] }} invitados registrados">👥</button>
                                    @endif

                                    {{-- Abrir WhatsApp (con el último mensaje si existe) --}}
                                    @if ($phoneIntl)
                                        <button type="button" class="btn secondary icon-btn" data-open-whatsapp
                                            data-phone="{{ $phoneIntl }}"
                                            data-message="{{ $lastSend?->rendered_message ?? '' }}"
                                            title="{{ $lastSend ? 'Abrir WhatsApp con el último mensaje precargado' : 'Abrir WhatsApp con este número' }}">💬</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty">No hay familias con esos filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($rows->hasPages())
            <div class="pagination-wrap">
                <div class="small">Mostrando {{ $rows->firstItem() }} - {{ $rows->lastItem() }} de {{ $rows->total() }}</div>
                {{ $rows->links() }}
            </div>
        @endif
    </div>

    {{-- Modal: Invitados registrados de la familia --}}
    <div class="modal-bg" id="companions-modal">
        <div class="modal-box" style="max-width: 760px;">
            <div class="inline" style="justify-content: space-between; align-items: start; margin-bottom: 14px;">
                <div>
                    <div class="section-kicker">Invitados registrados</div>
                    <h3 class="section-title" style="margin: 0;" id="cmp-guest-name">—</h3>
                </div>
                <button type="button" class="btn ghost" id="cmp-close">✕</button>
            </div>

            <div id="cmp-stats" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 14px;">
                <div style="padding: 12px; background: #faf6ff; border-radius: 10px; text-align: center;">
                    <div style="font-size: 11px; color: var(--muted); text-transform: uppercase; font-weight: 700;">Total</div>
                    <div style="font-size: 22px; font-weight: 800; color: var(--primary-dark);" id="cmp-total">0</div>
                    <div style="font-size: 11px; color: var(--muted);" id="cmp-total-expected"></div>
                </div>
                <div style="padding: 12px; background: #faf6ff; border-radius: 10px; text-align: center;">
                    <div style="font-size: 11px; color: var(--muted); text-transform: uppercase; font-weight: 700;">Adultos</div>
                    <div style="font-size: 22px; font-weight: 800;" id="cmp-adults">0</div>
                    <div style="font-size: 11px; color: var(--muted);" id="cmp-adults-expected"></div>
                </div>
                <div style="padding: 12px; background: #faf6ff; border-radius: 10px; text-align: center;">
                    <div style="font-size: 11px; color: var(--muted); text-transform: uppercase; font-weight: 700;">Adolescentes</div>
                    <div style="font-size: 22px; font-weight: 800;" id="cmp-adolescents">0</div>
                    <div style="font-size: 11px; color: var(--muted);" id="cmp-adolescents-expected"></div>
                </div>
                <div style="padding: 12px; background: #faf6ff; border-radius: 10px; text-align: center;">
                    <div style="font-size: 11px; color: var(--muted); text-transform: uppercase; font-weight: 700;">Niños</div>
                    <div style="font-size: 22px; font-weight: 800;" id="cmp-children">0</div>
                    <div style="font-size: 11px; color: var(--muted);" id="cmp-children-expected"></div>
                </div>
            </div>

            <div class="table-wrap" style="max-height: 50vh; overflow: auto;">
                <table style="min-width: 100%;">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Género</th>
                            <th>Origen</th>
                            <th>Registrado</th>
                        </tr>
                    </thead>
                    <tbody id="cmp-list"></tbody>
                </table>
            </div>

            <div id="cmp-loading" class="small" style="margin-top: 12px; display: none;">Cargando…</div>

            <div class="inline" style="margin-top: 14px; justify-content: flex-end;">
                <a href="{{ route('companions.index') }}" class="btn secondary">Ir al módulo Invitados →</a>
            </div>
        </div>
    </div>

    {{-- Modal de envío rápido --}}
    <div class="modal-bg" id="quick-send-modal">
        <div class="modal-box">
            <div class="inline" style="justify-content: space-between; align-items: start; margin-bottom: 14px;">
                <div>
                    <div class="section-kicker">Envío rápido</div>
                    <h3 class="section-title" style="margin: 0;" id="qs-guest-name">Familia</h3>
                </div>
                <button type="button" class="btn ghost" id="qs-close">✕</button>
            </div>

            <div style="margin-bottom: 14px;">
                <label>Plantilla a usar</label>
                <select id="qs-template" style="height: 42px;">
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="qs-loading" class="small" style="display: none; padding: 14px; background: #faf6ff; border-radius: 8px;">Generando mensaje y link...</div>

            <div id="qs-preview" style="display: none;">
                <label>Mensaje que se enviará</label>
                <textarea id="qs-message" rows="12" readonly style="font-family: 'SF Mono', Menlo, monospace; font-size: 13px;"></textarea>
                <div class="small" id="qs-link-info" style="margin-top: 6px;"></div>
            </div>

            <div id="qs-error" class="error" style="display: none; margin-top: 10px;"></div>

            <div class="inline" style="margin-top: 18px; gap: 8px; justify-content: flex-end;">
                <button type="button" class="btn ghost" id="qs-cancel">Cancelar</button>
                <button type="button" class="btn secondary" id="qs-copy-only" disabled>Solo copiar</button>
                <button type="button" class="btn" id="qs-copy-send" disabled>Copiar + WhatsApp</button>
            </div>
        </div>
    </div>

    <script>
        async function copyToClipboard(text) {
            try { await navigator.clipboard.writeText(text); }
            catch (e) {
                const ta = document.createElement('textarea');
                ta.value = text; document.body.appendChild(ta);
                ta.select(); document.execCommand('copy'); document.body.removeChild(ta);
            }
        }

        // Copiar último mensaje desde la tabla
        document.querySelectorAll('[data-copy-existing]').forEach(btn => {
            btn.addEventListener('click', async () => {
                await copyToClipboard(btn.dataset.message);
                const orig = btn.textContent;
                btn.textContent = '✓';
                setTimeout(() => { btn.textContent = orig; }, 1500);
            });
        });

        // Modal de invitados registrados
        (function () {
            const modal = document.getElementById('companions-modal');
            const guestNameEl = document.getElementById('cmp-guest-name');
            const list = document.getElementById('cmp-list');
            const loading = document.getElementById('cmp-loading');
            const closeBtn = document.getElementById('cmp-close');

            const totalEl = document.getElementById('cmp-total');
            const adultsEl = document.getElementById('cmp-adults');
            const adolEl = document.getElementById('cmp-adolescents');
            const childEl = document.getElementById('cmp-children');
            const totalExp = document.getElementById('cmp-total-expected');
            const adultsExp = document.getElementById('cmp-adults-expected');
            const adolExp = document.getElementById('cmp-adolescents-expected');
            const childExp = document.getElementById('cmp-children-expected');

            const close = () => modal.classList.remove('open');
            closeBtn.addEventListener('click', close);
            modal.addEventListener('click', e => { if (e.target === modal) close(); });

            const fmtExpected = (got, exp) => {
                if (exp === 0 && got === 0) return '';
                if (got === exp) return `✓ de ${exp} esperados`;
                if (got < exp) return `de ${exp} esperados (faltan ${exp - got})`;
                return `de ${exp} esperados (${got - exp} extra)`;
            };

            document.querySelectorAll('[data-view-companions]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const guestId = btn.dataset.guestId;
                    list.innerHTML = '';
                    loading.style.display = 'block';
                    modal.classList.add('open');

                    try {
                        const res = await fetch(`/message-sends/companions/${guestId}`, {
                            headers: { 'Accept': 'application/json' },
                        });
                        const data = await res.json();

                        guestNameEl.textContent = data.guest_name + ' · ' + data.status;
                        totalEl.textContent = data.counts.total;
                        adultsEl.textContent = data.counts.adults;
                        adolEl.textContent = data.counts.adolescents;
                        childEl.textContent = data.counts.children;
                        totalExp.textContent = fmtExpected(data.counts.total, data.expected.total);
                        adultsExp.textContent = fmtExpected(data.counts.adults, data.expected.adults);
                        adolExp.textContent = fmtExpected(data.counts.adolescents, data.expected.adolescents);
                        childExp.textContent = fmtExpected(data.counts.children, data.expected.children);

                        const sourceBadge = (src) => {
                            if (src === 'link') return '<span class="pill status-confirmado" style="font-size:10px;padding:2px 8px;">🔗 Link</span>';
                            if (src === 'manual') return '<span class="pill status-default" style="font-size:10px;padding:2px 8px;">✋ Manual</span>';
                            return '<span class="pill" style="font-size:10px;padding:2px 8px;background:#f0e9fa;color:var(--muted);">— Histórico</span>';
                        };

                        if (data.companions.length === 0) {
                            list.innerHTML = '<tr><td colspan="5" class="empty">Aún no hay invitados registrados para esta familia.</td></tr>';
                        } else {
                            list.innerHTML = data.companions.map(c => `
                                <tr>
                                    <td><strong>${c.name}</strong>${c.notes ? `<div class="small">${c.notes}</div>` : ''}</td>
                                    <td>${c.type}</td>
                                    <td>${c.sex || '—'}</td>
                                    <td>${sourceBadge(c.source)}</td>
                                    <td class="small">${c.created_at || '—'}</td>
                                </tr>
                            `).join('');
                        }
                    } catch (e) {
                        list.innerHTML = '<tr><td colspan="5" class="empty">No se pudieron cargar los invitados.</td></tr>';
                    } finally {
                        loading.style.display = 'none';
                    }
                });
            });
        })();

        // Selector App/Web de WhatsApp (persistente por navegador)
        (function () {
            const sel = document.getElementById('wa-open-mode');
            if (!sel) return;
            sel.value = window.waMode();
            sel.addEventListener('change', () => {
                localStorage.setItem('whatsapp_open_mode', sel.value === 'web' ? 'web' : 'app');
            });
        })();

        // Abrir WhatsApp (con último mensaje si existe)
        document.querySelectorAll('[data-open-whatsapp]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const phone = btn.dataset.phone;
                const message = btn.dataset.message;
                if (message) {
                    await copyToClipboard(message);
                }
                window.open(window.waUrl(phone, message), '_blank');
            });
        });

        // Modal de envío rápido
        (function () {
            const modal = document.getElementById('quick-send-modal');
            const qsGuestName = document.getElementById('qs-guest-name');
            const qsTemplate = document.getElementById('qs-template');
            const qsLoading = document.getElementById('qs-loading');
            const qsPreview = document.getElementById('qs-preview');
            const qsMessage = document.getElementById('qs-message');
            const qsLinkInfo = document.getElementById('qs-link-info');
            const qsError = document.getElementById('qs-error');
            const qsCopyOnly = document.getElementById('qs-copy-only');
            const qsCopySend = document.getElementById('qs-copy-send');
            const qsCancel = document.getElementById('qs-cancel');
            const qsClose = document.getElementById('qs-close');

            let currentState = {};

            function close() { modal.classList.remove('open'); currentState = {}; }
            qsCancel.addEventListener('click', close);
            qsClose.addEventListener('click', close);
            modal.addEventListener('click', e => { if (e.target === modal) close(); });

            async function loadPreview() {
                qsLoading.style.display = 'block';
                qsPreview.style.display = 'none';
                qsError.style.display = 'none';
                qsCopyOnly.disabled = true;
                qsCopySend.disabled = true;

                try {
                    const res = await fetch(`/message-sends/render/${currentState.guestId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ message_template_id: qsTemplate.value }),
                    });

                    if (!res.ok) throw new Error('No se pudo generar el mensaje');
                    const data = await res.json();

                    if (!data.eligible) {
                        qsError.textContent = 'Esta familia no es elegible para esta plantilla (probablemente requiere link y el estatus no lo permite).';
                        qsError.style.display = 'block';
                        qsLoading.style.display = 'none';
                        return;
                    }

                    qsMessage.value = data.message;
                    qsMessage.rows = Math.min(16, Math.max(6, (data.message.match(/\n/g) || []).length + 2));
                    qsLinkInfo.textContent = data.public_guest_link_id ? '🔗 Link reutilizado o generado' : '✉️ Esta plantilla no incluye link';
                    currentState.message = data.message;
                    currentState.linkId = data.public_guest_link_id;
                    currentState.templateId = qsTemplate.value;

                    qsLoading.style.display = 'none';
                    qsPreview.style.display = 'block';
                    qsCopyOnly.disabled = false;
                    qsCopySend.disabled = !currentState.phone;
                } catch (e) {
                    qsError.textContent = e.message;
                    qsError.style.display = 'block';
                    qsLoading.style.display = 'none';
                }
            }

            async function registerSend() {
                await fetch('{{ route('message-sends.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        guest_id: currentState.guestId,
                        message_template_id: currentState.templateId,
                        public_guest_link_id: currentState.linkId,
                        rendered_message: currentState.message,
                    }),
                });
            }

            qsTemplate.addEventListener('change', loadPreview);

            qsCopyOnly.addEventListener('click', async () => {
                await copyToClipboard(currentState.message);
                await registerSend();
                qsCopyOnly.textContent = '✓ Copiado y registrado';
                setTimeout(() => { qsCopyOnly.textContent = 'Solo copiar'; }, 2000);
            });

            qsCopySend.addEventListener('click', async () => {
                await copyToClipboard(currentState.message);
                await registerSend();
                if (currentState.phone) {
                    window.open(window.waUrl(currentState.phone, currentState.message), '_blank');
                }
                close();
                // Recargar tabla para reflejar nuevo envío
                window.location.reload();
            });

            document.querySelectorAll('[data-quick-send]').forEach(btn => {
                btn.addEventListener('click', () => {
                    currentState = {
                        guestId: btn.dataset.guestId,
                        phone: btn.dataset.phone,
                    };
                    qsGuestName.textContent = btn.dataset.guestName;

                    // Pre-seleccionar última plantilla usada
                    if (btn.dataset.defaultTemplate) {
                        qsTemplate.value = btn.dataset.defaultTemplate;
                    }

                    modal.classList.add('open');
                    loadPreview();
                });
            });
        })();
    </script>
@endsection
