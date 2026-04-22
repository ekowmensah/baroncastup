<?php
// Bulk SMS Management View Content
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">📱 Bulk SMS Management</h1>
                <div>
                    <a href="<?= SUPERADMIN_URL ?>/bulk-sms/templates" class="btn btn-outline-primary">
                        <i class="fas fa-file-alt"></i> Manage Templates
                    </a>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title"><?= count($events) ?></h4>
                                    <p class="card-text">Active Events</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-calendar-alt fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title"><?= count($templates) ?></h4>
                                    <p class="card-text">SMS Templates</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-file-alt fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title"><?= count($recent_campaigns) ?></h4>
                                    <p class="card-text">Recent Campaigns</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-paper-plane fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">0</h4>
                                    <p class="card-text">Pending Campaigns</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event Selection -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">📋 Select Event for Bulk SMS Campaign</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($events)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            No active events found. Please create an event first.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($events as $event): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card h-100 event-card" style="cursor: pointer;" 
                                         onclick="selectEvent(<?= $event['id'] ?>, '<?= htmlspecialchars($event['name']) ?>')">
                                        <div class="card-body">
                                            <h6 class="card-title"><?= htmlspecialchars($event['name']) ?></h6>
                                            <p class="card-text text-muted small">
                                                <?= date('M j, Y', strtotime($event['start_date'])) ?> - 
                                                <?= date('M j, Y', strtotime($event['end_date'])) ?>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge badge-<?= $event['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                    <?= ucfirst($event['status']) ?>
                                                </span>
                                                <i class="fas fa-arrow-right text-primary"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SMS Templates Overview -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">📝 Available SMS Templates</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($templates)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            No SMS templates found. <a href="<?= SUPERADMIN_URL ?>/bulk-sms/templates">Create templates</a> to get started.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php 
                            $templateTypes = [];
                            foreach ($templates as $template) {
                                $templateTypes[$template['type']][] = $template;
                            }
                            ?>
                            <?php foreach ($templateTypes as $type => $typeTemplates): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">
                                                <?php
                                                $typeIcons = [
                                                    'vote_confirmation' => 'fas fa-check-circle',
                                                    'event_reminder' => 'fas fa-bell',
                                                    'custom' => 'fas fa-edit'
                                                ];
                                                $icon = $typeIcons[$type] ?? 'fas fa-file-alt';
                                                ?>
                                                <i class="<?= $icon ?>"></i>
                                                <?= ucwords(str_replace('_', ' ', $type)) ?>
                                            </h6>
                                        </div>
                                        <div class="card-body p-2">
                                            <?php foreach ($typeTemplates as $template): ?>
                                                <div class="small mb-1">
                                                    <i class="fas fa-file-alt text-muted"></i>
                                                    <?= htmlspecialchars($template['name']) ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Campaigns -->
            <?php if (!empty($recent_campaigns)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">📊 Recent Campaigns</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Campaign</th>
                                    <th>Event</th>
                                    <th>Recipients</th>
                                    <th>Success Rate</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_campaigns as $campaign): ?>
                                <tr>
                                    <td><?= htmlspecialchars($campaign['name']) ?></td>
                                    <td><?= htmlspecialchars($campaign['event_name']) ?></td>
                                    <td><?= number_format($campaign['total_recipients']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $campaign['success_rate'] > 90 ? 'success' : ($campaign['success_rate'] > 70 ? 'warning' : 'danger') ?>">
                                            <?= $campaign['success_rate'] ?>%
                                        </span>
                                    </td>
                                    <td><?= date('M j, Y H:i', strtotime($campaign['created_at'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewCampaignDetails(<?= $campaign['id'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.event-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

.card {
    border: 1px solid #e3e6f0;
    border-radius: 0.35rem;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}
</style>

<script>
function selectEvent(eventId, eventName) {
    if (confirm(`Start bulk SMS campaign for "${eventName}"?`)) {
        window.location.href = `<?= SUPERADMIN_URL ?>/bulk-sms/compose?event_id=${eventId}`;
    }
}

function viewCampaignDetails(campaignId) {
    // Implement campaign details view
    alert('Campaign details feature coming soon!');
}

// Initialize tooltips
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
