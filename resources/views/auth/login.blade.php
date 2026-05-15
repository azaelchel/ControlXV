<x-guest-layout>
    <div style="margin-bottom: 20px;">
        <div style="font-size: 13px; letter-spacing: .14em; text-transform: uppercase; color: #9b6bc8; font-weight: 700; margin-bottom: 8px;">
            Acceso de prueba
        </div>
        <h2 style="margin: 0; font-size: 30px; line-height: 1.1; font-weight: 800; color: #4c2d63;">
            Inicia sesión
        </h2>
        <p style="margin: 10px 0 0; color: #786582; line-height: 1.65;">
            Entra al panel de familias e invitados para capturar, editar y revisar confirmaciones.
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login', absolute: false) }}">
        @csrf

        <div>
            <x-input-label for="login" :value="__('Usuario o correo')" />
            <x-text-input
                id="login"
                class="block mt-1 w-full"
                type="text"
                name="login"
                :value="old('login')"
                required
                autofocus
                autocomplete="username"
                placeholder="Ejemplo: azael"
            />
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" />

            <x-text-input
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="Tu contraseña"
            />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-purple-300 text-purple-600 shadow-sm focus:ring-purple-400" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Mantener sesión') }}</span>
            </label>
        </div>

        <div style="margin-top: 22px; padding: 14px 16px; border-radius: 16px; background: #f7effc; color: #7b56a0; font-size: 13px; line-height: 1.6;">
            <strong>Acceso temporal para pruebas:</strong><br>
            Usuario: <strong>azael</strong><br>
            Contraseña: <strong>123</strong>
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button class="ms-3" style="background: linear-gradient(135deg, #c68bea 0%, #9265ca 100%); border: 0; box-shadow: 0 16px 30px rgba(146, 101, 202, .18);">
                {{ __('Entrar al panel') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
