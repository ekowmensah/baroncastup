<!-- Category Details Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-tag me-2"></i>
            <?= htmlspecialchars($category['name']) ?>
        </h2>
        <p class="text-muted mb-0"><?= htmlspecialchars($category['description'] ?: 'No description provided') ?></p>
    </div>
    <div class="btn-group">
        <button class="btn btn-outline-secondary" onclick="editCategory(<?= $category['id'] ?>)">
            <i class="fas fa-edit me-2"></i>Edit Category
        </button>
        <a href="<?= ORGANIZER_URL ?>/categories" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Back to Categories
        </a>
    </div>
</div>

<!-- Category Info Card -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Category Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Category Name:</strong></td>
                                <td><?= htmlspecialchars($category['name']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Event:</strong></td>
                                <td>
                                    <span class="fw-semibold"><?= htmlspecialchars($category['event_name']) ?></span>
                                    <br>
                                    <span class="badge bg-<?= $category['event_status'] === 'active' ? 'success' : ($category['event_status'] === 'draft' ? 'warning' : 'secondary') ?>">
                                        <?= ucfirst($category['event_status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="badge bg-<?= $category['active'] ? 'success' : 'secondary' ?>">
                                        <?= $category['active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Event Dates:</strong></td>
                                <td>
                                    <?= date('M j, Y', strtotime($category['start_date'])) ?> - 
                                    <?= date('M j, Y', strtotime($category['end_date'])) ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Created:</strong></td>
                                <td><?= date('M j, Y H:i', strtotime($category['created_at'])) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Last Updated:</strong></td>
                                <td><?= date('M j, Y H:i', strtotime($category['updated_at'])) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Category Statistics -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-12">
                        <div class="fs-4 fw-semibold text-primary"><?= count($contestants) ?></div>
                        <div class="small text-muted">Total Contestants</div>
                    </div>
                </div>
                
                <div class="row text-center mb-3">
                    <div class="col-6">
                        <div class="fs-5 fw-semibold text-success">
                            <?= array_sum(array_column($contestants, 'total_votes')) ?>
                        </div>
                        <div class="small text-muted">Total Votes</div>
                    </div>
                    <div class="col-6">
                        <div class="fs-5 fw-semibold text-info">
                            GH₵<?= number_format(array_sum(array_column($contestants, 'revenue')), 2) ?>
                        </div>
                        <div class="small text-muted">Revenue</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contestants in Category -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-users me-2"></i>Contestants in this Category
        </h5>
        <div>
            <a href="<?= ORGANIZER_URL ?>/contestants/create?category=<?= $category['id'] ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-2"></i>Add Contestant
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($contestants)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Contestant</th>
                            <th>Voting Code</th>
                            <th class="text-center">Votes</th>
                            <th class="text-center">Revenue</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contestants as $index => $contestant): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-<?= $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : ($index === 2 ? 'dark' : 'light text-dark')) ?>">
                                        #<?= $index + 1 ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($contestant['image_url'])): ?>
                                            <img src="<?= htmlspecialchars(image_url($contestant['image_url'])) ?>" 
                                                 alt="<?= htmlspecialchars($contestant['name']) ?>"
                                                 class="rounded-circle me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-user text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-semibold"><?= htmlspecialchars($contestant['name']) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($contestant['contestant_code']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <div class="d-flex align-items-center">
                                            <code class="bg-primary text-white px-2 py-1 rounded">
                                                <?= htmlspecialchars($contestant['voting_shortcode']) ?>
                                            </code>
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
                                <td class="text-center">
                                    <strong><?= number_format($contestant['total_votes']) ?></strong>
                                </td>
                                <td class="text-center">
                                    <strong class="text-success">GH₵<?= number_format($contestant['revenue'], 2) ?></strong>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= ORGANIZER_URL ?>/contestants/<?= $contestant['id'] ?>" class="btn btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= ORGANIZER_URL ?>/contestants/<?= $contestant['id'] ?>/edit" class="btn btn-outline-secondary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Contestants Yet</h5>
                <p class="text-muted">Add contestants to this category to start collecting votes</p>
                <a href="<?= ORGANIZER_URL ?>/contestants/create?category=<?= $category['id'] ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add First Contestant
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function editCategory(id) {
    // Redirect to categories page with edit action
    window.location.href = `<?= ORGANIZER_URL ?>/categories#edit-${id}`;
}
</script>
