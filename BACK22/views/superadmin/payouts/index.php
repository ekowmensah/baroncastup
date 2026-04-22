<?php 
$content = ob_start();
if (!isset($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$csrf_field = '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
$csrf_token_value = htmlspecialchars($_SESSION['csrf_token']);
?>

<!-- Super Admin Payout Management Dashboard -->
<div class="row">
    <!-- Payout Statistics Overview -->
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Payout Overview
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="text-center">
                            <div class="h4 text-warning mb-1" id="stat-pending-count">
                                <?= $stats['pending_count'] ?? 0 ?>
                            </div>
                            <div class="text-muted small">Pending Approval</div>
                            <div class="h4 text-warning mb-1" id="stat-pending-amount">
                                GH₵<?= number_format($stats['pending_approval'] ?? 0, 2) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <div class="h4 text-info mb-1">
                                <?= count($approved_payouts ?? []) ?>
                            </div>
                            <div class="text-muted small">Approved</div>
                            <div class="text-info small fw-bold" id="stat-approved-amount">
                                GH₵<?= number_format($stats['approved_pending'] ?? 0, 2) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <div class="h4 text-primary mb-1" id="stat-processing-count">
                                <?= $stats['processing_count'] ?? 0 ?>
                            </div>
                            <div class="text-muted small">Processing</div>
                            <div class="text-primary small fw-bold">
                                GH₵<?= number_format($stats['processing_amount'] ?? 0, 2) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <div class="h4 text-success mb-1" id="stat-this-month">
                                <?= $stats['this_month_count'] ?? 0 ?>
                            </div>
                            <div class="text-muted small">Paid This Month</div>
                            <div class="text-success small fw-bold">
                                GH₵<?= number_format($stats['total_paid'] ?? 0, 2) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <div class="h4 text-secondary mb-1" id="stat-avg-payout">
                                GH₵<?= number_format($stats['avg_payout'] ?? 0, 2) ?>
                            </div>
                            <div class="text-muted small">Average Payout</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <div class="h4 text-dark mb-1" id="stat-total-volume">
                                GH₵<?= number_format(($stats['total_paid'] ?? 0) + ($stats['pending_approval'] ?? 0) + ($stats['approved_pending'] ?? 0), 2) ?>
                            </div>
                            <div class="text-muted small">Total Volume</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Pending Approvals -->
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clock me-2 text-warning"></i>
                    Pending Approvals
                    <?php if (!empty($pending_payouts)): ?>
                        <span class="badge bg-warning text-dark"><?= count($pending_payouts) ?></span>
                    <?php endif; ?>
                </h5>
                <?php if (!empty($pending_payouts)): ?>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-success" onclick="showBulkApprovalModal()">
                            <i class="fas fa-check-double me-1"></i>Bulk Approve
                        </button>
                        <a href="<?= SUPERADMIN_URL ?>/payouts/pending" class="btn btn-sm btn-outline-primary">
                            View All
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($pending_payouts)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <h6 class="text-muted">No Pending Approvals</h6>
                        <p class="text-muted small">All payout requests have been reviewed.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Organizer</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Requested</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($pending_payouts, 0, 5) as $payout): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($payout['tenant_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($payout['tenant_email']) ?></small>
                                    </td>
                                    <td>
                                        <span class="fw-bold">GH₵<?= number_format($payout['amount'], 2) ?></span>
                                        <?php if ($payout['processing_fee'] > 0): ?>
                                            <br><small class="text-muted">Fee: GH₵<?= number_format($payout['processing_fee'], 2) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?= ucfirst(str_replace('_', ' ', $payout['method_type'] ?? 'N/A')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= $payout['created_at'] ? date('M j, Y', strtotime($payout['created_at'])) : 'Not available' ?>
                                            <br><?= $payout['created_at'] ? date('g:i A', strtotime($payout['created_at'])) : '' ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= SUPERADMIN_URL ?>/payouts/approve/<?= $payout['id'] ?>" 
                                               class="btn btn-outline-success btn-sm" title="Review">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-success btn-sm" 
                                                    onclick="quickApprove(<?= $payout['id'] ?>)" title="Quick Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <a href="<?= SUPERADMIN_URL ?>/payouts/reject/<?= $payout['id'] ?>" 
                                               class="btn btn-outline-danger btn-sm" title="Reject Payout">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($pending_payouts) > 5): ?>
                        <div class="text-center mt-3">
                            <a href="<?= SUPERADMIN_URL ?>/payouts/pending" class="btn btn-outline-primary btn-sm">
                                View All <?= count($pending_payouts) ?> Pending Requests
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Approved & Ready to Process -->
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-check-circle me-2 text-success"></i>
                    Ready to Process
                    <?php if (!empty($approved_payouts)): ?>
                        <span class="badge bg-success"><?= count($approved_payouts) ?></span>
                    <?php endif; ?>
                </h5>
                <?php if (!empty($approved_payouts)): ?>
                    <button type="button" class="btn btn-sm btn-primary" onclick="processBatch()">
                        <i class="fas fa-play me-1"></i>Process Batch
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($approved_payouts)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-tasks text-muted fa-3x mb-3"></i>
                        <h6 class="text-muted">No Approved Payouts</h6>
                        <p class="text-muted small">Approved payouts will appear here for processing.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Organizer</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Approved</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($approved_payouts, 0, 5) as $payout): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($payout['tenant_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($payout['tenant_email']) ?></small>
                                    </td>
                                    <td>
                                        <span class="fw-bold">GH₵<?= number_format($payout['amount'], 2) ?></span>
                                        <?php if ($payout['processing_fee'] > 0): ?>
                                            <br><small class="text-muted">Net: GH₵<?= number_format($payout['net_amount'], 2) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?= ucfirst(str_replace('_', ' ', $payout['method_type'] ?? 'N/A')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= $payout['approved_at'] ? date('M j, Y', strtotime($payout['approved_at'])) : 'Not available' ?>
                                            <br><?= $payout['approved_at'] ? date('g:i A', strtotime($payout['approved_at'])) : '' ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= SUPERADMIN_URL ?>/payouts/details/<?= $payout['id'] ?>" 
                                               class="btn btn-outline-info btn-sm" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-primary btn-sm" 
                                                    onclick="processSingle(<?= $payout['id'] ?>)" title="Process Now">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <a href="<?= SUPERADMIN_URL ?>/payouts/reverse-approved/<?= $payout['id'] ?>" 
                                               class="btn btn-outline-warning btn-sm" title="Reverse to Pending">
                                                <i class="fas fa-undo"></i>
                                            </a>
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
    </div>
</div>

<!-- Recent Payout Activity -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>
                    Recent Payout Activity
                </h5>
                <a href="<?= SUPERADMIN_URL ?>/payouts/history" class="btn btn-sm btn-outline-primary">
                    View Full History
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($recent_payouts)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-file-alt text-muted fa-3x mb-3"></i>
                        <h6 class="text-muted">No Recent Activity</h6>
                        <p class="text-muted small">Recent payout activities will appear here.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Payout ID</th>
                                    <th>Organizer</th>
                                    <th>Amount</th>
                                    <th>Expected Amount</th>
                                    <th>Status</th>
                                    <th>Method</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_payouts as $payout): ?>
                                <tr>
                                    <td>
                                        <code><?= htmlspecialchars($payout['payout_id']) ?></code>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($payout['tenant_name'] ?? 'Unknown Tenant') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($payout['tenant_email'] ?? 'No email') ?></small>
                                    </td>
                                    <td>
                                        <span class="fw-bold">GH₵<?= number_format($payout['amount'], 2) ?></span>
                                        <?php if ($payout['processing_fee'] > 0): ?>
                                            <br><small class="text-muted">Fee: GH₵<?= number_format($payout['processing_fee'], 2) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">GH₵<?= number_format($payout['net_amount'] ?? ($payout['amount'] - ($payout['processing_fee'] ?? 0)), 2) ?></span>
                                        <br><small class="text-muted">After fees</small>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClasses = [
                                            'pending' => 'bg-warning text-dark',
                                            'approved' => 'bg-info',
                                            'processing' => 'bg-primary',
                                            'paid' => 'bg-success',
                                            'failed' => 'bg-danger',
                                            'rejected' => 'bg-secondary',
                                            'cancelled' => 'bg-dark'
                                        ];
                                        $statusClass = $statusClasses[$payout['status']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?= $statusClass ?>">
                                            <?= ucfirst($payout['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?= ucfirst(str_replace('_', ' ', $payout['method_type'] ?? 'N/A')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= $payout['created_at'] ? date('M j, Y g:i A', strtotime($payout['created_at'])) : 'Not available' ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= SUPERADMIN_URL ?>/payouts/details/<?= $payout['id'] ?>" 
                                               class="btn btn-outline-info btn-sm" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($payout['status'] === 'paid'): ?>
                                                <button type="button" class="btn btn-outline-success btn-sm" 
                                                        onclick="downloadPayoutReceipt(<?= $payout['id'] ?>)" title="Download Receipt">
                                                    <i class="fas fa-receipt"></i>
                                                </button>
                                                <a href="<?= SUPERADMIN_URL ?>/payouts/reverse-processed/<?= $payout['id'] ?>" 
                                                   class="btn btn-outline-warning btn-sm" title="Reverse to Approved">
                                                    <i class="fas fa-undo"></i>
                                                </a>
                                            <?php elseif ($payout['status'] === 'approved'): ?>
                                                <a href="<?= SUPERADMIN_URL ?>/payouts/reverse-approved/<?= $payout['id'] ?>" 
                                                   class="btn btn-outline-warning btn-sm" title="Reverse to Pending">
                                                    <i class="fas fa-undo"></i>
                                                </a>
                                            <?php endif; ?>
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
    </div>
</div>

<!-- Bulk Approval Modal -->
<div class="modal fade" id="bulkApprovalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Approve Payouts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= SUPERADMIN_URL ?>/payouts/bulk-approve">
                <?= $csrf_field ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Payouts to Approve:</label>
                        <div class="max-height-200 overflow-auto border rounded p-2">
                            <?php foreach ($pending_payouts ?? [] as $payout): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="payout_ids[]" 
                                       value="<?= $payout['id'] ?>" id="payout_<?= $payout['id'] ?>">
                                <label class="form-check-label" for="payout_<?= $payout['id'] ?>">
                                    <strong><?= htmlspecialchars($payout['tenant_name']) ?></strong> - 
                                    GH₵<?= number_format($payout['amount'], 2) ?>
                                    <small class="text-muted">(<?= $payout['created_at'] ? date('M j', strtotime($payout['created_at'])) : 'N/A' ?>)</small>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="bulk_notes" class="form-label">Approval Notes (Optional)</label>
                        <textarea class="form-control" id="bulk_notes" name="bulk_notes" rows="3" 
                                  placeholder="Add notes for this bulk approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>Approve Selected
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const CSRF_TOKEN = '<?= $csrf_token_value ?>';

function addCsrfToForm(form) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = '_csrf_token';
    input.value = CSRF_TOKEN;
    form.appendChild(input);
}

function showBulkApprovalModal() {
    new bootstrap.Modal(document.getElementById('bulkApprovalModal')).show();
}

function quickApprove(payoutId) {
    if (confirm('Are you sure you want to approve this payout?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `<?= SUPERADMIN_URL ?>/payouts/process-approval/${payoutId}`;
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'approve';
        form.appendChild(actionInput);
        
        const notesInput = document.createElement('input');
        notesInput.type = 'hidden';
        notesInput.name = 'notes';
        notesInput.value = 'Quick approval from dashboard';
        form.appendChild(notesInput);
        
        addCsrfToForm(form);
        document.body.appendChild(form);
        form.submit();
    }
}

function quickReject(payoutId) {
    const reason = prompt('Please provide a reason for rejection:');
    if (reason && reason.trim()) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `<?= SUPERADMIN_URL ?>/payouts/approve/${payoutId}`;
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'reject';
        form.appendChild(actionInput);
        
        const reasonInput = document.createElement('input');
        reasonInput.type = 'hidden';
        reasonInput.name = 'reason';
        reasonInput.value = reason.trim();
        form.appendChild(reasonInput);
        
        addCsrfToForm(form);
        document.body.appendChild(form);
        form.submit();
    }
}

function processSingle(payoutId) {
    if (confirm('Are you sure you want to process this payout now?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `<?= SUPERADMIN_URL ?>/payouts/process/${payoutId}`;
        addCsrfToForm(form);
        document.body.appendChild(form);
        form.submit();
    }
}

function processBatch() {
    const approvedIds = <?= json_encode(array_column($approved_payouts ?? [], 'id')) ?>;
    if (approvedIds.length === 0) {
        alert('No approved payouts to process.');
        return;
    }
    if (confirm(`Are you sure you want to process all ${approvedIds.length} approved payout(s)?`)) {
        // Process first one, then redirect will happen
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `<?= SUPERADMIN_URL ?>/payouts/process/${approvedIds[0]}`;
        addCsrfToForm(form);
        document.body.appendChild(form);
        form.submit();
    }
}

function downloadPayoutReceipt(payoutId) {
    // Open receipt in new window/tab
    window.open(`<?= SUPERADMIN_URL ?>/payouts/${payoutId}/receipt`, '_blank');
}

// Lightweight AJAX polling - update stats without full page reload
setInterval(function() {
    if (document.visibilityState !== 'visible' || document.querySelector('.modal.show')) return;
    
    fetch(`<?= SUPERADMIN_URL ?>/api/payouts/stats`)
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.stats) return;
            const s = data.stats;
            // Update stat card values if elements exist
            const updates = {
                'stat-pending-count': s.pending_count ?? 0,
                'stat-pending-amount': 'GH₵' + Number(s.pending_approval ?? 0).toLocaleString(undefined, {minimumFractionDigits: 2}),
                'stat-approved-amount': 'GH₵' + Number(s.approved_pending ?? 0).toLocaleString(undefined, {minimumFractionDigits: 2}),
                'stat-processing-count': s.processing_count ?? 0,
                'stat-this-month': s.this_month_count ?? 0,
                'stat-avg-payout': 'GH₵' + Number(s.avg_payout ?? 0).toLocaleString(undefined, {minimumFractionDigits: 2}),
                'stat-total-volume': 'GH₵' + Number(s.total_paid ?? 0).toLocaleString(undefined, {minimumFractionDigits: 2})
            };
            for (const [id, val] of Object.entries(updates)) {
                const el = document.getElementById(id);
                if (el) el.textContent = val;
            }
        })
        .catch(() => {}); // Silently ignore network errors
}, 30000);
</script>

<style>
.max-height-200 {
    max-height: 200px;
}

.card-title .badge {
    font-size: 0.75em;
}

.card-title.alert-danger {
    border-left: 4px solid #dc3545;
}

/* Reversal Button Styling */
.btn-outline-warning:hover {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #000;
}

.btn-group .btn-outline-warning {
    border-left: 1px solid #dee2e6;
}

/* Tooltip styling for better UX */
[title] {
    position: relative;
}

/* Status-based button grouping */
.btn-group-sm .btn {
    margin-right: 1px;
}

.btn-group-sm .btn:last-child {
    margin-right: 0;
}

.table-sm th,
.table-sm td {
    padding: 0.5rem 0.25rem;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layout/superadmin_layout.php';
?>
