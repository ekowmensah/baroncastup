<!-- OTP Verification Component -->
<div id="otp-verification-section" class="otp-verification-wrapper" style="display: none;">
    <div class="otp-verification-card">
        <div class="otp-header">
            <i class="fas fa-shield-alt"></i>
            <h4>Verify Your Phone Number</h4>
            <p>Enter the 6-digit code sent to <span id="otp-phone-display"></span></p>
        </div>
        
        <div class="otp-input-group">
            <input type="text" 
                   id="otp-code-input" 
                   class="otp-input" 
                   maxlength="6" 
                   placeholder="000000"
                   autocomplete="off"
                   inputmode="numeric"
                   pattern="[0-9]*">
            <button type="button" id="verify-otp-btn" class="btn btn-verify-otp">
                <i class="fas fa-check-circle"></i> Verify OTP
            </button>
        </div>
        
        <div class="otp-timer-section">
            <span id="otp-timer" class="otp-timer">5:00</span>
            <button type="button" id="resend-otp-btn" class="btn-resend-otp" style="display: none;">
                <i class="fas fa-redo"></i> Resend OTP
            </button>
        </div>
        
        <div id="otp-error-message" class="otp-error" style="display: none;"></div>
        <div id="otp-success-message" class="otp-success" style="display: none;"></div>
    </div>
</div>

<style>
/* OTP Verification Styles */
.otp-verification-wrapper {
    margin: 1.5rem 0;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.otp-verification-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    color: white;
}

.otp-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.otp-header i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #ffd700;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.otp-header h4 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: white;
}

.otp-header p {
    font-size: 0.95rem;
    opacity: 0.9;
    margin: 0;
}

#otp-phone-display {
    font-weight: 700;
    color: #ffd700;
}

.otp-input-group {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.otp-input {
    flex: 1;
    padding: 1rem;
    font-size: 1.5rem;
    font-weight: 700;
    text-align: center;
    letter-spacing: 0.5rem;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 0.75rem;
    background: rgba(255, 255, 255, 0.95);
    color: #2d3748;
    transition: all 0.3s ease;
}

.otp-input:focus {
    outline: none;
    border-color: #ffd700;
    box-shadow: 0 0 0 4px rgba(255, 215, 0, 0.2);
    transform: scale(1.02);
}

.otp-input::placeholder {
    color: #cbd5e0;
    letter-spacing: 0.5rem;
}

.btn-verify-otp {
    padding: 1rem 2rem;
    font-size: 1rem;
    font-weight: 600;
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
    border: none;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
    box-shadow: 0 4px 15px rgba(72, 187, 120, 0.4);
}

.btn-verify-otp:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(72, 187, 120, 0.6);
}

.btn-verify-otp:active {
    transform: translateY(0);
}

.btn-verify-otp:disabled {
    background: #a0aec0;
    cursor: not-allowed;
    box-shadow: none;
}

.otp-timer-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 0.75rem;
    margin-bottom: 1rem;
}

.otp-timer {
    font-size: 1.25rem;
    font-weight: 700;
    color: #ffd700;
    font-family: 'Courier New', monospace;
}

.btn-resend-otp {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    font-weight: 600;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-resend-otp:hover {
    background: rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.5);
}

.otp-error {
    padding: 1rem;
    background: rgba(245, 101, 101, 0.2);
    border: 2px solid rgba(245, 101, 101, 0.5);
    border-radius: 0.75rem;
    color: #fff;
    font-weight: 600;
    text-align: center;
    animation: shake 0.5s;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
}

