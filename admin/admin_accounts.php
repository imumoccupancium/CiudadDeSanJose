<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$user = [
    'name' => $_SESSION['user_name'] ?? 'Admin User',
    'role' => $_SESSION['user_role'] ?? 'admin'
];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Accounts Management - Ciudad De San Jose</title>
    <link rel="icon" type="image/png" href="../assets/logo.png">

    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/vendor/datatables/css/dataTables.bootstrap5.min.css">
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

        .badge-self {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            font-size: 0.65rem;
            padding: 2px 7px;
            border-radius: 20px;
            vertical-align: middle;
            margin-left: 4px;
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
                <h5 class="mb-0 fw-bold">Admin Accounts Management</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="dashboard.php"
                                class="text-decoration-none text-muted">Management</a></li>
                        <li class="breadcrumb-item active fw-medium">Admin Accounts</li>
                    </ol>
                </nav>
            </div>

            <div class="ms-auto d-flex align-items-center gap-3">
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
                                <i class="bi bi-shield-lock-fill fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="totalAdmins">0</h4>
                            <small class="text-muted fw-medium fs-7">Total Admin Accounts</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 p-2 text-center">
                        <div class="card-body">
                            <div class="stat-icon bg-success bg-opacity-10 text-success mx-auto mb-3">
                                <i class="bi bi-shield-check fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="activeAdmins">0</h4>
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
                            <small class="text-muted fw-medium fs-7">Last Admin Activity</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Table Card -->
            <div class="card border-0 mb-5">
                <div
                    class="card-header bg-white border-0 py-4 px-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h5 class="fw-bold mb-1">Admin Account Registry</h5>
                        <p class="text-muted small mb-0">Manage administrator accounts for Ciudad De San Jose</p>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-md-end">
                        <div class="input-group" style="max-width: 250px;">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control bg-light border-0 small" id="adminSearch" placeholder="Search admins...">
                        </div>
                        <button class="btn btn-primary rounded-pill btn-sm px-4" data-bs-toggle="modal"
                            data-bs-target="#addAdminModal">
                            <i class="bi bi-plus-lg me-1"></i> Add Admin Account
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="adminTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-uppercase small fw-bold text-muted border-0">Username</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Full Name</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Email</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Status</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Last Login</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0 pe-4 text-end">
                                        Actions</th>
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

    <!-- Add Admin Modal -->
    <div class="modal fade" id="addAdminModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-shield-plus text-primary me-2"></i>
                        Create Admin Account
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="addAdminForm">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Full Name *</label>
                                <input type="text" class="form-control rounded-3 p-2 px-3" name="name"
                                    placeholder="Juan Dela Cruz" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Username *</label>
                                <input type="text" class="form-control rounded-3 p-2 px-3" name="username"
                                    placeholder="admin_juan" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Password *</label>
                                <input type="password" class="form-control rounded-3 p-2 px-3" name="password" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Email Address</label>
                                <input type="email" class="form-control rounded-3 p-2 px-3" name="email"
                                    placeholder="admin@example.com">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4"
                        data-bs-dismiss="modal">Discard</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="saveAdminBtn">
                        <i class="bi bi-save me-1"></i> Save Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Admin Modal -->
    <div class="modal fade" id="editAdminModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square text-warning me-2"></i>
                        Update Admin Account
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="editAdminForm">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Full Name *</label>
                                <input type="text" class="form-control rounded-3 p-2 px-3" name="name" id="edit_name"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Username</label>
                                <input type="text" class="form-control rounded-3 p-2 px-3" name="username"
                                    id="edit_username" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">New Password <span
                                        class="text-muted fw-normal">(leave blank to keep)</span></label>
                                <input type="password" class="form-control rounded-3 p-2 px-3" name="password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Email Address</label>
                                <input type="email" class="form-control rounded-3 p-2 px-3" name="email"
                                    id="edit_email">
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
                    <button type="button" class="btn btn-light rounded-pill px-4"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="updateAdminBtn">
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
        // Mobile sidebar toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('show');
        });

        const CURRENT_USER_ID = <?php echo json_encode($_SESSION['user_id']); ?>;

        $(document).ready(function () {
            const table = $('#adminTable').DataTable({
                ajax: {
                    url: 'api/get_admin_accounts.php',
                    dataSrc: ''
                },
                columns: [
                    {
                        data: 'username',
                        render: (data, type, row) => {
                            const selfTag = (row.id == CURRENT_USER_ID)
                                ? '<span class="badge-self">You</span>'
                                : '';
                            return `<span class="fw-bold text-primary ps-4">${data}</span>${selfTag}`;
                        }
                    },
                    { data: 'name', render: data => `<span class="fw-bold text-dark">${data}</span>` },
                    { 
                        data: 'email', 
                        render: data => `<div class="small text-muted"><i class="bi bi-envelope me-1"></i> ${data || 'N/A'}</div>` 
                    },
                    {
                        data: 'status',
                        render: data => {
                            const badge = data === 'active' ? 'success' : 'secondary';
                            return `<span class="badge bg-${badge} bg-opacity-10 text-${badge} rounded-pill px-2 py-1 small text-uppercase">${data}</span>`;
                        }
                    },
                    { 
                        data: 'last_login', 
                        render: data => `<div class="small text-muted">${data || 'Never'}</div>` 
                    },
                    {
                        data: null,
                        render: data => {
                            const isSelf = data.id == CURRENT_USER_ID;
                            const deleteBtn = isSelf
                                ? `<button class="btn btn-sm btn-light rounded-pill p-2" disabled title="Cannot delete your own account">
                                        <i class="bi bi-trash text-muted"></i>
                                   </button>`
                                : `<button class="btn btn-sm btn-light rounded-pill p-2 delete-btn" data-id="${data.id}">
                                        <i class="bi bi-trash text-danger"></i>
                                   </button>`;
                            return `
                                <div class="text-end pe-2">
                                    <button class="btn btn-sm btn-light rounded-pill p-2 edit-btn" data-id="${data.id}">
                                        <i class="bi bi-pencil text-warning"></i>
                                    </button>
                                    ${deleteBtn}
                                </div>`;
                        }
                    }
                ],
                pageLength: 10,
                order: [[1, 'asc']],
                dom: 'trtp'
            });

            // Search
            $('#adminSearch').on('keyup input', function() {
                table.search(this.value).draw();
            });

            function updateStats() {
                $.get('api/get_admin_accounts.php', function (data) {
                    if (!Array.isArray(data)) return;
                    const total = data.length;
                    const active = data.filter(a => a.status === 'active').length;
                    const logins = data
                        .filter(a => a.last_login)
                        .sort((a, b) => new Date(b.last_login) - new Date(a.last_login));

                    $('#totalAdmins').text(total);
                    $('#activeAdmins').text(active);

                    if (logins.length > 0) {
                        const d = new Date(logins[0].last_login);
                        $('#recentLogin').text(
                            d.toLocaleDateString('en-US', { month: 'short', day: '2-digit' }) + ', ' +
                            d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
                        );
                    } else {
                        $('#recentLogin').text('None');
                    }
                }, 'json');
            }
            updateStats();

            // Add
            $('#saveAdminBtn').click(function () {
                const formData = new FormData($('#addAdminForm')[0]);
                $.ajax({
                    url: 'api/add_admin_account.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (resp) {
                        const data = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                        if (data.success) {
                            Swal.fire('Success', data.message, 'success');
                            $('#addAdminModal').modal('hide');
                            $('#addAdminForm')[0].reset();
                            table.ajax.reload();
                            updateStats();
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    }
                });
            });

            // Edit – open modal
            $('#adminTable').on('click', '.edit-btn', function () {
                const id = $(this).data('id');
                $.get(`api/get_admin_account.php?id=${id}`, function (data) {
                    $('#edit_id').val(data.id);
                    $('#edit_name').val(data.name);
                    $('#edit_username').val(data.username);
                    $('#edit_email').val(data.email);
                    $('#edit_status').val(data.status);
                    new bootstrap.Modal(document.getElementById('editAdminModal')).show();
                }, 'json');
            });

            // Edit – save
            $('#updateAdminBtn').click(function () {
                const formData = new FormData($('#editAdminForm')[0]);
                $.ajax({
                    url: 'api/update_admin_account.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (resp) {
                        const data = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                        if (data.success) {
                            Swal.fire('Updated', data.message, 'success');
                            $('#editAdminModal').modal('hide');
                            table.ajax.reload();
                            updateStats();
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    }
                });
            });

            // Delete
            $('#adminTable').on('click', '.delete-btn', function () {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This admin account will be permanently deleted.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef233c',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('api/delete_admin_account.php', { id: id }, function (resp) {
                            const data = (typeof resp === 'string') ? JSON.parse(resp) : resp;
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