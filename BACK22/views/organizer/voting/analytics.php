<!-- Voting Analytics Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-chart-pie me-2"></i>
            Voting Analytics
        </h2>
        <p class="text-muted mb-0">Deep insights into voting patterns and behavior</p>
    </div>
    <div>
        <div class="btn-group" role="group">
            <button class="btn btn-outline-primary" onclick="refreshAnalytics()">
                <i class="fas fa-sync me-2"></i>Refresh
            </button>
            <button class="btn btn-outline-success" onclick="exportAnalytics()">
                <i class="fas fa-download me-2"></i>Export Report
            </button>
        </div>
    </div>
</div>

<!-- Analytics Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <label class="form-label small">Time Period</label>
                        <select class="form-select form-select-sm" id="timePeriod">
                            <option value="24h" <?= $selectedPeriod === '24h' ? 'selected' : '' ?>>Last 24 hours</option>
                            <option value="7d" <?= $selectedPeriod === '7d' ? 'selected' : '' ?>>Last 7 days</option>
                            <option value="30d" <?= $selectedPeriod === '30d' ? 'selected' : '' ?>>Last 30 days</option>
                            <option value="90d" <?= $selectedPeriod === '90d' ? 'selected' : '' ?>>Last 90 days</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Event</label>
                        <select class="form-select form-select-sm" id="eventFilter">
                            <option value="">All Events</option>
                            <?php if (!empty($events)): ?>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?= $event['id'] ?>" <?= $selectedEventId == $event['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($event['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Category</label>
                        <select class="form-select form-select-sm" id="categoryFilter">
                            <option value="">All Categories</option>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>">
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">&nbsp;</label>
                        <button class="btn btn-primary btn-sm w-100" onclick="applyFilters()">
                            <i class="fas fa-filter me-1"></i>Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($votingStats['total_votes'] ?? 0) ?></div>
                    <div>Total Votes</div>
                    <div class="small">+15% vs last period</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-vote-yea fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card success text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($votingStats['unique_voters'] ?? 0) ?></div>
                    <div>Unique Voters</div>
                    <div class="small"><?= $votingStats['unique_voters'] > 0 ? number_format(($votingStats['total_votes'] ?? 0) / $votingStats['unique_voters'], 1) : 0 ?> avg votes/voter</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-users fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card warning text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">GHS <?= number_format($votingStats['total_revenue'] ?? 0, 2) ?></div>
                    <div>Vote Revenue</div>
                    <div class="small">GHS <?= $votingStats['total_votes'] > 0 ? number_format(($votingStats['total_revenue'] ?? 0) / $votingStats['total_votes'], 2) : 0 ?> per vote</div>
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
                    <div class="fs-4 fw-semibold"><?= $votingStats['events_with_votes'] ?? 0 ?></div>
                    <div>Active Events</div>
                    <div class="small">With votes</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-chart-line fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Voting Patterns -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Voting Patterns Over Time</h5>
            </div>
            <div class="card-body">
                <canvas id="votingPatternsChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Vote Distribution -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Vote Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="voteDistributionChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Voter Demographics -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Voter Demographics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-4">
                        <div class="fs-5 fw-semibold text-primary">45%</div>
                        <div class="small text-muted">Mobile</div>
                    </div>
                    <div class="col-4">
                        <div class="fs-5 fw-semibold text-success">35%</div>
                        <div class="small text-muted">Desktop</div>
                    </div>
                    <div class="col-4">
                        <div class="fs-5 fw-semibold text-info">20%</div>
                        <div class="small text-muted">Tablet</div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Peak Hours (6-9 PM)</span>
                        <span class="small fw-semibold">42%</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: 42%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Weekend Activity</span>
                        <span class="small fw-semibold">68%</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: 68%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Repeat Voters</span>
                        <span class="small fw-semibold">34%</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-warning" style="width: 34%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top Performing Content -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Performance Insights</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Metric</th>
                                <th>Value</th>
                                <th>Trend</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Avg. Session Duration</td>
                                <td>4m 32s</td>
                                <td><span class="badge bg-success">+12%</span></td>
                            </tr>
                            <tr>
                                <td>Bounce Rate</td>
                                <td>23%</td>
                                <td><span class="badge bg-success">-8%</span></td>
                            </tr>
                            <tr>
                                <td>Conversion Rate</td>
                                <td>67%</td>
                                <td><span class="badge bg-success">+5%</span></td>
                            </tr>
                            <tr>
                                <td>Avg. Votes per User</td>
                                <td>6.4</td>
                                <td><span class="badge bg-warning">-2%</span></td>
                            </tr>
                            <tr>
                                <td>Peak Concurrent Users</td>
                                <td>89</td>
                                <td><span class="badge bg-success">+23%</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Analytics -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Contestant Performance Analysis</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Contestant</th>
                                <th>Total Votes</th>
                                <th>Unique Voters</th>
                                <th>Avg Votes/Voter</th>
                                <th>Revenue</th>
                                <th>Growth Rate</th>
                                <th>Peak Hour</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($performanceInsights)): ?>
                                <?php foreach ($performanceInsights as $contestant): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($contestant['image_url']): ?>
                                                    <img src="<?= htmlspecialchars(image_url($contestant['image_url'])) ?>" 
                                                         class="rounded-circle me-2" width="32" height="32"
                                                         style="object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 32px; height: 32px; font-size: 12px;">
                                                        <?= strtoupper(substr($contestant['name'], 0, 1)) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <div class="fw-semibold"><?= htmlspecialchars($contestant['name']) ?></div>
                                                    <div class="small text-muted"><?= htmlspecialchars($contestant['contestant_code'] ?? 'N/A') ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= number_format($contestant['total_votes']) ?></div>
                                            <div class="small text-muted">Total votes</div>
                                        </td>
                                        <td><?= number_format($contestant['unique_voters']) ?></td>
                                        <td><?= number_format($contestant['avg_votes_per_voter'], 1) ?></td>
                                        <td>GHS <?= number_format($contestant['revenue'], 2) ?></td>
                                        <td><span class="badge bg-info">Active</span></td>
                                        <td><?= $contestant['peak_hour'] ? sprintf('%02d:00', $contestant['peak_hour']) : 'N/A' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-chart-bar fa-2x mb-2 opacity-50"></i>
                                            <p class="mb-0">No performance data available for the selected period</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
});

