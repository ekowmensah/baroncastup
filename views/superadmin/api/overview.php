<!-- API Usage Overview -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-chart-bar text-primary me-2"></i>
            API Usage Overview
        </h2>
        <p class="text-muted mb-0">Monitor API performance, usage statistics, and health metrics</p>
    </div>
    <div>
        <button class="btn btn-outline-primary" onclick="refreshMetrics()">
            <i class="fas fa-sync me-2"></i>Refresh Metrics
        </button>
    </div>
</div>

<!-- API Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-primary">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($api['total_requests'] ?? 0) ?></div>
                    <div>Total Requests</div>
                    <div class="small">Last 24 hours</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-chart-line fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $api['success_rate'] ?? 0 ?>%</div>
                    <div>Success Rate</div>
                    <div class="small">2xx responses</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $api['avg_response_time'] ?? 0 ?>ms</div>
                    <div>Avg Response Time</div>
                    <div class="small">Last hour</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-stopwatch fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-warning">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($api['active_keys'] ?? 0) ?></div>
                    <div>Active API Keys</div>
                    <div class="small">Currently valid</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-key fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- API Health Status -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-heartbeat me-2"></i>
                    API Health Status
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php 
                    $endpoints = $api['endpoints'] ?? [
                        ['name' => 'Authentication', 'status' => 'healthy', 'response_time' => 45],
                        ['name' => 'Events', 'status' => 'healthy', 'response_time' => 120],
                        ['name' => 'Voting', 'status' => 'healthy', 'response_time' => 89],
                        ['name' => 'Users', 'status' => 'degraded', 'response_time' => 340],
                        ['name' => 'Analytics', 'status' => 'healthy', 'response_time' => 156],
                        ['name' => 'Webhooks', 'status' => 'healthy', 'response_time' => 78]
                    ];
                    ?>
                    <?php foreach ($endpoints as $endpoint): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <?php 
                                $statusClass = match($endpoint['status']) {
                                    'healthy' => 'text-success',
                                    'degraded' => 'text-warning',
                                    'down' => 'text-danger',
                                    default => 'text-secondary'
                                };
                                $statusIcon = match($endpoint['status']) {
                                    'healthy' => 'fas fa-check-circle',
                                    'degraded' => 'fas fa-exclamation-triangle',
                                    'down' => 'fas fa-times-circle',
                                    default => 'fas fa-question-circle'
                                };
                                ?>
                                <i class="<?= $statusIcon ?> <?= $statusClass ?> fa-lg"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-semibold"><?= htmlspecialchars($endpoint['name']) ?></div>
                                <small class="text-muted"><?= $endpoint['response_time'] ?>ms avg</small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Request Distribution
                </h5>
            </div>
            <div class="card-body">
                <canvas id="requestDistributionChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- API Usage Charts -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-area me-2"></i>
                    API Traffic Over Time
                </h5>
            </div>
            <div class="card-body">
                <canvas id="apiTrafficChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error Rates
                </h5>
            </div>
            <div class="card-body">
                <canvas id="errorRatesChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top API Consumers -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-trophy me-2"></i>
                    Top API Consumers
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($api['top_consumers'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Requests</th>
                                    <th>Success Rate</th>
                                    <th>Last Active</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($api['top_consumers'], 0, 10) as $consumer): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($consumer['name'] ?? 'Unknown') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($consumer['key_preview'] ?? '') ?></small>
                                    </td>
                                    <td><?= number_format($consumer['request_count'] ?? 0) ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($consumer['success_rate'] ?? 0) > 95 ? 'success' : (($consumer['success_rate'] ?? 0) > 90 ? 'warning' : 'danger') ?>">
                                            <?= $consumer['success_rate'] ?? 0 ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <small><?= date('M j, H:i', strtotime($consumer['last_active'] ?? 'now')) ?></small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No API usage data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-route me-2"></i>
                    Most Used Endpoints
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($api['popular_endpoints'])): ?>
                    <?php foreach (array_slice($api['popular_endpoints'], 0, 8) as $endpoint): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="fw-semibold"><?= htmlspecialchars($endpoint['path'] ?? '/unknown') ?></div>
                            <small class="text-muted"><?= strtoupper($endpoint['method'] ?? 'GET') ?></small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold"><?= number_format($endpoint['count'] ?? 0) ?></div>
                            <small class="text-muted">requests</small>
                        </div>
                    </div>
                    <div class="progress mb-3" style="height: 6px;">
                        <div class="progress-bar" role="progressbar" style="width: <?= ($endpoint['percentage'] ?? 0) ?>%"></div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No endpoint usage data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent API Activity -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-stream me-2"></i>
            Recent API Activity
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($api['recent_activity'])): ?>
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Method</th>
                            <th>Endpoint</th>
                            <th>Client</th>
                            <th>Status</th>
                            <th>Response Time</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($api['recent_activity'], 0, 20) as $activity): ?>
                        <tr>
                            <td>
                                <small><?= date('H:i:s', strtotime($activity['timestamp'] ?? 'now')) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-<?= match($activity['method'] ?? 'GET') {
                                    'GET' => 'primary',
                                    'POST' => 'success',
                                    'PUT' => 'warning',
                                    'DELETE' => 'danger',
                                    default => 'secondary'
                                } ?>"><?= $activity['method'] ?? 'GET' ?></span>
                            </td>
                            <td>
                                <code><?= htmlspecialchars($activity['endpoint'] ?? '/unknown') ?></code>
                            </td>
                            <td>
                                <small><?= htmlspecialchars($activity['client_name'] ?? 'Unknown') ?></small>
                            </td>
                            <td>
                                <?php 
                                $status = $activity['status_code'] ?? 200;
                                $statusClass = $status < 300 ? 'success' : ($status < 400 ? 'warning' : 'danger');
                                ?>
                                <span class="badge bg-<?= $statusClass ?>"><?= $status ?></span>
                            </td>
                            <td>
                                <small><?= $activity['response_time'] ?? 0 ?>ms</small>
                            </td>
                            <td>
                                <code><?= htmlspecialchars($activity['ip_address'] ?? 'Unknown') ?></code>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-code fa-3x text-muted mb-3"></i>
                <h5>No Recent API Activity</h5>
                <p class="text-muted">API activity will appear here as requests are made.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function refreshMetrics() {
    console.log('Refreshing API metrics...');
    location.reload();
}

// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart !== 'undefined') {
        // Request Distribution Chart
        const distributionCtx = document.getElementById('requestDistributionChart');
        if (distributionCtx) {
            new Chart(distributionCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Events API', 'Voting API', 'Users API', 'Analytics API', 'Other'],
                    datasets: [{
                        data: [35, 25, 20, 15, 5],
                        backgroundColor: ['#36A2EB', '#FFCE56', '#FF6384', '#4BC0C0', '#9966FF']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
        
        // API Traffic Chart
        const trafficCtx = document.getElementById('apiTrafficChart');
        if (trafficCtx) {
            new Chart(trafficCtx, {
                type: 'line',
                data: {
                    labels: Array.from({length: 24}, (_, i) => `${i}:00`),
                    datasets: [{
                        label: 'Requests per Hour',
                        data: [120, 135, 142, 138, 145, 150, 148, 152, 158, 162, 155, 148, 145, 142, 138, 135, 140, 145, 150, 155, 152, 148, 142, 138],
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
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        // Error Rates Chart
        const errorCtx = document.getElementById('errorRatesChart');
        if (errorCtx) {
            new Chart(errorCtx, {
                type: 'bar',
                data: {
                    labels: ['2xx', '3xx', '4xx', '5xx'],
                    datasets: [{
                        label: 'Response Codes',
                        data: [85, 10, 4, 1],
                        backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }
    }
});
</script>
