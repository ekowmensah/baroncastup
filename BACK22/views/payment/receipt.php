<?php include __DIR__ . '/../layout/public_header.php'; ?>

<!-- Payment Receipt Page -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    
                    <!-- Receipt Header -->
                    <div class="text-center mb-4">
                        <div class="receipt-icon mb-3">
                            <i class="fas fa-receipt fa-3x text-primary"></i>
                        </div>
                        <h2 class="text-primary mb-2">Payment Receipt</h2>
                        <p class="text-muted">Thank you for your vote!</p>
                    </div>

                    <!-- Receipt Details -->
                    <div class="receipt-details bg-light rounded p-4 mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">Receipt Information</h5>
                                <div class="mb-2">
                                    <strong>Receipt Code:</strong><br>
                                    <span class="badge bg-primary fs-6"><?= htmlspecialchars($receipt['short_code']) ?></span>
                                </div>
                           <!--     <div class="mb-2">
                                    <strong>Transaction ID:</strong><br>
                                    <span class="text-muted">#<?= $transaction['id'] ?></span>
                                </div> -->
                                <div class="mb-2">
                                    <strong>Date & Time:</strong><br>
                                    <span class="text-muted"><?= date('M j, Y g:i A', strtotime($receipt['created_at'])) ?></span>
                                </div>
                                <div class="mb-2">
                                    <strong>Status:</strong><br>
                                    <span class="badge bg-success">Paid</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">Voting Details</h5>
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
                                    <strong>Votes Cast:</strong><br>
                                    <span class="text-primary fw-bold">1 vote</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="payment-summary bg-primary text-white rounded p-4 mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-1">Total Amount Paid</h5>
                                <small class="opacity-75">Including all fees and charges</small>
                            </div>
                            <div class="col-md-4 text-end">
                                <h3 class="mb-0">GH₵<?= number_format($transaction['amount'], 2) ?></h3>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="payment-method bg-light rounded p-3 mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <strong>Payment Method:</strong>
                                <span class="text-muted ms-2">Mobile Money</span>
                            </div>
                            <div class="col-md-4 text-end">
                                <strong>Phone:</strong>
                                <span class="text-muted"><?= htmlspecialchars($transaction['msisdn']) ?></span>
                            </div>
                        </div>
                        <?php if (!empty($transaction['provider_reference'])): ?>
                        <div class="row mt-2">
                            <div class="col-12">
                                <small class="text-muted">
                                    <strong>Reference:</strong> <?= htmlspecialchars($transaction['provider_reference']) ?>
                                </small>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Verification Info -->
                    <div class="verification-info border rounded p-3 mb-4">
                        <h6 class="text-primary mb-2">
                            <i class="fas fa-shield-alt me-2"></i>
                            Receipt Verification
                        </h6>
                        <p class="small text-muted mb-2">
                            This receipt can be verified using the receipt code: <strong><?= htmlspecialchars($receipt['short_code']) ?></strong>
                        </p>
                        <p class="small text-muted mb-2">
                            <a href="<?= APP_URL ?>/verify-receipt" class="text-decoration-none" target="_blank">
                                <i class="fas fa-external-link-alt me-1"></i>
                                Verify this receipt online
                            </a>
                        </p>
                        <p class="small text-muted mb-0">
                            Keep this receipt for your records. It serves as proof of your vote and payment.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons text-center">
                        <button onclick="window.print()" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-print me-2"></i>
                            Print Receipt
                        </button>
                        <a href="<?= APP_URL ?>/events/<?= $event['id'] ?>" class="btn btn-outline-primary btn-lg me-3">
                            <i class="fas fa-chart-bar me-2"></i>
                            View Results
                        </a>
                        <a href="<?= APP_URL ?>/vote-shortcode" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-plus me-2"></i>
                            Vote Again
                        </a>
                    </div>

                    <!-- Footer -->
                    <div class="receipt-footer text-center mt-4 pt-3 border-top">
                        <small class="text-muted">
                            <i class="fas fa-vote-yea me-1"></i>
                            <?= APP_NAME ?> - Secure Online Voting Platform<br>
                            Generated on <?= date('M j, Y g:i A') ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Print styles */
@media print {
    body * {
        visibility: hidden;
    }
    
    .container, .container * {
        visibility: visible;
    }
    
    .container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    
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
    
    .bg-primary {
        background-color: #0d6efd !important;
        -webkit-print-color-adjust: exact;
    }
    
    .text-white {
        color: #fff !important;
        -webkit-print-color-adjust: exact;
    }
}

/* Receipt styling */
.receipt-icon i {
    animation: receiptPulse 2s ease-in-out infinite;
}

@keyframes receiptPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.receipt-details {
    border: 1px solid #e9ecef;
}

.payment-summary {
    background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
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

/* Responsive adjustments */
@media (max-width: 768px) {
    .action-buttons .btn {
        display: block;
        width: 100%;
        margin: 0.5rem 0;
    }
    
    .payment-summary .text-end {
        text-align: center !important;
        margin-top: 1rem;
    }
}
</style>

<?php include __DIR__ . '/../layout/public_footer.php'; ?>
