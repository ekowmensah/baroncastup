<?php 
$content = ob_start(); 
?>

<!-- Edit Contestant -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-edit me-2"></i>
            Edit Contestant
        </h2>
        <p class="text-muted mb-0">Update <?= htmlspecialchars($contestant['name']) ?> details</p>
    </div>
    <div>
        <a href="<?= ORGANIZER_URL ?>/contestants/<?= $contestant['id'] ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Details
        </a>
    </div>
</div>

<form method="POST" action="<?= ORGANIZER_URL ?>/contestants/<?= $contestant['id'] ?>/edit" enctype="multipart/form-data">
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
                                <label for="name" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= htmlspecialchars($contestant['name']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="event_name" class="form-label">Event</label>
                                <input type="text" class="form-control" id="event_name" 
                                       value="<?= htmlspecialchars($contestant['event_name']) ?>" readonly>
                                <div class="form-text">Event cannot be changed after creation</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea class="form-control" id="bio" name="bio" rows="4" 
                                  placeholder="Contestant biography (optional)"><?= htmlspecialchars($contestant['bio'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="photo" class="form-label">Photo</label>
                                <input type="file" class="form-control" id="photo" name="photo" accept="image/*" onchange="previewContestantImage(this)">
                                <div class="form-text">JPG, PNG, GIF, WebP (max 2MB)</div>
                                <div id="photo-preview" class="mt-2" style="display: none;">
                                    <small class="text-info">New photo preview:</small>
                                    <div class="mt-1">
                                        <img id="preview-photo" src="" alt="Preview" 
                                             style="max-width: 150px; max-height: 150px; object-fit: cover;" 
                                             class="img-thumbnail">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="active" name="active" 
                                       <?= $contestant['active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="active">
                                    Active (visible to voters)
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($contestant['image_url']): ?>
                        <div class="mb-3" id="current-photo">
                            <label class="form-label">Current Photo:</label>
                            <div>
                                <img src="<?= htmlspecialchars(image_url($contestant['image_url'])) ?>" 
                                     alt="Current photo" 
                                     class="img-thumbnail" 
                                     style="max-width: 150px; max-height: 150px; object-fit: cover;">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Category Assignment -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tags me-2"></i>
                        Category Assignment
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($all_categories)): ?>
                        <div class="mb-3">
                            <label class="form-label">Categories *</label>
                            <div class="row">
                                <?php 
                                $currentCategoryIds = array_column($current_categories, 'category_id');
                                foreach ($all_categories as $category): ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   id="category_<?= $category['id'] ?>" 
                                                   name="categories[]" 
                                                   value="<?= $category['id'] ?>"
                                                   <?= in_array($category['id'], $currentCategoryIds) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="category_<?= $category['id'] ?>">
                                                <?= htmlspecialchars($category['name']) ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="form-text">Select categories this contestant should compete in</div>
                        </div>
                        
                        <?php if (!empty($current_categories)): ?>
                            <div class="alert alert-info">
                                <h6 class="alert-heading">Current Shortcodes:</h6>
                                <?php foreach ($current_categories as $catAssignment): ?>
                                    <span class="badge bg-primary me-2 mb-1">
                                        <?= htmlspecialchars($catAssignment['category_name']) ?>: 
                                        <strong><?= htmlspecialchars($catAssignment['short_code']) ?></strong>
                                    </span>
                                <?php endforeach; ?>
                                <div class="mt-2">
                                    <small>New shortcodes will be generated for newly assigned categories</small>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No categories available for this event. Please add categories to the event first.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Current Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-eye me-2"></i>
                        Current Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Event:</strong><br>
                        <span class="text-muted"><?= htmlspecialchars($contestant['event_name']) ?></span>
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong><br>
                        <?php if ($contestant['active']): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <strong>Categories:</strong><br>
                        <?php if (!empty($current_categories)): ?>
                            <?php foreach ($current_categories as $catAssignment): ?>
                                <span class="badge bg-primary me-1 mb-1"><?= htmlspecialchars($catAssignment['category_name']) ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-muted">None assigned</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Help -->
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-lightbulb me-2"></i>
                        Editing Tips
                    </h6>
                    <ul class="small mb-0">
                        <li>Upload a new photo to replace the current one</li>
                        <li>Changing categories will generate new shortcodes</li>
                        <li>Inactive contestants won't appear in voting</li>
                        <li>Bio supports line breaks for formatting</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Buttons -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-between">
                <a href="<?= ORGANIZER_URL ?>/contestants/<?= $contestant['id'] ?>" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Update Contestant
                </button>
            </div>
        </div>
    </div>
</form>

<script>
// Contestant photo preview
function previewContestantImage(input) {
    const previewDiv = document.getElementById('photo-preview');
    const previewImg = document.getElementById('preview-photo');
    const currentPhotoDiv = document.getElementById('current-photo');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file size (2MB max for contestants)
        if (file.size > 2 * 1024 * 1024) {
            alert('Photo must be less than 2MB');
            input.value = '';
            previewDiv.style.display = 'none';
            if (currentPhotoDiv) {
                currentPhotoDiv.style.opacity = '1';
            }
            return;
        }
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file');
            input.value = '';
            previewDiv.style.display = 'none';
            if (currentPhotoDiv) {
                currentPhotoDiv.style.opacity = '1';
            }
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewDiv.style.display = 'block';
            if (currentPhotoDiv) {
                currentPhotoDiv.style.opacity = '0.5';
            }
        };
        reader.readAsDataURL(file);
    } else {
        previewDiv.style.display = 'none';
        if (currentPhotoDiv) {
            currentPhotoDiv.style.opacity = '1';
        }
    }
}
</script>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../../layout/organizer_layout.php';
?>
