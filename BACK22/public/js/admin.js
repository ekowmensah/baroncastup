/**
 * Admin Dashboard JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize CoreUI components
    if (typeof coreui !== 'undefined') {
        // Initialize sidebar
        const sidebar = document.querySelector('#sidebar');
        if (sidebar) {
            new coreui.Sidebar(sidebar);
        }

        // Initialize all CoreUI components
        coreui.Tooltip.getOrCreateInstance('[data-coreui-toggle="tooltip"]');
        coreui.Dropdown.getOrCreateInstance('[data-coreui-toggle="dropdown"]');
    }

    // Auto-refresh for admin dashboard
    if (window.location.pathname.includes('/admin') && window.location.pathname.endsWith('/')) {
        setInterval(refreshAdminStats, 45000); // Every 45 seconds
    }

    // Initialize security monitoring
    initializeSecurityMonitoring();

    // Initialize system health checks
    initializeSystemHealth();

    // Enhanced table functionality
    initializeAdminTables();
});

function refreshAdminStats() {
    fetch(window.location.href + '/stats', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateAdminStats(data.stats);
            updateSecurityAlerts(data.security);
        }
    })
    .catch(error => {
        console.log('Admin stats refresh failed:', error);
    });
}

function updateAdminStats(stats) {
    // Update admin statistics
    Object.keys(stats).forEach(key => {
        const element = document.getElementById(`admin-stat-${key}`);
        if (element) {
            element.textContent = formatAdminNumber(stats[key]);
        }
    });
}

function updateSecurityAlerts(security) {
    const alertContainer = document.getElementById('security-alerts');
    if (alertContainer && security.alerts) {
        let alertsHtml = '';
        security.alerts.forEach(alert => {
            alertsHtml += `
                <div class="alert alert-${alert.level} alert-dismissible fade show" role="alert">
                    <i class="fas fa-${alert.icon} me-2"></i>
                    <strong>${alert.title}:</strong> ${alert.message}
                    <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
                </div>
            `;
        });
        alertContainer.innerHTML = alertsHtml;
    }
}

function formatAdminNumber(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toLocaleString();
}

function initializeSecurityMonitoring() {
    // Real-time security monitoring
    const securityPanel = document.getElementById('security-panel');
    if (securityPanel) {
        setInterval(checkSecurityStatus, 60000); // Every minute
    }
}

function checkSecurityStatus() {
    fetch('/admin/security/status', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        updateSecurityStatus(data);
    })
    .catch(error => {
        console.log('Security status check failed:', error);
    });
}

function updateSecurityStatus(data) {
    const statusIndicator = document.getElementById('security-status');
    if (statusIndicator) {
        statusIndicator.className = `badge bg-${data.status === 'secure' ? 'success' : 'warning'}`;
        statusIndicator.textContent = data.status.toUpperCase();
    }

    // Update threat level
    const threatLevel = document.getElementById('threat-level');
    if (threatLevel) {
        threatLevel.className = `alert alert-${data.threatLevel}`;
        threatLevel.textContent = `Threat Level: ${data.threatLevel.toUpperCase()}`;
    }
}

function initializeSystemHealth() {
    // System health monitoring
    const healthIndicators = document.querySelectorAll('.health-indicator');
    healthIndicators.forEach(indicator => {
        setInterval(() => {
            checkSystemHealth(indicator);
        }, 30000); // Every 30 seconds
    });
}

function checkSystemHealth(indicator) {
    const service = indicator.dataset.service;
    
    fetch(`/admin/system/health/${service}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        indicator.className = `health-indicator ${data.status}`;
        indicator.title = `${service}: ${data.message}`;
    })
    .catch(error => {
        indicator.className = 'health-indicator danger';
        indicator.title = `${service}: Connection failed`;
    });
}

function initializeAdminTables() {
    // Enhanced table functionality for admin
    const adminTables = document.querySelectorAll('.admin-table');
    adminTables.forEach(table => {
        addTableFilters(table);
        addBulkActions(table);
        addRowActions(table);
    });
}

function addTableFilters(table) {
    const filterContainer = table.parentNode.querySelector('.table-filters');
    if (filterContainer) {
        const filters = filterContainer.querySelectorAll('select, input');
        filters.forEach(filter => {
            filter.addEventListener('change', function() {
                filterTable(table, filters);
            });
        });
    }
}

function filterTable(table, filters) {
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        let showRow = true;
        
        filters.forEach(filter => {
            const column = filter.dataset.column;
            const value = filter.value.toLowerCase();
            
            if (value && column) {
                const cellIndex = parseInt(column);
                const cell = row.cells[cellIndex];
                const cellText = cell ? cell.textContent.toLowerCase() : '';
                
                if (!cellText.includes(value)) {
                    showRow = false;
                }
            }
        });
        
        row.style.display = showRow ? '' : 'none';
    });
}

function addBulkActions(table) {
    const bulkActions = table.parentNode.querySelector('.bulk-actions');
    if (bulkActions) {
        const selectAll = table.querySelector('thead input[type="checkbox"]');
        const rowCheckboxes = table.querySelectorAll('tbody input[type="checkbox"]');
        
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                rowCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                toggleBulkActions(bulkActions, this.checked);
            });
        }
        
        rowCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checkedCount = table.querySelectorAll('tbody input[type="checkbox"]:checked').length;
                toggleBulkActions(bulkActions, checkedCount > 0);
                
                if (selectAll) {
                    selectAll.checked = checkedCount === rowCheckboxes.length;
                    selectAll.indeterminate = checkedCount > 0 && checkedCount < rowCheckboxes.length;
                }
            });
        });
    }
}

function toggleBulkActions(bulkActions, show) {
    bulkActions.style.display = show ? 'block' : 'none';
}

function addRowActions(table) {
    // Add confirmation to delete buttons
    const deleteButtons = table.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const message = this.dataset.message || 'Are you sure you want to delete this item?';
            if (confirm(message)) {
                // Submit form or make AJAX request
                const form = this.closest('form');
                if (form) {
                    form.submit();
                } else {
                    window.location.href = this.href;
                }
            }
        });
    });
}

// Utility functions for admin
function suspendUser(userId, reason) {
    if (confirm('Are you sure you want to suspend this user?')) {
        fetch(`/admin/users/${userId}/suspend`, {
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
                alert('Failed to suspend user: ' + data.message);
            }
        });
    }
}

function approveEvent(eventId) {
    if (confirm('Are you sure you want to approve this event?')) {
        fetch(`/admin/events/${eventId}/approve`, {
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
                alert('Failed to approve event: ' + data.message);
            }
        });
    }
}

// Export admin functions
window.AdminDashboard = {
    suspendUser: suspendUser,
    approveEvent: approveEvent,
    refreshStats: refreshAdminStats
};
