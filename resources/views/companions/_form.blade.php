@csrf
@include('companions._fields')

<div class="inline" style="margin-top: 18px;">
    <button class="btn" type="submit">Guardar invitado</button>
    <a class="btn secondary" href="{{ route('companions.index') }}">Cancelar</a>
</div>
