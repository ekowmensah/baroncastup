<?php
// SMS Gateways Management View Content
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-server text-primary me-2"></i>
            SMS Gateways
        </h2>
        <p class="text-muted mb-0">Manage SMS gateway configurations and settings</p>
    </div>
    <div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGatewayModal">
            <i class="fas fa-plus me-2"></i>Add Gateway
        </button>
    </div>
</div>

<!-- Gateway Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary">
                <i class="fas fa-server"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= count($gateways ?? []) ?></div>
                <div class="stat-label">Total Gateways</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= count(array_filter($gateways ?? [], function($g) { return $g['is_active']; })) ?></div>
                <div class="stat-label">Active Gateways</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-info">
                <i class="fas fa-paper-plane"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($totalSms ?? 0) ?></div>
                <div class="stat-label">Total SMS Sent</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($successRate ?? 0, 1) ?>%</div>
                <div class="stat-label">Success Rate</div>
            </div>
        </div>
    </div>
</div>

        <!-- Gateways List -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">SMS Gateways</h5>
            </div>
            <div class="card-body">
                <?php if (empty($gateways)): ?>
                    <div class="empty-state text-center py-5">
                        <i class="fas fa-server fa-3x text-muted mb-3"></i>
                        <h5>No SMS Gateways Configured</h5>
                        <p class="text-muted">Add your first SMS gateway to start sending notifications.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGatewayModal">
                            <i class="fas fa-plus"></i> Add Gateway
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Sender ID</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Statistics</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gateways as $gateway): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="gateway-icon me-2">
                                                    <?php if ($gateway['type'] === 'mnotify'): ?>
                                                        <i class="fas fa-mobile-alt text-primary"></i>
                                                    <?php elseif ($gateway['type'] === 'hubtel'): ?>
                                                        <i class="fas fa-satellite-dish text-success"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-server text-secondary"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($gateway['name']) ?></div>
                                                    <small class="text-muted">Created <?= date('M j, Y', strtotime($gateway['created_at'])) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= strtoupper($gateway['type']) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($gateway['sender_id']) ?></td>
                                        <td>
                                            <span class="badge bg-info"><?= $gateway['priority'] ?></span>
                                        </td>
                                        <td>
                                            <?php if ($gateway['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $stats = $gatewayStats[$gateway['id']] ?? null;
                                            if ($stats): 
                                            ?>
                                                <small class="text-muted">
                                                    <?= number_format($stats['total_sent']) ?> sent<br>
                                                    <?= number_format($stats['success_rate'], 1) ?>% success
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted">No data</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" onclick="testGateway(<?= $gateway['id'] ?>)" title="Test Gateway">
                                                    <i class="fas fa-vial"></i>
                                                </button>
                                                <button class="btn btn-outline-secondary" onclick="editGateway(<?= $gateway['id'] ?>)" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-<?= $gateway['is_active'] ? 'warning' : 'success' ?>" 
                                                        onclick="toggleGateway(<?= $gateway['id'] ?>)" 
                                                        title="<?= $gateway['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                                    <i class="fas fa-<?= $gateway['is_active'] ? 'pause' : 'play' ?>"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" onclick="deleteGateway(<?= $gateway['id'] ?>)" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

<!-- Add Gateway Modal -->
<div class="modal fade" id="addGatewayModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add SMS Gateway</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addGatewayForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Gateway Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Gateway Type</label>
                                <select class="form-select" name="type" required onchange="toggleGatewayFields(this.value)">
                                    <option value="">Select Type</option>
                                    <option value="mnotify">mNotify</option>
                                    <option value="hubtel">Hubtel</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">API Key</label>
                                <input type="password" class="form-control" name="api_key" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Sender ID</label>
                                <input type="text" class="form-control" name="sender_id" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hubtel specific fields -->
                    <div id="hubtelFields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Client ID</label>
                                    <input type="text" class="form-control" name="client_id">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Client Secret</label>
                                    <input type="password" class="form-control" name="client_secret">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Priority</label>
                                <input type="number" class="form-control" name="priority" value="1" min="1">
                                <small class="form-text text-muted">Lower numbers have higher priority</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Test Phone</label>
                                <input type="text" class="form-control" name="test_phone" placeholder="233200000000">
                                <small class="form-text text-muted">Phone number for testing</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                            <label class="form-check-label">
                                Activate gateway immediately
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Gateway</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Test Gateway Modal -->
<div class="modal fade" id="testGatewayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Test SMS Gateway</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="testGatewayForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Test Phone Number</label>
                        <input type="text" class="form-control" name="test_phone" placeholder="233200000000" required>
                        <small class="form-text text-muted">Enter phone number to receive test SMS</small>
                    </div>
                    <div id="testResult" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Test SMS</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentGatewayId = null;

function toggleGatewayFields(type) {
    const hubtelFields = document.getElementById('hubtelFields');
    if (type === 'hubtel') {
        hubtelFields.style.display = 'block';
        hubtelFields.querySelectorAll('input').forEach(input => input.required = true);
    } else {
        hubtelFields.style.display = 'none';
        hubtelFields.querySelectorAll('input').forEach(input => input.required = false);
    }
}

function testGateway(gatewayId) {
    currentGatewayId = gatewayId;
    const modal = new bootstrap.Modal(document.getElementById('testGatewayModal'));
    modal.show();
}

function editGateway(gatewayId) {
    // Implementation for editing gateway
    window.location.href = '<?= SUPERADMIN_URL ?>/sms/gateways/' + gatewayId + '/edit';
}

function toggleGateway(gatewayId) {
    if (confirm('Are you sure you want to toggle this gateway status?')) {
        fetch('<?= SUPERADMIN_URL ?>/sms/gateways/' + gatewayId + '/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
}

function deleteGateway(gatewayId) {
    if (confirm('Are you sure you want to delete this gateway? This action cannot be undone.')) {
        fetch('<?= SUPERADMIN_URL ?>/sms/gateways/' + gatewayId, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
}

// Add Gateway Form
document.getElementById('addGatewayForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= SUPERADMIN_URL ?>/sms/gateways', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred: ' + error.message);
    });
});

// Test Gateway Form
document.getElementById('testGatewayForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const resultDiv = document.getElementById('testResult');
    
    resultDiv.innerHTML = '<div class="alert alert-info">Sending test SMS...</div>';
    
    fetch('<?= SUPERADMIN_URL ?>/sms/gateways/' + currentGatewayId + '/test', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.innerHTML = '<div class="alert alert-success">Test SMS sent successfully!</div>';
        } else {
            resultDiv.innerHTML = `<div class="alert alert-danger">Test failed: ${data.message}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        resultDiv.innerHTML = '<div class="alert alert-danger">An error occurred</div>';
    });
});
</script>

<style>
.stat-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
}

.gateway-icon {
    width: 32px;
    text-align: center;
}

.empty-state {
    padding: 3rem 2rem;
}
</style>
