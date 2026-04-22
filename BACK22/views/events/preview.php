<?php include __DIR__ . '/../layout/public_header.php'; ?>

<!-- Preview Banner -->
<div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-eye fa-2x me-3"></i>
        <div>
            <h5 class="alert-heading mb-1">
                <i class="fas fa-info-circle me-2"></i>
                Event Preview Mode
            </h5>
            <p class="mb-0">
                This is how your event will appear to the public. 
                <strong>Status:</strong> <?= ucfirst($event['status']) ?> | 
                <strong>Visibility:</strong> <?= ucfirst($event['visibility']) ?>
            </p>
        </div>
        <div class="ms-auto">
            <a href="<?= ORGANIZER_URL ?>/events/<?= $event['id'] ?>" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to Event
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Event Details -->
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <?php if ($event['featured_image']): ?>
                <img src="<?= htmlspecialchars(image_url($event['featured_image'])) ?>" 
                     class="card-img-top" 
                     style="height: 300px; object-fit: cover;"
                     alt="<?= htmlspecialchars($event['name']) ?>">
            <?php endif; ?>
            
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h1 class="card-title mb-2"><?= htmlspecialchars($event['name']) ?></h1>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-<?= $event['status'] === 'active' ? 'success' : ($event['status'] === 'draft' ? 'secondary' : 'warning') ?> fs-6">
                                <?= ucfirst($event['status']) ?>
                            </span>
                            <small class="text-muted">
                                <i class="fas fa-code me-1"></i>
                                <?= htmlspecialchars($event['code']) ?>
                            </small>
                        </div>
                    </div>
                    
                    <?php if ($canVote): ?>
                        <div class="btn btn-primary btn-lg disabled" title="Preview mode - voting disabled">
                            <i class="fas fa-vote-yea me-2"></i>
                            Vote Now (Preview)
                        </div>
                    <?php else: ?>
                        <div class="btn btn-secondary btn-lg disabled">
                            <i class="fas fa-clock me-2"></i>
                            <?php if ($event['status'] === 'draft'): ?>
                                Draft Event
                            <?php elseif (strtotime($event['start_date']) > time()): ?>
                                Voting Opens Soon
                            <?php else: ?>
                                Voting Closed
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($event['description']): ?>
                    <div class="mb-4">
                        <h5>About This Event</h5>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                    </div>
                <?php endif; ?>
                
                <!-- Event Timeline -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-play-circle text-success me-2"></i>
                            <div>
                                <small class="text-muted d-block">Voting Starts</small>
                                <strong><?= date('M j, Y g:i A', strtotime($event['start_date'])) ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-stop-circle text-danger me-2"></i>
                            <div>
                                <small class="text-muted d-block">Voting Ends</small>
                                <strong><?= date('M j, Y g:i A', strtotime($event['end_date'])) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Vote Price -->
                <div class="alert alert-info">
                    <i class="fas fa-money-bill me-2"></i>
                    <strong>Vote Price:</strong> GH₵<?= number_format($event['vote_price'], 2) ?> per vote
                </div>
            </div>
        </div>
        
        <!-- Categories and Contestants -->
        <?php if (!empty($categories)): ?>
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Categories & Contestants
                    </h5>
                </div>
                <div class="card-body">
                    <?php foreach ($categories as $category): ?>
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-tag me-2"></i>
                                <?= htmlspecialchars($category['name']) ?>
                            </h6>
                            
                            <?php 
                            $categoryContestants = array_filter($contestants, function($c) use ($category) {
                                return $c['category_id'] == $category['id'];
                            });
                            ?>
                            
                            <?php if (!empty($categoryContestants)): ?>
                                <div class="row g-3">
                                    <?php foreach ($categoryContestants as $contestant): ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card border h-100">
                                                <?php if ($contestant['image_url']): ?>
                                                    <img src="<?= htmlspecialchars(image_url($contestant['image_url'])) ?>" 
                                                         class="card-img-top" 
                                                         style="height: 200px; object-fit: cover;"
                                                         alt="<?= htmlspecialchars($contestant['name']) ?>">
                                                <?php endif; ?>
                                                <div class="card-body d-flex flex-column">
                                                    <h6 class="card-title"><?= htmlspecialchars($contestant['name']) ?></h6>
                                                    <?php if ($contestant['bio']): ?>
                                                        <p class="card-text text-muted small flex-grow-1">
                                                            <?= htmlspecialchars(substr($contestant['bio'], 0, 100)) ?>
                                                            <?= strlen($contestant['bio']) > 100 ? '...' : '' ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <div class="mt-auto">
                                                        <div class="btn btn-outline-primary btn-sm w-100 disabled">
                                                            <i class="fas fa-vote-yea me-1"></i>
                                                            Vote (Preview)
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-light">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No contestants added to this category yet.
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Categories Created</h5>
                    <p class="text-muted">Add categories and contestants to see how they'll appear to voters.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Event Stats -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Event Statistics
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-6">
                        <div class="border rounded p-3">
                            <h4 class="text-primary mb-1"><?= count($categories) ?></h4>
                            <small class="text-muted">Categories</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3">
                            <h4 class="text-success mb-1"><?= count($contestants) ?></h4>
                            <small class="text-muted">Contestants</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Leaderboard Preview -->
        <?php if (!empty($leaderboard)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-trophy me-2"></i>
                        Current Leaderboard
                    </h6>
                </div>
                <div class="card-body">
                    <?php foreach (array_slice($leaderboard, 0, 5) as $index => $leader): ?>
                        <div class="d-flex align-items-center mb-2">
                            <div class="me-3">
                                <?php if ($index === 0): ?>
                                    <i class="fas fa-crown text-warning"></i>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark"><?= $index + 1 ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold"><?= htmlspecialchars($leader['contestant_name']) ?></div>
                                <small class="text-muted"><?= number_format($leader['total_votes']) ?> votes</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Vote Bundles -->
        <?php if (!empty($bundles)): ?>
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-gift me-2"></i>
                        Vote Packages
                    </h6>
                </div>
                <div class="card-body">
                    <?php foreach ($bundles as $bundle): ?>
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($bundle['name']) ?></h6>
                                    <small class="text-muted"><?= $bundle['votes'] ?> votes</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-success">$<?= number_format($bundle['price'], 2) ?></div>
                                    <?php if (isset($bundle['discount']) && $bundle['discount'] > 0): ?>
                                        <small class="text-danger">Save <?= $bundle['discount'] ?>%</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.alert-warning {
    border-left: 5px solid #ffc107;
}

.card {
    border-radius: 12px;
}

.badge {
    border-radius: 20px;
}

.btn.disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.preview-mode {
    position: relative;
}

.preview-mode::after {
    content: "PREVIEW";
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(255, 193, 7, 0.9);
    color: #000;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: bold;
}
</style>

<?php include __DIR__ . '/../layout/public_footer.php'; ?>
