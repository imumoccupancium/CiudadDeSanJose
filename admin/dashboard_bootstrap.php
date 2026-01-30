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
    
    <style>
        /* Minimal custom styles - mostly Bootstrap utilities */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: linear-gradient(180deg, #212529 0%, #000 100%);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Sidebar -->
    <div class="sidebar text-white" id="sidebar">
        <div class="p-3 border-bottom border-secondary">
            <h5 class="mb-0">
                <i class="bi bi-qr-code-scan text-primary"></i>
                Entry Monitor
            </h5>
        </div>
        
        <nav class="nav flex-column p-2">
            <a href="#" class="nav-link text-white active rounded mb-1">
                <i class="bi bi-grid-fill me-2"></i> Dashboard
            </a>
            <a href="#" class="nav-link text-white-50 rounded mb-1">
                <i class="bi bi-people-fill me-2"></i> Homeowners
            </a>
            <a href="#" class="nav-link text-white-50 rounded mb-1">
                <i class="bi bi-clock-history me-2"></i> Activity Log
            </a>
            <a href="#" class="nav-link text-white-50 rounded mb-1">
                <i class="bi bi-file-earmark-bar-graph me-2"></i> Reports
            </a>
            <a href="#" class="nav-link text-white-50 rounded mb-1">
                <i class="bi bi-shield-check me-2"></i> Guards
            </a>
            <a href="#" class="nav-link text-white-50 rounded mb-1">
                <i class="bi bi-phone-fill me-2"></i> Scanners
            </a>
            <a href="#" class="nav-link text-white-50 rounded mb-1">
                <i class="bi bi-journal-text me-2"></i> Audit Log
            </a>
            <a href="#" class="nav-link text-white-50 rounded mb-1">
                <i class="bi bi-gear-fill me-2"></i> Settings
            </a>
        </nav>
        
        <div class="mt-auto p-3 border-top border-secondary">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-person-circle fs-2 text-primary me-2"></i>
                <div class="small">
                    <div class="fw-bold"><?php echo htmlspecialchars($user['name']); ?></div>
                    <div class="text-white-50"><?php echo htmlspecialchars($user['role']); ?></div>
                </div>
            </div>
            <a href="../auth/login.php" class="btn btn-outline-danger btn-sm w-100">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
            <div class="container-fluid">
                <button class="btn btn-link d-md-none" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
                
                <div>
                    <h5 class="mb-0">Dashboard</h5>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 small">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </nav>
                </div>
                
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <div class="input-group" style="width: 250px;">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search...">
                    </div>
                    <button class="btn btn-light position-relative">
                        <i class="bi bi-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span>
                    </button>
                    <button class="btn btn-light" id="themeToggle">
                        <i class="bi bi-moon"></i>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Dashboard Content -->
        <div class="container-fluid p-4">
            <!-- Stats Cards Row 1 -->
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                        <i class="bi bi-people fs-2"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="mb-0 fw-bold"><?php echo $totalHomeowners; ?></h3>
                                    <p class="text-muted mb-0 small">Total Homeowners</p>
                                    <small class="text-muted"><i class="bi bi-person-check"></i> Active accounts</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                        <i class="bi bi-house-check-fill fs-2"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="mb-0 fw-bold"><?php echo $currentlyInside; ?></h3>
                                    <p class="text-muted mb-0 small">Currently Inside</p>
                                    <small class="text-success"><i class="bi bi-arrow-down-circle"></i> In subdivision</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                                        <i class="bi bi-house-dash-fill fs-2"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="mb-0 fw-bold"><?php echo $currentlyOutside; ?></h3>
                                    <p class="text-muted mb-0 small">Currently Outside</p>
                                    <small class="text-muted"><i class="bi bi-arrow-up-circle"></i> Away from home</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-info bg-opacity-10 text-info rounded p-3">
                                        <i class="bi bi-arrow-down-square-fill fs-2"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="mb-0 fw-bold"><?php echo $totalEntriesToday; ?></h3>
                                    <p class="text-muted mb-0 small">Entries Today</p>
                                    <small class="text-info"><i class="bi bi-calendar-check"></i> <?php echo date('M d, Y'); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards Row 2 -->
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-danger bg-opacity-10 text-danger rounded p-3">
                                        <i class="bi bi-arrow-up-square-fill fs-2"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="mb-0 fw-bold"><?php echo $totalExitsToday; ?></h3>
                                    <p class="text-muted mb-0 small">Exits Today</p>
                                    <small class="text-muted"><i class="bi bi-calendar-check"></i> <?php echo date('M d, Y'); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                        <i class="bi bi-phone-fill fs-2"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="mb-0 fw-bold">2</h3>
                                    <p class="text-muted mb-0 small">Active Scanners</p>
                                    <small class="text-success"><i class="bi bi-check-circle"></i> All online</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                        <i class="bi bi-shield-check fs-2"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="mb-0 fw-bold">4</h3>
                                    <p class="text-muted mb-0 small">Active Guards</p>
                                    <small class="text-success"><i class="bi bi-person-badge"></i> On duty</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-info bg-opacity-10 text-info rounded p-3">
                                        <i class="bi bi-activity fs-2"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="mb-0 fw-bold"><?php echo $totalEntriesToday + $totalExitsToday; ?></h3>
                                    <p class="text-muted mb-0 small">Total Scans Today</p>
                                    <small class="text-info"><i class="bi bi-graph-up"></i> All activities</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Real-time Activity Log -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-clock-history text-primary"></i>
                                Real-Time Activity Log
                            </h5>
                            <button class="btn btn-sm btn-outline-primary" id="refreshActivityLog">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="activityLogTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Homeowner Name</th>
                                            <th>Homeowner ID</th>
                                            <th>Action</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Scanner Device</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Populated by DataTables -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Homeowner Status List -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-people text-primary"></i>
                                Homeowner Status
                            </h5>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary active" data-filter="all">All</button>
                                <button type="button" class="btn btn-outline-success" data-filter="in">Inside</button>
                                <button type="button" class="btn btn-outline-danger" data-filter="out">Outside</button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="homeownerStatusTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Homeowner ID</th>
                                            <th>Current Status</th>
                                            <th>Last Scan Time</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Populated by DataTables -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row mb-4">
                <div class="col-lg-8 mb-3 mb-lg-0">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-graph-up text-primary"></i>
                                Entry & Exit Analytics
                            </h5>
                            <select class="form-select form-select-sm w-auto" id="chartPeriod">
                                <option value="7">Last 7 Days</option>
                                <option value="30" selected>Last 30 Days</option>
                                <option value="90">Last 90 Days</option>
                            </select>
                        </div>
                        <div class="card-body">
                            <canvas id="entryExitChart" height="80"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-pie-chart-fill text-primary"></i>
                                Status Distribution
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scanner Devices -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-phone text-primary"></i>
                                Scanner Device Status
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-3">
                                    <div class="alert alert-success mb-0">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-circle-fill text-success me-2"></i>
                                            <div>
                                                <strong>Main Gate Scanner</strong>
                                                <p class="mb-0 small">Last scan: 2 minutes ago</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="alert alert-success mb-0">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-circle-fill text-success me-2"></i>
                                            <div>
                                                <strong>Back Gate Scanner</strong>
                                                <p class="mb-0 small">Last scan: 5 minutes ago</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="alert alert-danger mb-0">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-circle-fill text-danger me-2"></i>
                                            <div>
                                                <strong>Guard House Scanner</strong>
                                                <p class="mb-0 small">Offline since 1 hour ago</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="alert alert-success mb-0">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-circle-fill text-success me-2"></i>
                                            <div>
                                                <strong>Mobile Scanner 1</strong>
                                                <p class="mb-0 small">Last scan: 10 minutes ago</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <button class="btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-4 shadow-lg" style="width: 60px; height: 60px;" data-bs-toggle="modal" data-bs-target="#addHomeownerModal">
        <i class="bi bi-plus-lg fs-4"></i>
    </button>

    <!-- Add Homeowner Modal -->
    <div class="modal fade" id="addHomeownerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus"></i>
                        Add New Homeowner
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addHomeownerForm">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Homeowner ID</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="generateQR" checked>
                            <label class="form-check-label" for="generateQR">
                                Generate QR Code automatically
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Homeowner
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
        
        // Theme toggle
        document.getElementById('themeToggle').addEventListener('click', function() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', newTheme);
            this.querySelector('i').className = newTheme === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
        });
        
        // Initialize DataTables
        $(document).ready(function() {
            const activityTable = $('#activityLogTable').DataTable({
                ajax: {
                    url: 'api/get_activity_log.php',
                    dataSrc: ''
                },
                columns: [
                    { data: 'homeowner_name' },
                    { data: 'homeowner_id' },
                    { 
                        data: 'action',
                        render: function(data) {
                            return data === 'IN' 
                                ? '<span class="badge bg-success"><i class="bi bi-arrow-down-circle"></i> IN</span>'
                                : '<span class="badge bg-danger"><i class="bi bi-arrow-up-circle"></i> OUT</span>';
                        }
                    },
                    { data: 'date' },
                    { data: 'time' },
                    { data: 'device' }
                ],
                order: [[3, 'desc'], [4, 'desc']],
                pageLength: 10
            });
            
            const statusTable = $('#homeownerStatusTable').DataTable({
                ajax: {
                    url: 'api/get_homeowner_status.php',
                    dataSrc: ''
                },
                columns: [
                    { data: 'name' },
                    { data: 'homeowner_id' },
                    { 
                        data: 'current_status',
                        render: function(data) {
                            return data === 'IN'
                                ? '<span class="badge bg-success"><i class="bi bi-house-check"></i> INSIDE</span>'
                                : '<span class="badge bg-danger"><i class="bi bi-house-dash"></i> OUTSIDE</span>';
                        }
                    },
                    { data: 'last_scan_time' },
                    {
                        data: null,
                        render: function(data) {
                            return `
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            `;
                        }
                    }
                ],
                pageLength: 10
            });
            
            // Auto-refresh every 30 seconds
            setInterval(function() {
                activityTable.ajax.reload(null, false);
                statusTable.ajax.reload(null, false);
            }, 30000);
            
            // Manual refresh
            $('#refreshActivityLog').click(function() {
                activityTable.ajax.reload();
            });
            
            // Filter buttons
            $('[data-filter]').click(function() {
                const filter = $(this).data('filter');
                if (filter === 'all') {
                    statusTable.search('').draw();
                } else if (filter === 'in') {
                    statusTable.search('INSIDE').draw();
                } else {
                    statusTable.search('OUTSIDE').draw();
                }
                $('[data-filter]').removeClass('active');
                $(this).addClass('active');
            });
        });
        
        // Charts
        const ctx1 = document.getElementById('entryExitChart').getContext('2d');
        const entryExitChart = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Entries',
                    data: [],
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Exits',
                    data: [],
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true
            }
        });
        
        const ctx2 = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Inside', 'Outside'],
                datasets: [{
                    data: [<?php echo $currentlyInside; ?>, <?php echo $currentlyOutside; ?>],
                    backgroundColor: ['#198754', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true
            }
        });
        
        // Load chart data
        function loadChartData(period) {
            fetch(`api/get_chart_data.php?period=${period}`)
                .then(r => r.json())
                .then(data => {
                    entryExitChart.data.labels = data.labels;
                    entryExitChart.data.datasets[0].data = data.entries;
                    entryExitChart.data.datasets[1].data = data.exits;
                    entryExitChart.update();
                });
        }
        
        document.getElementById('chartPeriod').addEventListener('change', function() {
            loadChartData(this.value);
        });
        
        loadChartData(30);
    </script>
</body>
</html>
