@extends('layouts.app')

@section('title', 'Usuarios')
@section('heading', 'Usuarios')
@section('subheading', 'Módulo administrativo para crear, editar y controlar los accesos al sistema.')

@section('content')
    <div x-data="{ showCreateUserModal: false, showEditUserModal: {{ $editingUser ? 'true' : 'false' }} }">
        <div class="toolbar">
            <div class="inline">
                <button class="btn secondary" type="button" @click="showCreateUserModal = true">Crear usuario</button>
            </div>
        </div>

        <div class="modal-overlay" x-cloak x-show="showCreateUserModal" x-transition.opacity @keydown.escape.window="showCreateUserModal = false" @click.self="showCreateUserModal = false">
            <div class="modal-panel" style="max-width: 760px;">
                <div class="inline" style="justify-content: space-between; margin-bottom: 18px;">
                    <div>
                        <div class="section-kicker">Administración</div>
                        <h3 class="section-title">Nuevo usuario</h3>
                    </div>
                    <button class="btn ghost" type="button" @click="showCreateUserModal = false">Cerrar</button>
                </div>

                <form method="post" action="{{ route('users.store') }}">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ route('users.index') }}">
                    @include('users._fields', ['userRecord' => new \App\Models\User(), 'isEdit' => false])
                    <div class="inline" style="margin-top: 18px;">
                        <button class="btn" type="submit">Guardar usuario</button>
                        <button class="btn secondary" type="button" @click="showCreateUserModal = false">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>

        @if ($editingUser)
            <div class="modal-overlay" x-cloak x-show="showEditUserModal" x-transition.opacity @keydown.escape.window="showEditUserModal = false" @click.self="showEditUserModal = false">
                <div class="modal-panel" style="max-width: 760px;">
                    <div class="inline" style="justify-content: space-between; margin-bottom: 18px;">
                        <div>
                            <div class="section-kicker">Administración</div>
                            <h3 class="section-title">Editar usuario</h3>
                        </div>
                        <a class="btn ghost" href="{{ route('users.index') }}">Cerrar</a>
                    </div>

                    <form method="post" action="{{ route('users.update', $editingUser) }}">
                        @csrf
                        @method('put')
                        <input type="hidden" name="return_to" value="{{ route('users.index') }}">
                        @include('users._fields', ['userRecord' => $editingUser, 'isEdit' => true])
                        <div class="inline" style="margin-top: 18px;">
                            <button class="btn" type="submit">Guardar cambios</button>
                            <a class="btn secondary" href="{{ route('users.index') }}">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="grid cols-3" style="margin-bottom: 18px;">
            <div class="card metric">
                <div class="label">Usuarios</div>
                <div class="value">{{ $users->count() }}</div>
            </div>
            <div class="card metric">
                <div class="label">Activos</div>
                <div class="value">{{ $users->where('active', true)->count() }}</div>
            </div>
            <div class="card metric">
                <div class="label">Inactivos</div>
                <div class="value">{{ $users->where('active', false)->count() }}</div>
            </div>
        </div>

        <div class="card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Correo</th>
                            <th>Estatus</th>
                            <th>Creado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $userRecord)
                            <tr>
                                <td><strong>{{ $userRecord->name }}</strong></td>
                                <td>{{ $userRecord->email }}</td>
                                <td>
                                    <span class="pill {{ $userRecord->active ? 'status-confirmado' : 'status-rechazado' }}">
                                        {{ $userRecord->active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>{{ $userRecord->created_at?->format('d/m/Y H:i') ?: '—' }}</td>
                                <td>
                                    <div class="inline">
                                        <a class="btn secondary icon-btn" href="{{ route('users.index', ['edit' => $userRecord->id]) }}" title="Editar usuario" aria-label="Editar usuario">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 20h9"/>
                                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                            </svg>
                                        </a>
                                        <form method="post" action="{{ route('users.toggle', $userRecord) }}"
                                            data-confirm-title="{{ $userRecord->active ? '¿Desactivar este usuario?' : '¿Activar este usuario?' }}"
                                            data-confirm-text="{{ $userRecord->active ? 'Este acceso ya no podrá entrar al sistema.' : 'Este acceso volverá a poder entrar al sistema.' }}"
                                            data-confirm-button="{{ $userRecord->active ? 'Sí, desactivar' : 'Sí, activar' }}"
                                            data-confirm-color="{{ $userRecord->active ? '#d8527f' : '#2ea866' }}"
                                            data-confirm-icon="warning">
                                            @csrf
                                            @method('patch')
                                            <input type="hidden" name="return_to" value="{{ route('users.index') }}">
                                            <button class="btn {{ $userRecord->active ? 'danger' : 'success' }} icon-btn" type="submit" title="{{ $userRecord->active ? 'Desactivar usuario' : 'Activar usuario' }}" aria-label="{{ $userRecord->active ? 'Desactivar usuario' : 'Activar usuario' }}">
                                                @if ($userRecord->active)
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M18 6 6 18"/>
                                                        <path d="m6 6 12 12"/>
                                                    </svg>
                                                @else
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M20 6 9 17l-5-5"/>
                                                    </svg>
                                                @endif
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty">No hay usuarios registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
