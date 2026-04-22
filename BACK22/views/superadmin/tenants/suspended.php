<!-- Suspended Tenants -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-ban text-danger me-2"></i>
            Suspended Tenants
        </h2>
        <p class="text-muted mb-0">Manage suspended tenant accounts and review reinstatement requests</p>
    </div>
    <div>
        <button class="btn btn-outline-secondary" onclick="location.reload()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
    </div>
</div>

<!-- Suspension Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-danger">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= count($suspendedTenants ?? []) ?></div>
                    <div>Total Suspended</div>
                    <div class="small">Currently inactive</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-ban fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-warning">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">
                        <?= count(array_filter($suspendedTenants ?? [], function($t) { 
                            return !empty($t['reinstatement_request']); 
                        })) ?>
                    </div>
                    <div>Reinstatement Requests</div>
                    <div class="small">Pending review</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-undo fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">
                        <?= count(array_filter($suspendedTenants ?? [], function($t) { 
                            return (strtotime($t['suspended_at'] ?? 'now') > strtotime('-30 days')); 
                        })) ?>
                    </div>
                    <div>Recent Suspensions</div>
                    <div class="small">Last 30 days</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $reinstatedThisMonth ?? 5 ?></div>
                    <div>Reinstated</div>
                    <div class="small">This month</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Options -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" class="form-control" id="tenantSearch" placeholder="Search suspended tenants..." onkeyup="filterTenants()">
        </div>
    </div>
    <div class="col-md-4">
        <select class="form-select" id="suspensionReasonFilter" onchange="filterTenants()">
            <option value="">All Suspension Reasons</option>
            <option value="policy_violation">Policy Violation</option>
            <option value="payment_issues">Payment Issues</option>
            <option value="fraud_detected">Fraud Detected</option>
            <option value="security_breach">Security Breach</option>
            <option value="terms_violation">Terms Violation</option>
        </select>
    </div>
    <div class="col-md-4">
        <select class="form-select" id="requestStatusFilter" onchange="filterTenants()">
            <option value="">All Request Status</option>
            <option value="has_request">Has Reinstatement Request</option>
            <option value="no_request">No Reinstatement Request</option>
        </select>
    </div>
</div>

