@extends('layouts.public')

@section('title', 'Revisión de asistencia | XV de Zugeily')
@section('meta_title', 'XV Zugeily | Revisión de asistencia')
@section('meta_description', 'Confirma o valida la asistencia de tu familia a los XV de Zugeily. Revisa la información de las personas que asistirán contigo.')

@php
    $isLastChance = $mode === 'last_chance';
    $isInvitationLike = in_array($mode, ['invitation', 'last_chance'], true);
    $registrationClosedDate = \App\Models\Setting::get('registration_closed_date', '30 de junio');

    $modeTitle = 'Personas que asistirán';
    $modeMessage = $isInvitationLike
        ? 'Por favor registra a las personas que asistirán contigo y confirma tu respuesta.'
        : 'Ya tenemos registrada tu respuesta. Por favor revisa que la información de las personas que asistirán sea correcta.';
    $timeLabel = $isInvitationLike ? 'para confirmar' : 'para validar';
    $heroTitle = $isInvitationLike
        ? 'Confirma tu asistencia a los XV de Zugeily'
        : 'Ya tenemos tu respuesta, solo revisa que todo esté correcto';
    $heroKicker = $isInvitationLike
        ? 'Confirmación de asistencia'
        : 'Revisa y valida tu asistencia a los XV de Zugeily';
    $sectionKicker = $isInvitationLike
        ? 'Confirmación de asistencia'
        : 'Validación de asistencia';
@endphp

