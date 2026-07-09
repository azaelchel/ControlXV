@extends('layouts.app')

@section('title', 'Mensajes listos')
@section('heading', 'Mensajes listos para enviar')
@section('subheading', 'Copia cada mensaje y mándalo por WhatsApp. Los mensajes preparados quedan registrados en historial.')

@section('content')
    <div class="card" style="margin-bottom: 18px;">
        <div class="inline" style="justify-content: space-between; align-items: end; flex-wrap: wrap; gap: 14px;">
            <div>
                <div class="section-kicker">Plantilla</div>
                <h3 class="section-title" style="margin: 0;">{{ $template->name }}</h3>
                @if ($template->description)
                    <p class="small" style="margin: 6px 0 0 0;">{{ $template->description }}</p>
                @endif
            </div>
            <div class="inline" style="gap: 8px;">
                <a href="{{ route('message-sends.create') }}" class="btn secondary">← Cambiar selección</a>
                <a href="{{ route('message-sends.index') }}" class="btn secondary">Ver panel</a>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 18px; background: #faf6ff; border-color: #e6dff1;">
        <p class="small" style="line-height: 1.7; margin: 0;">
            <strong>Cómo funciona:</strong> Cada tarjeta trae el mensaje ya armado.
            Al preparar esta pantalla los mensajes quedan registrados en historial.
            Al hacer clic en <strong>Copiar y abrir WhatsApp</strong> se copia al portapapeles y se abre <strong>WhatsApp</strong> con el número del destinatario.
            Puedes <strong>volver a copiar las veces que quieras</strong>.
        </p>
    </div>

    <div style="display: flex; flex-direction: column; gap: 14px;">
        @foreach ($rows as $index => $row)
            @php
                $guest = $row['guest'];
                $message = $row['message'];
                $linkUrl = $row['link_url'];
                $link = $row['link'];
                $eligible = $row['eligible'];
                $phoneClean = preg_replace('/[^0-9]/', '', $guest->phone ?? '');
                $phoneIntl = $phoneClean ? (strlen($phoneClean) === 10 ? '52' . $phoneClean : $phoneClean) : null;
            @endphp
            <div class="card" data-send-card data-guest-id="{{ $guest->id }}">
                <div class="inline" style="justify-content: space-between; align-items: start; gap: 14px; flex-wrap: wrap;">
                    <div>
                        <div class="section-kicker">#{{ $index + 1 }} · {{ $guest->group_name }}</div>
                        <h3 class="section-title" style="margin: 0;">{{ $guest->name }}</h3>
                        <div class="small" style="margin-top: 6px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                            <span class="pill status-default">{{ $guest->status }}</span>
                            @if ($guest->phone)
                                <span>📱 {{ $guest->phone }}</span>
                            @else
                                <span style="color: var(--danger);">Sin teléfono</span>
                            @endif
                            @if ($link)
                                <span>Link {{ $link['reused'] ? 'reutilizado' : 'nuevo' }} · vence {{ \Carbon\Carbon::parse($link['expires_at'])->format('d/m/Y H:i') }}</span>
                            @endif
                            <span data-send-status style="display: none; color: #256141; font-weight: 700;">✓ Enviado registrado</span>
                        </div>
                    </div>
                    <div class="inline" style="gap: 8px; align-items: center;">
                        @if ($eligible)
                            <button type="button" class="btn" data-copy-action
                                data-message="{{ $message }}"
                                data-phone="{{ $phoneIntl ?? '' }}"
                                data-store-url="{{ route('message-sends.store') }}"
                                data-csrf="{{ csrf_token() }}"
                                data-guest-id="{{ $guest->id }}"
                                data-template-id="{{ $template->id }}"
                                data-link-id="{{ $link['id'] ?? '' }}">
                                {{ $phoneIntl ? 'Copiar y abrir WhatsApp' : 'Solo copiar mensaje' }}
                            </button>
                            <button type="button" class="btn secondary" data-recopy
                                data-message="{{ $message }}"
                                title="Solo copia, no registra envío ni abre WhatsApp">
                                Re-copiar
                            </button>
                        @else
                            <span class="pill status-no-asistira">No elegible (sin link disponible)</span>
                        @endif
                    </div>
                </div>

                <div style="margin-top: 14px;">
                    <textarea readonly rows="{{ min(14, max(6, substr_count($message, "\n") + 2)) }}" style="width: 100%; font-family: 'SF Mono', Menlo, monospace; font-size: 13px; line-height: 1.6;">{{ $message }}</textarea>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        async function copyText(text) {
            try {
                await navigator.clipboard.writeText(text);
                return true;
            } catch (e) {
                const ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                return true;
            }
        }

        document.querySelectorAll('[data-copy-action]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const card = btn.closest('[data-send-card]');
                const message = btn.dataset.message;
                const phone = btn.dataset.phone;
                const statusEl = card.querySelector('[data-send-status]');

                await copyText(message);

                try {
                    await fetch(btn.dataset.storeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': btn.dataset.csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            guest_id: btn.dataset.guestId,
                            message_template_id: btn.dataset.templateId,
                            public_guest_link_id: btn.dataset.linkId || null,
                            rendered_message: message,
                        }),
                    });
                } catch (e) {
                    console.error('No se pudo registrar el envío', e);
                }

                statusEl.style.display = 'inline';

                if (phone) {
                    window.open(window.waUrl(phone, message), '_blank');
                }
            });
        });

        document.querySelectorAll('[data-recopy]').forEach(btn => {
            btn.addEventListener('click', async () => {
                await copyText(btn.dataset.message);
                const original = btn.textContent;
                btn.textContent = '✓ Copiado';
                setTimeout(() => { btn.textContent = original; }, 1500);
            });
        });
    </script>
@endsection
