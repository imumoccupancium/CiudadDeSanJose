<?php
session_start();

// Database connection
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user = [
    'name' => $_SESSION['user_name'] ?? 'Admin User',
    'email' => $_SESSION['user_email'] ?? 'admin@ciudaddesanjose.com',
    'role' => $_SESSION['user_role'] ?? 'Administrator'
];

// Fetch dashboard statistics
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM homeowners WHERE status = 'active'");
    $totalHomeowners = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM homeowners WHERE current_status = 'IN' AND status = 'active'");
    $currentlyInside = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    $currentlyOutside = $totalHomeowners - $currentlyInside;
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM entry_logs WHERE action = 'IN' AND DATE(timestamp) = CURDATE()");
    $totalEntriesToday = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM entry_logs WHERE action = 'OUT' AND DATE(timestamp) = CURDATE()");
    $totalExitsToday = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
} catch (PDOException $e) {
    $totalHomeowners = 0;
    $currentlyInside = 0;
    $currentlyOutside = 0;
    $totalEntriesToday = 0;
    $totalExitsToday = 0;
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Subdivision Entry & Exit Monitoring</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- DataTables Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: rgba(67, 97, 238, 0.1);
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f72585;
            --danger: #ef233c;
            --dark: #212529;
            --light: #f8f9fa;
            --glass: rgba(255, 255, 255, 0.7);
            --sidebar-width: 260px;
            --top-navbar-height: 70px;
            --border-radius: 12px;
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        }

        [data-bs-theme="dark"] {
            --glass: rgba(33, 37, 41, 0.7);
            --light: #121417;
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--light);
            color: var(--dark);
            overflow-x: hidden;
        }

        .card {
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            background: #ffffff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card {
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            background: #ffffff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08) !important;
        }

        .stat-icon {
            width: 65px;
            height: 65px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            transition: transform 0.3s ease;
        }

        .card:hover .stat-icon {
            transform: scale(1.1);
        }

        .display-4 {
            font-weight: 800;
            letter-spacing: -1px;
            background: linear-gradient(135deg, var(--dark) 0%, #444 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .fab {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.4);
            border: none;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1100;
        }

        .fab:hover {
            transform: scale(1.1) rotate(90deg);
            box-shadow: 0 15px 30px rgba(67, 97, 238, 0.6);
        }

        @media (max-width: 992px) {
            .sidebar {
                left: calc(var(--sidebar-width) * -1);
            }
            .sidebar.show {
                left: 0;
            }
            .main-content {
                margin-left: 0;
            }
            .top-navbar {
                left: 0;
            }
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .glass-effect {
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            background: rgba(255, 255, 255, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
        }

        [data-bs-theme="dark"] .glass-effect {
            background: rgba(33, 37, 41, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
            100% { opacity: 1; transform: scale(1); }
        }

        .pulse {
            animation: pulse 2s infinite;
            display: inline-block;
        }

        .ls-1 { letter-spacing: 1px; }
    </style>
</head>
<body class="bg-light">
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar d-flex align-items-center px-4">
            <button class="btn btn-link d-lg-none me-3" id="sidebarToggle">
                <i class="bi bi-list fs-3 text-dark"></i>
            </button>
            
            <div class="d-none d-md-block">
                <h5 class="mb-0 fw-bold">Dashboard Overview</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Core</a></li>
                        <li class="breadcrumb-item active fw-medium">Analytics</li>
                    </ol>
                </nav>
            </div>
            
            <div class="ms-auto d-flex align-items-center gap-3">
                <div class="input-group d-none d-lg-flex" style="width: 300px;">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control bg-transparent border-start-0 ps-0" placeholder="Search data...">
                </div>
                
                <div class="dropdown">
                    <button class="btn btn-light rounded-pill position-relative px-3" data-bs-toggle="dropdown">
                        <i class="bi bi-bell text-primary"></i>
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-3" style="width: 300px;">
                        <li class="px-3 py-2 border-bottom fw-bold small">Notifications</li>
                        <li><a class="dropdown-item py-3 border-bottom" href="#">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-exclamation-circle text-warning me-3"></i>
                                <div>
                                    <div class="small fw-bold">Guard House Scanner Offline</div>
                                    <div class="text-muted smaller">1 hour ago</div>
                                </div>
                            </div>
                        </a></li>
                        <li><a class="dropdown-item text-center small text-primary py-2" href="#">View all alerts</a></li>
                    </ul>
                </div>
                
                <button class="btn btn-light rounded-pill px-3" id="themeToggle">
                    <i class="bi bi-moon-stars"></i>
                </button>
            </div>
        </nav>
        <div class="container-fluid p-4 pt-0">
            <!-- Synced Minimal Real-time Clock Section -->
            <div class="row g-0 mb-5 mt-3 py-4 border-bottom align-items-center">
                <div class="col-md-4">
                    <h5 class="mb-0 text-muted fw-bold" id="dateDisplay">Loading date...</h5>
                </div>
                <div class="col-md-4 text-md-center">
                    <h3 class="mb-0 fw-black text-dark text-uppercase" style="letter-spacing: 4px; font-weight: 800;" id="dayDisplay">---</h3>
                </div>
                <div class="col-md-4 text-md-end">
                    <h4 class="mb-0 fw-bold font-monospace text-primary ls-1" id="timeDisplay">00:00:00 --</h4>
                </div>
            </div>

            <!-- Essential Summary - Today's Activity -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card border-0 hover-lift h-100 p-2">
                        <div class="card-body text-center">
                            <div class="stat-icon bg-success bg-opacity-10">
                                <i class="bi bi-arrow-down-square-fill text-success" style="font-size: 2rem;"></i>
                            </div>
                            <h1 class="display-4 mb-1"><?php echo $totalEntriesToday; ?></h1>
                            <h6 class="text-muted text-uppercase small fw-bold mb-3" style="letter-spacing: 1px;">Entries Today</h6>
                            <div class="badge rounded-pill bg-success bg-opacity-10 text-success p-2 px-3">
                                <i class="bi bi-graph-up me-1"></i> +5% from yesterday
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 hover-lift h-100 p-2">
                        <div class="card-body text-center">
                            <div class="stat-icon bg-danger bg-opacity-10">
                                <i class="bi bi-arrow-up-square-fill text-danger" style="font-size: 2rem;"></i>
                            </div>
                            <h1 class="display-4 mb-1"><?php echo $totalExitsToday; ?></h1>
                            <h6 class="text-muted text-uppercase small fw-bold mb-3" style="letter-spacing: 1px;">Exits Today</h6>
                            <div class="badge rounded-pill bg-danger bg-opacity-10 text-danger p-2 px-3">
                                <i class="bi bi-graph-down me-1"></i> -2% from yesterday
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 hover-lift h-100 p-2">
                        <div class="card-body text-center">
                            <div class="stat-icon bg-primary bg-opacity-10">
                                <i class="bi bi-people-fill text-primary" style="font-size: 2rem;"></i>
                            </div>
                            <h1 class="display-4 mb-1"><?php echo $totalHomeowners; ?></h1>
                            <h6 class="text-muted text-uppercase small fw-bold mb-3" style="letter-spacing: 1px;">Total Homeowners</h6>
                            <div class="badge rounded-pill bg-primary bg-opacity-10 text-primary p-2 px-3">
                                <i class="bi bi-check-circle-fill me-1"></i> All active
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Status Overview -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card border-0 p-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold mb-0">Live Occupancy Status</h5>
                                <span class="badge bg-primary bg-opacity-10 text-primary p-2 px-3">Real-time update</span>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-4 rounded-4 bg-light border border-white">
                                        <div class="flex-shrink-0 me-4">
                                            <div class="stat-icon bg-success shadow-sm mb-0">
                                                <i class="bi bi-house-check text-white fs-3"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h2 class="mb-0 fw-bold"><?php echo $currentlyInside; ?></h2>
                                            <p class="mb-0 text-muted fw-medium">Homeowners Inside</p>
                                        </div>
                                        <div class="ms-auto text-end">
                                            <div class="text-success small fw-bold"><?php echo round(($currentlyInside/$totalHomeowners)*100); ?>%</div>
                                            <div class="progress mt-1" style="width: 60px; height: 4px;">
                                                <div class="progress-bar bg-success" style="width: <?php echo ($currentlyInside/$totalHomeowners)*100; ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-4 rounded-4 bg-light border border-white">
                                        <div class="flex-shrink-0 me-4">
                                            <div class="stat-icon bg-warning shadow-sm mb-0">
                                                <i class="bi bi-house-dash text-white fs-3"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h2 class="mb-0 fw-bold"><?php echo $currentlyOutside; ?></h2>
                                            <p class="mb-0 text-muted fw-medium">Homeowners Outside</p>
                                        </div>
                                        <div class="ms-auto text-end">
                                            <div class="text-warning small fw-bold"><?php echo round(($currentlyOutside/$totalHomeowners)*100); ?>%</div>
                                            <div class="progress mt-1" style="width: 60px; height: 4px;">
                                                <div class="progress-bar bg-warning" style="width: <?php echo ($currentlyOutside/$totalHomeowners)*100; ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Table Refined -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card border-0">
                        <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold mb-1">Recent Access Logs</h5>
                                <p class="text-muted small mb-0">Last entries and exits tracked by gate scanners</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-light rounded-pill btn-sm px-3" id="refreshActivityLog">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Reload
                                </button>
                                <a href="activity_logs.php" class="btn btn-primary rounded-pill btn-sm px-4">
                                    Full Log
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="activityLogTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 py-3 text-uppercase small fw-bold text-muted border-0">Homeowner</th>
                                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">ID Number</th>
                                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Movement</th>
                                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Date</th>
                                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Time</th>
                                            <th class="py-3 text-uppercase small fw-bold text-muted border-0 pe-4 text-center">Scanner</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-0">
                                        <!-- Populated by DataTables -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links with better icons -->
            <div class="row mb-5">
                <div class="col-12 mb-4">
                    <h5 class="fw-bold">Shortcuts & Tools</h5>
                </div>
                <div class="col-6 col-lg-3 mb-4">
                    <a href="homeowners.php" class="text-decoration-none">
                        <div class="card border-0 text-center py-4 hover-lift">
                            <div class="card-body">
                                <div class="bg-primary bg-opacity-10 rounded-4 p-3 d-inline-block mb-3">
                                    <i class="bi bi-person-gear text-primary fs-3"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">User Management</h6>
                                <p class="small text-muted mb-0">Add/Edit Homeowners</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-lg-3 mb-4">
                    <a href="activity_logs.php" class="text-decoration-none">
                        <div class="card border-0 text-center py-4 hover-lift">
                            <div class="card-body">
                                <div class="bg-success bg-opacity-10 rounded-4 p-3 d-inline-block mb-3">
                                    <i class="bi bi-journal-text text-success fs-3"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">Historic Logs</h6>
                                <p class="small text-muted mb-0">Browse all records</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-lg-3 mb-4">
                    <a href="#" class="text-decoration-none">
                        <div class="card border-0 text-center py-4 hover-lift">
                            <div class="card-body">
                                <div class="bg-info bg-opacity-10 rounded-4 p-3 d-inline-block mb-3">
                                    <i class="bi bi-bar-chart-steps text-info fs-3"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">Data Reports</h6>
                                <p class="small text-muted mb-0">Export PDF/Excel</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-lg-3 mb-4">
                    <a href="#" class="text-decoration-none">
                        <div class="card border-0 text-center py-4 hover-lift">
                            <div class="card-body">
                                <div class="bg-dark bg-opacity-10 rounded-4 p-3 d-inline-block mb-3">
                                    <i class="bi bi-sliders text-dark fs-3"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">Preferences</h6>
                                <p class="small text-muted mb-0">System configuration</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <button class="fab" id="quickActionBtn" data-bs-toggle="modal" data-bs-target="#addHomeownerModal" title="Quick Add Homeowner">
        <i class="bi bi-plus-lg"></i>
    </button>
</div> <!-- closes main-content -->

    <!-- Add Homeowner Modal -->
    <div class="modal fade" id="addHomeownerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-plus text-primary me-2"></i>
                        Register Homeowner
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="addHomeownerForm">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Full Name</label>
                            <input type="text" class="form-control rounded-3 p-2 px-3" placeholder="John Doe" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">Homeowner ID</label>
                                <input type="text" class="form-control rounded-3 p-2 px-3" placeholder="CSJ-2024-001" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">Phone Number</label>
                                <input type="tel" class="form-control rounded-3 p-2 px-3" placeholder="+63..." required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Email Address</label>
                            <input type="email" class="form-control rounded-3 p-2 px-3" placeholder="john@example.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Home Address</label>
                            <textarea class="form-control rounded-3 p-2 px-3" rows="2" placeholder="Block & Lot Details" required></textarea>
                        </div>
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" id="generateQR" checked>
                            <label class="form-check-label small fw-bold" for="generateQR">
                                Automatically generate secure QR access key
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Discard</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4">
                        Confirm & Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    <script>
        // Mobile sidebar toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });
        
        // Theme toggle logic refined
        document.getElementById('themeToggle').addEventListener('click', function() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', newTheme);
            
            const icon = this.querySelector('i');
            if (newTheme === 'dark') {
                icon.className = 'bi bi-sun-fill';
                this.classList.replace('btn-light', 'btn-dark');
            } else {
                icon.className = 'bi bi-moon-stars';
                this.classList.replace('btn-dark', 'btn-light');
            }
        });

        // REAL-TIME SYNCED CLOCK SYSTEM
        let timeOffset = 0;

        async function syncWithNetworkTime() {
            const syncStatus = document.getElementById('syncStatus');
            const timeDisplay = document.getElementById('timeDisplay');
            
            try {
                // Fetching from a reliable Time API
                const response = await fetch('https://worldtimeapi.org/api/timezone/Asia/Manila');
                if (!response.ok) throw new Error('Network response was not ok');
                const data = await response.json();
                
                const networkTime = new Date(data.datetime).getTime();
                const localTime = Date.now();
                
                // Calculate offset between local system and network time
                timeOffset = networkTime - localTime;
                
                if (syncStatus) {
                    syncStatus.innerHTML = '<i class="bi bi-dot text-success fs-5"></i> Synced';
                    syncStatus.classList.add('bg-success', 'bg-opacity-10', 'text-success');
                }
                updateRealTimeClock();
            } catch (error) {
                console.warn('Network time sync failed, using calibrated system time.', error);
                if (syncStatus) {
                    syncStatus.innerHTML = '<i class="bi bi-dot text-warning fs-5"></i> Local';
                }
            }
        }

        function updateRealTimeClock() {
            const now = new Date(Date.now() + timeOffset);
            
            // 1. Date Section (e.g., January 31, 2026)
            const dateOptions = { year: 'numeric', month: 'long', day: 'numeric' };
            const dateStr = now.toLocaleDateString('en-US', dateOptions);
            
            // 2. Day Section (e.g., Saturday)
            const dayOptions = { weekday: 'long' };
            const dayStr = now.toLocaleDateString('en-US', dayOptions);
            
            // 3. Time Section (e.g., 11:45:00 AM)
            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            const timeString = `${String(hours).padStart(2, '0')}:${minutes}:${seconds} ${ampm}`;
            
            // Update DOM elements
            const dateEl = document.getElementById('dateDisplay');
            const dayEl = document.getElementById('dayDisplay');
            const timeEl = document.getElementById('timeDisplay');

            if (dateEl) dateEl.textContent = dateStr;
            if (dayEl) dayEl.textContent = dayStr;
            if (timeEl) timeEl.textContent = timeString;
        }

        // Initial sync and start interval
        syncWithNetworkTime();
        setInterval(updateRealTimeClock, 1000);
        // Periodic re-sync every 15 minutes
        setInterval(syncWithNetworkTime, 900000);
        
        // Initialize DataTables with custom row styling
        $(document).ready(function() {
            const activityTable = $('#activityLogTable').DataTable({
                ajax: {
                    url: 'api/get_activity_log.php',
                    dataSrc: ''
                },
                columns: [
                    { 
                        data: 'homeowner_name',
                        render: function(data) {
                            return `<div class="d-flex align-items-center ps-2">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                                    <i class="bi bi-person"></i>
                                </div>
                                <span class="fw-bold text-dark">${data}</span>
                            </div>`;
                        }
                    },
                    { data: 'homeowner_id' },
                    { 
                        data: 'action',
                        render: function(data) {
                            const badgeClass = data === 'IN' ? 'bg-success' : 'bg-danger';
                            const icon = data === 'IN' ? 'bi-box-arrow-in-right' : 'bi-box-arrow-right';
                            return `<span class="badge ${badgeClass} bg-opacity-10 text-${data === 'IN' ? 'success' : 'danger'} rounded-pill p-2 px-3 fw-bold small">
                                <i class="bi ${icon} me-1"></i> ${data}
                            </span>`;
                        }
                    },
                    { data: 'date' },
                    { data: 'time' },
                    { 
                        data: 'device',
                        render: function(data) {
                            return `<div class="text-center small text-muted"><i class="bi bi-phone me-1"></i> ${data}</div>`;
                        }
                    }
                ],
                order: [[3, 'desc'], [4, 'desc']],
                pageLength: 8,
                lengthChange: false,
                info: false,
                dom: 'tp' // Simplified DataTables UI
            });
            
            // Auto-refresh every 30 seconds
            setInterval(function() {
                activityTable.ajax.reload(null, false);
            }, 30000);
            
            // Manual refresh
            $('#refreshActivityLog').click(function() {
                activityTable.ajax.reload();
            });
        });
        </script>
</body>
</html>
