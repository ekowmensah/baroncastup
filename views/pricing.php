<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing - <?= APP_NAME ?></title>
    
    <!-- CoreUI CSS -->
    <link href="<?= COREUI_CSS ?>" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= APP_URL ?>/public/css/auth.css" rel="stylesheet">
</head>
<body class="c-app" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <!-- Header -->
    <header class="navbar navbar-expand-lg navbar-dark bg-transparent">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= APP_URL ?>">
                <i class="fas fa-vote-yea me-2"></i><?= APP_NAME ?>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?= APP_URL ?>/login">Login</a>
                <a class="btn btn-outline-light btn-sm" href="<?= APP_URL ?>/register">Get Started</a>
            </div>
        </div>
    </header>

    <!-- Pricing Section -->
    <div class="container py-4">
        <div class="text-center text-white mb-4">
            <h1 class="display-5 fw-bold mb-2">Choose Your Plan</h1>
            <p class="lead mb-0">Start your voting platform journey with the perfect plan for your needs</p>
        </div>

        <div class="row justify-content-center">
            <?php foreach ($plans ?? [] as $plan): ?>
            <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                <div class="card h-100 shadow-sm <?= $plan['is_popular'] ? 'border-warning' : '' ?>">
                    <?php if ($plan['is_popular']): ?>
                        <div class="card-header bg-warning text-dark text-center py-1">
                            <small><i class="fas fa-star me-1"></i><strong>Most Popular</strong></small>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body text-center d-flex flex-column p-3">
                        <h5 class="card-title mb-2"><?= htmlspecialchars($plan['name']) ?></h5>
                        <div class="h4 fw-bold text-primary mb-2">
                            <?= $plan['price_display'] ?>
                        </div>
                        
                        <p class="text-muted small mb-3"><?= htmlspecialchars($plan['description']) ?></p>
                        
                        <ul class="list-unstyled text-start mb-3 flex-grow-1 small">
                            <li class="mb-1">
                                <i class="fas fa-calendar-alt text-info me-2"></i>
                                <strong>Events:</strong> <?= $plan['events_display'] ?>
                            </li>
                            <li class="mb-1">
                                <i class="fas fa-users text-success me-2"></i>
                                <strong>Contestants:</strong> <?= $plan['contestants_display'] ?> per event
                            </li>
                            <li class="mb-1">
                                <i class="fas fa-vote-yea text-warning me-2"></i>
                                <strong>Votes:</strong> <?= $plan['votes_display'] ?> per event
                            </li>
                            
                            <!-- Plan Features (excluding storage) -->
                            <?php if (!empty($plan['plan_features'])): ?>
                                <?php 
                                $filteredFeatures = array_filter($plan['plan_features'], function($feature) {
                                    return strtolower($feature['feature_key']) !== 'storage';
                                });
                                ?>
                                <?php foreach (array_slice($filteredFeatures, 0, 3) as $feature): ?>
                                <li class="mb-1">
                                    <?php if ($feature['is_boolean']): ?>
                                        <i class="fas fa-check text-success me-2"></i>
                                        <?= htmlspecialchars($feature['feature_name']) ?>
                                    <?php else: ?>
                                        <i class="fas fa-info-circle text-info me-2"></i>
                                        <strong><?= htmlspecialchars($feature['feature_name']) ?>:</strong> 
                                        <?= htmlspecialchars($feature['feature_value']) ?>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                        
                        <div class="mt-auto">
                            <div class="border-top pt-3 mb-3">
                                <!-- Highlighted Platform Fee Information -->
                                <div class="alert alert-info py-2 mb-2">
                                    <div class="text-center">
                                        <i class="fas fa-percentage me-2"></i>
                                        <strong>Platform Fee: <?= $plan['fee_display'] ?></strong>
                                    </div>
                                </div>
                                
                                <?php if ($plan['trial_days'] > 0): ?>
                                    <div class="text-center">
                                        <small class="text-success">
                                            <i class="fas fa-gift me-1"></i><?= $plan['trial_days'] ?> days free trial
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <a href="<?= APP_URL ?>/register?plan=<?= $plan['id'] ?>" class="btn btn-<?= $plan['is_popular'] ? 'warning' : 'primary' ?> btn-sm w-100">
                                Get Started
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center text-white mt-4">
            <p class="mb-2 small">
                <i class="fas fa-shield-alt me-2"></i>
                All plans include SSL security, 24/7 support, and regular backups
            </p>
            <p class="small mb-0">
                Need a custom solution? 
                <a href="mailto:sales@<?= strtolower(APP_NAME) ?>.com" class="text-warning text-decoration-none">
                    Contact our sales team
                </a>
            </p>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center text-white py-3">
        <div class="container">
            <p class="mb-0 small">&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</p>
        </div>
    </footer>

    <!-- CoreUI JavaScript -->
    <script src="<?= COREUI_JS ?>"></script>

    <!--Google Adsense-->

<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7353409583217467"
     crossorigin="anonymous"></script>

</body>
</html>
