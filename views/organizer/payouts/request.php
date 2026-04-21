<?php 
$content = ob_start();
if (!isset($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$csrf_token_value = htmlspecialchars($_SESSION['csrf_token']);
?>

<!-- Request Payout Form -->
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    Request Payout
                </h5>
            </div>
            <div class="card-body">
                <!-- Balance Info -->
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Available Balance:</strong> GH₵<?= number_format($balance['available'], 2) ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Minimum Payout:</strong> GH₵<?= number_format($schedule['minimum_amount'], 2) ?>
                        </div>
                    </div>
                </div>
                
                <?php if (empty($payout_methods)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    You need to add a payout method before requesting a payout.
                    <a href="<?= ORGANIZER_URL ?>/payouts/add-method" class="btn btn-sm btn-warning ms-2">
                        Add Payout Method
                    </a>
                </div>
                <?php else: ?>
                
                <form method="POST" id="payoutRequestForm">
                    <input type="hidden" name="_csrf_token" value="<?= $csrf_token_value ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Payout Amount *</label>
                                <div class="input-group">
                                    <span class="input-group-text">GH₵</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="amount" 
                                           name="amount" 
                                           step="0.01" 
                                           min="<?= $schedule['minimum_amount'] ?>" 
                                           max="<?= $balance['available'] ?>"
                                           placeholder="0.00"
                                           required>
                                </div>
                                <div class="form-text">
                                    Available: GH₵<?= number_format($balance['available'], 2) ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payout_method_id" class="form-label">Payout Method *</label>
                                <select class="form-select" id="payout_method_id" name="payout_method_id" required>
                                    <option value="">Select payout method</option>
                                    <?php foreach ($payout_methods as $method): ?>
                                    <option value="<?= $method['id'] ?>" 
                                            data-method-type="<?= $method['method_type'] ?>"
                                            <?= $method['is_default'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($method['method_name']) ?>
                                        <?php if ($method['is_default']): ?>
                                        (Default)
                                        <?php endif; ?>
                                        <?php if (!$method['is_verified']): ?>
                                        - Unverified
                                        <?php endif; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Fee Calculation -->
                    <div class="card bg-light mb-3" id="feeCalculation" style="display: none;">
                        <div class="card-body">
                            <h6 class="card-title">Payout Breakdown</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="h5 mb-1" id="requestedAmount">GH₵0.00</div>
                                        <div class="text-muted small">Requested Amount</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="h5 mb-1 text-warning" id="processingFee">GH₵0.00</div>
                                        <div class="text-muted small">Processing Fee</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="h5 mb-1 text-success" id="netAmount">GH₵0.00</div>
                                        <div class="text-muted small">You'll Receive</div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted" id="feeDetails"></small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Amount Buttons -->
                    <div class="mb-3">
                        <label class="form-label">Quick Select:</label>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-secondary btn-sm quick-amount" 
                                    data-amount="<?= min(50, $balance['available']) ?>">
                                GH₵50
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm quick-amount" 
                                    data-amount="<?= min(100, $balance['available']) ?>">
                                GH₵100
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm quick-amount" 
                                    data-amount="<?= min(250, $balance['available']) ?>">
                                GH₵250
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm quick-amount" 
                                    data-amount="<?= $balance['available'] ?>">
                                All Available
                            </button>
                        </div>
                    </div>
                    
                    <!-- Terms and Conditions -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                            <label class="form-check-label" for="agreeTerms">
                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">payout terms and conditions</a>
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= ORGANIZER_URL ?>/payouts" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Back to Dashboard
                        </a>
                        <button type="submit" class="btn btn-success" id="submitBtn" disabled>
                            <i class="fas fa-paper-plane me-2"></i>
                            Submit Payout Request
                        </button>
                    </div>
                </form>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payout Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Processing Times</h6>
                <ul>
                    <li><strong>Bank Transfer:</strong> 1-3 business days</li>
                    <li><strong>Mobile Money:</strong> Instant to 24 hours</li>
                    <li><strong>PayPal:</strong> Instant to 1 business day</li>
                    <li><strong>Stripe:</strong> 2-7 business days</li>
                </ul>
                
                <h6>Processing Fees</h6>
                <ul>
                    <li><strong>Bank Transfer:</strong> 1.0% + GH₵0.50</li>
                    <li><strong>Mobile Money:</strong> 1.5% + GH₵0.25</li>
                    <li><strong>PayPal:</strong> 2.9% + GH₵0.30</li>
                    <li><strong>Stripe:</strong> 2.9% + GH₵0.30</li>
                </ul>
                
                <h6>Important Notes</h6>
                <ul>
                    <li>Payout requests cannot be cancelled once submitted</li>
                    <li>Ensure your payout method details are correct</li>
                    <li>Failed payouts will be returned to your balance</li>
                    <li>Processing fees are non-refundable</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount');
    const methodSelect = document.getElementById('payout_method_id');
    const feeCalculation = document.getElementById('feeCalculation');
    const agreeTerms = document.getElementById('agreeTerms');
    const submitBtn = document.getElementById('submitBtn');
    const quickAmountBtns = document.querySelectorAll('.quick-amount');
    
    // Quick amount buttons
    quickAmountBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const amount = this.dataset.amount;
            amountInput.value = amount;
            calculateFees();
        });
    });
    
    // Calculate fees when amount or method changes
    amountInput.addEventListener('input', calculateFees);
    methodSelect.addEventListener('change', calculateFees);
    
    // Enable/disable submit button
    function updateSubmitButton() {
        const amount = parseFloat(amountInput.value) || 0;
        const method = methodSelect.value;
        const terms = agreeTerms.checked;
        const minAmount = <?= $schedule['minimum_amount'] ?>;
        const maxAmount = <?= $balance['available'] ?>;
        
        const isValid = amount >= minAmount && amount <= maxAmount && method && terms;
        submitBtn.disabled = !isValid;
    }
    
    amountInput.addEventListener('input', updateSubmitButton);
    methodSelect.addEventListener('change', updateSubmitButton);
    agreeTerms.addEventListener('change', updateSubmitButton);
    
    function calculateFees() {
        const amount = parseFloat(amountInput.value) || 0;
        const methodType = methodSelect.selectedOptions[0]?.dataset.methodType;
        
        if (amount > 0 && methodType) {
            fetch('<?= ORGANIZER_URL ?>/api/payouts/calculate-fees', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `amount=${amount}&method_type=${methodType}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('requestedAmount').textContent = 'GH₵' + data.amount.toFixed(2);
                    document.getElementById('processingFee').textContent = 'GH₵' + data.processing_fee.toFixed(2);
                    document.getElementById('netAmount').textContent = 'GH₵' + data.net_amount.toFixed(2);
                    document.getElementById('feeDetails').textContent = 
                        `Processing fee: ${data.fee_percentage}% + GH₵${data.fee_fixed.toFixed(2)}`;
                    
                    feeCalculation.style.display = 'block';
                } else {
                    feeCalculation.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error calculating fees:', error);
                feeCalculation.style.display = 'none';
            });
        } else {
            feeCalculation.style.display = 'none';
        }
    }
    
    // Form submission
    document.getElementById('payoutRequestForm').addEventListener('submit', function(e) {
        const amount = parseFloat(amountInput.value) || 0;
        const maxAmount = <?= $balance['available'] ?>;
        
        if (amount > maxAmount) {
            e.preventDefault();
            alert('Payout amount cannot exceed your available balance.');
            return;
        }
        
        if (!confirm('Are you sure you want to submit this payout request? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
});
</script>

<style>
.card {
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.input-group-text {
    background-color: #e9ecef;
    border-color: #ced4da;
}

.btn-group .btn {
    border-radius: 0.375rem;
    margin-right: 0.25rem;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

#feeCalculation {
    border: 1px solid #dee2e6;
}

.quick-amount:hover {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

@media (max-width: 768px) {
    .btn-group {
        display: flex;
        flex-wrap: wrap;
    }
    
    .btn-group .btn {
        flex: 1;
        margin-bottom: 0.25rem;
    }
}
</style>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../../layout/organizer_layout.php';
?>
