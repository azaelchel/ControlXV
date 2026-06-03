<div class="form-grid">
    <div class="span-2">
        <label for="invited_group">Familia o grupo</label>
        @if ($companion->exists)
            <input type="hidden" name="invited_group" value="{{ old('invited_group', $companion->invited_group) }}">
            <input id="invited_group" type="text" value="{{ old('invited_group', $companion->invited_group) }}" readonly style="background:#f7f2fb;color:#6a4f81;">
        @else
            <select id="invited_group" name="invited_group" required>
                <option value="">Selecciona</option>
                @foreach ($guestOptions as $value)
                    <option value="{{ $value }}" @selected(old('invited_group', $companion->invited_group) === $value)>{{ $value }}</option>
                @endforeach
            </select>
        @endif
        @error('invited_group') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div class="span-2">
        <label for="name">Nombre del invitado</label>
        <input id="name" name="name" type="text" value="{{ old('name', $companion->name) }}" required>
        @error('name') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="type">Tipo</label>
        <select id="type" name="type">
            <option value="">Sin definir</option>
            @foreach ($types as $value)
                <option value="{{ $value }}" @selected(old('type', $companion->type) === $value)>{{ $value }}</option>
            @endforeach
        </select>
        @error('type') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="sex">Género</label>
        <select id="sex" name="sex">
            <option value="">Sin definir</option>
            @foreach ($sexes as $value)
                <option value="{{ $value }}" @selected(old('sex', $companion->sex) === $value)>{{ $value }}</option>
            @endforeach
        </select>
        @error('sex') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div class="full">
        <label for="notes">Observaciones</label>
        <textarea id="notes" name="notes">{{ old('notes', $companion->notes) }}</textarea>
        @error('notes') <div class="error">{{ $message }}</div> @enderror
    </div>
</div>
