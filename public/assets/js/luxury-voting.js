/**
 * Luxury Voting Experience - Clean & Minimal
 */

// Global state
let selectedContestant = null;
let selectedBundle = null;
let isSubmitting = false;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeLuxuryVoting();
});

function initializeLuxuryVoting() {
    setupFormHandlers();
    setupInputEnhancements();
    addScrollAnimations();
}

function setupFormHandlers() {
    const form = document.getElementById('votingForm');
    if (form) {
        form.addEventListener('submit', handleSubmit);
    }
    
    // Phone number formatting
    const phoneInput = document.getElementById('msisdn');
    if (phoneInput) {
        phoneInput.addEventListener('input', handlePhoneInput);
        phoneInput.addEventListener('blur', formatPhoneNumber);
    }
    
    // Real-time validation
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('input', updateSubmitButton);
    });
}

function setupInputEnhancements() {
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
}

function addScrollAnimations() {
    const phases = document.querySelectorAll('.selection-phase');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });
    
    phases.forEach(phase => {
        phase.style.opacity = '0';
        phase.style.transform = 'translateY(30px)';
        phase.style.transition = 'all 0.6s ease';
        observer.observe(phase);
    });
}

function selectContestant(contestantId) {
    // Remove previous selections
    document.querySelectorAll('.contestant-tile').forEach(tile => {
        tile.classList.remove('selected');
    });
    
    // Select current contestant
    const selectedTile = document.querySelector(`[data-contestant-id="${contestantId}"]`);
    if (selectedTile) {
        selectedTile.classList.add('selected');
        
        // Smooth scroll to packages
        setTimeout(() => {
            const packagesPhase = document.querySelector('.packages-phase');
            if (packagesPhase) {
                packagesPhase.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }
        }, 300);
    }
    
    // Store selection
    selectedContestant = contestantId;
    document.getElementById('contestant_id').value = contestantId;
    
    // Update summary and button
    updateVoteSummary();
    updateSubmitButton();
}

function selectBundle(bundleId, votes, price) {
    // Remove previous selections
    document.querySelectorAll('.package-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    // Select current bundle
    const selectedOption = document.querySelector(`[data-bundle-id="${bundleId}"]`);
    if (selectedOption) {
        selectedOption.classList.add('selected');
        
        // Smooth scroll to final phase
        setTimeout(() => {
            const finalPhase = document.querySelector('.final-phase');
            if (finalPhase) {
                finalPhase.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }
        }, 300);
    }
    
    // Store selection
    selectedBundle = { id: bundleId, votes: votes, price: price };
    document.getElementById('bundle_id').value = bundleId;
    
    // Update summary and button
    updateVoteSummary();
    updateSubmitButton();
}

function updateVoteSummary() {
    const summaryDiv = document.getElementById('vote-summary');
    
    if (selectedContestant && selectedBundle) {
        const contestantTile = document.querySelector(`[data-contestant-id="${selectedContestant}"]`);
        const contestantName = contestantTile.querySelector('h4').textContent;
        const contestantImage = contestantTile.querySelector('img');
        const contestantCode = contestantTile.querySelector('.code');
        
        let imageHtml = '';
        if (contestantImage) {
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
        
        let codeHtml = '';
        if (contestantCode) {
            codeHtml = `<div class="summary-code">${contestantCode.textContent}</div>`;
        }
        
        summaryDiv.innerHTML = `
            <div class="summary-content">
                ${imageHtml}
                <div class="summary-details">
                    <div class="summary-item">
                        <label>Contestant</label>
                        <div class="value">
                            ${contestantName}
                            ${codeHtml}
                        </div>
                    </div>
                    
                    <div class="summary-item">
                        <label>Package</label>
                        <div class="value">${selectedBundle.votes} vote${selectedBundle.votes > 1 ? 's' : ''}</div>
                    </div>
                    
                    <div class="summary-item total">
                        <label>Total</label>
                        <div class="value price">$${selectedBundle.price.toFixed(2)}</div>
                    </div>
                </div>
            </div>
        `;
        
        // Add summary styles
        addSummaryStyles();
        
        // Animate summary
        const summaryContent = summaryDiv.querySelector('.summary-content');
        if (summaryContent) {
            summaryContent.style.opacity = '0';
            summaryContent.style.transform = 'translateY(20px)';
            setTimeout(() => {
                summaryContent.style.transition = 'all 0.4s ease';
                summaryContent.style.opacity = '1';
                summaryContent.style.transform = 'translateY(0)';
            }, 100);
        }
    }
}

function addSummaryStyles() {
    if (document.getElementById('summary-styles')) return;
    
    const style = document.createElement('style');
    style.id = 'summary-styles';
    style.textContent = `
        .summary-content {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }
        
        .summary-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            border: 2px solid #1a1a1a;
        }
        
        .summary-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .summary-avatar.placeholder {
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
        }
        
        .summary-details {
            flex: 1;
        }
        
        .summary-item {
            margin-bottom: 1rem;
        }
        
        .summary-item:last-child {
            margin-bottom: 0;
        }
        
        .summary-item label {
            display: block;
            font-size: 0.8rem;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }
        
        .summary-item .value {
            font-weight: 500;
            color: #1a1a1a;
        }
        
        .summary-code {
            display: inline-block;
            background: #f0f0f0;
            color: #666;
            padding: 2px 8px;
            border-radius: 8px;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }
        
        .summary-item.total {
            border-top: 1px solid #f0f0f0;
            padding-top: 1rem;
            margin-top: 1rem;
        }
        
        .summary-item .value.price {
            font-size: 1.5rem;
            font-weight: 300;
            color: #1a1a1a;
        }
    `;
    document.head.appendChild(style);
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
    updateSubmitButton();
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

function updateSubmitButton() {
    const submitButton = document.getElementById('vote-button');
    const msisdn = document.getElementById('msisdn').value;
    
    const isValid = selectedContestant && 
                   selectedBundle && 
                   msisdn.length >= 10 && 
                   !isSubmitting;
    
    submitButton.disabled = !isValid;
}

function handleSubmit(e) {
    e.preventDefault();
    
    if (isSubmitting) return;
    
    if (!selectedContestant || !selectedBundle) {
        showAlert('Please select a contestant and vote package', 'warning');
        return;
    }
    
    const msisdn = document.getElementById('msisdn').value;
    if (!msisdn || msisdn.length < 10) {
        showAlert('Please enter a valid phone number', 'warning');
        document.getElementById('msisdn').focus();
        return;
    }
    
    // Start loading state
    isSubmitting = true;
    const submitButton = document.getElementById('vote-button');
    submitButton.classList.add('loading');
    updateSubmitButton();
    
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
            showAlert(data.message || 'Vote failed. Please try again.', 'error');
        }
    })
    .catch(error => {
        console.error('Vote submission error:', error);
        showAlert('Network error. Please check your connection and try again.', 'error');
    })
    .finally(() => {
        // Reset loading state
        isSubmitting = false;
        submitButton.classList.remove('loading');
        updateSubmitButton();
    });
}

