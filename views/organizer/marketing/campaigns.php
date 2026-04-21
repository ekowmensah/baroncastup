<!-- Marketing Campaigns Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-envelope me-2"></i>
            Marketing Campaigns
        </h2>
        <p class="text-muted mb-0">Create and manage email marketing campaigns for your events</p>
    </div>
    <div>
        <button class="btn btn-primary" data-coreui-toggle="modal" data-coreui-target="#createCampaignModal">
            <i class="fas fa-plus me-2"></i>Create Campaign
        </button>
    </div>
</div>

<!-- Campaign Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $campaignStats['active_campaigns'] ?? 0 ?></div>
                    <div>Active Campaigns</div>
                    <div class="small">Currently running</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-paper-plane fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card success text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($campaignStats['emails_sent'] ?? 0) ?></div>
                    <div>Emails Sent</div>
                    <div class="small">This month</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-envelope fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card warning text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($campaignStats['open_rate'] ?? 0, 1) ?>%</div>
                    <div>Open Rate</div>
                    <div class="small">Above average</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-envelope-open fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card info text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($campaignStats['click_rate'] ?? 0, 1) ?>%</div>
                    <div>Click Rate</div>
                    <div class="small">Excellent performance</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-mouse-pointer fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Campaign Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="paused">Paused</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="typeFilter">
                            <option value="">All Types</option>
                            <option value="promotional">Promotional</option>
                            <option value="event_reminder">Event Reminder</option>
                            <option value="newsletter">Newsletter</option>
                            <option value="follow_up">Follow-up</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm" placeholder="Search campaigns..." id="searchCampaigns">
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-1">
                            <button class="btn btn-primary btn-sm flex-fill" onclick="applyFilters()">
                                <i class="fas fa-filter"></i>
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="resetFilters()">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Campaigns List -->
<div class="row">
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 campaign-card">
            <div class="card-header bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Event Launch Announcement</h6>
                    <span class="badge bg-light text-success">Active</span>
                </div>
            </div>
            <div class="card-body">
                <div class="campaign-type mb-2">
                    <span class="badge bg-primary">Promotional</span>
                </div>
                
                <p class="card-text small">
                    Announcing the launch of our new Beauty Contest 2024 event with early bird pricing.
                </p>
                
                <div class="campaign-stats mb-3">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="fw-semibold">2,456</div>
                            <div class="small text-muted">Sent</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-semibold">612</div>
                            <div class="small text-muted">Opens</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-semibold">89</div>
                            <div class="small text-muted">Clicks</div>
                        </div>
                    </div>
                </div>
                
                <div class="campaign-performance mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Open Rate</span>
                        <span class="small fw-semibold">24.9%</span>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: 24.9%"></div>
                    </div>
                </div>
                
                <div class="small text-muted">
                    <i class="fas fa-calendar me-1"></i>
                    Launched: Nov 10, 2024
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <div class="btn-group w-100" role="group">
                    <button class="btn btn-outline-primary btn-sm" onclick="viewCampaign(1)">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="editCampaign(1)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-outline-warning btn-sm" onclick="pauseCampaign(1)">
                        <i class="fas fa-pause"></i>
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="duplicateCampaign(1)">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 campaign-card">
            <div class="card-header bg-warning text-dark">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Voting Reminder</h6>
                    <span class="badge bg-dark text-warning">Scheduled</span>
                </div>
            </div>
            <div class="card-body">
                <div class="campaign-type mb-2">
                    <span class="badge bg-info">Event Reminder</span>
                </div>
                
                <p class="card-text small">
                    Reminder email for users to vote in the ongoing Beauty Contest 2024 event.
                </p>
                
                <div class="campaign-stats mb-3">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="fw-semibold">1,892</div>
                            <div class="small text-muted">Recipients</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-semibold">-</div>
                            <div class="small text-muted">Opens</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-semibold">-</div>
                            <div class="small text-muted">Clicks</div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-warning py-2">
                    <i class="fas fa-clock me-1"></i>
                    <small>Scheduled for Nov 15, 2024 at 6:00 PM</small>
                </div>
                
                <div class="small text-muted">
                    <i class="fas fa-calendar me-1"></i>
                    Created: Nov 12, 2024
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <div class="btn-group w-100" role="group">
                    <button class="btn btn-outline-primary btn-sm" onclick="viewCampaign(2)">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="editCampaign(2)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="sendNow(2)">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="cancelCampaign(2)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 campaign-card">
            <div class="card-header bg-secondary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Event Results Newsletter</h6>
                    <span class="badge bg-light text-secondary">Completed</span>
                </div>
            </div>
            <div class="card-body">
                <div class="campaign-type mb-2">
                    <span class="badge bg-success">Newsletter</span>
                </div>
                
                <p class="card-text small">
                    Newsletter announcing the results of the completed Talent Show 2024 event.
                </p>
                
                <div class="campaign-stats mb-3">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="fw-semibold">3,124</div>
                            <div class="small text-muted">Sent</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-semibold">892</div>
                            <div class="small text-muted">Opens</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-semibold">156</div>
                            <div class="small text-muted">Clicks</div>
                        </div>
                    </div>
                </div>
                
                <div class="campaign-performance mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Open Rate</span>
                        <span class="small fw-semibold">28.6%</span>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: 28.6%"></div>
                    </div>
                </div>
                
                <div class="small text-muted">
                    <i class="fas fa-calendar me-1"></i>
                    Completed: Nov 8, 2024
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <div class="btn-group w-100" role="group">
                    <button class="btn btn-outline-primary btn-sm" onclick="viewCampaign(3)">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="viewReport(3)">
                        <i class="fas fa-chart-bar"></i>
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="duplicateCampaign(3)">
                        <i class="fas fa-copy"></i>
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="archiveCampaign(3)">
                        <i class="fas fa-archive"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 campaign-card border-dashed">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="fas fa-plus fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">Create New Campaign</h6>
                <p class="text-muted small">Start a new email marketing campaign</p>
                <button class="btn btn-outline-primary" data-coreui-toggle="modal" data-coreui-target="#createCampaignModal">
                    <i class="fas fa-plus me-2"></i>Create Campaign
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create Campaign Modal -->
<div class="modal fade" id="createCampaignModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Campaign</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createCampaignForm">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Campaign Name *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Campaign Type *</label>
                                <select class="form-select" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="promotional">Promotional</option>
                                    <option value="event_reminder">Event Reminder</option>
                                    <option value="newsletter">Newsletter</option>
                                    <option value="follow_up">Follow-up</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Subject Line *</label>
                        <input type="text" class="form-control" name="subject" required>
                        <div class="form-text">Keep it under 50 characters for better open rates</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Target Audience *</label>
                                <select class="form-select" name="audience" required>
                                    <option value="">Select Audience</option>
                                    <option value="all_subscribers">All Subscribers</option>
                                    <option value="event_participants">Event Participants</option>
                                    <option value="inactive_users">Inactive Users</option>
                                    <option value="premium_users">Premium Users</option>
                                    <option value="custom">Custom Segment</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Related Event</label>
                                <select class="form-select" name="event_id">
                                    <option value="">No specific event</option>
                                    <option value="1">Beauty Contest 2024</option>
                                    <option value="2">Talent Show 2024</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email Template</label>
                        <select class="form-select" name="template" onchange="previewTemplate()">
                            <option value="">Choose Template</option>
                            <option value="promotional">Promotional Template</option>
                            <option value="reminder">Reminder Template</option>
                            <option value="newsletter">Newsletter Template</option>
                            <option value="custom">Custom Template</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email Content *</label>
                        <textarea class="form-control" name="content" rows="6" required placeholder="Write your email content here..."></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Send Option</label>
                                <select class="form-select" name="send_option" onchange="toggleSchedule()">
                                    <option value="now">Send Now</option>
                                    <option value="schedule">Schedule for Later</option>
                                    <option value="draft">Save as Draft</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3" id="scheduleDateTime" style="display: none;">
                                <label class="form-label">Schedule Date & Time</label>
                                <input type="datetime-local" class="form-control" name="scheduled_at">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="track_opens" checked>
                        <label class="form-check-label">
                            Track email opens
                        </label>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="track_clicks" checked>
                        <label class="form-check-label">
                            Track link clicks
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-outline-info" onclick="previewCampaign()">
                    <i class="fas fa-eye me-2"></i>Preview
                </button>
                <button type="button" class="btn btn-primary" onclick="saveCampaign()">
                    <i class="fas fa-save me-2"></i>Create Campaign
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.campaign-card {
    transition: transform 0.2s ease-in-out;
}