@section('content')
    <div class="hero">
        <div class="brand">
            <div class="badge">XV</div>
            <div>
                <div class="kicker">Revisión y confirmación</div>
                <h1>XV Zugeily</h1>
                <p>{{ $modeMessage }}</p>
            </div>
        </div>
    </div>

    @if ($isLastChance && ! $isExpired && ! $publicLink->responded_at && $guest->status !== 'No asistirá')
        <div class="card" style="margin-bottom: 22px; border-color:#ead4a2; background:#fff8e8;">
            <div class="kicker" style="color:#b26a00;">⚠️ Última oportunidad</div>
            <h2 style="margin:6px 0;">El registro ya cerró, pero aún estás a tiempo</h2>
            <p style="margin:0;">
                El registro para confirmar asistencia cerró el <strong>{{ $registrationClosedDate }}</strong>.
                Nos encantaría contar contigo, por eso te damos un último plazo:
                te queda(n) <strong>{{ $daysRemaining }} día(s)</strong> para confirmar aquí abajo.
                Pasado este tiempo ya no podremos incluirte en el registro final. 💜
            </p>
        </div>
    @endif

    <div class="grid cols-2" style="margin-bottom: 22px;">
        <div class="metric">
            <span class="small">Invitación para</span>
            <strong>{{ $guest->name }}</strong>
        </div>
        <div class="metric">
            <span class="small">
                @if ($guest->public_link_responded_at)
                    Respuesta registrada
                @elseif ($isExpired)
                    Vigencia agotada
                @else
                    {{ $isInvitationLike ? 'Tiempo disponible' : 'Tiempo para validar' }}
                @endif
            </span>
            <strong>
                @if ($isExpired)
                    Link vencido
                @elseif ($guest->public_link_responded_at instanceof \Illuminate\Support\Carbon)
                    {{ $guest->public_link_responded_at->format('d/m/Y H:i') }}
                @else
                    {{ $daysRemaining }} día(s)
                @endif
            </strong>
        </div>
    </div>

    @if ($publicLink->opened_at && ! $publicLink->responded_at && ! $isExpired)
        <div class="card" style="margin-bottom: 22px; background:#fcf7ff;">
            <div class="small">
                @if ($isInvitationLike)
                    Les quedan {{ $daysRemaining }} día(s) {{ $timeLabel }}.
                @else
                    Tienes {{ $daysRemaining }} día(s) para revisar o corregir la información registrada.
                @endif
            </div>
        </div>
    @endif

    @if ($guest->status === 'No asistirá')
        <div class="card" style="margin-bottom: 22px; border-color:#f0c4d2; background:#fff3f7;">
            <h2>Gracias por avisarnos</h2>
            <p>Hemos registrado que tu familia no podrá asistir a los XV de Zugeily.</p>
            <div class="small">Si necesitas cambiar tu respuesta, comunícate con quien te compartió este enlace.</div>
        </div>
    @elseif ($isExpired)
        <div class="card" style="margin-bottom: 22px; border-color:#ead4a2; background:#fff8e8;">
            <h2>Este enlace ya venció</h2>
            <p>Si todavía necesitan hacer algún cambio, por favor comuníquense con quien les compartió este enlace.</p>
        </div>
    @elseif ($publicLink->responded_at)
        @php
            $eventName = \App\Models\Setting::get('event_name', 'XV de Zugeily');
            $eventDate = \App\Models\Setting::get('event_date', '');
            $eventTime = \App\Models\Setting::get('event_time', '');
            $accessInfo = \App\Models\Setting::get('access_info_text', '');
            $isAttending = in_array($publicLink->response, ['confirmed', 'validated'], true);
        @endphp

        <div class="card" style="margin-bottom: 22px; border-color:#cde6d5; background:#f3fbf6;">
            @if ($publicLink->response === 'confirmed')
                <h2>¡Gracias por confirmar! 💜</h2>
                <p>Hemos registrado la asistencia de tu familia a los <strong>{{ $eventName }}</strong>.</p>
            @elseif ($publicLink->response === 'validated')
                <h2>¡Gracias por revisar la información! 💜</h2>
                <p>Hemos validado los datos de los invitados de tu familia para los <strong>{{ $eventName }}</strong>.</p>
            @elseif ($publicLink->response === 'declined')
                <h2>Gracias por avisarnos</h2>
                <p>Hemos registrado que tu familia no podrá asistir a los <strong>{{ $eventName }}</strong>.</p>
                <div class="small" style="margin-top: 8px;">Si necesitas cambiar tu respuesta, comunícate con quien te compartió este enlace.</div>
            @endif
        </div>

        @if ($isAttending && ($eventDate || $eventTime || $accessInfo))
            {{-- Tarjeta de recordatorio: día, hora, accesos --}}
            <div class="card" style="margin-bottom: 22px; border-color: #f0c4d2; background: #fff7fa;">
                <div style="line-height: 1.6;">
                    @if ($eventDate || $eventTime)
                        <div style="font-weight: 700; color: #6b1f3f; font-size: 18px; margin-bottom: 10px;">
                            Recuerda
                        </div>
                        @if ($eventDate)
                            <div style="margin-bottom: 4px;">
                                <span style="color: #6b1f3f; font-weight: 600;">Fecha:</span> <strong>{{ $eventDate }}</strong>
                            </div>
                        @endif
                        @if ($eventTime)
                            <div style="margin-bottom: 10px;">
                                <span style="color: #6b1f3f; font-weight: 600;">Hora de recepción:</span> <strong>{{ $eventTime }}</strong>
                            </div>
                        @endif
                        <p style="margin: 8px 0 0 0;">
                            Te pedimos por favor llegar con <strong>puntualidad</strong>
                            para que puedas disfrutar de todas las sorpresas que tenemos preparadas. ✨
                        </p>
                    @endif

                    @if ($accessInfo)
                        <div style="margin-top: 16px; padding-top: 14px; border-top: 1px solid #f0c4d2;">
                            <div style="font-weight: 700; color: #6b1f3f; margin-bottom: 6px; font-size: 15px;">
                                Sobre tus accesos
                            </div>
                            <p style="margin: 0;">{{ $accessInfo }}</p>
                        </div>
                    @endif

                    <div class="small" style="margin-top: 16px; opacity: 0.85;">
                        Si necesitas corregir algún dato, comunícate con quien te compartió este enlace.
                    </div>
                </div>
            </div>
        @endif
    @endif

    @if (! $isLocked)
    <div class="card">
        <div class="inline" style="justify-content: space-between; margin-bottom: 16px;">
            <div>
                <div class="kicker">{{ $sectionKicker }}</div>
                <h2>{{ $modeTitle }}</h2>
            </div>
            <div class="small">Confirma que los datos estén correctos o actualiza la información necesaria.</div>
        </div>

        @if (! $isLocked)
            <form method="post" action="{{ $declineUrl }}"
                style="margin-bottom: 16px;"
                data-confirm-title="¿Confirmas que no podrán asistir?"
                data-confirm-text="Al aceptar, registraremos que tu familia no asistirá y ya no será necesario llenar la información de invitados."
                data-confirm-button="Sí, no podremos asistir"
                data-confirm-color="#d8527f"
                data-confirm-icon="warning">
                @csrf
                <button class="btn danger" type="submit">No podremos asistir</button>
            </form>
        @endif

        {{-- Banner instructivo personalizado --}}
        <div style="margin-bottom: 18px; padding: 16px 18px; border-radius: 14px; background: linear-gradient(135deg, #fff7e0 0%, #ffeec4 100%); border-left: 4px solid #d4a017;">
            <div style="font-weight: 700; font-size: 15px; color: #6b4a00; margin-bottom: 6px;">
                @if ($isFamilyGroup)
                    👨‍👩‍👧 Esta invitación es para tu familia / grupo
                @else
                    👋 Hola {{ trim(($guest->prefix ?? '') . ' ' . $guest->name) }}, esta invitación es para ti
                @endif
            </div>
            <div style="font-size: 14px; color: #5a3e00; line-height: 1.6;">
                @if ($isFamilyGroup)
                    Tu invitación es para <strong>{{ $totalExpected }} persona{{ $totalExpected === 1 ? '' : 's' }}</strong>.
                    Por favor anota los nombres de <strong>TODOS</strong> los integrantes que asistirán, incluyéndote a ti.
                @else
                    @if ($totalExpected === 1)
                        Tu invitación es para <strong>1 persona</strong>. Te incluimos ya en la lista para que solo confirmes tu información.
                    @else
                        Tu invitación es para <strong>{{ $totalExpected }} personas</strong>, contándote a ti.
                        Asegúrate de anotarte como el primer invitado y luego agregar a las demás personas que te acompañarán.
                    @endif
                @endif
            </div>
        </div>

        <form method="post" action="{{ $updateUrl }}" id="public-guest-review-form">
            @csrf
            @method('put')

            <div style="display:grid; gap: 14px;">
                @forelse ($rows as $index => $row)
                    <div data-review-row class="card {{ old("rows.$index.delete") ? 'row-card-removed' : '' }}" style="padding:18px; border-radius:20px; background:{{ ($row['is_self_suggestion'] ?? false) ? '#fff7e0' : '#fcf9ff' }};">
                        <div class="inline" style="justify-content: space-between; align-items: center; margin-bottom: 14px;">
                            <div>
                                <strong style="display:block; color:#4a2f60;">
                                    Invitado {{ $index + 1 }}
                                    @if ($row['is_self_suggestion'] ?? false)
                                        <span style="background:#d4a017;color:white;font-size:11px;padding:2px 8px;border-radius:999px;margin-left:6px;">👤 Eres tú</span>
                                    @endif
                                </strong>
                                <span class="row-note">{{ ($row['is_self_suggestion'] ?? false) ? 'Verifica que tu nombre esté bien escrito' : ($row['existing'] ? 'Información completa' : 'Falta completar información') }}</span>
                            </div>
                            @unless ($isLocked)
                                <div>
                                    <input type="hidden" name="rows[{{ $index }}][id]" value="{{ old("rows.$index.id", $row['id']) }}">
                                    <input type="hidden" name="rows[{{ $index }}][delete]" value="{{ old("rows.$index.delete", 0) }}" data-delete-flag>
                                    <button class="btn danger small" type="button" data-remove-row>Eliminar</button>
                                    <div class="row-note" data-remove-note style="{{ old("rows.$index.delete") ? '' : 'display:none;' }}">Se eliminará al guardar.</div>
                                </div>
                            @else
                                <input type="hidden" name="rows[{{ $index }}][id]" value="{{ old("rows.$index.id", $row['id']) }}">
                                <input type="hidden" name="rows[{{ $index }}][delete]" value="{{ old("rows.$index.delete", 0) }}" data-delete-flag>
                            @endunless
                        </div>

                        <div class="row-fields" style="display:grid; gap: 12px;">
                            <div>
                                <label>Nombre del invitado</label>
                                <input type="text" name="rows[{{ $index }}][name]" value="{{ old("rows.$index.name", $row['name']) }}" placeholder="Nombre del invitado" @disabled($isLocked)>
                            </div>
                            <div>
                                <label>Categoría</label>
                                <select name="rows[{{ $index }}][type]" data-type-select @disabled($isLocked)>
                                    @foreach ($types as $type)
                                        <option value="{{ $type }}" @selected(old("rows.$index.type", $row['type']) === $type)>{{ $type }}</option>
                                    @endforeach
                                </select>
                                @php
                                    $curType = old("rows.$index.type", $row['type']);
                                    $menuMap = [
                                        'Niño'        => ['🍔 Menú infantil — hamburguesa (2 a 10 años)', 'color:#b26a00;'],
                                        'Adolescente' => ['<span style="font-size:1.5em; vertical-align:-3px;">🍔</span> Menú de adolescente — hamburguesa grande', 'color:#b26a00;'],
                                        'Adulto'      => ['🍽️ Menú de adulto', ''],
                                    ];
                                    [$menuTxt, $menuColor] = $menuMap[$curType] ?? ['🍽️ Menú general', ''];
                                @endphp
                                <div class="row-note" data-menu-hint style="margin-top:6px; font-weight:600; {{ $menuColor }}">
                                    {!! $menuTxt !!}
                                </div>
                            </div>
                            <div>
                                <label>Género</label>
                                <select name="rows[{{ $index }}][sex]" @disabled($isLocked)>
                                    <option value="">Sin definir</option>
                                    @foreach ($sexes as $sex)
                                        <option value="{{ $sex }}" @selected(old("rows.$index.sex", $row['sex']) === $sex)>{{ $sex }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card" style="padding:18px; border-radius:20px; background:#fcf9ff;">
                        <div class="small">No hay invitados cargados para esta familia.</div>
                    </div>
                @endforelse
            </div>

            @if (! $isLocked)
                <div class="inline" style="margin-top: 18px; justify-content: flex-end;">
                    <button class="btn" type="submit">{{ $isInvitationLike ? 'Guardar cambios y confirmar' : 'Guardar y validar información' }}</button>
                </div>
            @endif
        </form>
    </div>
    @endif

    <script>
        (() => {
            const tbody = document.getElementById('public-guest-review-form');

            if (!tbody) {
                return;
            }

            document.querySelectorAll('[data-type-select]').forEach((select) => {
                const hint = select.closest('div')?.querySelector('[data-menu-hint]');

                const menus = {
                    'Niño':        ['🍔 Menú infantil — hamburguesa (2 a 10 años)', '#b26a00'],
                    'Adolescente': ['<span style="font-size:1.5em; vertical-align:-3px;">🍔</span> Menú de adolescente — hamburguesa grande', '#b26a00'],
                    'Adulto':      ['🍽️ Menú de adulto', ''],
                };
                const update = () => {
                    if (!hint) {
                        return;
                    }

                    const [html, color] = menus[select.value] || ['🍽️ Menú general', ''];
                    hint.innerHTML = html;
                    hint.style.color = color;
                };

                update();
                select.addEventListener('change', update);
            });

            tbody.addEventListener('click', (event) => {
                const button = event.target.closest('[data-remove-row]');

                if (!button) {
                    return;
                }

                const row = button.closest('[data-review-row]');
                const deleteFlag = row?.querySelector('[data-delete-flag]');
                const removeNote = row?.querySelector('[data-remove-note]');

                if (!row || !deleteFlag) {
                    return;
                }

                if (!window.confirm('¿Seguro que deseas eliminar este invitado? El cambio se aplicará al guardar.')) {
                    return;
                }

                deleteFlag.value = '1';
                row.classList.add('row-card-removed');

                if (removeNote) {
                    removeNote.style.display = '';
                }
            });
        })();
    </script>
@endsection
