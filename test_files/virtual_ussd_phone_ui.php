<?php
declare(strict_types=1);
$configPath = dirname(__DIR__) . '/config/config.php';
if (is_file($configPath)) {
    require_once $configPath;
}
$appUrl = defined('APP_URL') ? APP_URL : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual USSD Phone</title>
    <style>
        :root {
            --bg: #0f172a;
            --panel: #111827;
            --line: #243044;
            --text: #e5e7eb;
            --muted: #94a3b8;
            --accent: #22c55e;
            --accent2: #60a5fa;
            --danger: #ef4444;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, sans-serif;
            background: linear-gradient(180deg, #0b1224 0%, #0f172a 100%);
            color: var(--text);
            min-height: 100vh;
            padding: 24px;
        }
        .wrap {
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 390px 1fr;
            gap: 20px;
        }
        .phone-shell {
            border: 1px solid var(--line);
            border-radius: 24px;
            background: #020617;
            padding: 14px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.45);
            position: sticky;
            top: 16px;
            height: fit-content;
        }
        .phone-screen {
            border: 1px solid #1f2937;
            border-radius: 18px;
            overflow: hidden;
            background: #0b1220;
        }
        .phone-header {
            padding: 10px 12px;
            border-bottom: 1px solid #1f2937;
            background: #0b1224;
            font-size: 13px;
            color: var(--muted);
        }
        .chat {
            height: 460px;
            overflow-y: auto;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            background: #020617;
        }
        .bubble {
            max-width: 90%;
            padding: 10px 12px;
            border-radius: 12px;
            white-space: pre-wrap;
            line-height: 1.35;
            font-size: 13px;
        }
        .bubble.system {
            align-self: flex-start;
            background: #0f1a2d;
            border: 1px solid #22314e;
        }
        .bubble.user {
            align-self: flex-end;
            background: #132414;
            border: 1px solid #2b4c2e;
        }
        .controls {
            padding: 12px;
            border-top: 1px solid #1f2937;
            background: #0b1224;
        }
        .controls input[type="text"] {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #314158;
            background: #0b1220;
            color: var(--text);
            margin-bottom: 8px;
        }
        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        button {
            border: 1px solid #2f4461;
            background: #10203a;
            color: #dbeafe;
            border-radius: 8px;
            padding: 9px 10px;
            cursor: pointer;
            font-size: 13px;
        }
        button:hover { filter: brightness(1.1); }
        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .panel {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: var(--panel);
            padding: 14px;
            margin-bottom: 14px;
        }
        .panel h3 {
            margin: 0 0 10px 0;
            font-size: 15px;
            color: #dbeafe;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        .field label {
            display: block;
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 4px;
        }
        .field input {
            width: 100%;
            padding: 9px 10px;
            border-radius: 8px;
            border: 1px solid #314158;
            background: #0b1220;
            color: var(--text);
            font-size: 13px;
        }
        .log {
            border: 1px solid #25324a;
            border-radius: 10px;
            background: #0a1326;
            padding: 10px;
            height: 290px;
            overflow: auto;
            font-family: Consolas, monospace;
            font-size: 12px;
            line-height: 1.45;
            color: #cbd5e1;
            white-space: pre-wrap;
        }
        .badge {
            display: inline-block;
            border-radius: 999px;
            padding: 4px 9px;
            font-size: 11px;
            border: 1px solid;
        }
        .ok { color: #86efac; border-color: #166534; background: #052e16; }
        .err { color: #fca5a5; border-color: #7f1d1d; background: #450a0a; }
        .muted { color: var(--muted); font-size: 12px; }
        .shortcodes-list {
            margin-top: 8px;
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .chip {
            border: 1px solid #2f4461;
            background: #10203a;
            color: #dbeafe;
            border-radius: 999px;
            padding: 5px 8px;
            font-size: 12px;
            cursor: pointer;
        }
        @media (max-width: 960px) {
            .wrap { grid-template-columns: 1fr; }
            .phone-shell { position: static; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="phone-shell">
            <div class="phone-screen">
                <div class="phone-header">
                    Virtual USSD Phone
                </div>
                <div id="chat" class="chat"></div>
                <div class="controls">
                    <input id="userInput" type="text" placeholder="Type shortcode or menu input">
                    <div class="row">
                        <button id="sendBtn">Send Input</button>
                        <button id="startBtn">Dial / Start</button>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="panel">
                <h3>Session Setup</h3>
                <div class="grid">
                    <div class="field" style="grid-column: span 2;">
                        <label for="endpoint">USSD Endpoint</label>
                        <input id="endpoint" type="text" value="">
                    </div>
                    <div class="field">
                        <label for="serviceCode">ServiceCode</label>
                        <input id="serviceCode" type="text" value="713*734">
                    </div>
                    <div class="field">
                        <label for="mobile">Mobile</label>
                        <input id="mobile" type="text" value="233244000000">
                    </div>
                    <div class="field">
                        <label for="sessionId">SessionId</label>
                        <input id="sessionId" type="text" value="">
                    </div>
                    <div class="field">
                        <label for="eventId">Event Id (for shortcode helper)</label>
                        <input id="eventId" type="text" value="2">
                    </div>
                </div>
                <div class="row" style="margin-top: 10px;">
                    <button id="newSessionBtn">New Session ID</button>
                    <button id="loadShortcodesBtn">Load Shortcodes</button>
                </div>
                <div id="shortcodes" class="shortcodes-list"></div>
            </div>

            <div class="panel">
                <h3>Fulfillment Testing</h3>
                <div class="muted">After AddToCart response, click simulate fulfillment to finish payment callback flow.</div>
                <div class="row" style="margin-top: 10px;">
                    <button id="fulfillmentBtn" disabled>Simulate Fulfillment</button>
                    <button id="resetBtn">Reset Screen</button>
                </div>
                <div id="status" style="margin-top: 10px;"></div>
            </div>

            <div class="panel">
                <h3>Debug Log</h3>
                <div id="log" class="log"></div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const APP_BASE_URL = <?php echo json_encode($appUrl, JSON_UNESCAPED_SLASHES); ?>;
        const inferredBasePath = window.location.pathname.includes('/test_files/')
            ? window.location.pathname.split('/test_files/')[0]
            : '';
        const BASE_URL = (APP_BASE_URL ? APP_BASE_URL : (window.location.origin + inferredBasePath)).replace(/\/+$/, '');
        const endpointEl = document.getElementById('endpoint');
        const serviceCodeEl = document.getElementById('serviceCode');
        const mobileEl = document.getElementById('mobile');
        const sessionIdEl = document.getElementById('sessionId');
        const eventIdEl = document.getElementById('eventId');
        const userInputEl = document.getElementById('userInput');
        const sendBtn = document.getElementById('sendBtn');
        const startBtn = document.getElementById('startBtn');
        const newSessionBtn = document.getElementById('newSessionBtn');
        const loadShortcodesBtn = document.getElementById('loadShortcodesBtn');
        const fulfillmentBtn = document.getElementById('fulfillmentBtn');
        const resetBtn = document.getElementById('resetBtn');
        const shortcodesEl = document.getElementById('shortcodes');
        const chatEl = document.getElementById('chat');
        const logEl = document.getElementById('log');
        const statusEl = document.getElementById('status');

        const state = {
            lastAddToCart: null,
            initialized: false
        };

        endpointEl.value = BASE_URL + '/api/ussd/callback';
        newSessionId();

        function newSessionId() {
            sessionIdEl.value = 'vp_' + Math.floor(Date.now() / 1000);
            state.lastAddToCart = null;
            fulfillmentBtn.disabled = true;
            status('');
        }

        function status(text, ok = true) {
            if (!text) {
                statusEl.innerHTML = '';
                return;
            }
            statusEl.innerHTML = `<span class="badge ${ok ? 'ok' : 'err'}">${escapeHtml(text)}</span>`;
        }

        function addBubble(role, message) {
            const div = document.createElement('div');
            div.className = 'bubble ' + role;
            div.textContent = message;
            chatEl.appendChild(div);
            chatEl.scrollTop = chatEl.scrollHeight;
        }

        function log(line) {
            logEl.textContent += line + '\n';
            logEl.scrollTop = logEl.scrollHeight;
        }

        function escapeHtml(text) {
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function parsePossiblyNoisyJson(rawText) {
            try {
                return JSON.parse(rawText);
            } catch (_) {}

            const firstBrace = rawText.indexOf('{');
            const lastBrace = rawText.lastIndexOf('}');
            if (firstBrace !== -1 && lastBrace !== -1 && lastBrace > firstBrace) {
                const candidate = rawText.slice(firstBrace, lastBrace + 1);
                try {
                    return JSON.parse(candidate);
                } catch (_) {}
            }
            return null;
        }

        async function postJson(payload) {
            const endpoint = endpointEl.value.trim();
            if (!endpoint) {
                throw new Error('Endpoint is required.');
            }

            const res = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });

            const text = await res.text();
            const json = parsePossiblyNoisyJson(text);
            log('> ' + JSON.stringify(payload));
            log('< HTTP ' + res.status);
            log('< ' + (json ? JSON.stringify(json) : text));
            return { status: res.status, text, json };
        }

        function buildBasePayload(message, type) {
            return {
                SessionId: sessionIdEl.value.trim(),
                ServiceCode: serviceCodeEl.value.trim(),
                Mobile: mobileEl.value.trim(),
                Message: message,
                Type: type
            };
        }

        function handleUssdResponse(obj) {
            if (!obj) {
                status('No JSON response received', false);
                addBubble('system', 'No JSON response. Check endpoint/server.');
                return;
            }

            if (obj.Type === 'AddToCart') {
                const item = obj.Item || {};
                const msg = (obj.Message || 'AddToCart received')
                    + '\n\nItem: ' + (item.ItemName || '-')
                    + '\nQty: ' + (item.Qty ?? '-')
                    + '\nPrice: ' + (item.Price ?? '-');
                addBubble('system', msg);
                state.lastAddToCart = obj;
                fulfillmentBtn.disabled = false;
                status('AddToCart received. You can simulate fulfillment.');
                return;
            }

            if (typeof obj.Message !== 'undefined') {
                addBubble('system', String(obj.Message));
            } else if (typeof obj.message !== 'undefined') {
                addBubble('system', String(obj.message));
            } else {
                addBubble('system', JSON.stringify(obj));
            }

            if (obj.Type === 'release') {
                status('Session released by USSD flow.');
            }
        }

        async function startDial() {
            const payload = buildBasePayload('', 'Initiation');
            try {
                const resp = await postJson(payload);
                handleUssdResponse(resp.json);
            } catch (e) {
                status(e.message || 'Dial failed', false);
                addBubble('system', 'Dial failed: ' + (e.message || 'unknown error'));
            }
        }

        async function sendInput() {
            const message = userInputEl.value.trim();
            if (!message) {
                status('Enter input first.', false);
                return;
            }
            addBubble('user', message);
            userInputEl.value = '';

            const payload = buildBasePayload(message, 'Response');
            try {
                const resp = await postJson(payload);
                handleUssdResponse(resp.json);
            } catch (e) {
                status(e.message || 'Send failed', false);
                addBubble('system', 'Send failed: ' + (e.message || 'unknown error'));
            }
        }

        async function simulateFulfillment() {
            if (!state.lastAddToCart) {
                status('No AddToCart yet. Confirm vote first.', false);
                return;
            }

            const item = state.lastAddToCart.Item || {};
            const payload = {
                SessionId: sessionIdEl.value.trim(),
                OrderId: 'ORD-' + new Date().toISOString().replace(/[^\d]/g, '').slice(0, 14),
                OrderInfo: {
                    Status: 'Paid',
                    Amount: Number(item.Price || 0),
                    Payment: {
                        IsSuccessful: true,
                        Mobile: mobileEl.value.trim()
                    }
                }
            };

            try {
                const resp = await postJson(payload);
                handleUssdResponse(resp.json);
                if (resp.json && resp.json.success === true) {
                    status('Fulfillment success. Vote processed.');
                } else {
                    status('Fulfillment returned error.', false);
                }
            } catch (e) {
                status(e.message || 'Fulfillment failed', false);
                addBubble('system', 'Fulfillment failed: ' + (e.message || 'unknown error'));
            }
        }

        async function loadShortcodes() {
            shortcodesEl.innerHTML = '';
            const eventId = eventIdEl.value.trim();
            if (!eventId) {
                status('Event Id required for shortcode helper', false);
                return;
            }

            const url = BASE_URL + '/api/events/' + encodeURIComponent(eventId) + '/shortcodes';
            try {
                const res = await fetch(url);
                const text = await res.text();
                const json = parsePossiblyNoisyJson(text);
                if (!json || !json.success || !Array.isArray(json.shortcodes)) {
                    status('Could not load shortcodes for event ' + eventId, false);
                    return;
                }

                json.shortcodes.forEach((row) => {
                    const chip = document.createElement('button');
                    chip.className = 'chip';
                    chip.type = 'button';
                    chip.textContent = row.short_code + ' - ' + row.contestant_name;
                    chip.addEventListener('click', function () {
                        userInputEl.value = row.short_code;
                        userInputEl.focus();
                    });
                    shortcodesEl.appendChild(chip);
                });

                status('Loaded ' + json.shortcodes.length + ' shortcode(s).');
            } catch (e) {
                status('Shortcode load failed: ' + (e.message || 'unknown error'), false);
            }
        }

        sendBtn.addEventListener('click', sendInput);
        startBtn.addEventListener('click', startDial);
        fulfillmentBtn.addEventListener('click', simulateFulfillment);
        newSessionBtn.addEventListener('click', newSessionId);
        loadShortcodesBtn.addEventListener('click', loadShortcodes);
        resetBtn.addEventListener('click', function () {
            chatEl.innerHTML = '';
            logEl.textContent = '';
            state.lastAddToCart = null;
            fulfillmentBtn.disabled = true;
            status('');
        });
        userInputEl.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendInput();
            }
        });

        // Friendly starter line.
        addBubble('system', 'Tap "Dial / Start" to begin a USSD session.');
        status('Ready');
    })();
    </script>
</body>
</html>
