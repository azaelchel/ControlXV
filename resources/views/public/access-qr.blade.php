<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>QR de acceso · {{ $eventName }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <style>
        :root {
            --bg: #f7f1fb;
            --ink: #2f2042;
            --muted: #746482;
            --primary: #8753b7;
            --primary-dark: #4b2c69;
            --rose: #d46f8d;
            --gold: #c9a45e;
            --gold-soft: #fff6df;
            --line: #e8daf4;
            --panel: #ffffff;
            --soft: #fbf8fd;
        }
        * { box-sizing: border-box; }
        html { min-height: 100%; background: var(--bg); }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Figtree, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(212,111,141,.18), transparent 32rem),
                linear-gradient(145deg, #fffafd 0%, #f4ecfa 52%, #fff8ed 100%);
        }
        a { color: inherit; }
        .page {
            width: min(100%, 680px);
            min-height: 100vh;
            margin: 0 auto;
            padding: max(18px, env(safe-area-inset-top)) 16px max(28px, env(safe-area-inset-bottom));
            display: grid;
            align-items: center;
        }
        .pass {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(139, 91, 179, .18);
            border-radius: 30px;
            background: rgba(255, 255, 255, .92);
            box-shadow: 0 24px 70px rgba(70, 42, 95, .16);
        }
        .pass::before {
            content: "";
            position: absolute;
            inset: 0 0 auto;
            height: 10px;
            background: linear-gradient(90deg, var(--gold), var(--rose), var(--primary));
        }
        .pass-inner { position: relative; padding: 28px; }
        .topline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }
        .brand { display: flex; align-items: center; gap: 14px; min-width: 0; }
        .mark {
            width: 58px;
            height: 58px;
            flex: 0 0 58px;
            border-radius: 20px;
            display: grid;
            place-items: center;
            color: white;
            font-size: 22px;
            font-weight: 900;
            background: linear-gradient(145deg, var(--primary), var(--rose));
            box-shadow: 0 14px 30px rgba(135, 83, 183, .25);
        }
        .kicker {
            color: var(--primary);
            font-size: 12px;
            letter-spacing: .2em;
            text-transform: uppercase;
            font-weight: 900;
            margin-bottom: 4px;
        }
        .guest { color: var(--muted); font-size: 15px; font-weight: 700; overflow-wrap: anywhere; }
        .event-date {
            flex: 0 0 auto;
            border-radius: 999px;
            padding: 8px 12px;
            background: var(--gold-soft);
            color: #815f1f;
            border: 1px solid #ead7a4;
            font-size: 13px;
            font-weight: 900;
            white-space: nowrap;
        }
        h1 {
            margin: 0;
            font-size: clamp(40px, 8vw, 68px);
            line-height: .92;
            letter-spacing: 0;
            color: var(--primary-dark);
        }
        .subtitle {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 12px 0 22px;
            color: var(--muted);
            font-size: 17px;
            font-weight: 700;
        }
        .spark { color: var(--gold); }
        .qr-shell {
            border: 1px solid var(--line);
            border-radius: 26px;
            background: linear-gradient(180deg, #fffcff, var(--soft));
            padding: 18px;
        }
        .qr-card {
            display: grid;
            gap: 14px;
            justify-items: center;
            border-radius: 22px;
            background: white;
            padding: 18px 18px 16px;
            border: 1px solid #eadff4;
            box-shadow: inset 0 0 0 8px #fbf8fd;
        }
        .qr-card img {
            display: block;
            width: min(100%, 350px);
            aspect-ratio: 1;
            object-fit: contain;
            border-radius: 14px;
            background: white;
        }
        .qr-caption {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: var(--primary-dark);
            font-size: 15px;
            font-weight: 800;
            text-align: center;
        }
        .missing {
            width: 100%;
            min-height: 220px;
            display: grid;
            place-items: center;
            text-align: center;
            padding: 30px 18px;
            border: 1px dashed #cdb8e2;
            border-radius: 18px;
            background: white;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.5;
            font-weight: 700;
        }
        .actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-top: 16px;
        }
        .btn {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 10px;
            min-height: 58px;
            padding: 12px 14px;
            border-radius: 18px;
            text-decoration: none;
            font-weight: 900;
            background: var(--primary);
            color: white;
            border: 1px solid rgba(255, 255, 255, .2);
            box-shadow: 0 14px 28px rgba(135, 83, 183, .2);
        }
        .btn.secondary {
            background: white;
            color: var(--primary-dark);
            border-color: var(--line);
            box-shadow: 0 10px 24px rgba(70, 42, 95, .07);
        }
        .icon {
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            background: rgba(255, 255, 255, .18);
            font-size: 18px;
        }
        .secondary .icon { background: #f7f0fb; color: var(--primary); }
        .btn-text { display: grid; gap: 1px; min-width: 0; }
        .btn-title { font-size: 15px; overflow-wrap: anywhere; }
        .btn-sub { font-size: 11px; font-weight: 800; opacity: .78; }
        .note {
            display: flex;
            gap: 10px;
            margin-top: 16px;
            padding: 14px 15px;
            background: #fffaf0;
            color: #76591f;
            border: 1px solid #ead8ad;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.45;
            font-weight: 700;
        }
        .note-icon { flex: 0 0 auto; color: var(--gold); font-weight: 900; }
        @media (max-width: 620px) {
            .page { align-items: start; padding: 10px 10px 18px; }
            .pass { border-radius: 24px; }
            .pass-inner { padding: 22px 16px 16px; }
            .topline { align-items: flex-start; margin-bottom: 16px; }
            .mark { width: 50px; height: 50px; flex-basis: 50px; border-radius: 17px; font-size: 19px; }
            .event-date { display: none; }
            h1 { font-size: clamp(38px, 15vw, 56px); }
            .subtitle { font-size: 15px; margin-bottom: 16px; }
            .qr-shell { padding: 12px; border-radius: 22px; }
            .qr-card { padding: 12px; box-shadow: inset 0 0 0 5px #fbf8fd; }
            .qr-card img { width: min(100%, 310px); }
            .actions { grid-template-columns: 1fr; gap: 9px; }
            .btn { min-height: 56px; border-radius: 16px; }
            .note { font-size: 13px; }
        }
        @media (max-width: 380px) {
            .brand { gap: 10px; }
            .pass-inner { padding-inline: 12px; }
            h1 { font-size: 34px; }
            .qr-card img { width: min(100%, 280px); }
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="pass" aria-label="Pase de acceso al evento">
            <div class="pass-inner">
                <div class="topline">
                    <div class="brand">
                        <div class="mark">XV</div>
                        <div>
                            <div class="kicker">Pase digital</div>
                            <div class="guest">{{ $guest->display_name }}</div>
                        </div>
                    </div>
                    @if ($eventDate)
                        <div class="event-date">{{ $eventDate }}</div>
                    @endif
                </div>

                <h1>{{ $eventName }}</h1>
                <div class="subtitle"><span class="spark">✦</span><span>QR personal de acceso</span></div>

                <div class="qr-shell">
                    <div class="qr-card">
                        @if ($link->isExpired())
                            <div class="missing">Este enlace ya venció. Por favor contacta al equipo organizador.</div>
                        @elseif ($qrDataUrl)
                            <img src="{{ $qrDataUrl }}" alt="QR de acceso para {{ $guest->name }}">
                            <div class="qr-caption"><span class="spark">✦</span><span>Presenta este código al llegar al evento.</span></div>
                        @else
                            <div class="missing">Tu QR aún está en preparación. Vuelve a abrir este enlace más tarde.</div>
                        @endif
                    </div>
                </div>

                <div class="actions">
                    <a class="btn" href="{{ $links['misa'] }}" target="_blank" rel="noopener">
                        <span class="icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-4.8 7-11a7 7 0 1 0-14 0c0 6.2 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
                        <span class="btn-text"><span class="btn-title">Ubicación misa</span><span class="btn-sub">Abrir en Maps</span></span>
                    </a>
                    <a class="btn" href="{{ $links['recepcion'] }}" target="_blank" rel="noopener">
                        <span class="icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10.5V20h14v-9.5"/><path d="M9 20v-6h6v6"/></svg></span>
                        <span class="btn-text"><span class="btn-title">Recepción</span><span class="btn-sub">Hacienda La Cúpula</span></span>
                    </a>
                    <a class="btn secondary" href="{{ $links['liverpool'] }}" target="_blank" rel="noopener">
                        <span class="icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 12v8H4v-8"/><path d="M2 7h20v5H2z"/><path d="M12 7v13"/><path d="M12 7H8.5A2.5 2.5 0 1 1 12 4.5V7Z"/><path d="M12 7h3.5A2.5 2.5 0 1 0 12 4.5V7Z"/></svg></span>
                        <span class="btn-text"><span class="btn-title">Mesa Liverpool</span><span class="btn-sub">Lista de regalos</span></span>
                    </a>
                    <a class="btn secondary" href="{{ $links['amazon'] }}" target="_blank" rel="noopener">
                        <span class="icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 8h15l-2 8H8L6 8Z"/><path d="M6 8 5.2 5H3"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg></span>
                        <span class="btn-text"><span class="btn-title">Mesa Amazon</span><span class="btn-sub">Lista de regalos</span></span>
                    </a>
                </div>

                <div class="note">
                    <span class="note-icon">i</span>
                    <span>Este enlace es personal para tu familia o grupo. No muestra mesa ni acompañantes; la información de acceso viene integrada en el QR.</span>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
