<!-- Dashboard Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Dashboard</h2>
        <p class="text-muted mb-0">Welcome back! Here's what's happening with your events.</p>
    </div>
    <div>
        <a href="<?= ORGANIZER_URL ?>/events/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create Event
        </a>
    </div>
</div>

<!-- Subscription Status Alert -->
<?php if (!$currentSubscription): ?>
<div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
        <div class="flex-grow-1">
            <h5 class="alert-heading mb-1">No Active Subscription</h5>
            <p class="mb-2">You don't have an active subscription plan. Subscribe to unlock all features and start creating unlimited events.</p>
            <a href="<?= ORGANIZER_URL ?>/subscribe" class="btn btn-warning btn-sm">
                <i class="fas fa-crown me-2"></i>Choose a Plan
            </a>
        </div>
    </div>
    <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
</div>
<?php else: ?>
<!-- Current Subscription Info -->
<div class="alert alert-info mb-4" role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-crown fa-2x me-3 text-warning"></i>
        <div class="flex-grow-1">
            <h6 class="alert-heading mb-1">
                <?= htmlspecialchars($currentSubscription['plan_name']) ?> Plan
                <?php if ($currentSubscription['status'] === 'trial'): ?>
                    <span class="badge bg-success ms-2">Trial</span>
                <?php else: ?>
                    <span class="badge bg-primary ms-2"><?= ucfirst($currentSubscription['status']) ?></span>
                <?php endif; ?>
            </h6>
            <div class="row">
                <div class="col-md-3">
                    <small class="text-muted">Events:</small><br>
                    <strong><?= is_null($currentSubscription['max_events']) ? 'Unlimited' : $currentSubscription['max_events'] ?></strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Contestants per Event:</small><br>
                    <strong><?= is_null($currentSubscription['max_contestants_per_event']) ? 'Unlimited' : number_format($currentSubscription['max_contestants_per_event']) ?></strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Votes per Event:</small><br>
                    <strong><?= is_null($currentSubscription['max_votes_per_event']) ? 'Unlimited' : number_format($currentSubscription['max_votes_per_event']) ?></strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Price:</small><br>
                    <strong>GH₵<?= number_format($currentSubscription['price'], 2) ?>/<?= $currentSubscription['billing_cycle'] ?></strong>
                </div>
            </div>
            <div class="mt-3">
                <a href="<?= ORGANIZER_URL ?>/switch-plan" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-exchange-alt me-2"></i>Switch Plan
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- USSD Code Display -->
<?php 
if (isset($tenant) && $tenant['ussd_code']): 
    require_once __DIR__ . '/../../src/Helpers/UssdHelper.php';
    $fullUssdCode = \SmartCast\Helpers\UssdHelper::formatUssdCode($tenant['ussd_code']);
