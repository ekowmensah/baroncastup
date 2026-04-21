<!-- Referrals Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-share-alt me-2"></i>
            Referral Program
        </h2>
        <p class="text-muted mb-0">Grow your audience through referrals and earn rewards</p>
    </div>
    <div>
        <div class="btn-group" role="group">
            <button class="btn btn-outline-primary" onclick="generateReferralLink()">
                <i class="fas fa-link me-2"></i>Generate Link
            </button>
            <button class="btn btn-outline-success" onclick="shareReferral()">
                <i class="fas fa-share me-2"></i>Share
            </button>
        </div>
    </div>
</div>

<!-- Referral Statistics -->
<div class="row mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $referralStats['total_referrals'] ?? 0 ?></div>
                    <div>Total Referrals</div>
                    <div class="small">All time</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-users fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card stats-card success text-white">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold"><?= $referralStats['successful_referrals'] ?? 0 ?></div>
                    <div>Successful</div>
                    <div class="small"><?= $referralStats['total_referrals'] > 0 ? round(($referralStats['successful_referrals'] / $referralStats['total_referrals']) * 100, 1) : 0 ?>% conversion rate</div>
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
                    <div class="fs-4 fw-semibold">GH₵<?= number_format($referralStats['earned_rewards'] ?? 0, 2) ?></div>
                    <div>Earned Rewards</div>
                    <div class="small">GH₵5.00 per referral</div>
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
                    <div class="fs-4 fw-semibold"><?= $referralStats['this_month'] ?? 0 ?></div>
                    <div>This Month</div>
                    <div class="small">+15% vs last month</div>
                </div>
                <div class="dropdown">
                    <i class="fas fa-calendar fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Referral Link -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Your Referral Link</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Share this link to earn GH₵5.00 for each successful referral who creates an event!
                </div>
                
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="referralLink" value="https://smartcast.com/ref/your-unique-code" readonly>
                    <button class="btn btn-outline-secondary" onclick="copyReferralLink()">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                
                <div class="d-flex gap-2 mb-3">
                    <button class="btn btn-primary btn-sm" onclick="shareOnSocial('facebook')">
                        <i class="fab fa-facebook me-1"></i>Facebook
                    </button>
                    <button class="btn btn-info btn-sm" onclick="shareOnSocial('twitter')">
                        <i class="fab fa-twitter me-1"></i>Twitter
                    </button>
                    <button class="btn btn-success btn-sm" onclick="shareOnSocial('whatsapp')">
                        <i class="fab fa-whatsapp me-1"></i>WhatsApp
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="shareOnSocial('email')">
                        <i class="fas fa-envelope me-1"></i>Email
                    </button>
                </div>
                
                <div class="row text-center">
                    <div class="col-4">
                        <div class="fw-semibold">234</div>
                        <div class="small text-muted">Link Clicks</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-semibold">89</div>
                        <div class="small text-muted">Sign-ups</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-semibold">38%</div>
                        <div class="small text-muted">Conversion</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Referral Rewards -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Reward Structure</h6>
            </div>
            <div class="card-body">
                <div class="reward-tier mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">Sign-up Bonus</div>
                            <div class="small text-muted">New user registers</div>
                        </div>
                        <div class="fw-semibold text-success">GH₵2.00</div>
                    </div>
                </div>
                
                <div class="reward-tier mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">First Event</div>
                            <div class="small text-muted">Creates their first event</div>
                        </div>
                        <div class="fw-semibold text-success">GH₵5.00</div>
                    </div>
                </div>
                
                <div class="reward-tier mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">Premium Upgrade</div>
                            <div class="small text-muted">Upgrades to premium plan</div>
                        </div>
                        <div class="fw-semibold text-success">GH₵10.00</div>
                    </div>
                </div>
                
                <hr>
                
                <div class="text-center">
                    <div class="fw-semibold text-primary">Available Balance</div>
                    <div class="fs-4 fw-bold text-success">GH₵445.00</div>
                    <button class="btn btn-sm btn-outline-primary mt-2" onclick="requestPayout()">
                        Request Payout
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Referral History -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Referral History</h5>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="refreshReferrals()">
                            <i class="fas fa-sync"></i>
                        </button>
                        <button class="btn btn-outline-success" onclick="exportReferrals()">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Referred User</th>
                                <th>Status</th>
                                <th>Actions Completed</th>
                                <th>Reward Earned</th>
                                <th>Source</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="fw-semibold">Nov 12, 2024</div>
                                    <div class="small text-muted">2:34 PM</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">john.doe@email.com</div>
                                    <div class="small text-muted">New user</div>
                                </td>
                                <td><span class="badge bg-success">Completed</span></td>
                                <td>
                                    <div class="small">
                                        <i class="fas fa-check text-success me-1"></i>Signed up<br>
                                        <i class="fas fa-check text-success me-1"></i>Created event<br>
                                        <i class="fas fa-times text-muted me-1"></i>Premium upgrade
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-success">GH₵7.00</div>
                                    <div class="small text-muted">GH₵2 + GH₵5</div>
                                </td>
                                <td>
                                    <span class="badge bg-primary">Facebook</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">Nov 11, 2024</div>
                                    <div class="small text-muted">4:15 PM</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">mary.smith@email.com</div>
                                    <div class="small text-muted">Active user</div>
                                </td>
                                <td><span class="badge bg-warning">Pending</span></td>
                                <td>
                                    <div class="small">
                                        <i class="fas fa-check text-success me-1"></i>Signed up<br>
                                        <i class="fas fa-clock text-warning me-1"></i>Creating event<br>
                                        <i class="fas fa-times text-muted me-1"></i>Premium upgrade
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-warning">GH₵2.00</div>
                                    <div class="small text-muted">Partial</div>
                                </td>
                                <td>
                                    <span class="badge bg-info">Twitter</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">Nov 10, 2024</div>
                                    <div class="small text-muted">1:22 PM</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">alex.wilson@email.com</div>
                                    <div class="small text-muted">Premium user</div>
                                </td>
                                <td><span class="badge bg-success">Completed</span></td>
                                <td>
                                    <div class="small">
                                        <i class="fas fa-check text-success me-1"></i>Signed up<br>
                                        <i class="fas fa-check text-success me-1"></i>Created event<br>
                                        <i class="fas fa-check text-success me-1"></i>Premium upgrade
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-success">GH₵17.00</div>
                                    <div class="small text-muted">GH₵2 + GH₵5 + GH₵10</div>
                                </td>
                                <td>
                                    <span class="badge bg-success">WhatsApp</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">Nov 9, 2024</div>
                                    <div class="small text-muted">11:45 AM</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">sarah.jones@email.com</div>
                                    <div class="small text-muted">Inactive</div>
                                </td>
                                <td><span class="badge bg-secondary">Expired</span></td>
                                <td>
                                    <div class="small">
                                        <i class="fas fa-check text-success me-1"></i>Signed up<br>
                                        <i class="fas fa-times text-danger me-1"></i>No activity<br>
                                        <i class="fas fa-times text-muted me-1"></i>Premium upgrade
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-secondary">GH₵0.00</div>
                                    <div class="small text-muted">Expired</div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">Email</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Referral Performance Chart -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Referral Performance</h5>
            </div>
            <div class="card-body">
                <canvas id="referralChart" width="400" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize referral performance chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('referralChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Referrals',
                data: [12, 19, 15, 25, 22, 30],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4
            }, {
                label: 'Rewards ($)',
                data: [60, 95, 75, 125, 110, 150],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                }
            }
        }
    });
});

