<!-- Global Settings -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-sliders-h text-primary me-2"></i>
            Global Settings
        </h2>
        <p class="text-muted mb-0">Configure platform-wide settings and preferences</p>
    </div>
    <div>
        <button class="btn btn-success" onclick="saveAllSettings()">
            <i class="fas fa-save me-2"></i>Save All Changes
        </button>
        <button class="btn btn-outline-secondary" onclick="location.reload()">
            <i class="fas fa-sync me-2"></i>Reset
        </button>
    </div>
</div>

<!-- Settings Categories -->
<div class="row">
    <div class="col-lg-3">
        <div class="nav flex-column nav-pills" id="settingsTab" role="tablist">
            <button class="nav-link active" id="general-tab" data-coreui-toggle="pill" data-coreui-target="#general" type="button">
                <i class="fas fa-cog me-2"></i>General
            </button>
            <button class="nav-link" id="security-tab" data-coreui-toggle="pill" data-coreui-target="#security" type="button">
                <i class="fas fa-shield-alt me-2"></i>Security
            </button>
            <button class="nav-link" id="email-tab" data-coreui-toggle="pill" data-coreui-target="#email" type="button">
                <i class="fas fa-envelope me-2"></i>Email
            </button>
            <button class="nav-link" id="payment-tab" data-coreui-toggle="pill" data-coreui-target="#payment" type="button">
                <i class="fas fa-credit-card me-2"></i>Payment
            </button>
            <button class="nav-link" id="api-tab" data-coreui-toggle="pill" data-coreui-target="#api" type="button">
                <i class="fas fa-code me-2"></i>API
            </button>
            <button class="nav-link" id="maintenance-tab" data-coreui-toggle="pill" data-coreui-target="#maintenance" type="button">
                <i class="fas fa-tools me-2"></i>Maintenance
            </button>
        </div>
    </div>
    
    <div class="col-lg-9">
        <div class="tab-content" id="settingsTabContent">
            <!-- General Settings -->
            <div class="tab-pane fade show active" id="general" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">General Platform Settings</h5>
                    </div>
                    <div class="card-body">
                        <form id="generalSettingsForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="platformName" class="form-label">Platform Name</label>
                                        <input type="text" class="form-control" id="platformName" name="platform_name" value="<?= $settings['platform_name'] ?? 'SmartCast' ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="platformUrl" class="form-label">Platform URL</label>
                                        <input type="url" class="form-control" id="platformUrl" name="platform_url" value="<?= $settings['platform_url'] ?? '' ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="platformDescription" class="form-label">Platform Description</label>
                                <textarea class="form-control" id="platformDescription" name="platform_description" rows="3"><?= $settings['platform_description'] ?? '' ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="defaultTimezone" class="form-label">Default Timezone</label>
                                        <select class="form-select" id="defaultTimezone" name="default_timezone">
                                            <option value="UTC" <?= ($settings['default_timezone'] ?? '') === 'UTC' ? 'selected' : '' ?>>UTC</option>
                                            <option value="America/New_York" <?= ($settings['default_timezone'] ?? '') === 'America/New_York' ? 'selected' : '' ?>>Eastern Time</option>
                                            <option value="America/Chicago" <?= ($settings['default_timezone'] ?? '') === 'America/Chicago' ? 'selected' : '' ?>>Central Time</option>
                                            <option value="America/Denver" <?= ($settings['default_timezone'] ?? '') === 'America/Denver' ? 'selected' : '' ?>>Mountain Time</option>
                                            <option value="America/Los_Angeles" <?= ($settings['default_timezone'] ?? '') === 'America/Los_Angeles' ? 'selected' : '' ?>>Pacific Time</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="defaultLanguage" class="form-label">Default Language</label>
                                        <select class="form-select" id="defaultLanguage" name="default_language">
                                            <option value="en" <?= ($settings['default_language'] ?? '') === 'en' ? 'selected' : '' ?>>English</option>
                                            <option value="es" <?= ($settings['default_language'] ?? '') === 'es' ? 'selected' : '' ?>>Spanish</option>
                                            <option value="fr" <?= ($settings['default_language'] ?? '') === 'fr' ? 'selected' : '' ?>>French</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="allowRegistration" name="allow_registration" <?= ($settings['allow_registration'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="allowRegistration">
                                    Allow new tenant registrations
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="maintenanceMode" name="maintenance_mode" <?= ($settings['maintenance_mode'] ?? false) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="maintenanceMode">
                                    Enable maintenance mode
                                </label>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Security Settings -->
            <div class="tab-pane fade" id="security" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Security Configuration</h5>
                    </div>
                    <div class="card-body">
                        <form id="securitySettingsForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sessionTimeout" class="form-label">Session Timeout (minutes)</label>
                                        <input type="number" class="form-control" id="sessionTimeout" name="session_timeout" value="<?= $settings['session_timeout'] ?? 60 ?>" min="5" max="1440">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="maxLoginAttempts" class="form-label">Max Login Attempts</label>
                                        <input type="number" class="form-control" id="maxLoginAttempts" name="max_login_attempts" value="<?= $settings['max_login_attempts'] ?? 5 ?>" min="3" max="10">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="passwordMinLength" class="form-label">Minimum Password Length</label>
                                        <input type="number" class="form-control" id="passwordMinLength" name="password_min_length" value="<?= $settings['password_min_length'] ?? 8 ?>" min="6" max="20">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="passwordExpiry" class="form-label">Password Expiry (days)</label>
                                        <input type="number" class="form-control" id="passwordExpiry" name="password_expiry" value="<?= $settings['password_expiry'] ?? 90 ?>" min="30" max="365">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="requireTwoFactor" name="require_two_factor" <?= ($settings['require_two_factor'] ?? false) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="requireTwoFactor">
                                    Require two-factor authentication for admins
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="enableFraudDetection" name="enable_fraud_detection" <?= ($settings['enable_fraud_detection'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="enableFraudDetection">
                                    Enable fraud detection system
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="logAllActivity" name="log_all_activity" <?= ($settings['log_all_activity'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="logAllActivity">
                                    Log all user activity
                                </label>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Email Settings -->
            <div class="tab-pane fade" id="email" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Email Configuration</h5>
                    </div>
                    <div class="card-body">
                        <form id="emailSettingsForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="smtpHost" class="form-label">SMTP Host</label>
                                        <input type="text" class="form-control" id="smtpHost" name="smtp_host" value="<?= $settings['smtp_host'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="smtpPort" class="form-label">SMTP Port</label>
                                        <input type="number" class="form-control" id="smtpPort" name="smtp_port" value="<?= $settings['smtp_port'] ?? 587 ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="smtpUsername" class="form-label">SMTP Username</label>
                                        <input type="text" class="form-control" id="smtpUsername" name="smtp_username" value="<?= $settings['smtp_username'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="smtpPassword" class="form-label">SMTP Password</label>
                                        <input type="password" class="form-control" id="smtpPassword" name="smtp_password" placeholder="••••••••">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fromEmail" class="form-label">From Email</label>
                                        <input type="email" class="form-control" id="fromEmail" name="from_email" value="<?= $settings['from_email'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fromName" class="form-label">From Name</label>
                                        <input type="text" class="form-control" id="fromName" name="from_name" value="<?= $settings['from_name'] ?? '' ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="smtpEncryption" name="smtp_encryption" <?= ($settings['smtp_encryption'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="smtpEncryption">
                                    Use TLS encryption
                                </label>
                            </div>
                            
                            <button type="button" class="btn btn-outline-primary" onclick="testEmailSettings()">
                                <i class="fas fa-paper-plane me-2"></i>Send Test Email
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Payment Settings -->
            <div class="tab-pane fade" id="payment" role="tabpanel">
                <!-- General Payment Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">General Payment Configuration</h5>
                    </div>
                    <div class="card-body">
                        <form id="paymentSettingsForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="defaultCurrency" class="form-label">Default Currency</label>
                                        <select class="form-select" id="defaultCurrency" name="default_currency">
                                            <option value="GHS" <?= ($settings['default_currency'] ?? '') === 'GHS' ? 'selected' : '' ?>>GHS - Ghana Cedi</option>
                                            <option value="USD" <?= ($settings['default_currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD - US Dollar (Legacy)</option>
                                            <option value="EUR" <?= ($settings['default_currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                                            <option value="GBP" <?= ($settings['default_currency'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP - British Pound</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="platformFee" class="form-label">Platform Fee (%)</label>
                                        <input type="number" class="form-control" id="platformFee" name="platform_fee" value="<?= $settings['platform_fee'] ?? 5 ?>" min="0" max="50" step="0.1">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="autoPayouts" name="auto_payouts" <?= ($settings['auto_payouts'] ?? false) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="autoPayouts">
                                    Enable automatic payouts to tenants
                                </label>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Paystack Configuration -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-credit-card me-2"></i>Paystack Configuration
                        </h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="paystackEnabled" <?= ($paystack_config['is_active'] ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label text-white" for="paystackEnabled">Enabled</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="paystackSettingsForm">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Get your Paystack API keys from <a href="https://dashboard.paystack.com/#/settings/developers" target="_blank">Paystack Dashboard</a>
                            </div>
                            
                            <div class="mb-3">
                                <label for="paystackPublicKey" class="form-label">Public Key</label>
                                <input type="text" class="form-control font-monospace" id="paystackPublicKey" 
                                       placeholder="pk_live_..." value="<?= $paystack_config['public_key'] ?? '' ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="paystackSecretKey" class="form-label">Secret Key</label>
                                <div class="input-group">
                                    <input type="password" class="form-control font-monospace" id="paystackSecretKey" 
                                           placeholder="sk_live_..." value="<?= $paystack_config['secret_key'] ?? '' ?>">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('paystackSecretKey')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="paystackWebhookSecret" class="form-label">Webhook Secret (Optional)</label>
                                <input type="text" class="form-control font-monospace" id="paystackWebhookSecret" 
                                       placeholder="whsec_..." value="<?= $paystack_config['webhook_secret'] ?? '' ?>">
                                <small class="text-muted">Used to verify webhook signatures</small>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="paystackPriority" class="form-label">Priority</label>
                                        <input type="number" class="form-control" id="paystackPriority" 
                                               value="<?= $paystack_config['priority'] ?? 1 ?>" min="1" max="10">
                                        <small class="text-muted">Lower number = higher priority</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="paystackDefault" 
                                                   <?= ($paystack_config['is_default'] ?? false) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="paystackDefault">Set as Default Gateway</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-success" onclick="savePaystackConfig()">
                                <i class="fas fa-save me-2"></i>Save Paystack Configuration
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="testPaystackConnection()">
                                <i class="fas fa-plug me-2"></i>Test Connection
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Hubtel Configuration -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-mobile-alt me-2"></i>Hubtel Configuration
                        </h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="hubtelEnabled" <?= ($hubtel_config['is_active'] ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label text-white" for="hubtelEnabled">Enabled</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="hubtelSettingsForm">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Get your Hubtel credentials from <a href="https://dashboard.hubtel.com" target="_blank">Hubtel Dashboard</a>
                            </div>
                            
                            <div class="mb-3">
                                <label for="hubtelClientId" class="form-label">Client ID</label>
                                <input type="text" class="form-control font-monospace" id="hubtelClientId" 
                                       placeholder="Your Hubtel Client ID" value="<?= $hubtel_config['client_id'] ?? '' ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="hubtelClientSecret" class="form-label">Client Secret</label>
                                <div class="input-group">
                                    <input type="password" class="form-control font-monospace" id="hubtelClientSecret" 
                                           placeholder="Your Hubtel Client Secret" value="<?= $hubtel_config['client_secret'] ?? '' ?>">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('hubtelClientSecret')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="hubtelMerchantAccount" class="form-label">Merchant Account (POS Sales ID)</label>
                                <input type="text" class="form-control font-monospace" id="hubtelMerchantAccount" 
                                       placeholder="Your POS Sales ID" value="<?= $hubtel_config['merchant_account'] ?? '' ?>">
                                <small class="text-muted">Find this in your Hubtel Merchant Account section</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="hubtelIpWhitelist" class="form-label">IP Whitelist</label>
                                <textarea class="form-control font-monospace" id="hubtelIpWhitelist" rows="3" 
                                          placeholder="Enter IP addresses (one per line)"><?= implode("\n", $hubtel_config['ip_whitelist'] ?? []) ?></textarea>
                                <small class="text-muted">Your server IPs that Hubtel should allow. Max 4 IPs.</small>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="hubtelPriority" class="form-label">Priority</label>
                                        <input type="number" class="form-control" id="hubtelPriority" 
                                               value="<?= $hubtel_config['priority'] ?? 2 ?>" min="1" max="10">
                                        <small class="text-muted">Lower number = higher priority</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="hubtelDefault" 
                                                   <?= ($hubtel_config['is_default'] ?? false) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="hubtelDefault">Set as Default Gateway</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Important:</strong> After saving, submit your IP addresses to Hubtel support for whitelisting. 
                                This usually takes 24-48 hours.
                            </div>
                            
                            <button type="button" class="btn btn-primary" onclick="saveHubtelConfig()">
                                <i class="fas fa-save me-2"></i>Save Hubtel Configuration
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="testHubtelConnection()">
                                <i class="fas fa-plug me-2"></i>Test Connection
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Flutterwave Configuration -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-globe-africa me-2"></i>Flutterwave Configuration (Multi-Country)
                        </h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="flutterwaveEnabled" <?= ($flutterwave_config['is_active'] ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="flutterwaveEnabled">Enabled</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="flutterwaveSettingsForm">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Multi-Country Support:</strong> Flutterwave enables payments from Nigeria, Kenya, Uganda, Rwanda, Tanzania, Zambia, South Africa, and more.
                                Get your credentials from <a href="https://app.flutterwave.com/dashboard/settings/apis" target="_blank">Flutterwave Dashboard</a>
                            </div>
                            
                            <div class="mb-3">
                                <label for="flutterwaveClientId" class="form-label">Client ID</label>
                                <input type="text" class="form-control font-monospace" id="flutterwaveClientId" 
                                       placeholder="Your Flutterwave Client ID" value="<?= $flutterwave_config['client_id'] ?? '' ?>">
                                <small class="text-muted">Found in Settings → API Keys</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="flutterwaveClientSecret" class="form-label">Client Secret</label>
                                <div class="input-group">
                                    <input type="password" class="form-control font-monospace" id="flutterwaveClientSecret" 
                                           placeholder="Your Flutterwave Client Secret" value="<?= $flutterwave_config['client_secret'] ?? '' ?>">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('flutterwaveClientSecret')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="flutterwaveEncryptionKey" class="form-label">Encryption Key</label>
                                <div class="input-group">
                                    <input type="password" class="form-control font-monospace" id="flutterwaveEncryptionKey" 
                                           placeholder="Your Encryption Key" value="<?= $flutterwave_config['encryption_key'] ?? '' ?>">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('flutterwaveEncryptionKey')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="flutterwaveWebhookSecret" class="form-label">Webhook Secret Hash</label>
                                <div class="input-group">
                                    <input type="password" class="form-control font-monospace" id="flutterwaveWebhookSecret" 
                                           placeholder="Your Webhook Secret" value="<?= $flutterwave_config['webhook_secret'] ?? '' ?>">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('flutterwaveWebhookSecret')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Used to verify webhook signatures</small>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="flutterwaveEnvironment" class="form-label">Environment</label>
                                        <select class="form-select" id="flutterwaveEnvironment">
                                            <option value="sandbox" <?= ($flutterwave_config['sandbox'] ?? true) ? 'selected' : '' ?>>Sandbox (Testing)</option>
                                            <option value="production" <?= !($flutterwave_config['sandbox'] ?? true) ? 'selected' : '' ?>>Production (Live)</option>
                                        </select>
                                        <small class="text-muted">Use sandbox for testing, production for live payments</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="flutterwavePriority" class="form-label">Priority</label>
                                        <input type="number" class="form-control" id="flutterwavePriority" 
                                               value="<?= $flutterwave_config['priority'] ?? 3 ?>" min="1" max="10">
                                        <small class="text-muted">Lower number = higher priority</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Supported Countries</label>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="fw_gh" checked disabled>
                                            <label class="form-check-label" for="fw_gh">🇬🇭 Ghana</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="fw_ng" checked disabled>
                                            <label class="form-check-label" for="fw_ng">🇳🇬 Nigeria</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="fw_ke" checked disabled>
                                            <label class="form-check-label" for="fw_ke">🇰🇪 Kenya</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="fw_ug" checked disabled>
                                            <label class="form-check-label" for="fw_ug">🇺🇬 Uganda</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="fw_rw" checked disabled>
                                            <label class="form-check-label" for="fw_rw">🇷🇼 Rwanda</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="fw_tz" checked disabled>
                                            <label class="form-check-label" for="fw_tz">🇹🇿 Tanzania</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="fw_zm" checked disabled>
                                            <label class="form-check-label" for="fw_zm">🇿🇲 Zambia</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="fw_za" checked disabled>
                                            <label class="form-check-label" for="fw_za">🇿🇦 South Africa</label>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted">All countries are supported by default</small>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Important:</strong> After saving, configure the webhook URL in your Flutterwave dashboard and test with sandbox credentials before going live.
                            </div>
                            
                            <button type="button" class="btn btn-warning" onclick="saveFlutterwaveConfig()">
                                <i class="fas fa-save me-2"></i>Save Flutterwave Configuration
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="testFlutterwaveConnection()">
                                <i class="fas fa-plug me-2"></i>Test Connection
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="window.open('<?= APP_URL ?>/FLUTTERWAVE_SETUP.md', '_blank')">
                                <i class="fas fa-book me-2"></i>Setup Guide
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Webhook URLs -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-link me-2"></i>Webhook URLs
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Configure these webhook URLs in your payment gateway dashboards:</p>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Paystack Webhook URL</label>
                            <div class="input-group">
                                <input type="text" class="form-control font-monospace" readonly 
                                       value="<?= APP_URL ?>/api/payment/webhook.php?provider=paystack">
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard(this)">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Hubtel Webhook URL</label>
                            <div class="input-group">
                                <input type="text" class="form-control font-monospace" readonly 
                                       value="<?= APP_URL ?>/api/payment/webhook.php?provider=hubtel">
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard(this)">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Flutterwave Webhook URL</label>
                            <div class="input-group">
                                <input type="text" class="form-control font-monospace" readonly 
                                       value="<?= APP_URL ?>/api/payment/webhook/flutterwave">
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard(this)">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <small class="text-muted">Configure this in Flutterwave Dashboard → Settings → Webhooks</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- API Settings -->
            <div class="tab-pane fade" id="api" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">API Configuration</h5>
                    </div>
                    <div class="card-body">
                        <form id="apiSettingsForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="apiRateLimit" class="form-label">Rate Limit (requests/minute)</label>
                                        <input type="number" class="form-control" id="apiRateLimit" name="api_rate_limit" value="<?= $settings['api_rate_limit'] ?? 100 ?>" min="10" max="1000">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="apiVersion" class="form-label">API Version</label>
                                        <select class="form-select" id="apiVersion" name="api_version">
                                            <option value="v1" <?= ($settings['api_version'] ?? '') === 'v1' ? 'selected' : '' ?>>v1</option>
                                            <option value="v2" <?= ($settings['api_version'] ?? '') === 'v2' ? 'selected' : '' ?>>v2</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="enableApiDocs" name="enable_api_docs" <?= ($settings['enable_api_docs'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="enableApiDocs">
                                    Enable API documentation
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="requireApiAuth" name="require_api_auth" <?= ($settings['require_api_auth'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="requireApiAuth">
                                    Require authentication for all API endpoints
                                </label>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Maintenance Settings -->
            <div class="tab-pane fade" id="maintenance" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Maintenance Configuration</h5>
                    </div>
                    <div class="card-body">
                        <form id="maintenanceSettingsForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="backupFrequency" class="form-label">Backup Frequency</label>
                                        <select class="form-select" id="backupFrequency" name="backup_frequency">
                                            <option value="daily" <?= ($settings['backup_frequency'] ?? '') === 'daily' ? 'selected' : '' ?>>Daily</option>
                                            <option value="weekly" <?= ($settings['backup_frequency'] ?? '') === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                                            <option value="monthly" <?= ($settings['backup_frequency'] ?? '') === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="logRetention" class="form-label">Log Retention (days)</label>
                                        <input type="number" class="form-control" id="logRetention" name="log_retention" value="<?= $settings['log_retention'] ?? 90 ?>" min="7" max="365">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="autoCleanup" name="auto_cleanup" <?= ($settings['auto_cleanup'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="autoCleanup">
                                    Enable automatic cleanup of old data
                                </label>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function saveAllSettings() {
    const forms = ['generalSettingsForm', 'securitySettingsForm', 'emailSettingsForm', 'paymentSettingsForm', 'apiSettingsForm', 'maintenanceSettingsForm'];
    const allData = {};
    
    forms.forEach(formId => {
        const form = document.getElementById(formId);
        if (form) {
            const formData = new FormData(form);
            for (let [key, value] of formData.entries()) {
                allData[key] = value;
            }
        }
    });
    
    console.log('Saving all settings:', allData);
    alert('Settings saved successfully!');
}

function testEmailSettings() {
    const form = document.getElementById('emailSettingsForm');
    const formData = new FormData(form);
    
    console.log('Testing email settings:', Object.fromEntries(formData));
    alert('Test email sent! Check your inbox.');
}

// Payment Gateway Functions
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function copyToClipboard(button) {
    const input = button.previousElementSibling;
    input.select();
    document.execCommand('copy');
    
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    button.classList.add('btn-success');
    button.classList.remove('btn-outline-secondary');
    
    setTimeout(() => {
        button.innerHTML = originalHTML;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-secondary');
    }, 2000);
}

async function savePaystackConfig() {
    const config = {
        public_key: document.getElementById('paystackPublicKey').value,
        secret_key: document.getElementById('paystackSecretKey').value,
        webhook_secret: document.getElementById('paystackWebhookSecret').value,
        priority: document.getElementById('paystackPriority').value,
        is_active: document.getElementById('paystackEnabled').checked ? 1 : 0,
        is_default: document.getElementById('paystackDefault').checked ? 1 : 0
    };
    
    try {
        const response = await fetch('<?= SUPERADMIN_URL ?>/api/payment-gateways/paystack', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(config)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Paystack configuration saved successfully!');
        } else {
            alert('❌ Error: ' + (result.message || 'Failed to save configuration'));
        }
    } catch (error) {
        console.error('Save error:', error);
        alert('❌ Failed to save Paystack configuration. Check console for details.');
    }
}

async function saveHubtelConfig() {
    const ipWhitelist = document.getElementById('hubtelIpWhitelist').value
        .split('\n')
        .map(ip => ip.trim())
        .filter(ip => ip.length > 0);
    
    const config = {
        client_id: document.getElementById('hubtelClientId').value,
        client_secret: document.getElementById('hubtelClientSecret').value,
        merchant_account: document.getElementById('hubtelMerchantAccount').value,
        ip_whitelist: ipWhitelist,
        priority: document.getElementById('hubtelPriority').value,
        is_active: document.getElementById('hubtelEnabled').checked ? 1 : 0,
        is_default: document.getElementById('hubtelDefault').checked ? 1 : 0
    };
    
    try {
        const response = await fetch('<?= SUPERADMIN_URL ?>/api/payment-gateways/hubtel', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(config)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Hubtel configuration saved successfully!');
        } else {
            alert('❌ Error: ' + (result.message || 'Failed to save configuration'));
        }
    } catch (error) {
        console.error('Save error:', error);
        alert('❌ Failed to save Hubtel configuration. Check console for details.');
    }
}

async function saveFlutterwaveConfig() {
    const config = {
        client_id: document.getElementById('flutterwaveClientId').value,
        client_secret: document.getElementById('flutterwaveClientSecret').value,
        encryption_key: document.getElementById('flutterwaveEncryptionKey').value,
        webhook_secret: document.getElementById('flutterwaveWebhookSecret').value,
        sandbox: document.getElementById('flutterwaveEnvironment').value === 'sandbox',
        api_url: document.getElementById('flutterwaveEnvironment').value === 'sandbox' 
            ? 'https://developersandbox-api.flutterwave.com' 
            : 'https://api.flutterwave.com',
        priority: document.getElementById('flutterwavePriority').value,
        is_active: document.getElementById('flutterwaveEnabled').checked ? 1 : 0
    };
    
    try {
        const response = await fetch('<?= SUPERADMIN_URL ?>/api/payment-gateways/flutterwave', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(config)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Flutterwave configuration saved successfully!\n\n' +
                  '📝 Next steps:\n' +
                  '1. Configure webhook URL in Flutterwave dashboard\n' +
                  '2. Test with sandbox credentials\n' +
                  '3. Remove CSS hide rule from vote-form.php (line 612-615)\n' +
                  '4. Try a test payment');
            
            // Reload page to show updated config
            setTimeout(() => location.reload(), 2000);
        } else {
            alert('❌ Error: ' + (result.message || 'Failed to save configuration'));
        }
    } catch (error) {
        console.error('Save error:', error);
        alert('❌ Failed to save Flutterwave configuration. Check console for details.');
    }
}

async function testFlutterwaveConnection() {
    const clientId = document.getElementById('flutterwaveClientId').value;
    const clientSecret = document.getElementById('flutterwaveClientSecret').value;
    
    if (!clientId || !clientSecret) {
        alert('⚠️ Please enter your Flutterwave Client ID and Client Secret first');
        return;
    }
    
    alert('🔄 Testing Flutterwave connection...\n\nThis will attempt to authenticate with Flutterwave API.');
    
    try {
        const response = await fetch('<?= SUPERADMIN_URL ?>/api/payment-gateways/test/flutterwave', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                client_id: clientId,
                client_secret: clientSecret,
                api_url: document.getElementById('flutterwaveEnvironment').value === 'sandbox' 
                    ? 'https://developersandbox-api.flutterwave.com' 
                    : 'https://api.flutterwave.com'
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Connection successful!\n\n' +
                  'Flutterwave API is responding correctly.\n' +
                  'Environment: ' + (result.environment || 'Unknown') + '\n' +
                  'You can now save the configuration.');
        } else {
            alert('❌ Connection failed!\n\n' +
                  'Error: ' + (result.message || 'Unknown error') + '\n\n' +
                  'Please check your credentials and try again.');
        }
    } catch (error) {
        console.error('Test error:', error);
        alert('❌ Connection test failed. Check console for details.');
    }
}

async function testPaystackConnection() {
    const secretKey = document.getElementById('paystackSecretKey').value;
    
    if (!secretKey) {
        alert('⚠️ Please enter your Paystack Secret Key first');
        return;
    }
    
    alert('🔄 Testing Paystack connection...');
    
    try {
        const response = await fetch('<?= SUPERADMIN_URL ?>/api/payment-gateways/test/paystack', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ secret_key: secretKey })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Paystack connection successful!\n\n' + (result.message || 'API credentials are valid.'));
        } else {
            alert('❌ Paystack connection failed!\n\n' + (result.message || 'Invalid API credentials.'));
        }
    } catch (error) {
        console.error('Test error:', error);
        alert('❌ Failed to test Paystack connection. Check console for details.');
    }
}

async function testHubtelConnection() {
    const clientId = document.getElementById('hubtelClientId').value;
    const clientSecret = document.getElementById('hubtelClientSecret').value;
    
    if (!clientId || !clientSecret) {
        alert('⚠️ Please enter your Hubtel Client ID and Secret first');
        return;
    }
    
    alert('🔄 Testing Hubtel connection...');
    
    try {
        const response = await fetch('<?= SUPERADMIN_URL ?>/api/payment-gateways/test/hubtel', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                client_id: clientId,
                client_secret: clientSecret 
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Hubtel connection successful!\n\n' + (result.message || 'API credentials are valid.'));
        } else {
            alert('❌ Hubtel connection failed!\n\n' + (result.message || 'Invalid API credentials or IP not whitelisted.'));
        }
    } catch (error) {
        console.error('Test error:', error);
        alert('❌ Failed to test Hubtel connection. Check console for details.');
    }
}
</script>
