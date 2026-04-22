<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?= APP_NAME ?></title>

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
            <!-- Left Panel - Hidden on Large screens -->
            <div class="col-lg-7 d-none d-lg-flex d-xl-none auth-left-panel">
                <div class="auth-content">
                    <div class="auth-brand mb-5">
                        <img src="<?= APP_URL ?>/logo1.png" alt="BaronCast Logo" class="auth-logo-img">
                        <h1 class="auth-brand-text"><?= APP_NAME ?></h1>
                        <p class="auth-tagline">Ghana's Leading Digital Voting Platform</p>
                    </div>

                    <div class="auth-features">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Easy Registration</h4>
                                <p>Get started in minutes with our simple registration process</p>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-tags"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Flexible Plans</h4>
                                <p>Choose from multiple plans designed for organizations of all sizes</p>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Quick Setup</h4>
                                <p>Create your first voting event within minutes of registration</p>
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

            <!-- Registration Form Panel -->
            <div class="col-lg-5 col-xl-12 col-12 d-flex align-items-center justify-content-center min-vh-100">
                <div class="auth-form-container auth-form-container-wide">
                    <!-- Mobile Logo -->
                    <div class="d-lg-none text-center mb-4">
                        <img src="<?= APP_URL ?>/logo1.png" alt="BaronCast Logo" class="mobile-logo">
                        <h2 class="mobile-brand-text mt-2"><?= APP_NAME ?></h2>
                    </div>

                    <!-- Desktop Logo for Large Screens -->
                    <div class="d-none d-xl-block text-center mb-4">
                        <img src="<?= APP_URL ?>/logo1.png" alt="BaronCast Logo" class="desktop-header-logo">
                        <h2 class="desktop-header-title mt-3">BaronCast</h2>
                    </div>

                    <div class="auth-form-card">
                        <div class="auth-form-header">
                            <h3 class="auth-form-title">Create Account</h3>
                            <p class="auth-form-subtitle">Join BaronCast and start managing your voting events</p>
                        </div>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-modern">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?= APP_URL ?>/register" class="auth-form needs-validation" novalidate>
                            <div class="form-group mb-4">
                                <label for="organization" class="form-label">Organization Name</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-building input-icon"></i>
                                    <input type="text"
                                           id="organization"
                                           class="form-control form-control-modern"
                                           name="organization"
                                           placeholder="Enter your organization name"
                                           value="<?= htmlspecialchars($data['organization'] ?? '') ?>"
                                           required>
                                    <div class="invalid-feedback">
                                        Please provide an organization name.
                                    </div>
                                </div>
                                <?php if (isset($errors['organization'])): ?>
                                    <div class="text-danger small mt-1">
                                        <?= htmlspecialchars($errors['organization']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

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
                                <?php if (isset($errors['email'])): ?>
                                    <div class="text-danger small mt-1">
                                        <?= htmlspecialchars($errors['email']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group mb-4">
                                <label for="phone" class="form-label">Phone Number</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-phone input-icon"></i>
                                    <input type="tel"
                                           id="phone"
                                           class="form-control form-control-modern"
                                           name="phone"
                                           placeholder="Enter your phone number (e.g., 0545644749)"
                                           value="<?= htmlspecialchars($data['phone'] ?? '') ?>"
                                           required>
                                    <div class="invalid-feedback">
                                        Please provide a valid phone number.
                                    </div>
                                </div>
                                <small class="text-muted">format (e.g., 0545644749 or 233545644749)</small>
                                <?php if (isset($errors['phone'])): ?>
                                    <div class="text-danger small mt-1">
                                        <?= htmlspecialchars($errors['phone']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password"
                                           id="password"
                                           class="form-control form-control-modern"
                                           name="password"
                                           placeholder="Create a password (min 8 characters)"
                                           required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('password')" aria-label="Toggle password visibility">
                                        <i class="fas fa-eye" id="password-eye"></i>
                                    </button>
                                    <div class="invalid-feedback">
                                        Please provide a password.
                                    </div>
                                </div>
                                <small class="text-muted">Minimum 8 characters</small>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="text-danger small mt-1">
                                        <?= htmlspecialchars($errors['password']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group mb-4">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password"
                                           id="confirm_password"
                                           class="form-control form-control-modern"
                                           name="confirm_password"
                                           placeholder="Confirm your password"
                                           required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')" aria-label="Toggle confirm password visibility">
                                        <i class="fas fa-eye" id="confirm_password-eye"></i>
                                    </button>
                                    <div class="invalid-feedback">
                                        Please confirm your password.
                                    </div>
                                </div>
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <div class="text-danger small mt-1">
                                        <?= htmlspecialchars($errors['confirm_password']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Subscription Plan Selection -->
                            <div class="plan-selection-section">
                                <label class="form-label">Choose Your Plan</label>
                                <?php if (isset($errors['plan_id'])): ?>
                                    <div class="text-danger small mb-3">
                                        <?= htmlspecialchars($errors['plan_id']) ?>
                                    </div>
                                <?php endif; ?>

                                <div class="plans-grid">
                                    <?php foreach ($plans ?? [] as $index => $plan): ?>
                                    <div class="plan-card-modern <?= $plan['is_popular'] ? 'plan-popular' : '' ?>"
                                         onclick="selectPlan(<?= $plan['id'] ?>)">
                                        <?php if ($plan['is_popular']): ?>
                                            <div class="plan-badge">
                                                <i class="fas fa-star"></i>
                                                Most Popular
                                            </div>
                                        <?php endif; ?>

                                        <div class="plan-header">
                                            <h5 class="plan-name"><?= htmlspecialchars($plan['name']) ?></h5>
                                            <div class="plan-price">
                                                <?= $plan['price_display'] ?>
                                                <?php if ($plan['trial_days'] > 0): ?>
                                                    <small class="plan-trial">
                                                        <i class="fas fa-gift"></i>
                                                        <?= $plan['trial_days'] ?> days free
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="plan-features">
                                            <div class="plan-feature">
                                                <i class="fas fa-calendar-alt"></i>
                                                <span><strong>Events:</strong> <?= $plan['events_display'] ?></span>
                                            </div>
                                            <div class="plan-feature">
                                                <i class="fas fa-users"></i>
                                                <span><strong>Contestants:</strong> <?= $plan['contestants_display'] ?></span>
                                            </div>
                                        </div>

                                        <div class="plan-radio">
                                            <input class="form-check-input" type="radio" name="plan_id"
                                                   id="plan_<?= $plan['id'] ?>" value="<?= $plan['id'] ?>"
                                                   <?php
                                                   $isSelected = false;
                                                   if (isset($selectedPlanId) && $selectedPlanId == $plan['id']) {
                                                       $isSelected = true;
                                                   } elseif (isset($data['plan_id']) && $data['plan_id'] == $plan['id']) {
                                                       $isSelected = true;
                                                   }
                                                   echo $isSelected ? 'checked' : '';
                                                   ?> required>
                                            <label class="form-check-label fw-bold" for="plan_<?= $plan['id'] ?>">
                                                Select This Plan
                                            </label>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <div class="form-check d-flex align-items-start">
                                    <input type="checkbox" class="form-check-input me-3 mt-1" id="terms" name="terms" required style="transform: scale(1.1);">
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" class="text-decoration-none">Terms of Service</a>
                                        and <a href="#" class="text-decoration-none">Privacy Policy</a> *
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-modern w-100 mb-4">
                                <i class="fas fa-user-plus me-2"></i>
                                Create Account
                            </button>
                        </form>

                        <div class="auth-form-footer">
                            <div class="divider">
                                <span>Already have an account?</span>
                            </div>

                            <div class="text-center mt-4">
                                <a href="<?= APP_URL ?>/login" class="btn btn-outline-primary btn-modern">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Sign In Instead
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
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const button = field.parentElement.querySelector('.password-toggle');
        const icon = button.querySelector('i');

        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Plan Selection Function
    function selectPlan(planId) {
        // Remove selected class from all plan cards
        document.querySelectorAll('.plan-card-modern').forEach(card => {
            card.classList.remove('selected');
        });

        // Select the radio button
        const radio = document.getElementById('plan_' + planId);
        if (radio) {
            radio.checked = true;

            // Add selected styling to the card
            const card = radio.closest('.plan-card-modern');
            if (card) {
                card.classList.add('selected');
            }
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

        // Add click handlers to plan cards
        document.querySelectorAll('.plan-card-modern').forEach(card => {
            card.style.cursor = 'pointer';

            // Check if this plan is already selected
            const radio = card.querySelector('input[type="radio"]');
            if (radio && radio.checked) {
                card.classList.add('selected');
            }
        });

        // Add change handlers to radio buttons
        document.querySelectorAll('input[name="plan_id"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    selectPlan(this.value);
                }
            });
        });

        // Auto-trigger visual selection for pre-selected plan
        const selectedRadio = document.querySelector('input[name="plan_id"]:checked');
        if (selectedRadio) {
            selectPlan(selectedRadio.value);
        }

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
