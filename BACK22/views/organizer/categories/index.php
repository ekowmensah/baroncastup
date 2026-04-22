<!-- Categories Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-tags me-2"></i>
            Categories
        </h2>
        <p class="text-muted mb-0">Organize your contestants into categories</p>
    </div>
    <div>
        <button class="btn btn-primary" data-coreui-toggle="modal" data-coreui-target="#createCategoryModal">
            <i class="fas fa-plus me-2"></i>Add Category
        </button>
    </div>
</div>

<!-- Categories Table -->
<?php if (!empty($categories)): ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>All Categories
        </h5>
        <div class="d-flex gap-2">
            <div class="input-group" style="width: 300px;">
                <input type="text" class="form-control" placeholder="Search categories..." id="categorySearch">
                <button class="btn btn-outline-secondary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="categoriesTable">
                <thead class="table-light">
                    <tr>
                        <th class="border-0">Category</th>
                        <th class="border-0">Event</th>
                        <th class="border-0 text-center">Status</th>
                        <th class="border-0 text-center">Contestants</th>
                        <th class="border-0 text-center">Total Votes</th>
                        <th class="border-0 text-center">Avg Votes</th>
                        <th class="border-0 text-center">Revenue</th>
                        <th class="border-0 text-center">Participation</th>
                        <th class="border-0 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <?php 
                        // Calculate participation rate based on actual engagement
                        // Use a more realistic formula: if avg votes per contestant > 1, it's good participation
                        $avgVotes = $category['avg_votes'] ?? 0;
                        $participationRate = $category['contestant_count'] > 0 ? 
                            min(100, max(0, ($avgVotes / max(1, $category['vote_price'] ?? 1)) * 20)) : 0;
                        ?>
                        <tr>
                            <td class="py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="fas fa-tag text-white"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold"><?= htmlspecialchars($category['name']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($category['description'] ?? 'No description') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3">
                                <div class="fw-semibold"><?= htmlspecialchars($category['event_name']) ?></div>
                                <div class="small text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    Active Event
                                </div>
                            </td>
                            <td class="text-center py-3">
                                <?php 
                                // Determine real event status based on dates
                                $now = time();
                                $startTime = strtotime($category['event_start_date'] ?? '');
                                $endTime = strtotime($category['event_end_date'] ?? '');
                                $eventStatus = $category['event_status'] ?? 'draft';
                                
                                if ($eventStatus === 'draft') {
                                    $statusBadge = '<span class="badge bg-warning">Draft</span>';
                                } elseif ($eventStatus === 'completed') {
                                    $statusBadge = '<span class="badge bg-info">Completed</span>';
                                } elseif ($startTime && $endTime) {
                                    if ($now < $startTime) {
                                        $statusBadge = '<span class="badge bg-primary">Upcoming</span>';
                                    } elseif ($now > $endTime) {
                                        $statusBadge = '<span class="badge bg-secondary">Ended</span>';
                                    } else {
                                        $statusBadge = '<span class="badge bg-success">Live</span>';
                                    }
                                } else {
                                    $statusBadge = '<span class="badge bg-secondary">Unknown</span>';
                                }
                                
                                echo $statusBadge;
                                ?>
                            </td>
                            <td class="text-center py-3">
                                <div class="fw-semibold text-primary"><?= number_format($category['contestant_count']) ?></div>
                                <div class="small text-muted">contestants</div>
                            </td>
                            <td class="text-center py-3">
                                <div class="fw-semibold text-success"><?= number_format($category['total_votes']) ?></div>
                                <div class="small text-muted">total votes</div>
                            </td>
                            <td class="text-center py-3">
                                <div class="fw-semibold"><?= number_format($category['avg_votes'] ?? 0, 1) ?></div>
                                <div class="small text-muted">per contestant</div>
                            </td>
                            <td class="text-center py-3">
                                <div class="fw-semibold text-info">GH₵<?= number_format($category['revenue'], 2) ?></div>
                                <div class="small text-muted">earned</div>
                            </td>
                            <td class="text-center py-3">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="me-2">
                                        <div class="small fw-semibold"><?= number_format($participationRate, 1) ?>%</div>
                                    </div>
                                    <div style="width: 60px;">
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-primary" style="width: <?= $participationRate ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center py-3">
                                <?php 
                                // Check if event is live to disable delete
                                $now = time();
                                $startTime = strtotime($category['event_start_date'] ?? '');
                                $endTime = strtotime($category['event_end_date'] ?? '');
                                $eventStatus = $category['event_status'] ?? 'draft';
                                $isLive = ($eventStatus === 'active' && $startTime && $endTime && $now >= $startTime && $now <= $endTime);
                                ?>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewCategory(<?= $category['id'] ?>)" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="editCategory(<?= $category['id'] ?>)" title="Edit Category">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($isLive): ?>
                                        <button class="btn btn-outline-danger" disabled title="Cannot delete category while event is live">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-danger" onclick="deleteCategory(<?= $category['id'] ?>)" title="Delete Category and All Contestants">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted">
                Showing <?= count($categories) ?> categories
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-download me-1"></i>Export
                </button>
                <button class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-sync me-1"></i>Refresh
                </button>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
    <!-- Empty State -->
    <div class="card">
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <i class="fas fa-tags fa-4x text-muted opacity-50"></i>
            </div>
            <h4 class="text-muted">No Categories Yet</h4>
            <p class="text-muted mb-4">Create categories to organize your contestants and improve event management</p>
            <button class="btn btn-primary" data-coreui-toggle="modal" data-coreui-target="#createCategoryModal">
                <i class="fas fa-plus me-2"></i>Create Your First Category
            </button>
        </div>
    </div>
<?php endif; ?>

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createCategoryForm">
                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Event</label>
                        <select class="form-select" name="event_id" required>
                            <option value="">Select Event</option>
                            <?php foreach ($events as $event): ?>
                                <option value="<?= $event['id'] ?>"><?= htmlspecialchars($event['name']) ?> (<?= ucfirst($event['status']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="active" checked>
                        <label class="form-check-label">
                            Active (visible to contestants)
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveCategory()">
                    <i class="fas fa-save me-2"></i>Add Category
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editCategoryForm">
                    <input type="hidden" id="editCategoryId" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="editCategoryName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="editCategoryDescription" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="editCategoryActive" name="active">
                        <label class="form-check-label">
                            Active (visible to contestants)
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateCategory()">
                    <i class="fas fa-save me-2"></i>Update Category
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('categorySearch');
    const table = document.getElementById('categoriesTable');
    
    if (searchInput && table) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const categoryName = row.cells[0].textContent.toLowerCase();
                const eventName = row.cells[1].textContent.toLowerCase();
                const description = row.querySelector('.text-muted').textContent.toLowerCase();
                
                if (categoryName.includes(searchTerm) || 
                    eventName.includes(searchTerm) || 
                    description.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update showing count
            const visibleRows = table.querySelectorAll('tbody tr:not([style*="display: none"])');
            const countElement = document.querySelector('.card-footer .text-muted');
            if (countElement) {
                countElement.textContent = `Showing ${visibleRows.length} of ${rows.length} categories`;
            }
        });
    }
});

