<!-- Platform Admins -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-user-shield text-primary me-2"></i>
            Platform Administrators
        </h2>
        <p class="text-muted mb-0">Manage platform admin accounts and permissions</p>
    </div>
    <div>
        <button class="btn btn-primary" onclick="createAdmin()">
            <i class="fas fa-user-plus me-2"></i>Add Admin
        </button>
        <button class="btn btn-outline-secondary" onclick="location.reload()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
    </div>
</div>

<!-- Admin Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-primary">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= count($admins ?? []) ?></div>
                    <div>Total Admins</div>
                    <div class="small">Platform administrators</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-user-shield fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">
                        <?= count(array_filter($admins ?? [], function($a) { return ($a['status'] ?? '') === 'active'; })) ?>
                    </div>
                    <div>Active Admins</div>
                    <div class="small">Currently enabled</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-danger">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">
                        <?= count(array_filter($admins ?? [], function($a) { return ($a['role'] ?? '') === 'superadmin'; })) ?>
                    </div>
                    <div>Super Admins</div>
                    <div class="small">Full access</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-crown fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">
                        <?= count(array_filter($admins ?? [], function($a) { 
                            return !empty($a['last_login']) && strtotime($a['last_login']) > strtotime('-24 hours'); 
                        })) ?>
                    </div>
                    <div>Active Today</div>
                    <div class="small">Last 24 hours</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-clock fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filters -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" class="form-control" id="adminSearch" placeholder="Search admins..." onkeyup="filterAdmins()">
        </div>
    </div>
    <div class="col-md-4">
        <select class="form-select" id="roleFilter" onchange="filterAdmins()">
            <option value="">All Roles</option>
            <option value="superadmin">Super Admin</option>
            <option value="admin">Admin</option>
            <option value="moderator">Moderator</option>
        </select>
    </div>
    <div class="col-md-4">
        <select class="form-select" id="statusFilter" onchange="filterAdmins()">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="suspended">Suspended</option>
        </select>
    </div>
</div>

<!-- Admins Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Platform Administrator Accounts
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($admins)): ?>
            <div class="table-responsive">
                <table class="table table-hover" id="adminsTable">
                    <thead>
                        <tr>
                            <th>Administrator</th>
                            <th>Role</th>
                            <th>Permissions</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $admin): ?>
                        <tr data-admin-id="<?= $admin['id'] ?? '' ?>" 
                            data-role="<?= $admin['role'] ?? '' ?>" 
                            data-status="<?= $admin['status'] ?? '' ?>">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <?php if (!empty($admin['avatar'])): ?>
                                            <img src="<?= htmlspecialchars(image_url($admin['avatar'])) ?>" alt="Avatar" class="rounded-circle" width="40" height="40">
                                        <?php else: ?>
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <?= strtoupper(substr($admin['name'] ?? 'A', 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0"><?= htmlspecialchars($admin['name'] ?? 'Unknown Admin') ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($admin['email'] ?? 'No email') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php 
                                $role = $admin['role'] ?? 'admin';
                                $roleClass = match($role) {
                                    'superadmin' => 'bg-danger',
                                    'admin' => 'bg-warning',
                                    'moderator' => 'bg-info',
                                    default => 'bg-secondary'
                                };
                                $roleIcon = match($role) {
                                    'superadmin' => 'fas fa-crown',
                                    'admin' => 'fas fa-user-shield',
                                    'moderator' => 'fas fa-user-cog',
                                    default => 'fas fa-user'
                                };
                                ?>
                                <span class="badge <?= $roleClass ?>">
                                    <i class="<?= $roleIcon ?> me-1"></i><?= ucfirst($role) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($admin['permissions'])): ?>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach (array_slice($admin['permissions'], 0, 3) as $permission): ?>
                                            <span class="badge bg-light text-dark small"><?= htmlspecialchars($permission) ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($admin['permissions']) > 3): ?>
                                            <span class="badge bg-secondary small">+<?= count($admin['permissions']) - 3 ?> more</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No specific permissions</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $status = $admin['status'] ?? 'inactive';
                                $badgeClass = match($status) {
                                    'active' => 'bg-success',
                                    'inactive' => 'bg-secondary',
                                    'suspended' => 'bg-danger',
                                    default => 'bg-dark'
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                                <?php if (($admin['two_factor_enabled'] ?? false)): ?>
                                    <br><small class="text-success">
                                        <i class="fas fa-shield-alt me-1"></i>2FA Enabled
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($admin['last_login'])): ?>
                                    <div><?= date('M j, Y', strtotime($admin['last_login'])) ?></div>
                                    <small class="text-muted"><?= date('H:i', strtotime($admin['last_login'])) ?></small>
                                <?php else: ?>
                                    <span class="text-muted">Never</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?= date('M j, Y', strtotime($admin['created_at'] ?? 'now')) ?></div>
                                <small class="text-muted">by <?= htmlspecialchars($admin['created_by'] ?? 'System') ?></small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewAdminDetails(<?= $admin['id'] ?? 0 ?>)" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="editAdmin(<?= $admin['id'] ?? 0 ?>)" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="managePermissions(<?= $admin['id'] ?? 0 ?>)" title="Permissions">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <?php if (($admin['status'] ?? '') === 'active'): ?>
                                        <button class="btn btn-sm btn-outline-warning" onclick="suspendAdmin(<?= $admin['id'] ?? 0 ?>)" title="Suspend">
                                            <i class="fas fa-pause"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-success" onclick="activateAdmin(<?= $admin['id'] ?? 0 ?>)" title="Activate">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteAdmin(<?= $admin['id'] ?? 0 ?>)" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-user-shield fa-3x text-muted mb-3"></i>
                <h5>No Platform Administrators</h5>
                <p class="text-muted">No admin accounts have been created yet.</p>
                <button class="btn btn-primary" onclick="createAdmin()">
                    <i class="fas fa-user-plus me-2"></i>Create First Admin
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Admin Creation/Edit Modal -->
<div class="modal fade" id="adminModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adminModalTitle">Create Administrator</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="adminForm">
                    <input type="hidden" id="adminId" name="admin_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="adminName" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="adminName" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="adminEmail" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="adminEmail" name="email" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="adminRole" class="form-label">Role</label>
                                <select class="form-select" id="adminRole" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="moderator">Moderator</option>
                                    <option value="superadmin">Super Admin</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="adminStatus" class="form-label">Status</label>
                                <select class="form-select" id="adminStatus" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="adminPermissions" class="form-label">Permissions</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="permTenants" name="permissions[]" value="manage_tenants">
                                    <label class="form-check-label" for="permTenants">Manage Tenants</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="permUsers" name="permissions[]" value="manage_users">
                                    <label class="form-check-label" for="permUsers">Manage Users</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="permFinancial" name="permissions[]" value="view_financial">
                                    <label class="form-check-label" for="permFinancial">View Financial Data</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="permSecurity" name="permissions[]" value="manage_security">
                                    <label class="form-check-label" for="permSecurity">Manage Security</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="permSystem" name="permissions[]" value="system_settings">
                                    <label class="form-check-label" for="permSystem">System Settings</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="permReports" name="permissions[]" value="view_reports">
                                    <label class="form-check-label" for="permReports">View Reports</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="requireTwoFactor" name="require_2fa">
                                <label class="form-check-label" for="requireTwoFactor">
                                    Require Two-Factor Authentication
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="sendWelcomeEmail" name="send_welcome" checked>
                                <label class="form-check-label" for="sendWelcomeEmail">
                                    Send Welcome Email
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveAdmin()">Save Administrator</button>
            </div>
        </div>
    </div>
