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
    <title>Homeowners Management - Entry Monitor</title>
    
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
        .qr-code-preview {
            width: 100px;
            height: 100px;
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
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
            <a href="homeowners.php" class="nav-link text-white active rounded mb-1">
                <i class="bi bi-people-fill me-2"></i> Homeowners
            </a>
            <a href="activity_logs.php" class="nav-link text-white-50 rounded mb-1">
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
                    <h5 class="mb-0">Homeowners Management</h5>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 small">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Homeowners</li>
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
                                    <i class="bi bi-people fs-2"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0 fw-bold" id="totalHomeowners">0</h4>
                                    <small class="text-muted">Total Homeowners</small>
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
                                    <i class="bi bi-person-check fs-2"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0 fw-bold" id="activeHomeowners">0</h4>
                                    <small class="text-muted">Active</small>
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
                                    <i class="bi bi-house-check fs-2"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0 fw-bold" id="insideCount">0</h4>
                                    <small class="text-muted">Currently Inside</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 text-warning rounded p-3 me-3">
                                    <i class="bi bi-house-dash fs-2"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0 fw-bold" id="outsideCount">0</h4>
                                    <small class="text-muted">Currently Outside</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Homeowners Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-people text-primary"></i>
                        All Homeowners
                    </h5>
                    <div class="d-flex gap-2">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary active" data-status="all">All</button>
                            <button type="button" class="btn btn-outline-success" data-status="active">Active</button>
                            <button type="button" class="btn btn-outline-warning" data-status="inactive">Inactive</button>
                        </div>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addHomeownerModal">
                            <i class="bi bi-plus-circle"></i> Add Homeowner
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="homeownersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>QR Code</th>
                                    <th>Status</th>
                                    <th>Current Location</th>
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

    <!-- Add Homeowner Modal -->
    <div class="modal fade" id="addHomeownerModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
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
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Homeowner ID *</label>
                                <input type="text" class="form-control" name="homeowner_id" placeholder="HO-XXX" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Address *</label>
                                <textarea class="form-control" name="address" rows="2" required></textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="generate_qr" id="generateQR" checked>
                                    <label class="form-check-label" for="generateQR">
                                        Generate QR Code automatically
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveHomeownerBtn">
                        <i class="bi bi-save"></i> Save Homeowner
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Homeowner Modal -->
    <div class="modal fade" id="editHomeownerModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil"></i>
                        Edit Homeowner
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editHomeownerForm">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="name" id="edit_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Homeowner ID *</label>
                                <input type="text" class="form-control" name="homeowner_id" id="edit_homeowner_id" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="edit_email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone" id="edit_phone">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Address *</label>
                                <textarea class="form-control" name="address" id="edit_address" rows="2" required></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="edit_status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateHomeownerBtn">
                        <i class="bi bi-save"></i> Update Homeowner
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Homeowner Modal -->
    <div class="modal fade" id="viewHomeownerModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person"></i>
                        Homeowner Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Name:</th>
                                    <td id="view_name"></td>
                                </tr>
                                <tr>
                                    <th>Homeowner ID:</th>
                                    <td id="view_homeowner_id"></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td id="view_email"></td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td id="view_phone"></td>
                                </tr>
                                <tr>
                                    <th>Address:</th>
                                    <td id="view_address"></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td id="view_status"></td>
                                </tr>
                                <tr>
                                    <th>Current Location:</th>
                                    <td id="view_location"></td>
                                </tr>
                                <tr>
                                    <th>Last Scan:</th>
                                    <td id="view_last_scan"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="qr-code-preview mx-auto mb-2" id="view_qr">
                                <i class="bi bi-qr-code fs-1 text-muted"></i>
                            </div>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download"></i> Download QR
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        
        // Initialize DataTable
        $(document).ready(function() {
            const table = $('#homeownersTable').DataTable({
                ajax: {
                    url: 'api/get_all_homeowners.php',
                    dataSrc: ''
                },
                columns: [
                    { data: 'homeowner_id' },
                    { data: 'name' },
                    { data: 'email' },
                    { data: 'phone' },
                    { data: 'address' },
                    { 
                        data: 'qr_code',
                        render: function(data) {
                            return data ? '<span class="badge bg-success"><i class="bi bi-qr-code"></i> Generated</span>' : '<span class="badge bg-secondary">Not Generated</span>';
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
                            return `<span class="badge bg-${badges[data]}">${data.toUpperCase()}</span>`;
                        }
                    },
                    { 
                        data: 'current_status',
                        render: function(data) {
                            return data === 'IN' 
                                ? '<span class="badge bg-info">INSIDE</span>'
                                : '<span class="badge bg-warning">OUTSIDE</span>';
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            return `
                                <button class="btn btn-sm btn-outline-primary view-btn" data-id="${data.id}">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning edit-btn" data-id="${data.id}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${data.id}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            `;
                        }
                    }
                ],
                pageLength: 25,
                order: [[0, 'asc']]
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
                    table.column(6).search('').draw();
                } else {
                    table.column(6).search(status.toUpperCase()).draw();
                }
                $('[data-status]').removeClass('active');
                $(this).addClass('active');
            });
            
            // View homeowner
            $('#homeownersTable').on('click', '.view-btn', function() {
                const id = $(this).data('id');
                $.get(`api/get_homeowner.php?id=${id}`, function(data) {
                    $('#view_name').text(data.name);
                    $('#view_homeowner_id').text(data.homeowner_id);
                    $('#view_email').text(data.email || 'N/A');
                    $('#view_phone').text(data.phone || 'N/A');
                    $('#view_address').text(data.address);
                    $('#view_status').html(`<span class="badge bg-success">${data.status.toUpperCase()}</span>`);
                    $('#view_location').html(data.current_status === 'IN' ? '<span class="badge bg-info">INSIDE</span>' : '<span class="badge bg-warning">OUTSIDE</span>');
                    $('#view_last_scan').text(data.last_scan_time || 'Never');
                    new bootstrap.Modal(document.getElementById('viewHomeownerModal')).show();
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
                if (confirm('Are you sure you want to delete this homeowner?')) {
                    const id = $(this).data('id');
                    $.post('api/delete_homeowner.php', { id: id }, function(response) {
                        if (response.success) {
                            table.ajax.reload();
                            updateStats();
                            alert('Homeowner deleted successfully!');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    }, 'json');
                }
            });
            
            // Save new homeowner
            $('#saveHomeownerBtn').click(function() {
                const formData = $('#addHomeownerForm').serialize();
                $.post('api/add_homeowner.php', formData, function(response) {
                    if (response.success) {
                        bootstrap.Modal.getInstance(document.getElementById('addHomeownerModal')).hide();
                        table.ajax.reload();
                        updateStats();
                        $('#addHomeownerForm')[0].reset();
                        alert('Homeowner added successfully!');
                    } else {
                        alert('Error: ' + response.message);
                    }
                }, 'json');
            });
            
            // Update homeowner
            $('#updateHomeownerBtn').click(function() {
                const formData = $('#editHomeownerForm').serialize();
                $.post('api/update_homeowner.php', formData, function(response) {
                    if (response.success) {
                        bootstrap.Modal.getInstance(document.getElementById('editHomeownerModal')).hide();
                        table.ajax.reload();
                        updateStats();
                        alert('Homeowner updated successfully!');
                    } else {
                        alert('Error: ' + response.message);
                    }
                }, 'json');
            });
        });
    </script>
</body>
</html>
