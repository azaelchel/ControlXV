@extends('layouts.app')

@section('title', 'Mesas — Mapa del salón')
@section('heading', 'Mapa del salón')
@section('subheading', 'Las mesas en su posición real. Toca una mesa para ver quién está sentado.')

@section('content')
<style>
    .venue-wrap { overflow-x: auto; padding-bottom: 6px; }
    .venue {
        position: relative; min-width: 940px; height: 580px; margin: 0 auto;
        background:
            linear-gradient(0deg, rgba(122,79,168,.04) 1px, transparent 1px) 0 0 / 100% 40px,
            linear-gradient(90deg, rgba(122,79,168,.04) 1px, transparent 1px) 0 0 / 40px 100%,
            #fbf8ff;
        border: 2px solid #e2d3f2; border-radius: 16px;
    }
    /* Zonas fijas del salón */
    .zone {
        position: absolute; display: grid; place-items: center; text-align: center;
        font-weight: 800; font-size: 12px; letter-spacing: .06em; text-transform: uppercase;
        color: #6b5a7e; border: 1.5px dashed #cdbbe6; border-radius: 10px; background: #fff;
    }
    .zone.pista { background: linear-gradient(135deg,#efe2fb,#e2d0f6); color:#5b3a86; border-style: solid; font-size: 15px; }
    .zone.mp { background: #fff5f9; border-color:#f0c4d2; color:#a03b62; font-size: 11px; }
    .zone.pasillo { background: #f6f2fb; color:#9985b3; }
    .zone.entrance { border-radius: 40% 8% 8% 40%; background: #f3ecfa; font-size: 10px; }
    .cocina-lbl { position:absolute; font-weight:800; font-size:12px; color:#6b5a7e; letter-spacing:.06em; }

    /* Mesas */
    .tbl {
        position: absolute; transform: translate(-50%,-50%);
        width: 72px; height: 84px; border-radius: 12px; cursor: pointer;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        color: #fff; border: 2px solid rgba(255,255,255,.7); box-shadow: 0 6px 16px rgba(80,40,120,.18);
        transition: transform .1s, box-shadow .15s; user-select: none;
    }
    .tbl:hover { transform: translate(-50%,-50%) scale(1.06); box-shadow: 0 10px 22px rgba(80,40,120,.28); z-index: 5; }
    .tbl.horizontal { width: 92px; height: 58px; }
    .tbl.vertical { width: 64px; height: 96px; }
    .tbl.principal { width: 72px; height: 84px; }
    .tbl .num { font-size: 22px; font-weight: 900; line-height: 1; }
    .tbl .occ { font-size: 12px; font-weight: 700; opacity: .95; margin-top: 3px; }
    .tbl.libre  { background: #cbb8e4; color:#4a2f6b; border-color:#e6dbf5; }
    .tbl.parcial { background: linear-gradient(135deg,#b07fd8,#8a55be); }
    .tbl.casi    { background: linear-gradient(135deg,#e6b364,#cf8f3f); }
    .tbl.llena   { background: linear-gradient(135deg,#7bc59a,#3f9e6b); }
    .tbl.sobre   { background: linear-gradient(135deg,#e2726f,#c9403c); }
    .tbl.principal { background: linear-gradient(135deg,#7f5aa6,#4f2d70); border-color:#e7c978; }

    .map-legend { display:flex; gap:14px; flex-wrap:wrap; align-items:center; font-size:12px; color:#6b5a7e; }
    .map-legend i { width:16px; height:16px; border-radius:5px; display:inline-block; vertical-align:middle; margin-right:5px; }
    .venue.hide-empty .tbl.is-empty { display: none; }
    .empty-toggle { margin-left:auto; display:inline-flex; align-items:center; gap:9px; font-size:13px; font-weight:700; color:#5f4c70; cursor:pointer; white-space:nowrap; }
    .empty-toggle input { position:absolute; opacity:0; pointer-events:none; width:1px; height:1px; }
    .toggle-ui { width:42px; height:22px; border-radius:999px; background:#d7cae8; border:2px solid #c9b9df; position:relative; flex:0 0 auto; }
    .toggle-ui::after { content:''; position:absolute; width:16px; height:16px; border-radius:50%; background:#fff; left:2px; top:1px; box-shadow:0 1px 4px rgba(55,35,80,.28); transition:left .15s, background .15s; }
    .empty-toggle input:checked + .toggle-ui { background:#2d1b43; border-color:#c9b9df; }
    .empty-toggle input:checked + .toggle-ui::after { left:20px; background:#fff; }
    .tm-check { width: 14px; height: 14px; min-width: 14px; accent-color: #8f55be; margin: 0; }
    .tm-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 10px; border-bottom: 1px solid #f0e9fa; cursor: pointer; }
</style>

@php
    $fillClass = function ($occ, $cap) {
        if ($cap > 0 && $occ > $cap) return 'sobre';
        if ($occ === 0) return 'libre';
        if ($cap > 0 && $occ >= $cap) return 'llena';
        if ($cap > 0 && $occ >= $cap - 2) return 'casi';
        return 'parcial';
    };
    $tableNum = fn ($name) => preg_replace('/\D+/', '', (string) $name) ?: $name;
    $horizontalTables = ['1', '3', '5', '7', '9', '11', '13', '15', '17', '19', '21', '23'];
    $orientationClass = function ($table) use ($tableNum, $horizontalTables) {
        if ($table->is_principal) {
            return 'principal';
        }

        return in_array($tableNum($table->name), $horizontalTables, true) ? 'horizontal' : 'vertical';
    };
@endphp

@php
    $tableOptionsPayload = $tableOptions->map(fn ($table) => [
        'id' => $table->id,
        'name' => $table->name,
        'available' => $table->availableSeats(),
    ])->values();
@endphp

    {{-- Resumen --}}
    <div class="card" style="margin-bottom: 16px;">
        <div class="inline" style="justify-content: space-between; flex-wrap: wrap; gap: 14px;">
            <div>
                <div class="kicker" style="color:#9b67c8; font-weight:800; font-size:12px; letter-spacing:.12em;">ACOMODO</div>
                <div style="font-size: 24px; font-weight: 800; color:#43275b;">
                    {{ $progress['seated'] }} / {{ $progress['eligible'] }}
                    <span style="font-size:14px; color:#8a72a4;">personas sentadas ({{ $progress['percent'] }}%)</span>
                </div>
                @if (collect($summary['eligible_by_category'] ?? [])->isNotEmpty())
                    <div class="small" style="margin-top: 4px; color:#8a72a4;">
                        Confirmados:
                        @foreach ($summary['eligible_by_category'] as $category => $total)
                            <strong>{{ $category }}</strong> {{ number_format($total) }}@if (! $loop->last) · @endif
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="inline" style="gap: 8px;">
                <a class="btn" href="{{ route('tables.manage') }}">🪑 Asignar</a>
                <a class="btn secondary" href="{{ route('tables.index') }}">📋 Vista lista</a>
                <a class="btn secondary" href="{{ route('tables.print') }}" target="_blank">📄 PDF</a>
            </div>
        </div>
        <div class="pbar" style="margin-top: 12px; height: 12px; border-radius:999px; background:#efe5f7; overflow:hidden;"><span style="display:block;height:100%;background:linear-gradient(90deg,#b07fd8,#8a55be); width: {{ $progress['percent'] }}%;"></span></div>
        <div class="map-legend" style="margin-top: 14px;">
            <span><i style="background:#cbb8e4;"></i>Vacía</span>
            <span><i style="background:linear-gradient(135deg,#b07fd8,#8a55be);"></i>Ocupándose</span>
            <span><i style="background:linear-gradient(135deg,#e6b364,#cf8f3f);"></i>Casi llena</span>
            <span><i style="background:linear-gradient(135deg,#7bc59a,#3f9e6b);"></i>Completa</span>
            <span><i style="background:linear-gradient(135deg,#e2726f,#c9403c);"></i>Sobrecupo</span>
            <label class="empty-toggle">
                <input type="checkbox" id="show-empty">
                <span class="toggle-ui" aria-hidden="true"></span>
                <span>Mostrar mesas vacías</span>
            </label>
        </div>
    </div>

    {{-- Croquis --}}
    <div class="card">
        <div class="venue-wrap">
            <div class="venue" id="venue">
                {{-- Zonas fijas --}}
                <div class="zone entrance" style="left:0; top:33%; width:3.2%; height:26%;">Entrada</div>
                <div class="zone" style="left:4%; top:4%; width:33%; height:10%;">Pasillo trasero</div>
                <div class="zone" style="left:40%; top:1%; width:17%; height:13%;">Música</div>
                <div class="zone" style="left:59%; top:4%; width:30%; height:10%;">Acceso a baños</div>
                <div class="cocina-lbl" style="left:91%; top:5%;">🍳 Cocina</div>
                <div class="zone pista" style="left:40%; top:24%; width:17%; height:30%;">Pista</div>
                <div class="zone pasillo" style="left:4%; top:85%; width:85%; height:11%;">Pasillo</div>

                {{-- Mesas --}}
                @foreach ($tables as $table)
                    @php
                        $occ = $table->occupiedSeats();
                        $cap = (int) $table->capacity;
                        $x = $table->position_x ?? 50;
                        $y = $table->position_y ?? 50;
                        $occupants = $table->assignments
                            ->filter(fn ($a) => $a->companion)
                            ->sortBy(fn ($a) => $a->companion->invited_group)
                            ->map(fn ($a) => [
                                'id'    => $a->companion_id,
                                'name'  => $a->companion->name,
                                'group' => $a->companion->invited_group,
                                'type'  => $a->companion->type ?: '—',
                            ])->values();
                    @endphp
                    <div class="tbl {{ $orientationClass($table) }} {{ $table->is_principal ? 'principal' : $fillClass($occ, $cap) }} {{ $occ === 0 && ! $table->is_principal ? 'is-empty' : '' }}"
                        style="left: {{ $x }}%; top: {{ $y }}%;"
                        data-name="{{ $table->name }}"
                        data-cap="{{ $cap }}"
                        data-occ="{{ $occ }}"
                        data-bulk-url="{{ route('tables.bulk', $table) }}"
                        data-occupants='@json($occupants, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP)'
                        title="{{ $table->name }} — {{ $occ }}/{{ $cap }}">
                        <span class="num">{{ $tableNum($table->name) }}</span>
                        <span class="occ">{{ $occ }}/{{ $cap }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        <p class="small" style="margin: 10px 0 0; color:#8a72a4;">Toca una mesa para ver quién está sentado. Cada mesa admite máximo 12 personas; no es necesario llenar todas.</p>
    </div>

    {{-- Modal detalle de mesa --}}
    <div class="modal-bg" id="tbl-modal" style="position:fixed; inset:0; background:rgba(20,15,30,.55); display:none; align-items:center; justify-content:center; z-index:100; padding:20px;">
        <div class="modal-box" style="background:#fff; border-radius:16px; max-width:520px; width:100%; max-height:90vh; overflow:auto; padding:22px;">
            <div class="inline" style="justify-content:space-between; align-items:start; margin-bottom:12px;">
                <div>
                    <div class="section-kicker">Mesa</div>
                    <h3 class="section-title" style="margin:0;" id="tm-name">—</h3>
                    <div class="small" id="tm-occ" style="margin-top:4px;"></div>
                </div>
                <button type="button" class="btn ghost" id="tm-close">✕</button>
            </div>
            <div id="tm-list"></div>
            <div class="inline" style="margin-top:14px; justify-content:flex-end;">
                <a class="btn secondary" href="{{ route('tables.manage') }}">🪑 Ir a asignaciones</a>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('tbl-modal');
            const nameEl = document.getElementById('tm-name');
            const occEl = document.getElementById('tm-occ');
            const listEl = document.getElementById('tm-list');
            const typeColor = { 'Adulto':'#6d28b8', 'Adolescente':'#1f9e6a', 'Niño':'#d6453f' };
const csrf = @json(csrf_token());
const tableOptions = @json($tableOptionsPayload, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP);
            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
            }[char]));

            document.querySelectorAll('.tbl').forEach(el => {
                el.addEventListener('click', () => {
                    const cap = el.dataset.cap, occ = el.dataset.occ;
                    let occupants = [];
                    try { occupants = JSON.parse(el.dataset.occupants || '[]'); } catch (e) {}
                    nameEl.textContent = el.dataset.name;
                    occEl.textContent = occ + ' de ' + cap + ' lugares ocupados' + (cap - occ > 0 ? ' · ' + (cap - occ) + ' libres' : ' · capacidad llena');
if (!occupants.length) {
    listEl.innerHTML = '<p class="small" style="margin:0;">Mesa vacía, aún sin invitados sentados.</p>';
} else {
    const bulkUrl = el.dataset.bulkUrl;
    const currentTableName = el.dataset.name;
    const targetOptions = tableOptions
        .filter((table) => table.name !== currentTableName)
        .map((table) => `<option value="${table.id}">${escapeHtml(table.name)} (${table.available} libres)</option>`)
        .join('');

    listEl.innerHTML = `
        <form method="post" action="${bulkUrl}" id="tm-bulk-form" style="margin:0;"
            data-confirm-title="¿Aplicar cambios a la mesa ${escapeHtml(currentTableName)}?"
            data-confirm-text="Mover o quitará solo los invitados seleccionados. No se elimina ninguna persona del sistema."
            data-confirm-button="Sí, aplicar"
            data-confirm-color="#8f55be"
            data-confirm-icon="warning">
            <input type="hidden" name="_token" value="${csrf}">
                                <input type="hidden" name="action" value="" data-bulk-action-input>
            <div class="inline" style="justify-content:space-between; gap:10px; margin-bottom:10px; flex-wrap:wrap;">
                <button type="button" class="btn small secondary" data-select-all>Seleccionar todos</button>
                <span class="small" data-selected-count>0 seleccionados</span>
                <span class="small" data-bulk-error style="display:none; color:#d8527f; font-weight:700;"></span>
            </div>
            <div style="border:1px solid #f0e9fa; border-radius:12px; overflow:hidden;">
                ${occupants.map((c, i) => `
                    <label class="tm-row">
                        <span class="inline" style="gap:8px; min-width:0;">
                            <input class="tm-check" type="checkbox" name="companion_ids[]" value="${c.id}" data-companion-check>
                            <span style="min-width:0;"><strong>${i + 1}.</strong> ${escapeHtml(c.name)} <span class="small" style="color:#9b8ab0;">· ${escapeHtml(c.group)}</span></span>
                        </span>
                        <span class="pill" style="background:${(typeColor[c.type]||'#8a8a96')}1a; color:${typeColor[c.type]||'#8a8a96'}; font-size:11px;">${escapeHtml(c.type)}</span>
                    </label>`).join('')}
            </div>
            <div class="inline" style="justify-content:space-between; gap:8px; margin-top:12px; flex-wrap:wrap;">
                <select name="target_table_id" style="min-width:190px; flex:1;">
                    <option value="">Mover a mesa...</option>
                    ${targetOptions}
                </select>
                <button type="submit" data-bulk-action="move" class="btn small">Mover seleccionados</button>
                <button type="submit" data-bulk-action="unassign" class="btn small danger"
                    data-confirm-title="¿Quitar seleccionados de ${escapeHtml(currentTableName)}?"
                    data-confirm-color="#d8527f">Quitar seleccionados</button>
            </div>
        </form>`;
}
                    modal.style.display = 'flex';
                });
            });
listEl.addEventListener('click', (event) => {
    const button = event.target.closest('[data-select-all]');
    if (!button) return;

    const checks = [...listEl.querySelectorAll('[data-companion-check]')];
    const shouldCheck = checks.some((check) => !check.checked);
    checks.forEach((check) => check.checked = shouldCheck);
    listEl.dispatchEvent(new Event('change', { bubbles: true }));
});

listEl.addEventListener('change', () => {
    const selected = listEl.querySelectorAll('[data-companion-check]:checked').length;
    const label = listEl.querySelector('[data-selected-count]');
    const error = listEl.querySelector('[data-bulk-error]');
    if (label) label.textContent = selected + ' seleccionados';
    if (error) {
        error.style.display = 'none';
        error.textContent = '';
    }
});

listEl.addEventListener('submit', (event) => {
    const form = event.target.closest('#tm-bulk-form');
    if (!form) return;

    const submitter = event.submitter;
    const actionInput = form.querySelector('[data-bulk-action-input]');
    if (submitter?.dataset.bulkAction && actionInput) {
        actionInput.value = submitter.dataset.bulkAction;
    }
    const action = actionInput?.value || '';
    const selected = form.querySelectorAll('[data-companion-check]:checked').length;
    const error = form.querySelector('[data-bulk-error]');
    if (error) {
        error.style.display = 'none';
        error.textContent = '';
    }

    if (selected < 1) {
        event.preventDefault();
        event.stopPropagation();
        if (error) {
            error.textContent = 'Selecciona al menos un invitado.';
            error.style.display = '';
        }
        return;
    }

    if (action === 'move' && !form.target_table_id.value) {
        event.preventDefault();
        event.stopPropagation();
        if (error) {
            error.textContent = 'Elige una mesa destino.';
            error.style.display = '';
        }
        return;
    }

    if (action === 'unassign') {
        form.dataset.confirmTitle = '¿Quitar seleccionados de esta mesa?';
        form.dataset.confirmText = 'Los invitados seleccionados quedarán sin mesa. No se elimina ninguna persona del sistema.';
        form.dataset.confirmButton = 'Sí, quitar de mesa';
        form.dataset.confirmColor = '#d8527f';
    } else {
        form.dataset.confirmTitle = '¿Mover seleccionados a otra mesa?';
        form.dataset.confirmText = 'Los invitados seleccionados se moverán a la mesa destino.';
        form.dataset.confirmButton = 'Sí, mover';
        form.dataset.confirmColor = '#8f55be';
    }
});

const close = () => { modal.style.display = 'none'; };
            document.getElementById('tm-close').addEventListener('click', close);
            modal.addEventListener('click', e => { if (e.target === modal) close(); });

            // Mostrar/ocultar mesas vacías (por defecto ocultas; se recuerda en el navegador).
            const venue = document.getElementById('venue');
            const showEmpty = document.getElementById('show-empty');
            const apply = (on) => venue.classList.toggle('hide-empty', !on);
            const saved = localStorage.getItem('tables_show_empty') === '1';
            showEmpty.checked = saved;
            apply(saved);
            showEmpty.addEventListener('change', () => {
                localStorage.setItem('tables_show_empty', showEmpty.checked ? '1' : '0');
                apply(showEmpty.checked);
            });
        })();
    </script>
@endsection
