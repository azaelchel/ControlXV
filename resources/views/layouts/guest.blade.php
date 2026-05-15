<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Control de Familias e Invitados para XV') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                font-family: Figtree, sans-serif;
                background:
                    radial-gradient(circle at top left, rgba(255, 222, 248, 0.9) 0%, transparent 28%),
                    radial-gradient(circle at bottom right, rgba(224, 198, 255, 0.85) 0%, transparent 34%),
                    linear-gradient(135deg, #fff7fc 0%, #f4ecfb 52%, #efe6fa 100%);
                min-height: 100vh;
            }

            .xv-shell {
                min-height: 100vh;
                display: grid;
                grid-template-columns: 1.1fr .9fr;
            }

            .xv-hero {
                position: relative;
                overflow: hidden;
                padding: 52px;
                display: flex;
                align-items: center;
            }

            .xv-hero::before,
            .xv-hero::after {
                content: "";
                position: absolute;
                border-radius: 999px;
                filter: blur(8px);
            }

            .xv-hero::before {
                width: 260px;
                height: 260px;
                left: -60px;
                top: -40px;
                background: rgba(218, 159, 238, 0.25);
            }

            .xv-hero::after {
                width: 320px;
                height: 320px;
                right: -90px;
                bottom: -70px;
                background: rgba(255, 196, 227, 0.28);
            }

            .xv-hero-card {
                position: relative;
                z-index: 1;
                max-width: 560px;
                padding: 38px;
                border-radius: 32px;
                background: rgba(255, 255, 255, 0.58);
                border: 1px solid rgba(198, 151, 234, 0.38);
                box-shadow: 0 24px 70px rgba(146, 93, 185, 0.12);
                backdrop-filter: blur(18px);
            }

            .xv-badge {
                width: 112px;
                height: 112px;
                margin-bottom: 22px;
                border-radius: 32px;
                display: grid;
                place-items: center;
                color: #fff;
                font-size: 38px;
                font-weight: 800;
                letter-spacing: .08em;
                background:
                    linear-gradient(135deg, #f6bfd8 0%, #c88deb 48%, #9265ca 100%);
                box-shadow:
                    inset 0 1px 12px rgba(255,255,255,.28),
                    0 24px 46px rgba(146, 93, 185, .22);
                position: relative;
            }

            .xv-badge::before {
                content: "✦";
                position: absolute;
                top: 10px;
                right: 16px;
                font-size: 15px;
                opacity: .9;
            }

            .xv-badge::after {
                content: "✦";
                position: absolute;
                bottom: 12px;
                left: 16px;
                font-size: 13px;
                opacity: .78;
            }

            .xv-kicker {
                color: #8d5ab9;
                font-size: 13px;
                letter-spacing: .18em;
                text-transform: uppercase;
                font-weight: 700;
                margin-bottom: 10px;
            }

            .xv-title {
                font-size: clamp(32px, 4vw, 52px);
                line-height: 1.02;
                font-weight: 800;
                color: #45275d;
                margin: 0 0 12px;
            }

            .xv-copy {
                margin: 0;
                color: #6b5977;
                font-size: 16px;
                line-height: 1.75;
            }

            .xv-points {
                margin-top: 22px;
                display: grid;
                gap: 10px;
                color: #6a4f81;
                font-size: 14px;
            }

            .xv-point {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px 12px;
                border-radius: 14px;
                background: rgba(255,255,255,.62);
                border: 1px solid rgba(216, 193, 238, .7);
            }

            .xv-point span {
                width: 26px;
                height: 26px;
                border-radius: 999px;
                display: grid;
                place-items: center;
                background: linear-gradient(135deg, #deb2f3, #9c69cd);
                color: #fff;
                font-size: 12px;
                font-weight: 700;
            }

            .xv-login {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 36px 28px;
            }

            .xv-login-panel {
                width: 100%;
                max-width: 460px;
                padding: 34px 30px;
                border-radius: 30px;
                background: rgba(255,255,255,.86);
                border: 1px solid rgba(216, 193, 238, .86);
                box-shadow: 0 24px 68px rgba(120, 76, 156, .14);
                backdrop-filter: blur(16px);
            }

            @media (max-width: 980px) {
                .xv-shell { grid-template-columns: 1fr; }
                .xv-hero { padding: 28px 22px 10px; }
                .xv-login { padding-top: 8px; }
                .xv-hero-card { max-width: none; }
            }
        </style>
    </head>
    <body>
        <div class="xv-shell">
            <section class="xv-hero">
                <div class="xv-hero-card">
                    <div class="xv-badge">XV</div>
                    <div class="xv-kicker">Panel de organización</div>
                    <h1 class="xv-title">Control de Familias e Invitados para XV</h1>
                    <p class="xv-copy">
                        Un acceso limpio para revisar familias o grupos, invitados, confirmaciones, seguimientos y conteos
                        desde cualquier lugar, sin depender del Excel.
                    </p>

                    <div class="xv-points">
                        <div class="xv-point"><span>1</span>Consulta rápida de familias o grupos, estatus y totales.</div>
                        <div class="xv-point"><span>2</span>Edición cómoda para ajustes diarios y seguimiento.</div>
                        <div class="xv-point"><span>3</span>Base lista para subirse después a Railway.</div>
                    </div>
                </div>
            </section>

            <section class="xv-login">
                <div class="xv-login-panel">
                    {{ $slot }}
                </div>
            </section>
        </div>
    </body>
</html>
