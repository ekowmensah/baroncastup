<?php
/**
 * SuperAdmin Contestants Management - Index View
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>
                        Contestants Management
                    </h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshContestants()">
                            <i class="fas fa-sync-alt me-1"></i>
                            Refresh
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-filter me-1"></i>
                                Filter
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="filterContestants('all')">All Contestants</a></li>
                                <li><a class="dropdown-item" href="#" onclick="filterContestants('active')">Active</a></li>
                                <li><a class="dropdown-item" href="#" onclick="filterContestants('inactive')">Inactive</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="filterContestants('with_votes')">With Votes</a></li>
                                <li><a class="dropdown-item" href="#" onclick="filterContestants('no_votes')">No Votes</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="contestantsTable">
                            <thead>
                                <tr>
                                    <th>Contestant</th>
                                    <th>Event</th>
                                    <th>Tenant</th>
                                    <th>Voting Shortcodes</th>
                                    <th>Status</th>
                                    <th>Performance</th>
                                    <th>Revenue</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contestants as $contestant): ?>
                                <tr data-contestant-id="<?= $contestant['id'] ?>" 
                                    data-status="<?= $contestant['active'] ? 'active' : 'inactive' ?>"
                                    data-votes="<?= $contestant['total_votes'] ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($contestant['image_url']): ?>
                                                <img src="<?= htmlspecialchars(image_url($contestant['image_url'])) ?>" 
                                                     alt="Contestant" class="rounded-circle me-3" 
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="fas fa-user text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="mb-1">
                                                    <a href="<?= SUPERADMIN_URL ?>/contestants/<?= $contestant['id'] ?>" 
                                                       class="text-decoration-none">
                                                        <?= htmlspecialchars($contestant['name']) ?>
                                                    </a>
                                                </h6>
                                                <small class="text-muted"><?= htmlspecialchars($contestant['contestant_code']) ?></small>
                                                <?php if ($contestant['bio']): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars(substr($contestant['bio'], 0, 50)) ?>...</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($contestant['event_name']) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($contestant['event_code']) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($contestant['tenant_name']) ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($contestant['category_shortcodes']): ?>
                                            <div class="small">
                                                <?php 
                                                $shortcodes = explode('|', $contestant['category_shortcodes']);
                                                $visibleShortcodes = array_slice($shortcodes, 0, 1);
                                                $hiddenShortcodes = array_slice($shortcodes, 1);
                                                ?>
                                                
                                                <div class="shortcodes-container" data-contestant-id="<?= $contestant['id'] ?>">
                                                    <?php foreach ($visibleShortcodes as $shortcode): ?>
                                                        <?php if (strpos($shortcode, ':') !== false): ?>
                                                            <?php list($catName, $code) = explode(':', $shortcode, 2); ?>
                                                            <?php if (!empty($code)): ?>
                                                                <div class="mb-1">
                                                                    <span class="badge bg-primary me-1"><?= htmlspecialchars($code) ?></span>
                                                                    <small class="text-muted"><?= htmlspecialchars($catName) ?></small>
                                                                    <button class="btn btn-xs btn-outline-secondary ms-1" 
                                                                            onclick="copyToClipboard('<?= htmlspecialchars($code) ?>')"
                                                                            title="Copy shortcode">
                                                                        <i class="fas fa-copy" style="font-size: 10px;"></i>
                                                                    </button>
                                                                </div>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                    
                                                    <?php if (!empty($hiddenShortcodes)): ?>
                                                        <div class="hidden-shortcodes" id="hidden-<?= $contestant['id'] ?>" style="display: none;">
                                                            <?php foreach ($hiddenShortcodes as $shortcode): ?>
                                                                <?php if (strpos($shortcode, ':') !== false): ?>
                                                                    <?php list($catName, $code) = explode(':', $shortcode, 2); ?>
                                                                    <?php if (!empty($code)): ?>
                                                                        <div class="mb-1">
                                                                            <span class="badge bg-primary me-1"><?= htmlspecialchars($code) ?></span>
                                                                            <small class="text-muted"><?= htmlspecialchars($catName) ?></small>
                                                                            <button class="btn btn-xs btn-outline-secondary ms-1" 
                                                                                    onclick="copyToClipboard('<?= htmlspecialchars($code) ?>')"
                                                                                    title="Copy shortcode">
                                                                                <i class="fas fa-copy" style="font-size: 10px;"></i>
                                                                            </button>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </div>
                                                        
                                                        <button class="btn btn-xs btn-link text-muted p-0 toggle-shortcodes" 
                                                                onclick="toggleShortcodes(<?= $contestant['id'] ?>)"
                                                                id="toggle-btn-<?= $contestant['id'] ?>">
                                                            <small>+<?= count($hiddenShortcodes) ?> more</small>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">No shortcodes</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $contestant['active'] ? 'success' : 'secondary' ?>">
                                            <?= $contestant['active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                        <br>
                                        <small class="text-muted">Order: <?= $contestant['display_order'] ?></small>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div><i class="fas fa-vote-yea me-1 text-success"></i><?= number_format($contestant['total_votes']) ?> votes</div>
                                            <div><i class="fas fa-receipt me-1 text-info"></i><?= number_format($contestant['transaction_count']) ?> transactions</div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            GH₵<?= number_format($contestant['total_revenue'], 2) ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= SUPERADMIN_URL ?>/contestants/<?= $contestant['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                        data-bs-toggle="dropdown" title="Actions">
                                                    <i class="fas fa-cog"></i>
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
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
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
                    <input type="hidden" id="contestantId" name="contestant_id">
                    <input type="hidden" id="newActive" name="active">
                    
                    <div class="mb-3">
                        <label class="form-label">Contestant:</label>
                        <div id="contestantName" class="fw-bold"></div>
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
                
                <form id="deleteContestantForm">
                    <input type="hidden" id="deleteContestantId" name="contestant_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Contestant:</label>
                        <div id="deleteContestantName" class="fw-bold"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Event:</label>
                        <div id="deleteEventName" class="fw-bold"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Performance:</label>
                        <div id="deletePerformance" class="fw-bold text-danger"></div>
                    </div>
                    
                    <p class="text-muted">
                        Deleting this contestant will permanently remove all associated votes and transaction records. 
                        This action cannot be undone.
                    </p>
                </form>
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
    const contestantRow = document.querySelector(`tr[data-contestant-id="${contestantId}"]`);
    const contestantName = contestantRow.querySelector('h6 a').textContent;
    
    document.getElementById('contestantId').value = contestantId;
    document.getElementById('newActive').value = active;
    document.getElementById('contestantName').textContent = contestantName;
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
    const contestantRow = document.querySelector(`tr[data-contestant-id="${contestantId}"]`);
    const contestantName = contestantRow.querySelector('h6 a').textContent;
    const eventName = contestantRow.querySelector('td:nth-child(2) strong').textContent;
    const votes = contestantRow.dataset.votes;
    const revenue = contestantRow.querySelector('td:nth-child(7) strong').textContent;
    
    document.getElementById('deleteContestantId').value = contestantId;
    document.getElementById('deleteContestantName').textContent = contestantName;
    document.getElementById('deleteEventName').textContent = eventName;
    document.getElementById('deletePerformance').textContent = `${votes} votes, ${revenue} revenue`;
    
    new bootstrap.Modal(document.getElementById('deleteContestantModal')).show();
}

function confirmDeleteContestant() {
    const formData = new FormData(document.getElementById('deleteContestantForm'));
    
    fetch('<?= SUPERADMIN_URL ?>/contestants/delete', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('deleteContestantModal')).hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        showAlert('danger', 'An error occurred while deleting the contestant');
        console.error('Error:', error);
    });
}

function filterContestants(filter) {
    const rows = document.querySelectorAll('#contestantsTable tbody tr');
    
    rows.forEach(row => {
        const status = row.dataset.status;
        const votes = parseInt(row.dataset.votes);
        let show = false;
        
        switch(filter) {
            case 'all':
                show = true;
                break;
            case 'active':
                show = status === 'active';
                break;
            case 'inactive':
                show = status === 'inactive';
                break;
            case 'with_votes':
                show = votes > 0;
                break;
            case 'no_votes':
                show = votes === 0;
                break;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

function refreshContestants() {
    location.reload();
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

function toggleShortcodes(contestantId) {
    const hiddenDiv = document.getElementById(`hidden-${contestantId}`);
    const toggleBtn = document.getElementById(`toggle-btn-${contestantId}`);
    
    if (hiddenDiv.style.display === 'none') {
        // Show hidden shortcodes
        hiddenDiv.style.display = 'block';
        toggleBtn.innerHTML = '<small>Show less</small>';
        toggleBtn.classList.add('text-primary');
        toggleBtn.classList.remove('text-muted');
    } else {
        // Hide shortcodes
        hiddenDiv.style.display = 'none';
        const hiddenCount = hiddenDiv.children.length;
        toggleBtn.innerHTML = `<small>+${hiddenCount} more</small>`;
        toggleBtn.classList.add('text-muted');
        toggleBtn.classList.remove('text-primary');
    }
}
</script>
