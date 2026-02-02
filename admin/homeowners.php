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
    <title>Homeowners Management - San Jose</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js"></script>

    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    
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
                <h5 class="mb-0 fw-bold">Homeowners Management</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-muted">Core</a></li>
                        <li class="breadcrumb-item active fw-medium">Registry</li>
                    </ol>
                </nav>
            </div>
            
            <div class="ms-auto d-flex align-items-center gap-3">
                <div class="input-group d-none d-lg-flex" style="width: 300px;">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control bg-transparent border-start-0 ps-0" placeholder="Search registry...">
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
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="totalHomeowners">0</h4>
                            <small class="text-muted fw-medium fs-7">Total Registered</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 p-2 text-center">
                        <div class="card-body">
                            <div class="stat-icon bg-success bg-opacity-10 text-success mx-auto mb-3">
                                <i class="bi bi-person-check-fill fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="activeHomeowners">0</h4>
                            <small class="text-muted fw-medium fs-7">Active Accounts</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 p-2 text-center">
                        <div class="card-body">
                            <div class="stat-icon bg-info bg-opacity-10 text-info mx-auto mb-3">
                                <i class="bi bi-house-check-fill fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="insideCount">0</h4>
                            <small class="text-muted fw-medium fs-7">Currently Inside</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 p-2 text-center">
                        <div class="card-body">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning mx-auto mb-3">
                                <i class="bi bi-house-dash-fill fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="outsideCount">0</h4>
                            <small class="text-muted fw-medium fs-7">Currently Outside</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Registry Table Card -->
            <div class="card border-0 mb-5">
                <div class="card-header bg-white border-0 py-4 px-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h5 class="fw-bold mb-1">Resident Registry</h5>
                        <p class="text-muted small mb-0">Manage and monitor all homeowners in the community</p>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="btn-group rounded-pill overflow-hidden border">
                            <button type="button" class="btn btn-light btn-sm px-3 active" data-status="all">All</button>
                            <button type="button" class="btn btn-light btn-sm px-3" data-status="active">Active</button>
                            <button type="button" class="btn btn-light btn-sm px-3" data-status="inactive">Inactive</button>
                        </div>
                        <button class="btn btn-primary rounded-pill btn-sm px-4" data-bs-toggle="modal" data-bs-target="#addHomeownerModal">
                            <i class="bi bi-plus-lg me-1"></i> Add Resident
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="homeownersTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-uppercase small fw-bold text-muted border-0">Homeowner ID</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Resident Name</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Contact Info</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">QR Status</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Account</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Location</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0 pe-4 text-end">Actions</th>
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

    <!-- Floating Action Button -->
    <button class="fab" data-bs-toggle="modal" data-bs-target="#addHomeownerModal" title="Quick Add Homeowner">
        <i class="bi bi-plus-lg"></i>
    </button>

    <!-- Add Homeowner Modal -->
    <div class="modal fade" id="addHomeownerModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-plus text-primary me-2"></i>
                        Register Resident
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="addHomeownerForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Full Name *</label>
                                <input type="text" class="form-control rounded-3 p-2 px-3" name="name" placeholder="Juan Dela Cruz" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Homeowner ID *</label>
                                <input type="text" class="form-control rounded-3 p-2 px-3" name="homeowner_id" placeholder="CSJ-2024-001" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Email Address</label>
                                <input type="email" class="form-control rounded-3 p-2 px-3" name="email" placeholder="juan@example.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Phone Number</label>
                                <input type="tel" class="form-control rounded-3 p-2 px-3" name="phone" placeholder="+63 9xx xxxx xxx">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Home Address *</label>
                                <textarea class="form-control rounded-3 p-2 px-3" name="address" rows="2" placeholder="Block, Lot & Street Details" required></textarea>
                            </div>
                            <div class="col-12 mt-4">
                                <div class="form-check form-switch bg-light p-3 rounded-3 border">
                                    <div class="ps-4">
                                        <input class="form-check-input" type="checkbox" name="generate_qr" id="generateQR" checked>
                                        <label class="form-check-label fw-bold" for="generateQR">
                                            Auto-generate Secure Access Key (QR)
                                        </label>
                                        <p class="small text-muted mb-0">The resident can use this for entry/exit at the main gate.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Discard</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="saveHomeownerBtn">
                        <i class="bi bi-save me-1"></i> Save Resident
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Homeowner Modal -->
    <div class="modal fade" id="editHomeownerModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square text-warning me-2"></i>
                        Update Resident Info
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="editHomeownerForm">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Full Name *</label>
                                <input type="text" class="form-control rounded-3 p-2 px-3" name="name" id="edit_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Homeowner ID *</label>
                                <input type="text" class="form-control rounded-3 p-2 px-3" name="homeowner_id" id="edit_homeowner_id" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Email Address</label>
                                <input type="email" class="form-control rounded-3 p-2 px-3" name="email" id="edit_email">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Phone Number</label>
                                <input type="tel" class="form-control rounded-3 p-2 px-3" name="phone" id="edit_phone">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Home Address *</label>
                                <textarea class="form-control rounded-3 p-2 px-3" name="address" id="edit_address" rows="2" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Account Status</label>
                                <select class="form-select rounded-3" name="status" id="edit_status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="updateHomeownerBtn">
                        <i class="bi bi-check2-circle me-1"></i> Update Registry
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Homeowner Modal -->
    <div class="modal fade" id="viewHomeownerModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-badge text-primary me-2"></i>
                        Resident Profile
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-8">
                            <div class="bg-light p-4 rounded-4 border border-white">
                                <div class="row g-3">
                                    <div class="col-sm-6 text-muted small fw-bold text-uppercase">Resident Name</div>
                                    <div class="col-sm-6 fw-bold" id="view_name"></div>
                                    
                                    <div class="col-sm-6 text-muted small fw-bold text-uppercase">ID Number</div>
                                    <div class="col-sm-6 fw-bold text-primary" id="view_homeowner_id"></div>
                                    
                                    <div class="col-sm-6 text-muted small fw-bold text-uppercase">Email</div>
                                    <div class="col-sm-6" id="view_email"></div>
                                    
                                    <div class="col-sm-6 text-muted small fw-bold text-uppercase">Phone</div>
                                    <div class="col-sm-6" id="view_phone"></div>
                                    
                                    <div class="col-sm-6 text-muted small fw-bold text-uppercase">Address</div>
                                    <div class="col-sm-6" id="view_address"></div>
                                    
                                    <div class="col-sm-6 text-muted small fw-bold text-uppercase">Account Status</div>
                                    <div class="col-sm-6" id="view_status"></div>
                                    
                                    <div class="col-sm-6 text-muted small fw-bold text-uppercase">Current Location</div>
                                    <div class="col-sm-6" id="view_location"></div>
                                    
                                    <div class="col-sm-6 text-muted small fw-bold text-uppercase">Last Scan Event</div>
                                    <div class="col-sm-6 small fw-medium" id="view_last_scan"></div>

                                    <div class="col-sm-12 mt-3">
                                        <div class="alert alert-info border-0 shadow-sm rounded-4 d-flex align-items-center mb-0">
                                            <i class="bi bi-shield-check fs-4 me-3"></i>
                                            <div>
                                                <div class="small fw-bold">QR Access Key Validity</div>
                                                <div class="small opacity-75" id="view_qr_expiry">Valid until: Dec 31, 2026</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="card border-0 bg-white p-3 shadow-sm mx-auto mb-3" style="width: 160px; height: 160px;">
                                <div id="view_qr" class="w-100 h-100 d-flex align-items-center justify-content-center bg-light rounded shadow-inner">
                                    <i class="bi bi-qr-code fs-1 text-muted"></i>
                                </div>
                            </div>
                            <button class="btn btn-outline-primary btn-sm rounded-pill px-4 mb-2 w-100" id="downloadQR">
                                <i class="bi bi-download me-1"></i> Download Key
                            </button>
                            <button class="btn btn-light btn-sm rounded-pill px-4 w-100 regenerate-qr-btn" id="regeneateQRView">
                                <i class="bi bi-arrow-clockwise me-1"></i> Regenerate
                            </button>
                        </div>

                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 w-100" data-bs-dismiss="modal">Close Profile</button>
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
        

        
        // Initialize DataTable
        $(document).ready(function() {
            const table = $('#homeownersTable').DataTable({
                ajax: {
                    url: 'api/get_all_homeowners.php',
                    dataSrc: ''
                },
                columns: [
                    { 
                        data: 'homeowner_id',
                        render: function(data) {
                            return `<span class="fw-bold text-primary pe-4 ps-2">${data}</span>`;
                        }
                    },
                    { 
                        data: 'name',
                        render: function(data) {
                            return `<div class="fw-bold text-dark">${data}</div>`;
                        }
                    },
                    { 
                        data: null,
                        render: function(data) {
                            return `<div class="small text-muted">
                                <i class="bi bi-envelope me-1"></i> ${data.email || 'None'}<br>
                                <i class="bi bi-phone me-1"></i> ${data.phone || 'None'}
                            </div>`;
                        }
                    },
                    { 
                        data: null,
                        render: function(data) {
                            const hasQR = data.qr_token;
                            return `<div class="qr-status-click" data-id="${data.id}" style="cursor: pointer;">
                                ${hasQR ? 
                                    `<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 small">
                                        <i class="bi bi-check-circle-fill me-1"></i> ACTIVE
                                    </span>
                                    <div class="x-small text-muted mt-1" style="font-size: 0.65rem;">
                                        Exp: ${data.qr_expiry ? new Date(data.qr_expiry).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'}) : 'N/A'}
                                    </div>` : 
                                    `<span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-1 small">
                                        <i class="bi bi-x-circle me-1"></i> MISSING
                                    </span><br><small class="text-primary" style="font-size: 0.6rem;">Click to generate</small>`
                                }
                            </div>`;
                        }
                    },

                    { 
                        data: 'status',
                        render: function(data) {
                            const badges = {
                                'active': 'success',
                                'inactive': 'secondary',
                                'suspended': 'danger'
                            };
                            return `<span class="badge bg-${badges[data]} text-uppercase px-2" style="font-size: 0.7rem;">${data}</span>`;
                        }
                    },
                    { 
                        data: 'current_status',
                        render: function(data) {
                            const color = data === 'IN' ? 'primary' : 'warning';
                            const icon = data === 'IN' ? 'bi-house-check' : 'bi-house-dash';
                            return `<span class="badge bg-${color} bg-opacity-10 text-${color} rounded-pill px-3 py-2 fw-bold" style="font-size: 0.75rem;">
                                <i class="bi ${icon} me-1"></i> ${data === 'IN' ? 'INSIDE' : 'OUTSIDE'}
                            </span>`;
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            return `<div class="text-end pe-2">
                                <button class="btn btn-sm btn-light rounded-pill p-2 view-btn" data-id="${data.id}" title="View Profile">
                                    <i class="bi bi-eye text-primary"></i>
                                </button>
                                <button class="btn btn-sm btn-light rounded-pill p-2 edit-btn" data-id="${data.id}" title="Edit Data">
                                    <i class="bi bi-pencil text-warning"></i>
                                </button>
                                <button class="btn btn-sm btn-light rounded-pill p-2 regen-btn" data-id="${data.id}" title="Regenerate QR">
                                    <i class="bi bi-qr-code text-success"></i>
                                </button>
                                <button class="btn btn-sm btn-light rounded-pill p-2 delete-btn" data-id="${data.id}" title="Delete Resident">
                                    <i class="bi bi-trash text-danger"></i>
                                </button>
                            </div>`;

                        }
                    }
                ],
                pageLength: 10,
                order: [[1, 'asc']],
                dom: 'trtp', // Simplified UI
                language: {
                    paginate: {
                        next: '<i class="bi bi-chevron-right"></i>',
                        previous: '<i class="bi bi-chevron-left"></i>'
                    }
                }
            });
            
            // Update stats
            function updateStats() {
                $.get('api/get_homeowner_stats.php', function(data) {
                    $('#totalHomeowners').text(data.total);
                    $('#activeHomeowners').text(data.active);
                    $('#insideCount').text(data.inside);
                    $('#outsideCount').text(data.outside);
                });
            }
            updateStats();
            
            // Status filter
            $('[data-status]').click(function() {
                const status = $(this).data('status');
                if (status === 'all') {
                    table.column(4).search('').draw();
                } else {
                    table.column(4).search(status).draw();
                }
                $('[data-status]').removeClass('active btn-primary text-white').addClass('btn-light');
                $(this).addClass('active btn-primary text-white').removeClass('btn-light');
            });
            
            // View homeowner
            let qrScanner = null;
            $('#homeownersTable').on('click', '.view-btn', function() {
                const id = $(this).data('id');
                $.get(`api/get_homeowner.php?id=${id}`, function(data) {
                    $('#view_name').text(data.name);
                    $('#view_homeowner_id').text(data.homeowner_id);
                    $('#view_email').text(data.email || 'N/A');
                    $('#view_phone').text(data.phone || 'N/A');
                    $('#view_address').text(data.address);
                    $('#view_qr_expiry').text('Valid until: ' + (data.qr_expiry_formatted || 'N/A'));
                    
                    const badges = {'active': 'success', 'inactive': 'secondary', 'suspended': 'danger'};
                    $('#view_status').html(`<span class="badge bg-${badges[data.status]} px-3 rounded-pill">${data.status.toUpperCase()}</span>`);
                    
                    $('#view_location').html(data.current_status === 'IN' ? 
                        '<span class="badge bg-primary bg-opacity-10 text-primary px-3 rounded-pill">INSIDE</span>' : 
                        '<span class="badge bg-warning bg-opacity-10 text-warning px-3 rounded-pill">OUTSIDE</span>');
                    
                    $('#view_last_scan').text(data.last_scan_time || 'No records available');

                    // Generate QR Code
                    $('#view_qr').empty();
                    if (data.qr_token) {
                        new QRCode(document.getElementById("view_qr"), {
                            text: data.qr_token,
                            width: 128,
                            height: 128,
                            colorDark : "#000000",
                            colorLight : "#ffffff",
                            correctLevel : QRCode.CorrectLevel.H
                        });
                        $('#regeneateQRView').attr('data-id', data.id);
                    } else {
                        $('#view_qr').html('<i class="bi bi-qr-code fs-1 text-muted"></i>');
                    }

                    new bootstrap.Modal(document.getElementById('viewHomeownerModal')).show();
                });
            });

            // QR Status Click Handler (Big Popup / Generate Prompt)
            $('#homeownersTable').on('click', '.qr-status-click', function() {
                const id = $(this).data('id');
                const rowData = table.row($(this).closest('tr')).data();
                
                if (rowData.qr_token) {
                    // Show Large QR Code Popup with Download and Regenerate options
                    Swal.fire({
                        title: `${rowData.name}'s Access Key`,
                        html: `
                            <div id="big_qr" class="d-flex justify-content-center my-3"></div>
                            <div class="small text-muted mb-3 text-uppercase fw-bold ls-1" style="letter-spacing: 0.05rem;">
                                Valid until: ${new Date(rowData.qr_expiry).toLocaleDateString('en-US', {month:'long', day:'numeric', year:'numeric'})}
                            </div>
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-primary rounded-pill px-4 btn-sm" id="downloadBigQR">
                                    <i class="bi bi-download me-1"></i> Download
                                </button>
                                <button class="btn btn-outline-warning rounded-pill px-4 btn-sm" id="regenBigQR">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Regenerate
                                </button>
                            </div>
                        `,
                        showConfirmButton: false,
                        showCloseButton: true,
                        didOpen: () => {
                            new QRCode(document.getElementById("big_qr"), {
                                text: rowData.qr_token,
                                width: 220,
                                height: 220,
                                colorDark : "#000000",
                                colorLight : "#ffffff",
                                correctLevel : QRCode.CorrectLevel.H
                            });
                            
                            // Download Logic
                            $('#downloadBigQR').click(function() {
                                const qrImg = document.querySelector('#big_qr img');
                                const link = document.createElement('a');
                                link.href = qrImg.src;
                                link.download = `QR_${rowData.homeowner_id}.png`;
                                link.click();
                            });

                            // Regenerate Logic from within Quick-View
                            $('#regenBigQR').click(function() {
                                Swal.close();
                                // Trigger the existing regeneration logic
                                $(`.regen-btn[data-id="${id}"]`).click();
                            });
                        }
                    });
                } else {

                    // Prompt to Generate
                    Swal.fire({
                        title: 'No Access Key found',
                        text: `Would you like to generate a secure QR code for ${rowData.name}?`,
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Generate',
                        cancelButtonText: 'Not now',
                        confirmButtonColor: 'var(--primary)'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.post('api/generate_qr.php', { id: id }, function(response) {
                                if (response.success) {
                                    Swal.fire('Generated!', 'Resident now has an active access key.', 'success');
                                    table.ajax.reload();
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            }, 'json');
                        }
                    });
                }
            });

            // Regenerate QR Code

            $(document).on('click', '.regen-btn, .regenerate-qr-btn', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Regenerate QR Code?',
                    text: "The old QR code will immediately stop working!",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: 'var(--primary)',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, generate new!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('api/generate_qr.php', { id: id }, function(response) {
                            if (response.success) {
                                if ($('#viewHomeownerModal').is(':visible')) {
                                    $('#view_qr').empty();
                                    new QRCode(document.getElementById("view_qr"), {
                                        text: response.token,
                                        width: 128,
                                        height: 128,
                                        colorDark : "#000000",
                                        colorLight : "#ffffff",
                                        correctLevel : QRCode.CorrectLevel.H
                                    });
                                    $('#view_qr_expiry').text('Valid until: ' + response.expiry);
                                }
                                Swal.fire({
                                    title: 'Succeeded!',
                                    text: 'New access key is ready.',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                table.ajax.reload(null, false);
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }, 'json');
                    }
                });
            });


            
            // Edit homeowner
            $('#homeownersTable').on('click', '.edit-btn', function() {
                const id = $(this).data('id');
                $.get(`api/get_homeowner.php?id=${id}`, function(data) {
                    $('#edit_id').val(data.id);
                    $('#edit_name').val(data.name);
                    $('#edit_homeowner_id').val(data.homeowner_id);
                    $('#edit_email').val(data.email);
                    $('#edit_phone').val(data.phone);
                    $('#edit_address').val(data.address);
                    $('#edit_status').val(data.status);
                    new bootstrap.Modal(document.getElementById('editHomeownerModal')).show();
                });
            });
            
            // Delete homeowner
            $('#homeownersTable').on('click', '.delete-btn', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This resident's data will be permanently removed!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: 'var(--danger)',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    borderRadius: '15px'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('api/delete_homeowner.php', { id: id }, function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'Resident has been removed from registry.',
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                table.ajax.reload();
                                updateStats();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }, 'json');
                    }
                });
            });

            
            // Save new homeowner
            $('#saveHomeownerBtn').click(function() {
                const formData = $('#addHomeownerForm').serialize();
                $.post('api/add_homeowner.php', formData, function(response) {
                    if (response.success) {
                        bootstrap.Modal.getInstance(document.getElementById('addHomeownerModal')).hide();
                        Swal.fire({
                            title: 'Success!',
                            text: 'New resident registered successfully.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        table.ajax.reload();
                        updateStats();
                        $('#addHomeownerForm')[0].reset();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }, 'json');
            });

            
            // Update homeowner
            $('#updateHomeownerBtn').click(function() {
                const formData = $('#editHomeownerForm').serialize();
                $.post('api/update_homeowner.php', formData, function(response) {
                    if (response.success) {
                        bootstrap.Modal.getInstance(document.getElementById('editHomeownerModal')).hide();
                        Swal.fire({
                            title: 'Updated!',
                            text: 'Resident profile has been updated.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        table.ajax.reload();
                        updateStats();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }, 'json');
            });

            // Download QR Code
            $('#downloadQR').click(function() {
                const qrImg = document.querySelector('#view_qr img');
                if (qrImg) {
                    const homeownerId = $('#view_homeowner_id').text();
                    const link = document.createElement('a');
                    link.href = qrImg.src;
                    link.download = `QR_${homeownerId}.png`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    alert('QR Code image not found. Please try again.');
                }
            });
        });
    </script>
</body>
</html>
