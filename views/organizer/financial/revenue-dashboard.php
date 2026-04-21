<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
/* Embedded CSS for Revenue Dashboard */
:root {
    --primary: #6366f1;
    --primary-dark: #4f46e5;
    --success: #10b981;
    --info: #3b82f6;
    --warning: #f59e0b;
    --danger: #ef4444;
    --bg-main: #f1f5f9;
    --bg-card: #ffffff;
    --text-primary: #0f172a;
    --text-secondary: #475569;
    --text-muted: #94a3b8;
    --border: #e2e8f0;
}

.revenue-dashboard-modern {
    margin-left: calc(-50vw + 50%);
    margin-right: calc(-50vw + 50%);
    width: 100vw;
    position: relative;
    min-height: 100vh;
    background: var(--bg-main);
}

.revenue-hero {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    padding: 3rem 0;
    color: white;
    position: relative;
    overflow: hidden;
}

.hero-content {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.pulse-dot {
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.hero-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
}

.hero-subtitle {
    font-size: 1.125rem;
    opacity: 0.9;
    margin: 0;
}

.hero-actions {
    display: flex;
    gap: 1rem;
}

.btn-hero {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.5rem;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-hero.btn-primary {
    background: white;
    color: var(--primary);
}

.btn-hero.btn-secondary {
    background: rgba(255,255,255,0.2);
    color: white;
    border: 1px solid rgba(255,255,255,0.3);
}

.btn-hero:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.stats-section {
    padding: 2rem 0;
    margin-top: -2rem;
    position: relative;
    z-index: 2;
}

.stats-grid-modern {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

.stat-card-modern {
    background: var(--bg-card);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border-left: 4px solid var(--primary);
}

.stat-card-modern.stat-success {
    border-left-color: var(--success);
}

.stat-card-modern.stat-info {
    border-left-color: var(--info);
}

.stat-card-modern.stat-warning {
    border-left-color: var(--warning);
}

.stat-card-modern:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.stat-icon-modern {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.stat-card-modern.stat-success .stat-icon-modern {
    background: linear-gradient(135deg, var(--success), #059669);
}

.stat-card-modern.stat-info .stat-icon-modern {
    background: linear-gradient(135deg, var(--info), #2563eb);
}

.stat-card-modern.stat-warning .stat-icon-modern {
    background: linear-gradient(135deg, var(--warning), #d97706);
}

.stat-badge {
    padding: 0.25rem 0.75rem;
    background: var(--bg-main);
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-secondary);
}

.stat-amount {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.stat-label-modern {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: 0.75rem;
}

.stat-footer {
    padding-top: 0.75rem;
    border-top: 1px solid var(--border);
}

.stat-trend-up {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--success);
}

.stat-trend-neutral {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-muted);
}

/* Simplified styles for other sections */
.main-content {
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.dashboard-card {
    background: var(--bg-card);
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 2rem;
}

.card-header-premium {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border);
}

.card-body-premium {
    padding: 1.5rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: var(--text-secondary);
}

/* Content Grid */
.content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.card-title-section {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.card-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
}

.card-subtitle {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin: 0.25rem 0 0 0;
}

/* Events List */
.events-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.event-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--bg-main);
    border-radius: 12px;
    transition: all 0.2s;
}

.event-item:hover {
    background: #e0e7ff;
    transform: translateX(4px);
}

.rank-badge {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.875rem;
}

.rank-badge.rank-1 {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
}

.rank-badge.rank-2 {
    background: linear-gradient(135deg, #94a3b8, #64748b);
}

.rank-badge.rank-3 {
    background: linear-gradient(135deg, #fb923c, #f97316);
}

.event-info {
    flex: 1;
}

.event-name {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.event-meta {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.revenue-amount {
    font-weight: 700;
    color: var(--success);
    font-size: 1.125rem;
}

/* Payout Info */
.payout-info {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: var(--bg-main);
    border-radius: 8px;
}

.info-label {
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.info-value {
    font-weight: 600;
    color: var(--text-primary);
}

.btn-payout-primary {
    width: 100%;
    padding: 0.875rem;
    background: linear-gradient(135deg, var(--success), #059669);
    border: none;
    border-radius: 12px;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-payout-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
}

.payout-notice {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: rgba(245, 158, 11, 0.1);
    border: 1px solid rgba(245, 158, 11, 0.3);
    border-radius: 8px;
    color: var(--warning);
    font-size: 0.875rem;
}

/* Transactions Table */
.transactions-section {
    margin-top: 2rem;
}

.transaction-filters {
    display: flex;
    gap: 0.5rem;
}

.filter-btn {
    padding: 0.5rem 1rem;
    background: var(--bg-main);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text-secondary);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-btn.active,
.filter-btn:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.transactions-table {
    width: 100%;
}

.table-header {
    display: grid;
    grid-template-columns: 1.2fr 2fr 0.8fr 1fr 1fr 1fr;
    gap: 1rem;
    padding: 1rem;
    background: var(--bg-main);
    border-radius: 12px;
    margin-bottom: 0.75rem;
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.table-body {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.transaction-row {
    display: grid;
    grid-template-columns: 1.2fr 2fr 0.8fr 1fr 1fr 1fr;
    gap: 1rem;
    padding: 1rem;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    transition: all 0.2s;
    align-items: center;
}

.transaction-row:hover {
    border-color: var(--primary);
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.date-primary {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.875rem;
}

.date-secondary {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
}

.event-primary {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.875rem;
}

.event-secondary {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
}

.votes-badge {
    padding: 0.375rem 0.875rem;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    color: white;
    display: inline-block;
}

.amount-primary {
    font-weight: 700;
    color: var(--text-primary);
    font-size: 0.875rem;
}

.fee-amount {
    font-weight: 700;
    color: var(--danger);
    font-size: 0.875rem;
}

.fee-percentage {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
}

.net-amount {
    font-weight: 700;
    color: var(--success);
    font-size: 0.875rem;
}

.empty-icon {
    width: 80px;
    height: 80px;
    border-radius: 20px;
    background: var(--bg-main);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2rem;
    color: var(--text-muted);
}

.empty-state h4, .empty-state h5 {
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: var(--text-secondary);
    margin-bottom: 1.5rem;
}

.btn-primary {
    padding: 0.875rem 1.5rem;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    border: none;
    border-radius: 12px;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
}

/* Chart */
.chart-container {
    position: relative;
    height: 300px;
}

.btn-group-premium {
    display: flex;
    border-radius: 8px;
    overflow: hidden;
    background: var(--bg-main);
}

.btn-premium {
    padding: 0.5rem 1rem;
    background: transparent;
    border: none;
    color: var(--text-secondary);
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-premium.active,
.btn-premium:hover {
    background: var(--primary);
    color: white;
}

/* Responsive */
@media (max-width: 1024px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .hero-content {
        flex-direction: column;
        text-align: center;
    }
    .hero-title {
        font-size: 2rem;
    }
    .stats-grid-modern {
        grid-template-columns: 1fr;
    }
    .table-header {
        display: none;
    }
    .transaction-row {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    .transaction-row > div {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
}
</style>

<!-- Modern Revenue Dashboard - Redesigned -->
<div class="revenue-dashboard-modern">
    <!-- Hero Header -->
    <div class="revenue-hero">
        <div class="container-fluid">
            <div class="hero-content">
                <div class="hero-left">
                    <div class="status-badge">
                        <span class="pulse-dot"></span>
                        <span>Live Dashboard</span>
                    </div>
                    <h1 class="hero-title">Revenue & Earnings</h1>
                    <p class="hero-subtitle">Track your earnings, monitor transactions, and manage payouts</p>
                </div>
                <div class="hero-actions">
                    <button class="btn-hero btn-secondary" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i>
                        <span>Refresh</span>
                    </button>
                    <button class="btn-hero btn-primary" onclick="requestPayout()" <?= ($balance['available'] < 10) ? 'disabled' : '' ?>>
                        <i class="fas fa-wallet"></i>
                        <span>Request Payout</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-section">
        <div class="container-fluid">
            <div class="stats-grid-modern">
                <!-- Available Balance -->
                <div class="stat-card-modern stat-primary">
                    <div class="stat-header">
                        <div class="stat-icon-modern">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="stat-badge">Available</div>
                    </div>
                    <div class="stat-body">
                        <div class="stat-amount">GH₵<?= number_format($balance['available'] ?? 0, 2) ?></div>
                        <div class="stat-label-modern">Ready for Payout</div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-trend-up"><i class="fas fa-arrow-up"></i> Ready</span>
                    </div>
                </div>

                <!-- Total Earned -->
                <div class="stat-card-modern stat-success">
                    <div class="stat-header">
                        <div class="stat-icon-modern">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="stat-badge">Total</div>
                    </div>
                    <div class="stat-body">
                        <div class="stat-amount">GH₵<?= number_format($balance['total_earned'] ?? 0, 2) ?></div>
                        <div class="stat-label-modern">All Time Earnings</div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-trend-up"><i class="fas fa-trophy"></i> Lifetime</span>
                    </div>
                </div>

                <!-- Today's Earnings -->
                <div class="stat-card-modern stat-info">
                    <div class="stat-header">
                        <div class="stat-icon-modern">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="stat-badge">Today</div>
                    </div>
                    <div class="stat-body">
                        <div class="stat-amount">GH₵<?= number_format($todayEarnings ?? 0, 2) ?></div>
                        <div class="stat-label-modern">Last 24 Hours</div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-trend-up"><i class="fas fa-clock"></i> Active</span>
                    </div>
                </div>

                <!-- Total Paid Out -->
                <div class="stat-card-modern stat-warning">
                    <div class="stat-header">
                        <div class="stat-icon-modern">
                            <i class="fas fa-money-check-alt"></i>
                        </div>
                        <div class="stat-badge">Paid</div>
                    </div>
                    <div class="stat-body">
                        <div class="stat-amount">GH₵<?= number_format($balance['total_paid'] ?? 0, 2) ?></div>
                        <div class="stat-label-modern">Total Payouts</div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-trend-neutral"><i class="fas fa-check-circle"></i> Completed</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="content-grid">
                
                <!-- Revenue Chart -->
                <div class="chart-section">
                    <div class="dashboard-card">
                        <div class="card-header-premium">
                            <div class="card-title-section">
                                <div class="card-icon">
                                    <i class="fas fa-chart-area"></i>
                                </div>
                                <div>
                                    <h3 class="card-title">Revenue Trend</h3>
                                    <p class="card-subtitle">Last 30 days performance</p>
                                </div>
                            </div>
                            <div class="chart-controls">
                                <div class="btn-group btn-group-premium">
                                    <button class="btn btn-premium active" onclick="updateChartPeriod('30d')">30D</button>
                                    <button class="btn btn-premium" onclick="updateChartPeriod('7d')">7D</button>
                                    <button class="btn btn-premium" onclick="updateChartPeriod('24h')">24H</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body-premium">
                            <div class="chart-container">
                                <canvas id="revenueChart" height="100"></canvas>
                                <!-- Debug Info for Chart -->
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">
                                    Chart Data: <?= isset($chartData) ? count($chartData) . ' data points' : 'No data' ?> | 
                                    Labels: <?= isset($chartLabels) ? count($chartLabels) . ' labels' : 'No labels' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Content -->
                <div class="sidebar-content">
                    
                    <!-- Top Earning Events -->
                    <div class="dashboard-card">
                        <div class="card-header-premium">
                            <div class="card-title-section">
                                <div class="card-icon">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <h4 class="card-title">Top Events</h4>
                            </div>
                        </div>
                        <div class="card-body-premium">
                            <?php if (!empty($topEvents)): ?>
                                <div class="events-list">
                                    <?php foreach ($topEvents as $index => $event): ?>
                                        <div class="event-item">
                                            <div class="event-rank">
                                                <span class="rank-badge rank-<?= $index + 1 ?>"><?= $index + 1 ?></span>
                                            </div>
                                            <div class="event-info">
                                                <div class="event-name"><?= htmlspecialchars($event['name']) ?></div>
                                                <div class="event-meta"><?= $event['transaction_count'] ?> transactions</div>
                                            </div>
                                            <div class="event-revenue">
                                                <div class="revenue-amount">GH₵<?= number_format($event['total_revenue'], 2) ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <h5>No Revenue Data</h5>
                                    <p>Create events and start receiving votes to see your top earning events here!</p>
                                    <!-- Debug Info -->
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 1rem;">
                                        Debug: <?= isset($topEvents) ? 'Array with ' . count($topEvents) . ' items' : 'Variable not set' ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Payout Information -->
                    <div class="dashboard-card">
                        <div class="card-header-premium">
                            <div class="card-title-section">
                                <div class="card-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <h4 class="card-title">Payout Info</h4>
                            </div>
                        </div>
                        <div class="card-body-premium">
                            <div class="payout-info">
                                <div class="info-item">
                                    <div class="info-label">Minimum Amount</div>
                                    <div class="info-value">GH₵10.00</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Processing Time</div>
                                    <div class="info-value">1-3 business days</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Next Auto-Payout</div>
                                    <div class="info-value">Monthly on 1st</div>
                                </div>
                            </div>
                            
                            <?php if ($balance['available'] >= 10): ?>
                                <div class="payout-action">
                                    <button class="btn-payout-primary" onclick="requestPayout()">
                                        <i class="fas fa-money-check-alt"></i>
                                        Request Payout Now
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="payout-notice">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Minimum payout amount not reached</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="transactions-section">
                <div class="dashboard-card">
                    <div class="card-header-premium">
                        <div class="card-title-section">
                            <div class="card-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            <div>
                                <h3 class="card-title">Recent Revenue Transactions</h3>
                                <p class="card-subtitle">Latest earnings from your events</p>
                            </div>
                        </div>
                        <div class="transaction-filters">
                            <button class="filter-btn active" onclick="filterTransactions('all')">All</button>
                            <button class="filter-btn" onclick="filterTransactions('today')">Today</button>
                            <button class="filter-btn" onclick="filterTransactions('week')">This Week</button>
                        </div>
                    </div>
                    <div class="card-body-premium">
                        <?php if (!empty($recentTransactions)): ?>
                            <div class="transactions-table">
                                <div class="table-header">
                                    <div class="col-date">Date & Time</div>
                                    <div class="col-event">Event & Contestant</div>
                                    <div class="col-votes">Votes</div>
                                    <div class="col-gross">Gross Amount</div>
                                    <div class="col-fee">Platform Fee</div>
                                    <div class="col-net">Your Earnings</div>
                                </div>
                                <div class="table-body">
                                    <?php foreach ($recentTransactions as $transaction): ?>
                                        <div class="transaction-row">
                                            <div class="col-date">
                                                <div class="date-primary"><?= date('M j, Y', strtotime($transaction['created_at'])) ?></div>
                                                <div class="date-secondary"><?= date('H:i', strtotime($transaction['created_at'])) ?></div>
                                            </div>
                                            <div class="col-event">
                                                <div class="event-primary"><?= htmlspecialchars($transaction['event_name']) ?></div>
                                                <div class="event-secondary"><?= htmlspecialchars($transaction['contestant_name']) ?></div>
                                            </div>
                                            <div class="col-votes">
                                                <span class="votes-badge"><?= $transaction['vote_count'] ?></span>
                                            </div>
                                            <div class="col-gross">
                                                <div class="amount-primary">GH₵<?= number_format($transaction['amount'], 2) ?></div>
                                            </div>
                                            <div class="col-fee">
                                                <?php 
                                                $platformFee = $transaction['calculated_platform_fee'] ?? $transaction['platform_fee'] ?? 0;
                                                $feePercentage = $transaction['calculated_fee_percentage'] ?? 0;
                                                
                                                if ($feePercentage == 0 && $transaction['amount'] > 0 && $platformFee > 0) {
                                                    $feePercentage = ($platformFee / $transaction['amount']) * 100;
                                                }
                                                ?>
                                                <div class="fee-amount">-GH₵<?= number_format($platformFee, 2) ?></div>
                                                <div class="fee-percentage"><?= number_format($feePercentage, 1) ?>%</div>
                                            </div>
                                            <div class="col-net">
                                                <?php 
                                                $netAmount = $transaction['calculated_net_amount'] ?? $transaction['net_amount'] ?? 0;
                                                ?>
                                                <div class="net-amount">+GH₵<?= number_format($netAmount, 2) ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h4>No Revenue Yet</h4>
                                <p>Start receiving votes to see your earnings here!</p>
                                <button class="btn-primary" onclick="window.location.href='<?= ORGANIZER_URL ?>/events'">
                                    <i class="fas fa-plus"></i>
                                    Create Your First Event
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles moved to external CSS file: /public/css/revenue-dashboard-modern.css -->

<script>
function requestPayout() {
    const availableBalance = <?= $balance['available'] ?? 0 ?>;
    
    if (availableBalance < 10) {
        alert('Minimum payout amount is GH₵10.00');
        return;
    }
    
    if (confirm(`Request payout of GH₵${availableBalance.toFixed(2)}?`)) {
        // Implementation for payout request
        fetch('/organizer/financial/request-payout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                amount: availableBalance
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Payout request submitted successfully! You will receive confirmation shortly.');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to submit payout request'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while submitting your payout request. Please try again.');
        });
    }
}

function updateChartPeriod(period) {
    // Remove active class from all buttons
    document.querySelectorAll('.btn-premium').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Add active class to clicked button
    event.target.classList.add('active');
    
    // Update chart data based on period
    // This would typically make an AJAX request to get new data
    console.log('Updating chart for period:', period);
}

function filterTransactions(filter) {
    // Remove active class from all filter buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Add active class to clicked button
    event.target.classList.add('active');
    
    // Filter transactions based on selection
    const rows = document.querySelectorAll('.transaction-row');
    const now = new Date();
    
    rows.forEach(row => {
        const dateText = row.querySelector('.date-primary').textContent;
        const transactionDate = new Date(dateText);
        let show = true;
        
        if (filter === 'today') {
            show = transactionDate.toDateString() === now.toDateString();
        } else if (filter === 'week') {
            const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
            show = transactionDate >= weekAgo;
        }
        
        row.style.display = show ? 'grid' : 'none';
    });
}

// Initialize revenue chart
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart !== 'undefined') {
        const ctx = document.getElementById('revenueChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($chartLabels ?? []) ?>,
                    datasets: [{
                        label: 'Daily Revenue (GH₵)',
                        data: <?= json_encode($chartData ?? []) ?>,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#6366f1',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                color: '#475569',
                                font: {
                                    size: 12,
                                    weight: '600'
                                },
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: '#ffffff',
                            titleColor: '#0f172a',
                            bodyColor: '#475569',
                            borderColor: '#e2e8f0',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return 'Revenue: GH₵' + context.parsed.y.toFixed(2);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: '#e2e8f0',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#64748b',
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#e2e8f0',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#64748b',
                                font: {
                                    size: 11
                                },
                                callback: function(value) {
                                    return 'GH₵' + value.toFixed(2);
                                }
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }
    }
});
</script>
