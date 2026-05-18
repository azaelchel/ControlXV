<div class="form-grid">
    <div>
        <label for="name">Usuario</label>
        <input id="name" name="name" value="{{ old('name', $userRecord->name) }}" required maxlength="255" placeholder="Usuario de acceso">
        @error('name') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="email">Correo</label>
        <input id="email" type="email" name="email" value="{{ old('email', $userRecord->email) }}" required maxlength="255" placeholder="correo@dominio.com">
        @error('email') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="password">{{ $isEdit ? 'Nueva contraseña' : 'Contraseña' }}</label>
        <input id="password" type="password" name="password" {{ $isEdit ? '' : 'required' }} minlength="6" placeholder="{{ $isEdit ? 'Solo si deseas cambiarla' : 'Mínimo 6 caracteres' }}">
        @error('password') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="password_confirmation">Confirmar contraseña</label>
        <input id="password_confirmation" type="password" name="password_confirmation" {{ $isEdit ? '' : 'required' }} minlength="6" placeholder="Repite la contraseña">
    </div>

    <div class="full">
        <label class="inline" style="gap:10px;">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" name="active" value="1" @checked(old('active', $userRecord->active ?? true)) style="width:auto;">
            <span>Usuario activo</span>
        </label>
    </div>
</div>
