<?php
require_once __DIR__ . '/../../../src/Helpers/UssdHelper.php';
$fullUssdCode = $tenant['ussd_code'] ? \SmartCast\Helpers\UssdHelper::formatUssdCode($tenant['ussd_code']) : '';
$baseCode = \SmartCast\Helpers\UssdHelper::getBaseCodeFormatted();
?>
<!-- USSD Settings -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-mobile-alt text-primary me-2"></i>
            USSD Settings
        </h2>
        <p class="text-muted mb-0">Manage your USSD voting configuration</p>
    </div>
</div>

<!-- USSD Status Card -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card <?= $tenant['ussd_enabled'] ? 'border-success' : 'border-warning' ?>">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-2">
                            <?php if ($tenant['ussd_code']): ?>
                                <i class="fas fa-hashtag text-primary me-2"></i>
                                Your USSD Code: <span class="badge bg-primary fs-5"><?= htmlspecialchars($fullUssdCode) ?></span>
                            <?php else: ?>
                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                USSD Code Not Assigned
                            <?php endif; ?>
                        </h4>
                        <p class="mb-0">
                            <?php if ($tenant['ussd_enabled']): ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Active
                                </span>
                                <span class="text-muted ms-2">Your USSD voting is currently enabled</span>
                            <?php elseif ($tenant['ussd_code']): ?>
                                <span class="badge bg-warning">
                                    <i class="fas fa-pause-circle me-1"></i>Disabled
                                </span>
                                <span class="text-muted ms-2">Your USSD voting is currently disabled</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-times-circle me-1"></i>Not Configured
                                </span>
                                <span class="text-muted ms-2">Contact support to get a USSD code assigned</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <?php if ($tenant['ussd_code']): ?>
                            <div class="display-6 text-primary">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($tenant['ussd_code']): ?>
<!-- USSD Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-primary">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $stats['total_sessions'] ?? 0 ?></div>
                    <div>Total Sessions</div>
                    <div class="small">All time</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-phone fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $stats['unique_users'] ?? 0 ?></div>
                    <div>Unique Users</div>
                    <div class="small">Different numbers</div>
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
                    <div class="fs-4 fw-semibold"><?= $stats['successful_votes'] ?? 0 ?></div>
                    <div>Successful Votes</div>
                    <div class="small">Completed</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-warning">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-6 fw-semibold">
                        <?php if ($stats['last_session']): ?>
                            <?= date('M d, Y', strtotime($stats['last_session'])) ?>
                        <?php else: ?>
                            Never
                        <?php endif; ?>
                    </div>
                    <div>Last Session</div>
                    <div class="small">Most recent</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-clock fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- USSD Configuration Form -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-cog me-2"></i>USSD Configuration
        </h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= ORGANIZER_URL ?>/settings/ussd/update">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">USSD Code</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="<?= htmlspecialchars($fullUssdCode) ?>" readonly>
                        </div>
                        <small class="text-muted">Assigned by system administrator</small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-control" readonly>
                            <?php if ($tenant['ussd_enabled']): ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Active
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning">
                                    <i class="fas fa-pause-circle me-1"></i>Disabled
                                </span>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted">Contact support to change status</small>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Welcome Message</label>
                <textarea class="form-control" name="ussd_welcome_message" rows="3" placeholder="Welcome to <?= htmlspecialchars($tenant['name']) ?>!"><?= htmlspecialchars($tenant['ussd_welcome_message'] ?? '') ?></textarea>
                <small class="text-muted">
                    Custom message shown when users dial your USSD code. 
                    <strong>Tip:</strong> Keep it short (max 30 characters recommended)
                </small>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Preview:</strong> 
                <div class="mt-2 p-3 bg-white border rounded">
                    <code>
                        <?php 
                        $preview = $tenant['ussd_welcome_message'] ?: "Welcome to " . substr($tenant['name'], 0, 15) . "!";
                        echo htmlspecialchars($preview);
                        ?>
                        <br><br>
                        1. Vote for Nominee<br>
                        2. Vote on an Event<br>
                        3. Create an Event<br>
                        4. Exit
                    </code>
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
                <a href="<?= ORGANIZER_URL ?>/settings" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- How to Use USSD -->
<div class="card mt-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">
            <i class="fas fa-question-circle me-2"></i>How to Use USSD Voting
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary">For Voters:</h6>
                <ol>
                    <li>Dial <strong><?= htmlspecialchars($fullUssdCode) ?></strong> on any phone</li>
                    <li>Follow the menu prompts</li>
                    <li>Select event and contestant</li>
                    <li>Choose vote package</li>
                    <li>Approve mobile money payment</li>
                    <li>Vote recorded!</li>
                </ol>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary">Voting Options:</h6>
                <ul>
                    <li><strong>Quick Vote:</strong> Enter contestant shortcode directly</li>
                    <li><strong>Browse Events:</strong> Navigate through all your events</li>
                    <li><strong>Mobile Money:</strong> Secure payment via Hubtel</li>
                    <li><strong>Works Offline:</strong> No internet required</li>
                </ul>
            </div>
        </div>
        
        <div class="alert alert-warning mt-3">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Note:</strong> USSD voting requires active mobile money accounts. 
            Voters will be prompted to approve payment on their phones.
        </div>
    </div>
</div>

<?php else: ?>
<!-- No USSD Code Assigned -->
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-mobile-alt fa-4x text-muted mb-3"></i>
        <h4>USSD Code Not Assigned</h4>
        <p class="text-muted mb-4">
            You don't have a USSD code assigned yet. Contact our support team to get started with USSD voting.
        </p>
        <a href="mailto:support@smartcast.com" class="btn btn-primary">
            <i class="fas fa-envelope me-2"></i>Contact Support
        </a>
    </div>
</div>
<?php endif; ?>
