<div class="grid cols-2" style="gap:12px;">
    <div>
        <label class="small">Concepto / proveedor del gasto</label>
        <input name="name" value="{{ old('name', $expense->name ?? '') }}" required placeholder="Ej. Salón Jardín, Foto y video">
    </div>
    <div>
        <label class="small">Categoría</label>
        <select name="category">
            <option value="">Sin categoría</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat }}" @selected(old('category', $expense->category ?? '') === $cat)>{{ $cat }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="small">Proveedor (opcional)</label>
        <input name="provider" value="{{ old('provider', $expense->provider ?? '') }}" placeholder="Nombre del proveedor / contacto">
    </div>
    <div>
        <label class="small">Costo total</label>
        <input name="total_amount" type="number" step="0.01" min="0" value="{{ old('total_amount', $expense->total_amount ?? '') }}" required>
    </div>
    <div>
        <label class="small">Fecha límite de pago (opcional)</label>
        <input name="due_date" type="date" value="{{ old('due_date', isset($expense) && $expense?->due_date ? $expense->due_date->toDateString() : '') }}">
    </div>
    <div>
        <label class="small">Notas (opcional)</label>
        <input name="notes" value="{{ old('notes', $expense->notes ?? '') }}" placeholder="Detalles, condiciones…">
    </div>
</div>
