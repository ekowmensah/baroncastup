<!-- System Maintenance -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-tools text-warning me-2"></i>
            System Maintenance
        </h2>
        <p class="text-muted mb-0">System maintenance tasks and health monitoring</p>
    </div>
    <div>
        <button class="btn btn-warning" onclick="runMaintenance()">
            <i class="fas fa-play me-2"></i>Run Maintenance
        </button>
        <button class="btn btn-outline-secondary" onclick="location.reload()">
            <i class="fas fa-sync me-2"></i>Refresh
        </button>
    </div>
</div>

<!-- System Health -->
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-heartbeat me-2"></i>
                    System Health Status
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="display-6 text-success mb-2">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h6>Database</h6>
                            <span class="badge bg-success">Healthy</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="display-6 text-success mb-2">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h6>File System</h6>
                            <span class="badge bg-success">Healthy</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="display-6 text-warning mb-2">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <h6>Cache</h6>
                            <span class="badge bg-warning">Needs Cleanup</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="display-6 text-success mb-2">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h6>Services</h6>
                            <span class="badge bg-success">Running</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Maintenance Tasks -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-tasks me-2"></i>
                    Maintenance Tasks
                </h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Clear Application Cache</h6>
                            <p class="mb-1 text-muted">Remove temporary files and cached data</p>
                            <small class="text-muted">Last run: 2 hours ago</small>
                        </div>
                        <button class="btn btn-outline-primary btn-sm" onclick="clearCache()">
                            <i class="fas fa-trash me-1"></i>Clear
                        </button>
                    </div>
                    
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Optimize Database</h6>
                            <p class="mb-1 text-muted">Optimize database tables and indexes</p>
                            <small class="text-muted">Last run: 1 day ago</small>
                        </div>
                        <button class="btn btn-outline-primary btn-sm" onclick="optimizeDatabase()">
                            <i class="fas fa-database me-1"></i>Optimize
                        </button>
                    </div>
                    
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Clean Log Files</h6>
                            <p class="mb-1 text-muted">Archive and clean old log files</p>
                            <small class="text-muted">Last run: 3 days ago</small>
                        </div>
                        <button class="btn btn-outline-primary btn-sm" onclick="cleanLogs()">
                            <i class="fas fa-file-alt me-1"></i>Clean
                        </button>
                    </div>
                    
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Update System Dependencies</h6>
                            <p class="mb-1 text-muted">Check and update system packages</p>
                            <small class="text-muted">Last run: 1 week ago</small>
                        </div>
                        <button class="btn btn-outline-warning btn-sm" onclick="updateDependencies()">
                            <i class="fas fa-download me-1"></i>Update
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    System Resources
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>CPU Usage</span>
                        <span>45%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: 45%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Memory Usage</span>
                        <span>62%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-warning" style="width: 62%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Disk Usage</span>
                        <span>38%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-info" style="width: 38%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Network I/O</span>
                        <span>25%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-primary" style="width: 25%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function runMaintenance() {
    if (confirm('Are you sure you want to run all maintenance tasks? This may take several minutes.')) {
        console.log('Running maintenance tasks...');
        alert('Maintenance tasks started. You will be notified when complete.');
    }
}

function clearCache() {
    console.log('Clearing cache...');
    alert('Cache cleared successfully!');
}

function optimizeDatabase() {
    console.log('Optimizing database...');
    alert('Database optimization started.');
}

function cleanLogs() {
    console.log('Cleaning logs...');
    alert('Log cleanup completed!');
}

function updateDependencies() {
    if (confirm('This will update system dependencies. Continue?')) {
        console.log('Updating dependencies...');
        alert('Dependency update started.');
    }
}
</script>
