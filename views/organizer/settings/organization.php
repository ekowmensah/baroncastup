<!-- Organization Settings Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-building me-2"></i>
            Organization Settings
        </h2>
        <p class="text-muted mb-0">Manage your organization profile and preferences</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Organization Profile -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Organization Profile</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= ORGANIZER_URL ?>/settings/organization" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Organization Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= htmlspecialchars($tenant['name'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Contact Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($tenant['email'] ?? '') ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($tenant['phone'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="website" class="form-label">Website</label>
                                <input type="url" class="form-control" id="website" name="website" 
                                       value="<?= htmlspecialchars($tenant['website'] ?? '') ?>" 
                                       placeholder="https://example.com">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Organization Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($tenant['description'] ?? '') ?></textarea>
                        <div class="form-text">Brief description of your organization (optional)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2"><?= htmlspecialchars($tenant['address'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?= htmlspecialchars($tenant['city'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="state" class="form-label">State/Province</label>
                                <input type="text" class="form-control" id="state" name="state" 
                                       value="<?= htmlspecialchars($tenant['state'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="country" class="form-label">Country</label>
                                <select class="form-select" id="country" name="country">
                                    <option value="">Select Country</option>
                                    <option value="US" <?= ($tenant['country'] ?? '') === 'US' ? 'selected' : '' ?>>United States</option>
                                    <option value="CA" <?= ($tenant['country'] ?? '') === 'CA' ? 'selected' : '' ?>>Canada</option>
                                    <option value="GB" <?= ($tenant['country'] ?? '') === 'GB' ? 'selected' : '' ?>>United Kingdom</option>
                                    <option value="AU" <?= ($tenant['country'] ?? '') === 'AU' ? 'selected' : '' ?>>Australia</option>
                                    <option value="DE" <?= ($tenant['country'] ?? '') === 'DE' ? 'selected' : '' ?>>Germany</option>
                                    <option value="FR" <?= ($tenant['country'] ?? '') === 'FR' ? 'selected' : '' ?>>France</option>
                                    <option value="IN" <?= ($tenant['country'] ?? '') === 'IN' ? 'selected' : '' ?>>India</option>
                                    <option value="JP" <?= ($tenant['country'] ?? '') === 'JP' ? 'selected' : '' ?>>Japan</option>
                                    <option value="BR" <?= ($tenant['country'] ?? '') === 'BR' ? 'selected' : '' ?>>Brazil</option>
                                    <option value="MX" <?= ($tenant['country'] ?? '') === 'MX' ? 'selected' : '' ?>>Mexico</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="logo" class="form-label">Organization Logo</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                        <div class="form-text">Upload a logo for your organization (JPG, PNG, max 2MB)</div>
                        <?php if (!empty($tenant['logo'])): ?>
                            <div class="mt-2">
                                <img src="<?= htmlspecialchars($tenant['logo']) ?>" alt="Current Logo" class="img-thumbnail" style="max-height: 100px;">
                                <div class="small text-muted">Current logo</div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Subscription Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Subscription Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Current Plan</label>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-<?= ($tenant['plan'] ?? 'basic') === 'premium' ? 'success' : 'primary' ?> me-2">
                                    <?= ucfirst($tenant['plan'] ?? 'Basic') ?>
                                </span>
                                <a href="#" class="btn btn-sm btn-outline-primary">Upgrade Plan</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Account Status</label>
                            <div>
                                <span class="badge bg-<?= ($tenant['active'] ?? 1) ? 'success' : 'danger' ?>">
                                    <?= ($tenant['active'] ?? 1) ? 'Active' : 'Inactive' ?>
                                </span>
                                <?php if ($tenant['verified'] ?? 0): ?>
                                    <span class="badge bg-success ms-1">Verified</span>
                                <?php else: ?>
                                    <span class="badge bg-warning ms-1">Unverified</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Member Since</label>
                            <div><?= date('F j, Y', strtotime($tenant['created_at'] ?? 'now')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Last Updated</label>
                            <div><?= date('F j, Y', strtotime($tenant['updated_at'] ?? 'now')) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Account Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Account Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="fs-5 fw-semibold text-primary">12</div>
                        <div class="small text-muted">Total Events</div>
                    </div>
                    <div class="col-6">
                        <div class="fs-5 fw-semibold text-success">8</div>
                        <div class="small text-muted">Active Events</div>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="fs-5 fw-semibold text-info">2,456</div>
                        <div class="small text-muted">Total Votes</div>
                    </div>
                    <div class="col-6">
                        <div class="fs-5 fw-semibold text-warning">GH₵1,228</div>
                        <div class="small text-muted">Revenue</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= ORGANIZER_URL ?>/events/create" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-plus me-2"></i>Create Event
                    </a>
                    <a href="<?= ORGANIZER_URL ?>/settings/users" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-users me-2"></i>Manage Team
                    </a>
                    <a href="<?= ORGANIZER_URL ?>/financial/overview" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-chart-line me-2"></i>View Financials
                    </a>
                    <button class="btn btn-outline-info btn-sm" onclick="exportData()">
                        <i class="fas fa-download me-2"></i>Export Data
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Support -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Need Help?</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted">Contact our support team for assistance with your account.</p>
                <div class="d-grid gap-2">
                    <a href="#" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-headset me-2"></i>Contact Support
                    </a>
                    <a href="#" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-book me-2"></i>Documentation
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportData() {
    if (confirm('Export all organization data? This may take a few minutes.')) {
        console.log('Exporting organization data...');
        alert('Data export functionality will be implemented soon!');
    }
}
</script>
