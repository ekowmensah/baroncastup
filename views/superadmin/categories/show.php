<?php
/**
 * SuperAdmin Categories Management - Show Category Details View
 */
?>

<div class="container-fluid">
    <!-- Category Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="fas fa-tag me-2"></i>
                                <?= htmlspecialchars($category['name']) ?>
                            </h2>
                            <?php if ($category['description']): ?>
                                <p class="text-muted mb-3"><?= htmlspecialchars($category['description']) ?></p>
                            <?php endif; ?>
                            <div class="row">
                                <div class="col-sm-6">
                                    <small class="text-muted">Event:</small>
                                    <div class="fw-bold">
                                        <a href="<?= SUPERADMIN_URL ?>/events/<?= $category['event_id'] ?>" 
                                           class="text-decoration-none">
                                            <?= htmlspecialchars($category['event_name']) ?>
                                        </a>
                                    </div>
                                    <small class="text-muted"><?= htmlspecialchars($category['event_code']) ?></small>
                                </div>
                                <div class="col-sm-6">
                                    <small class="text-muted">Tenant:</small>
                                    <div class="fw-bold"><?= htmlspecialchars($category['tenant_name']) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 class="text-primary mb-1"><?= number_format($category['contestant_count']) ?></h4>
                                    <small class="text-muted">Contestants</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-success mb-1"><?= number_format($category['total_votes']) ?></h4>
                                    <small class="text-muted">Total Votes</small>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="btn-group">
                                    <button class="btn btn-outline-primary btn-sm dropdown-toggle" 
                                            type="button" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="<?= SUPERADMIN_URL ?>/events/<?= $category['event_id'] ?>">
                                            <i class="fas fa-calendar-alt me-2"></i>View Event
                                        </a></li>
                                        <li><a class="dropdown-item" href="<?= SUPERADMIN_URL ?>/contestants?category_id=<?= $category['id'] ?>">
                                            <i class="fas fa-users me-2"></i>View All Contestants
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteCategory(<?= $category['id'] ?>)">
                                            <i class="fas fa-trash me-2"></i>Delete Category
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Category Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Display Order:</strong></div>
                        <div class="col-sm-8"><?= $category['display_order'] ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Created:</strong></div>
                        <div class="col-sm-8"><?= date('F j, Y g:i A', strtotime($category['created_at'])) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Last Updated:</strong></div>
                        <div class="col-sm-8"><?= date('F j, Y g:i A', strtotime($category['updated_at'])) ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Category Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end">
                                <h5 class="text-primary mb-1"><?= number_format($category['contestant_count']) ?></h5>
                                <small class="text-muted">Contestants</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h5 class="text-success mb-1"><?= number_format($category['total_votes']) ?></h5>
                                <small class="text-muted">Total Votes</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h5 class="text-info mb-1">
                                <?= $category['contestant_count'] > 0 ? number_format($category['total_votes'] / $category['contestant_count'], 1) : '0' ?>
                            </h5>
                            <small class="text-muted">Avg Votes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contestants in Category -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>
                        Contestants in this Category (<?= count($contestants) ?>)
                    </h5>
                    <?php if (count($contestants) > 0): ?>
                        <a href="<?= SUPERADMIN_URL ?>/contestants?category_id=<?= $category['id'] ?>" 
                           class="btn btn-sm btn-outline-primary">
                            View All
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($contestants)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Contestants Found</h5>
                            <p class="text-muted">This category doesn't have any contestants assigned to it yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Contestant</th>
                                        <th>Code</th>
                                        <th>Status</th>
                                        <th>Total Votes</th>
                                        <th>Category Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($contestants as $contestant): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($contestant['image_url']): ?>
                                                    <img src="<?= htmlspecialchars(image_url($contestant['image_url'])) ?>" 
                                                         alt="Contestant" class="rounded-circle me-3" 
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <h6 class="mb-1">
                                                        <a href="<?= SUPERADMIN_URL ?>/contestants/<?= $contestant['id'] ?>" 
                                                           class="text-decoration-none">
                                                            <?= htmlspecialchars($contestant['name']) ?>
                                                        </a>
                                                    </h6>
                                                    <?php if ($contestant['bio']): ?>
                                                        <small class="text-muted"><?= htmlspecialchars(substr($contestant['bio'], 0, 50)) ?>...</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <code><?= htmlspecialchars($contestant['contestant_code']) ?></code>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $contestant['active'] ? 'success' : 'secondary' ?>">
                                                <?= $contestant['active'] ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-success"><?= number_format($contestant['total_votes']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $contestant['category_active'] ? 'success' : 'warning' ?>">
                                                <?= $contestant['category_active'] ? 'Active in Category' : 'Inactive in Category' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?= SUPERADMIN_URL ?>/contestants/<?= $contestant['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                        data-bs-toggle="dropdown" title="Actions">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <?php if ($contestant['category_active']): ?>
                                                        <li><a class="dropdown-item" href="#" onclick="toggleContestantInCategory(<?= $contestant['id'] ?>, <?= $category['id'] ?>, 0)">
                                                            <i class="fas fa-times text-warning me-2"></i>Remove from Category
                                                        </a></li>
                                                    <?php else: ?>
                                                        <li><a class="dropdown-item" href="#" onclick="toggleContestantInCategory(<?= $contestant['id'] ?>, <?= $category['id'] ?>, 1)">
                                                            <i class="fas fa-check text-success me-2"></i>Activate in Category
                                                        </a></li>
                                                    <?php endif; ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item" href="<?= APP_URL ?>/events/<?= $category['event_code'] ?>/contestant/<?= $contestant['contestant_code'] ?>" target="_blank">
                                                        <i class="fas fa-external-link-alt me-2"></i>View Public Profile
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Category Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </div>
                
                <p>Are you sure you want to delete the category <strong>"<?= htmlspecialchars($category['name']) ?>"</strong>?</p>
                
                <p class="text-muted">
                    This will remove all contestant associations with this category. 
                    The contestants themselves will not be deleted.
                </p>
                
                <p class="text-danger">
                    <strong><?= count($contestants) ?> contestants</strong> are currently in this category.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteCategory()">
                    <i class="fas fa-trash me-1"></i>Delete Category
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function deleteCategory(categoryId) {
    new bootstrap.Modal(document.getElementById('deleteCategoryModal')).show();
}

function confirmDeleteCategory() {
    const formData = new FormData();
    formData.append('category_id', <?= $category['id'] ?>);
    
    fetch('<?= SUPERADMIN_URL ?>/categories/delete', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('deleteCategoryModal')).hide();
            setTimeout(() => {
                window.location.href = '<?= SUPERADMIN_URL ?>/categories';
            }, 1000);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        showAlert('danger', 'An error occurred while deleting the category');
        console.error('Error:', error);
    });
}

function toggleContestantInCategory(contestantId, categoryId, active) {
    const formData = new FormData();
    formData.append('contestant_id', contestantId);
    formData.append('category_id', categoryId);
    formData.append('active', active);
    
    fetch('<?= SUPERADMIN_URL ?>/categories/toggle-contestant', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        showAlert('danger', 'An error occurred while updating the contestant status');
        console.error('Error:', error);
    });
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.row'));
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
</script>
