<?php
// Bulk SMS Compose View Content
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">📱 Compose Bulk SMS</h1>
                    <p class="text-muted">Event: <strong><?= htmlspecialchars($event['name']) ?></strong></p>
                </div>
                <div>
                    <a href="<?= SUPERADMIN_URL ?>/bulk-sms" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <form id="bulkSmsForm">
                <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                
                <div class="row">
                    <!-- Left Column - Configuration -->
                    <div class="col-lg-8">
                        <!-- Step 1: Select Recipients -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <span class="badge badge-primary">1</span>
                                    Select Recipients
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Recipient Type</label>
                                            <select name="recipient_type" id="recipientType" class="form-control" required>
                                                <option value="">Select recipient type...</option>
                                                <option value="nominees">Nominees/Contestants</option>
                                                <option value="voters">Voters/Fans</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Target Group</label>
                                            <select name="group_type" id="groupType" class="form-control" required>
                                                <option value="">First select recipient type...</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Advanced Filters -->
                                <div id="advancedFilters" style="display: none;">
                                    <hr>
                                    <h6>Advanced Filters</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Category (Optional)</label>
                                                <select name="category_id" class="form-control">
                                                    <option value="">All Categories</option>
                                                    <?php foreach ($categories as $category): ?>
                                                        <option value="<?= $category['id'] ?>">
                                                            <?= htmlspecialchars($category['name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="voteFilters" style="display: none;">
                                            <div class="form-group">
                                                <label>Vote Range</label>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <input type="number" name="min_votes" class="form-control form-control-sm" placeholder="Min votes">
                                                    </div>
                                                    <div class="col-6">
                                                        <input type="number" name="max_votes" class="form-control form-control-sm" placeholder="Max votes">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="amountFilters" style="display: none;">
                                            <div class="form-group">
                                                <label>Amount Range (GH₵)</label>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <input type="number" name="min_amount" class="form-control form-control-sm" placeholder="Min amount" step="0.01">
                                                    </div>
                                                    <div class="col-6">
                                                        <input type="number" name="max_amount" class="form-control form-control-sm" placeholder="Max amount" step="0.01">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="dateFilters" style="display: none;">
                                            <div class="form-group">
                                                <label>Days Since Last Vote</label>
                                                <input type="number" name="days_since" class="form-control" placeholder="e.g., 7 for last week">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Limit Recipients</label>
                                                <input type="number" name="limit" class="form-control" placeholder="Max recipients" value="100">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button type="button" id="previewRecipientsBtn" class="btn btn-outline-primary" disabled>
                                        <i class="fas fa-eye"></i> Preview Recipients
                                    </button>
                                    <button type="button" onclick="$('#advancedFilters').toggle()" class="btn btn-outline-secondary">
                                        <i class="fas fa-filter"></i> Advanced Filters
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Compose Message -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <span class="badge badge-primary">2</span>
                                    Compose Message
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Message Type</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="useTemplate" name="message_type" value="template" class="custom-control-input" checked>
                                                <label class="custom-control-label" for="useTemplate">Use Template</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="customMessage" name="message_type" value="custom" class="custom-control-input">
                                                <label class="custom-control-label" for="customMessage">Custom Message</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Template Selection -->
                                <div id="templateSection">
                                    <div class="form-group">
                                        <label>Select Template</label>
                                        <select name="template_id" id="templateSelect" class="form-control">
                                            <option value="">Choose a template...</option>
                                            <?php foreach ($templates as $template): ?>
                                                <option value="<?= $template['id'] ?>" data-type="<?= $template['type'] ?>">
                                                    <?= htmlspecialchars($template['name']) ?> 
                                                    (<?= ucwords(str_replace('_', ' ', $template['type'])) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div id="templatePreview" class="alert alert-info" style="display: none;">
                                        <h6>Template Preview:</h6>
                                        <div id="templatePreviewContent"></div>
                                        <small class="text-muted">
                                            <strong>Available Variables:</strong> <span id="templateVariables"></span>
                                        </small>
                                    </div>
                                </div>

                                <!-- Custom Message -->
                                <div id="customMessageSection" style="display: none;">
                                    <div class="form-group">
                                        <label>Custom Message</label>
                                        <textarea name="custom_message" class="form-control" rows="4" 
                                                  placeholder="Enter your custom message here..."></textarea>
                                        <small class="text-muted">
                                            You can use variables like {event_name}, {nominee_name}, {vote_count}, etc.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Review & Send -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <span class="badge badge-primary">3</span>
                                    Review & Send
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="campaignSummary" class="alert alert-light">
                                    <h6>Campaign Summary</h6>
                                    <ul class="mb-0">
                                        <li><strong>Event:</strong> <?= htmlspecialchars($event['name']) ?></li>
                                        <li><strong>Recipients:</strong> <span id="summaryRecipients">Not selected</span></li>
                                        <li><strong>Message:</strong> <span id="summaryMessage">Not composed</span></li>
                                        <li><strong>Estimated Cost:</strong> <span id="estimatedCost">-</span></li>
                                    </ul>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" id="sendTestSms" class="btn btn-outline-warning" disabled>
                                        <i class="fas fa-paper-plane"></i> Send Test SMS
                                    </button>
                                    <button type="submit" id="sendBulkSms" class="btn btn-primary" disabled>
                                        <i class="fas fa-paper-plane"></i> Send Bulk SMS
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Preview & Stats -->
                    <div class="col-lg-4">
                        <!-- Recipient Preview -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">👥 Recipient Preview</h6>
                            </div>
                            <div class="card-body">
                                <div id="recipientStats" class="text-center text-muted">
                                    <i class="fas fa-users fa-3x mb-2"></i>
                                    <p>Select recipients to see preview</p>
                                </div>
                                <div id="recipientList" style="display: none; max-height: 300px; overflow-y: auto;">
                                    <!-- Recipients will be loaded here -->
                                </div>
                            </div>
                        </div>

                        <!-- Group Statistics -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">📊 Group Statistics</h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border-right">
                                            <h4 class="text-primary" id="totalNominees"><?= $groups['nominee_groups']['all_nominees']['count'] ?></h4>
                                            <small class="text-muted">Total Nominees</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-success" id="totalVoters"><?= $groups['voter_groups']['all_voters']['count'] ?></h4>
                                        <small class="text-muted">Total Voters</small>
                                    </div>
                                </div>
                                <hr>
                                <div id="groupBreakdown">
                                    <h6>Nominee Groups:</h6>
                                    <?php foreach ($groups['nominee_groups'] as $key => $group): ?>
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span><?= $group['name'] ?></span>
                                            <span class="badge badge-light"><?= $group['count'] ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <h6 class="mt-3">Voter Groups:</h6>
                                    <?php foreach ($groups['voter_groups'] as $key => $group): ?>
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span><?= $group['name'] ?></span>
                                            <span class="badge badge-light"><?= $group['count'] ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- SMS Cost Calculator -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">💰 Cost Estimate</h6>
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <h4 class="text-info" id="totalCost">GH₵0.00</h4>
                                    <small class="text-muted">Estimated total cost</small>
                                </div>
                                <hr>
                                <div class="small">
                                    <div class="d-flex justify-content-between">
                                        <span>Recipients:</span>
                                        <span id="costRecipients">0</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Cost per SMS:</span>
                                        <span>GH₵0.05</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Success Rate:</span>
                                        <span>~95%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Recipient Preview Modal -->
<div class="modal fade" id="recipientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">👥 Recipient Preview</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="recipientModalContent">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="proceedWithSending()">
                    Proceed with These Recipients
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const SUPERADMIN_URL = '<?= SUPERADMIN_URL ?>';
const EVENT_ID = <?= $event['id'] ?>;

// Group options for different recipient types
const groupOptions = {
    nominees: {
        'all': 'All Nominees',
        'top_performers': 'Top Performers',
        'low_performers': 'Low Performers', 
        'no_votes': 'No Votes Yet'
    },
    voters: {
        'all_voters': 'All Voters',
        'high_spenders': 'High Spenders',
        'frequent_voters': 'Frequent Voters',
        'recent_voters': 'Recent Voters',
        'inactive_voters': 'Inactive Voters'
    }
};

$(document).ready(function() {
    // Initialize form handlers
    initializeFormHandlers();
    
    // Load default templates
    createDefaultTemplates();
});

function initializeFormHandlers() {
    // Recipient type change
    $('#recipientType').change(function() {
        const recipientType = $(this).val();
        const groupSelect = $('#groupType');
        
        groupSelect.empty().append('<option value="">Select group...</option>');
        
        if (recipientType && groupOptions[recipientType]) {
            Object.entries(groupOptions[recipientType]).forEach(([key, value]) => {
                groupSelect.append(`<option value="${key}">${value}</option>`);
            });
        }
        
        updateFilterVisibility();
        updatePreviewButton();
    });
    
    // Group type change
    $('#groupType').change(function() {
        updateFilterVisibility();
        updatePreviewButton();
    });
    
    // Message type change
    $('input[name="message_type"]').change(function() {
        if ($(this).val() === 'template') {
            $('#templateSection').show();
            $('#customMessageSection').hide();
        } else {
            $('#templateSection').hide();
            $('#customMessageSection').show();
        }
        updateSendButton();
    });
    
    // Template selection change
    $('#templateSelect').change(function() {
        const templateId = $(this).val();
        if (templateId) {
            loadTemplatePreview(templateId);
        } else {
            $('#templatePreview').hide();
        }
        updateSendButton();
    });
    
    // Preview recipients button
    $('#previewRecipientsBtn').click(function() {
        previewRecipients();
    });
    
    // Form submission
    $('#bulkSmsForm').submit(function(e) {
        e.preventDefault();
        sendBulkSms();
    });
}

function updateFilterVisibility() {
    const recipientType = $('#recipientType').val();
    
    $('#voteFilters').toggle(recipientType === 'nominees');
    $('#amountFilters').toggle(recipientType === 'voters');
    $('#dateFilters').toggle(recipientType === 'voters');
}

function updatePreviewButton() {
    const recipientType = $('#recipientType').val();
    const groupType = $('#groupType').val();
    
    $('#previewRecipientsBtn').prop('disabled', !recipientType || !groupType);
}

function updateSendButton() {
    const messageType = $('input[name="message_type"]:checked').val();
    const templateSelected = $('#templateSelect').val();
    const customMessage = $('textarea[name="custom_message"]').val().trim();
    const recipientType = $('#recipientType').val();
    const groupType = $('#groupType').val();
    
    const messageReady = (messageType === 'template' && templateSelected) || 
                        (messageType === 'custom' && customMessage);
    const recipientsReady = recipientType && groupType;
    
    $('#sendBulkSms').prop('disabled', !messageReady || !recipientsReady);
    $('#sendTestSms').prop('disabled', !messageReady);
}

function loadTemplatePreview(templateId) {
    $.get(`${SUPERADMIN_URL}/bulk-sms/template-preview`, {
        template_id: templateId,
        event_id: EVENT_ID
    })
    .done(function(response) {
        if (response.success) {
            $('#templatePreviewContent').html(response.preview_message.replace(/\n/g, '<br>'));
            $('#templateVariables').text(response.available_variables.join(', '));
            $('#templatePreview').show();
        }
    })
    .fail(function() {
        alert('Failed to load template preview');
    });
}

function previewRecipients() {
    const formData = new FormData($('#bulkSmsForm')[0]);
    
    $.ajax({
        url: `${SUPERADMIN_URL}/bulk-sms/preview-recipients`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                displayRecipientPreview(response);
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Failed to preview recipients');
        }
    });
}

function displayRecipientPreview(data) {
    let html = `
        <div class="alert alert-info">
            <strong>Total Recipients:</strong> ${data.total_count} | 
            <strong>Can Send:</strong> ${data.can_send_count} | 
            <strong>No Phone:</strong> ${data.cannot_send_count}
        </div>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
    `;
    
    // Dynamic headers based on recipient type
    if (data.recipients.length > 0) {
        const firstRecipient = data.recipients[0];
        Object.keys(firstRecipient).forEach(key => {
            if (key !== 'can_send') {
                html += `<th>${key.replace('_', ' ').toUpperCase()}</th>`;
            }
        });
        html += '<th>STATUS</th>';
    }
    
    html += `
                    </tr>
                </thead>
                <tbody>
    `;
    
    data.recipients.forEach(recipient => {
        html += '<tr>';
        Object.entries(recipient).forEach(([key, value]) => {
            if (key !== 'can_send') {
                html += `<td>${value || '-'}</td>`;
            }
        });
        html += `<td><span class="badge badge-${recipient.can_send ? 'success' : 'warning'}">${recipient.can_send ? 'Ready' : 'No Phone'}</span></td>`;
        html += '</tr>';
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    $('#recipientModalContent').html(html);
    $('#recipientModal').modal('show');
    
    // Update cost estimate
    updateCostEstimate(data.can_send_count);
}

function updateCostEstimate(recipientCount) {
    const costPerSms = 0.05;
    const totalCost = recipientCount * costPerSms;
    
    $('#costRecipients').text(recipientCount);
    $('#totalCost').text(`GH₵${totalCost.toFixed(2)}`);
    $('#estimatedCost').text(`GH₵${totalCost.toFixed(2)}`);
}

function proceedWithSending() {
    $('#recipientModal').modal('hide');
    // Enable send button and update summary
    $('#sendBulkSms').prop('disabled', false);
}

function sendBulkSms() {
    if (!confirm('Are you sure you want to send this bulk SMS campaign?')) {
        return;
    }
    
    const submitBtn = $('#sendBulkSms');
    const originalText = submitBtn.html();
    
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');
    
    const formData = new FormData($('#bulkSmsForm')[0]);
    
    $.ajax({
        url: `${SUPERADMIN_URL}/bulk-sms/send`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                alert(`Campaign completed!\nTotal: ${response.result.summary.total_sent}\nSuccessful: ${response.result.summary.successful}\nFailed: ${response.result.summary.failed}\nSuccess Rate: ${response.result.summary.success_rate}%`);
                window.location.href = `${SUPERADMIN_URL}/bulk-sms`;
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Failed to send bulk SMS');
        },
        complete: function() {
            submitBtn.prop('disabled', false).html(originalText);
        }
    });
}

function createDefaultTemplates() {
    // This would create default templates if none exist
    // Implementation depends on your needs
}

// Update form validation on input changes
$('#bulkSmsForm input, #bulkSmsForm select, #bulkSmsForm textarea').on('input change', function() {
    updateSendButton();
});
</script>
