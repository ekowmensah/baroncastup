<?php
require_once __DIR__ . '/../../../src/Helpers/UssdHelper.php';
$baseCode = \SmartCast\Helpers\UssdHelper::getBaseCodeFormatted();
?>
<!-- USSD Management Dashboard -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-mobile-alt text-primary me-2"></i>
            USSD Management
        </h2>
        <p class="text-muted mb-0">Manage USSD codes and settings for all tenants</p>
    </div>
    <div>
        <button class="btn btn-outline-secondary" onclick="location.reload()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
    </div>
</div>

<!-- USSD Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-primary">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $stats['total_tenants'] ?? 0 ?></div>
                    <div>Total Tenants</div>
                    <div class="small">All organizations</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-building fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $stats['assigned_tenants'] ?? 0 ?></div>
                    <div>Assigned Codes</div>
                    <div class="small">With USSD codes</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-hashtag fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $stats['active_tenants'] ?? 0 ?></div>
                    <div>Active USSD</div>
                    <div class="small">Currently enabled</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-warning">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= count($availableCodes ?? []) ?></div>
                    <div>Available Codes</div>
                    <div class="small">Unassigned (1-999)</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-list fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tenants Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-table me-2"></i>Tenant USSD Codes
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="tenantsTable">
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>USSD Code</th>
                        <th>Welcome Message</th>
                        <th>Status</th>
                        <th>Sessions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tenants as $tenant): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($tenant['name']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($tenant['email'] ?? '') ?></small>
                        </td>
                        <td>
                            <?php if ($tenant['ussd_code']): ?>
                                <span class="badge bg-primary"><?= htmlspecialchars(\SmartCast\Helpers\UssdHelper::formatUssdCode($tenant['ussd_code'])) ?></span>
                            <?php else: ?>
                                <span class="text-muted">Not assigned</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($tenant['ussd_welcome_message']): ?>
                                <small><?= htmlspecialchars(substr($tenant['ussd_welcome_message'], 0, 40)) ?><?= strlen($tenant['ussd_welcome_message']) > 40 ? '...' : '' ?></small>
                            <?php else: ?>
                                <span class="text-muted">Default</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($tenant['ussd_enabled']): ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Active
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-times-circle me-1"></i>Disabled
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="text-muted">-</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <?php if ($tenant['ussd_code']): ?>
                                    <button class="btn btn-outline-primary" onclick="editUssdCode(<?= $tenant['id'] ?>, '<?= $tenant['ussd_code'] ?>', '<?= htmlspecialchars($tenant['ussd_welcome_message'] ?? '', ENT_QUOTES) ?>')">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-outline-<?= $tenant['ussd_enabled'] ? 'warning' : 'success' ?>" onclick="toggleUssdStatus(<?= $tenant['id'] ?>, <?= $tenant['ussd_enabled'] ? 0 : 1 ?>)">
                                        <i class="fas fa-<?= $tenant['ussd_enabled'] ? 'pause' : 'play' ?>"></i> <?= $tenant['ussd_enabled'] ? 'Disable' : 'Enable' ?>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="revokeUssdCode(<?= $tenant['id'] ?>)">
                                        <i class="fas fa-times"></i> Revoke
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-primary" onclick="assignUssdCode(<?= $tenant['id'] ?>, '<?= htmlspecialchars($tenant['name'], ENT_QUOTES) ?>')">
                                        <i class="fas fa-plus"></i> Assign Code
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Assign USSD Code Modal -->
<div class="modal fade" id="assignUssdModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign USSD Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assignUssdForm">
                    <input type="hidden" id="assign_tenant_id" name="tenant_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Tenant</label>
                        <input type="text" class="form-control" id="assign_tenant_name" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">USSD Code</label>
                        <div class="input-group">
                            <span class="input-group-text"><?= htmlspecialchars($baseCode) ?></span>
                            <select class="form-select" id="assign_ussd_code" name="ussd_code" required>
                                <option value="">Select code...</option>
                                <?php foreach ($availableCodes as $code): ?>
                                    <option value="<?= $code ?>"><?= $code ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="input-group-text">#</span>
                        </div>
                        <small class="text-muted">Choose an available code (1-999)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Welcome Message (Optional)</label>
                        <textarea class="form-control" id="assign_welcome_message" name="welcome_message" rows="3" placeholder="Welcome to [Tenant Name]!"></textarea>
                        <small class="text-muted">Custom message shown when users dial the USSD code</small>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Preview:</strong> <?= htmlspecialchars($baseCode) ?><span id="preview_code">XX</span># will be assigned to this tenant
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitAssignUssd()">
                    <i class="fas fa-check me-2"></i>Assign Code
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Update preview when code is selected
    const codeSelect = document.getElementById('assign_ussd_code');
    if (codeSelect) {
        codeSelect.addEventListener('change', function() {
            document.getElementById('preview_code').textContent = this.value || 'XX';
        });
    }
});

function assignUssdCode(tenantId, tenantName) {
    document.getElementById('assign_tenant_id').value = tenantId;
    document.getElementById('assign_tenant_name').value = tenantName;
    document.getElementById('assign_ussd_code').value = '';
    document.getElementById('assign_welcome_message').value = '';
    document.getElementById('preview_code').textContent = 'XX';
    
    var modal = new bootstrap.Modal(document.getElementById('assignUssdModal'));
    modal.show();
}

function editUssdCode(tenantId, currentCode, welcomeMessage) {
    document.getElementById('assign_tenant_id').value = tenantId;
    document.getElementById('assign_ussd_code').value = currentCode;
    document.getElementById('assign_welcome_message').value = welcomeMessage;
    document.getElementById('preview_code').textContent = currentCode;
    
    var modal = new bootstrap.Modal(document.getElementById('assignUssdModal'));
    modal.show();
}

function submitAssignUssd() {
    const formData = new FormData(document.getElementById('assignUssdForm'));
    
    fetch('<?= SUPERADMIN_URL ?>/ussd/assign', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function toggleUssdStatus(tenantId, enabled) {
    if (!confirm('Are you sure you want to ' + (enabled ? 'enable' : 'disable') + ' USSD for this tenant?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('tenant_id', tenantId);
    formData.append('enabled', enabled);
    
    fetch('<?= SUPERADMIN_URL ?>/ussd/toggle', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function revokeUssdCode(tenantId) {
    if (!confirm('Are you sure you want to revoke the USSD code from this tenant? This will disable their USSD voting.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('tenant_id', tenantId);
    
    fetch('<?= SUPERADMIN_URL ?>/ussd/revoke', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}
</script>
