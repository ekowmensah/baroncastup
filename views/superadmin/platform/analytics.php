<!-- Platform Analytics -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-chart-line text-primary me-2"></i>
            <?= htmlspecialchars($title ?? 'Platform Analytics') ?>
        </h2>
        <p class="text-muted mb-0">Comprehensive platform performance and usage analytics</p>
    </div>
    <div>
        <div class="btn-group" role="group">
            <button class="btn btn-outline-secondary" onclick="exportAnalytics()">
                <i class="fas fa-download me-2"></i>Export
            </button>
            <button class="btn btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync me-2"></i>Refresh
            </button>
        </div>
    </div>
</div>

<!-- Key Performance Indicators -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-primary">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($analytics['total_events'] ?? 0) ?></div>
                    <div>Total Events</div>
                    <div class="small">All time</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-chart-bar fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($analytics['active_users'] ?? 0) ?></div>
                    <div>Active Users</div>
                    <div class="small">Last 30 days</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-users fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($analytics['total_votes'] ?? 0) ?></div>
                    <div>Total Votes</div>
                    <div class="small">Platform wide</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-vote-yea fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-warning">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($analytics['engagement_rate'] ?? 0, 1) ?>%</div>
                    <div>Engagement Rate</div>
                    <div class="small">Events with votes</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-heart fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Charts -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-area me-2"></i>
                    Platform Usage Over Time
                </h5>
            </div>
            <div class="card-body">
                <canvas id="usageChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Event Types Distribution
                </h5>
            </div>
            <div class="card-body">
                <canvas id="eventTypesChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Metrics -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-building me-2"></i>
                    Top Performing Tenants
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($analytics['top_tenants'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tenant</th>
                                    <th>Events</th>
                                    <th>Votes</th>
                                    <th>Engagement</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($analytics['top_tenants'], 0, 5) as $tenant): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($tenant['name'] ?? 'Unknown') ?></div>
                                    </td>
                                    <td><?= number_format($tenant['events'] ?? 0) ?></td>
                                    <td><?= number_format($tenant['votes'] ?? 0) ?></td>
                                    <td>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?= $tenant['engagement'] ?? 0 ?>%">
                                            </div>
                                        </div>
                                        <small><?= number_format($tenant['engagement'] ?? 0, 1) ?>%</small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No tenant data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-trophy me-2"></i>
                    Most Active Users
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($analytics['top_users'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Events Created</th>
                                    <th>Votes Cast</th>
                                    <th>Last Active</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($analytics['top_users'], 0, 5) as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 12px;">
                                                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-2">
                                                <div class="fw-semibold"><?= htmlspecialchars($user['name'] ?? 'Unknown') ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= number_format($user['events_created'] ?? 0) ?></td>
                                    <td><?= number_format($user['votes_cast'] ?? 0) ?></td>
                                    <td>
                                        <small><?= date('M j', strtotime($user['last_active'] ?? 'now')) ?></small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No user activity data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- System Performance Metrics -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-server me-2"></i>
                    System Performance Metrics
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="fs-5 fw-bold text-success"><?= number_format($analytics['avg_response_time'] ?? 0) ?>ms</div>
                            <small class="text-muted">Avg Response Time</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="fs-5 fw-bold text-info"><?= number_format($analytics['uptime'] ?? 0, 2) ?>%</div>
                            <small class="text-muted">System Uptime</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="fs-5 fw-bold text-primary"><?= number_format($analytics['api_calls'] ?? 0) ?></div>
                            <small class="text-muted">API Calls (24h)</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="fs-5 fw-bold text-warning"><?= number_format($analytics['error_rate'] ?? 0, 2) ?>%</div>
                            <small class="text-muted">Error Rate</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Recent Platform Activity
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($analytics['recent_activity'])): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Activity</th>
                                    <th>User</th>
                                    <th>Tenant</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($analytics['recent_activity'], 0, 10) as $activity): ?>
                                <tr>
                                    <td>
                                        <small><?= date('M j, H:i', strtotime($activity['created_at'] ?? 'now')) ?></small>
                                    </td>
                                    <td>
                                        <?php 
                                        $activityType = $activity['type'] ?? 'unknown';
                                        $badgeClass = $activityType === 'event_created' ? 'bg-success' : 
                                                     ($activityType === 'vote_cast' ? 'bg-info' : 
                                                     ($activityType === 'user_registered' ? 'bg-primary' : 'bg-secondary'));
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= ucfirst(str_replace('_', ' ', $activityType)) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($activity['user_name'] ?? 'System') ?></td>
                                    <td><?= htmlspecialchars($activity['tenant_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($activity['details'] ?? 'No details') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                        <h6>No Recent Activity</h6>
                        <p class="text-muted">Platform activity will appear here as it happens.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function exportAnalytics() {
    // Implementation for exporting analytics data
    console.log('Exporting analytics data');
    // You would typically generate a CSV or PDF report
}

// Initialize charts if Chart.js is available
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart !== 'undefined') {
        // Usage Chart
        const usageCtx = document.getElementById('usageChart');
        if (usageCtx) {
            new Chart(usageCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Events Created',
                        data: [65, 59, 80, 81, 56, 55, 40, 65, 59, 80, 81, 56],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.1,
                        fill: true
                    }, {
                        label: 'Votes Cast',
                        data: [28, 48, 40, 19, 86, 27, 90, 28, 48, 40, 19, 86],
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        // Event Types Chart
        const eventTypesCtx = document.getElementById('eventTypesChart');
        if (eventTypesCtx) {
            new Chart(eventTypesCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Polls', 'Surveys', 'Elections', 'Feedback'],
                    datasets: [{
                        data: [
                            <?= $analytics['event_types']['polls'] ?? 0 ?>,
                            <?= $analytics['event_types']['surveys'] ?? 0 ?>,
                            <?= $analytics['event_types']['elections'] ?? 0 ?>,
                            <?= $analytics['event_types']['feedback'] ?? 0 ?>
                        ],
                        backgroundColor: [
                            '#36A2EB',
                            '#FFCE56',
                            '#FF6384',
                            '#4BC0C0'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }
});
</script>
