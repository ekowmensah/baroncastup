<?php
// Real transaction data with proper joins is now provided by the controller
// All event names, contestant names, and vote counts come from the database
?>

<!-- Transactions Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-exchange-alt me-2"></i>
            Transactions
        </h2>
        <p class="text-muted mb-0">View and manage all your financial transactions</p>
    </div>
    <div>
        <div class="btn-group" role="group">
            <button class="btn btn-outline-primary" onclick="window.location.reload()">
                <i class="fas fa-sync me-2"></i>Refresh
            </button>
            <a href="<?= ORGANIZER_URL ?>/financial/transactions/export" class="btn btn-outline-success">
                <i class="fas fa-download me-2"></i>Export CSV
            </a>
        </div>
    </div>
</div>

<!-- Transaction Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <label class="form-label small">Date Range</label>
                        <select class="form-select form-select-sm" id="dateRange">
                            <option value="today">Today</option>
                            <option value="week" selected>This Week</option>
                            <option value="month">This Month</option>
                            <option value="quarter">This Quarter</option>
                            <option value="year">This Year</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Status</label>
                        <select class="form-select form-select-sm" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="success">Success</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Event</label>
                        <select class="form-select form-select-sm" id="eventFilter">
                            <option value="">All Events</option>
                            <?php if (!empty($events)): ?>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?= $event['id'] ?>"><?= htmlspecialchars($event['name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Amount Range</label>
                        <select class="form-select form-select-sm" id="amountFilter">
                            <option value="">All Amounts</option>
                            <option value="0-10">GH₵0 - GH₵10</option>
                            <option value="10-50">GH₵10 - GH₵50</option>
                            <option value="50-100">GH₵50 - GH₵100</option>
                            <option value="100+">GH₵100+</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Search</label>
                        <input type="text" class="form-control form-control-sm" placeholder="Transaction ID..." id="searchTransactions">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">&nbsp;</label>
                        <div class="d-flex gap-1">
                            <button class="btn btn-primary btn-sm flex-fill" onclick="applyFilters()">
                                <i class="fas fa-filter"></i>
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="resetFilters()">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($stats['total_transactions'] ?? 0) ?></div>
                    <div>Total Transactions</div>
                    <div class="small">This period</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-exchange-alt fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card success text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($stats['successful_transactions'] ?? 0) ?></div>
                    <div>Successful</div>
                    <div class="small">
                        <?php 
                        $successRate = ($stats['total_transactions'] ?? 0) > 0 
                            ? (($stats['successful_transactions'] ?? 0) / $stats['total_transactions']) * 100 
                            : 0;
                        ?>
                        <?= number_format($successRate, 1) ?>% success rate
                    </div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card warning text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">GH₵<?= number_format($stats['successful_volume'] ?? 0, 2) ?></div>
                    <div>Revenue Volume</div>
                    <div class="small">
                        <?php 
                        $avgTransaction = ($stats['successful_transactions'] ?? 0) > 0 
                            ? ($stats['successful_volume'] ?? 0) / $stats['successful_transactions'] 
                            : 0;
                        ?>
                        Average: GH₵<?= number_format($avgTransaction, 2) ?>
                    </div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-money-bill fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card info text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($stats['total_votes'] ?? 0) ?></div>
                    <div>Total Votes</div>
                    <div class="small">
                        <?php 
                        $failedCount = ($stats['failed_transactions'] ?? 0) + ($stats['pending_transactions'] ?? 0);
                        ?>
                        From successful transactions
                        <?php if ($failedCount > 0): ?>
                            <br><?= $failedCount ?> failed/pending
                        <?php endif; ?>
                    </div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-vote-yea fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transactions Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Transaction History</h5>
            <div class="small text-muted">
                <?php 
                $start = (($pagination['current_page'] ?? 1) - 1) * ($pagination['per_page'] ?? 25) + 1;
                $end = min($start + ($pagination['per_page'] ?? 25) - 1, $pagination['total_records'] ?? 0);
                ?>
                Showing <?= number_format($start) ?>-<?= number_format($end) ?> of <?= number_format($pagination['total_records'] ?? 0) ?> transactions
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>
                            <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll()">
                        </th>
                        <th>Date/Time</th>
                        <th>Transaction ID</th>
                        <th>Voter</th>
                        <th>Event</th>
                        <th>Contestant</th>
                        <th>Votes</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transactions)): ?>
                        <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><input type="checkbox" class="form-check-input transaction-checkbox" value="<?= htmlspecialchars($transaction['id'] ?? '') ?>"></td>
                            <td>
                                <?php if (!empty($transaction['created_at'])): ?>
                                    <div class="fw-semibold"><?= date('M j, Y', strtotime($transaction['created_at'])) ?></div>
                                    <div class="small text-muted"><?= date('g:i A', strtotime($transaction['created_at'])) ?></div>
                                <?php else: ?>
                                    <div class="fw-semibold">N/A</div>
                                    <div class="small text-muted">No date</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= date('M j, Y', strtotime($transaction['created_at'])) ?>-<?= str_pad($transaction['id'] ?? 0, 3, '0', STR_PAD_LEFT) ?></div>
                                <?php if (!empty($transaction['provider_reference'])): ?>
                                    <div class="small text-muted">Ref: <?= htmlspecialchars($transaction['provider_reference'] ?? '') ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $phone = $transaction['msisdn'] ?? 'Anonymous';
                                $maskedPhone = 'Anonymous';
                                if ($phone !== 'Anonymous' && strlen($phone) > 4) {
                                    $maskedPhone = '****' . substr($phone, -4);
                                }
                                ?>
                                <div class="fw-semibold" 
                                     title="<?= htmlspecialchars($phone) ?>"
                                     style="cursor: pointer;"
                                     onmouseover="this.textContent='<?= htmlspecialchars($phone) ?>'"
                                     onmouseout="this.textContent='<?= htmlspecialchars($maskedPhone) ?>'">
                                    <?= htmlspecialchars($maskedPhone) ?>
                                </div>
                                <div class="small text-muted">
                                    <?php if (!empty($transaction['provider'])): ?>
                                        <?= ucfirst(htmlspecialchars($transaction['provider'])) ?> payment
                                    <?php else: ?>
                                        Mobile payment
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                // Use real event name from database join
                                $eventName = $transaction['event_name'] ?? 'Event #' . ($transaction['event_id'] ?? 'N/A');
                                ?>
                                <div class="fw-semibold"><?= htmlspecialchars($eventName) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($transaction['category_name'] ?? 'Category') ?></div>
                            </td>
                            <td>
                                <?php
                                // Use real contestant name from database join
                                $contestantName = $transaction['contestant_name'] ?? 'Contestant #' . ($transaction['contestant_id'] ?? 'N/A');
                                ?>
                                <div class="fw-semibold"><?= htmlspecialchars($contestantName) ?></div>
                            </td>
                            <td>
                                <?php 
                                // Get actual vote count from votes table or bundle data
                                $votes = $transaction['actual_votes'] ?? $transaction['bundle_vote_count'] ?? 0;
                                $bundleId = $transaction['bundle_id'] ?? 0;
                                $bundleName = $transaction['bundle_name'] ?? 'Bundle #' . $bundleId;
                                $amount = floatval($transaction['amount'] ?? 0);
                                
                                // Calculate price per vote
                                $pricePerVote = $votes > 0 ? $amount / $votes : 0;
                                ?>
                                <div class="fw-semibold"><?= number_format($votes) ?> <?= $votes == 1 ? 'Vote' : 'Votes' ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold">GH₵<?= number_format($transaction['amount'] ?? 0, 2) ?></div>
                                <div class="small text-muted">
                                    <?php if ($votes > 0): ?>
                                        GH₵<?= number_format($pricePerVote, 2) ?> per vote
                                    <?php else: ?>
                                        Total amount
                                    <?php endif; ?>
                                    <?php if (!empty($transaction['coupon_code'])): ?>
                                        <br>Coupon: <?= htmlspecialchars($transaction['coupon_code']) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                $status = $transaction['status'] ?? 'unknown';
                                $statusClass = match($status) {
                                    'success', 'completed' => 'bg-success',
                                    'pending' => 'bg-warning',
                                    'failed' => 'bg-danger',
                                    'refunded' => 'bg-info',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= ucfirst($status) ?></span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewTransaction('<?= htmlspecialchars($transaction['id'] ?? '') ?>')" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($status === 'success' || $status === 'completed'): ?>
                                        <button class="btn btn-outline-success" onclick="downloadReceipt('<?= htmlspecialchars($transaction['id'] ?? '') ?>')" title="Download Receipt">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        <button class="btn btn-outline-info" onclick="resendReceipt('<?= htmlspecialchars($transaction['id'] ?? '') ?>')" title="Resend Receipt">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                    <?php elseif ($status === 'pending'): ?>
                                        <button class="btn btn-outline-warning" onclick="retryTransaction('<?= htmlspecialchars($transaction['id'] ?? '') ?>')" title="Retry Transaction">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="cancelTransaction('<?= htmlspecialchars($transaction['id'] ?? '') ?>')" title="Cancel Transaction">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php elseif ($status === 'failed'): ?>
                                        <button class="btn btn-outline-warning" onclick="retryTransaction('<?= htmlspecialchars($transaction['id'] ?? '') ?>')" title="Retry Transaction">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                        <button class="btn btn-outline-info" onclick="refundTransaction('<?= htmlspecialchars($transaction['id'] ?? '') ?>')" title="Process Refund">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-exchange-alt fa-3x mb-3 opacity-50"></i>
                                    <h5>No Transactions Found</h5>
                                    <p>No transactions match your current filters.</p>
                                    <button class="btn btn-outline-primary" onclick="resetFilters()">
                                        <i class="fas fa-undo me-1"></i>Reset Filters
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <button class="btn btn-sm btn-outline-danger" onclick="bulkAction('refund')" disabled id="bulkRefundBtn">
                    <i class="fas fa-undo me-1"></i>Bulk Refund
                </button>
                <button class="btn btn-sm btn-outline-warning" onclick="bulkAction('retry')" disabled id="bulkRetryBtn">
                    <i class="fas fa-redo me-1"></i>Bulk Retry
                </button>
            </div>
            <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
            <nav aria-label="Transactions pagination">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= ($pagination['current_page'] ?? 1) <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= max(1, ($pagination['current_page'] ?? 1) - 1) ?>" tabindex="-1">Previous</a>
                    </li>
                    
                    <?php 
                    $currentPage = $pagination['current_page'] ?? 1;
                    $totalPages = $pagination['total_pages'] ?? 1;
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++): 
                    ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= min($totalPages, $currentPage + 1) ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function applyFilters() {
    const dateRange = document.getElementById('dateRange').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const eventFilter = document.getElementById('eventFilter').value;
    const amountFilter = document.getElementById('amountFilter').value;
    const searchTerm = document.getElementById('searchTransactions').value;
    
    // Build query parameters
    const params = new URLSearchParams(window.location.search);
    
    if (dateRange) params.set('date_range', dateRange);
    else params.delete('date_range');
    
    if (statusFilter) params.set('status', statusFilter);
    else params.delete('status');
    
    if (eventFilter) params.set('event', eventFilter);
    else params.delete('event');
    
    if (amountFilter) params.set('amount_range', amountFilter);
    else params.delete('amount_range');
    
    if (searchTerm) params.set('search', searchTerm);
    else params.delete('search');
    
    // Reset to first page when applying filters
    params.set('page', '1');
    
    // Redirect with new filters
    window.location.href = '?' + params.toString();
}

