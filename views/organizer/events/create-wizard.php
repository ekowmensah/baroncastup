<!-- Multi-Step Event Creation Wizard -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Plan Limits Alert -->
            <?php 
            $currentEventCount = isset($tenantLimits['current_events']) ? $tenantLimits['current_events'] : 0;
            $maxEvents = isset($tenantLimits['max_events']) ? $tenantLimits['max_events'] : null;
            $canCreateEvent = is_null($maxEvents) || $currentEventCount < $maxEvents;
            $isAtLimit = !is_null($maxEvents) && $currentEventCount >= $maxEvents;
            
            // Check if we're editing a draft event and at limit
            $isDraftAtLimit = ($isEditing ?? false) && 
                             ($currentEventStatus ?? '') === 'draft' && 
                             !$canCreateEvent;
            ?>
            
            <?php if (!$canCreateEvent && !($isEditing ?? false)): ?>
            <div class="alert alert-danger mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading mb-1">Event Limit Reached</h5>
                        <p class="mb-2">You have reached your plan limit of <strong><?= $maxEvents ?> published events</strong>. You currently have <strong><?= $currentEventCount ?> published events</strong>.</p>
                        <p class="mb-2"><small class="text-muted"><i class="fas fa-info-circle me-1"></i>Note: Draft events don't count toward your limit. You can still save events as drafts.</small></p>
                        <a href="<?= ORGANIZER_URL ?>/switch-plan" class="btn btn-warning btn-sm me-2">
                            <i class="fas fa-arrow-up me-2"></i>Upgrade Plan
                        </a>
                        <a href="<?= ORGANIZER_URL ?>/events" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i>Back to Events
                        </a>
                    </div>
                </div>
            </div>
            <?php elseif ($isDraftAtLimit): ?>
            <div class="alert alert-warning mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading mb-1">Cannot Publish Draft Event</h5>
                        <p class="mb-2">You have reached your plan limit of <strong><?= $maxEvents ?> published events</strong>. This draft event cannot be published until you upgrade your plan.</p>
                        <p class="mb-2"><small class="text-muted"><i class="fas fa-info-circle me-1"></i>You can continue editing and saving as draft, but publishing requires a plan upgrade.</small></p>
                        <a href="<?= ORGANIZER_URL ?>/switch-plan" class="btn btn-warning btn-sm me-2">
                            <i class="fas fa-arrow-up me-2"></i>Upgrade Plan
                        </a>
                    </div>
                </div>
            </div>
            <?php elseif (!is_null($maxEvents)): ?>
            <div class="alert alert-info mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle me-3"></i>
                    <div class="flex-grow-1">
                        <strong>Plan Usage:</strong> <?= $currentEventCount ?> of <?= $maxEvents ?> published events used
                        <?php if ($currentEventCount >= $maxEvents - 1): ?>
                            <span class="text-warning ms-2">
                                <i class="fas fa-exclamation-triangle"></i> Almost at limit!
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if ($currentEventCount >= $maxEvents - 2): ?>
                    <a href="<?= ORGANIZER_URL ?>/switch-plan" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-arrow-up me-2"></i>Upgrade
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Progress Steps -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">
                            <i class="fas fa-magic me-2"></i>
                            Create New Event
                        </h4>
                        <a href="<?= ORGANIZER_URL ?>/events" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 25%" id="progressBar"></div>
                    </div>
                    
                    <!-- Step Indicators -->
                    <div class="d-flex justify-content-between">
                        <div class="step-indicator active" data-step="1">
                            <div class="step-circle">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="step-label">Event Details</div>
                        </div>
                        <div class="step-indicator" data-step="2">
                            <div class="step-circle">
                                <i class="fas fa-tags"></i>
                            </div>
                            <div class="step-label">Categories</div>
                        </div>
                        <div class="step-indicator" data-step="3">
                            <div class="step-circle">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="step-label">Nominees</div>
                        </div>
                        <div class="step-indicator" data-step="4">
                            <div class="step-circle">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="step-label">Preview</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Step Content -->
            <form id="eventWizardForm" method="POST" action="<?= ORGANIZER_URL ?>/events/wizard" enctype="multipart/form-data">
                <?php if ($isEditing ?? false): ?>
                    <input type="hidden" name="edit_event_id" value="<?= $editEventId ?>">
                <?php endif; ?>
                
                <!-- Step 1: Event Details -->
                <div class="step-content active" id="step1">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Event Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Event Name *</label>
                                                <input type="text" class="form-control" id="name" name="name" 
                                                       value="<?= htmlspecialchars($eventData['name'] ?? '') ?>" required>
                                                <div class="form-text">Choose a memorable name for your event</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="code" class="form-label">Event Code *</label>
                                                <input type="text" class="form-control" id="code" name="code" 
                                                       value="<?= htmlspecialchars($eventData['code'] ?? '') ?>" required>
                                                <div class="form-text">Unique identifier (auto-generated if empty)</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Describe your voting event..."><?= htmlspecialchars($eventData['description'] ?? '') ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="featured_image" class="form-label">Featured Image</label>
                                        <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*" onchange="previewEventImage(this)">
                                        <div class="form-text">Upload a banner image (JPG, PNG, max 5MB)</div>
                                        <?php if (!empty($eventData['featured_image'])): ?>
                                            <div class="mt-2" id="current-image">
                                                <small class="text-success">✓ Current image: <?= basename($eventData['featured_image']) ?></small>
                                                <div class="mt-1">
                                                    <img src="<?= htmlspecialchars(image_url($eventData['featured_image'])) ?>" 
                                                         alt="Current featured image" 
                                                         style="max-width: 200px; max-height: 100px; object-fit: cover;" 
                                                         class="img-thumbnail">
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div id="image-preview" class="mt-2" style="display: none;">
                                            <small class="text-info">New image preview:</small>
                                            <div class="mt-1">
                                                <img id="preview-img" src="" alt="Preview" 
                                                     style="max-width: 200px; max-height: 100px; object-fit: cover;" 
                                                     class="img-thumbnail">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="start_date" class="form-label">Start Date & Time *</label>
                                                <input type="datetime-local" class="form-control" id="start_date" name="start_date" 
                                                       value="<?= isset($eventData['start_date']) ? date('Y-m-d\TH:i', strtotime($eventData['start_date'])) : '' ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="end_date" class="form-label">End Date & Time *</label>
                                                <input type="datetime-local" class="form-control" id="end_date" name="end_date" 
                                                       value="<?= isset($eventData['end_date']) ? date('Y-m-d\TH:i', strtotime($eventData['end_date'])) : '' ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="vote_price" class="form-label">Vote Price (GH₵)</label>
                                                <input type="number" class="form-control" id="vote_price" name="vote_price" 
                                                       value="<?= htmlspecialchars($eventData['vote_price'] ?? '0.50') ?>" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="visibility" class="form-label">Visibility</label>
                                                <select class="form-select" id="visibility" name="visibility">
                                                    <option value="public" <?= ($eventData['visibility'] ?? 'public') === 'public' ? 'selected' : '' ?>>Public</option>
                                                    <option value="private" <?= ($eventData['visibility'] ?? 'public') === 'private' ? 'selected' : '' ?>>Private</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-lightbulb me-2"></i>
                                        Tips for Success
                                    </h6>
                                    <ul class="small mb-0">
                                        <li>Choose a clear, descriptive event name</li>
                                        <li>Set realistic start and end dates</li>
                                        <li>Upload an eye-catching banner image</li>
                                        <li>Consider your target audience for pricing</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Step 2: Categories -->
                <div class="step-content" id="step2" style="display: none;">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-tags me-2"></i>
                                        Event Categories
                                    </h5>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="addCategory()">
                                        <i class="fas fa-plus me-2"></i>Add Category
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="text-center text-muted py-4" id="noCategoriesMessage">
                                        <i class="fas fa-tags fa-2x mb-2"></i>
                                        <p>No categories added yet. Click "Add Category" to get started.</p>
                                    </div>
                                    <div id="categoriesContainer">
                                        <!-- Categories will be added here dynamically -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-info-circle me-2"></i>
                                        About Categories
                                    </h6>
                                    <p class="small mb-3">Categories help organize your nominees. Examples:</p>
                                    <ul class="small mb-0">
                                        <li><strong>Music Awards:</strong> Best Artist, Best Song, Best Album</li>
                                        <li><strong>Beauty Contest:</strong> Miss Photogenic, Best Talent</li>
                                        <li><strong>Sports:</strong> MVP, Best Goal, Fan Favorite</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Step 3: Nominees -->
                <div class="step-content" id="step3" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0">
                                    <i class="fas fa-users me-2"></i>
                                    Event Nominees
                                </h5>
                                <button type="button" class="btn btn-primary btn-sm" onclick="addNominee()">
                                    <i class="fas fa-plus me-2"></i>Add Nominee
                                </button>
                            </div>
                            <div class="alert alert-info mb-0 py-2 small">
                                <i class="fas fa-info-circle me-2"></i>
                                Each nominee gets unique shortcodes per category for USSD voting. Shortcodes are auto-generated.
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="text-center text-muted py-4" id="noNomineesMessage">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <p>No nominees added yet. Click "Add Nominee" to get started.</p>
                            </div>
                            <div id="nomineesContainer">
                                <!-- Nominees will be added here dynamically -->
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Step 4: Preview -->
                <div class="step-content" id="step4" style="display: none;">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-eye me-2"></i>
                                        Event Preview
                                    </h5>
                                    <p class="text-muted mb-0">Review your event details before creating</p>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Event Details Preview -->
                                        <div class="col-md-6">
                                            <div class="card bg-light mb-3">
                                                <div class="card-header">
                                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Event Information</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row mb-2">
                                                        <div class="col-sm-4"><strong>Name:</strong></div>
                                                        <div class="col-sm-8" id="preview-name">-</div>
                                                    </div>
                                                    <div class="row mb-2">
                                                        <div class="col-sm-4"><strong>Code:</strong></div>
                                                        <div class="col-sm-8" id="preview-code">-</div>
                                                    </div>
                                                    <div class="row mb-2">
                                                        <div class="col-sm-4"><strong>Description:</strong></div>
                                                        <div class="col-sm-8" id="preview-description">-</div>
                                                    </div>
                                                    <div class="row mb-2">
                                                        <div class="col-sm-4"><strong>Start Date:</strong></div>
                                                        <div class="col-sm-8" id="preview-start-date">-</div>
                                                    </div>
                                                    <div class="row mb-2">
                                                        <div class="col-sm-4"><strong>End Date:</strong></div>
                                                        <div class="col-sm-8" id="preview-end-date">-</div>
                                                    </div>
                                                    <div class="row mb-2">
                                                        <div class="col-sm-4"><strong>Vote Price:</strong></div>
                                                        <div class="col-sm-8" id="preview-vote-price">-</div>
                                                    </div>
                                                    <div class="row mb-2">
                                                        <div class="col-sm-4"><strong>Visibility:</strong></div>
                                                        <div class="col-sm-8" id="preview-visibility">-</div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-4"><strong>Featured Image:</strong></div>
                                                        <div class="col-sm-8" id="preview-image">
                                                            <div id="preview-image-container" style="display: none;">
                                                                <img id="preview-image-display" src="" alt="Featured Image" 
                                                                     style="max-width: 150px; max-height: 100px; object-fit: cover;" 
                                                                     class="img-thumbnail">
                                                            </div>
                                                            <span id="preview-no-image" class="text-muted">No image uploaded</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Categories and Nominees Preview -->
                                        <div class="col-md-6">
                                            <div class="card bg-light mb-3">
                                                <div class="card-header">
                                                    <h6 class="mb-0"><i class="fas fa-tags me-2"></i>Categories</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div id="preview-categories">
                                                        <p class="text-muted">No categories added</p>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="card bg-light">
                                                <div class="card-header">
                                                    <h6 class="mb-0"><i class="fas fa-users me-2"></i>Nominees</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div id="preview-nominees">
                                                        <p class="text-muted">No nominees added</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Summary Stats -->
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="alert alert-info">
                                                <h6 class="alert-heading"><i class="fas fa-chart-bar me-2"></i>Event Summary</h6>
                                                <div class="row">
                                                    <div class="col-md-3 text-center">
                                                        <div class="h4 mb-0" id="preview-category-count">0</div>
                                                        <small class="text-muted">Categories</small>
                                                    </div>
                                                    <div class="col-md-3 text-center">
                                                        <div class="h4 mb-0" id="preview-nominee-count">0</div>
                                                        <small class="text-muted">Nominees</small>
                                                    </div>
                                                    <div class="col-md-3 text-center">
                                                        <div class="h4 mb-0" id="preview-duration">0</div>
                                                        <small class="text-muted">Days Duration</small>
                                                    </div>
                                                    <div class="col-md-3 text-center">
                                                        <div class="h4 mb-0" id="preview-estimated-revenue">GH₵0</div>
                                                        <small class="text-muted">Est. Revenue (100 votes)</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation Buttons -->
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" id="prevBtn" onclick="changeStep(-1)" style="display: none;">
                                <i class="fas fa-arrow-left me-2"></i>Previous
                            </button>
                            <div></div>
                            <div>
                                <button type="button" class="btn btn-primary" id="nextBtn" onclick="changeStep(1)">
                                    Next <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                                <?php if ((!$canCreateEvent && !($isEditing ?? false)) || $isDraftAtLimit): ?>
                                    <a href="<?= ORGANIZER_URL ?>/switch-plan" class="btn btn-warning" id="upgradeBtn" style="display: none;" 
                                       title="Upgrade plan to publish events">
                                        <i class="fas fa-crown me-2"></i>Upgrade to Publish
                                    </a>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;" name="action" value="publish">
                                        <i class="fas fa-check me-2"></i><?= ($isEditing ?? false) ? 'Publish Event' : 'Create Event' ?>
                                    </button>
                                <?php endif; ?>
                                <button type="submit" class="btn btn-outline-primary" id="draftBtn" name="action" value="draft">
                                    <i class="fas fa-save me-2"></i>Save Draft
                                    <?php if (!$canCreateEvent && !($isEditing ?? false)): ?>
                                        <small class="d-block">Drafts don't count toward limit</small>
                                    <?php endif; ?>
                                </button>
                                <!-- Debug: Simple submit button that's always visible -->
                            <!--    <button type="submit" class="btn btn-warning" onclick="return confirm('Submit form with current data?')" name="action" value="debug">
                                    <i class="fas fa-bug me-2"></i>Debug Submit
                                </button> -->
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">
                    <i class="fas fa-tags me-2"></i>Add Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="categoryName" class="form-label">Category Name *</label>
                    <input type="text" class="form-control" id="categoryName" placeholder="e.g., Best Actor, Best Actress" required>
                    <div class="form-text">Enter a descriptive name for this category</div>
                </div>
                <div class="mb-3">
                    <label for="categoryDescription" class="form-label">Description (Optional)</label>
                    <textarea class="form-control" id="categoryDescription" rows="3" placeholder="Brief description of this category"></textarea>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="addNomineesNow">
                    <label class="form-check-label" for="addNomineesNow">
                        <i class="fas fa-user-plus me-1"></i>Add nominees for this category now
                    </label>
                    <div class="form-text">After saving, you'll be prompted to add nominees directly to this category</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="saveCategoryFromModal()">
                    <i class="fas fa-plus me-2"></i>Add Category
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Nominee Modal -->
<div class="modal fade" id="addNomineeModal" tabindex="-1" aria-labelledby="addNomineeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addNomineeModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Add Nominee
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Existing Nominees Section -->
                <div class="mb-4" id="existingNomineesSection" style="display: none;">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Select existing nominees</strong> to assign them to the selected categories, or create a new nominee below.
                    </div>
                    <label class="form-label fw-bold">Select Existing Nominees</label>
                    <input type="text" class="form-control mb-2" id="existingNomineeSearch" placeholder="Search nominees..." onkeyup="filterExistingNominees(this.value)">
                    <div id="existingNomineesCheckboxList" style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; background: #f8f9fa;">
                        <!-- Existing nominees will be populated here -->
                    </div>
                    <div class="form-text mb-3">
                        <span id="selectedExistingNomineesCount">0</span> nominees selected
                    </div>
                    <div class="text-center my-3">
                        <span class="badge bg-secondary">OR</span>
                    </div>
                </div>
                
                <!-- Create New Nominee Section -->
                <div id="createNewNomineeSection">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="form-label fw-bold mb-0">Create New Nominee</label>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="toggleNewNomineeBtn" onclick="toggleNewNomineeForm()" style="display: none;">
                            <i class="fas fa-plus me-1"></i>Create New
                        </button>
                    </div>
                    <div id="newNomineeForm" style="display: none;">
                        <div class="mb-3">
                            <label for="nomineeName" class="form-label">Nominee Name *</label>
                            <input type="text" class="form-control" id="nomineeName" placeholder="Enter nominee's full name">
                        </div>
                <div class="mb-3">
                    <label for="nomineeBio" class="form-label">Bio (Optional)</label>
                    <textarea class="form-control" id="nomineeBio" rows="3" placeholder="Brief biography or description"></textarea>
                </div>
                        <div class="mb-3">
                            <label for="nomineePhoto" class="form-label">Photo (Optional)</label>
                            <input type="file" class="form-control" id="nomineePhoto" accept="image/*">
                            <div class="form-text">JPG, PNG (max 2MB)</div>
                        </div>
                    </div>
                    
                    <!-- Categories Section - Always Visible -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold mb-0">Assign to Categories *</label>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="quickAddCategory()">
                                <i class="fas fa-plus me-1"></i>New Category
                            </button>
                        </div>
                        <input type="text" class="form-control mb-2" id="categorySearch" placeholder="Search categories...">
                        <div id="categoriesCheckboxList" style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px;">
                            <!-- Categories will be populated dynamically as checkboxes -->
                        </div>
                        <div class="form-text">
                            <span id="selectedCategoriesCount">0</span> categories selected
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="saveNomineeFromModal()">
                    <i class="fas fa-user-plus me-2"></i><span id="addNomineeButtonText">Add Nominee</span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.step-indicator {
    text-align: center;
    flex: 1;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8px;
    transition: all 0.3s ease;
}

