<!-- Audit Logs -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-history text-info me-2"></i>
            Audit Logs
        </h2>
        <p class="text-muted mb-0">System audit trail and activity monitoring</p>
    </div>
    <div>
        <button class="btn btn-outline-info" onclick="exportAuditLogs()">
            <i class="fas fa-download me-2"></i>Export Logs
        </button>
        <button class="btn btn-outline-secondary" onclick="location.reload()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
    </div>
</div>

<!-- Audit Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($audit['total_logs'] ?? 0) ?></div>
                    <div>Total Logs</div>
                    <div class="small">All time</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-history fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-primary">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($audit['today_logs'] ?? 0) ?></div>
                    <div>Today's Logs</div>
                    <div class="small">Last 24 hours</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-warning">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($audit['critical_events'] ?? 0) ?></div>
                    <div>Critical Events</div>
                    <div class="small">Requires attention</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($audit['unique_users'] ?? 0) ?></div>
                    <div>Active Users</div>
                    <div class="small">Today</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-users fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Options -->
<div class="row mb-4">
    <div class="col-md-3">
        <input type="text" class="form-control" id="auditSearch" placeholder="Search logs..." onkeyup="filterLogs()">
    </div>
    <div class="col-md-2">
        <select class="form-select" id="actionFilter" onchange="filterLogs()">
            <option value="">All Actions</option>
            <option value="login">Login</option>
            <option value="logout">Logout</option>
            <option value="create">Create</option>
            <option value="update">Update</option>
            <option value="delete">Delete</option>
            <option value="suspend">Suspend</option>
        </select>
    </div>
    <div class="col-md-2">
        <select class="form-select" id="userTypeFilter" onchange="filterLogs()">
            <option value="">All Users</option>
            <option value="admin">Admins</option>
            <option value="tenant">Tenants</option>
            <option value="user">Users</option>
        </select>
    </div>
    <div class="col-md-2">
        <input type="date" class="form-control" id="dateFilter" onchange="filterLogs()">
    </div>
    <div class="col-md-3">
        <div class="btn-group w-100" role="group">
            <button class="btn btn-outline-secondary" onclick="setTimeRange('today')">Today</button>
            <button class="btn btn-outline-secondary" onclick="setTimeRange('week')">Week</button>
            <button class="btn btn-outline-secondary" onclick="setTimeRange('month')">Month</button>
        </div>
    </div>
</div>

<!-- Audit Logs Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            System Audit Trail
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($audit['logs'])): ?>
            <div class="table-responsive">
                <table class="table table-hover table-sm" id="auditTable">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Resource</th>
                            <th>IP Address</th>
                            <th>User Agent</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($audit['logs'] as $log): ?>
                        <tr data-action="<?= $log['action'] ?? '' ?>" 
                            data-user-type="<?= $log['user_type'] ?? '' ?>"
                            data-date="<?= date('Y-m-d', strtotime($log['created_at'] ?? 'now')) ?>">
                            <td>
                                <div><?= date('M j, Y H:i:s', strtotime($log['created_at'] ?? 'now')) ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($log['user_name'] ?? 'System') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($log['user_email'] ?? '') ?></small>
                            </td>
                            <td>
                                <?php 
                                $action = $log['action'] ?? 'unknown';
                                $actionClass = match($action) {
                                    'login' => 'bg-success',
                                    'logout' => 'bg-secondary',
                                    'create' => 'bg-primary',
                                    'update' => 'bg-info',
                                    'delete' => 'bg-danger',
                                    'suspend' => 'bg-warning',
                                    default => 'bg-dark'
                                };
                                ?>
                                <span class="badge <?= $actionClass ?>"><?= ucfirst($action) ?></span>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($log['resource_type'] ?? 'N/A') ?></div>
                                <?php if (!empty($log['resource_id'])): ?>
                                    <small class="text-muted">ID: <?= $log['resource_id'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code><?= htmlspecialchars($log['ip_address'] ?? 'Unknown') ?></code>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= htmlspecialchars(substr($log['user_agent'] ?? 'Unknown', 0, 30)) ?>...
                                </small>
                            </td>
                            <td>
                                <?php 
                                $status = $log['status'] ?? 'success';
                                $statusClass = $status === 'success' ? 'bg-success' : 'bg-danger';
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= ucfirst($status) ?></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewLogDetails(<?= $log['id'] ?? 0 ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <nav aria-label="Audit logs pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item"><a class="page-link" href="#">Previous</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">Next</a></li>
                </ul>
            </nav>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                <h5>No Audit Logs</h5>
                <p class="text-muted">No audit logs found for the selected criteria.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Audit Log Details</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="logDetailsContent">
                    <!-- Details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function filterLogs() {
    const searchTerm = document.getElementById('auditSearch').value.toLowerCase();
    const actionFilter = document.getElementById('actionFilter').value;
    const userTypeFilter = document.getElementById('userTypeFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;
    const rows = document.querySelectorAll('#auditTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const action = row.getAttribute('data-action');
        const userType = row.getAttribute('data-user-type');
        const date = row.getAttribute('data-date');
        
        const matchesSearch = text.includes(searchTerm);
        const matchesAction = !actionFilter || action === actionFilter;
        const matchesUserType = !userTypeFilter || userType === userTypeFilter;
        const matchesDate = !dateFilter || date === dateFilter;
        
        row.style.display = matchesSearch && matchesAction && matchesUserType && matchesDate ? '' : 'none';
    });
}

function setTimeRange(range) {
    const dateFilter = document.getElementById('dateFilter');
    const today = new Date();
    
    switch(range) {
        case 'today':
            dateFilter.value = today.toISOString().split('T')[0];
            break;
        case 'week':
            const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
            dateFilter.value = weekAgo.toISOString().split('T')[0];
            break;
        case 'month':
            const monthAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
            dateFilter.value = monthAgo.toISOString().split('T')[0];
            break;
    }
    
    filterLogs();
}

function viewLogDetails(logId) {
    // Load detailed log information
    document.getElementById('logDetailsContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading log details...</p>
        </div>
    `;
    
    const modal = new coreui.Modal(document.getElementById('logDetailsModal'));
    modal.show();
    
    // Simulate loading log details
    setTimeout(() => {
        document.getElementById('logDetailsContent').innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Log Information</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Log ID:</strong></td><td>${logId}</td></tr>
                        <tr><td><strong>Timestamp:</strong></td><td>${new Date().toLocaleString()}</td></tr>
                        <tr><td><strong>Action:</strong></td><td>User Login</td></tr>
                        <tr><td><strong>Status:</strong></td><td><span class="badge bg-success">Success</span></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>User Information</h6>
                    <table class="table table-sm">
                        <tr><td><strong>User:</strong></td><td>John Doe</td></tr>
                        <tr><td><strong>Email:</strong></td><td>john@example.com</td></tr>
                        <tr><td><strong>IP Address:</strong></td><td>192.168.1.100</td></tr>
                        <tr><td><strong>User Agent:</strong></td><td>Mozilla/5.0...</td></tr>
                    </table>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <h6>Additional Details</h6>
                    <pre class="bg-light p-3 rounded"><code>{"resource_id": 123, "changes": {"status": "active"}, "metadata": {"session_id": "abc123"}}</code></pre>
                </div>
            </div>
        `;
    }, 1000);
}

function exportAuditLogs() {
    console.log('Exporting audit logs...');
    alert('Audit logs export initiated. You will receive an email when ready.');
}
</script>
