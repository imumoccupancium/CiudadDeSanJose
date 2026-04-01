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
    <title>Visitor Activity - Ciudad De San Jose</title>
    <!-- Website Icon -->
    <link rel="icon" type="image/png" href="../assets/logo.png">

    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/vendor/datatables/css/dataTables.bootstrap5.min.css">
    
    <!-- Local Fonts (Inter) -->
    <link rel="stylesheet" href="../assets/vendor/fonts/inter/inter.css">
    
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

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding-top: var(--top-navbar-height);
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

        .top-navbar {
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            height: var(--top-navbar-height);
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            z-index: 1000;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
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
                <h5 class="mb-0 fw-bold">Visitor Activity Logs</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-muted">Core</a></li>
                        <li class="breadcrumb-item active fw-medium">Visitor Feed</li>
                    </ol>
                </nav>
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
                            <small class="text-muted fw-medium fs-7">Total scans</small>
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
                            <h4 class="mb-0 fw-bold" id="uniqueVisitors">0</h4>
                            <small class="text-muted fw-medium fs-7">Active Visitors</small>
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
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-muted">From Date</label>
                                    <input type="date" class="form-control rounded-pill border-light bg-light" id="dateFrom">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-muted">To Date</label>
                                    <input type="date" class="form-control rounded-pill border-light bg-light" id="dateTo">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-muted">Movement</label>
                                    <select class="form-select rounded-pill border-light bg-light" id="actionFilter">
                                        <option value="">All Scans</option>
                                        <option value="IN">Entry (IN)</option>
                                        <option value="OUT">Exit (OUT)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Search Visitor</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-light" style="border-radius: 50rem 0 0 50rem;"><i class="bi bi-search text-muted"></i></span>
                                        <input type="text" class="form-control border-light bg-light rounded-end-pill" id="visitorSearch" placeholder="Search by name, ID or host...">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-muted">&nbsp;</label>
                                    <button class="btn btn-primary rounded-pill btn-sm w-100 py-2" id="exportExcel">
                                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Excel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Logs Table Card -->
                    <div class="card border-0">
                        <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold mb-1">Detailed Gate History</h5>
                                <p class="text-muted small mb-0">Complete record of every visitor scan</p>
                            </div>
                            <div class="d-flex gap-2">
                                <!-- Refresh moved to live updates -->
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="visitorActivityTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 py-3 text-uppercase small fw-bold text-muted border-0">#</th>
                                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Visitor</th>
                                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Host / Resident</th>
                                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Action</th>
                                            <th class="py-3 text-uppercase small fw-bold text-muted border-0">Timestamp</th>
                                            <th class="py-3 text-uppercase small fw-bold text-muted border-0 pe-4">Scanner</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-0"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Feed Section -->
                <div class="col-lg-4">
                    <div class="card border-0">
                        <div class="card-header bg-white border-0 py-4 px-4">
                            <h5 class="fw-bold mb-1">Live Feed</h5>
                            <p class="text-muted small mb-0">Most recent visitor movements</p>
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

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../assets/vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../assets/vendor/datatables/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Mobile sidebar toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });

        $(document).ready(function() {
            // Date defaults
            const today = new Date().toISOString().split('T')[0];
            $('#dateTo').val(today);
            const weekAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
            $('#dateFrom').val(weekAgo);

            const table = $('#visitorActivityTable').DataTable({
                ajax: {
                    url: `api/get_visitor_activity_log.php?date_from=${weekAgo}&date_to=${today}`,
                    dataSrc: ''
                },
                columns: [
                    { 
                        data: null,
                        render: (d, t, r, m) => `<span class="text-muted small ps-2">${m.row + 1}</span>`
                    },
                    { 
                        data: 'name',
                        render: (d, t, r) => `<div><div class="fw-bold text-dark">${d}</div><div class="smaller text-muted fs-7">${r.id_number}</div></div>`
                    },
                    { data: 'host_name', render: d => `<span class="small fw-medium">${d}</span>` },
                    { 
                        data: 'action',
                        render: a => {
                            const badge = a === 'IN' ? 'success' : 'danger';
                            const icon = a === 'IN' ? 'bi-box-arrow-in-right' : 'bi-box-arrow-right';
                            return `<span class="badge bg-${badge} bg-opacity-10 text-${badge} rounded-pill px-3 py-2 fw-bold" style="font-size: 0.7rem;">
                                <i class="bi ${icon} me-1"></i> ${a === 'IN' ? 'ENTRY' : 'EXIT'}
                            </span>`;
                        }
                    },
                    { 
                        data: 'timestamp',
                        render: (data, type, row) => {
                            if (type === 'sort' || type === 'type') return data;
                            return `<div class="small fw-medium text-dark">${row.date}</div><div class="smaller text-muted">${row.time}</div>`;
                        }
                    },
                    { 
                        data: 'device',
                        render: d => `<span class="badge bg-light text-dark border px-2"><i class="bi bi-qr-code-scan me-1"></i> ${d}</span>`
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
                $.get('api/get_visitor_activity_stats.php', function(data) {
                    $('#totalLogs').text(data.total_logs);
                    $('#totalEntries').text(data.total_entries);
                    $('#totalExits').text(data.total_exits);
                    $('#uniqueVisitors').text(data.unique_visitors);
                });
            }

            function loadTimeline() {
                $.get('api/get_visitor_activity_log.php?limit=12', function(data) {
                    let html = '';
                    data.forEach(item => {
                        const actionClass = item.action === 'IN' ? 'entry' : 'exit';
                        const icon = item.action === 'IN' ? 'bi-arrow-down-right-circle-fill' : 'bi-arrow-up-right-circle-fill';
                        const color = item.action === 'IN' ? 'success' : 'danger';
                        html += `
                            <div class="timeline-item ${actionClass}">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="fw-bold text-dark">${item.name}</span>
                                    <span class="text-${color} small fw-bold">
                                         <i class="bi ${icon} me-1"></i> ${item.action}
                                    </span>
                                </div>
                                <div class="small text-muted mb-2">Host: ${item.host_name}</div>
                                <div class="d-flex align-items-center gap-3 mt-1">
                                    <span class="smaller bg-light rounded px-2 text-muted fw-medium py-1">
                                        <i class="bi bi-clock me-1"></i> ${item.time}
                                    </span>
                                    <span class="smaller text-muted">
                                        <i class="bi bi-geo-alt me-1"></i> ${item.device}
                                    </span>
                                </div>
                            </div>`;
                    });
                    $('#recentTimeline').html(html);
                });
            }

            updateStats();
            loadTimeline();

            // Live Activity Filters (Instant update on selection or entry)
            $('#dateFrom, #dateTo, #actionFilter').on('change input blur', function() {
                const url = `api/get_visitor_activity_log.php?date_from=${$('#dateFrom').val()}&date_to=${$('#dateTo').val()}&action=${$('#actionFilter').val()}`;
                table.ajax.url(url).load();
            });

            // Live Search
            $('#visitorSearch').on('keyup input', function() {
                table.search(this.value).draw();
            });

            $('#clearFilter').click(() => {
                $('#dateFrom').val(weekAgo);
                $('#dateTo').val(today);
                $('#actionFilter').val('');
                table.ajax.url('api/get_visitor_activity_log.php').load();
            });

            $('#exportExcel').click(function() {
                const dateFrom = $('#dateFrom').val();
                const dateTo = $('#dateTo').val();
                const action = $('#actionFilter').val();
                window.location.href = `api/export_visitor_activity_excel.php?date_from=${dateFrom}&date_to=${dateTo}&action=${action}`;
            });

            // ============================================
            // REAL-TIME AUTO-UPDATE (SSE)
            // ============================================
            const activityStream = new EventSource('api/sse_activity.php');

            activityStream.onmessage = function(event) {
                const data = JSON.parse(event.data);
                if (data.type === 'new_scan' || data.type === 'init') {
                    // Update Table
                    table.ajax.reload(null, false);
                    
                    // Update Stats
                    updateStats(); 
                    
                    // Update Timeline
                    loadTimeline();
                }
            };

            activityStream.onerror = function() {
                console.warn("SSE Connection lost. Polling fallback active.");
            };

            setInterval(() => { 
                if (activityStream.readyState === EventSource.CLOSED) {
                    table.ajax.reload(null, false); updateStats(); loadTimeline(); 
                }
            }, 30000);
        });
    </script>
</body>
</html>
