
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="app-url" content="<?= APP_URL ?>">
    <title><?= $title ?? 'Vote' ?> - <?= APP_NAME ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/public/assets/images/favicon.svg">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= APP_URL ?>/public/assets/images/icon-192.png">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<style>
/* Professional Voting Form - Ultra Modern Design */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body { 
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    margin: 0;
    padding: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    color: #2d3748;
    line-height: 1.6;
    min-height: 100vh;
    animation: gradientShift 10s ease infinite;
}

@keyframes gradientShift {
    0%, 100% { background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%); }
    50% { background: linear-gradient(135deg, #f093fb 0%, #f5576c 50%, #4facfe 100%); }
}

/* Container & Layout */
.professional-vote-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1.5rem;
    min-height: 100vh;
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 1rem;
    align-items: start;
}

.sidebar-container {
    position: sticky;
    top: 1.5rem;
    height: fit-content;
}

.main-content {
    min-height: 100vh;
}

.back-navigation {
    grid-column: 1 / -1;
    margin-bottom: 0.2rem;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #4a5568;
    text-decoration: none;
    font-weight: 500;
    padding: 0.15rem 0.3rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.back-link:hover {
    background: rgba(255, 255, 255, 0.9);
    color: #2d3748;
    text-decoration: none;
    transform: translateX(-2px);
}

/* Nominee Showcase */
.nominee-showcase {
    background: linear-gradient(135deg, rgba(26, 32, 44, 0.95) 0%, rgba(45, 55, 72, 0.95) 50%, rgba(74, 85, 104, 0.95) 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 2rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    transform: translateY(0);
    transition: transform 0.3s ease;
}

.nominee-showcase:hover {
    transform: translateY(-5px);
}

.nominee-showcase::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.05) 50%, transparent 70%);
    animation: shimmer 3s ease-in-out infinite;
}

@keyframes shimmer {
    0%, 100% { transform: translateX(-100%); }
    50% { transform: translateX(100%); }
}

/* Nominee Card */
.nominee-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 1.5rem;
    padding: 1.25rem;
    margin-bottom: 1rem;
    box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(30px);
    position: relative;
    overflow: hidden;
    animation: slideInUp 0.6s ease-out;
}

@keyframes slideInUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.nominee-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 2px;
    background: linear-gradient(90deg, transparent, #667eea, transparent);
    animation: slideRight 2s ease-in-out infinite;
}

@keyframes slideRight {
    0% { left: -100%; }
    100% { left: 100%; }
}

.nominee-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.nominee-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255, 255, 255, 0.3);
    flex-shrink: 0;
    transition: all 0.3s ease;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
}

.nominee-avatar:hover {
    transform: scale(1.05) rotate(3deg);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.25);
}

.nominee-avatar-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(226, 232, 240, 0.8), rgba(203, 213, 224, 0.8));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #a0aec0;
    flex-shrink: 0;
    transition: all 0.3s ease;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
}

.nominee-avatar-placeholder:hover {
    transform: scale(1.05);
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.nominee-details h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 0.5rem;
}

.nominee-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.category-tag {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.775rem;
    font-weight: 500;
}

.code-tag {
    background: #f7fafc;
    color: #4a5568;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    border: 1px solid #e2e8f0;
}

.vote-count {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #e53e3e;
    font-weight: 600;
}

/* Voting Interface */
.voting-interface {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 1.5rem;
    padding: 1.1rem;
    margin-bottom: 1rem;
    box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(30px);
    animation: slideInUp 0.8s ease-out 0.2s both;
    position: relative;
}

.voting-interface::after {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(45deg, #667eea, #764ba2, #f093fb, #f5576c);
    border-radius: 1.5rem;
    z-index: -1;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.voting-interface:hover::after {
    opacity: 0.1;
}

.interface-header {
    text-align: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e2e8f0;
}

.interface-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 0.5rem;
}

.interface-subtitle {
    color: #718096;
    font-size: 0.875rem;
}

/* Vote Input Methods */
.vote-methods {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.vote-method {
    border: 2px solid rgba(226, 232, 240, 0.5);
    border-radius: 1rem;
    padding: 1rem;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    position: relative;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
    transform: scale(1);
}

.vote-method:hover {
    border-color: #667eea;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.2);
    transform: translateY(-2px) scale(1.01);
    background: rgba(255, 255, 255, 0.95);
}

.vote-method.active {
    border-color: #667eea;
    background: linear-gradient(135deg, rgba(240, 244, 255, 0.9), rgba(232, 240, 254, 0.9));
    transform: scale(1.02);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.25);
}

.method-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.method-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.method-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1a202c;
}

.method-description {
    color: #718096;
    font-size: 0.8rem;
    margin-bottom: 0.75rem;
    line-height: 1.3;
}

/* Custom Vote Input */
.custom-vote-input {
    display: none;
}

.vote-method.active .custom-vote-input {
    display: block;
}

.vote-input-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    justify-content: center;
}

.vote-counter {
    display: flex;
    align-items: center;
    border: 2px solid #e2e8f0;
    border-radius: 0.5rem;
    overflow: hidden;
}

.counter-btn {
    background: #f7fafc;
    border: none;
    padding: 0.5rem;
    cursor: pointer;
    color: #4a5568;
    transition: all 0.2s ease;
    font-size: 1rem;
    font-weight: 600;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.counter-btn:hover {
    background: #edf2f7;
    color: #2d3748;
}

.counter-input {
    border: none;
    padding: 0.5rem;
    text-align: center;
    font-size: 1rem;
    font-weight: 600;
    color: #1a202c;
    width: 60px;
    background: white;
}

.counter-input:focus {
    outline: none;
}

.vote-total {
    color: #667eea;
    font-weight: 600;
    font-size: 1rem;
}

/* Package Selection */
.packages-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
}

.package-option {
    border: 2px solid rgba(226, 232, 240, 0.5);
    border-radius: 1rem;
    padding: 0.3rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
    transform: scale(1);
}

.package-option:hover {
    border-color: #667eea;
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.25);
    background: rgba(255, 255, 255, 0.95);
}