.campaign-card:hover {
    transform: translateY(-2px);
}

.border-dashed {
    border: 2px dashed #dee2e6 !important;
}

.border-dashed:hover {
    border-color: #007bff !important;
}
</style>

<script>
function applyFilters() {
    const statusFilter = document.getElementById('statusFilter').value;
    const typeFilter = document.getElementById('typeFilter').value;
    const searchTerm = document.getElementById('searchCampaigns').value;
    
    console.log('Applying filters:', { statusFilter, typeFilter, searchTerm });
    // Implementation for applying filters
}

function resetFilters() {
    document.getElementById('statusFilter').value = '';
    document.getElementById('typeFilter').value = '';
    document.getElementById('searchCampaigns').value = '';
    applyFilters();
}

function viewCampaign(id) {
    console.log('Viewing campaign:', id);
    // Implementation for viewing campaign details
}

function editCampaign(id) {
    console.log('Editing campaign:', id);
    // Implementation for editing campaign
}

function pauseCampaign(id) {
    if (confirm('Are you sure you want to pause this campaign?')) {
        console.log('Pausing campaign:', id);
        alert('Campaign paused successfully!');
    }
}

function duplicateCampaign(id) {
    if (confirm('Create a copy of this campaign?')) {
        console.log('Duplicating campaign:', id);
        alert('Campaign duplicated successfully!');
    }
}

