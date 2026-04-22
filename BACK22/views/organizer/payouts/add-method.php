<?php 
$content = ob_start(); 
?>

<!-- Add Payout Method -->
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-plus me-2"></i>
                    Add Payout Method
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" id="addMethodForm">
                    <!-- Method Type Selection -->
                    <div class="mb-4">
                        <label class="form-label">Select Payment Method *</label>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <input type="radio" class="btn-check" name="method_type" id="bank_transfer" value="bank_transfer" required>
                                <label class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3" for="bank_transfer">
                                    <i class="fas fa-university fa-2x mb-2"></i>
                                    <div class="fw-bold">Bank Transfer</div>
                                    <small class="text-muted">1-3 business days</small>
                                    <small class="text-muted">1.0% + GH₵0.50</small>
                                </label>
                            </div>
                            <div class="col-md-3 mb-3">
                                <input type="radio" class="btn-check" name="method_type" id="mobile_money" value="mobile_money" required>
                                <label class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3" for="mobile_money">
                                    <i class="fas fa-mobile-alt fa-2x mb-2"></i>
                                    <div class="fw-bold">Mobile Money</div>
                                    <small class="text-muted">Instant to 24 hours</small>
                                    <small class="text-muted">1.5% + GH₵0.25</small>
                                </label>
                            </div>
                            <div class="col-md-3 mb-3">
                                <input type="radio" class="btn-check" name="method_type" id="paypal" value="paypal" required>
                                <label class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3" for="paypal">
                                    <i class="fab fa-paypal fa-2x mb-2"></i>
                                    <div class="fw-bold">PayPal</div>
                                    <small class="text-muted">Instant to 1 day</small>
                                    <small class="text-muted">2.9% + GH₵0.30</small>
                                </label>
                            </div>
                            <div class="col-md-3 mb-3">
                                <input type="radio" class="btn-check" name="method_type" id="stripe" value="stripe" required>
                                <label class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3" for="stripe">
                                    <i class="fab fa-stripe fa-2x mb-2"></i>
                                    <div class="fw-bold">Stripe</div>
                                    <small class="text-muted">2-7 business days</small>
                                    <small class="text-muted">2.9% + GH₵0.30</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Method Name -->
                    <div class="mb-3">
                        <label for="method_name" class="form-label">Method Name *</label>
                        <input type="text" class="form-control" id="method_name" name="method_name" 
                               placeholder="e.g., My Primary Bank Account" required>
                        <div class="form-text">Give this method a name to easily identify it</div>
                    </div>
                    
                    <!-- Dynamic Method Details -->
                    <div id="methodDetails" style="display: none;">
                        <!-- Details will be populated by JavaScript -->
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= ORGANIZER_URL ?>/payouts/methods" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Back to Methods
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Add Method
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const methodTypeInputs = document.querySelectorAll('input[name="method_type"]');
    const methodDetails = document.getElementById('methodDetails');
    const methodNameInput = document.getElementById('method_name');
    
    methodTypeInputs.forEach(input => {
        input.addEventListener('change', function() {
            updateMethodDetails(this.value);
            updateMethodName(this.value);
        });
    });
    
    function updateMethodName(methodType) {
        const defaultNames = {
            'bank_transfer': 'My Bank Account',
            'mobile_money': 'My Mobile Money',
            'paypal': 'My PayPal Account',
            'stripe': 'My Stripe Account'
        };
        
        if (!methodNameInput.value || Object.values(defaultNames).includes(methodNameInput.value)) {
            methodNameInput.value = defaultNames[methodType] || '';
        }
    }
    
    function updateMethodDetails(methodType) {
        let html = '';
        
        switch (methodType) {
            case 'bank_transfer':
                html = `
                    <div class="card bg-light mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-university me-2"></i>
                                Bank Transfer Details
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Bank Name *</label>
                                        <input type="text" class="form-control" name="bank_name" 
                                               placeholder="e.g., Chase Bank" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Account Number *</label>
                                        <input type="text" class="form-control" name="account_number" 
                                               placeholder="1234567890" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Account Holder Name *</label>
                                        <input type="text" class="form-control" name="account_name" 
                                               placeholder="John Doe" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Routing Number</label>
                                        <input type="text" class="form-control" name="routing_number" 
                                               placeholder="123456789">
                                        <div class="form-text">Optional - for US banks</div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Bank Code (SWIFT/BIC)</label>
                                <input type="text" class="form-control" name="bank_code" 
                                       placeholder="CHASUS33">
                                <div class="form-text">Optional - for international transfers</div>
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'mobile_money':
                html = `
                    <div class="card bg-light mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-mobile-alt me-2"></i>
                                Mobile Money Details
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Mobile Money Provider *</label>
                                        <select class="form-select" name="provider" required>
                                            <option value="">Select Provider</option>
                                            <option value="MTN">MTN Mobile Money</option>
                                            <option value="Vodafone">Vodafone Cash</option>
                                            <option value="AirtelTigo">AirtelTigo Money</option>
                                            <option value="Telecel">Telecel Cash</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control" name="phone_number" 
                                               placeholder="+233123456789" required>
                                        <div class="form-text">Include country code</div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Account Name *</label>
                                <input type="text" class="form-control" name="account_name" 
                                       placeholder="John Doe" required>
                                <div class="form-text">Name registered with mobile money account</div>
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'paypal':
                html = `
                    <div class="card bg-light mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fab fa-paypal me-2"></i>
                                PayPal Details
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">PayPal Email Address *</label>
                                <input type="email" class="form-control" name="email" 
                                       placeholder="john@example.com" required>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Make sure this email is associated with your PayPal account
                                </div>
                            </div>
                            <div class="alert alert-info">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>PayPal Requirements:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Your PayPal account must be verified</li>
                                    <li>Account must be in good standing</li>
                                    <li>Business accounts recommended for faster processing</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'stripe':
                html = `
                    <div class="card bg-light mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fab fa-stripe me-2"></i>
                                Stripe Details
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Stripe Account ID *</label>
                                <input type="text" class="form-control" name="account_id" 
                                       placeholder="acct_1234567890" required>
                                <div class="form-text">
                                    Your Stripe Connect account ID (starts with "acct_")
                                </div>
                            </div>
                            <div class="alert alert-info">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Stripe Requirements:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>You must have a Stripe Connect account</li>
                                    <li>Account must be fully verified</li>
                                    <li>All required business information must be provided</li>
                                </ul>
                            </div>
                            <div class="mt-3">
                                <a href="https://stripe.com/connect" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-external-link-alt me-1"></i>
                                    Create Stripe Connect Account
                                </a>
                            </div>
                        </div>
                    </div>
                `;
                break;
        }
        
        methodDetails.innerHTML = html;
        methodDetails.style.display = html ? 'block' : 'none';
    }
    
    // Form validation
    document.getElementById('addMethodForm').addEventListener('submit', function(e) {
        const methodType = document.querySelector('input[name="method_type"]:checked');
        
        if (!methodType) {
            e.preventDefault();
            alert('Please select a payment method type.');
            return;
        }
        
        // Additional validation based on method type
        if (methodType.value === 'paypal') {
            const email = document.querySelector('input[name="email"]');
            if (email && !email.value.includes('@')) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                email.focus();
                return;
            }
        }
        
        if (methodType.value === 'mobile_money') {
            const phone = document.querySelector('input[name="phone_number"]');
            if (phone && !phone.value.startsWith('+')) {
                e.preventDefault();
                alert('Please include the country code (e.g., +233).');
                phone.focus();
                return;
            }
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

.btn-outline-primary:hover,
.btn-outline-success:hover,
.btn-outline-info:hover,
.btn-outline-warning:hover {
    transform: translateY(-1px);
}

.form-label {
    font-weight: 600;
    color: #495057;
}

.alert {
    border-radius: 8px;
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
