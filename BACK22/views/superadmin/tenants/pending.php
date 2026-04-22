<!-- Pending Tenants -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-clock text-warning me-2"></i>
            Pending Tenant Approvals
        </h2>
        <p class="text-muted mb-0">Review and approve new tenant applications</p>
    </div>
    <div>
        <button class="btn btn-outline-secondary" onclick="location.reload()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
    </div>
</div>

<!-- Pending Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-warning">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= count($pendingTenants ?? []) ?></div>
                    <div>Pending Approval</div>
                    <div class="small">Awaiting review</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-clock fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">
                        <?= count(array_filter($pendingTenants ?? [], function($t) { 
                            return (strtotime($t['created_at'] ?? 'now') > strtotime('-7 days')); 
                        })) ?>
                    </div>
                    <div>This Week</div>
                    <div class="small">New applications</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-calendar-week fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $approvedThisMonth ?? 15 ?></div>
                    <div>Approved</div>
                    <div class="small">This month</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-danger">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $rejectedThisMonth ?? 3 ?></div>
                    <div>Rejected</div>
                    <div class="small">This month</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-times-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Tenants List -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Tenant Applications Awaiting Review
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($pendingTenants)): ?>
            <div class="row">
                <?php foreach ($pendingTenants as $tenant): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card tenant-card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><?= htmlspecialchars($tenant['name'] ?? 'Unknown Organization') ?></h6>
                            <span class="badge bg-warning">Pending</span>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <strong>Contact Person:</strong><br>
                                    <span><?= htmlspecialchars($tenant['contact_name'] ?? 'N/A') ?></span>
                                </div>
                                <div class="col-sm-6">
                                    <strong>Email:</strong><br>
                                    <span><?= htmlspecialchars($tenant['contact_email'] ?? 'N/A') ?></span>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <strong>Phone:</strong><br>
                                    <span><?= htmlspecialchars($tenant['contact_phone'] ?? 'N/A') ?></span>
                                </div>
                                <div class="col-sm-6">
                                    <strong>Industry:</strong><br>
                                    <span><?= htmlspecialchars($tenant['industry'] ?? 'N/A') ?></span>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Organization Description:</strong><br>
                                <p class="text-muted small"><?= htmlspecialchars($tenant['description'] ?? 'No description provided') ?></p>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <strong>Expected Users:</strong><br>
                                    <span><?= number_format($tenant['expected_users'] ?? 0) ?></span>
                                </div>
                                <div class="col-sm-6">
                                    <strong>Applied:</strong><br>
                                    <span><?= date('M j, Y', strtotime($tenant['created_at'] ?? 'now')) ?></span>
                                </div>
                            </div>
                            
                            <?php if (!empty($tenant['business_documents'])): ?>
                            <div class="mb-3">
                                <strong>Documents:</strong><br>
                                <?php foreach ($tenant['business_documents'] as $doc): ?>
                                    <a href="<?= htmlspecialchars($doc['url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary me-1 mb-1">
                                        <i class="fas fa-file-alt me-1"></i><?= htmlspecialchars($doc['name']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <strong>Requested Plan:</strong><br>
                                <span class="badge bg-info"><?= htmlspecialchars($tenant['requested_plan'] ?? 'Basic') ?></span>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-success" onclick="approveTenant(<?= $tenant['id'] ?? 0 ?>)">
                                    <i class="fas fa-check me-1"></i>Approve
                                </button>
                                <button class="btn btn-info" onclick="viewTenantDetails(<?= $tenant['id'] ?? 0 ?>)">
                                    <i class="fas fa-eye me-1"></i>Details
                                </button>
                                <button class="btn btn-danger" onclick="rejectTenant(<?= $tenant['id'] ?? 0 ?>)">
                                    <i class="fas fa-times me-1"></i>Reject
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5>No Pending Applications</h5>
                <p class="text-muted">All tenant applications have been reviewed.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Tenant Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="approvalForm">
                    <input type="hidden" id="tenantId" name="tenant_id">
                    
                    <div class="mb-3">
                        <label for="approvalPlan" class="form-label">Assign Plan</label>
                        <select class="form-select" id="approvalPlan" name="plan" required>
                            <option value="">Select Plan</option>
                            <option value="basic">Basic Plan</option>
                            <option value="professional">Professional Plan</option>
                            <option value="enterprise">Enterprise Plan</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="approvalNotes" class="form-label">Approval Notes</label>
                        <textarea class="form-control" id="approvalNotes" name="notes" rows="3" placeholder="Optional notes for the tenant..."></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="sendWelcomeEmail" name="send_welcome" checked>
                        <label class="form-check-label" for="sendWelcomeEmail">
                            Send welcome email to tenant
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmApproval()">Approve Tenant</button>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Tenant Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="rejectionForm">
                    <input type="hidden" id="rejectTenantId" name="tenant_id">
                    
                    <div class="mb-3">
                        <label for="rejectionReason" class="form-label">Reason for Rejection</label>
                        <select class="form-select" id="rejectionReason" name="reason" required>
                            <option value="">Select Reason</option>
                            <option value="incomplete_information">Incomplete Information</option>
                            <option value="invalid_documents">Invalid Documents</option>
                            <option value="business_not_eligible">Business Not Eligible</option>
                            <option value="suspicious_activity">Suspicious Activity</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="rejectionNotes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="rejectionNotes" name="notes" rows="3" placeholder="Provide detailed explanation for rejection..." required></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="sendRejectionEmail" name="send_notification" checked>
                        <label class="form-check-label" for="sendRejectionEmail">
                            Send rejection notification to applicant
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmRejection()">Reject Application</button>
            </div>
        </div>
    </div>
</div>

<script>
function approveTenant(tenantId) {
    document.getElementById('tenantId').value = tenantId;
    const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
    modal.show();
}

function rejectTenant(tenantId) {
    document.getElementById('rejectTenantId').value = tenantId;
    const modal = new bootstrap.Modal(document.getElementById('rejectionModal'));
    modal.show();
}

function viewTenantDetails(tenantId) {
    // Implementation for viewing detailed tenant information
    console.log('Viewing tenant details:', tenantId);
}

function confirmApproval() {
    const form = document.getElementById('approvalForm');
    const formData = new FormData(form);
    
    if (!formData.get('tenant_id')) {
        alert('Error: No tenant selected');
        return;
    }
    
    fetch(`<?= SUPERADMIN_URL ?>/tenants/approve`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('approvalModal'));
        modal.hide();
        
        if (data.success) {
            alert(`✅ ${data.tenant_name || 'Tenant'} has been approved successfully!`);
            location.reload();
        } else {
            alert('Error approving tenant: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error approving tenant');
        const modal = bootstrap.Modal.getInstance(document.getElementById('approvalModal'));
        modal.hide();
    });
}

function confirmRejection() {
    const form = document.getElementById('rejectionForm');
    const formData = new FormData(form);
    
    if (!formData.get('tenant_id') || !formData.get('reason') || !formData.get('notes')) {
        alert('Please fill in all required fields');
        return;
    }
    
    // Combine reason and notes for the rejection reason
    const reason = formData.get('reason');
    const notes = formData.get('notes');
    const combinedReason = reason === 'other' ? notes : `${reason}: ${notes}`;
    
    const rejectionData = new FormData();
    rejectionData.append('tenant_id', formData.get('tenant_id'));
    rejectionData.append('reason', combinedReason);
    
    fetch(`<?= SUPERADMIN_URL ?>/tenants/reject`, {
        method: 'POST',
        body: rejectionData
    })
    .then(response => response.json())
    .then(data => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('rejectionModal'));
        modal.hide();
        
        if (data.success) {
            alert(`❌ ${data.tenant_name || 'Tenant'} application has been rejected.`);
            location.reload();
        } else {
            alert('Error rejecting tenant: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error rejecting tenant');
        const modal = bootstrap.Modal.getInstance(document.getElementById('rejectionModal'));
        modal.hide();
    });
}
</script>