.step-indicator.active .step-circle {
    background: #0d6efd;
    color: white;
}

.step-indicator.completed .step-circle {
    background: #198754;
    color: white;
}

.step-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6c757d;
}

.step-indicator.active .step-label {
    color: #0d6efd;
}

.step-indicator.completed .step-label {
    color: #198754;
}

/* Photo hover effect */
.photo-wrapper:hover .photo-overlay {
    opacity: 1 !important;
}

.nominee-photo-container .photo-wrapper {
    transition: transform 0.2s;
}

.nominee-photo-container .photo-wrapper:hover {
    transform: scale(1.05);
}

.category-item, .nominee-item {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1rem;
    background: #f8f9fa;
}

.category-item:hover, .nominee-item:hover {
    border-color: #0d6efd;
    background: #fff;
}
/* Drag and drop styles */
.drag-handle {
    cursor: move;
    user-select: none;
}

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

.category-item,
.nominee-item {
    transition: all 0.2s ease;
}

.category-item:hover,
.nominee-item:hover {
    background: #f8f9fa;
}

#nomineesContainer {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

#categoriesContainer {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
</style>

<script>
let currentStep = 1;
let categories = [];
let nominees = [];
let categorySortable = null;
let nomineesSortable = null;

// Step navigation
function changeStep(direction) {
    if (direction === 1 && !validateCurrentStep()) {
        return;
    }
    
    const newStep = currentStep + direction;
    if (newStep < 1 || newStep > 4) return;
    
    // Hide current step
    document.getElementById(`step${currentStep}`).style.display = 'none';
    document.querySelector(`[data-step="${currentStep}"]`).classList.remove('active');
    
    // Show new step
    currentStep = newStep;
    document.getElementById(`step${currentStep}`).style.display = 'block';
    document.querySelector(`[data-step="${currentStep}"]`).classList.add('active');
    
    // Update progress bar
    const progress = (currentStep / 4) * 100;
    document.getElementById('progressBar').style.width = progress + '%';
    
    // Update navigation buttons
    document.getElementById('prevBtn').style.display = currentStep === 1 ? 'none' : 'inline-block';
    document.getElementById('nextBtn').style.display = currentStep === 4 ? 'none' : 'inline-block';
    document.getElementById('submitBtn').style.display = currentStep === 4 ? 'inline-block' : 'none';
    
    // Mark completed steps
    for (let i = 1; i < currentStep; i++) {
        document.querySelector(`[data-step="${i}"]`).classList.add('completed');
    }
    
    // If moving to preview step, populate preview data
    if (currentStep === 4) {
        populatePreview();
    }
}

