<!-- Debug: Check eventStats data -->
<?php if (isset($_GET['debug'])): ?>
<div class="alert alert-info">
    <strong>Debug - Event Stats:</strong>
    <pre><?= print_r($eventStats, true) ?></pre>
</div>
<?php endif; ?>

<?php
// Helper function to determine event status
function getEventStatus($event) {
    $now = time();
    $startTime = strtotime($event['start_date']);
    $endTime = strtotime($event['end_date']);
    
    if ($event['status'] === 'draft') {
        return [
            'status' => 'draft',
            'class' => 'warning',
            'icon' => 'fas fa-exclamation-triangle',
            'description' => 'This event is in draft mode and is not visible to the public'
        ];
    } elseif ($event['status'] === 'completed') {
        return [
            'status' => 'completed',
            'class' => 'info',
            'icon' => 'fas fa-flag-checkered',
            'description' => 'This event has been completed'
        ];
    } elseif ($now < $startTime) {
        return [
            'status' => 'upcoming',
            'class' => 'primary',
            'icon' => 'fas fa-clock',
            'description' => 'This event is scheduled to start on ' . date('M j, Y \a\t g:i A', $startTime)
        ];
    } elseif ($now > $endTime) {
        return [
            'status' => 'ended',
            'class' => 'secondary',
            'icon' => 'fas fa-flag-checkered',
            'description' => 'This event ended on ' . date('M j, Y \a\t g:i A', $endTime)
        ];
    } else {
        return [
            'status' => 'live',
            'class' => 'success',
            'icon' => 'fas fa-check-circle',
            'description' => 'This event is live and accepting votes'
        ];
    }
}

$eventStatus = getEventStatus($event);
?>

<!-- Comprehensive Event Details Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-calendar-alt me-2"></i>
            <?= htmlspecialchars($event['name']) ?>
        </h2>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-<?= $eventStatus['class'] ?> fs-6">
                <?= ucfirst($eventStatus['status']) ?>
            </span>
            <span class="text-muted">
                <i class="fas fa-calendar me-1"></i>
                <?= date('M j', strtotime($event['start_date'])) ?> - <?= date('M j, Y', strtotime($event['end_date'])) ?>
            </span>
            <span class="text-muted">
                <i class="fas fa-money-bill me-1"></i>
                GH₵<?= number_format($eventStats['vote_price'] ?? $event['vote_price'] ?? 0, 2) ?> per vote
            </span>
        </div>
    </div>
    <div class="btn-group" role="group">
        <a href="<?= ORGANIZER_URL ?>/events/wizard?edit=<?= $event['id'] ?>/edit" class="btn btn-outline-primary">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <a href="<?= ORGANIZER_URL ?>/events/<?= $event['id'] ?>/export-pdf" class="btn btn-outline-success">
            <i class="fas fa-file-pdf me-1"></i>Export PDF
        </a>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-cog me-1"></i>More
            </button>
            <ul class="dropdown-menu">
                <?php if ($event['status'] === 'draft'): ?>
                    <li>
                        <form method="POST" action="<?= ORGANIZER_URL ?>/events/<?= $event['id'] ?>/publish" class="d-inline">
                            <button type="submit" class="dropdown-item" onclick="return confirm('Publish this event?')">
                                <i class="fas fa-play me-2"></i>Publish Event
                            </button>
                        </form>
                    </li>
                <?php endif; ?>
                <li>
                    <a class="dropdown-item" href="<?= ORGANIZER_URL ?>/voting/live?event=<?= $event['id'] ?>">
                        <i class="fas fa-chart-line me-2"></i>Live Results
                    </a>
                </li>
                <li>
                    <form method="POST" action="<?= ORGANIZER_URL ?>/events/<?= $event['id'] ?>/toggle-results" class="d-inline">
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-<?= $event['results_visible'] ? 'eye-slash' : 'eye' ?> me-2"></i>
                            <?= $event['results_visible'] ? 'Hide' : 'Show' ?> Results
                        </button>
                    </form>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="<?= ORGANIZER_URL ?>/events/wizard?edit=<?= $event['id'] ?>">
                        <i class="fas fa-magic me-2"></i>Event Wizard
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Event Status Alert -->
<div class="alert alert-<?= $eventStatus['class'] ?>">
    <i class="<?= $eventStatus['icon'] ?> me-2"></i>
    <?= $eventStatus['description'] ?>.
    
    <?php if ($eventStatus['status'] === 'draft'): ?>
        <a href="#" onclick="publishEvent()" class="btn btn-sm btn-<?= $eventStatus['class'] ?> ms-2">
            <i class="fas fa-play me-1"></i>Publish Event
        </a>
    <?php elseif ($eventStatus['status'] === 'upcoming'): ?>
        <a href="<?= ORGANIZER_URL ?>/events/<?= $event['id'] ?>/edit" class="btn btn-sm btn-<?= $eventStatus['class'] ?> ms-2">
            <i class="fas fa-edit me-1"></i>Edit Event
        </a>
    <?php elseif ($eventStatus['status'] === 'live'): ?>
        <a href="<?= ORGANIZER_URL ?>/voting/live?event=<?= $event['id'] ?>" class="btn btn-sm btn-<?= $eventStatus['class'] ?> ms-2">
            <i class="fas fa-chart-line me-1"></i>View Live Results
        </a>
    <?php elseif (in_array($eventStatus['status'], ['ended', 'completed'])): ?>
        <a href="<?= ORGANIZER_URL ?>/voting/live?event=<?= $event['id'] ?>" class="btn btn-sm btn-<?= $eventStatus['class'] ?> ms-2">
            <i class="fas fa-trophy me-1"></i>View Final Results
        </a>
    <?php endif; ?>
