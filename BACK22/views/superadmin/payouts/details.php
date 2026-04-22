<?php 
$content = ob_start();
if (!isset($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$csrf_token_value = htmlspecialchars($_SESSION['csrf_token']);

$statusConfig = [
    'pending'    => ['class' => 'warning',   'icon' => 'clock',            'text' => 'Pending Approval'],
    'approved'   => ['class' => 'info',      'icon' => 'check-circle',     'text' => 'Approved'],
    'processing' => ['class' => 'primary',   'icon' => 'spinner fa-spin',  'text' => 'Processing'],
    'paid'       => ['class' => 'success',   'icon' => 'check-double',     'text' => 'Paid'],
    'failed'     => ['class' => 'danger',    'icon' => 'times-circle',     'text' => 'Failed'],
    'rejected'   => ['class' => 'danger',    'icon' => 'ban',              'text' => 'Rejected'],
    'cancelled'  => ['class' => 'secondary', 'icon' => 'minus-circle',     'text' => 'Cancelled'],
];
$sc = $statusConfig[$payout['status']] ?? ['class' => 'secondary', 'icon' => 'question-circle', 'text' => ucfirst($payout['status'])];

$recipientDetails = json_decode($payout['recipient_details'] ?? '{}', true) ?: [];
$methodAccountDetails = json_decode($payout_method['account_details'] ?? '{}', true) ?: [];
?>

<!-- Status Banner -->
<div class="alert alert-<?= $sc['class'] ?> d-flex align-items-center justify-content-between mb-4" role="alert">
    <div>
        <i class="fas fa-<?= $sc['icon'] ?> me-2 fa-lg"></i>
        <strong>Payout <?= $sc['text'] ?></strong>
        <span class="ms-2 opacity-75">|</span>
        <code class="ms-2"><?= htmlspecialchars($payout['payout_id']) ?></code>
    </div>
    <a href="<?= SUPERADMIN_URL ?>/payouts" class="btn btn-sm btn-outline-dark">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<?php if (!empty($payout['failure_reason'])): ?>
<div class="alert alert-warning mb-4">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Note:</strong> <?= htmlspecialchars($payout['failure_reason']) ?>
</div>
<?php endif; ?>

<div class="row">
    <!-- LEFT COLUMN -->
    <div class="col-lg-8">

        <!-- Amount Summary -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="text-muted small text-uppercase mb-1">Amount Requested</div>
                        <div class="h3 mb-0 text-primary">GH₵<?= number_format($payout['amount'], 2) ?></div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small text-uppercase mb-1">Processing Fee</div>
                        <div class="h3 mb-0 text-danger">- GH₵<?= number_format($payout['processing_fee'] ?? 0, 2) ?></div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small text-uppercase mb-1">Net Payout</div>
                        <div class="h3 mb-0 text-success">GH₵<?= number_format($payout['net_amount'] ?? $payout['amount'], 2) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payout Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2 text-primary"></i>Payout Details
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <tbody>
                        <tr>
                            <td class="fw-bold text-muted" style="width:35%">Payout ID</td>
                            <td><code><?= htmlspecialchars($payout['payout_id']) ?></code></td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Type</td>
                            <td><?= ucfirst($payout['payout_type'] ?? 'manual') ?></td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Method</td>
                            <td>
                                <i class="fas fa-<?= ($payout['payout_method'] === 'mobile_money') ? 'mobile-alt' : (($payout['payout_method'] === 'bank_transfer') ? 'university' : 'globe') ?> me-1"></i>
                                <?= ucfirst(str_replace('_', ' ', $payout['payout_method'] ?? 'N/A')) ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Requested</td>
                            <td>
                                <?= $payout['created_at'] ? date('F j, Y \a\t g:i A', strtotime($payout['created_at'])) : 'N/A' ?>
                                <?php if ($payout['created_at']): ?>
                                    <small class="text-muted ms-1">(<?= self_time_ago($payout['created_at']) ?>)</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if (!empty($payout['approved_at'])): ?>
                        <tr>
                            <td class="fw-bold text-muted">Approved</td>
                            <td>
                                <i class="fas fa-check text-success me-1"></i>
                                <?= date('F j, Y \a\t g:i A', strtotime($payout['approved_at'])) ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($payout['processed_at'])): ?>
                        <tr>
                            <td class="fw-bold text-muted">Processed</td>
                            <td>
                                <i class="fas fa-cog text-primary me-1"></i>
                                <?= date('F j, Y \a\t g:i A', strtotime($payout['processed_at'])) ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($payout['provider_reference'])): ?>
                        <tr>
                            <td class="fw-bold text-muted">Provider Reference</td>
                            <td><code><?= htmlspecialchars($payout['provider_reference']) ?></code></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recipient Details -->
        <?php if (!empty($recipientDetails)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-user-check me-2 text-success"></i>Recipient Details
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <tbody>
                        <?php if (!empty($recipientDetails['account_name'])): ?>
                        <tr>
                            <td class="fw-bold text-muted" style="width:35%">Account Name</td>
                            <td><?= htmlspecialchars($recipientDetails['account_name']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($recipientDetails['phone_number'])): ?>
                        <tr>
                            <td class="fw-bold text-muted">Phone Number</td>
                            <td>
                                <i class="fas fa-phone me-1 text-muted"></i>
                                <?= htmlspecialchars($recipientDetails['phone_number']) ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($recipientDetails['provider'])): ?>
                        <tr>
                            <td class="fw-bold text-muted">Provider / Network</td>
                            <td><?= htmlspecialchars($recipientDetails['provider']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($recipientDetails['bank_name'])): ?>
                        <tr>
                            <td class="fw-bold text-muted">Bank</td>
                            <td><?= htmlspecialchars($recipientDetails['bank_name']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($recipientDetails['account_number'])): ?>
                        <tr>
                            <td class="fw-bold text-muted">Account Number</td>
                            <td><code><?= htmlspecialchars($recipientDetails['account_number']) ?></code></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($recipientDetails['branch'])): ?>
                        <tr>
                            <td class="fw-bold text-muted">Branch</td>
                            <td><?= htmlspecialchars($recipientDetails['branch']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php
                        $shown = ['account_name','phone_number','provider','bank_name','account_number','branch'];
                        $extra = array_diff_key($recipientDetails, array_flip($shown));
                        foreach ($extra as $key => $val):
                            if (is_string($val) || is_numeric($val)):
                        ?>
                        <tr>
                            <td class="fw-bold text-muted"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) ?></td>
                            <td><?= htmlspecialchars($val) ?></td>
                        </tr>
                        <?php endif; endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="card mb-4">
            <div class="card-body">
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
                        <a href="<?= SUPERADMIN_URL ?>/payouts/reverse-approved/<?= $payout['id'] ?>" class="btn btn-outline-warning">
                            <i class="fas fa-undo me-1"></i>Reverse to Pending
                        </a>
                    <?php elseif ($payout['status'] === 'paid'): ?>
                        <button type="button" class="btn btn-success" onclick="downloadPayoutReceipt(<?= $payout['id'] ?>)">
                            <i class="fas fa-receipt me-1"></i>Download Receipt
                        </button>
                        <a href="<?= SUPERADMIN_URL ?>/payouts/reverse-processed/<?= $payout['id'] ?>" class="btn btn-outline-warning">
                            <i class="fas fa-undo me-1"></i>Reverse to Approved
                        </a>
                    <?php endif; ?>

                    <?php if (in_array($payout['status'], ['pending', 'approved'])): ?>
                        <form method="POST" action="<?= SUPERADMIN_URL ?>/payouts/recalculate-fees/<?= $payout['id'] ?>" class="d-inline">
                            <input type="hidden" name="_csrf_token" value="<?= $csrf_token_value ?>">
                            <button type="submit" class="btn btn-outline-info" onclick="return confirm('Recalculate processing fees?')">
                                <i class="fas fa-calculator me-1"></i>Recalculate Fees
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="col-lg-4">

        <!-- Organizer Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-building me-2 text-primary"></i>Organizer
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px">
                        <i class="fas fa-user text-primary"></i>
                    </div>
                    <div>
                        <div class="fw-bold"><?= htmlspecialchars($tenant['name'] ?? 'Unknown') ?></div>
                        <small class="text-muted"><?= htmlspecialchars($tenant['email'] ?? '') ?></small>
                    </div>
                </div>
                <table class="table table-sm table-borderless mb-0 small">
                    <?php if (!empty($tenant['phone'])): ?>
                    <tr>
                        <td class="text-muted"><i class="fas fa-phone me-1"></i> Phone</td>
                        <td class="text-end"><?= htmlspecialchars($tenant['phone']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="text-muted"><i class="fas fa-tag me-1"></i> Plan</td>
                        <td class="text-end"><span class="badge bg-info"><?= htmlspecialchars($tenant['plan_name'] ?? ucfirst($tenant['plan'] ?? 'Free')) ?></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-shield-alt me-1"></i> Verification</td>
                        <td class="text-end">
                            <?php if (!empty($tenant['verified'])): ?>
                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>Verified</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Unverified</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-calendar me-1"></i> Joined</td>
                        <td class="text-end"><?= !empty($tenant['created_at']) ? date('M j, Y', strtotime($tenant['created_at'])) : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-calendar-alt me-1"></i> Events</td>
                        <td class="text-end"><?= $tenant_stats['total_events'] ?? 0 ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-coins me-1"></i> Revenue</td>
                        <td class="text-end">GH₵<?= number_format($tenant_stats['total_revenue'] ?? 0, 2) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-money-check me-1"></i> Past Payouts</td>
                        <td class="text-end"><?= $tenant_stats['previous_payouts'] ?? 0 ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Balance Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-wallet me-2 text-success"></i>Organizer Balance
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="ps-3"><i class="fas fa-circle text-success me-1" style="font-size:0.5em;vertical-align:middle"></i> Available</td>
                            <td class="text-end pe-3 fw-bold text-success">GH₵<?= number_format($balance['available'] ?? 0, 2) ?></td>
                        </tr>
                        <tr>
                            <td class="ps-3"><i class="fas fa-circle text-warning me-1" style="font-size:0.5em;vertical-align:middle"></i> Pending Approval</td>
                            <td class="text-end pe-3">GH₵<?= number_format($balance['pending_approval'] ?? $balance['pending'] ?? 0, 2) ?></td>
                        </tr>
                        <tr>
                            <td class="ps-3"><i class="fas fa-circle text-info me-1" style="font-size:0.5em;vertical-align:middle"></i> Approved</td>
                            <td class="text-end pe-3">GH₵<?= number_format($balance['approved_pending'] ?? 0, 2) ?></td>
                        </tr>
                        <tr>
                            <td class="ps-3"><i class="fas fa-circle text-primary me-1" style="font-size:0.5em;vertical-align:middle"></i> Processing</td>
                            <td class="text-end pe-3">GH₵<?= number_format($balance['processing'] ?? 0, 2) ?></td>
                        </tr>
                        <tr class="table-light">
                            <td class="ps-3 fw-bold">Total Earned</td>
                            <td class="text-end pe-3 fw-bold">GH₵<?= number_format($balance['total_earned'] ?? 0, 2) ?></td>
                        </tr>
                        <tr class="table-light">
                            <td class="ps-3 fw-bold">Total Paid Out</td>
                            <td class="text-end pe-3 fw-bold text-primary">GH₵<?= number_format($balance['total_paid'] ?? 0, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payout Method Card -->
        <?php if ($payout_method): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-credit-card me-2 text-info"></i>Payout Method
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="fw-bold"><?= htmlspecialchars($payout_method['method_name'] ?? 'Unknown') ?></div>
                        <small class="text-muted"><?= ucfirst(str_replace('_', ' ', $payout_method['method_type'] ?? 'N/A')) ?></small>
                    </div>
                    <?php if (!empty($payout_method['is_verified'])): ?>
                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Verified</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>Unverified</span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($methodAccountDetails)): ?>
                <table class="table table-sm table-borderless mb-0 small">
                    <?php foreach ($methodAccountDetails as $key => $val): ?>
                        <?php if (is_string($val) || is_numeric($val)): ?>
                        <tr>
                            <td class="text-muted"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) ?></td>
                            <td class="text-end"><?= htmlspecialchars($val) ?></td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </table>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
function self_time_ago($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' min ago';
    return 'just now';
}
?>

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
.table td { vertical-align: middle; }
.alert code { background: rgba(0,0,0,0.05); padding: 2px 6px; border-radius: 3px; }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layout/superadmin_layout.php';
?>
