@extends('layouts.app')

@section('title', 'Editar invitado')
@section('heading', 'Editar invitado')
@section('subheading', 'Actualiza el invitado y su relación con la familia o grupo correspondiente.')

@section('content')
    <div class="card">
        <form method="post" action="{{ route('companions.update', $companion) }}">
            @method('put')
            @include('companions._form')
        </form>
    </div>
@endsection
