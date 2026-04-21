<?php
// SMS Templates Management View Content
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">📝 SMS Templates</h1>
                    <p class="text-muted">Manage your bulk SMS message templates</p>
                </div>
                <div>
                    <a href="<?= SUPERADMIN_URL ?>/bulk-sms" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Bulk SMS
                    </a>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#templateModal">
                        <i class="fas fa-plus"></i> New Template
                    </button>
                </div>
            </div>

            <!-- Templates Grid -->
            <div class="row">
                <?php if (empty($templates)): ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                <h5>No Templates Found</h5>
                                <p class="text-muted">Create your first SMS template to get started with bulk messaging.</p>
                                <button class="btn btn-primary" data-toggle="modal" data-target="#templateModal">
                                    <i class="fas fa-plus"></i> Create First Template
                                </button>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php 
                    $templateTypes = [];
                    foreach ($templates as $template) {
                        $templateTypes[$template['type']][] = $template;
                    }
                    ?>
                    
                    <?php foreach ($templateTypes as $type => $typeTemplates): ?>
                        <div class="col-12 mb-4">
                            <h5 class="mb-3">
                                <?php
                                $typeIcons = [
                                    'vote_confirmation' => 'fas fa-check-circle text-success',
                                    'event_reminder' => 'fas fa-bell text-warning',
                                    'payment_receipt' => 'fas fa-receipt text-info',
                                    'custom' => 'fas fa-edit text-primary'
                                ];
                                $icon = $typeIcons[$type] ?? 'fas fa-file-alt text-secondary';
                                ?>
                                <i class="<?= $icon ?>"></i>
                                <?= ucwords(str_replace('_', ' ', $type)) ?> Templates
                            </h5>
                            
                            <div class="row">
                                <?php foreach ($typeTemplates as $template): ?>
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card h-100">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0"><?= htmlspecialchars($template['name']) ?></h6>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                            data-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="#" 
                                                           onclick="editTemplate(<?= $template['id'] ?>)">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                        <a class="dropdown-item" href="#" 
                                                           onclick="previewTemplate(<?= $template['id'] ?>)">
                                                            <i class="fas fa-eye"></i> Preview
                                                        </a>
                                                        <a class="dropdown-item" href="#" 
                                                           onclick="duplicateTemplate(<?= $template['id'] ?>)">
                                                            <i class="fas fa-copy"></i> Duplicate
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           onclick="deleteTemplate(<?= $template['id'] ?>)">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="template-preview mb-3" style="max-height: 120px; overflow-y: auto;">
                                                    <small class="text-muted">
                                                        <?= nl2br(htmlspecialchars(substr($template['template'], 0, 200))) ?>
                                                        <?= strlen($template['template']) > 200 ? '...' : '' ?>
                                                    </small>
                                                </div>
                                                
                                                <?php if (!empty($template['variables'])): ?>
                                                    <?php $variables = json_decode($template['variables'], true) ?: []; ?>
                                                    <div class="mb-2">
                                                        <small class="text-muted">
                                                            <strong>Variables:</strong>
                                                            <?php foreach (array_slice($variables, 0, 3) as $var): ?>
                                                                <span class="badge badge-light">{<?= $var ?>}</span>
                                                            <?php endforeach; ?>
                                                            <?php if (count($variables) > 3): ?>
                                                                <span class="text-muted">+<?= count($variables) - 3 ?> more</span>
                                                            <?php endif; ?>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-footer bg-light">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <?= date('M j, Y', strtotime($template['created_at'])) ?>
                                                    </small>
                                                    <div>
                                                        <span class="badge badge-<?= $template['is_active'] ? 'success' : 'secondary' ?>">
                                                            <?= $template['is_active'] ? 'Active' : 'Inactive' ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Template Modal -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="templateForm">
                <div class="modal-header">
                    <h5 class="modal-title">📝 <span id="modalTitle">Create SMS Template</span></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="template_id" id="templateId">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Template Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="templateName" class="form-control" 
                                       placeholder="e.g., Top Performer Alert" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Template Type</label>
                                <select name="type" id="templateType" class="form-control">
                                    <option value="custom">Custom</option>
                                    <option value="vote_confirmation">Vote Confirmation</option>
                                    <option value="event_reminder">Event Reminder</option>
                                    <option value="payment_receipt">Payment Receipt</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Message Template <span class="text-danger">*</span></label>
                        <textarea name="template" id="templateContent" class="form-control" rows="6" 
                                  placeholder="Enter your message template here..." required></textarea>
                        <small class="text-muted">
                            Use variables like {event_name}, {nominee_name}, {vote_count}, etc. 
                            <a href="#" onclick="showVariableHelp()">View all variables</a>
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label>Available Variables</label>
                        <div id="variablesList">
                            <div class="row" id="variablesGrid">
                                <!-- Variables will be populated here -->
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addVariable()">
                            <i class="fas fa-plus"></i> Add Variable
                        </button>
                    </div>
                    
                    <div class="form-group">
                        <label>Preview</label>
                        <div id="templatePreview" class="alert alert-light">
                            <em>Template preview will appear here...</em>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">👁️ Template Preview</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="previewContent">
                    <!-- Preview content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.template-preview {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.25rem;
    padding: 0.75rem;
    font-family: monospace;
    font-size: 0.875rem;
}

