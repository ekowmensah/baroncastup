<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="app-url" content="<?= APP_URL ?>">
    <title><?= $title ?? 'Organizer Dashboard' ?> - <?= APP_NAME ?></title>
    
    <!-- CoreUI CSS -->
    <link href="<?= COREUI_CSS ?>" rel="stylesheet">
    <!-- CoreUI Icons -->
    <link href="https://cdn.jsdelivr.net/npm/@coreui/icons@3.0.1/css/all.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= APP_URL ?>/public/css/organizer.css" rel="stylesheet">
</head>
<body class="c-app c-default-layout c-layout-fixed-sidebar">
    
    <!-- Sidebar -->
    <div class="sidebar sidebar-dark sidebar-fixed sidebar-show" id="sidebar">
        <div class="sidebar-brand d-none d-md-flex">
            <i class="fas fa-vote-yea sidebar-brand-full me-2"></i>
            <strong class="sidebar-brand-full"><?= APP_NAME ?></strong>
            <i class="fas fa-vote-yea sidebar-brand-minimized"></i>
        </div>
        
        <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="<?= ORGANIZER_URL ?>">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            
            <!-- Events Management -->
            <li class="nav-group">
                <a class="nav-link nav-group-toggle" href="#">
                    <i class="nav-icon fas fa-calendar-alt"></i>
                    Events
                </a>
                <ul class="nav-group-items">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/events">
                            <i class="nav-icon fas fa-list"></i>
                            All Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/events/create">
                            <i class="nav-icon fas fa-plus"></i>
                            Create Event
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/events/drafts">
                            <i class="nav-icon fas fa-edit"></i>
                            Drafts
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Contestants -->
            <li class="nav-group">
                <a class="nav-link nav-group-toggle" href="#">
                    <i class="nav-icon fas fa-users"></i>
                    Contestants
                </a>
                <ul class="nav-group-items">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/contestants">
                            <i class="nav-icon fas fa-list"></i>
                            All Contestants
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/contestants/create">
                            <i class="nav-icon fas fa-user-plus"></i>
                            Add Contestant
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/categories">
                            <i class="nav-icon fas fa-tags"></i>
                            Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/shortcode-stats">
                            <i class="nav-icon fas fa-hashtag"></i>
                            Shortcode Stats
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Voting & Results -->
            <li class="nav-group">
                <a class="nav-link nav-group-toggle" href="#">
                    <i class="nav-icon fas fa-vote-yea"></i>
                    Voting
                </a>
                <ul class="nav-group-items">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/voting/live">
                            <i class="nav-icon fas fa-broadcast-tower"></i>
                            Live Results
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/voting/analytics">
                            <i class="nav-icon fas fa-chart-line"></i>
                            Analytics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/voting/receipts">
                            <i class="nav-icon fas fa-receipt"></i>
                            Vote Receipts
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Financial -->
            <li class="nav-group">
                <a class="nav-link nav-group-toggle" href="#">
                    <i class="nav-icon fas fa-money-bill"></i>
                    Financial
                </a>
                <ul class="nav-group-items">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/financial/revenue">
                            <i class="nav-icon fas fa-chart-line text-success"></i>
                            Revenue Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/financial/overview">
                            <i class="nav-icon fas fa-chart-pie"></i>
                            Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/financial/transactions">
                            <i class="nav-icon fas fa-credit-card"></i>
                            Transactions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/payouts">
                            <i class="nav-icon fas fa-money-check-alt"></i>
                            Payouts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/financial/bundles">
                            <i class="nav-icon fas fa-box"></i>
                            Vote Bundles
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Marketing -->
      <!--      <li class="nav-group">
                <a class="nav-link nav-group-toggle" href="#">
                    <i class="nav-icon fas fa-bullhorn"></i>
                    Marketing
                </a>
                <ul class="nav-group-items">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/marketing/coupons">
                            <i class="nav-icon fas fa-tags"></i>
                            Coupons
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/marketing/referrals">
                            <i class="nav-icon fas fa-share-alt"></i>
                            Referrals
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/marketing/campaigns">
                            <i class="nav-icon fas fa-envelope"></i>
                            Campaigns
                        </a>
                    </li>
                </ul>
            </li> -->
            
            <!-- Settings -->
            <li class="nav-group">
                <a class="nav-link nav-group-toggle" href="#">
                    <i class="nav-icon fas fa-cog"></i>
                    Settings
                </a>
                <ul class="nav-group-items">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/settings/organization">
                            <i class="nav-icon fas fa-building"></i>
                            Organization
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/settings/users">
                            <i class="nav-icon fas fa-users-cog"></i>
                            Team Members
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/settings/integrations">
                            <i class="nav-icon fas fa-plug"></i>
                            Integrations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/settings/security">
                            <i class="nav-icon fas fa-shield-alt"></i>
                            Security
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ORGANIZER_URL ?>/settings/ussd">
                            <i class="nav-icon fas fa-mobile-alt"></i>
                            USSD Settings
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Reports -->
            <li class="nav-item">
                <a class="nav-link" href="<?= ORGANIZER_URL ?>/reports">
                    <i class="nav-icon fas fa-chart-bar"></i>
                    Reports
                </a>
            </li>
        </ul>
        
        <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
    </div>

    <!-- Main Content -->
    <div class="wrapper d-flex flex-column min-vh-100 bg-light">
        <!-- Header -->
        <header class="header header-sticky mb-4">
            <div class="container-fluid">
                <button class="header-toggler px-md-0 me-md-3" type="button" onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()">
                    <i class="fas fa-bars"></i>
                </button>
                
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?= ORGANIZER_URL ?>">Dashboard</a>
                        </li>
                        <?php if (isset($breadcrumbs) && is_array($breadcrumbs)): ?>
                            <?php foreach ($breadcrumbs as $crumb): ?>
                                <?php if (isset($crumb['url'])): ?>
                                    <li class="breadcrumb-item">
                                        <a href="<?= $crumb['url'] ?>"><?= htmlspecialchars($crumb['title']) ?></a>
                                    </li>
                                <?php else: ?>
                                    <li class="breadcrumb-item active"><?= htmlspecialchars($crumb['title']) ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ol>
                </nav>
                
                <ul class="header-nav ms-auto">
                    <!-- Notifications -->
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-coreui-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <span class="badge badge-sm bg-danger ms-1">3</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-lg pt-0">
                            <div class="dropdown-header bg-light">
                                <strong>You have 3 notifications</strong>
                            </div>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-vote-yea text-success me-2"></i>
                                New votes received
                                <span class="float-end text-medium-emphasis small">2 min ago</span>
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-user-plus text-info me-2"></i>
                                New contestant added
                                <span class="float-end text-medium-emphasis small">5 min ago</span>
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-calendar text-warning me-2"></i>
                                Event starting soon
                                <span class="float-end text-medium-emphasis small">10 min ago</span>
                            </a>
                            <div class="dropdown-footer">
                                <a href="<?= ORGANIZER_URL ?>/notifications" class="btn btn-link btn-block">View all</a>
                            </div>
                        </div>
                    </li>
                    
                    <!-- User Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link py-0" data-coreui-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                            <div class="avatar avatar-md">
                                <i class="fas fa-user-circle fa-2x"></i>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end pt-0 pr-5 w-100">
                            <div class="dropdown-header bg-light py-2">
                                <div class="fw-semibold"><?= htmlspecialchars($_SESSION['user_email'] ?? 'User') ?></div>
                                <div class="text-medium-emphasis small">
                                    <?php if (isset($_SESSION['is_impersonating']) && $_SESSION['is_impersonating']): ?>
                                        Organizer (Used by SuperAdmin)
                                    <?php else: ?>
                                        Organizer
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (isset($_SESSION['is_impersonating']) && $_SESSION['is_impersonating']): ?>
                                <button class="dropdown-item text-warning fw-bold" onclick="returnToSuperAdmin()">
                                    <i class="fas fa-arrow-left me-2"></i>Return to SuperAdmin
                                </button>
                                <div class="dropdown-divider"></div>
                            <?php endif; ?>
                            <a class="dropdown-item" href="<?= ORGANIZER_URL ?>/profile">
                                <i class="fas fa-user me-2"></i>Profile
                            </a>
                            <a class="dropdown-item" href="<?= ORGANIZER_URL ?>/settings">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                            <a class="dropdown-item" href="<?= PUBLIC_URL ?>">
                                <i class="fas fa-home me-2"></i>Public Site
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="<?= PUBLIC_URL ?>/logout" class="d-inline">
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </button>
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
        </header>

        <!-- Body -->
        <div class="body flex-grow-1 px-3">
            <div class="container-lg">
                <!-- Flash Messages -->
                <?php if (isset($_SESSION['flash_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= htmlspecialchars($_SESSION['flash_success']) ?>
                        <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['flash_success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['flash_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($_SESSION['flash_error']) ?>
                        <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['flash_error']); ?>
                <?php endif; ?>

                <!-- Content will be inserted here -->
                <?= $content ?? '' ?>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <span class="text-medium-emphasis">&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</span>
                    </div>
                    <div class="col-md-6">
                    <!--    <div class="text-end">
                            <span class="text-medium-emphasis">Version <?= APP_VERSION ?></span>
                        </div> -->
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- CoreUI JavaScript -->
    <script src="<?= COREUI_JS ?>"></script>
    <!-- Bootstrap JavaScript (required for dropdowns and other components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="<?= APP_URL ?>/public/js/organizer.js"></script>
    <!-- Image Helper -->
    <script src="<?= APP_URL ?>/public/assets/js/image-helper.js"></script>
    <script>window.APP_URL = '<?= APP_URL ?>';</script>
    
    <!-- Fix CoreUI dropdowns and sidebar -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all dropdowns with Bootstrap
        var dropdownElementList = [].slice.call(document.querySelectorAll('[data-coreui-toggle="dropdown"]'));
        var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });
        
        // Ensure sidebar is properly shown on load
        const sidebar = document.querySelector('#sidebar');
        if (sidebar && window.innerWidth >= 992) {
            sidebar.classList.add('sidebar-show');
        }
        
        // Fix sidebar toggle
        const sidebarToggler = document.querySelector('.header-toggler');
        if (sidebarToggler) {
            sidebarToggler.addEventListener('click', function() {
                if (sidebar) {
                    sidebar.classList.toggle('sidebar-show');
                }
            });
        }
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (sidebar) {
                if (window.innerWidth >= 992) {
                    sidebar.classList.add('sidebar-show');
                } else {
                    sidebar.classList.remove('sidebar-show');
                }
            }
        });
    });
    
    function returnToSuperAdmin() {
        if (confirm('Return to SuperAdmin account? This will end the tenant impersonation session.')) {
            fetch(`<?= SUPERADMIN_URL ?>/return-to-superadmin`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect_url || '<?= SUPERADMIN_URL ?>';
                } else {
                    alert('Error returning to SuperAdmin: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error returning to SuperAdmin');
            });
        }
    }
    </script>

    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
    var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
    (function(){
    var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
    s1.async=true;
    s1.src='https://embed.tawk.to/68f3fecb99b574195303217e/1j7shmj6n';
    s1.charset='UTF-8';
    s1.setAttribute('crossorigin','*');
    s0.parentNode.insertBefore(s1,s0);
    })();
    </script>
    <!--End of Tawk.to Script-->


      <!--Google Analytics-->
    <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-4T3JY6872R"></script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-4T3JY6872R');
        </script>



<!--Google Adsense-->
   <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7353409583217467"
     crossorigin="anonymous"></script>    
     
     
</body>
</html>
