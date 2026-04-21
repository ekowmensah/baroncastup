<!-- Add Contestant Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-user-plus me-2"></i>
            Add New Contestant
        </h2>
        <p class="text-muted mb-0">
            <?php if (isset($_GET['event_id'])): ?>
                Adding contestant to: <strong><?= htmlspecialchars($selectedEvent['name'] ?? 'Selected Event') ?></strong>
            <?php else: ?>
                Add a contestant to your voting event
            <?php endif; ?>
        </p>
    </div>
    <div>
        <a href="<?= ORGANIZER_URL ?>/contestants" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Contestants
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i>
                    Contestant Information
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= ORGANIZER_URL ?>/contestants" enctype="multipart/form-data" id="contestantForm">
                    <?php if (isset($_GET['event_id'])): ?>
                        <input type="hidden" name="event_id" value="<?= htmlspecialchars($_GET['event_id']) ?>">
                    <?php endif; ?>
                    
                    <!-- Basic Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user me-1"></i>
                                    Contestant Name *
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="name" 
                                       name="name" 
                                       placeholder="Enter contestant's full name"
                                       required>
                                <div class="form-text">The name that will appear to voters</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="contestant_code" class="form-label">
                                    <i class="fas fa-hashtag me-1"></i>
                                    Contestant Code *
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="contestant_code" 
                                       name="contestant_code" 
                                       placeholder="e.g., CONT001"
                                       style="text-transform: uppercase;"
                                       required>
                                <div class="form-text">Unique identifier (auto-generated if left empty)</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Event and Category Selection -->
                    <?php if (!isset($_GET['event_id'])): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="event_id" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>
                                    Event *
                                </label>
                                <select class="form-select form-select-lg" id="event_id" name="event_id" required>
                                    <option value="">Select Event</option>
                                    <?php if (!empty($events)): ?>
                                        <?php foreach ($events as $event): ?>
                                            <option value="<?= $event['id'] ?>" 
                                                    data-status="<?= $event['status'] ?>"
                                                    data-categories="<?= htmlspecialchars(json_encode($event['categories'] ?? [])) ?>">
                                                <?= htmlspecialchars($event['name']) ?> 
                                                (<?= ucfirst($event['status']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">
                                    <i class="fas fa-tag me-1"></i>
                                    Category *
                                </label>
                                <select class="form-select form-select-lg" id="category_id" name="category_id" required disabled>
                                    <option value="">Select Event First</option>
                                </select>
                                <div class="form-text">Categories will load after selecting an event</div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">
                                    <i class="fas fa-tag me-1"></i>
                                    Category *
                                </label>
                                <select class="form-select form-select-lg" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php if (!empty($categories)): ?>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>">
                                                <?= htmlspecialchars($category['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Bio -->
                    <div class="mb-3">
                        <label for="bio" class="form-label">
                            <i class="fas fa-align-left me-1"></i>
                            Biography (Optional)
                        </label>
                        <textarea class="form-control" 
                                  id="bio" 
                                  name="bio" 
                                  rows="4" 
                                  placeholder="Tell voters about this contestant..."></textarea>
                        <div class="form-text">A brief description that will help voters learn about the contestant</div>
                    </div>
                    
                    <!-- Image Upload -->
                    <div class="mb-4">
                        <label for="image" class="form-label">
                            <i class="fas fa-image me-1"></i>
                            Contestant Photo
                        </label>
                        <div class="card border-dashed">
                            <div class="card-body text-center">
                                <div id="imagePreview" class="mb-3" style="display: none;">
                                    <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                </div>
                                <div id="uploadArea">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <p class="mb-2">Click to upload or drag and drop</p>
                                    <p class="text-muted small">JPG, PNG, GIF up to 2MB</p>
                                </div>
                                <input type="file" 
                                       class="form-control" 
                                       id="image" 
                                       name="image" 
                                       accept="image/*"
                                       style="display: none;">
                                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('image').click()">
                                    <i class="fas fa-upload me-2"></i>Choose Photo
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button type="submit" name="action" value="save" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>
                                Save Contestant
                            </button>
                            <button type="submit" name="action" value="save_and_add" class="btn btn-success btn-lg">
                                <i class="fas fa-plus me-2"></i>
                                Save & Add Another
                            </button>
                        </div>
                        <div>
                            <a href="<?= ORGANIZER_URL ?>/contestants" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Tips Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-lightbulb me-2"></i>
                    Tips for Adding Contestants
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-primary">
                        <i class="fas fa-user me-1"></i>
                        Name Guidelines
                    </h6>
                    <p class="small text-muted">Use the contestant's full name as it should appear to voters. Avoid abbreviations.</p>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-primary">
                        <i class="fas fa-hashtag me-1"></i>
                        Contestant Codes
                    </h6>
                    <p class="small text-muted">Keep codes short (3-8 characters) and memorable. They'll be used for voting shortcuts.</p>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-primary">
                        <i class="fas fa-image me-1"></i>
                        Photo Requirements
                    </h6>
                    <ul class="small text-muted mb-0">
                        <li>High quality, clear photos work best</li>
                        <li>Square or portrait orientation preferred</li>
                        <li>Maximum file size: 2MB</li>
                        <li>Supported formats: JPG, PNG, GIF</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <?php if (isset($selectedEvent)): ?>
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Event Overview
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <h5 class="text-primary mb-1"><?= count($categories ?? []) ?></h5>
                            <small class="text-muted">Categories</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <h5 class="text-success mb-1"><?= $selectedEvent['contestant_count'] ?? 0 ?></h5>
                            <small class="text-muted">Contestants</small>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <strong>Status:</strong> <?= ucfirst($selectedEvent['status'] ?? 'Unknown') ?><br>
                        <strong>Vote Price:</strong> $<?= number_format($selectedEvent['vote_price'] ?? 0, 2) ?>
                    </small>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.border-dashed {
    border: 2px dashed #dee2e6 !important;
    border-radius: 8px;
}

.border-dashed:hover {
    border-color: #007bff !important;
    background-color: #f8f9ff;
}

.form-control-lg, .form-select-lg {
    border-radius: 8px;
}

.card {
    border-radius: 12px;
}

.btn-lg {
    border-radius: 8px;
    padding: 12px 24px;
}

#contestantForm .form-label {
    font-weight: 600;
    color: #495057;
}

.img-thumbnail {
    border-radius: 8px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventSelect = document.getElementById('event_id');
    const categorySelect = document.getElementById('category_id');
    const nameInput = document.getElementById('name');
    const codeInput = document.getElementById('contestant_code');
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const uploadArea = document.getElementById('uploadArea');
    
    // Auto-generate contestant code from name
    nameInput.addEventListener('input', function() {
        if (!codeInput.value) {
            const name = this.value.toUpperCase();
            const code = name.replace(/[^A-Z0-9]/g, '').substring(0, 6);
            codeInput.value = code;
        }
    });
    
    // Uppercase contestant code
    codeInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
    
    // Event selection handler
    if (eventSelect) {
        eventSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (this.value) {
                categorySelect.disabled = false;
                categorySelect.innerHTML = '<option value="">Loading categories...</option>';
                
                // Get categories for the selected event
                fetch(`<?= ORGANIZER_URL ?>/api/events/${this.value}/categories`)
                    .then(response => response.json())
                    .then(data => {
                        categorySelect.innerHTML = '<option value="">Select Category</option>';
                        
                        if (data.success && data.categories) {
                            data.categories.forEach(category => {
                                const option = document.createElement('option');
                                option.value = category.id;
                                option.textContent = category.name;
                                categorySelect.appendChild(option);
                            });
                        } else {
                            categorySelect.innerHTML = '<option value="">No categories found</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading categories:', error);
                        categorySelect.innerHTML = '<option value="">Error loading categories</option>';
                    });
            } else {
                categorySelect.disabled = true;
                categorySelect.innerHTML = '<option value="">Select Event First</option>';
            }
        });
    }
    
    // Image preview
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size
            if (file.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB');
                this.value = '';
                return;
            }
            
            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file');
                this.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
                uploadArea.style.display = 'none';
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.style.display = 'none';
            uploadArea.style.display = 'block';
        }
    });
    
    // Form validation
    document.getElementById('contestantForm').addEventListener('submit', function(e) {
        const name = nameInput.value.trim();
        const code = codeInput.value.trim();
        const eventId = eventSelect ? eventSelect.value : document.querySelector('input[name="event_id"]').value;
        const categoryId = categorySelect.value;
        
        if (!name || !code || !eventId || !categoryId) {
            e.preventDefault();
            alert('Please fill in all required fields');
            return false;
        }
        
        if (code.length < 3) {
            e.preventDefault();
            alert('Contestant code must be at least 3 characters long');
            return false;
        }
    });
});
</script>
