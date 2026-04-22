<!-- Vote Bundles Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-box me-2"></i>
            Vote Bundles
        </h2>
        <p class="text-muted mb-0">Manage vote packages and pricing tiers for your events</p>
    </div>
    <div>
        <button class="btn btn-primary" data-coreui-toggle="modal" data-coreui-target="#createBundleModal">
            <i class="fas fa-plus me-2"></i>Create Bundle
        </button>
    </div>
</div>

<!-- Bundle Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $bundleStats['active_bundles'] ?></div>
                    <div>Active Bundles</div>
                    <div class="small">Available for purchase</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-box fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card success text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $bundleStats['bundles_sold'] ?></div>
                    <div>Bundles Sold</div>
                    <div class="small">All time</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card warning text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">GH₵<?= number_format($bundleStats['total_revenue'], 2) ?></div>
                    <div>Bundle Revenue</div>
                    <div class="small">From all bundles</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-money-bill fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card info text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= count($events) ?></div>
                    <div>Events</div>
                    <div class="small">With bundles</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-calendar fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bundle Grid -->
<?php if (empty($eventBundles)): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-box fa-3x text-muted mb-3"></i>
                <h4>No Vote Bundles Found</h4>
                <p class="text-muted mb-4">You haven't created any vote bundles yet. Create your first bundle to start offering vote packages to your audience.</p>
                <button class="btn btn-primary" data-coreui-toggle="modal" data-coreui-target="#createBundleModal">
                    <i class="fas fa-plus me-2"></i>Create Your First Bundle
                </button>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Events with Bundles -->
<?php foreach ($eventBundles as $eventId => $eventData): ?>
<div class="mb-4">
    <h4 class="mb-3">
        <i class="fas fa-calendar me-2"></i>
        <?= htmlspecialchars($eventData['event']['name']) ?>
        <small class="text-muted">(<?= count($eventData['bundles']) ?> bundles)</small>
    </h4>
    
    <div class="row">
        <?php foreach ($eventData['bundles'] as $bundle): 
            $pricePerVote = $bundle['votes'] > 0 ? $bundle['price'] / $bundle['votes'] : 0;
            $cardClass = $bundle['active'] ? '' : 'opacity-75';
            $headerClass = $bundle['active'] ? 'bg-primary' : 'bg-secondary';
            $badgeClass = $bundle['active'] ? 'bg-success' : 'bg-secondary';
            $badgeText = $bundle['active'] ? 'Active' : 'Inactive';
        ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 bundle-card <?= $cardClass ?>">
                <div class="card-header <?= $headerClass ?> text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= htmlspecialchars($bundle['name']) ?></h5>
                        <span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                    </div>
                </div>
                <div class="card-body text-center">
                    <div class="bundle-price mb-3">
                        <div class="fs-1 fw-bold">GH₵<?= number_format($bundle['price'], 2) ?></div>
                        <div class="text-muted">GH₵<?= number_format($pricePerVote, 2) ?> per vote</div>
                    </div>
                    
                    <div class="bundle-features mb-4">
                        <div class="fs-4 fw-semibold text-primary"><?= $bundle['votes'] ?> Vote<?= $bundle['votes'] > 1 ? 's' : '' ?></div>
                        <div class="small text-muted">Event: <?= htmlspecialchars($eventData['event']['name']) ?></div>
                    </div>
                    
                    <div class="bundle-stats">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="fw-semibold">0</div>
                                <div class="small text-muted">Sold</div>
                            </div>
                            <div class="col-6">
                                <div class="fw-semibold">GH₵0.00</div>
                                <div class="small text-muted">Revenue</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="btn-group w-100">
                        <button class="btn btn-outline-primary btn-sm" onclick="editBundle(<?= $bundle['id'] ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="viewStats(<?= $bundle['id'] ?>)">
                            <i class="fas fa-chart-bar"></i>
                        </button>
                        <button class="btn btn-outline-<?= $bundle['active'] ? 'warning' : 'success' ?> btn-sm" 
                                onclick="toggleBundle(<?= $bundle['id'] ?>, <?= $bundle['active'] ? 'false' : 'true' ?>)">
                            <i class="fas fa-<?= $bundle['active'] ? 'eye-slash' : 'eye' ?>"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="deleteBundle(<?= $bundle['id'] ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<!-- Create Bundle Modal -->
