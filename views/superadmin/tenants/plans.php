<!-- Enhanced Subscription Plans Management -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-layer-group text-primary me-2"></i>
            Subscription Plans
        </h2>
        <p class="text-muted mb-0">Manage subscription plans with fee rules and unlimited features</p>
    </div>
    <div>
        <button class="btn btn-primary" onclick="createPlan()">
            <i class="fas fa-plus me-2"></i>Create Plan
        </button>
        <button class="btn btn-outline-secondary" onclick="location.reload()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
    </div>
</div>

<!-- Plan Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-primary">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= count($plans ?? []) ?></div>
                    <div>Total Plans</div>
                    <div class="small">Available plans</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-layer-group fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">
                        <?= count(array_filter($plans ?? [], function($p) { return ($p['status'] ?? '') === 'active'; })) ?>
                    </div>
                    <div>Active Plans</div>
                    <div class="small">Currently offered</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">
                        <?php 
                        $totalSubscriptions = 0;
                        foreach ($plans ?? [] as $plan) {
                            $totalSubscriptions += $plan['subscriber_count'] ?? 0;
                        }
                        echo number_format($totalSubscriptions);
                        ?>
                    </div>
                    <div>Total Subscriptions</div>
                    <div class="small">Active subscribers</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-users fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-warning">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">GH₵<?php 
                        $totalRevenue = 0;
                        foreach ($plans ?? [] as $plan) {
                            $totalRevenue += ($plan['price'] ?? 0) * (($plan['usage_stats']['active_subscriptions'] ?? 0));
                        }
                        echo number_format($totalRevenue);
                        ?></div>
                    <div>Monthly Revenue</div>
                    <div class="small">From subscriptions</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-money-bill fa-2x opacity-75"></i>
            </div>
        </div>
    </div>
</div>

<!-- Subscription Plans -->
<div class="row">
    <?php if (!empty($plans)): ?>
    <?php foreach ($plans as $plan): ?>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-light border-0 py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0 fw-bold"><?= htmlspecialchars($plan['name']) ?></h6>
                        <?php if ($plan['is_popular']): ?>
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-star me-1"></i>Popular
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card-body p-3">
                    <div class="text-center mb-2">
                        <?php if ($plan['price'] == 0): ?>
                            <div class="h5 text-success mb-0">Free</div>
                        <?php else: ?>
                            <div class="h5 text-primary mb-0">
                                <div class="h5 text-primary mb-0">GH₵<?= number_format($plan['price'], 2) ?><small class="text-muted">/<?= $plan['billing_cycle'] ?></small></div>
                            </div>
                        <?php endif; ?>
                        <p class="text-muted small mb-2"><?= htmlspecialchars($plan['description'] ?? '') ?></p>
                    </div>
                    
                    <ul class="list-unstyled mb-3 flex-grow-1 small">
                        <li class="mb-1">
                            <i class="fas fa-calendar-alt text-info me-2"></i>
                            <strong>Events:</strong> <?= is_null($plan['max_events']) ? 'Unlimited' : $plan['max_events'] ?>
                        </li>
                        <li class="mb-1">
                            <i class="fas fa-users text-success me-2"></i>
                            <strong>Contestants:</strong> <?= is_null($plan['max_contestants_per_event']) ? 'Unlimited' : number_format($plan['max_contestants_per_event']) ?> per event
                        </li>
                        <li class="mb-1">
                            <i class="fas fa-vote-yea text-warning me-2"></i>
                            <strong>Votes:</strong> <?= is_null($plan['max_votes_per_event']) ? 'Unlimited' : number_format($plan['max_votes_per_event']) ?> per event
                        </li>
                        <?php if ($plan['fee_rule_id']): ?>
                        <li class="mb-1">
                            <i class="fas fa-percentage text-danger me-2"></i>
                            <strong>Fee:</strong> 
                            <?php if ($plan['rule_type'] === 'percentage'): ?>
                                <?= $plan['percentage_rate'] ?>%
                            <?php elseif ($plan['rule_type'] === 'fixed'): ?>
                                $<?= number_format($plan['fixed_amount'], 2) ?>
                            <?php endif; ?>
                        </li>
                        <?php endif; ?>
                    </ul>
                    
                    <!-- Subscriber Count -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">Subscribers:</small>
                        <span class="badge bg-primary"><?= $plan['usage_stats']['active_subscriptions'] ?? 0 ?></span>
                    </div>
                    
                    <!-- Plan Features -->
                    <?php if (!empty($plan['features'])): ?>
                    <div class="mb-3">
                        <h6 class="text-muted">Features:</h6>
                        <div>
                            <?php foreach (array_slice($plan['features'], 0, 6) as $feature): ?>
                            <div class="mb-1">
                                <small class="text-muted d-block">
                                    <?php if ($feature['is_boolean']): ?>
                                        <i class="fas fa-check text-success me-1"></i>
                                    <?php else: ?>
                                        <i class="fas fa-info-circle text-info me-1"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($feature['feature_name']) ?>
                                    <?php if (!$feature['is_boolean'] && $feature['feature_value']): ?>
                                        <span class="text-primary ms-1"><?= htmlspecialchars($feature['feature_value']) ?></span>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mt-auto">
                        <div class="btn-group w-100" role="group">
                            <button class="btn btn-outline-primary btn-sm" onclick="editPlan(<?= $plan['id'] ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-outline-info btn-sm" onclick="viewPlanStats(<?= $plan['id'] ?>)">
                                <i class="fas fa-chart-bar"></i> Stats
                            </button>
                            <?php if ($plan['can_delete']): ?>
                                <button class="btn btn-outline-danger btn-sm" onclick="deletePlan(<?= $plan['id'] ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            <?php else: ?>
                                <button class="btn btn-outline-secondary btn-sm" disabled title="Cannot delete: Has active subscriptions">
                                    <i class="fas fa-lock"></i> Protected
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                <h5>No Subscription Plans</h5>
                <p class="text-muted">Create your first subscription plan to get started.</p>
                <button class="btn btn-primary" onclick="createPlan()">
                    <i class="fas fa-plus me-2"></i>Create First Plan
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Plan Usage Analytics -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Plan Subscription Trends
                </h5>
            </div>
            <div class="card-body">
                <canvas id="planTrendsChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Plan Distribution
                </h5>
            </div>
            <div class="card-body">
                <canvas id="planDistributionChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Plan Creation Modal -->
