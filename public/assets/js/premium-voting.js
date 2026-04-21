/**
 * Premium Voting Page JavaScript - Ultra Modern Experience
 */

// Global state
let selectedContestant = null;
let selectedBundle = null;
let isSubmitting = false;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializePremiumVoting();
});

function initializePremiumVoting() {
    // Add entrance animations
    addEntranceAnimations();
    
    // Initialize form handlers
    initializeFormHandlers();
    
    // Setup carousel navigation
    setupCarouselNavigation();
    
    // Initialize input enhancements
    initializeInputEnhancements();
    
    // Setup scroll effects
    setupScrollEffects();
}

function addEntranceAnimations() {
    // Animate hero elements
    const heroElements = document.querySelectorAll('.hero-text > *');
    heroElements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        setTimeout(() => {
            element.style.transition = 'all 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 200);
    });
    
    // Animate voting steps
    const votingSteps = document.querySelectorAll('.voting-step');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 300);
            }
        });
    }, { threshold: 0.1 });
    
    votingSteps.forEach(step => {
        step.style.opacity = '0';
        step.style.transform = 'translateY(50px)';
        step.style.transition = 'all 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
        observer.observe(step);
    });
}

function initializeFormHandlers() {
    const form = document.getElementById('votingForm');
    if (form) {
        form.addEventListener('submit', handlePremiumSubmit);
    }
    
    // Phone number formatting and validation
    const phoneInput = document.getElementById('msisdn');
    if (phoneInput) {
        phoneInput.addEventListener('input', handlePhoneInput);
        phoneInput.addEventListener('blur', formatPhoneNumber);
    }
    
    // Real-time validation
    const inputs = document.querySelectorAll('.premium-input');
    inputs.forEach(input => {
        input.addEventListener('input', updateVoteButton);
        input.addEventListener('focus', handleInputFocus);
        input.addEventListener('blur', handleInputBlur);
    });
}

function setupCarouselNavigation() {
    // Initialize horizontal scrolling for each category
    const carousels = document.querySelectorAll('.contestants-carousel');
    carousels.forEach(carousel => {
        const track = carousel.querySelector('.contestants-track');
        if (track) {
            // Add touch/mouse drag scrolling
            let isDown = false;
            let startX;
            let scrollLeft;
            
            track.addEventListener('mousedown', (e) => {
                isDown = true;
                track.style.cursor = 'grabbing';
                startX = e.pageX - track.offsetLeft;
                scrollLeft = track.scrollLeft;
            });
            
            track.addEventListener('mouseleave', () => {
                isDown = false;
                track.style.cursor = 'grab';
            });
            
            track.addEventListener('mouseup', () => {
                isDown = false;
                track.style.cursor = 'grab';
            });
            
            track.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - track.offsetLeft;
                const walk = (x - startX) * 2;
                track.scrollLeft = scrollLeft - walk;
            });
        }
    });
}

function scrollCategory(categoryId, direction) {
    const carousel = document.getElementById(`carousel-${categoryId}`);
    const track = carousel.querySelector('.contestants-track');
    const cardWidth = 280 + 24; // card width + gap
    const scrollAmount = cardWidth * 2; // scroll 2 cards at a time
    
    if (direction === 'next') {
        track.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    } else {
        track.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    }
    
    // Update navigation button states
    setTimeout(() => updateNavButtons(categoryId), 300);
}

function updateNavButtons(categoryId) {
    const carousel = document.getElementById(`carousel-${categoryId}`);
    const track = carousel.querySelector('.contestants-track');
    const prevBtn = document.querySelector(`[onclick="scrollCategory('${categoryId}', 'prev')"]`);
    const nextBtn = document.querySelector(`[onclick="scrollCategory('${categoryId}', 'next')"]`);
    
    if (prevBtn && nextBtn) {
        prevBtn.style.opacity = track.scrollLeft <= 0 ? '0.5' : '1';
        nextBtn.style.opacity = track.scrollLeft >= (track.scrollWidth - track.clientWidth) ? '0.5' : '1';
    }
}

