<?php include __DIR__ . '/../layout/header.php'; ?>

<style>
/* Modern Voting Page Styles - Updated v2.0 */
/* Inline CSS to ensure it loads */
body { 
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    margin: 0;
    padding: 0;
    background: #f8f9fa;
    color: #333;
}

.voting-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Modern Event Header - UPDATED */
.event-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    padding: 0 !important;
    border-radius: 20px !important;
    margin-bottom: 30px !important;
    overflow: hidden !important;
    position: relative !important;
    box-shadow: 0 15px 40px rgba(102, 126, 234, 0.3) !important;
}

.event-header::before {
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

.header-content {
    position: relative !important;
    z-index: 2 !important;
    padding: 40px 30px !important;
    text-align: center !important;
}

.event-title {
    font-size: clamp(2rem, 5vw, 3.5rem) !important;
    font-weight: 800 !important;
    margin: 0 0 1.5rem 0 !important;
    line-height: 1.1 !important;
    text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.3) !important;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
    -webkit-background-clip: text !important;
    -webkit-text-fill-color: transparent !important;
    background-clip: text !important;
    color: white !important; /* Fallback for browsers that don't support background-clip */
}

.event-subtitle {
    font-size: 1.1rem !important;
    opacity: 0.9 !important;
    margin-bottom: 2rem !important;
    font-weight: 500 !important;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3) !important;
    color: white !important;
}

.event-stats {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)) !important;
    gap: 1.5rem !important;
    max-width: 600px !important;
    margin: 0 auto 2rem !important;
}

.stat-item {
    background: rgba(255, 255, 255, 0.15) !important;
    backdrop-filter: blur(15px) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    border-radius: 15px !important;
    padding: 1.5rem 1rem !important;
    text-align: center !important;
    transition: all 0.3s ease !important;
    position: relative !important;
    overflow: hidden !important;
}

.stat-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    transition: left 0.6s;
}

.stat-item:hover::before {
    left: 100%;
}

.stat-item:hover {
    transform: translateY(-3px) !important;
    background: rgba(255, 255, 255, 0.2) !important;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2) !important;
}

.stat-icon {
    font-size: 1.5rem !important;
    margin-bottom: 0.5rem !important;
    opacity: 0.8 !important;
    display: block !important;
    color: white !important;
}

.stat-number {
    font-size: 2.2rem !important;
    font-weight: 800 !important;
    margin-bottom: 0.25rem !important;
    text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.4) !important;
    color: white !important;
}

.stat-label {
    font-size: 0.85rem !important;
    opacity: 0.9 !important;
    text-transform: uppercase !important;
    letter-spacing: 1px !important;
    font-weight: 600 !important;
    color: white !important;
}

.status-badges {
    display: flex !important;
    justify-content: center !important;
    gap: 1rem !important;
    flex-wrap: wrap !important;
}

.status-badge-voting {
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    padding: 12px 20px !important;
    border-radius: 50px !important;
    font-weight: 600 !important;
    font-size: 0.9rem !important;
    backdrop-filter: blur(15px) !important;
    border: 2px solid rgba(255, 255, 255, 0.2) !important;
    text-transform: uppercase !important;
    letter-spacing: 1px !important;
    transition: all 0.3s ease !important;
}

.status-badge-voting:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3) !important;
}

.badge-visible {
    background: rgba(40, 167, 69, 0.9) !important;
    border-color: rgba(40, 167, 69, 0.5) !important;
    color: white !important;
}

.badge-hidden {
    background: rgba(108, 117, 125, 0.9) !important;
    border-color: rgba(108, 117, 125, 0.5) !important;
    color: white !important;
}

.voting-indicator {
    background: rgba(255, 193, 7, 0.9) !important;
    border-color: rgba(255, 193, 7, 0.5) !important;
    color: #000 !important;
    font-weight: 700 !important;
}

.voting-section {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
}

.category-section {
    margin-bottom: 40px;
}

.category-title {
    font-size: 1.3rem;
    font-weight: 500;
    margin-bottom: 15px;
    color: #555;
    border-bottom: 2px solid #eee;
    padding-bottom: 10px;
}

.contestants-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}

