<?php
/**
 * SuperAdmin Events Management - Index View
 */
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-calendar-check text-primary me-2"></i>
            Events Management
        </h2>
        <p class="text-muted mb-0">Monitor and manage all platform events</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" onclick="exportEventsReport()">
            <i class="fas fa-download me-2"></i>Export Report
        </button>
        <button class="btn btn-primary" onclick="refreshEvents()">
            <i class="fas fa-sync-alt me-2"></i>Refresh
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <?php
    $totalEvents = count($events);
    $approvedEvents = count(array_filter($events, fn($e) => $e['admin_status'] === 'approved'));
    $pendingEvents = count(array_filter($events, fn($e) => $e['admin_status'] === 'pending'));
    $activeEvents = count(array_filter($events, fn($e) => $e['status'] === 'active'));
    $expiredEvents = count(array_filter($events, fn($e) => strtotime($e['end_date']) < time() && $e['status'] !== 'closed'));
    $totalRevenue = array_sum(array_column($events, 'total_revenue'));
    $totalVotes = array_sum(array_column($events, 'total_votes'));
    $totalTransactions = array_sum(array_column($events, 'transaction_count'));
    ?>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-primary">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($totalEvents) ?></div>
                    <div>Total Events</div>
                    <div class="small"><?= $activeEvents ?> currently active</div>
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
                    <div class="fs-4 fw-semibold">GHS <?= number_format($totalRevenue, 2) ?></div>
                    <div>Total Revenue</div>
                    <div class="small"><?= number_format($totalTransactions) ?> transactions</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($totalVotes) ?></div>
                    <div>Total Votes</div>
                    <div class="small">Across all events</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-vote-yea fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white <?= $pendingEvents > 0 ? 'bg-warning' : 'bg-secondary' ?>">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($pendingEvents) ?></div>
                    <div>Pending Review</div>
                    <div class="small"><?= $expiredEvents ?> expired events</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-clock fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Events Table -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="input-group" style="max-width: 300px;">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" id="searchEvents" 
                                   placeholder="Search events..." onkeyup="searchEvents()">
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php if ($expiredEvents > 0): ?>
                            <button class="btn btn-warning btn-sm" onclick="closeExpiredEvents()">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Close <?= $expiredEvents ?> Expired
                            </button>
                            <?php endif; ?>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                        data-bs-toggle="dropdown">
                                    <i class="fas fa-filter me-1"></i>
                                    <span id="currentFilter">All Events</span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Approval Status</h6></li>
                                    <li><a class="dropdown-item" href="#" onclick="filterEvents('all')"><i class="fas fa-list me-2"></i>All Events</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="filterEvents('approved')"><i class="fas fa-check-circle text-success me-2"></i>Approved (<?= $approvedEvents ?>)</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="filterEvents('pending')"><i class="fas fa-clock text-warning me-2"></i>Pending (<?= $pendingEvents ?>)</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="filterEvents('rejected')"><i class="fas fa-times-circle text-danger me-2"></i>Rejected</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Event Status</h6></li>
                                    <li><a class="dropdown-item" href="#" onclick="filterEvents('active')"><i class="fas fa-play-circle text-success me-2"></i>Active (<?= $activeEvents ?>)</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="filterEvents('closed')"><i class="fas fa-stop-circle text-secondary me-2"></i>Closed</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="filterEvents('expired')"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Expired (<?= $expiredEvents ?>)</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="eventsTable">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Tenant</th>
                                    <th>Status</th>
                                    <th>Dates</th>
                                    <th>Stats</th>
                                    <th>Revenue</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($events as $event): ?>
                                <tr data-event-id="<?= $event['id'] ?>" 
                                    data-status="<?= $event['admin_status'] ?>"
                                    data-event-status="<?= $event['status'] ?>"
                                    data-expired="<?= (strtotime($event['end_date']) < time() && $event['status'] !== 'closed') ? 'true' : 'false' ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($event['featured_image']): ?>
                                                <img src="<?= htmlspecialchars(image_url($event['featured_image'])) ?>" 
                                                     alt="Event Image" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="fas fa-calendar-alt text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="mb-1">
                                                    <a href="<?= SUPERADMIN_URL ?>/events/<?= $event['id'] ?>" 
                                                       class="text-decoration-none">
                                                        <?= htmlspecialchars($event['name']) ?>
                                                    </a>
                                                </h6>
                                                <small class="text-muted"><?= htmlspecialchars($event['code']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($event['tenant_name']) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($event['created_by_email']) ?></small>
                                        </div>
                                    </td>
                                    <td>
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
                                        
                                        $adminStatusIcon = match($event['admin_status']) {
                                            'approved' => 'check-circle',
                                            'pending' => 'clock',
                                            'rejected' => 'times-circle',
                                            'under_review' => 'eye',
                                            default => 'question-circle'
                                        };
                                        ?>
                                        
                                        <!-- Event Status Badge (Primary) -->
                                        <span class="badge bg-<?= $eventStatusClass ?> mb-1">
                                            <i class="fas fa-circle me-1" style="font-size: 0.5em;"></i>
                                            <?= $eventStatusText ?>
                                        </span>
                                        
                                        <!-- Admin Approval Status (Secondary) -->
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-<?= $adminStatusIcon ?> me-1"></i>
                                            <?= ucfirst($event['admin_status']) ?>
                                        </small>
                                        
                                        <!-- Additional Info -->
                                        <?php if ($event['visibility'] === 'private'): ?>
                                            <br><small class="text-muted"><i class="fas fa-lock me-1"></i>Private</small>
                                        <?php endif; ?>
                                        
                                        <?php if (strtotime($event['end_date']) < time() && $event['status'] !== 'closed'): ?>
                                            <br><small class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Expired</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div>
                                            <small class="text-muted">Start:</small><br>
                                            <?= date('M j, Y', strtotime($event['start_date'])) ?>
                                            <br>
                                            <small class="text-muted">End:</small><br>
                                            <?= date('M j, Y', strtotime($event['end_date'])) ?>
                                            
                                            <?php if (strtotime($event['end_date']) < time() && $event['status'] !== 'closed'): ?>
                                                <br><span class="badge bg-danger small">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>Expired
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div><i class="fas fa-users me-1"></i><?= number_format($event['contestant_count']) ?> contestants</div>
                                            <div><i class="fas fa-tags me-1"></i><?= number_format($event['category_count']) ?> categories</div>
                                            <div><i class="fas fa-vote-yea me-1"></i><?= number_format($event['total_votes']) ?> votes</div>
                                            <div><i class="fas fa-receipt me-1"></i><?= number_format($event['transaction_count']) ?> transactions</div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            GHS <?= number_format($event['total_revenue'], 2) ?>
                                        </strong>
                                        <br>
                                        <small class="text-muted">
                                            GHS <?= number_format($event['vote_price'], 2) ?> per vote
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= SUPERADMIN_URL ?>/events/<?= $event['id'] ?>" 
                                               class="btn btn-sm btn-primary" title="View Details">
                                                <i class="fas fa-eye me-1"></i>View
                                            </a>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-secondary dropdown-toggle dropdown-toggle-split" 
                                                        data-bs-toggle="dropdown" title="More Actions">
                                                    <span class="visually-hidden">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><h6 class="dropdown-header">Approval Actions</h6></li>
                                                    <?php if ($event['admin_status'] !== 'approved'): ?>
                                                        <li><a class="dropdown-item" href="#" onclick="updateEventStatus(<?= $event['id'] ?>, 'approved')">
                                                            <i class="fas fa-check-circle text-success me-2"></i>Approve Event
                                                        </a></li>
                                                    <?php endif; ?>
                                                    <?php if ($event['admin_status'] !== 'pending'): ?>
                                                        <li><a class="dropdown-item" href="#" onclick="updateEventStatus(<?= $event['id'] ?>, 'pending')">
                                                            <i class="fas fa-clock text-warning me-2"></i>Mark as Pending
                                                        </a></li>
                                                    <?php endif; ?>
                                                    <?php if ($event['admin_status'] !== 'rejected'): ?>
                                                        <li><a class="dropdown-item" href="#" onclick="updateEventStatus(<?= $event['id'] ?>, 'rejected')">
                                                            <i class="fas fa-times-circle text-danger me-2"></i>Reject Event
                                                        </a></li>
                                                    <?php endif; ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><h6 class="dropdown-header">Event Actions</h6></li>
                                                    <?php if ($event['status'] !== 'closed' && strtotime($event['end_date']) < time()): ?>
                                                        <li><a class="dropdown-item" href="#" onclick="closeEvent(<?= $event['id'] ?>)">
                                                            <i class="fas fa-stop-circle text-warning me-2"></i>Close Event
                                                        </a></li>
                                                    <?php endif; ?>
                                                    <?php if (isset($event['featured']) && $event['featured'] != 1): ?>
                                                        <li><a class="dropdown-item" href="#" onclick="toggleFeatured(<?= $event['id'] ?>, 1)">
                                                            <i class="fas fa-star text-warning me-2"></i>Feature Event
                                                        </a></li>
                                                    <?php elseif (isset($event['featured']) && $event['featured'] == 1): ?>
                                                        <li><a class="dropdown-item" href="#" onclick="toggleFeatured(<?= $event['id'] ?>, 0)">
                                                            <i class="far fa-star text-muted me-2"></i>Unfeature Event
                                                        </a></li>
                                                    <?php endif; ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><h6 class="dropdown-header">View Options</h6></li>
                                                    <li><a class="dropdown-item" href="<?= APP_URL ?>/events/<?= $event['code'] ?>" target="_blank">
                                                        <i class="fas fa-external-link-alt me-2"></i>View Public Page
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="viewEventAnalytics(<?= $event['id'] ?>)">
                                                        <i class="fas fa-chart-line me-2"></i>View Analytics
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="viewEventTransactions(<?= $event['id'] ?>)">
                                                        <i class="fas fa-receipt me-2"></i>View Transactions
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
                    <input type="hidden" id="eventId" name="event_id">
                    <input type="hidden" id="newStatus" name="status">
                    
                    <div class="mb-3">
                        <label class="form-label">Event:</label>
                        <div id="eventName" class="fw-bold"></div>
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
    const eventRow = document.querySelector(`tr[data-event-id="${eventId}"]`);
    const eventName = eventRow.querySelector('h6 a').textContent;
    
    document.getElementById('eventId').value = eventId;
    document.getElementById('newStatus').value = status;
    document.getElementById('eventName').textContent = eventName;
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

function filterEvents(status) {
    const rows = document.querySelectorAll('#eventsTable tbody tr');
    
    rows.forEach(row => {
        if (status === 'all' || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function refreshEvents() {
    location.reload();
}

function closeExpiredEvents() {
    if (!confirm('Are you sure you want to close all expired events? This action cannot be undone.')) {
        return;
    }
    
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Closing...';
    button.disabled = true;
    
    fetch('<?= SUPERADMIN_URL ?>/events/close-expired', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            if (data.closed_count > 0) {
                setTimeout(() => location.reload(), 1500);
            }
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        showAlert('danger', 'An error occurred while closing expired events');
        console.error('Error:', error);
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function filterEvents(filter) {
    const rows = document.querySelectorAll('#eventsTable tbody tr');
    
    rows.forEach(row => {
        const status = row.dataset.status;
        const eventStatus = row.dataset.eventStatus;
        const expired = row.dataset.expired === 'true';
        let show = false;
        
        switch(filter) {
            case 'all':
                show = true;
                break;
            case 'approved':
                show = status === 'approved';
                break;
            case 'pending':
                show = status === 'pending';
                break;
            case 'rejected':
                show = status === 'rejected';
                break;
            case 'under_review':
                show = status === 'under_review';
                break;
            case 'active':
                show = eventStatus === 'active';
                break;
            case 'closed':
                show = eventStatus === 'closed';
                break;
            case 'expired':
                show = expired;
                break;
        }
        
        row.style.display = show ? '' : 'none';
    });
    
    // Update filter button text
    const filterButton = document.querySelector('.dropdown-toggle');
    const filterText = filter.charAt(0).toUpperCase() + filter.slice(1);
    if (filter !== 'all') {
        filterButton.innerHTML = `<i class="fas fa-filter me-1"></i>${filterText}`;
    } else {
        filterButton.innerHTML = `<i class="fas fa-filter me-1"></i>Filter`;
    }
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

function searchEvents() {
    const searchTerm = document.getElementById('searchEvents').value.toLowerCase();
    const rows = document.querySelectorAll('#eventsTable tbody tr');
    
    rows.forEach(row => {
        const eventName = row.querySelector('h6 a').textContent.toLowerCase();
        const eventCode = row.querySelector('small.text-muted').textContent.toLowerCase();
        const tenantName = row.querySelector('td:nth-child(2) strong').textContent.toLowerCase();
        
        if (eventName.includes(searchTerm) || eventCode.includes(searchTerm) || tenantName.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function closeEvent(eventId) {
    if (!confirm('Are you sure you want to close this event? This will finalize the results and prevent further voting.')) {
        return;
    }
    
    fetch(`<?= SUPERADMIN_URL ?>/events/${eventId}/close`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Event closed successfully');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('danger', data.message || 'Failed to close event');
        }
    })
    .catch(error => {
        showAlert('danger', 'An error occurred while closing the event');
        console.error('Error:', error);
    });
}

function toggleFeatured(eventId, featured) {
    const action = featured ? 'feature' : 'unfeature';
    
    fetch(`<?= SUPERADMIN_URL ?>/events/${eventId}/toggle-featured`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ featured: featured })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', `Event ${action}d successfully`);
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('danger', data.message || `Failed to ${action} event`);
        }
    })
    .catch(error => {
        showAlert('danger', `An error occurred while ${action}ing the event`);
        console.error('Error:', error);
    });
}

function viewEventAnalytics(eventId) {
    window.location.href = `<?= SUPERADMIN_URL ?>/events/${eventId}#analytics`;
}

function viewEventTransactions(eventId) {
    window.location.href = `<?= SUPERADMIN_URL ?>/events/${eventId}#transactions`;
}

function exportEventsReport() {
    showAlert('info', 'Generating events report...');
    
    fetch('<?= SUPERADMIN_URL ?>/events/export', {
        method: 'POST'
    })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `events-report-${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        a.remove();
        showAlert('success', 'Report downloaded successfully');
    })
    .catch(error => {
        showAlert('danger', 'Failed to generate report');
        console.error('Error:', error);
    });
}
</script>
