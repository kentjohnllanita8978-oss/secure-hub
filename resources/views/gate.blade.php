<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign in to {{ $integration->name }} – SecureTS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Inter, sans-serif;
            background: #0F172A;
            color: #F8FAFC;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 420px;
            background: #1E293B;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 28px;
        }
        .app { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
        .app img { width: 40px; height: 40px; border-radius: 8px; background: rgba(255,255,255,0.06); }
        h1 { font-size: 20px; margin-bottom: 6px; }
        p { color: #94A3B8; font-size: 14px; line-height: 1.5; margin-bottom: 20px; }
        label { display: block; font-size: 12px; color: #94A3B8; margin-bottom: 6px; }
        input {
            width: 100%;
            background: rgba(255,255,255,0.07);
            border: 1.5px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 12px;
            color: #F8FAFC;
            font-size: 14px;
            margin-bottom: 14px;
        }
        button {
            width: 100%;
            background: #3D5AF1;
            border: 0;
            border-radius: 10px;
            padding: 14px;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }
        button:disabled { opacity: 0.6; cursor: default; }
        .status { text-align: center; }
        .status strong { display: block; font-size: 18px; margin: 12px 0 8px; }
        a { color: #818CF8; }
        .err { color: #F87171; font-size: 13px; margin-bottom: 12px; }
        .hidden { display: none; }
    </style>
</head>
<body>
<div class="card">
    <div class="app">
        <img src="{{ $integration->favicon }}" alt="" onerror="this.style.display='none'">
        <div>
            <div style="font-weight:700">{{ $integration->name }}</div>
            <div style="font-size:12px;color:#94A3B8">Protected by SecureTS</div>
        </div>
    </div>

    <div id="firstPanel" class="{{ $isFirst ? '' : 'hidden' }}">
        <h1>First-time setup</h1>
        <p>Enter the credentials this app uses to sign you in. Later visits will ask for approval on your phone instead.</p>
        <div class="err" id="firstError"></div>
        <form id="firstForm">
            <label for="username">Username or email</label>
            <input id="username" name="credential_username" autocomplete="username" required>
            <label for="password">Password</label>
            <input id="password" name="credential_password" type="password" autocomplete="current-password" required>
            <button type="submit" id="firstBtn">Continue</button>
        </form>
    </div>

    <div id="waitPanel" class="status {{ $isFirst ? 'hidden' : '' }}">
        <h1>Waiting for approval</h1>
        <p>Open the SecureTS app on your phone and choose Approve or Decline.</p>
        <strong id="waitStatus">Contacting your device…</strong>
    </div>

    <div id="donePanel" class="status hidden">
        <strong id="doneTitle"></strong>
        <p id="doneText"></p>
        <p><a id="doneLink" href="{{ $integration->url }}">Continue to {{ $integration->name }}</a></p>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const ATTEMPT_URL = @json($integration->gateUrl());
const IS_FIRST = @json($isFirst);

function show(id) {
    ['firstPanel', 'waitPanel', 'donePanel'].forEach((p) => {
        document.getElementById(p).classList.toggle('hidden', p !== id);
    });
}

async function attempt(body) {
    const res = await fetch(ATTEMPT_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify(body),
    });
    const data = await res.json();
    if (!res.ok) {
        throw new Error(data.message || Object.values(data.errors || {}).flat()[0] || 'Request failed');
    }
    return data;
}

function finish(data) {
    if (data.status === 'auto' || data.status === 'approved') {
        document.getElementById('doneTitle').textContent = 'Approved';
        document.getElementById('doneText').textContent = 'You can continue to the app.';
        if (data.redirect_url) document.getElementById('doneLink').href = data.redirect_url;
        show('donePanel');
        return;
    }
    if (data.status === 'declined') {
        document.getElementById('doneTitle').textContent = 'Declined';
        document.getElementById('doneText').textContent = 'This login was denied from your phone.';
        show('donePanel');
        return;
    }
    show('waitPanel');
    poll(data.event_id);
}

async function poll(eventId) {
    const url = ATTEMPT_URL.replace(/\/$/, '') + '/events/' + eventId;
    const tick = async () => {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) return;
        const data = await res.json();
        document.getElementById('waitStatus').textContent = 'Status: ' + data.status;
        if (data.status !== 'pending') {
            clearInterval(timer);
            finish(data);
        }
    };
    const timer = setInterval(tick, 2000);
    tick();
}

document.getElementById('firstForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('firstBtn');
    const err = document.getElementById('firstError');
    err.textContent = '';
    btn.disabled = true;
    try {
        const data = await attempt({
            credential_username: document.getElementById('username').value.trim(),
            credential_password: document.getElementById('password').value,
        });
        finish(data);
    } catch (ex) {
        err.textContent = ex.message;
        btn.disabled = false;
    }
});

if (!IS_FIRST) {
    attempt({}).then(finish).catch((ex) => {
        document.getElementById('waitStatus').textContent = ex.message;
    });
}
</script>
</body>
</html>
