<!-- Payouts Management -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-money-check-alt text-success me-2"></i>
            Payouts
        </h2>
        <p class="text-muted mb-0">Manage tenant payouts and revenue sharing</p>
    </div>
    <div>
        <button class="btn btn-success" onclick="processPendingPayouts()">
            <i class="fas fa-play me-2"></i>Process Pending
        </button>
        <button class="btn btn-outline-secondary" onclick="location.reload()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
    </div>
</div>

<!-- Payout Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">$<?= number_format($payouts['total_paid'] ?? 0) ?></div>
                    <div>Total Paid Out</div>
                    <div class="small">All time</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-money-check-alt fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-warning">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">$<?= number_format($payouts['pending_amount'] ?? 0) ?></div>
                    <div>Pending Payouts</div>
                    <div class="small">Awaiting processing</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-clock fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-primary">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($payouts['this_month'] ?? 0) ?></div>
                    <div>This Month</div>
                    <div class="small">Payouts processed</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-calendar fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">$<?= number_format($payouts['avg_payout'] ?? 0) ?></div>
                    <div>Average Payout</div>
                    <div class="small">Per tenant</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-chart-line fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Payouts -->
<?php if (!empty($payouts['pending'])): ?>
<div class="card mb-4 border-warning">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Pending Payouts (<?= count($payouts['pending']) ?>)
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>Amount</th>
                        <th>Period</th>
                        <th>Bank Details</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payouts['pending'] as $payout): ?>
                    <tr data-payout-id="<?= $payout['id'] ?? 0 ?>">
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($payout['tenant_name'] ?? 'Unknown') ?></div>
                            <small class="text-muted"><?= htmlspecialchars($payout['tenant_email'] ?? '') ?></small>
                        </td>
                        <td>
                            <div class="fw-bold text-success">$<?= number_format($payout['amount'] ?? 0, 2) ?></div>
                            <small class="text-muted">Revenue share: <?= $payout['share_percentage'] ?? 0 ?>%</small>
                        </td>
                        <td>
                            <div><?= htmlspecialchars($payout['period'] ?? 'N/A') ?></div>
                        </td>
                        <td>
                            <div><?= htmlspecialchars($payout['bank_name'] ?? 'N/A') ?></div>
                            <small class="text-muted">****<?= $payout['account_last_four'] ?? '0000' ?></small>
                        </td>
                        <td>
                            <div><?= date('M j, Y', strtotime($payout['due_date'] ?? 'now')) ?></div>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-success" onclick="processPayout(<?= $payout['id'] ?? 0 ?>)">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewPayoutDetails(<?= $payout['id'] ?? 0 ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="holdPayout(<?= $payout['id'] ?? 0 ?>)">
                                    <i class="fas fa-pause"></i>
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
<?php endif; ?>

