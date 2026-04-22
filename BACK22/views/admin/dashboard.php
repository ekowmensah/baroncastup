<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Dashboard</h2>
        <p class="text-muted mb-0">Welcome back! Here's what's happening with your events.</p>
    </div>
    <div>
        <a href="<?= APP_URL ?>/admin/events/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create Event
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stats-card text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0" id="stat-total_events"><?= $stats['total_events'] ?? 0 ?></h3>
                        <p class="mb-0">Total Events</p>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-calendar-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stats-card success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0" id="stat-active_events"><?= $stats['active_events'] ?? 0 ?></h3>
                        <p class="mb-0">Active Events</p>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-play-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stats-card warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0" id="stat-total_contestants"><?= $stats['total_contestants'] ?? 0 ?></h3>
                        <p class="mb-0">Contestants</p>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stats-card info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0" id="stat-total_votes"><?= number_format($stats['total_votes'] ?? 0) ?></h3>
                        <p class="mb-0">Total Votes</p>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-vote-yea fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Events -->
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-calendar text-primary me-2"></i>
                    Recent Events
                </h5>
                <a href="<?= APP_URL ?>/admin/events" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($recentEvents)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Status</th>
                                    <th>Start Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentEvents as $event): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars($event['name']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($event['code']) ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $event['status'] === 'active' ? 'success' : ($event['status'] === 'draft' ? 'warning' : 'secondary') ?>">
                                                <?= ucfirst($event['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?= date('M j, Y H:i', strtotime($event['start_date'])) ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= APP_URL ?>/admin/events/<?= $event['id'] ?>/edit" 
                                                   class="btn btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= APP_URL ?>/events/<?= $event['id'] ?>" 
                                                   class="btn btn-outline-success" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-plus fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Events Yet</h5>
                        <p class="text-muted mb-3">Create your first voting event to get started.</p>
                        <a href="<?= APP_URL ?>/admin/events/create" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create Event
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Activity Feed -->
    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-history text-info me-2"></i>
                    Recent Activity
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($recentLogs)): ?>
                    <div class="activity-feed">
                        <?php foreach ($recentLogs as $log): ?>
                            <div class="activity-item mb-3">
                                <div class="d-flex">
                                    <div class="activity-icon me-3">
                                        <?php
                                        $iconClass = match($log['action']) {
                                            'login' => 'fas fa-sign-in-alt text-success',
                                            'logout' => 'fas fa-sign-out-alt text-secondary',
                                            'event_created' => 'fas fa-plus-circle text-primary',
                                            'event_updated' => 'fas fa-edit text-warning',
                                            'contestant_created' => 'fas fa-user-plus text-info',
                                            'vote_cast' => 'fas fa-vote-yea text-success',
                                            default => 'fas fa-circle text-muted'
                                        };
                                        ?>
                                        <i class="<?= $iconClass ?>"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="activity-content">
                                            <strong><?= ucfirst(str_replace('_', ' ', $log['action'])) ?></strong>
                                            <?php if ($log['user_email']): ?>
                                                <br><small class="text-muted">by <?= htmlspecialchars($log['user_email']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            <?= date('M j, H:i', strtotime($log['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-history fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No recent activity</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-bolt text-warning me-2"></i>
                    Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= APP_URL ?>/admin/events/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create Event
                    </a>
                    <a href="<?= APP_URL ?>/admin/contestants/create" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus me-2"></i>Add Contestant
                    </a>
                    <a href="<?= APP_URL ?>/admin/reports" class="btn btn-outline-info">
                        <i class="fas fa-chart-bar me-2"></i>View Reports
                    </a>
                    <a href="<?= APP_URL ?>/admin/settings" class="btn btn-outline-secondary">
                        <i class="fas fa-cog me-2"></i>Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Dashboard Widgets -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-line text-success me-2"></i>
                    Voting Trends (Last 7 Days)
                </h6>
            </div>
            <div class="card-body">
                <canvas id="votingTrendsChart" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-trophy text-warning me-2"></i>
                    Top Performing Events
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($recentEvents)): ?>
                    <?php foreach (array_slice($recentEvents, 0, 3) as $index => $event): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($event['name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($event['code']) ?></small>
                            </div>
                            <div class="text-end">
                                <div class="badge bg-<?= $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'success') ?>">
                                    #<?= $index + 1 ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-trophy fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No events to rank yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 10px;
}

.stats-card.success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.stats-card.warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stats-card.info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stats-icon {
    opacity: 0.8;
}

.activity-feed {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    border-left: 2px solid #e9ecef;
    padding-left: 1rem;
    position: relative;
}

.activity-item:last-child {
    border-left: none;
}

.activity-icon {
    position: absolute;
    left: -8px;
    background: white;
    padding: 2px;
}

.admin-dashboard {
    /* Marker for auto-refresh */
}
</style>

<script>
// Auto-refresh dashboard stats every 30 seconds
setInterval(function() {
    refreshDashboardStats();
}, 30000);

function refreshDashboardStats() {
    // This would fetch updated stats via AJAX
    console.log('Refreshing dashboard stats...');
}

// Initialize voting trends chart (placeholder)
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('votingTrendsChart');
    if (ctx) {
        // This would use Chart.js or similar library
        ctx.getContext('2d').fillText('Voting trends chart would be implemented here', 10, 100);
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