function generateReferralLink() {
    console.log('Generating new referral link...');
    alert('New referral link generated!');
}

function copyReferralLink() {
    const linkInput = document.getElementById('referralLink');
    linkInput.select();
    document.execCommand('copy');
    
    // Show feedback
    const button = event.target;
    const originalIcon = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    button.classList.add('btn-success');
    button.classList.remove('btn-outline-secondary');
    
    setTimeout(() => {
        button.innerHTML = originalIcon;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-secondary');
    }, 2000);
}

function shareOnSocial(platform) {
    const referralLink = document.getElementById('referralLink').value;
    const message = "Check out SmartCast - the best platform for online voting events!";
    
    let shareUrl = '';
    
    switch (platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(referralLink)}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(message)}&url=${encodeURIComponent(referralLink)}`;
            break;
        case 'whatsapp':
            shareUrl = `https://wa.me/?text=${encodeURIComponent(message + ' ' + referralLink)}`;
            break;
        case 'email':
            shareUrl = `mailto:?subject=${encodeURIComponent('Check out SmartCast!')}&body=${encodeURIComponent(message + '\n\n' + referralLink)}`;
            break;
    }
    
    if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }
}

function shareReferral() {
    if (navigator.share) {
        navigator.share({
            title: 'SmartCast Referral',
            text: 'Check out SmartCast - the best platform for online voting events!',
            url: document.getElementById('referralLink').value
        });
    } else {
        copyReferralLink();
        alert('Referral link copied to clipboard!');
    }
}

function requestPayout() {
    if (confirm('Request payout of GH₵445.00 to your account?')) {
        console.log('Requesting referral payout...');
        alert('Payout request submitted successfully!');
    }
}

function refreshReferrals() {
    console.log('Refreshing referral data...');
    location.reload();
}

function exportReferrals() {
    console.log('Exporting referral data...');
    alert('Referral data export functionality will be implemented soon!');
}
</script>
