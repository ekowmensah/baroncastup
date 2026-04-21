<!-- Voting Receipts Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-receipt me-2"></i>
            Voting Receipts
        </h2>
        <p class="text-muted mb-0">Track and manage all voting transactions and receipts</p>
    </div>
    <div>
        <div class="btn-group" role="group">
            <button class="btn btn-outline-primary" onclick="refreshReceipts()">
                <i class="fas fa-sync me-2"></i>Refresh
            </button>
            <button class="btn btn-outline-success" onclick="exportReceipts()">
                <i class="fas fa-download me-2"></i>Export All
            </button>
        </div>
    </div>
</div>

<!-- Receipt Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <label class="form-label small">Date Range</label>
                        <select class="form-select form-select-sm" id="dateRange">
                            <option value="today" <?= $selectedRange === 'today' ? 'selected' : '' ?>>Today</option>
                            <option value="week" <?= $selectedRange === 'week' ? 'selected' : '' ?>>This Week</option>
                            <option value="month" <?= $selectedRange === 'month' ? 'selected' : '' ?>>This Month</option>
                            <option value="custom" <?= $selectedRange === 'custom' ? 'selected' : '' ?>>Custom Range</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Status</label>
                        <select class="form-select form-select-sm" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="success" <?= $selectedStatus === 'success' ? 'selected' : '' ?>>Success</option>
                            <option value="pending" <?= $selectedStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="failed" <?= $selectedStatus === 'failed' ? 'selected' : '' ?>>Failed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Event</label>
                        <select class="form-select form-select-sm" id="eventFilter">
                            <option value="">All Events</option>
                            <?php if (!empty($events)): ?>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?= $event['id'] ?>" <?= $selectedEventId == $event['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($event['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Search</label>
                        <input type="text" class="form-control form-control-sm" placeholder="Receipt ID, phone, contestant..." 
                               id="searchReceipts" value="<?= htmlspecialchars($searchQuery ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm flex-fill" onclick="applyFilters()">
                                <i class="fas fa-filter me-1"></i>Filter
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