</div>

<!-- Event Overview -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Event Information</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($event['featured_image'])): ?>
                    <div class="mb-3">
                        <img src="<?= htmlspecialchars(image_url($event['featured_image'])) ?>" class="img-fluid rounded" style="max-height: 300px; width: 100%; object-fit: cover;">
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Event Code:</strong></td>
                                <td>
                                    <span class="badge bg-primary"><?= htmlspecialchars($event['code']) ?></span>
                                    <button class="btn btn-sm btn-outline-secondary ms-1" onclick="copyEventCode()">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="badge bg-<?= $event['status'] === 'active' ? 'success' : ($event['status'] === 'draft' ? 'warning' : 'secondary') ?>">
                                        <?= ucfirst($event['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Visibility:</strong></td>
                                <td>
                                    <span class="badge bg-<?= $event['visibility'] === 'public' ? 'success' : 'warning' ?>">
                                        <?= ucfirst($event['visibility']) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Created:</strong></td>
                                <td><?= date('M j, Y H:i', strtotime($event['created_at'])) ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Start Date:</strong></td>
                                <td><?= date('M j, Y H:i', strtotime($event['start_date'])) ?></td>
                            </tr>
                            <tr>
                                <td><strong>End Date:</strong></td>
                                <td><?= date('M j, Y H:i', strtotime($event['end_date'])) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Duration:</strong></td>
                                <td>
                                    <?php
                                    $start = new DateTime($event['start_date']);
                                    $end = new DateTime($event['end_date']);
                                    $diff = $start->diff($end);
                                    echo $diff->days . ' days, ' . $diff->h . ' hours';
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Results Visible:</strong></td>
                                <td>
                                    <span class="badge bg-<?= $event['results_visible'] ? 'success' : 'secondary' ?>">
                                        <?= $event['results_visible'] ? 'Yes' : 'No' ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <?php if (!empty($event['description'])): ?>
                    <div class="mt-3">
                        <h6>Description</h6>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Event Statistics -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Event Statistics</h6>
            </div>
            <div class="card-body">
                <?php 
                // Calculate all statistics from contestants data if eventStats is not reliable
                $calculatedStats = [
                    'total_contestants' => 0,
                    'total_votes' => 0,
                    'total_revenue' => 0,
                    'total_categories' => 0
                ];
                
                if (!empty($categoriesWithContestants)) {
                    $calculatedStats['total_categories'] = count($categoriesWithContestants);
                    
                    foreach ($categoriesWithContestants as $category) {
                        foreach ($category['contestants'] as $contestant) {
                            $calculatedStats['total_contestants']++;
                            $calculatedStats['total_votes'] += $contestant['total_votes'] ?? 0;
                            $calculatedStats['total_revenue'] += $contestant['revenue'] ?? 0;
                        }
                    }
                }
                
                // Use calculated stats if eventStats is empty or zero
                $displayStats = [
                    'total_contestants' => ($eventStats['total_contestants'] ?? 0) > 0 ? $eventStats['total_contestants'] : $calculatedStats['total_contestants'],
                    'total_votes' => ($eventStats['total_votes'] ?? 0) > 0 ? $eventStats['total_votes'] : $calculatedStats['total_votes'],
                    'total_revenue' => ($eventStats['total_revenue'] ?? 0) > 0 ? $eventStats['total_revenue'] : $calculatedStats['total_revenue'],
                    'total_categories' => ($eventStats['total_categories'] ?? 0) > 0 ? $eventStats['total_categories'] : $calculatedStats['total_categories'],
                    'unique_voters' => $eventStats['unique_voters'] ?? 0
                ];
                ?>
                
                <div class="row text-center mb-3">
                    <div class="col-6">
                        <div class="fs-4 fw-semibold text-primary"><?= number_format($displayStats['total_contestants']) ?></div>
                        <div class="small text-muted">Contestants</div>
                    </div>
                    <div class="col-6">
                        <div class="fs-4 fw-semibold text-success"><?= number_format($displayStats['total_votes']) ?></div>
                        <div class="small text-muted">Total Votes</div>
                    </div>
                </div>
                
                <div class="row text-center mb-3">
                    <div class="col-6">
                        <div class="fs-4 fw-semibold text-info">GH₵<?= number_format($displayStats['total_revenue'], 0) ?></div>
                        <div class="small text-muted">Revenue</div>
                    </div>
                    <div class="col-6">
                        <div class="fs-4 fw-semibold text-warning"><?= number_format($displayStats['unique_voters']) ?></div>
                        <div class="small text-muted">Unique Voters</div>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <a href="<?= ORGANIZER_URL ?>/contestants?event=<?= $event['id'] ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-users me-2"></i>Manage Contestants
                    </a>
                    <a href="<?= ORGANIZER_URL ?>/voting/analytics?event=<?= $event['id'] ?>" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-chart-bar me-2"></i>View Analytics
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if ($event['status'] === 'draft'): ?>
                        <button class="btn btn-success btn-sm" onclick="publishEvent()">
                            <i class="fas fa-play me-2"></i>Publish Event
                        </button>
                    <?php elseif ($event['status'] === 'active'): ?>
                        <button class="btn btn-warning btn-sm" onclick="pauseEvent()">
                            <i class="fas fa-pause me-2"></i>Pause Event
                        </button>
                    <?php endif; ?>
                    
                    <button class="btn btn-outline-secondary btn-sm" onclick="toggleResults()">
                        <i class="fas fa-eye me-2"></i>
                        <?= $event['results_visible'] ? 'Hide' : 'Show' ?> Results
                    </button>
                    
                    <button class="btn btn-outline-info btn-sm" onclick="sendReminder()">
                        <i class="fas fa-envelope me-2"></i>Send Reminder
                    </button>
                    
                    <button class="btn btn-outline-success btn-sm" onclick="downloadReport()">
                        <i class="fas fa-download me-2"></i>Download Report
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event URL and Sharing -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Event URLs & Sharing</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Public Event URL</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="eventUrl" value="<?= APP_URL ?>/events/<?= $event['code'] ?>" readonly>
                            <button class="btn btn-outline-secondary" onclick="copyUrl('eventUrl')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Voting URL</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="votingUrl" value="<?= APP_URL ?>/events/<?= $event['code'] ?>/vote" readonly>
                            <button class="btn btn-outline-secondary" onclick="copyUrl('votingUrl')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <label class="form-label">Share on Social Media</label>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm" onclick="shareOnSocial('facebook')">
                            <i class="fab fa-facebook me-1"></i>Facebook
                        </button>
                        <button class="btn btn-info btn-sm" onclick="shareOnSocial('twitter')">
                            <i class="fab fa-twitter me-1"></i>Twitter
                        </button>
                        <button class="btn btn-success btn-sm" onclick="shareOnSocial('whatsapp')">
                            <i class="fab fa-whatsapp me-1"></i>WhatsApp
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="shareOnSocial('email')">
                            <i class="fas fa-envelope me-1"></i>Email
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Statistics -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?= number_format($displayStats['total_contestants']) ?></h4>
                        <p class="mb-0">Contestants</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?= number_format($displayStats['total_votes']) ?></h4>
                        <p class="mb-0">Total Votes</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-vote-yea fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">GH₵<?= number_format($displayStats['total_revenue'], 0) ?></h4>
                        <p class="mb-0">Revenue</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-money-bill fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?= number_format($displayStats['total_categories']) ?></h4>
                        <p class="mb-0">Categories</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-tags fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Categories and Contestants -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Categories & Contestants
                </h5>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" id="dragToggle" onclick="toggleDragMode()">
                        <i class="fas fa-arrows-alt me-1"></i><span class="d-none d-md-inline">Enable Drag</span>
                    </button>
                    <button class="btn btn-outline-primary" onclick="expandAllCategories()">
                        <i class="fas fa-expand-alt me-1"></i><span class="d-none d-md-inline">Expand All</span>
                    </button>
                    <button class="btn btn-outline-secondary" onclick="collapseAllCategories()">
                        <i class="fas fa-compress-alt me-1"></i><span class="d-none d-md-inline">Collapse All</span>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($categoriesWithContestants)): ?>
                    <div class="accordion categories-container" id="categoriesAccordion">
                        <?php foreach ($categoriesWithContestants as $index => $category): ?>
                            <div class="accordion-item category-item" data-category-id="<?= $category['category_id'] ?>">
                                <div class="drag-handle" style="display: none; position: absolute; left: 10px; top: 50%; transform: translateY(-50%); cursor: move; z-index: 10; padding: 8px; color: #6c757d;">
                                    <i class="fas fa-grip-vertical"></i>
                                </div>
                                <h2 class="accordion-header" id="heading<?= $category['category_id'] ?>">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#collapse<?= $category['category_id'] ?>" aria-expanded="false">
                                        <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                            <div>
                                                <strong><?= htmlspecialchars($category['category_name']) ?></strong>
                                                <?php if (!empty($category['category_description'])): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($category['category_description']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-primary"><?= count($category['contestants']) ?> contestants</span>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse<?= $category['category_id'] ?>" class="accordion-collapse collapse" 
                                     data-bs-parent="#categoriesAccordion">
                                    <div class="accordion-body">
                                        <?php if (!empty($category['contestants'])): ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Rank</th>
                                                            <th>Contestant</th>
                                                            <th>Voting Code</th>
                                                            <th>Votes</th>
                                                            <th>Revenue</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="contestants-container" data-category-id="<?= $category['category_id'] ?>">
                                                        <?php foreach ($category['contestants'] as $rank => $contestant): ?>
                                                            <tr class="contestant-item" data-contestant-id="<?= $contestant['id'] ?>">
                                                                <td class="drag-handle-cell" style="display: none; cursor: move; color: #6c757d;">
                                                                    <i class="fas fa-grip-vertical"></i>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-<?= $rank === 0 ? 'warning' : ($rank === 1 ? 'secondary' : ($rank === 2 ? 'dark' : 'light text-dark')) ?>">
                                                                        #<?= $rank + 1 ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <?php if (!empty($contestant['image_url'])): ?>
                                                                            <img src="<?= htmlspecialchars(image_url($contestant['image_url'])) ?>" 
                                                                                 alt="<?= htmlspecialchars($contestant['name']) ?>"
                                                                                 class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                                                        <?php else: ?>
                                                                            <div class="bg-light rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                                                 style="width: 32px; height: 32px;">
                                                                                <i class="fas fa-user text-muted"></i>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                        <div>
                                                                            <strong><?= htmlspecialchars($contestant['name']) ?></strong>
                                                                            <br><small class="text-muted"><?= htmlspecialchars($contestant['contestant_code']) ?></small>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex flex-column gap-1">
                                                                        <div class="d-flex align-items-center">
                                                                            <code class="bg-primary text-white px-2 py-1 rounded"><?= htmlspecialchars($contestant['voting_shortcode']) ?></code>
                                                                            <button class="btn btn-sm btn-outline-secondary ms-1" 
                                                                                    onclick="copyToClipboard('<?= htmlspecialchars($contestant['voting_shortcode']) ?>')"
                                                                                    title="Copy voting code">
                                                                                <i class="fas fa-copy"></i>
                                                                            </button>
                                                                        </div>
                                                                        <div class="d-flex align-items-center">
                                                                            <small class="text-muted me-1">
                                                                                <i class="fas fa-link"></i>
                                                                                <?= APP_URL ?>/vote/<?= htmlspecialchars($contestant['voting_shortcode']) ?>
                                                                            </small>
                                                                            <button class="btn btn-sm btn-outline-success" 
                                                                                    onclick="copyToClipboard('<?= APP_URL ?>/vote/<?= htmlspecialchars($contestant['voting_shortcode']) ?>')"
                                                                                    title="Copy short URL">
                                                                                <i class="fas fa-copy"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <strong><?= number_format($contestant['total_votes']) ?></strong>
                                                                    <?php if ($contestant['last_vote_at']): ?>
                                                                        <br><small class="text-muted">Last: <?= date('M j, g:i A', strtotime($contestant['last_vote_at'])) ?></small>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <strong class="text-success">GH₵<?= number_format($contestant['revenue'], 2) ?></strong>
                                                                </td>
                                                                <td>
                                                                    <div class="btn-group btn-group-sm">
                                                                        <button class="btn btn-outline-primary" onclick="viewContestant(<?= $contestant['id'] ?>)">
                                                                            <i class="fas fa-eye"></i>
                                                                        </button>
                                                                        <button class="btn btn-outline-secondary" onclick="editContestant(<?= $contestant['id'] ?>)">
                                                                            <i class="fas fa-edit"></i>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center py-3">
                                                <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                                <p class="text-muted">No contestants in this category yet.</p>
                                                <a href="<?= ORGANIZER_URL ?>/contestants/create" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-plus me-1"></i>Add Contestant
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-list fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Categories Yet</h5>
                        <p class="text-muted">Create categories to organize your contestants</p>
                        <a href="<?= ORGANIZER_URL ?>/events/wizard?edit=<?= $event['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-magic me-2"></i>Use Event Wizard
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Recent Votes -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-clock me-2"></i>Recent Votes
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($recentVotes)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($recentVotes, 0, 5) as $vote): ?>
                            <div class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <strong><?= htmlspecialchars($vote['contestant_name']) ?></strong>
                                        <br><small class="text-muted">
                                            <?= htmlspecialchars($vote['category_name']) ?> • 
                                            Code: <?= htmlspecialchars($vote['voting_code']) ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-primary"><?= $vote['quantity'] ?> votes</span>
                                        <br><small class="text-muted">
                                            <?php 
                                            $timeAgo = $vote['seconds_ago'];
                                            if ($timeAgo < 60) echo $timeAgo . 's ago';
                                            elseif ($timeAgo < 3600) echo floor($timeAgo/60) . 'm ago';
                                            else echo floor($timeAgo/3600) . 'h ago';
                                            ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="<?= ORGANIZER_URL ?>/voting/receipts?event=<?= $event['id'] ?>" class="btn btn-sm btn-outline-primary">
                            View All Votes
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-vote-yea fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No votes yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Event Info -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Event Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <small class="text-muted">Event Code</small>
                        <br><strong><?= htmlspecialchars($event['code']) ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Vote Price</small>
                        <br><strong>GH₵<?= number_format($eventStats['vote_price'] ?? $event['vote_price'] ?? 0, 2) ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Transactions</small>
                        <br><strong><?= number_format($eventStats['total_transactions'] ?? 0) ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Avg. Transaction</small>
                        <br><strong>GH₵<?= number_format($eventStats['avg_transaction_amount'] ?? 0, 2) ?></strong>
                    </div>
                    <div class="col-12">
                        <small class="text-muted">Results Visibility</small>
                        <br><span class="badge bg-<?= $event['results_visible'] ? 'success' : 'warning' ?>">
                            <?= $event['results_visible'] ? 'Public' : 'Hidden' ?>
                        </span>
                    </div>
                </div>
                
                <?php if (!empty($event['description'])): ?>
                    <hr>
                    <small class="text-muted">Description</small>
                    <p class="small"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function copyEventCode() {
    const code = '<?= $event['code'] ?>';
    navigator.clipboard.writeText(code).then(() => {
        alert('Event code copied to clipboard!');
    });
}

function copyUrl(inputId) {
    const input = document.getElementById(inputId);
    input.select();
    document.execCommand('copy');
    
    // Show feedback
    const button = event.target.closest('button');
    const originalIcon = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    button.classList.add('btn-success');
    button.classList.remove('btn-outline-secondary');
    
    setTimeout(() => {
        button.innerHTML = originalIcon;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-secondary');
    }, 2000);
}

function shareEvent() {
    const eventUrl = '<?= APP_URL ?>/events/<?= $event['code'] ?>';
    const eventName = '<?= addslashes($event['name']) ?>';
    
    if (navigator.share) {
        navigator.share({
            title: eventName,
            text: `Check out this voting event: ${eventName}`,
            url: eventUrl
        });
    } else {
        copyUrl('eventUrl');
        alert('Event URL copied to clipboard!');
    }
}

function shareOnSocial(platform) {
    const eventUrl = '<?= APP_URL ?>/events/<?= $event['code'] ?>';
    const eventName = '<?= addslashes($event['name']) ?>';
    const message = `Check out this voting event: ${eventName}`;
    
    let shareUrl = '';
    
    switch (platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(eventUrl)}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(message)}&url=${encodeURIComponent(eventUrl)}`;
            break;
        case 'whatsapp':
            shareUrl = `https://wa.me/?text=${encodeURIComponent(message + ' ' + eventUrl)}`;
            break;
        case 'email':
            shareUrl = `mailto:?subject=${encodeURIComponent(eventName)}&body=${encodeURIComponent(message + '\n\n' + eventUrl)}`;
            break;
    }
    
    if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }
}