function resetFilters() {
    // Clear all filters and redirect to base URL
    window.location.href = window.location.pathname;
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.transaction-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateBulkButtons();
}

function updateBulkButtons() {
    const checkedBoxes = document.querySelectorAll('.transaction-checkbox:checked');
    const bulkRefundBtn = document.getElementById('bulkRefundBtn');
    const bulkRetryBtn = document.getElementById('bulkRetryBtn');
    
    if (checkedBoxes.length > 0) {
        bulkRefundBtn.disabled = false;
        bulkRetryBtn.disabled = false;
    } else {
        bulkRefundBtn.disabled = true;
        bulkRetryBtn.disabled = true;
    }
}

function viewTransaction(transactionId) {
    // Open transaction details modal or page
    window.open(`<?= ORGANIZER_URL ?>/financial/transactions/${transactionId}`, '_blank');
}

function downloadReceipt(transactionId) {
    // Download receipt PDF
    window.open(`<?= ORGANIZER_URL ?>/financial/transactions/${transactionId}/receipt`, '_blank');
}

function resendReceipt(transactionId) {
    if (confirm('Are you sure you want to resend the receipt for this transaction?')) {
        fetch(`<?= ORGANIZER_URL ?>/financial/transactions/${transactionId}/resend-receipt`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Receipt resent successfully!');
            } else {
                alert('Failed to resend receipt: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while resending the receipt.');
        });
    }
}

