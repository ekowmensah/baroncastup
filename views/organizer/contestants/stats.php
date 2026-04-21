<?php 
$content = ob_start(); 
?>

<!-- Contestant Statistics -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-chart-bar me-2"></i>
            Statistics
        </h2>
        <p class="text-muted mb-0"><?= htmlspecialchars($contestant['name']) ?> - <?= htmlspecialchars($contestant['event_name']) ?></p>
    </div>
    <div>
        <a href="<?= ORGANIZER_URL ?>/contestants/<?= $contestant['id'] ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Details
        </a>
    </div>
</div>

<!-- Overview Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="fas fa-vote-yea fa-2x mb-2"></i>
                <h3 class="mb-1"><?= number_format($vote_stats['total_votes']) ?></h3>
                <small>Total Votes</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="fas fa-receipt fa-2x mb-2"></i>
                <h3 class="mb-1"><?= number_format($vote_stats['total_transactions']) ?></h3>
                <small>Transactions</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="fas fa-money-bill fa-2x mb-2"></i>
                <h3 class="mb-1">GH₵<?= number_format($vote_stats['total_revenue'], 2) ?></h3>
                <small>Revenue Generated</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <i class="fas fa-calendar-day fa-2x mb-2"></i>
                <h3 class="mb-1"><?= number_format($vote_stats['voting_days']) ?></h3>
                <small>Active Days</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Category Breakdown -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-tags me-2"></i>
                    Performance by Category
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($category_stats)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Shortcode</th>
                                    <th class="text-center">Votes</th>
                                    <th class="text-center">Transactions</th>
                                    <th class="text-center">Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $maxVotes = max(array_column($category_stats, 'votes'));
                                foreach ($category_stats as $stat): 
                                    $percentage = $maxVotes > 0 ? ($stat['votes'] / $maxVotes) * 100 : 0;
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($stat['category_name']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?= htmlspecialchars($stat['short_code']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <strong><?= number_format($stat['votes']) ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <?= number_format($stat['transactions']) ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar" 
                                                     style="width: <?= $percentage ?>%"
                                                     title="<?= number_format($percentage, 1) ?>%">
                                                    <?= number_format($percentage, 1) ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-chart-bar fa-2x mb-2"></i>
                        <p>No voting data available yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>
                    Recent Voting Activity
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_votes)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Votes</th>
                                    <th>Amount</th>
                                    <th>Voter</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_votes as $vote): ?>
                                    <tr>
                                        <td>
                                            <small><?= date('M j, Y g:i A', strtotime($vote['created_at'])) ?></small>
                                        </td>
                                        <td>
                                            <?php if ($vote['category_name']): ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($vote['category_name']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= number_format($vote['quantity']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="text-success">$<?= number_format($vote['amount'], 2) ?></span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($vote['voter_name'] ?? 'Anonymous') ?>
                                                <?php if ($vote['msisdn']): ?>
                                                    <br><?= htmlspecialchars(substr($vote['msisdn'], 0, 3) . '***' . substr($vote['msisdn'], -2)) ?>
                                                <?php endif; ?>
                                            </small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-history fa-2x mb-2"></i>
                        <p>No recent voting activity</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Contestant Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user me-2"></i>
                    Contestant Info
                </h6>
            </div>
            <div class="card-body text-center">
                <?php if ($contestant['image_url']): ?>
                    <img src="<?= htmlspecialchars(image_url($contestant['image_url'])) ?>" 
                         alt="<?= htmlspecialchars($contestant['name']) ?>" 
                         class="img-thumbnail mb-3" 
                         style="max-width: 150px; max-height: 150px; object-fit: cover;">
                <?php else: ?>
                    <div class="bg-light border rounded d-flex align-items-center justify-content-center mb-3" 
                         style="width: 150px; height: 150px; margin: 0 auto;">
                        <i class="fas fa-user fa-3x text-muted"></i>
                    </div>
                <?php endif; ?>
                
                <h5><?= htmlspecialchars($contestant['name']) ?></h5>
                <p class="text-muted"><?= htmlspecialchars($contestant['event_name']) ?></p>
                
                <?php if ($contestant['bio']): ?>
                    <div class="text-start">
                        <small class="text-muted"><?= nl2br(htmlspecialchars($contestant['bio'])) ?></small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-tools me-2"></i>
                    Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= ORGANIZER_URL ?>/contestants/<?= $contestant['id'] ?>/edit" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit Contestant
                    </a>
                    <a href="<?= ORGANIZER_URL ?>/events/<?= $contestant['event_id'] ?>" class="btn btn-outline-primary">
                        <i class="fas fa-calendar me-2"></i>View Event
                    </a>
                    <button class="btn btn-outline-info" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .card-header, .navbar, .sidebar {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../../layout/organizer_layout.php';
?>