function publishEvent() {
    if (confirm('Are you sure you want to publish this event? It will become visible to voters.')) {
        fetch(`<?= ORGANIZER_URL ?>/events/<?= $event['id'] ?>/publish`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error publishing event: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error publishing event');
        });
    }
}

function pauseEvent() {
    if (confirm('Are you sure you want to pause this event? Voting will be temporarily disabled.')) {
        console.log('Pausing event...');
        alert('Event paused successfully!');
    }
}

function toggleResults() {
    const actionText = <?= $event['results_visible'] ? '"hide"' : '"show"' ?>;
    
    if (confirm(`Are you sure you want to ${actionText} the results for this event?`)) {
        // Create and submit form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `<?= ORGANIZER_URL ?>/events/<?= $event['id'] ?>/toggle-results`;
        
        document.body.appendChild(form);
        form.submit();
    }
}

function sendReminder() {
    if (confirm('Send a reminder email to all registered voters?')) {
        console.log('Sending reminder...');
        alert('Reminder sent successfully!');
    }
}

function downloadReport() {
    console.log('Downloading event report...');
    alert('Report download functionality will be implemented soon!');
}

function duplicateEvent() {
    if (confirm('Create a copy of this event?')) {
        console.log('Duplicating event...');
        alert('Event duplicated successfully!');
    }
}

