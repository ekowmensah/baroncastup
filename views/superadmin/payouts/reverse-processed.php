<?php 
$content = ob_start();
if (!isset($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$csrf_field = '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
?>

<!-- Reverse Processed Payout Form -->
<div class="row">
    <div class="col-lg-8 mx-auto">
        <!-- Payout Details -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0">
                    <i class="fas fa-undo me-2"></i>
                    Reverse Processed Payout
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> You are about to reverse a processed payout back to approved status. The funds will be moved from "Total Paid" back to "Pending" status.
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
                                <td class="fw-bold">Processed Date:</td>
                                <td><?= $payout['processed_at'] ? date('F j, Y g:i A', strtotime($payout['processed_at'])) : 'Not available' ?></td>
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
                                    <span class="badge bg-success">
                                        Paid/Processed
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reversal Form -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-edit me-2"></i>
                    Reversal Details
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= SUPERADMIN_URL ?>/payouts/reverse-processed/<?= $payout['id'] ?>">
                    <?= $csrf_field ?>
                    <div class="mb-4">
                        <label for="reason" class="form-label">
                            <strong>Reason for Reversal <span class="text-danger">*</span></strong>
                        </label>
                        <textarea class="form-control" id="reason" name="reason" rows="5" 
                                  placeholder="Please provide a clear and detailed reason for reversing this processed payout. This action will move the status back to 'approved' and funds back to pending." 
                                  required></textarea>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Be specific about why the payout needs to be reversed. This helps maintain audit trail.
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6>Common Reversal Reasons:</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setReason('Payment processing error detected')">
                                Processing Error
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setReason('Incorrect payout amount processed')">
                                Incorrect Amount
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setReason('Wrong payout method used')">
                                Wrong Method
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setReason('Organizer requested reversal')">
                                Organizer Request
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setReason('Administrative correction required')">
                                Admin Correction
                            </button>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            What happens after reversal?
                        </h6>
                        <ul class="mb-0">
                            <li>The payout status will be changed from "Paid" to "Approved"</li>
                            <li>The funds (GH₵<?= number_format($payout['amount'], 2) ?>) will be moved from "Total Paid" back to "Pending"</li>
                            <li>The payout can then be re-processed or further modified</li>
                            <li>All processing details (date, reference) will be cleared</li>
                            <li>A complete audit trail will be maintained</li>
                        </ul>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= SUPERADMIN_URL ?>/payouts" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-undo me-1"></i>Reverse to Approved
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
        alert('Please provide a reason for reversal.');
        return;
    }
    
    if (!confirm('Are you sure you want to reverse this processed payout back to approved status? This action cannot be undone.')) {
        e.preventDefault();
    }
});
</script>

<style>
.card-header.bg-warning {
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
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
