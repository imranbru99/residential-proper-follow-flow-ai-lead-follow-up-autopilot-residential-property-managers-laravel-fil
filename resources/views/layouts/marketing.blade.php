<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ResidentialProperFollowFlow — AI Lead Follow-Up Autopilot for Residential Property Managers')</title>
    <meta name="description" content="@yield('meta_description', 'ResidentialProperFollowFlow generates consent-aware residential property follow-ups, escalates qualified replies to staff, and operates on properties, residents, maintenance requests and vendor work orders. Laravel Filament SaaS.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Source+Serif+4:opsz,wght@8..60,500;8..60,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #102033;
            --teal: #0284c7;
            --teal-deep: #0369a1;
            --slate: #3d4f63;
            --mist: #e8f4fc;
            --foam: #f4f9fd;
            --line: rgba(16, 32, 51, 0.12);
            --ok: #0f7a4c;
            --warm: #c27803;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Outfit, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(1100px 480px at 8% -8%, rgba(2, 132, 199, 0.18), transparent 55%),
                radial-gradient(900px 420px at 100% 0%, rgba(194, 120, 3, 0.1), transparent 50%),
                linear-gradient(180deg, #f7fbfe 0%, var(--mist) 100%);
            min-height: 100vh;
        }
        a { color: inherit; text-decoration: none; }
        .wrap { width: min(1120px, calc(100% - 2rem)); margin: 0 auto; }
        header {
            position: sticky; top: 0; z-index: 20;
            backdrop-filter: blur(10px);
            background: rgba(247, 251, 254, 0.88);
            border-bottom: 1px solid var(--line);
        }
        .nav { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 0.9rem 0; }
        .brand { font-weight: 800; font-size: 1.15rem; letter-spacing: -0.02em; }
        .brand span { color: var(--teal-deep); }
        .nav-links { display: flex; flex-wrap: wrap; gap: 0.85rem; align-items: center; font-size: 0.92rem; }
        .nav-links a:hover { color: var(--teal-deep); }
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 0.7rem 1.1rem; border-radius: 0.55rem; font-weight: 700;
            border: 1px solid transparent; transition: 0.2s ease;
        }
        .btn-primary { background: var(--ink); color: #fff; }
        .btn-primary:hover { background: var(--slate); }
        .btn-accent { background: var(--teal); color: #fff; }
        .btn-accent:hover { background: var(--teal-deep); }
        .btn-ghost { border-color: var(--line); background: #fff; }
        .hero {
            display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 2rem;
            padding: 3.5rem 0 2.5rem; align-items: center;
        }
        .eyebrow { text-transform: uppercase; letter-spacing: 0.12em; font-size: 0.72rem; font-weight: 700; color: var(--teal-deep); margin: 0 0 0.75rem; }
        h1, h2, h3 { letter-spacing: -0.03em; line-height: 1.1; margin: 0 0 0.85rem; }
        h1 { font-size: clamp(2.2rem, 4vw, 3.4rem); font-family: "Source Serif 4", serif; }
        h2 { font-size: clamp(1.6rem, 3vw, 2.2rem); }
        .lead { font-size: 1.08rem; line-height: 1.65; color: var(--slate); max-width: 38rem; }
        .cta-row { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1.4rem; }
        .panel {
            background: linear-gradient(160deg, #0f1f33, #1a3a5c 70%);
            color: #fff; border-radius: 1.1rem; padding: 1.4rem;
            box-shadow: 0 24px 50px rgba(15, 31, 51, 0.22);
            min-height: 280px;
        }
        .panel small { opacity: 0.7; }
        .panel .score { font-size: 2.6rem; color: #7dd3fc; font-weight: 800; }
        .grid { display: grid; gap: 1rem; }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .card {
            background: rgba(255,255,255,0.88);
            border: 1px solid var(--line);
            border-radius: 0.9rem;
            padding: 1.15rem 1.2rem;
        }
        .card h3 { font-size: 1.05rem; }
        .muted { color: var(--slate); line-height: 1.55; }
        .section { padding: 2.5rem 0; }
        .pill {
            display: inline-block; padding: 0.25rem 0.55rem; border-radius: 999px;
            background: rgba(2,132,199,0.18); color: var(--teal-deep); font-size: 0.75rem; font-weight: 700;
        }
        .pill.off { background: rgba(15,28,46,0.08); color: var(--slate); }
        .flash { background: #e6f7ef; border: 1px solid #9ad7bb; color: var(--ok); padding: 0.85rem 1rem; border-radius: 0.7rem; margin-bottom: 1rem; }
        form label { display: block; font-weight: 600; margin: 0.7rem 0 0.3rem; font-size: 0.9rem; }
        form input, form textarea, form select {
            width: 100%; padding: 0.7rem 0.8rem; border-radius: 0.55rem;
            border: 1px solid var(--line); background: #fff; font: inherit;
        }
        footer { border-top: 1px solid var(--line); padding: 2rem 0 2.5rem; margin-top: 2rem; color: var(--slate); font-size: 0.92rem; }
        @media (max-width: 860px) {
            .hero, .grid-3, .grid-2 { grid-template-columns: 1fr; }
            .nav-links { display: none; }
        }
    </style>
    @yield('head')
</head>
<body>
<header>
    <div class="wrap nav">
        <a class="brand" href="{{ url('/') }}">ResidentialProper<span>Follow</span>Flow</a>
        <nav class="nav-links">
            <a href="{{ route('features') }}">Features</a>
            <a href="{{ route('modules') }}">Modules</a>
            <a href="{{ route('workflow') }}">Workflow</a>
            <a href="{{ route('pricing') }}">Pricing</a>
            <a href="{{ route('demo') }}">Demo</a>
            <a href="{{ route('contact') }}">Contact</a>
            <a class="btn btn-primary" href="{{ url('/admin') }}">Admin Login</a>
        </nav>
    </div>
</header>
<main>
    @yield('content')
</main>
<footer>
    <div class="wrap">
        <strong>ResidentialProperFollowFlow</strong> — AI lead follow-up autopilot for residential property managers.
        Built with Laravel + Filament. Modular SaaS for real estate & property sales operations.
        <div style="margin-top:0.7rem">
            Hire: <a href="https://imrandev.bd/contact">https://imrandev.bd/contact</a> · me@imrandev.bd
        </div>
    </div>
</footer>
</body>
</html>
