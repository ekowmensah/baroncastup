<?php 
$content = ob_start();
$baseUrl = SUPERADMIN_URL . '/payouts/history';
?>

<!-- Payout History -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>
                    Payout History
                    <small class="text-muted">(<?= number_format($total_records) ?> total)</small>
                </h5>
                <a href="<?= SUPERADMIN_URL ?>/payouts" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                </a>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <form method="GET" action="<?= $baseUrl ?>" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" name="search" 
                                   value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="Search by payout ID or organizer...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="status">
                            <option value="">All Statuses</option>
                            <?php 
                            $statuses = ['pending', 'approved', 'processing', 'paid', 'failed', 'rejected', 'cancelled'];
                            foreach ($statuses as $s): ?>
                                <option value="<?= $s ?>" <?= $status_filter === $s ? 'selected' : '' ?>>
                                    <?= ucfirst($s) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                        <?php if ($search || $status_filter): ?>
                        <a href="<?= $baseUrl ?>" class="btn btn-outline-secondary ms-1">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- Payouts Table -->
                <?php if (empty($payouts)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No payouts found</h6>
                        <?php if ($search || $status_filter): ?>
                            <p class="text-muted small">Try adjusting your filters.</p>
                        <?php endif; ?>
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
                                    <th>Net</th>
                                    <th>Method</th>
                                    <th>Status</th>
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
                                        <div class="fw-bold"><?= htmlspecialchars($payout['tenant_name'] ?? 'Unknown') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($payout['tenant_email'] ?? '') ?></small>
                                    </td>
                                    <td class="fw-bold">GH₵<?= number_format($payout['amount'] ?? 0, 2) ?></td>
                                    <td class="text-muted small">GH₵<?= number_format($payout['processing_fee'] ?? 0, 2) ?></td>
                                    <td class="text-success">GH₵<?= number_format($payout['net_amount'] ?? 0, 2) ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?= ucfirst(str_replace('_', ' ', $payout['payout_method'] ?? 'N/A')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'approved' => 'info',
                                            'processing' => 'primary',
                                            'paid' => 'success',
                                            'failed' => 'danger',
                                            'rejected' => 'danger',
                                            'cancelled' => 'secondary'
                                        ];
                                        $color = $statusColors[$payout['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $color ?>">
                                            <?= ucfirst($payout['status']) ?>
                                        </span>
                                    </td>
                                    <td class="small">
                                        <?= $payout['created_at'] ? date('M j, Y', strtotime($payout['created_at'])) : 'N/A' ?>
                                    </td>
                                    <td>
                                        <a href="<?= SUPERADMIN_URL ?>/payouts/details/<?= $payout['id'] ?>" 
                                           class="btn btn-outline-primary btn-sm" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($payout['status'] === 'paid'): ?>
                                        <button class="btn btn-outline-success btn-sm" 
                                                onclick="window.open('<?= SUPERADMIN_URL ?>/payouts/<?= $payout['id'] ?>/receipt', '_blank')"
                                                title="Download Receipt">
                                            <i class="fas fa-receipt"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Showing <?= (($current_page - 1) * $per_page) + 1 ?>–<?= min($current_page * $per_page, $total_records) ?> 
                            of <?= number_format($total_records) ?> payouts
                        </div>
                        <ul class="pagination pagination-sm mb-0">
                            <?php 
                            $queryParams = [];
                            if ($status_filter) $queryParams['status'] = $status_filter;
                            if ($search) $queryParams['search'] = $search;
                            ?>
                            
                            <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $baseUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $current_page - 1])) ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            
                            <?php
                            $start = max(1, $current_page - 2);
                            $end = min($total_pages, $current_page + 2);
                            
                            if ($start > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $baseUrl . '?' . http_build_query(array_merge($queryParams, ['page' => 1])) ?>">1</a>
                                </li>
                                <?php if ($start > 2): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $baseUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $i])) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($end < $total_pages): ?>
                                <?php if ($end < $total_pages - 1): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $baseUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $total_pages])) ?>"><?= $total_pages ?></a>
                                </li>
                            <?php endif; ?>
                            
                            <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $baseUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $current_page + 1])) ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layout/superadmin_layout.php';
?>
