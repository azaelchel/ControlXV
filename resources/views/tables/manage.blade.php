@extends('layouts.app')

@section('title', 'Mesas — Asignaciones')
@section('heading', 'Mesas · Asignaciones')
@section('subheading', 'Crea mesas y acomoda a los invitados confirmados. Puedes sentar un grupo completo o dividirlo entre mesas.')

@section('content')
    @if ($errors->any())
        <div class="card" style="margin-bottom: 16px; border-color: #f3c8d7; background: #ffe7ef; color: #8a3354;">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="inline" style="justify-content: space-between; margin-bottom: 16px; gap: 10px; flex-wrap: wrap;">
        <div class="grid cols-4" style="flex: 1; gap: 10px;">
            <div class="card metric"><div class="label">Mesas</div><div class="value">{{ $summary['tables'] }}</div></div>
            <div class="card metric"><div class="label">Capacidad máxima</div><div class="value">{{ $summary['capacity'] }}</div></div>
            <div class="card metric"><div class="label">Sentados</div><div class="value">{{ $summary['seated'] }}</div></div>
            <div class="card metric"><div class="label">Sin mesa</div><div class="value">{{ $summary['unassigned'] }}</div></div>
        </div>
    </div>
    @if (collect($summary['eligible_by_category'] ?? [])->isNotEmpty())
        <div class="small" style="margin: -6px 0 16px; color:#8a72a4;">
            Personas confirmadas a acomodar:
            @foreach ($summary['eligible_by_category'] as $category => $total)
                <strong>{{ $category }}</strong> {{ number_format($total) }}@if (! $loop->last) · @endif
            @endforeach
        </div>
    @endif

    <div class="inline" style="justify-content: space-between; margin-bottom: 16px; gap: 10px; flex-wrap: wrap;">
        <a class="btn secondary" href="{{ route('tables.index') }}">← Ver consulta</a>
        <a class="btn secondary" href="{{ route('tables.print') }}" target="_blank">📄 Descargar PDF</a>
    </div>

    {{-- Aviso de grupos divididos --}}
    @if ($dividedGroups->isNotEmpty())
        <div class="card" style="margin-bottom: 18px; border-color: #f0d9a8; background: #fff8e9;">
            <strong>⚠️ Grupos divididos en varias mesas:</strong>
            <ul style="margin: 8px 0 0; padding-left: 18px;">
                @foreach ($dividedGroups as $group => $tableNames)
                    <li class="small"><strong>{{ $group }}</strong> → {{ implode(', ', $tableNames) }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Crear mesa --}}
    <details class="card" style="margin-bottom: 18px;">
        <summary style="cursor: pointer; font-weight: 700;">➕ Crear nueva mesa</summary>
        <form method="post" action="{{ route('tables.store') }}" style="margin-top: 14px;">
            @csrf
            @include('tables._form', ['table' => null, 'types' => $types, 'shapes' => $shapes])
            <button class="btn" type="submit" style="margin-top: 10px;">Crear mesa</button>
        </form>
    </details>

    {{-- Invitados sin mesa (con filtro) --}}
    <div class="card" style="margin-bottom: 18px;">
        <div class="inline" style="justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <h3 style="margin: 0;">Invitados sin mesa ({{ $summary['unassigned'] }})</h3>
            <form method="get" class="inline" style="gap: 6px;">
                <input name="q" value="{{ $q }}" placeholder="Filtrar por nombre o grupo…" style="min-width: 240px;">
                <button class="btn small" type="submit">Filtrar</button>
                @if ($q !== '')
                    <a class="btn small secondary" href="{{ route('tables.manage') }}">Limpiar</a>
                @endif
            </form>
        </div>

        @if ($tables->isEmpty())
            <p class="small" style="margin: 12px 0 0;">Primero crea al menos una mesa para poder acomodar a los invitados.</p>
        @elseif ($unassigned->isEmpty())
            <p class="small" style="margin: 12px 0 0;">{{ $q !== '' ? 'Ningún invitado sin mesa coincide con el filtro.' : '🎉 Todos los invitados confirmados ya tienen mesa.' }}</p>
        @else
            <div class="grid cols-2" style="gap: 12px; margin-top: 12px;">
                @foreach ($unassigned as $group => $people)
                    <div style="border: 1px solid #efe5f7; border-radius: 12px; padding: 12px;">
                        <div class="inline" style="justify-content: space-between; align-items: start; gap: 8px;">
                            <strong>{{ $group }} <span class="small">({{ $people->count() }})</span></strong>
                            <form method="post" action="#" class="inline" style="gap: 6px;"
                                onsubmit="this.action='{{ url('confirmed-tables') }}/'+this.table_id.value+'/assign-group';">
                                @csrf
                                <input type="hidden" name="group" value="{{ $group }}">
                                <select name="table_id" required style="font-size: 12px; padding: 4px 8px;">
                                    <option value="" disabled selected hidden>Elegir mesa…</option>
                                    @foreach ($tables as $t)
                                        @php $available = $t->availableSeats(); @endphp
                                        <option value="{{ $t->id }}" @disabled($available < 1)>{{ $t->name }} ({{ $available }} libres)</option>
                                    @endforeach
                                </select>
                                <button class="btn small" type="submit">Sentar grupo</button>
                            </form>
                        </div>
                        <ul style="margin: 8px 0 0; padding-left: 0; list-style: none;">
                            @foreach ($people as $person)
                                <li class="inline" style="justify-content: space-between; padding: 4px 0; gap: 8px;">
                                    <span class="small">{{ $person->name }} · {{ $person->type }}</span>
                                    <form method="post" action="#" class="inline" style="gap: 4px;"
                                        onsubmit="this.action='{{ url('confirmed-tables') }}/'+this.table_id.value+'/assign';">
                                        @csrf
                                        <input type="hidden" name="companion_id" value="{{ $person->id }}">
                                        <select name="table_id" required style="font-size: 11px; padding: 3px 6px;">
                                            <option value="" disabled selected hidden>Elegir mesa…</option>
                                            @foreach ($tables as $t)
                                                @php $available = $t->availableSeats(); @endphp
                                                <option value="{{ $t->id }}" @disabled($available < 1)>{{ $t->name }} ({{ $available }} libres)</option>
                                            @endforeach
                                        </select>
                                        <button class="btn small secondary" type="submit">Sentar</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Mesas --}}
    @if ($tables->isNotEmpty())
        <div class="grid cols-2" style="gap: 16px;">
            @foreach ($tables as $table)
                @php $occupied = $table->occupiedSeats(); @endphp
                <div class="card" style="{{ $table->is_principal ? 'border-color: #c693ea;' : '' }}">
                    <div class="inline" style="justify-content: space-between; align-items: start;">
                        <div>
                            <h3 style="margin: 0;">{{ $table->is_principal ? '⭐ ' : '' }}{{ $table->name }}</h3>
                            <div class="small">{{ $table->table_type ?: 'Sin tipo' }} · {{ $table->shape ?: 'Sin forma' }}</div>
                        </div>
                        <span class="pill {{ $occupied >= $table->capacity ? 'status-no-asistira' : 'status-confirmado' }}">{{ $occupied }}/{{ $table->capacity }}</span>
                    </div>

                    @if ($table->notes)
                        <p class="small" style="margin: 8px 0 0;">📝 {{ $table->notes }}</p>
                    @endif

                    <div style="margin-top: 12px;">
                        @forelse ($table->assignments->sortBy(fn ($a) => $a->companion?->invited_group) as $assignment)
                            @continue(! $assignment->companion)
                            <div class="inline" style="justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #f3eefa; gap: 8px;">
                                <span class="small">{{ $assignment->companion->name }}
                                    <span style="color: #9b8ab0;">· {{ $assignment->companion->invited_group }}</span>
                                </span>
                                <div class="inline" style="gap: 4px;">
                                    @if ($tables->count() > 1)
                                        <form method="post" action="#" class="inline" style="gap: 3px;"
                                            onsubmit="this.action='{{ url('confirmed-tables') }}/'+this.table_id.value+'/assign';">
                                            @csrf
                                            <input type="hidden" name="companion_id" value="{{ $assignment->companion_id }}">
                                            <select name="table_id" required style="font-size: 11px; padding: 2px 5px;">
                                                <option value="" disabled selected hidden>Mover a…</option>
                                                @foreach ($tables as $t)
                                                    @if ($t->id !== $table->id)
                                                        @php $available = $t->availableSeats(); @endphp
                                                        <option value="{{ $t->id }}" @disabled($available < 1)>{{ $t->name }} ({{ $available }} libres)</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <button class="btn small secondary" type="submit">↪</button>
                                        </form>
                                    @endif
                                    <form method="post" action="{{ route('tables.unassign', $table) }}">
                                        @csrf
                                        <input type="hidden" name="companion_id" value="{{ $assignment->companion_id }}">
                                        <button class="btn small danger" type="submit" title="Quitar de la mesa">✕</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="small" style="margin: 0; color: #9b8ab0;">Mesa vacía.</p>
                        @endforelse
                    </div>

                    <div class="inline" style="margin-top: 12px; gap: 8px;">
                        <details style="flex: 1;">
                            <summary class="btn secondary small" style="display: inline-block;">Editar</summary>
                            <form method="post" action="{{ route('tables.update', $table) }}" style="margin-top: 10px;">
                                @csrf
                                @method('put')
                                @include('tables._form', ['table' => $table, 'types' => $types, 'shapes' => $shapes])
                                <button class="btn small" type="submit" style="margin-top: 8px;">Guardar</button>
                            </form>
                        </details>
                        <form method="post" action="{{ route('tables.destroy', $table) }}"
                            data-confirm-title="¿Eliminar la mesa {{ $table->name }}?"
                            data-confirm-text="Sus invitados quedarán sin mesa. No se borra ninguna persona."
                            data-confirm-button="Sí, eliminar"
                            data-confirm-color="#d8527f"
                            data-confirm-icon="warning">
                            @csrf
                            @method('delete')
                            <button class="btn small danger" type="submit">Eliminar mesa</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
