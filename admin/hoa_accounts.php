<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
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
    <title>HOA Accounts Management - San Jose</title>
    
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/vendor/datatables/css/dataTables.bootstrap5.min.css">
    
    <!-- Local Fonts (Inter) -->
    <link rel="stylesheet" href="../assets/vendor/fonts/inter/inter.css">
    <script src="../assets/vendor/sweetalert2/sweetalert2.all.min.js"></script>
    
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
                <h5 class="mb-0 fw-bold">HOA Accounts Management</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-muted">Management</a></li>
                        <li class="breadcrumb-item active fw-medium">HOA Accounts</li>
                    </ol>
                </nav>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="container-fluid p-4">
            <!-- Stats Row -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 p-2 text-center">
                        <div class="card-body">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary mx-auto mb-3">
                                <i class="bi bi-person-badge-fill fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="totalHOA">0</h4>
                            <small class="text-muted fw-medium fs-7">Total HOA Accounts</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 p-2 text-center">
                        <div class="card-body">
                            <div class="stat-icon bg-success bg-opacity-10 text-success mx-auto mb-3">
                                <i class="bi bi-person-check-fill fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="activeHOA">0</h4>
                            <small class="text-muted fw-medium fs-7">Active Accounts</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 p-2 text-center">
                        <div class="card-body">
                            <div class="stat-icon bg-info bg-opacity-10 text-info mx-auto mb-3">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="recentLogin">None</h4>
                            <small class="text-muted fw-medium fs-7">Last HOA Activity</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Table Card -->
            <div class="card border-0 mb-5">
                <div class="card-header bg-white border-0 py-4 px-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h5 class="fw-bold mb-1">HOA Account Registry</h5>
                        <p class="text-muted small mb-0">Manage accounts for Homeowner Association members</p>
                    </div>
                    <button class="btn btn-primary rounded-pill btn-sm px-4" data-bs-toggle="modal" data-bs-target="#addHOAModal">
                        <i class="bi bi-plus-lg me-1"></i> Add HOA Account
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="hoaTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-uppercase small fw-bold text-muted border-0">Username</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Full Name</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Email</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Status</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Last Login</th>
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

    <!-- Add HOA Modal -->
    <div class="modal fade" id="addHOAModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-plus text-primary me-2"></i>
                        Create HOA Account
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="addHOAForm">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Full Name *</label>
                                <input type="text" class="form-control rounded-3 p-2 px-3" name="name" placeholder="Juan Dela Cruz" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Username *</label>
                                <input type="text" class="form-control rounded-3 p-2 px-3" name="username" placeholder="hoa_juan" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Password *</label>
                                <input type="password" class="form-control rounded-3 p-2 px-3" name="password" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Email Address</label>
                                <input type="email" class="form-control rounded-3 p-2 px-3" name="email" placeholder="hoa@example.com">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Discard</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="saveHOABtn">
                        <i class="bi bi-save me-1"></i> Save Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit HOA Modal -->
    <div class="modal fade" id="editHOAModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square text-warning me-2"></i>
                        Update HOA Account
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="editHOAForm">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Full Name *</label>
                                <input type="text" class="form-control rounded-3 p-2 px-3" name="name" id="edit_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Username *</label>
                                <input type="text" class="form-control rounded-3 p-2 px-3" name="username" id="edit_username" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">New Password (leave blank to keep)</label>
                                <input type="password" class="form-control rounded-3 p-2 px-3" name="password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Email Address</label>
                                <input type="email" class="form-control rounded-3 p-2 px-3" name="email" id="edit_email">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Status</label>
                                <select class="form-select rounded-3" name="status" id="edit_status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="updateHOABtn">
                        <i class="bi bi-check2-circle me-1"></i> Update Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../assets/vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../assets/vendor/datatables/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            const table = $('#hoaTable').DataTable({
                ajax: {
                    url: 'api/get_hoa_accounts.php',
                    dataSrc: ''
                },
                columns: [
                    { data: 'username' },
                    { data: 'name', render: data => `<span class="fw-bold">${data}</span>` },
                    { data: 'email', render: data => data || 'N/A' },
                    { 
                        data: 'status',
                        render: data => {
                            const badge = data === 'active' ? 'success' : 'secondary';
                            return `<span class="badge bg-${badge} text-uppercase">${data}</span>`;
                        }
                    },
                    { data: 'last_login', render: data => data || 'Never' },
                    {
                        data: null,
                        render: data => `
                            <div class="text-end pe-2">
                                <button class="btn btn-sm btn-light rounded-pill p-2 edit-btn" data-id="${data.id}">
                                    <i class="bi bi-pencil text-warning"></i>
                                </button>
                                <button class="btn btn-sm btn-light rounded-pill p-2 delete-btn" data-id="${data.id}">
                                    <i class="bi bi-trash text-danger"></i>
                                </button>
                            </div>
                        `
                    }
                ],
                dom: 'trtp'
            });

            function updateStats() {
                $.get('api/get_hoa_stats.php', function(data) {
                    $('#totalHOA').text(data.total);
                    $('#activeHOA').text(data.active);
                    $('#recentLogin').text(data.recent_activity || 'None');
                });
            }
            updateStats();

            $('#saveHOABtn').click(function() {
                const formData = new FormData($('#addHOAForm')[0]);
                $.ajax({
                    url: 'api/add_hoa_account.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(resp) {
                        const data = JSON.parse(resp);
                        if (data.success) {
                            Swal.fire('Success', data.message, 'success');
                            $('#addHOAModal').modal('hide');
                            $('#addHOAForm')[0].reset();
                            table.ajax.reload();
                            updateStats();
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    }
                });
            });

            $('#hoaTable').on('click', '.edit-btn', function() {
                const id = $(this).data('id');
                $.get(`api/get_hoa_account.php?id=${id}`, function(data) {
                    $('#edit_id').val(data.id);
                    $('#edit_name').val(data.name);
                    $('#edit_username').val(data.username);
                    $('#edit_email').val(data.email);
                    $('#edit_status').val(data.status);
                    new bootstrap.Modal(document.getElementById('editHOAModal')).show();
                });
            });

            $('#updateHOABtn').click(function() {
                const formData = new FormData($('#editHOAForm')[0]);
                $.ajax({
                    url: 'api/update_hoa_account.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(resp) {
                        const data = JSON.parse(resp);
                        if (data.success) {
                            Swal.fire('Updated', data.message, 'success');
                            $('#editHOAModal').modal('hide');
                            table.ajax.reload();
                            updateStats();
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    }
                });
            });

            $('#hoaTable').on('click', '.delete-btn', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef233c',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('api/delete_hoa_account.php', { id: id }, function(resp) {
                            const data = JSON.parse(resp);
                            if (data.success) {
                                Swal.fire('Deleted', data.message, 'success');
                                table.ajax.reload();
                                updateStats();
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
