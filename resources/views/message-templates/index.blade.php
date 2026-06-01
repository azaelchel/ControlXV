@extends('layouts.app')

@section('title', 'Plantillas')
@section('heading', 'Plantillas de mensajes')
@section('subheading', 'Mensajes reutilizables para WhatsApp con placeholders que se reemplazan al generar un envío')

@section('content')
    <div class="grid" style="grid-template-columns: 320px minmax(0, 1fr); align-items: start; gap: 18px;">
        <div>
            <div class="card" style="margin-bottom: 14px;">
                <div class="section-kicker">Plantillas existentes</div>
                <div class="subnav">
                    @foreach ($templates as $template)
                        <a class="subnav-link" href="#template-{{ $template->id }}">
                            <span style="display: flex; align-items: center; gap: 6px;">
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $template->active ? '#3aa55c' : '#c2c8d0' }};"></span>
                                <span>{{ $template->name }}</span>
                            </span>
                            <span class="subnav-count">{{ $template->sends()->count() }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="card">
                <div class="section-kicker">Placeholders disponibles</div>
                <p class="small" style="margin-bottom: 12px;">Estos textos se reemplazan automáticamente al enviar:</p>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 6px;">
                    @foreach ($placeholders as $token => $desc)
                        <li style="display: flex; flex-direction: column; gap: 2px; padding: 8px 10px; background: #f5f0fb; border-radius: 8px;">
                            <code style="font-size: 12px; color: var(--primary-dark);">{{ $token }}</code>
                            <span class="small">{{ $desc }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div>
            <div class="card" style="margin-bottom: 18px;">
                <div class="section-kicker">Crear nueva</div>
                <h3 class="section-title">Nueva plantilla</h3>
                <form method="post" action="{{ route('message-templates.store') }}" class="form-grid" style="margin-top: 14px;">
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
                        <label>Contenido</label>
                        <textarea name="content" rows="10" required placeholder="Estimada Fam. {nombre}, ...

Te compartimos tu enlace:
{link}"></textarea>
                        @error('content')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="full">
                        <label style="display: inline-flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="includes_link" value="1" checked>
                            <span>Esta plantilla incluye un link de confirmación</span>
                        </label>
                    </div>
                    <div class="full inline">
                        <button class="btn" type="submit">Crear plantilla</button>
                    </div>
                </form>
            </div>

            @foreach ($templates as $template)
                <div class="card" id="template-{{ $template->id }}" style="margin-bottom: 18px;">
                    <div class="inline" style="justify-content: space-between; align-items: start; margin-bottom: 14px;">
                        <div>
                            @if ($template->kicker)
                                <div class="section-kicker">{{ $template->kicker }}</div>
                            @endif
                            <h3 class="section-title">{{ $template->name }}</h3>
                            @if ($template->description)
                                <p class="small" style="margin-top: 4px;">{{ $template->description }}</p>
                            @endif
                        </div>
                        <div style="display: flex; gap: 8px;">
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
                            <label>Contenido</label>
                            <textarea name="content" rows="12" required>{{ old('content', $template->content) }}</textarea>
                        </div>
                        <div class="full">
                            <label style="display: inline-flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="includes_link" value="1" @checked($template->includes_link)>
                                <span>Esta plantilla incluye un link de confirmación</span>
                            </label>
                        </div>
                        <div class="full inline">
                            <button class="btn" type="submit">Guardar cambios</button>
                            <span class="small">Usada en {{ $template->sends()->count() }} envíos</span>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
@endsection
