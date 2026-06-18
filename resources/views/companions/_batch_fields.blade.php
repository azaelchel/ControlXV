@php
    $selectedGroup = old('invited_group');
@endphp

<div class="form-grid" id="companion-batch-builder" data-companion-batch-builder>
    <div class="full">
        <label for="invited_group_batch">Familia o grupo</label>
        <select id="invited_group_batch" name="invited_group" required data-companion-group-select>
            <option value="" disabled hidden @selected(empty($selectedGroup))>Selecciona una opción</option>
            @foreach ($guestOptions as $value)
                <option value="{{ $value }}" @selected($selectedGroup === $value)>{{ $value }}</option>
            @endforeach
        </select>
        @error('invited_group') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div class="full">
        <div class="muted-box" data-companion-profile-summary>
            Selecciona una familia o grupo para ver cuántos invitados faltan por registrar.
        </div>
        @error('entries') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div class="full">
        <div data-companion-empty-state class="muted-box">
            Aquí aparecerán automáticamente los registros pendientes por capturar.
        </div>

        <div data-companion-rows style="display: grid; gap: 12px;"></div>
    </div>
</div>

<script type="application/json" id="companion-profiles-json">@json($guestProfiles)</script>
