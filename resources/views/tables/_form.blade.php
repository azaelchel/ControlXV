<div class="grid cols-2" style="gap: 12px;">
    <div>
        <label class="small">Nombre o número de mesa</label>
        <input name="name" value="{{ old('name', $table->name ?? '') }}" required placeholder="Ej. Mesa 1, Principal, Familia López">
    </div>
    <div>
        <label class="small">Capacidad</label>
        <input name="capacity" type="number" min="1" max="50" value="{{ old('capacity', $table->capacity ?? 10) }}" required>
    </div>
    <div>
        <label class="small">Tipo de mesa</label>
        <select name="table_type">
            <option value="">Sin tipo</option>
            @foreach ($types as $type)
                <option value="{{ $type }}" @selected(old('table_type', $table->table_type ?? '') === $type)>{{ $type }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="small">Forma</label>
        <select name="shape">
            <option value="">Sin forma</option>
            @foreach ($shapes as $shape)
                <option value="{{ $shape }}" @selected(old('shape', $table->shape ?? '') === $shape)>{{ $shape }}</option>
            @endforeach
        </select>
    </div>
</div>
<div style="margin-top: 10px;">
    <label class="small">Observaciones</label>
    <input name="notes" value="{{ old('notes', $table->notes ?? '') }}" placeholder="Opcional">
</div>
<label class="inline small" style="margin-top: 10px; gap: 6px; cursor: pointer;">
    <input type="checkbox" name="is_principal" value="1" @checked(old('is_principal', $table->is_principal ?? false)) style="width: auto;">
    Marcar como mesa principal
</label>
