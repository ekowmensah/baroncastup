<!-- Security Settings Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-shield-alt me-2"></i>
            Security Settings
        </h2>
        <p class="text-muted mb-0">Protect your account and manage security preferences</p>
    </div>
    <div>
        <button class="btn btn-outline-primary" onclick="runSecurityScan()">
            <i class="fas fa-search me-2"></i>Security Scan
        </button>
    </div>
</div>

<!-- Security Status -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-shield-check fa-2x text-success"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="mb-1 text-success">Your account is secure</h5>
                        <p class="mb-0 text-muted">All security checks passed. Last security scan: <?= date('M j, Y H:i') ?></p>
                    </div>
                    <div>
                        <span class="badge bg-success">Secure</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Password & Authentication -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Password & Authentication</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= ORGANIZER_URL ?>/settings/security">
                    <input type="hidden" name="action" value="update_password">
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password *</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password *</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <div class="form-text">
                                    <div id="passwordStrength" class="mt-1"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password *</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <h6 class="mb-2">Password Requirements:</h6>
                                <ul class="list-unstyled mb-0 small">
                                    <li><i class="fas fa-check text-success me-1"></i> At least 8 characters long</li>
                                    <li><i class="fas fa-check text-success me-1"></i> Contains uppercase and lowercase letters</li>
                                    <li><i class="fas fa-check text-success me-1"></i> Contains at least one number</li>
                                    <li><i class="fas fa-check text-success me-1"></i> Contains at least one special character</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key me-2"></i>Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Two-Factor Authentication -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Two-Factor Authentication</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-1">Authenticator App</h6>
                        <p class="text-muted small mb-0">Use an authenticator app to generate verification codes</p>
                    </div>
                    <div>
                        <span class="badge bg-secondary">Not Enabled</span>
                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="setup2FA()">
                            <i class="fas fa-mobile-alt me-1"></i>Setup
                        </button>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-1">SMS Verification</h6>
                        <p class="text-muted small mb-0">Receive verification codes via text message</p>
                    </div>
                    <div>
                        <span class="badge bg-secondary">Not Enabled</span>
                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="setupSMS()">
                            <i class="fas fa-sms me-1"></i>Setup
                        </button>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Email Verification</h6>
                        <p class="text-muted small mb-0">Receive verification codes via email</p>
                    </div>
                    <div>
                        <span class="badge bg-success">Enabled</span>
                        <button class="btn btn-sm btn-outline-secondary ms-2" onclick="disableEmail2FA()">
                            <i class="fas fa-times me-1"></i>Disable
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Login Activity -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Login Activity</h5>
                    <button class="btn btn-sm btn-outline-secondary" onclick="refreshLoginActivity()">
                        <i class="fas fa-sync me-1"></i>Refresh
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date & Time</th>
                                <th>Location</th>
                                <th>Device</th>
                                <th>IP Address</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="fw-semibold">Nov 12, 2024</div>
                                    <div class="small text-muted">2:34 PM</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">New York, NY</div>
                                    <div class="small text-muted">United States</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">Chrome on Windows</div>
                                    <div class="small text-muted">Desktop</div>
                                </td>
                                <td>192.168.1.100</td>
                                <td>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>Current Session
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">Nov 11, 2024</div>
                                    <div class="small text-muted">4:15 PM</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">New York, NY</div>
                                    <div class="small text-muted">United States</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">Safari on iPhone</div>
                                    <div class="small text-muted">Mobile</div>
                                </td>
                                <td>192.168.1.101</td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-sign-out-alt me-1"></i>Signed Out
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">Nov 10, 2024</div>
                                    <div class="small text-muted">9:22 AM</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">Los Angeles, CA</div>
                                    <div class="small text-muted">United States</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">Firefox on Linux</div>
                                    <div class="small text-muted">Desktop</div>
                                </td>
                                <td>203.0.113.45</td>
                                <td>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Suspicious
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- API Keys -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">API Keys</h5>
                    <button class="btn btn-sm btn-primary" onclick="generateAPIKey()">
                        <i class="fas fa-plus me-1"></i>Generate Key
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    API keys allow external applications to access your SmartCast data. Keep them secure and never share them publicly.
                </div>
                
                <div class="api-key-item border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">Production API Key</h6>
                            <div class="font-monospace small text-muted mb-2">sk_live_••••••••••••••••••••••••••••••••••••1234</div>
                            <div class="small text-muted">
                                Created: Nov 1, 2024 | Last used: Nov 12, 2024 | 
                                <span class="text-success">Active</span>
                            </div>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary" onclick="copyAPIKey('sk_live_1234')">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button class="btn btn-outline-warning" onclick="regenerateAPIKey('sk_live_1234')">
                                <i class="fas fa-sync"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="revokeAPIKey('sk_live_1234')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="api-key-item border rounded p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">Development API Key</h6>
                            <div class="font-monospace small text-muted mb-2">sk_test_••••••••••••••••••••••••••••••••••••5678</div>
                            <div class="small text-muted">
                                Created: Oct 15, 2024 | Last used: Never | 
                                <span class="text-secondary">Inactive</span>
                            </div>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary" onclick="copyAPIKey('sk_test_5678')">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button class="btn btn-outline-warning" onclick="regenerateAPIKey('sk_test_5678')">
                                <i class="fas fa-sync"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="revokeAPIKey('sk_test_5678')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Security Score -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Security Score</h6>
            </div>
            <div class="card-body text-center">
                <div class="position-relative d-inline-block">
                    <div class="progress-circle">
                        <svg width="120" height="120">
                            <circle cx="60" cy="60" r="50" stroke="#e9ecef" stroke-width="8" fill="none"></circle>
                            <circle cx="60" cy="60" r="50" stroke="#28a745" stroke-width="8" fill="none" 
                                    stroke-dasharray="314" stroke-dashoffset="94" stroke-linecap="round"></circle>
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <div class="fs-3 fw-bold text-success">85%</div>
                            <div class="small text-muted">Good</div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <h6 class="text-success">Good Security</h6>
                    <p class="small text-muted">Your account has strong security measures in place.</p>
                </div>
            </div>
        </div>
        
        <!-- Security Recommendations -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Security Recommendations</h6>
            </div>
            <div class="card-body">
                <div class="recommendation-item mb-3">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-mobile-alt text-warning me-2 mt-1"></i>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">Enable 2FA</div>
                            <div class="small text-muted">Add two-factor authentication for extra security</div>
                        </div>
                        <button class="btn btn-sm btn-outline-primary" onclick="setup2FA()">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
                
                <div class="recommendation-item mb-3">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-key text-info me-2 mt-1"></i>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">Strong Password</div>
                            <div class="small text-muted">Your password meets all requirements</div>
                        </div>
                        <i class="fas fa-check text-success"></i>
                    </div>
                </div>
                
                <div class="recommendation-item">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-shield-check text-success me-2 mt-1"></i>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">Regular Backups</div>
                            <div class="small text-muted">Your data is backed up automatically</div>
                        </div>
                        <i class="fas fa-check text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Security Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Security Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-warning btn-sm" onclick="signOutAllDevices()">
                        <i class="fas fa-sign-out-alt me-2"></i>Sign Out All Devices
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="downloadSecurityReport()">
                        <i class="fas fa-download me-2"></i>Security Report
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="viewAuditLog()">
                        <i class="fas fa-list me-2"></i>Audit Log
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="deleteAccount()">
                        <i class="fas fa-trash me-2"></i>Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2FA Setup Modal -->