<div class="modal fade" id="createPlanModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Subscription Plan</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <form action="<?= SUPERADMIN_URL ?>/tenants/plans/create" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Plan Name *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Price *</label>
                                <div class="input-group">
                                    <div class="input-group"><span class="input-group-text">GH₵</span><input type="number" class="form-control" name="price" step="0.01" min="0" required></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Billing Cycle *</label>
                                <select class="form-select" name="billing_cycle" required>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                    <option value="lifetime">Lifetime</option>
                                    <option value="free">Free</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Fee Rule</label>
                                <select class="form-select" name="fee_rule_id">
                                    <option value="">No fee rule</option>
                                    <?php foreach ($feeRules as $rule): ?>
                                    <option value="<?= $rule['id'] ?>">
                                        <?php if ($rule['rule_type'] === 'percentage'): ?>
                                            <?= $rule['percentage_rate'] ?>%
                                        <?php elseif ($rule['rule_type'] === 'fixed'): ?>
                                            GH₵<?= number_format($rule['fixed_amount'], 2) ?>
                                        <?php endif; ?>
                                        <?php if ($rule['tenant_id']): ?>
                                            (Tenant Specific)
                                        <?php else: ?>
                                            (Global)
                                        <?php endif; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Max Events</label>
                                <input type="number" class="form-control" name="max_events" min="1" placeholder="Leave empty for unlimited">
                                <small class="text-muted">Leave empty for unlimited</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Max Contestants per Event</label>
                                <input type="number" class="form-control" name="max_contestants_per_event" min="1" placeholder="Leave empty for unlimited">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Max Votes per Event</label>
                                <input type="number" class="form-control" name="max_votes_per_event" min="1" placeholder="Leave empty for unlimited">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Trial Days</label>
                                <input type="number" class="form-control" name="trial_days" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" class="form-control" name="sort_order" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_popular" id="is_popular">
                                    <label class="form-check-label" for="is_popular">
                                        Mark as Popular Plan
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function createPlan() {
    console.log('Create plan button clicked');
    const modalElement = document.getElementById('createPlanModal');
    if (!modalElement) {
        console.error('Create plan modal not found');
        showAlert('Modal not found. Please refresh the page.', 'error');
        return;
    }
    
    // Reset form
    const form = modalElement.querySelector('form');
    if (form) {
        form.reset();
    }
    
    try {
        const modal = new coreui.Modal(modalElement);
        modal.show();
        console.log('Modal shown successfully');
    } catch (error) {
        console.error('Error showing modal:', error);
        showAlert('Error opening create plan modal', 'error');
    }
}

