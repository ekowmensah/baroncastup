/**
 * Super Admin Dashboard JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize CoreUI components
    if (typeof coreui !== 'undefined') {
        const sidebar = document.querySelector('#sidebar');
        if (sidebar) {
            new coreui.Sidebar(sidebar);
        }

        // Initialize all CoreUI components safely
        const tooltipElements = document.querySelectorAll('[data-coreui-toggle="tooltip"]');
        if (tooltipElements.length > 0) {
            tooltipElements.forEach(element => {
                try {
                    coreui.Tooltip.getOrCreateInstance(element);
                } catch (e) {
                    console.warn('Could not initialize CoreUI tooltip:', e);
                }
            });
        }
        
        const dropdownElements = document.querySelectorAll('[data-coreui-toggle="dropdown"]');
        if (dropdownElements.length > 0) {
            dropdownElements.forEach(element => {
                try {
                    coreui.Dropdown.getOrCreateInstance(element);
                } catch (e) {
                    console.warn('Could not initialize CoreUI dropdown:', e);
                }
            });
        }
    }

    // Initialize Bootstrap tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        const bsTooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        if (bsTooltipElements.length > 0) {
            bsTooltipElements.forEach(element => {
                try {
                    new bootstrap.Tooltip(element);
                } catch (e) {
                    console.warn('Could not initialize Bootstrap tooltip:', e);
                }
            });
        }
    }

    // Auto-refresh for super admin dashboard
    if (window.location.pathname.includes('/superadmin') && window.location.pathname.endsWith('/')) {
        setInterval(refreshPlatformStats, 60000); // Every minute
    }

    // Initialize platform monitoring
    initializePlatformMonitoring();

    // Initialize real-time alerts
    initializeRealTimeAlerts();

    // Initialize advanced analytics
    initializeAnalytics();
});

function refreshPlatformStats() {
    fetch(window.location.href + '/stats', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updatePlatformStats(data.stats);
            updateSystemHealth(data.health);
            updateSecurityStatus(data.security);
        }
    })
    .catch(error => {
        console.log('Platform stats refresh failed:', error);
    });
}

function updatePlatformStats(stats) {
    // Update platform-wide statistics
    Object.keys(stats).forEach(key => {
        const element = document.getElementById(`platform-stat-${key}`);
        if (element) {
            element.textContent = formatPlatformNumber(stats[key]);
        }
    });

    // Update growth indicators
    updateGrowthIndicators(stats.growth || {});
}

function updateGrowthIndicators(growth) {
    Object.keys(growth).forEach(metric => {
        const indicator = document.getElementById(`growth-${metric}`);
        if (indicator) {
            const value = growth[metric];
            const isPositive = value >= 0;
            
            indicator.className = `badge bg-${isPositive ? 'success' : 'danger'}`;
            indicator.innerHTML = `
                <i class="fas fa-arrow-${isPositive ? 'up' : 'down'} me-1"></i>
                ${Math.abs(value).toFixed(1)}%
            `;
        }
    });
}

function updateSystemHealth(health) {
    const healthContainer = document.getElementById('system-health');
    if (healthContainer && health) {
        let healthHtml = '';
        
        Object.keys(health).forEach(service => {
            const status = health[service];
            healthHtml += `
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="health-indicator ${status.status} mb-2"></div>
                            <h6>${service}</h6>
                            <small class="text-muted">${status.message}</small>
                        </div>
                    </div>
                </div>
            `;
        });
        
        healthContainer.innerHTML = healthHtml;
    }
}

function updateSecurityStatus(security) {
    const securityContainer = document.getElementById('security-overview');
    if (securityContainer && security) {
        // Update threat level
        const threatLevel = document.getElementById('threat-level');
        if (threatLevel) {
            threatLevel.className = `alert alert-${security.threatLevel}`;
            threatLevel.innerHTML = `
                <i class="fas fa-shield-alt me-2"></i>
                Threat Level: ${security.threatLevel.toUpperCase()}
            `;
        }

        // Update security metrics
        updateSecurityMetrics(security.metrics || {});
    }
}

function updateSecurityMetrics(metrics) {
    Object.keys(metrics).forEach(metric => {
        const element = document.getElementById(`security-${metric}`);
        if (element) {
            element.textContent = formatPlatformNumber(metrics[metric]);
        }
    });
}

function formatPlatformNumber(num) {
    if (num >= 1000000000) {
        return (num / 1000000000).toFixed(1) + 'B';
    } else if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toLocaleString();
}

function initializePlatformMonitoring() {
    // Real-time platform monitoring
    const monitoringPanel = document.getElementById('platform-monitoring');
    if (monitoringPanel) {
        setInterval(checkPlatformHealth, 30000); // Every 30 seconds
    }
}

function checkPlatformHealth() {
    fetch('/superadmin/platform/health', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        updatePlatformHealth(data);
    })
    .catch(error => {
        console.log('Platform health check failed:', error);
    });
}

function updatePlatformHealth(data) {
    const statusIndicator = document.getElementById('platform-status');
    if (statusIndicator) {
        statusIndicator.className = `badge bg-${data.status === 'healthy' ? 'success' : 'danger'}`;
        statusIndicator.textContent = data.status.toUpperCase();
    }

    // Update individual service statuses
    if (data.services) {
        Object.keys(data.services).forEach(service => {
            const serviceIndicator = document.getElementById(`service-${service}`);
            if (serviceIndicator) {
                const status = data.services[service];
                serviceIndicator.className = `health-indicator ${status.status}`;
                serviceIndicator.title = `${service}: ${status.message}`;
            }
        });
    }
}

function initializeRealTimeAlerts() {
    // WebSocket or polling for real-time alerts
    setInterval(checkCriticalAlerts, 15000); // Every 15 seconds
}

function checkCriticalAlerts() {
    // Temporarily disabled - endpoint not yet implemented
    // Uncomment when /superadmin/alerts/critical endpoint is ready
    /*
    fetch('/superadmin/alerts/critical', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.alerts && data.alerts.length > 0) {
            showCriticalAlerts(data.alerts);
        }
    })
    .catch(error => {
        console.log('Critical alerts check failed:', error);
    });
    */
}

