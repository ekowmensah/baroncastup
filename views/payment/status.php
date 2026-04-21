<?php include __DIR__ . '/../layout/public_header.php'; ?>

<!-- Payment Status Page -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5 text-center">
                    
                    <?php 
                    $status = $transaction['status'] ?? 'unknown';
                    if ($status === 'success'): ?>
                        <!-- Success Status -->
                        <div class="mb-4">
                            <div class="success-icon mb-3">
                                <i class="fas fa-check-circle fa-4x text-success"></i>
                            </div>
                            <h2 class="text-success mb-3">Payment Successful!</h2>
                            <p class="lead text-muted">Your vote has been recorded successfully.</p>
                        </div>
                        
                    <?php elseif ($status === 'pending'): ?>
                        <!-- Pending Status -->
                        <div class="mb-4">
                            <div class="pending-icon mb-3">
                                <i class="fas fa-clock fa-4x text-warning"></i>
                            </div>
                            <h2 class="text-warning mb-3">Payment Pending</h2>
                            <p class="lead text-muted">Your payment is being processed. Please wait...</p>
                            
                            <!-- Auto-refresh indicator -->
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle me-2"></i>
                                This page will automatically refresh every 10 seconds to check payment status.
                            </div>
                        </div>
                        
                    <?php elseif ($status === 'failed'): ?>
                        <!-- Failed Status -->
                        <div class="mb-4">
                            <div class="error-icon mb-3">
                                <i class="fas fa-times-circle fa-4x text-danger"></i>
                            </div>
                            <h2 class="text-danger mb-3">Payment Failed</h2>
                            <p class="lead text-muted">Unfortunately, your payment could not be processed.</p>
                            
                            <?php if (!empty($transaction['failure_reason'])): ?>
                                <div class="alert alert-danger mt-3">
                                    <strong>Error:</strong> <?= htmlspecialchars($transaction['failure_reason']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                    <?php else: ?>
                        <!-- Unknown Status -->
                        <div class="mb-4">
                            <div class="unknown-icon mb-3">
                                <i class="fas fa-question-circle fa-4x text-secondary"></i>
                            </div>
                            <h2 class="text-secondary mb-3">Payment Status Unknown</h2>
                            <p class="lead text-muted">We're checking the status of your payment...</p>
                        </div>
                    <?php endif; ?>

                    <!-- Transaction Details -->
                    <div class="transaction-details bg-light rounded p-4 mb-4">
                        <h5 class="mb-3">Transaction Details</h5>
                        
                        <div class="row text-start">
                            <div class="col-md-6 mb-3">
                                <strong>Transaction ID:</strong><br>
                                <span class="text-muted">#<?= $transaction['id'] ?? 'Unknown' ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Amount:</strong><br>
                                <span class="text-success fs-5">GH₵<?= number_format($transaction['amount'] ?? 0, 2) ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Event:</strong><br>
                                <span class="text-muted"><?= htmlspecialchars($event['name'] ?? 'Unknown Event') ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Contestant:</strong><br>
                                <span class="text-muted"><?= htmlspecialchars($contestant['name'] ?? 'Unknown Contestant') ?></span>
                            </div>
                            <?php if ($category): ?>
                            <div class="col-md-6 mb-3">
                                <strong>Category:</strong><br>
                                <span class="text-muted"><?= htmlspecialchars($category['name']) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="col-md-6 mb-3">
                                <strong>Votes:</strong><br>
                                <span class="text-primary fw-bold"><?= number_format($transaction['vote_quantity'] ?? 0) ?> votes</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Voter:</strong><br>
                                <span class="text-muted"><?= htmlspecialchars($transaction['voter_name'] ?? 'Unknown') ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Date:</strong><br>
                                <span class="text-muted">
                                    <?php if (!empty($transaction['created_at'])): ?>
                                        <?= date('M j, Y g:i A', strtotime($transaction['created_at'])) ?>
                                    <?php else: ?>
                                        Unknown
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <?php if ($status === 'success'): ?>
                            <a href="<?= APP_URL ?>/events/<?= $event['id'] ?? '' ?>" class="btn btn-primary btn-lg me-3">
                                <i class="fas fa-chart-bar me-2"></i>
                                View Results
                            </a>
                            <a href="<?= APP_URL ?>/events" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-plus me-2"></i>
                                Vote Again
                            </a>
                            
                        <?php elseif ($status === 'failed'): ?>
                            <a href="<?= APP_URL ?>/vote?contestant_id=<?= $transaction['contestant_id'] ?? '' ?>&category_id=<?= $transaction['category_id'] ?? '' ?>&event_id=<?= $transaction['event_id'] ?? '' ?>&source=retry" class="btn btn-primary btn-lg me-3">
                                <i class="fas fa-redo me-2"></i>
                                Try Again
                            </a>
                       <!--     <a href="<?= APP_URL ?>/vote-shortcode" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to Search
                            </a> -->
                            
                        <?php elseif ($status === 'pending'): ?>
                         <!--   <button onclick="simulatePayment()" class="btn btn-warning btn-lg me-3">
                                <i class="fas fa-play me-2"></i>
                                Simulate Payment (Test)
                            </button>
                            <a href="<?= APP_URL ?>/vote-shortcode" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to Voting
                            </a>-->
                        <?php else: ?>
                      <!--      <a href="<?= APP_URL ?>/vote-shortcode" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to Voting
                            </a> -->
                        <?php endif; ?>
                    </div>

                    <!-- Receipt Download (for successful payments) -->
                    <?php if ($status === 'success'): ?>
                        <div class="mt-4 pt-3 border-top">
                            <div class="receipt-section text-center">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-receipt me-2"></i>
                                    Your Receipt
                                </h6>
                                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                                    <a href="<?= APP_URL ?>/payment/receipt/<?= $transaction['id'] ?? '' ?>" 
                                       class="btn btn-outline-primary">
                                        <i class="fas fa-download me-2"></i>
                                        View Receipt
                                    </a>
                                    <button onclick="printReceipt(<?= $transaction['id'] ?? '' ?>)" 
                                            class="btn btn-outline-secondary">
                                        <i class="fas fa-print me-2"></i>
                                        Print Receipt
                                    </button>
                                    <a href="<?= APP_URL ?>/verify-receipt" 
                                       class="btn btn-outline-info"
                                       target="_blank">
                                        <i class="fas fa-shield-check me-2"></i>
                                        Verify Receipt
                                    </a>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    Keep your receipt for verification and records
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Auto-refresh for pending payments -->
<?php if ($status === 'pending'): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh every 10 seconds for pending payments
    setTimeout(function() {
        window.location.reload();
    }, 10000);
    
    // Also check via AJAX every 5 seconds
    const checkStatus = setInterval(function() {
        fetch('<?= APP_URL ?>/api/payment/status/<?= $transaction['id'] ?? '' ?>')
            .then(response => response.json())
            .then(data => {
                if (data.status !== 'pending') {
                    clearInterval(checkStatus);
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error checking payment status:', error);
            });
    }, 5000);
});

