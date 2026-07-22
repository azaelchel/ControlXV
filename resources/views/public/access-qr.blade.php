<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QR de acceso · {{ $eventName }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <style>
        :root { --bg:#f7f2fb; --panel:#fff; --border:#e6d7f2; --primary:#8f55be; --dark:#3c2651; --muted:#756582; --gold:#c9a86a; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Figtree, sans-serif; color: var(--dark); background: linear-gradient(180deg,#fbf6ff,#f4edf9); min-height: 100vh; }
        .wrap { max-width: 760px; margin: 0 auto; padding: 24px 16px 36px; }
        .hero { background: var(--panel); border: 1px solid var(--border); border-radius: 22px; padding: 24px; box-shadow: 0 18px 50px rgba(100,60,140,.10); }
        .kicker { color: var(--primary); font-size: 12px; letter-spacing: .18em; text-transform: uppercase; font-weight: 800; }
        h1 { margin: 8px 0 6px; font-size: clamp(30px, 8vw, 54px); line-height: .95; }
        .small { color: var(--muted); font-size: 14px; line-height: 1.55; }
        .qr-panel { margin-top: 18px; background: #faf7fe; border: 1px solid var(--border); border-radius: 18px; padding: 18px; text-align: center; }
        .qr-panel img { max-width: min(100%, 420px); width: 100%; border-radius: 14px; background: white; border: 1px solid #eadff4; }
        .missing { padding: 34px 20px; border: 1px dashed #cdb8e2; border-radius: 14px; background: white; color: var(--muted); }
        .actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-top: 18px; }
        .btn { display: flex; align-items: center; justify-content: center; text-align: center; min-height: 48px; padding: 12px 14px; border-radius: 14px; text-decoration: none; font-weight: 800; background: var(--primary); color: white; box-shadow: 0 12px 28px rgba(143,85,190,.18); }
        .btn.secondary { background: white; color: var(--primary); border: 1px solid var(--border); box-shadow: none; }
        .note { margin-top: 14px; padding: 13px 14px; background: #fffaf0; color: #805f21; border: 1px solid #ead8ad; border-radius: 14px; font-size: 13px; line-height: 1.5; }
        @media (max-width: 560px) { .actions { grid-template-columns: 1fr; } .hero { padding: 20px; } }
    </style>
</head>
<body>
    <main class="wrap">
        <section class="hero">
            <div class="kicker">QR de acceso</div>
            <h1>{{ $eventName }}</h1>
            <div class="small">
                {{ $guest->display_name }}@if ($eventDate) · {{ $eventDate }} @endif
            </div>

            <div class="qr-panel">
                @if ($link->isExpired())
                    <div class="missing">Este enlace ya venció. Por favor contacta al equipo organizador.</div>
                @elseif ($qrDataUrl)
                    <img src="{{ $qrDataUrl }}" alt="QR de acceso para {{ $guest->name }}">
                    <div class="small" style="margin-top: 10px;">Presenta este código en el acceso el día del evento.</div>
                @else
                    <div class="missing">Tu QR aún está en preparación. Vuelve a abrir este enlace más tarde.</div>
                @endif
            </div>

            <div class="actions">
                <a class="btn" href="{{ $links['misa'] }}" target="_blank" rel="noopener">Ubicación misa</a>
                <a class="btn" href="{{ $links['recepcion'] }}" target="_blank" rel="noopener">Ubicación recepción</a>
                <a class="btn secondary" href="{{ $links['liverpool'] }}" target="_blank" rel="noopener">Mesa Liverpool</a>
                <a class="btn secondary" href="{{ $links['amazon'] }}" target="_blank" rel="noopener">Mesa Amazon</a>
            </div>

            <div class="note">Este enlace es personal para tu familia o grupo. No muestra mesa ni lista de acompañantes; la información de acceso viene integrada en el QR.</div>
        </section>
    </main>
</body>
</html>
