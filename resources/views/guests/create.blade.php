@extends('layouts.app')

@section('title', 'Nuevo invitado')
@section('heading', 'Nuevo invitado')
@section('subheading', 'Captura un registro nuevo con la lógica del Excel')

@section('content')
    <div class="card">
        <form method="post" action="{{ route('guests.store') }}">
            @include('guests._form')
        </form>
    </div>
@endsection
