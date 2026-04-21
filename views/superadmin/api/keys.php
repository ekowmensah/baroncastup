<!-- API Keys Management -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-key text-warning me-2"></i>
            API Keys
        </h2>
        <p class="text-muted mb-0">Manage API keys and access tokens</p>
    </div>
    <div>
        <button class="btn btn-primary" onclick="createApiKey()">
            <i class="fas fa-plus me-2"></i>Generate Key
        </button>
        <button class="btn btn-outline-secondary" onclick="location.reload()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
    </div>
</div>

<!-- API Key Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-primary">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $keys['total_keys'] ?? 25 ?></div>
                    <div>Total Keys</div>
                    <div class="small">All time</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-key fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $keys['active_keys'] ?? 22 ?></div>
                    <div>Active Keys</div>
                    <div class="small">Currently valid</div>
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
                    <div class="fs-4 fw-semibold"><?= $keys['expired_keys'] ?? 3 ?></div>
                    <div>Expired Keys</div>
                    <div class="small">Need renewal</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($keys['total_requests'] ?? 15000) ?></div>
                    <div>Total Requests</div>
                    <div class="small">Last 30 days</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-chart-line fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- API Keys Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            API Key Management
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Key Name</th>
                        <th>API Key</th>
                        <th>Client</th>
                        <th>Permissions</th>
                        <th>Usage</th>
                        <th>Status</th>
                        <th>Expires</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sampleKeys = [
                        [
                            'id' => 1,
                            'name' => 'Mobile App Key',
                            'key' => 'sk_live_abc123...',
                            'client' => 'SmartCast Mobile',
                            'permissions' => ['read', 'write'],
                            'usage' => 1250,
                            'status' => 'active',
                            'expires_at' => '2024-12-31',
                            'created_at' => '2024-01-15'
                        ],
                        [
                            'id' => 2,
                            'name' => 'Web Dashboard',
                            'key' => 'sk_live_def456...',
                            'client' => 'Admin Dashboard',
                            'permissions' => ['read', 'write', 'admin'],
                            'usage' => 850,
                            'status' => 'active',
                            'expires_at' => '2024-06-30',
                            'created_at' => '2024-01-10'
                        ],
                        [
                            'id' => 3,
                            'name' => 'Analytics Service',
                            'key' => 'sk_live_ghi789...',
                            'client' => 'Analytics Bot',
                            'permissions' => ['read'],
                            'usage' => 2100,
                            'status' => 'expired',
                            'expires_at' => '2024-01-01',
                            'created_at' => '2023-12-01'
                        ]
                    ];
                    ?>
                    <?php foreach ($sampleKeys as $key): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($key['name']) ?></div>
                            <small class="text-muted">Created <?= date('M j, Y', strtotime($key['created_at'])) ?></small>
                        </td>
                        <td>
                            <code class="user-select-all"><?= htmlspecialchars($key['key']) ?></code>
                            <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('<?= $key['key'] ?>')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </td>
                        <td><?= htmlspecialchars($key['client']) ?></td>
                        <td>
                            <?php foreach ($key['permissions'] as $permission): ?>
                                <span class="badge bg-light text-dark me-1"><?= ucfirst($permission) ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td>
                            <div class="fw-bold"><?= number_format($key['usage']) ?></div>
                            <small class="text-muted">requests</small>
                        </td>
                        <td>
                            <?php 
                            $statusClass = match($key['status']) {
                                'active' => 'bg-success',
                                'expired' => 'bg-danger',
                                'suspended' => 'bg-warning',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= ucfirst($key['status']) ?></span>
                        </td>
                        <td>
                            <?php if ($key['expires_at']): ?>
                                <div><?= date('M j, Y', strtotime($key['expires_at'])) ?></div>
                                <?php if (strtotime($key['expires_at']) < time()): ?>
                                    <small class="text-danger">Expired</small>
                                <?php elseif (strtotime($key['expires_at']) < strtotime('+30 days')): ?>
                                    <small class="text-warning">Expires soon</small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Never</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-primary" onclick="viewKeyDetails(<?= $key['id'] ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="editKey(<?= $key['id'] ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning" onclick="regenerateKey(<?= $key['id'] ?>)">
                                    <i class="fas fa-sync"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="revokeKey(<?= $key['id'] ?>)">
                                    <i class="fas fa-ban"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create API Key Modal -->
<div class="modal fade" id="apiKeyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate API Key</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="apiKeyForm">
                    <div class="mb-3">
                        <label for="keyName" class="form-label">Key Name</label>
                        <input type="text" class="form-control" id="keyName" name="name" required placeholder="e.g., Mobile App Key">
                    </div>
                    
                    <div class="mb-3">
                        <label for="clientName" class="form-label">Client/Application Name</label>
                        <input type="text" class="form-control" id="clientName" name="client" required placeholder="e.g., SmartCast Mobile App">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="permRead" name="permissions[]" value="read" checked>
                                    <label class="form-check-label" for="permRead">Read Access</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="permWrite" name="permissions[]" value="write">
                                    <label class="form-check-label" for="permWrite">Write Access</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="permAdmin" name="permissions[]" value="admin">
                                    <label class="form-check-label" for="permAdmin">Admin Access</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rateLimit" class="form-label">Rate Limit (requests/minute)</label>
                                <input type="number" class="form-control" id="rateLimit" name="rate_limit" value="100" min="1" max="1000">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expiresAt" class="form-label">Expires At (Optional)</label>
                                <input type="date" class="form-control" id="expiresAt" name="expires_at">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2" placeholder="Optional description for this API key"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="generateApiKey()">Generate Key</button>
            </div>
        </div>
    </div>
</div>

<script>
function createApiKey() {
    const modal = new coreui.Modal(document.getElementById('apiKeyModal'));
    modal.show();
}

function generateApiKey() {
    const form = document.getElementById('apiKeyForm');
    const formData = new FormData(form);
    
    console.log('Generating API key:', Object.fromEntries(formData));
    
    // Close modal and refresh page
    const modal = coreui.Modal.getInstance(document.getElementById('apiKeyModal'));
    modal.hide();
    
    alert('API key generated successfully!');
    location.reload();
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('API key copied to clipboard!');
    });
}

function viewKeyDetails(keyId) {
    console.log('Viewing key details:', keyId);
}

function editKey(keyId) {
    console.log('Editing key:', keyId);
}

function regenerateKey(keyId) {
    if (confirm('Are you sure you want to regenerate this API key? The old key will be invalidated immediately.')) {
        console.log('Regenerating key:', keyId);
        alert('API key regenerated successfully!');
        location.reload();
    }
}

function revokeKey(keyId) {
    if (confirm('Are you sure you want to revoke this API key? This action cannot be undone.')) {
        console.log('Revoking key:', keyId);
        alert('API key revoked successfully!');
        location.reload();
    }
}
</script>