// Validate current step
function validateCurrentStep() {
    if (currentStep === 1) {
        const name = document.getElementById('name').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (!name || !startDate || !endDate) {
            alert('Please fill in all required fields');
            return false;
        }
    } else if (currentStep === 2) {
        if (categories.length === 0) {
            alert('Please add at least one category');
            return false;
        }
    } else if (currentStep === 3) {
        if (nominees.length === 0) {
            alert('Please add at least one nominee');
            return false;
        }
        // Check that each nominee has at least one category
        for (let nominee of nominees) {
            if (!nominee.categories || nominee.categories.length === 0) {
                alert(`Please assign at least one category to "${nominee.name}"`);
                return false;
            }
        }
    }
    return true;
}

// Populate preview step with current data
function populatePreview() {
    // Event details
    document.getElementById('preview-name').textContent = document.getElementById('name').value || '-';
    document.getElementById('preview-code').textContent = document.getElementById('code').value || '-';
    document.getElementById('preview-description').textContent = document.getElementById('description').value || 'No description';
    
    // Format dates
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    document.getElementById('preview-start-date').textContent = startDate ? new Date(startDate).toLocaleString() : '-';
    document.getElementById('preview-end-date').textContent = endDate ? new Date(endDate).toLocaleString() : '-';
    
    // Vote price
    const votePrice = document.getElementById('vote_price').value || '0';
    document.getElementById('preview-vote-price').textContent = '$' + parseFloat(votePrice).toFixed(2);
    
    // Visibility
    const visibility = document.getElementById('visibility').value;
    document.getElementById('preview-visibility').textContent = visibility.charAt(0).toUpperCase() + visibility.slice(1);
    
    // Featured image
    const imageInput = document.getElementById('featured_image');
    const previewImageContainer = document.getElementById('preview-image-container');
    const previewImageDisplay = document.getElementById('preview-image-display');
    const previewNoImage = document.getElementById('preview-no-image');
    
    if (imageInput.files && imageInput.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImageDisplay.src = e.target.result;
            previewImageContainer.style.display = 'block';
            previewNoImage.style.display = 'none';
        };
        reader.readAsDataURL(imageInput.files[0]);
    } else {
        previewImageContainer.style.display = 'none';
        previewNoImage.style.display = 'inline';
    }
    
    // Categories
    const categoriesContainer = document.getElementById('preview-categories');
    if (categories.length > 0) {
        const categoriesHtml = categories.map(cat => `
            <div class="badge bg-primary me-2 mb-2">${cat.name}</div>
        `).join('');
        categoriesContainer.innerHTML = categoriesHtml;
    } else {
        categoriesContainer.innerHTML = '<p class="text-muted">No categories added</p>';
    }
    
    // Nominees
    const nomineesContainer = document.getElementById('preview-nominees');
    if (nominees.length > 0) {
        const nomineesHtml = nominees.map(nominee => {
            const assignedCategories = nominee.categories ? 
                nominee.categories.map(catId => {
                    const cat = categories.find(c => c.id == catId);
                    return cat ? cat.name : 'Unknown';
                }).join(', ') : 'No categories';
            
            return `
                <div class="border rounded p-2 mb-2">
                    <strong>${nominee.name}</strong>
                    <br><small class="text-muted">Categories: ${assignedCategories}</small>
                    ${nominee.bio ? `<br><small>${nominee.bio}</small>` : ''}
                </div>
            `;
        }).join('');
        nomineesContainer.innerHTML = nomineesHtml;
    } else {
        nomineesContainer.innerHTML = '<p class="text-muted">No nominees added</p>';
    }
    
    // Summary stats
    document.getElementById('preview-category-count').textContent = categories.length;
    document.getElementById('preview-nominee-count').textContent = nominees.length;
    
    // Calculate duration
    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        document.getElementById('preview-duration').textContent = diffDays;
    } else {
        document.getElementById('preview-duration').textContent = '0';
    }
    
    // Estimated revenue (100 votes * vote price)
    const estimatedRevenue = (100 * parseFloat(votePrice)).toFixed(2);
    document.getElementById('preview-estimated-revenue').textContent = '$' + estimatedRevenue;
}

