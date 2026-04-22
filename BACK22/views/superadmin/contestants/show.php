<?php
/**
 * SuperAdmin Contestants Management - Show Contestant Details View
 */
?>

<div class="container-fluid">
    <!-- Contestant Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <?php if ($contestant['image_url']): ?>
                                <img src="<?= htmlspecialchars(image_url($contestant['image_url'])) ?>" 
                                     alt="Contestant" class="img-fluid rounded-circle">
                            <?php else: ?>
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                     style="width: 120px; height: 120px;">
                                    <i class="fas fa-user fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h2 class="mb-2"><?= htmlspecialchars($contestant['name']) ?></h2>
                            <p class="text-muted mb-2">
                                <code><?= htmlspecialchars($contestant['contestant_code']) ?></code>
                            </p>
                            <?php if ($contestant['bio']): ?>
                                <p class="mb-3"><?= nl2br(htmlspecialchars($contestant['bio'])) ?></p>
                            <?php endif; ?>
                            <div class="row">
                                <div class="col-sm-6">
                                    <small class="text-muted">Event:</small>
                                    <div class="fw-bold">
                                        <a href="<?= SUPERADMIN_URL ?>/events/<?= $contestant['event_id'] ?>" 
                                           class="text-decoration-none">
                                            <?= htmlspecialchars($contestant['event_name']) ?>
                                        </a>
                                    </div>
                                    <small class="text-muted"><?= htmlspecialchars($contestant['event_code']) ?></small>
                                </div>
                                <div class="col-sm-6">
                                    <small class="text-muted">Tenant:</small>
                                    <div class="fw-bold"><?= htmlspecialchars($contestant['tenant_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($contestant['tenant_email']) ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 text-end">
                            <span class="badge bg-<?= $contestant['active'] ? 'success' : 'secondary' ?> fs-6 mb-2">
                                <?= $contestant['active'] ? 'Active' : 'Inactive' ?>
                            </span>
                            <br>
                            <small class="text-muted">Display Order: <?= $contestant['display_order'] ?></small>
                            <div class="mt-3">
                                <div class="btn-group">
                                    <button class="btn btn-outline-primary btn-sm dropdown-toggle" 
                                            type="button" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        <?php if ($contestant['active']): ?>
                                            <li><a class="dropdown-item" href="#" onclick="updateContestantStatus(<?= $contestant['id'] ?>, 0)">
                                                <i class="fas fa-pause text-warning me-2"></i>Deactivate
                                            </a></li>
                                        <?php else: ?>
                                            <li><a class="dropdown-item" href="#" onclick="updateContestantStatus(<?= $contestant['id'] ?>, 1)">
                                                <i class="fas fa-play text-success me-2"></i>Activate
                                            </a></li>
                                        <?php endif; ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="<?= SUPERADMIN_URL ?>/events/<?= $contestant['event_id'] ?>">
                                            <i class="fas fa-calendar-alt me-2"></i>View Event
                                        </a></li>
                                        <li><a class="dropdown-item" href="<?= APP_URL ?>/events/<?= $contestant['event_code'] ?>/contestant/<?= $contestant['contestant_code'] ?>" target="_blank">
                                            <i class="fas fa-external-link-alt me-2"></i>View Public Profile
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteContestant(<?= $contestant['id'] ?>)">
                                            <i class="fas fa-trash me-2"></i>Delete Contestant
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-vote-yea fa-2x text-success mb-2"></i>
                    <h4 class="mb-1"><?= number_format($contestant['total_votes']) ?></h4>
                    <small class="text-muted">Total Votes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-receipt fa-2x text-info mb-2"></i>
                    <h4 class="mb-1"><?= number_format($contestant['transaction_count']) ?></h4>
                    <small class="text-muted">Transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-money-bill-wave fa-2x text-warning mb-2"></i>
                    <h4 class="mb-1">GH₵<?= number_format($contestant['total_revenue'], 2) ?></h4>
                    <small class="text-muted">Total Revenue</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-tags fa-2x text-primary mb-2"></i>
                    <h4 class="mb-1"><?= count($categories) ?></h4>
                    <small class="text-muted">Categories</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Contestant Details and Categories -->
    <div class="row">
        <!-- Contestant Information -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Contestant Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Full Name:</strong></div>
                        <div class="col-sm-8"><?= htmlspecialchars($contestant['name']) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Code:</strong></div>
                        <div class="col-sm-8"><code><?= htmlspecialchars($contestant['contestant_code']) ?></code></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Display Order:</strong></div>
                        <div class="col-sm-8"><?= $contestant['display_order'] ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Status:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge bg-<?= $contestant['active'] ? 'success' : 'secondary' ?>">
                                <?= $contestant['active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Voting Shortcodes:</strong></div>
                        <div class="col-sm-8">
                            <?php if (!empty($categories)): ?>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($categories as $category): ?>
                                        <?php if (!empty($category['short_code'])): ?>
                                            <div class="d-flex align-items-center bg-light rounded p-2">
                                                <span class="badge bg-primary me-2"><?= htmlspecialchars($category['short_code']) ?></span>
                                                <small class="text-muted me-2"><?= htmlspecialchars($category['name']) ?></small>
                                                <button class="btn btn-xs btn-outline-secondary" 
                                                        onclick="copyToClipboard('<?= htmlspecialchars($category['short_code']) ?>')"
                                                        title="Copy shortcode">
                                                    <i class="fas fa-copy" style="font-size: 10px;"></i>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                                <small class="text-muted mt-1 d-block">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Voters can use these shortcodes to quickly find and vote for this contestant
                                </small>
                            <?php else: ?>
                                <span class="text-muted">No voting shortcodes assigned</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Created:</strong></div>
                        <div class="col-sm-8"><?= date('F j, Y g:i A', strtotime($contestant['created_at'])) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Last Updated:</strong></div>
                        <div class="col-sm-8"><?= date('F j, Y g:i A', strtotime($contestant['updated_at'])) ?></div>
                    </div>
                    <?php if ($contestant['bio']): ?>
                    <div class="row">
                        <div class="col-sm-4"><strong>Biography:</strong></div>
                        <div class="col-sm-8">
                            <div class="small">
                                <?= nl2br(htmlspecialchars($contestant['bio'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Categories -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tags me-2"></i>
                        Categories (<?= count($categories) ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($categories)): ?>
                        <p class="text-muted text-center py-3">Not assigned to any categories</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($categories as $category): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-1">
                                        <a href="<?= SUPERADMIN_URL ?>/categories/<?= $category['id'] ?>" 
                                           class="text-decoration-none">
                                            <?= htmlspecialchars($category['name']) ?>
                                        </a>
                                    </h6>
                                    <?php if ($category['description']): ?>
                                        <small class="text-muted"><?= htmlspecialchars($category['description']) ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?= $category['contestant_active'] ? 'success' : 'warning' ?>">
                                        <?= $category['contestant_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Votes/Transactions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        Recent Votes & Transactions
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentVotes)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-vote-yea fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Votes Yet</h5>
                            <p class="text-muted">This contestant hasn't received any votes yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Phone Number</th>
                                        <th>Votes</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Transaction ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentVotes as $vote): ?>
                                    <tr>
                                        <td><?= date('M j, Y g:i A', strtotime($vote['transaction_date'])) ?></td>
                                        <td>
                                            <span class="font-monospace">
                                                <?= htmlspecialchars(substr($vote['phone'], 0, 3) . '****' . substr($vote['phone'], -4)) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?= number_format($vote['quantity']) ?></span>
                                        </td>
                                        <td>
                                            <strong class="text-success">GH₵<?= number_format($vote['amount'], 2) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $vote['status'] === 'success' ? 'success' : 'danger' ?>">
                                                <?= ucfirst($vote['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="font-monospace text-muted"><?= htmlspecialchars($vote['transaction_id']) ?></small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (count($recentVotes) >= 20): ?>
                            <div class="text-center mt-3">
                                <small class="text-muted">Showing last 20 transactions</small>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Contestant Status Modal -->
<div class="modal fade" id="statusUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Contestant Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="statusUpdateForm">
                    <input type="hidden" id="contestantId" name="contestant_id" value="<?= $contestant['id'] ?>">
                    <input type="hidden" id="newActive" name="active">
                    
                    <div class="mb-3">
                        <label class="form-label">Contestant:</label>
                        <div class="fw-bold"><?= htmlspecialchars($contestant['name']) ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Status:</label>
                        <div id="statusDisplay" class="fw-bold"></div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <span id="statusMessage"></span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmStatusUpdate()">Update Status</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Contestant Modal -->
<div class="modal fade" id="deleteContestantModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Contestant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </div>
                
                <p>Are you sure you want to delete <strong>"<?= htmlspecialchars($contestant['name']) ?>"</strong>?</p>
                
                <div class="alert alert-warning">
                    <strong>Current Performance:</strong><br>
                    • <?= number_format($contestant['total_votes']) ?> votes<br>
                    • <?= number_format($contestant['transaction_count']) ?> transactions<br>
                    • GH₵<?= number_format($contestant['total_revenue'], 2) ?> revenue
                </div>
                
                <p class="text-muted">
                    Deleting this contestant will permanently remove all associated votes and transaction records. 
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteContestant()">
                    <i class="fas fa-trash me-1"></i>Delete Contestant
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function updateContestantStatus(contestantId, active) {
    document.getElementById('newActive').value = active;
    document.getElementById('statusDisplay').textContent = active ? 'Active' : 'Inactive';
    document.getElementById('statusMessage').textContent = active ? 
        'This contestant will be visible to voters and can receive votes.' :
        'This contestant will be hidden from voters and cannot receive new votes.';
    
    new bootstrap.Modal(document.getElementById('statusUpdateModal')).show();
}

function confirmStatusUpdate() {
    const formData = new FormData(document.getElementById('statusUpdateForm'));
    
    fetch('<?= SUPERADMIN_URL ?>/contestants/update-status', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('statusUpdateModal')).hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        showAlert('danger', 'An error occurred while updating the contestant status');
        console.error('Error:', error);
    });
}

function deleteContestant(contestantId) {
    new bootstrap.Modal(document.getElementById('deleteContestantModal')).show();
}

function confirmDeleteContestant() {
    const formData = new FormData();
    formData.append('contestant_id', <?= $contestant['id'] ?>);
    
    fetch('<?= SUPERADMIN_URL ?>/contestants/delete', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('deleteContestantModal')).hide();
            setTimeout(() => {
                window.location.href = '<?= SUPERADMIN_URL ?>/contestants';
            }, 1000);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        showAlert('danger', 'An error occurred while deleting the contestant');
        console.error('Error:', error);
    });
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.row'));
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            showAlert('success', `Shortcode "${text}" copied to clipboard!`);
        }).catch(() => {
            showAlert('warning', 'Failed to copy shortcode to clipboard');
        });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            showAlert('success', `Shortcode "${text}" copied to clipboard!`);
        } catch (err) {
            showAlert('warning', 'Failed to copy shortcode to clipboard');
        } finally {
            textArea.remove();
        }
    }
}
</script>
