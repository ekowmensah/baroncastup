<?php 
$content = ob_start(); 
?>

<!-- Add Contestant Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-user-plus me-2"></i>
            Add New Contestant
        </h2>
        <p class="text-muted mb-0">Add contestants to your voting events with category assignments</p>
    </div>
    <div>
        <a href="<?= ORGANIZER_URL ?>/contestants" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Contestants
        </a>
    </div>
</div>

<form method="POST" action="<?= ORGANIZER_URL ?>/contestants" enctype="multipart/form-data" id="contestantForm">
    <div class="row">
        <div class="col-md-8">
            <!-- Event Selection -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar me-2"></i>
                        Select Event
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="event_id" class="form-label">Event *</label>
                        <select class="form-select" id="event_id" name="event_id" required onchange="loadEventCategories()">
                            <option value="">Select an event to add contestants to</option>
                            <?php if (!empty($events)): ?>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?= $event['id'] ?>" data-vote-price="<?= $event['vote_price'] ?>">
                                        <?= htmlspecialchars($event['name']) ?> 
                                        (<?= ucfirst($event['status']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="form-text">Choose the event where you want to add contestants</div>
                    </div>
                </div>
            </div>

            <!-- Contestants Section -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Contestants
                    </h5>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addContestant()" id="addContestantBtn" disabled>
                        <i class="fas fa-plus me-2"></i>Add Contestant
                    </button>
                </div>
                <div class="card-body">
                    <div class="text-center text-muted py-4" id="noContestantsMessage">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <p>No contestants added yet. Select an event first, then click "Add Contestant" to get started.</p>
                    </div>
                    <div id="contestantsContainer">
                        <!-- Contestants will be added here dynamically -->
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Event Info -->
            <div class="card mb-3" id="eventInfoCard" style="display: none;">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Event Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Event:</strong>
                        <div id="selectedEventName" class="text-muted">-</div>
                    </div>
                    <div class="mb-2">
                        <strong>Vote Price:</strong>
                        <div id="selectedEventPrice" class="text-muted">-</div>
                    </div>
                    <div class="mb-2">
                        <strong>Categories:</strong>
                        <div id="selectedEventCategories" class="text-muted">Loading...</div>
                    </div>
                </div>
            </div>

            <!-- Tips -->
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-lightbulb me-2"></i>
                        Tips for Adding Contestants
                    </h6>
                    <ul class="small mb-0">
                        <li>Upload high-quality photos (JPG, PNG)</li>
                        <li>Write compelling bios to engage voters</li>
                        <li>Assign contestants to relevant categories</li>
                        <li>Shortcodes are auto-generated for voting</li>
                        <li>You can edit contestant details later</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-between">
                <a href="<?= ORGANIZER_URL ?>/contestants" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-success" id="submitBtn" disabled>
                    <i class="fas fa-save me-2"></i>Save Contestants
                </button>
            </div>
        </div>
    </div>
</form>

<script>
let contestants = [];
let eventCategories = [];
let selectedEvent = null;

function loadEventCategories() {
    const eventSelect = document.getElementById('event_id');
    const selectedEventId = eventSelect.value;
    const eventInfoCard = document.getElementById('eventInfoCard');
    const addBtn = document.getElementById('addContestantBtn');
    
    if (!selectedEventId) {
        eventInfoCard.style.display = 'none';
        addBtn.disabled = true;
        return;
    }
    
    // Show event info
    const selectedOption = eventSelect.options[eventSelect.selectedIndex];
    selectedEvent = {
        id: selectedEventId,
        name: selectedOption.text.split(' (')[0],
        vote_price: selectedOption.dataset.votePrice
    };
    
    document.getElementById('selectedEventName').textContent = selectedEvent.name;
    document.getElementById('selectedEventPrice').textContent = '$' + selectedEvent.vote_price;
    eventInfoCard.style.display = 'block';
    
    // Load categories for this event
    fetch(`<?= ORGANIZER_URL ?>/events/${selectedEventId}/categories`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                eventCategories = data.categories;
                document.getElementById('selectedEventCategories').innerHTML = 
                    eventCategories.map(cat => `<span class="badge bg-secondary me-1">${cat.name}</span>`).join('');
                addBtn.disabled = false;
            } else {
                document.getElementById('selectedEventCategories').textContent = 'No categories found';
                addBtn.disabled = true;
            }
        })
        .catch(error => {
            console.error('Error loading categories:', error);
            document.getElementById('selectedEventCategories').textContent = 'Error loading categories';
            addBtn.disabled = true;
        });
}

function addContestant() {
    if (eventCategories.length === 0) {
        alert('This event has no categories. Please add categories to the event first.');
        return;
    }
    
    const contestantName = prompt('Enter contestant name:');
    if (!contestantName) return;
    
    const contestant = {
        id: Date.now(),
        name: contestantName,
        bio: '',
        image_url: '',
        categories: []
    };
    
    contestants.push(contestant);
    renderContestants();
    updateSubmitButton();
}

