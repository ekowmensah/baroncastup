<?php
$content = ob_start();
if (!isset($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$csrf_token_value = htmlspecialchars($_SESSION['csrf_token']);

$statusClasses = [
    'pending' => 'warning',
    'approved' => 'info',
    'processing' => 'primary',
    'paid' => 'success',
    'success' => 'success',
    'failed' => 'danger',
    'rejected' => 'danger',
    'cancelled' => 'secondary'
];
?>

<style>
.history-shell {
    background: #f6f8fb;
    padding: 1rem;
    border-radius: 12px;
}

.history-card {
    border: 1px solid #dde6ef;
    border-radius: 12px;
}

.history-title {
    font-weight: 700;
    color: #1f2d3d;
}

.amount-main {
    font-weight: 700;
}

.amount-sub {
    font-size: 0.82rem;
    color: #6c757d;
}

.table thead th {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #5f6f80;
}

.method-icon {
    width: 18px;
    text-align: center;
}
</style>

<div class="history-shell">
    <div class="card history-card">
        <div class="card-header bg-white border-0">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0 history-title"><i class="fas fa-history me-2"></i>Payout History</h5>
                <div class="btn-group">
                    <button class="btn btn-outline-primary btn-sm" onclick="location.reload()">
                        <i class="fas fa-sync me-1"></i>Refresh
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="exportHistory()">
                        <i class="fas fa-file-export me-1"></i>Export
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($payouts)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Payout</th>
                                <th>Requested</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Processed</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payouts as $payout): ?>
                                <?php
                                    $methodIcons = [
                                        'bank_transfer' => 'fas fa-university',
                                        'mobile_money' => 'fas fa-mobile-alt',
                                        'paypal' => 'fab fa-paypal',
                                        'stripe' => 'fab fa-stripe'
                                    ];
                                    $status = $payout['status'] ?? 'unknown';
                                    $statusClass = $statusClasses[$status] ?? 'secondary';
                                    $amount = (float)($payout['amount'] ?? 0);
                                    $fee = (float)($payout['processing_fee'] ?? 0);
                                    $net = isset($payout['net_amount']) ? (float)$payout['net_amount'] : ($amount - $fee);
                                    $icon = $methodIcons[$payout['payout_method'] ?? ''] ?? 'fas fa-credit-card';
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><code><?= htmlspecialchars($payout['payout_id']) ?></code></div>
                                        <div class="amount-sub"><?= htmlspecialchars(ucfirst($payout['payout_type'] ?? 'manual')) ?> payout</div>
                                    </td>
                                    <td>
                                        <?php if (!empty($payout['created_at'])): ?>
                                            <div><?= date('M j, Y', strtotime($payout['created_at'])) ?></div>
                                            <div class="amount-sub"><?= date('g:i A', strtotime($payout['created_at'])) ?></div>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="amount-main">GHS <?= number_format($amount, 2) ?></div>
                                        <?php if ($fee > 0): ?>
                                            <div class="amount-sub">Fee: GHS <?= number_format($fee, 2) ?></div>
                                        <?php endif; ?>
                                        <div class="amount-sub">Net: GHS <?= number_format($net, 2) ?></div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="<?= $icon ?> method-icon"></i>
                                            <span><?= htmlspecialchars(ucwords(str_replace('_', ' ', $payout['payout_method'] ?? 'N/A'))) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-<?= $statusClass ?>"><?= htmlspecialchars(ucfirst($status)) ?></span>
                                        <?php if ($status === 'failed' && !empty($payout['failure_reason'])): ?>
                                            <div class="amount-sub text-danger mt-1"><?= htmlspecialchars(substr($payout['failure_reason'], 0, 60)) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($payout['processed_at'])): ?>
                                            <div><?= date('M j, Y', strtotime($payout['processed_at'])) ?></div>
                                            <div class="amount-sub"><?= date('g:i A', strtotime($payout['processed_at'])) ?></div>
                                        <?php elseif (($payout['status'] ?? '') === 'processing'): ?>
                                            <span class="text-primary"><i class="fas fa-spinner fa-spin me-1"></i>Processing</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="viewPayoutDetails(<?= (int)$payout['id'] ?>)" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            <?php if (in_array($status, ['pending', 'approved'])): ?>
                                                <button class="btn btn-outline-danger" onclick="openCancelPayoutModal(<?= (int)$payout['id'] ?>)" title="Cancel Payout">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>

                                            <?php if (in_array($status, ['paid', 'success'])): ?>
                                                <button class="btn btn-outline-success" onclick="openReceiptPreview(<?= (int)$payout['id'] ?>)" title="Preview Receipt">
                                                    <i class="fas fa-receipt"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No payout history yet</h5>
                    <p class="text-muted">Your payout requests will appear here.</p>
                    <a href="<?= ORGANIZER_URL ?>/payouts/request" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Request Payout
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="payoutDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Payout Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="payoutDetailsContent">
                <div class="text-center py-4"><div class="spinner-border" role="status"></div></div>
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

