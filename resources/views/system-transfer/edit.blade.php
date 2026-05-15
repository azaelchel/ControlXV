@extends('layouts.app')

@section('title', 'Respaldo')
@section('heading', 'Respaldo del sistema')
@section('subheading', 'Importa un respaldo JSON del sistema ya normalizado para poblar producción sin depender del Excel.')

@section('content')
    <div class="card" style="margin-bottom: 18px;">
        <div class="section-kicker">Migración rápida</div>
        <h3 class="section-title">Importar respaldo JSON</h3>
        <p class="small" style="margin-top: 10px; line-height: 1.7;">
            Este proceso reemplaza catálogos, familias o grupos, invitados y mesas confirmadas en la base actual.
            Por seguridad, los usuarios no se importan a menos que lo marques explícitamente.
        </p>
    </div>

    <div class="card">
        <form method="post" action="{{ route('system-transfer.import') }}" enctype="multipart/form-data" class="form-grid">
            @csrf

            <div class="full">
                <label for="backup_file">Archivo de respaldo</label>
                <input id="backup_file" type="file" name="backup_file" accept=".json,application/json,text/plain" required>
                @error('backup_file')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="full">
                <label for="with_users" style="display: inline-flex; align-items: center; gap: 8px;">
                    <input id="with_users" type="checkbox" name="with_users" value="1">
                    <span>Importar también usuarios del sistema</span>
                </label>
            </div>

            <div class="full inline">
                <button class="btn" type="submit">Importar respaldo</button>
            </div>
        </form>
    </div>
@endsection
