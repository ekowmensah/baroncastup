<!-- Create Event Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-plus me-2"></i>
            Create New Event
        </h2>
        <p class="text-muted mb-0">Set up a new voting event for your organization</p>
    </div>
    <div>
        <a href="<?= ORGANIZER_URL ?>/events" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Events
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <form method="POST" action="<?= ORGANIZER_URL ?>/events" enctype="multipart/form-data">
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
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="code" class="form-label">Event Code *</label>
                                <input type="text" class="form-control" id="code" name="code" required>
                                <div class="form-text">Unique identifier for this event</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="featured_image" class="form-label">Featured Image</label>
                        <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                        <div class="form-text">Upload a banner image for your event (JPG, PNG, max 5MB)</div>
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
                                <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">End Date & Time *</label>
                                <input type="datetime-local" class="form-control" id="end_date" name="end_date" required>
                            </div>
                        </div>
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
                                <label for="visibility" class="form-label">Visibility</label>
                                <select class="form-select" id="visibility" name="visibility">
                                    <option value="public">Public - Anyone can view</option>
                                    <option value="private">Private - Invitation only</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Initial Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="draft">Draft - Not visible to public</option>
                                    <option value="active">Active - Live and accepting votes</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="active" name="active" checked>
                                <label class="form-check-label" for="active">
                                    Event is active
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="results_visible" name="results_visible">
                                <label class="form-check-label" for="results_visible">
                                    Results visible to public
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="<?= ORGANIZER_URL ?>/events" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Create Event
                </button>
            </div>
        </form>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Event Creation Tips</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-lightbulb text-warning me-2"></i>
                        Choose a clear, descriptive name
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-lightbulb text-warning me-2"></i>
                        Use a short, memorable event code
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-lightbulb text-warning me-2"></i>
                        Set realistic start and end times
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-lightbulb text-warning me-2"></i>
                        Upload an engaging banner image
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Next Steps</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted">After creating your event:</p>
                <ol class="small">
                    <li>Add contestants</li>
                    <li>Set up categories (optional)</li>
                    <li>Configure voting rules</li>
                    <li>Test the voting process</li>
                    <li>Publish your event</li>
                </ol>
            </div>
        </div>
    </div>
</div>