<div class="modal fade" id="cancelPayoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-ban me-2"></i>Cancel Payout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Cancel this payout request?</p>
                <p class="text-muted mb-0">If it is not processed yet, funds will return to your available balance.</p>
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

<script>
function exportHistory() {
    alert('CSV export is not connected yet.');
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
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

function parseRecipientDetails(recipientDetails) {
    if (!recipientDetails) return {};
    if (typeof recipientDetails === 'object') return recipientDetails;
    try {
        return JSON.parse(recipientDetails);
    } catch (e) {
        return {};
    }
}

function getMethodRows(payout, details) {
    const rows = [];

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

    return '<tr><td>Method</td><td>' + escapeHtml(ucFirst(String(payout.payout_method || 'N/A').replace('_', ' '))) + '</td></tr>' +
           '<tr><td>Details</td><td>Payment method configured</td></tr>';
}

function fetchPayout(payoutId) {
    return fetch('<?= ORGANIZER_URL ?>/payouts/details/' + payoutId, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then((res) => res.json())
    .then((data) => {
        if (!data.success || !data.payout) {
            throw new Error(data.error || 'Failed to load payout');
        }
        return data.payout;
    });
}

function viewPayoutDetails(payoutId) {
    const modal = new bootstrap.Modal(document.getElementById('payoutDetailsModal'));
    const content = document.getElementById('payoutDetailsContent');
    content.innerHTML = '<div class="text-center py-4"><div class="spinner-border" role="status"></div></div>';
    modal.show();

    fetchPayout(payoutId)
        .then((p) => {
            const details = parseRecipientDetails(p.recipient_details);
            content.innerHTML = '' +
                '<div class="row g-3">' +
                    '<div class="col-md-6">' +
                        '<h6>Payout</h6>' +
                        '<table class="table table-sm">' +
                            '<tr><td>ID</td><td><code>' + escapeHtml(p.payout_id || 'N/A') + '</code></td></tr>' +
                            '<tr><td>Status</td><td>' + escapeHtml(ucFirst(p.status || 'unknown')) + '</td></tr>' +
                            '<tr><td>Amount</td><td>GHS ' + safeMoney(p.amount) + '</td></tr>' +
                            '<tr><td>Fee</td><td>GHS ' + safeMoney(p.processing_fee) + '</td></tr>' +
                            '<tr><td>Net</td><td><strong>GHS ' + safeMoney(p.net_amount || (Number(p.amount || 0) - Number(p.processing_fee || 0))) + '</strong></td></tr>' +
                            '<tr><td>Created</td><td>' + formatDate(p.created_at) + '</td></tr>' +
                            '<tr><td>Approved</td><td>' + formatDate(p.approved_at) + '</td></tr>' +
                            '<tr><td>Processed</td><td>' + formatDate(p.processed_at) + '</td></tr>' +
                        '</table>' +
                    '</div>' +
                    '<div class="col-md-6">' +
                        '<h6>Method</h6>' +
                        '<table class="table table-sm">' +
                            getMethodRows(p, details) +
                        '</table>' +
                    '</div>' +
                '</div>' +
                (p.failure_reason ? '<div class="alert alert-danger mb-0"><strong>Failure:</strong> ' + escapeHtml(p.failure_reason) + '</div>' : '');
        })
        .catch((e) => {
            content.innerHTML = '<div class="alert alert-danger">' + escapeHtml(e.message || 'Unable to load payout details.') + '</div>';
        });
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

    fetchPayout(payoutId)
        .then((p) => {
            const details = parseRecipientDetails(p.recipient_details);
            content.innerHTML = '' +
                '<div class="border rounded p-3">' +
                    '<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">' +
                        '<div>' +
                            '<h5 class="mb-1">BaronCast Payout Receipt</h5>' +
                            '<div class="text-muted small">Payout ID: <code>' + escapeHtml(p.payout_id || 'N/A') + '</code></div>' +
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
        .catch((e) => {
            content.innerHTML = '<div class="alert alert-danger">' + escapeHtml(e.message || 'Unable to load receipt preview.') + '</div>';
        });
}

function openCancelPayoutModal(payoutId) {
    const form = document.getElementById('cancelPayoutForm');
    form.action = '<?= ORGANIZER_URL ?>/payouts/' + payoutId + '/cancel';
    const modal = new bootstrap.Modal(document.getElementById('cancelPayoutModal'));
    modal.show();
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layout/organizer_layout.php';
?>