?>
<div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-white rounded-circle p-3 me-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-mobile-alt fa-2x" style="color: #667eea;"></i>
                    </div>
                    <div>
                        <h5 class="text-white mb-1">Your USSD Voting Code</h5>
                        <p class="text-white-50 mb-0">Share this code with your voters for easy mobile voting</p>
                    </div>
                </div>
                
                <div class="d-flex align-items-center">
                    <div class="bg-white rounded px-4 py-3 me-3">
                        <h1 class="mb-0 fw-bold" style="color: #667eea; font-size: 2.5rem; letter-spacing: 2px;">
                            <?= htmlspecialchars($fullUssdCode) ?>
                        </h1>
                    </div>
                    
                    <?php if ($tenant['ussd_enabled']): ?>
                        <span class="badge bg-success px-3 py-2" style="font-size: 0.9rem;">
                            <i class="fas fa-check-circle me-1"></i>Active
                        </span>
                    <?php else: ?>
                        <span class="badge bg-warning px-3 py-2" style="font-size: 0.9rem;">
                            <i class="fas fa-pause-circle me-1"></i>Disabled
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="mt-3">
                    <button class="btn btn-light btn-sm me-2" onclick="copyUssdCode('<?= htmlspecialchars($fullUssdCode) ?>')">
                        <i class="fas fa-copy me-1"></i>Copy Code
                    </button>
                    <a href="<?= ORGANIZER_URL ?>/settings/ussd" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-cog me-1"></i>Manage USSD
                    </a>
                </div>
            </div>
            
            <div class="col-md-4 text-center">
                <div class="bg-white bg-opacity-10 rounded p-3">
                    <div class="text-white mb-2">
                        <i class="fas fa-info-circle me-2"></i>How to Vote
                    </div>
                    <div class="text-white-50 small text-start">
                        <ol class="mb-0 ps-3">
                            <li>Dial <strong class="text-white"><?= htmlspecialchars($fullUssdCode) ?></strong></li>
                            <li>Select event</li>
                            <li>Choose contestant</li>
                            <li>Complete payment</li>
                            <li>Vote recorded!</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyUssdCode(code) {
    navigator.clipboard.writeText(code).then(function() {
        // Show success message
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-light');
        
        setTimeout(function() {
            btn.innerHTML = originalHTML;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-light');
        }, 2000);
    }).catch(function(err) {
        alert('Failed to copy code. Please copy manually: ' + code);
    });
}
</script>
<?php elseif (isset($tenant)): ?>
<!-- No USSD Code Assigned -->
<div class="alert alert-info mb-4" role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-mobile-alt fa-2x me-3"></i>
        <div class="flex-grow-1">
            <h6 class="alert-heading mb-1">USSD Voting Available</h6>
            <p class="mb-2">Enable mobile voting for your events! Contact support to get your USSD code assigned.</p>
            <a href="mailto:support@smartcast.com" class="btn btn-info btn-sm">
                <i class="fas fa-envelope me-2"></i>Request USSD Code
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($stats['total_events'] ?? 0) ?></div>
                    <div>Total Events</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                </div>
            </div>
            <div class="c-chart-wrapper mt-3 mx-3" style="height:70px;">
                <!-- Chart placeholder -->
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card success text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($stats['active_events'] ?? 0) ?></div>
                    <div>Active Events</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-play-circle fa-2x opacity-75"></i>
                </div>
            </div>
            <div class="c-chart-wrapper mt-3 mx-3" style="height:70px;">
                <!-- Chart placeholder -->
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card warning text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($stats['total_contestants'] ?? 0) ?></div>
                    <div>Contestants</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-users fa-2x opacity-75"></i>
                </div>
            </div>
            <div class="c-chart-wrapper mt-3 mx-3" style="height:70px;">
                <!-- Chart placeholder -->
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card info text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($stats['total_votes'] ?? 0) ?></div>
                    <div>Total Votes</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-vote-yea fa-2x opacity-75"></i>
                </div>
            </div>
            <div class="c-chart-wrapper mt-3 mx-3" style="height:70px;">
                <!-- Chart placeholder -->
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Events -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-calendar-alt me-2"></i>Recent Events
            </div>
            <div class="card-body">
                <?php if (!empty($recentEvents)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Status</th>
                                    <th>Votes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentEvents as $event): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($event['name']) ?></div>
                                            <div class="small text-medium-emphasis"><?= htmlspecialchars($event['code']) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $event['status'] === 'active' ? 'success' : ($event['status'] === 'draft' ? 'warning' : 'secondary') ?>">
                                                <?= ucfirst($event['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= number_format($event['total_votes'] ?? 0) ?></div>
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
                        <a href="<?= ORGANIZER_URL ?>/events/create" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create Event
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Financial Overview -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-money-bill me-2"></i>Financial Overview
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="border-end">
                            <div class="fs-5 fw-semibold text-success">GH₵<?= number_format($balance['available'] ?? 0, 2) ?></div>
                            <div class="text-uppercase text-medium-emphasis small">Available</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-end">
                            <div class="fs-5 fw-semibold text-info">GH₵<?= number_format($balance['pending'] ?? 0, 2) ?></div>
                            <div class="text-uppercase text-medium-emphasis small">Pending</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="fs-5 fw-semibold text-primary">GH₵<?= number_format($balance['total_earned'] ?? 0, 2) ?></div>
                        <div class="text-uppercase text-medium-emphasis small">Total Earned</div>
                    </div>
                </div>
                
                <hr class="mt-0">
                
                <?php if (!empty($recentVotes)): ?>
                    <div class="small text-medium-emphasis">Recent Activity</div>
                    <?php foreach (array_slice($recentVotes, 0, 3) as $vote): ?>
                        <div class="d-flex justify-content-between align-items-center py-2">
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($vote['contestant_name']) ?></div>
                                <div class="small text-medium-emphasis"><?= htmlspecialchars($vote['event_name']) ?></div>
                            </div>
                            <div class="text-end">
                                <div class="fw-semibold text-success">+GH₵<?= number_format($vote['amount'], 2) ?></div>
                                <div class="small text-medium-emphasis"><?= $vote['quantity'] ?> vote<?= $vote['quantity'] > 1 ? 's' : '' ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-chart-line fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No financial activity yet</p>
                    </div>
                <?php endif; ?>
                
                <div class="mt-3">
                    <a href="<?= ORGANIZER_URL ?>/financial/overview" class="btn btn-outline-primary btn-sm w-100">
                        View Financial Details
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bolt me-2"></i>Quick Actions
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="<?= ORGANIZER_URL ?>/events/create" class="btn btn-outline-primary w-100">
                            <i class="fas fa-plus me-2"></i>Create Event
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="<?= ORGANIZER_URL ?>/contestants/create" class="btn btn-outline-success w-100">
                            <i class="fas fa-user-plus me-2"></i>Add Contestant
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="<?= ORGANIZER_URL ?>/voting/live" class="btn btn-outline-info w-100">
                            <i class="fas fa-broadcast-tower me-2"></i>Live Results
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="<?= ORGANIZER_URL ?>/reports" class="btn btn-outline-warning w-100">
                            <i class="fas fa-chart-bar me-2"></i>View Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh dashboard every 30 seconds
setInterval(function() {
    // This would refresh dashboard stats via AJAX
    console.log('Refreshing dashboard...');
}, 30000);
</script>
