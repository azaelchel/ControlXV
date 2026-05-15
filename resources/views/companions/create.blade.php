@extends('layouts.app')

@section('title', 'Alta de invitados')
@section('heading', 'Alta de invitados')
@section('subheading', 'Formulario para registrar invitados vinculados a las familias o grupos ya existentes.')

@section('content')
    <div class="card">
        <form method="post" action="{{ route('companions.store') }}">
            @include('companions._form')
        </form>
    </div>
@endsection
