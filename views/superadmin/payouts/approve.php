<?php 
$content = ob_start();
if (!isset($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$csrf_field = '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
?>

<!-- Payout Approval Review -->
<div class="row">
    <div class="col-lg-8">
        <!-- Payout Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-invoice me-2"></i>
                    Payout Request Details
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Payout ID:</td>
                                <td><code><?= htmlspecialchars($payout['payout_id']) ?></code></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Amount Requested:</td>
                                <td class="h5 text-primary">GH₵<?= number_format($payout['amount'], 2) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Processing Fee:</td>
                                <td>GH₵<?= number_format($payout['processing_fee'], 2) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Net Amount:</td>
                                <td class="h6 text-success">GH₵<?= number_format($payout['net_amount'], 2) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Status:</td>
                                <td>
                                    <span class="badge bg-warning text-dark">
                                        <?= ucfirst($payout['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Requested Date:</td>
                                <td><?= $payout['created_at'] ? date('F j, Y g:i A', strtotime($payout['created_at'])) : 'Not available' ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Payout Method:</td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <?= ucfirst(str_replace('_', ' ', $payout['payout_method'])) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Method Details:</td>
                                <td>
                                    <?php if ($payout_method && isset($payout_method['method_name'])): ?>
                                        <div class="small">
                                            <strong><?= htmlspecialchars($payout_method['method_name']) ?></strong>
                                            <?php if (isset($payout_method['is_verified']) && $payout_method['is_verified']): ?>
                                                <i class="fas fa-check-circle text-success ms-1" title="Verified"></i>
                                            <?php else: ?>
                                                <i class="fas fa-exclamation-triangle text-warning ms-1" title="Not Verified"></i>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Method details not available</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Provider Reference:</td>
                                <td>
                                    <?php if ($payout['provider_reference']): ?>
                                        <code><?= htmlspecialchars($payout['provider_reference']) ?></code>
                                    <?php else: ?>
                                        <span class="text-muted">Not assigned yet</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Failure Reason:</td>
                                <td>
                                    <?php if ($payout['failure_reason']): ?>
                                        <div class="text-danger small">
                                            <?= htmlspecialchars($payout['failure_reason']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">None</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Recipient Details -->
                <?php if ($payout['recipient_details']): ?>
                <div class="mt-4">
                    <h6 class="fw-bold">Recipient Details:</h6>
                    <div class="bg-light p-3 rounded">
                        <?php 
                        $details = json_decode($payout['recipient_details'], true);
                        if ($details): 
                        ?>
                            <div class="row">
                                <?php foreach ($details as $key => $value): ?>
                                    <?php if (!empty($value)): ?>
                                    <div class="col-md-6 mb-2">
                                        <strong><?= ucfirst(str_replace('_', ' ', $key)) ?>:</strong>
                                        <span class="ms-2"><?= htmlspecialchars($value) ?></span>
                                    </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <code><?= htmlspecialchars($payout['recipient_details']) ?></code>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Audit Trail -->
        <?php if (!empty($audit_logs)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>
                    Audit Trail
                </h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php foreach ($audit_logs as $log): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-<?= $log['action'] === 'approved' ? 'success' : ($log['action'] === 'rejected' ? 'danger' : 'primary') ?>"></div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between">
                                <strong><?= ucfirst($log['action']) ?></strong>
                                <small class="text-muted"><?= date('M j, Y g:i A', strtotime($log['created_at'])) ?></small>
                            </div>
                            <?php if ($log['performed_by_name']): ?>
                                <div class="text-muted small">by <?= htmlspecialchars($log['performed_by_name']) ?></div>
                            <?php endif; ?>
                            <?php if ($log['notes']): ?>
                                <div class="mt-1 small"><?= htmlspecialchars($log['notes']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <!-- Organizer Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-building me-2"></i>
                    Organizer Information
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="h5 mb-1"><?= htmlspecialchars($tenant['name'] ?? 'Unknown Tenant') ?></div>
                    <div class="text-muted"><?= htmlspecialchars($tenant['email'] ?? 'No email') ?></div>
                    <?php if (!empty($tenant['phone'])): ?>
                        <div class="text-muted small"><?= htmlspecialchars($tenant['phone']) ?></div>
                    <?php endif; ?>
                </div>

                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="fw-bold">Plan:</td>
                        <td>
                            <span class="badge bg-info">
                                <?= htmlspecialchars($tenant['plan_name'] ?? 'Unknown') ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Status:</td>
                        <td>
                            <?php if (isset($tenant['verified']) && $tenant['verified']): ?>
                                <span class="badge bg-success">Verified</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Unverified</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Member Since:</td>
                        <td><?= isset($tenant['created_at']) && $tenant['created_at'] ? date('M Y', strtotime($tenant['created_at'])) : 'Unknown' ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Total Events:</td>
                        <td><?= $tenant_stats['total_events'] ?? 0 ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Total Revenue:</td>
                        <td>GH₵<?= number_format($tenant_stats['total_revenue'] ?? 0, 2) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Previous Payouts:</td>
                        <td><?= $tenant_stats['previous_payouts'] ?? 0 ?></td>
                    </tr>
                </table>

                <div class="mt-3">
                    <a href="<?= SUPERADMIN_URL ?>/tenants" class="btn btn-outline-info btn-sm w-100">
                        <i class="fas fa-building me-1"></i>View All Tenants
                    </a>
                </div>
            </div>
        </div>

        <!-- Balance Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-wallet me-2"></i>
                    Balance Information
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="fw-bold">Available Balance:</td>
                        <td class="text-success">GH₵<?= number_format($balance['available'], 2) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Pending Approval:</td>
                        <td class="text-warning">GH₵<?= number_format($balance['pending_approval'] ?? 0, 2) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Approved Pending:</td>
                        <td class="text-info">GH₵<?= number_format($balance['approved_pending'] ?? 0, 2) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Total Earned:</td>
                        <td class="text-primary">GH₵<?= number_format($balance['total_earned'], 2) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Total Paid:</td>
                        <td class="text-success">GH₵<?= number_format($balance['total_paid'], 2) ?></td>
                    </tr>
                </table>

                <?php if (($balance['available'] ?? 0) < $payout['amount']): ?>
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Insufficient Balance!</strong><br>
                    Available balance (GH₵<?= number_format($balance['available'] ?? 0, 2) ?>) 
                    is less than requested amount.
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Approval Actions -->
        <?php if ($payout['status'] === 'pending'): ?>
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-gavel me-2"></i>
                    Approval Decision
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= SUPERADMIN_URL ?>/payouts/process-approval/<?= $payout['id'] ?>">
                    <?= $csrf_field ?>
                    <div class="mb-3">
                        <label class="form-label">Decision</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="action" value="approve" id="approve" required>
                            <label class="form-check-label text-success" for="approve">
                                <i class="fas fa-check me-1"></i>Approve Payout
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="action" value="reject" id="reject" required>
                            <label class="form-check-label text-danger" for="reject">
                                <i class="fas fa-times me-1"></i>Reject Payout
                            </label>
                        </div>
                    </div>

                    <div class="mb-3" id="approval-notes" style="display: none;">
                        <label for="notes" class="form-label">Approval Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Add any notes about this approval..."></textarea>
                    </div>

                    <div class="mb-3" id="rejection-reason" style="display: none;">
                        <label for="reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" 
                                  placeholder="Please provide a clear reason for rejection..." required></textarea>
                        <div class="form-text">This will be sent to the organizer.</div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i>Submit Decision
                        </button>
                        <a href="<?= SUPERADMIN_URL ?>/payouts" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Payouts
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-info-circle text-info fa-2x mb-3"></i>
                <h6>Payout Already Processed</h6>
                <p class="text-muted small">This payout has already been reviewed and processed.</p>
                
                <div class="d-grid gap-2">
                    <?php if ($payout['status'] === 'paid'): ?>
                        <button type="button" class="btn btn-success" onclick="downloadPayoutReceipt(<?= $payout['id'] ?>)">
                            <i class="fas fa-receipt me-1"></i>Download Receipt
                        </button>
                        <a href="<?= SUPERADMIN_URL ?>/payouts/reverse-processed/<?= $payout['id'] ?>" class="btn btn-warning">
                            <i class="fas fa-undo me-1"></i>Reverse to Approved
                        </a>
                    <?php elseif ($payout['status'] === 'approved'): ?>
                        <a href="<?= SUPERADMIN_URL ?>/payouts/reverse-approved/<?= $payout['id'] ?>" class="btn btn-info">
                            <i class="fas fa-undo me-1"></i>Reverse to Pending
                        </a>
                    <?php endif; ?>
                    <a href="<?= SUPERADMIN_URL ?>/payouts" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Payouts
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const approveRadio = document.getElementById('approve');
    const rejectRadio = document.getElementById('reject');
    const approvalNotes = document.getElementById('approval-notes');
    const rejectionReason = document.getElementById('rejection-reason');
    const reasonTextarea = document.getElementById('reason');

    function toggleFields() {
        if (approveRadio.checked) {
            approvalNotes.style.display = 'block';
            rejectionReason.style.display = 'none';
            reasonTextarea.required = false;
        } else if (rejectRadio.checked) {
            approvalNotes.style.display = 'none';
            rejectionReason.style.display = 'block';
            reasonTextarea.required = true;
        }
    }

    approveRadio.addEventListener('change', toggleFields);
    rejectRadio.addEventListener('change', toggleFields);
});

function downloadPayoutReceipt(payoutId) {
    // Open receipt in new window/tab
    window.open(`<?= SUPERADMIN_URL ?>/payouts/${payoutId}/receipt`, '_blank');
}
</script>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layout/superadmin_layout.php';
?>