// Category management
function addCategory() {
    // Clear previous inputs
    document.getElementById('categoryName').value = '';
    document.getElementById('categoryDescription').value = '';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
    modal.show();
    
    // Focus on name input after modal is shown
    document.getElementById('addCategoryModal').addEventListener('shown.bs.modal', function () {
        document.getElementById('categoryName').focus();
    }, { once: true });
}

// Quick add category from nominee modal
function quickAddCategory() {
    const categoryName = prompt('Enter category name:');
    if (!categoryName || !categoryName.trim()) {
        return;
    }
    
    const category = {
        id: Date.now(),
        name: categoryName.trim(),
        description: ''
    };
    
    categories.push(category);
    
    // Render categories to create form inputs (so it saves to DB)
    renderCategories();
    
    // Re-populate the categories checkboxes in the modal
    populateCategoryCheckboxes();
    
    // Auto-select the new category
    setTimeout(() => {
        const newCheckbox = document.querySelector(`.category-checkbox[value="${category.id}"]`);
        if (newCheckbox) {
            newCheckbox.checked = true;
            updateSelectedCategoriesCount();
        }
    }, 100);
    
    showToast(`Category "${categoryName}" added!`, 'success');
}

function saveCategoryFromModal() {
    const categoryName = document.getElementById('categoryName').value.trim();
    const categoryDescription = document.getElementById('categoryDescription').value.trim();
    const addNomineesNow = document.getElementById('addNomineesNow').checked;
    
    if (!categoryName) {
        alert('Please enter a category name');
        document.getElementById('categoryName').focus();
        return;
    }
    
    const category = {
        id: Date.now(),
        name: categoryName,
        description: categoryDescription
    };
    
    categories.push(category);
    renderCategories();
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('addCategoryModal'));
    modal.hide();
    
    // Reset form
    document.getElementById('categoryName').value = '';
    document.getElementById('categoryDescription').value = '';
    document.getElementById('addNomineesNow').checked = false;
    
    // Show success message
    showToast('Category added successfully!', 'success');
    
    // If user wants to add nominees now, open Add Nominee modal with this category pre-selected
    if (addNomineesNow) {
        setTimeout(() => {
            addNomineeForCategory(category.id);
        }, 500); // Small delay for modal transition
    }
}

function removeCategory(categoryId) {
    // Convert to string for comparison (database IDs are strings)
    const idToRemove = String(categoryId);
    categories = categories.filter(c => String(c.id) !== idToRemove);
    console.log('Removed category:', idToRemove, 'Remaining:', categories);
    renderCategories();
}

function renderCategories() {
    const container = document.getElementById('categoriesContainer');
    const noMessage = document.getElementById('noCategoriesMessage');
    
    // Check if elements exist
    if (!container || !noMessage) {
        console.error('Required elements not found');
        return;
    }
    
    if (categories.length === 0) {
        noMessage.style.display = 'block';
        container.innerHTML = '';
        return;
    }
    
    noMessage.style.display = 'none';
    
    const html = categories.map((category, index) => `
        <div class="category-item" data-category-id="${category.id}">
            <div class="d-flex align-items-start gap-2">
                <div class="drag-handle" style="cursor: move; padding: 8px; color: #6c757d;">
                    <i class="fas fa-grip-vertical"></i>
                </div>
                <div class="flex-grow-1">
                    <input type="text" 
                           class="form-control form-control-sm mb-2" 
                           name="categories[${category.id}][name]" 
                           value="${category.name}"
                           placeholder="Category name"
                           onchange="updateCategoryName(${category.id}, this.value)"
                           required>
                    <input type="hidden" name="categories[${category.id}][order]" value="${index}">
                    <textarea class="form-control form-control-sm mb-2" 
                              name="categories[${category.id}][description]" 
                              placeholder="Category description (optional)" 
                              rows="2" 
                              onchange="updateCategoryDescription(${category.id}, this.value)">${category.description}</textarea>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addNomineeForCategory(${category.id})">
                        <i class="fas fa-user-plus me-1"></i>Add Nominees
                    </button>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeCategory(${category.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = html;
    
    // Initialize sortable if not already initialized
    if (!categorySortable && typeof Sortable !== 'undefined') {
        initializeCategorySortable();
    }
}

function updateCategoryName(categoryId, name) {
    const category = categories.find(c => String(c.id) === String(categoryId));
    if (category) {
        category.name = name;
    }
}

function updateCategoryDescription(categoryId, description) {
    const category = categories.find(c => String(c.id) === String(categoryId));
    if (category) {
        category.description = description;
    }
}

function updateNomineeName(nomineeId, name) {
    const nominee = nominees.find(n => n.id === nomineeId);
    if (nominee) {
        nominee.name = name;
    }
}

function updateNomineeBio(nomineeId, bio) {
    const nominee = nominees.find(n => n.id === nomineeId);
    if (nominee) {
        nominee.bio = bio;
    }
}

function updateNomineeCategories(nomineeId) {
    const nominee = nominees.find(n => n.id === nomineeId);
    if (nominee) {
        const oldCategories = nominee.categories || [];
        
        // Get selected categories from Select2
        const selectElement = document.getElementById(`categories-select-${nomineeId}`);
        const newCategories = Array.from(selectElement.selectedOptions).map(option => option.value);
        
        nominee.categories = newCategories;
        
        // Shortcodes will be generated on save/publish
        // const addedCategories = newCategories.filter(catId => !oldCategories.includes(catId));
        // addedCategories.forEach(categoryId => {
        //     generateShortcodeForCategory(nomineeId, nominee.name, categoryId);
        // });
        
        // Remove shortcodes for removed categories
        const removedCategories = oldCategories.filter(catId => !newCategories.includes(catId));
        removedCategories.forEach(categoryId => {
            if (nominee.shortcodes) {
                delete nominee.shortcodes[categoryId];
            }
            if (nominee.shortcode_regenerations) {
                delete nominee.shortcode_regenerations[categoryId];
            }
        });
        
        // Re-render to show updated shortcodes
        renderNominees();
    }
}

function filterNomineeCategories(nomineeId, searchTerm) {
    const categoryItems = document.querySelectorAll(`.category-item-${nomineeId}`);
    const term = searchTerm.toLowerCase();
    
    categoryItems.forEach(item => {
        const categoryName = item.getAttribute('data-category-name');
        if (categoryName.includes(term)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function previewNomineePhoto(nomineeId, fileInput) {
    const photoWrapper = document.getElementById(`photo-wrapper-${nomineeId}`);
    
    if (fileInput.files && fileInput.files[0]) {
        const file = fileInput.files[0];
        
        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            fileInput.value = '';
            return;
        }
        
        // Update nominee data
        const nominee = nominees.find(n => n.id === nomineeId);
        if (nominee) {
            nominee.photo_file = file;
            nominee.image_url = ''; // Clear old URL to show new preview
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            // Replace the entire photo wrapper content with the new image
            photoWrapper.innerHTML = `
                <img src="${e.target.result}" 
                     alt="Photo" 
                     style="width: 100%; height: 100%; object-fit: cover;">
                <div class="photo-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s;">
                    <i class="fas fa-camera text-white"></i>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    }
}

