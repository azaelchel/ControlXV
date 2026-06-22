<div class="grid cols-2" style="gap:12px;">
    <div>
        <label class="small">Padrino</label>
        <select name="guest_id" required>
            <option value="" disabled hidden @selected(empty($support->guest_id ?? null))>Selecciona una opción</option>
            @foreach ($padrinoOptions as $g)
                <option value="{{ $g->id }}" @selected((int) old('guest_id', $support->guest_id ?? 0) === (int) $g->id)>{{ $g->name }}@if ($g->sponsor) ({{ $g->sponsor }})@endif</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="small">Concepto del apoyo (opcional)</label>
        <input name="concept" value="{{ old('concept', $support->concept ?? '') }}" placeholder="Ej. Apoyo para salón">
    </div>
    <div>
        <label class="small">Monto que apoyará en total</label>
        <input name="pledged_amount" type="number" step="0.01" min="0" value="{{ old('pledged_amount', $support->pledged_amount ?? '') }}" required>
    </div>
    <div>
        <label class="small">Notas (opcional)</label>
        <input name="notes" value="{{ old('notes', $support->notes ?? '') }}" placeholder="Detalles…">
    </div>
</div>
