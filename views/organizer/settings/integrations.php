<!-- Integrations Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-plug me-2"></i>
            Integrations
        </h2>
        <p class="text-muted mb-0">Connect your favorite tools and services</p>
    </div>
    <div>
        <button class="btn btn-outline-primary" onclick="refreshIntegrations()">
            <i class="fas fa-sync me-2"></i>Refresh Status
        </button>
    </div>
</div>

<!-- Integration Categories -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body py-2">
                <div class="d-flex gap-3">
                    <button class="btn btn-sm btn-primary" onclick="filterIntegrations('all')">All</button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="filterIntegrations('payment')">Payment</button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="filterIntegrations('email')">Email</button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="filterIntegrations('social')">Social Media</button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="filterIntegrations('analytics')">Analytics</button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="filterIntegrations('storage')">Storage</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Integrations -->
<div class="integration-category" data-category="payment">
    <h4 class="mb-3">
        <i class="fas fa-credit-card me-2"></i>Payment Gateways
    </h4>
    <div class="row mb-4">
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100 integration-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="integration-icon bg-primary text-white rounded me-3">
                                <i class="fab fa-stripe"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Stripe</h6>
                                <small class="text-muted">Payment processing</small>
                            </div>
                        </div>
                        <span class="badge bg-success">Connected</span>
                    </div>
                    <p class="small text-muted mb-3">Accept credit cards, digital wallets, and bank transfers with Stripe's secure payment platform.</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-sm btn-outline-secondary" onclick="configureIntegration('stripe')">
                            <i class="fas fa-cog me-1"></i>Configure
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="disconnectIntegration('stripe')">
                            <i class="fas fa-unlink me-1"></i>Disconnect
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100 integration-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="integration-icon bg-info text-white rounded me-3">
                                <i class="fab fa-paypal"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">PayPal</h6>
                                <small class="text-muted">Digital payments</small>
                            </div>
                        </div>
                        <span class="badge bg-secondary">Not Connected</span>
                    </div>
                    <p class="small text-muted mb-3">Enable PayPal payments for your events and reach customers worldwide.</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-sm btn-primary" onclick="connectIntegration('paypal')">
                            <i class="fas fa-link me-1"></i>Connect
                        </button>
                        <a href="#" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-info-circle me-1"></i>Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100 integration-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="integration-icon bg-warning text-white rounded me-3">
                                <i class="fas fa-university"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Bank Transfer</h6>
                                <small class="text-muted">Direct bank payments</small>
                            </div>
                        </div>
                        <span class="badge bg-secondary">Not Connected</span>
                    </div>
                    <p class="small text-muted mb-3">Accept direct bank transfers and ACH payments from customers.</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-sm btn-primary" onclick="connectIntegration('bank')">
                            <i class="fas fa-link me-1"></i>Connect
                        </button>
                        <a href="#" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-info-circle me-1"></i>Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Email Integrations -->
<div class="integration-category" data-category="email">
    <h4 class="mb-3">
        <i class="fas fa-envelope me-2"></i>Email Services
    </h4>
    <div class="row mb-4">
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100 integration-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="integration-icon bg-success text-white rounded me-3">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">SendGrid</h6>
                                <small class="text-muted">Email delivery</small>
                            </div>
                        </div>
                        <span class="badge bg-success">Connected</span>
                    </div>
                    <p class="small text-muted mb-3">Reliable email delivery for notifications, receipts, and marketing campaigns.</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-sm btn-outline-secondary" onclick="configureIntegration('sendgrid')">
                            <i class="fas fa-cog me-1"></i>Configure
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="disconnectIntegration('sendgrid')">
                            <i class="fas fa-unlink me-1"></i>Disconnect
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100 integration-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="integration-icon bg-warning text-white rounded me-3">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Mailchimp</h6>
                                <small class="text-muted">Email marketing</small>
                            </div>
                        </div>
                        <span class="badge bg-secondary">Not Connected</span>
                    </div>
                    <p class="small text-muted mb-3">Sync your voter lists with Mailchimp for advanced email marketing campaigns.</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-sm btn-primary" onclick="connectIntegration('mailchimp')">
                            <i class="fas fa-link me-1"></i>Connect
                        </button>
                        <a href="#" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-info-circle me-1"></i>Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Social Media Integrations -->
