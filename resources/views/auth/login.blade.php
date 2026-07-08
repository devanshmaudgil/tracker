<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RADiiX INFINITEii — Sign In</title>
    <link rel="icon" type="image/webp" href="{{ asset('favicon.webp') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --teal-deep: #0a2d29;
            --teal-matte: #0f3d37;
            --teal-glow: #1a5c52;
            --gold: #f1cd86;
            --gold-bright: #ffe4a8;
            --gold-dim: #c9a85c;
            --white: #ffffff;
            --white-soft: rgba(255, 255, 255, 0.92);
            --glass: rgba(10, 45, 41, 0.55);
            --glass-border: rgba(241, 205, 134, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--teal-deep);
            color: var(--white);
        }

        /* ── Neural canvas background ── */
        #neural-canvas {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .bg-gradient {
            position: fixed;
            inset: 0;
            z-index: 2;
            background:
                radial-gradient(ellipse 80% 60% at 20% 80%, rgba(26, 92, 82, 0.45) 0%, transparent 60%),
                radial-gradient(ellipse 70% 50% at 80% 20%, rgba(241, 205, 134, 0.08) 0%, transparent 55%),
                radial-gradient(ellipse 100% 100% at 50% 50%, rgba(15, 61, 55, 0.6) 0%, var(--teal-deep) 100%);
            pointer-events: none;
        }

        .scan-line {
            position: fixed;
            inset: 0;
            z-index: 3;
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 2px,
                rgba(241, 205, 134, 0.015) 2px,
                rgba(241, 205, 134, 0.015) 4px
            );
            pointer-events: none;
            animation: scanDrift 8s linear infinite;
        }

        @keyframes scanDrift {
            0% { transform: translateY(0); }
            100% { transform: translateY(4px); }
        }

        /* ── Split-screen curtain reveal ── */
        .curtain {
            position: fixed;
            top: 0;
            width: 50%;
            height: 100%;
            z-index: 100;
            background: var(--teal-deep);
            overflow: hidden;
            transition: transform 2.6s cubic-bezier(0.65, 0, 0.15, 1);
        }

        .curtain::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(135deg, transparent 40%, rgba(241, 205, 134, 0.06) 50%, transparent 60%),
                repeating-linear-gradient(
                    90deg,
                    transparent,
                    transparent 40px,
                    rgba(241, 205, 134, 0.04) 40px,
                    rgba(241, 205, 134, 0.04) 41px
                );
        }

        .curtain::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 2px;
            height: 60%;
            transform: translateY(-50%);
            background: linear-gradient(180deg, transparent, var(--gold), transparent);
            box-shadow: 0 0 20px var(--gold), 0 0 40px rgba(241, 205, 134, 0.4);
        }

        .curtain-left {
            left: 0;
            transform: translateX(0);
        }

        .curtain-left::after {
            right: 0;
        }

        .curtain-right {
            right: 0;
            transform: translateX(0);
        }

        .curtain-right::after {
            left: 0;
        }

        .curtain-left.open {
            transform: translateX(-100%);
        }

        .curtain-right.open {
            transform: translateX(100%);
        }

        .curtain-brand {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 102;
            text-align: center;
            pointer-events: none;
            white-space: nowrap;
            opacity: 1;
            transition: opacity 1s ease 0.4s, transform 1.2s ease 0.4s;
        }

        .curtain-brand.hide {
            opacity: 0;
            transform: translate(-50%, -50%) scale(0.92);
        }

        .curtain-brand-title {
            font-size: clamp(36px, 7vw, 64px);
            font-weight: 700;
            letter-spacing: 0.06em;
            line-height: 1.1;
            color: var(--white);
            text-shadow: 0 0 40px rgba(241, 205, 134, 0.2);
        }

        .curtain-brand-title .gold {
            color: var(--gold);
        }

        .curtain-brand-tagline {
            margin-top: 14px;
            font-size: clamp(11px, 1.8vw, 14px);
            font-weight: 500;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: var(--gold);
            opacity: 0.85;
        }

        .center-logo {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.85);
            z-index: 101;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.8s ease, transform 0.8s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .center-logo.show {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }

        .center-logo.fade-out {
            opacity: 0;
            transform: translate(-50%, -50%) scale(1.08);
            transition: opacity 1.4s ease, transform 1.4s ease;
        }

        .center-logo img {
            height: clamp(100px, 18vw, 160px);
            width: auto;
            filter: drop-shadow(0 8px 40px rgba(241, 205, 134, 0.45));
        }

        .center-logo-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 220px;
            height: 220px;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            background: radial-gradient(circle, rgba(241, 205, 134, 0.18) 0%, transparent 70%);
            z-index: -1;
            animation: logoGlowPulse 2.5s ease-in-out infinite;
        }

        @keyframes logoGlowPulse {
            0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.6; }
            50% { transform: translate(-50%, -50%) scale(1.15); opacity: 1; }
        }

        /* ── Main scene ── */
        .login-scene {
            position: fixed;
            inset: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            opacity: 0;
            transform: scale(0.96);
            transition: opacity 1s ease 1.4s, transform 1s cubic-bezier(0.22, 1, 0.36, 1) 1.4s;
        }

        .login-scene.visible {
            opacity: 1;
            transform: scale(1);
        }

        .login-wrapper {
            display: flex;
            width: 100%;
            max-width: 960px;
            min-height: 520px;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid var(--glass-border);
            box-shadow:
                0 0 0 1px rgba(255, 255, 255, 0.04),
                0 25px 80px rgba(0, 0, 0, 0.45),
                0 0 60px rgba(241, 205, 134, 0.06);
            backdrop-filter: blur(2px);
        }

        /* Left brand panel */
        .brand-panel {
            flex: 1;
            background: linear-gradient(160deg, rgba(15, 61, 55, 0.9) 0%, rgba(10, 45, 41, 0.95) 100%);
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .brand-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(241, 205, 134, 0.12) 0%, transparent 70%);
            animation: brandGlow 4s ease-in-out infinite alternate;
        }

        @keyframes brandGlow {
            0% { transform: scale(1) translate(0, 0); opacity: 0.6; }
            100% { transform: scale(1.2) translate(-20px, 20px); opacity: 1; }
        }

        .brand-panel .hex-grid {
            position: absolute;
            inset: 0;
            opacity: 0.06;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='28' height='49' viewBox='0 0 28 49'%3E%3Cg fill-rule='evenodd'%3E%3Cg fill='%23f1cd86' fill-opacity='1'%3E%3Cpath d='M13.99 9.25l13 7.5v15l-13 7.5-13-7.5v-15l13-7.5zM0 16.25v15l13 7.5 13-7.5v-15L13 8.75 0 16.25zm13 7.5L26 16.25v15l-13 7.5L0 31.25v-15l13-7.5z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .brand-content {
            position: relative;
            z-index: 1;
        }

        .brand-logo {
            height: 72px;
            width: auto;
            margin-bottom: 28px;
            filter: drop-shadow(0 4px 20px rgba(241, 205, 134, 0.25));
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        .brand-title {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: var(--white);
            margin-bottom: 8px;
            line-height: 1.2;
            position: relative;
            display: block;
            overflow: hidden;
            width: fit-content;
            max-width: 100%;
        }

        .brand-title span {
            color: var(--gold);
            text-shadow: 0 0 14px rgba(241, 205, 134, 0.35);
        }

        .brand-title::after {
            content: '';
            position: absolute;
            top: -10%;
            left: 0;
            width: 45%;
            height: 120%;
            background: linear-gradient(
                90deg,
                transparent 0%,
                rgba(241, 205, 134, 0.15) 25%,
                rgba(255, 228, 168, 0.65) 50%,
                rgba(241, 205, 134, 0.15) 75%,
                transparent 100%
            );
            transform: translateX(-130%);
            animation: brandTitleShine 3.8s ease-in-out infinite;
            pointer-events: none;
            mix-blend-mode: screen;
        }

        @keyframes brandTitleShine {
            0% { transform: translateX(-130%); opacity: 0; }
            8% { opacity: 1; }
            92% { opacity: 1; }
            100% { transform: translateX(280%); opacity: 0; }
        }

        .brand-tagline {
            font-size: 14px;
            font-weight: 500;
            color: var(--gold);
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 32px;
            opacity: 0.88;
            line-height: 1.55;
            max-width: 260px;
            white-space: normal;
        }

        .brand-desc {
            font-size: 15px;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.65);
            max-width: 280px;
        }

        .brand-desc-gold {
            color: var(--gold);
        }

        .brand-stats {
            display: flex;
            gap: 28px;
            margin-top: 40px;
            padding-top: 28px;
            border-top: 1px solid rgba(241, 205, 134, 0.15);
        }

        .stat-item {
            opacity: 0;
            animation: statFadeIn 0.6s ease forwards;
        }

        .stat-item:nth-child(1) { animation-delay: 1.75s; }
        .stat-item:nth-child(2) { animation-delay: 1.9s; }
        .stat-item:nth-child(3) { animation-delay: 2.05s; }

        @keyframes statFadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stat-value {
            font-size: 22px;
            font-weight: 700;
            color: var(--gold);
        }

        .stat-label {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 2px;
        }

        /* Right form panel */
        .form-panel {
            flex: 1;
            background: rgba(255, 255, 255, 0.97);
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            margin-bottom: 32px;
            opacity: 0;
            animation: formSlideUp 0.7s ease 1.65s forwards;
        }

        @keyframes formSlideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-header h2 {
            font-size: 26px;
            font-weight: 700;
            color: var(--teal-deep);
            margin-bottom: 6px;
        }

        .form-header p {
            font-size: 14px;
            color: #6b7c7a;
        }

        .form-field {
            margin-bottom: 22px;
            opacity: 0;
            animation: formSlideUp 0.6s ease forwards;
        }

        .form-field:nth-child(1) { animation-delay: 1.8s; }
        .form-field:nth-child(2) { animation-delay: 1.95s; }

        .form-field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--teal-deep);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: var(--teal-glow);
            opacity: 0.5;
            transition: color 0.3s, opacity 0.3s;
            pointer-events: none;
        }

        .input-wrap input {
            width: 100%;
            padding: 14px 16px 14px 44px;
            border: 2px solid #e8eeed;
            border-radius: 10px;
            font-size: 15px;
            font-family: inherit;
            color: var(--teal-deep);
            background: #fafcfb;
            transition: border-color 0.3s, box-shadow 0.3s, background 0.3s;
        }

        .input-wrap.has-toggle input {
            padding-right: 48px;
        }

        .input-wrap input::placeholder {
            color: #a8b8b5;
        }

        .input-wrap input,
        .input-wrap input:focus,
        .input-wrap input:focus-visible {
            outline: none !important;
            outline-offset: 0;
            -webkit-tap-highlight-color: transparent;
        }

        /* Suppress browser invalid (black) border on empty required fields */
        .input-wrap input:invalid,
        .input-wrap input:required:invalid {
            border-color: #e8eeed;
            box-shadow: none;
        }

        .input-wrap input:invalid:placeholder-shown:focus,
        .input-wrap input:required:invalid:placeholder-shown:focus {
            border-color: #e8eeed;
            box-shadow: none;
        }

        .input-wrap input:focus {
            outline: none;
            border-color: #e8eeed;
            background: #fafcfb;
            box-shadow: none;
        }

        .input-wrap:focus-within .input-icon {
            color: var(--gold-dim);
            opacity: 1;
        }

        .input-underline {
            position: absolute;
            bottom: -2px;
            left: 50%;
            width: 0;
            height: 5px;
            transform: translateX(-50%);
            background: linear-gradient(
                90deg,
                transparent 0%,
                var(--gold-dim) 18%,
                var(--gold-bright) 38%,
                var(--gold) 50%,
                var(--gold-bright) 62%,
                var(--gold-dim) 82%,
                transparent 100%
            );
            background-size: 200% 100%;
            -webkit-mask-image: linear-gradient(90deg, transparent 0%, #000 6%, #000 94%, transparent 100%);
            mask-image: linear-gradient(90deg, transparent 0%, #000 6%, #000 94%, transparent 100%);
            transition: width 1.4s cubic-bezier(0.22, 1, 0.36, 1);
            border-radius: 0;
            filter: drop-shadow(0 0 8px rgba(241, 205, 134, 0.4));
        }

        .input-wrap:focus-within .input-underline {
            width: 95%;
            animation: underlineShimmer 2.5s ease-in-out infinite;
        }

        @keyframes underlineShimmer {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 6px;
            cursor: pointer;
            color: var(--teal-glow);
            opacity: 0.45;
            transition: color 0.3s, opacity 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-password:hover {
            opacity: 0.85;
            color: var(--gold-dim);
        }

        .toggle-password svg {
            width: 20px;
            height: 20px;
            position: static;
            transform: none;
            opacity: 1;
        }

        .toggle-password .icon-eye-off {
            display: none;
        }

        .toggle-password.visible .icon-eye {
            display: none;
        }

        .toggle-password.visible .icon-eye-off {
            display: block;
        }

        .remember-row {
            display: flex;
            align-items: center;
            margin-bottom: 28px;
            opacity: 0;
            animation: formSlideUp 0.6s ease 2.1s forwards;
        }

        .remember-row input[type="checkbox"] {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #d0dbd9;
            border-radius: 5px;
            cursor: pointer;
            position: relative;
            transition: border-color 0.2s, background 0.2s;
            flex-shrink: 0;
        }

        .remember-row input[type="checkbox"]:checked {
            background: var(--teal-deep);
            border-color: var(--teal-deep);
        }

        .remember-row input[type="checkbox"]:checked::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 2px;
            width: 6px;
            height: 10px;
            border: solid var(--gold);
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .remember-row label {
            margin-left: 10px;
            font-size: 14px;
            color: #4a5c59;
            cursor: pointer;
            user-select: none;
        }

        .btn-login {
            width: 100%;
            padding: 15px 24px;
            background: linear-gradient(135deg, var(--teal-deep) 0%, var(--teal-matte) 100%);
            color: var(--gold);
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            font-family: inherit;
            letter-spacing: 0.06em;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.3s;
            opacity: 0;
            animation: formSlideUp 0.6s ease 2.25s forwards;
        }

        .btn-login::after {
            content: '';
            position: absolute;
            inset: 0;
            width: 0;
            background: linear-gradient(90deg, rgba(241, 205, 134, 0.15) 0%, rgba(241, 205, 134, 0.34) 50%, rgba(241, 205, 134, 0.15) 100%);
            transition: width 1.2s cubic-bezier(0.22, 1, 0.36, 1);
            pointer-events: none;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(241, 205, 134, 0.25), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(10, 45, 41, 0.35), 0 0 20px rgba(241, 205, 134, 0.15);
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login .btn-text {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login svg {
            width: 18px;
            height: 18px;
            transition: transform 0.3s;
        }

        .btn-login:hover svg {
            transform: translateX(4px);
        }

        .btn-login .btn-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.22s ease, transform 0.22s ease;
        }

        .btn-login .btn-loading {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%) translateY(8px);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease, transform 0.3s ease;
            white-space: nowrap;
        }

        .btn-spinner {
            width: 17px;
            height: 17px;
            border-radius: 50%;
            border: 2px solid rgba(241, 205, 134, 0.22);
            border-top-color: var(--gold-bright);
            border-right-color: var(--gold);
            animation: btnSpin 0.7s linear infinite;
            flex-shrink: 0;
        }

        .btn-loading-text {
            font-size: 14px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #fff3d6;
        }

        .btn-login.is-loading {
            cursor: wait;
            transform: none;
            box-shadow: 0 8px 28px rgba(10, 45, 41, 0.35), 0 0 24px rgba(241, 205, 134, 0.22);
        }

        .btn-login.is-loading::after {
            width: 100%;
        }

        .btn-login.is-loading .btn-label {
            opacity: 0;
            transform: translateY(-8px);
        }

        .btn-login.is-loading .btn-loading {
            opacity: 1;
            transform: translate(-50%, -50%) translateY(0);
        }

        .btn-login.is-loading::before {
            animation: btnSweep 1.1s linear infinite;
        }

        @keyframes btnSpin {
            to { transform: rotate(360deg); }
        }

        @keyframes btnSweep {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .error-message {
            background: linear-gradient(135deg, #fff5f5 0%, #ffe8e8 100%);
            border: 1px solid #f5c6c6;
            color: #b33;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: shake 0.4s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            75% { transform: translateX(6px); }
        }

        .error-list {
            list-style: none;
        }

        .error-list li::before {
            content: '⚠ ';
        }

        .corner-accent {
            position: fixed;
            width: 120px;
            height: 120px;
            z-index: 5;
            pointer-events: none;
            opacity: 0;
            transition: opacity 1s ease 1.55s;
        }

        .login-scene.visible ~ .corner-accent,
        .corner-accent.visible {
            opacity: 1;
        }

        .corner-tl {
            top: 0;
            left: 0;
            border-top: 2px solid rgba(241, 205, 134, 0.3);
            border-left: 2px solid rgba(241, 205, 134, 0.3);
        }

        .corner-br {
            bottom: 0;
            right: 0;
            border-bottom: 2px solid rgba(241, 205, 134, 0.3);
            border-right: 2px solid rgba(241, 205, 134, 0.3);
        }

        @media (max-width: 768px) {
            body { overflow-y: auto; }
            html, body { overflow: auto; min-height: 100%; }

            .login-scene {
                position: relative;
                min-height: 100vh;
                padding: 16px;
            }

            .login-wrapper {
                flex-direction: column;
                min-height: auto;
            }

            .brand-panel {
                padding: 32px 28px;
            }

            .brand-stats {
                gap: 20px;
            }

            .form-panel {
                padding: 32px 28px;
            }

            .curtain-brand-title { font-size: 32px; }
            .curtain-brand-tagline { letter-spacing: 0.15em; }
        }

        @media (prefers-reduced-motion: reduce) {
            .curtain { transition: none; }
            .login-scene { transition: none; opacity: 1; transform: none; }
            .center-logo { display: none; }
            .curtain-brand { display: none; }
            .form-header, .form-field, .remember-row, .btn-login, .stat-item {
                animation: none;
                opacity: 1;
            }
            .brand-logo { animation: none; }
            .brand-title::after { animation: none; display: none; }
            .scan-line { animation: none; }
            .btn-spinner { animation: none; }
            .btn-login.is-loading::before { animation: none; }
        }
    </style>
</head>
<body>
    <!-- Split-screen curtains -->
    <div class="curtain curtain-left" id="curtain-left"></div>
    <div class="curtain curtain-right" id="curtain-right"></div>

    <div class="curtain-brand" id="curtain-brand">
        <div class="curtain-brand-title">RADiiX <span class="gold">INFINITEii</span></div>
        <div class="curtain-brand-tagline">Rooting Intelligence Inspiring Innovation</div>
    </div>

    <div class="center-logo" id="center-logo">
        <div class="center-logo-glow"></div>
        <img src="{{ asset('logo.png') }}" alt="RADiiX INFINITEii">
    </div>

    <!-- Animated neural background -->
    <canvas id="neural-canvas"></canvas>
    <div class="bg-gradient"></div>
    <div class="scan-line"></div>

    <div class="corner-accent corner-tl" id="corner-tl"></div>
    <div class="corner-accent corner-br" id="corner-br"></div>

    <!-- Login scene -->
    <div class="login-scene" id="login-scene">
        <div class="login-wrapper">
            <aside class="brand-panel">
                <div class="hex-grid"></div>
                <div class="brand-content">
                    <img src="{{ asset('logo.png') }}" alt="RADiiX INFINITEii" class="brand-logo">
                    <h1 class="brand-title">RADiiX <span>INFINITEii</span></h1>
                    <p class="brand-tagline">Passion. Purpose. Pride.</p>
                    <p class="brand-desc">Your <span class="brand-desc-gold">Intelligent Recruitment Command Center</span> - Track operations in real time.</p>
                    <div class="brand-stats">
                        <div class="stat-item">
                            <div class="stat-value">AI</div>
                            <div class="stat-label">Powered</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">360°</div>
                            <div class="stat-label">Tracking</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">∞</div>
                            <div class="stat-label">Insights</div>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="form-panel">
                <div class="form-header">
                    <h2>Welcome!</h2>
                    <p>Sign in to access your recruiterment workspace</p>
                </div>

                @if ($errors->any())
                    <div class="error-message">
                        <ul class="error-list">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="login-form">
                    @csrf
                    <div class="form-field">
                        <label for="username">Username</label>
                        <div class="input-wrap">
                            <input type="text" id="username" name="username" value="{{ old('username') }}" placeholder="Enter your username" required>
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span class="input-underline"></span>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="password">Password</label>
                        <div class="input-wrap has-toggle">
                            <input type="password" id="password" name="password" placeholder="Enter your password" required>
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <button type="button" class="toggle-password" id="toggle-password" aria-label="Show password">
                                <svg class="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="icon-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                            <span class="input-underline"></span>
                        </div>
                    </div>

                    <div class="remember-row">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me on this device</label>
                    </div>

                    <button type="submit" class="btn-login">
                        <span class="btn-text">
                            <span class="btn-label">
                                Sign In
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </span>
                            <span class="btn-loading" aria-hidden="true">
                                <span class="btn-spinner"></span>
                                <span class="btn-loading-text">Signing In</span>
                            </span>
                        </span>
                    </button>
                </form>
            </main>
        </div>
    </div>

    <script>
        (function () {
            const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            /* ── Curtain split reveal ── */
            const curtainLeft = document.getElementById('curtain-left');
            const curtainRight = document.getElementById('curtain-right');
            const curtainBrand = document.getElementById('curtain-brand');
            const centerLogo = document.getElementById('center-logo');
            const loginScene = document.getElementById('login-scene');
            const corners = [document.getElementById('corner-tl'), document.getElementById('corner-br')];

            const CURTAIN_DELAY = 700;
            const CURTAIN_DURATION = 2600;
            const LOGO_FADE_DELAY = CURTAIN_DELAY + 700;
            const FORM_APPEAR_DELAY = CURTAIN_DELAY + 800;

            function playIntro() {
                if (prefersReduced) {
                    curtainLeft.style.display = 'none';
                    curtainRight.style.display = 'none';
                    curtainBrand.style.display = 'none';
                    centerLogo.style.display = 'none';
                    loginScene.classList.add('visible');
                    corners.forEach(c => c.classList.add('visible'));
                    return;
                }

                setTimeout(() => {
                    curtainLeft.classList.add('open');
                    curtainRight.classList.add('open');
                    curtainBrand.classList.add('hide');
                    centerLogo.classList.add('show');
                }, CURTAIN_DELAY);

                setTimeout(() => {
                    centerLogo.classList.add('fade-out');
                }, LOGO_FADE_DELAY);

                setTimeout(() => {
                    loginScene.classList.add('visible');
                    corners.forEach(c => c.classList.add('visible'));
                    document.activeElement?.blur();
                }, FORM_APPEAR_DELAY);

                setTimeout(() => {
                    curtainLeft.style.visibility = 'hidden';
                    curtainRight.style.visibility = 'hidden';
                    centerLogo.style.visibility = 'hidden';
                    curtainBrand.style.visibility = 'hidden';
                }, CURTAIN_DELAY + CURTAIN_DURATION + 600);
            }

            playIntro();

            /* Prevent any field from being auto-focused on load */
            if (document.activeElement && document.activeElement.matches('input, textarea, select')) {
                document.activeElement.blur();
            }
            window.addEventListener('load', () => {
                if (document.activeElement && document.activeElement.matches('input, textarea, select')) {
                    document.activeElement.blur();
                }
            });

            /* ── Password visibility toggle ── */
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('toggle-password');

            togglePassword.addEventListener('click', function () {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                this.classList.toggle('visible', isPassword);
                this.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            });

            /* ── Neural network canvas ── */
            const canvas = document.getElementById('neural-canvas');
            const ctx = canvas.getContext('2d');
            let nodes = [];
            let animId;
            const NODE_COUNT = prefersReduced ? 20 : 55;
            const CONNECT_DIST = 160;
            const GOLD = '241, 205, 134';
            const TEAL = '26, 92, 82';

            function resize() {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
                initNodes();
            }

            function initNodes() {
                nodes = [];
                for (let i = 0; i < NODE_COUNT; i++) {
                    nodes.push({
                        x: Math.random() * canvas.width,
                        y: Math.random() * canvas.height,
                        vx: (Math.random() - 0.5) * 0.4,
                        vy: (Math.random() - 0.5) * 0.4,
                        r: Math.random() * 2 + 1.5,
                        pulse: Math.random() * Math.PI * 2,
                        hue: Math.random() > 0.3 ? GOLD : TEAL,
                    });
                }
            }

            function drawNeural() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                nodes.forEach(n => {
                    n.x += n.vx;
                    n.y += n.vy;
                    n.pulse += 0.02;

                    if (n.x < 0 || n.x > canvas.width) n.vx *= -1;
                    if (n.y < 0 || n.y > canvas.height) n.vy *= -1;

                    const glow = 0.85 + Math.sin(n.pulse) * 0.15;
                    const grd = ctx.createRadialGradient(n.x, n.y, 0, n.x, n.y, n.r * 4);
                    grd.addColorStop(0, `rgba(${n.hue}, 1)`);
                    grd.addColorStop(0.5, `rgba(${n.hue}, ${glow * 0.5})`);
                    grd.addColorStop(1, `rgba(${n.hue}, 0)`);
                    ctx.beginPath();
                    ctx.arc(n.x, n.y, n.r * 4, 0, Math.PI * 2);
                    ctx.fillStyle = grd;
                    ctx.fill();

                    ctx.beginPath();
                    ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
                    ctx.fillStyle = `rgba(${n.hue}, 1)`;
                    ctx.fill();
                });

                for (let i = 0; i < nodes.length; i++) {
                    for (let j = i + 1; j < nodes.length; j++) {
                        const dx = nodes[i].x - nodes[j].x;
                        const dy = nodes[i].y - nodes[j].y;
                        const dist = Math.sqrt(dx * dx + dy * dy);
                        if (dist < CONNECT_DIST) {
                            const alpha = (1 - dist / CONNECT_DIST);
                            ctx.beginPath();
                            ctx.moveTo(nodes[i].x, nodes[i].y);
                            ctx.lineTo(nodes[j].x, nodes[j].y);
                            ctx.strokeStyle = `rgba(${GOLD}, ${alpha})`;
                            ctx.lineWidth = 1;
                            ctx.stroke();

                            if (dist < CONNECT_DIST * 0.4 && Math.random() > 0.992) {
                                const t = Math.random();
                                const px = nodes[i].x + (nodes[j].x - nodes[i].x) * t;
                                const py = nodes[i].y + (nodes[j].y - nodes[i].y) * t;
                                ctx.beginPath();
                                ctx.arc(px, py, 2, 0, Math.PI * 2);
                                ctx.fillStyle = `rgba(${GOLD}, 1)`;
                                ctx.fill();
                            }
                        }
                    }
                }

                animId = requestAnimationFrame(drawNeural);
            }

            resize();
            if (!prefersReduced) drawNeural();
            window.addEventListener('resize', () => {
                cancelAnimationFrame(animId);
                resize();
                if (!prefersReduced) drawNeural();
            });

            /* ── Button loading state on submit ── */
            document.getElementById('login-form').addEventListener('submit', function () {
                const btn = this.querySelector('.btn-login');
                if (btn.classList.contains('is-loading')) return;
                btn.classList.add('is-loading');
                btn.disabled = true;
                btn.setAttribute('aria-busy', 'true');
            });
        })();
    </script>
</body>
</html>