<div class="modal fade" id="createBundleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Bundle</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createBundleForm" method="POST" action="<?= ORGANIZER_URL ?>/financial/bundles/create">
                    <div class="mb-3">
                        <label class="form-label">Event *</label>
                        <select class="form-select" name="event_id" required>
                            <option value="">Select an event</option>
                            <?php foreach ($events as $event): ?>
                                <option value="<?= $event['id'] ?>"><?= htmlspecialchars($event['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Bundle Name *</label>
                                <input type="text" class="form-control" name="name" placeholder="e.g., Vote Pack (10)" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Number of Votes *</label>
                                <input type="number" class="form-control" name="votes" min="1" max="10000" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Price *</label>
                        <div class="input-group">
                            <span class="input-group-text">GH₵</span>
                            <input type="number" class="form-control" name="price" step="0.01" min="0.01" required>
                        </div>
                        <div class="form-text">Total price for the entire bundle</div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="active" checked>
                        <label class="form-check-label">
                            Active (available for purchase)
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" form="createBundleForm">
                    <i class="fas fa-save me-2"></i>Create Bundle
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Bundle Modal -->
<div class="modal fade" id="editBundleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Bundle</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editBundleForm" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Event</label>
                        <input type="text" class="form-control" id="edit-event-name" readonly>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Bundle Name *</label>
                                <input type="text" class="form-control" name="name" id="edit-name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Number of Votes *</label>
                                <input type="number" class="form-control" name="votes" id="edit-votes" min="1" max="10000" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Price *</label>
                        <div class="input-group">
                            <span class="input-group-text">GH₵</span>
                            <input type="number" class="form-control" name="price" id="edit-price" step="0.01" min="0.01" required>
                        </div>
                        <div class="form-text">Total price for the entire bundle</div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="active" id="edit-active">
                        <label class="form-check-label">
                            Active (available for purchase)
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" form="editBundleForm">
                    <i class="fas fa-save me-2"></i>Update Bundle
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Bundle data for edit functionality
const bundleData = <?= json_encode($eventBundles) ?>;
const eventData = <?= json_encode(array_column($events, null, 'id')) ?>;

function editBundle(id) {
    // Find bundle data
    let bundle = null;
    let eventName = '';
    
    for (const eventId in bundleData) {
        const eventBundles = bundleData[eventId].bundles;
        bundle = eventBundles.find(b => b.id == id);
        if (bundle) {
            eventName = bundleData[eventId].event.name;
            break;
        }
    }
    
    if (!bundle) {
        alert('Bundle not found');
        return;
    }
    
    // Populate edit form
    document.getElementById('edit-event-name').value = eventName;
    document.getElementById('edit-name').value = bundle.name;
    document.getElementById('edit-votes').value = bundle.votes;
    document.getElementById('edit-price').value = parseFloat(bundle.price).toFixed(2);
    document.getElementById('edit-active').checked = bundle.active == 1;
    
    // Set form action
    document.getElementById('editBundleForm').action = `<?= ORGANIZER_URL ?>/financial/bundles/${id}/update`;
    
    // Show modal
    const modal = new coreui.Modal(document.getElementById('editBundleModal'));
    modal.show();
}

function viewStats(id) {
    // Find bundle data
    let bundle = null;
    let eventName = '';
    
    for (const eventId in bundleData) {
        const eventBundles = bundleData[eventId].bundles;
        bundle = eventBundles.find(b => b.id == id);
        if (bundle) {
            eventName = bundleData[eventId].event.name;
            break;
        }
    }
    
    if (!bundle) {
        alert('Bundle not found');
        return;
    }
    
    // Create stats modal content
    const statsContent = `
        <div class="modal fade" id="statsModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Bundle Statistics</h5>
                        <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h6>${bundle.name}</h6>
                        <p class="text-muted">${eventName}</p>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h4 class="text-primary">${bundle.votes}</h4>
                                        <small>Votes per Bundle</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h4 class="text-success">GH₵${parseFloat(bundle.price).toFixed(2)}</h4>
                                        <small>Bundle Price</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-6">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h4 class="text-info">0</h4>
                                        <small>Times Sold</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h4 class="text-warning">GH₵0.00</h4>
                                        <small>Total Revenue</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                Price per vote: GH₵${(parseFloat(bundle.price) / parseInt(bundle.votes)).toFixed(2)}
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing stats modal if any
    const existingModal = document.getElementById('statsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add new modal to body
    document.body.insertAdjacentHTML('beforeend', statsContent);
    
    // Show modal
    const modal = new coreui.Modal(document.getElementById('statsModal'));
    modal.show();
}

function toggleBundle(id, activate) {
    const action = activate === 'true' ? 'activate' : 'deactivate';
    if (confirm(`Are you sure you want to ${action} this bundle?`)) {
        // Create a form to submit the toggle request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `<?= ORGANIZER_URL ?>/financial/bundles/${id}/update`;
        
        const activeInput = document.createElement('input');
        activeInput.type = 'hidden';
        activeInput.name = 'active';
        activeInput.value = activate === 'true' ? '1' : '0';
        
        form.appendChild(activeInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteBundle(id) {
    if (confirm('Are you sure you want to delete this bundle? This action cannot be undone.')) {
        // Create a form to submit the delete request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `<?= ORGANIZER_URL ?>/financial/bundles/${id}/delete`;
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
