@extends('layouts.public')

@section('title', 'Revisión de invitados | XV de Zugeily')

@section('content')
    <div class="hero">
        <div class="brand">
            <div class="badge">XV</div>
            <div>
                <div class="kicker">Revisión de invitados</div>
                <h1>Gracias por su participación para los XV de Zugeily</h1>
                <p>
                    Les pedimos revisar su lista de invitados. Si existe alguna modificación, por favor aplíquenla aquí.
                    Si todavía no han registrado a sus invitados, pueden hacerlo en este mismo formulario.
                    Cuando terminen, guarden los cambios para que el sistema actualice su información.
                </p>
            </div>
        </div>
    </div>

    <div class="grid cols-2" style="margin-bottom: 22px;">
        <div class="metric">
            <span class="small">Familia o grupo</span>
            <strong>{{ $guest->name }}</strong>
        </div>
        <div class="metric">
            <span class="small">Estado actual</span>
            <strong>{{ $guest->status }}</strong>
        </div>
    </div>

    @if ($guest->status === 'Rechazado')
        <div class="card" style="margin-bottom: 22px; border-color:#f0c4d2; background:#fff3f7;">
            <div class="kicker" style="color:#b44f74;">Confirmación final</div>
            <h2>Esta familia indicó que no podrá asistir</h2>
            <p>
                Agradecemos mucho su atención y el tiempo de revisar la invitación.
                El formulario quedó bloqueado para evitar cambios posteriores.
            </p>
        </div>
    @endif

    <div class="card">
        <div class="inline" style="justify-content: space-between; margin-bottom: 16px;">
            <div>
                <div class="kicker">Formulario familiar</div>
                <h2>Lista de invitados</h2>
            </div>
            <div class="small">Pueden revisar su lista y corregir únicamente la información necesaria de sus invitados.</div>
        </div>

        @if ($guest->status !== 'Rechazado')
            <form method="post" action="{{ $signedDeclineUrl }}"
                style="margin-bottom: 16px;"
                data-confirm-title="¿Seguro que no podrán asistir?"
                data-confirm-text="Se cambiará el estatus de la familia a Rechazado y el formulario quedará bloqueado."
                data-confirm-button="Sí, no podremos asistir"
                data-confirm-color="#d8527f"
                data-confirm-icon="warning">
                @csrf
                <button class="btn danger" type="submit">Una disculpa, no podremos asistir</button>
            </form>
        @endif

        <form method="post" action="{{ $signedUpdateUrl }}" id="public-guest-review-form">
            @csrf
            @method('put')

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Sexo</th>
                            <th>Observaciones</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="public-guest-review-rows">
                        @forelse ($rows as $index => $row)
                            <tr data-review-row class="{{ old("rows.$index.delete") ? 'row-card-removed' : '' }}">
                                <td class="row-fields">
                                    <input type="hidden" name="rows[{{ $index }}][id]" value="{{ old("rows.$index.id", $row['id']) }}">
                                    <input type="hidden" name="rows[{{ $index }}][delete]" value="{{ old("rows.$index.delete", 0) }}" data-delete-flag>
                                    <input type="text" name="rows[{{ $index }}][name]" value="{{ old("rows.$index.name", $row['name']) }}" placeholder="Nombre del invitado" @disabled($guest->status === 'Rechazado')>
                                    <div class="row-note">{{ $row['existing'] ? 'Registro actual' : 'Pendiente por registrar' }}</div>
                                </td>
                                <td class="row-fields">
                                    <select name="rows[{{ $index }}][type]" @disabled($guest->status === 'Rechazado')>
                                        @foreach ($types as $type)
                                            <option value="{{ $type }}" @selected(old("rows.$index.type", $row['type']) === $type)>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="row-fields">
                                    <select name="rows[{{ $index }}][sex]" @disabled($guest->status === 'Rechazado')>
                                        <option value="">Sin definir</option>
                                        @foreach ($sexes as $sex)
                                            <option value="{{ $sex }}" @selected(old("rows.$index.sex", $row['sex']) === $sex)>{{ $sex }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="row-fields">
                                    <textarea name="rows[{{ $index }}][notes]" placeholder="Opcional" @disabled($guest->status === 'Rechazado')>{{ old("rows.$index.notes", $row['notes']) }}</textarea>
                                </td>
                                <td>
                                    @unless ($guest->status === 'Rechazado')
                                        <button class="btn danger small" type="button" data-remove-row>
                                            Eliminar
                                        </button>
                                        <div class="row-note" data-remove-note style="{{ old("rows.$index.delete") ? '' : 'display:none;' }}">
                                            Se eliminará al guardar.
                                        </div>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-placeholder>
                                <td colspan="5" class="small">No hay invitados registrados todavía. Puedes empezar a capturarlos aquí.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($guest->status !== 'Rechazado')
                <div class="inline" style="margin-top: 18px; justify-content: space-between;">
                    <div class="inline">
                        <button class="btn" type="submit">Guardar cambios</button>
                    </div>
                </div>
            @endif
        </form>
    </div>

    <script>
        (() => {
            const tbody = document.getElementById('public-guest-review-rows');
            const form = document.getElementById('public-guest-review-form');

            if (!tbody || !form) {
                return;
            }

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
