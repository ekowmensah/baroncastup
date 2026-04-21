/**
 * Organizer Dashboard JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize CoreUI components
    if (typeof coreui !== 'undefined') {
        // Initialize sidebar
        const sidebar = document.querySelector('#sidebar');
        if (sidebar) {
            new coreui.Sidebar(sidebar);
        }

        // Initialize tooltips
        const tooltips = document.querySelectorAll('[data-coreui-toggle="tooltip"]');
        tooltips.forEach(tooltip => {
            new coreui.Tooltip(tooltip);
        });

        // Initialize dropdowns
        const dropdowns = document.querySelectorAll('[data-coreui-toggle="dropdown"]');
        dropdowns.forEach(dropdown => {
            new coreui.Dropdown(dropdown);
        });
    }

    // Auto-refresh dashboard stats every 30 seconds
    if (window.location.pathname.includes('/organizer') && window.location.pathname.endsWith('/')) {
        setInterval(refreshDashboardStats, 30000);
    }

    // Initialize charts if Chart.js is available
    initializeCharts();

    // Form enhancements
    enhanceForms();

    // Table enhancements
    enhanceTables();
});

function refreshDashboardStats() {
    // Refresh dashboard statistics via AJAX
    fetch(window.location.href + '/stats', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateStatsCards(data.stats);
        }
    })
    .catch(error => {
        console.log('Stats refresh failed:', error);
    });
}

function updateStatsCards(stats) {
    // Update statistics cards
    const statElements = {
        'total_events': document.getElementById('stat-total_events'),
        'active_events': document.getElementById('stat-active_events'),
        'total_contestants': document.getElementById('stat-total_contestants'),
        'total_votes': document.getElementById('stat-total_votes')
    };

    Object.keys(statElements).forEach(key => {
        const element = statElements[key];
        if (element && stats[key] !== undefined) {
            element.textContent = formatNumber(stats[key]);
        }
    });
}

function formatNumber(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toLocaleString();
}

function initializeCharts() {
    // Initialize dashboard charts
    const chartElements = document.querySelectorAll('.chart-container');
    chartElements.forEach(element => {
        const chartType = element.dataset.chartType;
        const chartData = JSON.parse(element.dataset.chartData || '{}');
        
        if (typeof Chart !== 'undefined') {
            createChart(element, chartType, chartData);
        }
    });
}

function createChart(element, type, data) {
    const ctx = element.getContext('2d');
    new Chart(ctx, {
        type: type,
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    display: false
                },
                x: {
                    display: false
                }
            }
        }
    });
}

function enhanceForms() {
    // Add form validation and enhancements
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                submitBtn.disabled = true;

                // Re-enable after 5 seconds as fallback
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 5000);
            }
        });
    });

    // File upload preview
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = document.querySelector(`#${input.id}-preview`);
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.id = `${input.id}-preview`;
                        preview.className = 'img-thumbnail mt-2';
                        preview.style.maxWidth = '200px';
                        input.parentNode.appendChild(preview);
                    }
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    });
}

function enhanceTables() {
    // Add table search functionality
    const searchInputs = document.querySelectorAll('.table-search');
    searchInputs.forEach(input => {
        const tableId = input.dataset.table;
        const table = document.getElementById(tableId);
        
        if (table) {
            input.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }
    });

    // Add sortable columns
    const sortableHeaders = document.querySelectorAll('.sortable');
    sortableHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            sortTable(this);
        });
    });
}

function sortTable(header) {
    const table = header.closest('table');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const columnIndex = Array.from(header.parentNode.children).indexOf(header);
    
    const isAscending = header.classList.contains('sort-asc');
    
    // Remove existing sort classes
    header.parentNode.querySelectorAll('th').forEach(th => {
        th.classList.remove('sort-asc', 'sort-desc');
    });
    
    // Add new sort class
    header.classList.add(isAscending ? 'sort-desc' : 'sort-asc');
    
    // Sort rows
    rows.sort((a, b) => {
        const aValue = a.children[columnIndex].textContent.trim();
        const bValue = b.children[columnIndex].textContent.trim();
        
        const comparison = isNaN(aValue) ? 
            aValue.localeCompare(bValue) : 
            parseFloat(aValue) - parseFloat(bValue);
            
        return isAscending ? -comparison : comparison;
    });
    
    // Reorder rows
    rows.forEach(row => tbody.appendChild(row));
}

// Delete confirmation
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

// Export functions
window.OrganizerDashboard = {
    refreshStats: refreshDashboardStats,
    confirmDelete: confirmDelete,
    formatNumber: formatNumber
};