function editPlan(planId) {
    console.log('Edit plan clicked for ID:', planId);
    console.log('Testing with Pro plan (ID: 5)');
    
    if (!planId) {
        console.error('No plan ID provided');
        showAlert('Invalid plan ID', 'error');
        return;
    }
    
    const url = `<?= SUPERADMIN_URL ?>/tenants/plans/${planId}/get`;
    console.log('Fetching plan data from:', url);
    
    // Load plan data from backend
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Plan data received:', data);
            if (data.success && data.plan) {
                const plan = data.plan;
                
                // Create edit modal dynamically
                showEditPlanModal(plan);
            } else {
                console.error('Failed to load plan data:', data);
                showAlert(data.message || 'Failed to load plan data', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading plan:', error);
            showAlert('Error loading plan data: ' + error.message, 'error');
        });
}

function showEditPlanModal(plan) {
    // Create edit modal HTML
    const modalHtml = `
        <div class="modal fade" id="editPlanModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Subscription Plan</h5>
                        <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
                    </div>
                    <form id="editPlanForm" action="<?= SUPERADMIN_URL ?>/tenants/plans/${plan.id}/update" method="POST">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Plan Name *</label>
                                        <input type="text" class="form-control" name="name" value="${plan.name}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Price *</label>
                                        <div class="input-group">
                                            <div class="input-group"><span class="input-group-text">GH₵</span><input type="number" class="form-control" name="price" step="0.01" min="0" value="${plan.price}" required></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Billing Cycle *</label>
                                        <select class="form-select" name="billing_cycle" required>
                                            <option value="monthly" ${plan.billing_cycle === 'monthly' ? 'selected' : ''}>Monthly</option>
                                            <option value="yearly" ${plan.billing_cycle === 'yearly' ? 'selected' : ''}>Yearly</option>
                                            <option value="lifetime" ${plan.billing_cycle === 'lifetime' ? 'selected' : ''}>Lifetime</option>
                                            <option value="free" ${plan.billing_cycle === 'free' ? 'selected' : ''}>Free</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Fee Rule</label>
                                        <select class="form-select" name="fee_rule_id" id="editFeeRuleSelect">
                                            <option value="">No fee rule</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="2">${plan.description || ''}</textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Max Events</label>
                                        <input type="number" class="form-control" name="max_events" min="1" value="${plan.max_events || ''}" placeholder="Leave empty for unlimited">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Max Contestants per Event</label>
                                        <input type="number" class="form-control" name="max_contestants_per_event" min="1" value="${plan.max_contestants_per_event || ''}" placeholder="Leave empty for unlimited">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Max Votes per Event</label>
                                        <input type="number" class="form-control" name="max_votes_per_event" min="1" value="${plan.max_votes_per_event || ''}" placeholder="Leave empty for unlimited">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Trial Days</label>
                                        <input type="number" class="form-control" name="trial_days" min="0" value="${plan.trial_days || 0}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Sort Order</label>
                                        <input type="number" class="form-control" name="sort_order" min="0" value="${plan.sort_order || 0}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input" type="checkbox" name="is_popular" id="edit_is_popular" ${plan.is_popular ? 'checked' : ''}>
                                            <label class="form-check-label" for="edit_is_popular">
                                                Mark as Popular Plan
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Plan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('editPlanModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new coreui.Modal(document.getElementById('editPlanModal'));
    modal.show();
    
    // Populate fee rules dropdown after modal is shown
    setTimeout(() => {
        populateFeeRulesDropdown(plan.fee_rule_id);
    }, 100);
    
    // Add form submission handler
    const form = document.getElementById('editPlanForm');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        
        // Debug: Log form data
        console.log('Form action:', form.action);
        console.log('Form data entries:');
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }
        
        // Show loading state
        submitBtn.textContent = 'Updating...';
        submitBtn.disabled = true;
        
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response redirected:', response.redirected);
            console.log('Response headers:', response.headers);
            
            if (response.redirected) {
                // Follow redirect for success
                console.log('Following redirect to:', response.url);
                window.location.href = response.url;
                return;
            }
            
            // Check content type
            const contentType = response.headers.get('content-type');
            console.log('Content-Type:', contentType);
            
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // Response is not JSON, likely an error page
                return response.text().then(text => {
                    console.error('Non-JSON response received:', text);
                    throw new Error('Server returned an error page instead of JSON');
                });
            }
        })
        .then(data => {
            console.log('Response data:', data);
            if (data && data.success) {
                modal.hide();
                showAlert(data.message || 'Plan updated successfully!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else if (data) {
                showAlert(data.message || 'Failed to update plan', 'error');
            }
        })
        .catch(error => {
            console.error('Error updating plan:', error);
            showAlert('Error updating plan: ' + error.message, 'error');
        })
        .finally(() => {
            // Reset button state
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });
}

function populateFeeRulesDropdown(selectedFeeRuleId) {
    const feeRules = <?= json_encode($feeRules ?? []) ?>;
    const select = document.getElementById('editFeeRuleSelect');
    
    if (!select) return;
    
    // Clear existing options except the first one
    select.innerHTML = '<option value="">No fee rule</option>';
    
    // Add fee rule options
    feeRules.forEach(rule => {
        const option = document.createElement('option');
        option.value = rule.id;
        option.selected = rule.id == selectedFeeRuleId;
        
        let feeText = '';
        if (rule.rule_type === 'percentage') {
            feeText = rule.percentage_rate + '% Fee';
        } else if (rule.rule_type === 'fixed') {
            feeText = 'GH₵' + parseFloat(rule.fixed_amount).toFixed(2) + ' Fee';
        }
        
        option.textContent = feeText + (rule.tenant_id ? ' (Tenant Specific)' : ' (Global)');
        select.appendChild(option);
    });
}

function viewPlanStats(planId) {
    console.log('View stats clicked for plan ID:', planId);
    
    if (!planId) {
        console.error('No plan ID provided');
        showAlert('Invalid plan ID', 'error');
        return;
    }
    
    const url = `<?= SUPERADMIN_URL ?>/tenants/plans/${planId}/stats`;
    console.log('Fetching plan stats from:', url);
    
    // Load plan statistics from backend
    fetch(url)
        .then(response => {
            console.log('Stats response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Plan stats received:', data);
            if (data.success && data.stats) {
                showPlanStatsModal(data.stats, data.plan);
            } else {
                console.error('Failed to load plan stats:', data);
                showAlert(data.message || 'Failed to load plan statistics', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading plan stats:', error);
            showAlert('Error loading plan statistics: ' + error.message, 'error');
        });
}

function showPlanStatsModal(stats, plan) {
    const modalHtml = `
        <div class="modal fade" id="planStatsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Plan Statistics - ${plan.name}</h5>
                        <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <div class="h4 mb-0">${stats.active_subscriptions || 0}</div>
                                        <div class="small">Active Subscriptions</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <div class="h4 mb-0">$${(stats.monthly_revenue || 0).toLocaleString()}</div>
                                        <div class="small">Monthly Revenue</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <div class="h4 mb-0">${stats.total_events || 0}</div>
                                        <div class="small">Total Events</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <div class="h4 mb-0">${stats.churn_rate || 0}%</div>
                                        <div class="small">Churn Rate</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Recent Subscriptions</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Tenant</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${(stats.recent_subscriptions || []).map(sub => `
                                                <tr>
                                                    <td>${sub.tenant_name}</td>
                                                    <td>${new Date(sub.created_at).toLocaleDateString()}</td>
                                                    <td><span class="badge bg-${sub.status === 'active' ? 'success' : 'secondary'}">${sub.status}</span></td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Usage Metrics</h6>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span>Avg Events per Tenant:</span>
                                        <strong>${stats.avg_events_per_tenant || 0}</strong>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span>Avg Contestants per Event:</span>
                                        <strong>${stats.avg_contestants_per_event || 0}</strong>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span>Total Votes:</span>
                                        <strong>${(stats.total_votes || 0).toLocaleString()}</strong>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span>Revenue per Subscription:</span>
                                        <strong>$${stats.revenue_per_subscription || 0}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="exportPlanStats(${plan.id})">
                            <i class="fas fa-download me-2"></i>Export Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('planStatsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new coreui.Modal(document.getElementById('planStatsModal'));
    modal.show();
}

function exportPlanStats(planId) {
    window.open(`<?= SUPERADMIN_URL ?>/tenants/plans/${planId}/export`, '_blank');
}

// Utility function to show alerts
function showAlert(message, type = 'info') {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
    `;
    
    // Insert at top of page
    const container = document.querySelector('.container-fluid') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

function deletePlan(planId) {
    if (confirm('Are you sure you want to delete this plan?\n\nThis action cannot be undone and will only work if there are no active subscriptions to this plan.')) {
        // Use fetch for better error handling
        fetch(`<?= SUPERADMIN_URL ?>/tenants/plans/${planId}/delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => {
            if (response.redirected) {
                // Backend redirected, follow the redirect
                window.location.href = response.url;
            } else {
                return response.json();
            }
        })
        .then(data => {
            if (data && data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else if (data) {
                showAlert(data.message || 'Failed to delete plan', 'error');
            }
        })
        .catch(error => {
            console.error('Error deleting plan:', error);
            showAlert('Error deleting plan. Please try again.', 'error');
        });
    }
}

// Debug function to test URLs
function debugUrls() {
    console.log('SUPERADMIN_URL:', '<?= SUPERADMIN_URL ?>');
    console.log('Test URLs:');
    console.log('- Get plan 1:', '<?= SUPERADMIN_URL ?>/tenants/plans/1/get');
    console.log('- Stats plan 1:', '<?= SUPERADMIN_URL ?>/tenants/plans/1/stats');
    console.log('- Delete plan 1:', '<?= SUPERADMIN_URL ?>/tenants/plans/1/delete');
}

// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    console.log('Plans page loaded');
    debugUrls();
    
    // Test if buttons exist
    const editButtons = document.querySelectorAll('[onclick*="editPlan"]');
    const statsButtons = document.querySelectorAll('[onclick*="viewPlanStats"]');
    const deleteButtons = document.querySelectorAll('[onclick*="deletePlan"]');
    
    console.log('Found buttons:', {
        edit: editButtons.length,
        stats: statsButtons.length,
        delete: deleteButtons.length
    });
    
    // Debug: Check can_delete values for each plan
    console.log('Plan delete permissions:');
    <?php foreach ($plans as $plan): ?>
        console.log('Plan <?= $plan['id'] ?> (<?= htmlspecialchars($plan['name']) ?>): can_delete = <?= $plan['can_delete'] ? 'true' : 'false' ?>');
    <?php endforeach; ?>
    
    // Initialize tooltips if available
    try {
        if (typeof coreui !== 'undefined' && coreui.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-coreui-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new coreui.Tooltip(tooltipTriggerEl);
            });
        }
    } catch (error) {
        console.warn('Tooltip initialization failed:', error);
    }
    
    // Plan Trends Chart
    const trendsCtx = document.getElementById('planTrendsChart');
    if (trendsCtx) {
        if (typeof Chart !== 'undefined') {
            new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Basic Plan',
                    data: [12, 15, 18, 22, 25, 28],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }, {
                    label: 'Professional Plan',
                    data: [8, 10, 12, 15, 18, 22],
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1
                }, {
                    label: 'Enterprise Plan',
                    data: [2, 3, 4, 5, 6, 8],
                    borderColor: 'rgb(255, 205, 86)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        } else {
            console.warn('Chart.js not loaded, skipping trends chart');
            trendsCtx.innerHTML = '<p class="text-center text-muted p-4">Chart.js not loaded</p>';
        }
    }
    
    // Plan Distribution Chart
    const distributionCtx = document.getElementById('planDistributionChart');
    if (distributionCtx) {
        if (typeof Chart !== 'undefined') {
            new Chart(distributionCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($plans ?? [], 'name')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($plans ?? [], 'subscriber_count')) ?>,
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
        } else {
            console.warn('Chart.js not loaded, skipping distribution chart');
            distributionCtx.innerHTML = '<p class="text-center text-muted p-4">Chart.js not loaded</p>';
        }
    }
    
    // Initialize tooltips safely
    try {
        if (typeof coreui !== 'undefined' && coreui.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-coreui-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new coreui.Tooltip(tooltipTriggerEl);
            });
        }
    } catch (error) {
        console.warn('Tooltip initialization failed:', error);
    }
});
</script>
