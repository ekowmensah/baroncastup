<!-- Edit Event Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-edit me-2"></i>
            Edit Event
        </h2>
        <p class="text-muted mb-0">Update your event details and settings</p>
    </div>
    <div>
        <a href="<?= ORGANIZER_URL ?>/events/<?= $event['id'] ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Event
        </a>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <form method="POST" action="<?= ORGANIZER_URL ?>/events/<?= $event['id'] ?>/edit" enctype="multipart/form-data">
            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Event Name *</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($event['name']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="code" class="form-label">Event Code *</label>
                                <input type="text" class="form-control" id="code" name="code" value="<?= htmlspecialchars($event['code']) ?>" required>
                                <div class="form-text">Unique identifier for this event</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="featured_image" class="form-label">Featured Image</label>
                        <?php if (!empty($event['featured_image'])): ?>
                            <div class="mb-2" id="current-image">
                                <img src="<?= htmlspecialchars(image_url($event['featured_image'])) ?>" alt="Current featured image" class="img-thumbnail" style="max-height: 100px;">
                                <div class="small text-muted">Current image</div>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*" onchange="previewEventImage(this)">
                        <div class="form-text">Upload a new banner image to replace the current one (JPG, PNG, max 5MB)</div>
                        <div id="image-preview" class="mt-2" style="display: none;">
                            <small class="text-info">New image preview:</small>
                            <div class="mt-1">
                                <img id="preview-img" src="" alt="Preview" 
                                     style="max-width: 200px; max-height: 100px; object-fit: cover;" 
                                     class="img-thumbnail">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Event Schedule -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Event Schedule</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Start Date & Time *</label>
                                <input type="datetime-local" class="form-control" id="start_date" name="start_date" 
                                       value="<?= date('Y-m-d\TH:i', strtotime($event['start_date'])) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">End Date & Time *</label>
                                <input type="datetime-local" class="form-control" id="end_date" name="end_date" 
                                       value="<?= date('Y-m-d\TH:i', strtotime($event['end_date'])) ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="timezone" class="form-label">Timezone</label>
                        <select class="form-select" id="timezone" name="timezone">
                            <option value="UTC" <?= ($event['timezone'] ?? '') === 'UTC' ? 'selected' : '' ?>>UTC</option>
                            <option value="America/New_York" <?= ($event['timezone'] ?? '') === 'America/New_York' ? 'selected' : '' ?>>Eastern Time</option>
                            <option value="America/Chicago" <?= ($event['timezone'] ?? '') === 'America/Chicago' ? 'selected' : '' ?>>Central Time</option>
                            <option value="America/Denver" <?= ($event['timezone'] ?? '') === 'America/Denver' ? 'selected' : '' ?>>Mountain Time</option>
                            <option value="America/Los_Angeles" <?= ($event['timezone'] ?? '') === 'America/Los_Angeles' ? 'selected' : '' ?>>Pacific Time</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Event Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Event Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="draft" <?= $event['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                    <option value="active" <?= $event['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="completed" <?= $event['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= $event['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="visibility" class="form-label">Visibility</label>
                                <select class="form-select" id="visibility" name="visibility">
                                    <option value="public" <?= ($event['visibility'] ?? 'public') === 'public' ? 'selected' : '' ?>>Public</option>
                                    <option value="private" <?= ($event['visibility'] ?? '') === 'private' ? 'selected' : '' ?>>Private</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vote_price" class="form-label">Vote Price ($)</label>
                                <input type="number" class="form-control" id="vote_price" name="vote_price" 
                                       value="<?= $event['vote_price'] ?? '0.50' ?>" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_votes_per_user" class="form-label">Max Votes per User</label>
                                <input type="number" class="form-control" id="max_votes_per_user" name="max_votes_per_user" 
                                       value="<?= $event['max_votes_per_user'] ?? '' ?>" min="1">
                                <div class="form-text">Leave empty for unlimited</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="allow_multiple_votes" name="allow_multiple_votes" 
                               <?= ($event['allow_multiple_votes'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="allow_multiple_votes">
                            Allow multiple votes per contestant
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Event
                            </button>
                            <a href="<?= ORGANIZER_URL ?>/events/<?= $event['id'] ?>" class="btn btn-outline-secondary ms-2">
                                Cancel
                            </a>
                        </div>
                        <div>
                            <?php if ($event['status'] === 'draft'): ?>
                                <button type="button" class="btn btn-success" onclick="publishEvent()">
                                    <i class="fas fa-rocket me-2"></i>Publish Event
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Event Status -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Event Status</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-<?= $event['status'] === 'active' ? 'success' : ($event['status'] === 'draft' ? 'warning' : 'secondary') ?> me-2">
                        <?= ucfirst($event['status']) ?>
                    </span>
                    <span class="text-muted small">Current status</span>
                </div>
                
                <div class="small text-muted">
                    <div><strong>Created:</strong> <?= date('M j, Y g:i A', strtotime($event['created_at'])) ?></div>
                    <div><strong>Last Updated:</strong> <?= date('M j, Y g:i A', strtotime($event['updated_at'])) ?></div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= ORGANIZER_URL ?>/events/<?= $event['id'] ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye me-2"></i>View Event
                    </a>
                    <a href="<?= ORGANIZER_URL ?>/contestants?event=<?= $event['id'] ?>" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-users me-2"></i>Manage Contestants
                    </a>
                    <a href="<?= ORGANIZER_URL ?>/voting/live?event=<?= $event['id'] ?>" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-chart-line me-2"></i>Live Results
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function publishEvent() {
    if (confirm('Are you sure you want to publish this event? Once published, it will be visible to voters.')) {
        // Submit form to publish endpoint
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= ORGANIZER_URL ?>/events/<?= $event['id'] ?>/publish';
        document.body.appendChild(form);
        form.submit();
    }
}

// Event image preview
function previewEventImage(input) {
    const previewDiv = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    const currentImageDiv = document.getElementById('current-image');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            alert('Image must be less than 5MB');
            input.value = '';
            previewDiv.style.display = 'none';
            if (currentImageDiv) {
                currentImageDiv.style.opacity = '1';
            }
            return;
        }
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file');
            input.value = '';
            previewDiv.style.display = 'none';
            if (currentImageDiv) {
                currentImageDiv.style.opacity = '1';
            }
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewDiv.style.display = 'block';
            if (currentImageDiv) {
                currentImageDiv.style.opacity = '0.5';
            }
        };
        reader.readAsDataURL(file);
    } else {
        previewDiv.style.display = 'none';
        if (currentImageDiv) {
            currentImageDiv.style.opacity = '1';
        }
    }
}
</script>