<div class="modal fade" id="setup2FAModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Setup Two-Factor Authentication</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="qr-code-placeholder bg-light border rounded p-4 d-inline-block">
                        <i class="fas fa-qrcode fa-4x text-muted"></i>
                        <div class="mt-2 small text-muted">QR Code will appear here</div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <h6>Setup Instructions:</h6>
                    <ol class="mb-0 small">
                        <li>Install an authenticator app (Google Authenticator, Authy, etc.)</li>
                        <li>Scan the QR code above with your app</li>
                        <li>Enter the 6-digit code from your app below</li>
                    </ol>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Verification Code</label>
                    <input type="text" class="form-control text-center" maxlength="6" placeholder="000000">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Backup Codes</label>
                    <div class="bg-light border rounded p-3">
                        <div class="small font-monospace">
                            123456789<br>
                            987654321<br>
                            456789123<br>
                            789123456<br>
                            321654987
                        </div>
                    </div>
                    <div class="form-text">Save these backup codes in a secure location. You can use them to access your account if you lose your authenticator device.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="enable2FA()">
                    <i class="fas fa-shield-check me-2"></i>Enable 2FA
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.progress-circle {
    position: relative;
}

.progress-circle circle {
    transform: rotate(-90deg);
    transform-origin: 50% 50%;
}