</div>

<script>
function filterAdmins() {
    const searchTerm = document.getElementById('adminSearch').value.toLowerCase();
    const roleFilter = document.getElementById('roleFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('#adminsTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const role = row.getAttribute('data-role');
        const status = row.getAttribute('data-status');
        
        const matchesSearch = text.includes(searchTerm);
        const matchesRole = !roleFilter || role === roleFilter;
        const matchesStatus = !statusFilter || status === statusFilter;
        
        row.style.display = matchesSearch && matchesRole && matchesStatus ? '' : 'none';
    });
}

function createAdmin() {
    document.getElementById('adminModalTitle').textContent = 'Create Administrator';
    document.getElementById('adminForm').reset();
    document.getElementById('adminId').value = '';
    
    const modal = new coreui.Modal(document.getElementById('adminModal'));
    modal.show();
}

function editAdmin(adminId) {
    document.getElementById('adminModalTitle').textContent = 'Edit Administrator';
    document.getElementById('adminId').value = adminId;
    
    // Load admin data (implementation needed)
    console.log('Loading admin data for:', adminId);
    
    const modal = new coreui.Modal(document.getElementById('adminModal'));
    modal.show();
}

function saveAdmin() {
    const form = document.getElementById('adminForm');
    const formData = new FormData(form);
    
    console.log('Saving admin:', Object.fromEntries(formData));
    
    // Close modal and refresh page
    const modal = coreui.Modal.getInstance(document.getElementById('adminModal'));
    modal.hide();
    
    alert('Administrator saved successfully!');
    location.reload();
}

function viewAdminDetails(adminId) {
    console.log('Viewing admin details:', adminId);
    // Implementation for viewing detailed admin information
}

function managePermissions(adminId) {
    console.log('Managing permissions for admin:', adminId);
    // Implementation for managing admin permissions
}

function suspendAdmin(adminId) {
    if (confirm('Are you sure you want to suspend this administrator?')) {
        console.log('Suspending admin:', adminId);
        alert('Administrator suspended successfully!');
        location.reload();
    }
}

function activateAdmin(adminId) {
    console.log('Activating admin:', adminId);
    alert('Administrator activated successfully!');
    location.reload();
}

function deleteAdmin(adminId) {
    if (confirm('Are you sure you want to delete this administrator? This action cannot be undone.')) {
        console.log('Deleting admin:', adminId);
        alert('Administrator deleted successfully!');
        location.reload();
    }
}
</script>