function exportEvent() {
    console.log('Exporting event data...');
    alert('Export functionality will be implemented soon!');
}

function deleteEvent() {
    if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
        console.log('Deleting event...');
        alert('Event deletion functionality will be implemented with proper safeguards!');
    }
}

function viewResults() {
    window.open('<?= ORGANIZER_URL ?>/voting/live?event=<?= $event['id'] ?>', '_blank');
}

// Copy voting code to clipboard
function copyToClipboard(code) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(code).then(() => {
            showToast('Voting code copied!', 'success');
        }).catch(err => {
            console.error('Failed to copy: ', err);
            fallbackCopyTextToClipboard(code);
        });
    } else {
        fallbackCopyTextToClipboard(code);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.top = "0";
    textArea.style.left = "0";
    
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showToast('Voting code copied!', 'success');
        } else {
            showToast('Failed to copy code', 'error');
        }
    } catch (err) {
        console.error('Fallback: Unable to copy', err);
        showToast('Failed to copy code', 'error');
    }
    
    document.body.removeChild(textArea);
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 200px;';
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'} me-2"></i>${message}`;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 3000);
}

// Accordion controls
function expandAllCategories() {
    try {
        document.querySelectorAll('#categoriesAccordion .accordion-collapse').forEach(collapse => {
            // Check if bootstrap is available
            if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                const bsCollapse = new bootstrap.Collapse(collapse, { show: true });
            } else {
                console.error('Bootstrap Collapse not available');
                // Fallback: manually show the collapse
                collapse.classList.add('show');
                const button = document.querySelector(`[data-bs-target="#${collapse.id}"]`);
                if (button) {
                    button.classList.remove('collapsed');
                    button.setAttribute('aria-expanded', 'true');
                }
            }
        });
    } catch (error) {
        console.error('Error expanding categories:', error);
    }
}