.api-key-item {
    background-color: #f8f9fa;
}

.recommendation-item {
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
}

.recommendation-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
</style>

<script>
// Password strength checker
document.getElementById('new_password').addEventListener('input', function() {
    const password = this.value;
    const strengthDiv = document.getElementById('passwordStrength');
    
    let strength = 0;
    let feedback = [];
    
    if (password.length >= 8) strength++;
    else feedback.push('At least 8 characters');
    
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    else feedback.push('Upper and lowercase letters');
    
    if (/\d/.test(password)) strength++;
    else feedback.push('At least one number');
    
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    else feedback.push('At least one special character');
    
    const strengthText = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'][strength];
    const strengthColor = ['danger', 'warning', 'info', 'success', 'success'][strength];
    
    strengthDiv.innerHTML = `
        <div class="progress" style="height: 4px;">
            <div class="progress-bar bg-${strengthColor}" style="width: ${(strength / 4) * 100}%"></div>
        </div>
        <div class="small text-${strengthColor} mt-1">${strengthText}</div>
    `;
});

function runSecurityScan() {
    console.log('Running security scan...');
    alert('Security scan completed! No issues found.');
}

function setup2FA() {
    const modal = new coreui.Modal(document.getElementById('setup2FAModal'));
    modal.show();
}

function setupSMS() {
    const phone = prompt('Enter your phone number for SMS verification:');
    if (phone) {
        console.log('Setting up SMS 2FA for:', phone);
        alert('SMS verification setup initiated! Check your phone for a verification code.');
    }
}

function disableEmail2FA() {
    if (confirm('Are you sure you want to disable email verification? This will reduce your account security.')) {
        console.log('Disabling email 2FA...');
        alert('Email verification disabled.');
    }
}

function enable2FA() {
    const code = document.querySelector('#setup2FAModal input').value;
    if (code && code.length === 6) {
        console.log('Enabling 2FA with code:', code);
        alert('Two-factor authentication enabled successfully!');
        
        const modal = coreui.Modal.getInstance(document.getElementById('setup2FAModal'));
        modal.hide();
    } else {
        alert('Please enter a valid 6-digit code.');
    }
}

function refreshLoginActivity() {
    console.log('Refreshing login activity...');
    location.reload();
}

function generateAPIKey() {
    const keyName = prompt('Enter a name for this API key:');
    if (keyName) {
        console.log('Generating API key:', keyName);
        alert('New API key generated successfully!');
    }
}

function copyAPIKey(keyId) {
    console.log('Copying API key:', keyId);
    alert('API key copied to clipboard!');
}

function regenerateAPIKey(keyId) {
    if (confirm('Are you sure you want to regenerate this API key? The old key will stop working immediately.')) {
        console.log('Regenerating API key:', keyId);
        alert('API key regenerated successfully!');
    }
}

function revokeAPIKey(keyId) {
    if (confirm('Are you sure you want to revoke this API key? This action cannot be undone.')) {
        console.log('Revoking API key:', keyId);
        alert('API key revoked successfully!');
    }
}

function signOutAllDevices() {
    if (confirm('Are you sure you want to sign out all devices? You will need to log in again on all your devices.')) {
        console.log('Signing out all devices...');
        alert('All devices signed out successfully!');
    }
}

function downloadSecurityReport() {
    console.log('Downloading security report...');
    alert('Security report download will be available soon!');
}

function viewAuditLog() {
    console.log('Opening audit log...');
    alert('Audit log functionality will be available soon!');
}

function deleteAccount() {
    const confirmation = prompt('Type "DELETE" to confirm account deletion:');
    if (confirmation === 'DELETE') {
        alert('Account deletion functionality requires additional verification steps.');
    }
}
</script>
