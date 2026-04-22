<!-- Plan Switch Header -->
<div class="text-center mb-3">
    <h4 class="mb-2">
        <i class="fas fa-exchange-alt text-primary me-2"></i>
        Switch Your Plan
    </h4>
    <?php if ($currentSubscription): ?>
    <div class="badge bg-info mb-2">
        <i class="fas fa-info-circle me-1"></i>
        Currently: <strong><?= htmlspecialchars($currentSubscription['plan_name']) ?></strong>
    </div>
    <?php endif; ?>
</div>

<!-- Plan Switching Form -->
<form action="<?= ORGANIZER_URL ?>/switch-plan" method="POST">
    <div class="row justify-content-center">
        <?php foreach ($plans ?? [] as $plan): ?>
        <?php $isCurrent = $currentSubscription && $currentSubscription['plan_id'] == $plan['id']; ?>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 plan-card <?= $plan['is_popular'] ? 'border-warning' : '' ?> <?= $isCurrent ? 'border-success current-plan' : '' ?>" 
                 onclick="<?= $isCurrent ? '' : 'selectPlan(' . $plan['id'] . ')' ?>">
                
                <?php if ($isCurrent): ?>
                    <div class="card-header bg-success text-white text-center py-1">
                        <small><i class="fas fa-check me-1"></i><strong>Current</strong></small>
                    </div>
                <?php elseif ($plan['is_popular']): ?>
                    <div class="card-header bg-warning text-dark text-center py-1">
                        <small><i class="fas fa-star me-1"></i><strong>Popular</strong></small>
                    </div>
                <?php endif; ?>
                
                <div class="card-body text-center d-flex flex-column p-3">
                    <h6 class="card-title mb-2"><?= htmlspecialchars($plan['name']) ?></h6>
                    <div class="h5 fw-bold text-primary mb-2">
                        <?= $plan['price_display'] ?>
                    </div>
                    
                    <p class="text-muted small mb-3"><?= htmlspecialchars($plan['description']) ?></p>
                    
                    <ul class="list-unstyled text-start mb-3 flex-grow-1 small">
                        <li class="mb-1">
                            <i class="fas fa-calendar-alt text-info me-1"></i>
                            <strong>Events:</strong> <?= $plan['events_display'] ?>
                        </li>
                        <li class="mb-1">
                            <i class="fas fa-users text-success me-1"></i>
                            <strong>Contestants:</strong> <?= $plan['contestants_display'] ?> per event
                        </li>
                        <li class="mb-1">
                            <i class="fas fa-vote-yea text-warning me-1"></i>
                            <strong>Votes:</strong> <?= $plan['votes_display'] ?> per event
                        </li>
                        
                        <!-- Plan Features -->
                        <?php if (!empty($plan['plan_features'])): ?>
                            <?php 
                            $filteredFeatures = array_filter($plan['plan_features'], function($feature) {
                                return strtolower($feature['feature_key']) !== 'storage';
                            });
                            ?>
                            <?php foreach (array_slice($filteredFeatures, 0, 2) as $feature): ?>
                            <li class="mb-1">
                                <?php if ($feature['is_boolean']): ?>
                                    <i class="fas fa-check text-success me-1"></i>
                                    <small><?= htmlspecialchars($feature['feature_name']) ?></small>
                                <?php else: ?>
                                    <i class="fas fa-info-circle text-info me-1"></i>
                                    <small><strong><?= htmlspecialchars($feature['feature_name']) ?>:</strong> 
                                    <?= htmlspecialchars($feature['feature_value']) ?></small>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if ($plan['trial_days'] > 0 && !$currentSubscription): ?>
                        <li class="mb-1">
                            <i class="fas fa-gift text-warning me-1"></i>
                            <small><strong><?= $plan['trial_days'] ?> days free trial</strong></small>
                        </li>
                        <?php endif; ?>
                    </ul>
                    
                    <div class="mt-auto">
                        <?php if ($isCurrent): ?>
                            <div class="badge bg-success">
                                <i class="fas fa-check me-1"></i>Current
                            </div>
                        <?php else: ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="plan_id" 
                                       id="plan_<?= $plan['id'] ?>" value="<?= $plan['id'] ?>" required>
                                <label class="form-check-label small fw-bold" for="plan_<?= $plan['id'] ?>">
                                    <?php if ($currentSubscription && $plan['price'] > $currentSubscription['price']): ?>
                                        <i class="fas fa-arrow-up text-success me-1"></i>Upgrade
                                    <?php elseif ($currentSubscription && $plan['price'] < $currentSubscription['price']): ?>
                                        <i class="fas fa-arrow-down text-info me-1"></i>Downgrade
                                    <?php else: ?>
                                        Switch
                                    <?php endif; ?>
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Action Buttons -->
    <div class="text-center mt-3">
        <a href="<?= ORGANIZER_URL ?>" class="btn btn-outline-secondary btn-sm me-2">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-exchange-alt me-1"></i>Switch Plan
        </button>
    </div>
</form>

<!-- Plan Switch Benefits -->
<div class="row mt-3">
    <div class="col-md-4 text-center">
        <i class="fas fa-sync-alt fa-2x text-primary mb-2"></i>
        <h6>Instant Switch</h6>
        <small class="text-muted">Changes take effect immediately</small>
    </div>
    <div class="col-md-4 text-center">
        <i class="fas fa-shield-check fa-2x text-success mb-2"></i>
        <h6>No Data Loss</h6>
        <small class="text-muted">All events and data remain safe</small>
    </div>
    <div class="col-md-4 text-center">
        <i class="fas fa-calculator fa-2x text-info mb-2"></i>
        <h6>Prorated Billing</h6>
        <small class="text-muted">Fair billing adjustments</small>
    </div>
</div>

<style>
.plan-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.plan-card:not(.current-plan):hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.plan-card.current-plan {
    opacity: 0.8;
    cursor: default;
}

.plan-card.selected {
    border-color: #28a745 !important;
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
}

.plan-card .form-check-input:checked {
    background-color: #28a745;
    border-color: #28a745;
}
</style>

<script>
function selectPlan(planId) {
    // Remove selected class from all cards
    document.querySelectorAll('.plan-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Select the radio button
    const radio = document.getElementById('plan_' + planId);
    if (radio) {
        radio.checked = true;
        
        // Add selected class to the card
        const card = radio.closest('.plan-card');
        if (card && !card.classList.contains('current-plan')) {
            card.classList.add('selected');
        }
    }
}

// Initialize plan selection styling
document.addEventListener('DOMContentLoaded', function() {
    // Add change handlers to radio buttons
    document.querySelectorAll('input[name="plan_id"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                selectPlan(this.value);
            }
        });
    });
});
</script>
