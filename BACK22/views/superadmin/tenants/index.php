<!-- Tenants Management -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-building text-primary me-2"></i>
            <?= htmlspecialchars($title ?? 'All Tenants') ?>
        </h2>
        <p class="text-muted mb-0">Manage all tenant organizations on the platform</p>
    </div>
    <div>
        <button class="btn btn-primary" onclick="createTenant()">
            <i class="fas fa-plus me-2"></i>Add Tenant
        </button>
        <button class="btn btn-outline-secondary" onclick="location.reload()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
    </div>
</div>

<!-- Tenant Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-primary">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= count($tenants ?? []) ?></div>
                    <div>Total Tenants</div>
                    <div class="small">All organizations</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-building fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">
                        <?= count(array_filter($tenants ?? [], function($t) { return ($t['status'] ?? '') === 'active'; })) ?>
                    </div>
                    <div>Active Tenants</div>
                    <div class="small">Currently operational</div>
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
                    <div class="fs-4 fw-semibold">
                        <?= count(array_filter($tenants ?? [], function($t) { return ($t['status'] ?? '') === 'suspended'; })) ?>
                    </div>
                    <div>Suspended</div>
                    <div class="small">Temporarily disabled</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-pause-circle fa-2x opacity-75"></i>
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
                        $totalEvents = 0;
                        foreach ($tenants ?? [] as $tenant) {
                            $totalEvents += $tenant['stats']['total_events'] ?? 0;
                        }
                        echo number_format($totalEvents);
                        ?>
                    </div>
                    <div>Total Events</div>
                    <div class="small">Across all tenants</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-chart-bar fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filters -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" class="form-control" id="tenantSearch" placeholder="Search tenants..." onkeyup="filterTenants()">
        </div>
    </div>
    <div class="col-md-6">
        <select class="form-select" id="statusFilter" onchange="filterTenants()">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
</div>

