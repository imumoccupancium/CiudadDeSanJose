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
    <title>Visitor Logs - Ciudad De San Jose</title>
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



        .badge-qr-status {
            cursor: pointer;
            transition: transform 0.2s;
        }

        .badge-qr-status:hover {
            transform: scale(1.05);
        }

        .smaller {
            font-size: 0.75rem;
        }

        .fs-7 {
            font-size: 0.85rem !important;
        }
    </style>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar d-flex align-items-center px-4">
            <button class="btn btn-link d-lg-none me-3" id="sidebarToggle">
                <i class="bi bi-list fs-3 text-dark"></i>
            </button>
            <div>
                <h5 class="mb-0 fw-bold">Visitor Registry</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small text-muted">
                        <li class="breadcrumb-item">Residents</li>
                        <li class="breadcrumb-item active">Visitor Management</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
            </div>
        </nav>

        <div class="container-fluid p-4">
            <!-- Stats Row -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 p-2 text-center">
                        <div class="card-body">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary mx-auto mb-3">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="statInside">0</h4>
                            <small class="text-muted fw-medium fs-7">Currently Inside</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 p-2 text-center">
                        <div class="card-body">
                            <div class="stat-icon bg-info bg-opacity-10 text-info mx-auto mb-3">
                                <i class="bi bi-person-plus-fill fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="statTotalToday">0</h4>
                            <small class="text-muted fw-medium fs-7">Visits Today</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 p-2 text-center">
                        <div class="card-body">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning mx-auto mb-3">
                                <i class="bi bi-tools fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="statService">0</h4>
                            <small class="text-muted fw-medium fs-7">Service Personnel</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 p-2 text-center">
                        <div class="card-body">
                            <div class="stat-icon bg-success bg-opacity-10 text-success mx-auto mb-3">
                                <i class="bi bi-check-circle-fill fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold" id="statTotalExited">0</h4>
                            <small class="text-muted fw-medium fs-7">Exited Today</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters & List -->
            <div class="card border-0 mb-4">
                <div class="card-body p-4">
                    <div class="row g-3 align-items-end mb-4">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted">Type</label>
                            <select class="form-select rounded-pill border-light bg-light" id="filterType">
                                <option value="">All Types</option>
                                <option value="Personal">Personal</option>
                                <option value="Service">Service</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted">Status</label>
                            <select class="form-select rounded-pill border-light bg-light" id="filterStatus">
                                <option value="">All Status</option>
                                <option value="INSIDE">Inside</option>
                                <option value="OUT">Exited</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Time Period</label>
                            <div class="input-group">
                                <input type="date" class="form-control rounded-start-pill border-light bg-light"
                                    id="filterFrom">
                                <input type="date" class="form-control rounded-end-pill border-light bg-light"
                                    id="filterTo">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Search</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light d-none d-md-flex" style="border-radius: 50rem 0 0 50rem;"><i class="bi bi-search py-1 text-muted"></i></span>
                                <input type="text" class="form-control border-light bg-light rounded-end-pill" id="visitorSearch" placeholder="Search visitors or host...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label d-none d-md-block">&nbsp;</label>
                            <button class="btn btn-primary rounded-pill btn-sm px-4 w-100" data-bs-toggle="modal"
                                data-bs-target="#addVisitorModal">
                                <i class="bi bi-plus-lg me-1"></i> Add Visitor
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="visitorLogsTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-uppercase small fw-bold text-muted border-0">Visitor</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Visiting</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0 text-center">QR
                                        Status</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Time In/Out</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0">Status</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted border-0 pe-4 text-end">
                                        Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <?php include 'modals/visitor_modals.php'; ?>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../assets/vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../assets/vendor/datatables/js/dataTables.bootstrap5.min.js"></script>
    <script src="../assets/vendor/qrcodejs/qrcode.min.js"></script>
    <script src="../assets/vendor/sweetalert2/sweetalert2.all.min.js"></script>

    <script>
        let cachedLogs = [];
        $(document).ready(function () {
            const table = $('#visitorLogsTable').DataTable({
                ajax: {
                    url: 'api/get_visitor_logs.php',
                    dataSrc: 'data'
                },
                columns: [
                    {
                        data: 'visitor_name',
                        render: (d, t, r) => `
                            <div>
                                <div class="fw-bold text-dark">${d}</div>
                                <div class="smaller text-muted">${r.visitor_type} • ${r.company || 'Private'}</div>
                            </div>`
                    },
                    {
                        data: 'homeowner_name',
                        render: (d, t, r) => `<div class="small fw-medium">${d}</div><div class="smaller text-muted">${r.homeowner_address}</div>`
                    },
                    {
                        data: 'qr_expiry',
                        className: 'text-center',
                        render: function (d, t, r) {
                            const hasQR = r.qr_token;
                            const isExpired = new Date(d) < new Date();

                            if (!hasQR) {
                                return `<span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-1 small">
                                            <i class="bi bi-x-circle me-1"></i> MISSING
                                        </span><br><small class="text-primary" style="font-size: 0.6rem;">Click to generate</small>`;
                            }

                            if (isExpired) {
                                return `<div class="badge-qr-status view-qr-quick" data-id="${r.id}">
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 small">
                                                <i class="bi bi-clock-history me-1"></i> EXPIRED
                                            </span>
                                            <div class="x-small text-danger mt-1" style="font-size: 0.65rem; font-weight: bold;">
                                                Exp: ${d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A'}
                                            </div>
                                        </div>`;
                            }

                            return `<div class="badge-qr-status view-qr-quick" data-id="${r.id}" style="cursor: pointer;">
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 small">
                                    <i class="bi bi-check-circle-fill me-1"></i> VALID
                                </span>
                                <div class="x-small text-muted mt-1" style="font-size: 0.65rem;">
                                    Exp: ${d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A'}
                                </div>
                            </div>`;
                        }
                    },
                    {
                        data: 'time_in_fmt',
                        render: (d, t, r) => {
                            const timeIn = d || '--:-- --';
                            const timeOut = r.time_out_fmt || '--:-- --';
                            return `<div class="small text-success fw-bold"><i class="bi bi-box-arrow-in-right me-1"></i> ${timeIn}</div>
                                    <div class="small text-muted fw-bold"><i class="bi bi-box-arrow-right me-1"></i> ${timeOut}</div>`;
                        }
                    },
                    {
                        data: 'current_status',
                        render: function (data) {
                            if (data === 'None' || !data) {
                                return `<span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-2 fw-bold" style="font-size: 0.75rem;">
                                    <i class="bi bi-clock-history me-1"></i> PENDING
                                </span>`;
                            }
                            const isInside = (data === 'IN' || data === 'INSIDE');
                            const color = isInside ? 'primary' : 'warning';
                            const icon = isInside ? 'bi-house-check' : 'bi-house-dash';
                            return `<span class="badge bg-${color} bg-opacity-10 text-${color} rounded-pill px-3 py-2 fw-bold" style="font-size: 0.75rem;">
                                <i class="bi ${icon} me-1"></i> ${isInside ? 'INSIDE' : 'OUTSIDE'}
                            </span>`;
                        }
                    },
                    {
                        data: null,
                        className: 'text-end pe-4',
                        render: (data) => `
                            <div class="d-flex justify-content-end gap-1">
                                <button class="btn btn-light btn-sm rounded-circle view-log-btn" data-id="${data.id}" title="View Details"><i class="bi bi-eye text-primary"></i></button>
                                <button class="btn btn-light btn-sm rounded-circle edit-log-btn" data-id="${data.id}" title="Edit Visitor"><i class="bi bi-pencil text-warning"></i></button>
                                <button class="btn btn-light btn-sm rounded-circle row-regen-btn" data-id="${data.id}" title="Regenerate QR"><i class="bi bi-qr-code text-success"></i></button>
                                <button class="btn btn-light btn-sm rounded-circle delete-log-btn" data-id="${data.id}" title="Delete Log"><i class="bi bi-trash text-danger"></i></button>
                            </div>`
                    }
                ],
                order: [[3, 'desc']],
                dom: 'trtp'
            });

            table.on('xhr.dt', function (e, settings, json) {
                if (json && json.data) {
                    cachedLogs = json.data;
                    updateStats(cachedLogs);
                }
            });

            function updateStats(logs) {
                if (!Array.isArray(logs)) return;
                const today = new Date().toISOString().split('T')[0];
                const stats = {
                    inside: logs.filter(l => (l.current_status === 'IN' || l.current_status === 'INSIDE' || l.status === 'INSIDE') && (l.current_status !== 'OUT' && l.status !== 'OUT')).length,
                    totalToday: logs.filter(l => l.created_at.includes(today)).length,
                    service: logs.filter(l => l.visitor_type === 'Service').length,
                    exitedToday: logs.filter(l => l.current_status === 'OUT' || l.status === 'OUT').length
                };
                $('#statInside').text(stats.inside);
                $('#statTotalToday').text(stats.totalToday);
                $('#statService').text(stats.service);
                $('#statTotalExited').text(stats.exitedToday);
            }

            // Quick View QR
            $(document).on('click', '.view-qr-quick', function () {
                const id = $(this).data('id');
                const log = cachedLogs.find(l => l.id == id);
                if (log && log.qr_token) {
                    Swal.fire({
                        title: `<span class="fw-bold">${log.visitor_name}</span>`,
                        html: `
                            <div class="d-flex flex-column align-items-center mb-1">
                                <div id="big_visitor_qr" class="mb-3 p-3 bg-white rounded shadow-sm"></div>
                                <div class="small text-muted mb-2">Host: <strong>${log.homeowner_name}</strong></div>
                                <div class="small text-muted mb-3" style="font-size: 0.75rem;">
                                    Valid until: ${new Date(log.qr_expiry).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
                                </div>
                                <div class="d-flex justify-content-center gap-2 w-100 px-3">
                                    <button class="btn btn-primary rounded-pill px-4 flex-grow-1" id="downloadBigVisitorQR">
                                        <i class="bi bi-download me-1"></i> Download
                                    </button>
                                    <button class="btn btn-outline-warning rounded-pill px-4 flex-grow-1" id="regenBigVisitorQR">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Regenerate
                                    </button>
                                </div>
                            </div>`,
                        showConfirmButton: false,
                        showCloseButton: true,
                        didOpen: () => {
                            new QRCode(document.getElementById("big_visitor_qr"), {
                                text: log.qr_token, width: 200, height: 200,
                                colorDark: "#000000", colorLight: "#ffffff",
                                correctLevel: QRCode.CorrectLevel.H
                            });
                            $('#downloadBigVisitorQR').click(() => {
                                const qrImg = document.querySelector('#big_visitor_qr img');
                                if (qrImg) {
                                    const link = document.createElement('a');
                                    link.href = qrImg.src;
                                    link.download = `QR_Visitor_${log.visitor_name.replace(/\s+/g, '_')}.png`;
                                    link.click();
                                }
                            });
                            $('#regenBigVisitorQR').click(() => { Swal.close(); triggerRegenerate(log.id); });
                        }
                    });
                }
            });

            // View Details Modal
            $(document).on('click', '.view-log-btn', function () {
                const id = $(this).data('id');
                const log = cachedLogs.find(l => l.id == id);
                if (log) {
                    $('#viewVisitorModal').data('current-id', id);
                    $('#viewVisitorName').text(log.visitor_name);
                    $('#viewTimeIn').text(log.time_in_fmt || '--:-- --');
                    $('#viewTimeOut').text(log.time_out_fmt || '--:-- --');
                    $('#viewHostName').text(log.homeowner_name);
                    $('#viewHostAddress').text(log.homeowner_address);
                    $('#viewPurpose').text(log.purpose || 'Not specified');
                    $('#viewGate').text(log.gate);

                    if (log.qr_token && (log.status === 'INSIDE' || log.status === 'None')) {
                        $('#view_qr_expiry').text('Valid until: ' + (log.qr_expiry || 'N/A'));
                        $('#downloadVisitorQR').show();
                        $('#view_visitor_qr').empty();
                        new QRCode("view_visitor_qr", {
                            text: log.qr_token, width: 128, height: 128,
                            colorDark: "#000000", colorLight: "#ffffff",
                            correctLevel: QRCode.CorrectLevel.H
                        });
                    } else {
                        $('#view_visitor_qr').html('<i class="bi bi-qr-code fs-1 text-muted"></i>');
                        $('#view_qr_expiry').text(log.status === 'OUT' ? 'Pass Expired / Used' : 'No QR Token');
                        $('#downloadVisitorQR').hide();
                    }
                    $('#viewVisitorModal').modal('show');
                }
            });

            // Edit Details Modal
            $(document).on('click', '.edit-log-btn', function () {
                const id = $(this).data('id');
                const log = cachedLogs.find(l => l.id == id);
                if (log) {
                    $('#edit_visitor_id').val(log.id);
                    $('#edit_visitor_name').val(log.visitor_name);
                    $('#edit_visitor_type').val(log.visitor_type).trigger('change');
                    $('#edit_company').val(log.company || '');
                    $('#edit_modal_homeowner_id').val(log.homeowner_id);
                    $('#edit_modal_person_to_visit').val(log.homeowner_name);
                    $('#edit_residentSearchInput').val(log.homeowner_name + ' - ' + log.homeowner_address);
                    $('#edit_selectedResidentLabel').text(log.homeowner_name);
                    $('#edit_selectionFeedback').show();
                    $('#edit_qr_expiry').val(log.qr_expiry ? log.qr_expiry.substring(0, 10) : '');
                    $('#edit_purpose').val(log.purpose || '');
                    $('#edit_status').val(log.status || 'INSIDE');
                    $('#editVisitorModal').modal('show');
                }
            });

            $('#updateVisitorBtn').click(function () {
                const formData = $('#editVisitorForm').serialize();
                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Updating...');

                $.post('api/edit_visitor.php', formData, function (res) {
                    if (res.success) {
                        Swal.fire('Success', res.message, 'success');
                        $('#editVisitorModal').modal('hide');
                        table.ajax.reload(null, false);
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                    $('#updateVisitorBtn').prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Update Registry');
                }, 'json');
            });

            // Delete Log
            $(document).on('click', '.delete-log-btn', function () {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This visitor log will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef233c',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('api/delete_visitor.php', { id: id }, function (res) {
                            if (res.success) {
                                Swal.fire('Deleted!', res.message, 'success');
                                table.ajax.reload(null, false);
                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        }, 'json');
                    }
                });
            });

            $(document).on('click', '.row-regen-btn', function () {
                triggerRegenerate($(this).data('id'));
            });

            // Live Filters (Picker and manual input)
            $('#filterType, #filterStatus, #filterFrom, #filterTo').on('change input blur', function() {
                const url = `api/get_visitor_logs.php?type=${$('#filterType').val()}&status=${$('#filterStatus').val()}&from=${$('#filterFrom').val()}&to=${$('#filterTo').val()}`;
                table.ajax.url(url).load();
            });

            // Live Search
            $('#visitorSearch').on('keyup input', function() {
                table.search(this.value).draw();
            });

            // Regenerate Logic
            function triggerRegenerate(id) {
                const today = new Date().toISOString().split('T')[0];
                const nextYear = new Date(); nextYear.setFullYear(nextYear.getFullYear() + 1);
                const defaultExpiry = nextYear.toISOString().split('T')[0];

                Swal.fire({
                    title: 'Regenerate QR Pass?',
                    html: `
                        <p class="text-muted small">Old pass will be invalidated!</p>
                        <div class="mt-3 text-start">
                            <label class="form-label small fw-bold text-muted">New Expiry Date</label>
                            <input type="date" id="swal_visitor_expiry" class="form-control rounded-pill" value="${defaultExpiry}" min="${today}">
                        </div>`,
                    showCancelButton: true, confirmButtonText: 'Generate New',
                    preConfirm: () => document.getElementById('swal_visitor_expiry').value
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('api/regenerate_visitor_qr.php', { id: id, expiry_date: result.value }, function (res) {
                            if (res.success) {
                                Swal.fire('Success', res.message, 'success');
                                table.ajax.reload();
                            } else { Swal.fire('Error', res.message, 'error'); }
                        }, 'json');
                    }
                });
            }

            $('.regenerate-qr-btn').click(function () {
                triggerRegenerate($('#viewVisitorModal').data('current-id'));
            });

            $('#downloadVisitorQR').click(function () {
                const qrImg = document.querySelector('#view_visitor_qr img');
                const vName = $('#viewVisitorName').text();
                if (qrImg) {
                    const link = document.createElement('a');
                    link.href = qrImg.src;
                    link.download = `QR_Visitor_${vName.replace(/\s+/g, '_')}.png`;
                    link.click();
                }
            });

            // Save New Visitor
            $('#saveVisitorBtn').click(function () {
                const formData = $('#addVisitorForm').serialize();
                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Saving...');

                $.post('api/add_visitor.php', formData, function (res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Visitor Logged!',
                            html: `
                                <div class="text-center">
                                    <p>${res.message}</p>
                                    <div id="save_qr_preview" class="mb-3 d-flex justify-content-center"></div>
                                    <p class="small text-muted mb-0">Expiry: ${res.qr_expiry}</p>
                                </div>`,
                            didOpen: () => {
                                new QRCode(document.getElementById("save_qr_preview"), {
                                    text: res.qr_token, width: 140, height: 140,
                                    colorDark: "#000000", colorLight: "#ffffff",
                                    correctLevel: QRCode.CorrectLevel.H
                                });
                            }
                        });
                        $('#addVisitorModal').modal('hide');
                        $('#addVisitorForm')[0].reset();
                        table.ajax.reload();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                    $('#saveVisitorBtn').prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Log Visitor & Generate QR');
                }, 'json').fail(function () {
                    Swal.fire('Error', 'Server error occurred', 'error');
                    $('#saveVisitorBtn').prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Log Visitor & Generate QR');
                });
            });

            // Polling for Visitor Scan Alerts
            function checkScanAlerts() {
                $.get('api/get_latest_alerts.php?type=visitor', function (alerts) {
                    if (Array.isArray(alerts)) {
                        alerts.forEach(function (alert) {
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 4500,
                                timerProgressBar: true
                            });

                            Toast.fire({
                                icon: alert.status === 'success' ? 'success' : 'error',
                                title: alert.status === 'success' ? 'Visitor Log Updated' : 'Access Denied',
                                text: alert.message
                            });

                            // Reload table to show new entry immediately
                            table.ajax.reload(null, false);
                        });
                    }
                });
            }

            // Check immediately on load, then every 2 seconds
            checkScanAlerts();
            setInterval(checkScanAlerts, 2000);

            // Periodically refresh data every 30 seconds
            setInterval(() => table.ajax.reload(null, false), 30000);
        });
    </script>
</body>

</html>