.package-option.selected {
    border-color: #667eea;
    background: linear-gradient(135deg, rgba(240, 244, 255, 0.9), rgba(232, 240, 254, 0.9));
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.package-popular {
    position: absolute;
    top: -6px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #f093fb, #f5576c);
    color: white;
    padding: 0.025rem 0.2rem;
    border-radius: 0.75rem;
    font-size: 0.625rem;
    font-weight: 300;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.package-votes {
    font-size: 1.1rem;
    font-weight: 400;
    color: #667eea;
    margin-bottom: 0.25rem;
}

.package-price {
    font-size: 1rem;
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 0.125rem;
}

.package-unit {
    font-size: 0.75rem;
    color: #718096;
}

/* Contact Form */
.contact-section {
    background: white;
    border-radius: 1.5rem;
    padding: 1.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

.form-group {
    position: relative;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-input {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #f9fafb;
}

.form-input:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-help {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

/* Vote Summary */
.vote-summary {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 1rem;
    padding: 1rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e2e8f0;
}

/* Hide mobile vote summary on desktop */
#mobile-vote-summary {
    display: none !important;
}

/* Show sidebar vote summary on desktop */
.sidebar-container .vote-summary {
    display: block;
}

.summary-header {
    font-size: 1rem;
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.summary-items {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.875rem;
}

.summary-item:last-child {
    border-bottom: none;
    font-weight: 600;
    font-size: 1rem;
    color: #667eea;
}

.summary-label {
    color: #6b7280;
}

.summary-value {
    font-weight: 500;
    color: #1a202c;
}

/* Temporarily hide Flutterwave until configured in database */
.payment-method-option:nth-child(3) {
    display: none !important;
} 

/* Submit Button */
.submit-section {
    text-align: center;
}

.vote-submit-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 1rem 2.5rem;
    border-radius: 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    min-width: 200px;
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.vote-submit-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.vote-submit-btn:hover::before {
    left: 100%;
}

.vote-submit-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
}

.vote-submit-btn:disabled {
    background: #cbd5e0;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.btn-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-loader {
    display: none;
}

.vote-submit-btn.loading .btn-content {
    opacity: 0;
}

.vote-submit-btn.loading .btn-loader {
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

/* Alert Styles */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 0.75rem;
    margin-bottom: 1.5rem;
    border: 1px solid;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.alert.success {
    background: linear-gradient(135deg, #f0fff4, #dcfce7);
    border-color: #22c55e;
    color: #15803d;
}

.alert.error {
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    border-color: #ef4444;
    color: #dc2626;
}

.alert-icon {
    font-size: 1.25rem;
    flex-shrink: 0;
    margin-top: 0.125rem;
}

.alert-content h4 {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.alert-content p {
    margin-bottom: 0.25rem;
}

/* Floating Particles Background */
.particles {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: -1;
}

.particle {
    position: absolute;
    width: 4px;
    height: 4px;
    background: rgba(255, 255, 255, 0.6);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}

.particle:nth-child(1) { left: 20%; animation-delay: 0s; }
.particle:nth-child(2) { left: 40%; animation-delay: 1s; }
.particle:nth-child(3) { left: 60%; animation-delay: 2s; }
.particle:nth-child(4) { left: 80%; animation-delay: 3s; }
.particle:nth-child(5) { left: 10%; animation-delay: 4s; }
.particle:nth-child(6) { left: 90%; animation-delay: 5s; }

@keyframes float {
    0%, 100% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { transform: translateY(-10vh) rotate(360deg); opacity: 0; }
}

/* Enhanced Mobile Experience */
@media (max-width: 768px) {
    .professional-vote-container {
        padding: 1rem;
        display: block;
        grid-template-columns: none;
        gap: 0;
    }
    
    .sidebar-container {
        position: static;
        margin-bottom: 1.5rem;
    }
    
    .main-content {
        min-height: auto;
        display: flex;
        flex-direction: column;
    }
    
    .back-navigation {
        grid-column: auto;
        margin-bottom: 0.5rem;
    }
    
    .vote-methods {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .nominee-header {
        flex-direction: column;
        text-align: center;
        gap: 0.75rem;
    }
    
    .nominee-avatar, .nominee-avatar-placeholder {
        width: 100px;
        height: 100px;
        font-size: 2.5rem;
    }
    
    .packages-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
    }
    
    .package-option {
        padding: 0.5rem;
    }
    
    .package-votes {
        font-size: 1.25rem;
    }
    
    .package-price {
        font-size: 0.875rem;
    }
    
    .package-unit {
        font-size: 0.625rem;
    }
    
    .vote-input-group {
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
    }
    
    .vote-submit-btn {
        width: 100%;
        padding: 1rem 2rem;
        font-size: 1rem;
    }
    
    .nominee-card, .voting-interface, .contact-section {
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    /* Mobile: Move vote summary above submit button */
    .submit-section {
        order: 2;
    }
    
    #mobile-vote-summary {
        order: 1;
        margin-bottom: 1rem;
        margin-top: 1rem;
    }
    
    /* Hide sidebar vote summary on mobile */
    .sidebar-container .vote-summary {
        display: none !important;
    }
    
    /* Show mobile vote summary on mobile */
    #mobile-vote-summary {
        display: block !important;
    }
}

@media (max-width: 480px) {
    .packages-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 0.375rem;
    }
    
    .package-option {
        padding: 0.375rem;
    }
    
    .package-votes {
        font-size: 1rem;
    }
    
    .package-price {
        font-size: 0.75rem;
    }
    
    .package-unit {
        font-size: 0.5rem;
    }
    
    .nominee-details h1 {
        font-size: 1.25rem;
    }
    
    .interface-title {
}
</style>

<div class="particles">
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
</div>

<div class="professional-vote-container">
    <!-- Back Navigation -->
    <div class="back-navigation">
        <?php
            require_once __DIR__ . '/../../src/Helpers/SlugHelper.php';
            $eventSlug = \SmartCast\Helpers\SlugHelper::generateEventSlug($event);
        ?>
        <a href="<?= APP_URL ?>/events/<?= $eventSlug ?>/vote" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Nominees
        </a>
    </div>

    <!-- Sticky Sidebar with Nominee Information -->
    <div class="sidebar-container">
        <!-- Alert Container -->
        <div id="alert-container"></div>

        <!-- Nominee Information Card -->
        <div class="nominee-card">
            <div class="nominee-header">
                <?php if (!empty($contestant['image_url'])): ?>
                    <img src="<?= htmlspecialchars(image_url($contestant['image_url'])) ?>" 
                         alt="<?= htmlspecialchars($contestant['name']) ?>"
                         class="nominee-avatar">
                <?php elseif (!empty($event['featured_image'])): ?>
                    <img src="<?= htmlspecialchars(image_url($event['featured_image'])) ?>" 
                         alt="<?= htmlspecialchars($contestant['name']) ?>"
                         class="nominee-avatar"
                         style="opacity: 0.8; border: 3px solid #667eea;">
                <?php else: ?>
                    <div class="nominee-avatar-placeholder">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
                
                <div class="nominee-details">
                    <h1><?= htmlspecialchars($contestant['name']) ?></h1>
                    <div class="nominee-meta">
                        <?php if (isset($category) && $category): ?>
                            <span class="category-tag">
                                <i class="fas fa-tag"></i>
                                <?= htmlspecialchars($category['name']) ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if (!empty($contestant['short_code'])): ?>
                            <span class="code-tag">
                                <i class="fas fa-hashtag"></i>
                                <?= htmlspecialchars($contestant['short_code']) ?>
                            </span>
                        <?php endif; ?>
                        
                      <!--  <div class="vote-count">
                            <i class="fas fa-heart"></i>
                            <span><?= number_format($contestant['total_votes'] ?? 0) ?> votes</span>
                        </div> -->
                    </div>
                </div>
            </div>
            
            <?php if (!empty($contestant['bio'])): ?>
             <!--   <p style="color: #6b7280; line-height: 1.6; margin-top: 1rem;">
                    <?= htmlspecialchars($contestant['bio']) ?>
                </p> -->
            <?php endif; ?>
        </div>

        <!-- Vote Summary in Sidebar -->
        <div id="vote-summary" class="vote-summary" style="display: none;">
            <div class="summary-header">
                <i class="fas fa-receipt"></i>
                Vote Summary
            </div>
            <div class="summary-items" id="summary-content">
                <!-- Summary will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
    <form id="votingForm">
        <input type="hidden" id="contestant_id" name="contestant_id" value="<?= $contestant['id'] ?>">
        <input type="hidden" id="bundle_id" name="bundle_id">
        <input type="hidden" id="category_id" name="category_id" value="<?= $category['id'] ?? $contestant['category_id'] ?? '' ?>">
        <input type="hidden" id="coupon_code" name="coupon_code" value="">
        <input type="hidden" id="referral_code" name="referral_code" value="">
        
        <!-- Voting Interface -->
        <div class="voting-interface">
            <div class="interface-header">
                <h2 class="interface-title">Cast Your Vote</h2>
                <p class="interface-subtitle">Choose how you want to vote for this nominee</p>
            </div>
            
            <!-- Vote Methods -->
            <div class="vote-methods">
                <!-- Custom Vote Count -->
                <div class="vote-method" id="custom-method" onclick="selectVoteMethod('custom')">
                    <div class="method-header">
                        <div class="method-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="method-title">Custom Amount</div>
                    </div>
                    <div class="method-description">
                        Enter the exact number of votes you want to cast
                    </div>
                    <div class="custom-vote-input">
                        <div class="vote-input-group">
                            <div class="vote-counter">
                                <button type="button" class="counter-btn" onclick="changeVoteCount(-1)">−</button>
                                <input type="number" id="custom-votes" class="counter-input" value="1" min="1" max="10000" onchange="updateCustomVotes()">
                                <button type="button" class="counter-btn" onclick="changeVoteCount(1)">+</button>
                            </div>
                            <div class="vote-total">
                                Total: GH₵<span id="custom-total"><?= number_format($vote_price, 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Package Selection -->
                <div class="vote-method" id="package-method" onclick="selectVoteMethod('package')">
                    <div class="method-header">
                        <div class="method-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="method-title">Vote Packages</div>
                    </div>
                    <div class="method-description">
                        Choose from pre-defined vote packages with better rates
                    </div>
                </div>
            </div>
            
            <!-- Package Options (shown when package method is selected) -->
            <div id="packages-container" style="display: none;">
                <?php if (empty($bundles)): ?>
                    <p style="text-align: center; color: #666; padding: 2rem;">
                        No vote packages available. Please refresh the page.
                    </p>
                <?php else: ?>
                <div class="packages-grid">
                    <?php foreach ($bundles as $index => $bundle): ?>
                    <div class="package-option" data-bundle-id="<?= $bundle['id'] ?>"
                         onclick="selectPackage(<?= $bundle['id'] ?>, <?= $bundle['votes'] ?>, <?= $bundle['price'] ?>)">
                        
                        <?php if ($index === 1): ?>
                            <div class="package-popular">Most Popular</div>
                        <?php endif; ?>
                        
                        
                        <div style="color: #718096; margin-bottom: 0.2rem; font-size: 0.875rem;">
                          <span class="package-votes"><?= $bundle['votes'] ?></span> Vote<?= $bundle['votes'] > 1 ? 's' : '' ?> / GH₵<?= number_format($bundle['price'], 2) ?>
                        </div>
                        <div class="package-unit">GH₵<?= number_format($bundle['price'] / $bundle['votes'], 2) ?> per Vote</div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payment Method Selection -->
        <div class="payment-method-section" style="margin-bottom: 1.5rem;">
            <div class="interface-header" style="margin-bottom: 1rem;">
                <h3 class="interface-title" style="margin-bottom: 0.25rem;">Payment Method</h3>
                <p class="interface-subtitle" style="margin-bottom: 0;">Choose how you want to pay</p>
            </div>
            
            <div class="payment-methods" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <label class="payment-method-option" style="cursor: pointer;">
                    <input type="radio" name="payment_method" value="mobile_money" checked style="display: none;">
                    <div class="method-card" style="background: rgba(255, 255, 255, 0.95); border: 2px solid #667eea; border-radius: 1rem; padding: 1.5rem; text-align: center; transition: all 0.3s ease;">
                        <i class="fas fa-mobile-alt" style="font-size: 2.5rem; color: #667eea; margin-bottom: 0.75rem;"></i>
                        <div style="font-weight: 600; font-size: 1.1rem; color: #2d3748; margin-bottom: 0.25rem;">Mobile Money (Ghana)</div>
                        <small style="color: #718096; font-size: 0.875rem;">MTN, Telecel, AirtelTigo</small>
                    </div>
                </label>
                
                <label class="payment-method-option" style="cursor: pointer;">
                    <input type="radio" name="payment_method" value="card" style="display: none;">
                    <div class="method-card" style="background: rgba(255, 255, 255, 0.95); border: 2px solid #e2e8f0; border-radius: 1rem; padding: 1.5rem; text-align: center; transition: all 0.3s ease;">
                        <i class="fas fa-credit-card" style="font-size: 2.5rem; color: #718096; margin-bottom: 0.75rem;"></i>
                        <div style="font-weight: 600; font-size: 1.1rem; color: #2d3748; margin-bottom: 0.25rem;">Card Payment</div>
                        <small style="color: #718096; font-size: 0.875rem;">Visa, Mastercard</small>
                    </div>
                </label>
                
                <label class="payment-method-option" style="cursor: pointer;">
                    <input type="radio" name="payment_method" value="flutterwave" style="display: none;">
                    <div class="method-card" style="background: rgba(255, 255, 255, 0.95); border: 2px solid #e2e8f0; border-radius: 1rem; padding: 1.5rem; text-align: center; transition: all 0.3s ease;">
                        <i class="fas fa-globe-africa" style="font-size: 2.5rem; color: #718096; margin-bottom: 0.75rem;"></i>
                        <div style="font-weight: 600; font-size: 1.1rem; color: #2d3748; margin-bottom: 0.25rem;">FlutterWave</div>
                        <small style="color: #718096; font-size: 0.875rem;">Nigeria, Kenya, Uganda, Rwanda +</small>
                    </div>
                </label>
            </div>
            
            <!-- Flutterwave Payment Options -->
            <div id="flutterwave-options" style="display: none; margin-top: 1rem; background: rgba(255, 255, 255, 0.95); border-radius: 1rem; padding: 1.5rem;">
                <!-- Country Selection -->
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" style="margin-bottom: 0.5rem; font-weight: 600;">Select Your Country *</label>
                    <select id="flutterwave-country" class="form-input" style="padding: 0.75rem; border-radius: 0.5rem; border: 2px solid #e2e8f0;">
                        <option value="">Select Country</option>
                        <option value="GH">🇬🇭 Ghana</option>
                        <option value="NG">🇳🇬 Nigeria</option>
                        <option value="KE">🇰🇪 Kenya</option>
                        <option value="UG">🇺🇬 Uganda</option>
                        <option value="RW">🇷🇼 Rwanda</option>
                        <option value="TZ">🇹🇿 Tanzania</option>
                        <option value="ZM">🇿🇲 Zambia</option>
                        <option value="ZA">🇿🇦 South Africa</option>
                    </select>
                </div>
                
                <div style="margin-top: 0.75rem; padding: 0.75rem; background: rgba(102, 126, 234, 0.1); border-radius: 0.5rem; border-left: 4px solid #667eea;">
                    <small style="color: #4a5568;">
                        <i class="fas fa-info-circle" style="color: #667eea;"></i>
                        <strong>Note:</strong> You'll be redirected to Flutterwave to choose your payment method (Mobile Money, Card, Bank Transfer, or USSD) and complete payment securely.
                    </small>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="contact-section" id="contact-section">
            <div class="interface-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0;">
                <div style="flex: 1;">
                    <h3 class="interface-title" style="margin-bottom: 0.25rem;">Contact Information</h3>
                    <p class="interface-subtitle" style="margin-bottom: 0;" id="contact-subtitle">We need your phone number to process the vote</p>
                </div>
                <div class="form-group" style="margin: 0; min-width: 250px; flex-shrink: 0;" id="phone-field">
                    <label class="form-label" style="margin-bottom: 0.25rem;">Mobile Number *</label>
                    <input type="tel" id="msisdn" name="msisdn" class="form-input" 
                           placeholder="+233 545 644 749" required>
                </div>
            </div>
        </div>

        <!-- Mobile Vote Summary (hidden on desktop, shown on mobile above submit button) -->
        <div id="mobile-vote-summary" class="vote-summary" style="display: none;">
            <div class="summary-header">
                <i class="fas fa-receipt"></i>
                Vote Summary
            </div>
            <div class="summary-items" id="mobile-summary-content">
                <!-- Summary will be populated by JavaScript -->
            </div>
        </div>

        <!-- Submit Button -->
        <div class="submit-section">
            <button type="submit" id="vote-button" class="vote-submit-btn" disabled>
                <div class="btn-content">
                    <i class="fas fa-vote-yea"></i>
                    <span>Cast Your Vote</span>
                </div>
                <div class="btn-loader">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
            </button>
        </div>
    </form>
    </div> <!-- End main-content -->
    </div> <!-- End professional-vote-container -->


<script>
// Professional Voting System JavaScript
let currentVoteMethod = null;
let selectedBundle = null;
let customVoteCount = 1;
const votePrice = <?= $vote_price ?>; // Price per vote from event

// Mobile networks by country for Flutterwave
const flutterwaveNetworks = {
    'GH': ['MTN', 'VODAFONE', 'AIRTELTIGO'],
    'NG': ['MTN', 'AIRTEL', 'GLO', '9MOBILE'],
    'KE': ['MPESA', 'AIRTEL'],
    'UG': ['MTN', 'AIRTEL'],
    'RW': ['MTN', 'AIRTEL'],
    'TZ': ['MPESA', 'TIGO', 'AIRTEL'],
    'ZM': ['MTN', 'AIRTEL'],
    'ZA': ['VODACOM']
};

// Initialize the voting interface
document.addEventListener('DOMContentLoaded', function() {
    // Set default to custom method
    selectVoteMethod('custom');
    updateCustomVotes();
    
    // Add event listeners
    document.getElementById('msisdn').addEventListener('input', updateSubmitButton);
    document.getElementById('custom-votes').addEventListener('input', updateCustomVotes);
    
    // Payment method selection
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            handlePaymentMethodChange(this.value);
        });
    });
    
    // Flutterwave country selection (simplified - no payment method needed)
    document.getElementById('flutterwave-country').addEventListener('change', function() {
        updateSubmitButton();
    });
    
    // Style payment method cards on selection
    document.querySelectorAll('.payment-method-option').forEach(label => {
        label.addEventListener('click', function() {
            // Remove selected style from all cards
            document.querySelectorAll('.method-card').forEach(card => {
                card.style.borderColor = '#e2e8f0';
                card.querySelector('i').style.color = '#718096';
            });
            
            // Add selected style to clicked card
            const card = this.querySelector('.method-card');
            const icon = card.querySelector('i');
            card.style.borderColor = '#667eea';
            icon.style.color = '#667eea';
        });
    });
});

function handlePaymentMethodChange(method) {
    const phoneField = document.getElementById('phone-field');
    const msisdnInput = document.getElementById('msisdn');
    const contactSubtitle = document.getElementById('contact-subtitle');
    const flutterwaveOptions = document.getElementById('flutterwave-options');
    
    if (method === 'card') {
        // Card payment - phone optional
        phoneField.style.display = 'none';
        msisdnInput.required = false;
        contactSubtitle.textContent = 'Card payment will redirect to secure checkout page';
        flutterwaveOptions.style.display = 'none';
    } else if (method === 'flutterwave') {
        // Flutterwave - show country selection only
        phoneField.style.display = 'block';
        msisdnInput.required = false; // Phone optional - Flutterwave will collect if needed
        contactSubtitle.textContent = 'You will be redirected to Flutterwave to complete payment';
        flutterwaveOptions.style.display = 'block';
    } else {
        // Mobile money (Ghana) - phone required
        phoneField.style.display = 'block';
        msisdnInput.required = true;
        contactSubtitle.textContent = 'We need your phone number to process the vote';
        flutterwaveOptions.style.display = 'none';
    }
    
    updateSubmitButton();
}

function updateFlutterwaveNetworks(countryCode) {
    const networkSelect = document.getElementById('flutterwave-network');
    networkSelect.innerHTML = '<option value="">Select Network</option>';
    
    if (countryCode && flutterwaveNetworks[countryCode]) {
        flutterwaveNetworks[countryCode].forEach(network => {
            const option = document.createElement('option');
            option.value = network;
            option.textContent = network;
            networkSelect.appendChild(option);
        });
    }
}

function selectVoteMethod(method) {
    // Remove active class from all methods
    document.querySelectorAll('.vote-method').forEach(el => {
        el.classList.remove('active');
    });
    
    // Add active class to selected method
    document.getElementById(method + '-method').classList.add('active');
    
    // Show/hide package container
    const packagesContainer = document.getElementById('packages-container');
    if (method === 'package') {
        packagesContainer.style.display = 'block';
    } else {
        packagesContainer.style.display = 'none';
        // Clear package selection
        document.querySelectorAll('.package-option').forEach(el => {
            el.classList.remove('selected');
        });
        selectedBundle = null;
        const bundleIdElement = document.getElementById('bundle_id');
        if (bundleIdElement) {
            bundleIdElement.value = '';
        }
    }
    
    currentVoteMethod = method;
    updateVoteSummary();
    updateSubmitButton();
}

function changeVoteCount(delta) {
    const input = document.getElementById('custom-votes');
    let newValue = parseInt(input.value) + delta;
    
    if (newValue < 1) newValue = 1;
    if (newValue > 10000) newValue = 10000;
    
    input.value = newValue;
    updateCustomVotes();
}

function updateCustomVotes() {
    const input = document.getElementById('custom-votes');
    customVoteCount = parseInt(input.value) || 1;
    
    if (customVoteCount < 1) customVoteCount = 1;
    if (customVoteCount > 10000) customVoteCount = 10000;
    
    input.value = customVoteCount;
    
    const total = customVoteCount * votePrice;
    document.getElementById('custom-total').textContent = total.toFixed(2);
}

function selectPackage(bundleId, votes, price) {
    // Remove selection from all packages
    document.querySelectorAll('.package-option').forEach(el => {
        el.classList.remove('selected');
    });
    
    // Select current package
    const selectedPackage = document.querySelector(`[data-bundle-id="${bundleId}"]`);
    if (selectedPackage) {
        selectedPackage.classList.add('selected');
    }
    
    // Store selection
    selectedBundle = { id: bundleId, votes: votes, price: price };
    const bundleIdElement = document.getElementById('bundle_id');
    if (bundleIdElement) {
        bundleIdElement.value = bundleId;
    }
    
    updateVoteSummary();
    updateSubmitButton();
}

function updateVoteSummary() {
    const summaryDiv = document.getElementById('vote-summary');
    const summaryContent = document.getElementById('summary-content');
    const mobileSummaryDiv = document.getElementById('mobile-vote-summary');
    const mobileSummaryContent = document.getElementById('mobile-summary-content');
    
    let votes = 0;
    let total = 0;
    let method = '';
    
    if (currentVoteMethod === 'custom') {
        votes = customVoteCount;
        total = customVoteCount * votePrice;
        method = 'Custom Amount';
    } else if (currentVoteMethod === 'package' && selectedBundle) {
        votes = selectedBundle.votes;
        total = selectedBundle.price;
        method = 'Package Deal';
    }
    
    if (votes > 0) {
        const summaryHTML = `
            <div class="summary-item">
                <span class="summary-label">Nominee:</span>
                <span class="summary-value"><?= htmlspecialchars($contestant['name']) ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Method:</span>
                <span class="summary-value">${method}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Votes:</span>
                <span class="summary-value">${votes} vote${votes > 1 ? 's' : ''}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Total Amount:</span>
                <span class="summary-value">GH₵${total.toFixed(2)}</span>
            </div>
        `;
        
        // Update both desktop and mobile summaries
        summaryContent.innerHTML = summaryHTML;
        mobileSummaryContent.innerHTML = summaryHTML;
        summaryDiv.style.display = 'block';
        mobileSummaryDiv.style.display = 'block';
    } else {
        summaryDiv.style.display = 'none';
        mobileSummaryDiv.style.display = 'none';
    }
}

function updateSubmitButton() {
    const submitBtn = document.getElementById('vote-button');
    const msisdn = document.getElementById('msisdn').value;
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'mobile_money';
    
    const hasVotes = (currentVoteMethod === 'custom' && customVoteCount > 0) || 
                     (currentVoteMethod === 'package' && selectedBundle);
    
    // Validation based on payment method
    if (paymentMethod === 'card') {
        // Card payment - only need votes selected
        submitBtn.disabled = !hasVotes;
    } else if (paymentMethod === 'flutterwave') {
        // Flutterwave - only need country selected (payment method handled by Flutterwave)
        const country = document.getElementById('flutterwave-country').value;
        submitBtn.disabled = !(hasVotes && country);
    } else {
        // Mobile money (Ghana) - need both votes and phone
        const hasPhone = msisdn.length >= 10;
        submitBtn.disabled = !(hasVotes && hasPhone);
    }
}

// Form submission
document.getElementById('votingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get payment method
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    
    // Validate phone number for mobile money
    const msisdn = document.getElementById('msisdn').value;
    
    // Phone required for Ghana mobile money only
    const phoneRequired = paymentMethod === 'mobile_money';
    
    if (phoneRequired && (!msisdn || msisdn.length < 10)) {
        showAlert('Please enter a valid phone number', 'error');
        return;
    }
    
    // Validate Flutterwave fields (only country needed)
    if (paymentMethod === 'flutterwave') {
        const country = document.getElementById('flutterwave-country').value;
        
        if (!country) {
            showAlert('Please select your country', 'error');
            return;
        }
    }
    
    if (currentVoteMethod === 'package' && !selectedBundle) {
        showAlert('Please select a vote package', 'error');
        return;
    }
    
    if (currentVoteMethod === 'custom' && customVoteCount < 1) {
        showAlert('Please enter a valid number of votes', 'error');
        return;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('vote-button');
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;
    
    // Prepare form data
    const formData = new FormData();
    formData.append('contestant_id', <?= $contestant['id'] ?>);
    
    // Safely get form values with null checks
    const categoryIdElement = document.getElementById('category_id');
    const couponCodeElement = document.getElementById('coupon_code');
    const referralCodeElement = document.getElementById('referral_code');
    
    formData.append('category_id', categoryIdElement ? categoryIdElement.value : '');
    formData.append('msisdn', msisdn || '');
    formData.append('coupon_code', couponCodeElement ? couponCodeElement.value : '');
    formData.append('referral_code', referralCodeElement ? referralCodeElement.value : '');
    formData.append('payment_method', paymentMethod);
    
    // Add Flutterwave-specific data
    if (paymentMethod === 'flutterwave') {
        formData.append('country', document.getElementById('flutterwave-country').value);
        // Flutterwave will handle payment method selection on their checkout page
    }
    
    if (currentVoteMethod === 'package') {
        formData.append('bundle_id', selectedBundle.id);
    } else {
        // For custom votes, send the vote count and method
        formData.append('custom_votes', customVoteCount);
        formData.append('vote_method', 'custom');
        // Don't send bundle_id for custom votes
    }
    
    // Submit vote
    fetch('<?= APP_URL ?>/events/<?= $eventSlug ?>/vote/process', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.payment_initiated) {
            // Check if we have a payment URL
            if (data.payment_url) {
                if (paymentMethod === 'card') {
                    // Hubtel card payment - redirect to checkout
                    window.location.href = data.payment_url;
                } else if (paymentMethod === 'flutterwave') {
                    // Flutterwave - redirect to hosted checkout page
                    window.location.href = data.payment_url;
                } else {
                    // Mobile money - open in popup
                    showPaymentPopup(data);
                }
            } else {
                // Direct charge (Hubtel mobile money) - show payment status
                showPaymentStatus(data);
            }
        } else if (data.success) {
            // Direct success (fallback) - use available data or defaults
            const receipt = data.receipt || data.payment_reference || data.transaction_id || 'N/A';
            const votesCast = data.votes_cast || data.votes || customVoteCount || 'N/A';
            const contestantName = data.contestant_name || '<?= htmlspecialchars($contestant['name']) ?>';
            
            showAlert(`
                <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                <div class="alert-content">
                    <h4>Vote Successful! 🎉</h4>
                    <p>Your vote for <strong>${contestantName}</strong> has been recorded.</p>
                    <p><strong>Receipt:</strong> ${receipt}</p>
                    <p><strong>Votes Cast:</strong> ${votesCast}</p>
                </div>
            `, 'success');
            
            // Redirect to payment status page after 3 seconds
            setTimeout(() => {
                if (data.transaction_id) {
                    window.location.href = '<?= APP_URL ?>/payment/status/' + data.transaction_id;
                } else {
                    // Fallback to voting page if no transaction ID
                    window.location.href = '<?= APP_URL ?>/events/<?= $eventSlug ?>/vote';
                }
            }, 2000);
        } else {
            console.log('Vote failed:', data); // Debug log
            let errorMessage = data.message || 'Please try again.';
            if (data.errors) {
                errorMessage += '<br><small>Errors: ' + JSON.stringify(data.errors) + '</small>';
            }
            if (data.received_data) {
                errorMessage += '<br><small>Received: ' + JSON.stringify(data.received_data) + '</small>';
            }
            
            showAlert(`
                <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="alert-content">
                    <h4>Vote Failed</h4>
                    <p>${errorMessage}</p>
                </div>
            `, 'error');
        }
    })
    .catch(error => {
        showAlert(`
            <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="alert-content">
                <h4>Network Error</h4>
                <p>Please check your connection and try again.</p>
            </div>
        `, 'error');
    })
    .finally(() => {
        // Reset loading state
        submitBtn.classList.remove('loading');
        updateSubmitButton();
    });
});

function showPaymentPopup(paymentData) {
    const alertContainer = document.getElementById('alert-container');
    
    const receipt = paymentData.receipt || paymentData.payment_reference || 'N/A';
    const votesCast = paymentData.votes_cast || customVoteCount || 'N/A';
    const contestantName = paymentData.contestant_name || '<?= htmlspecialchars($contestant['name']) ?>';
    
    // Show payment popup message
    alertContainer.innerHTML = `
        <div class="alert success">
            <div class="alert-icon"><i class="fas fa-mobile-alt"></i></div>
            <div class="alert-content">
                <h4>Payment Initiated 📱</h4>
                <p>Complete your mobile money payment in the popup window.</p>
                <div style="background: rgba(255,255,255,0.1); padding: 12px; border-radius: 8px; margin: 12px 0;">
                    <p><strong>Contestant:</strong> ${contestantName}</p>
                    <p><strong>Votes:</strong> ${votesCast}</p>
                    <p><strong>Reference:</strong> ${receipt}</p>
                    <p><strong>Provider:</strong> ${paymentData.provider || 'Mobile Money'}</p>
                </div>
                <div style="margin-top: 1rem;">
                    <button onclick="openPaymentPopup('${paymentData.payment_url}')" 
                            style="background: #667eea; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: 600;">
                        <i class="fas fa-external-link-alt"></i> Open Payment Window
                    </button>
                    <div style="margin-top: 10px;">
                        <div class="payment-status-loader">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span id="popup-status">Waiting for payment completion...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    // Auto-open popup
    setTimeout(() => {
        openPaymentPopup(paymentData.payment_url);
    }, 1000);
    
    // Don't start status checking for popup payments - wait for popup message instead
    // The popup will send the result via postMessage
}

function showPaymentStatus(paymentData) {
    const alertContainer = document.getElementById('alert-container');
    
    // Show payment initiated message
    alertContainer.innerHTML = `
        <div class="alert success">
            <div class="alert-icon"><i class="fas fa-mobile-alt"></i></div>
            <div class="alert-content">
                <h4>Payment Initiated 📱</h4>
                <p>Transaction Pending. Check your mobile phone to Approve Payment.</p>
                <div style="margin-top: 1rem;">
                    <div class="payment-status-loader">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span id="status-text">Checking payment status...</span>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    // Start checking payment status and store controller globally
    window.currentStatusController = checkPaymentStatus(paymentData.transaction_id, paymentData.status_check_url);
}

function showPopupClosedOptions(transactionId, statusUrl) {
    showAlert(`
        <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="alert-content">
            <h4>Payment Window Closed</h4>
            <p>The payment window was closed. Your payment may still be processing.</p>
            <div style="margin-top: 1rem;">
                <button onclick="continueStatusCheck('${transactionId}', '${statusUrl}')" 
                        style="background: #667eea; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; margin-right: 10px;">
                    <i class="fas fa-sync"></i> Continue Checking
                </button>
                <button onclick="window.location.href='/events/<?= $eventSlug ?>/vote'" 
                        style="background: #6c757d; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer;">
                    <i class="fas fa-arrow-left"></i> Back to Voting
                </button>
            </div>
            <p style="margin-top: 10px; font-size: 0.9rem; color: #666;">
                You can also check your payment status later using your transaction reference.
            </p>
        </div>
    `, 'warning');
}

function continueStatusCheck(transactionId, statusUrl) {
    // Continue checking but with reduced frequency
    let attempts = 0;
    const maxAttempts = 24; // Check for 2 more minutes (every 5 seconds)
    
    const statusChecker = setInterval(() => {
        attempts++;
        
        fetch(statusUrl)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                switch (data.payment_status) {
                    case 'success':
                        clearInterval(statusChecker);
                        showPaymentSuccess(data);
                        break;
                        
                    case 'failed':
                        clearInterval(statusChecker);
                        showPaymentFailed(data);
                        break;
                        
                    case 'expired':
                        clearInterval(statusChecker);
                        showPaymentExpired(data);
                        break;
                        
                    default:
                        // Update status
                        const alertContainer = document.querySelector('.alert.warning .alert-content h4');
                        if (alertContainer) {
                            alertContainer.textContent = `Still Checking... (${attempts}/${maxAttempts})`;
                        }
                }
            }
            
            if (attempts >= maxAttempts) {
                clearInterval(statusChecker);
                showPaymentTimeout();
            }
        })
        .catch(error => {
            console.error('Status check error:', error);
            clearInterval(statusChecker);
            showPaymentError();
        });
    }, 5000);
}

