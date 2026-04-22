<!-- Draft Events Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-file-alt me-2"></i>
            Draft Events
        </h2>
        <p class="text-muted mb-0">Events that are not yet published or active</p>
    </div>
    <div>
        <a href="<?= ORGANIZER_URL ?>/events/wizard" class="btn btn-primary">
            <i class="fas fa-magic me-2"></i>Create Event
        </a>
    </div>
</div>

<!-- Draft Events List -->
<?php if (!empty($events)): ?>
    <div class="row">
        <?php foreach ($events as $event): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <?php if (!empty($event['featured_image'])): ?>
                        <img src="<?= htmlspecialchars(image_url($event['featured_image'])) ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-gradient-warning d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-file-alt fa-3x text-white opacity-50"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0"><?= htmlspecialchars($event['name']) ?></h5>
                            <span class="badge bg-warning">Draft</span>
                        </div>
                        
                        <p class="card-text text-muted small mb-2">
                            <i class="fas fa-code me-1"></i>
                            Code: <strong><?= htmlspecialchars($event['code']) ?></strong>
                        </p>
                        
                        <?php if (!empty($event['description'])): ?>
                            <p class="card-text small">
                                <?= htmlspecialchars(substr($event['description'], 0, 100)) ?>
                                <?= strlen($event['description']) > 100 ? '...' : '' ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="small text-muted mb-3">
                            <i class="fas fa-calendar me-1"></i>
                            Created: <?= date('M j, Y', strtotime($event['created_at'])) ?>
                        </div>
                        
                        <div class="small text-muted mb-3">
                            <i class="fas fa-clock me-1"></i>
                            Scheduled: <?= date('M j, Y', strtotime($event['start_date'])) ?> - 
                            <?= date('M j, Y', strtotime($event['end_date'])) ?>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent">
                        <div class="btn-group w-100" role="group">
                            <a href="<?= ORGANIZER_URL ?>/events/wizard?edit=<?= $event['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-magic me-1"></i>Continue
                            </a>
                            <?php 
                            $currentEventCount = $tenantLimits['current_events'] ?? 0;
                            $maxEvents = $tenantLimits['max_events'];
                            $canPublish = is_null($maxEvents) || $currentEventCount < $maxEvents;
                            ?>
                            <?php if ($canPublish): ?>
                                <button class="btn btn-outline-success btn-sm" onclick="publishEvent(<?= $event['id'] ?>)">
                                    <i class="fas fa-play me-1"></i>Publish
                                </button>
                            <?php else: ?>
                                <a href="<?= ORGANIZER_URL ?>/switch-plan" class="btn btn-warning btn-sm" 
                                   title="Upgrade plan to publish events (<?= $currentEventCount ?>/<?= $maxEvents ?> limit reached)">
                                    <i class="fas fa-crown me-1"></i>Upgrade
                                </a>
                            <?php endif; ?>
                            <button class="btn btn-outline-danger btn-sm" onclick="deleteEvent(<?= $event['id'] ?>)">
                                <i class="fas fa-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <!-- Empty State -->
    <div class="text-center py-5">
        <div class="mb-4">
            <i class="fas fa-file-alt fa-4x text-muted opacity-50"></i>
        </div>
        <h4 class="text-muted">No Draft Events</h4>
        <p class="text-muted mb-4">You don't have any draft events at the moment</p>
        <a href="<?= ORGANIZER_URL ?>/events/wizard" class="btn btn-primary">
            <i class="fas fa-magic me-2"></i>Create Your First Event
        </a>
    </div>
<?php endif; ?>

<script>
function publishEvent(eventId) {
    if (confirm('Are you sure you want to publish this event? It will become visible to voters.')) {
        fetch(`<?= ORGANIZER_URL ?>/events/${eventId}/publish`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error publishing event: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error publishing event');
        });
    }
}

function deleteEvent(eventId) {
    if (confirm('Are you sure you want to delete this draft event? This action cannot be undone.')) {
        fetch(`<?= ORGANIZER_URL ?>/events/${eventId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting event: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting event');
        });
    }
}
</script>
