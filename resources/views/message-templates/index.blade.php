@extends('layouts.app')

@section('title', 'Plantillas')
@section('heading', 'Plantillas de mensajes')
@section('subheading', 'Mensajes reutilizables para WhatsApp con datos que se sustituyen al enviar')

@section('content')
    <div class="card" style="margin-bottom: 18px;">
        <div class="inline" style="justify-content: space-between; align-items: start; gap: 14px; flex-wrap: wrap;">
            <div>
                <div class="section-kicker">¿Qué son las plantillas?</div>
                <h3 class="section-title">Mensajes con campos dinámicos</h3>
                <p class="small" style="margin-top: 6px; line-height: 1.6; max-width: 720px;">
                    Estas plantillas se usan en <a href="{{ route('message-sends.create') }}">Mensajes → Enviar masivo</a>.
                    Los campos entre llaves (ej. <code>{nombre}</code>) se reemplazan automáticamente por los datos reales de cada familia.
                    Para configurar los valores comunes como fecha del evento o nombre del equipo, ve a <a href="{{ route('settings.index') }}">Configuración</a>.
                </p>
            </div>
            <button type="button" class="btn" id="new-template-btn">+ Nueva plantilla</button>
        </div>
    </div>

    <div class="card" style="margin-bottom: 18px; background: #faf6ff;">
        <div class="section-kicker">Campos disponibles</div>
        <h4 class="section-title" style="font-size: 16px;">Estos textos se reemplazan automáticamente</h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 8px; margin-top: 10px;">
            @foreach ($placeholders as $token => $desc)
                <div style="padding: 8px 12px; background: white; border-radius: 8px; border: 1px solid #e6dff1;">
                    <code style="font-weight: 700; color: var(--primary-dark);">{{ $token }}</code>
                    <div class="small" style="margin-top: 2px;">{{ $desc }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Formulario de nueva plantilla (oculto al inicio) --}}
    <div class="card" style="margin-bottom: 18px; display: none;" id="new-template-card">
        <div class="inline" style="justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <h3 class="section-title">Nueva plantilla</h3>
            <button type="button" class="btn ghost" id="cancel-new">Cancelar</button>
        </div>
        <form method="post" action="{{ route('message-templates.store') }}" class="form-grid">
            @csrf
            <div>
                <label>Nombre interno</label>
                <input type="text" name="name" placeholder="Ej: Recordatorio 2 semanas" required>
                @error('name')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div>
                <label>Etiqueta corta (kicker)</label>
                <input type="text" name="kicker" placeholder="Ej: Recordatorio">
            </div>
            <div class="full">
                <label>Descripción interna</label>
                <input type="text" name="description" placeholder="¿Para quién se usa esta plantilla?">
            </div>
            <div class="full">
                <label>Contenido del mensaje</label>
                <textarea name="content" rows="10" required placeholder="Estimada/o {prefijo} {nombre}, ..."></textarea>
                <div class="small" style="margin-top: 4px;">Si incluyes <code>{link}</code> en el contenido, automáticamente se generará el link de confirmación al enviar.</div>
                @error('content')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="full inline">
                <button class="btn" type="submit">Crear plantilla</button>
            </div>
        </form>
    </div>

    {{-- Accordion de plantillas existentes --}}
    <div style="display: flex; flex-direction: column; gap: 12px;">
        @foreach ($templates as $template)
            <div class="card template-card" data-template-card data-template-id="{{ $template->id }}" style="padding: 0; overflow: hidden;">
                <button type="button" class="template-header" data-template-toggle
                    style="width: 100%; text-align: left; background: white; border: none; padding: 16px 20px; cursor: pointer; display: flex; align-items: center; gap: 14px;">
                    <span style="display: inline-flex; width: 24px; height: 24px; align-items: center; justify-content: center; font-size: 18px; color: var(--primary); transition: transform 0.2s;" data-template-chevron>▸</span>
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: {{ $template->active ? '#3aa55c' : '#c2c8d0' }};"></span>
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <strong style="font-size: 16px;">{{ $template->name }}</strong>
                            @if ($template->kicker)
                                <span class="pill status-default">{{ $template->kicker }}</span>
                            @endif
                            @if (! $template->active)
                                <span class="pill status-no-asistira">Inactiva</span>
                            @endif
                        </div>
                        @if ($template->description)
                            <div class="small" style="margin-top: 4px;">{{ $template->description }}</div>
                        @endif
                    </div>
                    <div class="small">
                        {{ $template->sends()->count() }} envíos · {{ $template->hasLinkPlaceholder() ? 'con link' : 'sin link' }}
                    </div>
                </button>

                <div class="template-body" data-template-body style="display: none; padding: 0 20px 20px 20px; border-top: 1px solid #f0e9fa;">
                    <div class="inline" style="justify-content: flex-end; gap: 8px; margin: 14px 0;">
                        <form method="post" action="{{ route('message-templates.toggle', $template) }}" style="display: inline;">
                            @csrf
                            @method('patch')
                            <button class="btn secondary" type="submit">{{ $template->active ? 'Desactivar' : 'Activar' }}</button>
                        </form>
                        <form method="post" action="{{ route('message-templates.destroy', $template) }}" style="display: inline;"
                            data-confirm-title="¿Eliminar esta plantilla?"
                            data-confirm-text="Los envíos pasados quedarán sin plantilla asociada pero conservarán su mensaje."
                            data-confirm-button="Sí, eliminar"
                            data-confirm-color="#d8527f">
                            @csrf
                            @method('delete')
                            <button class="btn danger" type="submit">Eliminar</button>
                        </form>
                    </div>

                    <form method="post" action="{{ route('message-templates.update', $template) }}" class="form-grid">
                        @csrf
                        @method('put')
                        <div>
                            <label>Nombre interno</label>
                            <input type="text" name="name" value="{{ old('name', $template->name) }}" required>
                        </div>
                        <div>
                            <label>Etiqueta corta (kicker)</label>
                            <input type="text" name="kicker" value="{{ old('kicker', $template->kicker) }}">
                        </div>
                        <div class="full">
                            <label>Descripción interna</label>
                            <input type="text" name="description" value="{{ old('description', $template->description) }}">
                        </div>
                        <div class="full">
                            <label>Contenido del mensaje</label>
                            <textarea name="content" rows="14" required style="font-family: inherit;">{{ old('content', $template->content) }}</textarea>
                            <div class="small" style="margin-top: 4px;">Si incluye <code>{link}</code>, se genera automáticamente el link de confirmación al enviar.</div>
                        </div>
                        <div class="full inline">
                            <button class="btn" type="submit">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        (function () {
            // Accordion: solo una expandida a la vez
            document.querySelectorAll('[data-template-toggle]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const card = btn.closest('[data-template-card]');
                    const body = card.querySelector('[data-template-body]');
                    const chev = card.querySelector('[data-template-chevron]');
                    const isOpen = body.style.display !== 'none';

                    document.querySelectorAll('[data-template-body]').forEach(b => b.style.display = 'none');
                    document.querySelectorAll('[data-template-chevron]').forEach(c => c.style.transform = '');

                    if (!isOpen) {
                        body.style.display = 'block';
                        chev.style.transform = 'rotate(90deg)';
                    }
                });
            });

            // Toggle de nueva plantilla
            const newBtn = document.getElementById('new-template-btn');
            const newCard = document.getElementById('new-template-card');
            const cancelBtn = document.getElementById('cancel-new');
            newBtn.addEventListener('click', () => {
                newCard.style.display = 'block';
                newCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                newCard.querySelector('input[name="name"]').focus();
            });
            cancelBtn.addEventListener('click', () => { newCard.style.display = 'none'; });
        })();
    </script>
@endsection