function showCriticalAlerts(alerts) {
    const alertContainer = document.getElementById('critical-alerts');
    if (alertContainer) {
        let alertsHtml = '';
        alerts.forEach(alert => {
            alertsHtml += `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>CRITICAL:</strong> ${alert.message}
                    <small class="d-block mt-1">${alert.timestamp}</small>
                    <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
                </div>
            `;
        });
        alertContainer.innerHTML = alertsHtml;
    }
}

function initializeAnalytics() {
    // Initialize advanced analytics charts
    if (typeof Chart !== 'undefined') {
        initializePlatformCharts();
    }
}

function initializePlatformCharts() {
    // Revenue chart
    const revenueChart = document.getElementById('revenue-chart');
    if (revenueChart) {
        createPlatformChart(revenueChart, 'line', 'revenue');
    }

    // User growth chart
    const userChart = document.getElementById('user-growth-chart');
    if (userChart) {
        createPlatformChart(userChart, 'bar', 'users');
    }

    // Tenant distribution chart
    const tenantChart = document.getElementById('tenant-chart');
    if (tenantChart) {
        createPlatformChart(tenantChart, 'doughnut', 'tenants');
    }
}

function createPlatformChart(element, type, dataType) {
    const ctx = element.getContext('2d');
    
    // Fetch chart data
    fetch(`/superadmin/analytics/${dataType}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        new Chart(ctx, {
            type: type,
            data: data.chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    })
    .catch(error => {
        console.log(`Failed to load ${dataType} chart:`, error);
    });
}

// Super Admin specific functions
function suspendTenant(tenantId, reason) {
    const confirmMessage = `Are you sure you want to suspend this tenant?\nReason: ${reason}`;
    
    if (confirm(confirmMessage)) {
        fetch(`/superadmin/tenants/${tenantId}/suspend`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to suspend tenant: ' + data.message);
            }
        });
    }
}

function emergencyShutdown() {
    const confirmMessage = 'EMERGENCY SHUTDOWN: This will disable all platform services. Are you absolutely sure?';
    
    if (confirm(confirmMessage)) {
        const secondConfirm = prompt('Type "EMERGENCY SHUTDOWN" to confirm:');
        if (secondConfirm === 'EMERGENCY SHUTDOWN') {
            fetch('/superadmin/system/emergency-shutdown', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            });
        }
    }
}

function maintenanceMode(enable) {
    const action = enable ? 'enable' : 'disable';
    const confirmMessage = `Are you sure you want to ${action} maintenance mode?`;
    
    if (confirm(confirmMessage)) {
        fetch(`/superadmin/system/maintenance/${action}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(`Failed to ${action} maintenance mode: ` + data.message);
            }
        });
    }
}

// Export super admin functions
window.SuperAdminDashboard = {
    suspendTenant: suspendTenant,
    emergencyShutdown: emergencyShutdown,
    maintenanceMode: maintenanceMode,
    refreshStats: refreshPlatformStats
};
