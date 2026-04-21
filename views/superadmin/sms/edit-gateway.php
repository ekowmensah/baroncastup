<?php
// Edit SMS Gateway View Content
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-edit text-primary me-2"></i>
            Edit SMS Gateway
        </h2>
        <p class="text-muted mb-0">Update SMS gateway configuration and settings</p>
    </div>
    <div>
        <a href="<?= SUPERADMIN_URL ?>/sms/gateways" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Gateways
        </a>
    </div>
</div>

<!-- Edit Gateway Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Gateway Configuration</h5>
            </div>
            <div class="card-body">
                <form id="editGatewayForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Gateway Name</label>
                                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($gateway['name']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Gateway Type</label>
                                <select class="form-select" name="type" required onchange="toggleGatewayFields(this.value)">
                                    <option value="mnotify" <?= $gateway['type'] === 'mnotify' ? 'selected' : '' ?>>mNotify</option>
                                    <option value="hubtel" <?= $gateway['type'] === 'hubtel' ? 'selected' : '' ?>>Hubtel</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">API Key</label>
                                <input type="password" class="form-control" name="api_key" value="<?= htmlspecialchars($gateway['api_key']) ?>" required>
                                <small class="form-text text-muted">Leave blank to keep current key</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Sender ID</label>
                                <input type="text" class="form-control" name="sender_id" value="<?= htmlspecialchars($gateway['sender_id']) ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hubtel specific fields -->
                    <div id="hubtelFields" style="display: <?= $gateway['type'] === 'hubtel' ? 'block' : 'none' ?>;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Client ID</label>
                                    <input type="text" class="form-control" name="client_id" value="<?= htmlspecialchars($gateway['client_id'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Client Secret</label>
                                    <input type="password" class="form-control" name="client_secret" value="<?= htmlspecialchars($gateway['client_secret'] ?? '') ?>">
                                    <small class="form-text text-muted">Leave blank to keep current secret</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Priority</label>
                                <input type="number" class="form-control" name="priority" value="<?= $gateway['priority'] ?>" min="1">
                                <small class="form-text text-muted">Lower numbers have higher priority</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Test Phone</label>
                                <input type="text" class="form-control" name="test_phone" value="<?= htmlspecialchars($gateway['test_phone'] ?? '') ?>" placeholder="233200000000">
                                <small class="form-text text-muted">Phone number for testing</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" <?= $gateway['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label">
                                Gateway is active
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Gateway
                        </button>
                        <button type="button" class="btn btn-success" onclick="testCurrentGateway()">
                            <i class="fas fa-vial me-2"></i>Test Gateway
                        </button>
                        <a href="<?= SUPERADMIN_URL ?>/sms/gateways" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Gateway Info -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Gateway Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">Gateway ID</label>
                    <div class="fw-bold"><?= $gateway['id'] ?></div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Created</label>
                    <div><?= date('M j, Y H:i', strtotime($gateway['created_at'])) ?></div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Last Updated</label>
                    <div><?= date('M j, Y H:i', strtotime($gateway['updated_at'])) ?></div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Status</label>
                    <div>
                        <?php if ($gateway['is_active']): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Test Results -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Test Results</h5>
            </div>
            <div class="card-body">
                <div id="testResults">
                    <p class="text-muted">Click "Test Gateway" to verify connection</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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

function testCurrentGateway() {
    const testResults = document.getElementById('testResults');
    const testPhone = document.querySelector('input[name="test_phone"]').value;
    
    if (!testPhone) {
        testResults.innerHTML = '<div class="alert alert-warning">Please enter a test phone number first</div>';
        return;
    }
    
    testResults.innerHTML = '<div class="alert alert-info">Testing gateway connection...</div>';
    
    const formData = new FormData();
    formData.append('test_phone', testPhone);
    
    fetch('<?= SUPERADMIN_URL ?>/sms/gateways/<?= $gateway['id'] ?>/test', {
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
            return response.text().then(text => {
                throw new Error('Server returned non-JSON response: ' + text.substring(0, 100));
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            testResults.innerHTML = '<div class="alert alert-success"><i class="fas fa-check me-2"></i>Test SMS sent successfully!</div>';
        } else {
            testResults.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Test failed: ${data.message}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        testResults.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Test failed: ${error.message}</div>`;
    });
}

// Edit Gateway Form
document.getElementById('editGatewayForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
    
    fetch('<?= SUPERADMIN_URL ?>/sms/gateways/<?= $gateway['id'] ?>/update', {
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
            return response.text().then(text => {
                throw new Error('Server returned non-JSON response: ' + text.substring(0, 200));
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success message and redirect
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML = `
                <i class="fas fa-check me-2"></i>${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.card-body').insertBefore(alert, document.getElementById('editGatewayForm'));
            
            setTimeout(() => {
                window.location.href = '<?= SUPERADMIN_URL ?>/sms/gateways';
            }, 2000);
        } else {
            // Show error message
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show';
            alert.innerHTML = `
                <i class="fas fa-exclamation-triangle me-2"></i>Error: ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.card-body').insertBefore(alert, document.getElementById('editGatewayForm'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>Error: ${error.message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.card-body').insertBefore(alert, document.getElementById('editGatewayForm'));
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});
</script>
