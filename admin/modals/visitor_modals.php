<!-- Add Visitor Modal -->
<div class="modal fade" id="addVisitorModal" tabindex="-1" aria-labelledby="addVisitorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold" id="addVisitorModalLabel">
                    <i class="bi bi-person-plus text-primary me-2"></i>
                    Log New Visitor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="addVisitorForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Visitor Full Name *</label>
                            <input type="text" class="form-control rounded-3 p-2 px-3" name="visitor_name" placeholder="John Doe" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Visit Category</label>
                            <select class="form-select rounded-3 p-2 px-3" name="visitor_type" id="modal_visitor_type">
                                <option value="Personal">Personal Visit</option>
                                <option value="Service">Delivery / Service</option>
                            </select>
                        </div>
                        <div class="col-md-12" id="company_field_wrapper" style="display: none;">
                            <label class="form-label small fw-bold text-muted">Company / Organization</label>
                            <input type="text" class="form-control rounded-3 p-2 px-3" name="company" id="modal_company" placeholder="e.g. Grab, Shopee, Meralco">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-muted">Resident to Visit *</label>
                            <div class="position-relative" id="customSearchWrapper">
                                <input type="text" class="form-control rounded-3 p-2 px-3" 
                                       id="residentSearchInput" 
                                       placeholder="Type to search resident name..." 
                                       autocomplete="off" 
                                       required>
                                <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                                
                                <!-- Custom Dropdown List -->
                                <ul id="residentResultsList" class="list-group position-absolute w-100 mt-1 shadow-lg" style="z-index: 1050; max-height: 200px; overflow-y: auto; display: none;">
                                    <?php
                                    try {
                                        $hStmt = $pdo->query("SELECT id, name, address, homeowner_id FROM homeowners WHERE status = 'active' ORDER BY name ASC");
                                        while($h = $hStmt->fetch()) {
                                            $display = "{$h['name']} - {$h['address']} ({$h['homeowner_id']})";
                                            echo "<li class=\"list-group-item list-group-item-action py-2 fs-7 resident-item\" 
                                                      data-id=\"{$h['id']}\" 
                                                      data-name=\"{$h['name']}\" 
                                                      style=\"cursor: pointer;\">{$display}</li>";
                                        }
                                    } catch(Exception $e) {}
                                    ?>
                                </ul>
                                <!-- Hidden field for actual ID to be submitted -->
                                <input type="hidden" name="homeowner_id" id="modal_homeowner_id">
                                <!-- Hidden field for the person_to_visit record -->
                                <input type="hidden" name="person_to_visit" id="modal_person_to_visit">
                            </div>
                            <div id="selectionFeedback" class="small mt-2 text-success" style="display: none;">
                                <i class="bi bi-check2-circle"></i> Resident selected: <strong id="selectedResidentLabel"></strong>
                            </div>
                        </div>
                        
                        <!-- Fixed Gate -->
                        <input type="hidden" name="gate" value="Main Gate">

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">QR Pass Expiry Date</label>
                            <input type="date" class="form-control rounded-3 p-2 px-3" name="qr_expiry" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Purpose of Visit</label>
                            <input type="text" class="form-control rounded-3 p-2 px-3" name="purpose" id="modal_purpose" placeholder="Hangout, Social visit, etc.">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="saveVisitorBtn">
                    <i class="bi bi-check2-circle me-1"></i> Log Visitor & Generate QR
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .resident-item:hover { background-color: #f8f9fa; }
    .fs-7 { font-size: 0.875rem !important; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('modal_visitor_type');
        const companyWrapper = document.getElementById('company_field_wrapper');
        const searchInput = document.getElementById('residentSearchInput');
        const resultsList = document.getElementById('residentResultsList');
        const residentItems = document.querySelectorAll('.resident-item');
        const hiddenId = document.getElementById('modal_homeowner_id');
        const hiddenName = document.getElementById('modal_person_to_visit');
        const feedback = document.getElementById('selectionFeedback');
        const label = document.getElementById('selectedResidentLabel');
        const purposeInput = document.getElementById('modal_purpose');

        // Handle Category Toggles
        typeSelect?.addEventListener('change', function() {
            if (this.value === 'Service') {
                companyWrapper.style.display = 'block';
                purposeInput.placeholder = 'Repair, Delivery, Maintenance, etc.';
            } else {
                companyWrapper.style.display = 'none';
                document.getElementById('modal_company').value = '';
                purposeInput.placeholder = 'Hangout, Social visit, etc.';
            }
        });

        // Custom Searchable Logic
        searchInput?.addEventListener('focus', () => { if (searchInput.value.length > 0) resultsList.style.display = 'block'; });
        
        searchInput?.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            let hasResults = false;

            if (filter.length === 0) {
                resultsList.style.display = 'none';
                return;
            }

            residentItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(filter)) {
                    item.style.display = 'block';
                    hasResults = true;
                } else {
                    item.style.display = 'none';
                }
            });

            resultsList.style.display = hasResults ? 'block' : 'none';
        });

        // Selection Logic
        residentItems.forEach(item => {
            item.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const fullName = this.textContent;

                searchInput.value = fullName;
                hiddenId.value = id;
                hiddenName.value = name;
                
                label.textContent = name;
                feedback.style.display = 'block';
                resultsList.style.display = 'none';
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!document.getElementById('customSearchWrapper').contains(e.target)) {
                resultsList.style.display = 'none';
            }
        });

        // Reset modal state
        $('#addVisitorModal').on('show.bs.modal', function() {
            document.getElementById('addVisitorForm').reset();
            companyWrapper.style.display = 'none';
            feedback.style.display = 'none';
            resultsList.style.display = 'none';
            hiddenId.value = '';
            hiddenName.value = '';
            searchInput.value = '';
        });
    });