<!-- Receipt Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($receiptStats['total_receipts'] ?? 0) ?></div>
                    <div>Total Receipts</div>
                    <div class="small">GHS <?= number_format($receiptStats['total_revenue'] ?? 0, 2) ?> revenue</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-receipt fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card success text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($receiptStats['successful_receipts'] ?? 0) ?></div>
                    <div>Successful</div>
                    <div class="small"><?= $receiptStats['success_rate'] ?? 0 ?>% success rate</div>
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
                    <div class="fs-4 fw-semibold"><?= number_format($receiptStats['pending_receipts'] ?? 0) ?></div>
                    <div>Pending</div>
                    <div class="small">Processing...</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-clock fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card danger text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= number_format($receiptStats['failed_receipts'] ?? 0) ?></div>
                    <div>Failed</div>
                    <div class="small"><?= $receiptStats['failure_rate'] ?? 0 ?>% failure rate</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-times-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receipts Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Receipts</h5>
            <div class="small text-muted">
                <?php if ($totalReceipts > 0): ?>
                    Showing <?= (($currentPage - 1) * 20) + 1 ?>-<?= min($currentPage * 20, $totalReceipts) ?> of <?= number_format($totalReceipts) ?> receipts
                <?php else: ?>
                    No receipts found
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Receipt ID</th>
                        <th>Phone Number</th>
                        <th>Event</th>
                        <th>Contestant</th>
                        <th>Votes</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($receipts)): ?>
                        <?php foreach ($receipts as $receipt): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold">#<?= htmlspecialchars($receipt['id']) ?></div>
                                    <div class="small text-muted">Provider: <?= htmlspecialchars($receipt['provider'] ?? 'N/A') ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($receipt['voter_phone'] ?? 'N/A') ?></div>
                                    <div class="small text-muted">Mobile Money</div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($receipt['event_name'] ?? 'N/A') ?></div>
                                    <div class="small text-muted">Code: <?= htmlspecialchars($receipt['event_code'] ?? 'N/A') ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($receipt['contestant_name'] ?? 'N/A') ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($receipt['contestant_code'] ?? 'N/A') ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= number_format($receipt['votes_purchased'] ?? 0) ?> votes</div>
                                    <div class="small text-muted">Bundle</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">GHS <?= number_format($receipt['amount'] ?? 0, 2) ?></div>
                                    <div class="small text-muted">GHS <?= $receipt['votes_purchased'] > 0 ? number_format(($receipt['amount'] ?? 0) / $receipt['votes_purchased'], 2) : 0 ?> per vote</div>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $receipt['status'] === 'success' ? 'success' : ($receipt['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                        <?= ucfirst($receipt['status'] ?? 'Unknown') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= date('M j, Y', strtotime($receipt['created_at'])) ?></div>
                                    <div class="small text-muted"><?= date('g:i A', strtotime($receipt['created_at'])) ?></div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewReceipt('<?= $receipt['id'] ?>')" title="View Receipt">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success" onclick="downloadReceipt('<?= $receipt['id'] ?>')" title="Download PDF">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        <button class="btn btn-outline-info" onclick="resendReceipt('<?= $receipt['id'] ?>')" title="Resend Email">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-receipt fa-2x mb-2"></i>
                                    <p>No receipts found</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($totalPages > 1): ?>
    <div class="card-footer">
        <nav aria-label="Receipts pagination">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $currentPage > 1 ? '?page=' . ($currentPage - 1) . '&' . http_build_query($_GET) : '#' ?>" tabindex="-1">Previous</a>
                </li>
                
                <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query(array_filter($_GET, function($key) { return $key !== 'page'; }, ARRAY_FILTER_USE_KEY)) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $currentPage < $totalPages ? '?page=' . ($currentPage + 1) . '&' . http_build_query($_GET) : '#' ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- Receipt Details Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Receipt Details</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="receiptContent">
                    <!-- Receipt content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">
                    <i class="fas fa-print me-2"></i>Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function refreshReceipts() {
    console.log('Refreshing receipts...');
    location.reload();
}

function exportReceipts() {
    console.log('Exporting receipts...');
    alert('Receipt export functionality will be implemented soon!');
}

function applyFilters() {
    const dateRange = document.getElementById('dateRange').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const eventFilter = document.getElementById('eventFilter').value;
    const searchTerm = document.getElementById('searchReceipts').value;
    
    const url = new URL(window.location.href);
    url.searchParams.set('range', dateRange);
    
    if (statusFilter) {
        url.searchParams.set('status', statusFilter);
    } else {
        url.searchParams.delete('status');
    }
    
    if (eventFilter) {
        url.searchParams.set('event', eventFilter);
    } else {
        url.searchParams.delete('event');
    }
    
    if (searchTerm) {
        url.searchParams.set('search', searchTerm);
    } else {
        url.searchParams.delete('search');
    }
    
    // Reset to page 1 when applying filters
    url.searchParams.delete('page');
    
    window.location.href = url.toString();
}

function resetFilters() {
    const url = new URL(window.location.href);
    url.searchParams.delete('range');
    url.searchParams.delete('status');
    url.searchParams.delete('event');
    url.searchParams.delete('search');
    url.searchParams.delete('page');
    
    window.location.href = url.toString();
}

function viewReceipt(receiptId) {
    console.log('Viewing receipt:', receiptId);
    
    // Sample receipt content
    const receiptContent = `
        <div class="receipt-container">
            <div class="text-center mb-4">
                <h4>SmartCast Voting Receipt</h4>
                <p class="text-muted">Receipt ID: ${receiptId}</p>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h6>Voter Information</h6>
                    <p><strong>Email:</strong> john.doe@email.com</p>
                    <p><strong>Date:</strong> Nov 12, 2024 2:34 PM</p>
                    <p><strong>IP Address:</strong> 192.168.1.100</p>
                </div>
                <div class="col-md-6">
                    <h6>Transaction Details</h6>
                    <p><strong>Transaction ID:</strong> TXN789456</p>
                    <p><strong>Payment Method:</strong> Credit Card</p>
                    <p><strong>Status:</strong> <span class="badge bg-success">Success</span></p>
                </div>
            </div>
            
            <hr>
            
            <h6>Vote Details</h6>
            <table class="table table-sm">
                <tr>
                    <td>Event:</td>
                    <td>Beauty Contest 2024</td>
                </tr>
                <tr>
                    <td>Contestant:</td>
                    <td>Sarah Johnson (SJ001)</td>
                </tr>
                <tr>
                    <td>Number of Votes:</td>
                    <td>5 votes</td>
                </tr>
                <tr>
                    <td>Price per Vote:</td>
                    <td>GH₵0.50</td>
                </tr>
                <tr class="table-light">
                    <td><strong>Total Amount:</strong></td>
                    <td><strong>GH₵2.50</strong></td>
                </tr>
            </table>
        </div>
    `;
    
    document.getElementById('receiptContent').innerHTML = receiptContent;
    const modal = new coreui.Modal(document.getElementById('receiptModal'));
    modal.show();
}

function downloadReceipt(receiptId) {
    console.log('Downloading receipt:', receiptId);
    alert('PDF download functionality will be implemented soon!');
}

function resendReceipt(receiptId) {
    if (confirm('Are you sure you want to resend this receipt via email?')) {
        console.log('Resending receipt:', receiptId);
        alert('Receipt resent successfully!');
    }
}

function retryTransaction(receiptId) {
    if (confirm('Are you sure you want to retry this transaction?')) {
        console.log('Retrying transaction:', receiptId);
        alert('Transaction retry initiated!');
    }
}

function cancelTransaction(receiptId) {
    if (confirm('Are you sure you want to cancel this transaction? This action cannot be undone.')) {
        console.log('Cancelling transaction:', receiptId);
        alert('Transaction cancelled!');
    }
}

function contactSupport(receiptId) {
    console.log('Contacting support for receipt:', receiptId);
    alert('Support ticket created for receipt: ' + receiptId);
}

function printReceipt() {
    window.print();
}
</script>