function initializeCharts() {
    const votingTrends = <?= json_encode($votingTrends) ?>;
    const topContestants = <?= json_encode($topContestants) ?>;
    
    // Voting Patterns Chart
    const patternsCtx = document.getElementById('votingPatternsChart').getContext('2d');
    
    // Prepare data from real voting trends
    const labels = [];
    const votesData = [];
    const revenueData = [];
    
    if (votingTrends && votingTrends.length > 0) {
        votingTrends.forEach(trend => {
            labels.push(trend.period);
            votesData.push(parseInt(trend.votes));
            revenueData.push(parseFloat(trend.revenue));
        });
    } else {
        // Default empty data
        labels.push('No Data');
        votesData.push(0);
        revenueData.push(0);
    }
    
    new Chart(patternsCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Votes',
                data: votesData,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4
            }, {
                label: 'Revenue (GHS)',
                data: revenueData,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Votes'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Revenue (GHS)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
    
    // Vote Distribution Chart
    const distributionCtx = document.getElementById('voteDistributionChart').getContext('2d');
    
    // Prepare data from top contestants
    const contestantLabels = [];
    const contestantData = [];
    const contestantColors = ['#ffc107', '#007bff', '#17a2b8', '#28a745', '#dc3545', '#6f42c1'];
    
    if (topContestants && topContestants.length > 0) {
        const totalVotes = topContestants.reduce((sum, contestant) => sum + parseInt(contestant.total_votes), 0);
        
        topContestants.slice(0, 5).forEach((contestant, index) => {
            contestantLabels.push(contestant.name);
            const percentage = totalVotes > 0 ? (parseInt(contestant.total_votes) / totalVotes) * 100 : 0;
            contestantData.push(percentage);
        });
        
        // Add "Others" if there are more than 5 contestants
        if (topContestants.length > 5) {
            const othersVotes = topContestants.slice(5).reduce((sum, contestant) => sum + parseInt(contestant.total_votes), 0);
            const othersPercentage = totalVotes > 0 ? (othersVotes / totalVotes) * 100 : 0;
            if (othersPercentage > 0) {
                contestantLabels.push('Others');
                contestantData.push(othersPercentage);
            }
        }
    } else {
        contestantLabels.push('No Data');
        contestantData.push(100);
    }
    
    new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: contestantLabels,
            datasets: [{
                data: contestantData,
                backgroundColor: contestantColors.slice(0, contestantLabels.length)
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed.toFixed(1) + '%';
                        }
                    }
                }
            }
        }
    });
}

function refreshAnalytics() {
    const url = new URL(window.location.href);
    url.searchParams.set('ajax', '1');
    
    fetch(url.toString())
        .then(response => response.json())
        .then(data => {
            // Update stats cards
            updateStatsCards(data.votingStats);
            // Reinitialize charts with new data
            location.reload(); // For now, just reload to update charts
        })
        .catch(error => {
            console.error('Error refreshing analytics:', error);
        });
}

function exportAnalytics() {
    const timePeriod = document.getElementById('timePeriod').value;
    const eventFilter = document.getElementById('eventFilter').value;
    const categoryFilter = document.getElementById('categoryFilter').value;
    
    const params = new URLSearchParams({
        export: 'analytics',
        period: timePeriod,
        event: eventFilter,
        category: categoryFilter
    });
    
    window.open(`<?= ORGANIZER_URL ?>/voting/export?${params.toString()}`, '_blank');
}

function applyFilters() {
    const timePeriod = document.getElementById('timePeriod').value;
    const eventFilter = document.getElementById('eventFilter').value;
    const categoryFilter = document.getElementById('categoryFilter').value;
    
    const url = new URL(window.location.href);
    url.searchParams.set('period', timePeriod);
    
    if (eventFilter) {
        url.searchParams.set('event', eventFilter);
    } else {
        url.searchParams.delete('event');
    }
    
    if (categoryFilter) {
        url.searchParams.set('category', categoryFilter);
    } else {
        url.searchParams.delete('category');
    }
    
    window.location.href = url.toString();
}

function updateStatsCards(stats) {
    // Update the stats cards with new data
    if (stats) {
        document.querySelector('.stats-card:nth-child(1) .fs-4').textContent = new Intl.NumberFormat().format(stats.total_votes || 0);
        document.querySelector('.stats-card:nth-child(2) .fs-4').textContent = new Intl.NumberFormat().format(stats.unique_voters || 0);
        document.querySelector('.stats-card:nth-child(3) .fs-4').textContent = 'GHS ' + new Intl.NumberFormat('en-US', {minimumFractionDigits: 2}).format(stats.total_revenue || 0);
        document.querySelector('.stats-card:nth-child(4) .fs-4').textContent = stats.events_with_votes || 0;
    }
}
</script>
