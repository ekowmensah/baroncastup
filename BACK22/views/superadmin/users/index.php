<!-- Users Management -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-users text-primary me-2"></i>
            <?= htmlspecialchars($title ?? 'All Users') ?>
        </h2>
        <p class="text-muted mb-0">Manage all users across the platform</p>
    </div>
    <div>
        <button class="btn btn-primary" onclick="createUser()">
            <i class="fas fa-user-plus me-2"></i>Add User
        </button>
        <button class="btn btn-outline-secondary" onclick="location.reload()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
    </div>
</div>

<!-- User Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-primary">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= count($users ?? []) ?></div>
                    <div>Total Users</div>
                    <div class="small">All registered users</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-users fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">
                        <?= count(array_filter($users ?? [], function($u) { return ($u['status'] ?? '') === 'active'; })) ?>
                    </div>
                    <div>Active Users</div>
                    <div class="small">Currently active</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-user-check fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">
                        <?= count(array_filter($users ?? [], function($u) { return ($u['role'] ?? '') === 'admin'; })) ?>
                    </div>
                    <div>Administrators</div>
                    <div class="small">Admin users</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-user-shield fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-warning">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">
                        <?= count(array_filter($users ?? [], function($u) { return ($u['status'] ?? '') === 'suspended'; })) ?>
                    </div>
                    <div>Suspended</div>
                    <div class="small">Temporarily disabled</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-user-slash fa-2x opacity-75"></i>
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
            <input type="text" class="form-control" id="userSearch" placeholder="Search users..." onkeyup="filterUsers()">
        </div>
    </div>
    <div class="col-md-4">
        <select class="form-select" id="roleFilter" onchange="filterUsers()">
            <option value="">All Roles</option>
            <option value="superadmin">Super Admin</option>
            <option value="admin">Admin</option>
            <option value="user">User</option>
        </select>
    </div>
    <div class="col-md-4">
        <select class="form-select" id="statusFilter" onchange="filterUsers()">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            User Directory
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($users)): ?>
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Tenant</th>
                            <th>Last Login</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr data-user-id="<?= $user['id'] ?? '' ?>" data-role="<?= $user['role'] ?? '' ?>" data-status="<?= $user['status'] ?? '' ?>">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <?php if (!empty($user['avatar'])): ?>
                                            <img src="<?= htmlspecialchars(image_url($user['avatar'])) ?>" alt="Avatar" class="rounded-circle" width="40" height="40">
                                        <?php else: ?>
                                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0"><?= htmlspecialchars($user['name'] ?? 'Unknown User') ?></h6>
                                        <small class="text-muted">ID: <?= $user['id'] ?? 'N/A' ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <?= htmlspecialchars($user['email'] ?? 'No email') ?>
                                    <?php if (($user['email_verified'] ?? false)): ?>
                                        <i class="fas fa-check-circle text-success ms-1" title="Email verified"></i>
                                    <?php else: ?>
                                        <i class="fas fa-exclamation-circle text-warning ms-1" title="Email not verified"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php 
                                $role = $user['role'] ?? 'user';
                                $roleClass = $role === 'superadmin' ? 'bg-danger' : ($role === 'admin' ? 'bg-warning' : 'bg-info');
                                ?>
                                <span class="badge <?= $roleClass ?>"><?= ucfirst($role) ?></span>
                            </td>
                            <td>
                                <?php 
                                $status = $user['active'] ?? 'inactive';
                                $badgeClass = $status === 'active' ? 'bg-success' : ($status === 'suspended' ? 'bg-warning' : 'bg-secondary');
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                            </td>
                            <td>
                                <?php if (!empty($user['tenant_name'])): ?>
                                    <span class="badge bg-light text-dark"><?= htmlspecialchars($user['tenant_name']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">No tenant</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($user['last_login'])): ?>
                                    <small><?= date('M j, Y H:i', strtotime($user['last_login'])) ?></small>
                                <?php else: ?>
                                    <small class="text-muted">Never</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small><?= date('M j, Y', strtotime($user['created_at'] ?? 'now')) ?></small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewUser(<?= $user['id'] ?? 0 ?>)" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="editUser(<?= $user['id'] ?? 0 ?>)" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if (($user['status'] ?? '') === 'active'): ?>
                                        <button class="btn btn-sm btn-outline-warning" onclick="suspendUser(<?= $user['id'] ?? 0 ?>)" title="Suspend">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-success" onclick="activateUser(<?= $user['id'] ?? 0 ?>)" title="Activate">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-info" onclick="loginAsUser(<?= $user['id'] ?? 0 ?>)" title="Login As User">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?= $user['id'] ?? 0 ?>)" title="Delete">
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
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5>No Users Found</h5>
                <p class="text-muted">No users have been registered yet.</p>
                <button class="btn btn-primary" onclick="createUser()">
                    <i class="fas fa-user-plus me-2"></i>Create First User
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- User Activity Chart -->
<div class="row mt-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    User Registration Trend
                </h5>
            </div>
            <div class="card-body">
                <canvas id="userRegistrationChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    User Roles Distribution
                </h5>
            </div>
            <div class="card-body">
                <canvas id="userRolesChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
function filterUsers() {
    const searchTerm = document.getElementById('userSearch').value.toLowerCase();
    const roleFilter = document.getElementById('roleFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('#usersTable tbody tr');
    
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

function createUser() {
    // Implementation for creating a new user
    console.log('Creating new user');
}

function viewUser(userId) {
    // Implementation for viewing user details
    console.log('Viewing user:', userId);
}

function editUser(userId) {
    // Implementation for editing user
    console.log('Editing user:', userId);
}

function suspendUser(userId) {
    if (confirm('Are you sure you want to suspend this user?')) {
        // Implementation for suspending user
        console.log('Suspending user:', userId);
    }
}

function activateUser(userId) {
    // Implementation for activating user
    console.log('Activating user:', userId);
}

function loginAsUser(userId) {
    if (confirm('Are you sure you want to login as this user? This will log you out of the super admin account.')) {
        // Implementation for logging in as user
        console.log('Logging in as user:', userId);
    }
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        // Implementation for deleting user
        console.log('Deleting user:', userId);
    }
}

// Initialize charts if Chart.js is available
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart !== 'undefined') {
        // User Registration Chart
        const registrationCtx = document.getElementById('userRegistrationChart');
        if (registrationCtx) {
            new Chart(registrationCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'New Users',
                        data: [12, 19, 3, 5, 2, 3],
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
        
        // User Roles Chart
        const rolesCtx = document.getElementById('userRolesChart');
        if (rolesCtx) {
            new Chart(rolesCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Users', 'Admins', 'Super Admins'],
                    datasets: [{
                        data: [
                            <?= count(array_filter($users ?? [], function($u) { return ($u['role'] ?? '') === 'user'; })) ?>,
                            <?= count(array_filter($users ?? [], function($u) { return ($u['role'] ?? '') === 'admin'; })) ?>,
                            <?= count(array_filter($users ?? [], function($u) { return ($u['role'] ?? '') === 'superadmin'; })) ?>
                        ],
                        backgroundColor: ['#36A2EB', '#FFCE56', '#FF6384']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    }
});
</script>
