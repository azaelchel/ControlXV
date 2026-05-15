@csrf
@include('guests._fields')

<div class="inline" style="margin-top: 18px;">
    <button class="btn" type="submit">Guardar</button>
    <a class="btn secondary" href="{{ route('guests.index') }}">Cancelar</a>
</div>
