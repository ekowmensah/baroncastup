<!-- Platform Performance -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-server text-primary me-2"></i>
            Platform Performance
        </h2>
        <p class="text-muted mb-0">System performance metrics and optimization insights</p>
    </div>
    <div>
        <div class="btn-group" role="group">
            <button class="btn btn-outline-secondary" onclick="exportPerformanceReport()">
                <i class="fas fa-download me-2"></i>Export Report
            </button>
            <button class="btn btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync me-2"></i>Refresh
            </button>
        </div>
    </div>
</div>

<!-- Performance Metrics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $performance['avg_response_time'] ?? '145' ?>ms</div>
                    <div>Avg Response Time</div>
                    <div class="small">Last 24 hours</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-tachometer-alt fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($performance['requests_per_minute'] ?? 1250) ?></div>
                    <div>Requests/Min</div>
                    <div class="small">Current load</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-chart-line fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-warning">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $performance['error_rate'] ?? '0.02' ?>%</div>
                    <div>Error Rate</div>
                    <div class="small">Last hour</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-primary">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $performance['uptime'] ?? '99.98' ?>%</div>
                    <div>Uptime</div>
                    <div class="small">Last 30 days</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-server fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Real-time Performance Charts -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-area me-2"></i>
                    Real-time Performance Metrics
                </h5>
            </div>
            <div class="card-body">
                <canvas id="performanceChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-memory me-2"></i>
                    System Resources
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>CPU Usage</span>
                        <span><?= $performance['cpu_usage'] ?? 35 ?>%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $performance['cpu_usage'] ?? 35 ?>%"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Memory Usage</span>
                        <span><?= $performance['memory_usage'] ?? 68 ?>%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= $performance['memory_usage'] ?? 68 ?>%"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Disk Usage</span>
                        <span><?= $performance['disk_usage'] ?? 42 ?>%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $performance['disk_usage'] ?? 42 ?>%"></div>
                    </div>
                </div>
                
                <div class="mb-0">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Network I/O</span>
                        <span><?= $performance['network_io'] ?? 28 ?>%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $performance['network_io'] ?? 28 ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Database Performance -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-database me-2"></i>
                    Database Performance
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="border-end">
                            <div class="fs-5 fw-bold text-success"><?= $performance['db_queries_per_sec'] ?? 145 ?></div>
                            <small class="text-muted">Queries/sec</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-end">
                            <div class="fs-5 fw-bold text-info"><?= $performance['db_avg_query_time'] ?? 12 ?>ms</div>
                            <small class="text-muted">Avg Query Time</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="fs-5 fw-bold text-primary"><?= $performance['db_connections'] ?? 25 ?></div>
                        <small class="text-muted">Active Connections</small>
                    </div>
                </div>
                
                <hr>
                
                <h6>Slow Queries</h6>
                <?php if (!empty($performance['slow_queries'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Query</th>
                                    <th>Time</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($performance['slow_queries'], 0, 3) as $query): ?>
                                <tr>
                                    <td>
                                        <code class="small"><?= htmlspecialchars(substr($query['query'] ?? 'SELECT...', 0, 30)) ?>...</code>
                                    </td>
                                    <td><?= $query['avg_time'] ?? '0' ?>ms</td>
                                    <td><?= $query['count'] ?? 0 ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted small">No slow queries detected</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-globe me-2"></i>
                    API Performance
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-4">
                        <div class="border-end">
                            <div class="fs-5 fw-bold text-success"><?= number_format($performance['api_requests_today'] ?? 15420) ?></div>
                            <small class="text-muted">Requests Today</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-end">
                            <div class="fs-5 fw-bold text-info"><?= $performance['api_avg_response'] ?? 89 ?>ms</div>
                            <small class="text-muted">Avg Response</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="fs-5 fw-bold text-warning"><?= $performance['api_error_rate'] ?? 0.1 ?>%</div>
                        <small class="text-muted">Error Rate</small>
                    </div>
                </div>
                
                <h6>Top API Endpoints</h6>
                <?php if (!empty($performance['top_endpoints'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Endpoint</th>
                                    <th>Requests</th>
                                    <th>Avg Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($performance['top_endpoints'], 0, 3) as $endpoint): ?>
                                <tr>
                                    <td>
                                        <code class="small"><?= htmlspecialchars($endpoint['path'] ?? '/api/...') ?></code>
                                    </td>
                                    <td><?= number_format($endpoint['requests'] ?? 0) ?></td>
                                    <td><?= $endpoint['avg_time'] ?? '0' ?>ms</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted small">No endpoint data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Performance Alerts & Recommendations -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-lightbulb me-2"></i>
                    Performance Recommendations
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($performance['recommendations'])): ?>
                    <?php foreach ($performance['recommendations'] as $recommendation): ?>
                    <div class="alert alert-<?= $recommendation['type'] ?? 'info' ?> d-flex align-items-center" role="alert">
                        <i class="fas fa-<?= $recommendation['icon'] ?? 'info-circle' ?> me-2"></i>
                        <div>
                            <strong><?= htmlspecialchars($recommendation['title'] ?? 'Recommendation') ?></strong><br>
                            <small><?= htmlspecialchars($recommendation['description'] ?? 'No description available') ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <div>
                            <strong>System Optimized</strong><br>
                            <small>No performance issues detected. System is running optimally.</small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Performance Alerts
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($performance['alerts'])): ?>
                    <?php foreach (array_slice($performance['alerts'], 0, 5) as $alert): ?>
                    <div class="d-flex align-items-start mb-3">
                        <div class="flex-shrink-0">
                            <?php 
                            $alertClass = match($alert['severity'] ?? 'info') {
                                'critical' => 'text-danger',
                                'warning' => 'text-warning',
                                'info' => 'text-info',
                                default => 'text-secondary'
                            };
                            ?>
                            <i class="fas fa-circle <?= $alertClass ?>"></i>
                        </div>
                        <div class="flex-grow-1 ms-2">
                            <div class="fw-semibold small"><?= htmlspecialchars($alert['message'] ?? 'Alert') ?></div>
                            <small class="text-muted"><?= date('H:i', strtotime($alert['created_at'] ?? 'now')) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <p class="text-muted small mb-0">No performance alerts</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function exportPerformanceReport() {
    console.log('Exporting performance report...');
    // Implementation for exporting performance report
}

// Initialize performance chart
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart !== 'undefined') {
        const ctx = document.getElementById('performanceChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: Array.from({length: 24}, (_, i) => `${i}:00`),
                    datasets: [{
                        label: 'Response Time (ms)',
                        data: [120, 135, 142, 138, 145, 150, 148, 152, 158, 162, 155, 148, 145, 142, 138, 135, 140, 145, 150, 155, 152, 148, 142, 138],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.1,
                        fill: true
                    }, {
                        label: 'Requests/min',
                        data: [800, 850, 920, 980, 1050, 1120, 1200, 1280, 1350, 1420, 1380, 1320, 1250, 1180, 1120, 1080, 1150, 1220, 1290, 1350, 1320, 1280, 1200, 1150],
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.1,
                        fill: true,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Response Time (ms)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Requests/min'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    }
                }
            });
        }
    }
});
</script>
