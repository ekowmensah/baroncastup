<?php
// Get the calculated status from the event
$calculatedStatus = $event['calculatedStatus'];

// Generate event slug
require_once __DIR__ . '/../../src/Helpers/SlugHelper.php';
$eventSlug = \SmartCast\Helpers\SlugHelper::generateEventSlug($event);
?>

<div class="col-sm-6 col-md-4 col-lg-3 mb-4 event-card" 
     data-event-id="<?= $event['id'] ?>" 
     data-status="<?= $calculatedStatus ?>"
     data-start-date="<?= $event['start_date'] ?>"
     data-end-date="<?= $event['end_date'] ?>">
    <div class="card h-100 border-0 shadow-sm event-card-hover position-relative">
        <div class="position-relative overflow-hidden">
            <?php if ($event['featured_image']): ?>
                <img src="<?= htmlspecialchars(image_url($event['featured_image'])) ?>" 
                     class="card-img-top event-image" 
                     alt="<?= htmlspecialchars($event['name']) ?>">
            <?php else: ?>
                <div class="card-img-top bg-gradient-primary d-flex align-items-center justify-content-center event-image">
                    <i class="fas fa-calendar-alt fa-3x text-white opacity-50"></i>
                </div>
            <?php endif; ?>
            
            <!-- Status Badge -->
            <div class="position-absolute top-0 end-0 m-3">
                <?php
                if ($calculatedStatus === 'ended') {
                    $badgeClass = 'bg-danger';
                    $badgeText = '<i class="fas fa-stop me-1"></i>ENDED';
                } elseif ($calculatedStatus === 'upcoming') {
                    $badgeClass = 'bg-warning text-dark';
                    $badgeText = '<i class="fas fa-clock me-1"></i>UPCOMING';
                } elseif ($calculatedStatus === 'active') {
                    $badgeClass = 'bg-success';
                    $badgeText = '<i class="fas fa-circle me-1 pulse"></i>LIVE';
                } else {
                    $badgeClass = 'bg-secondary';
                    $badgeText = ucfirst($calculatedStatus);
                }
                ?>
                <span class="badge badge-status <?= $badgeClass ?>">
                    <?= $badgeText ?>
                </span>
            </div>
            
            <!-- Hover Overlay with Details Button -->
            <div class="card-hover-overlay">
                <div class="hover-content">
                    <a href="<?= APP_URL ?>/events/<?= $eventSlug ?>" class="btn btn-light btn-details">
                        <i class="fas fa-info-circle me-2"></i>
                        View Details
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card-body px-3 pt-3 pb-1 d-flex flex-column">
            <!-- Event Header -->
            <div class="event-header mb-2">
                <h6 class="event-title mb-1">
                    <?= htmlspecialchars($event['name']) ?>
                    <?php if ($calculatedStatus === 'active'): ?>
                        <span class="live-badge">LIVE</span>
                    <?php endif; ?>
                </h6>
            </div>
            
            <!-- Ultra Compact Stats -->
            <div class="ultra-stats mb-2">
                <?php
                    // Calculate days left or show appropriate status
                    $startDate = new DateTime($event['start_date']);
                    $endDate = new DateTime($event['end_date']);
                    $today = new DateTime();
                    
                    if ($calculatedStatus === 'ended') {
                        $daysLeftText = 'Ended';
                        $textClass = 'text-danger';
                    } elseif ($calculatedStatus === 'upcoming') {
                        $daysToStart = max(0, $today->diff($startDate)->days);
                        if ($daysToStart === 0) {
                            $daysLeftText = 'Starts Today';
                            $textClass = 'text-warning';
                        } else {
                            $daysLeftText = 'Starts in ' . $daysToStart . ' day' . ($daysToStart !== 1 ? 's' : '');
                            $textClass = 'text-info';
                        }
                    } else {
                        // Active event
                        $daysLeft = max(0, $today->diff($endDate)->days);
                        if ($daysLeft === 0) {
                            $daysLeftText = 'Ends Today';
                            $textClass = 'text-warning';
                        } else {
                            $daysLeftText = $daysLeft . ' Day' . ($daysLeft !== 1 ? 's' : '') . ' Left';
                            $textClass = '';
                        }
                    }
                ?>
                <div class="stats-row">
                    <span class="stat-item"><?= $event['contestant_count'] ?? 0 ?> Contestants</span>
                    <span class="stat-divider">•</span>
                 <!--   <span class="stat-item"><?= number_format($event['total_votes'] ?? 0) ?> Votes</span> 
                    <span class="stat-divider">•</span>-->
                    <span class="stat-item <?= $textClass ?>"><?= $daysLeftText ?></span>
                </div>
            </div>
            
            <!-- Minimal Dates -->
            <div class="mini-dates">
                <div class="date-row">
                    <?php if ($calculatedStatus === 'upcoming'): ?>
                        <span class="date-label">Starts:</span> <?= date('M j', strtotime($event['start_date'])) ?>
                    <?php else: ?>
                        <span class="date-label">Started:</span> <?= date('M j', strtotime($event['start_date'])) ?>
                    <?php endif; ?>
                    <span class="date-separator">|</span>
                    <?php if ($calculatedStatus === 'ended'): ?>
                        <span class="date-label">Ended:</span> <?= date('M j, g:i A', strtotime($event['end_date'])) ?>
                    <?php else: ?>
                        <span class="date-label">Ends:</span> <?= date('M j, g:i A', strtotime($event['end_date'])) ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="mt-auto" style="margin-top: 8px !important; margin-bottom: 0 !important;">
                <div class="compact-actions">
                    <?php if ($calculatedStatus === 'active'): ?>
                        <a href="<?= APP_URL ?>/events/<?= $eventSlug ?>/vote" 
                           class="vote-btn">
                            VOTE NOW
                        </a>
                        <?php if ($event['results_visible']): ?>
                            <a href="<?= APP_URL ?>/events/<?= $eventSlug ?>" 
                               class="results-btn">
                                Results
                            </a>
                        <?php endif; ?>
                    <?php elseif ($calculatedStatus === 'upcoming'): ?>
                        <span class="coming-soon">Starts Soon</span>
                    <?php elseif ($calculatedStatus === 'ended'): ?>
                        <?php if ($event['results_visible']): ?>
                            <a href="<?= APP_URL ?>/events/<?= $eventSlug ?>" 
                               class="results-btn primary">
                                View Final Results
                            </a>
                        <?php else: ?>
                            <span class="ended-event">Event Ended</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if ($event['results_visible']): ?>
                            <a href="<?= APP_URL ?>/events/<?= $eventSlug ?>" 
                               class="results-btn primary">
                                View Results
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
