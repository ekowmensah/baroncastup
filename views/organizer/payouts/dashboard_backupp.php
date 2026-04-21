<?php 
$content = ob_start(); 
?>

<style>
/* Modern Professional Payout Dashboard */
.payout-dashboard {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 2rem 0;
}

.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.dashboard-header {
    text-align: center;
    color: white;
    margin-bottom: 3rem;
}

.dashboard-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.dashboard-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    font-weight: 300;
}

.glass-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 2rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.glass-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
}

.balance-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.balance-card {
    padding: 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.balance-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2);
}

.balance-amount {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.balance-label {
    color: #6b7280;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.875rem;
}

.status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.workflow-section {
    margin-bottom: 3rem;
}

.workflow-steps {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.workflow-step {
    text-align: center;
    padding: 1.5rem;
    position: relative;
}

.step-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
}

.step-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #1f2937;
}

.step-description {
    font-size: 0.875rem;
    color: #6b7280;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-bottom: 3rem;
}

.btn-primary-gradient {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border: none;
    color: white;
    padding: 1rem 2rem;
    border-radius: 2rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.btn-primary-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
    color: white;
}

.payout-history {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 2rem;
    padding: 2rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.section-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f3f4f6;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

@media (max-width: 768px) {
    .workflow-steps {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .action-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .balance-amount {
        font-size: 2rem;
    }
}
</style>

<div class="payout-dashboard">
<div class="dashboard-container">

<!-- Dashboard Header -->
<div class="dashboard-header">
    <h1 class="dashboard-title">Payout Management</h1>
    <p class="dashboard-subtitle">Monitor your earnings and manage payout requests with our advanced approval system</p>
</div>
<!-- Balance Overview -->
<div class="balance-grid">
    <div class="glass-card balance-card">
        <div class="balance-amount">GH₵<?= number_format($balance['available'] ?? 0, 2) ?></div>
        <div class="balance-label">Available Balance</div>
        <div class="status-indicator bg-success text-white">
            <i class="fas fa-check-circle"></i>
            Ready to withdraw
        </div>
    </div>
    
    <div class="glass-card balance-card">
        <div class="balance-amount">GH₵<?= number_format(($balance['pending_approval'] ?? 0) + ($balance['approved_pending'] ?? 0) + ($balance['processing'] ?? 0), 2) ?></div>
        <div class="balance-label">Total Pending</div>
        <?php if (isset($balance['pending_approval']) && $balance['pending_approval'] > 0): ?>
            <div class="status-indicator bg-warning text-dark">
                <i class="fas fa-clock"></i>Awaiting Admin Approval
            </div>
        <?php elseif (isset($balance['approved_pending']) && $balance['approved_pending'] > 0): ?>
            <div class="status-indicator bg-info text-white">
                <i class="fas fa-check"></i>Approved - Processing Soon
            </div>
        <?php elseif (isset($balance['processing']) && $balance['processing'] > 0): ?>
            <div class="status-indicator bg-primary text-white">
                <i class="fas fa-spinner fa-spin"></i>Currently Processing
            </div>
        <?php else: ?>
            <div class="status-indicator bg-light text-muted">
                <i class="fas fa-info-circle"></i>No pending requests
            </div>
        <?php endif; ?>
    </div>
    
    <div class="glass-card balance-card">
        <div class="balance-amount">GH₵<?= number_format($balance['total_earned'] ?? 0, 2) ?></div>
        <div class="balance-label">Total Earned</div>
        <div class="status-indicator bg-info text-white">
            <i class="fas fa-chart-line"></i>
            Lifetime earnings
        </div>
    </div>
    
    <div class="glass-card balance-card">
        <div class="balance-amount">$<?= number_format($balance['total_paid'] ?? 0, 2) ?></div>
        <div class="balance-label">Total Paid Out</div>
        <div class="status-indicator bg-success text-white">
            <i class="fas fa-money-bill-wave"></i>
            Successfully paid
        </div>
    </div>
</div>
                
<!-- Payout Workflow Process -->
<div class="workflow-section">
    <div class="glass-card">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-route me-2"></i>
                Payout Process Workflow
            </h3>
        </div>
        
        <div class="workflow-steps">
            <div class="workflow-step">
                <div class="step-icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="step-title">1. Request</div>
                <div class="step-description">Submit your payout request with preferred method</div>
            </div>
            
            <div class="workflow-step">
                <div class="step-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="step-title">2. Admin Review</div>
                <div class="step-description">Our team reviews your request (usually within 24 hours)</div>
            </div>
            
            <div class="workflow-step">
                <div class="step-icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <div class="step-title">3. Processing</div>
                <div class="step-description">Once approved, your payout enters the processing queue</div>
            </div>
            
            <div class="workflow-step">
                <div class="step-icon">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="step-title">4. Complete</div>
                <div class="step-description">Funds are sent to your chosen payout method</div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="action-buttons">
    <?php if ($can_request_payout && ($balance['available'] ?? 0) >= ($schedule['minimum_amount'] ?? 0)): ?>
        <a href="<?= ORGANIZER_URL ?>/payouts/request" class="btn btn-primary-gradient">
            <i class="fas fa-money-bill-wave me-2"></i>
            Request Payout Now
        </a>
    <?php endif; ?>
    
    <a href="<?= ORGANIZER_URL ?>/payouts/methods" class="btn btn-outline-light">
        <i class="fas fa-credit-card me-2"></i>
        Manage Methods
    </a>
    
    <a href="<?= ORGANIZER_URL ?>/payouts/settings" class="btn btn-outline-light">
        <i class="fas fa-cog me-2"></i>
        Settings
    </a>
</div>

<!-- Status Breakdown -->
                <?php if (isset($balance['pending_approval']) || isset($balance['approved_pending']) || isset($balance['processing'])): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-2"></i>
                                Payout Status Breakdown
                            </h6>
                            <div class="row">
                                <?php if (isset($balance['pending_approval']) && $balance['pending_approval'] > 0): ?>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-hourglass-half text-warning me-2"></i>
                                        <div>
                                            <div class="fw-bold">$<?= number_format($balance['pending_approval'], 2) ?></div>
                                            <small class="text-muted">Awaiting Admin Approval</small>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (isset($balance['approved_pending']) && $balance['approved_pending'] > 0): ?>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <div>
                                            <div class="fw-bold">$<?= number_format($balance['approved_pending'], 2) ?></div>
                                            <small class="text-muted">Approved, Processing Soon</small>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (isset($balance['processing']) && $balance['processing'] > 0): ?>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-spinner text-primary me-2"></i>
                                        <div>
                                            <div class="fw-bold">$<?= number_format($balance['processing'], 2) ?></div>
                                            <small class="text-muted">Currently Processing</small>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Workflow Information -->
                            <div class="mt-3 pt-3 border-top">
                                <small class="text-muted">
                                    <strong>Payout Process:</strong> 
                                    Request → Admin Approval → Processing → Payment Sent
                                    <br>
                                    <i class="fas fa-info-circle me-1"></i>
                                    All payout requests require admin approval before processing. You'll be notified of any status changes.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- New Workflow Information for First-time Users -->
                <?php if (empty($recent_payouts) && ($balance['available'] ?? 0) > 0): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="alert alert-success">
                            <h6 class="alert-heading">
                                <i class="fas fa-rocket me-2"></i>
                                Ready for Your First Payout!
                            </h6>
                            <p class="mb-2">You have funds available for payout. Here's how our new approval process works:</p>
                            <ol class="mb-2">
                                <li><strong>Request:</strong> Submit your payout request with your preferred method</li>
                                <li><strong>Review:</strong> Our admin team reviews your request (usually within 24 hours)</li>
                                <li><strong>Approval:</strong> Once approved, your payout enters the processing queue</li>
                                <li><strong>Payment:</strong> Funds are sent to your chosen payout method</li>
                            </ol>
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                This process ensures secure and verified payouts for all organizers.
                            </small>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($can_request_payout && $balance['available'] >= $schedule['minimum_amount']): ?>
                <div class="text-center mt-4">
                    <a href="<?= ORGANIZER_URL ?>/payouts/request" class="btn btn-success btn-lg">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        Request Payout
                    </a>
                </div>
                <?php elseif ($balance['available'] < $schedule['minimum_amount']): ?>
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle me-2"></i>
                    Minimum payout amount is $<?= number_format($schedule['minimum_amount'], 2) ?>. 
                    Current available balance: $<?= number_format($balance['available'], 2) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= ORGANIZER_URL ?>/payouts/request" class="btn btn-outline-success">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        Request Payout
                    </a>
                    <a href="<?= ORGANIZER_URL ?>/payouts/methods" class="btn btn-outline-primary">
                        <i class="fas fa-credit-card me-2"></i>
                        Manage Methods
                    </a>
                    <a href="<?= ORGANIZER_URL ?>/payouts/settings" class="btn btn-outline-secondary">
                        <i class="fas fa-cog me-2"></i>
                        Payout Settings
                    </a>
                    <a href="<?= ORGANIZER_URL ?>/payouts/history" class="btn btn-outline-info">
                        <i class="fas fa-history me-2"></i>
                        View History
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Payout Schedule Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Payout Schedule
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Frequency:</strong> 
                    <span class="badge bg-primary"><?= ucfirst($schedule['frequency']) ?></span>
                </div>
                <div class="mb-3">
                    <strong>Minimum Amount:</strong> 
                    $<?= number_format($schedule['minimum_amount'], 2) ?>
                </div>
                <div class="mb-3">
                    <strong>Auto Payout:</strong> 
                    <?php if ($schedule['auto_payout_enabled']): ?>
                        <span class="badge bg-success">Enabled</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Disabled</span>
                    <?php endif; ?>
                </div>
                <?php if ($schedule['next_payout_date']): ?>
                <div class="mb-3">
                    <strong>Next Payout:</strong> 
                    <?= date('M j, Y', strtotime($schedule['next_payout_date'])) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Stats -->
<?php if ($revenue_stats): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Revenue Statistics
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="h4 text-success mb-1">$<?= number_format($revenue_stats['total_gross'] ?? 0, 2) ?></div>
                            <div class="text-muted small">Total Gross Revenue</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="h4 text-info mb-1">$<?= number_format($revenue_stats['total_net_amount'] ?? 0, 2) ?></div>
                            <div class="text-muted small">Net Revenue</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="h4 text-warning mb-1"><?= number_format($revenue_stats['transaction_count'] ?? 0) ?></div>
                            <div class="text-muted small">Transactions</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="h4 text-primary mb-1">
                                <?php 
                                $avgTransaction = ($revenue_stats['transaction_count'] ?? 0) > 0 
                                    ? ($revenue_stats['total_gross'] ?? 0) / $revenue_stats['transaction_count'] 
                                    : 0;
                                ?>
                                $<?= number_format($avgTransaction, 2) ?>
                            </div>
                            <div class="text-muted small">Avg Transaction</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
                                            <i class="fas fa-receipt me-1"></i>Receipt
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary btn-sm" 
                                            onclick="viewPayoutDetails(<?= $payout['id'] ?>)" 
                                            title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($payout['status'] === 'pending'): ?>
                                        <button class="btn btn-outline-danger btn-sm" 
                                                onclick="cancelPayout(<?= $payout['id'] ?>)" 
                                                title="Cancel Request">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Payout Methods -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-credit-card me-2"></i>
                    Payout Methods
                </h5>
                <a href="<?= ORGANIZER_URL ?>/payouts/add-method" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-plus"></i>
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($payout_methods)): ?>
                <?php foreach ($payout_methods as $method): ?>
                <div class="d-flex align-items-center mb-3 p-2 border rounded">
                    <div class="flex-grow-1">
                        <div class="fw-bold"><?= htmlspecialchars($method['method_name']) ?></div>
                        <small class="text-muted">
                            <?php
                            // Format account details for display
                            $details = json_decode($method['account_details'], true);
                            $displayText = '';
                            
                            switch ($method['method_type']) {
                                case 'bank_transfer':
                                    $displayText = ($details['bank_name'] ?? 'Bank') . ' - ****' . substr($details['account_number'] ?? '', -4);
                                    break;
                                case 'mobile_money':
                                    $displayText = ($details['provider'] ?? 'Mobile') . ' - ****' . substr($details['phone_number'] ?? '', -4);
                                    break;
                                case 'paypal':
                                    $displayText = $details['email'] ?? 'PayPal Account';
                                    break;
                                case 'stripe':
                                    $displayText = 'Stripe - ****' . substr($details['account_id'] ?? '', -4);
                                    break;
                                default:
                                    $displayText = ucwords(str_replace('_', ' ', $method['method_type']));
                            }
                            
                            echo htmlspecialchars($displayText);
                            ?>
                        </small>
                        <?php if ($method['is_default']): ?>
                        <span class="badge bg-primary ms-2">Default</span>
                        <?php endif; ?>
                        <?php if ($method['is_verified']): ?>
                        <span class="badge bg-success ms-1">Verified</span>
                        <?php else: ?>
                        <span class="badge bg-warning ms-1">Unverified</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="text-center py-3">
                    <i class="fas fa-credit-card fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-2">No payout methods configured</p>
                    <a href="<?= ORGANIZER_URL ?>/payouts/add-method" class="btn btn-sm btn-primary">
                        Add Method
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.badge {
    font-size: 0.75em;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.btn-lg {
    padding: 12px 30px;
    font-size: 1.1rem;
}

@media (max-width: 768px) {
    .col-md-3 {
        margin-bottom: 1rem;
    }
    
    .d-grid .btn {
        margin-bottom: 0.5rem;
    }
}

.btn-xs {
    padding: 0.125rem 0.25rem;
    font-size: 0.75rem;
}
</style>

<script>
function downloadPayoutReceipt(payoutId) {
    // Open receipt in new window/tab
    window.open(`<?= ORGANIZER_URL ?>/payouts/${payoutId}/receipt`, '_blank');
}
</script>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../../layout/organizer_layout.php';
?>