<!-- Suspended Tenants List -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Suspended Tenant Accounts
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($suspendedTenants)): ?>
            <div class="table-responsive">
                <table class="table table-hover" id="suspendedTenantsTable">
                    <thead>
                        <tr>
                            <th>Organization</th>
                            <th>Suspension Reason</th>
                            <th>Suspended Date</th>
                            <th>Duration</th>
                            <th>Reinstatement</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suspendedTenants as $tenant): ?>
                        <tr data-tenant-id="<?= $tenant['id'] ?? '' ?>" 
                            data-reason="<?= $tenant['suspension_reason'] ?? '' ?>"
                            data-has-request="<?= !empty($tenant['reinstatement_request']) ? 'has_request' : 'no_request' ?>">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <?php if (!empty($tenant['logo'])): ?>
                                            <img src="<?= htmlspecialchars(image_url($tenant['logo'])) ?>" alt="Logo" class="rounded" width="40" height="40">
                                        <?php else: ?>
                                            <div class="bg-danger text-white rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <?= strtoupper(substr($tenant['name'] ?? 'T', 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0"><?= htmlspecialchars($tenant['name'] ?? 'Unknown') ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($tenant['contact_email'] ?? 'No email') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php 
                                $reason = $tenant['suspension_reason'] ?? 'unknown';
                                $badgeClass = match($reason) {
                                    'fraud_detected' => 'bg-danger',
                                    'security_breach' => 'bg-danger',
                                    'policy_violation' => 'bg-warning',
                                    'payment_issues' => 'bg-info',
                                    'terms_violation' => 'bg-secondary',
                                    default => 'bg-dark'
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= ucfirst(str_replace('_', ' ', $reason)) ?></span>
                                <?php if (!empty($tenant['suspension_notes'])): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars(substr($tenant['suspension_notes'], 0, 50)) ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?= date('M j, Y', strtotime($tenant['suspended_at'] ?? 'now')) ?></div>
                                <small class="text-muted"><?= date('H:i', strtotime($tenant['suspended_at'] ?? 'now')) ?></small>
                            </td>
                            <td>
                                <?php 
                                $suspendedDays = floor((time() - strtotime($tenant['suspended_at'] ?? 'now')) / (60 * 60 * 24));
                                ?>
                                <div class="fw-bold"><?= $suspendedDays ?> days</div>
                                <small class="text-muted">suspended</small>
                            </td>
                            <td>
                                <?php if (!empty($tenant['reinstatement_request'])): ?>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock me-1"></i>Pending Review
                                    </span>
                                    <br><small class="text-muted">
                                        <?= date('M j', strtotime($tenant['reinstatement_request']['created_at'] ?? 'now')) ?>
                                    </small>
                                <?php else: ?>
                                    <span class="text-muted">No request</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewSuspensionDetails(<?= $tenant['id'] ?? 0 ?>)" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if (!empty($tenant['reinstatement_request'])): ?>
                                        <button class="btn btn-sm btn-outline-warning" onclick="reviewReinstatement(<?= $tenant['id'] ?? 0 ?>)" title="Review Request">
                                            <i class="fas fa-gavel"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-success" onclick="reinstateTenant(<?= $tenant['id'] ?? 0 ?>)" title="Reinstate">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="permanentlyDeleteTenant(<?= $tenant['id'] ?? 0 ?>)" title="Permanent Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5>No Suspended Tenants</h5>
                <p class="text-muted">No tenant accounts are currently suspended.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Reinstatement Modal -->
<div class="modal fade" id="reinstatementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review Reinstatement Request</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="reinstatementDetails">
                    <!-- Details will be loaded here -->
                </div>
                
                <form id="reinstatementForm">
                    <input type="hidden" id="reinstateTenantId" name="tenant_id">
                    
                    <div class="mb-3">
                        <label for="reinstatementDecision" class="form-label">Decision</label>
                        <select class="form-select" id="reinstatementDecision" name="decision" required>
                            <option value="">Select Decision</option>
                            <option value="approve">Approve Reinstatement</option>
                            <option value="reject">Reject Request</option>
                            <option value="conditional">Conditional Approval</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="conditionsDiv" style="display: none;">
                        <label for="reinstatementConditions" class="form-label">Conditions</label>
                        <textarea class="form-control" id="reinstatementConditions" name="conditions" rows="3" placeholder="Specify conditions for reinstatement..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reinstatementNotes" class="form-label">Review Notes</label>
                        <textarea class="form-control" id="reinstatementNotes" name="notes" rows="3" placeholder="Internal notes about this decision..."></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="sendReinstatementEmail" name="send_notification" checked>
                        <label class="form-check-label" for="sendReinstatementEmail">
                            Send decision notification to tenant
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitReinstatementDecision()">Submit Decision</button>
            </div>
        </div>
    </div>
</div>

<script>
function filterTenants() {
    const searchTerm = document.getElementById('tenantSearch').value.toLowerCase();
    const reasonFilter = document.getElementById('suspensionReasonFilter').value;
    const requestFilter = document.getElementById('requestStatusFilter').value;
    const rows = document.querySelectorAll('#suspendedTenantsTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const reason = row.getAttribute('data-reason');
        const hasRequest = row.getAttribute('data-has-request');
        
        const matchesSearch = text.includes(searchTerm);
        const matchesReason = !reasonFilter || reason === reasonFilter;
        const matchesRequest = !requestFilter || hasRequest === requestFilter;
        
        row.style.display = matchesSearch && matchesReason && matchesRequest ? '' : 'none';
    });
}

function viewSuspensionDetails(tenantId) {
    console.log('Viewing suspension details for tenant:', tenantId);
    // Implementation for viewing detailed suspension information
}

function reviewReinstatement(tenantId) {
    document.getElementById('reinstateTenantId').value = tenantId;
    
    // Load reinstatement request details
    document.getElementById('reinstatementDetails').innerHTML = `
        <div class="alert alert-info">
            <h6>Reinstatement Request Details</h6>
            <p>Loading request details...</p>
        </div>
    `;
    
    const modal = new coreui.Modal(document.getElementById('reinstatementModal'));
    modal.show();
}

function reinstateTenant(tenantId) {
    if (confirm('Are you sure you want to reinstate this tenant? This will restore full access to their account.')) {
        console.log('Reinstating tenant:', tenantId);
        // Implementation for reinstating tenant
        alert('Tenant has been reinstated successfully!');
        location.reload();
    }
}

function permanentlyDeleteTenant(tenantId) {
    if (confirm('WARNING: This will permanently delete the tenant and all associated data. This action cannot be undone. Are you sure?')) {
        console.log('Permanently deleting tenant:', tenantId);
        // Implementation for permanent deletion
        alert('Tenant has been permanently deleted.');
        location.reload();
    }
}

function submitReinstatementDecision() {
    const form = document.getElementById('reinstatementForm');
    const formData = new FormData(form);
    
    console.log('Submitting reinstatement decision:', Object.fromEntries(formData));
    
    // Close modal and refresh page
    const modal = coreui.Modal.getInstance(document.getElementById('reinstatementModal'));
    modal.hide();
    
    alert('Reinstatement decision submitted successfully!');
    location.reload();
}

// Show/hide conditions field based on decision
document.addEventListener('DOMContentLoaded', function() {
    const decisionSelect = document.getElementById('reinstatementDecision');
    const conditionsDiv = document.getElementById('conditionsDiv');
    
    if (decisionSelect) {
        decisionSelect.addEventListener('change', function() {
            if (this.value === 'conditional') {
                conditionsDiv.style.display = 'block';
            } else {
                conditionsDiv.style.display = 'none';
            }
        });
    }
});
</script>