<!-- Payout History -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-history me-2"></i>
            Payout History
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($payouts['history'])): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Payout ID</th>
                            <th>Tenant</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Method</th>
                            <th>Processed Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payouts['history'] as $payout): ?>
                        <tr>
                            <td>
                                <code><?= htmlspecialchars($payout['payout_id'] ?? 'N/A') ?></code>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($payout['tenant_name'] ?? 'Unknown') ?></div>
                            </td>
                            <td>
                                <div class="fw-bold">$<?= number_format($payout['amount'] ?? 0, 2) ?></div>
                                <?php if (!empty($payout['fee'])): ?>
                                    <small class="text-muted">Fee: $<?= number_format($payout['fee'], 2) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $status = $payout['status'] ?? 'unknown';
                                $statusClass = match($status) {
                                    'completed' => 'bg-success',
                                    'processing' => 'bg-info',
                                    'failed' => 'bg-danger',
                                    'on_hold' => 'bg-warning',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $status)) ?></span>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($payout['method'] ?? 'N/A') ?></div>
                            </td>
                            <td>
                                <div><?= date('M j, Y', strtotime($payout['processed_at'] ?? 'now')) ?></div>
                                <small class="text-muted"><?= date('H:i', strtotime($payout['processed_at'] ?? 'now')) ?></small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewPayoutDetails(<?= $payout['id'] ?? 0 ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="downloadPayoutReceipt(<?= $payout['id'] ?? 0 ?>)">
                                        <i class="fas fa-receipt"></i>
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
                <i class="fas fa-money-check-alt fa-3x text-muted mb-3"></i>
                <h5>No Payout History</h5>
                <p class="text-muted">No payouts have been processed yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Payout Details Modal -->
<div class="modal fade" id="payoutDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payout Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="payoutDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="approveFromModal" style="display: none;">
                    <i class="fas fa-check me-2"></i>Approve
                </button>
                <button type="button" class="btn btn-danger" id="rejectFromModal" style="display: none;">
                    <i class="fas fa-times me-2"></i>Reject
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Reason Modal -->
<div class="modal fade" id="rejectReasonModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Payout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="rejectionReason" class="form-label">Reason for rejection *</label>
                    <textarea class="form-control" id="rejectionReason" rows="3" 
                              placeholder="Please provide a reason for rejecting this payout..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmReject">
                    <i class="fas fa-times me-2"></i>Reject Payout
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPayoutId = null;

function processPendingPayouts() {
    if (confirm('Are you sure you want to process all pending payouts?')) {
        const pendingPayouts = document.querySelectorAll('tr[data-payout-id]');
        const payoutIds = Array.from(pendingPayouts).map(row => row.dataset.payoutId);
        
        if (payoutIds.length === 0) {
            alert('No pending payouts to process.');
            return;
        }
        
        fetch('<?= SUPERADMIN_URL ?>/financial/payouts/batch-process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ payout_ids: payoutIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing payouts.');
        });
    }
}

