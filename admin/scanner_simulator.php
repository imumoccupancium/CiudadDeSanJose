<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user = [
    'name' => $_SESSION['user_name'] ?? 'Admin User',
    'role' => $_SESSION['user_role'] ?? 'Administrator'
];

// Fetch some active data for the dropdowns
try {
    // 1. Homeowners
    $hStmt = $pdo->query("SELECT id, homeowner_id, name, qr_token, 'Homeowner' as type FROM homeowners WHERE status = 'active' AND qr_token IS NOT NULL LIMIT 50");
    $homeowners = $hStmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Visitors (any with QR)
    $vStmt = $pdo->query("SELECT id, homeowner_id, visitor_name as name, qr_token, 'Visitor' as type FROM visitor_logs WHERE qr_token IS NOT NULL LIMIT 20");
    $visitors = $vStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $allSimulatedEntries = array_merge($homeowners, $visitors);
} catch(Exception $e) {
    $allSimulatedEntries = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanner Hardware Simulator - Ciudad De San Jose</title>
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/vendor/fonts/inter/inter.css">
    <style>
        :root {
            --primary: #4361ee;
            --success: #4cc9f0;
            --sidebar-width: 260px;
        }
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .main-content { margin-left: var(--sidebar-width); padding: 2rem; }
        .simulator-card { border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .gate-display {
            background: #000;
            border-radius: 15px;
            padding: 2rem;
            color: #0f0;
            font-family: 'Courier New', monospace;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border: 4px solid #333;
            box-shadow: inset 0 0 20px rgba(0,255,0,0.2);
            position: relative;
            overflow: hidden;
        }
        .gate-display::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.06), rgba(0, 255, 0, 0.02), rgba(0, 0, 255, 0.06));
            background-size: 100% 2px, 3px 100%;
            pointer-events: none;
        }
        .status-light {
            width: 15px; height: 15px; border-radius: 50%; background: #333; margin-bottom: 1rem;
        }
        .status-light.active-in { background: #0f0; box-shadow: 0 0 10px #0f0; }
        .status-light.active-out { background: #f00; box-shadow: 0 0 10px #f00; }
        .blink { animation: blinker 1s linear infinite; }
        @keyframes blinker { 50% { opacity: 0; } }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <h3 class="fw-bold mb-0">QR Scanner Hardware Simulator</h3>
                    <p class="text-muted">Use this tool to simulate hardware relay signals for entry and exit gates.</p>
                </div>
            </div>

            <div class="row g-4">
                <!-- Simulation Controls -->
                <div class="col-lg-5">
                    <div class="card simulator-card">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">Relay Controls</h5>
                            
                            <form id="simulatorForm">
                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted">Select Identity to Scan</label>
                                    <select class="form-select rounded-3 p-3" id="scanTarget" required>
                                        <option value="">Choose resident or visitor...</option>
                                        <?php foreach($allSimulatedEntries as $entry): ?>
                                            <option value="<?= $entry['qr_token'] ?>" 
                                                    data-id="<?= $entry['id'] ?>" 
                                                    data-homeowner-id="<?= $entry['homeowner_id'] ?>" 
                                                    data-name="<?= $entry['name'] ?>" 
                                                    data-type="<?= $entry['type'] ?>">
                                                <?= $entry['name'] ?> (<?= htmlspecialchars($entry['type']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text small">This pulls active QR tokens from the database.</div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted">Gate Direction</label>
                                    <div class="d-flex gap-3">
                                        <input type="radio" class="btn-check" name="action" id="actionIn" value="IN" checked>
                                        <label class="btn btn-outline-success rounded-pill px-4 flex-grow-1 py-3" for="actionIn">
                                            <i class="bi bi-box-arrow-in-right me-2"></i> ENTRY (Gate IN)
                                        </label>

                                        <input type="radio" class="btn-check" name="action" id="actionOut" value="OUT">
                                        <label class="btn btn-outline-danger rounded-pill px-4 flex-grow-1 py-3" for="actionOut">
                                            <i class="bi bi-box-arrow-right me-2"></i> EXIT (Gate OUT)
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted">Network Condition</label>
                                    <div class="form-check form-switch p-3 bg-light rounded-3 border">
                                        <input class="form-check-input ms-0 me-3" type="checkbox" id="simulateOffline">
                                        <label class="form-check-label fw-bold small" for="simulateOffline">
                                            Simulate Internet Outage
                                        </label>
                                        <div class="x-small text-muted mt-1" style="font-size: 0.65rem;">If enabled, the scanner will fail to reach the server and use local fallback logic.</div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted">Device ID (Hardware ID)</label>
                                    <input type="text" class="form-control rounded-3" value="SIMULATED_SCANNER_01" id="deviceName">
                                </div>

                                <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold" id="simulateBtn">
                                    <i class="bi bi-lightning-fill me-2"></i> TRIGGER SCAN SIGNAL
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Gate Display Simulation -->
                <div class="col-lg-7">
                    <div class="card simulator-card shadow-lg" style="background: #222;">
                        <div class="card-body p-4 text-center">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">SIMULATOR ONLINE</span>
                                <div class="d-flex gap-2">
                                    <div id="lightIn" class="status-light" title="IN Relay Status"></div>
                                    <div id="lightOut" class="status-light" title="OUT Relay Status"></div>
                                </div>
                            </div>
                            
                            <div class="gate-display shadow-inner" id="displayBoard">
                                <div id="displayStatus" class="fs-4 mb-2">READY FOR SCAN...</div>
                                <div id="displayName" class="fw-bold fs-5 text-white">---</div>
                                <div id="displayType" class="small mt-2" style="color: #0c0;">WAITING FOR SIGNAL</div>
                            </div>

                            <div class="mt-4 text-start">
                                <h6 class="text-white small fw-bold mb-3">Hardware Logic Log</h6>
                                <div id="hardwareLog" class="p-3 rounded-3" style="background: #111; color: #777; font-family: monospace; font-size: 0.75rem; height: 120px; overflow-y: auto;">
                                    [SYSTEM] Initializing Scanner Relay Simulation...<br>
                                    [SYSTEM] Handshake with api/auto_scan.php: OK<br>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/sweetalert2/sweetalert2.all.min.js"></script>

    <script>
        let offlineBuffer = [];

        // Handle Switch back to Online
        $('#simulateOffline').on('change', function() {
            const displayStatus = $('#displayStatus');
            const hardwareLog = $('#hardwareLog');
            
            function addLog(msg) {
                const now = new Date().toLocaleTimeString();
                hardwareLog.append(`[${now}] ${msg}<br>`);
                hardwareLog.scrollTop(hardwareLog[0].scrollHeight);
            }

            if (!this.checked) {
                if (offlineBuffer.length === 0) {
                    addLog('   [SYSTEM] Internet restored. No pending logs to sync.');
                    return;
                }

                addLog(`   [SYSTEM] Internet restored. Synchronizing ${offlineBuffer.length} pending log(s)...`);
                displayStatus.text('SYNCING LOGS...').css('color', '#4cc9f0').addClass('blink');
                
                $.ajax({
                    url: 'api/sync_offline_logs.php',
                    method: 'POST',
                    data: JSON.stringify(offlineBuffer),
                    contentType: 'application/json',
                    success: function(res) {
                        if (res.success) {
                            addLog(`   [SYNC] Server Response: Success (${res.processed} processed).`);
                            addLog('   [SYNC] Local cache is now fully synchronized.');
                            offlineBuffer = []; // Clear buffer
                        } else {
                            addLog(`   [SYNC] Server Error: ${res.message}`);
                        }
                        displayStatus.text('READY FOR SCAN...').css('color', '#0f0').removeClass('blink');
                    },
                    error: function() {
                        addLog('   [SYNC] Fatal Error: Could not reach sync API.');
                        displayStatus.text('READY FOR SCAN...').css('color', '#0f0').removeClass('blink');
                    }
                });
            } else {
                addLog('   [SYSTEM] Disconnecting from server... ');
                addLog('   [SYSTEM] SYSTEM NOW RUNNING IN OFFLINE FALLBACK MODE. ');
            }
        });

        $(document).ready(function() {
            const form = $('#simulatorForm');
            const displayStatus = $('#displayStatus');
            const displayName = $('#displayName');
            const displayType = $('#displayType');
            const hardwareLog = $('#hardwareLog');
            const lightIn = $('#lightIn');
            const lightOut = $('#lightOut');

            function addLog(msg) {
                const now = new Date().toLocaleTimeString();
                hardwareLog.append(`[${now}] ${msg}<br>`);
                hardwareLog.scrollTop(hardwareLog[0].scrollHeight);
            }

            form.on('submit', function(e) {
                e.preventDefault();
                
                const btn = $('#simulateBtn');
                const token = $('#scanTarget').val();
                const action = $('input[name="action"]:checked').val();
                const deviceName = $('#deviceName').val();
                const selectedOpt = $('#scanTarget option:selected');
                const userName = selectedOpt.data('name');

                if (!token) {
                    Swal.fire('Error', 'Please select an identity to simulate.', 'error');
                    return;
                }

                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> EMITTING SIGNAL...');
                addLog(`EMITTING ${action} SIGNAL FOR: ${userName}`);
                
                // Show processing on display
                displayStatus.text('PROCESSING...').addClass('blink');
                displayName.text(userName.toUpperCase());

                // Simulate Offline Condition
                if ($('#simulateOffline').is(':checked')) {
                    setTimeout(() => {
                        addLog('   [OFFLINE] Internet unreachable. Switching to local database...');
                        addLog(`   [LOCAL]   Found: ${userName} (${selectedOpt.data('type')}). Access GRANTED (offline).`);
                        
                        // Push to temporary offline buffer for real sync later
                        offlineBuffer.push({
                            user_internal_id: selectedOpt.data('id'),
                            homeowner_id: selectedOpt.data('homeowner-id'),
                            name: userName,
                            user_type: selectedOpt.data('type').toLowerCase(),
                            action: action,
                            timestamp: new Date().toISOString().slice(0, 19).replace('T', ' '),
                            device_name: deviceName
                        });

                        addLog('   [LOCAL]   Offline log saved internally in buffer.');
                        
                        lightIn.addClass('active-in'); 
                        displayStatus.text('ACCESS GRANTED (OFFLINE)').css('color', '#0f0').removeClass('blink');
                        displayType.text('GATING IN OFFLINE MODE');

                        setTimeout(() => {
                            lightIn.removeClass('active-in');
                            displayStatus.text('READY FOR SCAN...').css('color', '#0f0');
                        }, 3000);

                        btn.prop('disabled', false).html('<i class="bi bi-lightning-fill me-2"></i> TRIGGER SCAN SIGNAL');
                    }, 1200);
                    return;
                }

                $.ajax({
                    url: 'api/auto_scan.php',
                    method: 'POST',
                    data: {
                        token: token,
                        action: action,
                        device_name: deviceName
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            addLog(`RELAY TRIGGERED: ${res.message}`);
                            
                            // Visual relay feedback
                            if (action === 'IN') lightIn.addClass('active-in');
                            else lightOut.addClass('active-out');

                            displayStatus.text('ACCESS GRANTED').css('color', '#0f0').removeClass('blink');
                            displayType.text(action === 'IN' ? 'WELCOME TO CIUDAD' : 'HAVE A SAFE TRIP');

                            setTimeout(() => {
                                lightIn.removeClass('active-in');
                                lightOut.removeClass('active-out');
                                displayStatus.text('READY FOR SCAN...').css('color', '#0f0');
                                displayType.text('WAITING FOR SIGNAL');
                            }, 3000);

                            Swal.fire({
                                icon: 'success',
                                title: action === 'IN' ? 'Welcome!' : 'Goodbye!',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            addLog(`ERROR: ${res.message}`);
                            displayStatus.text('ACCESS DENIED').css('color', '#f00').removeClass('blink');
                            displayType.text('REASON: ' + res.message.toUpperCase());
                            
                            setTimeout(() => {
                                displayStatus.text('READY FOR SCAN...').css('color', '#0f0');
                                displayType.text('WAITING FOR SIGNAL');
                            }, 3000);

                            Swal.fire('Denied', res.message, 'error');
                        }
                    },
                    error: function() {
                        addLog('CRITICAL ERROR: Failed to reach hardware API');
                        displayStatus.text('SYSTEM OFFLINE').css('color', '#f00');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="bi bi-lightning-fill me-2"></i> TRIGGER SCAN SIGNAL');
                    }
                });
            });
        });
    </script>
</body>
</html>
