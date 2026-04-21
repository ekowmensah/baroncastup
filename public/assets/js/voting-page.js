/**
 * Modern Voting Page JavaScript
 */

// Global variables
let selectedContestant = null;
let selectedBundle = null;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    initializeVotingPage();
});

function initializeVotingPage() {
    // Add animation classes
    addAnimationClasses();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Setup event listeners
    setupEventListeners();
    
    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

function addAnimationClasses() {
    // Add fade-in animation to voting steps
    const votingSteps = document.querySelectorAll('.voting-step');
    votingSteps.forEach((step, index) => {
        setTimeout(() => {
            step.classList.add('fade-in');
        }, index * 200);
    });
    
    // Add slide-up animation to sidebar
    const sidebar = document.querySelector('.sidebar-content');
    if (sidebar) {
        setTimeout(() => {
            sidebar.classList.add('slide-up');
        }, 600);
    }
}

function initializeFormValidation() {
    const form = document.getElementById('votingForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }
}

function setupEventListeners() {
    // Phone number input validation
    const msisdnInput = document.getElementById('msisdn');
    if (msisdnInput) {
        msisdnInput.addEventListener('input', function() {
            validatePhoneNumber(this);
            updateVoteButton();
        });
        
        msisdnInput.addEventListener('blur', function() {
            formatPhoneNumber(this);
        });
    }
    
    // Coupon code application
    const couponInput = document.getElementById('coupon_code');
    if (couponInput) {
        couponInput.addEventListener('blur', function() {
            if (this.value) {
                validateCoupon(this.value);
            }
        });
    }
}

function selectContestant(contestantId) {
    // Remove previous selection
    document.querySelectorAll('.contestant-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Select current contestant
    const selectedCard = document.querySelector(`[data-contestant-id="${contestantId}"]`);
    if (selectedCard) {
        selectedCard.classList.add('selected');
        
        // Add selection animation
        const overlay = selectedCard.querySelector('.selection-overlay');
        if (overlay) {
            overlay.style.opacity = '1';
        }
    }
    
    // Store selection
    selectedContestant = contestantId;
    document.getElementById('contestant_id').value = contestantId;
    
    // Update summary and button
    updateVoteSummary();
    updateVoteButton();
    
    // Scroll to next step
    scrollToNextStep('packages-grid');
}

function selectBundle(bundleId, votes, price) {
    // Remove previous selection
    document.querySelectorAll('.package-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Select current bundle
    const selectedCard = document.querySelector(`[data-bundle-id="${bundleId}"]`);
    if (selectedCard) {
        selectedCard.classList.add('selected');
    }
    
    // Store selection
    selectedBundle = { id: bundleId, votes: votes, price: price };
    document.getElementById('bundle_id').value = bundleId;
    
    // Update summary and button
    updateVoteSummary();
    updateVoteButton();
    
    // Scroll to next step
    scrollToNextStep('contact-form');
}

function updateVoteSummary() {
    const summaryDiv = document.getElementById('vote-summary');
    
    if (selectedContestant && selectedBundle) {
        const contestantCard = document.querySelector(`[data-contestant-id="${selectedContestant}"]`);
        const contestantName = contestantCard.querySelector('.contestant-name').textContent;
        const contestantImage = contestantCard.querySelector('img');
        const contestantCategory = contestantCard.querySelector('.contestant-category');
        
        let imageHtml = '';
        if (contestantImage) {
            imageHtml = `<img src="${contestantImage.src}" alt="${contestantName}" class="summary-image mb-3">`;
        }
        
        let categoryHtml = '';
        if (contestantCategory) {
            categoryHtml = `<div class="summary-category mb-2">${contestantCategory.innerHTML}</div>`;
        }
        
        summaryDiv.innerHTML = `
            <div class="vote-summary-content">
                ${imageHtml}
                <div class="summary-contestant mb-3">
                    <strong>Voting for:</strong>
                    <div class="contestant-name-large">${contestantName}</div>
                    ${categoryHtml}
                </div>
                <div class="summary-package mb-3">
                    <strong>Package:</strong>
                    <div class="package-details">
                        ${selectedBundle.votes} vote${selectedBundle.votes > 1 ? 's' : ''}
                    </div>
                </div>
                <div class="summary-total mb-3">
                    <strong>Total Amount:</strong>
                    <div class="total-price">$${selectedBundle.price.toFixed(2)}</div>
                </div>
                <div class="summary-footer">
                    <small class="text-muted">Complete the form to proceed</small>
                </div>
            </div>
        `;
        
        // Add CSS for summary styling
        addSummaryStyling();
    }
}

function addSummaryStyling() {
    const style = document.createElement('style');
    style.textContent = `
        .vote-summary-content {
            text-align: center;
        }
        .summary-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #28a745;
        }
        .contestant-name-large {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3748;
            margin-top: 0.5rem;
        }
        .summary-category {
            font-size: 0.9rem;
        }
        .package-details {
            font-size: 1rem;
            color: #667eea;
            font-weight: 600;
            margin-top: 0.25rem;
        }
        .total-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #28a745;
            margin-top: 0.25rem;
        }
        .summary-footer {
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
            margin-top: 1rem;
        }
    `;
    
    if (!document.querySelector('#summary-styles')) {
        style.id = 'summary-styles';
        document.head.appendChild(style);
    }
}

function updateVoteButton() {
    const voteButton = document.getElementById('vote-button');
    const msisdn = document.getElementById('msisdn').value;
    
    if (selectedContestant && selectedBundle && msisdn.length >= 10) {
        voteButton.disabled = false;
        voteButton.classList.remove('btn-secondary');
        voteButton.classList.add('btn-success');
    } else {
        voteButton.disabled = true;
        voteButton.classList.remove('btn-success');
        voteButton.classList.add('btn-secondary');
    }
}

function validatePhoneNumber(input) {
    const phoneRegex = /^\+?[\d\s\-\(\)]{10,}$/;
    const isValid = phoneRegex.test(input.value);
    
    if (input.value.length > 0) {
        if (isValid) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
        }
    } else {
        input.classList.remove('is-valid', 'is-invalid');
    }
    
    return isValid;
}

function formatPhoneNumber(input) {
    let value = input.value.replace(/\D/g, '');
    
    if (value.length > 0 && !value.startsWith('233') && !value.startsWith('+233')) {
        if (value.startsWith('0')) {
            value = '233' + value.substring(1);
        } else if (value.length === 9) {
            value = '233' + value;
        }
        input.value = '+' + value;
    }
}

function validateCoupon(couponCode) {
    // This would typically make an AJAX call to validate the coupon
    // For now, we'll just show a placeholder message
    showToast('Coupon validation would be implemented here', 'info');
}

function scrollToNextStep(targetClass) {
    setTimeout(() => {
        const target = document.querySelector('.' + targetClass);
        if (target) {
            target.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
        }
    }, 500);
}

function handleFormSubmit(e) {
    e.preventDefault();
    
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
    
    // Show loading state
    const voteButton = document.getElementById('vote-button');
    const originalText = voteButton.innerHTML;
    voteButton.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
        Processing Vote...
    `;
    voteButton.disabled = true;
    
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
        // Restore button state
        voteButton.innerHTML = originalText;
        updateVoteButton();
    });
}

function showVoteSuccess(data) {
    const alertHtml = `
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <div class="success-icon me-3">
                    <i class="fas fa-check-circle fa-2x text-success"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="alert-heading mb-1">Vote Successful! 🎉</h5>
                    <p class="mb-2">Your vote has been recorded successfully.</p>
                    <div class="small">
                        <strong>Receipt Code:</strong> <code>${data.receipt}</code><br>
                        <strong>Transaction ID:</strong> ${data.transaction_id}<br>
                        <strong>Votes Cast:</strong> ${data.votes_cast}
                    </div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.getElementById('alert-container').innerHTML = alertHtml;
    
    // Scroll to top smoothly
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    // Show confetti animation if available
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
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <div class="error-icon me-3">
                    <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="alert-heading mb-1">Vote Failed</h5>
                    <p class="mb-0">${message}</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.getElementById('alert-container').innerHTML = alertHtml;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showAlert(message, type = 'info') {
    const alertClass = `alert-${type}`;
    const iconClass = type === 'warning' ? 'fa-exclamation-triangle' : 
                     type === 'success' ? 'fa-check-circle' : 'fa-info-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.getElementById('alert-container').innerHTML = alertHtml;
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}

function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast" role="alert">
            <div class="toast-header">
                <i class="fas fa-info-circle text-${type} me-2"></i>
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

function resetForm() {
    // Clear selections
    document.querySelectorAll('.contestant-card, .package-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Clear form
    document.getElementById('votingForm').reset();
    
    // Reset variables
    selectedContestant = null;
    selectedBundle = null;
    
    // Update UI
    document.getElementById('vote-summary').innerHTML = `
        <div class="empty-summary">
            <div class="empty-icon">
                <i class="fas fa-arrow-left"></i>
            </div>
            <p>Select a contestant and vote package to see your summary</p>
        </div>
    `;
    
    updateVoteButton();
    
    // Clear validation classes
    document.querySelectorAll('.is-valid, .is-invalid').forEach(element => {
        element.classList.remove('is-valid', 'is-invalid');
    });
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Export functions for global access
window.selectContestant = selectContestant;
window.selectBundle = selectBundle;
window.resetForm = resetForm;
