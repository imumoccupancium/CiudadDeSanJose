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
    <title>Visitors Management - San Jose</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
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
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            transition: transform 0.3s ease;
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

        #homeownerDropdown {
            animation: slideDown 0.2s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .homeowner-item:last-child {
            border-bottom: none !important;
        }

        #homeownerSearch:focus {
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
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
                <h5 class="mb-0 fw-bold">Visitor Management</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-muted">Core</a></li>
                        <li class="breadcrumb-item active fw-medium">Visitor Logs</li>
                    </ol>
                </nav>
            </div>
            
            <div class="ms-auto d-flex align-items-center gap-3">
                <div class="input-group d-none d-lg-flex" style="width: 300px;">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control bg-transparent border-start-0 ps-0" id="topSearch" placeholder="Search visitors...">
                </div>
                

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
                                <i class="bi bi-person-check-fill fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="statInside">0</h4>
                            <small class="text-muted fw-medium fs-7">Currently Inside</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 p-2 text-center">
                        <div class="card-body">
                            <div class="stat-icon bg-success bg-opacity-10 text-success mx-auto mb-3">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="statTotalToday">0</h4>
                            <small class="text-muted fw-medium fs-7">Total Today</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 p-2 text-center">
                        <div class="card-body">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning mx-auto mb-3">
                                <i class="bi bi-truck-flatbed fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="statService">0</h4>
                            <small class="text-muted fw-medium fs-7">Service/Delivery</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 p-2 text-center">
                        <div class="card-body">
                            <div class="stat-icon bg-info bg-opacity-10 text-info mx-auto mb-3">
                                <i class="bi bi-clock-fill fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="statTotalExited">0</h4>
                            <small class="text-muted fw-medium fs-7">Exited Today</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Log Table Card -->
            <div class="card border-0 mb-5">
                <div class="card-header bg-white border-0 py-4 px-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h5 class="fw-bold mb-1">Visitor Activity Log</h5>
                        <p class="text-muted small mb-0">Monitor and manage all external visitors in the subdivision</p>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="btn-group rounded-pill overflow-hidden border">
                            <button type="button" class="btn btn-light btn-sm px-3 active" data-filter="all">All</button>
                            <button type="button" class="btn btn-light btn-sm px-3" data-filter="Personal">Personal</button>
                            <button type="button" class="btn btn-light btn-sm px-3" data-filter="Service">Service</button>
                        </div>
                        <button class="btn btn-primary rounded-pill btn-sm px-4" data-bs-toggle="modal" data-bs-target="#addVisitorModal">
                            <i class="bi bi-plus-lg me-1"></i> Log Visitor
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="visitorsTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-uppercase small fw-bold text-muted border-0">Visitor Name</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Type</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Person to Visit</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0 text-center">Entry/Gate</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0 text-center">Time In / Out</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0 text-center">Status</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0 pe-4 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="border-0" id="visitorsTableBody">
                                <!-- Data will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <button class="fab" data-bs-toggle="modal" data-bs-target="#addVisitorModal" title="Log New Visitor">
        <i class="bi bi-plus-lg"></i>
    </button>

    <!-- Add Visitor Modal -->
    <div class="modal fade" id="addVisitorModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-plus text-primary me-2"></i>
                        Register New Visitor
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="addVisitorForm">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Visitor Full Name</label>
                            <input type="text" name="visitor_name" class="form-control rounded-3 p-2 px-3 bg-light border-0" placeholder="e.g. Juan De La Cruz" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Visitor Type</label>
                                <select name="visitor_type" id="modalVisitorType" class="form-select rounded-3 p-2 px-3 bg-light border-0" required>
                                    <option value="Personal">Personal Visit</option>
                                    <option value="Service">Delivery / Service</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Assigned Gate</label>
                                <div class="bg-light rounded-3 p-2 px-3 d-flex align-items-center" style="height: 41px;">
                                    <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                                    <span class="fw-bold text-dark">Main Gate</span>
                                    <input type="hidden" name="gate" value="Main Gate">
                                </div>
                                <small class="text-muted small mt-1 d-block italic">Primary Entrance & Exit</small>
                            </div>
                        </div>

                        <div class="mb-3 d-none" id="companyField">
                            <label class="form-label small fw-bold text-muted text-uppercase">Company Name</label>
                            <input type="text" name="company" class="form-control rounded-3 p-2 px-3 bg-light border-0" placeholder="e.g. Grab, PLDT, Meralco">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Person to Visit</label>
                            <div class="position-relative">
                                <input type="text" 
                                       id="homeownerSearch" 
                                       class="form-control rounded-3 p-2 px-3 bg-light border-0" 
                                       placeholder="Type to search homeowner name or address..." 
                                       autocomplete="off"
                                       required>
                                <input type="hidden" name="person_to_visit" id="selectedHomeowner" required>
                                <input type="hidden" name="homeowner_id" id="selectedHomeownerId">
                                <div id="homeownerDropdown" class="position-absolute w-100 bg-white border rounded-3 shadow-lg mt-1 d-none" style="max-height: 250px; overflow-y: auto; z-index: 1050;">
                                    <!-- Dropdown items will be populated here -->
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1">
                                <i class="bi bi-info-circle me-1"></i>
                                <span id="selectedHomeownerInfo">Start typing to search...</span>
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Purpose of Visit</label>
                            <textarea name="purpose" class="form-control rounded-3 p-2 px-3 bg-light border-0" rows="2" placeholder="e.g. Social Visit, Maintenance, Delivery"></textarea>
                        </div>

                        <div class="bg-primary bg-opacity-10 p-3 rounded-4 mt-4 d-flex justify-content-between align-items-center">
                            <div>
                                <small class="fw-bold text-primary text-uppercase mb-0 d-block">System Timestamp</small>
                                <span class="h6 mb-0 fw-bold text-primary font-monospace" id="modalTimeNow">12:00:00 PM</span>
                            </div>
                            <i class="bi bi-clock-history text-primary fs-3"></i>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Discard</button>
                    <button type="submit" form="addVisitorForm" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        Confirm Entry
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // Mobile sidebar toggle (Same as homeowners.php)
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });



        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#visitorsTable').DataTable({
                pageLength: 10,
                dom: 'trtp',
                language: {
                    paginate: {
                        next: '<i class="bi bi-chevron-right"></i>',
                        previous: '<i class="bi bi-chevron-left"></i>'
                    }
                }
            });

            // Top Search Bar
            $('#topSearch').on('keyup', function() {
                table.search(this.value).draw();
            });

            // Filter Buttons
            $('[data-filter]').click(function() {
                const filter = $(this).data('filter');
                if (filter === 'all') {
                    table.column(1).search('').draw();
                } else {
                    table.column(1).search(filter).draw();
                }
                $('[data-filter]').removeClass('active btn-primary text-white').addClass('btn-light');
                $(this).addClass('active btn-primary text-white').removeClass('btn-light');
            });

            // Modal: Dynamic "Company" field
            $('#modalVisitorType').on('change', function() {
                const isPro = (this.value === 'Professional' || this.value === 'Service');
                $('#companyField').toggleClass('d-none', !isPro);
            });

            // Modal: Live Clock Sync
            setInterval(() => {
                const now = new Date();
                $('#modalTimeNow').text(now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
            }, 1000);

            // Homeowner Search Autocomplete
            let searchTimeout;
            const $searchInput = $('#homeownerSearch');
            const $dropdown = $('#homeownerDropdown');
            const $selectedHomeowner = $('#selectedHomeowner');
            const $selectedHomeownerId = $('#selectedHomeownerId');
            const $selectedInfo = $('#selectedHomeownerInfo');

            // Search homeowners with debounce
            $searchInput.on('input', function() {
                const query = $(this).val().trim();
                
                clearTimeout(searchTimeout);
                
                if (query.length === 0) {
                    $dropdown.addClass('d-none').empty();
                    $selectedHomeowner.val('');
                    $selectedHomeownerId.val('');
                    $selectedInfo.text('Start typing to search...');
                    return;
                }

                // Show loading state
                $dropdown.removeClass('d-none').html(`
                    <div class="p-3 text-center text-muted">
                        <i class="bi bi-hourglass-split me-2"></i>Searching...
                    </div>
                `);

                // Debounce search
                searchTimeout = setTimeout(() => {
                    console.log('Searching for:', query); // Debug log
                    $.ajax({
                        url: 'api/search_homeowners.php',
                        method: 'GET',
                        data: { q: query },
                        dataType: 'json',
                        success: function(response) {
                            console.log('Search response:', response); // Debug log
                            if (response.success && response.data.length > 0) {
                                let html = '';
                                response.data.forEach(homeowner => {
                                    html += `
                                        <div class="homeowner-item p-3 border-bottom" 
                                             data-id="${homeowner.id}"
                                             data-name="${homeowner.name}"
                                             data-address="${homeowner.address}"
                                             data-homeowner-id="${homeowner.homeowner_id}"
                                             style="cursor: pointer; transition: background-color 0.2s;">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                     style="width: 40px; height: 40px; min-width: 40px;">
                                                    <i class="bi bi-person-fill"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold text-dark">${homeowner.name}</div>
                                                    <small class="text-muted">
                                                        <i class="bi bi-house-door me-1"></i>${homeowner.address}
                                                        <span class="ms-2 badge bg-light text-dark">${homeowner.homeowner_id}</span>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                });
                                $dropdown.html(html).css('z-index', '2000'); // Ensure it's on top
                            } else {
                                $dropdown.html(`
                                    <div class="p-3 text-center text-muted">
                                        <i class="bi bi-search me-2"></i>No homeowners found
                                    </div>
                                `);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Search error:', error); // Debug log
                            $dropdown.html(`
                                <div class="p-3 text-center text-danger">
                                    <i class="bi bi-exclamation-triangle me-2"></i>Error loading results
                                </div>
                            `);
                        }
                    });
                }, 300); // 300ms debounce
            });

            // Handle homeowner selection
            $(document).on('click', '.homeowner-item', function() {
                const name = $(this).data('name');
                const address = $(this).data('address');
                const homeownerId = $(this).data('homeowner-id');
                const id = $(this).data('id');

                // Set values
                $searchInput.val(name);
                $selectedHomeowner.val(name);
                $selectedHomeownerId.val(id);
                
                // Update info text
                $selectedInfo.html(`
                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                    Selected: <strong>${name}</strong> - ${address}
                `);

                // Hide dropdown
                $dropdown.addClass('d-none');
            });

            // Hover effect for dropdown items
            $(document).on('mouseenter', '.homeowner-item', function() {
                $(this).css('background-color', 'rgba(67, 97, 238, 0.05)');
            }).on('mouseleave', '.homeowner-item', function() {
                $(this).css('background-color', 'transparent');
            });

            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#homeownerSearch, #homeownerDropdown').length) {
                    $dropdown.addClass('d-none');
                }
            });

            // Clear selection when modal is closed
            $('#addVisitorModal').on('hidden.bs.modal', function() {
                $searchInput.val('');
                $selectedHomeowner.val('');
                $selectedHomeownerId.val('');
                $selectedInfo.text('Start typing to search...');
                $dropdown.addClass('d-none').empty();
            });

            // --- VISITOR MANAGEMENT LOGIC ---

            // Load Visitor Logs
            function loadVisitorLogs() {
                $.ajax({
                    url: 'api/get_visitor_logs.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            renderTable(response.data);
                            updateStats(response.data);
                        } else {
                            console.error('Failed to load logs:', response.message);
                        }
                    },
                    error: function() {
                        console.error('Error fetching visitor logs');
                    }
                });
            }

            // Render Table Rows
            function renderTable(logs) {
                table.clear();
                
                logs.forEach(log => {
                    const statusBadge = log.status === 'INSIDE' 
                        ? `<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 fw-bold" style="font-size: 0.75rem;">
                             <i class="bi bi-house-check me-1"></i> INSIDE
                           </span>`
                        : `<span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-2 fw-bold" style="font-size: 0.75rem;">
                             <i class="bi bi-door-open me-1"></i> EXITED
                           </span>`;

                    const actionButtons = log.status === 'INSIDE'
                        ? `<button class="btn btn-sm btn-outline-danger rounded-pill px-3 checkout-btn" data-id="${log.id}">
                             <i class="bi bi-box-arrow-right me-1"></i> Checkout
                           </button>`
                        : `<button class="btn btn-sm btn-light rounded-pill p-2" title="View Details"><i class="bi bi-eye text-primary"></i></button>`;

                    const timeOutDisplay = log.time_out_fmt 
                        ? `<span class="text-secondary">${log.time_out_fmt}</span>` 
                        : `<span class="text-muted">--:-- --</span>`;

                    table.row.add([
                        `<div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="bi bi-person"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">${log.visitor_name}</div>
                                <div class="smaller text-muted">${log.visitor_type} Visit ${log.company ? '• ' + log.company : ''}</div>
                            </div>
                        </div>`,
                        `<span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">${log.visitor_type}</span>`,
                        `<div>
                            <div class="fw-medium">${log.homeowner_name}</div>
                            <div class="smaller text-muted">${log.homeowner_address}</div>
                        </div>`,
                        `<div class="text-center small fw-bold">${log.gate}</div>`,
                        `<div class="text-center font-monospace small">
                            <span class="text-success">${log.time_in_fmt}</span><br>
                            ${timeOutDisplay}
                        </div>`,
                        `<div class="text-center">${statusBadge}</div>`,
                        `<div class="pe-4 text-end">${actionButtons}</div>`
                    ]);
                });
                
                table.draw();
            }

            // Update Stats counters
            function updateStats(logs) {
                const today = new Date().toISOString().split('T')[0];
                const stats = {
                    inside: logs.filter(l => l.status === 'INSIDE').length,
                    totalToday: logs.filter(l => l.created_at.includes(today)).length,
                    service: logs.filter(l => l.visitor_type === 'Service').length,
                    exitedToday: logs.filter(l => l.status === 'OUT' && l.created_at.includes(today)).length
                };

                $('#statInside').text(stats.inside);
                $('#statTotalToday').text(stats.totalToday);
                $('#statService').text(stats.service);
                $('#statTotalExited').text(stats.exitedToday);
            }

            // Handle Checkout
            $(document).on('click', '.checkout-btn', function() {
                const id = $(this).data('id');
                
                Swal.fire({
                    title: 'Confirm Checkout',
                    text: "Mark this visitor as having exited?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#4361ee',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, checkout'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'api/checkout_visitor.php',
                            method: 'POST',
                            data: { id: id },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Success', response.message, 'success');
                                    loadVisitorLogs();
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            }
                        });
                    }
                });
            });

            // Handle Form Submission
            $('#addVisitorForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = $(this).serialize();
                
                // Validate homeowner selection
                if (!$('#selectedHomeownerId').val()) {
                    Swal.fire('Missing Selection', 'Please select a homeowner from the search results.', 'warning');
                    return;
                }

                $.ajax({
                    url: 'api/add_visitor.php',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#addVisitorModal').modal('hide');
                            Swal.fire('Success', response.message, 'success');
                            loadVisitorLogs();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'An unexpected error occurred.', 'error');
                    }
                });
            });

            // Initial load
            loadVisitorLogs();

            // Auto-refresh every 30 seconds
            setInterval(loadVisitorLogs, 30000);
        });
    </script>
</body>
</html>