function collapseAllCategories() {
    try {
        document.querySelectorAll('#categoriesAccordion .accordion-collapse.show').forEach(collapse => {
            if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                const bsCollapse = bootstrap.Collapse.getInstance(collapse);
                if (bsCollapse) {
                    bsCollapse.hide();
                } else {
                    // Create new instance and hide
                    const newCollapse = new bootstrap.Collapse(collapse, { show: false });
                }
            } else {
                console.error('Bootstrap Collapse not available');
                // Fallback: manually hide the collapse
                collapse.classList.remove('show');
                const button = document.querySelector(`[data-bs-target="#${collapse.id}"]`);
                if (button) {
                    button.classList.add('collapsed');
                    button.setAttribute('aria-expanded', 'false');
                }
            }
        });
    } catch (error) {
        console.error('Error collapsing categories:', error);
    }
}

// Debug function to check Bootstrap availability
function checkBootstrapAvailability() {
    if (typeof bootstrap !== 'undefined') {
        console.log('✅ Bootstrap is available');
        if (bootstrap.Collapse) {
            console.log('✅ Bootstrap Collapse is available');
        } else {
            console.log('❌ Bootstrap Collapse is NOT available');
        }
    } else {
        console.log('❌ Bootstrap is NOT available');
    }
}

// Check Bootstrap on page load
document.addEventListener('DOMContentLoaded', function() {
    checkBootstrapAvailability();
    
    // Initialize accordion if Bootstrap is available
    if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
        console.log('Initializing accordion...');
        // Accordion should work automatically with data-bs-* attributes
    } else {
        console.warn('Bootstrap not available, accordion may not work properly');
    }
});