function sendNow(id) {
    if (confirm('Send this campaign immediately to all recipients?')) {
        console.log('Sending campaign now:', id);
        alert('Campaign sent successfully!');
    }
}

function cancelCampaign(id) {
    if (confirm('Are you sure you want to cancel this scheduled campaign?')) {
        console.log('Cancelling campaign:', id);
        alert('Campaign cancelled successfully!');
    }
}

function viewReport(id) {
    console.log('Viewing report for campaign:', id);
    // Implementation for viewing campaign report
}

function archiveCampaign(id) {
    if (confirm('Archive this completed campaign?')) {
        console.log('Archiving campaign:', id);
        alert('Campaign archived successfully!');
    }
}

function toggleSchedule() {
    const sendOption = document.querySelector('select[name="send_option"]').value;
    const scheduleDiv = document.getElementById('scheduleDateTime');
    
    if (sendOption === 'schedule') {
        scheduleDiv.style.display = 'block';
        scheduleDiv.querySelector('input').required = true;
    } else {
        scheduleDiv.style.display = 'none';
        scheduleDiv.querySelector('input').required = false;
    }
}

function previewTemplate() {
    const template = document.querySelector('select[name="template"]').value;
    const contentTextarea = document.querySelector('textarea[name="content"]');
    
    let templateContent = '';
    
    switch (template) {
        case 'promotional':
            templateContent = `🎉 Exciting News About Our Latest Event!

Hi [Name],

We're thrilled to announce our newest voting event that you won't want to miss!

[Event Details]

Don't miss out on this amazing opportunity. Vote now and make your voice heard!

Best regards,
The SmartCast Team`;
            break;
        case 'reminder':
            templateContent = `⏰ Don't Forget to Vote!

Hi [Name],

This is a friendly reminder that voting is still open for [Event Name].

Your vote matters! Make sure to participate before the deadline.

Vote Now: [Vote Link]

Thanks for your participation!
The SmartCast Team`;
            break;
        case 'newsletter':
            templateContent = `📰 SmartCast Newsletter - [Month] Edition

Hi [Name],

Here's what's been happening in the SmartCast community:

• Event highlights
• Winner announcements
• Upcoming events
• Community spotlights

Stay connected with us for more updates!

Best regards,
The SmartCast Team`;
            break;
    }
    
    if (templateContent) {
        contentTextarea.value = templateContent;
    }
}

function previewCampaign() {
    console.log('Previewing campaign...');
    alert('Campaign preview functionality will be implemented soon!');
}

function saveCampaign() {
    const form = document.getElementById('createCampaignForm');
    const formData = new FormData(form);
    
    console.log('Saving campaign...');
    
    // Close modal
    const modal = coreui.Modal.getInstance(document.getElementById('createCampaignModal'));
    modal.hide();
    
    alert('Campaign created successfully!');
}
</script>
