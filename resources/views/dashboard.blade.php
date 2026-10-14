<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard – SecureThreatShield</title>
    <meta name="description" content="SecureThreatShield Dashboard – threat protection overview.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --brand-blue: #3D5AF1;
            --brand-dark: #0F172A;
            --brand-mid:  #1E293B;
            --brand-border: rgba(255,255,255,0.08);
            --text-primary: #F8FAFC;
            --text-muted: #94A3B8;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--brand-dark);
            color: var(--text-primary);
            min-height: 100vh;
        }

        /* ── Navbar ── */
        nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            height: 64px;
            background: #0A0F1E;
            border-bottom: 1px solid var(--brand-border);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .nav-logo { display: flex; align-items: center; gap: 10px; }
        .hexagon {
            width: 32px; height: 32px;
            background: var(--brand-blue);
            clip-path: polygon(50% 0%,93% 25%,93% 75%,50% 100%,7% 75%,7% 25%);
            display: flex; align-items: center; justify-content: center;
        }
        .shield-svg { width: 26px; height: 30px; }
        .logo-text { display: flex; flex-direction: column; line-height: 1; }
        .logo-name { font-size: 16px; font-weight: 700; color: #fff; }
        .logo-sub  { font-size: 9px;  font-weight: 500; color: var(--text-muted); margin-top: 1px; }

        .nav-right { display: flex; align-items: center; gap: 14px; }
        .user-badge {
            display: flex; align-items: center; gap: 8px;
            font-size: 14px; color: var(--text-muted);
        }
        .user-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--brand-blue);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; color: #fff;
        }
        .btn-logout {
            background: transparent;
            border: 1.5px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.2s, color 0.2s;
        }
        .btn-logout:hover { border-color: #FC8181; color: #FC8181; }

        /* ── Content ── */
        .content { padding: 48px 40px; max-width: 1100px; margin: 0 auto; }

        .page-header { margin-bottom: 36px; }
        .page-header h1 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 6px;
        }
        .page-header p { font-size: 14px; color: var(--text-muted); }

        /* ── Stat cards ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 36px;
        }
        .stat-card {
            background: var(--brand-mid);
            border: 1px solid var(--brand-border);
            border-radius: 14px;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 3px;
        }
        .stat-card.blue::before   { background: var(--brand-blue); }
        .stat-card.green::before  { background: #22C55E; }
        .stat-card.yellow::before { background: #EAB308; }
        .stat-card.red::before    { background: #EF4444; }
        .stat-label {
            font-size: 12px; font-weight: 500;
            color: var(--text-muted); margin-bottom: 10px;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .stat-value { font-size: 26px; font-weight: 800; }
        .stat-card.blue   .stat-value { color: #818CF8; }
        .stat-card.green  .stat-value { color: #4ADE80; }
        .stat-card.yellow .stat-value { color: #FACC15; }
        .stat-card.red    .stat-value { color: #F87171; }

        /* ── Status panel ── */
        .status-panel {
            background: var(--brand-mid);
            border: 1px solid var(--brand-border);
            border-radius: 14px;
            padding: 28px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .status-icon {
            width: 56px; height: 56px;
            background: rgba(34,197,94,0.15);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .status-text h2 { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
        .status-text p  { font-size: 14px; color: var(--text-muted); }
    </style>
</head>
<body>

<!-- ── Navbar ── -->
<nav>
    <div class="nav-logo">
        <div class="hexagon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="white">
                <path d="M18 8h-1V6A5 5 0 0 0 7 6v2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10a2 2 0 0 0-2-2zm-6 9a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm3.1-9H8.9V6a3.1 3.1 0 0 1 6.2 0v2z"/>
            </svg>
        </div>
        <svg class="shield-svg" viewBox="0 0 36 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 0L0 7v13c0 11 7.5 20.9 18 24 10.5-3.1 18-13 18-24V7L18 0z" fill="#1E293B"/>
            <path d="M18 0L0 7v13h18V0z" fill="#C0392B"/>
            <path d="M18 0L36 7v13H18V0z" fill="#ECF0F1"/>
            <path d="M18 20H0v13c0 0 7.5 7 18 7V20z" fill="#27AE60"/>
            <path d="M18 20H36v13c0 0-7.5 7-18 7V20z" fill="#2980B9"/>
        </svg>
        <div class="logo-text">
            <span class="logo-name">Secure<span style="color:#818CF8">TS</span></span>
            <span class="logo-sub">ThreatShield</span>
        </div>
    </div>

    <div class="nav-right">
        <div class="user-badge">
            <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            <span>{{ Auth::user()->name }}</span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">Logout</button>
        </form>
    </div>
</nav>

<!-- ── Main Content ── -->
<div class="content">
    <div class="page-header">
        <h1>Welcome back, {{ Auth::user()->name }}! 👋</h1>
        <p>Here's your SecureThreatShield protection overview.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-label">Protection Status</div>
            <div class="stat-value">Active</div>
        </div>
        <div class="stat-card green">
            <div class="stat-label">Threats Blocked</div>
            <div class="stat-value">0</div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-label">Auth Latency</div>
            <div class="stat-value">&lt;500ms</div>
        </div>
        <div class="stat-card red">
            <div class="stat-label">Alerts</div>
            <div class="stat-value">0</div>
        </div>
    </div>

    <div class="status-panel">
        <div class="status-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <polyline points="9 12 11 14 15 10"/>
            </svg>
        </div>
        <div class="status-text">
            <h2 style="color:#4ADE80">All Systems Operational</h2>
            <p>ThreatShield is actively monitoring your account. No threats detected.</p>
        </div>
    </div>
</div>

</body>
</html>