function processPayout(payoutId) {
    if (confirm('Are you sure you want to approve this payout?')) {
        fetch(`<?= SUPERADMIN_URL ?>/financial/payouts/${payoutId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Payout approved successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while approving the payout.');
        });
    }
}

function viewPayoutDetails(payoutId) {
    currentPayoutId = payoutId;
    const modal = new bootstrap.Modal(document.getElementById('payoutDetailsModal'));
    
    fetch(`<?= SUPERADMIN_URL ?>/financial/payouts/${payoutId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPayoutDetails(data.payout);
                
                // Show action buttons if payout is pending
                if (data.payout.status === 'queued') {
                    document.getElementById('approveFromModal').style.display = 'inline-block';
                    document.getElementById('rejectFromModal').style.display = 'inline-block';
                } else {
                    document.getElementById('approveFromModal').style.display = 'none';
                    document.getElementById('rejectFromModal').style.display = 'none';
                }
            } else {
                document.getElementById('payoutDetailsContent').innerHTML = 
                    '<div class="alert alert-danger">Error loading payout details: ' + data.error + '</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('payoutDetailsContent').innerHTML = 
                '<div class="alert alert-danger">An error occurred while loading payout details.</div>';
        });
    
    modal.show();
}

function displayPayoutDetails(payout) {
    const accountDetails = JSON.parse(payout.account_details || '{}');
    let accountInfo = '';
    
    switch (payout.method_type) {
        case 'bank_transfer':
            accountInfo = `
                <strong>Bank:</strong> ${accountDetails.bank_name || 'N/A'}<br>
                <strong>Account:</strong> ****${(accountDetails.account_number || '').slice(-4)}<br>
                <strong>Account Name:</strong> ${accountDetails.account_name || 'N/A'}
            `;
            break;
        case 'mobile_money':
            accountInfo = `
                <strong>Provider:</strong> ${accountDetails.provider || 'N/A'}<br>
                <strong>Phone:</strong> ****${(accountDetails.phone_number || '').slice(-4)}<br>
                <strong>Account Name:</strong> ${accountDetails.account_name || 'N/A'}
            `;
            break;
        case 'paypal':
            accountInfo = `<strong>Email:</strong> ${accountDetails.email || 'N/A'}`;
            break;
        case 'stripe':
            accountInfo = `<strong>Account ID:</strong> ****${(accountDetails.account_id || '').slice(-4)}`;
            break;
    }
    
    const statusClass = {
        'queued': 'warning',
        'processing': 'info',
        'success': 'success',
        'failed': 'danger',
        'cancelled': 'secondary'
    }[payout.status] || 'secondary';
    
    document.getElementById('payoutDetailsContent').innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>Payout Information</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Payout ID:</strong></td>
                        <td><code>${payout.payout_id}</code></td>
                    </tr>
                    <tr>
                        <td><strong>Amount:</strong></td>
                        <td><strong>$${parseFloat(payout.amount).toFixed(2)}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Processing Fee:</strong></td>
                        <td>$${parseFloat(payout.processing_fee || 0).toFixed(2)}</td>
                    </tr>
                    <tr>
                        <td><strong>Net Amount:</strong></td>
                        <td><strong>$${parseFloat(payout.net_amount || payout.amount).toFixed(2)}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td><span class="badge bg-${statusClass}">${payout.status.charAt(0).toUpperCase() + payout.status.slice(1)}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Requested:</strong></td>
                        <td>${new Date(payout.created_at).toLocaleString()}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Tenant Information</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Tenant:</strong></td>
                        <td>${payout.tenant_name}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>${payout.tenant_email}</td>
                    </tr>
                    <tr>
                        <td><strong>Phone:</strong></td>
                        <td>${payout.tenant_phone || 'N/A'}</td>
                    </tr>
                </table>
                
                <h6>Payment Method</h6>
                <div class="card bg-light">
                    <div class="card-body">
                        <strong>${payout.method_name}</strong><br>
                        <small class="text-muted">${payout.method_type.replace('_', ' ').toUpperCase()}</small><br>
                        <div class="mt-2">${accountInfo}</div>
                        ${payout.method_verified ? '<span class="badge bg-success mt-2">Verified</span>' : '<span class="badge bg-warning mt-2">Unverified</span>'}
                    </div>
                </div>
            </div>
        </div>
    `;
}

function holdPayout(payoutId) {
    const reason = prompt('Please provide a reason for rejecting this payout:');
    if (reason && reason.trim()) {
        rejectPayoutWithReason(payoutId, reason.trim());
    }
}

function rejectPayoutWithReason(payoutId, reason) {
    fetch(`<?= SUPERADMIN_URL ?>/financial/payouts/${payoutId}/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Payout rejected successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while rejecting the payout.');
    });
}

function downloadPayoutReceipt(payoutId) {
    window.open(`<?= SUPERADMIN_URL ?>/financial/payouts/${payoutId}/receipt`, '_blank');
}

// Modal event handlers
document.getElementById('approveFromModal').addEventListener('click', function() {
    if (currentPayoutId) {
        processPayout(currentPayoutId);
        bootstrap.Modal.getInstance(document.getElementById('payoutDetailsModal')).hide();
    }
});

document.getElementById('rejectFromModal').addEventListener('click', function() {
    if (currentPayoutId) {
        bootstrap.Modal.getInstance(document.getElementById('payoutDetailsModal')).hide();
        const rejectModal = new bootstrap.Modal(document.getElementById('rejectReasonModal'));
        rejectModal.show();
    }
});

document.getElementById('confirmReject').addEventListener('click', function() {
    const reason = document.getElementById('rejectionReason').value.trim();
    if (!reason) {
        alert('Please provide a reason for rejection.');
        return;
    }
    
    if (currentPayoutId) {
        rejectPayoutWithReason(currentPayoutId, reason);
        bootstrap.Modal.getInstance(document.getElementById('rejectReasonModal')).hide();
        document.getElementById('rejectionReason').value = '';
    }
});
</script>