function previewCategoryPhoto(nomineeId, categoryId, fileInput) {
    const previewElement = document.getElementById(`cat-photo-preview-${nomineeId}-${categoryId}`);
    
    if (fileInput.files && fileInput.files[0]) {
        const file = fileInput.files[0];
        
        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            fileInput.value = '';
            return;
        }
        
        // Update nominee data
        const nominee = nominees.find(n => n.id === nomineeId);
        if (nominee) {
            if (!nominee.category_photo_files) {
                nominee.category_photo_files = {};
            }
            nominee.category_photo_files[categoryId] = file;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            previewElement.innerHTML = `<img src="${e.target.result}" 
                                             alt="Category Photo" 
                                             style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;">`;
            previewElement.style.display = 'block';
            previewElement.classList.add('border');
        };
        reader.readAsDataURL(file);
    }
}

// Nominee management
function addNominee() {
    if (categories.length === 0) {
        alert('Please add categories first before adding nominees');
        return;
    }
    
    // Clear previous inputs
    document.getElementById('nomineeName').value = '';
    document.getElementById('nomineeBio').value = '';
    document.getElementById('nomineePhoto').value = '';
    document.getElementById('categorySearch').value = '';
    document.getElementById('existingNomineeSearch').value = '';
    
    // Show/hide sections based on whether there are existing nominees
    const existingSection = document.getElementById('existingNomineesSection');
    const newNomineeForm = document.getElementById('newNomineeForm');
    const toggleBtn = document.getElementById('toggleNewNomineeBtn');
    
    if (nominees.length > 0) {
        // Has existing nominees - show them, collapse create form
        existingSection.style.display = 'block';
        populateExistingNominees();
        newNomineeForm.style.display = 'none';
        toggleBtn.style.display = 'inline-block';
        toggleBtn.innerHTML = '<i class="fas fa-plus me-1"></i>Create New';
    } else {
        // No existing nominees - hide that section, show create form
        existingSection.style.display = 'none';
        newNomineeForm.style.display = 'block';
        toggleBtn.style.display = 'none';
    }
    
    // Populate categories as checkboxes
    populateCategoryCheckboxes();
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('addNomineeModal'));
    modal.show();
    
    // Focus on name input after modal is shown
    document.getElementById('addNomineeModal').addEventListener('shown.bs.modal', function () {
        if (nominees.length > 0) {
            document.getElementById('existingNomineeSearch').focus();
        } else {
            document.getElementById('nomineeName').focus();
        }
    }, { once: true });
}

// Add nominee with specific category pre-selected
function addNomineeForCategory(categoryId) {
    if (categories.length === 0) {
        alert('Please add categories first before adding nominees');
        return;
    }
    
    // Clear previous inputs
    document.getElementById('nomineeName').value = '';
    document.getElementById('nomineeBio').value = '';
    document.getElementById('nomineePhoto').value = '';
    document.getElementById('categorySearch').value = '';
    document.getElementById('existingNomineeSearch').value = '';
    
    // Show/hide sections based on whether there are existing nominees
    const existingSection = document.getElementById('existingNomineesSection');
    const newNomineeForm = document.getElementById('newNomineeForm');
    const toggleBtn = document.getElementById('toggleNewNomineeBtn');
    
    if (nominees.length > 0) {
        // Has existing nominees - show them, collapse create form
        existingSection.style.display = 'block';
        populateExistingNominees();
        newNomineeForm.style.display = 'none';
        toggleBtn.style.display = 'inline-block';
        toggleBtn.innerHTML = '<i class="fas fa-plus me-1"></i>Create New';
    } else {
        // No existing nominees - hide that section, show create form
        existingSection.style.display = 'none';
        newNomineeForm.style.display = 'block';
        toggleBtn.style.display = 'none';
    }
    
    // Populate categories as checkboxes
    populateCategoryCheckboxes();
    
    // Pre-select the specified category
    const categoryCheckbox = document.querySelector(`.category-checkbox[value="${categoryId}"]`);
    if (categoryCheckbox) {
        categoryCheckbox.checked = true;
        updateSelectedCategoriesCount();
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('addNomineeModal'));
    modal.show();
    
    // Focus on name input after modal is shown
    document.getElementById('addNomineeModal').addEventListener('shown.bs.modal', function () {
        if (nominees.length > 0) {
            document.getElementById('existingNomineeSearch').focus();
        } else {
            document.getElementById('nomineeName').focus();
        }
    }, { once: true });
}

function populateExistingNominees(searchTerm = '') {
    const container = document.getElementById('existingNomineesCheckboxList');
    const filteredNominees = nominees.filter(nom => 
        nom.name.toLowerCase().includes(searchTerm.toLowerCase())
    );
    
    if (filteredNominees.length === 0) {
        container.innerHTML = '<div class="text-muted text-center py-3">No nominees found</div>';
        return;
    }
    
    container.innerHTML = filteredNominees.map(nom => {
        const categoryNames = (nom.categories || []).map(catId => {
            const cat = categories.find(c => String(c.id) === String(catId));
            return cat ? cat.name : '';
        }).filter(Boolean).join(', ');
        
        return `
            <div class="form-check">
                <input class="form-check-input existing-nominee-checkbox" 
                       type="checkbox" 
                       value="${nom.id}" 
                       id="existing-nom-${nom.id}" 
                       onchange="updateSelectedExistingNomineesCount()">
                <label class="form-check-label" for="existing-nom-${nom.id}">
                    <strong>${nom.name}</strong>
                    ${categoryNames ? `<br><small class="text-muted">Currently in: ${categoryNames}</small>` : ''}
                </label>
            </div>
        `;
    }).join('');
    
    updateSelectedExistingNomineesCount();
}

function filterExistingNominees(searchTerm) {
    populateExistingNominees(searchTerm);
}

function updateSelectedExistingNomineesCount() {
    const checkboxes = document.querySelectorAll('.existing-nominee-checkbox:checked');
    const count = checkboxes.length;
    document.getElementById('selectedExistingNomineesCount').textContent = count;
    
    // Update button text
    const buttonText = document.getElementById('addNomineeButtonText');
    if (count > 0) {
        buttonText.textContent = `Assign Selected (${count})`;
    } else {
        buttonText.textContent = 'Add Nominee';
    }
}

function toggleNewNomineeForm() {
    const form = document.getElementById('newNomineeForm');
    const btn = document.getElementById('toggleNewNomineeBtn');
    
    if (form.style.display === 'none') {
        form.style.display = 'block';
        btn.innerHTML = '<i class="fas fa-minus me-1"></i>Hide Form';
    } else {
        form.style.display = 'none';
        btn.innerHTML = '<i class="fas fa-plus me-1"></i>Create New';
    }
}

function populateCategoryCheckboxes(searchTerm = '') {
    const container = document.getElementById('categoriesCheckboxList');
    const filteredCategories = categories.filter(cat => 
        cat.name.toLowerCase().includes(searchTerm.toLowerCase())
    );
    
    if (filteredCategories.length === 0) {
        container.innerHTML = '<div class="text-muted text-center py-3">No categories found</div>';
        return;
    }
    
    container.innerHTML = filteredCategories.map(cat => `
        <div class="form-check">
            <input class="form-check-input category-checkbox" type="checkbox" value="${cat.id}" id="cat-${cat.id}" onchange="updateSelectedCategoriesCount()">
            <label class="form-check-label" for="cat-${cat.id}">
                ${cat.name}
            </label>
        </div>
    `).join('');
    
    updateSelectedCategoriesCount();
}