function selectContestant(contestantId) {
    // Remove previous selections
    document.querySelectorAll('.premium-contestant-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Select current contestant
    const selectedCard = document.querySelector(`[data-contestant-id="${contestantId}"]`);
    if (selectedCard) {
        selectedCard.classList.add('selected');
        
        // Add selection animation
        const badge = selectedCard.querySelector('.selection-badge');
        if (badge) {
            badge.style.animation = 'bounceIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
        }
        
        // Scroll to packages section
        setTimeout(() => {
            const packagesSection = document.querySelector('.package-selection');
            if (packagesSection) {
                packagesSection.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }
        }, 500);
    }
    
    // Store selection
    selectedContestant = contestantId;
    document.getElementById('contestant_id').value = contestantId;
    
    // Update summary and button
    updateVoteSummary();
    updateVoteButton();
    
    // Add haptic feedback (if supported)
    if (navigator.vibrate) {
        navigator.vibrate(50);
    }
}

function selectBundle(bundleId, votes, price) {
    // Remove previous selections
    document.querySelectorAll('.premium-package-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Select current bundle
    const selectedCard = document.querySelector(`[data-bundle-id="${bundleId}"]`);
    if (selectedCard) {
        selectedCard.classList.add('selected');
        
        // Add selection animation
        selectedCard.style.animation = 'pulse 0.6s ease';
        
        // Scroll to contact form
        setTimeout(() => {
            const contactSection = document.querySelector('.contact-information');
            if (contactSection) {
                contactSection.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }
        }, 500);
    }
    
    // Store selection
    selectedBundle = { id: bundleId, votes: votes, price: price };
    document.getElementById('bundle_id').value = bundleId;
    
    // Update summary and button
    updateVoteSummary();
    updateVoteButton();
    
    // Add haptic feedback
    if (navigator.vibrate) {
        navigator.vibrate([50, 50, 50]);
    }
}

function updateVoteSummary() {
    const summaryDiv = document.getElementById('vote-summary');
    
    if (selectedContestant && selectedBundle) {
        const contestantCard = document.querySelector(`[data-contestant-id="${selectedContestant}"]`);
        const contestantName = contestantCard.querySelector('.contestant-name').textContent;
        const contestantImage = contestantCard.querySelector('.contestant-photo, .placeholder-avatar');
        const contestantCode = contestantCard.querySelector('.contestant-code');
        
        let imageHtml = '';
        if (contestantImage) {
            if (contestantImage.tagName === 'IMG') {
                imageHtml = `
                    <div class="summary-avatar">
                        <img src="${contestantImage.src}" alt="${contestantName}">
                    </div>
                `;
            } else {
                imageHtml = `
                    <div class="summary-avatar placeholder">
                        <i class="fas fa-user"></i>
                    </div>
                `;
            }
        }
        
        let codeHtml = '';
        if (contestantCode) {
            codeHtml = `<div class="summary-code">${contestantCode.textContent}</div>`;
        }
        
        summaryDiv.innerHTML = `
            <div class="vote-summary-card">
                ${imageHtml}
                <div class="summary-details">
                    <div class="summary-section">
                        <label>Voting for:</label>
                        <div class="summary-contestant">
                            <span class="contestant-name">${contestantName}</span>
                            ${codeHtml}
                        </div>
                    </div>
                    
                    <div class="summary-section">
                        <label>Package:</label>
                        <div class="summary-package">
                            <span class="vote-count">${selectedBundle.votes} vote${selectedBundle.votes > 1 ? 's' : ''}</span>
                        </div>
                    </div>
                    
                    <div class="summary-section total">
                        <label>Total:</label>
                        <div class="summary-total">
                            <span class="amount">$${selectedBundle.price.toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add summary styling
        addSummaryStyles();
        
        // Animate summary appearance
        const summaryCard = summaryDiv.querySelector('.vote-summary-card');
        if (summaryCard) {
            summaryCard.style.opacity = '0';
            summaryCard.style.transform = 'translateY(20px)';
            setTimeout(() => {
                summaryCard.style.transition = 'all 0.5s ease';
                summaryCard.style.opacity = '1';
                summaryCard.style.transform = 'translateY(0)';
            }, 100);
        }
    }
}

function addSummaryStyles() {
    if (document.getElementById('summary-styles')) return;
    
    const style = document.createElement('style');
    style.id = 'summary-styles';
    style.textContent = `
        .vote-summary-card {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }
        
        .summary-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            border: 3px solid #4facfe;
        }
        
        .summary-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .summary-avatar.placeholder {
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        
        .summary-details {
            flex: 1;
        }
        
        .summary-section {
            margin-bottom: 0.75rem;
        }
        
        .summary-section:last-child {
            margin-bottom: 0;
        }
        
        .summary-section label {
            display: block;
            font-size: 0.8rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.25rem;
        }
        
        .summary-contestant .contestant-name {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 1.1rem;
        }
        
        .summary-code {
            display: inline-block;
            background: var(--primary-gradient);
            color: white;
            padding: 0.125rem 0.5rem;
            border-radius: 0.5rem;
            font-size: 0.7rem;
            margin-top: 0.25rem;
        }
        
        .summary-package .vote-count {
            color: #4facfe;
            font-weight: 600;
        }
        
        .summary-section.total {
            border-top: 1px solid var(--border-light);
            padding-top: 0.75rem;
            margin-top: 1rem;
        }
        
        .summary-total .amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: #4facfe;
        }
    `;
    document.head.appendChild(style);
}

function initializeInputEnhancements() {
    const inputs = document.querySelectorAll('.premium-input');
    inputs.forEach(input => {
        // Add floating label behavior
        if (input.value) {
            input.classList.add('has-value');
        }
    });
}

function handleInputFocus(e) {
    const wrapper = e.target.closest('.input-wrapper');
    if (wrapper) {
        wrapper.classList.add('focused');
    }
}

function handleInputBlur(e) {
    const wrapper = e.target.closest('.input-wrapper');
    if (wrapper) {
        wrapper.classList.remove('focused');
    }
    
    if (e.target.value) {
        e.target.classList.add('has-value');
    } else {
        e.target.classList.remove('has-value');
    }
}

function handlePhoneInput(e) {
    let value = e.target.value.replace(/\D/g, '');
    
    // Format as user types
    if (value.length > 0) {
        if (value.startsWith('233')) {
            value = '+' + value;
        } else if (value.startsWith('0')) {
            value = '+233' + value.substring(1);
        } else if (value.length <= 9) {
            value = '+233' + value;
        }
    }
    
    e.target.value = value;
    updateVoteButton();
}

function formatPhoneNumber(e) {
    let value = e.target.value.replace(/\D/g, '');
    
    if (value.length > 0 && !value.startsWith('233')) {
        if (value.startsWith('0')) {
            value = '233' + value.substring(1);
        } else if (value.length === 9) {
            value = '233' + value;
        }
        e.target.value = '+' + value;
    }
}

function updateVoteButton() {
    const voteButton = document.getElementById('vote-button');
    const msisdn = document.getElementById('msisdn').value;
    
    const isValid = selectedContestant && 
                   selectedBundle && 
                   msisdn.length >= 10 && 
                   !isSubmitting;
    
    if (isValid) {
        voteButton.disabled = false;
        voteButton.classList.remove('disabled');
    } else {
        voteButton.disabled = true;
        voteButton.classList.add('disabled');
    }
}

function setupScrollEffects() {
    // Parallax effect for hero
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const heroImage = document.querySelector('.hero-image');
        
        if (heroImage) {
            heroImage.style.transform = `translateY(${scrolled * 0.5}px)`;
        }
    });
}

function handlePremiumSubmit(e) {
    e.preventDefault();
    
    if (isSubmitting) return;
    
    if (!selectedContestant || !selectedBundle) {
        showPremiumAlert('Please select a contestant and vote package', 'warning');
        return;
    }
    
    const msisdn = document.getElementById('msisdn').value;
    if (!msisdn || msisdn.length < 10) {
        showPremiumAlert('Please enter a valid phone number', 'warning');
        document.getElementById('msisdn').focus();
        return;
    }
    
    // Start loading state
    isSubmitting = true;
    const voteButton = document.getElementById('vote-button');
    voteButton.classList.add('loading');
    updateVoteButton();
    
    // Prepare form data
    const formData = new FormData();
    formData.append('contestant_id', selectedContestant);
    formData.append('bundle_id', selectedBundle.id);
    formData.append('msisdn', msisdn);
    formData.append('coupon_code', document.getElementById('coupon_code').value || '');
    formData.append('referral_code', document.getElementById('referral_code').value || '');
    
    // Submit vote
    fetch(window.location.href, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showVoteSuccess(data);
        } else {
            showVoteError(data.message || 'Vote failed. Please try again.');
        }
    })
    .catch(error => {
        console.error('Vote submission error:', error);
        showVoteError('Network error. Please check your connection and try again.');
    })
    .finally(() => {
        // Reset loading state
        isSubmitting = false;
        voteButton.classList.remove('loading');
        updateVoteButton();
    });
}

function showVoteSuccess(data) {
    // Create success modal/alert
    const alertHtml = `
        <div class="premium-alert success">
            <div class="alert-content">
                <div class="alert-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="alert-body">
                    <h3>Vote Successful! 🎉</h3>
                    <p>Your vote has been recorded successfully.</p>
                    <div class="alert-details">
                        <div class="detail-item">
                            <strong>Receipt Code:</strong> 
                            <code>${data.receipt}</code>
                        </div>
                        <div class="detail-item">
                            <strong>Transaction ID:</strong> 
                            <span>${data.transaction_id}</span>
                        </div>
                        <div class="detail-item">
                            <strong>Votes Cast:</strong> 
                            <span>${data.votes_cast}</span>
                        </div>
                    </div>
                </div>
            </div>
            <button class="alert-close" onclick="closeAlert(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    showPremiumAlert(alertHtml, 'custom');
    
    // Add confetti effect
    if (typeof confetti !== 'undefined') {
        confetti({
            particleCount: 100,
            spread: 70,
            origin: { y: 0.6 }
        });
    }
    
    // Reset form after delay
    setTimeout(() => {
        resetForm();
    }, 5000);
}

function showVoteError(message) {
    const alertHtml = `
        <div class="premium-alert error">
            <div class="alert-content">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="alert-body">
                    <h3>Vote Failed</h3>
                    <p>${message}</p>
                </div>
            </div>
            <button class="alert-close" onclick="closeAlert(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    showPremiumAlert(alertHtml, 'custom');
}

function showPremiumAlert(content, type) {
    const alertContainer = document.getElementById('alert-container');
    
    if (type === 'custom') {
        alertContainer.innerHTML = content;
    } else {
        const alertClass = type === 'warning' ? 'warning' : 'info';
        const iconClass = type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
        
        alertContainer.innerHTML = `
            <div class="premium-alert ${alertClass}">
                <div class="alert-content">
                    <div class="alert-icon">
                        <i class="fas ${iconClass}"></i>
                    </div>
                    <div class="alert-body">
                        <p>${content}</p>
                    </div>
                </div>
                <button class="alert-close" onclick="closeAlert(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    }
    
    // Animate alert appearance
    const alert = alertContainer.querySelector('.premium-alert');
    if (alert) {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-20px)';
        setTimeout(() => {
            alert.style.transition = 'all 0.3s ease';
            alert.style.opacity = '1';
            alert.style.transform = 'translateY(0)';
        }, 100);
    }
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    // Auto-dismiss after 5 seconds (except for success)
    if (type !== 'custom' || !content.includes('successful')) {
        setTimeout(() => {
            closeAlert(alert);
        }, 5000);
    }
}

function closeAlert(element) {
    const alert = element.closest('.premium-alert');
    if (alert) {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-20px)';
        setTimeout(() => {
            alert.remove();
        }, 300);
    }
}

