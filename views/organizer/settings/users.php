<!-- Team Members Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-users-cog me-2"></i>
            Team Members
        </h2>
        <p class="text-muted mb-0">Manage your team members and their permissions</p>
    </div>
    <div>
        <button class="btn btn-primary" data-coreui-toggle="modal" data-coreui-target="#inviteUserModal">
            <i class="fas fa-user-plus me-2"></i>Invite Member
        </button>
    </div>
</div>

<!-- Team Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= count($users ?? []) ?></div>
                    <div>Total Members</div>
                    <div class="small">Including you</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-users fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card success text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= count(array_filter($users ?? [], function($u) { return $u['active']; })) ?></div>
                    <div>Active Members</div>
                    <div class="small">Currently active</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-user-check fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card warning text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= count(array_filter($users ?? [], function($u) { return $u['role'] === 'owner'; })) ?></div>
                    <div>Owners</div>
                    <div class="small">Full access</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-crown fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card info text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= count(array_filter($users ?? [], function($u) { return $u['role'] === 'manager'; })) ?></div>
                    <div>Managers</div>
                    <div class="small">Limited access</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-user-tie fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Team Members List -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Team Members</h5>
            <div class="input-group" style="width: 300px;">
                <input type="text" class="form-control form-control-sm" placeholder="Search members..." id="searchMembers">
                <button class="btn btn-outline-secondary btn-sm" onclick="searchMembers()">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Member</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Active</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <?php if (!empty($user['avatar'])): ?>
                                                <img src="<?= htmlspecialchars($user['avatar']) ?>" class="avatar-img rounded-circle">
                                            <?php else: ?>
                                                <div class="avatar-img rounded-circle bg-primary text-white d-flex align-items-center justify-content-center">
                                                    <?= strtoupper(substr($user['email'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?= htmlspecialchars($user['first_name'] ?? '') ?> <?= htmlspecialchars($user['last_name'] ?? '') ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($user['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $user['role'] === 'owner' ? 'warning' : ($user['role'] === 'manager' ? 'info' : 'secondary') ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $user['active'] ? 'success' : 'secondary' ?>">
                                        <?= $user['active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="small">
                                        <?php if (!empty($user['last_login'])): ?>
                                            <?= date('M j, Y', strtotime($user['last_login'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Never</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="small">
                                        <?= date('M j, Y', strtotime($user['created_at'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="editUser(<?= $user['id'] ?>)" title="Edit User">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($user['role'] !== 'owner'): ?>
                                            <button class="btn btn-outline-warning" onclick="changeRole(<?= $user['id'] ?>)" title="Change Role">
                                                <i class="fas fa-user-cog"></i>
                                            </button>
                                            <?php if ($user['active']): ?>
                                                <button class="btn btn-outline-danger" onclick="deactivateUser(<?= $user['id'] ?>)" title="Deactivate">
                                                    <i class="fas fa-user-slash"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-outline-success" onclick="activateUser(<?= $user['id'] ?>)" title="Activate">
                                                    <i class="fas fa-user-check"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <p>No team members found</p>
                                    <button class="btn btn-primary" data-coreui-toggle="modal" data-coreui-target="#inviteUserModal">
                                        <i class="fas fa-user-plus me-2"></i>Invite Your First Member
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Invite User Modal -->
<div class="modal fade" id="inviteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Invite Team Member</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="inviteUserForm" method="POST" action="<?= ORGANIZER_URL ?>/settings/users">
                    <input type="hidden" name="action" value="invite">
                    
                    <div class="mb-3">
                        <label class="form-label">Email Address *</label>
                        <input type="email" class="form-control" name="email" required>
                        <div class="form-text">We'll send an invitation to this email address</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="first_name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="last_name">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select class="form-select" name="role" required>
                            <option value="">Select Role</option>
                            <option value="manager">Manager</option>
                            <option value="staff">Staff</option>
                        </select>
                        <div class="form-text">
                            <strong>Manager:</strong> Can create and manage events<br>
                            <strong>Staff:</strong> Limited access to assigned events
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Personal Message (Optional)</label>
                        <textarea class="form-control" name="message" rows="3" placeholder="Add a personal message to the invitation..."></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="send_welcome" checked>
                        <label class="form-check-label">
                            Send welcome email with getting started guide
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendInvitation()">
                    <i class="fas fa-paper-plane me-2"></i>Send Invitation
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Team Member</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm" method="POST" action="<?= ORGANIZER_URL ?>/settings/users">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="user_id" id="editUserId">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="first_name" id="editFirstName">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="last_name" id="editLastName">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" id="editEmail">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" id="editRole">
                            <option value="owner">Owner</option>
                            <option value="manager">Manager</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="active" id="editActive">
                        <label class="form-check-label">
                            Active (user can access the system)
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateUser()">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function searchMembers() {
    const searchTerm = document.getElementById('searchMembers').value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function sendInvitation() {
    const form = document.getElementById('inviteUserForm');
    if (form.checkValidity()) {
        form.submit();
    } else {
        form.reportValidity();
    }
}

function editUser(userId) {
    // In a real implementation, you would fetch user data via AJAX
    console.log('Editing user:', userId);
    
    // For now, just show the modal
    const modal = new coreui.Modal(document.getElementById('editUserModal'));
    modal.show();
    
    // Set the user ID
    document.getElementById('editUserId').value = userId;
}

function updateUser() {
    const form = document.getElementById('editUserForm');
    if (form.checkValidity()) {
        form.submit();
    } else {
        form.reportValidity();
    }
}

function changeRole(userId) {
    const newRole = prompt('Enter new role (owner, manager, staff):');
    if (newRole && ['owner', 'manager', 'staff'].includes(newRole.toLowerCase())) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= ORGANIZER_URL ?>/settings/users';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'update_role';
        
        const userIdInput = document.createElement('input');
        userIdInput.type = 'hidden';
        userIdInput.name = 'user_id';
        userIdInput.value = userId;
        
        const roleInput = document.createElement('input');
        roleInput.type = 'hidden';
        roleInput.name = 'role';
        roleInput.value = newRole.toLowerCase();
        
        form.appendChild(actionInput);
        form.appendChild(userIdInput);
        form.appendChild(roleInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function deactivateUser(userId) {
    if (confirm('Are you sure you want to deactivate this user? They will lose access to the system.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= ORGANIZER_URL ?>/settings/users';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'deactivate';
        
        const userIdInput = document.createElement('input');
        userIdInput.type = 'hidden';
        userIdInput.name = 'user_id';
        userIdInput.value = userId;
        
        form.appendChild(actionInput);
        form.appendChild(userIdInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function activateUser(userId) {
    if (confirm('Are you sure you want to activate this user?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= ORGANIZER_URL ?>/settings/users';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'activate';
        
        const userIdInput = document.createElement('input');
        userIdInput.type = 'hidden';
        userIdInput.name = 'user_id';
        userIdInput.value = userId;
        
        form.appendChild(actionInput);
        form.appendChild(userIdInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Add search functionality on input
document.getElementById('searchMembers').addEventListener('input', searchMembers);
</script>