function removeContestant(contestantId) {
    contestants = contestants.filter(c => c.id !== contestantId);
    renderContestants();
    updateSubmitButton();
}

function renderContestants() {
    const container = document.getElementById('contestantsContainer');
    const noMessage = document.getElementById('noContestantsMessage');
    
    if (contestants.length === 0) {
        noMessage.style.display = 'block';
        container.innerHTML = '';
        return;
    }
    
    noMessage.style.display = 'none';
    
    const html = contestants.map(contestant => {
        const categoryOptions = eventCategories.map(cat => {
            const isSelected = contestant.categories && contestant.categories.includes(cat.id);
            return `<option value="${cat.id}" ${isSelected ? 'selected' : ''}>${cat.name}</option>`;
        }).join('');
        
        return `
            <div class="contestant-item border rounded p-3 mb-3" data-contestant-id="${contestant.id}">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="mb-0">${contestant.name}</h6>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeContestant(${contestant.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                
                <input type="hidden" name="contestants[${contestant.id}][name]" value="${contestant.name}">
                
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label small">Bio</label>
                        <textarea class="form-control form-control-sm" 
                                  name="contestants[${contestant.id}][bio]" 
                                  placeholder="Contestant bio (optional)" 
                                  rows="3" 
                                  onchange="updateContestantBio(${contestant.id}, this.value)">${contestant.bio || ''}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Photo</label>
                        <input type="file" class="form-control form-control-sm" 
                               name="contestant_photo_${contestant.id}" 
                               accept="image/*"
                               onchange="previewContestantPhoto(${contestant.id}, this)">
                        <div class="form-text">JPG, PNG (max 2MB)</div>
                        
                        <div class="mt-2" id="photo-preview-${contestant.id}">
                            <div style="width: 60px; height: 60px; background-color: #f8f9fa; border: 1px dashed #dee2e6; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                <i class="fas fa-camera text-muted"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Categories *</label>
                        <select class="form-select form-select-sm" 
                                name="contestants[${contestant.id}][categories][]" 
                                multiple 
                                required
                                onchange="updateContestantCategories(${contestant.id}, this)">
                            ${categoryOptions}
                        </select>
                        <div class="form-text">Hold Ctrl/Cmd to select multiple</div>
                        
                        <div class="mt-2">
                            <small class="text-muted">Shortcodes will be auto-generated</small>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    container.innerHTML = html;
}

function updateContestantBio(contestantId, bio) {
    const contestant = contestants.find(c => c.id === contestantId);
    if (contestant) {
        contestant.bio = bio;
    }
}

function updateContestantCategories(contestantId, selectElement) {
    const contestant = contestants.find(c => c.id === contestantId);
    if (contestant) {
        contestant.categories = Array.from(selectElement.selectedOptions).map(option => option.value);
    }
}

function previewContestantPhoto(contestantId, input) {
    const previewContainer = document.getElementById(`photo-preview-${contestantId}`);
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            alert('File too large. Maximum size is 2MB.');
            input.value = '';
            return;
        }
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            previewContainer.innerHTML = `
                <img src="${e.target.result}" 
                     alt="Photo preview" 
                     style="width: 60px; height: 60px; object-fit: cover;" 
                     class="img-thumbnail">
                <div class="mt-1">
                    <small class="text-success">
                        <i class="fas fa-check me-1"></i>
                        ${file.name} (${(file.size / 1024).toFixed(1)} KB)
                    </small>
                </div>
            `;
        };
        reader.onerror = function() {
            alert('Error reading file. Please try again.');
            input.value = '';
        };
        reader.readAsDataURL(file);
    } else {
        // Reset to default state
        previewContainer.innerHTML = `
            <div style="width: 60px; height: 60px; background-color: #f8f9fa; border: 1px dashed #dee2e6; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                <i class="fas fa-camera text-muted"></i>
            </div>
        `;
    }
}

function updateSubmitButton() {
    const submitBtn = document.getElementById('submitBtn');
    const eventSelected = document.getElementById('event_id').value;
    
    submitBtn.disabled = !eventSelected || contestants.length === 0;
}

// Form submission
document.getElementById('contestantForm').addEventListener('submit', function(e) {
    if (contestants.length === 0) {
        e.preventDefault();
        alert('Please add at least one contestant.');
        return;
    }
    
    // Validate that all contestants have categories selected
    let hasErrors = false;
    contestants.forEach(contestant => {
        if (!contestant.categories || contestant.categories.length === 0) {
            hasErrors = true;
        }
    });
    
    if (hasErrors) {
        e.preventDefault();
        alert('Please assign categories to all contestants.');
        return;
    }
});
</script>

<style>
.contestant-item {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6 !important;
}

.contestant-item:hover {
    background-color: #e9ecef;
}

.form-select[multiple] {
    min-height: 80px;
}

.badge {
    font-size: 0.75em;
}

#eventInfoCard {
    position: sticky;
    top: 20px;
}
</style>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../../layout/organizer_layout.php';
?>
