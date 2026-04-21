<?php 
$content = ob_start(); 
?>

<!-- Contestant Details -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-user me-2"></i>
            <?= htmlspecialchars($contestant['name']) ?>
        </h2>
        <p class="text-muted mb-0">Contestant in <?= htmlspecialchars($contestant['event_name']) ?></p>
    </div>
    <div>
        <a href="<?= ORGANIZER_URL ?>/contestants/<?= $contestant['id'] ?>/edit" class="btn btn-primary me-2">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
        <a href="<?= ORGANIZER_URL ?>/contestants/<?= $contestant['id'] ?>/stats" class="btn btn-info me-2">
            <i class="fas fa-chart-bar me-2"></i>Statistics
        </a>
        <a href="<?= ORGANIZER_URL ?>/contestants" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Contestants
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Basic Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Basic Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Name:</label>
                            <p class="mb-0"><?= htmlspecialchars($contestant['name']) ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Event:</label>
                            <p class="mb-0"><?= htmlspecialchars($contestant['event_name']) ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status:</label>
                            <p class="mb-0">
                                <?php if ($contestant['active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <?php if ($contestant['image_url']): ?>
                            <div class="text-center">
                                <img src="<?= htmlspecialchars(image_url($contestant['image_url'])) ?>" 
                                     alt="<?= htmlspecialchars($contestant['name']) ?>" 
                                     class="img-thumbnail" 
                                     style="max-width: 200px; max-height: 200px; object-fit: cover;">
                            </div>
                        <?php else: ?>
                            <div class="text-center">
                                <div class="bg-light border rounded d-flex align-items-center justify-content-center" 
                                     style="width: 200px; height: 200px; margin: 0 auto;">
                                    <i class="fas fa-user fa-3x text-muted"></i>
                                </div>
                                <p class="text-muted mt-2">No photo uploaded</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($contestant['bio']): ?>
                    <div class="mt-3">
                        <label class="form-label fw-bold">Bio:</label>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($contestant['bio'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Categories & Shortcodes -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-tags me-2"></i>
                    Categories & Shortcodes
                </h5>
            </div>
            <div class="card-body">
                <?php if ($contestant['categories']): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Categories:</label>
                            <div>
                                <?php 
                                $categories = explode(',', $contestant['categories']);
                                foreach ($categories as $category): ?>
                                    <span class="badge bg-primary me-1 mb-1"><?= htmlspecialchars(trim($category)) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Shortcodes:</label>
                            <div>
                                <?php 
                                $shortcodes = explode(',', $contestant['shortcodes']);
                                foreach ($shortcodes as $shortcode): 
                                    if (trim($shortcode)): ?>
                                        <span class="badge bg-success me-1 mb-1"><?= htmlspecialchars(trim($shortcode)) ?></span>
                                    <?php endif;
                                endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No categories assigned</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Quick Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Quick Statistics
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary mb-1"><?= number_format($contestant['total_votes']) ?></h4>
                            <small class="text-muted">Total Votes</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success mb-1"><?= number_format($contestant['total_transactions']) ?></h4>
                        <small class="text-muted">Transactions</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-tools me-2"></i>
                    Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= ORGANIZER_URL ?>/contestants/<?= $contestant['id'] ?>/edit" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit Details
                    </a>
                    <a href="<?= ORGANIZER_URL ?>/contestants/<?= $contestant['id'] ?>/stats" class="btn btn-info">
                        <i class="fas fa-chart-bar me-2"></i>View Statistics
                    </a>
                    <a href="<?= ORGANIZER_URL ?>/events/<?= $contestant['event_id'] ?>" class="btn btn-outline-primary">
                        <i class="fas fa-calendar me-2"></i>View Event
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../../layout/organizer_layout.php';
?>
