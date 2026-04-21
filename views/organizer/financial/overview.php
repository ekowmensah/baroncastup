<!-- Financial Overview Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-chart-line me-2"></i>
            Financial Overview
        </h2>
        <p class="text-muted mb-0">Track your earnings, payouts, and financial performance</p>
    </div>
    <div>
        <div class="btn-group" role="group">
            <button class="btn btn-outline-primary" onclick="refreshFinancials()">
                <i class="fas fa-sync me-2"></i>Refresh
            </button>
            <button class="btn btn-outline-success" onclick="exportFinancials()">
                <i class="fas fa-download me-2"></i>Export Report
            </button>
        </div>
    </div>
</div>

<!-- Financial Summary Cards -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">GH₵<?= number_format($balance['available'] ?? 0, 2) ?></div>
                    <div>Available Balance</div>
                    <div class="small">Ready for payout</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-wallet fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card success text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">GH₵<?= number_format($balance['total_earned'] ?? 0, 2) ?></div>
                    <div>Total Earned</div>
                    <div class="small">All time earnings</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-coins fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card warning text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">GH₵<?= number_format($balance['pending'] ?? 0, 2) ?></div>
                    <div>Pending</div>
                    <div class="small">Processing...</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-clock fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card info text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">GH₵<?= number_format($balance['total_paid'] ?? 0, 2) ?></div>
                    <div>Total Paid Out</div>
                    <div class="small">Lifetime payouts</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-hand-holding-usd fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Revenue Trends -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Revenue Trends</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueTrendsChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <!-- <button class="btn btn-primary" onclick="requestPayout()">
                        <i class="fas fa-money-bill-wave me-2"></i>Request Payout
                    </button> -->

                    <a href="<?= ORGANIZER_URL ?>/financial/revenue" class="btn btn-primary">
                        <i class="fas fa-money-bill-wave me-2"></i>REQUEST PAYOUT
                    </a>
                    <a href="<?= ORGANIZER_URL ?>/financial/transactions" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-2"></i>View Transactions
                    </a>
                    <a href="<?= ORGANIZER_URL ?>/financial/payouts" class="btn btn-outline-info">
                        <i class="fas fa-history me-2"></i>Payout History
                    </a>
                    <button class="btn btn-outline-success" onclick="downloadStatement()">
                        <i class="fas fa-file-pdf me-2"></i>Download Statement
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Performance Metrics</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Revenue Growth</span>
                        <span class="small fw-semibold text-success">+<?= number_format($revenueStats['growth_percentage'] ?? 0, 1) ?>%</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: <?= min(100, abs($revenueStats['growth_percentage'] ?? 0)) ?>%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">This Week</span>
                        <span class="small fw-semibold">GH₵<?= number_format($revenueStats['this_week'] ?? 0, 2) ?></span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: 75%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Last Week</span>
                        <span class="small fw-semibold">GH₵<?= number_format($revenueStats['last_week'] ?? 0, 2) ?></span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" style="width: 60%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Transactions</h5>
                    <a href="<?= ORGANIZER_URL ?>/financial/transactions" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Transaction ID</th>
                                <th>Event</th>
                                <th>Contestant</th>
                                <th>Votes</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentTransactions)): ?>
                                <?php foreach ($recentTransactions as $transaction): ?>
                                    <tr>
                                        <td><?= date('M j, Y H:i', strtotime($transaction['created_at'])) ?></td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($transaction['transaction_id'] ?? 'N/A') ?></div>
                                        </td>
                                        <td><?= htmlspecialchars($transaction['event_name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($transaction['contestant_name'] ?? 'N/A') ?></td>
                                        <td><?= number_format($transaction['quantity'] ?? 0) ?></td>
                                        <td>GH₵<?= number_format($transaction['amount'] ?? 0, 2) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $transaction['status'] === 'success' ? 'success' : ($transaction['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                                <?= ucfirst($transaction['status'] ?? 'unknown') ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-receipt fa-2x mb-2"></i>
                                            <p>No recent transactions found</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payout Request Modal -->
<div class="modal fade" id="payoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Payout</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Available balance: <strong>GH₵<?= number_format($balance['available'] ?? 0, 2) ?></strong>
                </div>
                
                <form id="payoutForm">
                    <div class="mb-3">
                        <label class="form-label">Payout Amount *</label>
                        <div class="input-group">
                            <span class="input-group-text">GH₵</span>
                            <input type="number" class="form-control" name="amount" step="0.01" max="<?= $balance['available'] ?? 0 ?>" required>
                        </div>
                        <div class="form-text">Minimum payout: GH₵10.00</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select class="form-select" name="payment_method" required>
                            <option value="">Select Payment Method</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="paypal">PayPal</option>
                            <option value="stripe">Stripe</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitPayout()">
                    <i class="fas fa-paper-plane me-2"></i>Submit Request
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize revenue trends chart with real data
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueTrendsChart').getContext('2d');
    
    // Get actual revenue data from PHP
    const chartLabels = <?= json_encode($revenueTrends['labels'] ?? []) ?>;
    const chartData = <?= json_encode($revenueTrends['data'] ?? []) ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Revenue (GH₵)',
                data: chartData,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'GH₵' + value.toFixed(2);
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: GH₵' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            }
        }
    });
});

function refreshFinancials() {
    console.log('Refreshing financial data...');
    location.reload();
}

function exportFinancials() {
    console.log('Exporting financial report...');
    alert('Financial report export functionality will be implemented soon!');
}

function requestPayout() {
    const modal = new coreui.Modal(document.getElementById('payoutModal'));
    modal.show();
}

function submitPayout() {
    const form = document.getElementById('payoutForm');
    const formData = new FormData(form);
    
    console.log('Submitting payout request...');
    
    // Close modal
    const modal = coreui.Modal.getInstance(document.getElementById('payoutModal'));
    modal.hide();
    
    alert('Payout request submitted successfully!');
}

function downloadStatement() {
    console.log('Downloading financial statement...');
    alert('Statement download functionality will be implemented soon!');
}
</script>