<div class="integration-category" data-category="social">
    <h4 class="mb-3">
        <i class="fas fa-share-alt me-2"></i>Social Media
    </h4>
    <div class="row mb-4">
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100 integration-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="integration-icon bg-primary text-white rounded me-3">
                                <i class="fab fa-facebook"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Facebook</h6>
                                <small class="text-muted">Social sharing</small>
                            </div>
                        </div>
                        <span class="badge bg-secondary">Not Connected</span>
                    </div>
                    <p class="small text-muted mb-3">Share events on Facebook and enable social login for voters.</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-sm btn-primary" onclick="connectIntegration('facebook')">
                            <i class="fas fa-link me-1"></i>Connect
                        </button>
                        <a href="#" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-info-circle me-1"></i>Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100 integration-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="integration-icon bg-info text-white rounded me-3">
                                <i class="fab fa-twitter"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Twitter</h6>
                                <small class="text-muted">Social sharing</small>
                            </div>
                        </div>
                        <span class="badge bg-secondary">Not Connected</span>
                    </div>
                    <p class="small text-muted mb-3">Auto-post event updates and results to Twitter.</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-sm btn-primary" onclick="connectIntegration('twitter')">
                            <i class="fas fa-link me-1"></i>Connect
                        </button>
                        <a href="#" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-info-circle me-1"></i>Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Integrations -->
<div class="integration-category" data-category="analytics">
    <h4 class="mb-3">
        <i class="fas fa-chart-line me-2"></i>Analytics & Tracking
    </h4>
    <div class="row mb-4">
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100 integration-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="integration-icon bg-danger text-white rounded me-3">
                                <i class="fab fa-google"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Google Analytics</h6>
                                <small class="text-muted">Web analytics</small>
                            </div>
                        </div>
                        <span class="badge bg-success">Connected</span>
                    </div>
                    <p class="small text-muted mb-3">Track visitor behavior and event performance with Google Analytics.</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-sm btn-outline-secondary" onclick="configureIntegration('google_analytics')">
                            <i class="fas fa-cog me-1"></i>Configure
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="disconnectIntegration('google_analytics')">
                            <i class="fas fa-unlink me-1"></i>Disconnect
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100 integration-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="integration-icon bg-dark text-white rounded me-3">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Mixpanel</h6>
                                <small class="text-muted">Event tracking</small>
                            </div>
                        </div>
                        <span class="badge bg-secondary">Not Connected</span>
                    </div>
                    <p class="small text-muted mb-3">Advanced event tracking and user behavior analysis.</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-sm btn-primary" onclick="connectIntegration('mixpanel')">
                            <i class="fas fa-link me-1"></i>Connect
                        </button>
                        <a href="#" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-info-circle me-1"></i>Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Storage Integrations -->
<div class="integration-category" data-category="storage">
    <h4 class="mb-3">
        <i class="fas fa-cloud me-2"></i>Cloud Storage
    </h4>
    <div class="row mb-4">
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100 integration-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="integration-icon bg-warning text-white rounded me-3">
                                <i class="fab fa-aws"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Amazon S3</h6>
                                <small class="text-muted">File storage</small>
                            </div>
                        </div>
                        <span class="badge bg-success">Connected</span>
                    </div>
                    <p class="small text-muted mb-3">Store event images, videos, and documents in Amazon S3.</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-sm btn-outline-secondary" onclick="configureIntegration('aws_s3')">
                            <i class="fas fa-cog me-1"></i>Configure
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="disconnectIntegration('aws_s3')">
                            <i class="fas fa-unlink me-1"></i>Disconnect
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100 integration-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="integration-icon bg-primary text-white rounded me-3">
                                <i class="fab fa-google-drive"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Google Drive</h6>
                                <small class="text-muted">File storage</small>
                            </div>
                        </div>
                        <span class="badge bg-secondary">Not Connected</span>
                    </div>
                    <p class="small text-muted mb-3">Backup event data and media files to Google Drive.</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-sm btn-primary" onclick="connectIntegration('google_drive')">
                            <i class="fas fa-link me-1"></i>Connect
                        </button>
                        <a href="#" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-info-circle me-1"></i>Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Integration Configuration Modal -->
<div class="modal fade" id="integrationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="integrationModalTitle">Configure Integration</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="integrationModalBody">
                <!-- Integration-specific configuration will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveIntegrationConfig()">
                    <i class="fas fa-save me-2"></i>Save Configuration
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.integration-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.integration-card {
    transition: transform 0.2s ease-in-out;
}

.integration-card:hover {
    transform: translateY(-2px);
}

.integration-category {
    margin-bottom: 2rem;
}
</style>

<script>
function filterIntegrations(category) {
    const categories = document.querySelectorAll('.integration-category');
    const buttons = document.querySelectorAll('.card-body .btn');
    
    // Update button states
    buttons.forEach(btn => {
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-outline-secondary');
    });
    event.target.classList.remove('btn-outline-secondary');
    event.target.classList.add('btn-primary');
    
    // Show/hide categories
    categories.forEach(cat => {
        if (category === 'all' || cat.dataset.category === category) {
            cat.style.display = 'block';
        } else {
            cat.style.display = 'none';
        }
    });
}

