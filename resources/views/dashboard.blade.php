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
        .nav-logo { display: flex; align-items: center; }
        .nav-logo img { height: 38px; width: auto; }

        .nav-links { display: flex; align-items: center; gap: 4px; }
        .nav-links a {
            color: var(--text-muted); font-size: 14px; font-weight: 500;
            text-decoration: none; padding: 7px 16px; border-radius: 8px;
            transition: background 0.2s, color 0.2s;
        }
        .nav-links a:hover  { background: var(--brand-mid); color: var(--text-primary); }
        .nav-links a.active { background: rgba(61,90,241,0.15); color: #818CF8; }

        .nav-right { display: flex; align-items: center; gap: 14px; }
        .user-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--brand-blue);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; color: #fff;
            border: 0;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .user-avatar:hover, .user-avatar:focus-visible {
            box-shadow: 0 0 0 4px rgba(61,90,241,0.24);
            transform: translateY(-1px);
        }

        /* ── Floating profile window ── */
        .profile-window {
            position: fixed;
            top: 76px;
            right: 28px;
            z-index: 200;
            width: min(360px, calc(100vw - 32px));
            background: #111A2D;
            border: 1px solid rgba(129,140,248,0.24);
            border-radius: 16px;
            box-shadow: 0 24px 70px rgba(0,0,0,0.45);
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
            transform: translateY(-10px) scale(0.98);
            transition: opacity 0.2s, transform 0.2s;
        }
        .profile-window.is-open {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0) scale(1);
        }
        .profile-window.is-maximized {
            top: 80px;
            right: 28px;
            bottom: 28px;
            width: min(640px, calc(100vw - 56px));
        }
        .profile-window.is-minimized {
            width: 220px;
            bottom: auto;
        }
        .profile-window.is-minimized .profile-body,
        .profile-window.is-minimized .profile-window-footer { display: none; }
        .profile-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            color: var(--text-muted);
            background: transparent;
            border: 0;
            border-radius: 7px;
            cursor: pointer;
        }
        .profile-back:hover, .profile-back:focus-visible {
            color: var(--text-primary);
            background: var(--brand-mid);
        }
        .profile-body { padding: 20px; }
        .profile-summary {
            display: flex;
            align-items: center;
            gap: 14px;
            padding-bottom: 18px;
            margin-bottom: 14px;
            border-bottom: 1px solid var(--brand-border);
        }
        .profile-large-avatar {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 50%;
            background: var(--brand-blue);
            color: #fff;
            font-size: 18px;
            font-weight: 800;
        }
        .profile-summary h2 { font-size: 16px; margin-bottom: 4px; }
        .profile-summary p, .profile-detail-label { color: var(--text-muted); font-size: 12px; }
        .profile-detail { display: flex; flex-direction: column; gap: 5px; margin-bottom: 16px; }
        .profile-detail-value { font-size: 14px; color: var(--text-primary); word-break: break-word; }
        .profile-actions { display: grid; gap: 8px; }
        .profile-action, .sign-out-button {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 13px;
            border: 1px solid var(--brand-border);
            border-radius: 10px;
            color: var(--text-primary);
            background: rgba(255,255,255,0.03);
            font: inherit;
            font-size: 13px;
            text-align: left;
            cursor: pointer;
        }
        .profile-action:hover, .profile-action:focus-visible { background: var(--brand-mid); }
        .sign-out-button {
            justify-content: center;
            margin-top: 18px;
            background: rgba(239,68,68,0.14);
            border-color: rgba(248,113,113,0.35);
            color: #FCA5A5;
            font-weight: 700;
            text-align: center;
        }
        .sign-out-button:hover, .sign-out-button:focus-visible { background: rgba(239,68,68,0.24); }
        .profile-view[hidden] { display: none; }
        .profile-view-header { display: flex; align-items: center; gap: 8px; margin-bottom: 20px; }
        .profile-view-header h2 { font-size: 16px; }
        .settings-note { color: var(--text-muted); font-size: 13px; line-height: 1.6; }
        .profile-window-footer { padding: 0 20px 20px; color: var(--text-muted); font-size: 11px; }

        @media (max-width: 640px) {
            nav { padding: 0 18px; }
            .nav-links { gap: 0; }
            .nav-links a { padding: 7px 9px; font-size: 12px; }
            .content { padding: 36px 18px; }
            .profile-window { top: 70px; right: 16px; }
            .profile-window.is-maximized { top: 70px; right: 16px; bottom: 16px; width: calc(100vw - 32px); }
        }

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
        <img src="{{ asset('images/securets-logo.png') }}" alt="SecureThreatShield Logo">
    </div>

    <div class="nav-links">
        <a href="{{ route('dashboard') }}" class="active">Dashboard</a>
        <a href="{{ route('integrations') }}">Integrations</a>
    </div>

    <div class="nav-right">
        <button type="button" class="user-avatar" id="profile-trigger" aria-label="Open profile" aria-expanded="false">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
        </button>
    </div>