function retryTransaction(transactionId) {
    if (confirm('Are you sure you want to retry this transaction?')) {
        fetch(`<?= ORGANIZER_URL ?>/financial/transactions/${transactionId}/retry`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Transaction retry initiated!');
                location.reload();
            } else {
                alert('Failed to retry transaction: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while retrying the transaction.');
        });
    }
}

function cancelTransaction(transactionId) {
    if (confirm('Are you sure you want to cancel this transaction? This action cannot be undone.')) {
        fetch(`<?= ORGANIZER_URL ?>/financial/transactions/${transactionId}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Transaction cancelled!');
                location.reload();
            } else {
                alert('Failed to cancel transaction: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while cancelling the transaction.');
        });
    }
}

function refundTransaction(transactionId) {
    if (confirm('Are you sure you want to process a refund for this transaction?')) {
        fetch(`<?= ORGANIZER_URL ?>/financial/transactions/${transactionId}/refund`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Refund processed successfully!');
                location.reload();
            } else {
                alert('Failed to process refund: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing the refund.');
        });
    }
}

function bulkAction(action) {
    const checkedBoxes = document.querySelectorAll('.transaction-checkbox:checked');
    const transactionIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (transactionIds.length === 0) {
        alert('Please select at least one transaction.');
        return;
    }
    
    if (confirm(`Are you sure you want to ${action} ${transactionIds.length} selected transactions?`)) {
        fetch(`<?= ORGANIZER_URL ?>/financial/transactions/bulk-${action}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ transaction_ids: transactionIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Bulk ${action} completed successfully!`);
                location.reload();
            } else {
                alert(`Failed to perform bulk ${action}: ` + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(`An error occurred while performing bulk ${action}.`);
        });
    }
}

// Add event listeners and initialize filters from URL
document.addEventListener('DOMContentLoaded', function() {
    // Initialize checkboxes
    const checkboxes = document.querySelectorAll('.transaction-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkButtons);
    });
    
    // Set filter values from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('date_range')) {
        document.getElementById('dateRange').value = urlParams.get('date_range');
    }
    if (urlParams.get('status')) {
        document.getElementById('statusFilter').value = urlParams.get('status');
    }
    if (urlParams.get('event')) {
        document.getElementById('eventFilter').value = urlParams.get('event');
    }
    if (urlParams.get('amount_range')) {
        document.getElementById('amountFilter').value = urlParams.get('amount_range');
    }
    if (urlParams.get('search')) {
        document.getElementById('searchTransactions').value = urlParams.get('search');
    }
    
    // Add Enter key support for search
    document.getElementById('searchTransactions').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyFilters();
        }
    });
    
    // Auto-apply filters when dropdowns change
    document.getElementById('dateRange').addEventListener('change', applyFilters);
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('eventFilter').addEventListener('change', applyFilters);
    document.getElementById('amountFilter').addEventListener('change', applyFilters);
});

// Add loading state for better UX
function showLoading() {
    const loadingOverlay = document.createElement('div');
    loadingOverlay.id = 'loadingOverlay';
    loadingOverlay.innerHTML = `
        <div class="d-flex justify-content-center align-items-center" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    document.body.appendChild(loadingOverlay);
}

function hideLoading() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        loadingOverlay.remove();
    }
}
</script>
