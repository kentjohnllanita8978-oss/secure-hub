<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Settings – SecureThreatShield</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing:border-box; }
        :root { --blue:#3D5AF1; --dark:#0F172A; --mid:#1E293B; --border:rgba(255,255,255,.08); --text:#F8FAFC; --muted:#94A3B8; }
        body { margin:0; min-height:100vh; background:var(--dark); color:var(--text); font-family:'Inter',sans-serif; }
        @include('account.navigation-styles')
        main { width:min(720px, calc(100% - 40px)); margin:0 auto; padding:56px 0; }
        h1 { margin:0 0 8px; font-size:28px; }
        .intro { margin:0 0 28px; color:var(--muted); font-size:14px; }
        .card { background:var(--mid); border:1px solid var(--border); border-radius:16px; padding:24px; }
        .setting { display:flex; align-items:center; justify-content:space-between; gap:20px; padding:16px 0; border-bottom:1px solid var(--border); }
        .setting:first-child { padding-top:0; }
        .setting:last-child { padding-bottom:0; border-bottom:0; }
        .setting h2 { margin:0 0 5px; font-size:15px; }
        .setting p { margin:0; color:var(--muted); font-size:13px; line-height:1.5; }
        .status { padding:5px 10px; border-radius:20px; background:rgba(148,163,184,.12); color:var(--muted); font-size:11px; white-space:nowrap; }
        @media (max-width:640px) { main { width:min(100% - 32px, 720px); padding:36px 0; } .card { padding:20px; } }
    </style>
</head>
<body>
    @include('account.navigation', ['activeNav' => 'settings'])
    @include('account.profile-menu')
    <main>
        <h1>Settings</h1>
        <p class="intro">Review account preferences and security options.</p>
        <section class="card">
            <div class="setting"><div><h2>Account security</h2><p>Your account is protected by your SecureThreatShield login.</p></div><span class="status">Active</span></div>
            <div class="setting"><div><h2>Notifications</h2><p>Mobile approval notifications will be configurable here.</p></div><span class="status">Coming soon</span></div>
            <div class="setting"><div><h2>Appearance</h2><p>Theme and display preferences will be configurable here.</p></div><span class="status">Coming soon</span></div>
        </section>
    </main>
</body>
</html>