function viewCategory(id) {
    // Redirect to category details page
    window.location.href = `<?= ORGANIZER_URL ?>/categories/${id}`;
}

function editCategory(id) {
    // Fetch category data and populate edit modal
    fetch(`<?= ORGANIZER_URL ?>/categories/${id}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Populate edit modal with category data
            document.getElementById('editCategoryId').value = id;
            document.getElementById('editCategoryName').value = data.category.name;
            document.getElementById('editCategoryDescription').value = data.category.description || '';
            document.getElementById('editCategoryActive').checked = data.category.active == 1;
            
            // Show edit modal
            const modal = new coreui.Modal(document.getElementById('editCategoryModal'));
            modal.show();
        } else {
            alert('Failed to load category: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while loading the category');
    });
}

function deleteCategory(id) {
    if (confirm('⚠️ WARNING: This will permanently delete the category and ALL contestants in it!\n\nThis action cannot be undone. Are you sure you want to continue?')) {
        // Make AJAX request to delete
        fetch(`<?= ORGANIZER_URL ?>/categories/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`✅ ${data.message}\n${data.deleted_contestants || 0} contestants were also deleted.`);
                location.reload();
            } else {
                alert('❌ Failed to delete category: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ An error occurred while deleting the category');
        });
    }
}

function saveCategory() {
    const form = document.getElementById('createCategoryForm');
    const formData = new FormData(form);
    
    // Validate required fields
    if (!formData.get('name') || !formData.get('event_id')) {
        alert('Please fill in all required fields');
        return;
    }
    
    // Make AJAX request to save category
    fetch('<?= ORGANIZER_URL ?>/categories', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal and refresh page
            const modal = coreui.Modal.getInstance(document.getElementById('createCategoryModal'));
            modal.hide();
            
            // Show success message and reload
            alert('✅ Category created successfully!');
            location.reload();
        } else {
            alert('❌ Failed to create category: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ An error occurred while creating the category');
    });
}

function updateCategory() {
    const form = document.getElementById('editCategoryForm');
    const formData = new FormData(form);
    const categoryId = document.getElementById('editCategoryId').value;
    
    // Validate required fields
    if (!formData.get('name')) {
        alert('Please fill in the category name');
        return;
    }
    
    // Make AJAX request to update category
    fetch(`<?= ORGANIZER_URL ?>/categories/${categoryId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal and refresh page
            const modal = coreui.Modal.getInstance(document.getElementById('editCategoryModal'));
            modal.hide();
            
            // Show success message and reload
            alert('✅ Category updated successfully!');
            location.reload();
        } else {
            alert('❌ Failed to update category: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ An error occurred while updating the category');
    });
}

// Refresh button functionality
document.addEventListener('DOMContentLoaded', function() {
    const refreshBtn = document.querySelector('[title="Refresh"], .btn:has(.fa-sync)');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            location.reload();
        });
    }
});
</script>
