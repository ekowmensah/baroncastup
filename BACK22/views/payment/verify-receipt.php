<?php include __DIR__ . '/../layout/public_header.php'; ?>

<!-- Receipt Verification Page -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <?php if (!isset($receipt)): ?>
            <!-- Verification Form -->
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="verification-icon mb-3">
                            <i class="fas fa-shield-check fa-3x text-primary"></i>
                        </div>
                        <h2 class="text-primary mb-2">Verify Receipt</h2>
                        <p class="text-muted">Enter your receipt code to verify its authenticity</p>
                    </div>

                    <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= APP_URL ?>/verify-receipt" class="verification-form">
                        <div class="mb-4">
                            <label for="receipt_code" class="form-label">Receipt Code</label>
                            <input type="text" 
                                   class="form-control form-control-lg text-center" 
                                   id="receipt_code" 
                                   name="receipt_code" 
                                   placeholder="Enter 8-character receipt code"
                                   value="<?= htmlspecialchars($receipt_code ?? '') ?>"
                                   maxlength="9"
                                   style="letter-spacing: 0.2em; font-family: monospace;"
                                   required>
                            <div class="form-text">
                                Receipt codes are 8 characters long and contain only letters and numbers (e.g., 7WEPC4TY)
                                <div id="character-count" class="mt-1">
                                    <span id="char-count">0</span>/8 characters
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-search me-2"></i>
                                Verify Receipt
                            </button>
                        </div>
                    </form>

                    <!-- Help Section -->
                    <div class="help-section mt-5 pt-4 border-top">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-question-circle me-2"></i>
                            How to Find Your Receipt Code
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="help-item">
                                    <i class="fas fa-receipt text-muted me-2"></i>
                                    <strong>On Your Receipt:</strong> Look for the 8-character code at the top of your receipt
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="help-item">
                                    <i class="fas fa-envelope text-muted me-2"></i>
                                    <strong>In Email:</strong> Check your email confirmation for the receipt code
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="help-item">
                                    <i class="fas fa-mobile-alt text-muted me-2"></i>
                                    <strong>SMS Notification:</strong> Receipt code may be included in SMS confirmations
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="help-item">
                                    <i class="fas fa-print text-muted me-2"></i>
                                    <strong>Printed Receipt:</strong> Code appears on any printed copies
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php else: ?>
            <!-- Verification Result -->
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    
                    <?php if ($is_valid): ?>
                    <!-- Valid Receipt -->
                    <div class="text-center mb-4">
                        <div class="success-icon mb-3">
                            <i class="fas fa-check-circle fa-4x text-success"></i>
                        </div>
                        <h2 class="text-success mb-2">Receipt Verified!</h2>
                        <p class="text-muted">This receipt is authentic and valid</p>
                    </div>

                    <!-- Receipt Details -->
                    <div class="verification-details bg-light rounded p-4 mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-success mb-3">
                                    <i class="fas fa-check-shield me-2"></i>
                                    Verification Details
                                </h6>
                                <div class="mb-2">
                                    <strong>Receipt Code:</strong><br>
                                    <span class="badge bg-success fs-6"><?= htmlspecialchars($receipt['short_code']) ?></span>
                                </div>
                                <div class="mb-2">
                                    <strong>Status:</strong><br>
                                    <span class="badge bg-success">Verified ✓</span>
                                </div>
                       <!--         <div class="mb-2">
                                    <strong>Transaction ID:</strong><br>
                                    <span class="text-muted">#<?= $transaction['id'] ?></span>
                                </div> -->
                                <div class="mb-2">
                                    <strong>Date:</strong><br>
                                    <span class="text-muted"><?= date('M j, Y g:i A', strtotime($receipt['created_at'])) ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-success mb-3">
                                    <i class="fas fa-vote-yea me-2"></i>
                                    Voting Details
                                </h6>
                                <div class="mb-2">
                                    <strong>Event:</strong><br>
                                    <span class="text-muted"><?= htmlspecialchars($event['name']) ?></span>
                                </div>
                                <div class="mb-2">
                                    <strong>Contestant:</strong><br>
                                    <span class="text-muted"><?= htmlspecialchars($contestant['name']) ?></span>
                                </div>
                                <?php if ($category): ?>
                                <div class="mb-2">
                                    <strong>Category:</strong><br>
                                    <span class="text-muted"><?= htmlspecialchars($category['name']) ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="mb-2">
                                    <strong>Amount Paid:</strong><br>
                                    <span class="text-success fw-bold">GH₵<?= number_format($transaction['amount'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Information -->
                    <div class="security-info border rounded p-3 mb-4">
                        <h6 class="text-success mb-2">
                            <i class="fas fa-lock me-2"></i>
                            Security Verification
                        </h6>
                        <p class="small text-muted mb-2">
                            ✓ Receipt code matches our records<br>
                            ✓ Transaction hash verified<br>
                            ✓ Payment confirmed as successful<br>
                            ✓ Vote recorded in the system
                        </p>
                        <p class="small text-muted mb-0">
                            This receipt has been cryptographically verified and is guaranteed authentic.
                        </p>
                    </div>

                    <?php else: ?>
                    <!-- Invalid Receipt -->
                    <div class="text-center mb-4">
                        <div class="error-icon mb-3">
                            <i class="fas fa-times-circle fa-4x text-danger"></i>
                        </div>
                        <h2 class="text-danger mb-2">Verification Failed</h2>
                        <p class="text-muted">This receipt could not be verified</p>
                    </div>

                    <!-- Error Details -->
                    <div class="alert alert-danger" role="alert">
                        <h6 class="alert-heading">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Receipt Verification Failed
                        </h6>
                        <p class="mb-2">The receipt code <strong><?= htmlspecialchars($receipt_code) ?></strong> was found in our system, but the verification hash does not match.</p>
                        <hr>
                        <p class="mb-0">
                            <strong>Possible reasons:</strong><br>
                            • Receipt may have been tampered with<br>
                            • System error during receipt generation<br>
                            • Data corruption in our records
                        </p>
                    </div>

                    <div class="alert alert-warning" role="alert">
                        <strong>What to do:</strong> Please contact our support team with this receipt code for manual verification.
                    </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="action-buttons text-center">
                        <a href="<?= APP_URL ?>/verify-receipt" class="btn btn-outline-primary btn-lg me-3">
                            <i class="fas fa-search me-2"></i>
                            Verify Another Receipt
                        </a>
                        <?php if ($is_valid): ?>
                        <button onclick="window.print()" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-print me-2"></i>
                            Print Verification
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<style>
/* Verification page styles */
.verification-icon i {
    animation: verificationPulse 2s ease-in-out infinite;
}

.success-icon i {
    animation: successBounce 1s ease-in-out;
}

.error-icon i {
    animation: errorShake 0.5s ease-in-out;
}

@keyframes verificationPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

@keyframes successBounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    60% { transform: translateY(-5px); }
}

@keyframes errorShake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.verification-form .form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.help-item {
    display: flex;
    align-items: flex-start;
    font-size: 0.9rem;
}

.help-item i {
    margin-top: 0.1rem;
    flex-shrink: 0;
}

.verification-details {
    border: 1px solid #e9ecef;
}

.security-info {
    background-color: #f8f9fa;
    border-color: #28a745 !important;
}

.btn-lg {
    border-radius: 25px;
    padding: 12px 30px;
    font-weight: 600;
}

.card {
    border-radius: 15px;
}

.badge {
    font-size: 0.9em;
}

/* Print styles */
@media print {
    .action-buttons {
        display: none !important;
    }
    
    .card {
        box-shadow: none !important;
        border: 1px solid #000 !important;
    }
    
    .bg-light {
        background-color: #f8f9fa !important;
        -webkit-print-color-adjust: exact;
    }
    
    .text-success {
        color: #28a745 !important;
        -webkit-print-color-adjust: exact;
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .action-buttons .btn {
        display: block;
        width: 100%;
        margin: 0.5rem 0;
    }
    
    .verification-details .col-md-6:first-child {
        margin-bottom: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const receiptInput = document.getElementById('receipt_code');
    
    if (receiptInput) {
        // Auto-focus on page load
        receiptInput.focus();
        
        // Initialize character count
        const initialValue = receiptInput.value.replace(/\s/g, '');
        updateCharacterCount(initialValue.length);
        
        // Handle input formatting and validation
        receiptInput.addEventListener('input', function() {
            // Remove any non-alphanumeric characters and convert to uppercase
            let cleanValue = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            
            // Limit to 8 characters
            if (cleanValue.length > 8) {
                cleanValue = cleanValue.substring(0, 8);
            }
            
            // Format with space after 4th character for readability
            if (cleanValue.length > 4) {
                this.value = cleanValue.substring(0, 4) + ' ' + cleanValue.substring(4);
            } else {
                this.value = cleanValue;
            }
            
            // Update character count and validation
            updateCharacterCount(cleanValue.length);
            
            // Remove browser validation error
            this.setCustomValidity('');
        });
        
        // Custom validation
        receiptInput.addEventListener('invalid', function() {
            const cleanValue = this.value.replace(/\s/g, '');
            if (cleanValue.length < 8) {
                this.setCustomValidity(`Please enter ${8 - cleanValue.length} more character${8 - cleanValue.length > 1 ? 's' : ''} (${cleanValue.length}/8)`);
            } else if (cleanValue.length > 8) {
                this.setCustomValidity('Receipt code cannot be longer than 8 characters');
            } else if (!/^[A-Z0-9]{8}$/.test(cleanValue)) {
                this.setCustomValidity('Receipt code must contain only letters and numbers');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Remove spacing before form submission
        receiptInput.closest('form').addEventListener('submit', function(e) {
            const cleanValue = receiptInput.value.replace(/\s/g, '');
            
            // Validate before submission
            if (cleanValue.length < 8) {
                e.preventDefault();
                receiptInput.setCustomValidity(`Please enter ${8 - cleanValue.length} more character${8 - cleanValue.length > 1 ? 's' : ''} (${cleanValue.length}/8)`);
                receiptInput.reportValidity();
                return false;
            } else if (cleanValue.length > 8) {
                e.preventDefault();
                receiptInput.setCustomValidity('Receipt code cannot be longer than 8 characters');
                receiptInput.reportValidity();
                return false;
            } else if (!/^[A-Z0-9]{8}$/.test(cleanValue)) {
                e.preventDefault();
                receiptInput.setCustomValidity('Receipt code must contain only letters and numbers');
                receiptInput.reportValidity();
                return false;
            }
            
            // Set clean value for submission
            receiptInput.value = cleanValue;
        });
    }
    
    // Function to update character count display
    function updateCharacterCount(count) {
        const charCountElement = document.getElementById('char-count');
        const characterCountElement = document.getElementById('character-count');
        
        if (charCountElement) {
            charCountElement.textContent = count;
            
            // Color coding based on progress
            if (count === 8) {
                characterCountElement.className = 'mt-1 text-success';
            } else if (count > 5) {
                characterCountElement.className = 'mt-1 text-warning';
            } else {
                characterCountElement.className = 'mt-1 text-muted';
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../layout/public_footer.php'; ?>
