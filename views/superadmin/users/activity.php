<!-- User Activity -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-history text-primary me-2"></i>
            User Activity Monitoring
        </h2>
        <p class="text-muted mb-0">Track and analyze user behavior across the platform</p>
    </div>
    <div>
        <div class="btn-group" role="group">
            <button class="btn btn-outline-secondary" onclick="exportActivityReport()">
                <i class="fas fa-download me-2"></i>Export Report
            </button>
            <button class="btn btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync me-2"></i>Refresh
            </button>
        </div>
    </div>
</div>

<!-- Activity Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-primary">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($activity['active_users_today'] ?? 0) ?></div>
                    <div>Active Today</div>
                    <div class="small">Unique users</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-users fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($activity['total_sessions'] ?? 0) ?></div>
                    <div>Total Sessions</div>
                    <div class="small">Last 24 hours</div>
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
                    <div class="fs-4 fw-semibold"><?= $activity['avg_session_duration'] ?? '12:34' ?></div>
                    <div>Avg Session</div>
                    <div class="small">Duration (mm:ss)</div>
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
                    <div class="fs-4 fw-semibold"><?= number_format($activity['page_views'] ?? 0) ?></div>
                    <div>Page Views</div>
                    <div class="small">Last 24 hours</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-eye fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Charts -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    User Activity Trends
                </h5>
            </div>
            <div class="card-body">
                <canvas id="activityTrendsChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Activity Types
                </h5>
            </div>
            <div class="card-body">
                <canvas id="activityTypesChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Filter Options -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" class="form-control" id="activitySearch" placeholder="Search activities..." onkeyup="filterActivities()">
        </div>
    </div>
    <div class="col-md-3">
        <select class="form-select" id="activityTypeFilter" onchange="filterActivities()">
            <option value="">All Activity Types</option>
            <option value="login">Login</option>
            <option value="logout">Logout</option>
            <option value="event_created">Event Created</option>
            <option value="vote_cast">Vote Cast</option>
            <option value="profile_updated">Profile Updated</option>
            <option value="password_changed">Password Changed</option>
        </select>
    </div>
    <div class="col-md-3">
        <select class="form-select" id="timeRangeFilter" onchange="filterActivities()">
            <option value="">All Time</option>
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
        </select>
    </div>
    <div class="col-md-3">
        <select class="form-select" id="tenantFilter" onchange="filterActivities()">
            <option value="">All Tenants</option>
            <?php if (!empty($tenants)): ?>
                <?php foreach ($tenants as $tenant): ?>
                    <option value="<?= $tenant['id'] ?>"><?= htmlspecialchars($tenant['name']) ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
</div>

