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
    <title>Activity Logs - San Jose</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #1a1c1e 0%, #000 100%);
            z-index: 1050;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding-top: var(--top-navbar-height);
            transition: all 0.3s ease;
        }

        .top-navbar {
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            height: var(--top-navbar-height);
            background: var(--glass);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            z-index: 1000;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .nav-link {
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.7) !important;
            display: flex;
            align-items: center;
            gap: 12px;
            border-radius: 10px;
            transition: all 0.2s ease;
            margin: 4px 15px;
        }

        .nav-link i {
            font-size: 1.2rem;
            opacity: 0.8;
        }

        .nav-link:hover {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.05);
        }

        .nav-link.active {
            color: #fff !important;
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%) !important;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }

        .card {
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            background: #ffffff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            transition: transform 0.3s ease;
        }

        .timeline-item {
            border-left: 2px solid var(--primary-light);
            padding-left: 1.5rem;
            padding-bottom: 1.5rem;
            position: relative;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -7px;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary);
            border: 2px solid white;
            box-shadow: 0 0 0 4px var(--primary-light);
        }
        
        .timeline-item.entry::before { background: var(--success); box-shadow: 0 0 0 4px rgba(76, 201, 240, 0.2); }
        .timeline-item.exit::before { background: var(--danger); box-shadow: 0 0 0 4px rgba(239, 35, 60, 0.2); }

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
    </style>
