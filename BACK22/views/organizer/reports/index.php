<!-- Reports & Analytics Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-chart-bar me-2 text-primary"></i>
            Reports & Analytics
        </h2>
        <p class="text-muted mb-0">Comprehensive insights and performance metrics</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" onclick="window.print()">
            <i class="fas fa-print me-2"></i>Print Report
        </button>
        <button class="btn btn-primary" onclick="location.reload()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
    </div>
</div>

<!-- Key Metrics Overview -->
<div class="row mb-4">
    <!-- Total Events -->
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                        <i class="fas fa-calendar-alt fa-2x text-primary"></i>
                    </div>
                </div>
                <div class="mb-2">
                    <h3 class="fw-bold mb-0"><?= number_format($reportData['events_summary']['total'] ?? 0) ?></h3>
                    <p class="text-muted mb-0">Total Events</p>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-success">
                        <i class="fas fa-check-circle me-1"></i>
                        <?= number_format($reportData['events_summary']['active'] ?? 0) ?> active
                    </small>
                    <small class="text-muted">
                        <?= number_format($reportData['events_summary']['completed'] ?? 0) ?> completed
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Votes -->
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="bg-success bg-opacity-10 rounded-3 p-3">
                        <i class="fas fa-vote-yea fa-2x text-success"></i>
                    </div>
                </div>
                <div class="mb-2">
                    <h3 class="fw-bold mb-0"><?= number_format($reportData['voting_summary']['total_quantity'] ?? 0) ?></h3>
                    <p class="text-muted mb-0">Total Votes Cast</p>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-info">
                        <i class="fas fa-receipt me-1"></i>
                        <?= number_format($reportData['voting_summary']['total_votes'] ?? 0) ?> transactions
                    </small>
                    <small class="text-muted">
                        <?= number_format($reportData['voting_summary']['unique_voters'] ?? 0) ?> voters
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Revenue -->
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                        <i class="fas fa-money-bill-wave fa-2x text-warning"></i>
                    </div>
                </div>
                <div class="mb-2">
                    <h3 class="fw-bold mb-0">GH₵<?= number_format($reportData['financial_summary']['total_revenue'] ?? 0, 2) ?></h3>
                    <p class="text-muted mb-0">Total Revenue</p>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-success">
                        <i class="fas fa-arrow-<?= ($reportData['financial_summary']['growth_percentage'] ?? 0) >= 0 ? 'up' : 'down' ?> me-1"></i>
                        <?= ($reportData['financial_summary']['growth_percentage'] ?? 0) >= 0 ? '+' : '' ?><?= number_format($reportData['financial_summary']['growth_percentage'] ?? 0, 1) ?>%
                    </small>
                    <small class="text-muted">
                        vs last month
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Average Transaction -->
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="bg-info bg-opacity-10 rounded-3 p-3">
                        <i class="fas fa-chart-line fa-2x text-info"></i>
                    </div>
                </div>
                <div class="mb-2">
                    <h3 class="fw-bold mb-0">GH₵<?= number_format($reportData['financial_summary']['avg_transaction'] ?? 0, 2) ?></h3>
                    <p class="text-muted mb-0">Avg Transaction</p>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-primary">
                        <i class="fas fa-receipt me-1"></i>
                        <?= number_format($reportData['financial_summary']['total_transactions'] ?? 0) ?> total
                    </small>
                    <small class="text-muted">
                        Per transaction
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Trends Chart -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    <i class="fas fa-chart-area me-2 text-primary"></i>Revenue & Voting Trends
                </h5>
                <small class="text-muted">Last 30 days performance</small>
            </div>
            <div class="card-body" style="height: 350px;">
                <canvas id="revenueTrendsChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Event Status Distribution -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    <i class="fas fa-pie-chart me-2 text-success"></i>Event Status
                </h5>
                <small class="text-muted">Current distribution</small>
            </div>
            <div class="card-body">
                <div style="height: 250px;">
                    <canvas id="eventStatusChart"></canvas>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="d-flex align-items-center">
                            <span class="badge bg-success me-2" style="width: 12px; height: 12px;"></span>
                            <small>Active</small>
                        </span>
                        <small class="fw-semibold"><?= $reportData['events_summary']['active'] ?? 0 ?></small>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="d-flex align-items-center">
                            <span class="badge bg-secondary me-2" style="width: 12px; height: 12px;"></span>
                            <small>Completed</small>
                        </span>
                        <small class="fw-semibold"><?= $reportData['events_summary']['completed'] ?? 0 ?></small>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="d-flex align-items-center">
                            <span class="badge bg-warning me-2" style="width: 12px; height: 12px;"></span>
                            <small>Draft</small>
                        </span>
                        <small class="fw-semibold"><?= $reportData['events_summary']['draft'] ?? 0 ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Performance Metrics -->
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    <i class="fas fa-tachometer-alt me-2 text-info"></i>Performance Metrics
                </h5>
                <small class="text-muted">Key performance indicators</small>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <div class="border-end">
                            <h4 class="fw-bold text-success mb-1"><?= number_format($reportData['performance_metrics']['success_rate'] ?? 0, 1) ?>%</h4>
                            <p class="text-muted mb-0 small">Payment Success Rate</p>
                            <small class="text-muted">
                                <?= number_format($reportData['performance_metrics']['successful_transactions'] ?? 0) ?> of <?= number_format($reportData['performance_metrics']['total_transactions'] ?? 0) ?> successful
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border-end">
                            <h4 class="fw-bold text-primary mb-1"><?= number_format($reportData['performance_metrics']['avg_votes_per_transaction'] ?? 0, 1) ?></h4>
                            <p class="text-muted mb-0 small">Avg Votes per Transaction</p>
                            <small class="text-muted">
                                Based on <?= number_format($reportData['voting_summary']['total_votes'] ?? 0) ?> transactions
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h4 class="fw-bold text-warning mb-1">GH₵<?= number_format($reportData['performance_metrics']['avg_revenue_per_transaction'] ?? 0, 2) ?></h4>
                        <p class="text-muted mb-0 small">Avg Revenue per Transaction</p>
                        <small class="text-muted">
                            Net earnings after fees
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Performing Events -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="fas fa-trophy me-2 text-warning"></i>Top Performing Events
                    </h5>
                    <small class="text-muted">Events ranked by revenue</small>
                </div>
                <a href="<?= ORGANIZER_URL ?>/events" class="btn btn-sm btn-outline-primary">
                    View All Events
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0">Rank</th>
                                <th class="border-0">Event Name</th>
                                <th class="border-0 text-center">Status</th>
                                <th class="border-0 text-center">Contestants</th>
                                <th class="border-0 text-center">Votes</th>
                                <th class="border-0 text-center">Revenue</th>
                                <th class="border-0 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($reportData['top_events'])): ?>
                                <?php foreach ($reportData['top_events'] as $index => $event): ?>
                                    <tr>
                                        <td class="py-3">
                                            <span class="badge bg-<?= $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'light text-dark') ?>">
                                                #<?= $index + 1 ?>
                                            </span>
                                            <?php if ($index === 0): ?>
                                                <i class="fas fa-crown text-warning ms-1"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3">
                                            <div>
                                                <div class="fw-semibold"><?= htmlspecialchars($event['name']) ?></div>
                                                <div class="small text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    Created <?= date('M j, Y', strtotime($event['created_at'])) ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center py-3">
                                            <span class="badge bg-<?= $event['status'] === 'active' ? 'success' : ($event['status'] === 'completed' ? 'secondary' : 'warning') ?>">
                                                <?= ucfirst($event['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center py-3">
                                            <div class="fw-semibold"><?= number_format($event['contestant_count']) ?></div>
                                        </td>
                                        <td class="text-center py-3">
                                            <div class="fw-semibold"><?= number_format($event['total_votes']) ?></div>
                                            <div class="small text-muted">
                                                <?= $event['contestant_count'] > 0 ? number_format($event['total_votes'] / $event['contestant_count'], 0) : 0 ?> avg
                                            </div>
                                        </td>
                                        <td class="text-center py-3">
                                            <div class="fw-semibold text-success">GH₵<?= number_format($event['revenue'], 2) ?></div>
                                            <div class="small text-muted">
                                                <?= $event['total_votes'] > 0 ? 'GH₵' . number_format($event['revenue'] / $event['total_votes'], 2) : 'GH₵0.00' ?> per vote
                                            </div>
                                        </td>
                                        <td class="text-center py-3">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= ORGANIZER_URL ?>/events/<?= $event['id'] ?>/view" class="btn btn-outline-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= ORGANIZER_URL ?>/events/<?= $event['id'] ?>/analytics" class="btn btn-outline-info" title="Analytics">
                                                    <i class="fas fa-chart-line"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <p class="mb-0">No events found</p>
                                            <small>Create your first event to see analytics</small>
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Trends Chart
    const revenueCtx = document.getElementById('revenueTrendsChart').getContext('2d');
    const chartLabels = <?= json_encode($reportData['revenue_trends']['labels'] ?? []) ?>;
    const revenueData = <?= json_encode($reportData['revenue_trends']['revenue'] ?? []) ?>;
    const votesData = <?= json_encode($reportData['revenue_trends']['votes'] ?? []) ?>;
    
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Revenue (GH₵)',
                data: revenueData,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y'
            }, {
                label: 'Votes',
                data: votesData,
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                tension: 0.4,
                fill: false,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                if (context.datasetIndex === 0) {
                                    label += 'GH₵' + context.parsed.y.toFixed(2);
                                } else {
                                    label += context.parsed.y;
                                }
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'GH₵' + value.toFixed(0);
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
    
    // Event Status Chart
    const statusCtx = document.getElementById('eventStatusChart').getContext('2d');
    const activeEvents = <?= $reportData['events_summary']['active'] ?? 0 ?>;
    const completedEvents = <?= $reportData['events_summary']['completed'] ?? 0 ?>;
    const draftEvents = <?= $reportData['events_summary']['draft'] ?? 0 ?>;
    
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Completed', 'Draft'],
            datasets: [{
                data: [activeEvents, completedEvents, draftEvents],
                backgroundColor: [
                    'rgba(25, 135, 84, 0.8)',
                    'rgba(108, 117, 125, 0.8)',
                    'rgba(255, 193, 7, 0.8)'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
