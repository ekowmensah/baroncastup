<!-- Risk Blocks -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-ban text-danger me-2"></i>
            Risk Blocks
        </h2>
        <p class="text-muted mb-0">Manage IP blocks, user restrictions, and security measures</p>
    </div>
    <div>
        <button class="btn btn-danger" onclick="createBlock()">
            <i class="fas fa-plus me-2"></i>Add Block
        </button>
        <button class="btn btn-outline-secondary" onclick="location.reload()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
    </div>
</div>

<!-- Block Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-danger">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($blocks['active_blocks'] ?? 0) ?></div>
                    <div>Active Blocks</div>
                    <div class="small">Currently enforced</div>
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
                    <div class="fs-4 fw-semibold"><?= number_format($blocks['ip_blocks'] ?? 0) ?></div>
                    <div>IP Blocks</div>
                    <div class="small">Blocked addresses</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-globe fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($blocks['user_blocks'] ?? 0) ?></div>
                    <div>User Blocks</div>
                    <div class="small">Blocked accounts</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-user-slash fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($blocks['blocked_attempts'] ?? 0) ?></div>
                    <div>Blocked Attempts</div>
                    <div class="small">Last 24 hours</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-shield-alt fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Blocks -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Active Security Blocks
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($blocks['active'])): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Target</th>
                            <th>Reason</th>
                            <th>Severity</th>
                            <th>Created</th>
                            <th>Expires</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blocks['active'] as $block): ?>
                        <tr>
                            <td>
                                <?php 
                                $type = $block['type'] ?? 'unknown';
                                $typeClass = match($type) {
                                    'ip' => 'bg-warning',
                                    'user' => 'bg-info',
                                    'email' => 'bg-secondary',
                                    'country' => 'bg-primary',
                                    default => 'bg-dark'
                                };
                                ?>
                                <span class="badge <?= $typeClass ?>"><?= ucfirst($type) ?> Block</span>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($block['target'] ?? 'Unknown') ?></div>
                                <?php if (!empty($block['target_info'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars($block['target_info']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($block['reason'] ?? 'No reason specified') ?></div>
                            </td>
                            <td>
                                <?php 
                                $severity = $block['severity'] ?? 'medium';
                                $severityClass = match($severity) {
                                    'critical' => 'bg-danger',
                                    'high' => 'bg-warning',
                                    'medium' => 'bg-info',
                                    'low' => 'bg-success',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $severityClass ?>"><?= ucfirst($severity) ?></span>
                            </td>
                            <td>
                                <div><?= date('M j, Y', strtotime($block['created_at'] ?? 'now')) ?></div>
                                <small class="text-muted">by <?= htmlspecialchars($block['created_by'] ?? 'System') ?></small>
                            </td>
                            <td>
                                <?php if (!empty($block['expires_at'])): ?>
                                    <div><?= date('M j, Y', strtotime($block['expires_at'])) ?></div>
                                    <small class="text-muted"><?= date('H:i', strtotime($block['expires_at'])) ?></small>
                                <?php else: ?>
                                    <span class="text-muted">Never</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewBlockDetails(<?= $block['id'] ?? 0 ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="editBlock(<?= $block['id'] ?? 0 ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="unblockTarget(<?= $block['id'] ?? 0 ?>)">
                                        <i class="fas fa-unlock"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteBlock(<?= $block['id'] ?? 0 ?>)">
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
                <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                <h5>No Active Blocks</h5>
                <p class="text-muted">No security blocks are currently active.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Block Activity -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>
                    Recent Block Activity
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($blocks['recent_activity'])): ?>
                    <div class="timeline">
                        <?php foreach ($blocks['recent_activity'] as $activity): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-<?= ($activity['action'] ?? '') === 'blocked' ? 'danger' : 'success' ?>"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title"><?= htmlspecialchars($activity['title'] ?? 'Unknown Activity') ?></h6>
                                <p class="timeline-description"><?= htmlspecialchars($activity['description'] ?? '') ?></p>
                                <small class="text-muted"><?= date('M j, Y H:i', strtotime($activity['created_at'] ?? 'now')) ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No recent block activity.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Block Types
                </h5>
            </div>
            <div class="card-body">
                <canvas id="blockTypesChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Block Modal -->
<div class="modal fade" id="blockModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="blockModalTitle">Create Security Block</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="blockForm">
                    <input type="hidden" id="blockId" name="block_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="blockType" class="form-label">Block Type</label>
                                <select class="form-select" id="blockType" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="ip">IP Address</option>
                                    <option value="user">User Account</option>
                                    <option value="email">Email Address</option>
                                    <option value="country">Country</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="blockSeverity" class="form-label">Severity</label>
                                <select class="form-select" id="blockSeverity" name="severity" required>
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="blockTarget" class="form-label">Target</label>
                        <input type="text" class="form-control" id="blockTarget" name="target" placeholder="IP address, email, user ID, etc." required>
                        <div class="form-text">Enter the specific target to block (e.g., 192.168.1.1, user@example.com, US)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="blockReason" class="form-label">Reason</label>
                        <textarea class="form-control" id="blockReason" name="reason" rows="3" placeholder="Explain why this block is necessary..." required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="blockDuration" class="form-label">Duration</label>
                                <select class="form-select" id="blockDuration" name="duration">
                                    <option value="1h">1 Hour</option>
                                    <option value="24h">24 Hours</option>
                                    <option value="7d">7 Days</option>
                                    <option value="30d" selected>30 Days</option>
                                    <option value="permanent">Permanent</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="blockExpires" class="form-label">Custom Expiry (Optional)</label>
                                <input type="datetime-local" class="form-control" id="blockExpires" name="expires_at">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="blockActive" name="active" checked>
                        <label class="form-check-label" for="blockActive">
                            Activate block immediately
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="saveBlock()">Create Block</button>
            </div>
        </div>
    </div>
</div>

<script>
function createBlock() {
    document.getElementById('blockModalTitle').textContent = 'Create Security Block';
    document.getElementById('blockForm').reset();
    document.getElementById('blockId').value = '';
    
    const modal = new coreui.Modal(document.getElementById('blockModal'));
    modal.show();
}

function editBlock(blockId) {
    document.getElementById('blockModalTitle').textContent = 'Edit Security Block';
    document.getElementById('blockId').value = blockId;
    
    // Load block data (implementation needed)
    console.log('Loading block data for:', blockId);
    
    const modal = new coreui.Modal(document.getElementById('blockModal'));
    modal.show();
}

function saveBlock() {
    const form = document.getElementById('blockForm');
    const formData = new FormData(form);
    
    console.log('Saving block:', Object.fromEntries(formData));
    
    // Close modal and refresh page
    const modal = coreui.Modal.getInstance(document.getElementById('blockModal'));
    modal.hide();
    
    alert('Security block created successfully!');
    location.reload();
}

function viewBlockDetails(blockId) {
    console.log('Viewing block details:', blockId);
}

function unblockTarget(blockId) {
    if (confirm('Are you sure you want to remove this security block?')) {
        console.log('Unblocking target:', blockId);
        alert('Block removed successfully!');
        location.reload();
    }
}

function deleteBlock(blockId) {
    if (confirm('Are you sure you want to permanently delete this block? This action cannot be undone.')) {
        console.log('Deleting block:', blockId);
        alert('Block deleted successfully!');
        location.reload();
    }
}

// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart !== 'undefined') {
        const ctx = document.getElementById('blockTypesChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['IP Blocks', 'User Blocks', 'Email Blocks', 'Country Blocks'],
                    datasets: [{
                        data: [<?= $blocks['ip_blocks'] ?? 0 ?>, <?= $blocks['user_blocks'] ?? 0 ?>, <?= $blocks['email_blocks'] ?? 0 ?>, <?= $blocks['country_blocks'] ?? 0 ?>],
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    }
});
</script>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -29px;
    top: 17px;
    width: 2px;
    height: calc(100% + 5px);
    background-color: #dee2e6;
}

.timeline-title {
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.timeline-description {
    margin-bottom: 5px;
    font-size: 0.85rem;
    color: #6c757d;
}
</style>