function updateSelectedCategoriesCount() {
    const checkboxes = document.querySelectorAll('.category-checkbox:checked');
    document.getElementById('selectedCategoriesCount').textContent = checkboxes.length;
}

// Category search functionality
document.addEventListener('DOMContentLoaded', function() {
    const categorySearch = document.getElementById('categorySearch');
    if (categorySearch) {
        categorySearch.addEventListener('input', function(e) {
            populateCategoryCheckboxes(e.target.value);
        });
    }
});

function saveNomineeFromModal() {
    // Check if user selected existing nominees
    const selectedExistingCheckboxes = document.querySelectorAll('.existing-nominee-checkbox:checked');
    const selectedExistingNomineeIds = Array.from(selectedExistingCheckboxes).map(cb => cb.value);
    
    // Get selected categories from checkboxes
    const selectedCategoryCheckboxes = document.querySelectorAll('.category-checkbox:checked');
    const selectedCategories = Array.from(selectedCategoryCheckboxes).map(cb => cb.value);
    
    if (selectedCategories.length === 0) {
        alert('Please select at least one category');
        return;
    }
    
    // Handle existing nominees assignment
    if (selectedExistingNomineeIds.length > 0) {
        selectedExistingNomineeIds.forEach(nomineeId => {
            const nominee = nominees.find(n => String(n.id) === String(nomineeId));
            if (nominee) {
                // Add new categories to existing nominee
                selectedCategories.forEach(catId => {
                    if (!nominee.categories.includes(catId)) {
                        nominee.categories.push(catId);
                        // Shortcode will be generated on save/publish
                    }
                });
            }
        });
        
        renderNominees();
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('addNomineeModal'));
        modal.hide();
        
        // Show success message
        const count = selectedExistingNomineeIds.length;
        showToast(`${count} nominee(s) assigned to selected categories!`, 'success');
        return;
    }
    
    // Handle new nominee creation
    const nomineeName = document.getElementById('nomineeName').value.trim();
    const nomineeBio = document.getElementById('nomineeBio').value.trim();
    const nomineePhoto = document.getElementById('nomineePhoto').files[0];
    
    if (!nomineeName) {
        alert('Please enter nominee name or select existing nominees');
        document.getElementById('nomineeName').focus();
        return;
    }
    
    const nominee = {
        id: Date.now(),
        name: nomineeName,
        bio: nomineeBio,
        image_url: '',
        categories: selectedCategories,
        shortcodes: {}, // Object to store shortcode per category
        shortcode_regenerations: {} // Object to store regeneration count per category
    };
    
    // Handle photo if uploaded
    if (nomineePhoto) {
        nominee.photo_file = nomineePhoto;
        // Create preview URL
        const reader = new FileReader();
        reader.onload = function(e) {
            nominee.image_url = e.target.result;
        };
        reader.readAsDataURL(nomineePhoto);
    }
    
    nominees.push(nominee);
    
    // Shortcodes will be generated on save/publish
    // selectedCategories.forEach(categoryId => {
    //     generateShortcodeForCategory(nominee.id, nomineeName, categoryId);
    // });
    
    renderNominees();
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('addNomineeModal'));
    modal.hide();
    
    // Show success message
    showToast(`Nominee "${nomineeName}" added successfully!`, 'success');
}

function removeNominee(nomineeId) {
    const nominee = nominees.find(n => n.id === nomineeId);
    
    // If this is an existing nominee (has database ID), track it for deletion
    if (nominee && nominee.is_existing && nominee.db_id) {
        // Add to deleted nominees list
        if (!window.deletedNomineeIds) {
            window.deletedNomineeIds = [];
        }
        window.deletedNomineeIds.push(nominee.db_id);
        
        // Update hidden field
        updateDeletedNomineesField();
    }
    
    // Remove from nominees array
    nominees = nominees.filter(n => n.id !== nomineeId);
    renderNominees();
    
    showToast('Nominee removed', 'info');
}

function updateDeletedNomineesField() {
    // Get or create hidden field for deleted nominee IDs
    let deletedField = document.getElementById('deleted_nominee_ids');
    if (!deletedField) {
        deletedField = document.createElement('input');
        deletedField.type = 'hidden';
        deletedField.id = 'deleted_nominee_ids';
        deletedField.name = 'deleted_nominee_ids';
        document.getElementById('eventWizardForm').appendChild(deletedField);
    }
    
    // Update value with comma-separated IDs
    deletedField.value = (window.deletedNomineeIds || []).join(',');
}

