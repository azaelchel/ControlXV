<div class="form-grid">
    <div>
        <label for="group_name">Grupo</label>
        <select id="group_name" name="group_name" required>
            <option value="" disabled hidden @selected(empty(old('group_name', $guest->group_name)))>Selecciona una opción</option>
            @foreach ($options['groups'] as $value)
                <option value="{{ $value }}" @selected(old('group_name', $guest->group_name) === $value)>{{ $value }}</option>
            @endforeach
        </select>
        @error('group_name') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="prefix">Prefijo</label>
        <select id="prefix" name="prefix">
            <option value="">Sin prefijo</option>
            @foreach ($options['prefixes'] as $value)
                <option value="{{ $value }}" @selected(old('prefix', $guest->prefix) === $value)>{{ $value }}</option>
            @endforeach
        </select>
        @error('prefix') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div class="full">
        <label for="name">Nombre</label>
        <input id="name" name="name" type="text" value="{{ old('name', $guest->name) }}" required>
        @error('name') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="category">Categoria</label>
        <select id="category" name="category" required>
            <option value="" disabled hidden @selected(empty(old('category', $guest->category)))>Selecciona una opción</option>
            @foreach ($options['categories'] as $value)
                <option value="{{ $value }}" @selected(old('category', $guest->category) === $value)>{{ $value }}</option>
            @endforeach
        </select>
        @error('category') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="status">Estatus</label>
        <select id="status" name="status" required>
            <option value="" disabled hidden @selected(empty(old('status', $guest->status)))>Selecciona una opción</option>
            @foreach ($options['statuses'] as $value)
                <option value="{{ $value }}" @selected(old('status', $guest->status) === $value)>{{ $value }}</option>
            @endforeach
        </select>
        @error('status') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="phone">Telefono</label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $guest->phone) }}">
        <div class="small">Se guarda normalizado solo con numeros.</div>
        @error('phone') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="sponsor">Padrino</label>
        <select id="sponsor" name="sponsor">
            <option value="">Sin padrino</option>
            @foreach ($options['sponsors'] as $value)
                <option value="{{ $value }}" @selected(old('sponsor', $guest->sponsor) === $value)>{{ $value }}</option>
            @endforeach
        </select>
        @error('sponsor') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="adults">Adultos</label>
        <input id="adults" name="adults" type="number" min="0" value="{{ old('adults', $guest->adults ?? 0) }}" required>
        @error('adults') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="adolescents">Adolescentes</label>
        <input id="adolescents" name="adolescents" type="number" min="0" value="{{ old('adolescents', $guest->adolescents ?? 0) }}" required>
        @error('adolescents') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="children">Niños</label>
        <input id="children" name="children" type="number" min="0" value="{{ old('children', $guest->children ?? 0) }}" required>
        @error('children') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div class="full">
        <label for="notes">Observaciones</label>
        <textarea id="notes" name="notes">{{ old('notes', $guest->notes) }}</textarea>
        @error('notes') <div class="error">{{ $message }}</div> @enderror
    </div>
</div>