.contestant-card {
    background: #f8f9fa;
    border: 2px solid transparent;
    border-radius: 12px;
    padding: 15px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    min-height: 140px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.contestant-card:hover {
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.contestant-card:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.contestant-card.selected {
    border-color: #667eea;
    background: #e8f0fe;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.contestant-card.selected::after {
    content: '✓';
    position: absolute;
    top: 8px;
    right: 8px;
    background: #667eea;
    color: white;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
}

.contestant-image {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto 10px;
    display: block;
}

.contestant-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #ddd;
    margin: 0 auto 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #999;
}

.contestant-name {
    font-weight: 600;
    margin-bottom: 5px;
}

.contestant-code {
    background: #667eea;
    color: white;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    display: inline-block;
    margin-bottom: 5px;
}

.packages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.package-card {
    background: white;
    border: 2px solid #eee;
    border-radius: 12px;
    padding: 25px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    min-height: 180px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.package-card:hover {
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.package-card:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.package-card.selected {
    border-color: #667eea;
    background: #e8f0fe;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.package-card.selected::after {
    content: '✓';
    position: absolute;
    top: 12px;
    right: 12px;
    background: #667eea;
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: bold;
}

.package-name {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 10px;
}

.vote-count {
    font-size: 2rem;
    font-weight: 700;
    color: #667eea;
    margin-bottom: 10px;
}

.package-price {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.price-per-vote {
    font-size: 0.9rem;
    color: #666;
}

.form-section {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #333;
}

.form-input {
    width: 100%;
    padding: 12px;
    border: 2px solid #eee;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-input:focus {
    outline: none;
    border-color: #667eea;
}

.vote-button {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 15px 40px;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
}

.vote-button:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.vote-button:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@media (max-width: 768px) {
    .voting-container {
        padding: 15px;
    }
    
    .event-header {
        margin-bottom: 20px;
    }
    
    .header-content {
        padding: 30px 20px;
    }
    
    .event-title {
        font-size: clamp(1.8rem, 6vw, 2.5rem);
        margin-bottom: 1rem;
    }
    
    .event-subtitle {
        font-size: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .event-stats {
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .stat-item {
        padding: 1rem 0.75rem;
    }
    
    .stat-icon {
        font-size: 1.2rem;
    }
    
    .stat-number {
        font-size: 1.8rem;
    }
    
    .stat-label {
        font-size: 0.8rem;
    }
    
    .status-badges {
        gap: 0.75rem;
    }
    
    .status-badge-voting {
        padding: 10px 16px;
        font-size: 0.85rem;
    }
    
    .voting-section, .form-section {
        padding: 20px 15px;
        margin-bottom: 20px;
    }
    
    .section-title {
        font-size: 1.3rem;
    }
    
    .category-title {
        font-size: 1.1rem;
    }
    
    .contestants-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 15px;
    }
    
    .contestant-card {
        padding: 12px;
    }
    
    .contestant-image, .contestant-placeholder {
        width: 60px;
        height: 60px;
        margin-bottom: 8px;
    }
    
    .contestant-name {
        font-size: 0.9rem;
    }
    
    .contestant-code {
        font-size: 0.7rem;
        padding: 2px 6px;
    }
    
    .packages-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .package-card {
        padding: 20px 15px;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .form-input {
        padding: 15px 12px;
        font-size: 16px; /* Prevents zoom on iOS */
    }
    
    .vote-button {
        padding: 18px 30px;
        font-size: 1rem;
    }
}

@media (max-width: 480px) {
    .voting-container {
        padding: 10px;
    }
    
    .header-content {
        padding: 25px 15px;
    }
    
    .event-title {
        font-size: clamp(1.6rem, 7vw, 2rem);
        line-height: 1.2;
    }
    
    .event-subtitle {
        font-size: 0.95rem;
        margin-bottom: 1.25rem;
    }
    
    .event-stats {
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
    }
    
    .stat-item {
        padding: 0.75rem 0.5rem;
    }
    
    .stat-number {
        font-size: 1.6rem;
    }
    
    .stat-label {
        font-size: 0.75rem;
    }
    
    .status-badges {
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }
    
    .status-badge-voting {
        padding: 8px 14px;
        font-size: 0.8rem;
    }
    
    .voting-section, .form-section {
        padding: 15px 10px;
        border-radius: 10px;
    }
    
    .contestants-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 10px;
    }
    
    .contestant-card {
        padding: 10px;
    }
    
    .contestant-image, .contestant-placeholder {
        width: 50px;
        height: 50px;
        margin-bottom: 6px;
    }
    
    .contestant-placeholder {
        font-size: 1.5rem;
    }
    
    .contestant-name {
        font-size: 0.85rem;
        line-height: 1.2;
    }
    
    .package-card {
        padding: 15px 10px;
    }
    
    .package-name {
        font-size: 1.1rem;
    }
    
    .vote-count {
        font-size: 1.8rem;
    }
    
    .package-price {
        font-size: 1.3rem;
    }
    
    .form-input {
        padding: 16px 12px;
    }
    
    .vote-button {
        padding: 20px 25px;
        font-size: 1rem;
    }
    
    .alert {
        padding: 12px 15px;
        font-size: 0.9rem;
    }
    
    #vote-summary {
        padding: 15px;
        font-size: 0.9rem;
    }
}

@media (max-width: 360px) {
    .header-content {
        padding: 20px 10px;
    }
    
    .event-stats {
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0.5rem;
    }
    
    .stat-item {
        padding: 0.5rem 0.25rem;
    }
    
    .stat-number {
        font-size: 1.4rem;
    }
    
    .stat-label {
        font-size: 0.7rem;
    }
    
    .contestants-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 8px;
    }
    
    .contestant-card {
        padding: 8px;
    }
    
    .contestant-image, .contestant-placeholder {
        width: 45px;
        height: 45px;
        margin-bottom: 5px;
    }
    
    .contestant-name {
        font-size: 0.8rem;
    }
    
    .contestant-code {
        font-size: 0.65rem;
        padding: 1px 4px;
    }
    
    .event-title {
        font-size: 1.6rem;
    }
    
    .section-title {
        font-size: 1.2rem;
    }
    
    .category-title {
        font-size: 1rem;
    }
}
</style>

<div class="voting-container">
    <!-- Modern Event Header -->
    <div class="event-header">
        <div class="header-content">
            <h1 class="event-title"><?= htmlspecialchars($event['name']) ?></h1>
            <p class="event-subtitle">Cast your votes and support your favorite contestants</p>
            
            <div class="event-stats">
                <div class="stat-item">
                    <i class="fas fa-users stat-icon"></i>
                    <div class="stat-number"><?= count($contestants) ?></div>
                    <div class="stat-label">Contestants</div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-layer-group stat-icon"></i>
                    <div class="stat-number"><?= count($contestantsByCategory) ?></div>
                    <div class="stat-label">Categories</div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-calendar-alt stat-icon"></i>
                    <div class="stat-number"><?= date('d', strtotime($event['end_date'])) ?></div>
                    <div class="stat-label"><?= date('M Y', strtotime($event['end_date'])) ?></div>
                </div>
            </div>
            
            <div class="status-badges">
                <div class="status-badge-voting voting-indicator">
                    <i class="fas fa-vote-yea"></i>
                    <span>Voting Active</span>
                </div>
                
                <?php if ($event['results_visible']): ?>
                    <div class="status-badge-voting badge-visible">
                        <i class="fas fa-eye"></i>
                        <span>Results Visible</span>
                    </div>
                <?php else: ?>
                    <div class="status-badge-voting badge-hidden">
                        <i class="fas fa-eye-slash"></i>
                        <span>Results Hidden</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Alert Container -->
    <div id="alert-container"></div>
    
    <!-- Voting Form -->
    <form id="votingForm">
        <input type="hidden" id="contestant_id" name="contestant_id">
        <input type="hidden" id="bundle_id" name="bundle_id">
        
        <!-- Step 1: Select Contestant -->
        <div class="voting-section">
            <h2 class="section-title">Step 1: Select Contestant</h2>
            
            <?php foreach ($contestantsByCategory as $category): ?>
            <div class="category-section">
                <h3 class="category-title"><?= htmlspecialchars($category['name']) ?> (<?= count($category['contestants']) ?> contestants)</h3>
                
                <div class="contestants-grid">
                    <?php foreach ($category['contestants'] as $contestant): 
                        // Use category-specific photo if available, otherwise use default
                        $voteImage = (!empty($contestant['category_image_url']) && $contestant['use_category_photo']) 
                            ? $contestant['category_image_url'] 
                            : $contestant['image_url'];
                    ?>
                    <div class="contestant-card" 
                         data-contestant-id="<?= $contestant['id'] ?>"
                         onclick="selectContestant(<?= $contestant['id'] ?>)">
                        
                        <?php if ($voteImage): ?>
                            <img src="<?= htmlspecialchars(image_url($voteImage)) ?>" 
                                 alt="<?= htmlspecialchars($contestant['name']) ?>"
                                 class="contestant-image">
                        <?php elseif (!empty($event['featured_image'])): ?>
                            <img src="<?= htmlspecialchars(image_url($event['featured_image'])) ?>" 
                                 alt="<?= htmlspecialchars($contestant['name']) ?>"
                                 class="contestant-image"
                                 style="opacity: 0.8; border: 2px solid #667eea;">
                        <?php else: ?>
                            <div class="contestant-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="contestant-name"><?= htmlspecialchars($contestant['name']) ?></div>
                        
                        <?php if ($contestant['short_code']): ?>
                            <div class="contestant-code"><?= htmlspecialchars($contestant['short_code']) ?></div>
                        <?php endif; ?>
                        
                        <?php if ($event['results_visible']): ?>
                        <div class="vote-stats">
                            <i class="fas fa-heart" style="color: #ff4444;"></i>
                            <?= number_format($contestant['total_votes'] ?? 0) ?> votes
                        </div>
                        <?php else: ?>
                        <div class="vote-stats">
                            <i class="fas fa-heart" style="color: #ccc;"></i>
                            <span style="color: #999;">Results Hidden</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Step 2: Select Package -->
        <div class="voting-section">
            <h2 class="section-title">Step 2: Select Vote Package</h2>
            
            <div class="packages-grid">
                <?php foreach ($bundles as $bundle): ?>
                <div class="package-card" 
                     data-bundle-id="<?= $bundle['id'] ?>"
                     onclick="selectBundle(<?= $bundle['id'] ?>, <?= $bundle['votes'] ?>, <?= $bundle['price'] ?>)">
                    
                    <div class="package-name"><?= htmlspecialchars($bundle['name']) ?></div>
                    
                    <div class="vote-count"><?= $bundle['votes'] ?></div>
                    <div style="margin-bottom: 10px; color: #666;">Vote<?= $bundle['votes'] > 1 ? 's' : '' ?></div>
                    
                    <div class="package-price">GH₵<?= number_format($bundle['price'], 2) ?></div>
                    <div class="price-per-vote">GH₵<?= number_format($bundle['price'] / $bundle['votes'], 2) ?> per vote</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Step 3: Complete Vote -->
        <div class="form-section">
            <h2 class="section-title">Step 3: Complete Your Vote</h2>
            
            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label">Mobile Number *</label>
                    <input type="tel" id="msisdn" name="msisdn" class="form-input" 
                           placeholder="+233 24 123 4567" required>
                    <small style="color: #666; font-size: 0.9rem;">Include country code (e.g., +233 for Ghana)</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Coupon Code (Optional)</label>
                    <input type="text" id="coupon_code" name="coupon_code" class="form-input" 
                           placeholder="Enter coupon code">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Referral Code (Optional)</label>
                    <input type="text" id="referral_code" name="referral_code" class="form-input" 
                           placeholder="Enter referral code">
                </div>
            </div>
            
            <!-- Vote Summary -->
            <div id="vote-summary" style="margin: 20px 0; padding: 20px; background: #f8f9fa; border-radius: 8px; display: none;">
                <!-- Summary will be populated by JavaScript -->
            </div>
            
            <button type="submit" id="vote-button" class="vote-button" disabled>
                <span id="button-text">Cast Your Vote</span>
                <span id="button-loader" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Processing...
                </span>
            </button>
        </div>
    </form>
</div>

<script>
// Simple JavaScript for voting functionality
let selectedContestant = null;
let selectedBundle = null;

function selectContestant(contestantId) {
    // Remove previous selections
    document.querySelectorAll('.contestant-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Select current contestant
    const selectedCard = document.querySelector(`[data-contestant-id="${contestantId}"]`);
    selectedCard.classList.add('selected');
    
    // Store selection
    selectedContestant = contestantId;
    document.getElementById('contestant_id').value = contestantId;
    
    updateVoteSummary();
    updateVoteButton();
}

function selectBundle(bundleId, votes, price) {
    // Remove previous selections
    document.querySelectorAll('.package-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Select current bundle
    const selectedCard = document.querySelector(`[data-bundle-id="${bundleId}"]`);
    selectedCard.classList.add('selected');
    
    // Store selection
    selectedBundle = { id: bundleId, votes: votes, price: price };
    document.getElementById('bundle_id').value = bundleId;
    
    updateVoteSummary();
    updateVoteButton();
}

function updateVoteSummary() {
    const summaryDiv = document.getElementById('vote-summary');
    
    if (selectedContestant && selectedBundle) {
        const contestantCard = document.querySelector(`[data-contestant-id="${selectedContestant}"]`);
        const contestantName = contestantCard.querySelector('.contestant-name').textContent;
        
        summaryDiv.innerHTML = `
            <h4 style="margin-bottom: 15px; color: #333;">Vote Summary</h4>
            <div style="margin-bottom: 10px;">
                <strong>Contestant:</strong> ${contestantName}
            </div>
            <div style="margin-bottom: 10px;">
                <strong>Package:</strong> ${selectedBundle.votes} vote${selectedBundle.votes > 1 ? 's' : ''}
            </div>
            <div style="margin-bottom: 10px;">
                <strong>Total:</strong> <span style="font-size: 1.2rem; color: #667eea; font-weight: 600;">GH₵${selectedBundle.price.toFixed(2)}</span>
            </div>
        `;
        summaryDiv.style.display = 'block';
    } else {
        summaryDiv.style.display = 'none';
    }
}

function updateVoteButton() {
    const voteButton = document.getElementById('vote-button');
    const msisdn = document.getElementById('msisdn').value;
    
    if (selectedContestant && selectedBundle && msisdn.length >= 10) {
        voteButton.disabled = false;
    } else {
        voteButton.disabled = true;
    }
}

// Form submission
document.getElementById('votingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!selectedContestant || !selectedBundle) {
        alert('Please select a contestant and vote package');
        return;
    }
    
    const msisdn = document.getElementById('msisdn').value;
    if (!msisdn || msisdn.length < 10) {
        alert('Please enter a valid phone number');
        return;
    }
    
    // Show loading state
    const voteButton = document.getElementById('vote-button');
    const buttonText = document.getElementById('button-text');
    const buttonLoader = document.getElementById('button-loader');
    
    buttonText.style.display = 'none';
    buttonLoader.style.display = 'inline';
    voteButton.disabled = true;
    
    // Prepare form data
    const formData = new FormData();
    formData.append('contestant_id', selectedContestant);
    formData.append('bundle_id', selectedBundle.id);
    formData.append('msisdn', msisdn);
    formData.append('coupon_code', document.getElementById('coupon_code').value);
    formData.append('referral_code', document.getElementById('referral_code').value);
    
    // Submit vote
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('alert-container').innerHTML = `
                <div class="alert success">
                    <strong>Vote Successful!</strong> Your vote has been recorded.
                    <br><small>Receipt: <strong>${data.receipt}</strong></small>
                </div>
            `;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            document.getElementById('alert-container').innerHTML = `
                <div class="alert error">
                    <strong>Vote Failed!</strong> ${data.message || 'Please try again.'}
                </div>
            `;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    })
    .catch(error => {
        document.getElementById('alert-container').innerHTML = `
            <div class="alert error">
                <strong>Network Error!</strong> Please check your connection and try again.
            </div>
        `;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    })
    .finally(() => {
        // Restore button state
        buttonText.style.display = 'inline';
        buttonLoader.style.display = 'none';
        voteButton.disabled = false;
    });
});

// Update vote button when phone number changes
document.getElementById('msisdn').addEventListener('input', updateVoteButton);
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