function renderNominees() {
    const container = document.getElementById('nomineesContainer');
    const noMessage = document.getElementById('noNomineesMessage');
    
    // Check if elements exist
    if (!container || !noMessage) {
        console.error('Required elements not found');
        return;
    }
    
    if (nominees.length === 0) {
        noMessage.style.display = 'block';
        container.innerHTML = '';
        return;
    }
    
    noMessage.style.display = 'none';
    
    // Store current file inputs before re-rendering
    const fileInputs = {};
    nominees.forEach(nominee => {
        const fileInput = document.querySelector(`input[name="nominee_photo_${nominee.id}"]`);
        if (fileInput && fileInput.files && fileInput.files[0]) {
            fileInputs[nominee.id] = fileInput.files[0];
        }
    });
    
    const html = nominees.map((nominee, index) => `
        <div class="nominee-item" data-nominee-id="${nominee.id}">
                <div class="d-flex gap-3">
                    <div class="drag-handle" style="cursor: move; padding: 4px; color: #6c757d;">
                        <i class="fas fa-grip-vertical"></i>
                    </div>
                    
                    <!-- Photo with hover effect -->
                    <div class="nominee-photo-container" style="position: relative; width: 80px; height: 80px;">
                        <input type="file" 
                               id="photo-input-${nominee.id}"
                               name="nominee_photo_${nominee.id}" 
                               accept="image/*"
                               style="display: none;"
                               onchange="previewNomineePhoto(${nominee.id}, this)">
                        <div class="photo-wrapper" 
                             id="photo-wrapper-${nominee.id}"
                             style="width: 80px; height: 80px; border-radius: 8px; overflow: hidden; cursor: pointer; position: relative;"
                             onclick="document.getElementById('photo-input-${nominee.id}').click()">
                            ${nominee.image_url ? `
                                <img src="${getImageUrl(nominee.image_url)}" 
                                     alt="Photo" 
                                     style="width: 100%; height: 100%; object-fit: cover;">
                                <div class="photo-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s;">
                                    <i class="fas fa-camera text-white"></i>
                                </div>
                            ` : `
                                <div style="width: 100%; height: 100%; background-color: #f8f9fa; border: 2px dashed #dee2e6; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                                    <i class="fas fa-camera text-muted mb-1"></i>
                                    <small class="text-muted">Add Photo</small>
                                </div>
                            `}
                        </div>
                    </div>
                    
                    <div class="flex-grow-1">
                        <div class="row g-2">
                            <!-- Name and Bio in one row -->
                            <div class="col-md-4">
                                <label class="form-label small fw-bold mb-1">Name *</label>
                                <input type="text" 
                                       class="form-control form-control-sm" 
                                       name="nominees[${nominee.id}][name]" 
                                       value="${nominee.name}"
                                       placeholder="Nominee name"
                                       onchange="updateNomineeName(${nominee.id}, this.value)"
                                       required>
                                <input type="hidden" name="nominees[${nominee.id}][order]" value="${index}">
                            </div>
                            
                            <div class="col-md-8">
                                <label class="form-label small fw-bold mb-1">Bio (Optional)</label>
                                <input type="text" 
                                       class="form-control form-control-sm" 
                                       name="nominees[${nominee.id}][bio]" 
                                       placeholder="Brief biography or description" 
                                       value="${nominee.bio || ''}"
                                       onchange="updateNomineeBio(${nominee.id}, this.value)">
                            </div>
                            
                            <!-- Categories with Select2 -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold mb-1">Categories *</label>
                                <select class="form-select form-select-sm nominee-categories-select" 
                                        id="categories-select-${nominee.id}"
                                        name="nominees[${nominee.id}][categories][]" 
                                        multiple
                                        data-nominee-id="${nominee.id}">
                                    ${categories.map(cat => `
                                        <option value="${cat.id}" ${nominee.categories && nominee.categories.map(String).includes(String(cat.id)) ? 'selected' : ''}>
                                            ${cat.name}
                                        </option>
                                    `).join('')}
                                </select>
                            </div>
                            
                            <!-- Inline Shortcodes -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold mb-1">Shortcodes</label>
                                <div class="d-flex flex-wrap gap-1" style="min-height: 31px; align-items: center;">
                                    ${nominee.categories && nominee.categories.length > 0 ? 
                                        nominee.categories.map(catId => {
                                            const category = categories.find(c => String(c.id) === String(catId));
                                            const categoryName = category ? category.name : 'Unknown';
                                            const shortcodes = nominee.shortcodes || {};
                                            const shortcode = shortcodes[catId] || (nominee.is_existing ? 'Loading...' : 'On Save');
                                            const regenerations = nominee.shortcode_regenerations || {};
                                            const regenCount = regenerations[catId] || 0;
                                            
                                            return `
                                                <span class="badge bg-primary" 
                                                      id="shortcode-${nominee.id}-${catId}"
                                                      title="${categoryName}: ${shortcode}${nominee.is_existing ? ' (Locked)' : ' - ' + regenCount + '/3 regens'}">
                                                    ${shortcode}
                                                    ${nominee.is_existing ? '<i class="fas fa-lock ms-1" style="font-size: 0.7em;"></i>' : ''}
                                                </span>
                                            `;
                                        }).join('') 
                                        : '<small class="text-muted">No categories</small>'
                                    }
                                </div>
                            </div>
                            
                            <!-- Per-Category Photos (Collapsible) -->
                            ${nominee.categories && nominee.categories.length > 0 ? `
                            <div class="col-12 mt-3">
                                <div class="accordion accordion-flush" id="accordion-${nominee.id}">
                                    <div class="accordion-item border">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2 px-3" type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#collapse-photos-${nominee.id}">
                                                <i class="fas fa-images me-2"></i>
                                                <small class="fw-bold">Per-Category Photos (Optional)</small>
                                                <small class="text-muted ms-2">Use different photos for each category</small>
                                            </button>
                                        </h2>
                                        <div id="collapse-photos-${nominee.id}" class="accordion-collapse collapse">
                                            <div class="accordion-body p-3">
                                                <div class="row g-2">
                                                    ${nominee.categories.map(catId => {
                                                        const category = categories.find(c => String(c.id) === String(catId));
                                                        const categoryName = category ? category.name : 'Unknown';
                                                        const categoryPhotos = nominee.category_photos || {};
                                                        const categoryPhoto = categoryPhotos[catId];
                                                        
                                                        return `
                                                        <div class="col-md-6">
                                                            <div class="card">
                                                                <div class="card-body p-2">
                                                                    <label class="form-label small mb-1 fw-bold">${categoryName}</label>
                                                                    <div class="d-flex gap-2 align-items-center">
                                                                        <input type="file" 
                                                                               id="cat-photo-${nominee.id}-${catId}"
                                                                               name="nominee_category_photo_${nominee.id}_${catId}"
                                                                               accept="image/*"
                                                                               class="form-control form-control-sm"
                                                                               onchange="previewCategoryPhoto(${nominee.id}, '${catId}', this)">
                                                                        ${categoryPhoto ? `
                                                                            <img src="${getImageUrl(categoryPhoto)}" 
                                                                                 id="cat-photo-preview-${nominee.id}-${catId}"
                                                                                 alt="Category Photo" 
                                                                                 style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;" 
                                                                                 class="border">
                                                                        ` : `
                                                                            <div id="cat-photo-preview-${nominee.id}-${catId}" 
                                                                                 style="width: 40px; height: 40px; background: #f8f9fa; border: 1px dashed #dee2e6; border-radius: 4px; display: none;">
                                                                            </div>
                                                                        `}
                                                                    </div>
                                                                    <small class="text-muted d-block mt-1">
                                                                        <i class="fas fa-info-circle me-1"></i>
                                                                        Leave empty to use default photo
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        `;
                                                    }).join('')}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                <div class="mt-2 text-end">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeNominee(${nominee.id})">
                        <i class="fas fa-trash me-1"></i>Remove Nominee
                    </button>
                </div>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = html;
    
    // Destroy existing Select2 instances first
    $('.nominee-categories-select').each(function() {
        if ($(this).data('select2')) {
            $(this).select2('destroy');
        }
    });
    
    // Initialize Select2 for all category selects
    setTimeout(() => {
        if (typeof $.fn.select2 !== 'undefined') {
            $('.nominee-categories-select').each(function() {
                const nomineeId = $(this).data('nominee-id');
                $(this).select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Select categories...',
                    allowClear: false,
                    width: '100%',
                    dropdownAutoWidth: true
                }).on('change', function() {
                    updateNomineeCategories(nomineeId);
                });
            });
        } else {
            console.error('Select2 not loaded');
        }
    }, 150);
    
    // Initialize sortable if not already initialized
    if (!nomineesSortable && typeof Sortable !== 'undefined') {
        initializeNomineesSortable();
    }
    
    // Restore file inputs and previews after re-rendering
    Object.keys(fileInputs).forEach(nomineeId => {
        const file = fileInputs[nomineeId];
        const fileInput = document.querySelector(`input[name="nominee_photo_${nomineeId}"]`);
        
        if (fileInput && file) {
            // Create a new FileList with the preserved file
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;
            
            // Update nominee data
            const nominee = nominees.find(n => n.id == nomineeId);
            if (nominee) {
                nominee.photo_file = file;
            }
            
            // Show preview
            const previewDiv = document.getElementById(`nominee-preview-${nomineeId}`);
            const previewImg = document.getElementById(`nominee-preview-img-${nomineeId}`);
            const currentImageDiv = document.getElementById(`current-nominee-image-${nomineeId}`);
            const noImageDiv = document.getElementById(`no-nominee-image-${nomineeId}`);
            
            if (previewDiv && previewImg) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewDiv.style.display = 'block';
                    if (currentImageDiv) currentImageDiv.style.opacity = '0.5';
                    if (noImageDiv) noImageDiv.style.opacity = '0.5';
                };
                reader.readAsDataURL(file);
            }
        }
    });
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
            return;
        }
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file');
            input.value = '';
            previewDiv.style.display = 'none';
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

// Auto-generate event code from name (only if not editing)
<?php if (!($isEditing ?? false)): ?>
document.getElementById('name').addEventListener('input', function() {
    const name = this.value;
    const code = name.toUpperCase()
                    .replace(/[^A-Z0-9]/g, '')
                    .substring(0, 10);
    document.getElementById('code').value = code;
});
<?php endif; ?>

