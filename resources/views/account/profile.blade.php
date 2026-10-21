<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile – SecureThreatShield</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        :root { --blue:#3D5AF1; --dark:#0F172A; --mid:#1E293B; --border:rgba(255,255,255,.08); --text:#F8FAFC; --muted:#94A3B8; }
        body { margin:0; min-height:100vh; background:var(--dark); color:var(--text); font-family:'Inter',sans-serif; }
        @include('account.navigation-styles')
        main { width:min(720px, calc(100% - 40px)); margin:0 auto; padding:56px 0; }
        h1 { margin:0 0 8px; font-size:28px; }
        .intro { margin:0 0 28px; color:var(--muted); font-size:14px; }
        .card { background:var(--mid); border:1px solid var(--border); border-radius:16px; padding:28px; }
        .summary { display:flex; align-items:center; gap:16px; padding-bottom:24px; border-bottom:1px solid var(--border); }
        .large-avatar { width:64px; height:64px; display:grid; place-items:center; border-radius:50%; background:var(--blue); font-size:24px; font-weight:800; }
        .summary h2 { margin:0 0 5px; font-size:18px; }
        .summary p, .label { margin:0; color:var(--muted); font-size:13px; }
        .details { display:grid; gap:20px; padding-top:24px; }
        .value { display:block; margin-top:6px; font-size:15px; }
        @media (max-width:640px) { main { width:min(100% - 32px, 720px); padding:36px 0; } .card { padding:22px; } }
    </style>
</head>
<body>
    @include('account.navigation', ['activeNav' => 'profile'])
    @include('account.profile-menu')
    <main>
        <h1>Profile details</h1>
        <p class="intro">Manage the account information connected to SecureThreatShield.</p>
        <section class="card">
            <div class="summary">
                <div class="large-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <div><h2>{{ Auth::user()->name }}</h2><p>SecureThreatShield account</p></div>
            </div>
            <div class="details">
                <div><span class="label">Name</span><span class="value">{{ Auth::user()->name }}</span></div>
                <div><span class="label">Email address</span><span class="value">{{ Auth::user()->email }}</span></div>
            </div>
        </section>
    </main>
</body>
</html>