.card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: box-shadow 0.2s ease;
}

.variable-tag {
    cursor: pointer;
    margin: 2px;
}

.variable-tag:hover {
    background-color: #007bff !important;
    color: white !important;
}
</style>

<script>
const SUPERADMIN_URL = '<?= SUPERADMIN_URL ?>';

// Common variables for SMS templates
const commonVariables = {
    'event': ['event_name', 'start_date', 'end_date', 'voting_url'],
    'nominee': ['nominee_name', 'category_name', 'vote_count', 'current_position', 'percentage'],
    'voter': ['voter_name', 'total_votes_cast', 'total_amount_spent', 'favorite_nominee', 'last_vote_date'],
    'transaction': ['amount', 'receipt_number', 'transaction_id'],
    'general': ['date', 'time', 'app_name']
};

$(document).ready(function() {
    initializeTemplateForm();
    loadVariables();
});

function initializeTemplateForm() {
    // Template form submission
    $('#templateForm').submit(function(e) {
        e.preventDefault();
        saveTemplate();
    });
    
    // Template content change - update preview
    $('#templateContent').on('input', function() {
        updatePreview();
    });
    
    // Template type change - update suggested variables
    $('#templateType').change(function() {
        updateSuggestedVariables();
    });
    
    // Modal reset on close
    $('#templateModal').on('hidden.bs.modal', function() {
        resetTemplateForm();
    });
}

function loadVariables() {
    let html = '';
    Object.entries(commonVariables).forEach(([category, variables]) => {
        html += `<div class="col-md-6 mb-2">`;
        html += `<h6 class="text-muted">${category.charAt(0).toUpperCase() + category.slice(1)}</h6>`;
        variables.forEach(variable => {
            html += `<span class="badge badge-light variable-tag mr-1" onclick="insertVariable('${variable}')">{${variable}}</span>`;
        });
        html += `</div>`;
    });
    $('#variablesGrid').html(html);
}

function insertVariable(variable) {
    const textarea = document.getElementById('templateContent');
    const cursorPos = textarea.selectionStart;
    const textBefore = textarea.value.substring(0, cursorPos);
    const textAfter = textarea.value.substring(cursorPos);
    
    textarea.value = textBefore + `{${variable}}` + textAfter;
    textarea.focus();
    textarea.setSelectionRange(cursorPos + variable.length + 2, cursorPos + variable.length + 2);
    
    updatePreview();
}

function updatePreview() {
    const content = $('#templateContent').val();
    if (!content.trim()) {
        $('#templatePreview').html('<em>Template preview will appear here...</em>');
        return;
    }
    
    // Replace variables with sample data for preview
    let preview = content;
    const sampleData = {
        'event_name': 'Ghana Music Awards 2024',
        'nominee_name': 'John Doe',
        'category_name': 'Best Actor',
        'vote_count': '150',
        'amount': 'GH₵25.50',
        'receipt_number': 'ABC12345',
        'voter_name': 'Jane Smith',
        'date': new Date().toLocaleDateString(),
        'time': new Date().toLocaleTimeString()
    };
    
    Object.entries(sampleData).forEach(([key, value]) => {
        preview = preview.replace(new RegExp(`{${key}}`, 'g'), value);
    });
    
    $('#templatePreview').html(preview.replace(/\n/g, '<br>'));
}

