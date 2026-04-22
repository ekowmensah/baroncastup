<?php 
$content = ob_start(); 
?>

<!-- Payout Settings -->
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-cog me-2"></i>
                    Payout Settings
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" id="settingsForm">
                    <!-- Payout Frequency -->
                    <div class="mb-4">
                        <label class="form-label">Payout Frequency *</label>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <input type="radio" class="btn-check" name="frequency" id="manual" value="manual" 
                                       <?= $schedule['frequency'] === 'manual' ? 'checked' : '' ?> required>
                                <label class="btn btn-outline-secondary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3" for="manual">
                                    <i class="fas fa-hand-paper fa-2x mb-2"></i>
                                    <div class="fw-bold">Manual</div>
                                    <small class="text-muted">Request when needed</small>
                                </label>
                            </div>
                            <div class="col-md-3 mb-3">
                                <input type="radio" class="btn-check" name="frequency" id="daily" value="daily" 
                                       <?= $schedule['frequency'] === 'daily' ? 'checked' : '' ?> required>
                                <label class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3" for="daily">
                                    <i class="fas fa-calendar-day fa-2x mb-2"></i>
                                    <div class="fw-bold">Daily</div>
                                    <small class="text-muted">Every day</small>
                                </label>
                            </div>
                            <div class="col-md-3 mb-3">
                                <input type="radio" class="btn-check" name="frequency" id="weekly" value="weekly" 
                                       <?= $schedule['frequency'] === 'weekly' ? 'checked' : '' ?> required>
                                <label class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3" for="weekly">
                                    <i class="fas fa-calendar-week fa-2x mb-2"></i>
                                    <div class="fw-bold">Weekly</div>
                                    <small class="text-muted">Once per week</small>
                                </label>
                            </div>
                            <div class="col-md-3 mb-3">
                                <input type="radio" class="btn-check" name="frequency" id="monthly" value="monthly" 
                                       <?= $schedule['frequency'] === 'monthly' ? 'checked' : '' ?> required>
                                <label class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3" for="monthly">
                                    <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                                    <div class="fw-bold">Monthly</div>
                                    <small class="text-muted">Once per month</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Schedule Details -->
                    <div id="scheduleDetails" style="display: none;">
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-calendar-check me-2"></i>
                                    Schedule Configuration
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Minimum Payout Amount *</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" name="minimum_amount" 
                                                       value="<?= $schedule['minimum_amount'] ?>" 
                                                       step="0.01" min="1" max="1000" required>
                                            </div>
                                            <div class="form-text">Minimum balance required for automatic payout</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3" id="payoutDayContainer" style="display: none;">
                                            <label class="form-label" id="payoutDayLabel">Payout Day</label>
                                            <select class="form-select" name="payout_day" id="payoutDaySelect">
                                                <!-- Options will be populated by JavaScript -->
                                            </select>
                                            <div class="form-text" id="payoutDayHelp"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="auto_payout_enabled" 
                                           id="auto_payout_enabled" <?= $schedule['auto_payout_enabled'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="auto_payout_enabled">
                                        <strong>Enable Automatic Payouts</strong>
                                    </label>
                                    <div class="form-text">
                                        When enabled, payouts will be processed automatically based on your schedule
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Instant Payout Settings -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-bolt me-2"></i>
                                Instant Payout Settings
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Instant Payout Threshold</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" name="instant_payout_threshold" 
                                                   value="<?= $schedule['instant_payout_threshold'] ?>" 
                                                   step="0.01" min="100" max="10000">
                                        </div>
                                        <div class="form-text">
                                            Automatically trigger payout when balance reaches this amount
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Next Scheduled Payout</label>
                                        <input type="text" class="form-control" 
                                               value="<?= $schedule['next_payout_date'] ? date('M j, Y', strtotime($schedule['next_payout_date'])) : 'Not scheduled' ?>" 
                                               readonly>
                                        <div class="form-text">
                                            Based on your current settings
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Security & Notifications -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-shield-alt me-2"></i>
                                Security & Notifications
                            </h6>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="email_notifications" checked>
                                <label class="form-check-label" for="email_notifications">
                                    Email notifications for payout status updates
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="sms_notifications">
                                <label class="form-check-label" for="sms_notifications">
                                    SMS notifications for successful payouts
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="require_confirmation" checked>
                                <label class="form-check-label" for="require_confirmation">
                                    Require email confirmation for large payouts (>GH₵500)
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Current Settings Summary -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            Current Settings Summary
                        </h6>
                        <div id="settingsSummary">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= ORGANIZER_URL ?>/payouts" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Back to Dashboard
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const frequencyInputs = document.querySelectorAll('input[name="frequency"]');
    const scheduleDetails = document.getElementById('scheduleDetails');
    const payoutDayContainer = document.getElementById('payoutDayContainer');
    const payoutDaySelect = document.getElementById('payoutDaySelect');
    const payoutDayLabel = document.getElementById('payoutDayLabel');
    const payoutDayHelp = document.getElementById('payoutDayHelp');
    const autoPayoutCheckbox = document.getElementById('auto_payout_enabled');
    
    // Initialize
    updateScheduleDetails();
    updateSettingsSummary();
    
    frequencyInputs.forEach(input => {
        input.addEventListener('change', function() {
            updateScheduleDetails();
            updateSettingsSummary();
        });
    });
    
    // Update settings summary when inputs change
    document.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('change', updateSettingsSummary);
    });
    
    function updateScheduleDetails() {
        const selectedFrequency = document.querySelector('input[name="frequency"]:checked')?.value;
        
        if (selectedFrequency === 'manual') {
            scheduleDetails.style.display = 'none';
            autoPayoutCheckbox.checked = false;
        } else {
            scheduleDetails.style.display = 'block';
            updatePayoutDayOptions(selectedFrequency);
        }
    }
    
    function updatePayoutDayOptions(frequency) {
        let options = '';
        let label = '';
        let help = '';
        
        switch (frequency) {
            case 'weekly':
                label = 'Day of Week';
                help = 'Which day of the week to process payouts';
                options = `
                    <option value="1" ${<?= $schedule['payout_day'] ?> == 1 ? 'selected' : ''}>Monday</option>
                    <option value="2" ${<?= $schedule['payout_day'] ?> == 2 ? 'selected' : ''}>Tuesday</option>
                    <option value="3" ${<?= $schedule['payout_day'] ?> == 3 ? 'selected' : ''}>Wednesday</option>
                    <option value="4" ${<?= $schedule['payout_day'] ?> == 4 ? 'selected' : ''}>Thursday</option>
                    <option value="5" ${<?= $schedule['payout_day'] ?> == 5 ? 'selected' : ''}>Friday</option>
                    <option value="6" ${<?= $schedule['payout_day'] ?> == 6 ? 'selected' : ''}>Saturday</option>
                    <option value="7" ${<?= $schedule['payout_day'] ?> == 7 ? 'selected' : ''}>Sunday</option>
                `;
                payoutDayContainer.style.display = 'block';
                break;
                
            case 'monthly':
                label = 'Day of Month';
                help = 'Which day of the month to process payouts';
                options = '';
                for (let i = 1; i <= 28; i++) {
                    const selected = <?= $schedule['payout_day'] ?> == i ? 'selected' : '';
                    options += `<option value="${i}" ${selected}>${i}${getOrdinalSuffix(i)}</option>`;
                }
                payoutDayContainer.style.display = 'block';
                break;
                
            case 'daily':
            default:
                payoutDayContainer.style.display = 'none';
                break;
        }
        
        payoutDayLabel.textContent = label;
        payoutDayHelp.textContent = help;
        payoutDaySelect.innerHTML = options;
    }
    
    function getOrdinalSuffix(num) {
        const j = num % 10;
        const k = num % 100;
        if (j == 1 && k != 11) return 'st';
        if (j == 2 && k != 12) return 'nd';
        if (j == 3 && k != 13) return 'rd';
        return 'th';
    }
    
    function updateSettingsSummary() {
        const frequency = document.querySelector('input[name="frequency"]:checked')?.value || 'manual';
        const minAmount = document.querySelector('input[name="minimum_amount"]')?.value || '0';
        const autoEnabled = document.getElementById('auto_payout_enabled')?.checked || false;
        const instantThreshold = document.querySelector('input[name="instant_payout_threshold"]')?.value || '0';
        
        let summary = `<strong>Frequency:</strong> ${frequency.charAt(0).toUpperCase() + frequency.slice(1)}<br>`;
        summary += `<strong>Minimum Amount:</strong> $${parseFloat(minAmount).toFixed(2)}<br>`;
        summary += `<strong>Auto Payout:</strong> ${autoEnabled ? 'Enabled' : 'Disabled'}<br>`;
        summary += `<strong>Instant Threshold:</strong> $${parseFloat(instantThreshold).toFixed(2)}`;
        
        if (frequency === 'weekly') {
            const dayNames = ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            const dayValue = document.querySelector('select[name="payout_day"]')?.value || '1';
            summary += `<br><strong>Payout Day:</strong> ${dayNames[dayValue] || 'Monday'}`;
        } else if (frequency === 'monthly') {
            const dayValue = document.querySelector('select[name="payout_day"]')?.value || '1';
            summary += `<br><strong>Payout Day:</strong> ${dayValue}${getOrdinalSuffix(parseInt(dayValue))} of each month`;
        }
        
        document.getElementById('settingsSummary').innerHTML = summary;
    }
    
    // Form validation
    document.getElementById('settingsForm').addEventListener('submit', function(e) {
        const frequency = document.querySelector('input[name="frequency"]:checked')?.value;
        const minAmount = parseFloat(document.querySelector('input[name="minimum_amount"]')?.value || 0);
        const autoEnabled = document.getElementById('auto_payout_enabled')?.checked;
        
        if (frequency !== 'manual' && autoEnabled && minAmount < 1) {
            e.preventDefault();
            alert('Minimum payout amount must be at least GH₵1.00 for automatic payouts.');
            return;
        }
        
        if (confirm('Are you sure you want to update your payout settings? This will affect future automatic payouts.')) {
            // Form will submit normally
        } else {
            e.preventDefault();
        }
    });
});
</script>

<style>
.btn-check:checked + .btn {
    transform: scale(1.02);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.card {
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-label {
    font-weight: 600;
    color: #495057;
}

.alert {
    border-radius: 8px;
}

.input-group-text {
    background-color: #e9ecef;
    border-color: #ced4da;
}

@media (max-width: 768px) {
    .col-md-3 {
        margin-bottom: 1rem;
    }
    
    .btn {
        padding: 1rem;
        font-size: 0.875rem;
    }
}
</style>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../../layout/organizer_layout.php';
?>
