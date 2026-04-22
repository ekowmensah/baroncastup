<?php 
$content = ob_start();
if (!isset($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$csrf_token_value = htmlspecialchars($_SESSION['csrf_token']);
?>

<!-- Payout Details View -->
<div class="row">
    <div class="col-lg-8">
        <!-- Payout Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Payout Information
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
                                <td class="fw-bold">Amount:</td>
                                <td class="h5 text-primary">GH₵<?= number_format($payout['amount'], 2) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Processing Fee:</td>
                                <td>GH₵<?= number_format($payout['processing_fee'] ?? 0, 2) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Net Amount:</td>
                                <td class="h6 text-success">GH₵<?= number_format($payout['net_amount'] ?? $payout['amount'], 2) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Status:</td>
                                <td>
                                    <?php
                                    $statusConfig = [
                                        'pending' => ['class' => 'warning', 'text' => 'Pending Approval'],
                                        'approved' => ['class' => 'info', 'text' => 'Approved'],
                                        'processing' => ['class' => 'primary', 'text' => 'Processing'],
                                        'paid' => ['class' => 'success', 'text' => 'Paid'],
                                        'rejected' => ['class' => 'danger', 'text' => 'Rejected'],
                                        'cancelled' => ['class' => 'secondary', 'text' => 'Cancelled']
                                    ];
                                    $config = $statusConfig[$payout['status']] ?? ['class' => 'secondary', 'text' => 'Unknown'];
                                    ?>
                                    <span class="badge bg-<?= $config['class'] ?> px-3 py-2">
                                        <?= $config['text'] ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Payout Method:</td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <?= ucfirst(str_replace('_', ' ', $payout['payout_method'] ?? 'N/A')) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Requested Date:</td>
                                <td><?= $payout['created_at'] ? date('F j, Y g:i A', strtotime($payout['created_at'])) : 'Not available' ?></td>
                            </tr>
                            <?php if ($payout['approved_at']): ?>
                            <tr>
                                <td class="fw-bold">Approved Date:</td>
                                <td><?= date('F j, Y g:i A', strtotime($payout['approved_at'])) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($payout['processed_at']): ?>
                            <tr>
                                <td class="fw-bold">Processed Date:</td>
                                <td><?= date('F j, Y g:i A', strtotime($payout['processed_at'])) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($payout['provider_reference']): ?>
                            <tr>
                                <td class="fw-bold">Reference:</td>
                                <td><code><?= htmlspecialchars($payout['provider_reference']) ?></code></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-4">
                    <div class="d-flex gap-2 flex-wrap">
                        <?php if ($payout['status'] === 'pending'): ?>
                            <a href="<?= SUPERADMIN_URL ?>/payouts/approve/<?= $payout['id'] ?>" class="btn btn-success">
                                <i class="fas fa-check me-1"></i>Review for Approval
                            </a>
                            <a href="<?= SUPERADMIN_URL ?>/payouts/reject/<?= $payout['id'] ?>" class="btn btn-danger">
                                <i class="fas fa-times me-1"></i>Reject
                            </a>
                        <?php elseif ($payout['status'] === 'approved'): ?>
                            <button type="button" class="btn btn-primary" onclick="processPayout(<?= $payout['id'] ?>)">
                                <i class="fas fa-play me-1"></i>Process Now
                            </button>
                            <a href="<?= SUPERADMIN_URL ?>/payouts/reverse-approved/<?= $payout['id'] ?>" class="btn btn-warning">
                                <i class="fas fa-undo me-1"></i>Reverse to Pending
                            </a>
                        <?php elseif ($payout['status'] === 'paid'): ?>
                            <button type="button" class="btn btn-success" onclick="downloadPayoutReceipt(<?= $payout['id'] ?>)">
                                <i class="fas fa-receipt me-1"></i>Download Receipt
                            </button>
                            <a href="<?= SUPERADMIN_URL ?>/payouts/reverse-processed/<?= $payout['id'] ?>" class="btn btn-warning">
                                <i class="fas fa-undo me-1"></i>Reverse to Approved
                            </a>
                        <?php endif; ?>
                        
                        <!-- Recalculate Fees Button (show if fees are 0 or for pending/approved payouts) -->
                        <?php if (($payout['processing_fee'] == 0 && $payout['amount'] > 0) || in_array($payout['status'], ['pending', 'approved'])): ?>
                            <form method="POST" action="<?= SUPERADMIN_URL ?>/payouts/recalculate-fees/<?= $payout['id'] ?>" style="display: inline;">
                                <button type="submit" class="btn btn-info" onclick="return confirm('Recalculate processing fees based on payout method?')">
                                    <i class="fas fa-calculator me-1"></i>Recalculate Fees
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <a href="<?= SUPERADMIN_URL ?>/payouts" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Payouts
                        </a>
                    </div>
                </div>
            </div>
        </div>
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
            </div>
        </div>

        <!-- Balance Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-wallet me-2"></i>
                    Current Balance
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h6 text-success">GH₵<?= number_format($balance['available'] ?? 0, 2) ?></div>
                        <small class="text-muted">Available</small>
                    </div>
                    <div class="col-6">
                        <div class="h6 text-warning">GH₵<?= number_format($balance['pending'] ?? 0, 2) ?></div>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h6 text-info">GH₵<?= number_format($balance['total_earned'] ?? 0, 2) ?></div>
                        <small class="text-muted">Total Earned</small>
                    </div>
                    <div class="col-6">
                        <div class="h6 text-primary">GH₵<?= number_format($balance['total_paid'] ?? 0, 2) ?></div>
                        <small class="text-muted">Total Paid</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Method Details -->
        <?php if ($payout_method): ?>
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-credit-card me-2"></i>
                    Payout Method Details
                </h6>
            </div>
            <div class="card-body">
                <div class="fw-bold"><?= htmlspecialchars($payout_method['method_name'] ?? 'Unknown Method') ?></div>
                <div class="text-muted small mb-2"><?= ucfirst(str_replace('_', ' ', $payout_method['method_type'] ?? 'N/A')) ?></div>
                
                <?php if (isset($payout_method['is_verified']) && $payout_method['is_verified']): ?>
                    <span class="badge bg-success">
                        <i class="fas fa-check-circle me-1"></i>Verified
                    </span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark">
                        <i class="fas fa-exclamation-triangle me-1"></i>Not Verified
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function processPayout(payoutId) {
    if (confirm('Are you sure you want to process this payout? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= SUPERADMIN_URL ?>/payouts/process/' + payoutId;
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_csrf_token';
        csrfInput.value = '<?= $csrf_token_value ?>';
        form.appendChild(csrfInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function downloadPayoutReceipt(payoutId) {
    window.open('<?= SUPERADMIN_URL ?>/payouts/' + payoutId + '/receipt', '_blank');
}
</script>

<style>
.table-borderless td {
    padding: 0.5rem 0.75rem;
}

.badge {
    font-size: 0.875em;
}

.btn-group .btn {
    margin-right: 0.5rem;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layout/superadmin_layout.php';
?>