// Contestant actions
function viewContestant(id) {
    window.location.href = `<?= ORGANIZER_URL ?>/contestants/${id}`;
}

function editContestant(id) {
    window.location.href = `<?= ORGANIZER_URL ?>/contestants/${id}/edit`;
}

// Drag and Drop functionality
let categorySortables = [];
let contestantSortables = [];
let dragModeEnabled = false;

function toggleDragMode() {
    const dragToggle = document.getElementById('dragToggle');
    dragModeEnabled = !dragModeEnabled;
    
    if (dragModeEnabled) {
        // Enable drag mode
        dragToggle.innerHTML = '<i class="fas fa-times me-1"></i><span class="d-none d-md-inline">Disable Drag</span>';
        dragToggle.classList.remove('btn-outline-primary');
        dragToggle.classList.add('btn-danger');
        
        // Show drag handles
        document.querySelectorAll('.drag-handle').forEach(handle => {
            handle.style.display = 'block';
        });
        document.querySelectorAll('.drag-handle-cell').forEach(cell => {
            cell.style.display = 'table-cell';
        });
        
        // Add padding to accordion headers for drag handle
        document.querySelectorAll('.accordion-header').forEach(header => {
            header.style.paddingLeft = '40px';
            header.dataset.dragModeActive = 'true';
        });
        
        // Initialize sortables
        initializeSortables();
        
        showNotification('Drag mode enabled. Drag categories and contestants to reorder them.', 'info');
    } else {
        // Disable drag mode
        dragToggle.innerHTML = '<i class="fas fa-arrows-alt me-1"></i><span class="d-none d-md-inline">Enable Drag</span>';
        dragToggle.classList.remove('btn-danger');
        dragToggle.classList.add('btn-outline-primary');
        
        // Hide drag handles
        document.querySelectorAll('.drag-handle').forEach(handle => {
            handle.style.display = 'none';
        });
        document.querySelectorAll('.drag-handle-cell').forEach(cell => {
            cell.style.display = 'none';
        });
        
        // Remove padding from accordion headers
        document.querySelectorAll('.accordion-header').forEach(header => {
            header.style.paddingLeft = '';
            delete header.dataset.dragModeActive;
        });
        
        // Destroy sortables
        destroySortables();
        
        showNotification('Drag mode disabled.', 'success');
    }
}

