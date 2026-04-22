<?php include __DIR__ . '/../layout/header.php'; ?>

<style>
/* Modern Voting Page Header - Updated */
.modern-voting-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0;
    border-radius: 20px;
    margin-bottom: 30px;
    overflow: hidden;
    position: relative;
    box-shadow: 0 15px 40px rgba(102, 126, 234, 0.3);
}

.modern-voting-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="20" cy="80" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
    pointer-events: none;
}

.header-content-modern {
    position: relative;
    z-index: 2;
    padding: 40px 30px;
    text-align: center;
}

.event-title-modern {
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 800;
    margin: 0 0 1.5rem 0;
    line-height: 1.1;
    text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.3);
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    color: white; /* Fallback */
}

.event-subtitle-modern {
    font-size: 1.1rem;
    opacity: 0.9;
    margin-bottom: 2rem;
    font-weight: 500;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
    color: white;
}

.event-stats-modern {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1.5rem;
    max-width: 600px;
    margin: 0 auto 2rem;
}

.stat-item-modern {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 15px;
    padding: 1.5rem 1rem;
    text-align: center;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-item-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    transition: left 0.6s;
}

.stat-item-modern:hover::before {
    left: 100%;
}

.stat-item-modern:hover {
    transform: translateY(-3px);
    background: rgba(255, 255, 255, 0.2);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.stat-icon-modern {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    opacity: 0.8;
    display: block;
    color: white;
}

.stat-number-modern {
    font-size: 2.2rem;
    font-weight: 800;
    margin-bottom: 0.25rem;
    text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.4);
    color: white;
}

.stat-label-modern {
    font-size: 0.85rem;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
    color: white;
}

.status-badges-modern {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.status-badge-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 12px 20px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.9rem;
    backdrop-filter: blur(15px);
    border: 2px solid rgba(255, 255, 255, 0.2);
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
}

.status-badge-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
}

.badge-active {
    background: rgba(255, 193, 7, 0.9);
    border-color: rgba(255, 193, 7, 0.5);
    color: #000;
    font-weight: 700;
}

