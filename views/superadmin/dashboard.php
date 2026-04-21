<!-- Super Admin Dashboard -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-crown text-warning me-2"></i>
            Platform Dashboard
        </h2>
        <p class="text-muted mb-0">Real-time platform analytics and insights</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" onclick="location.reload()">
            <i class="fas fa-sync-alt me-2"></i>Refresh
        </button>
        <button class="btn btn-outline-primary" onclick="window.location.href='<?= SUPERADMIN_URL ?>/platform/analytics'">
            <i class="fas fa-chart-line me-2"></i>Full Analytics
        </button>
    </div>
</div>

<!-- Critical Alerts -->
<?php if (!empty($securityAlerts)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-critical">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Security Alerts</h5>
            <?php foreach (array_slice($securityAlerts, 0, 3) as $alert): ?>
                <div class="mb-2">
                    <strong><?= htmlspecialchars($alert['type']) ?>:</strong>
                    <?= htmlspecialchars($alert['description']) ?>
                    <small class="text-muted">(<?= $alert['created_at'] ?>)</small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Platform Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card text-white bg-primary">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">GHS <?= number_format($stats['total_revenue'] ?? 0, 2) ?></div>
                    <div>Total Platform Revenue</div>
                    <div class="small mt-2">
                        <i class="fas fa-arrow-up me-1"></i>
                        <?= number_format($stats['revenue_growth'] ?? 0, 1) ?>% from last month
                    </div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-2">
                <small>This month: GHS <?= number_format($stats['monthly_revenue'] ?? 0, 2) ?></small>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($stats['successful_transactions'] ?? 0) ?></div>
                    <div>Successful Transactions</div>
                    <div class="small mt-2">
                        <i class="fas fa-check-circle me-1"></i>
                        <?= number_format($stats['monthly_transactions'] ?? 0) ?> this month
                    </div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-receipt fa-2x opacity-75"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-2">
                <small>Today: <?= number_format($stats['today_transactions'] ?? 0) ?> (GHS <?= number_format($stats['today_revenue'] ?? 0, 2) ?>)</small>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($stats['total_votes'] ?? 0) ?></div>
                    <div>Total Votes Cast</div>
                    <div class="small mt-2">
                        <i class="fas fa-calendar-alt me-1"></i>
                        <?= number_format($stats['active_events'] ?? 0) ?> active events
                    </div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-vote-yea fa-2x opacity-75"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-2">
                <small><?= number_format($stats['total_contestants'] ?? 0) ?> contestants competing</small>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card text-white bg-warning">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($stats['active_tenants'] ?? 0) ?></div>
                    <div>Active Tenants</div>
                    <div class="small mt-2">
                        <i class="fas fa-building me-1"></i>
                        <?= number_format($stats['total_tenants'] ?? 0) ?> total
                    </div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-users-cog fa-2x opacity-75"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-2">
                <small><?= number_format($stats['pending_tenants'] ?? 0) ?> pending verification</small>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Stats Row -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <div class="fs-3 fw-bold text-primary"><?= number_format($stats['total_events'] ?? 0) ?></div>
                <div class="text-muted small">Total Events</div>
                <div class="mt-2">
                    <span class="badge bg-success"><?= $stats['active_events'] ?? 0 ?> Active</span>
                    <span class="badge bg-secondary"><?= $stats['draft_events'] ?? 0 ?> Draft</span>
                    <span class="badge bg-dark"><?= $stats['closed_events'] ?? 0 ?> Closed</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <div class="fs-3 fw-bold text-success">GHS <?= number_format($stats['pending_payouts'] ?? 0, 2) ?></div>
                <div class="text-muted small">Pending Payouts</div>
                <div class="mt-2">
                    <small class="text-muted">Paid out: GHS <?= number_format($stats['total_paid_out'] ?? 0, 2) ?></small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <div class="fs-3 fw-bold text-info"><?= number_format($stats['total_users'] ?? 0) ?></div>
                <div class="text-muted small">Platform Users</div>
                <div class="mt-2">
                    <small class="text-success"><i class="fas fa-circle"></i> <?= number_format($stats['active_users'] ?? 0) ?> active</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <div class="fs-3 fw-bold text-warning"><?= number_format($stats['total_contestants'] ?? 0) ?></div>
                <div class="text-muted small">Active Contestants</div>
                <div class="mt-2">
                    <small class="text-muted"><?= number_format($stats['total_categories'] ?? 0) ?> categories</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top Performing Events -->
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy text-warning me-2"></i>
                        Top Performing Events
                    </h5>
                    <a href="<?= SUPERADMIN_URL ?>/events" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($topEvents)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th class="text-end">Votes</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topEvents as $event): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            <a href="<?= SUPERADMIN_URL ?>/events/<?= $event['id'] ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($event['name']) ?>
                                            </a>
                                        </div>
                                        <small class="text-muted"><?= htmlspecialchars($event['tenant_name']) ?></small>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-info"><?= number_format($event['total_votes']) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-success">GHS <?= number_format($event['platform_revenue'], 2) ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No active events yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Top Tenants by Revenue -->
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-star text-warning me-2"></i>
                        Top Tenants by Revenue
                    </h5>
                    <a href="<?= SUPERADMIN_URL ?>/tenants" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($topTenants)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tenant</th>
                                    <th class="text-end">Events</th>
                                    <th class="text-end">Earnings</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topTenants as $tenant): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            <a href="<?= SUPERADMIN_URL ?>/tenants/<?= $tenant['id'] ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($tenant['name']) ?>
                                            </a>
                                        </div>
                                        <small class="text-muted"><?= htmlspecialchars($tenant['email']) ?></small>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-secondary"><?= $tenant['event_count'] ?></span>
                                        <small class="text-success">(<?= $tenant['active_events'] ?> active)</small>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-success">GHS <?= number_format($tenant['total_earned'], 2) ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-building fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No tenant data yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-exchange-alt me-2"></i>
                        Recent Transactions
                    </h5>
                    <a href="<?= SUPERADMIN_URL ?>/financial/transactions" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($recentTransactions)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Event</th>
                                    <th>Tenant</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Votes</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTransactions as $tx): ?>
                                <tr>
                                    <td><span class="badge bg-secondary">#<?= $tx['id'] ?></span></td>
                                    <td>
                                        <div class="small"><?= htmlspecialchars($tx['event_name']) ?></div>
                                    </td>
                                    <td>
                                        <div class="small text-muted"><?= htmlspecialchars($tx['tenant_name']) ?></div>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold">GHS <?= number_format($tx['amount'], 2) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-info"><?= number_format($tx['votes'] ?? 0) ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = match($tx['status']) {
                                            'success' => 'success',
                                            'pending' => 'warning',
                                            'failed' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($tx['status']) ?></span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= date('M j, H:i', strtotime($tx['created_at'])) ?></small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No transactions yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2 col-sm-4">
                        <a href="<?= SUPERADMIN_URL ?>/tenants" class="btn btn-outline-primary w-100 py-3">
                            <i class="fas fa-building fa-2x d-block mb-2"></i>
                            <div class="small">Manage Tenants</div>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <a href="<?= SUPERADMIN_URL ?>/events" class="btn btn-outline-success w-100 py-3">
                            <i class="fas fa-calendar-alt fa-2x d-block mb-2"></i>
                            <div class="small">All Events</div>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <a href="<?= SUPERADMIN_URL ?>/users" class="btn btn-outline-info w-100 py-3">
                            <i class="fas fa-users fa-2x d-block mb-2"></i>
                            <div class="small">Platform Users</div>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <a href="<?= SUPERADMIN_URL ?>/financial/overview" class="btn btn-outline-warning w-100 py-3">
                            <i class="fas fa-money-bill-wave fa-2x d-block mb-2"></i>
                            <div class="small">Financials</div>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <a href="<?= SUPERADMIN_URL ?>/financial/distribution" class="btn btn-outline-secondary w-100 py-3">
                            <i class="fas fa-chart-pie fa-2x d-block mb-2"></i>
                            <div class="small">Distribution</div>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <a href="<?= SUPERADMIN_URL ?>/settings" class="btn btn-outline-dark w-100 py-3">
                            <i class="fas fa-cog fa-2x d-block mb-2"></i>
                            <div class="small">Settings</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stats-card {
    border: none;
    margin-bottom: 1rem;
}

.stats-card .card-body {
    padding: 1.5rem;
}

.stats-card .card-footer {
    font-size: 0.875rem;
}

.card.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

.card-header.bg-white {
    background-color: #fff !important;
    border-bottom: 1px solid #e9ecef;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

.btn-outline-primary:hover,
.btn-outline-success:hover,
.btn-outline-info:hover,
.btn-outline-warning:hover,
.btn-outline-secondary:hover,
.btn-outline-dark:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.2s;
}
</style>

<script>
// Auto-refresh dashboard every 5 minutes
setInterval(function() {
    location.reload();
}, 300000);
</script>
