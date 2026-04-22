<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= APP_URL ?>/public/css/auth.css" rel="stylesheet">
</head>
<body class="auth-body">
    <!-- Background Elements -->
    <div class="auth-background">
        <div class="auth-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container-fluid h-100">
        <div class="row h-100 g-0">
            <!-- Left Panel - Hidden on Mobile -->
            <div class="col-lg-7 d-none d-lg-flex auth-left-panel">
                <div class="auth-content">
                    <div class="auth-brand mb-5">
                        <img src="<?= APP_URL ?>/logo1.png" alt="BaronCast Logo" class="auth-logo-img">
                        <h1 class="auth-brand-text"><?= APP_NAME ?></h1>
                        <p class="auth-tagline">Ghana's Leading Digital Voting Platform</p>
                    </div>
                    
                    <div class="auth-features">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Mobile Money Integration</h4>
                                <p>Seamless voting with MTN, Vodafone & AirtelTigo</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Real-time Results</h4>
                                <p>Live analytics and instant vote counting</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Secure & Transparent</h4>
                                <p>Fraud prevention with SMS receipts</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="auth-stats mt-5">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="stat-number">10K+</div>
                                <div class="stat-label">Events</div>
                            </div>
                            <div class="col-4">
                                <div class="stat-number">500K+</div>
                                <div class="stat-label">Votes Cast</div>
                            </div>
                            <div class="col-4">
                                <div class="stat-number">99.9%</div>
                                <div class="stat-label">Uptime</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Panel - Login Form -->
            <div class="col-lg-5 col-12 d-flex align-items-center justify-content-center">
                <div class="auth-form-container">
                    <!-- Mobile Logo -->
                    <div class="d-lg-none text-center mb-4">
                        <img src="<?= APP_URL ?>/logo1.png" alt="BaronCast Logo" class="mobile-logo">
                        <h2 class="mobile-brand-text mt-2"><?= APP_NAME ?></h2>
                    </div>
                    
                    <div class="auth-form-card">
                        <div class="auth-form-header">
                            <h3 class="auth-form-title">Welcome Back</h3>
                            <p class="auth-form-subtitle">Sign in to your account to continue</p>
                        </div>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-modern">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($flashMessages['success']) && $flashMessages['success']): ?>
                            <div class="alert alert-success alert-modern">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= htmlspecialchars($flashMessages['success']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($flashMessages['info']) && $flashMessages['info']): ?>
                            <div class="alert alert-info alert-modern">
                                <i class="fas fa-info-circle me-2"></i>
                                <?= htmlspecialchars($flashMessages['info']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($flashMessages['warning']) && $flashMessages['warning']): ?>
                            <div class="alert alert-warning alert-modern">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($flashMessages['warning']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($flashMessages['error']) && $flashMessages['error']): ?>
                            <div class="alert alert-danger alert-modern">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($flashMessages['error']) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?= APP_URL ?>/login" class="auth-form needs-validation" novalidate>
                            <div class="form-group mb-4">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-envelope input-icon"></i>
                                    <input type="email" 
                                           id="email"
                                           class="form-control form-control-modern" 
                                           name="email" 
                                           placeholder="Enter your email address"
                                           value="<?= htmlspecialchars($data['email'] ?? '') ?>" 
                                           required>
                                    <div class="invalid-feedback">
                                        Please provide a valid email address.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" 
                                           id="password"
                                           class="form-control form-control-modern" 
                                           name="password" 
                                           placeholder="Enter your password" 
                                           required>
                                    <button type="button" class="password-toggle" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="password-eye"></i>
                                    </button>
                                    <div class="invalid-feedback">
                                        Please provide a password.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">
                                        Remember me for 30 days
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-modern w-100 mb-4">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Sign In to BaronCast
                            </button>
                            
                            <div class="text-center">
                                <a href="#" class="forgot-password-link" onclick="alert('Please contact support for password reset.')">
                                    <i class="fas fa-key me-1"></i>
                                    Forgot your password?
                                </a>
                            </div>
                        </form>
                        
                        <div class="auth-form-footer">
                            <div class="divider">
                                <span>New to BaronCast?</span>
                            </div>
                            
                            <div class="text-center mt-4">
                                <a href="<?= APP_URL ?>/register" class="btn btn-outline-primary btn-modern">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Create Account
                                </a>
                            </div>
                            
                            <div class="auth-links mt-4">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <a href="<?= APP_URL ?>/" class="auth-link">
                                            <i class="fas fa-home me-1"></i>
                                            Homepage
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="<?= APP_URL ?>/pricing" class="auth-link">
                                            <i class="fas fa-tags me-1"></i>
                                            Pricing
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
    // Form Validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
    
    // Password Toggle Function
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const passwordEye = document.getElementById('password-eye');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordEye.classList.remove('fa-eye');
            passwordEye.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            passwordEye.classList.remove('fa-eye-slash');
            passwordEye.classList.add('fa-eye');
        }
    }
    
    // Enhanced Form Interactions
    document.addEventListener('DOMContentLoaded', function() {
        // Add focus effects to inputs
        const inputs = document.querySelectorAll('.form-control-modern');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('input-focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('input-focused');
                if (this.value) {
                    this.parentElement.classList.add('input-filled');
                } else {
                    this.parentElement.classList.remove('input-filled');
                }
            });
            
            // Check if input has value on load
            if (input.value) {
                input.parentElement.classList.add('input-filled');
            }
        });
        
        // Animate shapes
        const shapes = document.querySelectorAll('.shape');
        shapes.forEach((shape, index) => {
            shape.style.animationDelay = `${index * 0.5}s`;
        });
    });
    </script>

  <!--Google Adsense-->

<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7353409583217467"
     crossorigin="anonymous"></script>

</body>
</html>