</head>
<body class="bg-light">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="p-4 mb-3">
            <h4 class="text-white fw-bold mb-0">
                <i class="bi bi-qr-code-scan text-primary me-2"></i>
                San Jose
            </h4>
        </div>
        
        <nav class="nav flex-column mt-2">
            <a href="dashboard.php" class="nav-link">
                <i class="bi bi-grid-fill"></i> Dashboard
            </a>
            <a href="homeowners.php" class="nav-link">
                <i class="bi bi-people-fill"></i> Homeowners
            </a>
            <a href="activity_logs.php" class="nav-link active">
                <i class="bi bi-clock-history"></i> Activity Log
            </a>
            <div class="px-4 py-3 small text-uppercase text-white-50 fw-bold" style="letter-spacing: 1px;">Management</div>
            <a href="#" class="nav-link">
                <i class="bi bi-file-earmark-bar-graph"></i> Reports
            </a>
            <a href="#" class="nav-link">
                <i class="bi bi-shield-check"></i> Guards
            </a>
            <a href="#" class="nav-link">
                <i class="bi bi-phone-fill"></i> Scanners
            </a>
            <div class="px-4 py-3 small text-uppercase text-white-50 fw-bold" style="letter-spacing: 1px;">System</div>
            <a href="#" class="nav-link">
                <i class="bi bi-gear-fill"></i> Settings
            </a>
        </nav>
        
        <div class="mt-auto p-4">
            <div class="d-flex align-items-center mb-4 p-3 rounded-3 bg-white bg-opacity-10">
                <div class="flex-shrink-0 me-3">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 40px; height: 40px;">
                        <?php echo substr($user['name'], 0, 1); ?>
                    </div>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-bold text-white text-truncate"><?php echo htmlspecialchars($user['name']); ?></div>
                    <div class="text-white-50 small text-truncate"><?php echo htmlspecialchars($user['role']); ?></div>
                </div>
            </div>
            <a href="../auth/login.php" class="btn btn-outline-danger btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar d-flex align-items-center px-4">
            <button class="btn btn-link d-lg-none me-3" id="sidebarToggle">
                <i class="bi bi-list fs-3 text-dark"></i>
            </button>
            
            <div class="d-none d-md-block">
                <h5 class="mb-0 fw-bold">System Activity Logs</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-muted">Core</a></li>
                        <li class="breadcrumb-item active fw-medium">Audit Trail</li>
                    </ol>
                </nav>
            </div>
            
            <div class="ms-auto d-flex align-items-center gap-3">
                <button class="btn btn-light rounded-pill px-3" id="themeToggle">
                    <i class="bi bi-moon-stars"></i>
                </button>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="container-fluid p-4">
            <!-- Stats Row -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 p-2 text-center">
                        <div class="card-body">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary mx-auto mb-3">
                                <i class="bi bi-activity fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="totalLogs">0</h4>
                            <small class="text-muted fw-medium fs-7">Total Events</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 p-2 text-center">
                        <div class="card-body">
                            <div class="stat-icon bg-success bg-opacity-10 text-success mx-auto mb-3">
                                <i class="bi bi-box-arrow-in-right fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="totalEntries">0</h4>
                            <small class="text-muted fw-medium fs-7">Entries Today</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 p-2 text-center">
                        <div class="card-body">
                            <div class="stat-icon bg-danger bg-opacity-10 text-danger mx-auto mb-3">
                                <i class="bi bi-box-arrow-right fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="totalExits">0</h4>
                            <small class="text-muted fw-medium fs-7">Exits Today</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 p-2 text-center">
                        <div class="card-body">
                            <div class="stat-icon bg-info bg-opacity-10 text-info mx-auto mb-3">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="uniqueHomeowners">0</h4>
                            <small class="text-muted fw-medium fs-7">Active Residents</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mb-4">
                    <!-- Filters Card -->
                    <div class="card border-0 mb-4">
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-muted">From Date</label>
                                    <input type="date" class="form-control rounded-pill border-light bg-light" id="dateFrom">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-muted">To Date</label>
                                    <input type="date" class="form-control rounded-pill border-light bg-light" id="dateTo">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-muted">Movement</label>
                                    <select class="form-select rounded-pill border-light bg-light" id="actionFilter">
                                        <option value="">All Types</option>
                                        <option value="IN">Entry (IN)</option>
                                        <option value="OUT">Exit (OUT)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-muted">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-primary rounded-pill w-100" id="applyFilter">
                                            <i class="bi bi-filter"></i> Run Filter
                                        </button>
                                        <button class="btn btn-light rounded-pill" id="clearFilter">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Logs Table Card -->
                    <div class="card border-0">
                        <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold mb-1">Detailed Access Log</h5>
                                <p class="text-muted small mb-0">Complete history of subdivision crossings</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-light rounded-pill btn-sm px-3" id="exportExcel">
                                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Excel
                                </button>
                                <button class="btn btn-light rounded-pill btn-sm px-3" id="exportPDF">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                </button>
                                <button class="btn btn-primary rounded-pill btn-sm p-2 px-2" id="refreshLogs">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="activityLogsTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 py-3 text-uppercase small fw-bold text-muted border-0">#</th>
                                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Homeowner</th>
                                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">ID Number</th>
                                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Action</th>
                                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Timestamp</th>
                                            <th class="py-3 text-uppercase small fw-bold text-muted border-0 pe-4">Scanner</th>
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

                <!-- Timeline Section -->
                <div class="col-lg-4">
                    <div class="card border-0">
                        <div class="card-header bg-white border-0 py-4 px-4">
                            <h5 class="fw-bold mb-1">Live Feed</h5>
                            <p class="text-muted small mb-0">Most recent community movements</p>
                        </div>
                        <div class="card-body p-4 pt-0" style="max-height: 700px; overflow-y: auto;">
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
        
        // Date defaults
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
                        render: (d, t, r, m) => `<span class="text-muted small ps-2">${m.row + 1}</span>`
                    },
                    { 
                        data: 'homeowner_name',
                        render: d => `<span class="fw-bold text-dark">${d}</span>`
                    },
                    { 
                        data: 'homeowner_id',
                        render: d => `<span class="text-primary small fw-medium">${d}</span>`
                    },
                    { 
                        data: 'action',
                        render: function(data) {
                            const badgeClass = data === 'IN' ? 'bg-success' : 'bg-danger';
                            const icon = data === 'IN' ? 'bi-box-arrow-in-right' : 'bi-box-arrow-right';
                            return `<span class="badge ${badgeClass} bg-opacity-10 text-${data === 'IN' ? 'success' : 'danger'} rounded-pill px-3 py-2 fw-bold" style="font-size: 0.7rem;">
                                <i class="bi ${icon} me-1"></i> ${data === 'IN' ? 'ENTRY' : 'EXIT'}
                            </span>`;
                        }
                    },
                    { 
                        data: null,
                        render: d => `<div class="small fw-medium text-dark">${d.date}</div><div class="smaller text-muted">${d.time}</div>`
                    },
                    { 
                        data: 'device',
                        render: d => `<span class="badge bg-light text-dark border"><i class="bi bi-phone me-1"></i> ${d}</span>`
                    }
                ],
                order: [[4, 'desc']],
                pageLength: 20,
                dom: 'trtp',
                language: {
                    paginate: {
                        next: '<i class="bi bi-chevron-right"></i>',
                        previous: '<i class="bi bi-chevron-left"></i>'
                    }
                }
            });
            
            function updateStats() {
                $.get('api/get_activity_stats.php', function(data) {
                    $('#totalLogs').text(data.total_logs);
                    $('#totalEntries').text(data.total_entries);
                    $('#totalExits').text(data.total_exits);
                    $('#uniqueHomeowners').text(data.unique_homeowners);
                });
            }
            updateStats();
            
            function loadTimeline() {
                $.get('api/get_activity_log.php?limit=12', function(data) {
                    let html = '';
                    data.forEach(item => {
                        const actionClass = item.action === 'IN' ? 'entry' : 'exit';
                        const icon = item.action === 'IN' ? 'bi-arrow-down-right-circle-fill' : 'bi-arrow-up-right-circle-fill';
                        const color = item.action === 'IN' ? 'success' : 'danger';
                        html += `
                            <div class="timeline-item ${actionClass}">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="fw-bold text-dark">${item.homeowner_name}</span>
                                    <span class="text-${color} small fw-bold">
                                        <i class="bi ${icon} me-1"></i> ${item.action}
                                    </span>
                                </div>
                                <div class="small text-muted mb-2">${item.homeowner_id}</div>
                                <div class="d-flex align-items-center gap-3 mt-1">
                                    <span class="smaller bg-light rounded px-2 text-muted fw-medium py-1">
                                        <i class="bi bi-clock me-1"></i> ${item.time}
                                    </span>
                                    <span class="smaller text-muted">
                                        <i class="bi bi-geo-alt me-1"></i> ${item.device}
                                    </span>
                                </div>
                            </div>
                        `;
                    });
                    $('#recentTimeline').html(html);
                });
            }
            loadTimeline();
            
            // Interaction Handlers
            $('#refreshLogs').click(() => { table.ajax.reload(); updateStats(); loadTimeline(); });
            
            $('#applyFilter').click(() => {
                const url = `api/get_activity_log.php?date_from=${$('#dateFrom').val()}&date_to=${$('#dateTo').val()}&action=${$('#actionFilter').val()}`;
                table.ajax.url(url).load();
            });
            
            $('#clearFilter').click(() => {
                $('#dateFrom').val(weekAgo);
                $('#dateTo').val(today);
                $('#actionFilter').val('');
                table.ajax.url('api/get_activity_log.php').load();
            });

            setInterval(() => { table.ajax.reload(null, false); updateStats(); loadTimeline(); }, 30000);
            
            $('#exportExcel, #exportPDF').click(function() {
                alert('Export feature connected to backend API. Service available in production.');
            });
        });
    </script>
</body>
</html>