// Initialize sortable instances
function initializeSortables() {
    if (typeof Sortable === 'undefined') {
        console.error('SortableJS library not loaded');
        return;
    }
    
    // Clear existing sortables first
    destroySortables();
    
    // Initialize category sortables
    document.querySelectorAll('.categories-container').forEach((container) => {
        const sortable = new Sortable(container, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                const eventId = <?= $event['id'] ?>;
                const categoryIds = Array.from(container.querySelectorAll('.category-item')).map(item => item.dataset.categoryId);
                saveCategoryOrder(eventId, categoryIds);
            }
        });
        categorySortables.push(sortable);
    });
    
    // Initialize contestant sortables
    document.querySelectorAll('.contestants-container').forEach((container) => {
        const sortable = new Sortable(container, {
            handle: '.drag-handle-cell',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                const categoryId = container.dataset.categoryId;
                const contestantIds = Array.from(container.querySelectorAll('.contestant-item')).map(item => item.dataset.contestantId);
                saveContestantOrder(categoryId, contestantIds);
            }
        });
        contestantSortables.push(sortable);
    });
}

// Destroy sortable instances
function destroySortables() {
    categorySortables.forEach(sortable => sortable.destroy());
    contestantSortables.forEach(sortable => sortable.destroy());
    categorySortables = [];
    contestantSortables = [];
}