// Simulate payment function
function simulatePayment() {
    if (!confirm('This will simulate a successful payment for testing purposes. Continue?')) {
        return;
    }
    
    fetch('<?= APP_URL ?>/api/payment/simulate/<?= $transaction['id'] ?? '' ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Payment simulated successfully!');
                window.location.reload();
            } else {
                alert('Simulation failed: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Simulation error:', error);
            alert('An error occurred during simulation');
        });
}

// Print receipt function
function printReceipt(transactionId) {
    // Open receipt in new window and print
    const receiptWindow = window.open(
        '<?= APP_URL ?>/payment/receipt/' + transactionId, 
        'receipt', 
        'width=800,height=600,scrollbars=yes,resizable=yes'
    );
    
    receiptWindow.onload = function() {
        receiptWindow.print();
    };
}
</script>
<?php endif; ?>

<style>
.success-icon i {
    animation: successPulse 2s infinite;
}

.pending-icon i {
    animation: pendingSpin 2s linear infinite;
}

@keyframes successPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

@keyframes pendingSpin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.transaction-details {
    border: 1px solid #e9ecef;
}

.btn-lg {
    border-radius: 25px;
    padding: 12px 30px;
    font-weight: 600;
}

.card {
    border-radius: 15px;
}

.receipt-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
    padding: 1.5rem;
    margin-top: 1rem;
}

.receipt-section .btn {
    border-radius: 20px;
    font-weight: 500;
    min-width: 120px;
}

.receipt-section .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

@media (max-width: 576px) {
    .receipt-section .d-flex {
        gap: 0.5rem !important;
    }
    
    .receipt-section .btn {
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
        min-width: auto;
    }
}
</style>

<?php include __DIR__ . '/../layout/public_footer.php'; ?>
