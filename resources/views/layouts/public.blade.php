<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @php
            $metaTitle = trim($__env->yieldContent('meta_title', $__env->yieldContent('title', 'Revisión de asistencia | XV de Zugeily')));
            $metaDescription = trim($__env->yieldContent('meta_description', 'Confirma o valida la asistencia de tu familia a los XV de Zugeily. Revisa que la información de las personas que asistirán sea correcta.'));
            // Cache-busting con filemtime para que WhatsApp/Facebook re-fetcheen si la imagen cambia
            $ogImagePath = public_path('images/og/xv-zugeily-revision-og.jpg');
            $ogImageVersion = file_exists($ogImagePath) ? filemtime($ogImagePath) : time();
            $metaImage = trim($__env->yieldContent('meta_image', asset('images/og/xv-zugeily-revision-og.jpg') . '?v=' . $ogImageVersion));
            $metaUrl = url()->current();
        @endphp
        <title>{{ $metaTitle }}</title>
        <meta name="description" content="{{ $metaDescription }}">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="XV de Zugeily">
        <meta property="og:title" content="{{ $metaTitle }}">
        <meta property="og:description" content="{{ $metaDescription }}">
        <meta property="og:url" content="{{ $metaUrl }}">
        <meta property="og:image" content="{{ $metaImage }}">
        <meta property="og:image:secure_url" content="{{ $metaImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $metaTitle }}">
        <meta name="twitter:description" content="{{ $metaDescription }}">
        <meta name="twitter:image" content="{{ $metaImage }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root {
                --bg: #f7f2fb;
                --panel: #ffffff;
                --border: #e5d7f1;
                --primary: #9e68c9;
                --primary-dark: #7f46b0;
                --text: #2f2140;
                --muted: #776582;
                --danger: #d8527f;
                --success: #2ea866;
            }
            * { box-sizing: border-box; }
            body {
                margin: 0;
                min-height: 100vh;
                font-family: Figtree, sans-serif;
                color: var(--text);
                background:
                    radial-gradient(circle at top left, #fbf2ff 0%, transparent 28%),
                    radial-gradient(circle at bottom right, #efdff9 0%, transparent 24%),
                    linear-gradient(180deg, #f7f2fb 0%, #f5eefb 100%);
            }
            .shell { max-width: 1120px; margin: 0 auto; padding: 28px 22px 40px; }
            .hero, .card {
                background: rgba(255,255,255,.95);
                border: 1px solid var(--border);
                border-radius: 24px;
                box-shadow: 0 18px 55px rgba(122, 79, 168, .10);
            }
            .hero { padding: 28px; margin-bottom: 22px; }
            .grid { display: grid; gap: 16px; }
            .grid.cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .grid.cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .brand { display: flex; gap: 16px; align-items: flex-start; }
            .badge {
                width: 62px; height: 62px; border-radius: 20px;
                background: linear-gradient(135deg, #c693ea, #8f55be);
                display: grid; place-items: center; color: #fff;
                font-weight: 800; font-size: 24px;
            }
            h1, h2, h3 { margin: 0; color: #43275b; }
            h1 { font-size: clamp(32px, 4vw, 46px); line-height: 1.05; }
            p { color: var(--muted); line-height: 1.7; }
            .small { font-size: 13px; color: var(--muted); }
            .card { padding: 22px; }
            .metric { padding: 18px; border-radius: 18px; background: #fcf9ff; border: 1px solid #ecdff7; }
            .metric strong { display: block; font-size: 28px; margin-top: 8px; color: #4a2f60; }
            .kicker { font-size: 12px; text-transform: uppercase; letter-spacing: .18em; font-weight: 800; color: #9b67c8; margin-bottom: 8px; }
            .btn, button, input, select, textarea { font: inherit; }
            .btn {
                display: inline-flex; align-items: center; justify-content: center; gap: 8px;
                border: 0; border-radius: 12px; padding: 10px 16px; cursor: pointer;
                background: var(--primary); color: #fff; font-weight: 600;
                box-shadow: 0 10px 24px rgba(158, 104, 201, .18);
            }
            .btn.secondary { background: #fff; color: var(--primary-dark); border: 1px solid var(--border); box-shadow: none; }
            .btn.danger { background: var(--danger); box-shadow: 0 10px 24px rgba(216, 82, 127, .18); }
            .btn.ghost { background: transparent; color: var(--primary-dark); border: 1px dashed var(--border); box-shadow: none; }
            .btn.small { padding: 7px 12px; font-size: 12px; box-shadow: none; }
            .inline { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
            .table-wrap { overflow: auto; }
            table { width: 100%; border-collapse: collapse; min-width: 860px; }
            th, td { padding: 14px; border-bottom: 1px solid #efe5f7; text-align: left; vertical-align: top; }
            th { font-size: 12px; text-transform: uppercase; letter-spacing: .08em; color: #8a72a4; }
            input, select, textarea {
                width: 100%; border: 1px solid #dcc8ec; border-radius: 12px;
                padding: 10px 12px; background: #fff; color: #2f2140;
            }
            textarea { min-height: 84px; resize: vertical; }
            .row-card-removed { opacity: .45; }
            .row-card-removed .row-fields { display: none; }
            .row-note { margin-top: 10px; font-size: 12px; color: #8a6aa8; }
            .flash { margin-bottom: 18px; padding: 14px 16px; border-radius: 16px; background: #efe0fb; color: #5f4c70; border: 1px solid #e0cef0; }
            .error-box { margin-bottom: 18px; padding: 14px 16px; border-radius: 16px; background: #ffe7ef; color: #8a3354; border: 1px solid #f3c8d7; }
            @media (max-width: 860px) {
                .grid.cols-3, .grid.cols-2 { grid-template-columns: 1fr; }
                .hero, .card { padding: 18px; }
            }
        </style>
    </head>
    <body>
        <div class="shell">
            @if ($errors->any())
                <div class="error-box">
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('content')
        </div>
    </body>
</html>
