<!-- Platform Overview -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-chart-pie text-primary me-2"></i>
            Platform Overview
        </h2>
        <p class="text-muted mb-0">High-level platform metrics and system status</p>
    </div>
    <div>
        <button class="btn btn-outline-primary" onclick="location.reload()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
    </div>
</div>

<!-- System Status Cards -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">99.9%</div>
                    <div>System Uptime</div>
                    <div class="small">Last 30 days</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-server fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($overview['total_tenants'] ?? 0) ?></div>
                    <div>Active Tenants</div>
                    <div class="small">Organizations</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-building fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-primary">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($overview['total_users'] ?? 0) ?></div>
                    <div>Platform Users</div>
                    <div class="small">All roles</div>
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
                    <div class="fs-4 fw-semibold"><?= number_format($overview['monthly_revenue'] ?? 0) ?></div>
                    <div>Monthly Revenue</div>
                    <div class="small">GHS</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-money-bill fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Platform Health Dashboard -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-heartbeat me-2"></i>
                    Platform Health Status
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center p-3">
                            <div class="health-indicator mb-2"></div>
                            <h6>Database</h6>
                            <small class="text-success">Operational</small>
                            <div class="mt-2">
                                <small class="text-muted">Response: <?= $overview['db_response_time'] ?? '12' ?>ms</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3">
                            <div class="health-indicator mb-2"></div>
                            <h6>API Services</h6>
                            <small class="text-success">Operational</small>
                            <div class="mt-2">
                                <small class="text-muted">Requests: <?= number_format($overview['api_requests_today'] ?? 15420) ?>/day</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3">
                            <div class="health-indicator mb-2"></div>
                            <h6>File Storage</h6>
                            <small class="text-success">Operational</small>
                            <div class="mt-2">
                                <small class="text-muted">Usage: <?= $overview['storage_usage'] ?? '67' ?>%</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6>Server Load</h6>
                        <div class="progress mb-3">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $overview['server_load'] ?? 35 ?>%">
                                <?= $overview['server_load'] ?? 35 ?>%
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Memory Usage</h6>
                        <div class="progress mb-3">
                            <div class="progress-bar bg-info" role="progressbar" style="width: <?= $overview['memory_usage'] ?? 58 ?>%">
                                <?= $overview['memory_usage'] ?? 58 ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Growth Metrics
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>New Tenants</span>
                        <span class="fw-bold text-success">+<?= $overview['new_tenants_month'] ?? 12 ?></span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 75%"></div>
                    </div>
                    <small class="text-muted">This month</small>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>New Users</span>
                        <span class="fw-bold text-primary">+<?= $overview['new_users_month'] ?? 234 ?></span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 65%"></div>
                    </div>
                    <small class="text-muted">This month</small>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Revenue Growth</span>
                        <span class="fw-bold text-warning">+<?= $overview['revenue_growth'] ?? 18 ?>%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 82%"></div>
                    </div>
                    <small class="text-muted">vs last month</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity & Quick Actions -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Recent Platform Activity
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($overview['recent_activity'])): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($overview['recent_activity'], 0, 5) as $activity): ?>
                        <div class="list-group-item px-0 py-2">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <?php 
                                    $iconClass = match($activity['type'] ?? 'default') {
                                        'tenant_created' => 'fas fa-building text-success',
                                        'user_registered' => 'fas fa-user-plus text-primary',
                                        'payment_received' => 'fas fa-money-bill text-warning',
                                        'security_alert' => 'fas fa-exclamation-triangle text-danger',
                                        default => 'fas fa-info-circle text-info'
                                    };
                                    ?>
                                    <i class="<?= $iconClass ?>"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-semibold"><?= htmlspecialchars($activity['title'] ?? 'Activity') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($activity['description'] ?? 'No description') ?></small>
                                </div>
                                <div class="flex-shrink-0">
                                    <small class="text-muted"><?= date('H:i', strtotime($activity['created_at'] ?? 'now')) ?></small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No recent activity to display.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <button class="btn btn-outline-primary w-100" onclick="location.href='<?= SUPERADMIN_URL ?>/tenants/pending'">
                            <i class="fas fa-clock d-block mb-2"></i>
                            <small>Review Pending<br>Tenants</small>
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-outline-warning w-100" onclick="location.href='<?= SUPERADMIN_URL ?>/security/fraud'">
                            <i class="fas fa-shield-alt d-block mb-2"></i>
                            <small>Security<br>Alerts</small>
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-outline-success w-100" onclick="location.href='<?= SUPERADMIN_URL ?>/financial/revenue'">
                            <i class="fas fa-chart-line d-block mb-2"></i>
                            <small>Revenue<br>Reports</small>
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-outline-info w-100" onclick="location.href='<?= SUPERADMIN_URL ?>/system/maintenance'">
                            <i class="fas fa-tools d-block mb-2"></i>
                            <small>System<br>Maintenance</small>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Platform Statistics -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Platform Usage Statistics
                </h5>
            </div>
            <div class="card-body">
                <canvas id="platformUsageChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize charts if Chart.js is available
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart !== 'undefined') {
        const ctx = document.getElementById('platformUsageChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Active Tenants',
                        data: [12, 15, 18, 22, 25, 28, 32, 35, 38, 42, 45, 48],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.1,
                        fill: true
                    }, {
                        label: 'Total Users',
                        data: [150, 180, 220, 280, 350, 420, 500, 580, 660, 750, 840, 920],
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.1,
                        fill: true
                    }, {
                        label: 'Monthly Revenue ($)',
                        data: [2500, 3200, 4100, 5300, 6800, 8200, 9500, 11200, 12800, 14500, 16200, 18000],
                        borderColor: 'rgb(255, 205, 86)',
                        backgroundColor: 'rgba(255, 205, 86, 0.1)',
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
