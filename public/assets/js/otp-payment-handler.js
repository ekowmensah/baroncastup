/**
 * OTP Payment Handler
 * Manages OTP verification flow for payment security
 */

class OtpPaymentHandler {
    constructor(options = {}) {
        this.phoneInput = options.phoneInput || document.querySelector('input[name="msisdn"]');
        this.sendOtpBtn = options.sendOtpBtn || document.getElementById('send-otp-btn');
        this.paymentBtn = options.paymentBtn || document.querySelector('.vote-button');
        this.apiBaseUrl = options.apiBaseUrl || '';
        
        this.otpVerified = false;
        this.sessionToken = null;
        this.verifiedPhone = null;
        
        this.init();
    }
    
    init() {
        if (this.sendOtpBtn) {
            this.sendOtpBtn.addEventListener('click', () => this.sendOtp());
        }
        
        if (this.phoneInput) {
            this.phoneInput.addEventListener('input', () => this.onPhoneChange());
        }
        
        // Set up OTP verification callback
        window.onOtpVerified = (sessionToken, phone) => {
            this.otpVerified = true;
            this.sessionToken = sessionToken;
            this.verifiedPhone = phone;
            this.onOtpVerificationSuccess();
        };
    }
    
    async sendOtp() {
        const phone = this.phoneInput.value.trim();
        
        if (!phone) {
            this.showError('Please enter your phone number');
            return;
        }
        
        if (!this.validatePhoneNumber(phone)) {
            this.showError('Please enter a valid phone number');
            return;
        }
        
        // Disable button and show loading
        this.sendOtpBtn.disabled = true;
        this.sendOtpBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending OTP...';
        
        try {
            const response = await fetch(`${this.apiBaseUrl}/api/otp/send-payment-otp`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ phone })
            });
            
            const result = await response.json();
            
            if (result.success) {
                if (result.skip_otp) {
                    // Registered user - skip OTP
                    this.showSuccess('Registered user - proceeding to payment');
                    this.otpVerified = true;
                    this.verifiedPhone = phone;
                    this.enablePaymentButton();
                    this.sendOtpBtn.style.display = 'none';
                } else {
                    // Show OTP verification UI
                    this.showSuccess('OTP sent to ' + this.maskPhone(phone));
                    window.initOtpVerification(phone);
                    this.sendOtpBtn.style.display = 'none';
                }
            } else {
                this.showError(result.message || 'Failed to send OTP');
                this.sendOtpBtn.disabled = false;
                this.sendOtpBtn.innerHTML = '<i class="fas fa-shield-alt"></i> Send OTP';
            }
        } catch (error) {
            console.error('Send OTP error:', error);
            this.showError('Failed to send OTP. Please try again.');
            this.sendOtpBtn.disabled = false;
            this.sendOtpBtn.innerHTML = '<i class="fas fa-shield-alt"></i> Send OTP';
        }
    }
    
    onPhoneChange() {
        // Reset verification if phone changes
        if (this.otpVerified && this.phoneInput.value !== this.verifiedPhone) {
            this.resetVerification();
        }
    }
    
    onOtpVerificationSuccess() {
        this.enablePaymentButton();
        this.showSuccess('Phone verified! You can now proceed to payment.');
    }
    
    enablePaymentButton() {
        if (this.paymentBtn) {
            this.paymentBtn.disabled = false;
            this.paymentBtn.classList.remove('disabled');
        }
    }
    
    resetVerification() {
        this.otpVerified = false;
        this.sessionToken = null;
        this.verifiedPhone = null;
        
        if (this.phoneInput) {
            this.phoneInput.readOnly = false;
            this.phoneInput.style.background = '';
            this.phoneInput.style.cursor = '';
        }
        
        if (this.sendOtpBtn) {
            this.sendOtpBtn.style.display = 'inline-block';
            this.sendOtpBtn.disabled = false;
        }
        
        if (this.paymentBtn) {
            this.paymentBtn.disabled = true;
            this.paymentBtn.classList.add('disabled');
        }
        
        const otpSection = document.getElementById('otp-verification-section');
        if (otpSection) {
            otpSection.style.display = 'none';
        }
    }
    
    validatePhoneNumber(phone) {
        // Remove all non-numeric characters
        const cleaned = phone.replace(/\D/g, '');
        
        // Check if it's a valid Ghana phone number
        // Should be 10 digits starting with 0, or 12 digits starting with 233
        if (cleaned.length === 10 && cleaned.startsWith('0')) {
            return true;
        }
        if (cleaned.length === 12 && cleaned.startsWith('233')) {
            return true;
        }
        if (cleaned.length === 9) {
            return true;
        }
        
        return false;
    }
    
    maskPhone(phone) {
        if (phone.length > 6) {
            return phone.substring(0, 3) + '****' + phone.substring(phone.length - 3);
        }
        return phone;
    }
    
    showError(message) {
        // Try to find existing alert container
        let alertContainer = document.querySelector('.alert-container');
        if (!alertContainer) {
            alertContainer = document.createElement('div');
            alertContainer.className = 'alert-container';
            document.body.appendChild(alertContainer);
        }
        
        const alert = document.createElement('div');
        alert.className = 'alert alert-error';
        alert.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            <span>${message}</span>
        `;
        
        alertContainer.appendChild(alert);
        
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
    
    showSuccess(message) {
        let alertContainer = document.querySelector('.alert-container');
        if (!alertContainer) {
            alertContainer = document.createElement('div');
            alertContainer.className = 'alert-container';
            document.body.appendChild(alertContainer);
        }
        
        const alert = document.createElement('div');
        alert.className = 'alert alert-success';
        alert.innerHTML = `
            <i class="fas fa-check-circle"></i>
            <span>${message}</span>
        `;
        
        alertContainer.appendChild(alert);
        
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
    
    getSessionToken() {
        return this.sessionToken || window.getOtpSessionToken();
    }
    
    isVerified() {
        return this.otpVerified;
    }
    
    getVerifiedPhone() {
        return this.verifiedPhone || window.getCurrentOtpPhone();
    }
}

// Add alert styles
const style = document.createElement('style');
style.textContent = `
.alert-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    max-width: 400px;
}

.alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    margin-bottom: 12px;
    border-radius: 12px;
    font-weight: 600;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    animation: slideInRight 0.3s ease-out;
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.alert i {
    font-size: 1.5rem;
}

.alert-error {
    background: linear-gradient(135deg, #f56565 0%, #c53030 100%);
    color: white;
}

.alert-success {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
}

@media (max-width: 768px) {
    .alert-container {
        top: 10px;
        right: 10px;
        left: 10px;
        max-width: none;
    }
}
`;
document.head.appendChild(style);

// Export for global use
window.OtpPaymentHandler = OtpPaymentHandler;
