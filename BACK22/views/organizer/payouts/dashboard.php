<?php
$content = ob_start();
if (!isset($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$csrf_token_value = htmlspecialchars($_SESSION['csrf_token']);

$available = (float)($balance['available'] ?? 0);
$pendingApproval = (float)($balance['pending_approval'] ?? 0);
$approvedPending = (float)($balance['approved_pending'] ?? 0);
$processing = (float)($balance['processing'] ?? 0);
$totalPending = $pendingApproval + $approvedPending + $processing;
$totalEarned = (float)($balance['total_earned'] ?? 0);
$totalPaid = (float)($balance['total_paid'] ?? 0);
$minimumAmount = (float)($schedule['minimum_amount'] ?? 10);
$canRequest = !empty($can_request_payout) && $available >= $minimumAmount;
$hasMethods = !empty($payout_methods);

$statusMap = [
    'pending' => ['class' => 'warning', 'label' => 'Pending Review'],
    'approved' => ['class' => 'info', 'label' => 'Approved'],
    'processing' => ['class' => 'primary', 'label' => 'Processing'],
    'paid' => ['class' => 'success', 'label' => 'Paid'],
    'failed' => ['class' => 'danger', 'label' => 'Failed'],
    'rejected' => ['class' => 'danger', 'label' => 'Rejected'],
    'cancelled' => ['class' => 'secondary', 'label' => 'Cancelled']
];
?>

<style>
:root {
    --payout-bg: #f4f7fb;
    --payout-ink: #112031;
    --payout-muted: #5b6b7a;
    --payout-card: #ffffff;
    --payout-border: #d9e2ec;
    --payout-primary: #0f766e;
    --payout-primary-soft: #d9f5f2;
    --payout-accent: #f59e0b;
}

.payout-shell {
    background: radial-gradient(circle at 10% 10%, #e8f8f7 0%, transparent 40%),
                radial-gradient(circle at 90% 20%, #fff2d9 0%, transparent 35%),
                var(--payout-bg);
    min-height: calc(100vh - 80px);
    padding: 1.25rem;
}

.payout-wrap {
    max-width: 1200px;
    margin: 0 auto;
}

.payout-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.payout-title {
    margin: 0;
    color: var(--payout-ink);
    font-weight: 700;
    letter-spacing: 0.2px;
}

.payout-subtitle {
    color: var(--payout-muted);
    margin: 0.25rem 0 0;
}

.payout-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-payout-primary {
    background: var(--payout-primary);
    border-color: var(--payout-primary);
    color: #fff;
}

.btn-payout-primary:hover {
    background: #0c5f58;
    border-color: #0c5f58;
    color: #fff;
}

.btn-payout-soft {
    background: #fff;
    border: 1px solid var(--payout-border);
    color: var(--payout-ink);
}

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.kpi-card {
    background: var(--payout-card);
    border: 1px solid var(--payout-border);
    border-radius: 14px;
    padding: 1rem;
}

.kpi-label {
    color: var(--payout-muted);
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.kpi-value {
    color: var(--payout-ink);
    font-size: 1.35rem;
    font-weight: 700;
    margin-top: 0.2rem;
}

.kpi-note {
    font-size: 0.82rem;
    color: var(--payout-muted);
    margin-top: 0.25rem;
}

.panel {
    background: var(--payout-card);
    border: 1px solid var(--payout-border);
    border-radius: 14px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.panel-title {
    margin: 0;
    color: var(--payout-ink);
    font-weight: 600;
}

.breakdown {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.breakdown .badge {
    font-size: 0.82rem;
    padding: 0.5rem 0.65rem;
}

.meta-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1rem;
}

.meta-list {
    margin: 0;
    padding: 0;
    list-style: none;
}

.meta-list li {
    display: flex;
    justify-content: space-between;
    border-bottom: 1px dashed #e5ebf0;
    padding: 0.45rem 0;
    gap: 0.75rem;
}

.meta-list li:last-child {
    border-bottom: 0;
}

.method-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: var(--payout-primary-soft);
    color: #0c5f58;
    border: 1px solid #b8ece6;
    border-radius: 999px;
    padding: 0.25rem 0.55rem;
    font-size: 0.8rem;
    margin: 0.2rem 0.2rem 0 0;
}

.table thead th {
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--payout-muted);
    border-bottom-width: 1px;
}

.table td {
    vertical-align: middle;
}

.amount-main {
    font-weight: 700;
    color: var(--payout-ink);
}

.amount-sub {
    font-size: 0.82rem;
    color: var(--payout-muted);
}

@media (max-width: 991px) {
    .kpi-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .meta-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .kpi-grid {
        grid-template-columns: 1fr;
    }

    .payout-shell {
        padding: 0.75rem;
    }
}
</style>

<div class="payout-shell">
    <div class="payout-wrap">
        <div class="payout-topbar">
            <div>
                <h1 class="payout-title">Payouts</h1>
                <p class="payout-subtitle">Track balances, payout status, and actions in one place.</p>
            </div>
            <div class="payout-actions">
                <?php if ($canRequest && $hasMethods): ?>
                    <a href="<?= ORGANIZER_URL ?>/payouts/request" class="btn btn-sm btn-payout-primary">
                        <i class="fas fa-money-bill-wave me-1"></i> Request Payout
                    </a>
                <?php endif; ?>
                <a href="<?= ORGANIZER_URL ?>/payouts/methods" class="btn btn-sm btn-payout-soft">
                    <i class="fas fa-credit-card me-1"></i> Methods
                </a>
                <a href="<?= ORGANIZER_URL ?>/payouts/settings" class="btn btn-sm btn-payout-soft">
                    <i class="fas fa-sliders-h me-1"></i> Settings
                </a>
                <a href="<?= ORGANIZER_URL ?>/payouts/history" class="btn btn-sm btn-payout-soft">
                    <i class="fas fa-history me-1"></i> History
                </a>
            </div>
        </div>

        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-label">Available</div>
                <div class="kpi-value">GHS <?= number_format($available, 2) ?></div>
                <div class="kpi-note">Ready to request</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Pending</div>
                <div class="kpi-value">GHS <?= number_format($totalPending, 2) ?></div>
                <div class="kpi-note">In review or processing</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Total Earned</div>
                <div class="kpi-value">GHS <?= number_format($totalEarned, 2) ?></div>
                <div class="kpi-note">Lifetime earnings</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Total Paid</div>
                <div class="kpi-value">GHS <?= number_format($totalPaid, 2) ?></div>
                <div class="kpi-note">Successfully disbursed</div>
            </div>
        </div>

        <div class="meta-grid">
            <div class="panel">
                <div class="panel-head">
                    <h2 class="panel-title">Pending Breakdown</h2>
                </div>
                <div class="breakdown">
                    <span class="badge text-bg-warning">Awaiting approval: GHS <?= number_format($pendingApproval, 2) ?></span>
                    <span class="badge text-bg-info">Approved queue: GHS <?= number_format($approvedPending, 2) ?></span>
                    <span class="badge text-bg-primary">Processing: GHS <?= number_format($processing, 2) ?></span>
                </div>
                <?php if (!$canRequest): ?>
                    <div class="alert alert-warning mt-3 mb-0">
                        You currently cannot request payout.
                        <?php if (!$hasMethods): ?> Add at least one payout method first.<?php else: ?> Minimum required is GHS <?= number_format($minimumAmount, 2) ?>.<?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <h2 class="panel-title">Configuration</h2>
                </div>
                <ul class="meta-list">
                    <li><span>Minimum payout</span><strong>GHS <?= number_format($minimumAmount, 2) ?></strong></li>
                    <li><span>Frequency</span><strong><?= ucfirst($schedule['frequency'] ?? 'manual') ?></strong></li>
                    <li><span>Auto payout</span><strong><?= !empty($schedule['auto_payout_enabled']) ? 'Enabled' : 'Disabled' ?></strong></li>
                    <li><span>Active methods</span><strong><?= is_array($payout_methods) ? count($payout_methods) : 0 ?></strong></li>
                </ul>
                <?php if (!empty($payout_methods)): ?>
                    <div class="mt-2">
                        <?php foreach ($payout_methods as $method): ?>
                            <span class="method-pill">
                                <i class="fas fa-wallet"></i>
                                <?= htmlspecialchars($method['method_name'] ?? ucfirst(str_replace('_', ' ', $method['method_type'] ?? 'Method'))) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h2 class="panel-title">Recent Payouts</h2>
            </div>

            <?php if (empty($recent_payouts)): ?>
                <div class="text-center py-4">
                    <div class="text-muted mb-2">No payout requests yet.</div>
                    <?php if ($canRequest && $hasMethods): ?>
                        <a href="<?= ORGANIZER_URL ?>/payouts/request" class="btn btn-sm btn-payout-primary">Create your first request</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Amount</th>
                                <th>Net</th>
                                <th>Status</th>
                                <th>Method</th>
                                <th>Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_payouts as $payout): ?>
                                <?php
                                    $status = $payout['status'] ?? 'unknown';
                                    $statusCfg = $statusMap[$status] ?? ['class' => 'secondary', 'label' => ucfirst($status)];
                                    $amount = (float)($payout['amount'] ?? 0);
                                    $fee = (float)($payout['processing_fee'] ?? 0);
                                    $net = isset($payout['net_amount']) ? (float)$payout['net_amount'] : ($amount - $fee);
                                ?>
                                <tr>
                                    <td>
                                        <div class="amount-main">GHS <?= number_format($amount, 2) ?></div>
                                        <?php if ($fee > 0): ?><div class="amount-sub">Fee: GHS <?= number_format($fee, 2) ?></div><?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="amount-main">GHS <?= number_format($net, 2) ?></div>
                                        <div class="amount-sub">Expected payout</div>
                                    </td>
                                    <td><span class="badge text-bg-<?= $statusCfg['class'] ?>"><?= $statusCfg['label'] ?></span></td>
                                    <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $payout['payout_method'] ?? 'N/A'))) ?></td>
                                    <td>
                                        <?php if (!empty($payout['created_at'])): ?>
                                            <div><?= date('M j, Y', strtotime($payout['created_at'])) ?></div>
                                            <div class="amount-sub"><?= date('g:i A', strtotime($payout['created_at'])) ?></div>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewPayoutDetails(<?= (int)$payout['id'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if (in_array(($payout['status'] ?? ''), ['paid', 'success'])): ?>
                                            <button class="btn btn-sm btn-outline-success" onclick="openReceiptPreview(<?= (int)$payout['id'] ?>)">
                                                <i class="fas fa-receipt"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (($payout['status'] ?? '') === 'pending'): ?>
                                            <button class="btn btn-sm btn-outline-danger" onclick="openCancelPayoutModal(<?= (int)$payout['id'] ?>)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
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

<div class="modal fade" id="payoutDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-receipt me-2"></i>Payout Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="payoutDetailsContent">
                <div class="text-center py-4"><div class="spinner-border" role="status"></div></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelPayoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-ban me-2"></i>Cancel Payout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Cancel this pending payout request?</p>
                <p class="text-muted mb-0">The amount will move back to your available balance.</p>
            </div>
            <div class="modal-footer">
                <form method="POST" id="cancelPayoutForm" action="">
                    <input type="hidden" name="_csrf_token" value="<?= $csrf_token_value ?>">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Keep Request</button>
                    <button type="submit" class="btn btn-danger">Cancel Payout</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="receiptPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-invoice me-2"></i>Payout Receipt Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="receiptPreviewContent">
                <div class="text-center py-4"><div class="spinner-border" role="status"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="downloadReceiptButton">
                    <i class="fas fa-download me-1"></i>Download Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openCancelPayoutModal(payoutId) {
    const form = document.getElementById('cancelPayoutForm');
    form.action = '<?= ORGANIZER_URL ?>/payouts/' + payoutId + '/cancel';
    const modal = new bootstrap.Modal(document.getElementById('cancelPayoutModal'));
    modal.show();
}

function formatDate(dateString) {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return 'N/A';
    return date.toLocaleString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
}

function ucFirst(str) {
    if (!str || typeof str !== 'string') return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function safeMoney(value) {
    const num = Number(value || 0);
    return Number.isFinite(num) ? num.toFixed(2) : '0.00';
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function getMethodRows(payout, details) {
    const rows = [];
    if (!details || typeof details !== 'object') {
        return '<tr><td>Details</td><td>Payment method configured</td></tr>';
    }

    if (payout.payout_method === 'bank_transfer') {
        rows.push('<tr><td>Method</td><td>Bank Transfer</td></tr>');
        rows.push('<tr><td>Account</td><td>' + (details.account_number ? ('****' + String(details.account_number).slice(-4)) : 'N/A') + '</td></tr>');
        rows.push('<tr><td>Bank</td><td>' + escapeHtml(details.bank_name || 'N/A') + '</td></tr>');
        rows.push('<tr><td>Account Name</td><td>' + escapeHtml(details.account_name || 'N/A') + '</td></tr>');
        return rows.join('');
    }

    if (payout.payout_method === 'mobile_money') {
        rows.push('<tr><td>Method</td><td>Mobile Money</td></tr>');
        rows.push('<tr><td>Phone</td><td>' + (details.phone_number ? ('****' + String(details.phone_number).slice(-4)) : 'N/A') + '</td></tr>');
        rows.push('<tr><td>Provider</td><td>' + escapeHtml(details.provider || 'N/A') + '</td></tr>');
        rows.push('<tr><td>Account Name</td><td>' + escapeHtml(details.account_name || 'N/A') + '</td></tr>');
        return rows.join('');
    }

    if (payout.payout_method === 'paypal') {
        rows.push('<tr><td>Method</td><td>PayPal</td></tr>');
        rows.push('<tr><td>Email</td><td>' + escapeHtml(details.email || 'N/A') + '</td></tr>');
        return rows.join('');
    }

    if (payout.payout_method === 'stripe') {
        rows.push('<tr><td>Method</td><td>Stripe</td></tr>');
        rows.push('<tr><td>Account ID</td><td>' + (details.account_id ? ('****' + String(details.account_id).slice(-4)) : 'N/A') + '</td></tr>');
        return rows.join('');
    }

    rows.push('<tr><td>Method</td><td>' + escapeHtml(ucFirst(String(payout.payout_method || 'N/A').replace('_', ' ')) || 'N/A') + '</td></tr>');
    rows.push('<tr><td>Details</td><td>Payment method configured</td></tr>');
    return rows.join('');
}

function openReceiptPreview(payoutId) {
    const modal = new bootstrap.Modal(document.getElementById('receiptPreviewModal'));
    const content = document.getElementById('receiptPreviewContent');
    const downloadButton = document.getElementById('downloadReceiptButton');

    content.innerHTML = '<div class="text-center py-4"><div class="spinner-border" role="status"></div></div>';
    downloadButton.onclick = function () {
        window.open('<?= ORGANIZER_URL ?>/payouts/' + payoutId + '/receipt', '_blank');
    };

    modal.show();

    fetch('<?= ORGANIZER_URL ?>/payouts/details/' + payoutId, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then((res) => res.json())
    .then((data) => {
        if (!data.success || !data.payout) {
            content.innerHTML = '<div class="alert alert-danger">Unable to load receipt preview.</div>';
            return;
        }

        const p = data.payout;
        let details = {};
        try {
            details = p.recipient_details ? JSON.parse(p.recipient_details) : {};
        } catch (e) {
            details = {};
        }

        content.innerHTML = '' +
            '<div class="border rounded p-3">' +
                '<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">' +
                    '<div>' +
                        '<h5 class="mb-1">BaronCast Payout Receipt</h5>' +
                        '<div class="text-muted small">Receipt ID: <code>' + escapeHtml(p.payout_id || 'N/A') + '</code></div>' +
                    '</div>' +
                    '<span class="badge text-bg-success">Paid</span>' +
                '</div>' +
                '<div class="row g-3">' +
                    '<div class="col-md-6">' +
                        '<table class="table table-sm mb-0">' +
                            '<tr><td>Amount</td><td><strong>GHS ' + safeMoney(p.amount) + '</strong></td></tr>' +
                            '<tr><td>Processing Fee</td><td>GHS ' + safeMoney(p.processing_fee) + '</td></tr>' +
                            '<tr><td>Net Amount</td><td><strong>GHS ' + safeMoney(p.net_amount || (Number(p.amount || 0) - Number(p.processing_fee || 0))) + '</strong></td></tr>' +
                            '<tr><td>Requested</td><td>' + formatDate(p.created_at) + '</td></tr>' +
                            '<tr><td>Processed</td><td>' + formatDate(p.processed_at) + '</td></tr>' +
                            (p.provider_reference ? '<tr><td>Reference</td><td><code>' + escapeHtml(p.provider_reference) + '</code></td></tr>' : '') +
                        '</table>' +
                    '</div>' +
                    '<div class="col-md-6">' +
                        '<table class="table table-sm mb-0">' +
                            getMethodRows(p, details) +
                        '</table>' +
                    '</div>' +
                '</div>' +
            '</div>';
    })
    .catch(() => {
        content.innerHTML = '<div class="alert alert-danger">Error loading receipt preview.</div>';
    });
}

function viewPayoutDetails(payoutId) {
    const modal = new bootstrap.Modal(document.getElementById('payoutDetailsModal'));
    const content = document.getElementById('payoutDetailsContent');
    content.innerHTML = '<div class="text-center py-4"><div class="spinner-border" role="status"></div></div>';
    modal.show();

    fetch('<?= ORGANIZER_URL ?>/payouts/details/' + payoutId, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then((res) => res.json())
    .then((data) => {
        if (!data.success || !data.payout) {
            content.innerHTML = '<div class="alert alert-danger">Failed to load payout details.</div>';
            return;
        }

        const p = data.payout;
        let details = {};
        try {
            details = p.recipient_details ? JSON.parse(p.recipient_details) : {};
        } catch (e) {
            details = {};
        }

        const providerRows = [];
        if (details.account_number) providerRows.push('<tr><td>Account</td><td>****' + String(details.account_number).slice(-4) + '</td></tr>');
        if (details.phone_number) providerRows.push('<tr><td>Phone</td><td>****' + String(details.phone_number).slice(-4) + '</td></tr>');
        if (details.email) providerRows.push('<tr><td>Email</td><td>' + details.email + '</td></tr>');
        if (details.bank_name) providerRows.push('<tr><td>Bank</td><td>' + details.bank_name + '</td></tr>');
        if (details.provider) providerRows.push('<tr><td>Provider</td><td>' + details.provider + '</td></tr>');

        const statusClassMap = {
            pending: 'warning',
            approved: 'info',
            processing: 'primary',
            paid: 'success',
            failed: 'danger',
            rejected: 'danger',
            cancelled: 'secondary'
        };
        const statusClass = statusClassMap[p.status] || 'secondary';

        content.innerHTML = '' +
            '<div class="row g-3">' +
                '<div class="col-md-6">' +
                    '<h6 class="mb-2">Payout</h6>' +
                    '<table class="table table-sm">' +
                        '<tr><td>ID</td><td><code>' + (p.payout_id || 'N/A') + '</code></td></tr>' +
                        '<tr><td>Status</td><td><span class="badge text-bg-' + statusClass + '">' + ucFirst(p.status || 'unknown') + '</span></td></tr>' +
                        '<tr><td>Amount</td><td>GHS ' + safeMoney(p.amount) + '</td></tr>' +
                        '<tr><td>Fee</td><td>GHS ' + safeMoney(p.processing_fee) + '</td></tr>' +
                        '<tr><td>Net</td><td><strong>GHS ' + safeMoney(p.net_amount || (Number(p.amount || 0) - Number(p.processing_fee || 0))) + '</strong></td></tr>' +
                        '<tr><td>Created</td><td>' + formatDate(p.created_at) + '</td></tr>' +
                        '<tr><td>Approved</td><td>' + formatDate(p.approved_at) + '</td></tr>' +
                        '<tr><td>Processed</td><td>' + formatDate(p.processed_at) + '</td></tr>' +
                    '</table>' +
                '</div>' +
                '<div class="col-md-6">' +
                    '<h6 class="mb-2">Method</h6>' +
                    '<table class="table table-sm">' +
                        '<tr><td>Type</td><td>' + ucFirst(String(p.payout_method || 'n/a').replace('_', ' ')) + '</td></tr>' +
                        providerRows.join('') +
                    '</table>' +
                    (p.failure_reason ? '<div class="alert alert-danger mb-0"><strong>Reason:</strong> ' + p.failure_reason + '</div>' : '') +
                '</div>' +
            '</div>';
    })
    .catch(() => {
        content.innerHTML = '<div class="alert alert-danger">Error loading payout details.</div>';
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layout/organizer_layout.php';
?>
