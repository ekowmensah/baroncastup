<!-- Revenue Distribution Analytics -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-chart-line text-success me-2"></i>
            Revenue Distribution
        </h2>
        <p class="text-muted mb-0">Real-time revenue sharing analytics and tenant earnings</p>
    </div>
    <div>
        <button class="btn btn-success" onclick="processAllPayouts()">
            <i class="fas fa-money-check-alt me-2"></i>Process Payouts
        </button>
        <button class="btn btn-outline-secondary" onclick="location.reload()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
    </div>
</div>

<!-- Distribution Summary Cards -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-primary">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">GHS <?= number_format($distribution['total_platform_revenue'] ?? 0, 2) ?></div>
                    <div>Platform Revenue</div>
                    <div class="small">All-time platform fees</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-chart-pie fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">GHS <?= number_format($distribution['total_tenant_earnings'] ?? 0, 2) ?></div>
                    <div>Tenant Earnings</div>
                    <div class="small">All-time tenant share</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-users fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-warning">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">GHS <?= number_format($distribution['pending_payouts'] ?? 0, 2) ?></div>
                    <div>Pending Payouts</div>
                    <div class="small">Current available balance</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-clock fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($distribution['distribution_rate'] ?? 0, 1) ?>%</div>
                    <div>Avg Fee Rate</div>
                    <div class="small">All-time average</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-percentage fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Distribution Trends Chart -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-area me-2"></i>
                    Revenue Distribution Trends (Last 30 Days)
                </h5>
            </div>
            <div class="card-body">
                <canvas id="distributionTrendsChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-doughnut me-2"></i>
                    Fee Rule Usage
                </h5>
            </div>
            <div class="card-body">
                <canvas id="feeRuleChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tenant Earnings vs Platform Share -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-balance-scale me-2"></i>
            Tenant Earnings vs Platform Share
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($distribution['tenant_earnings_breakdown'])): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th class="text-end">Transactions</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-end">Platform Share</th>
                            <th class="text-end">Tenant Earnings</th>
                            <th class="text-end">Fee %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalTransactions = 0;
                        $totalAmount = 0;
                        $totalPlatformFees = 0;
                        $totalTenantEarnings = 0;
                        
                        foreach ($distribution['tenant_earnings_breakdown'] as $tenant): 
                            $totalTransactions += $tenant['transaction_count'];
                            $totalAmount += $tenant['total_transaction_amount'];
                            $totalPlatformFees += $tenant['platform_fees'];
                            $totalTenantEarnings += $tenant['tenant_earnings'];
                        ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($tenant['tenant_name']) ?></div>
                            </td>
                            <td class="text-end">
                                <span class="badge bg-secondary"><?= number_format($tenant['transaction_count']) ?></span>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold">GHS <?= number_format($tenant['total_transaction_amount'], 2) ?></span>
                            </td>
                            <td class="text-end">
                                <span class="text-primary fw-bold">GHS <?= number_format($tenant['platform_fees'], 2) ?></span>
                            </td>
                            <td class="text-end">
                                <span class="text-success fw-bold">GHS <?= number_format($tenant['tenant_earnings'], 2) ?></span>
                            </td>
                            <td class="text-end">
                                <span class="badge bg-info"><?= number_format($tenant['avg_fee_percentage'], 1) ?>%</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="table-active fw-bold">
                            <td>TOTAL</td>
                            <td class="text-end">
                                <span class="badge bg-dark"><?= number_format($totalTransactions) ?></span>
                            </td>
                            <td class="text-end">GHS <?= number_format($totalAmount, 2) ?></td>
                            <td class="text-end text-primary">GHS <?= number_format($totalPlatformFees, 2) ?></td>
                            <td class="text-end text-success">GHS <?= number_format($totalTenantEarnings, 2) ?></td>
                            <td class="text-end">
                                <span class="badge bg-info"><?= $totalAmount > 0 ? number_format(($totalPlatformFees / $totalAmount) * 100, 1) : 0 ?>%</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-balance-scale fa-3x text-muted mb-3"></i>
                <h5>No Transaction Data Yet</h5>
                <p class="text-muted">Revenue distribution data will appear here once transactions are processed.</p>
            </div>
        <?php endif; ?>
    </div>