<!-- Tenants Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Tenant Directory
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($tenants)): ?>
            <div class="table-responsive">
                <table class="table table-hover" id="tenantsTable">
                    <thead>
                        <tr>
                            <th>Organization</th>
                            <th>Plan & Revenue</th>
                            <th>Status</th>
                            <th>Events & Activity</th>
                            <th>Users</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tenants as $tenant): ?>
                        <tr data-tenant-id="<?= $tenant['id'] ?? '' ?>" data-status="<?= $tenant['active'] ? 'active' : 'inactive' ?>">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <?php if (!empty($tenant['logo'])): ?>
                                            <img src="<?= htmlspecialchars(image_url($tenant['logo'])) ?>" alt="Logo" class="rounded" width="40" height="40">
                                        <?php else: ?>
                                            <div class="bg-primary text-white rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <?= strtoupper(substr($tenant['name'] ?? 'T', 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0"><?= htmlspecialchars($tenant['name'] ?? 'Unknown') ?></h6>
                                        <small class="text-muted">
                                            <?php if (!empty($tenant['phone'])): ?>
                                                <i class="fas fa-phone me-1"></i><?= htmlspecialchars($tenant['phone']) ?>
                                            <?php endif; ?>
                                            <?php if (!empty($tenant['phone']) && (!empty($tenant['website']) || !empty($tenant['email']))): ?>
                                                <span class="mx-1">•</span>
                                            <?php endif; ?>
                                            <?php if (!empty($tenant['website'])): ?>
                                                <i class="fas fa-globe me-1"></i><?= htmlspecialchars($tenant['website']) ?>
                                            <?php elseif (!empty($tenant['email'])): ?>
                                                <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($tenant['email']) ?>
                                            <?php endif; ?>
                                            <?php if (empty($tenant['phone']) && empty($tenant['website']) && empty($tenant['email'])): ?>
                                                No contact info
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="fw-semibold">
                                        <?php if (!empty($tenant['plan_name'])): ?>
                                            <?= htmlspecialchars($tenant['plan_name']) ?>
                                            <?php if ($tenant['plan_price'] > 0): ?>
                                                <span class="badge bg-info ms-1">
                                                    $<?= number_format($tenant['plan_price'], 2) ?>/<?= $tenant['billing_cycle'] ?? 'month' ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success ms-1">Free</span>
                                            <?php endif; ?>
                                            <?php if ($tenant['subscription_status'] === 'active'): ?>
                                                <i class="fas fa-check-circle text-success ms-1" title="Active Subscription"></i>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">No Active Plan</span>
                                            <span class="badge bg-warning ms-1">Unsubscribed</span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-success">
                                        <i class="fas fa-money-bill me-1"></i>
                                        Total: GH₵<?= number_format($tenant['total_revenue'] ?? 0, 2) ?>
                                        <?php if (($tenant['monthly_revenue'] ?? 0) > 0): ?>
                                            | Month: GH₵<?= number_format($tenant['monthly_revenue'], 2) ?>
                                        <?php endif; ?>
                                        <?php if (isset($tenant['available']) && $tenant['available'] > 0): ?>
                                            | Balance: GH₵<?= number_format($tenant['available'], 2) ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <?php 
                                $isActive = $tenant['active'] ?? 0;
                                $isVerified = $tenant['verified'] ?? 0;
                                
                                if (!$isVerified && $isActive) {
                                    $status = 'pending_approval';
                                    $statusText = 'Pending Approval';
                                    $badgeClass = 'bg-warning';
                                } elseif ($isVerified && $isActive) {
                                    $status = 'active';
                                    $statusText = 'Active';
                                    $badgeClass = 'bg-success';
                                } elseif (!$isActive && !$isVerified) {
                                    $status = 'rejected';
                                    $statusText = 'Rejected';
                                    $badgeClass = 'bg-danger';
                                } else {
                                    $status = 'suspended';
                                    $statusText = 'Suspended';
                                    $badgeClass = 'bg-secondary';
                                }
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                                <?php if ($status === 'pending_approval'): ?>
                                    <br><small class="text-warning"><i class="fas fa-clock me-1"></i>Awaiting admin approval</small>
                                <?php elseif ($status === 'rejected'): ?>
                                    <br><small class="text-danger"><i class="fas fa-times-circle me-1"></i>Application rejected</small>
                                <?php elseif ($status === 'suspended'): ?>
                                    <br><small class="text-muted"><i class="fas fa-pause-circle me-1"></i>Account suspended</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div>
                                    <div class="fw-semibold">
                                        <?= number_format($tenant['total_events'] ?? 0) ?> events
                                        <span class="badge bg-success ms-1"><?= number_format($tenant['active_events'] ?? 0) ?> active</span>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-users me-1"></i><?= number_format($tenant['total_contestants'] ?? 0) ?> contestants
                                        <?php if (!empty($tenant['last_transaction_date'])): ?>
                                            <br><i class="fas fa-clock me-1"></i>Last activity: <?= date('M j', strtotime($tenant['last_transaction_date'])) ?>
                                        <?php else: ?>
                                            <br><i class="fas fa-clock me-1"></i>No recent activity
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div class="text-center">
                                    <div class="fw-bold"><?= number_format($tenant['user_count'] ?? 0) ?></div>
                                    <small class="text-muted">users</small>
                                </div>
                            </td>
                            <td>
                                <small><?= date('M j, Y', strtotime($tenant['created_at'] ?? 'now')) ?></small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewTenant(<?= $tenant['id'] ?>)" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" title="More Actions">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <?php if ($status === 'pending_approval'): ?>
                                                <li><h6 class="dropdown-header text-warning">
                                                    <i class="fas fa-clock me-1"></i>Pending Approval
                                                </h6></li>
                                                <li>
                                                    <button class="dropdown-item text-success" onclick="approveTenant(<?= $tenant['id'] ?>, '<?= htmlspecialchars($tenant['name']) ?>')">
                                                        <i class="fas fa-check me-2"></i>Approve Tenant
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item text-danger" onclick="rejectTenant(<?= $tenant['id'] ?>, '<?= htmlspecialchars($tenant['name']) ?>')">
                                                        <i class="fas fa-times me-2"></i>Reject Application
                                                    </button>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                            <?php endif; ?>
                                            
                                            <?php if ($status === 'active'): ?>
                                                <li>
                                                    <button class="dropdown-item" onclick="changePlan(<?= $tenant['id'] ?>)">
                                                        <i class="fas fa-exchange-alt me-2"></i>Change Plan
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item" onclick="viewRevenue(<?= $tenant['id'] ?>)">
                                                        <i class="fas fa-chart-line me-2"></i>View Revenue
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item" onclick="loginAsTenant(<?= $tenant['id'] ?>)">
                                                        <i class="fas fa-sign-in-alt me-2"></i>Login as Tenant
                                                    </button>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button class="dropdown-item text-warning" onclick="suspendTenant(<?= $tenant['id'] ?>, '<?= htmlspecialchars($tenant['name']) ?>')">
                                                        <i class="fas fa-pause me-2"></i>Suspend Tenant
                                                    </button>
                                                </li>
                                            <?php elseif ($status === 'suspended' || $status === 'rejected'): ?>
                                                <li>
                                                    <button class="dropdown-item text-success" onclick="reactivateTenant(<?= $tenant['id'] ?>, '<?= htmlspecialchars($tenant['name']) ?>')">
                                                        <i class="fas fa-play me-2"></i>Reactivate Tenant
                                                    </button>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                <h5>No Tenants Found</h5>
                <p class="text-muted">No tenant organizations have been created yet.</p>
                <button class="btn btn-primary" onclick="createTenant()">
                    <i class="fas fa-plus me-2"></i>Create First Tenant
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function filterTenants() {
    const searchTerm = document.getElementById('tenantSearch').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('#tenantsTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const status = row.getAttribute('data-status');
        
        const matchesSearch = text.includes(searchTerm);
        const matchesStatus = !statusFilter || status === statusFilter;
        
        row.style.display = matchesSearch && matchesStatus ? '' : 'none';
    });
}

