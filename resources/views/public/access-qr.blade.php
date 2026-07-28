<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    @php
        $previewTitle = 'Pase digital · ' . $eventName;
        $previewDescription = 'QR personal de acceso para ' . $guest->display_name . '. Presenta este código al llegar al evento.';
        $previewImage = asset('images/og/xv-zugeily-access-qr.jpg');
        $passHolderPrefix = filled($guest->prefix) ? $guest->prefix : 'Fam.';
        $passHolderName = trim(collect([$passHolderPrefix, $guest->name])->filter()->implode(' '));
        $monogramImage = asset('images/xv/pase-digital/monograma-zugeily.png');
        $heroImageWebp = asset('images/xv/pase-digital/fondo-ramo-vestido.webp');
        $heroImagePng = asset('images/xv/pase-digital/fondo-ramo-vestido.png');
    @endphp
    <title>{{ $previewTitle }}</title>
    <meta name="description" content="{{ $previewDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ request()->fullUrl() }}">
    <meta property="og:title" content="{{ $previewTitle }}">
    <meta property="og:description" content="{{ $previewDescription }}">
    <meta property="og:image" content="{{ $previewImage }}">
    <meta property="og:image:secure_url" content="{{ $previewImage }}">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $previewTitle }}">
    <meta name="twitter:description" content="{{ $previewDescription }}">
    <meta name="twitter:image" content="{{ $previewImage }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:500,600,700|figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <style>
        :root {
            --xv-purple: #75528f;
            --xv-purple-dark: #5d3d76;
            --xv-lavender: #cbb7dd;
            --xv-lavender-light: #f5eff9;
            --xv-blush: #f9e9f0;
            --xv-gold: #c9a15b;
            --xv-gold-light: #ead7a0;
            --xv-sage: #8fa17a;
            --xv-ivory: #fffdf9;
            --xv-white: #ffffff;
            --xv-text: #5d5163;
            --xv-muted: #887892;
            --xv-line: #eadff1;
        }

        * { box-sizing: border-box; }
        html { min-height: 100%; background: var(--xv-ivory); }
        body {
            margin: 0;
            min-height: 100vh;
            overflow-x: hidden;
            font-family: Figtree, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--xv-text);
            background:
                radial-gradient(circle at 10% 0%, rgba(249, 205, 221, .36), transparent 22rem),
                radial-gradient(circle at 92% 16%, rgba(232, 209, 156, .30), transparent 20rem),
                radial-gradient(circle at 50% 100%, rgba(203, 183, 221, .30), transparent 24rem),
                linear-gradient(180deg, #fffefe 0%, #fff8fc 48%, #fffdf9 100%);
        }
        body::before,
        body::after {
            content: "";
            position: fixed;
            pointer-events: none;
            z-index: -1;
            border-radius: 999px;
        }
        body::before {
            width: 42rem;
            height: 42rem;
            left: -18rem;
            top: 8rem;
            background: radial-gradient(circle, rgba(255, 255, 255, .86), rgba(246, 229, 237, .18) 58%, transparent 70%);
        }
        body::after {
            width: 34rem;
            height: 34rem;
            right: -16rem;
            bottom: -10rem;
            background: radial-gradient(circle, rgba(255, 255, 255, .74), rgba(201, 161, 91, .14) 56%, transparent 72%);
        }
        a { color: inherit; }

        .page {
            width: min(100%, 760px);
            min-height: 100vh;
            margin: 0 auto;
            padding: max(18px, env(safe-area-inset-top)) 14px max(28px, env(safe-area-inset-bottom));
            display: grid;
            align-items: center;
        }
        .pass {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(201, 161, 91, .32);
            border-radius: 32px;
            background: rgba(255, 255, 255, .88);
            box-shadow: 0 24px 70px rgba(93, 61, 118, .13), 0 2px 0 rgba(255, 255, 255, .9) inset;
        }
        .pass::before {
            content: "";
            position: absolute;
            inset: 10px;
            pointer-events: none;
            border: 1px solid rgba(201, 161, 91, .22);
            border-radius: 24px;
            z-index: 2;
        }
        .hero {
            position: relative;
            min-height: 310px;
            overflow: hidden;
            isolation: isolate;
            background: #fff8fc;
        }
        .hero picture,
        .hero-photo {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
        }
        .hero-photo {
            object-fit: cover;
            object-position: 76% 42%;
        }
        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 1;
            background:
                linear-gradient(90deg, rgba(255, 253, 249, .97) 0%, rgba(255, 253, 249, .86) 44%, rgba(255, 253, 249, .36) 68%, rgba(255, 253, 249, .08) 100%),
                linear-gradient(180deg, rgba(255,255,255,.24) 0%, rgba(255,253,249,.16) 56%, rgba(255,253,249,.94) 100%);
        }
        .hero::after {
            content: "";
            position: absolute;
            left: 34px;
            right: 34px;
            bottom: 22px;
            height: 1px;
            z-index: 2;
            background: linear-gradient(90deg, transparent, rgba(201, 161, 91, .74), transparent);
        }
        .hero-content {
            position: relative;
            z-index: 3;
            min-height: 310px;
            display: grid;
            align-content: center;
            gap: 12px;
            width: min(56%, 410px);
            padding: 34px 0 36px 36px;
        }
        .monogram {
            width: 148px;
            height: 154px;
            object-fit: contain;
            filter: drop-shadow(0 12px 24px rgba(93, 61, 118, .10));
        }
        .kicker {
            color: var(--xv-purple);
            font-size: 12px;
            letter-spacing: .22em;
            line-height: 1.5;
            text-transform: uppercase;
            font-weight: 900;
        }
        h1 {
            margin: 0;
            max-width: 10ch;
            font-family: "Cormorant Garamond", Georgia, serif;
            font-size: clamp(42px, 8vw, 72px);
            font-weight: 700;
            line-height: .92;
            letter-spacing: 0;
            color: #6d4c83;
            text-wrap: balance;
        }
        .event-date {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            gap: 8px;
            border-radius: 999px;
            padding: 8px 13px;
            background: rgba(255, 255, 255, .72);
            color: #7f6641;
            border: 1px solid rgba(201, 161, 91, .42);
            font-size: 13px;
            font-weight: 800;
            box-shadow: 0 10px 24px rgba(143, 92, 122, .09);
        }
        .pass-body {
            position: relative;
            z-index: 3;
            padding: 0 34px 34px;
        }
        .identity-strip {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 14px;
            align-items: center;
            margin: -12px 0 18px;
            padding: 16px 18px;
            border: 1px solid rgba(234, 223, 241, .95);
            border-radius: 22px;
            background: rgba(255, 255, 255, .90);
            box-shadow: 0 16px 34px rgba(93, 61, 118, .08);
            backdrop-filter: blur(14px);
        }
        .label {
            margin-bottom: 4px;
            color: var(--xv-muted);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        .guest-name {
            color: var(--xv-purple-dark);
            font-size: clamp(22px, 6vw, 34px);
            font-weight: 900;
            line-height: 1.08;
            overflow-wrap: anywhere;
        }
        .pass-badge {
            display: grid;
            place-items: center;
            min-width: 112px;
            min-height: 54px;
            padding: 10px 14px;
            border-radius: 18px;
            background: linear-gradient(145deg, #fffaf0, #f8edf7);
            border: 1px solid rgba(201, 161, 91, .42);
            color: #7f6641;
            font-size: 13px;
            font-weight: 900;
            text-align: center;
        }
        .qr-shell {
            position: relative;
            border: 1px solid rgba(203, 183, 221, .72);
            border-radius: 28px;
            background:
                linear-gradient(180deg, rgba(255,255,255,.88), rgba(255,250,253,.96)),
                linear-gradient(135deg, rgba(246, 229, 237, .82), rgba(242, 234, 247, .88));
            padding: 20px;
            box-shadow: 0 18px 44px rgba(93, 61, 118, .10);
        }
        .qr-shell::before,
        .qr-shell::after {
            content: "";
            position: absolute;
            width: 52px;
            height: 1px;
            top: 28px;
            background: linear-gradient(90deg, transparent, rgba(201, 161, 91, .86), transparent);
        }
        .qr-shell::before { left: 26px; }
        .qr-shell::after { right: 26px; }
        .qr-card {
            display: grid;
            gap: 14px;
            justify-items: center;
            max-width: 390px;
            margin: 0 auto;
            border-radius: 24px;
            background: #fff;
            padding: 26px 24px 20px;
            border: 1px solid #ede4f3;
            box-shadow: inset 0 0 0 8px #fff, inset 0 0 0 10px rgba(203, 183, 221, .24), 0 16px 26px rgba(93, 61, 118, .08);
        }
        .qr-title {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--xv-purple);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .18em;
            text-transform: uppercase;
        }
        .qr-card img {
            display: block;
            width: min(100%, 318px);
            aspect-ratio: 1;
            object-fit: contain;
            border-radius: 0;
            background: #fff;
        }
        .qr-caption {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #7e6d87;
            font-size: 15px;
            font-weight: 800;
            text-align: center;
        }
        .spark { color: var(--xv-gold); }
        .missing {
            width: 100%;
            min-height: 230px;
            display: grid;
            place-items: center;
            text-align: center;
            padding: 30px 18px;
            border: 1px dashed #cdb8e2;
            border-radius: 18px;
            background: #fff;
            color: var(--xv-muted);
            font-size: 16px;
            line-height: 1.5;
            font-weight: 800;
        }
        .actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 18px;
        }
        .action-card {
            display: grid;
            grid-template-columns: 44px minmax(0, 1fr);
            align-items: center;
            gap: 12px;
            min-height: 74px;
            padding: 14px;
            border-radius: 20px;
            text-decoration: none;
            background: rgba(255, 255, 255, .84);
            color: #6a5473;
            border: 1px solid rgba(234, 223, 241, .95);
            box-shadow: 0 12px 28px rgba(93, 61, 118, .07);
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease, background .18s ease;
            -webkit-tap-highlight-color: transparent;
        }
        .action-card.primary {
            background: linear-gradient(145deg, #fff, #fbf2f7);
            border-color: rgba(203, 183, 221, .88);
        }
        .action-card:hover,
        .action-card:focus-visible {
            transform: translateY(-2px);
            border-color: rgba(201, 161, 91, .55);
            box-shadow: 0 16px 32px rgba(93, 61, 118, .11);
            outline: none;
        }
        .action-card:active { transform: translateY(0); }
        .icon {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            color: #7e5b8c;
            background: linear-gradient(145deg, #f8f1fb, #fffaf0);
            border: 1px solid rgba(201, 161, 91, .28);
        }
        .icon svg {
            width: 21px;
            height: 21px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.9;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .btn-text { display: grid; gap: 3px; min-width: 0; }
        .btn-title { font-size: 15px; font-weight: 900; overflow-wrap: anywhere; }
        .btn-sub { color: var(--xv-muted); font-size: 12px; font-weight: 800; line-height: 1.25; }
        .btn-time { color: #9a7331; font-size: 11px; font-weight: 900; line-height: 1.25; letter-spacing: .03em; }
        .note {
            display: flex;
            gap: 12px;
            margin-top: 18px;
            padding: 16px 17px;
            background: linear-gradient(145deg, #fffaf0, #fffdf9);
            color: #806944;
            border: 1px solid rgba(201, 161, 91, .42);
            border-radius: 20px;
            font-size: 14px;
            line-height: 1.5;
            font-weight: 700;
        }
        .note-icon {
            flex: 0 0 28px;
            width: 28px;
            height: 28px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: rgba(201, 161, 91, .14);
            color: #9a7331;
            font-weight: 900;
        }
        .footer-line {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 18px;
            color: rgba(126, 109, 135, .78);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .footer-line::before,
        .footer-line::after {
            content: "";
            width: 54px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(201, 161, 91, .65));
        }
        .footer-line::after { transform: scaleX(-1); }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
            }
        }
        @media (max-width: 720px) {
            .page { align-items: start; padding: 10px 10px 18px; }
            .pass { border-radius: 26px; }
            .pass::before { inset: 7px; border-radius: 20px; }
            .hero { min-height: 270px; }
            .hero::before {
                background:
                    linear-gradient(90deg, rgba(255, 253, 249, .98) 0%, rgba(255, 253, 249, .86) 52%, rgba(255, 253, 249, .30) 100%),
                    linear-gradient(180deg, rgba(255,255,255,.10) 0%, rgba(255,253,249,.96) 100%);
            }
            .hero-photo { object-position: 73% 38%; }
            .hero-content { min-height: 270px; width: 66%; padding: 26px 0 34px 22px; }
            .monogram { width: 112px; height: 116px; }
            h1 { max-width: 9ch; font-size: clamp(38px, 12.5vw, 56px); }
            .event-date { font-size: 12px; padding: 7px 11px; }
            .pass-body { padding: 0 16px 20px; }
            .identity-strip {
                grid-template-columns: 1fr;
                margin-top: -8px;
                padding: 14px;
                border-radius: 19px;
            }
            .pass-badge { justify-self: start; min-width: 0; min-height: 0; padding: 8px 12px; }
            .qr-shell { padding: 12px; border-radius: 22px; }
            .qr-shell::before,
            .qr-shell::after { display: none; }
            .qr-card { padding: 20px 14px 16px; border-radius: 20px; box-shadow: inset 0 0 0 6px #fff, inset 0 0 0 8px rgba(203, 183, 221, .22), 0 12px 20px rgba(93, 61, 118, .07); }
            .qr-card img { width: min(100%, 300px); }
            .actions { grid-template-columns: 1fr; gap: 10px; }
            .action-card { min-height: 66px; border-radius: 18px; }
            .note { font-size: 13px; padding: 14px; }
        }
        @media (max-width: 420px) {
            .hero { min-height: 244px; }
            .hero-content { min-height: 244px; width: 72%; padding-left: 18px; padding-top: 22px; }
            .monogram { width: 94px; height: 98px; }
            .kicker { font-size: 11px; letter-spacing: .18em; }
            h1 { font-size: 36px; }
            .event-date { max-width: 100%; white-space: normal; line-height: 1.2; }
            .guest-name { font-size: 24px; }
            .qr-card img { width: min(100%, 286px); }
            .footer-line { gap: 8px; }
            .footer-line::before,
            .footer-line::after { width: 34px; }
        }
        @media (max-width: 360px) {
            .hero-content { width: 78%; padding-left: 15px; }
            h1 { font-size: 32px; }
            .qr-card img { width: min(100%, 260px); }
            .action-card { grid-template-columns: 40px minmax(0, 1fr); padding: 12px; }
            .icon { width: 40px; height: 40px; }
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="pass" aria-label="Pase de acceso al evento">
            <header class="hero">
                <picture aria-hidden="true">
                    <source srcset="{{ $heroImageWebp }}" type="image/webp">
                    <img class="hero-photo" src="{{ $heroImagePng }}" width="1600" height="1870" alt="" fetchpriority="high">
                </picture>
                <div class="hero-content">
                    <img class="monogram" src="{{ $monogramImage }}" width="1400" height="1460" alt="Monograma oficial de Zugeily">
                    <div class="kicker">Pase digital</div>
                    <h1>{{ $eventName }}</h1>
                    @if ($eventDate)
                        <div class="event-date">
                            <span aria-hidden="true">✦</span>
                            <span>{{ $eventDate }}</span>
                        </div>
                    @endif
                </div>
            </header>

            <div class="pass-body">
                <div class="identity-strip">
                    <div>
                        <div class="label">Pase personal para</div>
                        <div class="guest-name">{{ $passHolderName }}</div>
                    </div>
                    <div class="pass-badge">QR personal<br>de acceso</div>
                </div>

                <div class="qr-shell">
                    <div class="qr-card">
                        @if ($link->isExpired())
                            <div class="missing">Este enlace ya venció. Por favor contacta al equipo organizador.</div>
                        @elseif ($qrDataUrl)
                            <div class="qr-title"><span class="spark" aria-hidden="true">✦</span><span>Código de ingreso</span></div>
                            <img src="{{ $qrDataUrl }}" alt="QR de acceso para {{ $guest->name }}">
                            <div class="qr-caption"><span class="spark" aria-hidden="true">✦</span><span>Presenta este código al llegar al evento.</span></div>
                        @else
                            <div class="missing">Tu QR aún está en preparación. Vuelve a abrir este enlace más tarde.</div>
                        @endif
                    </div>
                </div>

                <div class="actions" aria-label="Enlaces importantes del evento">
                    <a class="action-card primary" href="{{ $links['misa'] }}" target="_blank" rel="noopener">
                        <span class="icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 21s7-4.8 7-11a7 7 0 1 0-14 0c0 6.2 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
                        <span class="btn-text"><span class="btn-title">Ubicación misa</span><span class="btn-sub">Catedral de Toluca</span><span class="btn-time">Misa 13:00 hrs</span></span>
                    </a>
                    <a class="action-card primary" href="{{ $links['recepcion'] }}" target="_blank" rel="noopener">
                        <span class="icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10.5V20h14v-9.5"/><path d="M9 20v-6h6v6"/></svg></span>
                        <span class="btn-text"><span class="btn-title">Ubicación recepción</span><span class="btn-sub">Hacienda La Cúpula</span><span class="btn-time">Recepción 3:30 PM</span></span>
                    </a>
                    <a class="action-card" href="{{ $links['liverpool'] }}" target="_blank" rel="noopener">
                        <span class="icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 12v8H4v-8"/><path d="M2 7h20v5H2z"/><path d="M12 7v13"/><path d="M12 7H8.5A2.5 2.5 0 1 1 12 4.5V7Z"/><path d="M12 7h3.5A2.5 2.5 0 1 0 12 4.5V7Z"/></svg></span>
                        <span class="btn-text"><span class="btn-title">Mesa Liverpool</span><span class="btn-sub">Lista de regalos</span></span>
                    </a>
                    <a class="action-card" href="{{ $links['amazon'] }}" target="_blank" rel="noopener">
                        <span class="icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 8h15l-2 8H8L6 8Z"/><path d="M6 8 5.2 5H3"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg></span>
                        <span class="btn-text"><span class="btn-title">Mesa Amazon</span><span class="btn-sub">Lista de regalos</span></span>
                    </a>
                </div>

                <div class="note">
                    <span class="note-icon" aria-hidden="true">i</span>
                    <span>Este enlace es personal para tu familia o grupo. No muestra mesa ni acompañantes; la información de acceso viene integrada en el QR.</span>
                </div>

                <div class="footer-line" aria-hidden="true">Zugeily</div>
            </div>
        </section>
    </main>
</body>
</html>
