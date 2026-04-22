<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Dashboard' ?> - <?= APP_NAME ?></title>
    
    <!-- CoreUI CSS -->
    <link href="<?= COREUI_CSS ?>" rel="stylesheet">
    <!-- CoreUI Icons -->
    <link href="https://cdn.jsdelivr.net/npm/@coreui/icons@3.0.1/css/all.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= APP_URL ?>/public/css/admin.css" rel="stylesheet">
</head>
<body class="c-app c-default-layout c-layout-fixed-sidebar">
    
    <!-- Sidebar -->
    <div class="sidebar sidebar-dark sidebar-fixed" id="sidebar">
        <div class="sidebar-brand d-none d-md-flex">
            <i class="fas fa-shield-alt sidebar-brand-full me-2"></i>
            <strong class="sidebar-brand-full">Admin Panel</strong>
            <i class="fas fa-shield-alt sidebar-brand-minimized"></i>
        </div>
        
        <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="<?= ADMIN_URL ?>">
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
                        <a class="nav-link" href="<?= ADMIN_URL ?>/events">
                            <i class="nav-icon fas fa-list"></i>
                            All Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/events/pending">
                            <i class="nav-icon fas fa-clock"></i>
                            Pending Approval
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/events/suspended">
                            <i class="nav-icon fas fa-ban"></i>
                            Suspended
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Users & Tenants -->
            <li class="nav-group">
                <a class="nav-link nav-group-toggle" href="#">
                    <i class="nav-icon fas fa-users"></i>
                    Users & Tenants
                </a>
                <ul class="nav-group-items">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/tenants">
                            <i class="nav-icon fas fa-building"></i>
                            Organizations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/users">
                            <i class="nav-icon fas fa-user"></i>
                            Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/roles">
                            <i class="nav-icon fas fa-user-tag"></i>
                            Roles & Permissions
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Monitoring -->
            <li class="nav-group">
                <a class="nav-link nav-group-toggle" href="#">
                    <i class="nav-icon fas fa-chart-line"></i>
                    Monitoring
                </a>
                <ul class="nav-group-items">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/monitoring/votes">
                            <i class="nav-icon fas fa-vote-yea"></i>
                            Vote Activity
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/monitoring/transactions">
                            <i class="nav-icon fas fa-credit-card"></i>
                            Transactions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/monitoring/performance">
                            <i class="nav-icon fas fa-server"></i>
                            Performance
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Security -->
            <li class="nav-group">
                <a class="nav-link nav-group-toggle" href="#">
                    <i class="nav-icon fas fa-shield-alt"></i>
                    Security
                </a>
                <ul class="nav-group-items">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/security/audit">
                            <i class="nav-icon fas fa-history"></i>
                            Audit Logs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/security/fraud">
                            <i class="nav-icon fas fa-exclamation-triangle"></i>
                            Fraud Detection
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/security/blocks">
                            <i class="nav-icon fas fa-ban"></i>
                            Risk Blocks
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
                        <a class="nav-link" href="<?= ADMIN_URL ?>/financial/revenue">
                            <i class="nav-icon fas fa-chart-pie"></i>
                            Revenue Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/financial/payouts">
                            <i class="nav-icon fas fa-money-check-alt"></i>
                            Payouts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/financial/fees">
                            <i class="nav-icon fas fa-percentage"></i>
                            Fee Rules
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- System -->
            <li class="nav-group">
                <a class="nav-link nav-group-toggle" href="#">
                    <i class="nav-icon fas fa-cog"></i>
                    System
                </a>
                <ul class="nav-group-items">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/system/settings">
                            <i class="nav-icon fas fa-sliders-h"></i>
                            Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/system/maintenance">
                            <i class="nav-icon fas fa-tools"></i>
                            Maintenance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/system/backups">
                            <i class="nav-icon fas fa-database"></i>
                            Backups
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Reports -->
            <li class="nav-item">
                <a class="nav-link" href="<?= ADMIN_URL ?>/reports">
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
                            <a href="<?= ADMIN_URL ?>">Admin</a>
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
                    <!-- System Status -->
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-circle text-success me-1"></i>
                            <small>System Online</small>
                        </span>
                    </li>
                    
                    <!-- Alerts -->
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-coreui-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="badge badge-sm bg-warning ms-1">2</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-lg pt-0">
                            <div class="dropdown-header bg-light">
                                <strong>System Alerts</strong>
                            </div>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-shield-alt text-warning me-2"></i>
                                Suspicious activity detected
                                <span class="float-end text-medium-emphasis small">5 min ago</span>
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-server text-info me-2"></i>
                                High server load
                                <span class="float-end text-medium-emphasis small">15 min ago</span>
                            </a>
                        </div>
                    </li>
                    
                    <!-- User Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link py-0" data-coreui-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                            <div class="avatar avatar-md">
                                <i class="fas fa-user-shield fa-2x"></i>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end pt-0 pr-5 w-100">
                            <div class="dropdown-header bg-light py-2">
                                <div class="fw-semibold"><?= htmlspecialchars($_SESSION['user_email'] ?? 'Admin') ?></div>
                                <div class="text-medium-emphasis small">
                                    <?php if (isset($_SESSION['is_impersonating']) && $_SESSION['is_impersonating']): ?>
                                        Administrator (Impersonated by SuperAdmin)
                                    <?php else: ?>
                                        Administrator
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (isset($_SESSION['is_impersonating']) && $_SESSION['is_impersonating']): ?>
                                <button class="dropdown-item text-warning fw-bold" onclick="returnToSuperAdmin()">
                                    <i class="fas fa-arrow-left me-2"></i>Return to SuperAdmin
                                </button>
                                <div class="dropdown-divider"></div>
                            <?php endif; ?>
                            <a class="dropdown-item" href="<?= ADMIN_URL ?>/profile">
                                <i class="fas fa-user me-2"></i>Profile
                            </a>
                            <a class="dropdown-item" href="<?= ADMIN_URL ?>/settings">
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
                        <span class="text-medium-emphasis">&copy; <?= date('Y') ?> <?= APP_NAME ?> Admin Panel</span>
                    </div>
                    <div class="col-md-6">
                        <div class="text-end">
                            <span class="text-medium-emphasis">Version <?= APP_VERSION ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- CoreUI JavaScript -->
    <script src="<?= COREUI_JS ?>"></script>
    <!-- Custom JavaScript -->
    <script src="<?= APP_URL ?>/public/js/admin.js"></script>
    
    <script>
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
</body>
</html>