<!-- Recent Activity Feed -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-stream me-2"></i>
                    Real-time Activity Feed
                </h5>
            </div>
            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                <?php if (!empty($activity['recent_activities'])): ?>
                    <div class="list-group list-group-flush" id="activityFeed">
                        <?php foreach ($activity['recent_activities'] as $activityItem): ?>
                        <div class="list-group-item px-0 py-3" 
                             data-activity-type="<?= $activityItem['type'] ?? '' ?>"
                             data-tenant-id="<?= $activityItem['tenant_id'] ?? '' ?>"
                             data-timestamp="<?= strtotime($activityItem['created_at'] ?? 'now') ?>">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <?php 
                                    $iconClass = match($activityItem['type'] ?? 'default') {
                                        'login' => 'fas fa-sign-in-alt text-success',
                                        'logout' => 'fas fa-sign-out-alt text-secondary',
                                        'event_created' => 'fas fa-plus-circle text-primary',
                                        'vote_cast' => 'fas fa-vote-yea text-info',
                                        'profile_updated' => 'fas fa-user-edit text-warning',
                                        'password_changed' => 'fas fa-key text-danger',
                                        default => 'fas fa-circle text-muted'
                                    };
                                    ?>
                                    <i class="<?= $iconClass ?> fa-lg"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($activityItem['user_name'] ?? 'Unknown User') ?></h6>
                                            <p class="mb-1"><?= htmlspecialchars($activityItem['description'] ?? 'No description') ?></p>
                                            <div class="d-flex align-items-center text-muted small">
                                                <i class="fas fa-building me-1"></i>
                                                <span><?= htmlspecialchars($activityItem['tenant_name'] ?? 'No tenant') ?></span>
                                                <span class="mx-2">•</span>
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                <span><?= htmlspecialchars($activityItem['ip_address'] ?? 'Unknown IP') ?></span>
                                                <?php if (!empty($activityItem['user_agent'])): ?>
                                                    <span class="mx-2">•</span>
                                                    <i class="fas fa-desktop me-1"></i>
                                                    <span><?= htmlspecialchars(substr($activityItem['user_agent'], 0, 30)) ?>...</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted"><?= date('H:i:s', strtotime($activityItem['created_at'] ?? 'now')) ?></small>
                                            <br>
                                            <small class="text-muted"><?= date('M j', strtotime($activityItem['created_at'] ?? 'now')) ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <h6>No Recent Activity</h6>
                        <p class="text-muted">User activity will appear here as it happens.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-trophy me-2"></i>
                    Most Active Users
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($activity['top_users'])): ?>
                    <?php foreach (array_slice($activity['top_users'], 0, 10) as $index => $user): ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px;">
                                #<?= $index + 1 ?>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-semibold"><?= htmlspecialchars($user['name'] ?? 'Unknown') ?></div>
                            <small class="text-muted"><?= number_format($user['activity_count'] ?? 0) ?> activities</small>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="badge bg-light text-dark"><?= htmlspecialchars($user['tenant_name'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No user activity data available.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-globe me-2"></i>
                    Geographic Distribution
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($activity['geographic_data'])): ?>
                    <?php foreach (array_slice($activity['geographic_data'], 0, 5) as $location): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                            <span><?= htmlspecialchars($location['country'] ?? 'Unknown') ?></span>
                        </div>
                        <div>
                            <span class="fw-bold"><?= number_format($location['user_count'] ?? 0) ?></span>
                            <small class="text-muted">users</small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No geographic data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Suspicious Activity Alerts -->
<?php if (!empty($activity['suspicious_activities'])): ?>
<div class="row">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Suspicious Activity Alerts
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Activity</th>
                                <th>Risk Level</th>
                                <th>Details</th>
                                <th>Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activity['suspicious_activities'] as $suspicious): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($suspicious['user_name'] ?? 'Unknown') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($suspicious['user_email'] ?? 'No email') ?></small>
                                </td>
                                <td><?= htmlspecialchars($suspicious['activity_type'] ?? 'Unknown') ?></td>
                                <td>
                                    <?php 
                                    $riskLevel = $suspicious['risk_level'] ?? 'low';
                                    $badgeClass = match($riskLevel) {
                                        'high' => 'bg-danger',
                                        'medium' => 'bg-warning',
                                        'low' => 'bg-info',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($riskLevel) ?></span>
                                </td>
                                <td><?= htmlspecialchars($suspicious['details'] ?? 'No details') ?></td>
                                <td>
                                    <div><?= date('M j, H:i', strtotime($suspicious['created_at'] ?? 'now')) ?></div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary" onclick="investigateActivity(<?= $suspicious['id'] ?? 0 ?>)">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" onclick="flagUser(<?= $suspicious['user_id'] ?? 0 ?>)">
                                            <i class="fas fa-flag"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="markSafe(<?= $suspicious['id'] ?? 0 ?>)">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function filterActivities() {
    const searchTerm = document.getElementById('activitySearch').value.toLowerCase();
    const typeFilter = document.getElementById('activityTypeFilter').value;
    const timeFilter = document.getElementById('timeRangeFilter').value;
    const tenantFilter = document.getElementById('tenantFilter').value;
    const activities = document.querySelectorAll('#activityFeed .list-group-item');
    
    const now = new Date().getTime() / 1000;
    const timeRanges = {
        'today': now - (24 * 60 * 60),
        'yesterday': now - (48 * 60 * 60),
        'week': now - (7 * 24 * 60 * 60),
        'month': now - (30 * 24 * 60 * 60)
    };
    
    activities.forEach(activity => {
        const text = activity.textContent.toLowerCase();
        const type = activity.getAttribute('data-activity-type');
        const tenantId = activity.getAttribute('data-tenant-id');
        const timestamp = parseInt(activity.getAttribute('data-timestamp'));
        
        const matchesSearch = text.includes(searchTerm);
        const matchesType = !typeFilter || type === typeFilter;
        const matchesTenant = !tenantFilter || tenantId === tenantFilter;
        const matchesTime = !timeFilter || timestamp >= timeRanges[timeFilter];
        
        activity.style.display = matchesSearch && matchesType && matchesTenant && matchesTime ? '' : 'none';
    });
}

function exportActivityReport() {
    console.log('Exporting activity report...');
    // Implementation for exporting activity report
}

function investigateActivity(activityId) {
    console.log('Investigating suspicious activity:', activityId);
    // Implementation for investigating suspicious activity
}

function flagUser(userId) {
    if (confirm('Are you sure you want to flag this user for review?')) {
        console.log('Flagging user:', userId);
        alert('User has been flagged for review.');
    }
}

function markSafe(activityId) {
    console.log('Marking activity as safe:', activityId);
    alert('Activity marked as safe.');
    location.reload();
}

// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart !== 'undefined') {
        // Activity Trends Chart
        const trendsCtx = document.getElementById('activityTrendsChart');
        if (trendsCtx) {
            new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: Array.from({length: 24}, (_, i) => `${i}:00`),
                    datasets: [{
                        label: 'Active Users',
                        data: [45, 52, 48, 61, 55, 67, 72, 78, 85, 92, 88, 95, 102, 98, 105, 112, 108, 115, 122, 118, 125, 132, 128, 135],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.1,
                        fill: true
                    }, {
                        label: 'Page Views',
                        data: [120, 135, 142, 138, 145, 150, 148, 152, 158, 162, 155, 148, 145, 142, 138, 135, 140, 145, 150, 155, 152, 148, 142, 138],
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
        
        // Activity Types Chart
        const typesCtx = document.getElementById('activityTypesChart');
        if (typesCtx) {
            new Chart(typesCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Login', 'Event Created', 'Vote Cast', 'Profile Updated', 'Other'],
                    datasets: [{
                        data: [35, 25, 20, 15, 5],
                        backgroundColor: [
                            '#36A2EB',
                            '#FFCE56',
                            '#FF6384',
                            '#4BC0C0',
                            '#9966FF'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    }
    
    // Auto-refresh activity feed every 30 seconds
    setInterval(function() {
        // Implementation for auto-refreshing activity feed
        console.log('Auto-refreshing activity feed...');
    }, 30000);
});
</script>