function refreshIntegrations() {
    console.log('Refreshing integration status...');
    // In a real implementation, this would check the status of all integrations
    alert('Integration status refreshed!');
}

function connectIntegration(service) {
    console.log('Connecting to:', service);
    
    // Show service-specific connection flow
    switch (service) {
        case 'paypal':
            window.open('https://www.paypal.com/connect', '_blank', 'width=600,height=600');
            break;
        case 'facebook':
            window.open('https://www.facebook.com/dialog/oauth', '_blank', 'width=600,height=600');
            break;
        case 'twitter':
            window.open('https://api.twitter.com/oauth/authorize', '_blank', 'width=600,height=600');
            break;
        default:
            alert(`Connecting to ${service}... This would open the OAuth flow in a real implementation.`);
    }
}

function disconnectIntegration(service) {
    if (confirm(`Are you sure you want to disconnect ${service}? This may affect your event functionality.`)) {
        console.log('Disconnecting from:', service);
        alert(`${service} disconnected successfully!`);
        // In a real implementation, this would make an API call to disconnect
        location.reload();
    }
}

function configureIntegration(service) {
    console.log('Configuring:', service);
    
    const modal = document.getElementById('integrationModal');
    const title = document.getElementById('integrationModalTitle');
    const body = document.getElementById('integrationModalBody');
    
    title.textContent = `Configure ${service.charAt(0).toUpperCase() + service.slice(1)}`;
    
    // Load service-specific configuration form
    let configForm = '';
    
    switch (service) {
        case 'stripe':
            configForm = `
                <form id="stripeConfig">
                    <div class="mb-3">
                        <label class="form-label">Publishable Key</label>
                        <input type="text" class="form-control" name="publishable_key" placeholder="pk_live_...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Secret Key</label>
                        <input type="password" class="form-control" name="secret_key" placeholder="sk_live_...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Webhook Endpoint</label>
                        <input type="url" class="form-control" name="webhook_url" value="${window.location.origin}/webhooks/stripe" readonly>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="test_mode">
                        <label class="form-check-label">Test Mode</label>
                    </div>
                </form>
            `;
            break;
        case 'sendgrid':
            configForm = `
                <form id="sendgridConfig">
                    <div class="mb-3">
                        <label class="form-label">API Key</label>
                        <input type="password" class="form-control" name="api_key" placeholder="SG.xxxxx">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">From Email</label>
                        <input type="email" class="form-control" name="from_email" placeholder="noreply@yourdomain.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">From Name</label>
                        <input type="text" class="form-control" name="from_name" placeholder="Your Organization">
                    </div>
                </form>
            `;
            break;
        case 'google_analytics':
            configForm = `
                <form id="analyticsConfig">
                    <div class="mb-3">
                        <label class="form-label">Tracking ID</label>
                        <input type="text" class="form-control" name="tracking_id" placeholder="GA-XXXXXXXXX-X">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Property ID</label>
                        <input type="text" class="form-control" name="property_id" placeholder="123456789">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="enhanced_ecommerce" checked>
                        <label class="form-check-label">Enhanced Ecommerce Tracking</label>
                    </div>
                </form>
            `;
            break;
        case 'aws_s3':
            configForm = `
                <form id="s3Config">
                    <div class="mb-3">
                        <label class="form-label">Access Key ID</label>
                        <input type="text" class="form-control" name="access_key" placeholder="AKIAIOSFODNN7EXAMPLE">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Secret Access Key</label>
                        <input type="password" class="form-control" name="secret_key" placeholder="wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bucket Name</label>
                        <input type="text" class="form-control" name="bucket_name" placeholder="my-smartcast-bucket">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Region</label>
                        <select class="form-select" name="region">
                            <option value="us-east-1">US East (N. Virginia)</option>
                            <option value="us-west-2">US West (Oregon)</option>
                            <option value="eu-west-1">Europe (Ireland)</option>
                            <option value="ap-southeast-1">Asia Pacific (Singapore)</option>
                        </select>
                    </div>
                </form>
            `;
            break;
        default:
            configForm = '<p>Configuration options for this service will be available soon.</p>';
    }
    
    body.innerHTML = configForm;
    
    const modalInstance = new coreui.Modal(modal);
    modalInstance.show();
}

function saveIntegrationConfig() {
    console.log('Saving integration configuration...');
    
    // In a real implementation, this would save the configuration via API
    alert('Configuration saved successfully!');
    
    const modal = coreui.Modal.getInstance(document.getElementById('integrationModal'));
    modal.hide();
}
</script>