function checkPaymentStatus(transactionId, statusUrl) {
    let attempts = 0;
    const maxAttempts = 120; // Check for 10 minutes (every 5 seconds) - increased for Hubtel phone approval
    let popupClosed = false;
    let statusChecker;
    
    // Function to update status display
    const updateStatusDisplay = (message, isError = false) => {
        const statusText = document.getElementById('status-text');
        const popupStatus = document.getElementById('popup-status');
        
        if (statusText) {
            statusText.textContent = message;
        }
        if (popupStatus) {
            const icon = isError ? 'fas fa-exclamation-triangle' : 'fas fa-spinner fa-spin';
            popupStatus.innerHTML = `<i class="${icon}"></i> ${message}`;
        }
    };
    
    statusChecker = setInterval(() => {
        attempts++;
        
        // If popup was closed, reduce checking frequency after 30 seconds
        if (popupClosed && attempts > 6) {
            clearInterval(statusChecker);
            showPopupClosedOptions(transactionId, statusUrl);
            return;
        }
        
        fetch(statusUrl)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                switch (data.payment_status) {
                    case 'success':
                        clearInterval(statusChecker);
                        showPaymentSuccess(data);
                        break;
                        
                    case 'failed':
                        clearInterval(statusChecker);
                        showPaymentFailed(data);
                        break;
                        
                    case 'expired':
                        clearInterval(statusChecker);
                        showPaymentExpired(data);
                        break;
                        
                    default:
                        // Update status display
                        const statusMessage = popupClosed 
                            ? `Checking payment status... (${attempts}/6 - popup closed)`
                            : `Checking payment status... (${attempts}/${maxAttempts})`;
                        updateStatusDisplay(statusMessage);
                }
            }
            
            // Stop checking after max attempts
            if (attempts >= maxAttempts) {
                clearInterval(statusChecker);
                showPaymentTimeout();
            }
        })
        .catch(error => {
            console.error('Status check error:', error);
            // Only stop on network errors if we've tried many times
            if (attempts >= 10) { // Stop after 10 failed network attempts (50 seconds)
                clearInterval(statusChecker);
                showPaymentError();
            }
        });
    }, 5000); // Check every 5 seconds
    
    // Return function to notify when popup is closed
    return {
        notifyPopupClosed: () => {
            popupClosed = true;
            updateStatusDisplay('Payment popup closed. Still checking for completion...', true);
        }
    };
}

