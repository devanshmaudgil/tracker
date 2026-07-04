<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome — RADiiX INFINITEii</title>
    <link rel="icon" type="image/webp" href="{{ asset('favicon.webp') }}">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --teal-deep: #0a2d29;
            --teal: #0f3d37;
            --gold: #f1cd86;
            --gold-dim: #e2b968;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at 15% 20%, rgba(241,205,134,.1), transparent 45%),
                linear-gradient(160deg, var(--teal-deep) 0%, var(--teal) 55%, #14554d 100%);
            color: #fff;
            padding: 24px;
            overflow: hidden;
        }
        .welcome {
            width: 100%;
            max-width: 680px;
            text-align: center;
            position: relative;
        }
        .welcome__glow {
            position: absolute;
            width: 360px; height: 360px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(241,205,134,.18), transparent 65%);
            top: -120px; right: -80px;
            pointer-events: none;
            animation: float 7s ease-in-out infinite;
        }
        @keyframes float {
            0%,100% { transform: translateY(0); }
            50% { transform: translateY(18px); }
        }
        .welcome__logo {
            height: 56px;
            margin-bottom: 28px;
            filter: drop-shadow(0 6px 16px rgba(0,0,0,.3));
            opacity: 0;
            animation: fadeUp .9s ease .1s forwards;
        }
        .welcome__eyebrow {
            font-size: 11px;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: var(--gold);
            font-weight: 700;
            margin-bottom: 20px;
            opacity: 0;
            animation: fadeUp .9s ease .25s forwards;
        }
        .welcome__quote {
            font-size: clamp(22px, 4vw, 30px);
            font-weight: 500;
            line-height: 1.55;
            color: #fff;
            margin-bottom: 22px;
            opacity: 0;
            animation: fadeUp 1s ease .45s forwards;
        }
        .welcome__quote::before { content: '\201C'; color: var(--gold); }
        .welcome__quote::after { content: '\201D'; color: var(--gold); }
        .welcome__author {
            font-size: 15px;
            color: rgba(255,255,255,.65);
            margin-bottom: 40px;
            opacity: 0;
            animation: fadeUp .9s ease .65s forwards;
        }
        .welcome__author strong { color: var(--gold); font-weight: 600; }
        .welcome__cta {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 30px;
            border: none;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--gold), var(--gold-dim));
            color: var(--teal-deep);
            font-size: 15px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 12px 32px rgba(241,205,134,.35);
            opacity: 0;
            animation: fadeUp .9s ease .85s forwards;
            transition: transform .18s, box-shadow .2s;
        }
        .welcome__cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 40px rgba(241,205,134,.5);
        }
        .welcome__cta svg { width: 18px; height: 18px; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (prefers-reduced-motion: reduce) {
            .welcome__logo, .welcome__eyebrow, .welcome__quote, .welcome__author, .welcome__cta {
                animation: none; opacity: 1;
            }
            .welcome__glow { animation: none; }
        }
    </style>
</head>
<body>
    <div class="welcome">
        <div class="welcome__glow" aria-hidden="true"></div>
        <img src="{{ asset('logo.png') }}" alt="RADiiX INFINITEii" class="welcome__logo">
        <div class="welcome__eyebrow">Thought for today</div>
        <p class="welcome__quote">{{ $quote['text'] }}</p>
        <p class="welcome__author">— <strong>{{ $quote['author'] }}</strong></p>
        <form method="POST" action="{{ route('welcome.continue') }}">
            @csrf
            <button type="submit" class="welcome__cta">
                Enter Recruitment Workspace
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>
        </form>
    </div>
</body>
</html>