function createTenant() {
    // Implementation for creating a new tenant
    console.log('Creating new tenant');
    // You would typically open a modal or redirect to a form
}

function viewTenant(tenantId) {
    // Implementation for viewing tenant details
    console.log('Viewing tenant:', tenantId);
}

function editTenant(tenantId) {
    // Implementation for editing tenant
    console.log('Editing tenant:', tenantId);
}

function changePlan(tenantId) {
    // Show plan change modal or form using new subscription system
    const availablePlans = <?= json_encode($availablePlans) ?>;
    
    // Build plan options string for prompt
    let plansList = 'Available subscription plans:\n';
    availablePlans.forEach(plan => {
        const price = plan.price > 0 ? `$${parseFloat(plan.price).toFixed(2)}/${plan.billing_cycle}` : 'Free';
        plansList += `- ${plan.name}: ${price} (ID: ${plan.id})\n`;
    });
    
    const planId = prompt(`${plansList}\nEnter the Plan ID to assign to tenant ${tenantId}:`);
    
    if (planId && !isNaN(planId)) {
        const selectedPlan = availablePlans.find(p => p.id == planId);
        if (selectedPlan) {
            if (confirm(`Change tenant ${tenantId} to "${selectedPlan.name}" plan?`)) {
                // Make API call to change plan using new subscription system
                fetch(`<?= SUPERADMIN_URL ?>/tenants/${tenantId}/change-plan`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ plan_id: parseInt(planId) })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Plan changed successfully!');
                        location.reload();
                    } else {
                        alert('Error changing plan: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error changing plan');
                });
            }
        } else {
            alert('Invalid Plan ID. Please enter a valid Plan ID from the list.');
        }
    } else if (planId !== null) {
        alert('Please enter a valid numeric Plan ID.');
    }
}

function viewRevenue(tenantId) {
    // Redirect to revenue analytics for this tenant
    window.open(`<?= SUPERADMIN_URL ?>/financial/revenue?tenant=${tenantId}`, '_blank');
}

function loginAsTenant(tenantId) {
    if (confirm('Login as this tenant? This will switch your session to their account.')) {
        // Make API call to switch session
        fetch(`<?= SUPERADMIN_URL ?>/tenants/${tenantId}/login-as`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect_url || '<?= ORGANIZER_URL ?>';
            } else {
                alert('Error switching to tenant: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error switching to tenant');
        });
    }
}

function approveTenant(tenantId, tenantName) {
    if (confirm(`Approve "${tenantName}" for platform access? They will be able to login and start using the platform.`)) {
        const formData = new FormData();
        formData.append('tenant_id', tenantId);
        
        fetch(`<?= SUPERADMIN_URL ?>/tenants/approve`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`✅ ${tenantName} has been approved successfully!`);
                location.reload();
            } else {
                alert('Error approving tenant: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error approving tenant');
        });
    }
}

function rejectTenant(tenantId, tenantName) {
    const reason = prompt(`Reject "${tenantName}" application?\n\nPlease provide a reason for rejection:`, 'Application does not meet requirements');
    
    if (reason !== null && reason.trim() !== '') {
        const formData = new FormData();
        formData.append('tenant_id', tenantId);
        formData.append('reason', reason.trim());
        
        fetch(`<?= SUPERADMIN_URL ?>/tenants/reject`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`❌ ${tenantName} application has been rejected.`);
                location.reload();
            } else {
                alert('Error rejecting tenant: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error rejecting tenant');
        });
    }
}

function suspendTenant(tenantId, tenantName) {
    const reason = prompt(`Suspend "${tenantName}"?\n\nPlease provide a reason for suspension:`, 'Policy violation');
    
    if (reason !== null && reason.trim() !== '') {
        const formData = new FormData();
        formData.append('tenant_id', tenantId);
        formData.append('reason', reason.trim());
        
        fetch(`<?= SUPERADMIN_URL ?>/tenants/suspend`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`⏸️ ${tenantName} has been suspended.`);
                location.reload();
            } else {
                alert('Error suspending tenant: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error suspending tenant');
        });
    }
}

function reactivateTenant(tenantId, tenantName) {
    if (confirm(`Reactivate "${tenantName}"? They will regain full access to their account.`)) {
        const formData = new FormData();
        formData.append('tenant_id', tenantId);
        
        fetch(`<?= SUPERADMIN_URL ?>/tenants/reactivate`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`✅ ${tenantName} has been reactivated successfully!`);
                location.reload();
            } else {
                alert('Error reactivating tenant: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error reactivating tenant');
        });
    }
}

function deleteTenant(tenantId) {
    if (confirm('Are you sure you want to delete this tenant? This action cannot be undone.')) {
        // Implementation for deleting tenant
        console.log('Deleting tenant:', tenantId);
    }
}
</script>
