<!-- All Transactions -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-credit-card text-primary me-2"></i>
            All Transactions
        </h2>
        <p class="text-muted mb-0">Monitor all platform transactions and payments</p>
    </div>
    <div>
        <button class="btn btn-outline-secondary" onclick="location.reload()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
    </div>
</div>

<!-- Transaction Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-primary">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($transactions['total_count'] ?? 0) ?></div>
                    <div>Total Transactions</div>
                    <div class="small">All time</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-credit-card fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">GH₵<?= number_format($transactions['total_volume'] ?? 0) ?></div>
                    <div>Total Volume</div>
                    <div class="small">All transactions</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-money-bill fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($transactions['today_count'] ?? 0) ?></div>
                    <div>Today</div>
                    <div class="small">Transactions</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-warning">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $transactions['success_rate'] ?? 0 ?>%</div>
                    <div>Success Rate</div>
                    <div class="small">Last 30 days</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Options -->
<div class="row mb-4">
    <div class="col-md-3">
        <input type="text" class="form-control" id="transactionSearch" placeholder="Search transactions..." onkeyup="filterTransactions()">
    </div>
    <div class="col-md-3">
        <select class="form-select" id="statusFilter" onchange="filterTransactions()">
            <option value="">All Statuses</option>
            <option value="completed">Completed</option>
            <option value="pending">Pending</option>
            <option value="failed">Failed</option>
            <option value="refunded">Refunded</option>
        </select>
    </div>
    <div class="col-md-3">
        <select class="form-select" id="typeFilter" onchange="filterTransactions()">
            <option value="">All Types</option>
            <option value="subscription">Subscription</option>
            <option value="one_time">One-time Payment</option>
            <option value="refund">Refund</option>
        </select>
    </div>
    <div class="col-md-3">
        <input type="date" class="form-control" id="dateFilter" onchange="filterTransactions()">
    </div>
</div>

<!-- Transactions Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Transaction History
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($transactions['list'])): ?>
            <div class="table-responsive">
                <table class="table table-hover" id="transactionsTable">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Tenant</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment Method</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions['list'] as $transaction): ?>
                        <tr data-transaction-id="<?= $transaction['id'] ?? '' ?>" 
                            data-status="<?= $transaction['status'] ?? '' ?>"
                            data-type="<?= $transaction['type'] ?? '' ?>"
                            data-date="<?= date('Y-m-d', strtotime($transaction['created_at'] ?? 'now')) ?>">
                            <td>
                                <code><?= htmlspecialchars($transaction['transaction_id'] ?? 'N/A') ?></code>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($transaction['tenant_name'] ?? 'Unknown') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($transaction['tenant_email'] ?? '') ?></small>
                            </td>
                            <td>
                                <?php 
                                $type = $transaction['type'] ?? 'unknown';
                                $typeClass = match($type) {
                                    'subscription' => 'bg-primary',
                                    'one_time' => 'bg-info',
                                    'refund' => 'bg-warning',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $typeClass ?>"><?= ucfirst(str_replace('_', ' ', $type)) ?></span>
                            </td>
                            <td>
                                <div class="fw-bold">GH₵<?= number_format($transaction['amount'] ?? 0, 2) ?></div>
                                <?php if (!empty($transaction['fee'])): ?>
                                    <small class="text-muted">Fee: GH₵<?= number_format($transaction['fee'], 2) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $status = $transaction['status'] ?? 'unknown';
                                $statusClass = match($status) {
                                    'completed' => 'bg-success',
                                    'pending' => 'bg-warning',
                                    'failed' => 'bg-danger',
                                    'refunded' => 'bg-info',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= ucfirst($status) ?></span>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($transaction['payment_method'] ?? 'N/A') ?></div>
                                <?php if (!empty($transaction['last_four'])): ?>
                                    <small class="text-muted">****<?= $transaction['last_four'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?= date('M j, Y', strtotime($transaction['created_at'] ?? 'now')) ?></div>
                                <small class="text-muted"><?= date('H:i', strtotime($transaction['created_at'] ?? 'now')) ?></small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewTransaction('<?= $transaction['id'] ?? 0 ?>')" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if (($transaction['status'] ?? '') === 'completed'): ?>
                                        <button class="btn btn-sm btn-outline-warning" onclick="refundTransaction('<?= $transaction['id'] ?? 0 ?>')" title="Refund">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-info" onclick="downloadReceipt('<?= $transaction['id'] ?? 0 ?>')" title="Receipt">
                                        <i class="fas fa-receipt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                <h5>No Transactions</h5>
                <p class="text-muted">No transactions have been processed yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function filterTransactions() {
    const searchTerm = document.getElementById('transactionSearch').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const typeFilter = document.getElementById('typeFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;
    const rows = document.querySelectorAll('#transactionsTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const status = row.getAttribute('data-status');
        const type = row.getAttribute('data-type');
        const date = row.getAttribute('data-date');
        
        const matchesSearch = text.includes(searchTerm);
        const matchesStatus = !statusFilter || status === statusFilter;
        const matchesType = !typeFilter || type === typeFilter;
        const matchesDate = !dateFilter || date === dateFilter;
        
        row.style.display = matchesSearch && matchesStatus && matchesType && matchesDate ? '' : 'none';
    });
}

function viewTransaction(transactionId) {
    console.log('Viewing transaction:', transactionId);
}

function refundTransaction(transactionId) {
    if (confirm('Are you sure you want to refund this transaction?')) {
        console.log('Refunding transaction:', transactionId);
        alert('Refund processed successfully!');
        location.reload();
    }
}

function downloadReceipt(transactionId) {
    console.log('Downloading receipt for transaction:', transactionId);
}
</script>