</nav>

<aside class="profile-window" id="profile-window" aria-label="Profile window" aria-hidden="true">
    <div class="profile-body">
        <section class="profile-view" id="profile-home-view">
            <div class="profile-summary">
                <div class="profile-large-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <div>
                    <h2>{{ Auth::user()->name }}</h2>
                    <p>SecureThreatShield account</p>
                </div>
            </div>
            <div class="profile-detail">
                <span class="profile-detail-label">Email address</span>
                <span class="profile-detail-value">{{ Auth::user()->email }}</span>
            </div>
            <div class="profile-actions">
                <button type="button" class="profile-action" data-profile-view="profile">
                    <span>Profile details</span>
                    <span aria-hidden="true">›</span>
                </button>
                <button type="button" class="profile-action" data-profile-view="settings">
                    <span>Settings</span>
                    <span aria-hidden="true">›</span>
                </button>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sign-out-button">Sign out</button>
            </form>
        </section>

        <section class="profile-view" id="profile-details-view" hidden>
            <div class="profile-view-header">
                <button type="button" class="profile-back" data-profile-back aria-label="Return to account menu" title="Back">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/><path d="M9 12h10"/></svg>
                </button>
                <h2>Profile details</h2>
            </div>
            <div class="profile-detail"><span class="profile-detail-label">Name</span><span class="profile-detail-value">{{ Auth::user()->name }}</span></div>
            <div class="profile-detail"><span class="profile-detail-label">Email address</span><span class="profile-detail-value">{{ Auth::user()->email }}</span></div>
        </section>

        <section class="profile-view" id="profile-settings-view" hidden>
            <div class="profile-view-header">
                <button type="button" class="profile-back" data-profile-back aria-label="Return to account menu" title="Back">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/><path d="M9 12h10"/></svg>
                </button>
                <h2>Settings</h2>
            </div>
            <p class="settings-note">Settings are coming soon. Use the return icon to go back to your account menu.</p>
        </section>
    </div>
    <div class="profile-window-footer">Account controls</div>
</aside>

<!-- ── Main Content ── -->
<div class="content">
    <div class="page-header">
        <h1>Welcome to your dashboard! 👋</h1>
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

    <a href="{{ route('integrations') }}" class="status-panel" style="text-decoration:none;color:inherit;margin-top:16px;display:flex">
        <div class="status-icon" style="background:rgba(61,90,241,0.12)">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#818CF8" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
            </svg>
        </div>
        <div class="status-text">
            <h2>Integration Hub</h2>
            <p>Connect an external app’s login page, get a secret key, and manage apps for this account. Approve or decline logins on the mobile app.</p>
        </div>
    </a>
</div>

<script>
    (() => {
        const trigger = document.getElementById('profile-trigger');
        const profileWindow = document.getElementById('profile-window');
        const homeView = document.getElementById('profile-home-view');
        const views = {
            profile: document.getElementById('profile-details-view'),
            settings: document.getElementById('profile-settings-view'),
        };

        const showView = (viewName) => {
            homeView.hidden = viewName !== 'home';
            Object.entries(views).forEach(([name, view]) => { view.hidden = name !== viewName; });
        };

        const closeWindow = () => {
            profileWindow.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
            profileWindow.setAttribute('aria-hidden', 'true');
            showView('home');
        };

        trigger.addEventListener('click', () => {
            const isOpen = profileWindow.classList.toggle('is-open');
            trigger.setAttribute('aria-expanded', String(isOpen));
            profileWindow.setAttribute('aria-hidden', String(!isOpen));
        });

        document.querySelectorAll('[data-profile-view]').forEach((button) => {
            button.addEventListener('click', () => showView(button.dataset.profileView));
        });
        document.querySelectorAll('[data-profile-back]').forEach((button) => {
            button.addEventListener('click', () => showView('home'));
        });

        document.addEventListener('click', (event) => {
            if (!profileWindow.contains(event.target) && !trigger.contains(event.target)) closeWindow();
        });
    })();
</script>

</body>
</html>
