<?php include __DIR__ . '/../layout/header.php'; ?>

<style>
/* Modern Payment Interface */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html, body {
    height: 100%;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: linear-gradient(135deg, #8B5CF6 0%, #A855F7 100%);
    color: #2d3748;
    overflow: hidden;
}

/* Main Container */
.payment-container {
    width: 100vw;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

/* Payment Card */
.payment-card {
    width: 100%;
    max-width: 900px;
    height: 600px;
    background: white;
    border-radius: 1rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    display: grid;
    grid-template-columns: 1fr 1fr;
    overflow: hidden;
}

/* Summary Section - Left Side */
.summary-section {
    background: linear-gradient(135deg, #374151 0%, #4B5563 100%);
    color: white;
    padding: 3rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
}

.summary-header {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 2rem;
    color: white;
}

.nominee-display {
    text-align: center;
    margin-bottom: 2rem;
}

.nominee-avatar-large {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid rgba(255, 255, 255, 0.2);
    margin: 0 auto 1.5rem;
    display: block;
}

.nominee-avatar-placeholder-large {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: rgba(255, 255, 255, 0.5);
    margin: 0 auto 1.5rem;
}

.nominee-name-large {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: white;
}

.nominee-category {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 0.25rem;
}

.event-title {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.7);
    font-weight: 500;
}

/* Vote Summary Details */
.vote-summary-details {
    margin-top: 2rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.summary-label {
    color: rgba(255, 255, 255, 0.8);
}

.summary-value {
    color: white;
    font-weight: 600;
}

.total-section {
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    padding-top: 1rem;
    margin-top: 1rem;
}

.total-amount {
    font-size: 2rem;
    font-weight: 700;
    color: white;
}

/* Payment Section - Right Side */
.payment-section {
    background: white;
    padding: 3rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.payment-header {
    margin-bottom: 2rem;
}

.payment-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1F2937;
    margin-bottom: 1rem;
}

.payment-logos {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 2rem;
}

.payment-logo {
    height: 24px;
    opacity: 0.7;
}

/* Form Styling */
.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-input {
    width: 100%;
    padding: 1rem;
    border: 2px solid #E5E7EB;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #F9FAFB;
}

.form-input:focus {
    outline: none;
    border-color: #8B5CF6;
    background: white;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.form-input::placeholder {
    color: #9CA3AF;
}

.vote-counter-container {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.vote-counter {
    display: flex;
    align-items: center;
    border: 2px solid #E5E7EB;
    border-radius: 0.5rem;
    overflow: hidden;
    background: white;
}

.counter-btn {
    background: #F3F4F6;
    border: none;
    padding: 1rem;
    cursor: pointer;
    color: #374151;
    font-size: 1.25rem;
    font-weight: 600;
    transition: all 0.2s ease;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.counter-btn:hover {
    background: #E5E7EB;
}

.counter-input {
    border: none;
    padding: 1rem;
    text-align: center;
    font-size: 1.125rem;
    font-weight: 600;
    color: #1F2937;
    width: 80px;
    background: white;
}

.counter-input:focus {
    outline: none;
}

.vote-cost-display {
    color: #6B7280;
    font-size: 0.875rem;
    font-weight: 500;
}

/* Pay Button */
.pay-button {
    width: 100%;
    background: linear-gradient(135deg, #8B5CF6 0%, #A855F7 100%);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 0.5rem;
    font-size: 1.125rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 1rem;
}

.pay-button:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
}

.pay-button:disabled {
    background: #D1D5DB;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.support-text {
    text-align: center;
    font-size: 0.75rem;
    color: #6B7280;
}

.support-link {
    color: #8B5CF6;
    text-decoration: none;
}

.support-link:hover {
    text-decoration: underline;
}

.nominee-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #667eea;
    flex-shrink: 0;
}

.nominee-avatar-placeholder {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e2e8f0, #cbd5e0);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #a0aec0;
    flex-shrink: 0;
}

.nominee-info {
    flex: 1;
}

.nominee-name {
    font-size: 1.25rem;
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

.category-tag, .code-tag {
    font-size: 0.75rem;
    padding: 0.2rem 0.5rem;
    border-radius: 0.5rem;
    font-weight: 500;
}

.category-tag {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.code-tag {
    background: #f7fafc;
    color: #4a5568;
    border: 1px solid #e2e8f0;
}

.vote-count {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    color: #e53e3e;
    font-weight: 600;
    font-size: 0.8rem;
    margin-left: auto;
}

/* Vote Methods - Left Column */
.vote-methods {
    background: white;
    border-radius: 0.75rem;
    padding: 1rem;
    border: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.method-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 0.75rem;
    text-align: center;
}

.vote-method {
    border: 2px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
}

.vote-method:hover {
    border-color: #667eea;
    transform: translateY(-1px);
}

.vote-method.active {
    border-color: #667eea;
    background: linear-gradient(135deg, #f0f4ff, #e8f0fe);
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
    margin: 0 auto 0.5rem;
}

.method-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 0.25rem;
}

.method-desc {
    font-size: 0.75rem;
    color: #718096;
}

/* Contact & Summary - Right Column */
.contact-summary {
    background: white;
    border-radius: 0.75rem;
    padding: 1rem;
    border: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.section-header {
    font-size: 1rem;
    font-weight: 600;
    color: #1a202c;
    text-align: center;
    margin-bottom: 0.5rem;
}

/* Custom Vote Controls */
.custom-vote-input {
    display: none;
    margin-top: 0.75rem;
}

.vote-method.active .custom-vote-input {
    display: block;
}

.vote-counter-group {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.vote-counter {
    display: flex;
    align-items: center;
    border: 2px solid #e2e8f0;
    border-radius: 0.4rem;
    overflow: hidden;
    background: white;
}

.counter-btn {
    background: #f7fafc;
    border: none;
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    color: #4a5568;
    font-size: 1rem;
    font-weight: 600;
}

.counter-btn:hover {
    background: #edf2f7;
}

.counter-input {
    border: none;
    padding: 0.5rem;
    text-align: center;
    font-size: 0.9rem;
    font-weight: 600;
    color: #1a202c;
    width: 60px;
}

.counter-input:focus {
    outline: none;
}

.vote-total {
    color: #667eea;
    font-weight: 600;
    font-size: 0.85rem;
    text-align: center;
}

/* Package Selection */
.package-option {
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 0.5rem;
    margin-bottom: 0.5rem;
    cursor: pointer;
    text-align: center;
    font-size: 0.8rem;
    transition: all 0.2s ease;
}

.package-option:hover {
    border-color: #667eea;
    transform: translateY(-1px);
    background: #f8fafc;
}

.package-option.selected {
    border-color: #667eea;
    background: linear-gradient(135deg, #f0f4ff, #e8f0fe);
}

.package-popular {
    position: absolute;
    top: -8px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #f093fb, #f5576c);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.package-votes {
    font-size: 2rem;
    font-weight: 700;
    color: #667eea;
    margin-bottom: 0.5rem;
}

.package-price {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 0.25rem;
}

/* Contact Form */
.contact-section {
    background: white;
    border-radius: 1.5rem;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
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
    padding: 0.875rem 1rem;
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
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid #e2e8f0;
}

.summary-header {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.summary-items {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e2e8f0;
}

.summary-item:last-child {
    border-bottom: none;
    font-weight: 600;
    font-size: 1.125rem;
    color: #667eea;
}

.summary-label {
    color: #6b7280;
}

.summary-value {
    font-weight: 500;
    color: #1a202c;
}

/* Submit Button */
.vote-submit-btn {
    width: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 1rem;
    border-radius: 0.5rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    box-shadow: 0 4px 14px rgba(102, 126, 234, 0.4);
    transition: all 0.3s ease;
}

.vote-submit-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
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

/* Responsive Design */
@media (max-width: 768px) {
    .payment-container {
        padding: 1rem;
    }
    
    .payment-card {
        grid-template-columns: 1fr;
        grid-template-rows: auto 1fr;
        height: auto;
        max-height: 90vh;
    }
    
    .summary-section {
        padding: 2rem;
    }
    
    .payment-section {
        padding: 2rem;
    }
    
    .nominee-avatar-large {
        width: 100px;
        height: 100px;
    }
    
    .nominee-avatar-placeholder-large {
        width: 100px;
        height: 100px;
        font-size: 2rem;
    }
    
    .vote-counter-container {
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
    }
    
    .vote-counter {
        justify-content: center;
    }
}
</style>





<!-- Alert Container -->
<div id="alert-container"></div>

<!-- Back Button -->
<div style="position: absolute; top: 2rem; left: 2rem; z-index: 100;">
    <?php
        require_once __DIR__ . '/../../src/Helpers/SlugHelper.php';
        $eventSlug = \SmartCast\Helpers\SlugHelper::generateEventSlug($event);
    ?>
    <a href="<?= APP_URL ?>/events/<?= $eventSlug ?>/vote" 
       style="display: flex; align-items: center; gap: 0.5rem; color: white; text-decoration: none; background: rgba(0,0,0,0.2); padding: 0.75rem 1rem; border-radius: 0.5rem; backdrop-filter: blur(10px); font-weight: 500; transition: all 0.3s ease;">
        <i class="fas fa-arrow-left"></i>
        Back to Nominees
    </a>
</div>

<div class="payment-container">
    <div class="payment-card">
        <!-- Summary Section - Left Side -->
        <div class="summary-section">
            <div>
                <h1 class="summary-header">Summary</h1>
                
                <div class="nominee-display">
                    <?php if (!empty($contestant['image_url'])): ?>
                        <img src="<?= htmlspecialchars(image_url($contestant['image_url'])) ?>"
                             alt="<?= htmlspecialchars($contestant['name']) ?>"
                             class="nominee-avatar-large">
                    <?php else: ?>
                        <div class="nominee-avatar-placeholder-large">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="nominee-name-large">Nominee: <?= htmlspecialchars($contestant['name']) ?></div>
                    <?php if (isset($category) && $category): ?>
                        <div class="nominee-category">Category: <?= htmlspecialchars($category['name']) ?></div>
                    <?php endif; ?>
                    <div class="event-title"><?= htmlspecialchars($event['name']) ?></div>
                </div>

                <div class="vote-summary-details">
                    <div class="summary-item">
                        <span class="summary-label">Cost per Vote:</span>
                        <span class="summary-value">GHS <?= number_format($vote_price, 0) ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Vote Count:</span>
                        <span class="summary-value" id="vote-count-display">1</span>
                    </div>
                </div>
            </div>

            <div class="total-section">
                <div style="font-size: 0.9rem; color: rgba(255, 255, 255, 0.8); margin-bottom: 0.5rem;">TOTAL</div>
                <div class="total-amount" id="total-display">GHS <?= number_format($vote_price, 0) ?></div>
            </div>
        </div>

        <!-- Payment Section - Right Side -->
        <div class="payment-section">
            <form id="votingForm">
                <input type="hidden" id="contestant_id" name="contestant_id" value="<?= $contestant['id'] ?>">
                <input type="hidden" id="bundle_id" name="bundle_id">
                <input type="hidden" id="category_id" name="category_id" value="<?= $category['id'] ?? $contestant['category_id'] ?? '' ?>">

                <div class="payment-header">
                    <h2 class="payment-title">Payment</h2>
                    
                    <div class="payment-logos">
                        <span style="color: #FF6B35; font-weight: bold; font-size: 0.9rem;">airteltico</span>
                        <span style="color: #FFD700; font-weight: bold; font-size: 0.9rem;">MTN</span>
                        <span style="color: #E31837; font-weight: bold; font-size: 0.9rem;">Vodafone</span>
                        <span style="color: #1A1F71; font-weight: bold; font-size: 0.9rem;">VISA</span>
                        <span style="color: #EB001B; font-weight: bold; font-size: 0.9rem;">Mastercard</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">* Number of Votes</label>
                    <div class="vote-counter-container">
                        <div class="vote-counter">
                            <button type="button" class="counter-btn" onclick="changeVoteCount(-1)">−</button>
                            <input type="number" id="custom-votes" class="counter-input" value="1" min="1" max="10000" onchange="updateVoteCount()">
                            <button type="button" class="counter-btn" onclick="changeVoteCount(1)">+</button>
                        </div>
                        <div class="vote-cost-display">Each vote costs: GHS <?= number_format($vote_price, 0) ?></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Method</label>
                    <select class="form-input" id="payment_method" name="payment_method">
                        <option value="">Select your payment method</option>
                        <option value="momo">Mobile Money</option>
                        <option value="card">Credit/Debit Card</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number (International numbers should include the country code)</label>
                    <input type="tel" id="msisdn" name="msisdn" class="form-input" 
                           placeholder="Enter Phone Number (International numbers should include the country code)" required>
                </div>

                <button type="submit" id="vote-button" class="pay-button" disabled>
                    Pay Here
                </button>

                <div class="support-text">
                    Having problem with checkout? <a href="#" class="support-link">Contact our support</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Modern Payment Interface JavaScript
let customVoteCount = 1;
const votePrice = <?= $vote_price ?>; // Price per vote from event

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    updateVoteCount();
    
    // Add event listeners
    document.getElementById('msisdn').addEventListener('input', updateSubmitButton);
    document.getElementById('custom-votes').addEventListener('input', updateVoteCount);
    document.getElementById('payment_method').addEventListener('change', updateSubmitButton);
    document.querySelectorAll('.vote-method').forEach(el => {
        el.addEventListener('click', function() {
            selectVoteMethod(el.dataset.method);
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
        document.getElementById('bundle_id').value = '';
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
    updateVoteCount();
}

function updateSubmitButton() {
    const submitBtn = document.getElementById('vote-button');
    const msisdn = document.getElementById('msisdn').value;
    const paymentMethod = document.getElementById('payment_method').value;
    
    const hasVotes = customVoteCount > 0;
    const hasPhone = msisdn.length >= 10;
    const hasPaymentMethod = paymentMethod !== '';
    
    submitBtn.disabled = !(hasVotes && hasPhone && hasPaymentMethod);
}

function selectPackage(bundleId, votes, price) {
    // Remove selection from all packages
    document.querySelectorAll('.package-option').forEach(el => {
        el.classList.remove('selected');
    });
    
    // Select current package
    const selectedPackage = document.querySelector(`[data-bundle-id="${bundleId}"]`);
    selectedPackage.classList.add('selected');
    
    // Store selection
    selectedBundle = { id: bundleId, votes: votes, price: price };
    document.getElementById('bundle_id').value = bundleId;
    
    updateVoteSummary();
    updateSubmitButton();
}

function updateVoteSummary() {
    const summaryDiv = document.getElementById('vote-summary');
    const summaryContent = document.getElementById('summary-content');
    
    let votes = 0;
    let total = 0;
    let method = '';
    
    if (currentVoteMethod === 'custom') {
        votes = customVoteCount;
        total = customVoteCount * votePrice;
        method = 'Custom';
    } else if (currentVoteMethod === 'package' && selectedBundle) {
        votes = selectedBundle.votes;
        total = selectedBundle.price;
        method = 'Package';
    }
    
    if (votes > 0) {
        summaryContent.innerHTML = `
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                <span>Nominee:</span>
                <span style="font-weight: 600;"><?= htmlspecialchars($contestant['name']) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                <span>Votes:</span>
                <span style="font-weight: 600;">${votes}</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: 600; color: #667eea;">
                <span>Total:</span>
                <span>GHS ${total.toFixed(2)}</span>
            </div>
        `;
        summaryDiv.style.display = 'block';
    } else {
        summaryDiv.style.display = 'none';
    }
}

function updateSubmitButton() {
    const submitBtn = document.getElementById('vote-button');
    const msisdn = document.getElementById('msisdn').value;
    
    const hasVotes = (currentVoteMethod === 'custom' && customVoteCount > 0) || 
                     (currentVoteMethod === 'package' && selectedBundle);
    const hasPhone = msisdn.length >= 10;
    
    submitBtn.disabled = !(hasVotes && hasPhone);
}

// Form submission
document.getElementById('votingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const msisdn = document.getElementById('msisdn').value;
    const paymentMethod = document.getElementById('payment_method').value;
    
    if (!msisdn || msisdn.length < 10) {
        showAlert('Please enter a valid phone number', 'error');
        return;
    }
    
    if (!paymentMethod) {
        showAlert('Please select a payment method', 'error');
        return;
    }
    
    if (customVoteCount < 1) {
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
    formData.append('category_id', document.getElementById('category_id').value);
    formData.append('msisdn', msisdn);
    formData.append('payment_method', document.getElementById('payment_method').value);
    
    // Always use custom vote method with the new interface
    formData.append('custom_votes', customVoteCount);
    formData.append('vote_method', 'custom');
    
    // Submit vote
    fetch('<?= APP_URL ?>/events/<?= $eventSlug ?>/vote/process', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.payment_initiated) {
            // Payment initiated - show payment status
            showPaymentStatus(data);
        } else if (data.success) {
            // Direct success (fallback)
            showAlert(`
                <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                <div class="alert-content">
                    <h4>Vote Successful! 🎉</h4>
                    <p>Your vote for <strong><?= htmlspecialchars($contestant['name']) ?></strong> has been recorded.</p>
                    <p><strong>Receipt:</strong> ${data.receipt}</p>
                    <p><strong>Votes Cast:</strong> ${data.votes_cast}</p>
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
            }, 3000);
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

function showPaymentStatus(paymentData) {
    const alertContainer = document.getElementById('alert-container');
    
    // Show payment initiated message
    alertContainer.innerHTML = `
        <div class="alert success">
            <div class="alert-icon"><i class="fas fa-mobile-alt"></i></div>
            <div class="alert-content">
                <h4>Payment Initiated 📱</h4>
                <p>${paymentData.message}</p>
                <p><strong>Reference:</strong> ${paymentData.payment_reference}</p>
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
    
    // Start checking payment status
    checkPaymentStatus(paymentData.transaction_id, paymentData.status_check_url);
}

function checkPaymentStatus(transactionId, statusUrl) {
    let attempts = 0;
    const maxAttempts = 60; // Check for 5 minutes (every 5 seconds)
    
    const statusChecker = setInterval(() => {
        attempts++;
        
        fetch(statusUrl)
        .then(response => response.json())
        .then(data => {
            const statusText = document.getElementById('status-text');
            
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
                        if (statusText) {
                            statusText.textContent = `Checking payment status... (${attempts}/${maxAttempts})`;
                        }
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
            if (attempts >= 3) { // Stop after 3 failed attempts
                clearInterval(statusChecker);
                showPaymentError();
            }
        });
    }, 5000); // Check every 5 seconds
}

function showPaymentSuccess(data) {
    showAlert(`
        <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
        <div class="alert-content">
            <h4>Payment Successful! 🎉</h4>
            <p>Your vote for <strong><?= htmlspecialchars($contestant['name']) ?></strong> has been recorded.</p>
            <p><strong>Receipt Number:</strong> ${data.receipt_number}</p>
            <p><strong>Amount:</strong> GHS ${data.amount}</p>
            <p><strong>Time:</strong> ${new Date(data.timestamp).toLocaleString()}</p>
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
    
    // Auto-dismiss after 5 seconds for non-success alerts
    if (type !== 'success') {
        setTimeout(() => {
            alertContainer.innerHTML = '';
        }, 5000);
    }
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
