<?php 
$content = ob_start(); 
?>

<!-- Payout Methods Management -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Payout Methods
                    </h5>
                    <a href="<?= ORGANIZER_URL ?>/payouts/add-method" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Add Method
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($methods)): ?>
                <div class="row">
                    <?php foreach ($methods as $method): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="card method-card <?= $method['is_default'] ? 'border-primary' : '' ?> <?= !$method['active'] ? 'opacity-50' : '' ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center">
                                        <?php
                                        $methodIcons = [
                                            'bank_transfer' => 'fas fa-university text-primary',
                                            'mobile_money' => 'fas fa-mobile-alt text-success',
                                            'paypal' => 'fab fa-paypal text-info',
                                            'stripe' => 'fab fa-stripe text-warning'
                                        ];
                                        $icon = $methodIcons[$method['method_type']] ?? 'fas fa-credit-card text-secondary';
                                        ?>
                                        <i class="<?= $icon ?> fa-2x me-3"></i>
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($method['method_name']) ?></h6>
                                            <small class="text-muted">
                                                <?= ucwords(str_replace('_', ' ', $method['method_type'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                type="button" 
                                                data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <?php if (!$method['is_default'] && $method['active']): ?>
                                            <li>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="set_default">
                                                    <input type="hidden" name="method_id" value="<?= $method['id'] ?>">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-star me-2"></i>Set as Default
                                                    </button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="editMethod(<?= $method['id'] ?>)">
                                                    <i class="fas fa-edit me-2"></i>Edit
                                                </a>
                                            </li>
                                            <?php if ($method['active']): ?>
                                            <li>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to deactivate this method?')">
                                                    <input type="hidden" name="action" value="deactivate">
                                                    <input type="hidden" name="method_id" value="<?= $method['id'] ?>">
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-ban me-2"></i>Deactivate
                                                    </button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                                
                                <!-- Method Details -->
                                <div class="method-details">
                                    <?php
                                    $details = json_decode($method['account_details'], true);
                                    switch ($method['method_type']):
                                        case 'bank_transfer':
                                    ?>
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Bank Name</small>
                                            <div><?= htmlspecialchars($details['bank_name'] ?? 'N/A') ?></div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Account</small>
                                            <div>****<?= substr($details['account_number'] ?? '', -4) ?></div>
                                        </div>
                                    </div>
                                    <?php break; case 'mobile_money': ?>
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Provider</small>
                                            <div><?= htmlspecialchars($details['provider'] ?? 'N/A') ?></div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Phone</small>
                                            <div>****<?= substr($details['phone_number'] ?? '', -4) ?></div>
                                        </div>
                                    </div>
                                    <?php break; case 'paypal': ?>
                                    <div>
                                        <small class="text-muted">PayPal Email</small>
                                        <div><?= htmlspecialchars($details['email'] ?? 'N/A') ?></div>
                                    </div>
                                    <?php break; case 'stripe': ?>
                                    <div>
                                        <small class="text-muted">Account ID</small>
                                        <div>****<?= substr($details['account_id'] ?? '', -4) ?></div>
                                    </div>
                                    <?php break; endswitch; ?>
                                </div>
                                
                                <!-- Status Badges -->
                                <div class="mt-3">
                                    <?php if ($method['is_default']): ?>
                                    <span class="badge bg-primary me-2">
                                        <i class="fas fa-star me-1"></i>Default
                                    </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($method['is_verified']): ?>
                                    <span class="badge bg-success me-2">
                                        <i class="fas fa-check-circle me-1"></i>Verified
                                    </span>
                                    <?php else: ?>
                                    <span class="badge bg-warning me-2">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Unverified
                                    </span>
                                    <?php endif; ?>
                                    
                                    <?php if (!$method['active']): ?>
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-ban me-1"></i>Inactive
                                    </span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Processing Info -->
                                <div class="mt-3 pt-3 border-top">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <?php
                                        $processingInfo = [
                                            'bank_transfer' => 'Processing: 1-3 business days | Fee: 1.0% + GH₵0.50',
                                            'mobile_money' => 'Processing: Instant to 24 hours | Fee: 1.5% + GH₵0.25',
                                            'paypal' => 'Processing: Instant to 1 business day | Fee: 2.9% + GH₵0.30',
                                            'stripe' => 'Processing: 2-7 business days | Fee: 2.9% + GH₵0.30'
                                        ];
                                        echo $processingInfo[$method['method_type']] ?? 'Processing info not available';
                                        ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Payout Methods</h5>
                    <p class="text-muted">Add a payout method to start receiving payments.</p>
                    <a href="<?= ORGANIZER_URL ?>/payouts/add-method" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Add Your First Method
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Info Cards -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-university fa-2x text-primary mb-2"></i>
                <h6>Bank Transfer</h6>
                <small class="text-muted">1-3 business days<br>1.0% + GH₵0.50</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-mobile-alt fa-2x text-success mb-2"></i>
                <h6>Mobile Money</h6>
                <small class="text-muted">Instant to 24 hours<br>1.5% + GH₵0.25</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fab fa-paypal fa-2x text-info mb-2"></i>
                <h6>PayPal</h6>
                <small class="text-muted">Instant to 1 day<br>2.9% + GH₵0.30</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fab fa-stripe fa-2x text-warning mb-2"></i>
                <h6>Stripe</h6>
                <small class="text-muted">2-7 business days<br>2.9% + GH₵0.30</small>
            </div>
        </div>
    </div>
</div>

<script>
function editMethod(methodId) {
    // TODO: Implement edit functionality
    alert('Edit functionality will be implemented soon!');
}
</script>

<style>
.method-card {
    transition: all 0.3s ease;
    border-radius: 10px;
}

.method-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.method-card.border-primary {
    border-width: 2px;
}

.method-details {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    margin: 1rem 0;
}

.card {
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.badge {
    font-size: 0.75em;
}

@media (max-width: 768px) {
    .col-lg-6 {
        margin-bottom: 1rem;
    }
    
    .method-card {
        margin-bottom: 1rem;
    }
}
</style>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../../layout/organizer_layout.php';
?>
