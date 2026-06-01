<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Control de Familias e Invitados para XV'))</title>

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
                --primary-soft: #efe0fb;
                --text: #2f2140;
                --muted: #776582;
                --success: #dff4e6;
                --danger: #ffe1eb;
                --warning: #fff1d8;
            }

            * { box-sizing: border-box; }
            [x-cloak] { display: none !important; }
            body {
                margin: 0;
                font-family: Figtree, sans-serif;
                color: var(--text);
                background:
                    radial-gradient(circle at top left, #fbf2ff 0%, transparent 28%),
                    radial-gradient(circle at top right, #f0e1fb 0%, transparent 24%),
                    linear-gradient(180deg, #f7f2fb 0%, #f6f0fb 100%);
                min-height: 100vh;
            }

            a { color: inherit; text-decoration: none; }

            .shell { display: grid; grid-template-columns: 250px minmax(0, 1fr); min-height: 100vh; }
            .sidebar {
                background: rgba(255,255,255,0.85);
                border-right: 1px solid var(--border);
                padding: 24px 16px;
                position: sticky;
                top: 0;
                height: 100vh;
                overflow-y: auto;
                backdrop-filter: blur(14px);
                display: flex;
                flex-direction: column;
                gap: 18px;
            }
            .sidebar .brand-block {
                display: flex; align-items: center; gap: 12px;
                padding: 4px 8px 18px 8px;
                border-bottom: 1px solid var(--border);
            }
            .sidebar .brand-block .badge {
                width: 42px; height: 42px;
                border-radius: 12px;
                display: grid; place-items: center;
                background: linear-gradient(135deg, #c693ea, #8f55be);
                color: #fff; font-weight: 700; font-size: 14px;
                box-shadow: 0 6px 18px rgba(143, 85, 190, .24);
            }
            .sidebar .brand-block .titles { line-height: 1.2; }
            .sidebar .brand-block .titles strong { font-size: 14px; color: var(--text); display: block; }
            .sidebar .brand-block .titles span { font-size: 11px; color: var(--muted); }

            .sidebar .nav-section { display: flex; flex-direction: column; gap: 2px; }
            .sidebar .nav-section-label {
                font-size: 10.5px;
                text-transform: uppercase;
                letter-spacing: 0.1em;
                font-weight: 700;
                color: var(--muted);
                padding: 6px 12px;
                margin-top: 2px;
            }
            .nav-item {
                display: flex; align-items: center; gap: 10px;
                padding: 9px 12px;
                border-radius: 10px;
                color: var(--text);
                font-size: 14px;
                font-weight: 500;
                transition: background 0.15s;
                cursor: pointer;
            }
            .nav-item:hover { background: rgba(158, 104, 201, 0.08); }
            .nav-item.active {
                background: linear-gradient(135deg, var(--primary) 0%, #8f55be 100%);
                color: white;
                font-weight: 600;
                box-shadow: 0 6px 16px rgba(158, 104, 201, .25);
            }
            .nav-item .ico { width: 18px; text-align: center; font-size: 15px; flex-shrink: 0; }

            .main-area { padding: 28px 32px; min-width: 0; max-width: 1400px; }

            .userbar-sidebar {
                margin-top: auto;
                padding-top: 16px;
                border-top: 1px solid var(--border);
                display: flex; flex-direction: column; gap: 8px;
            }
            .userbar-sidebar .user-info { padding: 8px 12px; font-size: 13px; }
            .userbar-sidebar .user-info strong { display: block; color: var(--text); }
            .userbar-sidebar .user-info span { color: var(--muted); font-size: 12px; }
            .userbar-sidebar .actions { display: flex; gap: 6px; padding: 0 8px; }
            .userbar-sidebar .actions a, .userbar-sidebar .actions button { flex: 1; font-size: 12px; padding: 7px 10px; }
            .subnav {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .subnav-link {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 10px;
                padding: 14px 16px;
                border-radius: 16px;
                background: rgba(255,255,255,.88);
                border: 1px solid var(--border);
                color: var(--primary-dark);
                font-weight: 700;
            }
            .subnav-link.active {
                background: linear-gradient(135deg, #f2e2fc, #fff);
                border-color: #ca9beb;
                box-shadow: 0 18px 34px rgba(158, 104, 201, .14);
            }
            .subnav-count {
                min-width: 32px;
                padding: 5px 8px;
                border-radius: 999px;
                background: #f4ebfb;
                color: var(--primary-dark);
                font-size: 12px;
                text-align: center;
            }
            .section-title {
                margin: 0;
                font-size: 26px;
                line-height: 1.1;
                color: #4a2f60;
            }
            .section-kicker {
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: .18em;
                font-weight: 800;
                color: #9b67c8;
                margin-bottom: 8px;
            }
            .topbar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 18px;
                margin-bottom: 24px;
                padding: 18px 22px;
                background: rgba(255,255,255,.92);
                border: 1px solid var(--border);
                border-radius: 22px;
                box-shadow: 0 18px 55px rgba(122, 79, 168, .08);
                backdrop-filter: blur(14px);
            }

            .brand { display: flex; align-items: center; gap: 16px; }
            .brand-badge {
                width: 52px;
                height: 52px;
                border-radius: 18px;
                display: grid;
                place-items: center;
                background: linear-gradient(135deg, #c693ea, #8f55be);
                color: #fff;
                font-weight: 700;
                letter-spacing: .08em;
                box-shadow: 0 14px 30px rgba(143, 85, 190, .24);
            }

            .brand h1, .page-head h2 { margin: 0; }
            .brand .small, .small { color: var(--muted); font-size: 13px; }

            .userbar {
                display: flex;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
                justify-content: flex-end;
            }

            .btn, button, input, select, textarea {
                font: inherit;
            }

            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                border: 0;
                border-radius: 12px;
                padding: 10px 16px;
                background: var(--primary);
                color: #fff;
                cursor: pointer;
                font-weight: 600;
                box-shadow: 0 10px 24px rgba(158, 104, 201, .18);
            }

            .btn.secondary {
                background: #fff;
                color: var(--primary-dark);
                border: 1px solid var(--border);
                box-shadow: none;
            }

            .btn.ghost {
                background: transparent;
                color: var(--primary-dark);
                border: 1px dashed var(--border);
                box-shadow: none;
            }

            .btn.danger {
                background: #d8527f;
                box-shadow: 0 10px 24px rgba(216, 82, 127, .18);
            }
            .btn.success {
                background: #2ea866;
                box-shadow: 0 10px 24px rgba(46, 168, 102, .18);
            }
            .btn.small {
                padding: 7px 11px;
                border-radius: 10px;
                font-size: 12px;
                box-shadow: none;
            }
            .icon-btn {
                width: 34px;
                height: 34px;
                padding: 0;
                border-radius: 10px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                box-shadow: none;
            }
            .icon-btn svg {
                width: 16px;
                height: 16px;
            }
            .inline-edit-select,
            .inline-edit-input {
                min-width: 132px;
                width: 100%;
                padding: 8px 10px;
                border-radius: 10px;
                font-size: 13px;
                font-weight: 600;
                border: 1px solid #d9c6ea;
                box-shadow: inset 0 1px 2px rgba(91, 60, 120, .03);
            }
            .inline-edit-input {
                font-weight: 500;
                min-width: 150px;
            }
            .inline-edit-select[data-inline-tone="category"].tone-category-real {
                background: #e8f6ee;
                color: #246847;
                border-color: #bfe0cc;
            }
            .inline-edit-select[data-inline-tone="category"].tone-category-probable {
                background: #fff1d9;
                color: #946317;
                border-color: #efd7a7;
            }
            .category-real {
                background: #e8f6ee;
                color: #246847;
            }
            .category-probable {
                background: #fff1d9;
                color: #946317;
            }
            .inline-edit-select[data-inline-tone="status"].tone-status-confirmado {
                background: #dff4e6;
                color: #256141;
                border-color: #bfe2cb;
            }
            .inline-edit-select[data-inline-tone="status"].tone-status-no-asistira {
                background: #ffe1eb;
                color: #9d355b;
                border-color: #f3bfd0;
            }
            .inline-edit-select[data-inline-tone="status"].tone-status-considerado {
                background: #fff1d8;
                color: #9a6a1a;
                border-color: #f1d8a6;
            }
            .inline-edit-select[data-inline-tone="status"].tone-status-invitacion-enviada {
                background: #e5f4ff;
                color: #1f628e;
                border-color: #b8dbf5;
            }
            .inline-edit-select[data-inline-tone="status"].tone-status-pendiente {
                background: #fff5cb;
                color: #8b6a11;
                border-color: #eddc92;
            }
            .inline-edit-select[data-inline-tone="status"].tone-status-no-contesto {
                background: #eceff3;
                color: #55606f;
                border-color: #d1d7de;
            }
            .inline-edit-select[data-inline-tone="status"].tone-status-por-definir,
            .inline-edit-select[data-inline-tone="status"].tone-status-default {
                background: #f7e7fb;
                color: #9649a9;
                border-color: #e3c4ef;
            }
            .modal-overlay {
                position: fixed;
                inset: 0;
                background: rgba(47, 33, 64, .38);
                backdrop-filter: blur(4px);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 24px;
                z-index: 1000;
            }
            .modal-panel {
                width: min(980px, 100%);
                max-height: 88vh;
                overflow: auto;
                background: rgba(255,255,255,.98);
                border: 1px solid var(--border);
                border-radius: 26px;
                box-shadow: 0 24px 80px rgba(47, 33, 64, .16);
                padding: 24px;
            }

            .page-head { margin-bottom: 22px; }
            .page-head h2 {
                margin: 0;
                font-size: clamp(30px, 3.6vw, 42px);
                line-height: 1.04;
                color: #43275b;
                letter-spacing: -.02em;
            }
            .page-head p {
                margin: 10px 0 0;
                color: var(--muted);
                font-size: 15px;
                line-height: 1.65;
                max-width: 860px;
            }

            .flash {
                margin-bottom: 18px;
                padding: 14px 16px;
                border-radius: 16px;
                background: var(--primary-soft);
                color: var(--primary-dark);
                border: 1px solid #decaef;
            }

            .grid { display: grid; gap: 18px; }
            .grid.cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .grid.cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .grid.cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .grid.cols-6 { grid-template-columns: repeat(6, minmax(0, 1fr)); }

            .card {
                background: rgba(255,255,255,.94);
                border: 1px solid var(--border);
                border-radius: 22px;
                padding: 20px;
                box-shadow: 0 18px 55px rgba(122, 79, 168, .08);
            }

            .metric .label {
                font-size: 13px;
                color: var(--muted);
                margin-bottom: 10px;
                text-transform: uppercase;
                letter-spacing: .04em;
            }

            .metric .value { font-size: 32px; font-weight: 700; }
            .metric-subvalue {
                margin-top: 8px;
                display: flex;
                align-items: baseline;
                gap: 8px;
                color: #5f4c70;
            }
            .metric-subvalue strong {
                font-size: 18px;
                font-weight: 800;
                color: #4a2f60;
            }
            .metric-subvalue span {
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: .06em;
                color: var(--muted);
            }
            .metric-net {
                margin-top: 10px;
                display: flex;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
                font-size: 13px;
                color: #5f4c70;
            }
            .metric-net strong {
                font-size: 22px;
                font-weight: 800;
                color: #4a2f60;
            }
            .metric-net em {
                font-style: normal;
                font-size: 12px;
                font-weight: 700;
                color: #8a56af;
                background: #f5eafb;
                border-radius: 999px;
                padding: 4px 8px;
            }
            .status-summary-card {
                cursor: pointer;
                transition: transform .14s ease, box-shadow .14s ease, border-color .14s ease;
            }
            .category-summary-card {
                cursor: pointer;
                transition: transform .14s ease, box-shadow .14s ease, border-color .14s ease;
            }
            .status-summary-card:hover {
                transform: translateY(-1px);
                border-color: #cfa7ec;
                box-shadow: 0 20px 34px rgba(122, 79, 168, .12);
            }
            .category-summary-card:hover {
                transform: translateY(-1px);
                border-color: #cfa7ec;
                box-shadow: 0 20px 34px rgba(122, 79, 168, .12);
            }
            .status-summary-card.is-active {
                border-color: #9e68c9;
                box-shadow: 0 22px 36px rgba(122, 79, 168, .18);
                background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(246,236,252,.95));
            }
            .category-summary-card.is-active {
                border-color: #9e68c9;
                box-shadow: 0 22px 36px rgba(122, 79, 168, .18);
                background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(246,236,252,.95));
            }

            .toolbar, .inline {
                display: flex;
                align-items: center;
                gap: 12px;
                flex-wrap: wrap;
            }

            .toolbar { justify-content: space-between; margin-bottom: 18px; }

            label {
                display: block;
                margin-bottom: 6px;
                font-size: 13px;
                font-weight: 600;
                color: var(--muted);
            }

            input, select, textarea {
                width: 100%;
                border: 1px solid #dcc8ec;
                border-radius: 12px;
                padding: 10px 12px;
                background: #fff;
                color: var(--text);
            }

            input:focus, select:focus, textarea:focus {
                outline: 2px solid #ebdaf8;
                border-color: #c999eb;
            }

            textarea { min-height: 110px; resize: vertical; }
            .form-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 14px;
            }
            .form-grid .full { grid-column: 1 / -1; }
            .form-grid .span-2 { grid-column: span 2; }
            .compact-grid {
                display: grid;
                grid-template-columns: 1.2fr .9fr 1.6fr .9fr .9fr 1fr;
                gap: 10px;
                align-items: end;
            }

            .table-wrap { overflow: auto; }
            table {
                width: 100%;
                border-collapse: collapse;
                min-width: 980px;
            }
            th, td {
                padding: 13px 12px;
                border-bottom: 1px solid #efe4f7;
                vertical-align: top;
                text-align: left;
            }
            th {
                color: var(--muted);
                font-size: 13px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .04em;
            }

            .pill {
                display: inline-flex;
                align-items: center;
                padding: 6px 10px;
                border-radius: 999px;
                font-size: 12px;
                font-weight: 700;
            }

            .status-confirmado { background: var(--success); color: #256141; }
            .status-no-asistira { background: var(--danger); color: #9d355b; }
            .status-considerado { background: var(--warning); color: #9a6a1a; }
            .status-invitacion-enviada { background: #e5f4ff; color: #1f628e; }
            .status-pendiente { background: #fff5cb; color: #8b6a11; }
            .status-no-contesto { background: #eceff3; color: #55606f; }
            .status-por-definir { background: #f7e7fb; color: #9649a9; }
            .status-default { background: #f3eafa; color: var(--primary-dark); }

            .empty { text-align: center; color: var(--muted); padding: 28px; }
            .error { color: #b23d68; font-size: 13px; margin-top: 6px; }
            .muted-box {
                padding: 14px;
                border-radius: 16px;
                border: 1px dashed var(--border);
                background: #fbf8fe;
            }
            .module-card {
                display: block;
                padding: 22px;
                border-radius: 24px;
                background: rgba(255,255,255,.95);
                border: 1px solid var(--border);
                box-shadow: 0 18px 55px rgba(122, 79, 168, .08);
            }
            .module-card h3 {
                margin: 0 0 8px;
            }
            .quick-row {
                background: #fcf8ff;
            }
            .quick-row td {
                padding: 18px 16px;
            }

            @media (max-width: 1100px) {
                .grid.cols-4, .grid.cols-3, .grid.cols-2, .grid.cols-6, .form-grid, .compact-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 760px) {
                .shell { grid-template-columns: 1fr; }
                .sidebar { position: static; height: auto; }
                .main-area { padding: 16px; }
                .topbar, .toolbar, .userbar, .compact-grid, .form-grid {
                    grid-template-columns: 1fr;
                    flex-direction: column;
                    align-items: stretch;
                }
                .grid.cols-4, .grid.cols-3, .grid.cols-2, .grid.cols-6 {
                    grid-template-columns: 1fr;
                }
                .form-grid .span-2, .form-grid .full { grid-column: auto; }
            }
        </style>
    </head>
    <body>
        <div class="shell">
            @auth
                <aside class="sidebar">
                    <div class="brand-block">
                        <div class="badge">XV</div>
                        <div class="titles">
                            <strong>ControlXV</strong>
                            <span>XV de Zugeily</span>
                        </div>
                    </div>

                    <div class="nav-section">
                        <div class="nav-section-label">Inicio</div>
                        @if (Auth::user()->canAccessModule('dashboard'))
                            <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <span class="ico">📊</span><span>Resumen</span>
                            </a>
                        @endif
                    </div>

                    <div class="nav-section">
                        <div class="nav-section-label">Invitados</div>
                        @if (Auth::user()->canAccessModule('guests'))
                            <a class="nav-item {{ request()->routeIs('guests.*') ? 'active' : '' }}" href="{{ route('guests.index') }}">
                                <span class="ico">👨‍👩‍👧</span><span>Familias / grupos</span>
                            </a>
                        @endif
                        @if (Auth::user()->canAccessModule('companions'))
                            <a class="nav-item {{ request()->routeIs('companions.*') ? 'active' : '' }}" href="{{ route('companions.index') }}">
                                <span class="ico">👤</span><span>Invitados</span>
                            </a>
                        @endif
                        @if (Auth::user()->canAccessModule('tables'))
                            <a class="nav-item {{ request()->routeIs('tables.*') ? 'active' : '' }}" href="{{ route('tables.index') }}">
                                <span class="ico">🍽️</span><span>Mesas</span>
                            </a>
                        @endif
                    </div>

                    <div class="nav-section">
                        <div class="nav-section-label">Comunicación</div>
                        @if (Auth::user()->canAccessModule('message_sends'))
                            <a class="nav-item {{ request()->routeIs('message-sends.*') ? 'active' : '' }}" href="{{ route('message-sends.index') }}">
                                <span class="ico">💬</span><span>Mensajes</span>
                            </a>
                        @endif
                        @if (Auth::user()->canAccessModule('message_templates'))
                            <a class="nav-item {{ request()->routeIs('message-templates.*') ? 'active' : '' }}" href="{{ route('message-templates.index') }}">
                                <span class="ico">📝</span><span>Plantillas</span>
                            </a>
                        @endif
                    </div>

                    <div class="nav-section">
                        <div class="nav-section-label">Sistema</div>
                        @if (Auth::user()->canAccessModule('catalogs'))
                            <a class="nav-item {{ request()->routeIs('catalogs.*') ? 'active' : '' }}" href="{{ route('catalogs.index') }}">
                                <span class="ico">📚</span><span>Catálogos</span>
                            </a>
                        @endif
                        @if (Auth::user()->canAccessModule('settings'))
                            <a class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                                <span class="ico">⚙️</span><span>Configuración</span>
                            </a>
                        @endif
                        @if (Auth::user()->canAccessModule('system_transfer'))
                            <a class="nav-item {{ request()->routeIs('system-transfer.*') ? 'active' : '' }}" href="{{ route('system-transfer.edit') }}">
                                <span class="ico">💾</span><span>Respaldo</span>
                            </a>
                        @endif
                        @if (Auth::user()->canAccessModule('users'))
                            <a class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                <span class="ico">🔐</span><span>Usuarios</span>
                            </a>
                        @endif
                    </div>

                    <div class="userbar-sidebar">
                        <div class="user-info">
                            <strong>{{ Auth::user()->name }}</strong>
                            <span>{{ Auth::user()->email }}</span>
                        </div>
                        <div class="actions">
                            <a class="btn secondary" href="{{ route('profile.edit') }}">Perfil</a>
                            <form method="POST" action="{{ route('logout', absolute: false) }}" style="flex: 1;">
                                @csrf
                                <button class="btn ghost" type="submit" style="width: 100%;">Salir</button>
                            </form>
                        </div>
                    </div>
                </aside>
            @endauth

            <div class="main-area">
                @if (session('status'))
                    <div data-flash-status="{{ session('status') }}" hidden></div>
                @endif

                @hasSection('content')
                    <div class="page-head">
                        <h2>@yield('heading', 'Panel')</h2>
                        @hasSection('subheading')
                            <p>@yield('subheading')</p>
                        @endif
                    </div>

                    @yield('content')
                @else
                    @isset($header)
                        <div class="page-head">
                            {{ $header }}
                        </div>
                    @endisset

                    <main>
                        {{ $slot }}
                    </main>
                @endif
            </div>
        </div>
    </body>
</html>