function showPaymentSuccess(data) {
    // Close payment popup if open
    if (paymentPopup && !paymentPopup.closed) {
        paymentPopup.close();
    }
    
    // Debug: Log the received data
    console.log('Payment success data received:', data);
    
    // Extract data with proper fallbacks
    const receiptNumber = data.receipt_number || data.payment_reference || data.transaction_id || 'N/A';
    const amount = data.amount || data.total_amount || data.price || (selectedBundle ? selectedBundle.price : null) || (customVoteCount ? (customVoteCount * votePrice).toFixed(2) : null) || 'N/A';
    const timestamp = data.timestamp ? new Date(data.timestamp).toLocaleString() : new Date().toLocaleString();
    const contestantName = data.contestant_name || '<?= htmlspecialchars($contestant['name']) ?>';
    
    // Debug: Log the extracted values
    console.log('Extracted values:', { receiptNumber, amount, timestamp, contestantName });
    
    showAlert(`
        <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
        <div class="alert-content">
            <h4>Payment Successful! 🎉</h4>
            <p>Your vote for <strong>${contestantName}</strong> has been recorded.</p>
            <p><strong>Receipt Number:</strong> ${receiptNumber}</p>
            <p><strong>Amount:</strong> GHS ${amount}</p>
            <p><strong>Time:</strong> ${timestamp}</p>
        </div>
    `, 'success');
    
    // Redirect to payment status page after 5 seconds
    setTimeout(() => {
        if (data.transaction_id) {
            window.location.href = '<?= APP_URL ?>/payment/status/' + data.transaction_id;
        } else {
            // Fallback to voting page if no transaction ID
            window.location.href = '<?= APP_URL ?>/events/<?= $eventSlug ?>/vote';
        }
    }, 5000);
}

