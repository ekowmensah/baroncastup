<!-- Plan Selection Header -->
<div class="text-center mb-5">
    <h2 class="mb-3">
        <i class="fas fa-crown text-warning me-2"></i>
        Choose Your Plan
    </h2>
    <p class="lead text-muted">Select the perfect plan for your voting events and unlock powerful features</p>
</div>

<!-- Plan Selection Form -->
<form action="<?= ORGANIZER_URL ?>/subscribe" method="POST">
    <div class="row justify-content-center">
        <?php foreach ($plans ?? [] as $plan): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 plan-card <?= $plan['is_popular'] ? 'border-warning' : '' ?>" onclick="selectPlan(<?= $plan['id'] ?>)">
                <?php if ($plan['is_popular']): ?>
                    <div class="card-header bg-warning text-dark text-center py-2">
                        <i class="fas fa-star me-1"></i><strong>Most Popular</strong>
                    </div>
                <?php endif; ?>
                
                <div class="card-body text-center d-flex flex-column">
                    <h4 class="card-title mb-3"><?= htmlspecialchars($plan['name']) ?></h4>
                    <div class="display-6 fw-bold text-primary mb-3">
                        <?= $plan['price_display'] ?>
                    </div>
                    
                    <p class="text-muted mb-4"><?= htmlspecialchars($plan['description']) ?></p>
                    
                    <ul class="list-unstyled text-start mb-4 flex-grow-1">
                        <li class="mb-2">
                            <i class="fas fa-calendar-alt text-info me-2"></i>
                            <strong>Events:</strong> <?= $plan['events_display'] ?>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-users text-success me-2"></i>
                            <strong>Contestants:</strong> <?= $plan['contestants_display'] ?> per event
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-vote-yea text-warning me-2"></i>
                            <strong>Votes:</strong> <?= $plan['votes_display'] ?> per event
                        </li>
                        
                        <!-- Plan Features -->
                        <?php if (!empty($plan['plan_features'])): ?>
                            <?php 
                            $filteredFeatures = array_filter($plan['plan_features'], function($feature) {
                                return strtolower($feature['feature_key']) !== 'storage';
                            });
                            ?>
                            <?php foreach (array_slice($filteredFeatures, 0, 4) as $feature): ?>
                            <li class="mb-2">
                                <?php if ($feature['is_boolean']): ?>
                                    <i class="fas fa-check text-success me-2"></i>
                                    <?= htmlspecialchars($feature['feature_name']) ?>
                                <?php else: ?>
                                    <i class="fas fa-info-circle text-info me-2"></i>
                                    <strong><?= htmlspecialchars($feature['feature_name']) ?>:</strong> 
                                    <?= htmlspecialchars($feature['feature_value']) ?>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if ($plan['trial_days'] > 0): ?>
                        <li class="mb-2">
                            <i class="fas fa-gift text-warning me-2"></i>
                            <strong><?= $plan['trial_days'] ?> days free trial</strong>
                        </li>
                        <?php endif; ?>
                    </ul>
                    
                    <div class="mt-auto">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="plan_id" 
                                   id="plan_<?= $plan['id'] ?>" value="<?= $plan['id'] ?>" required>
                            <label class="form-check-label fw-bold" for="plan_<?= $plan['id'] ?>">
                                Select This Plan
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Action Buttons -->
    <div class="text-center mt-5">
        <a href="<?= ORGANIZER_URL ?>" class="btn btn-secondary me-3">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-check me-2"></i>Subscribe to Selected Plan
        </button>
    </div>
</form>

<!-- Plan Benefits -->
<div class="row mt-5">
    <div class="col-md-4 text-center">
        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
        <h5>Secure & Reliable</h5>
        <p class="text-muted">Enterprise-grade security with 99.9% uptime guarantee</p>
    </div>
    <div class="col-md-4 text-center">
        <i class="fas fa-headset fa-3x text-success mb-3"></i>
        <h5>24/7 Support</h5>
        <p class="text-muted">Get help whenever you need it with our dedicated support team</p>
    </div>
    <div class="col-md-4 text-center">
        <i class="fas fa-sync-alt fa-3x text-info mb-3"></i>
        <h5>Easy Upgrades</h5>
        <p class="text-muted">Change your plan anytime as your needs grow</p>
    </div>
</div>

<style>
.plan-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.plan-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
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
        if (card) {
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