// Pre-populate data when editing
<?php if ($isEditing ?? false): ?>
// Wait for DOM to be ready before pre-populating
document.addEventListener('DOMContentLoaded', function() {
    // Add a small delay to ensure all elements are rendered
    setTimeout(function() {
        // Pre-populate categories
        <?php if (!empty($categories)): ?>
        categories = <?= json_encode(array_map(function($cat) {
            return [
                'id' => strval($cat['id']), // Convert to string for consistency
                'name' => $cat['name'],
                'description' => $cat['description'] ?? ''
            ];
        }, $categories)) ?>;
        console.log('Pre-populating categories:', categories);
        renderCategories();
        <?php endif; ?>

        // Pre-populate nominees
        <?php if (!empty($nominees)): ?>
        nominees = <?= json_encode(array_map(function($nominee) {
            // Convert shortcodes array to object with string keys for JavaScript
            $shortcodesObj = [];
            if (!empty($nominee['shortcodes'])) {
                foreach ($nominee['shortcodes'] as $catId => $code) {
                    $shortcodesObj[strval($catId)] = $code;
                }
            }
            
            // Set regenerations to 3 for all categories to disable regeneration
            $regenerationsObj = [];
            if (!empty($nominee['categories'])) {
                foreach ($nominee['categories'] as $catId) {
                    $regenerationsObj[strval($catId)] = 3;
                }
            }
            
            // Get category-specific photos
            $categoryPhotosObj = [];
            if (!empty($nominee['category_photos'])) {
                foreach ($nominee['category_photos'] as $catId => $photoUrl) {
                    if (!empty($photoUrl)) {
                        $categoryPhotosObj[strval($catId)] = $photoUrl;
                    }
                }
            }
            
            return [
                'id' => $nominee['id'],
                'db_id' => $nominee['id'], // Store database ID for deletion tracking
                'name' => $nominee['name'],
                'bio' => $nominee['bio'] ?? '',
                'image_url' => $nominee['image_url'] ?? '',
                'categories' => array_map('strval', $nominee['categories'] ?? []), // Convert to strings
                'shortcodes' => $shortcodesObj, // Object with category_id => shortcode
                'shortcode_regenerations' => $regenerationsObj, // Set to 3 per category to disable regeneration
                'category_photos' => $categoryPhotosObj, // Object with category_id => photo_url
                'is_existing' => true // Flag to indicate this is an existing nominee
            ];
        }, $nominees)) ?>;
        console.log('Pre-populating nominees:', nominees);
        renderNominees();
        <?php endif; ?>
    }, 100); // 100ms delay
});
<?php endif; ?>

// Remove duplicate function - already defined above

// Ensure buttons are visible on page load if needed
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the final step and show appropriate buttons
    const currentStepElement = document.querySelector('.step-indicator.active');
    if (currentStepElement && currentStepElement.dataset.step === '4') {
        const submitBtn = document.getElementById('submitBtn');
        const upgradeBtn = document.getElementById('upgradeBtn');
        const nextBtn = document.getElementById('nextBtn');
        
        if (submitBtn) {
            submitBtn.style.display = 'inline-block';
            nextBtn.style.display = 'none';
        }
        if (upgradeBtn) {
            upgradeBtn.style.display = 'inline-block';
            nextBtn.style.display = 'none';
        }
    }
});

// Initialize category sortable
function initializeCategorySortable() {
    const container = document.getElementById('categoriesContainer');
    if (!container || typeof Sortable === 'undefined') return;
    
    categorySortable = new Sortable(container, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        onEnd: function(evt) {
            // Reorder categories array
            const item = categories.splice(evt.oldIndex, 1)[0];
            categories.splice(evt.newIndex, 0, item);
            // Re-render to update order values
            renderCategories();
        }
    });
}

// Initialize nominees sortable
function initializeNomineesSortable() {
    const container = document.getElementById('nomineesContainer');
    if (!container || typeof Sortable === 'undefined') return;
    
    nomineesSortable = new Sortable(container, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        onEnd: function(evt) {
            // Reorder nominees array
            const item = nominees.splice(evt.oldIndex, 1)[0];
            nominees.splice(evt.newIndex, 0, item);
            // Re-render to update order values
            renderNominees();
        }
    });
}

// Shortcode generation functions (per category)
function generateShortcodeForCategory(nomineeId, nomineeName, categoryId) {
    fetch('<?= APP_URL ?>/api/shortcode/generate-preview', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            nominee_name: nomineeName,
            nominee_id: nomineeId,
            category_id: categoryId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update nominee object
            const nominee = nominees.find(n => n.id === nomineeId);
            if (nominee) {
                if (!nominee.shortcodes) nominee.shortcodes = {};
                nominee.shortcodes[categoryId] = data.shortcode;
                
                // Update display
                const shortcodeElement = document.getElementById(`shortcode-${nomineeId}-${categoryId}`);
                if (shortcodeElement) {
                    shortcodeElement.textContent = data.shortcode;
                }
            }
        } else {
            console.error('Failed to generate shortcode:', data.message);
            const shortcodeElement = document.getElementById(`shortcode-${nomineeId}-${categoryId}`);
            if (shortcodeElement) {
                shortcodeElement.textContent = 'Error';
                shortcodeElement.classList.remove('bg-primary');
                shortcodeElement.classList.add('bg-danger');
            }
        }
    })
    .catch(error => {
        console.error('Shortcode generation error:', error);
    });
}

function regenerateShortcodeForCategory(nomineeId, categoryId) {
    const nominee = nominees.find(n => n.id === nomineeId);
    if (!nominee) return;
    
    // Initialize regenerations object if needed
    if (!nominee.shortcode_regenerations) nominee.shortcode_regenerations = {};
    
    const regenCount = nominee.shortcode_regenerations[categoryId] || 0;
    
    // Check regeneration limit
    if (regenCount >= 3) {
        alert('Maximum regenerations (3) reached for this category.');
        return;
    }
    
    const category = categories.find(c => String(c.id) === String(categoryId));
    const categoryName = category ? category.name : 'this category';
    
    if (!confirm(`Are you sure you want to regenerate the shortcode for ${categoryName}? This will replace the current shortcode.`)) {
        return;
    }
    
    // Show loading state
    const shortcodeElement = document.getElementById(`shortcode-${nomineeId}-${categoryId}`);
    const regenerateBtn = document.getElementById(`regenerate-btn-${nomineeId}-${categoryId}`);
    
    if (shortcodeElement) {
        shortcodeElement.textContent = 'Generating...';
    }
    if (regenerateBtn) {
        regenerateBtn.disabled = true;
    }
    
    // Generate new shortcode
    fetch('<?= APP_URL ?>/api/shortcode/generate-preview', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            nominee_name: nominee.name,
            nominee_id: nomineeId + Math.random(), // Add randomness for different code
            category_id: categoryId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update nominee object
            if (!nominee.shortcodes) nominee.shortcodes = {};
            nominee.shortcodes[categoryId] = data.shortcode;
            nominee.shortcode_regenerations[categoryId] = regenCount + 1;
            
            // Re-render to update display and button state
            renderNominees();
            
            // Show success message
            showToast(`Shortcode regenerated for ${categoryName}: ${data.shortcode} (${nominee.shortcode_regenerations[categoryId]}/3)`, 'success');
        } else {
            showToast('Failed to regenerate shortcode: ' + data.message, 'error');
            if (regenerateBtn) {
                regenerateBtn.disabled = false;
            }
        }
    })
    .catch(error => {
        console.error('Shortcode regeneration error:', error);
        showToast('An error occurred while regenerating the shortcode.', 'error');
        if (regenerateBtn) {
            regenerateBtn.disabled = false;
        }
    });
}

// Toast notification function
function showToast(message, type = 'success') {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';
    const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
    
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas ${icon} me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function () {
        toastElement.remove();
    });
}

// Add Enter key support for modals
document.addEventListener('DOMContentLoaded', function() {
    // Category modal Enter key
    const categoryNameInput = document.getElementById('categoryName');
    if (categoryNameInput) {
        categoryNameInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveCategoryFromModal();
            }
        });
    }
    
    // Nominee modal Enter key (only for name field, not textarea)
    const nomineeNameInput = document.getElementById('nomineeName');
    if (nomineeNameInput) {
        nomineeNameInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveNomineeFromModal();
            }
        });
    }
});
</script>

<!-- SortableJS Library -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<!-- jQuery (required for Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
