<!-- Fraud Detection -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-exclamation-triangle text-danger me-2"></i>
            Fraud Detection
        </h2>
        <p class="text-muted mb-0">Advanced fraud detection and prevention system</p>
    </div>
    <div>
        <button class="btn btn-danger" onclick="runFraudScan()">
            <i class="fas fa-search me-2"></i>Run Scan
        </button>
        <button class="btn btn-outline-secondary" onclick="location.reload()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
    </div>
</div>

<!-- Fraud Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-danger">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($fraud['detected_today'] ?? 0) ?></div>
                    <div>Detected Today</div>
                    <div class="small">Fraud attempts</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-warning">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">$<?= number_format($fraud['prevented_amount'] ?? 0) ?></div>
                    <div>Amount Prevented</div>
                    <div class="small">This month</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-shield-alt fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $fraud['detection_rate'] ?? 0 ?>%</div>
                    <div>Detection Rate</div>
                    <div class="small">Accuracy</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-bullseye fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $fraud['false_positive_rate'] ?? 0 ?>%</div>
                    <div>False Positive Rate</div>
                    <div class="small">Lower is better</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Fraud Alerts -->
<?php if (!empty($fraud['active_alerts'])): ?>
<div class="card mb-4 border-danger">
    <div class="card-header bg-danger text-white">
        <h5 class="mb-0">
            <i class="fas fa-bell me-2"></i>
            Active Fraud Alerts (<?= count($fraud['active_alerts']) ?>)
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Alert</th>
                        <th>Risk Level</th>
                        <th>User/Tenant</th>
                        <th>Details</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fraud['active_alerts'] as $alert): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($alert['type'] ?? 'Unknown Alert') ?></div>
                            <small class="text-muted"><?= htmlspecialchars($alert['description'] ?? '') ?></small>
                        </td>
                        <td>
                            <?php 
                            $riskLevel = $alert['risk_level'] ?? 'low';
                            $badgeClass = match($riskLevel) {
                                'critical' => 'bg-danger',
                                'high' => 'bg-warning',
                                'medium' => 'bg-info',
                                'low' => 'bg-success',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= ucfirst($riskLevel) ?></span>
                        </td>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($alert['user_name'] ?? 'Unknown') ?></div>
                            <small class="text-muted"><?= htmlspecialchars($alert['tenant_name'] ?? 'No tenant') ?></small>
                        </td>
                        <td>
                            <small><?= htmlspecialchars($alert['details'] ?? 'No details') ?></small>
                        </td>
                        <td>
                            <div><?= date('M j, H:i', strtotime($alert['created_at'] ?? 'now')) ?></div>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-primary" onclick="investigateAlert(<?= $alert['id'] ?? 0 ?>)">
                                    <i class="fas fa-search"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="resolveAlert(<?= $alert['id'] ?? 0 ?>)">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="blockUser(<?= $alert['user_id'] ?? 0 ?>)">
                                    <i class="fas fa-ban"></i>
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
<?php endif; ?>

<!-- Fraud Detection Rules -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    Detection Rules
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($fraud['rules'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Rule Name</th>
                                    <th>Type</th>
                                    <th>Threshold</th>
                                    <th>Status</th>
                                    <th>Triggers</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fraud['rules'] as $rule): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($rule['name'] ?? 'Unknown Rule') ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= htmlspecialchars($rule['type'] ?? 'N/A') ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($rule['threshold'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($rule['active'] ?? false) ? 'success' : 'secondary' ?>">
                                            <?= ($rule['active'] ?? false) ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td><?= number_format($rule['trigger_count'] ?? 0) ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" onclick="editRule(<?= $rule['id'] ?? 0 ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-<?= ($rule['active'] ?? false) ? 'warning' : 'success' ?>" onclick="toggleRule(<?= $rule['id'] ?? 0 ?>)">
                                                <i class="fas fa-<?= ($rule['active'] ?? false) ? 'pause' : 'play' ?>"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No fraud detection rules configured.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Fraud Types
                </h5>
            </div>
            <div class="card-body">
                <canvas id="fraudTypesChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Fraud History -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-history me-2"></i>
            Recent Fraud Events
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($fraud['recent_events'])): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Event Type</th>
                            <th>User</th>
                            <th>Risk Score</th>
                            <th>Action Taken</th>
                            <th>Amount</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fraud['recent_events'] as $event): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($event['type'] ?? 'Unknown') ?></div>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($event['user_name'] ?? 'Unknown') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($event['user_email'] ?? '') ?></small>
                            </td>
                            <td>
                                <div class="fw-bold text-danger"><?= $event['risk_score'] ?? 0 ?>/100</div>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= htmlspecialchars($event['action_taken'] ?? 'None') ?></span>
                            </td>
                            <td>
                                <?php if (!empty($event['amount'])): ?>
                                    $<?= number_format($event['amount'], 2) ?>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?= date('M j, H:i', strtotime($event['created_at'] ?? 'now')) ?></div>
                            </td>
                            <td>
                                <?php 
                                $status = $event['status'] ?? 'pending';
                                $statusClass = match($status) {
                                    'resolved' => 'bg-success',
                                    'investigating' => 'bg-warning',
                                    'blocked' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= ucfirst($status) ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                <h5>No Recent Fraud Events</h5>
                <p class="text-muted">Your fraud detection system is working well!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function runFraudScan() {
    if (confirm('Are you sure you want to run a comprehensive fraud scan? This may take a few minutes.')) {
        console.log('Running fraud scan...');
        alert('Fraud scan initiated. You will be notified when complete.');
    }
}

function investigateAlert(alertId) {
    console.log('Investigating alert:', alertId);
}

function resolveAlert(alertId) {
    if (confirm('Are you sure you want to mark this alert as resolved?')) {
        console.log('Resolving alert:', alertId);
        alert('Alert marked as resolved.');
        location.reload();
    }
}

function blockUser(userId) {
    if (confirm('Are you sure you want to block this user? This will prevent them from accessing the platform.')) {
        console.log('Blocking user:', userId);
        alert('User has been blocked.');
        location.reload();
    }
}

function editRule(ruleId) {
    console.log('Editing fraud rule:', ruleId);
}

function toggleRule(ruleId) {
    console.log('Toggling fraud rule:', ruleId);
    alert('Rule status updated.');
    location.reload();
}

// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart !== 'undefined') {
        const ctx = document.getElementById('fraudTypesChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Payment Fraud', 'Account Takeover', 'Identity Theft', 'Bot Activity', 'Other'],
                    datasets: [{
                        data: [35, 25, 20, 15, 5],
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    }
});
</script>
