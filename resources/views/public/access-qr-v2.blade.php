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
        $heroImageWebp = asset('images/xv/pase-digital/fondo-completo-ramo-vestido.webp');
        $heroImagePng = asset('images/xv/pase-digital/fondo-completo-ramo-vestido.png');
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
            --xv-lavender-light: #f2eaf7;
            --xv-blush: #f6e5ed;
            --xv-gold: #c9a15b;
            --xv-gold-light: #e8d19c;
            --xv-sage: #8fa17a;
            --xv-ivory: #fffdf9;
            --xv-white: #ffffff;
            --xv-text: #5d5163;
            --xv-muted: #85758d;
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
                radial-gradient(circle at 12% 0%, rgba(246, 229, 237, .72), transparent 24rem),
                radial-gradient(circle at 88% 20%, rgba(203, 183, 221, .42), transparent 28rem),
                linear-gradient(180deg, #fffefd 0%, #fff8fc 46%, #fffdf9 100%);
        }
        a { color: inherit; }
        .page {
            width: min(100%, 760px);
            min-height: 100vh;
            margin: 0 auto;
            padding: max(16px, env(safe-area-inset-top)) 14px max(28px, env(safe-area-inset-bottom));
        }
        .pass {
            position: relative;
            overflow: hidden;
            border-radius: 32px;
            border: 1px solid rgba(201, 161, 91, .38);
            background: rgba(255, 255, 255, .82);
            box-shadow: 0 26px 72px rgba(93, 61, 118, .14), inset 0 0 0 1px rgba(255,255,255,.72);
        }
        .pass::before {
            content: "";
            position: absolute;
            inset: 11px;
            z-index: 4;
            pointer-events: none;
            border: 1px solid rgba(201, 161, 91, .26);
            border-radius: 23px;
        }
        .hero {
            position: relative;
            min-height: clamp(455px, 62vw, 565px);
            overflow: hidden;
            isolation: isolate;
            background: linear-gradient(145deg, #fffdf9, #f5eff9);
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
            object-position: 60% 24%;
        }
        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 1;
            background:
                linear-gradient(90deg, rgba(255, 253, 249, .98) 0%, rgba(255, 253, 249, .88) 34%, rgba(255, 253, 249, .28) 66%, rgba(255, 253, 249, .04) 100%),
                linear-gradient(180deg, rgba(255, 255, 255, .10) 0%, rgba(255, 253, 249, .03) 62%, rgba(255, 253, 249, .96) 100%);
        }
        .hero::after {
            content: "";
            position: absolute;
            left: 34px;
            right: 34px;
            bottom: 28px;
            z-index: 2;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(201, 161, 91, .86), transparent);
        }
        .hero-copy {
            position: relative;
            z-index: 3;
            width: min(52%, 390px);
            min-height: clamp(455px, 62vw, 565px);
            display: grid;
            align-content: center;
            gap: 13px;
            padding: 34px 0 54px 36px;
        }
        .monogram {
            width: 154px;
            height: 160px;
            object-fit: contain;
            filter: drop-shadow(0 16px 25px rgba(93, 61, 118, .11));
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
            max-width: 9ch;
            font-family: "Cormorant Garamond", Georgia, serif;
            font-size: clamp(52px, 9vw, 82px);
            font-weight: 700;
            line-height: .88;
            letter-spacing: 0;
            color: var(--xv-purple-dark);
            text-wrap: balance;
        }
        .event-date {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            gap: 9px;
            border-radius: 999px;
            padding: 10px 16px;
            background: rgba(255, 255, 255, .72);
            color: #7f6641;
            border: 1px solid rgba(201, 161, 91, .50);
            font-size: 14px;
            font-weight: 900;
            box-shadow: 0 12px 28px rgba(143, 92, 122, .10);
            backdrop-filter: blur(10px);
        }
        .body {
            position: relative;
            z-index: 5;
            padding: 0 34px 34px;
            margin-top: -26px;
        }
        .holder {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 16px;
            align-items: center;
            margin-bottom: 20px;
            padding: 20px 22px;
            border-radius: 26px;
            background: rgba(255, 255, 255, .92);
            border: 1px solid rgba(234, 223, 241, .98);
            box-shadow: 0 18px 42px rgba(93, 61, 118, .10);
            backdrop-filter: blur(18px);
        }
        .label {
            margin-bottom: 6px;
            color: var(--xv-muted);
            font-size: 12px;
            letter-spacing: .16em;
            text-transform: uppercase;
            font-weight: 900;
        }
        .guest-name {
            color: var(--xv-purple-dark);
            font-size: clamp(26px, 6vw, 40px);
            font-weight: 900;
            line-height: 1.04;
            overflow-wrap: anywhere;
        }
        .pass-badge {
            display: grid;
            place-items: center;
            min-width: 126px;
            min-height: 64px;
            padding: 11px 16px;
            border-radius: 20px;
            background: linear-gradient(145deg, #fffaf0, #fff7fb);
            border: 1px solid rgba(201, 161, 91, .48);
            color: #806944;
            font-size: 13px;
            font-weight: 900;
            text-align: center;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.72);
        }
        .qr-shell {
            position: relative;
            padding: 22px;
            border-radius: 30px;
            background: linear-gradient(180deg, rgba(255,255,255,.94), rgba(255,250,253,.98));
            border: 1px solid rgba(203, 183, 221, .76);
            box-shadow: 0 18px 44px rgba(93, 61, 118, .10);
        }
        .qr-shell::before,
        .qr-shell::after {
            content: "";
            position: absolute;
            top: 30px;
            width: 68px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(201, 161, 91, .82), transparent);
        }
        .qr-shell::before { left: 30px; }
        .qr-shell::after { right: 30px; }
        .qr-card {
            display: grid;
            justify-items: center;
            gap: 15px;
            max-width: 410px;
            margin: 0 auto;
            padding: 26px 24px 22px;
            border-radius: 26px;
            background: #fff;
            border: 1px solid #ede4f3;
            box-shadow: inset 0 0 0 8px #fff, inset 0 0 0 10px rgba(203, 183, 221, .24), 0 16px 30px rgba(93, 61, 118, .08);
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
            width: min(100%, 320px);
            aspect-ratio: 1;
            object-fit: contain;
            background: #fff;
            border-radius: 0;
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
            margin-top: 20px;
        }
        .action-card {
            display: grid;
            grid-template-columns: 46px minmax(0, 1fr) 16px;
            align-items: center;
            gap: 12px;
            min-height: 76px;
            padding: 14px;
            border-radius: 22px;
            text-decoration: none;
            background: rgba(255, 255, 255, .88);
            border: 1px solid rgba(234, 223, 241, .95);
            box-shadow: 0 12px 28px rgba(93, 61, 118, .07);
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
            -webkit-tap-highlight-color: transparent;
        }
        .action-card:hover,
        .action-card:focus-visible {
            transform: translateY(-2px);
            border-color: rgba(201, 161, 91, .58);
            box-shadow: 0 16px 34px rgba(93, 61, 118, .11);
            outline: none;
        }
        .action-card:active { transform: translateY(0); }
        .icon {
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            color: #765389;
            background: linear-gradient(145deg, #f8f1fb, #fffaf0);
            border: 1px solid rgba(201, 161, 91, .30);
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
        .btn-title { color: #6a5473; font-size: 15px; font-weight: 900; overflow-wrap: anywhere; }
        .btn-sub { color: var(--xv-muted); font-size: 12px; font-weight: 800; line-height: 1.25; }
        .btn-time { color: #9a7331; font-size: 11px; font-weight: 900; line-height: 1.25; letter-spacing: .03em; }
        .arrow { color: rgba(201, 161, 91, .88); font-weight: 900; }
        .note {
            display: flex;
            gap: 12px;
            margin-top: 18px;
            padding: 16px 17px;
            background: linear-gradient(145deg, #fffaf0, #fffdf9);
            color: #806944;
            border: 1px solid rgba(201, 161, 91, .42);
            border-radius: 21px;
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
            .page { padding: 10px 10px 18px; }
            .pass { border-radius: 26px; }
            .pass::before { inset: 7px; border-radius: 20px; }
            .hero { min-height: 470px; }
            .hero-photo { object-position: 58% 18%; }
            .hero::before {
                background:
                    linear-gradient(90deg, rgba(255, 253, 249, .97) 0%, rgba(255, 253, 249, .84) 42%, rgba(255, 253, 249, .18) 78%, rgba(255, 253, 249, .02) 100%),
                    linear-gradient(180deg, rgba(255, 255, 255, .04) 0%, rgba(255, 253, 249, .06) 64%, rgba(255, 253, 249, .96) 100%);
            }
            .hero-copy { width: 66%; min-height: 470px; padding: 28px 0 58px 22px; }
            .monogram { width: 124px; height: 130px; }
            h1 { font-size: clamp(43px, 13vw, 58px); }
            .event-date { font-size: 12px; padding: 8px 12px; }
            .body { padding: 0 16px 20px; margin-top: -22px; }
            .holder { grid-template-columns: 1fr; padding: 16px; border-radius: 21px; }
            .pass-badge { justify-self: start; min-width: 0; min-height: 0; padding: 9px 13px; }
            .qr-shell { padding: 12px; border-radius: 23px; }
            .qr-shell::before,
            .qr-shell::after { display: none; }
            .qr-card { padding: 20px 14px 16px; border-radius: 21px; box-shadow: inset 0 0 0 6px #fff, inset 0 0 0 8px rgba(203, 183, 221, .22), 0 12px 20px rgba(93, 61, 118, .07); }
            .qr-card img { width: min(100%, 300px); }
            .actions { grid-template-columns: 1fr; gap: 10px; }
            .action-card { min-height: 68px; border-radius: 18px; }
            .note { font-size: 13px; padding: 14px; }
        }
        @media (max-width: 420px) {
            .hero { min-height: 430px; }
            .hero-photo { object-position: 56% 14%; }
            .hero-copy { width: 74%; min-height: 430px; padding-left: 18px; padding-top: 24px; }
            .monogram { width: 106px; height: 110px; }
            .kicker { font-size: 11px; letter-spacing: .18em; }
            h1 { font-size: 38px; }
            .event-date { max-width: 100%; white-space: normal; line-height: 1.2; }
            .guest-name { font-size: 25px; }
            .qr-card img { width: min(100%, 286px); }
            .footer-line { gap: 8px; }
            .footer-line::before,
            .footer-line::after { width: 34px; }
        }
        @media (max-width: 360px) {
            .hero { min-height: 405px; }
            .hero-copy { width: 78%; padding-left: 15px; }
            h1 { font-size: 34px; }
            .qr-card img { width: min(100%, 260px); }
            .action-card { grid-template-columns: 40px minmax(0, 1fr) 14px; padding: 12px; }
            .icon { width: 40px; height: 40px; }
        }
        @media (min-width: 900px) {
            .page { display: grid; align-items: center; }
            .hero-photo { object-position: 58% 18%; }
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
                <div class="hero-copy">
                    <img class="monogram" src="{{ $monogramImage }}" width="1400" height="1460" alt="Monograma oficial de Zugeily">
                    <div class="kicker">Pase digital</div>
                    <h1>{{ $eventName }}</h1>
                    @if ($eventDate)
                        <div class="event-date"><span aria-hidden="true">✦</span><span>{{ $eventDate }}</span></div>
                    @endif
                </div>
            </header>

            <div class="body">
                <div class="holder">
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
                    <a class="action-card" href="{{ $links['misa'] }}" target="_blank" rel="noopener">
                        <span class="icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 21s7-4.8 7-11a7 7 0 1 0-14 0c0 6.2 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
                        <span class="btn-text"><span class="btn-title">Ubicación misa</span><span class="btn-sub">Catedral de Toluca</span><span class="btn-time">Misa 13:00 hrs</span></span>
                        <span class="arrow" aria-hidden="true">›</span>
                    </a>
                    <a class="action-card" href="{{ $links['recepcion'] }}" target="_blank" rel="noopener">
                        <span class="icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10.5V20h14v-9.5"/><path d="M9 20v-6h6v6"/></svg></span>
                        <span class="btn-text"><span class="btn-title">Ubicación recepción</span><span class="btn-sub">Hacienda La Cúpula</span><span class="btn-time">Recepción 3:30 PM</span></span>
                        <span class="arrow" aria-hidden="true">›</span>
                    </a>
                    <a class="action-card" href="{{ $links['liverpool'] }}" target="_blank" rel="noopener">
                        <span class="icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 12v8H4v-8"/><path d="M2 7h20v5H2z"/><path d="M12 7v13"/><path d="M12 7H8.5A2.5 2.5 0 1 1 12 4.5V7Z"/><path d="M12 7h3.5A2.5 2.5 0 1 0 12 4.5V7Z"/></svg></span>
                        <span class="btn-text"><span class="btn-title">Mesa Liverpool</span><span class="btn-sub">Lista de regalos</span></span>
                        <span class="arrow" aria-hidden="true">›</span>
                    </a>
                    <a class="action-card" href="{{ $links['amazon'] }}" target="_blank" rel="noopener">
                        <span class="icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 8h15l-2 8H8L6 8Z"/><path d="M6 8 5.2 5H3"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg></span>
                        <span class="btn-text"><span class="btn-title">Mesa Amazon</span><span class="btn-sub">Lista de regalos</span></span>
                        <span class="arrow" aria-hidden="true">›</span>
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
