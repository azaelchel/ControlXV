@extends('layouts.app')

@section('title', 'Editar invitado')
@section('heading', 'Editar invitado')
@section('subheading', 'Actualiza datos, seguimiento o conteos del registro')

@section('content')
    <div class="card">
        <form method="post" action="{{ route('guests.update', $guest) }}">
            @method('put')
            @include('guests._form')
        </form>
    </div>
@endsection