// Save category order to backend
function saveCategoryOrder(eventId, categoryIds) {
    fetch(`<?= ORGANIZER_URL ?>/api/categories/reorder`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            event_id: eventId,
            category_ids: categoryIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Category order updated successfully', 'success');
        } else {
            showNotification('Failed to update category order: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating category order', 'error');
    });
}

// Save contestant order to backend
function saveContestantOrder(categoryId, contestantIds) {
    fetch(`<?= ORGANIZER_URL ?>/api/contestants/reorder`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            category_id: categoryId,
            contestant_ids: contestantIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Contestant order updated successfully', 'success');
        } else {
            showNotification('Failed to update contestant order: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating contestant order', 'error');
    });
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>

<!-- SortableJS Library -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<style>
/* Drag and drop styles */
.sortable-ghost {
    opacity: 0.4;
    background: #f8f9fa;
    border: 2px dashed #007bff;
    border-radius: 8px;
}

.sortable-chosen {
    background: #e3f2fd;
    border: 2px solid #2196f3;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.sortable-drag {
    opacity: 0.8;
    transform: rotate(2deg);
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
}

.category-item {
    position: relative;
    transition: all 0.2s ease;
}

.contestant-item {
    transition: all 0.2s ease;
}

.drag-handle {
    user-select: none;
}

.drag-handle-cell {
    user-select: none;
}
</style>