</div>


<!-- Recent Revenue Distributions -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-stream me-2"></i>
            Recent Revenue Distributions
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($distribution['recent_distributions'])): ?>
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Tenant</th>
                            <th>Event</th>
                            <th>Contestant</th>
                            <th>Total Amount</th>
                            <th>Platform Fee</th>
                            <th>Tenant Earnings</th>
                            <th>Fee %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($distribution['recent_distributions'], 0, 20) as $dist): ?>
                        <tr>
                            <td>
                                <small><?= date('M j, H:i', strtotime($dist['created_at'])) ?></small>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($dist['tenant_name']) ?></div>
                            </td>
                            <td>
                                <small><?= htmlspecialchars($dist['event_name']) ?></small>
                            </td>
                            <td>
                                <small><?= htmlspecialchars($dist['contestant_name']) ?></small>
                            </td>
                            <td>
                                <span class="fw-bold">GHS <?= number_format($dist['total_amount'], 2) ?></span>
                            </td>
                            <td>
                                <span class="text-primary fw-bold">GHS <?= number_format($dist['platform_fee'], 2) ?></span>
                            </td>
                            <td>
                                <span class="text-success fw-bold">GHS <?= number_format($dist['tenant_amount'], 2) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <?= number_format(($dist['platform_fee'] / $dist['total_amount']) * 100, 1) ?>%
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                <h5>No Revenue Distributions Yet</h5>
                <p class="text-muted">Revenue distributions will appear here as votes are processed.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function processAllPayouts() {
    if (confirm('Process all pending payouts for tenants with minimum balance?')) {
        console.log('Processing all payouts...');
        alert('Payout processing initiated for all eligible tenants.');
    }
}

function processPayout(tenantId) {
    if (confirm('Process payout for this tenant?')) {
        console.log('Processing payout for tenant:', tenantId);
        alert('Payout processing initiated for tenant.');
    }
}

// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart !== 'undefined') {
        // Distribution Trends Chart
        const trendsCtx = document.getElementById('distributionTrendsChart');
        if (trendsCtx) {
            const trendsData = <?= json_encode($distribution['distribution_trends'] ?? []) ?>;
            
            new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: trendsData.map(d => new Date(d.date).toLocaleDateString('en-US', {month: 'short', day: 'numeric'})),
                    datasets: [{
                        label: 'Platform Fees',
                        data: trendsData.map(d => d.platform_fees),
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.1,
                        fill: true
                    }, {
                        label: 'Tenant Earnings',
                        data: trendsData.map(d => d.tenant_earnings),
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.1,
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
                                    return 'GHS ' + value.toFixed(2);
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Fee Rule Usage Chart
        const feeCtx = document.getElementById('feeRuleChart');
        if (feeCtx) {
            const feeData = <?= json_encode($distribution['fee_breakdown'] ?? []) ?>;
            
            if (feeData.length === 0) {
                feeCtx.parentElement.innerHTML = '<div class="text-center py-4"><i class="fas fa-chart-pie fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No fee data available</p></div>';
            } else {
            
            new Chart(feeCtx, {
                type: 'doughnut',
                data: {
                    labels: feeData.map(f => {
                        let label = f.name || (f.rule_type.charAt(0).toUpperCase() + f.rule_type.slice(1));
                        if (f.percentage_rate) label += ' (' + f.percentage_rate + '%)';
                        if (f.fixed_amount) label += ' (GHS ' + parseFloat(f.fixed_amount).toFixed(2) + ')';
                        return label;
                    }),
                    datasets: [{
                        data: feeData.map(f => parseFloat(f.total_collected)),
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#4BC0C0']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': GHS ' + context.parsed.toFixed(2);
                                }
                            }
                        }
                    }
                }
            });
            }
        }
    }
});
</script>