function resetForm() {
    // Clear selections
    document.querySelectorAll('.premium-contestant-card, .premium-package-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Clear form
    document.getElementById('votingForm').reset();
    
    // Reset variables
    selectedContestant = null;
    selectedBundle = null;
    
    // Update UI
    document.getElementById('vote-summary').innerHTML = `
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-arrow-up"></i>
            </div>
            <p>Select a contestant and package to see your summary</p>
        </div>
    `;
    
    updateVoteButton();
    
    // Clear input states
    document.querySelectorAll('.premium-input').forEach(input => {
        input.classList.remove('has-value');
    });
}

// Add premium alert styles
const alertStyles = `
    .premium-alert {
        background: white;
        border-radius: 1rem;
        box-shadow: var(--shadow-xl);
        padding: 1.5rem;
        margin-bottom: 1rem;
        position: relative;
        border-left: 4px solid;
    }
    
    .premium-alert.success {
        border-left-color: #4facfe;
    }
    
    .premium-alert.error {
        border-left-color: #ff4757;
    }
    
    .premium-alert.warning {
        border-left-color: #ffa502;
    }
    
    .alert-content {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .alert-icon {
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    
    .premium-alert.success .alert-icon {
        color: #4facfe;
    }
    
    .premium-alert.error .alert-icon {
        color: #ff4757;
    }
    
    .premium-alert.warning .alert-icon {
        color: #ffa502;
    }
    
    .alert-body h3 {
        margin: 0 0 0.5rem 0;
        color: var(--text-primary);
    }
    
    .alert-body p {
        margin: 0 0 1rem 0;
        color: var(--text-secondary);
    }
    
    .alert-details {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .detail-item {
        font-size: 0.9rem;
    }
    
    .detail-item code {
        background: var(--bg-light);
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-family: monospace;
    }
    
    .alert-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: none;
        border: none;
        font-size: 1.2rem;
        color: var(--text-muted);
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 0.25rem;
        transition: all 0.2s ease;
    }
    
    .alert-close:hover {
        background: var(--bg-light);
        color: var(--text-primary);
    }
`;

// Add styles to document
const styleSheet = document.createElement('style');
styleSheet.textContent = alertStyles;
document.head.appendChild(styleSheet);

// Export functions for global access
window.selectContestant = selectContestant;
window.selectBundle = selectBundle;
window.scrollCategory = scrollCategory;
window.closeAlert = closeAlert;
