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
    <title>Account Management - San Jose</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
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

        .account-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-weight: 700;
        }

        @media (max-width: 992px) {
            .sidebar { left: calc(var(--sidebar-width) * -1); }
            .sidebar.show { left: 0; }
            .main-content { margin-left: 0; }
            .top-navbar { left: 0; }
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
                <h5 class="mb-0 fw-bold">Account Management</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-muted">Core</a></li>
                        <li class="breadcrumb-item active fw-medium">Homeowners Accounts</li>
                    </ol>
                </nav>
            </div>
            
            <div class="ms-auto d-flex align-items-center gap-3">
                <div class="input-group d-none d-lg-flex" style="width: 300px;">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control bg-transparent border-start-0 ps-0" placeholder="Search accounts...">
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
                                <i class="bi bi-shield-lock-fill fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="totalAccounts">0</h4>
                            <small class="text-muted fw-medium fs-7">Total Accounts</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 p-2 text-center">
                        <div class="card-body">
                            <div class="stat-icon bg-success bg-opacity-10 text-success mx-auto mb-3">
                                <i class="bi bi-check-circle-fill fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="activeAccounts">0</h4>
                            <small class="text-muted fw-medium fs-7">Active Online</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 p-2 text-center">
                        <div class="card-body">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning mx-auto mb-3">
                                <i class="bi bi-clock-fill fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="pendingAccounts">0</h4>
                            <small class="text-muted fw-medium fs-7">Pending Setup</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 p-2 text-center">
                        <div class="card-body">
                            <div class="stat-icon bg-danger bg-opacity-10 text-danger mx-auto mb-3">
                                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="suspendedAccounts">0</h4>
                            <small class="text-muted fw-medium fs-7">Locked / Suspended</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Accounts Table Card -->
            <div class="card border-0 mb-5">
                <div class="card-header bg-white border-0 py-4 px-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h5 class="fw-bold mb-1">Access Credentials</h5>
                        <p class="text-muted small mb-0">Manage digital access and security for residents</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary rounded-pill btn-sm px-4">
                            <i class="bi bi-envelope-at me-1"></i> Invite New
                        </button>
                        <button class="btn btn-primary rounded-pill btn-sm px-4">
                            <i class="bi bi-plus-lg me-1"></i> New Account
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="accountsTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-uppercase small fw-bold text-muted border-0">User Account</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Assigned Resident</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Role</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Last Login</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Security</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0 pe-4 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="border-0">
                                <!-- Sample Data Rows -->
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="account-avatar me-3">JD</div>
                                            <div>
                                                <div class="fw-bold text-dark">juan.delacruz@email.com</div>
                                                <div class="small text-muted">Registered: Jan 15, 2026</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-primary">Juan Dela Cruz</div>
                                        <div class="small text-muted">ID: HO-001</div>
                                    </td>
                                    <td><span class="badge bg-light text-dark fw-bold px-3">Homeowner</span></td>
                                    <td>
                                        <div class="small">Jan 31, 2026</div>
                                        <div class="smaller text-muted">10:45 AM</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Active</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-light rounded-pill p-2" title="Reset Password">
                                            <i class="bi bi-key text-warning"></i>
                                        </button>
                                        <button class="btn btn-sm btn-light rounded-pill p-2" title="View Details">
                                            <i class="bi bi-eye text-primary"></i>
                                        </button>
                                        <button class="btn btn-sm btn-light rounded-pill p-2" title="Lock Account">
                                            <i class="bi bi-lock text-danger"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="account-avatar me-3" style="background-color: rgba(247, 37, 133, 0.1); color: var(--warning);">MS</div>
                                            <div>
                                                <div class="fw-bold text-dark">maria.santos@email.com</div>
                                                <div class="small text-muted">Registered: Jan 20, 2026</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-primary">Maria Santos</div>
                                        <div class="small text-muted">ID: HO-002</div>
                                    </td>
                                    <td><span class="badge bg-light text-dark fw-bold px-3">Resident</span></td>
                                    <td>
                                        <div class="text-muted small">Never logged in</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">Pending</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-light rounded-pill p-2" title="Resend Invite">
                                            <i class="bi bi-send text-info"></i>
                                        </button>
                                        <button class="btn btn-sm btn-light rounded-pill p-2" title="View Details">
                                            <i class="bi bi-eye text-primary"></i>
                                        </button>
                                        <button class="btn btn-sm btn-light rounded-pill p-2" title="Delete Account">
                                            <i class="bi bi-trash text-danger"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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

        // Initialize DataTable
        $(document).ready(function() {
            $('#accountsTable').DataTable({
                pageLength: 10,
                dom: 'trtp',
                language: {
                    paginate: {
                        next: '<i class="bi bi-chevron-right"></i>',
                        previous: '<i class="bi bi-chevron-left"></i>'
                    }
                }
            });

            // Set initial stats
            $('#totalAccounts').text('248');
            $('#activeAccounts').text('186');
            $('#pendingAccounts').text('42');
            $('#suspendedAccounts').text('20');
        });
    </script>
</body>
</html>
