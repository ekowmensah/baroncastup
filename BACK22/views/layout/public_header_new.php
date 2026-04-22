<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="app-url" content="<?= APP_URL ?>">
    <title><?= $title ?? 'BaronCast' ?> - <?= APP_NAME ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/public/assets/images/favicon.svg">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= APP_URL ?>/public/assets/images/icon-192.png">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#667eea">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    
    <!-- Enhanced SEO Meta Tags -->
    <meta name="description" content="<?= $seo_description ?? 'BaronCast - Ghana\'s leading digital voting platform. Secure online voting for events, competitions, talent shows, and elections with MTN, Vodafone, AirtelTigo mobile money integration.' ?>">
    <meta name="keywords" content="<?= $seo_keywords ?? 'digital voting Ghana, online voting platform, mobile money voting, MTN mobile money, Vodafone Cash, AirtelTigo Money, talent show voting, competition voting, secure voting system' ?>">
    <meta name="author" content="BaronCast">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?= $og_title ?? ($title ?? 'BaronCast') . ' - Ghana\'s #1 Digital Voting Platform' ?>">
    <meta property="og:description" content="<?= $og_description ?? 'Vote securely with mobile money! MTN, Vodafone, AirtelTigo supported. Real-time results, SMS receipts, fraud prevention.' ?>">
    <meta property="og:image" content="<?= $og_image ?? APP_URL . '/logo1.png' ?>">
    <meta property="og:url" content="<?= $canonical_url ?? APP_URL . $_SERVER['REQUEST_URI'] ?>">
    <meta property="og:type" content="website">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $twitter_title ?? ($title ?? 'BaronCast') . ' - Ghana Digital Voting Platform' ?>">
    <meta name="twitter:description" content="<?= $twitter_description ?? 'Secure mobile money voting in Ghana 🇬🇭 MTN • Vodafone • AirtelTigo supported. Real-time results, SMS receipts.' ?>">
    <meta name="twitter:image" content="<?= $twitter_image ?? APP_URL . '/logo1.png' ?>">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Stunning Header Styles -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --accent-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-primary: #2d3748;
            --text-secondary: #718096;
            --shadow-soft: 0 10px 40px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 20px 60px rgba(0, 0, 0, 0.15);
            --shadow-strong: 0 30px 80px rgba(0, 0, 0, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--text-primary);
            overflow-x: hidden;
        }

        /* Stunning Header Styles */
        .stunning-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-soft);
        }

        .stunning-header.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: var(--shadow-medium);
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 80px;
        }

        /* Logo Section */
        .brand-section {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .brand-section:hover {
            transform: translateY(-2px);
        }

        .brand-logo {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            object-fit: cover;
            box-shadow: var(--shadow-soft);
            transition: all 0.3s ease;
        }

        .brand-logo:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-medium);
        }

        .brand-text {
            display: flex;
            flex-direction: column;
        }

        .brand-name {
            font-family: 'Poppins', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
        }

        .brand-tagline {
            font-size: 0.75rem;
            color: var(--text-secondary);
            font-weight: 500;
            margin: 0;
        }

        /* Navigation Menu */
        .nav-menu {
            display: flex;
            align-items: center;
            gap: 2rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.95rem;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--primary-gradient);
            transition: left 0.3s ease;
            z-index: -1;
        }

        .nav-link:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-soft);
        }

        .nav-link:hover::before {
            left: 0;
        }

        .nav-link.active {
            background: var(--primary-gradient);
            color: white;
            box-shadow: var(--shadow-soft);
        }

        .nav-icon {
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover .nav-icon {
            transform: scale(1.1);
        }

        /* User Menu */
        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-soft);
        }

        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-medium);
        }

        .auth-buttons {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-login {
            padding: 0.75rem 1.5rem;
            background: transparent;
            border: 2px solid transparent;
            color: var(--text-primary);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 2px solid;
            border-image: var(--primary-gradient) 1;
            border-radius: 12px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .btn-login:hover {
            color: white;
            transform: translateY(-2px);
        }

        .btn-login:hover::before {
            opacity: 1;
        }

        .btn-register {
            padding: 0.75rem 2rem;
            background: var(--primary-gradient);
            border: none;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-soft);
            position: relative;
            overflow: hidden;
        }

        .btn-register::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--secondary-gradient);
            transition: left 0.3s ease;
        }

        .btn-register:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .btn-register:hover::before {
            left: 0;
        }

        .btn-register span {
            position: relative;
            z-index: 1;
        }

        /* Mobile Menu Toggle */
        .mobile-toggle {
            display: none;
            flex-direction: column;
            gap: 4px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
        }

        .mobile-toggle span {
            width: 25px;
            height: 3px;
            background: var(--primary-gradient);
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .mobile-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(6px, 6px);
        }

        .mobile-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .mobile-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(6px, -6px);
        }

        /* Mobile Menu */
        .mobile-menu {
            position: fixed;
            top: 80px;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            transform: translateY(-100%);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-medium);
            z-index: 999;
        }

        .mobile-menu.active {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }

        .mobile-nav {
            padding: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .mobile-nav .nav-link {
            padding: 1rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-container {
                padding: 0 1rem;
                height: 70px;
            }

            .nav-menu {
                display: none;
            }

            .mobile-toggle {
                display: flex;
            }

            .brand-name {
                font-size: 1.25rem;
            }

            .brand-tagline {
                display: none;
            }

            .auth-buttons {
                gap: 0.5rem;
            }

            .btn-login,
            .btn-register {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }
        }

        @media (max-width: 480px) {
            .header-container {
                height: 60px;
            }

            .brand-logo {
                width: 40px;
                height: 40px;
            }

            .brand-name {
                font-size: 1.1rem;
            }
        }

        /* Page Content Spacing */
        .page-content {
            margin-top: 80px;
        }

        @media (max-width: 768px) {
            .page-content {
                margin-top: 70px;
            }
        }

        @media (max-width: 480px) {
            .page-content {
                margin-top: 60px;
            }
        }

        /* Flash Messages */
        .flash-messages {
            position: fixed;
            top: 90px;
            right: 2rem;
            z-index: 1001;
            max-width: 400px;
        }

        .flash-alert {
            margin-bottom: 1rem;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--shadow-medium);
            animation: slideInRight 0.3s ease;
        }

        .flash-alert.success {
            background: rgba(34, 197, 94, 0.1);
            border-color: rgba(34, 197, 94, 0.3);
            color: #15803d;
        }

        .flash-alert.error {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
            color: #dc2626;
        }

        .flash-alert.info {
            background: rgba(59, 130, 246, 0.1);
            border-color: rgba(59, 130, 246, 0.3);
            color: #1d4ed8;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .flash-messages {
                top: 80px;
                right: 1rem;
                left: 1rem;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <!-- Stunning Header -->
    <header class="stunning-header" id="mainHeader">
        <div class="header-container">
            <!-- Brand Section -->
            <a href="<?= PUBLIC_URL ?>" class="brand-section">
                <img src="<?= APP_URL ?>/logo1.png" alt="BaronCast Logo" class="brand-logo">
                <div class="brand-text">
                    <h1 class="brand-name"><?= APP_NAME ?></h1>
                    <p class="brand-tagline">Digital Voting Platform</p>
                </div>
            </a>

            <!-- Desktop Navigation -->
            <nav class="nav-menu">
                <div class="nav-item">
                    <a href="<?= PUBLIC_URL ?>" class="nav-link">
                        <i class="fas fa-home nav-icon"></i>
                        <span>Home</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="<?= PUBLIC_URL ?>/events" class="nav-link">
                        <i class="fas fa-calendar-alt nav-icon"></i>
                        <span>Events</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="<?= APP_URL ?>/verify-receipt" class="nav-link">
                        <i class="fas fa-shield-check nav-icon"></i>
                        <span>Verify Receipt</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="<?= PUBLIC_URL ?>/about" class="nav-link">
                        <i class="fas fa-info-circle nav-icon"></i>
                        <span>About</span>
                    </a>
                </div>
            </nav>

            <!-- User Section -->
            <div class="user-menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-avatar" onclick="toggleUserMenu()">
                        <i class="fas fa-user"></i>
                    </div>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="<?= PUBLIC_URL ?>/login" class="btn-login">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Login</span>
                        </a>
                        <a href="<?= PUBLIC_URL ?>/register" class="btn-register">
                            <span><i class="fas fa-rocket"></i> Get Started</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Toggle -->
            <button class="mobile-toggle" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <nav class="mobile-nav">
            <a href="<?= PUBLIC_URL ?>" class="nav-link">
                <i class="fas fa-home nav-icon"></i>
                <span>Home</span>
            </a>
            <a href="<?= PUBLIC_URL ?>/events" class="nav-link">
                <i class="fas fa-calendar-alt nav-icon"></i>
                <span>Events</span>
            </a>
            <a href="<?= APP_URL ?>/verify-receipt" class="nav-link">
                <i class="fas fa-shield-check nav-icon"></i>
                <span>Verify Receipt</span>
            </a>
            <a href="<?= PUBLIC_URL ?>/about" class="nav-link">
                <i class="fas fa-info-circle nav-icon"></i>
                <span>About</span>
            </a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div style="border-top: 1px solid rgba(255,255,255,0.2); margin-top: 1rem; padding-top: 1rem;">
                    <a href="<?= PUBLIC_URL ?>/login" class="nav-link">
                        <i class="fas fa-sign-in-alt nav-icon"></i>
                        <span>Login</span>
                    </a>
                    <a href="<?= PUBLIC_URL ?>/register" class="nav-link">
                        <i class="fas fa-rocket nav-icon"></i>
                        <span>Get Started</span>
                    </a>
                </div>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Flash Messages -->
    <div class="flash-messages">
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="flash-alert success">
                <i class="fas fa-check-circle me-2"></i>
                <?= htmlspecialchars($_SESSION['flash_success']) ?>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="flash-alert error">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($_SESSION['flash_error']) ?>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_info'])): ?>
            <div class="flash-alert info">
                <i class="fas fa-info-circle me-2"></i>
                <?= htmlspecialchars($_SESSION['flash_info']) ?>
            </div>
            <?php unset($_SESSION['flash_info']); ?>
        <?php endif; ?>
    </div>

    <!-- Page Content -->
    <main class="page-content">

    <!-- JavaScript for Header Interactions -->
    <script>
        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('mainHeader');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Mobile menu toggle
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            const toggle = document.querySelector('.mobile-toggle');
            
            mobileMenu.classList.toggle('active');
            toggle.classList.toggle('active');
        }

        // User menu toggle (placeholder for future implementation)
        function toggleUserMenu() {
            // Implement user dropdown menu
            console.log('User menu clicked');
        }

        // Active link highlighting
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }
            });
        });

        // Auto-dismiss flash messages
        setTimeout(() => {
            const flashMessages = document.querySelectorAll('.flash-alert');
            flashMessages.forEach(message => {
                message.style.animation = 'slideOutRight 0.3s ease forwards';
                setTimeout(() => message.remove(), 300);
            });
        }, 5000);
    </script>
