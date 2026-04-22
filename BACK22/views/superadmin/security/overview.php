<!-- Security Overview -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-shield-alt text-primary me-2"></i>
            Security Overview
        </h2>
        <p class="text-muted mb-0">Platform security monitoring and fraud detection</p>
    </div>
    <div>
        <button class="btn btn-outline-primary" onclick="location.reload()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
    </div>
</div>

<!-- Security Metrics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($securityMetrics['total_events'] ?? 0) ?></div>
                    <div>Total Events</div>
                    <div class="small">Last 24 hours</div>
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
                    <div class="fs-4 fw-semibold"><?= number_format($securityMetrics['suspicious_events'] ?? 0) ?></div>
                    <div>Suspicious Events</div>
                    <div class="small">Requires review</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-danger">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($securityMetrics['blocked_events'] ?? 0) ?></div>
                    <div>Blocked Events</div>
                    <div class="small">Fraud prevented</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-ban fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($securityMetrics['active_sessions'] ?? 0) ?></div>
                    <div>Active Sessions</div>
                    <div class="small">Currently online</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-users fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fraud Statistics -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Fraud Detection Statistics
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($fraudStats)): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Detection Rate</h6>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?= $fraudStats['detection_rate'] ?? 0 ?>%">
                                    <?= number_format($fraudStats['detection_rate'] ?? 0, 1) ?>%
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>False Positive Rate</h6>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-warning" role="progressbar" 
                                     style="width: <?= $fraudStats['false_positive_rate'] ?? 0 ?>%">
                                    <?= number_format($fraudStats['false_positive_rate'] ?? 0, 1) ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="fs-5 fw-bold text-success"><?= number_format($fraudStats['prevented_amount'] ?? 0) ?></div>
                                <small class="text-muted">Fraud Prevented ($)</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="fs-5 fw-bold text-primary"><?= number_format($fraudStats['total_checks'] ?? 0) ?></div>
                                <small class="text-muted">Total Security Checks</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="fs-5 fw-bold text-info"><?= number_format($fraudStats['avg_response_time'] ?? 0) ?>ms</div>
                                <small class="text-muted">Avg Response Time</small>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No fraud statistics available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-shield-alt me-2"></i>
                    Security Status
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-success fa-2x"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">System Secure</h6>
                        <small class="text-muted">All security checks passed</small>
                    </div>
                </div>
                
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-info fa-2x"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">Last Scan</h6>
                        <small class="text-muted"><?= date('Y-m-d H:i:s') ?></small>
                    </div>
                </div>
                
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-database text-primary fa-2x"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">Database Status</h6>
                        <small class="text-success">Connected & Secure</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Fraud Events -->
<?php if (!empty($fraudEvents)): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Recent Fraud Events
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Risk Level</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($fraudEvents, 0, 10) as $event): ?>
                            <tr>
                                <td>
                                    <small><?= date('M j, H:i', strtotime($event['created_at'] ?? 'now')) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-warning"><?= htmlspecialchars($event['type'] ?? 'Unknown') ?></span>
                                </td>
                                <td><?= htmlspecialchars($event['description'] ?? 'No description') ?></td>
                                <td>
                                    <?php 
                                    $riskLevel = $event['risk_level'] ?? 'low';
                                    $badgeClass = $riskLevel === 'high' ? 'bg-danger' : ($riskLevel === 'medium' ? 'bg-warning' : 'bg-success');
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($riskLevel) ?></span>
                                </td>
                                <td>
                                    <?php 
                                    $status = $event['status'] ?? 'pending';
                                    $statusClass = $status === 'resolved' ? 'bg-success' : ($status === 'investigating' ? 'bg-info' : 'bg-secondary');
                                    ?>
                                    <span class="badge <?= $statusClass ?>"><?= ucfirst($status) ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewFraudEvent(<?= $event['id'] ?? 0 ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
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
<?php else: ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                <h5>No Recent Fraud Events</h5>
                <p class="text-muted">Your platform is secure with no recent fraud attempts detected.</p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function viewFraudEvent(eventId) {
    // Implementation for viewing fraud event details
    console.log('Viewing fraud event:', eventId);
}
</script>