.otp-success {
    padding: 1rem;
    background: rgba(72, 187, 120, 0.2);
    border: 2px solid rgba(72, 187, 120, 0.5);
    border-radius: 0.75rem;
    color: #fff;
    font-weight: 600;
    text-align: center;
    animation: slideDown 0.3s ease-out;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .otp-verification-card {
        padding: 1.5rem;
    }
    
    .otp-input-group {
        flex-direction: column;
    }
    
    .otp-input {
        font-size: 1.25rem;
        letter-spacing: 0.3rem;
    }
    
    .btn-verify-otp {
        width: 100%;
    }
    
    .otp-timer-section {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .btn-resend-otp {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .otp-header h4 {
        font-size: 1.25rem;
    }
    
    .otp-header p {
        font-size: 0.85rem;
    }
    
    .otp-input {
        font-size: 1.1rem;
        letter-spacing: 0.2rem;
        padding: 0.75rem;
    }
}
</style>

<script>
// OTP Verification JavaScript
(function() {
    let otpTimer = null;
    let otpTimeRemaining = 300; // 5 minutes in seconds
    let currentPhone = '';
    let sessionToken = '';
    
    // Initialize OTP verification
    window.initOtpVerification = function(phone) {
        currentPhone = phone;
        document.getElementById('otp-phone-display').textContent = maskPhoneNumber(phone);
        document.getElementById('otp-verification-section').style.display = 'block';
        document.getElementById('otp-code-input').value = '';
        document.getElementById('otp-error-message').style.display = 'none';
        document.getElementById('otp-success-message').style.display = 'none';
        startOtpTimer();
    };
    
    // Start OTP countdown timer
    function startOtpTimer() {
        otpTimeRemaining = 300;
        document.getElementById('resend-otp-btn').style.display = 'none';
        
        if (otpTimer) clearInterval(otpTimer);
        
        otpTimer = setInterval(function() {
            otpTimeRemaining--;
            
            const minutes = Math.floor(otpTimeRemaining / 60);
            const seconds = otpTimeRemaining % 60;
            document.getElementById('otp-timer').textContent = 
                `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (otpTimeRemaining <= 0) {
                clearInterval(otpTimer);
                document.getElementById('otp-timer').textContent = 'Expired';
                document.getElementById('resend-otp-btn').style.display = 'block';
            }
        }, 1000);
    }
    
    // Verify OTP button click
    document.getElementById('verify-otp-btn').addEventListener('click', async function() {
        const otpCode = document.getElementById('otp-code-input').value.trim();
        
        if (otpCode.length !== 6) {
            showOtpError('Please enter a 6-digit OTP code');
            return;
        }
        
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
        
        try {
            const response = await fetch('/api/otp/verify-payment-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    phone: currentPhone,
                    otp: otpCode
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                sessionToken = result.session_token;
                showOtpSuccess('Phone number verified successfully!');
                
                // Clear timer
                if (otpTimer) clearInterval(otpTimer);
                
                // Lock phone number field
                const phoneInput = document.getElementById('phone-number') || document.querySelector('input[name="msisdn"]');
                if (phoneInput) {
                    phoneInput.readOnly = true;
                    phoneInput.style.background = '#e2e8f0';
                    phoneInput.style.cursor = 'not-allowed';
                }
                
                // Enable payment button
                const paymentBtn = document.getElementById('proceed-payment-btn') || document.querySelector('.vote-button');
                if (paymentBtn) {
                    paymentBtn.disabled = false;
                    paymentBtn.classList.remove('disabled');
                }
                
                // Hide OTP section after 2 seconds
                setTimeout(function() {
                    document.getElementById('otp-verification-section').style.display = 'none';
                }, 2000);
                
                // Trigger callback if exists
                if (typeof window.onOtpVerified === 'function') {
                    window.onOtpVerified(sessionToken, currentPhone);
                }
            } else {
                showOtpError(result.message || 'Invalid OTP. Please try again.');
            }
        } catch (error) {
            console.error('OTP verification error:', error);
            showOtpError('Failed to verify OTP. Please try again.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Verify OTP';
        }
    });
    
    // Resend OTP button click
    document.getElementById('resend-otp-btn').addEventListener('click', async function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        
        try {
            const response = await fetch('/api/otp/send-payment-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    phone: currentPhone
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                if (result.skip_otp) {
                    showOtpSuccess('Registered user - OTP not required');
                    // Enable payment directly
                    const paymentBtn = document.getElementById('proceed-payment-btn') || document.querySelector('.vote-button');
                    if (paymentBtn) {
                        paymentBtn.disabled = false;
                    }
                } else {
                    showOtpSuccess('OTP sent successfully!');
                    startOtpTimer();
                }
            } else {
                showOtpError(result.message || 'Failed to send OTP');
            }
        } catch (error) {
            console.error('Resend OTP error:', error);
            showOtpError('Failed to send OTP. Please try again.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-redo"></i> Resend OTP';
        }
    });
    
    // OTP input - auto-submit on 6 digits
    document.getElementById('otp-code-input').addEventListener('input', function(e) {
        // Only allow numbers
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Auto-verify when 6 digits entered
        if (this.value.length === 6) {
            document.getElementById('verify-otp-btn').click();
        }
    });
    
    // Helper functions
    function showOtpError(message) {
        const errorEl = document.getElementById('otp-error-message');
        const successEl = document.getElementById('otp-success-message');
        errorEl.textContent = message;
        errorEl.style.display = 'block';
        successEl.style.display = 'none';
        
        setTimeout(function() {
            errorEl.style.display = 'none';
        }, 5000);
    }
    
    function showOtpSuccess(message) {
        const errorEl = document.getElementById('otp-error-message');
        const successEl = document.getElementById('otp-success-message');
        successEl.textContent = message;
        successEl.style.display = 'block';
        errorEl.style.display = 'none';
    }
    
    function maskPhoneNumber(phone) {
        if (phone.length > 6) {
            return phone.substring(0, 3) + '****' + phone.substring(phone.length - 3);
        }
        return phone;
    }
    
    // Export session token getter
    window.getOtpSessionToken = function() {
        return sessionToken;
    };
    
    window.getCurrentOtpPhone = function() {
        return currentPhone;
    };
})();
</script>
