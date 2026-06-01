@extends('layouts.app')

@section('title', 'Envío listo')
@section('heading', 'Mensajes listos para enviar')
@section('subheading', 'Copia cada mensaje y pégalo en WhatsApp. Se registra automáticamente al copiar.')

@section('content')
    <div class="card" style="margin-bottom: 18px;">
        <div class="inline" style="justify-content: space-between; align-items: end; flex-wrap: wrap; gap: 14px;">
            <div>
                <div class="section-kicker">Plantilla</div>
                <h3 class="section-title">{{ $template->name }}</h3>
                @if ($template->description)
                    <p class="small" style="margin-top: 4px;">{{ $template->description }}</p>
                @endif
            </div>
            <div class="inline" style="gap: 8px;">
                <a href="{{ route('message-sends.create') }}" class="btn secondary">Cambiar selección</a>
                <a href="{{ route('message-sends.index') }}" class="btn secondary">Ver histórico</a>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 18px; background: #faf6ff; border-color: #e6dff1;">
        <p class="small" style="line-height: 1.7;">
            <strong>Cómo funciona:</strong> Cada mensaje ya tiene los datos sustituidos (nombre + link) y el formato de WhatsApp.
            Al hacer clic en <strong>Copiar y abrir WhatsApp</strong>, el mensaje se copia al portapapeles, se registra el envío,
            y se abre WhatsApp con el número de la familia (puedes pegar el mensaje con un toque).
        </p>
    </div>

    <div style="display: flex; flex-direction: column; gap: 14px;">
        @foreach ($rows as $row)
            @php
                $guest = $row['guest'];
                $message = $row['message'];
                $linkUrl = $row['link_url'];
                $link = $row['link'];
                $eligible = $row['eligible'];
                $phoneClean = preg_replace('/[^0-9]/', '', $guest->phone ?? '');
                $whatsappUrl = $phoneClean
                    ? 'https://wa.me/' . (strlen($phoneClean) === 10 ? '52' . $phoneClean : $phoneClean)
                    : null;
            @endphp
            <div class="card" data-send-card data-guest-id="{{ $guest->id }}">
                <div class="inline" style="justify-content: space-between; align-items: start; gap: 14px; flex-wrap: wrap;">
                    <div>
                        <div class="section-kicker">{{ $guest->group_name }}</div>
                        <h3 class="section-title">{{ $guest->name }}</h3>
                        <div class="small" style="margin-top: 4px;">
                            <span class="pill status-default">{{ $guest->status }}</span>
                            @if ($guest->phone)
                                <span style="margin-left: 8px;">📱 {{ $guest->phone }}</span>
                            @else
                                <span style="margin-left: 8px; color: var(--danger);">Sin teléfono</span>
                            @endif
                            @if ($link)
                                <span style="margin-left: 8px;">
                                    Link {{ $link['reused'] ? 'reutilizado' : 'nuevo' }}, vence {{ \Carbon\Carbon::parse($link['expires_at'])->format('d/m/Y H:i') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="inline" style="gap: 8px;">
                        @if ($eligible && $whatsappUrl)
                            <button type="button" class="btn" data-copy-and-open
                                data-message="{{ $message }}"
                                data-wa="{{ $whatsappUrl }}"
                                data-store-url="{{ route('message-sends.store') }}"
                                data-csrf="{{ csrf_token() }}"
                                data-guest-id="{{ $guest->id }}"
                                data-template-id="{{ $template->id }}"
                                data-link-id="{{ $link['id'] ?? '' }}">
                                Copiar y abrir WhatsApp
                            </button>
                        @elseif ($eligible)
                            <button type="button" class="btn" data-copy-and-open
                                data-message="{{ $message }}"
                                data-wa=""
                                data-store-url="{{ route('message-sends.store') }}"
                                data-csrf="{{ csrf_token() }}"
                                data-guest-id="{{ $guest->id }}"
                                data-template-id="{{ $template->id }}"
                                data-link-id="{{ $link['id'] ?? '' }}">
                                Solo copiar mensaje
                            </button>
                        @else
                            <span class="pill status-no-asistira">No elegible (sin link disponible)</span>
                        @endif
                    </div>
                </div>

                <div style="margin-top: 14px;">
                    <textarea readonly rows="{{ min(14, max(6, substr_count($message, "\n") + 2)) }}" style="width: 100%; font-family: 'SF Mono', Menlo, monospace; font-size: 13px; line-height: 1.6;">{{ $message }}</textarea>
                </div>

                <div data-send-feedback style="margin-top: 10px; display: none;">
                    <span class="pill status-confirmado">✓ Mensaje copiado y envío registrado</span>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        document.querySelectorAll('[data-copy-and-open]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const card    = btn.closest('[data-send-card]');
                const message = btn.dataset.message;
                const waUrl   = btn.dataset.wa;
                const feedback = card.querySelector('[data-send-feedback]');

                try {
                    await navigator.clipboard.writeText(message);
                } catch (e) {
                    const ta = document.createElement('textarea');
                    ta.value = message;
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                }

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

                feedback.style.display = 'inline-block';
                btn.textContent = '✓ Copiado — re-copiar';
                btn.classList.remove('btn');
                btn.classList.add('btn', 'secondary');

                if (waUrl) {
                    window.open(waUrl, '_blank');
                }
            });
        });
    </script>
@endsection
