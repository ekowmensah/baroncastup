<?php 
$content = ob_start();
if (!isset($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$csrf_field = '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
$csrf_token_value = htmlspecialchars($_SESSION['csrf_token']);
?>

<!-- Pending Payouts -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clock me-2 text-warning"></i>
                    Pending Payout Approvals
                    <?php if (!empty($payouts)): ?>
                        <span class="badge bg-warning text-dark"><?= count($payouts) ?></span>
                    <?php endif; ?>
                </h5>
                <div>
                    <?php if (!empty($payouts)): ?>
                    <button type="button" class="btn btn-sm btn-success me-1" onclick="showBulkApprovalModal()">
                        <i class="fas fa-check-double me-1"></i>Bulk Approve
                    </button>
                    <?php endif; ?>
                    <a href="<?= SUPERADMIN_URL ?>/payouts" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($payouts)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <h6 class="text-muted">No Pending Approvals</h6>
                        <p class="text-muted small">All payout requests have been reviewed.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Payout ID</th>
                                    <th>Organizer</th>
                                    <th>Amount</th>
                                    <th>Fee</th>
                                    <th>Net Amount</th>
                                    <th>Method</th>
                                    <th>Requested</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payouts as $payout): ?>
                                <tr>
                                    <td>
                                        <code class="small"><?= htmlspecialchars($payout['payout_id'] ?? 'N/A') ?></code>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($payout['tenant_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($payout['tenant_email'] ?? '') ?></small>
                                    </td>
                                    <td class="fw-bold text-primary">GH₵<?= number_format($payout['amount'], 2) ?></td>
                                    <td class="text-muted small">GH₵<?= number_format($payout['processing_fee'] ?? 0, 2) ?></td>
                                    <td class="text-success fw-bold">GH₵<?= number_format($payout['net_amount'] ?? $payout['amount'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?= ucfirst(str_replace('_', ' ', $payout['method_type'] ?? $payout['payout_method'] ?? 'N/A')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= $payout['created_at'] ? date('M j, Y', strtotime($payout['created_at'])) : 'N/A' ?>
                                            <br><?= $payout['created_at'] ? date('g:i A', strtotime($payout['created_at'])) : '' ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= SUPERADMIN_URL ?>/payouts/approve/<?= $payout['id'] ?>" 
                                               class="btn btn-outline-success" title="Review & Approve">
                                                <i class="fas fa-eye me-1"></i>Review
                                            </a>
                                            <button type="button" class="btn btn-outline-success" 
                                                    onclick="quickApprove(<?= $payout['id'] ?>)" title="Quick Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <a href="<?= SUPERADMIN_URL ?>/payouts/reject/<?= $payout['id'] ?>" 
                                               class="btn btn-outline-danger" title="Reject">
                                                <i class="fas fa-times"></i>
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
                        <div style="max-height: 200px" class="overflow-auto border rounded p-2">
                            <?php foreach ($payouts ?? [] as $payout): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="payout_ids[]" 
                                       value="<?= $payout['id'] ?>" id="bulk_payout_<?= $payout['id'] ?>">
                                <label class="form-check-label" for="bulk_payout_<?= $payout['id'] ?>">
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
        notesInput.value = 'Quick approval from pending page';
        form.appendChild(notesInput);
        
        addCsrfToForm(form);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layout/superadmin_layout.php';
?>
