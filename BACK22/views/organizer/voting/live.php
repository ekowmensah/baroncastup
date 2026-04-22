<!-- Premium Live Results Dashboard -->
<div class="live-results-wrapper">
    <!-- Hero Header -->
    <div class="hero-header bg-gradient-dark">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div class="hero-content">
                    <div class="d-flex align-items-center mb-2">
                        <div class="live-indicator me-3">
                            <div class="pulse-dot"></div>
                            <span class="live-text">LIVE</span>
                        </div>
                        <div class="event-badge">
                            <i class="fas fa-bolt me-1"></i>
                            Real-time Results
                        </div>
                    </div>
                    <h1 class="hero-title mb-1">
                        <i class="fas fa-crown text-warning me-2"></i>
                        Live Leaderboard
                        <?php if ($selectedEvent): ?>
                            <span class="event-title">• <?= htmlspecialchars($selectedEvent['name']) ?></span>
                        <?php endif; ?>
                    </h1>
                    <p class="hero-subtitle text-muted mb-0">
                        <i class="fas fa-clock me-1"></i>
                        Last updated: <span id="lastUpdated" class="fw-semibold text-primary"><?= date('H:i:s') ?></span>
                        <span class="ms-3">
                            <i class="fas fa-sync-alt me-1"></i>
                            Auto-refresh: <span class="fw-semibold" id="refreshInterval">30s</span>
                        </span>
                    </p>
                </div>

                <div class="hero-actions">
                    <div class="action-buttons">
                        <button class="btn btn-glass btn-refresh" onclick="refreshResults()" id="refreshBtn">
                            <i class="fas fa-sync-alt me-2"></i>
                            <span>Refresh</span>
                        </button>
                        <button class="btn btn-glass btn-export" onclick="exportResults()">
                            <i class="fas fa-download me-2"></i>
                            <span>Export</span>
                        </button>
                        <button class="btn btn-glass btn-auto" onclick="toggleAutoRefresh()" id="autoRefreshBtn">
                            <i class="fas fa-pause me-2"></i>
                            <span>Auto-refresh</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Overview Cards -->
    <div class="stats-overview">
        <div class="container-fluid">
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="fas fa-vote-yea"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value" id="totalVotesStat">
                            <?= number_format($liveStats['total_votes'] ?? 0) ?>
                        </div>
                        <div class="stat-label">Total Votes</div>
                        <div class="stat-change">
                            <span class="change-indicator positive">
                                <i class="fas fa-arrow-up"></i>
                                +<?= number_format($liveStats['votes_this_hour'] ?? 0) ?> this hour
                            </span>
                        </div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value" id="totalRevenueStat">
                            GH₵<?= number_format($liveStats['total_revenue'] ?? 0, 2) ?>
                        </div>
                        <div class="stat-label">Total Revenue</div>
                        <div class="stat-change">
                            <span class="change-indicator positive">
                                <i class="fas fa-trending-up"></i>
                                Live earnings
                            </span>
                        </div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value" id="uniqueVotersStat">
                            <?= number_format($liveStats['unique_voters'] ?? 0) ?>
                        </div>
                        <div class="stat-label">Unique Voters</div>
                        <div class="stat-change">
                            <span class="change-indicator neutral">
                                <i class="fas fa-user-friends"></i>
                                Active participants
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Dashboard -->
    <div class="main-dashboard">
        <div class="container-fluid">
            <div class="dashboard-grid">

                <!-- Live Leaderboard -->
                <div class="dashboard-card leaderboard-card">
                    <div class="card-header-premium">
                        <div class="card-title-section">
                            <div class="card-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div>
                                <h3 class="card-title">Top Contestants</h3>
                                <p class="card-subtitle">Real-time rankings & performance</p>
                            </div>
                        </div>

                        <!-- Event Selector -->
                        <div class="event-selector">
                            <select class="form-select form-select-premium" id="eventSelector" onchange="changeEvent()">
                                <option value="">All Active Events</option>
                                <?php if (!empty($events)): ?>
                                    <?php foreach ($events as $event): ?>
                                        <option value="<?= $event['id'] ?>" <?= $selectedEventId == $event['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($event['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Category Selector -->
                        <div class="category-selector">
                            <select class="form-select form-select-premium" id="categorySelector" onchange="changeCategory()">
                                <option value="">All Categories</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>" <?= $selectedCategoryId == $category['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['name']) ?> (<?= $category['contestant_count'] ?> contestants)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="card-body-premium">
                        <div class="leaderboard-container" id="leaderboardContainer">
                            <?php if (!empty($topContestants)): ?>
                                <div class="leaderboard-list">
                                    <?php
                                    $totalVotes = array_sum(array_map(function($contestant) {
                                        return $contestant['total_votes'] ?? 0;
                                    }, $topContestants));

                                    foreach ($topContestants as $index => $contestant):
                                        $rank = $index + 1;
                                        $contestantVotes = $contestant['total_votes'] ?? 0;
                                        $percentage = $totalVotes > 0 ? ($contestantVotes / $totalVotes) * 100 : 0;
                                        $revenue = $contestant['revenue'] ?? 0;

                                        $rankClass = $rank === 1 ? 'gold' : ($rank === 2 ? 'silver' : ($rank === 3 ? 'bronze' : 'normal'));
                                        $rankIcon = $rank === 1 ? 'crown' : ($rank === 2 ? 'medal' : ($rank === 3 ? 'award' : 'hashtag'));
                                    ?>
                                        <div class="leaderboard-item <?= $rankClass ?>" data-rank="<?= $rank ?>">
                                            <div class="rank-section">
                                                <div class="rank-badge">
                                                    <i class="fas fa-<?= $rankIcon ?>"></i>
                                                    <span class="rank-number"><?= $rank ?></span>
                                                </div>
                                            </div>

                                            <div class="contestant-section">
                                                <div class="contestant-avatar">
                                                    <?php if ($contestant['image_url']): ?>
                                                        <img src="<?= htmlspecialchars(image_url($contestant['image_url'])) ?>"
                                                             alt="<?= htmlspecialchars($contestant['name']) ?>">
                                                    <?php else: ?>
                                                        <div class="avatar-placeholder">
                                                            <?= strtoupper(substr($contestant['name'], 0, 1)) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="contestant-info">
                                                    <h4 class="contestant-name"><?= htmlspecialchars($contestant['name']) ?></h4>
                                                    <div class="contestant-meta">
                                                        <span class="code-badge">
                                                            <i class="fas fa-hashtag"></i>
                                                            <?= htmlspecialchars($contestant['contestant_code'] ?? 'N/A') ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="stats-section">
                                                <div class="stat-item">
                                                    <div class="stat-value results-count">
                                                        <i class="fas fa-trophy"></i>
                                                        <?= number_format($contestantVotes) ?>
                                                    </div>
                                                    <div class="stat-label">Results</div>
                                                </div>

                                                <div class="stat-item">
                                                    <div class="stat-value percentage">
                                                        <i class="fas fa-chart-pie"></i>
                                                        <?= number_format($percentage, 1) ?>%
                                                    </div>
                                                    <div class="stat-label">Share</div>
                                                </div>

                                                <div class="stat-item">
                                                    <div class="stat-value revenue">
                                                        <i class="fas fa-coins"></i>
                                                        GH₵<?= number_format($revenue, 2) ?>
                                                    </div>
                                                    <div class="stat-label">Revenue</div>
                                                </div>
                                            </div>

                                            <div class="progress-section">
                                                <div class="progress-bar-custom">
                                                    <div class="progress-fill" style="width: <?= min($percentage, 100) ?>%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <h4>No Results Yet</h4>
                                    <p>
                                        <?php if (!$selectedEvent): ?>
                                            Select an event to view live results
                                        <?php else: ?>
                                            No votes have been cast for this event yet
                                        <?php endif; ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Activity Sidebar -->
                <div class="dashboard-sidebar">

                    <!-- Real-time Activity -->
                    <div class="sidebar-card activity-card">
                        <div class="card-header-premium">
                            <div class="card-title-section">
                                <div class="card-icon">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <h4 class="card-title">Live Activity</h4>
                            </div>
                        </div>

                        <div class="card-body-premium">
                            <div class="activity-metrics">
                                <div class="metric-item">
                                    <div class="metric-icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                    <div class="metric-content">
                                        <div class="metric-value">
                                            GH₵<?= number_format(($liveStats['total_revenue'] ?? 0) / max($liveStats['votes_this_hour'] ?? 1, 1), 2) ?>
                                        </div>
                                        <div class="metric-label">Avg per vote</div>
                                    </div>
                                </div>

                                <div class="metric-item">
                                    <div class="metric-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="metric-content">
                                        <div class="metric-value">
                                            <?= number_format($liveStats['unique_voters'] ?? 0) ?>
                                        </div>
                                        <div class="metric-label">Total voters</div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($selectedEvent): ?>
                                <div class="event-status-card">
                                    <div class="status-icon">
                                        <i class="fas fa-broadcast-tower"></i>
                                    </div>
                                    <div class="status-content">
                                        <div class="status-title">Event Status</div>
                                        <div class="status-value active">
                                            <?= ucfirst($selectedEvent['status']) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Votes Feed -->
                    <div class="sidebar-card votes-feed-card">
                        <div class="card-header-premium">
                            <div class="card-title-section">
                                <div class="card-icon">
                                    <i class="fas fa-history"></i>
                                </div>
                                <h4 class="card-title">Recent Votes</h4>
                            </div>
                        </div>

                        <div class="card-body-premium">
                            <div class="votes-feed" id="recentVotes">
                                <?php if (!empty($recentVotes)): ?>
                                    <?php foreach ($recentVotes as $vote): ?>
                                        <?php
                                        $timeAgo = $vote['seconds_ago'];
                                        if ($timeAgo < 60) {
                                            $timeDisplay = $timeAgo . 's ago';
                                            $timeClass = 'recent';
                                        } elseif ($timeAgo < 3600) {
                                            $timeDisplay = floor($timeAgo / 60) . 'm ago';
                                            $timeClass = 'medium';
                                        } else {
                                            $timeDisplay = floor($timeAgo / 3600) . 'h ago';
                                            $timeClass = 'old';
                                        }
                                        ?>
                                        <div class="vote-item">
                                            <div class="vote-icon">
                                                <i class="fas fa-vote-yea"></i>
                                            </div>
                                            <div class="vote-content">
                                                <div class="vote-contestant">
                                                    <?= htmlspecialchars($vote['contestant_name']) ?>
                                                </div>
                                                <div class="vote-details">
                                                    <span class="vote-count">
                                                        <?= number_format($vote['quantity']) ?> votes
                                                    </span>
                                                    <?php if ($vote['amount']): ?>
                                                        <span class="vote-amount">
                                                            GH₵<?= number_format($vote['amount'], 2) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="vote-time <?= $timeClass ?>">
                                                <?= $timeDisplay ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="empty-feed">
                                        <div class="empty-icon">
                                            <i class="fas fa-vote-yea"></i>
                                        </div>
                                        <p>No recent votes</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Voting Trends Chart -->
            <div class="trends-section">
                <div class="dashboard-card chart-card">
                    <div class="card-header-premium">
                        <div class="card-title-section">
                            <div class="card-icon">
                                <i class="fas fa-chart-area"></i>
                            </div>
                            <div>
                                <h3 class="card-title">Voting Trends</h3>
                                <p class="card-subtitle">24-hour activity overview</p>
                            </div>
                        </div>

                        <div class="chart-controls">
                            <div class="btn-group btn-group-premium" role="group">
                                <button type="button" class="btn btn-premium active" onclick="updateChartPeriod('24h')">24H</button>
                                <button type="button" class="btn btn-premium" onclick="updateChartPeriod('7d')">7D</button>
                                <button type="button" class="btn btn-premium" onclick="updateChartPeriod('30d')">30D</button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body-premium">
                        <?php if (!empty($votingTrends['contestants'])): ?>
                            <div class="chart-container">
                                <canvas id="votingTrendsChart" width="400" height="100"></canvas>
                            </div>
                        <?php else: ?>
                            <div class="empty-chart">
                                <div class="empty-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h4>No Trend Data</h4>
                                <p>
                                    <?php if (!$selectedEvent): ?>
                                        Select an event to view voting trends
                                    <?php else: ?>
                                        No votes have been cast in the last 24 hours
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ===== PREMIUM DARK THEME & GLASS MORPHISM ===== */
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --gold-gradient: linear-gradient(135deg, #ffd700 0%, #ffb347 100%);
    --silver-gradient: linear-gradient(135deg, #c0c0c0 0%, #a8a8a8 100%);
    --bronze-gradient: linear-gradient(135deg, #cd7f32 0%, #a0522d 100%);
    --glass-bg: rgba(255, 255, 255, 0.1);
    --glass-border: rgba(255, 255, 255, 0.2);
    --shadow-light: 0 8px 32px rgba(0, 0, 0, 0.1);
    --shadow-medium: 0 12px 40px rgba(0, 0, 0, 0.15);
    --shadow-heavy: 0 20px 60px rgba(0, 0, 0, 0.2);
    --border-radius: 16px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.live-results-wrapper {
    min-height: 100vh;
    background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
    color: #ffffff;
}

/* ===== HERO HEADER ===== */
.hero-header {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 2rem 0;
    position: relative;
    overflow: hidden;
}

.hero-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.1;
}

.live-indicator {
    display: flex;
    align-items: center;
    background: rgba(34, 197, 94, 0.2);
    border: 1px solid rgba(34, 197, 94, 0.3);
    border-radius: 50px;
    padding: 0.5rem 1rem;
    backdrop-filter: blur(10px);
}

.pulse-dot {
    width: 8px;
    height: 8px;
    background: #22c55e;
    border-radius: 50%;
    margin-right: 0.5rem;
    animation: live-pulse 1.5s infinite;
}

@keyframes live-pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.7; transform: scale(1.2); }
}

.live-text {
    color: #22c55e;
    font-weight: 600;
    font-size: 0.875rem;
    letter-spacing: 0.5px;
}

.event-badge {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 500;
    backdrop-filter: blur(10px);
}

.hero-title {
    font-size: 2.5rem;
    font-weight: 700;
    background: linear-gradient(135deg, #ffffff 0%, #e0e7ff 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.5rem;
}

.event-title {
    color: #fbbf24;
    font-weight: 600;
}

.hero-subtitle {
    font-size: 1rem;
    opacity: 0.9;
}

/* ===== GLASS MORPHISM BUTTONS ===== */
.btn-glass {
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    backdrop-filter: blur(20px);
    color: #ffffff;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-weight: 500;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.btn-glass::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-glass:hover::before {
    left: 100%;
}

.btn-glass:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
}

.btn-refresh:hover { border-color: #3b82f6; }
.btn-export:hover { border-color: #10b981; }
.btn-auto:hover { border-color: #f59e0b; }

/* ===== STATS OVERVIEW ===== */
.stats-overview {
    padding: 2rem 0;
    background: linear-gradient(135deg, rgba(15, 15, 35, 0.8) 0%, rgba(26, 26, 46, 0.8) 100%);
    backdrop-filter: blur(20px);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}

.stat-card {
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: var(--border-radius);
    padding: 1rem;
    backdrop-filter: blur(20px);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--primary-gradient);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-heavy);
    border-color: rgba(255, 255, 255, 0.3);
}

.stat-primary::before { background: var(--primary-gradient); }
.stat-success::before { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
.stat-info::before { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
.stat-warning::before { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin-bottom: 1rem;
    background: rgba(255, 255, 255, 0.1);
}

.stat-primary .stat-icon { background: rgba(102, 126, 234, 0.2); color: #667eea; }
.stat-success .stat-icon { background: rgba(16, 185, 129, 0.2); color: #10b981; }
.stat-info .stat-icon { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
.stat-warning .stat-icon { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
    color: #ffffff;
}

.stat-label {
    font-size: 0.875rem;
    opacity: 0.8;
    font-weight: 500;
}

.change-indicator {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-weight: 500;
}

.change-indicator.positive {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.change-indicator.neutral {
    background: rgba(59, 130, 246, 0.2);
    color: #3b82f6;
}

/* ===== MAIN DASHBOARD ===== */
.main-dashboard {
    padding: 2rem 0;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
}

.dashboard-card {
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: var(--border-radius);
    backdrop-filter: blur(20px);
    overflow: hidden;
    transition: var(--transition);
}

.dashboard-card:hover {
    border-color: rgba(255, 255, 255, 0.3);
    box-shadow: var(--shadow-medium);
}

/* ===== CARD HEADERS ===== */
.card-header-premium {
    background: rgba(255, 255, 255, 0.05);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-title-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.card-body-premium {
    padding: 1rem 1.5rem;
}

.category-selector {
    min-width: 200px;
}

.card-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: var(--primary-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.card-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
    color: #ffffff;
}

.card-subtitle {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.7);
    margin: 0.25rem 0 0 0;
}

/* ===== LEADERBOARD ===== */
.leaderboard-container {
    max-height: 600px;
    overflow-y: auto;
}

.leaderboard-list {
    padding: 0;
}

.leaderboard-item {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    transition: var(--transition);
    position: relative;
}

.leaderboard-item:hover {
    background: rgba(255, 255, 255, 0.05);
    transform: translateX(5px);
}

.leaderboard-item.gold {
    background: linear-gradient(135deg, rgba(255, 215, 0, 0.1) 0%, rgba(255, 179, 71, 0.1) 100%);
    border-left: 4px solid #ffd700;
}

.leaderboard-item.silver {
    background: linear-gradient(135deg, rgba(192, 192, 192, 0.1) 0%, rgba(168, 168, 168, 0.1) 100%);
    border-left: 4px solid #c0c0c0;
}

.leaderboard-item.bronze {
    background: linear-gradient(135deg, rgba(205, 127, 50, 0.1) 0%, rgba(160, 82, 45, 0.1) 100%);
    border-left: 4px solid #cd7f32;
}

/* ===== RANK BADGES ===== */
.rank-section {
    width: 80px;
    text-align: center;
}

.rank-badge {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    position: relative;
}

.leaderboard-item.gold .rank-badge {
    background: var(--gold-gradient);
    box-shadow: 0 0 20px rgba(255, 215, 0, 0.3);
}

.leaderboard-item.silver .rank-badge {
    background: var(--silver-gradient);
    box-shadow: 0 0 20px rgba(192, 192, 192, 0.3);
}

.leaderboard-item.bronze .rank-badge {
    background: var(--bronze-gradient);
    box-shadow: 0 0 20px rgba(205, 127, 50, 0.3);
}

.leaderboard-item.normal .rank-badge {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.rank-number {
    font-size: 0.875rem;
    font-weight: 700;
    margin-top: 2px;
}

/* ===== CONTESTANT SECTION ===== */
.contestant-section {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 1rem;
    margin: 0 1.5rem;
}

.contestant-avatar {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    overflow: hidden;
    border: 2px solid rgba(255, 255, 255, 0.2);
    position: relative;
}

.contestant-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: var(--primary-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1.25rem;
}

.contestant-name {
    font-size: 1.125rem;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 0.5rem;
}

.contestant-meta {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.code-badge, .category-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* ===== STATS SECTION ===== */
.stats-section {
    display: flex;
    gap: 2rem;
    margin-right: 2rem;
}

.stat-item {
    text-align: center;
    min-width: 100px;
}

.stat-value {
    font-size: 1.125rem;
    font-weight: 700;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 0.25rem;
}

.stat-value.results-count { color: #3b82f6; }
.stat-value.percentage { color: #10b981; }
.stat-value.revenue { color: #f59e0b; }

.stat-label {
    font-size: 0.75rem;
    opacity: 0.7;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 500;
}

/* ===== PROGRESS BAR ===== */
.progress-section {
    flex: 1;
    max-width: 150px;
}

.progress-bar-custom {
    height: 8px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: var(--primary-gradient);
    border-radius: 4px;
    transition: width 0.5s ease;
}

/* ===== SIDEBAR ===== */
.dashboard-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.sidebar-card {
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: var(--border-radius);
    backdrop-filter: blur(20px);
    overflow: hidden;
}

/* ===== ACTIVITY METRICS ===== */
.activity-metrics {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.metric-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.metric-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
}

.metric-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 0.25rem;
}

.metric-label {
    font-size: 0.875rem;
    opacity: 0.7;
}

/* ===== VOTES FEED ===== */
.votes-feed {
    max-height: 400px;
    overflow-y: auto;
}

.vote-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    transition: var(--transition);
}

.vote-item:hover {
    background: rgba(255, 255, 255, 0.05);
}

.vote-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: rgba(16, 185, 129, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #10b981;
}

.vote-contestant {
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 0.25rem;
}

.vote-details {
    display: flex;
    gap: 0.75rem;
    font-size: 0.875rem;
    opacity: 0.8;
}

.vote-time {
    font-size: 0.75rem;
    opacity: 0.6;
    margin-left: auto;
}

.vote-time.recent { color: #10b981; }
.vote-time.medium { color: #f59e0b; }
.vote-time.old { color: #ef4444; }

/* ===== EVENT STATUS ===== */
.event-status-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.2);
    border-radius: 12px;
    margin-top: 1rem;
}

.status-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(16, 185, 129, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #10b981;
}

.status-title {
    font-size: 0.875rem;
    opacity: 0.8;
    margin-bottom: 0.25rem;
}

.status-value.active {
    color: #10b981;
    font-weight: 600;
}

/* ===== TRENDS SECTION ===== */
.trends-section {
    margin-top: 2rem;
}

.chart-container {
    padding: 2rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    margin-top: 1rem;
}

/* ===== FORM ELEMENTS ===== */
.form-select-premium {
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: 12px;
    color: #ffffff;
    padding: 0.75rem 1rem;
    backdrop-filter: blur(10px);
    min-width: 200px;
}

.form-select-premium:focus {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(102, 126, 234, 0.5);
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* ===== PREMIUM BUTTONS ===== */
.btn-group-premium .btn {
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    color: #ffffff;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    backdrop-filter: blur(10px);
    transition: var(--transition);
}

.btn-group-premium .btn:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-1px);
}

.btn-group-premium .btn.active {
    background: var(--primary-gradient);
    border-color: transparent;
}

/* ===== EMPTY STATES ===== */
.empty-state, .empty-chart, .empty-feed {
    text-align: center;
    padding: 3rem 2rem;
    color: rgba(255, 255, 255, 0.7);
}

.empty-icon {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 1.5rem;
    color: rgba(255, 255, 255, 0.5);
}

.empty-state h4, .empty-chart h4 {
    color: #ffffff;
    margin-bottom: 1rem;
}

/* ===== RESPONSIVE DESIGN ===== */
@media (max-width: 1024px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }

    .hero-actions {
        margin-top: 1.5rem;
    }

    .action-buttons {
        flex-direction: column;
        gap: 0.5rem;
    }

    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }
    
    .card-title-section {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 1rem;
    }
    
    .event-selector, .category-selector {
        width: 100%;
        min-width: unset;
    }
}

@media (max-width: 768px) {
    .hero-title {
        font-size: 2rem;
    }

    .stats-section {
        flex-direction: column;
        gap: 1rem;
    }

    .contestant-section {
        flex-direction: column;
        text-align: center;
        margin: 0 1rem;
    }

    .contestant-meta {
        justify-content: center;
    }

    .leaderboard-item {
        flex-direction: column;
        text-align: center;
        padding: 1rem;
    }

    .rank-section, .stats-section, .progress-section {
        margin: 0.5rem 0;
    }
    
    .hero-content {
        text-align: center;
    }
    
    .hero-actions {
        justify-content: center;
    }
}

/* ===== ANIMATIONS ===== */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.leaderboard-item {
    animation: fadeInUp 0.5s ease-out forwards;
}

.leaderboard-item:nth-child(1) { animation-delay: 0.1s; }
.leaderboard-item:nth-child(2) { animation-delay: 0.2s; }
.leaderboard-item:nth-child(3) { animation-delay: 0.3s; }
.leaderboard-item:nth-child(4) { animation-delay: 0.4s; }
.leaderboard-item:nth-child(5) { animation-delay: 0.5s; }

/* ===== SCROLLBAR STYLING ===== */
.leaderboard-container::-webkit-scrollbar,
.votes-feed::-webkit-scrollbar {
    width: 6px;
}

.leaderboard-container::-webkit-scrollbar-track,
.votes-feed::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 3px;
}

.leaderboard-container::-webkit-scrollbar-thumb,
.votes-feed::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 3px;
}

.leaderboard-container::-webkit-scrollbar-thumb:hover,
.votes-feed::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let refreshInterval;
let chart;
let autoRefreshEnabled = true;
let currentEventId = <?= json_encode($selectedEventId) ?>;
let currentCategoryId = <?= json_encode($selectedCategoryId) ?>;

// Initialize live results
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($votingTrends['contestants'])): ?>
    initializeChart();
    <?php endif; ?>
    startAutoRefresh();
});

function initializeChart() {
    const canvas = document.getElementById('votingTrendsChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    const votingData = <?= json_encode($votingTrends) ?>;
    
    // Generate hour labels
    const labels = [];
    for (let i = 0; i < 24; i++) {
        labels.push(i.toString().padStart(2, '0') + ':00');
    }
    
    // Generate datasets from real data
    const datasets = [];
    const colors = ['#ffc107', '#007bff', '#17a2b8', '#28a745', '#dc3545', '#6f42c1', '#fd7e14'];
    let colorIndex = 0;
    
    Object.keys(votingData.contestants).forEach(contestantId => {
        const contestantName = votingData.contestants[contestantId];
        const data = votingData.data[contestantId] || Array(24).fill(0);
        const color = colors[colorIndex % colors.length];
        
        datasets.push({
            label: contestantName,
            data: data,
            borderColor: color,
            backgroundColor: color + '20',
            tension: 0.4,
            fill: false
        });
        
        colorIndex++;
    });
    
    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Votes'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Hour of Day'
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
}

function refreshResults() {
    const button = document.getElementById('refreshBtn');
    const icon = button.querySelector('i');
    icon.classList.add('fa-spin');
    
    // Show loading state
    showLoadingState();
    
    // Make AJAX request to get updated data
    const url = new URL(window.location.href);
    url.searchParams.set('ajax', '1');
    if (currentEventId) {
        url.searchParams.set('event', currentEventId);
    }
    if (currentCategoryId) {
        url.searchParams.set('category', currentCategoryId);
    }
    
    fetch(url.toString())
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            updateLiveStats(data.liveStats);
            updateLeaderboard(data.topContestants);
            updateRecentVotes(data.recentVotes);
            updateChart(data.votingTrends);
            document.getElementById('lastUpdated').textContent = data.lastUpdated;
            
            // Show success feedback
            showRefreshSuccess();
            hideLoadingState();
        })
        .catch(error => {
            console.error('Error refreshing data:', error);
            showRefreshError(error.message);
            hideLoadingState();
        })
        .finally(() => {
            icon.classList.remove('fa-spin');
        });
}

function showLoadingState() {
    const liveStatus = document.getElementById('liveStatus');
    if (liveStatus) {
        liveStatus.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>UPDATING...';
        liveStatus.className = 'fw-semibold text-info';
    }
}

function hideLoadingState() {
    const liveStatus = document.getElementById('liveStatus');
    if (liveStatus) {
        liveStatus.innerHTML = '<i class="fas fa-circle me-1 pulse"></i>LIVE';
        liveStatus.className = 'fw-semibold text-success';
    }
}

function showRefreshSuccess() {
    // Brief success indicator
    const liveStatus = document.getElementById('liveStatus');
    if (liveStatus) {
        liveStatus.innerHTML = '<i class="fas fa-check me-1"></i>UPDATED';
        liveStatus.className = 'fw-semibold text-success';
        
        setTimeout(() => {
            liveStatus.innerHTML = '<i class="fas fa-circle me-1 pulse"></i>LIVE';
        }, 2000);
    }
}

function showRefreshError(message) {
    const liveStatus = document.getElementById('liveStatus');
    if (liveStatus) {
        liveStatus.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>ERROR';
        liveStatus.className = 'fw-semibold text-danger';
        liveStatus.title = `Refresh failed: ${message}`;
        
        setTimeout(() => {
            liveStatus.innerHTML = '<i class="fas fa-circle me-1 pulse"></i>LIVE';
            liveStatus.className = 'fw-semibold text-success';
            liveStatus.title = '';
        }, 5000);
    }
}

function updateLiveStats(stats) {
    const totalVotesStat = document.getElementById('totalVotesStat');
    const totalRevenueStat = document.getElementById('totalRevenueStat');
    const uniqueVotersStat = document.getElementById('uniqueVotersStat');

    if (totalVotesStat) {
        totalVotesStat.textContent = new Intl.NumberFormat().format(stats.total_votes || 0);
    }
    if (totalRevenueStat) {
        totalRevenueStat.textContent = 'GH₵' + new Intl.NumberFormat('en-US', {minimumFractionDigits: 2}).format(stats.total_revenue || 0);
    }
    if (uniqueVotersStat) {
        uniqueVotersStat.textContent = new Intl.NumberFormat().format(stats.unique_voters || 0);
    }
}

function updateLeaderboard(contestants) {
    const container = document.querySelector('.leaderboard-container');
    if (!container || !contestants || contestants.length === 0) {
        if (container) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4>No Results Yet</h4>
                    <p>No votes have been cast for this event yet</p>
                </div>
            `;
        }
        return;
    }

    // Calculate total votes for percentages
    const totalVotes = contestants.reduce((sum, contestant) => sum + (contestant.total_votes || 0), 0);

    let html = '<div class="leaderboard-list">';
    contestants.forEach((contestant, index) => {
        const rank = index + 1;
        const contestantVotes = contestant.total_votes || 0;
        const percentage = totalVotes > 0 ? (contestantVotes / totalVotes) * 100 : 0;
        const revenue = contestant.revenue || 0;

        const rankClass = rank === 1 ? 'gold' : (rank === 2 ? 'silver' : (rank === 3 ? 'bronze' : 'normal'));
        const rankIcon = rank === 1 ? 'crown' : (rank === 2 ? 'medal' : (rank === 3 ? 'award' : 'hashtag'));

        const imageHtml = contestant.image_url ?
            `<img src="${getImageUrl(contestant.image_url)}" alt="${contestant.name}">` :
            `<div class="avatar-placeholder">${contestant.name.charAt(0).toUpperCase()}</div>`;

        html += `
            <div class="leaderboard-item ${rankClass}" data-rank="${rank}">
                <div class="rank-section">
                    <div class="rank-badge">
                        <i class="fas fa-${rankIcon}"></i>
                        <span class="rank-number">${rank}</span>
                    </div>
                </div>

                <div class="contestant-section">
                    <div class="contestant-avatar">
                        ${imageHtml}
                    </div>
                    <div class="contestant-info">
                        <h4 class="contestant-name">${contestant.name}</h4>
                        <div class="contestant-meta">
                            <span class="code-badge">
                                <i class="fas fa-hashtag"></i>
                                ${contestant.contestant_code || 'N/A'}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="stats-section">
                    <div class="stat-item">
                        <div class="stat-value results-count">
                            <i class="fas fa-trophy"></i>
                            ${new Intl.NumberFormat().format(contestantVotes)}
                        </div>
                        <div class="stat-label">Results</div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-value percentage">
                            <i class="fas fa-chart-pie"></i>
                            ${percentage.toFixed(1)}%
                        </div>
                        <div class="stat-label">Share</div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-value revenue">
                            <i class="fas fa-coins"></i>
                            GH₵${new Intl.NumberFormat('en-US', {minimumFractionDigits: 2}).format(revenue)}
                        </div>
                        <div class="stat-label">Revenue</div>
                    </div>
                </div>

                <div class="progress-section">
                    <div class="progress-bar-custom">
                        <div class="progress-fill" style="width: ${Math.min(percentage, 100)}%"></div>
                    </div>
                </div>
            </div>
        `;
    });

    html += '</div>';
    container.innerHTML = html;
}

function updateRecentVotes(votes) {
    const container = document.getElementById('recentVotes');
    if (!container || !votes) return;
    
    if (votes.length === 0) {
        container.innerHTML = `
            <div class="list-group-item text-center py-4">
                <div class="text-muted">
                    <i class="fas fa-vote-yea fa-2x mb-2 opacity-50"></i>
                    <p class="mb-0 small">No recent votes</p>
                </div>
            </div>
        `;
        return;
    }
    
    let html = '';
    votes.forEach(vote => {
        const timeAgo = vote.seconds_ago;
        let timeDisplay;
        if (timeAgo < 60) {
            timeDisplay = timeAgo + 's ago';
        } else if (timeAgo < 3600) {
            timeDisplay = Math.floor(timeAgo / 60) + 'm ago';
        } else {
            timeDisplay = Math.floor(timeAgo / 3600) + 'h ago';
        }
        
        html += `
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold small">${vote.contestant_name}</div>
                        <div class="text-muted small">
                            ${new Intl.NumberFormat().format(vote.quantity)} votes
                            ${vote.amount ? '• GHS ' + new Intl.NumberFormat('en-US', {minimumFractionDigits: 2}).format(vote.amount) : ''}
                        </div>
                    </div>
                    <div class="small text-muted">${timeDisplay}</div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function updateChart(trendsData) {
    if (!chart || !trendsData) return;
    
    // Update chart data
    const datasets = [];
    const colors = ['#ffc107', '#007bff', '#17a2b8', '#28a745', '#dc3545', '#6f42c1', '#fd7e14'];
    let colorIndex = 0;
    
    Object.keys(trendsData.contestants || {}).forEach(contestantId => {
        const contestantName = trendsData.contestants[contestantId];
        const data = trendsData.data[contestantId] || Array(24).fill(0);
        const color = colors[colorIndex % colors.length];
        
        datasets.push({
            label: contestantName,
            data: data,
            borderColor: color,
            backgroundColor: color + '20',
            tension: 0.4,
            fill: false
        });
        
        colorIndex++;
    });
    
    chart.data.datasets = datasets;
    chart.update();
}

function exportResults() {
    const eventId = currentEventId || '';
    const categoryId = currentCategoryId || '';
    let url = `<?= ORGANIZER_URL ?>/voting/export`;
    const params = [];
    
    if (eventId) params.push(`event=${eventId}`);
    if (categoryId) params.push(`category=${categoryId}`);
    
    if (params.length > 0) {
        url += '?' + params.join('&');
    }
    
    window.open(url, '_blank');
}

function changeEvent() {
    const eventId = document.getElementById('eventSelector').value;
    const categorySelector = document.getElementById('categorySelector');
    
    // Reset category selection when event changes
    if (categorySelector) {
        categorySelector.value = '';
    }
    
    currentEventId = eventId;
    
    // Redirect to update the page with new event
    const url = new URL(window.location.href);
    if (eventId) {
        url.searchParams.set('event', eventId);
    } else {
        url.searchParams.delete('event');
    }
    url.searchParams.delete('category'); // Remove category filter when changing event
    window.location.href = url.toString();
}

function changeCategory() {
    const eventId = document.getElementById('eventSelector').value;
    const categoryId = document.getElementById('categorySelector').value;
    
    // Update current category ID
    currentCategoryId = categoryId;
    
    // Update URL with both event and category parameters
    const url = new URL(window.location.href);
    if (eventId) {
        url.searchParams.set('event', eventId);
    } else {
        url.searchParams.delete('event');
    }
    
    if (categoryId) {
        url.searchParams.set('category', categoryId);
    } else {
        url.searchParams.delete('category');
    }
    
    window.location.href = url.toString();
}

function toggleAutoRefresh() {
    autoRefreshEnabled = !autoRefreshEnabled;
    const button = document.getElementById('autoRefreshBtn');
    const icon = button.querySelector('i');
    
    if (autoRefreshEnabled) {
        icon.className = 'fas fa-pause me-2';
        button.innerHTML = '<i class="fas fa-pause me-2"></i>Auto-refresh';
        startAutoRefresh();
    } else {
        icon.className = 'fas fa-play me-2';
        button.innerHTML = '<i class="fas fa-play me-2"></i>Auto-refresh';
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    }
}

function startAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
    
    if (autoRefreshEnabled) {
        refreshInterval = setInterval(() => {
            refreshResults();
        }, 30000); // Refresh every 30 seconds
    }
}

function updateChartPeriod(period) {
    // Update active button
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // This would make an AJAX call to get data for different periods
    console.log('Updating chart period to:', period);
}

// Image URL helper function
function getImageUrl(imagePath) {
    if (!imagePath) return null;
    
    // If it's already a full URL, return as is
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
        return imagePath;
    }
    
    // If it starts with APP_URL, return as is
    const appUrl = '<?= APP_URL ?>';
    if (imagePath.startsWith(appUrl)) {
        return imagePath;
    }
    
    // If it's a relative path starting with /, add APP_URL
    if (imagePath.startsWith('/')) {
        return appUrl + imagePath;
    }
    
    // Otherwise, assume it's a relative path and add APP_URL with leading slash
    return appUrl + '/' + imagePath.replace(/^\/+/, '');
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>
