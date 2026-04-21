<?php
/**
 * SuperAdmin Events Management - Show Event Details View
 */
?>

<div class="container-fluid">
    <!-- Event Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <?php if ($event['featured_image']): ?>
                                <img src="<?= htmlspecialchars(image_url($event['featured_image'])) ?>" 
                                     alt="Event Image" class="img-fluid rounded">
                            <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                     style="height: 120px;">
                                    <i class="fas fa-calendar-alt fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h2 class="mb-2"><?= htmlspecialchars($event['name']) ?></h2>
                            <p class="text-muted mb-2"><?= htmlspecialchars($event['description']) ?></p>
                            <div class="row">
                                <div class="col-sm-6">
                                    <small class="text-muted">Event Code:</small>
                                    <div class="fw-bold"><?= htmlspecialchars($event['code']) ?></div>
                                </div>
                                <div class="col-sm-6">
                                    <small class="text-muted">Tenant:</small>
                                    <div class="fw-bold"><?= htmlspecialchars($event['tenant_name']) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 text-end">
                            <?php
                            // Event Status (Primary)
                            $eventStatusClass = match($event['status']) {
                                'active' => 'success',
                                'draft' => 'secondary',
                                'closed' => 'dark',
                                'suspended' => 'warning',
                                default => 'secondary'
                            };
                            
                            $eventStatusText = match($event['status']) {
                                'active' => 'Active',
                                'draft' => 'Draft',
                                'closed' => 'Closed',
                                'suspended' => 'Suspended',
                                default => ucfirst($event['status'])
                            };
                            
                            // Admin Status (Secondary)
                            $adminStatusClass = match($event['admin_status']) {
                                'approved' => 'success',
                                'pending' => 'warning',
                                'rejected' => 'danger',
                                'under_review' => 'info',
                                default => 'secondary'
                            };
                            
                            $adminStatusText = match($event['admin_status']) {
                                'approved' => 'Approved',
                                'pending' => 'Pending',
                                'rejected' => 'Rejected',
                                'under_review' => 'Under Review',
                                default => 'Unknown'
                            };
                            
                            $adminStatusIcon = match($event['admin_status']) {
                                'approved' => 'check-circle',
                                'pending' => 'clock',
                                'rejected' => 'times-circle',
                                'under_review' => 'eye',
                                default => 'question-circle'
                            };
                            ?>
                            <!-- Event Status Badge (Primary) -->
                            <span class="badge bg-<?= $eventStatusClass ?> fs-6 mb-2">
                                <i class="fas fa-circle me-1" style="font-size: 0.5em;"></i>
                                <?= $eventStatusText ?>
                            </span>
                            <br>
                            <!-- Admin Approval Status (Secondary) -->
                            <small class="text-muted d-block mb-2">
                                <i class="fas fa-<?= $adminStatusIcon ?> me-1"></i>
                                <?= $adminStatusText ?>
                            </small>
                            <div class="btn-group">
                                <button class="btn btn-outline-primary btn-sm dropdown-toggle" 
                                        type="button" data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <?php if ($event['admin_status'] !== 'approved'): ?>
                                        <li><a class="dropdown-item" href="#" onclick="updateEventStatus(<?= $event['id'] ?>, 'approved')">
                                            <i class="fas fa-check text-success me-2"></i>Approve
                                        </a></li>
                                    <?php endif; ?>
                                    <?php if ($event['admin_status'] !== 'under_review'): ?>
                                        <li><a class="dropdown-item" href="#" onclick="updateEventStatus(<?= $event['id'] ?>, 'under_review')">
                                            <i class="fas fa-search text-info me-2"></i>Under Review
                                        </a></li>
                                    <?php endif; ?>
                                    <?php if ($event['admin_status'] !== 'rejected'): ?>
                                        <li><a class="dropdown-item" href="#" onclick="updateEventStatus(<?= $event['id'] ?>, 'rejected')">
                                            <i class="fas fa-times text-danger me-2"></i>Reject
                                        </a></li>
                                    <?php endif; ?>
                                    <?php if ($event['admin_status'] !== 'pending'): ?>
                                        <li><a class="dropdown-item" href="#" onclick="updateEventStatus(<?= $event['id'] ?>, 'pending')">
                                            <i class="fas fa-clock text-warning me-2"></i>Set Pending
                                        </a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?= APP_URL ?>/events/<?= $event['code'] ?>" target="_blank">
                                        <i class="fas fa-external-link-alt me-2"></i>View Public Page
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                    <h4 class="mb-1"><?= number_format($event['contestant_count']) ?></h4>
                    <small class="text-muted">Contestants</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-tags fa-2x text-info mb-2"></i>
                    <h4 class="mb-1"><?= number_format($event['category_count']) ?></h4>
                    <small class="text-muted">Categories</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-vote-yea fa-2x text-success mb-2"></i>
                    <h4 class="mb-1"><?= number_format($event['total_votes']) ?></h4>
                    <small class="text-muted">Total Votes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-money-bill-wave fa-2x text-warning mb-2"></i>
                    <h4 class="mb-1">GH₵<?= number_format($event['total_revenue'], 2) ?></h4>
                    <small class="text-muted">Total Revenue</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details and Management -->
    <div class="row">
        <!-- Event Information -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Event Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Start Date:</strong></div>
                        <div class="col-sm-8"><?= date('F j, Y g:i A', strtotime($event['start_date'])) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>End Date:</strong></div>
                        <div class="col-sm-8"><?= date('F j, Y g:i A', strtotime($event['end_date'])) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Vote Price:</strong></div>
                        <div class="col-sm-8">GH₵<?= number_format($event['vote_price'], 2) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Visibility:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge bg-<?= $event['visibility'] === 'public' ? 'success' : 'warning' ?>">
                                <?= ucfirst($event['visibility']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Results Visible:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge bg-<?= $event['results_visible'] ? 'success' : 'secondary' ?>">
                                <?= $event['results_visible'] ? 'Yes' : 'No' ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Created:</strong></div>
                        <div class="col-sm-8"><?= date('F j, Y g:i A', strtotime($event['created_at'])) ?></div>
                    </div>
                    <?php if ($event['admin_notes']): ?>
                    <div class="row">
                        <div class="col-sm-4"><strong>Admin Notes:</strong></div>
                        <div class="col-sm-8">
                            <div class="alert alert-info small mb-0">
                                <?= nl2br(htmlspecialchars($event['admin_notes'])) ?>
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
                    <a href="<?= SUPERADMIN_URL ?>/categories?event_id=<?= $event['id'] ?>" 
                       class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($categories)): ?>
                        <p class="text-muted text-center py-3">No categories found</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($categories as $category): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($category['name']) ?></h6>
                                    <?php if ($category['description']): ?>
                                        <small class="text-muted"><?= htmlspecialchars($category['description']) ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary"><?= $category['contestant_count'] ?> contestants</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Contestants -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>
                        Contestants (<?= count($contestants) ?>)
                    </h5>
                    <a href="<?= SUPERADMIN_URL ?>/contestants?event_id=<?= $event['id'] ?>" 
                       class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($contestants)): ?>
                        <p class="text-muted text-center py-3">No contestants found</p>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach (array_slice($contestants, 0, 8) as $contestant): ?>
                            <div class="col-md-3 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center p-3">
                                        <?php if ($contestant['image_url']): ?>
                                            <img src="<?= htmlspecialchars(image_url($contestant['image_url'])) ?>" 
                                                 alt="Contestant" class="rounded-circle mb-2" 
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-user text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <h6 class="mb-1"><?= htmlspecialchars($contestant['name']) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($contestant['contestant_code']) ?></small>
                                        <br>
                                        <span class="badge bg-<?= $contestant['active'] ? 'success' : 'secondary' ?> mt-1">
                                            <?= $contestant['active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($contestants) > 8): ?>
                            <div class="text-center">
                                <a href="<?= SUPERADMIN_URL ?>/contestants?event_id=<?= $event['id'] ?>" 
                                   class="btn btn-outline-primary">
                                    View All <?= count($contestants) ?> Contestants
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-receipt me-2"></i>
                        Recent Transactions
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentTransactions)): ?>
                        <p class="text-muted text-center py-3">No transactions found</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Contestant</th>
                                        <th>Phone</th>
                                        <th>Votes</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentTransactions as $transaction): ?>
                                    <tr>
                                        <td><?= date('M j, Y g:i A', strtotime($transaction['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($transaction['contestant_name']) ?></td>
                                        <td><?= htmlspecialchars($transaction['phone']) ?></td>
                                        <td><?= number_format($transaction['quantity']) ?></td>
                                        <td>GH₵<?= number_format($transaction['amount'], 2) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $transaction['status'] === 'success' ? 'success' : 'danger' ?>">
                                                <?= ucfirst($transaction['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Status Update Modal -->
<div class="modal fade" id="statusUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Event Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="statusUpdateForm">
                    <input type="hidden" id="eventId" name="event_id" value="<?= $event['id'] ?>">
                    <input type="hidden" id="newStatus" name="status">
                    
                    <div class="mb-3">
                        <label class="form-label">Event:</label>
                        <div class="fw-bold"><?= htmlspecialchars($event['name']) ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Status:</label>
                        <div id="statusDisplay" class="fw-bold"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="adminNotes" class="form-label">Admin Notes (Optional):</label>
                        <textarea class="form-control" id="adminNotes" name="admin_notes" rows="3" 
                                  placeholder="Add any notes about this status change..."></textarea>
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

<script>
function updateEventStatus(eventId, status) {
    document.getElementById('newStatus').value = status;
    document.getElementById('statusDisplay').textContent = status.charAt(0).toUpperCase() + status.slice(1);
    document.getElementById('adminNotes').value = '';
    
    new bootstrap.Modal(document.getElementById('statusUpdateModal')).show();
}

function confirmStatusUpdate() {
    const formData = new FormData(document.getElementById('statusUpdateForm'));
    
    fetch('<?= SUPERADMIN_URL ?>/events/update-status', {
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
        showAlert('danger', 'An error occurred while updating the event status');
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
</script>
