@extends('layouts.app')

@section('title', 'Historial de mensajes')
@section('heading', 'Historial de mensajes enviados')
@section('subheading', 'Línea de tiempo agrupada por fecha con cada mensaje preparado y su respuesta')

@section('content')
    <style>
        .filter-row { display: grid; grid-template-columns: 1.5fr 1fr 1fr 0.9fr 0.9fr auto; gap: 12px; align-items: end; }
        .filter-row > div { min-width: 0; }
        .filter-row label { display: block; font-size: 12px; font-weight: 600; color: var(--muted); margin-bottom: 4px; }
        .filter-row input, .filter-row select { width: 100%; box-sizing: border-box; height: 42px; }

        .tpl-pills { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; }
        .tpl-pill {
            padding: 8px 14px; border-radius: 999px; border: 1px solid var(--border);
            background: white; color: var(--text); font-size: 13px; font-weight: 500;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .tpl-pill.active { background: var(--primary); color: white; border-color: var(--primary); }
        .tpl-pill .count {
            background: rgba(0,0,0,0.08); padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 700;
        }
        .tpl-pill.active .count { background: rgba(255,255,255,0.25); }

        .day-group { margin-bottom: 22px; }
        .day-header {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 10px; padding: 8px 0;
            font-weight: 700; color: var(--primary-dark);
        }
        .day-header .day-line { flex: 1; height: 1px; background: var(--border); }
        .day-header .day-count { font-size: 12px; color: var(--muted); font-weight: 600; }

        .day-summary { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .sum-chip { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 600; color: var(--text); background: white; border: 1px solid var(--border); border-radius: 999px; padding: 5px 12px; text-decoration: none; cursor: pointer; transition: box-shadow .15s; }
        .sum-chip:hover { box-shadow: 0 2px 8px rgba(80,40,120,0.18); }
        .sum-chip.active { box-shadow: 0 0 0 2px var(--primary-dark); }
        .sum-chip strong { font-weight: 800; }
        .sum-chip.ok { background: #f3fbf6; border-color: #cde6d5; color: #1f7a44; }
        .sum-chip.no { background: #fff3f7; border-color: #f0c4d2; color: #b3325b; }
        .sum-chip.wait { background: #fff8e8; border-color: #ead4a2; color: #8a6100; }

        .send-row {
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 8px;
            display: grid;
            grid-template-columns: 80px 1fr auto auto;
            gap: 14px;
            align-items: start;
        }
        .send-row .time { font-size: 13px; color: var(--muted); font-weight: 600; padding-top: 2px; }
        .send-row .who strong { display: block; font-size: 14px; }
        .send-row .who .meta { font-size: 12px; color: var(--muted); margin-top: 2px; }
        .send-row .tpl { font-size: 12px; padding: 4px 10px; background: var(--primary-soft); color: var(--primary-dark); border-radius: 999px; font-weight: 600; }
        .send-row .actions { display: flex; gap: 6px; }
        .send-row .icon-btn { width: 30px; height: 30px; padding: 0; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; }

        .preview-modal { position: fixed; inset: 0; background: rgba(20,15,30,0.55); display: none; align-items: center; justify-content: center; z-index: 100; padding: 20px; }
        .preview-modal.open { display: flex; }
        .preview-box { background: white; border-radius: 16px; max-width: 640px; width: 100%; max-height: 92vh; overflow: auto; padding: 24px; }
    </style>

    {{-- Acciones rápidas --}}
    <div class="card" style="margin-bottom: 18px;">
        <div class="inline" style="justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div>
                <div class="section-kicker">Navegación</div>
                <h3 class="section-title" style="margin: 0;">{{ number_format($sends->total()) }} envíos en total</h3>
            </div>
            <div class="inline" style="gap: 8px;">
                <a href="{{ route('message-sends.index') }}" class="btn secondary">← Volver al panel</a>
                <a href="{{ route('message-sends.create') }}" class="btn">+ Envío masivo</a>
            </div>
        </div>
    </div>

    {{-- Pills de plantillas (filtro rápido) --}}
    @if ($templates->isNotEmpty())
        <div class="tpl-pills">
            <a href="{{ route('message-sends.history', request()->except(['template_id', 'page'])) }}" class="tpl-pill {{ ! $templateFilter ? 'active' : '' }}">
                Todas <span class="count">{{ $totalsByTemplate->sum() }}</span>
            </a>
            @foreach ($templates as $template)
                <a href="{{ route('message-sends.history', array_merge(request()->except(['page']), ['template_id' => $template->id])) }}"
                   class="tpl-pill {{ $templateFilter === $template->id ? 'active' : '' }}">
                    {{ $template->name }} <span class="count">{{ $totalsByTemplate[$template->id] ?? 0 }}</span>
                </a>
            @endforeach
        </div>
    @endif

    {{-- Filtros --}}
    <div class="card" style="margin-bottom: 18px;">
        <form method="get" action="{{ route('message-sends.history') }}" class="filter-row">
            @if ($templateFilter)
                <input type="hidden" name="template_id" value="{{ $templateFilter }}">
            @endif
            <div>
                <label>Buscar familia</label>
                <input type="text" name="q" value="{{ $nameQuery }}" placeholder="Nombre…">
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
                <label>Plantilla específica</label>
                <select name="template_id" onchange="this.form.submit()">
                    <option value="">Todas</option>
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}" @selected($templateFilter === $template->id)>{{ $template->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Desde</label>
                <input type="date" name="from" value="{{ $dateFrom }}">
            </div>
            <div>
                <label>Hasta</label>
                <input type="date" name="to" value="{{ $dateTo }}">
            </div>
            <div class="inline" style="gap: 6px;">
                <button class="btn secondary" type="submit">Filtrar</button>
                @if ($statusFilter || $templateFilter || $nameQuery || $dateFrom || $dateTo || $respFilter)
                    <a href="{{ route('message-sends.history') }}" class="btn secondary">Limpiar</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Línea de tiempo agrupada por fecha --}}
    <div class="card">
        <div class="inline" style="justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
            <div class="small">Mostrando {{ $sends->firstItem() ?? 0 }}–{{ $sends->lastItem() ?? 0 }} de {{ number_format($sends->total()) }} envío(s)</div>
            <div class="inline" style="gap: 14px; flex-wrap: wrap;">
                <form method="get" action="{{ route('message-sends.history') }}" style="display: inline;">
                    @foreach (request()->except(['sort', 'page']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <label style="display: inline-flex; align-items: center; gap: 6px;">
                        <span class="small">Ordenar por:</span>
                        <select name="sort" onchange="this.form.submit()" style="width: auto; height: 36px;">
                            <option value="hora" @selected($sortBy === 'hora')>Hora de envío</option>
                            <option value="resp_desc" @selected($sortBy === 'resp_desc')>Último en responder (arriba)</option>
                            <option value="resp_asc" @selected($sortBy === 'resp_asc')>Primero en responder (arriba)</option>
                            <option value="nombre" @selected($sortBy === 'nombre')>Nombre (A→Z)</option>
                        </select>
                    </label>
                </form>
                <form method="get" action="{{ route('message-sends.history') }}" style="display: inline;">
                    @foreach (request()->except(['per_page', 'page']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <label style="display: inline-flex; align-items: center; gap: 6px;">
                        <span class="small">Mostrar:</span>
                        <select name="per_page" onchange="this.form.submit()" style="width: auto; height: 36px;">
                            @foreach ([30, 60, 100, 200, 500] as $size)
                                <option value="{{ $size }}" @selected((int) $perPage === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                        <span class="small">por página</span>
                    </label>
                </form>
            </div>
        </div>

        @php
            $groupedSends = $sends->getCollection()->groupBy(fn ($s) => $s->sent_at?->format('Y-m-d') ?? '—');
        @endphp

        @forelse ($groupedSends as $date => $dailySends)
            @php
                $dailySends = $dailySends->sort(function ($a, $b) use ($sortBy) {
                    $an = $a->guest?->name ?? '';
                    $bn = $b->guest?->name ?? '';

                    if ($sortBy === 'nombre') {
                        return strcasecmp($an, $bn);
                    }

                    if ($sortBy === 'resp_desc' || $sortBy === 'resp_asc') {
                        $ar = $a->publicLink?->responded_at;
                        $br = $b->publicLink?->responded_at;
                        // Los que aún no responden se van al final.
                        if (! $ar && ! $br) { return strcasecmp($an, $bn); }
                        if (! $ar) { return 1; }
                        if (! $br) { return -1; }
                        $cmp = $ar->timestamp <=> $br->timestamp;
                        return $sortBy === 'resp_desc' ? -$cmp : $cmp;
                    }

                    // 'hora' (default): mismo minuto = misma hora; dentro del minuto, por nombre.
                    $ma = $a->sent_at?->format('Y-m-d H:i') ?? '';
                    $mb = $b->sent_at?->format('Y-m-d H:i') ?? '';
                    if ($ma !== $mb) { return strcmp($mb, $ma); }
                    return strcasecmp($an, $bn);
                })->values();
            @endphp
            @php $sum = $daySummaries[$date] ?? ['enviados' => $dailySends->count(), 'respondieron' => 0, 'confirmados' => 0, 'no_asistiran' => 0, 'sin_responder' => 0]; @endphp
            <div class="day-group">
                <div class="day-header">
                    <span>{{ $date !== '—' ? \Carbon\Carbon::parse($date)->isoFormat('dddd, D [de] MMMM [de] YYYY') : 'Sin fecha' }}</span>
                    <span class="day-line"></span>
                    <span class="day-count">
                        {{ $sum['enviados'] }} envío{{ $sum['enviados'] === 1 ? '' : 's' }}
                        @if ($dailySends->count() < $sum['enviados']) · mostrando {{ $dailySends->count() }} en esta página @endif
                    </span>
                </div>

                @php
                    $dayBase = array_filter([
                        'q'           => $nameQuery,
                        'status'      => $statusFilter,
                        'template_id' => $templateFilter ?: null,
                        'per_page'    => $perPage,
                        'sort'        => $sortBy !== 'hora' ? $sortBy : null,
                        'from'        => $date,
                        'to'          => $date,
                    ], fn ($v) => $v !== '' && $v !== null);
                    $isDayFiltered = ($dateFrom === $date && $dateTo === $date);
                @endphp
                <div class="day-summary">
                    <a href="{{ route('message-sends.history', $dayBase) }}" class="sum-chip {{ ($respFilter === '' && $isDayFiltered) ? 'active' : '' }}" title="Ver todos los de este día">📤 Enviados <strong>{{ $sum['enviados'] }}</strong></a>
                    <a href="{{ route('message-sends.history', $dayBase + ['resp' => 'responded']) }}" class="sum-chip {{ $respFilter === 'responded' ? 'active' : '' }}" title="Ver solo los que respondieron">✓ Respondieron <strong>{{ $sum['respondieron'] }}</strong></a>
                    <a href="{{ route('message-sends.history', $dayBase + ['resp' => 'confirmed']) }}" class="sum-chip ok {{ $respFilter === 'confirmed' ? 'active' : '' }}" title="Ver solo los que confirmaron/validaron su asistencia">💜 Confirmaron <strong>{{ $sum['confirmados'] }}</strong></a>
                    <a href="{{ route('message-sends.history', $dayBase + ['resp' => 'declined']) }}" class="sum-chip no {{ $respFilter === 'declined' ? 'active' : '' }}" title="Ver solo los que dijeron que no asistirán">✕ No asistirán <strong>{{ $sum['no_asistiran'] }}</strong></a>
                    <a href="{{ route('message-sends.history', $dayBase + ['resp' => 'pending']) }}" class="sum-chip wait {{ $respFilter === 'pending' ? 'active' : '' }}" title="Ver solo los que aún no responden">⏳ Sin responder <strong>{{ $sum['sin_responder'] }}</strong></a>
                </div>

                @foreach ($dailySends as $send)
                    @php
                        $link = $send->publicLink;
                        $stateLabel = 'Sin link';
                        $stateClass = 'status-default';
                        $stateIcon = '—';
                        if ($link) {
                            if ($link->responded_at) {
                                $timing = $link->responded_at->diffForHumans($send->sent_at, true, true) . ' después';
                                if ($link->response === 'declined') {
                                    $stateLabel = 'Rechazó — no asistirá';
                                    $stateClass = 'status-no-asistira';
                                    $stateIcon = '✕';
                                } elseif ($link->response === 'validated') {
                                    $stateLabel = 'Validó sus datos';
                                    $stateClass = 'status-confirmado';
                                    $stateIcon = '✓';
                                } else {
                                    $stateLabel = 'Confirmó asistencia';
                                    $stateClass = 'status-confirmado';
                                    $stateIcon = '✓';
                                }
                                $stateLabel .= ' · ' . $timing;
                            } elseif ($link->closed_reason === 'cancelled') {
                                $stateLabel = 'Cancelado';
                                $stateClass = 'status-no-asistira';
                                $stateIcon = '✕';
                            } elseif ($link->isExpired()) {
                                $stateLabel = 'Vencido sin respuesta';
                                $stateClass = 'status-no-asistira';
                                $stateIcon = '⏰';
                            } elseif ($link->opened_at) {
                                $stateLabel = 'Abrió, no contestó';
                                $stateClass = 'status-pendiente';
                                $stateIcon = '👁';
                            } else {
                                $stateLabel = 'Sin abrir';
                                $stateClass = 'status-considerado';
                                $stateIcon = '📤';
                            }
                        }

                        // Teléfono ACTUAL del invitado (para reenviar al número vigente,
                        // no al que se usó cuando se generó el envío). Formato internacional.
                        $currentPhone = $send->guest?->phone ?: $send->phone;
                        $phoneDigits  = preg_replace('/[^0-9]/', '', (string) $currentPhone);
                        $phoneIntl    = $phoneDigits === '' ? null : (strlen($phoneDigits) === 10 ? '52'.$phoneDigits : $phoneDigits);
                        $phoneChanged = $send->guest?->phone && $send->phone && preg_replace('/[^0-9]/', '', $send->guest->phone) !== preg_replace('/[^0-9]/', '', $send->phone);
                    @endphp
                    <div class="send-row">
                        <div class="time">{{ $send->sent_at?->format('H:i') ?? '—' }}</div>
                        <div class="who">
                            <strong>{{ $send->guest?->name ?? '—' }}</strong>
                            <div class="meta">
                                <span class="tpl">{{ $send->template?->name ?? 'Plantilla borrada' }}</span>
                                · {{ $send->guest?->status ?? '' }}
                                @if ($currentPhone)
                                    · 📱 {{ $currentPhone }}
                                    @if ($phoneChanged)
                                        <span title="El número cambió; se envió antes al {{ $send->phone }}" style="color:#b26a00; font-weight:600;">(actualizado)</span>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                            <span class="pill {{ $stateClass }}">{{ $stateIcon }} {{ $stateLabel }}</span>
                            @if ($link && ! $link->responded_at && ! $link->isExpired() && $link->closed_reason !== 'cancelled' && $link->expires_at)
                                <span class="pill status-considerado" data-countdown="{{ $link->expires_at->toIso8601String() }}"
                                    title="Tiempo restante antes de que venza el link" style="font-weight:700;">⏳ …</span>
                            @endif
                            @if ($link && $link->opened_at && !$link->responded_at && !$link->isExpired() && $link->closed_reason !== 'cancelled')
                                <form method="post" action="{{ route('message-sends.reset-opened', $link) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn ghost" style="padding: 3px 9px; font-size: 11px; border-radius: 8px;"
                                        title="Desmarcar como abierto (si lo abriste tú por error)">↩ Sin abrir</button>
                                </form>
                            @endif
                            @if ($link && $link->responded_at && $link->closed_reason === 'responded')
                                <form method="post" action="{{ route('message-sends.reopen-link', $link) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn ghost" style="padding: 3px 9px; font-size: 11px; border-radius: 8px; border-color: #c6a0e8; color: #7a3fbf;"
                                        title="Reabrir el link para que el invitado pueda actualizar sus acompañantes">🔓 Reabrir</button>
                                </form>
                            @endif
                        </div>
                        <div class="actions">
                            <button type="button" class="btn secondary icon-btn" data-preview-message
                                data-message="{{ $send->rendered_message }}"
                                data-guest="{{ $send->guest?->name }}"
                                data-when="{{ $send->sent_at?->format('d/m/Y H:i') }}"
                                title="Ver mensaje completo">👁</button>
                            <button type="button" class="btn secondary icon-btn" data-copy-msg
                                data-message="{{ $send->rendered_message }}"
                                title="Copiar mensaje">📋</button>
                            @if ($phoneIntl)
                                <button type="button" class="btn secondary icon-btn" data-wa-send
                                    data-phone="{{ $phoneIntl }}"
                                    data-message="{{ $send->rendered_message }}"
                                    title="Enviar por WhatsApp al número actual{{ $phoneChanged ? ' (actualizado)' : '' }}">💬</button>
                            @endif
                            @if ($link && $link->responded_at && $send->guest)
                                <button type="button" class="btn secondary icon-btn" data-view-companions
                                    data-guest-id="{{ $send->guest->id }}"
                                    style="background: #ecf7e9; color: #256141; border-color: #c6e4be;"
                                    title="Ver los invitados que registraron al confirmar">👥</button>
                            @endif
                            @if ($link)
                                <a class="btn secondary icon-btn"
                                    href="{{ route('guest-review.show', ['guest' => $send->guest, 'token' => $link->token]) }}"
                                    target="_blank"
                                    title="Ver link de revisión">🔗</a>
                            @endif
                            <form method="post" action="{{ route('message-sends.destroy', $send) }}" style="display: inline;"
                                data-confirm-title="¿Eliminar este envío del histórico?"
                                data-confirm-text="Solo se borra el registro. El link y la familia quedan intactos."
                                data-confirm-button="Sí, eliminar"
                                data-confirm-color="#d8527f">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn danger icon-btn" title="Eliminar">🗑️</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <div class="empty" style="padding: 40px;">No hay envíos con esos filtros.</div>
        @endforelse

        @if ($sends->hasPages())
            <div style="margin-top: 14px;">{{ $sends->links() }}</div>
        @endif
    </div>

    {{-- Modal: Invitados registrados --}}
    <div class="preview-modal" id="companions-modal-history">
        <div class="preview-box" style="max-width: 760px;">
            <div class="inline" style="justify-content: space-between; align-items: start; margin-bottom: 14px;">
                <div>
                    <div class="section-kicker">Invitados registrados</div>
                    <h3 class="section-title" style="margin: 0;" id="cmph-guest-name">—</h3>
                </div>
                <button type="button" class="btn ghost" id="cmph-close">✕</button>
            </div>

            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 14px;">
                <div style="padding: 12px; background: #faf6ff; border-radius: 10px; text-align: center;">
                    <div style="font-size: 11px; color: var(--muted); text-transform: uppercase; font-weight: 700;">Total</div>
                    <div style="font-size: 22px; font-weight: 800; color: var(--primary-dark);" id="cmph-total">0</div>
                    <div style="font-size: 11px; color: var(--muted);" id="cmph-total-expected"></div>
                </div>
                <div style="padding: 12px; background: #faf6ff; border-radius: 10px; text-align: center;">
                    <div style="font-size: 11px; color: var(--muted); text-transform: uppercase; font-weight: 700;">Adultos</div>
                    <div style="font-size: 22px; font-weight: 800;" id="cmph-adults">0</div>
                    <div style="font-size: 11px; color: var(--muted);" id="cmph-adults-expected"></div>
                </div>
                <div style="padding: 12px; background: #faf6ff; border-radius: 10px; text-align: center;">
                    <div style="font-size: 11px; color: var(--muted); text-transform: uppercase; font-weight: 700;">Adolescentes</div>
                    <div style="font-size: 22px; font-weight: 800;" id="cmph-adolescents">0</div>
                    <div style="font-size: 11px; color: var(--muted);" id="cmph-adolescents-expected"></div>
                </div>
                <div style="padding: 12px; background: #faf6ff; border-radius: 10px; text-align: center;">
                    <div style="font-size: 11px; color: var(--muted); text-transform: uppercase; font-weight: 700;">Niños</div>
                    <div style="font-size: 22px; font-weight: 800;" id="cmph-children">0</div>
                    <div style="font-size: 11px; color: var(--muted);" id="cmph-children-expected"></div>
                </div>
            </div>

            <div class="table-wrap" style="max-height: 50vh; overflow: auto;">
                <table style="min-width: 100%;">
                    <thead>
                        <tr><th>Nombre</th><th>Tipo</th><th>Género</th><th>Origen</th><th>Registrado</th></tr>
                    </thead>
                    <tbody id="cmph-list"></tbody>
                </table>
            </div>

            <div id="cmph-loading" class="small" style="margin-top: 12px; display: none;">Cargando…</div>
        </div>
    </div>

    {{-- Modal preview --}}
    <div class="preview-modal" id="preview-modal">
        <div class="preview-box">
            <div class="inline" style="justify-content: space-between; align-items: start; margin-bottom: 14px;">
                <div>
                    <div class="section-kicker">Mensaje enviado</div>
                    <h3 class="section-title" style="margin: 0;" id="preview-guest">—</h3>
                    <div class="small" id="preview-when"></div>
                </div>
                <button type="button" class="btn ghost" id="preview-close">✕</button>
            </div>
            <textarea readonly id="preview-text" rows="14" style="width: 100%; font-family: 'SF Mono', Menlo, monospace; font-size: 13px;"></textarea>
            <div class="inline" style="margin-top: 14px; gap: 8px; justify-content: flex-end;">
                <button type="button" class="btn" id="preview-copy">Copiar mensaje</button>
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

        document.querySelectorAll('[data-copy-msg]').forEach(btn => {
            btn.addEventListener('click', async () => {
                await copyToClipboard(btn.dataset.message);
                const orig = btn.textContent;
                btn.textContent = '✓';
                setTimeout(() => { btn.textContent = orig; }, 1500);
            });
        });

        (function () {
            const modal = document.getElementById('preview-modal');
            const txt = document.getElementById('preview-text');
            const guestEl = document.getElementById('preview-guest');
            const whenEl = document.getElementById('preview-when');
            const closeBtn = document.getElementById('preview-close');
            const copyBtn = document.getElementById('preview-copy');

            document.querySelectorAll('[data-preview-message]').forEach(btn => {
                btn.addEventListener('click', () => {
                    txt.value = btn.dataset.message;
                    guestEl.textContent = btn.dataset.guest || '—';
                    whenEl.textContent = 'Enviado el ' + (btn.dataset.when || '');
                    modal.classList.add('open');
                });
            });

            const close = () => modal.classList.remove('open');
            closeBtn.addEventListener('click', close);
            modal.addEventListener('click', e => { if (e.target === modal) close(); });

            copyBtn.addEventListener('click', async () => {
                await copyToClipboard(txt.value);
                copyBtn.textContent = '✓ Copiado';
                setTimeout(() => { copyBtn.textContent = 'Copiar mensaje'; }, 1500);
            });
        })();

        // Modal de invitados registrados
        (function () {
            const modal = document.getElementById('companions-modal-history');
            const nameEl = document.getElementById('cmph-guest-name');
            const list = document.getElementById('cmph-list');
            const loading = document.getElementById('cmph-loading');
            const closeBtn = document.getElementById('cmph-close');
            const tot = document.getElementById('cmph-total');
            const ad = document.getElementById('cmph-adults');
            const adol = document.getElementById('cmph-adolescents');
            const ni = document.getElementById('cmph-children');
            const totExp = document.getElementById('cmph-total-expected');
            const adExp = document.getElementById('cmph-adults-expected');
            const adolExp = document.getElementById('cmph-adolescents-expected');
            const niExp = document.getElementById('cmph-children-expected');

            const close = () => modal.classList.remove('open');
            closeBtn.addEventListener('click', close);
            modal.addEventListener('click', e => { if (e.target === modal) close(); });

            const fmtExpected = (got, exp) => {
                if (exp === 0 && got === 0) return '';
                if (got === exp) return `✓ de ${exp}`;
                if (got < exp) return `de ${exp} (faltan ${exp - got})`;
                return `de ${exp} (${got - exp} extra)`;
            };

            const sourceBadge = (src) => {
                if (src === 'link') return '<span class="pill status-confirmado" style="font-size:10px;padding:2px 8px;">🔗 Link</span>';
                if (src === 'manual') return '<span class="pill status-default" style="font-size:10px;padding:2px 8px;">✋ Manual</span>';
                return '<span class="pill" style="font-size:10px;padding:2px 8px;background:#f0e9fa;color:var(--muted);">— Histórico</span>';
            };

            document.querySelectorAll('[data-view-companions]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const guestId = btn.dataset.guestId;
                    list.innerHTML = '';
                    loading.style.display = 'block';
                    modal.classList.add('open');

                    try {
                        const res = await fetch(`/message-sends/companions/${guestId}`, { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();

                        nameEl.textContent = data.guest_name + ' · ' + data.status;
                        tot.textContent = data.counts.total;
                        ad.textContent = data.counts.adults;
                        adol.textContent = data.counts.adolescents;
                        ni.textContent = data.counts.children;
                        totExp.textContent = fmtExpected(data.counts.total, data.expected.total);
                        adExp.textContent = fmtExpected(data.counts.adults, data.expected.adults);
                        adolExp.textContent = fmtExpected(data.counts.adolescents, data.expected.adolescents);
                        niExp.textContent = fmtExpected(data.counts.children, data.expected.children);

                        if (data.companions.length === 0) {
                            list.innerHTML = '<tr><td colspan="5" class="empty">Aún no hay invitados registrados.</td></tr>';
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
                        list.innerHTML = '<tr><td colspan="5" class="empty">Error cargando invitados.</td></tr>';
                    } finally {
                        loading.style.display = 'none';
                    }
                });
            });
        })();

        document.querySelectorAll('[data-wa-send]').forEach(btn => {
            btn.addEventListener('click', () => {
                // Forzar la app de WhatsApp (wa.me abre la app en el teléfono),
                // ignorando la preferencia App/Web del resto del sistema.
                const phone = btn.dataset.phone;
                const message = btn.dataset.message;
                const url = 'https://wa.me/' + phone + (message ? '?text=' + encodeURIComponent(message) : '');
                window.open(url, '_blank');
            });
        });

        // Contador en vivo: cuánto le queda a cada link antes de vencer.
        (function () {
            const els = Array.from(document.querySelectorAll('[data-countdown]'));
            if (!els.length) return;
            const fmt = (ms) => {
                if (ms <= 0) return '⏰ Vencido';
                const s = Math.floor(ms / 1000);
                const d = Math.floor(s / 86400);
                const h = Math.floor((s % 86400) / 3600);
                const m = Math.floor((s % 3600) / 60);
                const sec = s % 60;
                if (d > 0) return `⏳ ${d}d ${h}h ${m}m`;
                if (h > 0) return `⏳ ${h}h ${m}m`;
                if (m > 0) return `⏳ ${m}m ${sec}s`;
                return `⏳ ${sec}s`;
            };
            const tick = () => {
                const now = Date.now();
                els.forEach(el => {
                    const left = new Date(el.dataset.countdown).getTime() - now;
                    el.textContent = fmt(left);
                    el.classList.remove('status-considerado', 'status-pendiente', 'status-no-asistira');
                    if (left <= 0) el.classList.add('status-no-asistira');
                    else if (left < 12 * 3600 * 1000) el.classList.add('status-pendiente');
                    else el.classList.add('status-considerado');
                });
            };
            tick();
            setInterval(tick, 1000);
        })();
    </script>
@endsection
