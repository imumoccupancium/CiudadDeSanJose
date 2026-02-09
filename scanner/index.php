<?php
session_start();
// No strict login required for the simulation to make it easy to test, 
// but in reality this would be restricted to a specific Device/IP or Guard login.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSJ Virtual Gate - Turnstile Simulator</title>
    
    <!-- Premium Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --bg-dark: #0f172a;
            --bg-card: rgba(30, 41, 59, 0.7);
            --accent-green: #10b981;
            --accent-red: #ef4444;
            --accent-blue: #3b82f6;
            --text-glow: 0 0 10px rgba(16, 185, 129, 0.5);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(circle at 20% 20%, rgba(59, 130, 246, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(16, 185, 129, 0.05) 0%, transparent 40%);
            color: white;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        /* Simulator Frame */
        .simulator-container {
            width: 100%;
            max-width: 1200px;
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 20px;
            padding: 20px;
        }

        /* Left Side: The "Display" */
        .screen-main {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .screen-main.unlocked {
            border-color: var(--accent-green);
            box-shadow: 0 0 50px rgba(16, 185, 129, 0.2);
        }

        .screen-header {
            text-align: center;
            margin-bottom: 30px;
            width: 100%;
        }

        .screen-header h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.5rem;
            letter-spacing: 2px;
            color: var(--accent-blue);
            text-transform: uppercase;
        }

        .gate-mode-selector {
            display: flex;
            background: rgba(15, 23, 42, 0.8);
            border-radius: 12px;
            padding: 5px;
            margin-top: 15px;
            width: fit-content;
            margin-inline: auto;
        }

        .mode-btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .mode-btn.active {
            background: var(--accent-blue);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        /* The Virtual Turnstile Bar */
        .turnstile-visualization {
            width: 100%;
            height: 200px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 40px 0;
            position: relative;
        }

        .turstile-arm {
            width: 300px;
            height: 15px;
            background: linear-gradient(90deg, #64748b, #cbd5e1);
            border-radius: 20px;
            position: relative;
            transform-origin: left center;
            transition: transform 1s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .unlocked .turstile-arm {
            transform: rotate(-120deg);
        }

        /* Status Light */
        .status-light {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #1e293b;
            border: 8px solid #0f172a;
            box-shadow: inset 0 0 20px rgba(0,0,0,0.5);
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: all 0.3s;
        }

        .status-light i {
            font-size: 2.5rem;
            color: #475569;
        }

        .screen-main.locked .status-light {
            background: radial-gradient(circle, #7f1d1d, #450a0a);
            box-shadow: 0 0 30px rgba(239, 68, 68, 0.4);
        }
        .screen-main.locked .status-light i { color: white; }

        .screen-main.unlocked .status-light {
            background: radial-gradient(circle, #065f46, #064e3b);
            box-shadow: 0 0 30px rgba(16, 185, 129, 0.6);
        }
        .screen-main.unlocked .status-light i { color: white; }

        .status-text {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .locked .status-text { color: var(--accent-red); }
        .unlocked .status-text { color: var(--accent-green); text-shadow: var(--text-glow); }

        /* QR Scanner Section (Right Side) */
        .control-panel {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .qr-scanner-box {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
        }

        #reader {
            width: 100%;
            border-radius: 16px;
            overflow: hidden;
            background: black;
            border: 2px solid #334155;
        }

        .manual-input-box {
            background: var(--bg-card);
            border-radius: 24px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-control {
            width: 100%;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 15px;
            color: white;
            font-size: 1rem;
            margin-bottom: 15px;
            outline: none;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: var(--accent-blue);
        }

        .btn-submit {
            width: 100%;
            background: var(--accent-blue);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-submit:hover {
            background: #2563eb;
        }

        /* User Info Toast (Floating) */
        .user-toast {
            position: absolute;
            bottom: -150px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(16, 185, 129, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid var(--accent-green);
            border-radius: 16px;
            padding: 20px 40px;
            display: flex;
            align-items: center;
            gap: 20px;
            transition: bottom 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 10;
        }

        .user-toast.show {
            bottom: 40px;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            background: var(--accent-green);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .simulator-container {
                grid-template-columns: 1fr;
            }
            body { overflow-y: auto; height: auto; padding: 20px; }
        }
    </style>
</head>
<body>

    <div class="simulator-container">
        <!-- Main Simulation Display -->
        <main class="screen-main locked" id="gateScreen">
            <div class="screen-header">
                <h1>Ciudad De San Jose</h1>
                <p style="color: #94a3b8; font-size: 0.9rem;">Gate Access Controller Simulation</p>
                
                <div class="gate-mode-selector">
                    <button class="mode-btn active" onclick="setMode('IN', this)">ENTRY</button>
                    <button class="mode-btn" onclick="setMode('OUT', this)">EXIT</button>
                </div>
            </div>

            <div class="status-light">
                <i class="bi bi-lock-fill" id="statusIcon"></i>
            </div>

            <div class="status-text" id="statusLabel">LOCKED</div>
            <p style="color: #94a3b8;" id="instructionLabel">PLEASE SCAN QR CODE</p>

            <div class="turnstile-visualization">
                <div style="width: 40px; height: 100px; background: #334155; border-radius: 10px; position: absolute; left: calc(50% - 145px); z-index: 2;"></div>
                <div class="turstile-arm"></div>
            </div>

            <!-- Animated User Info -->
            <div class="user-toast" id="userToast">
                <div class="user-avatar">
                    <i class="bi bi-person-check-fill"></i>
                </div>
                <div>
                    <div style="font-size: 0.8rem; text-transform: uppercase; color: var(--accent-green); font-weight: 700;">Access Granted</div>
                    <div style="font-size: 1.4rem; font-weight: 600;" id="userName">---</div>
                    <div style="color: #94a3b8; font-size: 0.9rem;" id="userType">Resident Account</div>
                </div>
            </div>
        </main>

        <!-- Control Side -->
        <aside class="control-panel">
            <div class="qr-scanner-box">
                <div style="font-weight: 600; margin-bottom: 15px; display: flex; justify-content: space-between;">
                    <span><i class="bi bi-camera me-2"></i>Virtual Scanner</span>
                    <span id="scannerStatus" style="font-size: 0.8rem; color: var(--accent-green);">Ready</span>
                </div>
                <div id="reader"></div>
                <p style="font-size: 0.8rem; color: #64748b; margin-top: 10px; text-align: center;">
                    Show your QR code to the camera or use manual input below.
                </p>
            </div>

            <div class="manual-input-box">
                <div style="font-weight: 600; margin-bottom: 15px;">
                    <i class="bi bi-keyboard me-2"></i>Manual Logic Test
                </div>
                <form id="manualForm">
                    <input type="text" class="form-control" id="qrToken" placeholder="Enter QR Token (e.g. QR-HO-001)" required>
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-send-fill me-2"></i>EXECUTE SIGNAL
                    </button>
                </form>
            </div>
        </aside>
    </div>

    <!-- Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <!-- SweetAlert For Errors -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let currentMode = 'IN';
        let isProcessing = false;

        function setMode(mode, btn) {
            currentMode = mode;
            document.querySelectorAll('.mode-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            // Visual reset
            resetGate();
        }

        // --- GATE LOGIC ---
        function triggerAccess(token) {
            if (isProcessing) return;
            isProcessing = true;

            fetch('../api/verify_gate.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `token=${encodeURIComponent(token)}&gate_mode=${currentMode}&gate_name=Simulation Gate`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    grantAccess(data.user);
                } else {
                    denyAccess(data.message);
                }
            })
            .catch(err => {
                Swal.fire('Error', 'Communication with server failed', 'error');
                isProcessing = false;
            });
        }

        function grantAccess(user) {
            const screen = document.getElementById('gateScreen');
            const icon = document.getElementById('statusIcon');
            const label = document.getElementById('statusLabel');
            const instruct = document.getElementById('instructionLabel');
            
            // Visual Unlock
            screen.classList.remove('locked');
            screen.classList.add('unlocked');
            icon.className = 'bi bi-unlock-fill';
            label.innerText = 'UNLOCKED';
            instruct.innerText = 'PROCEED NOW';

            // Show User Info
            document.getElementById('userName').innerText = user.name;
            document.getElementById('userType').innerText = `${user.type} • ${user.id}`;
            document.getElementById('userToast').classList.add('show');

            // Sound Effect (Optional, Browser allows after interaction)
            // new Audio('../assets/sounds/access_granted.mp3').play().catch(()=>{});

            // Re-lock after 5 seconds
            setTimeout(() => {
                resetGate();
                isProcessing = false;
            }, 5000);
        }

        function denyAccess(msg) {
            const screen = document.getElementById('gateScreen');
            const label = document.getElementById('statusLabel');
            
            label.innerText = 'DENIED';
            screen.style.borderColor = 'var(--accent-red)';
            screen.style.boxShadow = '0 0 30px rgba(239, 68, 68, 0.3)';

            Swal.fire({
                title: 'Access Denied',
                text: msg,
                icon: 'error',
                timer: 2000,
                showConfirmButton: false
            });

            setTimeout(() => {
                resetGate();
                isProcessing = false;
            }, 2000);
        }

        function resetGate() {
            const screen = document.getElementById('gateScreen');
            const icon = document.getElementById('statusIcon');
            const label = document.getElementById('statusLabel');
            const instruct = document.getElementById('instructionLabel');

            screen.classList.remove('unlocked');
            screen.classList.add('locked');
            screen.style.borderColor = '';
            screen.style.boxShadow = '';
            
            icon.className = 'bi bi-lock-fill';
            label.innerText = 'LOCKED';
            instruct.innerText = 'PLEASE SCAN QR CODE';

            document.getElementById('userToast').classList.remove('show');
        }

        // --- SCANNER SETUP ---
        function onScanSuccess(decodedText, decodedResult) {
            if (!isProcessing) {
                console.log(`Code scanned = ${decodedText}`, decodedResult);
                triggerAccess(decodedText);
            }
        }

        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess);

        // --- MANUAL FORM ---
        document.getElementById('manualForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const token = document.getElementById('qrToken').value;
            triggerAccess(token);
            document.getElementById('qrToken').value = '';
        });

    </script>
</body>
</html>
