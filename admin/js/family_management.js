// Family Member Management JavaScript
// This handles all family member CRUD operations and QR code management

let currentHomeownerId = null;
let currentHomeownerName = '';
let currentFamilyMemberId = null;

// Initialize family management modal
$(document).on('click', '.family-btn', function () {
    currentHomeownerId = $(this).data('id');
    currentHomeownerName = $(this).data('name');

    $('#family_homeowner_name').text(currentHomeownerName);
    loadFamilyMembers(currentHomeownerId);

    const familyModal = new bootstrap.Modal(document.getElementById('familyManagementModal'));
    familyModal.show();
});

// Load family members for a homeowner
function loadFamilyMembers(homeownerId) {
    $.get(`api/get_family_members.php?homeowner_id=${homeownerId}`, function (data) {
        if (data.length === 0) {
            $('#familyMembersList').html(`
                <div class="col-12 text-center py-5">
                    <i class="bi bi-people fs-1 text-muted"></i>
                    <p class="text-muted mt-3">No family members added yet.</p>
                    <button class="btn btn-primary rounded-pill px-4" id="addFirstMember">
                        <i class="bi bi-plus-lg me-2"></i>Add First Member
                    </button>
                </div>
            `);
        } else {
            let html = '';
            data.forEach(member => {
                const statusBadge = getAccessStatusBadge(member.access_status);
                const qrStatus = member.qr_token ? '<i class="bi bi-qr-code-scan text-success"></i>' : '<i class="bi bi-qr-code text-muted"></i>';
                const currentStatus = member.current_status === 'IN' ?
                    '<span class="badge bg-success bg-opacity-10 text-success rounded-pill small">INSIDE</span>' :
                    '<span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill small">OUTSIDE</span>';

                html += `
                    <div class="col-md-6 col-lg-4">
                        <div class="card border h-100 hover-lift">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                            <i class="bi bi-person-fill text-primary fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">${member.full_name}</h6>
                                            <small class="text-muted">${member.role}</small>
                                        </div>
                                    </div>
                                    ${qrStatus}
                                </div>
                                
                                <div class="small mb-2">
                                    ${statusBadge}
                                    ${currentStatus}
                                </div>
                                
                                ${member.qr_expiry ? `
                                    <div class="small text-muted mb-2">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        Expires: ${member.qr_expiry_formatted || 'N/A'}
                                    </div>
                                ` : ''}
                                
                                ${member.allowed_hours_start ? `
                                    <div class="small text-muted mb-2">
                                        <i class="bi bi-clock me-1"></i>
                                        ${member.allowed_hours_start_formatted} - ${member.allowed_hours_end_formatted}
                                    </div>
                                ` : '<div class="small text-success mb-2"><i class="bi bi-clock me-1"></i>24/7 Access</div>'}
                                
                                <div class="d-flex gap-1 mt-3">
                                    <button class="btn btn-sm btn-light rounded-pill flex-fill edit-family-btn" data-id="${member.id}">
                                        <i class="bi bi-pencil me-1"></i>Edit
                                    </button>
                                    ${member.qr_token ? `
                                        <button class="btn btn-sm btn-success rounded-pill p-2 view-family-qr-btn" data-id="${member.id}" data-token="${member.qr_token}" data-name="${member.full_name}" title="View QR">
                                            <i class="bi bi-qr-code"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning rounded-pill p-2 regen-family-qr-btn" data-id="${member.id}" title="Regenerate QR">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                    ` : `
                                        <button class="btn btn-sm btn-primary rounded-pill p-2 generate-family-qr-btn" data-id="${member.id}" title="Generate QR">
                                            <i class="bi bi-qr-code-scan"></i>
                                        </button>
                                    `}
                                    <button class="btn btn-sm btn-danger rounded-pill p-2 delete-family-btn" data-id="${member.id}" data-name="${member.full_name}" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#familyMembersList').html(html);
        }
    });
}

// Get access status badge
function getAccessStatusBadge(status) {
    const badges = {
        'active': '<span class="badge bg-success bg-opacity-10 text-success rounded-pill small">Active</span>',
        'disabled': '<span class="badge bg-danger bg-opacity-10 text-danger rounded-pill small">Disabled</span>',
        'suspended': '<span class="badge bg-warning bg-opacity-10 text-warning rounded-pill small">Suspended</span>'
    };
    return badges[status] || badges.active;
}

// Add family member button
$(document).on('click', '#addFamilyMemberBtn, #addFirstMember', function () {
    currentFamilyMemberId = null;
    $('#familyMemberModalTitle').text('Add Family Member');
    $('#familyMemberForm')[0].reset();
    $('#fm_homeowner_id').val(currentHomeownerId);
    $('#fm_id').val('');
    $('#generateQRSection').show();
    $('.entry-point-check').prop('checked', false);

    const addEditModal = new bootstrap.Modal(document.getElementById('addEditFamilyMemberModal'));
    addEditModal.show();
});

// Edit family member button
$(document).on('click', '.edit-family-btn', function () {
    const memberId = $(this).data('id');
    currentFamilyMemberId = memberId;

    $('#familyMemberModalTitle').text('Edit Family Member');
    $('#generateQRSection').hide();

    // Fetch member data
    $.get(`api/get_family_members.php?homeowner_id=${currentHomeownerId}`, function (data) {
        const member = data.find(m => m.id == memberId);
        if (member) {
            $('#fm_id').val(member.id);
            $('#fm_homeowner_id').val(member.homeowner_id);
            $('#fm_full_name').val(member.full_name);
            $('#fm_role').val(member.role);
            $('#fm_dob').val(member.date_of_birth);
            $('#fm_access_status').val(member.access_status);
            $('#fm_notes').val(member.relationship_notes);
            $('#fm_hours_start').val(member.allowed_hours_start);
            $('#fm_hours_end').val(member.allowed_hours_end);

            if (member.qr_expiry) {
                const expiryDate = member.qr_expiry.split(' ')[0];
                $('#fm_qr_expiry').val(expiryDate);
            }

            // Set entry points
            $('.entry-point-check').prop('checked', false);
            if (member.allowed_entry_points) {
                try {
                    const entryPoints = JSON.parse(member.allowed_entry_points);
                    entryPoints.forEach(point => {
                        $(`.entry-point-check[value="${point}"]`).prop('checked', true);
                    });
                } catch (e) {
                    console.error('Error parsing entry points', e);
                }
            }

            const addEditModal = new bootstrap.Modal(document.getElementById('addEditFamilyMemberModal'));
            addEditModal.show();
        }
    });
});

// Save family member
$('#saveFamilyMemberBtn').click(function () {
    const formData = new FormData($('#familyMemberForm')[0]);

    // Collect entry points
    const entryPoints = [];
    $('.entry-point-check:checked').each(function () {
        entryPoints.push($(this).val());
    });
    if (entryPoints.length > 0) {
        formData.append('allowed_entry_points', JSON.stringify(entryPoints));
    }

    const isEditing = $('#fm_id').val() !== '';
    const apiUrl = isEditing ? 'api/update_family_member.php' : 'api/add_family_member.php';

    $.ajax({
        url: apiUrl,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    confirmButtonColor: 'var(--primary)'
                });

                // Close the modal
                bootstrap.Modal.getInstance(document.getElementById('addEditFamilyMemberModal')).hide();

                // Reload family members
                loadFamilyMembers(currentHomeownerId);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message,
                    confirmButtonColor: 'var(--danger)'
                });
            }
        },
        error: function () {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to save family member',
                confirmButtonColor: 'var(--danger)'
            });
        }
    });
});

// View QR code
$(document).on('click', '.view-family-qr-btn', function () {
    const token = $(this).data('token');
    const name = $(this).data('name');

    Swal.fire({
        title: name,
        html: `
            <div class="text-center">
                <div id="family-qr-container" class="mb-3"></div>
                <p class="text-muted small">Family Member Access Code</p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-download me-2"></i>Download',
        cancelButtonText: 'Close',
        confirmButtonColor: 'var(--primary)',
        didOpen: () => {
            new QRCode(document.getElementById("family-qr-container"), {
                text: token,
                width: 256,
                height: 256,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            downloadFamilyQR(token, name);
        }
    });
});

// Download QR code
function downloadFamilyQR(token, name) {
    const canvas = document.createElement('canvas');
    canvas.width = 300;
    canvas.height = 300;
    const ctx = canvas.getContext('2d');

    // Generate QR
    QRCode.toCanvas(canvas, token, { width: 300, margin: 2 }, function (error) {
        if (!error) {
            canvas.toBlob(function (blob) {
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `${name.replace(/\s+/g, '_')}_QR.png`;
                link.click();
                URL.revokeObjectURL(url);
            });
        }
    });
}

// Regenerate QR code
$(document).on('click', '.regen-family-qr-btn', function () {
    const memberId = $(this).data('id');

    const today = new Date().toISOString().split('T')[0];
    const nextYear = new Date();
    nextYear.setFullYear(nextYear.getFullYear() + 1);
    const defaultExpiry = nextYear.toISOString().split('T')[0];

    Swal.fire({
        title: 'Regenerate QR Code?',
        html: `
            <p class="text-muted small">The old QR code will immediately stop working!</p>
            <div class="mt-3 text-start">
                <label class="form-label small fw-bold text-muted">Set New Expiry Date (Optional)</label>
                <input type="date" id="swal_family_expiry_date" class="form-control" value="${defaultExpiry}" min="${today}">
                <small class="text-muted">Defaults to 1 year from now if not changed.</small>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'var(--primary)',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, generate new!',
        preConfirm: () => {
            return document.getElementById('swal_family_expiry_date').value;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const expiryDate = result.value;
            $.post('api/generate_family_member_qr.php', { id: memberId, expiry_date: expiryDate }, function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'QR Code Generated!',
                        text: `New expiry: ${response.expiry}`,
                        confirmButtonColor: 'var(--primary)'
                    });
                    loadFamilyMembers(currentHomeownerId);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }, 'json');
        }
    });
});

// Generate QR code (first time)
$(document).on('click', '.generate-family-qr-btn', function () {
    const memberId = $(this).data('id');

    Swal.fire({
        title: 'Generate QR Code?',
        text: 'This will create an access code for this family member',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'var(--primary)',
        confirmButtonText: 'Generate QR'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('api/generate_family_member_qr.php', { id: memberId }, function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'QR Code Generated!',
                        text: `Expires: ${response.expiry}`,
                        confirmButtonColor: 'var(--primary)'
                    });
                    loadFamilyMembers(currentHomeownerId);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }, 'json');
        }
    });
});

// Delete family member
$(document).on('click', '.delete-family-btn', function () {
    const memberId = $(this).data('id');
    const memberName = $(this).data('name');

    Swal.fire({
        title: 'Delete Family Member?',
        html: `Are you sure you want to remove <strong>${memberName}</strong>?<br><small class="text-danger">This action cannot be undone and will delete all associated access logs.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'var(--danger)',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('api/delete_family_member.php', { id: memberId }, function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Family member has been removed',
                        confirmButtonColor: 'var(--primary)'
                    });
                    loadFamilyMembers(currentHomeownerId);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }, 'json');
        }
    });
});