function showPaymentFailed(data) {
    showAlert(`
        <div class="alert-icon"><i class="fas fa-times-circle"></i></div>
        <div class="alert-content">
            <h4>Payment Failed ❌</h4>
            <p>${data.message}</p>
            <p>Please try again or contact support if the problem persists.</p>
        </div>
    `, 'error');
}

// Listen for messages from payment popup
window.addEventListener('message', function(event) {
    if (event.data.type === 'PAYMENT_COMPLETE') {
        const paymentData = event.data.data;
        
        // Close popup if still open
        if (paymentPopup && !paymentPopup.closed) {
            paymentPopup.close();
        }
        
        // Show payment result on the page
        if (paymentData.success) {
            showPaymentSuccess(paymentData);
        } else {
            showPaymentFailed(paymentData);
        }
    }
});

let paymentPopup = null;

function openPaymentPopup(paymentUrl) {
    // Close existing popup if any
    if (paymentPopup && !paymentPopup.closed) {
        paymentPopup.close();
    }
    
    // Open new popup
    const popupFeatures = 'width=800,height=600,scrollbars=yes,resizable=yes,status=yes,location=yes,toolbar=no,menubar=no';
    paymentPopup = window.open(paymentUrl, 'PaystackPayment', popupFeatures);
    
    // Focus on popup
    if (paymentPopup) {
        paymentPopup.focus();
        
        // Update status
        const statusElement = document.getElementById('popup-status');
        if (statusElement) {
            statusElement.textContent = 'Payment window opened. Complete your mobile money payment.';
        }
        
        // Monitor popup closure
        const checkClosed = setInterval(() => {
            if (paymentPopup.closed) {
                clearInterval(checkClosed);
                // Notify status checker that popup was closed
                if (window.currentStatusController) {
                    window.currentStatusController.notifyPopupClosed();
                }
            }
        }, 1000);
    } else {
        // Popup blocked
        showAlert(`
            <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="alert-content">
                <h4>Popup Blocked</h4>
                <p>Please allow popups for this site and try again.</p>
                <p><a href="${paymentUrl}" target="_blank" style="color: #667eea;">Click here to open payment page manually</a></p>
            </div>
        `, 'error');
    }
}

function showPaymentExpired(data) {
    showAlert(`
        <div class="alert-icon"><i class="fas fa-clock"></i></div>
        <div class="alert-content">
            <h4>Payment Expired ⏰</h4>
            <p>${data.message}</p>
            <p>Please initiate a new payment to complete your vote.</p>
        </div>
    `, 'error');
}

function showPaymentTimeout() {
    showAlert(`
        <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="alert-content">
            <h4>Payment Status Unknown ⚠️</h4>
            <p>We couldn't verify your payment status. Please check your mobile money transaction history.</p>
            <p>If payment was successful, your vote will be counted automatically.</p>
        </div>
    `, 'error');
}

function showPaymentError() {
    showAlert(`
        <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="alert-content">
            <h4>Connection Error 🌐</h4>
            <p>Unable to check payment status due to connection issues.</p>
            <p>Please check your internet connection and refresh the page.</p>
        </div>
    `, 'error');
}

function showAlert(content, type) {
    const alertContainer = document.getElementById('alert-container');
    alertContainer.innerHTML = `<div class="alert ${type}">${content}</div>`;
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

</body>
</html>