</script>

<!-- View Visitor Details Modal -->
<div class="modal fade" id="viewVisitorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-person-badge text-primary me-2"></i>
                    Visitor Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4 align-items-center">
                    <div class="col-md-8">
                        <div class="bg-light p-4 rounded-4 border border-white">
                            <div class="row g-3">
                                <div class="col-sm-6 text-muted small fw-bold text-uppercase">Visitor Name</div>
                                <div class="col-sm-6 fw-bold" id="viewVisitorName">--</div>
                                
                                <div class="col-sm-6 text-muted small fw-bold text-uppercase">Time In</div>
                                <div class="col-sm-6 fw-bold text-success" id="viewTimeIn">--</div>
                                
                                <div class="col-sm-6 text-muted small fw-bold text-uppercase">Time Out</div>
                                <div class="col-sm-6 fw-bold text-muted" id="viewTimeOut">--</div>
                                
                                <div class="col-sm-6 text-muted small fw-bold text-uppercase">Host / Resident</div>
                                <div class="col-sm-6" id="viewHostName">--</div>
                                
                                <div class="col-sm-6 text-muted small fw-bold text-uppercase">Address</div>
                                <div class="col-sm-6 small" id="viewHostAddress">--</div>
                                
                                <div class="col-sm-6 text-muted small fw-bold text-uppercase">Purpose</div>
                                <div class="col-sm-6" id="viewPurpose">--</div>
                                
                                <div class="col-sm-6 text-muted small fw-bold text-uppercase">Entry Gate</div>
                                <div class="col-sm-6" id="viewGate">--</div>

                                <div class="col-sm-12 mt-3" id="qr_expiry_alert">
                                    <div class="alert alert-info border-0 shadow-sm rounded-4 d-flex align-items-center mb-0 px-3 py-2">
                                        <i class="bi bi-shield-check fs-5 me-3"></i>
                                        <div>
                                            <div class="small fw-bold" id="view_qr_expiry">Valid until: --</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="card border-0 bg-white p-3 shadow-sm mx-auto mb-3" style="width: 170px; height: 170px;">
                            <div id="view_visitor_qr" class="w-100 h-100 d-flex align-items-center justify-content-center bg-light rounded shadow-inner overflow-hidden">
                                <i class="bi bi-qr-code fs-1 text-muted"></i>
                            </div>
                        </div>
                        <button class="btn btn-outline-primary btn-sm rounded-pill px-4 mb-2 w-100" id="downloadVisitorQR">
                            <i class="bi bi-download me-1"></i> Download Pass
                        </button>
                        <button class="btn btn-light btn-sm rounded-pill px-4 w-100 regenerate-qr-btn">
                            <i class="bi bi-arrow-clockwise me-1"></i> New Pass
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4 w-100" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Visitor Modal -->
<div class="modal fade" id="editVisitorModal" tabindex="-1" aria-labelledby="editVisitorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold" id="editVisitorModalLabel">
                    <i class="bi bi-pencil-square text-warning me-2"></i>
                    Update Visitor Info
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editVisitorForm">
                    <input type="hidden" name="id" id="edit_visitor_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Visitor Full Name *</label>
                            <input type="text" class="form-control rounded-3 p-2 px-3" name="visitor_name" id="edit_visitor_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Visit Category</label>
                            <select class="form-select rounded-3 p-2 px-3" name="visitor_type" id="edit_visitor_type">
                                <option value="Personal">Personal Visit</option>
                                <option value="Service">Delivery / Service</option>
                            </select>
                        </div>
                        <div class="col-md-12" id="edit_company_field_wrapper" style="display: none;">
                            <label class="form-label small fw-bold text-muted">Company / Organization</label>
                            <input type="text" class="form-control rounded-3 p-2 px-3" name="company" id="edit_company" placeholder="e.g. Grab, Shopee, Meralco">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-muted">Resident to Visit *</label>
                            <div class="position-relative" id="edit_customSearchWrapper">
                                <input type="text" class="form-control rounded-3 p-2 px-3" 
                                       id="edit_residentSearchInput" 
                                       placeholder="Type to search resident name..." 
                                       autocomplete="off" 
                                       required>
                                <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                                
                                <ul id="edit_residentResultsList" class="list-group position-absolute w-100 mt-1 shadow-lg" style="z-index: 1050; max-height: 200px; overflow-y: auto; display: none;">
                                    <?php
                                    try {
                                        $hStmt = $pdo->query("SELECT id, name, address, homeowner_id FROM homeowners WHERE status = 'active' ORDER BY name ASC");
                                        while($h = $hStmt->fetch()) {
                                            $display = "{$h['name']} - {$h['address']} ({$h['homeowner_id']})";
                                            echo "<li class=\"list-group-item list-group-item-action py-2 fs-7 edit-resident-item\" 
                                                      data-id=\"{$h['id']}\" 
                                                      data-name=\"{$h['name']}\" 
                                                      style=\"cursor: pointer;\">{$display}</li>";
                                        }
                                    } catch(Exception $e) {}
                                    ?>
                                </ul>
                                <input type="hidden" name="homeowner_id" id="edit_modal_homeowner_id">
                                <input type="hidden" name="person_to_visit" id="edit_modal_person_to_visit">
                            </div>
                            <div id="edit_selectionFeedback" class="small mt-2 text-success" style="display: none;">
                                <i class="bi bi-check2-circle"></i> Resident selected: <strong id="edit_selectedResidentLabel"></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">QR Pass Expiry Date</label>
                            <input type="date" class="form-control rounded-3 p-2 px-3" name="qr_expiry" id="edit_qr_expiry">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Purpose of Visit</label>
                            <input type="text" class="form-control rounded-3 p-2 px-3" name="purpose" id="edit_purpose" placeholder="Hangout, Social visit, etc.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Status</label>
                            <select class="form-select rounded-3 p-2 px-3" name="status" id="edit_status">
                                <option value="INSIDE">INSIDE</option>
                                <option value="OUT">OUT</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="updateVisitorBtn">
                    <i class="bi bi-check2-circle me-1"></i> Update Registry
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editTypeSelect = document.getElementById('edit_visitor_type');
        const editCompanyWrapper = document.getElementById('edit_company_field_wrapper');
        const editSearchInput = document.getElementById('edit_residentSearchInput');
        const editResultsList = document.getElementById('edit_residentResultsList');
        const editResidentItems = document.querySelectorAll('.edit-resident-item');
        const editHiddenId = document.getElementById('edit_modal_homeowner_id');
        const editHiddenName = document.getElementById('edit_modal_person_to_visit');
        const editFeedback = document.getElementById('edit_selectionFeedback');
        const editLabel = document.getElementById('edit_selectedResidentLabel');
        const editPurposeInput = document.getElementById('edit_purpose');

        editTypeSelect?.addEventListener('change', function() {
            if (this.value === 'Service') {
                editCompanyWrapper.style.display = 'block';
                editPurposeInput.placeholder = 'Repair, Delivery, Maintenance, etc.';
            } else {
                editCompanyWrapper.style.display = 'none';
                document.getElementById('edit_company').value = '';
                editPurposeInput.placeholder = 'Hangout, Social visit, etc.';
            }
        });

        editSearchInput?.addEventListener('focus', () => { if (editSearchInput.value.length > 0) editResultsList.style.display = 'block'; });
        editSearchInput?.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            let hasResults = false;
            if (filter.length === 0) { editResultsList.style.display = 'none'; return; }
            editResidentItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(filter)) { item.style.display = 'block'; hasResults = true; } 
                else { item.style.display = 'none'; }
            });
            editResultsList.style.display = hasResults ? 'block' : 'none';
        });

        editResidentItems.forEach(item => {
            item.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const fullName = this.textContent;
                editSearchInput.value = fullName;
                editHiddenId.value = id;
                editHiddenName.value = name;
                editLabel.textContent = name;
                editFeedback.style.display = 'block';
                editResultsList.style.display = 'none';
            });
        });

        document.addEventListener('click', (e) => {
            if (!document.getElementById('edit_customSearchWrapper')?.contains(e.target)) {
                editResultsList.style.display = 'none';
            }
        });
    });
</script>