/* Responsive Design */
@media (max-width: 768px) {
    .header-content-modern {
        padding: 30px 20px;
    }
    
    .event-title-modern {
        font-size: clamp(1.8rem, 6vw, 2.5rem);
        margin-bottom: 1rem;
    }
    
    .event-subtitle-modern {
        font-size: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .event-stats-modern {
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .stat-item-modern {
        padding: 1rem 0.75rem;
    }
    
    .stat-icon-modern {
        font-size: 1.2rem;
    }
    
    .stat-number-modern {
        font-size: 1.8rem;
    }
    
    .stat-label-modern {
        font-size: 0.8rem;
    }
    
    .status-badges-modern {
        gap: 0.75rem;
    }
    
    .status-badge-modern {
        padding: 10px 16px;
        font-size: 0.85rem;
    }
}

@media (max-width: 480px) {
    .header-content-modern {
        padding: 25px 15px;
    }
    
    .event-stats-modern {
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
    }
    
    .stat-item-modern {
        padding: 0.75rem 0.5rem;
    }
    
    .stat-number-modern {
        font-size: 1.6rem;
    }
    
    .stat-label-modern {
        font-size: 0.75rem;
    }
    
    .status-badges-modern {
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }
    
    .status-badge-modern {
        padding: 8px 14px;
        font-size: 0.8rem;
    }
}
</style>

<div class="voting-page">
    <!-- Modern Hero Section -->
    <div class="modern-voting-header">
        <div class="header-content-modern">
            <h1 class="event-title-modern"><?= htmlspecialchars($event['name']) ?></h1>
            <p class="event-subtitle-modern">Choose your favorite nominee to vote for</p>
            
            <div class="event-stats-modern">
                <div class="stat-item-modern">
                    <i class="fas fa-users stat-icon-modern"></i>
                    <div class="stat-number-modern"><?= count($contestants) ?></div>
                    <div class="stat-label-modern">Contestants</div>
                </div>
                <div class="stat-item-modern">
                    <i class="fas fa-layer-group stat-icon-modern"></i>
                    <div class="stat-number-modern"><?= count($contestantsByCategory) ?></div>
                    <div class="stat-label-modern">Categories</div>
                </div>
                <div class="stat-item-modern">
                    <i class="fas fa-calendar-alt stat-icon-modern"></i>
                    <div class="stat-number-modern"><?= date('d', strtotime($event['end_date'])) ?></div>
                    <div class="stat-label-modern"><?= date('M Y', strtotime($event['end_date'])) ?></div>
                </div>
            </div>
            
            <div class="status-badges-modern">
                <div class="status-badge-modern badge-active">
                    <i class="fas fa-vote-yea"></i>
                    <span>Voting Active</span>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Main Content -->
            <div class="col-xl-8 col-lg-7">
                <!-- Alert Container -->
                <div id="alert-container" class="mb-4"></div>

                <!-- Category Tabs -->
                <?php if (!empty($contestantsByCategory) && count($contestantsByCategory) > 1): ?>
                <div class="category-tabs mb-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="card-title mb-3">
                                <i class="fas fa-tags text-primary me-2"></i>
                                Categories
                            </h5>
                            <ul class="nav nav-pills nav-fill" id="categoryTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="all-tab" data-bs-toggle="pill" 
                                            data-bs-target="#all-contestants" type="button" role="tab">
                                        <i class="fas fa-th-large me-2"></i>
                                        All Contestants
                                        <span class="badge bg-light text-dark ms-2"><?= count($contestants) ?></span>
                                    </button>
                                </li>
                                <?php foreach ($contestantsByCategory as $category): ?>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="cat-<?= $category['id'] ?>-tab" data-bs-toggle="pill" 
                                            data-bs-target="#cat-<?= $category['id'] ?>" type="button" role="tab">
                                        <?= htmlspecialchars($category['name']) ?>
                                        <span class="badge bg-light text-dark ms-2"><?= count($category['contestants']) ?></span>
                                    </button>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Voting Form -->
                <form id="votingForm" class="needs-validation" novalidate>
                    <input type="hidden" id="contestant_id" name="contestant_id">
                    <input type="hidden" id="bundle_id" name="bundle_id">
                    
                    <!-- Step 1: Select Contestant -->
                    <div class="voting-step mb-5">
                        <div class="step-header mb-4">
                            <div class="d-flex align-items-center">
                                <div class="step-number">
                                    <span class="badge bg-primary rounded-circle p-3">1</span>
                                </div>
                                <div class="step-content ms-3">
                                    <h4 class="mb-1">Choose Your Favorite</h4>
                                    <p class="text-muted mb-0">Select the contestant you want to vote for</p>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Content -->
                        <div class="tab-content" id="categoryTabContent">
                            <!-- All Contestants Tab -->
                            <div class="tab-pane fade show active" id="all-contestants" role="tabpanel">
                                <div class="contestants-grid">
                                    <?php foreach ($contestants as $contestant): ?>
                                        <?php include __DIR__ . '/partials/contestant-card.php'; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Category Tabs -->
                            <?php foreach ($contestantsByCategory as $category): ?>
                            <div class="tab-pane fade" id="cat-<?= $category['id'] ?>" role="tabpanel">
                                <div class="contestants-grid">
                                    <?php foreach ($category['contestants'] as $contestant): ?>
                                        <?php include __DIR__ . '/partials/contestant-card.php'; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Step 2: Select Vote Package -->
                    <div class="voting-step mb-5">
                        <div class="step-header mb-4">
                            <div class="d-flex align-items-center">
                                <div class="step-number">
                                    <span class="badge bg-primary rounded-circle p-3">2</span>
                                </div>
                                <div class="step-content ms-3">
                                    <h4 class="mb-1">Select Vote Package</h4>
                                    <p class="text-muted mb-0">Choose how many votes you want to cast</p>
                                </div>
                            </div>
                        </div>

                        <div class="packages-grid">
                            <?php foreach ($bundles as $bundle): ?>
                            <div class="package-card" data-bundle-id="<?= $bundle['id'] ?>"
                                 onclick="selectBundle(<?= $bundle['id'] ?>, <?= $bundle['votes'] ?>, <?= $bundle['price'] ?>)">
                                <div class="package-header">
                                    <div class="package-icon">
                                        <i class="fas fa-vote-yea"></i>
                                    </div>
                                    <h5 class="package-name"><?= htmlspecialchars($bundle['name']) ?></h5>
                                </div>
                                <div class="package-body">
                                    <div class="vote-count">
                                        <span class="votes-number"><?= $bundle['votes'] ?></span>
                                        <span class="votes-label">Vote<?= $bundle['votes'] > 1 ? 's' : '' ?></span>
                                    </div>
                                    <div class="package-price">
                                        <span class="price-main">GH₵<?= number_format($bundle['price'], 2) ?></span>
                                        <span class="price-per">GH₵<?= number_format($bundle['price'] / $bundle['votes'], 2) ?> per vote</span>
                                    </div>
                                </div>
                                <div class="package-footer">
                                    <div class="selection-indicator">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Step 3: Contact Information -->
                    <div class="voting-step mb-5">
                        <div class="step-header mb-4">
                            <div class="d-flex align-items-center">
                                <div class="step-number">
                                    <span class="badge bg-primary rounded-circle p-3">3</span>
                                </div>
                                <div class="step-content ms-3">
                                    <h4 class="mb-1">Contact Information</h4>
                                    <p class="text-muted mb-0">Enter your phone number to complete the vote</p>
                                </div>
                            </div>
                        </div>

                        <div class="contact-form">
                            <div class="row justify-content-center">
                                <div class="col-lg-8">
                                    <div class="form-floating mb-3">
                                        <input type="tel" class="form-control form-control-lg" 
                                               id="msisdn" name="msisdn" 
                                               placeholder="+233241234567" required>
                                        <label for="msisdn">
                                            <i class="fas fa-phone me-2"></i>
                                            Mobile Number *
                                        </label>
                                        <div class="form-text">Include country code (e.g., +233 for Ghana)</div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" 
                                                       id="coupon_code" name="coupon_code" 
                                                       placeholder="Enter coupon code">
                                                <label for="coupon_code">
                                                    <i class="fas fa-tag me-2"></i>
                                                    Coupon Code (Optional)
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" 
                                                       id="referral_code" name="referral_code" 
                                                       placeholder="Enter referral code">
                                                <label for="referral_code">
                                                    <i class="fas fa-users me-2"></i>
                                                    Referral Code (Optional)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center mb-5">
                        <button type="submit" id="vote-button" class="btn btn-success btn-lg px-5 py-3" disabled>
                            <i class="fas fa-vote-yea me-2"></i>
                            Cast Your Vote
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sidebar -->
            <div class="col-xl-4 col-lg-5">
                <div class="sidebar-content">
                    <!-- Vote Summary -->
                    <div class="summary-card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-receipt text-success me-2"></i>
                                Vote Summary
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="vote-summary">
                                <div class="empty-summary">
                                    <div class="empty-icon">
                                        <i class="fas fa-arrow-left"></i>
                                    </div>
                                    <p>Select a contestant and vote package to see your summary</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- How to Vote -->
                    <div class="info-card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-question-circle text-info me-2"></i>
                                How to Vote
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="steps-list">
                                <div class="step-item">
                                    <div class="step-icon">1</div>
                                    <div class="step-text">Select your favorite contestant</div>
                                </div>
                                <div class="step-item">
                                    <div class="step-icon">2</div>
                                    <div class="step-text">Choose a vote package</div>
                                </div>
                                <div class="step-item">
                                    <div class="step-icon">3</div>
                                    <div class="step-text">Enter your phone number</div>
                                </div>
                                <div class="step-item">
                                    <div class="step-icon">4</div>
                                    <div class="step-text">Click "Cast Your Vote"</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms -->
                    <div class="terms-card">
                        <div class="card-body">
                            <h6 class="mb-3">
                                <i class="fas fa-info-circle text-warning me-2"></i>
                                Terms & Conditions
                            </h6>
                            <ul class="terms-list">
                                <li>One vote per transaction</li>
                                <li>Votes are final and cannot be changed</li>
                                <li>Standard SMS rates may apply</li>
                                <li>Results are updated in real-time</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Styles -->
<link rel="stylesheet" href="/assets/css/voting-page.css">

<!-- Include Scripts -->
<script src="/assets/js/voting-page.js"></script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
