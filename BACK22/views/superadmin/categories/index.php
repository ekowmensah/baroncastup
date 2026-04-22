<?php
/**
 * SuperAdmin Categories Management - Index View
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tags me-2"></i>
                        Categories Management
                    </h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshCategories()">
                            <i class="fas fa-sync-alt me-1"></i>
                            Refresh
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-filter me-1"></i>
                                Filter
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="filterCategories('all')">All Categories</a></li>
                                <li><a class="dropdown-item" href="#" onclick="filterCategories('active')">With Contestants</a></li>
                                <li><a class="dropdown-item" href="#" onclick="filterCategories('empty')">Empty Categories</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="categoriesTable">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Event</th>
                                    <th>Tenant</th>
                                    <th>Contestants</th>
                                    <th>Total Votes</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                <tr data-category-id="<?= $category['id'] ?>" data-contestant-count="<?= $category['contestant_count'] ?>">
                                    <td>
                                        <div>
                                            <h6 class="mb-1">
                                                <a href="<?= SUPERADMIN_URL ?>/categories/<?= $category['id'] ?>" 
                                                   class="text-decoration-none">
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </a>
                                            </h6>
                                            <?php if ($category['description']): ?>
                                                <small class="text-muted"><?= htmlspecialchars($category['description']) ?></small>
                                            <?php endif; ?>
                                            <br>
                                            <small class="text-muted">Order: <?= $category['display_order'] ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($category['event_name']) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($category['event_code']) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($category['tenant_name']) ?></strong>
                                    </td>
                                    <td>
                                        <div class="text-center">
                                            <?php if ($category['contestant_count'] > 0): ?>
                                                <span class="badge bg-primary fs-6">
                                                    <?= number_format($category['contestant_count']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary fs-6">0</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-center">
                                            <strong class="text-success">
                                                <?= number_format($category['total_votes']) ?>
                                            </strong>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('M j, Y', strtotime($category['created_at'])) ?>
                                            <br>
                                            <?= date('g:i A', strtotime($category['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= SUPERADMIN_URL ?>/categories/<?= $category['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= SUPERADMIN_URL ?>/events/<?= $category['event_id'] ?>" 
                                               class="btn btn-sm btn-outline-info" title="View Event">
                                                <i class="fas fa-calendar-alt"></i>
                                            </a>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                        data-bs-toggle="dropdown" title="More Actions">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?= SUPERADMIN_URL ?>/contestants?category_id=<?= $category['id'] ?>">
                                                        <i class="fas fa-users me-2"></i>View Contestants
                                                    </a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteCategory(<?= $category['id'] ?>)">
                                                        <i class="fas fa-trash me-2"></i>Delete Category
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
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
                
                <form id="deleteCategoryForm">
                    <input type="hidden" id="categoryId" name="category_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Category:</label>
                        <div id="categoryName" class="fw-bold"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Event:</label>
                        <div id="eventName" class="fw-bold"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Contestants in this category:</label>
                        <div id="contestantCount" class="fw-bold text-danger"></div>
                    </div>
                    
                    <p class="text-muted">
                        Deleting this category will remove all contestant associations with it. 
                        The contestants themselves will not be deleted.
                    </p>
                </form>
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
    const categoryRow = document.querySelector(`tr[data-category-id="${categoryId}"]`);
    const categoryName = categoryRow.querySelector('h6 a').textContent;
    const eventName = categoryRow.querySelector('td:nth-child(2) strong').textContent;
    const contestantCount = categoryRow.dataset.contestantCount;
    
    document.getElementById('categoryId').value = categoryId;
    document.getElementById('categoryName').textContent = categoryName;
    document.getElementById('eventName').textContent = eventName;
    document.getElementById('contestantCount').textContent = contestantCount + ' contestants';
    
    new bootstrap.Modal(document.getElementById('deleteCategoryModal')).show();
}

function confirmDeleteCategory() {
    const formData = new FormData(document.getElementById('deleteCategoryForm'));
    
    fetch('<?= SUPERADMIN_URL ?>/categories/delete', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('deleteCategoryModal')).hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        showAlert('danger', 'An error occurred while deleting the category');
        console.error('Error:', error);
    });
}

function filterCategories(filter) {
    const rows = document.querySelectorAll('#categoriesTable tbody tr');
    
    rows.forEach(row => {
        const contestantCount = parseInt(row.dataset.contestantCount);
        
        switch(filter) {
            case 'all':
                row.style.display = '';
                break;
            case 'active':
                row.style.display = contestantCount > 0 ? '' : 'none';
                break;
            case 'empty':
                row.style.display = contestantCount === 0 ? '' : 'none';
                break;
        }
    });
}

function refreshCategories() {
    location.reload();
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