function showVoteSuccess(data) {
    const alertHtml = `
        <div class="luxury-alert success">
            <div class="alert-content">
                <div class="alert-header">
                    <h3>Vote Successful! 🎉</h3>
                </div>
                <div class="alert-body">
                    <p>Your vote has been recorded successfully.</p>
                    <div class="alert-details">
                        <div class="detail-row">
                            <strong>Receipt:</strong> 
                            <code>${data.receipt}</code>
                        </div>
                        <div class="detail-row">
                            <strong>Transaction:</strong> 
                            <span>${data.transaction_id}</span>
                        </div>
                        <div class="detail-row">
                            <strong>Votes Cast:</strong> 
                            <span>${data.votes_cast}</span>
                        </div>
                    </div>
                </div>
            </div>
            <button class="alert-close" onclick="closeAlert(this)">×</button>
        </div>
    `;
    
    showAlert(alertHtml, 'custom');
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    // Reset form after delay
    setTimeout(() => {
        resetForm();
    }, 5000);
}

function showAlert(content, type) {
    const alertContainer = document.getElementById('alert-container');
    
    if (type === 'custom') {
        alertContainer.innerHTML = content;
    } else {
        const alertClass = type === 'warning' ? 'warning' : type === 'error' ? 'error' : 'info';
        
        alertContainer.innerHTML = `
            <div class="luxury-alert ${alertClass}">
                <div class="alert-content">
                    <p>${content}</p>
                </div>
                <button class="alert-close" onclick="closeAlert(this)">×</button>
            </div>
        `;
    }
    
    // Animate alert
    const alert = alertContainer.querySelector('.luxury-alert');
    if (alert) {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            alert.style.transition = 'all 0.3s ease';
            alert.style.opacity = '1';
            alert.style.transform = 'translateY(0)';
        }, 100);
    }
    
    // Auto-dismiss warnings and errors
    if (type !== 'custom') {
        setTimeout(() => {
            closeAlert(alert);
        }, 5000);
    }
}

function closeAlert(element) {
    const alert = element.closest('.luxury-alert');
    if (alert) {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            alert.remove();
        }, 300);
    }
}

function resetForm() {
    // Clear selections
    document.querySelectorAll('.contestant-tile, .package-option').forEach(element => {
        element.classList.remove('selected');
    });
    
    // Clear form
    document.getElementById('votingForm').reset();
    
    // Reset variables
    selectedContestant = null;
    selectedBundle = null;
    
    // Update UI
    document.getElementById('vote-summary').innerHTML = `
        <div class="summary-placeholder">
            <i class="fas fa-vote-yea"></i>
            <p>Your vote summary will appear here</p>
        </div>
    `;
    
    updateSubmitButton();
}

// Add alert styles
const alertStyles = `
    .luxury-alert {
        position: relative;
        margin-bottom: 1rem;
    }
    
    .alert-content h3 {
        margin: 0 0 0.5rem 0;
        color: #1a1a1a;
    }
    
    .alert-content p {
        margin: 0 0 1rem 0;
        color: #666;
    }
    
    .alert-details {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9rem;
    }
    
    .detail-row code {
        background: #f0f0f0;
        padding: 2px 8px;
        border-radius: 4px;
        font-family: monospace;
    }
    
    .alert-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #999;
        cursor: pointer;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease;
    }
    
    .alert-close:hover {
        background: #f0f0f0;
        color: #1a1a1a;
    }
`;

// Add styles to document
const styleSheet = document.createElement('style');
styleSheet.textContent = alertStyles;
document.head.appendChild(styleSheet);

// Export functions for global access
window.selectContestant = selectContestant;
window.selectBundle = selectBundle;
window.closeAlert = closeAlert;
