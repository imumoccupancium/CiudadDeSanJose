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
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - Entry Monitor</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <style>
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
        .timeline-item {
            border-left: 2px solid #dee2e6;
            padding-left: 1.5rem;
            padding-bottom: 1.5rem;
            position: relative;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #0d6efd;
        }
        .timeline-item.entry::before {
            background: #198754;
        }
        .timeline-item.exit::before {
            background: #dc3545;
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
            <a href="dashboard.php" class="nav-link text-white-50 rounded mb-1">
                <i class="bi bi-grid-fill me-2"></i> Dashboard
            </a>
            <a href="homeowners.php" class="nav-link text-white-50 rounded mb-1">
                <i class="bi bi-people-fill me-2"></i> Homeowners
            </a>
            <a href="activity_logs.php" class="nav-link text-white active rounded mb-1">
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
                    <h5 class="mb-0">Activity Logs</h5>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 small">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Activity Logs</li>
                        </ol>
                    </nav>
                </div>
                
                <div class="d-flex align-items-center gap-2 ms-auto">
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

        <!-- Page Content -->
        <div class="container-fluid p-4">
            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded p-3 me-3">
                                    <i class="bi bi-activity fs-2"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0 fw-bold" id="totalLogs">0</h4>
                                    <small class="text-muted">Total Activities</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 text-success rounded p-3 me-3">
                                    <i class="bi bi-arrow-down-circle fs-2"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0 fw-bold" id="totalEntries">0</h4>
                                    <small class="text-muted">Entries Today</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="bg-danger bg-opacity-10 text-danger rounded p-3 me-3">
                                    <i class="bi bi-arrow-up-circle fs-2"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0 fw-bold" id="totalExits">0</h4>
                                    <small class="text-muted">Exits Today</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="bg-info bg-opacity-10 text-info rounded p-3 me-3">
                                    <i class="bi bi-people fs-2"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0 fw-bold" id="uniqueHomeowners">0</h4>
                                    <small class="text-muted">Unique Homeowners</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Filters and Table -->
                <div class="col-lg-8 mb-4">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small">Date From</label>
                                    <input type="date" class="form-control form-control-sm" id="dateFrom">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Date To</label>
                                    <input type="date" class="form-control form-control-sm" id="dateTo">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Action</label>
                                    <select class="form-select form-select-sm" id="actionFilter">
                                        <option value="">All</option>
                                        <option value="IN">Entry (IN)</option>
                                        <option value="OUT">Exit (OUT)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-primary btn-sm w-100" id="applyFilter">
                                            <i class="bi bi-funnel"></i> Filter
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm" id="clearFilter">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-list-ul text-primary"></i>
                                Activity Log Table
                            </h5>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-success" id="exportExcel">
                                    <i class="bi bi-file-earmark-excel"></i> Excel
                                </button>
                                <button class="btn btn-sm btn-outline-danger" id="exportPDF">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                </button>
                                <button class="btn btn-sm btn-outline-primary" id="refreshLogs">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="activityLogsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Homeowner</th>
                                            <th>Homeowner ID</th>
                                            <th>Action</th>
                                            <th>Date & Time</th>
                                            <th>Device</th>
                                            <th>Notes</th>
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

                <!-- Recent Activity Timeline -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-clock-history text-primary"></i>
                                Recent Activity
                            </h5>
                        </div>
                        <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                            <div id="recentTimeline">
                                <!-- Populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
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
        
        // Set default dates
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('dateTo').value = today;
        const weekAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        document.getElementById('dateFrom').value = weekAgo;
        
        // Initialize DataTable
        $(document).ready(function() {
            const table = $('#activityLogsTable').DataTable({
                ajax: {
                    url: 'api/get_activity_log.php',
                    dataSrc: ''
                },
                columns: [
                    { 
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    { data: 'homeowner_name' },
                    { data: 'homeowner_id' },
                    { 
                        data: 'action',
                        render: function(data) {
                            return data === 'IN' 
                                ? '<span class="badge bg-success"><i class="bi bi-arrow-down-circle"></i> ENTRY</span>'
                                : '<span class="badge bg-danger"><i class="bi bi-arrow-up-circle"></i> EXIT</span>';
                        }
                    },
                    { 
                        data: null,
                        render: function(data) {
                            return `${data.date} ${data.time}`;
                        }
                    },
                    { data: 'device' },
                    { 
                        data: 'notes',
                        render: function(data) {
                            return data || '-';
                        }
                    }
                ],
                order: [[4, 'desc']],
                pageLength: 25
            });
            
            // Update stats
            function updateStats() {
                $.get('api/get_activity_stats.php', function(data) {
                    $('#totalLogs').text(data.total_logs);
                    $('#totalEntries').text(data.total_entries);
                    $('#totalExits').text(data.total_exits);
                    $('#uniqueHomeowners').text(data.unique_homeowners);
                });
            }
            updateStats();
            
            // Load recent timeline
            function loadTimeline() {
                $.get('api/get_activity_log.php?limit=10', function(data) {
                    let html = '';
                    data.forEach(item => {
                        const actionClass = item.action === 'IN' ? 'entry' : 'exit';
                        const actionBadge = item.action === 'IN' 
                            ? '<span class="badge bg-success">ENTRY</span>'
                            : '<span class="badge bg-danger">EXIT</span>';
                        html += `
                            <div class="timeline-item ${actionClass}">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <strong>${item.homeowner_name}</strong>
                                    ${actionBadge}
                                </div>
                                <small class="text-muted d-block">${item.homeowner_id}</small>
                                <small class="text-muted d-block">
                                    <i class="bi bi-clock"></i> ${item.date} ${item.time}
                                </small>
                                <small class="text-muted d-block">
                                    <i class="bi bi-phone"></i> ${item.device}
                                </small>
                            </div>
                        `;
                    });
                    $('#recentTimeline').html(html);
                });
            }
            loadTimeline();
            
            // Auto-refresh every 30 seconds
            setInterval(function() {
                table.ajax.reload(null, false);
                updateStats();
                loadTimeline();
            }, 30000);
            
            // Refresh button
            $('#refreshLogs').click(function() {
                table.ajax.reload();
                updateStats();
                loadTimeline();
            });
            
            // Apply filters
            $('#applyFilter').click(function() {
                const dateFrom = $('#dateFrom').val();
                const dateTo = $('#dateTo').val();
                const action = $('#actionFilter').val();
                
                table.ajax.url(`api/get_activity_log.php?date_from=${dateFrom}&date_to=${dateTo}&action=${action}`).load();
            });
            
            // Clear filters
            $('#clearFilter').click(function() {
                $('#dateFrom').val(weekAgo);
                $('#dateTo').val(today);
                $('#actionFilter').val('');
                table.ajax.url('api/get_activity_log.php').load();
            });
            
            // Export functions
            $('#exportExcel').click(function() {
                alert('Excel export functionality will be implemented with backend API');
            });
            
            $('#exportPDF').click(function() {
                alert('PDF export functionality will be implemented with backend API');
            });
        });
    </script>
</body>
</html>
