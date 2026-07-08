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
    .empty-toggle { display:inline-flex; align-items:center; gap:7px; font-size:13px; font-weight:600; color:#5f4c70; cursor:pointer; }
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
            <label class="empty-toggle" style="margin-left:auto;">
                <input type="checkbox" id="show-empty"> Mostrar mesas vacías
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
                <div class="zone mp" style="left:40%; top:60%; width:17%; height:9%;">🎂 Pastel · 🎁 Regalos</div>
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
                                'name'  => $a->companion->name,
                                'group' => $a->companion->invited_group,
                                'type'  => $a->companion->type ?: '—',
                            ])->values();
                    @endphp
                    <div class="tbl {{ $table->is_principal ? 'principal' : $fillClass($occ, $cap) }} {{ $occ === 0 && ! $table->is_principal ? 'is-empty' : '' }}"
                        style="left: {{ $x }}%; top: {{ $y }}%;"
                        data-name="{{ $table->name }}"
                        data-cap="{{ $cap }}"
                        data-occ="{{ $occ }}"
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
                        listEl.innerHTML = occupants.map((c, i) => `
                            <div class="inline" style="justify-content:space-between; padding:8px 0; border-bottom:1px solid #f0e9fa;">
                                <span><strong>${i + 1}.</strong> ${c.name} <span class="small" style="color:#9b8ab0;">· ${c.group}</span></span>
                                <span class="pill" style="background:${(typeColor[c.type]||'#8a8a96')}1a; color:${typeColor[c.type]||'#8a8a96'}; font-size:11px;">${c.type}</span>
                            </div>`).join('');
                    }
                    modal.style.display = 'flex';
                });
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
