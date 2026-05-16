@extends('layouts.app')

@section('title', 'Alta de invitados')
@section('heading', 'Alta de invitados')
@section('subheading', 'Formulario para registrar invitados vinculados a las familias o grupos ya existentes.')

@section('content')
    <div class="card">
        <form method="post" action="{{ route('companions.store') }}">
            @csrf
            @include('companions._batch_fields')
            <div class="inline" style="margin-top: 18px;">
                <button class="btn" type="submit">Guardar invitados</button>
                <a class="btn secondary" href="{{ route('companions.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
