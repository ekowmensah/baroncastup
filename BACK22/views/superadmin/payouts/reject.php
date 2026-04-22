<?php 
$content = ob_start();
if (!isset($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$csrf_field = '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
?>

<!-- Payout Rejection Form -->
<div class="row">
    <div class="col-lg-8 mx-auto">
        <!-- Payout Details -->
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-times-circle me-2"></i>
                    Reject Payout Request
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> You are about to reject this payout request. The funds will be returned to the organizer's available balance.
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Payout ID:</td>
                                <td><code><?= htmlspecialchars($payout['payout_id']) ?></code></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Amount:</td>
                                <td class="h5 text-primary">GH₵<?= number_format($payout['amount'], 2) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Organizer:</td>
                                <td><?= htmlspecialchars($payout['tenant_name']) ?></td>
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
                                <td class="fw-bold">Processing Fee:</td>
                                <td>GH₵<?= number_format($payout['processing_fee'], 2) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Net Amount:</td>
                                <td class="h6 text-success">GH₵<?= number_format($payout['net_amount'], 2) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Payout Method:</td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <?= ucfirst(str_replace('_', ' ', $payout['payout_method'])) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Current Status:</td>
                                <td>
                                    <span class="badge bg-warning text-dark">
                                        Pending Approval
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rejection Form -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-edit me-2"></i>
                    Rejection Details
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= SUPERADMIN_URL ?>/payouts/process-rejection/<?= $payout['id'] ?>">
                    <?= $csrf_field ?>
                    <div class="mb-4">
                        <label for="reason" class="form-label">
                            <strong>Reason for Rejection <span class="text-danger">*</span></strong>
                        </label>
                        <textarea class="form-control" id="reason" name="reason" rows="5" 
                                  placeholder="Please provide a clear and detailed reason for rejecting this payout request. This message will be sent to the organizer." 
                                  required></textarea>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Be specific about why the payout is being rejected. This helps the organizer understand what needs to be corrected.
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6>Common Rejection Reasons:</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setReason('Insufficient documentation provided')">
                                Insufficient Documentation
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setReason('Invalid payout method details')">
                                Invalid Method Details
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setReason('Account verification required')">
                                Account Verification Required
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setReason('Suspicious activity detected')">
                                Suspicious Activity
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setReason('Minimum payout threshold not met')">
                                Minimum Threshold
                            </button>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            What happens after rejection?
                        </h6>
                        <ul class="mb-0">
                            <li>The payout status will be changed to "Rejected"</li>
                            <li>The funds (GH₵<?= number_format($payout['amount'], 2) ?>) will be returned to the organizer's available balance</li>
                            <li>The organizer will be notified of the rejection with your reason</li>
                            <li>The organizer can submit a new payout request after addressing the issues</li>
                        </ul>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= SUPERADMIN_URL ?>/payouts" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times me-1"></i>Reject Payout
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function setReason(reasonText) {
    const textarea = document.getElementById('reason');
    textarea.value = reasonText;
    textarea.focus();
}

// Confirm before submission
document.querySelector('form').addEventListener('submit', function(e) {
    const reason = document.getElementById('reason').value.trim();
    
    if (!reason) {
        e.preventDefault();
        alert('Please provide a reason for rejection.');
        return;
    }
    
    if (!confirm('Are you sure you want to reject this payout request? This action cannot be undone.')) {
        e.preventDefault();
    }
});
</script>

<style>
.card-header.bg-danger {
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-outline-secondary.btn-sm {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.alert-warning {
    border-left: 4px solid #ffc107;
}

.alert-info {
    border-left: 4px solid #0dcaf0;
}

.table-borderless td {
    padding: 0.5rem 0.75rem;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layout/superadmin_layout.php';
?>
