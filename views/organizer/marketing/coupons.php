<!-- Coupons Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-tags me-2"></i>
            Coupons & Discounts
        </h2>
        <p class="text-muted mb-0">Create and manage discount coupons for your events</p>
    </div>
    <div>
        <button class="btn btn-primary" data-coreui-toggle="modal" data-coreui-target="#createCouponModal">
            <i class="fas fa-plus me-2"></i>Create Coupon
        </button>
    </div>
</div>

<!-- Coupon Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">12</div>
                    <div>Active Coupons</div>
                    <div class="small">3 expiring soon</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-tags fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card success text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">847</div>
                    <div>Total Uses</div>
                    <div class="small">+23% this month</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-chart-line fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card warning text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">GH₵2,340</div>
                    <div>Discount Given</div>
                    <div class="small">Average: GH₵2.76</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-money-bill fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card info text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">68%</div>
                    <div>Conversion Rate</div>
                    <div class="small">Above average</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-percentage fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Coupons List -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0">All Coupons</h5>
            </div>
            <div class="col-auto">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" placeholder="Search coupons...">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Coupon Code</th>
                        <th>Type</th>
                        <th>Discount</th>
                        <th>Usage</th>
                        <th>Valid Until</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="fw-semibold">WELCOME20</div>
                            <div class="small text-muted">Welcome discount for new users</div>
                        </td>
                        <td><span class="badge bg-primary">Percentage</span></td>
                        <td>
                            <div class="fw-semibold">20%</div>
                            <div class="small text-muted">Max GH₵10</div>
                        </td>
                        <td>
                            <div class="fw-semibold">234 / 500</div>
                            <div class="progress mt-1" style="height: 4px;">
                                <div class="progress-bar" style="width: 47%"></div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold">Dec 31, 2024</div>
                            <div class="small text-success">45 days left</div>
                        </td>
                        <td><span class="badge bg-success">Active</span></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="editCoupon(1)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-info" onclick="viewStats(1)">
                                    <i class="fas fa-chart-bar"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="deactivateCoupon(1)">
                                    <i class="fas fa-pause"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="fw-semibold">VOTE50</div>
                            <div class="small text-muted">50% off vote bundles</div>
                        </td>
                        <td><span class="badge bg-success">Fixed Amount</span></td>
                        <td>
                            <div class="fw-semibold">GH₵5.00</div>
                            <div class="small text-muted">Per transaction</div>
                        </td>
                        <td>
                            <div class="fw-semibold">89 / 100</div>
                            <div class="progress mt-1" style="height: 4px;">
                                <div class="progress-bar bg-warning" style="width: 89%"></div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold">Nov 15, 2024</div>
                            <div class="small text-warning">3 days left</div>
                        </td>
                        <td><span class="badge bg-success">Active</span></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="editCoupon(2)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-info" onclick="viewStats(2)">
                                    <i class="fas fa-chart-bar"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="deactivateCoupon(2)">
                                    <i class="fas fa-pause"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="fw-semibold">EARLYBIRD</div>
                            <div class="small text-muted">Early bird special</div>
                        </td>
                        <td><span class="badge bg-primary">Percentage</span></td>
                        <td>
                            <div class="fw-semibold">15%</div>
                            <div class="small text-muted">No limit</div>
                        </td>
                        <td>
                            <div class="fw-semibold">156 / ∞</div>
                            <div class="progress mt-1" style="height: 4px;">
                                <div class="progress-bar bg-info" style="width: 100%"></div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold">Oct 30, 2024</div>
                            <div class="small text-danger">Expired</div>
                        </td>
                        <td><span class="badge bg-secondary">Expired</span></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="editCoupon(3)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-info" onclick="viewStats(3)">
                                    <i class="fas fa-chart-bar"></i>
                                </button>
                                <button class="btn btn-outline-success" onclick="reactivateCoupon(3)">
                                    <i class="fas fa-play"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Coupon Modal -->
<div class="modal fade" id="createCouponModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Coupon</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createCouponForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Coupon Code *</label>
                                <input type="text" class="form-control" name="code" required>
                                <div class="form-text">Use uppercase letters and numbers only</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Discount Type *</label>
                                <select class="form-select" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="percentage">Percentage</option>
                                    <option value="fixed">Fixed Amount</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Discount Value *</label>
                                <input type="number" class="form-control" name="value" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Maximum Discount</label>
                                <input type="number" class="form-control" name="max_discount" step="0.01">
                                <div class="form-text">For percentage discounts only</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Usage Limit</label>
                                <input type="number" class="form-control" name="usage_limit">
                                <div class="form-text">Leave empty for unlimited</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Valid Until</label>
                                <input type="datetime-local" class="form-control" name="expires_at">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="active" checked>
                        <label class="form-check-label">
                            Active (available for use immediately)
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveCoupon()">
                    <i class="fas fa-save me-2"></i>Create Coupon
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function editCoupon(id) {
    console.log('Editing coupon:', id);
    // Implementation for editing coupon
}

function viewStats(id) {
    console.log('Viewing stats for coupon:', id);
    // Implementation for viewing coupon statistics
}

function deactivateCoupon(id) {
    if (confirm('Are you sure you want to deactivate this coupon?')) {
        console.log('Deactivating coupon:', id);
        // Implementation for deactivating coupon
    }
}

function reactivateCoupon(id) {
    if (confirm('Are you sure you want to reactivate this coupon?')) {
        console.log('Reactivating coupon:', id);
        // Implementation for reactivating coupon
    }
}

function saveCoupon() {
    const form = document.getElementById('createCouponForm');
    const formData = new FormData(form);
    
    // Implementation for saving coupon
    console.log('Saving coupon...');
    
    // Close modal and refresh page
    const modal = coreui.Modal.getInstance(document.getElementById('createCouponModal'));
    modal.hide();
    
    // Show success message
    alert('Coupon created successfully!');
}
</script>
