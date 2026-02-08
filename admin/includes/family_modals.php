<!-- Family Management Modal -->
<div class="modal fade" id="familyManagementModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 p-4 pb-3 bg-primary bg-opacity-10">
                <div>
                    <h5 class="modal-title fw-bold mb-1">
                        <i class="bi bi-people-fill text-primary me-2"></i>
                        Family Members - <span id="family_homeowner_name"></span>
                    </h5>
                    <p class="small text-muted mb-0">Manage household members and their access permissions</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h6 class="fw-bold mb-0">Household Members</h6>
                        <small class="text-muted">Each member can have individual QR codes and access controls</small>
                    </div>
                    <button class="btn btn-primary rounded-pill px-4" id="addFamilyMemberBtn">
                        <i class="bi bi-plus-lg me-2"></i>Add Family Member
                    </button>
                </div>

                <div id="familyMembersList" class="row g-3">
                    <!-- Family members will be loaded here dynamically -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Family Member Modal -->
<div class="modal fade" id="addEditFamilyMemberModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-person-plus text-primary me-2"></i>
                    <span id="familyMemberModalTitle">Add Family Member</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="familyMemberForm">
                    <input type="hidden" name="id" id="fm_id">
                    <input type="hidden" name="homeowner_id" id="fm_homeowner_id">
                    
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-muted">Full Name *</label>
                            <input type="text" class="form-control rounded-3 p-2 px-3" name="full_name" id="fm_full_name" placeholder="e.g., Juan Dela Cruz Jr." required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Role *</label>
                            <select class="form-select rounded-3" name="role" id="fm_role" required>
                                <option value="Owner">Owner</option>
                                <option value="Spouse">Spouse</option>
                                <option value="Child" selected>Child</option>
                                <option value="Relative">Relative</option>
                                <option value="Caregiver">Caregiver</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Date of Birth</label>
                            <input type="date" class="form-control rounded-3" name="date_of_birth" id="fm_dob">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Access Status</label>
                            <select class="form-select rounded-3" name="access_status" id="fm_access_status">
                                <option value="active">Active</option>
                                <option value="disabled">Disabled</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Relationship Notes</label>
                            <textarea class="form-control rounded-3" name="relationship_notes" id="fm_notes" rows="2" placeholder="Additional information about this family member"></textarea>
                        </div>
                        
                        <div class="col-12">
                            <hr class="my-2">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="bi bi-shield-lock me-2"></i>Access Control Settings
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Allowed Hours Start</label>
                            <input type="time" class="form-control rounded-3" name="allowed_hours_start" id="fm_hours_start">
                            <small class="text-muted">Leave empty for 24/7 access</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Allowed Hours End</label>
                            <input type="time" class="form-control rounded-3" name="allowed_hours_end" id="fm_hours_end">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">QR Code Expiry</label>
                            <input type="date" class="form-control rounded-3" name="qr_expiry" id="fm_qr_expiry">
                            <small class="text-muted">Defaults to 1 year from generation if not set</small>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Allowed Entry Points</label>
                            <div class="d-flex gap-3 flex-wrap">
                                <div class="form-check">
                                    <input class="form-check-input entry-point-check" type="checkbox" value="Entry Gate" id="entry_gate">
                                    <label class="form-check-label" for="entry_gate">Entry Gate</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input entry-point-check" type="checkbox" value="Exit Gate" id="exit_gate">
                                    <label class="form-check-label" for="exit_gate">Exit Gate</label>
                                </div>
                            </div>
                            <small class="text-muted">Leave all unchecked to allow access through any gate</small>
                        </div>
                        
                        <div class="col-12 mt-3" id="generateQRSection">
                            <div class="form-check form-switch bg-light p-3 rounded-3 border">
                                <div class="ps-4">
                                    <input class="form-check-input" type="checkbox" name="generate_qr" id="fm_generate_qr" checked>
                                    <label class="form-check-label fw-bold" for="fm_generate_qr">
                                        Auto-generate QR Access Code
                                    </label>
                                    <p class="small text-muted mb-0">Generate a unique QR code for this family member</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="saveFamilyMemberBtn">
                    <i class="bi bi-save me-1"></i>Save Member
                </button>
            </div>
        </div>
    </div>
</div>
