<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Staffing Tracker')</title>
    <link rel="icon" type="image/webp" href="{{ asset('favicon.webp') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        :root {
            --teal-deep: #0a2d29;
            --teal-matte: #0f3d37;
            --teal-glow: #1a5c52;
            --gold: #f1cd86;
            --gold-bright: #ffe4a8;
            --gold-dim: #c9a85c;
            --sidebar-width: 280px;
            --sidebar-collapsed: 72px;
            --topbar-height: 72px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html {
            overflow-x: hidden;
        }

        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #eef2f1 0%, #f5f5f5 50%, #e8eeed 100%);
            color: #333;
            min-height: 100vh;
            overflow-x: hidden;
            max-width: 100%;
        }

        .app-container {
            display: flex;
            min-height: 100vh;
            max-width: 100%;
            overflow-x: hidden;
        }

        /* ── Sidebar: collapsed by default, expands on hover ── */
        .sidebar {
            width: var(--sidebar-collapsed);
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            background: var(--teal-deep);
            border-right: 1px solid rgba(241, 205, 134, 0.1);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.12);
            transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.35s ease;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar:hover {
            width: var(--sidebar-width);
            box-shadow: 8px 0 40px rgba(0, 0, 0, 0.22);
        }

        .sidebar.collapsed { width: var(--sidebar-collapsed); }

        /* Push main content when sidebar expands — no overlay on topbar */
        .app-container:has(.sidebar:hover) .main-content {
            margin-left: var(--sidebar-width);
        }

        .sidebar-header {
            padding: 22px 16px 18px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            border-bottom: 1px solid rgba(241, 205, 134, 0.1);
            position: relative;
            z-index: 2;
        }

        .sidebar-header-top { display: none; }

        .sidebar-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            width: 100%;
            text-align: center;
        }

        .sidebar-logo {
            height: 40px;
            width: auto;
            flex-shrink: 0;
            transition: height 0.35s ease;
        }

        .sidebar-brand-text {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #fff;
            white-space: nowrap;
            line-height: 1.2;
            transition: opacity 0.25s ease, height 0.35s ease, max-height 0.35s ease;
            opacity: 0;
            max-height: 0;
            overflow: hidden;
        }

        .sidebar:hover .sidebar-brand-text {
            opacity: 1;
            max-height: 40px;
        }

        .sidebar-brand-text .gold { color: var(--gold); }

        .sidebar:hover .sidebar-brand { gap: 12px; }
        .sidebar .sidebar-brand { gap: 0; }

        .sidebar:hover .sidebar-logo { height: 56px; }

        .toggle-btn { display: none; }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 16px 12px;
            position: relative;
            z-index: 2;
        }

        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(241, 205, 134, 0.25);
            border-radius: 4px;
        }

        .nav-section-label { display: none; }

        .sidebar-menu { list-style: none; }

        .sidebar-menu > li {
            margin-bottom: 4px;
            opacity: 0;
            animation: navSlideIn 0.5s ease forwards;
        }

        .sidebar-menu > li:nth-child(1) { animation-delay: 0.05s; }
        .sidebar-menu > li:nth-child(2) { animation-delay: 0.1s; }
        .sidebar-menu > li:nth-child(3) { animation-delay: 0.15s; }
        .sidebar-menu > li:nth-child(4) { animation-delay: 0.2s; }
        .sidebar-menu > li:nth-child(5) { animation-delay: 0.25s; }

        @keyframes navSlideIn {
            from { opacity: 0; transform: translateX(-12px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            color: rgba(255, 255, 255, 0.82);
            text-decoration: none;
            border-radius: 12px;
            position: relative;
            transition: color 0.25s, background 0.25s, transform 0.2s;
            overflow: hidden;
        }

        .sidebar-menu a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%) scaleY(0);
            width: 3px;
            height: 60%;
            background: var(--gold);
            border-radius: 0 3px 3px 0;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 0 12px var(--gold);
        }

        .sidebar-menu a:hover {
            color: var(--white, #fff);
            background: rgba(241, 205, 134, 0.08);
            transform: translateX(3px);
        }

        .sidebar-menu a:hover::before { transform: translateY(-50%) scaleY(1); }

        .sidebar-menu a.active {
            color: var(--teal-deep);
            background: linear-gradient(90deg, var(--gold) 0%, var(--gold-bright) 100%);
            box-shadow: 0 4px 20px rgba(241, 205, 134, 0.25);
            font-weight: 600;
        }

        .sidebar-menu a.active::before { display: none; }

        .menu-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .menu-icon svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .menu-label {
            white-space: nowrap;
            font-size: 14px;
            font-weight: 500;
            transition: opacity 0.25s, width 0.35s;
        }

        .sidebar:not(:hover) .menu-label { opacity: 0; width: 0; overflow: hidden; }
        .sidebar:not(:hover) .sidebar-menu a { justify-content: center; padding: 12px; gap: 0; }
        .sidebar:not(:hover) .sidebar-menu a:hover { transform: none; }
        .sidebar:not(:hover) .sidebar-menu a.active {
            width: 44px;
            margin: 0 auto;
            padding: 12px;
            border-radius: 12px;
        }
        .sidebar:not(:hover) .dropdown-chevron { display: none; }

        .sidebar:not(:hover) .sidebar-menu a[data-tooltip],
        .sidebar:not(:hover) .sidebar-footer a[data-tooltip] {
            position: relative;
        }

        .sidebar:not(:hover) .sidebar-menu a[data-tooltip]:hover::after,
        .sidebar:not(:hover) .sidebar-footer a[data-tooltip]:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            left: calc(100% + 12px);
            top: 50%;
            transform: translateY(-50%);
            background: var(--teal-deep);
            color: var(--gold);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            white-space: nowrap;
            border: 1px solid rgba(241, 205, 134, 0.25);
            box-shadow: 0 4px 16px rgba(0,0,0,0.3);
            z-index: 1001;
            pointer-events: none;
        }

        /* Dropdown */
        .dropdown-btn { justify-content: space-between !important; cursor: pointer; }

        .dropdown-chevron {
            font-size: 10px;
            color: rgba(255,255,255,0.5);
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            margin-left: auto;
        }

        .dropdown-btn.active .dropdown-chevron { transform: rotate(180deg); color: var(--gold); }
        .dropdown-btn.active { color: var(--gold); background: rgba(241, 205, 134, 0.06); }

        .submenu {
            list-style: none;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            margin: 2px 0 4px 8px;
            padding-left: 12px;
            border-left: 1px solid rgba(241, 205, 134, 0.15);
            transition: max-height 0.45s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
        }

        .submenu.active { max-height: 320px; opacity: 1; }

        .submenu a {
            padding: 9px 12px;
            font-size: 13px;
            border-radius: 8px;
        }

        .submenu a:hover { transform: translateX(2px); }
        .sidebar:not(:hover) .submenu { max-height: 0 !important; opacity: 0 !important; }

        .sidebar-footer {
            padding: 12px;
            border-top: 1px solid rgba(241, 205, 134, 0.1);
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sidebar-footer a,
        .sidebar-footer .logout-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            text-decoration: none;
            border-radius: 12px;
            transition: color 0.25s, background 0.25s;
            color: rgba(255, 255, 255, 0.72);
            font-size: 14px;
            font-weight: 500;
        }

        .sidebar-footer a:hover,
        .guide-footer-link:hover {
            color: var(--gold) !important;
            background: rgba(241, 205, 134, 0.08) !important;
        }

        .sidebar-footer a.active,
        .guide-footer-link.active {
            color: var(--gold) !important;
            background: rgba(241, 205, 134, 0.1) !important;
        }

        .sidebar:not(:hover) .sidebar-footer a { justify-content: center; padding: 12px; gap: 0; }
        .sidebar:not(:hover) .sidebar-footer .menu-label { opacity: 0; width: 0; overflow: hidden; }

        .logout-link {
            color: rgba(255, 255, 255, 0.65) !important;
        }

        .logout-link:hover {
            color: #ff8a8a !important;
            background: rgba(255, 100, 100, 0.1) !important;
        }

        /* ── Main + Topbar ── */
        .main-content {
            margin-left: var(--sidebar-collapsed);
            flex: 1;
            min-width: 0;
            max-width: 100%;
            overflow-x: hidden;
            transition: margin-left 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .app-topbar {
            height: var(--topbar-height);
            position: sticky;
            top: 0;
            z-index: 900;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            background: linear-gradient(135deg, var(--teal-deep) 0%, var(--teal-matte) 45%, var(--teal-glow) 100%);
            border-bottom: 2px solid transparent;
            border-image: linear-gradient(90deg, transparent, var(--gold), transparent) 1;
            box-shadow: 0 4px 24px rgba(10, 45, 41, 0.2);
            overflow: hidden;
            isolation: isolate;
        }

        .app-topbar::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 40% 80% at 0% 50%, rgba(241, 205, 134, 0.08) 0%, transparent 60%),
                radial-gradient(ellipse 30% 60% at 100% 50%, rgba(26, 92, 82, 0.4) 0%, transparent 60%);
            pointer-events: none;
        }

        .app-topbar::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.04), transparent);
            animation: topbarShimmer 6s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes topbarShimmer {
            0%, 100% { left: -100%; }
            50% { left: 150%; }
        }

        .topbar-left {
            position: relative;
            z-index: 1;
        }

        .topbar-heading {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            letter-spacing: 0.02em;
            line-height: 1.2;
        }

        .topbar-heading em { display: none; }

        .topbar-subtitle { display: none; }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
            z-index: 20;
        }

        .topbar-datetime {
            position: relative;
            padding-right: 16px;
            border-right: 1px solid rgba(241, 205, 134, 0.2);
            z-index: 30;
        }

        .world-clock {
            position: relative;
        }

        .world-clock.open {
            z-index: 40;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .world-clock-trigger {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px 7px 10px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(241, 205, 134, 0.2);
            border-radius: 999px;
            color: #fff;
            cursor: pointer;
            font-family: inherit;
            transition: background 0.25s, border-color 0.25s, box-shadow 0.25s;
        }

        .world-clock-trigger:hover,
        .world-clock.open .world-clock-trigger {
            background: rgba(241, 205, 134, 0.1);
            border-color: rgba(241, 205, 134, 0.35);
            box-shadow: 0 0 18px rgba(241, 205, 134, 0.12);
        }

        .world-clock-icon {
            width: 16px;
            height: 16px;
            color: var(--gold);
            flex-shrink: 0;
        }

        .world-clock-preview {
            position: relative;
            min-width: 108px;
            height: 18px;
            overflow: hidden;
        }

        .world-clock-slide {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            gap: 6px;
            opacity: 0;
            transform: translateY(6px);
            transition: opacity 0.45s ease, transform 0.45s ease;
            white-space: nowrap;
            font-size: 12px;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
            color: var(--gold);
        }

        .world-clock-slide.active {
            opacity: 1;
            transform: translateY(0);
        }

        .world-clock-slide .tz-badge {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: rgba(255, 255, 255, 0.55);
            padding: 2px 5px;
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.08);
        }

        .world-clock-chevron {
            width: 14px;
            height: 14px;
            color: rgba(255, 255, 255, 0.45);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            flex-shrink: 0;
        }

        .world-clock.open .world-clock-chevron {
            transform: rotate(180deg);
            color: var(--gold);
        }

        .world-clock-panel {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: 220px;
            background: linear-gradient(160deg, #0f3d37 0%, #0a2d29 100%);
            border: 1px solid rgba(241, 205, 134, 0.22);
            border-radius: 14px;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.04);
            padding: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px) scale(0.96);
            transform-origin: top right;
            transition: opacity 0.28s ease, transform 0.28s cubic-bezier(0.22, 1, 0.36, 1), visibility 0.28s;
            z-index: 5000;
            overflow: hidden;
        }

        .world-clock-panel.is-portaled {
            position: fixed;
            top: auto;
            right: auto;
            left: auto;
            margin: 0;
            max-width: calc(100vw - 24px);
        }

        .world-clock.open .world-clock-panel,
        .world-clock-panel.is-portaled.open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }

        .world-clock-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            opacity: 0;
            transform: translateX(8px);
            transition: background 0.2s;
        }

        .world-clock:not(.open) .world-clock-row,
        .world-clock-panel:not(.open) .world-clock-row {
            animation: none;
        }

        .world-clock.open .world-clock-row,
        .world-clock-panel.open .world-clock-row {
            animation: worldClockRowIn 0.4s ease forwards;
        }

        .world-clock.open .world-clock-row:nth-child(1),
        .world-clock-panel.open .world-clock-row:nth-child(1) { animation-delay: 0.04s; }
        .world-clock.open .world-clock-row:nth-child(2),
        .world-clock-panel.open .world-clock-row:nth-child(2) { animation-delay: 0.1s; }
        .world-clock.open .world-clock-row:nth-child(3),
        .world-clock-panel.open .world-clock-row:nth-child(3) { animation-delay: 0.16s; }

        @keyframes worldClockRowIn {
            to { opacity: 1; transform: translateX(0); }
        }

        .world-clock-row:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .world-clock-row + .world-clock-row {
            margin-top: 2px;
        }

        .world-clock-row__zone {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .world-clock-row__label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
        }

        .world-clock-row__date {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.62);
            white-space: nowrap;
        }

        .world-clock-row__time {
            font-size: 14px;
            font-weight: 700;
            color: var(--gold);
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .world-clock-row.is-primary .world-clock-row__label {
            color: var(--gold);
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 14px 6px 6px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(241, 205, 134, 0.18);
            border-radius: 40px;
            transition: background 0.3s, box-shadow 0.3s;
        }

        .topbar-user:hover {
            background: rgba(241, 205, 134, 0.1);
            box-shadow: 0 0 20px rgba(241, 205, 134, 0.12);
        }

        .topbar-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-dim) 100%);
            color: var(--teal-deep);
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(241, 205, 134, 0.3);
        }

        .topbar-username {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
        }

        .topbar-role { display: none; }

        .page-body {
            flex: 1;
            padding: 24px 28px;
        }

        /* Hide duplicate page h1 when topbar shows title */
        .has-topbar .content-header h1 { display: none; }
        .has-topbar .content-header {
            margin-bottom: 20px;
            margin-top: -4px;
        }

        @media (max-width: 1200px) { .page-body { padding: 18px 20px; } }

        @media (prefers-reduced-motion: reduce) {
            .world-clock-slide { transition: none; }
            .world-clock-panel { transition: none; }
            .world-clock.open .world-clock-row { animation: none; opacity: 1; transform: none; }
            .world-clock-panel.open .world-clock-row { animation: none; opacity: 1; transform: none; }
        }

        @media (max-width: 768px) {
            .sidebar { width: var(--sidebar-collapsed); }
            .sidebar:hover { width: var(--sidebar-collapsed); box-shadow: 4px 0 24px rgba(0, 0, 0, 0.12); }
            .app-container:has(.sidebar:hover) .main-content { margin-left: var(--sidebar-collapsed); }
            .sidebar:hover .sidebar-brand-text,
            .sidebar .sidebar-brand-text,
            .sidebar:hover .menu-label,
            .sidebar .menu-label,
            .sidebar .nav-section-label,
            .sidebar .dropdown-chevron { opacity: 0; width: 0; overflow: hidden; display: none; }
            .sidebar .sidebar-menu a,
            .sidebar:hover .sidebar-menu a { justify-content: center; padding: 12px; }
            .sidebar:hover .submenu { max-height: 0 !important; opacity: 0 !important; }
            .main-content { margin-left: var(--sidebar-collapsed); }
            .app-topbar { padding: 0 16px; height: 64px; }
            .topbar-datetime {
                padding-right: 8px;
            }

            .world-clock-preview {
                min-width: 92px;
            }

            .world-clock-slide {
                font-size: 11px;
            }

            .world-clock-panel {
                width: 200px;
            }

            .topbar-user .topbar-username { display: none; }
            .topbar-heading { font-size: 18px; }
            .page-body { padding: 14px 12px; }
        }

        /* ── Shared components (unchanged) ── */
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .content-header h1 { color: var(--teal-deep); font-size: 28px; }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            font-family: inherit;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-primary { background: var(--gold); color: var(--teal-deep); }
        .btn-primary:hover { background: var(--gold-bright); transform: translateY(-1px); box-shadow: 0 4px 14px rgba(241,205,134,0.35); }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn-danger:hover { background-color: #c82333; }
        .btn-secondary { background-color: #6c757d; color: white; }
        .btn-secondary:hover { background-color: #5a6268; }
        .table-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(10, 45, 41, 0.06);
            border: 1px solid rgba(10, 45, 41, 0.06);
        }
        table { width: 100%; border-collapse: collapse; }
        thead { background: var(--teal-deep); color: white; }
        th, td { padding: 8px; text-align: center; border-bottom: 1px solid #e8eeed; }
        tbody tr:hover { background-color: #f9fbfa; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 10px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: var(--teal-deep); font-weight: 500; }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; font-family: inherit;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none; border-color: var(--gold); box-shadow: 0 0 0 3px rgba(241, 205, 134, 0.15);
        }
        .action-buttons { display: flex; gap: 10px; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .user-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }

        @media (prefers-reduced-motion: reduce) {
            .sidebar-menu > li { animation: none; opacity: 1; }
            .app-topbar::after { animation: none; }
            .toggle-btn:hover { transform: none; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="app-container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <img src="{{ asset('logo.png') }}" alt="RADiiX INFINITEii" class="sidebar-logo">
                    <div class="sidebar-brand-text">RADiiX <span class="gold">INFINITEii</span></div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section-label">Main Menu</div>
                <ul class="sidebar-menu">
                    <li>
                        <a href="{{ route('dashboard.index') }}"
                           class="{{ request()->routeIs('dashboard.*') ? 'active' : '' }}"
                           data-tooltip="Dashboard Analytics">
                            <span class="menu-icon"><svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg></span>
                            <span class="menu-label">Dashboard Analytics</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('tracker.index') }}"
                           class="{{ request()->routeIs('tracker.*') ? 'active' : '' }}"
                           data-tooltip="Recruiterment Workspace">
                            <span class="menu-icon"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg></span>
                            <span class="menu-label">Recruiterment Workspace</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('resume.analysis.index') }}"
                           class="{{ request()->routeIs('resume.*') ? 'active' : '' }}"
                           data-tooltip="Resume Analysis">
                            <span class="menu-icon"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></span>
                            <span class="menu-label">Resume Analysis</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('candidates.search.index') }}"
                           class="{{ request()->routeIs('candidates.search.*') ? 'active' : '' }}"
                           data-tooltip="Find Candidates">
                            <span class="menu-icon"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
                            <span class="menu-label">Find Candidates</span>
                        </a>
                    </li>

                    <li class="dropdown-wrapper">
                        <a href="javascript:void(0)"
                           class="dropdown-btn {{ (request()->routeIs('months.*') || request()->routeIs('users.*') || request()->routeIs('clients.*') || request()->routeIs('regions.*') || request()->routeIs('candidates.*')) ? 'active' : '' }}"
                           onclick="toggleDropdown(this)"
                           data-tooltip="Register">
                            <span class="menu-icon"><svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></span>
                            <span class="menu-label">Register</span>
                            <span class="dropdown-chevron">▼</span>
                        </a>
                        <ul class="submenu {{ (request()->routeIs('months.*') || request()->routeIs('users.*') || request()->routeIs('clients.*') || request()->routeIs('regions.*') || request()->routeIs('candidates.*')) ? 'active' : '' }}">
                            <li><a href="{{ route('months.index') }}" class="{{ request()->routeIs('months.*') ? 'active' : '' }}"><span class="menu-label">Months</span></a></li>
                            <li><a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}"><span class="menu-label">Users</span></a></li>
                            <li><a href="{{ route('clients.info') }}" class="{{ request()->routeIs('clients.*') ? 'active' : '' }}"><span class="menu-label">Clients</span></a></li>
                            <li><a href="{{ route('regions.index') }}" class="{{ request()->routeIs('regions.*') ? 'active' : '' }}"><span class="menu-label">Region</span></a></li>
                            <li><a href="{{ route('candidates.index') }}" class="{{ request()->routeIs('candidates.*') ? 'active' : '' }}"><span class="menu-label">Candidates</span></a></li>
                        </ul>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <a href="{{ route('guide.index') }}"
                   class="guide-footer-link {{ request()->routeIs('guide.*') ? 'active' : '' }}"
                   data-tooltip="User Guide">
                    <span class="menu-icon"><svg viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></span>
                    <span class="menu-label">User Guide</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <a href="#" class="logout-link" data-tooltip="Logout" onclick="event.preventDefault(); this.closest('form').submit();">
                        <span class="menu-icon"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></span>
                        <span class="menu-label">Logout</span>
                    </a>
                </form>
            </div>
        </aside>

        <div class="main-content has-topbar">
            <header class="app-topbar">
                <div class="topbar-left">
                    @php
                        $defaultPageHeading = match (true) {
                            request()->routeIs('dashboard.*') => 'Analytics Dashboard',
                            request()->routeIs('tracker.*') => 'Recruiterment Workspace',
                            request()->routeIs('resume.*') => 'Resume Analysis',
                            request()->routeIs('candidates.search.*') => 'Find Candidates',
                            request()->routeIs('guide.*') => 'User Guide',
                            request()->routeIs('months.*', 'clients.*', 'regions.*', 'candidates.*') => 'Register',
                            request()->routeIs('users.*') => 'User Management',
                            default => 'Recruiterment Workspace',
                        };
                    @endphp
                    <h1 class="topbar-heading">@yield('page_heading', $defaultPageHeading)</h1>
                </div>
                <div class="topbar-right">
                    <div class="topbar-datetime">
                        <div class="world-clock" id="worldClock">
                            <button type="button" class="world-clock-trigger" id="worldClockTrigger" aria-expanded="false" aria-controls="worldClockPanel" aria-label="Show world clocks">
                                <svg class="world-clock-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <span class="world-clock-preview" id="worldClockPreview">
                                    <span class="world-clock-slide active" data-zone="ist"><span class="tz-badge">IST</span><span class="slide-time">--:--</span></span>
                                    <span class="world-clock-slide" data-zone="cdt"><span class="tz-badge">CDT</span><span class="slide-time">--:--</span></span>
                                    <span class="world-clock-slide" data-zone="est"><span class="tz-badge">EST</span><span class="slide-time">--:--</span></span>
                                </span>
                                <svg class="world-clock-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <div class="world-clock-panel" id="worldClockPanel" role="region" aria-label="World clocks">
                                <div class="world-clock-row is-primary" data-zone="ist">
                                    <div class="world-clock-row__zone">
                                        <span class="world-clock-row__label">India · IST</span>
                                        <span class="world-clock-row__date row-date">---</span>
                                    </div>
                                    <span class="world-clock-row__time row-time">--:--</span>
                                </div>
                                <div class="world-clock-row" data-zone="cdt">
                                    <div class="world-clock-row__zone">
                                        <span class="world-clock-row__label">Chicago · CDT</span>
                                        <span class="world-clock-row__date row-date">---</span>
                                    </div>
                                    <span class="world-clock-row__time row-time">--:--</span>
                                </div>
                                <div class="world-clock-row" data-zone="est">
                                    <div class="world-clock-row__zone">
                                        <span class="world-clock-row__label">New York · EST</span>
                                        <span class="world-clock-row__date row-date">---</span>
                                    </div>
                                    <span class="world-clock-row__time row-time">--:--</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="cal-trigger" onclick="openHolidayCalendar()" title="Holiday calendar" aria-label="Open holiday calendar">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </button>
                    @auth
                    <div class="topbar-user">
                        <div class="topbar-avatar">{{ strtoupper(substr(Auth::user()->username ?? 'U', 0, 1)) }}</div>
                        <div class="topbar-username">{{ Auth::user()->username ?? 'User' }}</div>
                    </div>
                    @endauth
                </div>
            </header>

            <div class="page-body">
                @php $suppressLayoutFlash = request()->routeIs('tracker.info', 'users.*', 'dashboard.*'); @endphp
                @if(session('success') && !$suppressLayoutFlash)
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error') && !$suppressLayoutFlash)
                    <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    @auth
        @include('partials._holiday_calendar')
    @endauth

    <script>
        function toggleDropdown(btn) {
            const submenu = btn.nextElementSibling;
            submenu.classList.toggle('active');
            btn.classList.toggle('active');
        }

        const WORLD_CLOCK_ZONES = {
            ist: { timeZone: 'Asia/Kolkata', label: 'IST' },
            cdt: { timeZone: 'America/Chicago', label: 'CDT' },
            est: { timeZone: 'America/New_York', label: 'EST' },
        };

        let worldClockSlideIndex = 0;
        let worldClockSlideTimer = null;
        let worldClockPanelAnchor = null;

        function positionWorldClockPanel() {
            const trigger = document.getElementById('worldClockTrigger');
            const panel = document.getElementById('worldClockPanel');
            const worldClock = document.getElementById('worldClock');
            if (!trigger || !panel || !worldClock?.classList.contains('open')) {
                return;
            }

            const rect = trigger.getBoundingClientRect();
            const panelWidth = Math.min(220, window.innerWidth - 24);
            const left = Math.min(
                Math.max(12, rect.right - panelWidth),
                window.innerWidth - panelWidth - 12
            );

            panel.style.top = `${rect.bottom + 10}px`;
            panel.style.left = `${left}px`;
            panel.style.width = `${panelWidth}px`;
            panel.style.right = 'auto';
        }

        function isWorldClockTarget(target) {
            const worldClock = document.getElementById('worldClock');
            const panel = document.getElementById('worldClockPanel');
            return (worldClock && worldClock.contains(target)) || (panel && panel.contains(target));
        }

        function tzTime(now, timeZone) {
            return now.toLocaleTimeString('en-US', {
                timeZone,
                hour: '2-digit',
                minute: '2-digit',
                hour12: true,
            });
        }

        function tzDate(now, timeZone) {
            return now.toLocaleDateString('en-US', {
                timeZone,
                weekday: 'short',
                month: 'short',
                day: 'numeric',
            });
        }

        function updateWorldClockSlides() {
            const slides = document.querySelectorAll('.world-clock-slide');
            if (!slides.length) return;

            slides.forEach((slide, index) => {
                slide.classList.toggle('active', index === worldClockSlideIndex);
            });

            worldClockSlideIndex = (worldClockSlideIndex + 1) % slides.length;
        }

        function startWorldClockCarousel() {
            const worldClock = document.getElementById('worldClock');
            if (!worldClock || worldClock.classList.contains('open')) return;
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            if (worldClockSlideTimer) clearInterval(worldClockSlideTimer);
            worldClockSlideTimer = setInterval(updateWorldClockSlides, 3500);
        }

        function stopWorldClockCarousel() {
            if (worldClockSlideTimer) {
                clearInterval(worldClockSlideTimer);
                worldClockSlideTimer = null;
            }
        }

        function setWorldClockOpen(isOpen) {
            const worldClock = document.getElementById('worldClock');
            const trigger = document.getElementById('worldClockTrigger');
            const panel = document.getElementById('worldClockPanel');
            if (!worldClock || !trigger || !panel) return;

            worldClock.classList.toggle('open', isOpen);
            trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

            if (isOpen) {
                stopWorldClockCarousel();

                if (!worldClockPanelAnchor) {
                    worldClockPanelAnchor = panel.parentElement;
                }

                if (panel.parentElement !== document.body) {
                    document.body.appendChild(panel);
                }

                panel.classList.add('is-portaled', 'open');
                positionWorldClockPanel();
            } else {
                panel.classList.remove('open', 'is-portaled');
                panel.style.top = '';
                panel.style.left = '';
                panel.style.width = '';
                panel.style.right = '';

                if (worldClockPanelAnchor && panel.parentElement === document.body) {
                    worldClockPanelAnchor.appendChild(panel);
                }

                startWorldClockCarousel();
            }
        }

        function updateTopbarClock() {
            const now = new Date();

            Object.keys(WORLD_CLOCK_ZONES).forEach((key) => {
                const config = WORLD_CLOCK_ZONES[key];
                const row = document.querySelector(`.world-clock-row[data-zone="${key}"]`);
                const slide = document.querySelector(`.world-clock-slide[data-zone="${key}"]`);

                const time = tzTime(now, config.timeZone);
                const date = tzDate(now, config.timeZone);

                if (row) {
                    const timeEl = row.querySelector('.row-time');
                    const dateEl = row.querySelector('.row-date');
                    if (timeEl) timeEl.textContent = time;
                    if (dateEl) dateEl.textContent = date;
                }

                if (slide) {
                    const slideTime = slide.querySelector('.slide-time');
                    if (slideTime) slideTime.textContent = time;
                }
            });
        }

        function initWorldClock() {
            const worldClock = document.getElementById('worldClock');
            const trigger = document.getElementById('worldClockTrigger');
            if (!worldClock || !trigger) return;

            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                setWorldClockOpen(!worldClock.classList.contains('open'));
            });

            document.addEventListener('click', (e) => {
                if (!isWorldClockTarget(e.target)) {
                    setWorldClockOpen(false);
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    setWorldClockOpen(false);
                }
            });

            window.addEventListener('scroll', () => {
                positionWorldClockPanel();
            }, true);

            window.addEventListener('resize', () => {
                positionWorldClockPanel();
            });

            startWorldClockCarousel();
        }

        window.addEventListener('DOMContentLoaded', function() {
            updateTopbarClock();
            initWorldClock();
            setInterval(updateTopbarClock, 1000);
        });
    </script>
    @stack('scripts')
</body>
</html>