function updateSuggestedVariables() {
    const type = $('#templateType').val();
    // Highlight relevant variables based on template type
    $('.variable-tag').removeClass('badge-primary').addClass('badge-light');
    
    let suggestedVars = [];
    switch(type) {
        case 'vote_confirmation':
            suggestedVars = ['event_name', 'nominee_name', 'category_name', 'vote_count', 'amount', 'receipt_number'];
            break;
        case 'event_reminder':
            suggestedVars = ['event_name', 'start_date', 'end_date', 'voting_url'];
            break;
        case 'payment_receipt':
            suggestedVars = ['amount', 'receipt_number', 'transaction_id', 'date'];
            break;
    }
    
    suggestedVars.forEach(variable => {
        $(`.variable-tag:contains('{${variable}}')`).removeClass('badge-light').addClass('badge-primary');
    });
}

function editTemplate(templateId) {
    // Load template data and populate form
    $.get(`${SUPERADMIN_URL}/bulk-sms/templates/${templateId}`)
    .done(function(template) {
        $('#templateId').val(template.id);
        $('#templateName').val(template.name);
        $('#templateType').val(template.type);
        $('#templateContent').val(template.template);
        $('#modalTitle').text('Edit SMS Template');
        updatePreview();
        $('#templateModal').modal('show');
    })
    .fail(function() {
        alert('Failed to load template');
    });
}

function previewTemplate(templateId) {
    // Load template preview
    $.get(`${SUPERADMIN_URL}/bulk-sms/template-preview`, {
        template_id: templateId,
        event_id: 1 // Use a default event for preview
    })
    .done(function(response) {
        if (response.success) {
            $('#previewContent').html(`
                <h6>${response.template.name}</h6>
                <div class="template-preview">
                    ${response.preview_message.replace(/\n/g, '<br>')}
                </div>
                <small class="text-muted mt-2 d-block">
                    <strong>Variables used:</strong> ${response.available_variables.join(', ')}
                </small>
            `);
            $('#previewModal').modal('show');
        }
    })
    .fail(function() {
        alert('Failed to load template preview');
    });
}

function duplicateTemplate(templateId) {
    if (confirm('Create a copy of this template?')) {
        // Implementation for duplicating template
        alert('Duplicate feature coming soon!');
    }
}

function deleteTemplate(templateId) {
    if (confirm('Are you sure you want to delete this template? This action cannot be undone.')) {
        $.ajax({
            url: `${SUPERADMIN_URL}/bulk-sms/templates/${templateId}`,
            method: 'DELETE',
            success: function() {
                location.reload();
            },
            error: function() {
                alert('Failed to delete template');
            }
        });
    }
}

function saveTemplate() {
    const formData = new FormData($('#templateForm')[0]);
    
    // Collect variables from the template content
    const content = $('#templateContent').val();
    const variables = [];
    const variableRegex = /{([^}]+)}/g;
    let match;
    while ((match = variableRegex.exec(content)) !== null) {
        if (!variables.includes(match[1])) {
            variables.push(match[1]);
        }
    }
    formData.append('variables', JSON.stringify(variables));
    
    $.ajax({
        url: `${SUPERADMIN_URL}/bulk-sms/templates/save`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#templateModal').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Failed to save template');
        }
    });
}

function resetTemplateForm() {
    $('#templateForm')[0].reset();
    $('#templateId').val('');
    $('#modalTitle').text('Create SMS Template');
    $('#templatePreview').html('<em>Template preview will appear here...</em>');
    $('.variable-tag').removeClass('badge-primary').addClass('badge-light');
}

function addVariable() {
    const variable = prompt('Enter variable name (without curly braces):');
    if (variable && variable.trim()) {
        insertVariable(variable.trim());
    }
}

function showVariableHelp() {
    alert('Common Variables:\n\n' +
          'Event: {event_name}, {start_date}, {end_date}, {voting_url}\n' +
          'Nominee: {nominee_name}, {category_name}, {vote_count}, {current_position}\n' +
          'Voter: {voter_name}, {total_votes_cast}, {total_amount_spent}\n' +
          'Transaction: {amount}, {receipt_number}, {transaction_id}\n' +
          'General: {date}, {time}, {app_name}');
}
</script>
