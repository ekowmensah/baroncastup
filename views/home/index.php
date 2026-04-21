<?php 
// Initialize stats with fallback values if not provided
$stats = $stats ?? [
    'total_events' => '12',
    'total_events_raw' => 12,
    'total_votes' => '1.2K+',
    'total_votes_raw' => 1247,
    'total_contestants' => '89',
    'total_contestants_raw' => 89,
    'active_events' => 3,
    'engagement_rate' => '94%',
    'uptime' => '99.9%'
];

include __DIR__ . '/../layout/public_header.php'; 
?>

<!-- Hero Section -->
<section class="hero-section position-relative overflow-hidden d-flex align-items-center" style="min-height: calc(100vh - 80px);">
    <!-- Animated Background -->
    <div class="hero-bg position-absolute w-100 h-100">
        <div class="gradient-bg"></div>
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
        </div>
    </div>
    
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="hero-content text-white">
                    <!-- Badge -->
                    <div class="hero-badge mb-4">
                        <span class="badge bg-warning text-dark px-4 py-2 rounded-pill fw-semibold">
                            <i class="fas fa-star me-2"></i>
                            #1 Voting Platform
                        </span>
                    </div>
                    
                    <!-- Main Heading -->
                    <h1 class="display-2 fw-bold mb-4 hero-title">
                        Transform Your
                        <span class="text-gradient">Events</span>
                        <br>With Smart Voting
                    </h1>
                    
                    <!-- Subtitle -->
                    <p class="lead mb-5 hero-subtitle opacity-90">
                        Create engaging competitions, manage contestants seamlessly, and deliver 
                        real-time results with our enterprise-grade voting platform trusted by thousands.
                    </p>
                    
                    <!-- CTA Buttons -->
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <div class="hero-actions mb-5">
                            <a href="<?= APP_URL ?>/register" class="btn btn-warning btn-lg me-3 px-5 py-3 rounded-pill shadow-lg hover-lift">
                                <i class="fas fa-rocket me-2"></i>100% Free
                            </a>
                            <a href="<?= APP_URL ?>/events" class="btn btn-outline-light btn-lg px-5 py-3 rounded-pill hover-lift">
                                <i class="fas fa-play me-2"></i>Vote Now
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="hero-actions mb-5">
                            <a href="<?= ORGANIZER_URL ?>" class="btn btn-warning btn-lg me-3 px-5 py-3 rounded-pill shadow-lg hover-lift">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                            <a href="<?= APP_URL ?>/events" class="btn btn-outline-light btn-lg px-5 py-3 rounded-pill hover-lift">
                                <i class="fas fa-calendar me-2"></i>Browse Events
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Statistics -->
                    <div class="hero-stats">
                        <div class="row g-4">
                            <div class="col-4">
                                <div class="stat-card text-center">
                                    <div class="stat-number fw-bold mb-1" data-count="<?= $stats['total_events_raw'] ?>">
                                        <?= $stats['total_events'] ?>
                                    </div>
                                    <div class="stat-label small opacity-75">Events</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-card text-center">
                                    <div class="stat-number fw-bold mb-1" data-count="<?= $stats['total_votes_raw'] ?>">
                                        <?= $stats['total_votes'] ?>
                                    </div>
                                    <div class="stat-label small opacity-75">Votes Cast</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-card text-center">
                                    <div class="stat-number fw-bold mb-1">
                                        <?= $stats['uptime'] ?>
                                    </div>
                                    <div class="stat-label small opacity-75">Uptime</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Hero Visual -->
            <div class="col-lg-6">
                <div class="hero-visual position-relative">
                    <!-- Main Dashboard Mockup -->
                    <div class="dashboard-mockup">
                        <div class="mockup-window bg-white rounded-4 shadow-2xl p-4 mb-4">
                            <!-- Header -->
                            <div class="mockup-header d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="mockup-avatar bg-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <i class="fas fa-trophy text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-dark fw-semibold">
                                            <?php if (!empty($events)): ?>
                                                <?= htmlspecialchars(substr($events[0]['name'] ?? 'Live Event', 0, 20)) ?>
                                            <?php else: ?>
                                                Ghana Music Awards 2024
                                            <?php endif; ?>
                                        </h6>
                                        <small class="text-muted">Live Voting Dashboard</small>
                                    </div>
                                </div>
                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                    <i class="fas fa-circle me-1" style="font-size: 0.6rem;"></i>Live
                                </span>
                            </div>
                            
                            <!-- Progress -->
                            <div class="voting-progress mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-semibold text-dark">Voting Progress</span>
                                    <span class="small text-muted">
                                        <?php 
                                        $avgVotes = max(100, intval($stats['total_votes_raw'] / max(1, $stats['total_events_raw'])));
                                        echo number_format($avgVotes);
                                        ?> votes
                                    </span>
                                </div>
                                <div class="progress rounded-pill" style="height: 12px;">
                                    <div class="progress-bar bg-gradient-success progress-bar-animated rounded-pill" style="width: 78%"></div>
                                </div>
                            </div>
                            
                            <!-- Metrics Grid -->
                            <div class="metrics-grid">
                                <div class="row g-3">
                                    <div class="col-4">
                                        <div class="metric-item text-center p-3 bg-primary-subtle rounded-3">
                                            <div class="metric-value h5 mb-1 text-primary fw-bold engagement-rate">
                                                <?= $stats['engagement_rate'] ?>
                                            </div>
                                            <div class="metric-label small text-muted">Engagement</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="metric-item text-center p-3 bg-success-subtle rounded-3">
                                            <div class="metric-value h5 mb-1 text-success fw-bold live-events-count">
                                                <?= $stats['active_events'] ?>
                                            </div>
                                            <div class="metric-label small text-muted">Live Events</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="metric-item text-center p-3 bg-warning-subtle rounded-3">
                                            <div class="metric-value h5 mb-1 text-warning fw-bold">
                                                <?= $stats['total_contestants'] ?>
                                            </div>
                                            <div class="metric-label small text-muted">Contestants</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Floating Stats Card -->
                        <div class="floating-card position-absolute bg-white rounded-3 shadow-lg p-3" style="bottom: -20px; right: -20px; width: 200px;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="small text-muted mb-1">Total Revenue</div>
                                    <div class="h6 mb-0 text-success fw-bold">
                                        <?= $stats['total_revenue'] ?? 'GH₵12.5K+' ?>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <i class="fas fa-chart-line fa-2x text-success opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section py-6">
    <div class="container">
        <!-- Section Header -->
        <div class="row">
            <div class="col-lg-8 mx-auto text-center mb-6">
                <div class="section-badge mb-4">
                    <span class="badge bg-primary-subtle text-primary px-4 py-2 rounded-pill fw-semibold">
                        <i class="fas fa-star me-2"></i>Features
                    </span>
                </div>
                <h2 class="display-4 fw-bold mb-4">Everything You Need for Professional Voting</h2>
                <p class="lead text-muted mb-0">
                    Comprehensive tools designed to create, manage, and analyze voting events 
                    with enterprise-grade security and reliability.
                </p>
            </div>
        </div>
        
        <!-- Features Grid -->
        <div class="row g-5">
            <div class="col-lg-4 col-md-6">
                <div class="feature-card h-100 position-relative">
                    <div class="feature-content bg-white rounded-4 p-5 h-100 shadow-hover border-0">
                        <div class="feature-icon-wrapper mb-4">
                            <div class="feature-icon bg-primary-gradient rounded-3 p-3 d-inline-flex">
                                <i class="fas fa-calendar-alt fa-2x text-white"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-3">Smart Event Management</h4>
                        <p class="text-muted mb-4">
                            Create and customize voting events with advanced scheduling, 
                            category management, and automated workflows.
                        </p>
                        <div class="feature-list">
                            <div class="feature-item d-flex align-items-center mb-2">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span class="small">Multi-category support</span>
                            </div>
                            <div class="feature-item d-flex align-items-center mb-2">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span class="small">Automated scheduling</span>
                            </div>
                            <div class="feature-item d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span class="small">Custom branding</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card h-100 position-relative">
                    <div class="feature-content bg-white rounded-4 p-5 h-100 shadow-hover border-0">
                        <div class="feature-icon-wrapper mb-4">
                            <div class="feature-icon bg-success-gradient rounded-3 p-3 d-inline-flex">
                                <i class="fas fa-users fa-2x text-white"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-3">Contestant Management</h4>
                        <p class="text-muted mb-4">
                            Comprehensive contestant profiles with rich media galleries, 
                            social integration, and performance tracking.
                        </p>
                        <div class="feature-list">
                            <div class="feature-item d-flex align-items-center mb-2">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span class="small">Rich media support</span>
                            </div>
                            <div class="feature-item d-flex align-items-center mb-2">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span class="small">Bulk import/export</span>
                            </div>
                            <div class="feature-item d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span class="small">Social integration</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card h-100 position-relative">
                    <div class="feature-content bg-white rounded-4 p-5 h-100 shadow-hover border-0">
                        <div class="feature-icon-wrapper mb-4">
                            <div class="feature-icon bg-info-gradient rounded-3 p-3 d-inline-flex">
                                <i class="fas fa-chart-line fa-2x text-white"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-3">Real-time Analytics</h4>
                        <p class="text-muted mb-4">
                            Advanced dashboard with live vote tracking, engagement metrics, 
                            and comprehensive reporting capabilities.
                        </p>
                        <div class="feature-list">
                            <div class="feature-item d-flex align-items-center mb-2">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span class="small">Live vote tracking</span>
                            </div>
                            <div class="feature-item d-flex align-items-center mb-2">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span class="small">Engagement analytics</span>
                            </div>
                            <div class="feature-item d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span class="small">Export reports</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Live Events Section -->
<?php if (!empty($events)): ?>
<section class="events-section py-6 bg-light">
    <div class="container">
        <!-- Section Header -->
        <div class="row">
            <div class="col-lg-8 mx-auto text-center mb-6">
                <div class="section-badge mb-4">
                    <span class="badge bg-danger-subtle text-danger px-4 py-2 rounded-pill fw-semibold">
                        <i class="fas fa-fire me-2"></i>Live Events
                    </span>
                </div>
                <h2 class="display-4 fw-bold mb-4">Join Live Voting Events</h2>
                <p class="lead text-muted mb-0">
                    Participate in exciting competitions happening right now. 
                    Your vote matters in shaping the results!
                </p>
            </div>
        </div>
        
        <!-- Events Grid -->
        <div class="row g-5">
            <?php foreach ($events as $event): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="event-card h-100 position-relative">
                        <div class="event-content bg-white rounded-4 overflow-hidden h-100 shadow-hover border-0">
                            <!-- Event Image -->
                            <?php if ($event['featured_image']): ?>
                                <div class="event-image position-relative overflow-hidden">
                                    <img src="<?= htmlspecialchars(image_url($event['featured_image'])) ?>" 
                                         class="w-100 event-img" 
                                         style="height: 240px; object-fit: cover;"
                                         alt="<?= htmlspecialchars($event['name']) ?>">
                                    <div class="event-overlay position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-25"></div>
                                    <div class="event-badge position-absolute top-0 end-0 m-3">
                                        <span class="badge bg-success px-3 py-2 rounded-pill">
                                            <i class="fas fa-circle me-1" style="font-size: 0.6rem;"></i>Live
                                        </span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="event-placeholder bg-primary-gradient d-flex align-items-center justify-content-center" style="height: 240px;">
                                    <i class="fas fa-trophy fa-4x text-white opacity-50"></i>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Event Content -->
                            <div class="event-body p-4">
                                <h5 class="event-title fw-bold mb-3"><?= htmlspecialchars($event['name']) ?></h5>
                                
                                <?php if ($event['description']): ?>
                                    <p class="event-description text-muted mb-4">
                                        <?= htmlspecialchars(substr($event['description'], 0, 100)) ?>
                                        <?= strlen($event['description']) > 100 ? '...' : '' ?>
                                    </p>
                                <?php endif; ?>
                                
                                <!-- Event Meta -->
                                <div class="event-meta mb-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-calendar text-primary me-2"></i>
                                        <small class="text-muted">
                                            <?= date('M j, Y', strtotime($event['start_date'])) ?>
                                        </small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-users text-success me-2"></i>
                                        <small class="text-muted">Active voting in progress</small>
                                    </div>
                                </div>
                                
                                <!-- CTA Button -->
                                <div class="d-grid">
                                    <a href="<?= APP_URL ?>/events/<?= $event['id'] ?>" 
                                       class="btn btn-primary btn-lg rounded-pill hover-lift">
                                        <i class="fas fa-vote-yea me-2"></i>Vote Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- View All Button -->
        <div class="text-center mt-6">
            <a href="<?= APP_URL ?>/events" class="btn btn-outline-primary btn-lg px-5 py-3 rounded-pill hover-lift">
                <i class="fas fa-calendar me-2"></i>View All Events
            </a>
        </div>
    </div>
</section>
<?php else: ?>
<!-- No Events Section -->
<section class="no-events-section py-6 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mx-auto text-center">
                <div class="no-events-content">
                    <div class="no-events-icon mb-5">
                        <div class="icon-wrapper bg-primary-subtle rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                            <i class="fas fa-calendar-plus fa-3x text-primary"></i>
                        </div>
                    </div>
                    <h3 class="display-6 fw-bold mb-4">No Active Events</h3>
                    <p class="lead text-muted mb-5">
                        Be the first to know when exciting voting events go live! 
                        Check back soon or create your own event.
                    </p>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <div class="d-flex gap-3 justify-content-center flex-wrap">
                            <a href="<?= APP_URL ?>/register" class="btn btn-primary btn-lg px-5 py-3 rounded-pill hover-lift">
                                <i class="fas fa-bell me-2"></i>Get Notified
                            </a>
                            <a href="<?= APP_URL ?>/register" class="btn btn-outline-primary btn-lg px-5 py-3 rounded-pill hover-lift">
                                <i class="fas fa-plus me-2"></i>Create Event
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="cta-section py-6 position-relative overflow-hidden bg-dark">
    <!-- Enhanced Background -->
    <div class="cta-bg position-absolute w-100 h-100">
        <div class="gradient-bg-dark opacity-90"></div>
    </div>
    
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div class="cta-content">
                    <h2 class="display-5 fw-bold mb-4 text-white">Ready to Transform Your Events?</h2>
                    <p class="lead mb-0 text-light">
                        Join thousands of event organizers who trust BaronCast for their voting needs. 
                        Start your FREE Events today and experience the future of digital voting.
                    </p>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="cta-actions">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="<?= APP_URL ?>/register" class="btn btn-warning btn-lg px-5 py-3 rounded-pill shadow-lg hover-lift">
                            <i class="fas fa-rocket me-2"></i>Start Now. 100% Free
                        </a>
                    <?php else: ?>
                        <a href="<?= ORGANIZER_URL ?>/events/create" class="btn btn-warning btn-lg px-5 py-3 rounded-pill shadow-lg hover-lift">
                            <i class="fas fa-plus me-2"></i>Create Your Event
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sleek Modern Styles -->
<style>
/* Override header padding for seamless hero section */
main.page-content {
    margin-top: 0px !important;
    padding-top: 0px !important;
}

/* Hero section positioning */
.hero-section {
    margin-top: 80px; /* Increased spacing for better separation */
    padding-top: 3.5rem; /* Additional padding for breathing room */
}

@media (max-width: 768px) {
    .hero-section {
        margin-top: 80px;
        padding-top: 1.5rem;
    }
}

@media (max-width: 480px) {
    .hero-section {
        margin-top: 70px;
        padding-top: 1rem;
    }
}

/* Utility Classes */
.py-6 { padding-top: 4rem !important; padding-bottom: 4rem !important; }
.mb-6 { margin-bottom: 4rem !important; }
.mt-6 { margin-top: 4rem !important; }

/* Gradient Backgrounds */
.gradient-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.gradient-bg-dark {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
}

/* Enhanced CTA Section Styling */
.cta-section {
    background: #1a1a2e;
}

.cta-section .text-white {
    color: #ffffff !important;
}

.cta-section .text-light {
    color: #f8f9fa !important;
    opacity: 0.9;
}

.bg-primary-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-success-gradient {
    background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
}

.bg-info-gradient {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

/* Text Gradient */
.text-gradient {
    background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Floating Shapes Animation */
.floating-shapes {
    position: absolute;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    animation: float 20s infinite linear;
}

.shape-1 {
    width: 80px;
    height: 80px;
    top: 20%;
    left: 10%;
    animation-delay: 0s;
}

.shape-2 {
    width: 60px;
    height: 60px;
    top: 60%;
    right: 20%;
    animation-delay: 5s;
}

.shape-3 {
    width: 100px;
    height: 100px;
    bottom: 20%;
    left: 20%;
    animation-delay: 10s;
}

.shape-4 {
    width: 40px;
    height: 40px;
    top: 40%;
    right: 10%;
    animation-delay: 15s;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px) rotate(0deg);
    }
    33% {
        transform: translateY(-30px) rotate(120deg);
    }
    66% {
        transform: translateY(30px) rotate(240deg);
    }
}

/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.hero-title {
    line-height: 1.1;
}

.hero-subtitle {
    font-size: 1.25rem;
    line-height: 1.6;
}

.hero-badge .badge {
    font-size: 0.875rem;
    letter-spacing: 0.5px;
}

/* Statistics */
.stat-card {
    padding: 1.5rem 1rem;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 1rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
}

.stat-card:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
}

.stat-number {
    font-size: 2rem;
    color: #ffd700;
}

.stat-label {
    color: rgba(255, 255, 255, 0.8);
}

/* Dashboard Mockup */
.dashboard-mockup {
    transform: perspective(1000px) rotateY(-5deg) rotateX(5deg);
    transition: all 0.3s ease;
}

.dashboard-mockup:hover {
    transform: perspective(1000px) rotateY(0deg) rotateX(0deg);
}

.mockup-window {
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    backdrop-filter: blur(10px);
}

.floating-card {
    animation: floatCard 6s ease-in-out infinite;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
}

@keyframes floatCard {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

/* Section Badges */
.section-badge .badge {
    font-size: 0.875rem;
    letter-spacing: 0.5px;
}

/* Feature Cards */
.feature-card {
    transition: all 0.4s ease;
}

.feature-card:hover {
    transform: translateY(-10px);
}

.feature-content {
    transition: all 0.4s ease;
}

.shadow-hover {
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.4s ease;
}

.shadow-hover:hover {
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.feature-icon {
    width: 64px;
    height: 64px;
    transition: all 0.3s ease;
}

.feature-card:hover .feature-icon {
    transform: scale(1.1);
}

/* Event Cards */
.event-card {
    transition: all 0.4s ease;
}

.event-card:hover {
    transform: translateY(-10px);
}

.event-img {
    transition: all 0.4s ease;
}

.event-card:hover .event-img {
    transform: scale(1.05);
}

.event-overlay {
    transition: all 0.3s ease;
}

.event-card:hover .event-overlay {
    background: rgba(0, 0, 0, 0.4) !important;
}

/* Buttons */
.hover-lift {
    transition: all 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.btn-lg {
    font-weight: 600;
    letter-spacing: 0.5px;
}

/* Progress Bar */
.progress-bar-animated {
    background-size: 1rem 1rem;
    animation: progress-bar-stripes 1s linear infinite;
}

.bg-gradient-success {
    background: linear-gradient(90deg, #56ab2f 0%, #a8e6cf 100%);
}

/* Responsive Design */
@media (max-width: 768px) {
    .display-2 {
        font-size: 2.5rem;
    }
    
    .display-4 {
        font-size: 2rem;
    }
    
    .hero-subtitle {
        font-size: 1.1rem;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .dashboard-mockup {
        transform: none;
        margin-top: 2rem;
    }
    
    .floating-card {
        position: relative !important;
        margin-top: 1rem;
        bottom: auto !important;
        right: auto !important;
        width: 100% !important;
    }
    
    .py-6 {
        padding-top: 3rem !important;
        padding-bottom: 3rem !important;
    }
    
    .hero-actions {
        text-align: center;
    }
    
    .hero-actions .btn {
        display: block;
        width: 100%;
        margin-bottom: 1rem;
    }
    
    .hero-actions .btn:last-child {
        margin-bottom: 0;
    }
}

@media (max-width: 480px) {
    .hero-section {
        padding: 2rem 0;
    }
    
    .display-2 {
        font-size: 2rem;
    }
    
    .hero-subtitle {
        font-size: 1rem;
    }
    
    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }
    
    .stat-card {
        padding: 1rem 0.5rem;
    }
    
    .feature-content,
    .event-content {
        padding: 2rem 1.5rem;
    }
}

/* Shadow Utilities */
.shadow-2xl {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

/* Animation Performance */
* {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.feature-card,
.event-card,
.hover-lift {
    will-change: transform;
}
</style>

<!-- Enhanced JavaScript for Sleek Homepage -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all animations and interactions
    initializeAnimations();
    initializeCounters();
    initializeLiveUpdates();
    
    function initializeAnimations() {
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);
        
        // Observe feature cards and event cards
        document.querySelectorAll('.feature-card, .event-card').forEach(card => {
            observer.observe(card);
        });
    }
    
    function initializeCounters() {
        const counters = document.querySelectorAll('[data-count]');
        
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-count')) || 0;
            const originalText = counter.textContent;
            let current = 0;
            const increment = Math.max(1, target / 60); // Animate over ~1 second
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                
                // Update display with proper formatting
                if (originalText.includes('K+')) {
                    counter.textContent = formatNumber(Math.floor(current));
                } else if (originalText.includes('M+')) {
                    counter.textContent = formatNumber(Math.floor(current));
                } else {
                    counter.textContent = Math.floor(current).toLocaleString();
                }
            }, 16); // ~60fps
        });
    }
    
    function formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M+';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K+';
        }
        return num.toLocaleString();
    }
    
    function initializeLiveUpdates() {
        // Update live statistics every 30 seconds
        setInterval(updateLiveStats, 30000);
        
        // Add pulse effect to live badges
        animateLiveBadges();
    }
    
    function updateLiveStats() {
        // Simulate live updates with subtle animations
        // In production, this could fetch real stats from an API
        const liveEventsElement = document.querySelector('.live-events-count');
        const engagementElement = document.querySelector('.engagement-rate');
        
        if (liveEventsElement) {
            // Add subtle pulse effect to show "live" activity
            liveEventsElement.style.transform = 'scale(1.05)';
            setTimeout(() => {
                liveEventsElement.style.transform = 'scale(1)';
            }, 200);
        }
        
        if (engagementElement) {
            // Add subtle pulse effect
            engagementElement.style.transform = 'scale(1.05)';
            setTimeout(() => {
                engagementElement.style.transform = 'scale(1)';
            }, 200);
        }
        
        updateProgressBars();
    }
    
    function updateStatDisplay(stats) {
        // Update live events count
        const liveEventsElement = document.querySelector('.live-events-count');
        if (liveEventsElement && stats.active_events !== undefined) {
            animateValueChange(liveEventsElement, stats.active_events);
        }
        
        // Update engagement rate
        const engagementElement = document.querySelector('.engagement-rate');
        if (engagementElement && stats.engagement_rate !== undefined) {
            animateValueChange(engagementElement, stats.engagement_rate);
        }
        
        // Update other dynamic elements
        updateProgressBars(stats);
    }
    
    function animateValueChange(element, newValue) {
        element.style.transform = 'scale(1.1)';
        element.style.transition = 'transform 0.2s ease';
        
        setTimeout(() => {
            element.textContent = newValue;
            element.style.transform = 'scale(1)';
        }, 100);
    }
    
    function updateProgressBars() {
        const progressBars = document.querySelectorAll('.progress-bar-animated');
        progressBars.forEach(bar => {
            // Add subtle animation to show activity
            bar.style.animationDuration = '0.8s';
            setTimeout(() => {
                bar.style.animationDuration = '1s';
            }, 2000);
        });
    }
    
    function animateLiveBadges() {
        const liveBadges = document.querySelectorAll('.badge:contains("Live"), .text-success');
        
        liveBadges.forEach(badge => {
            if (badge.textContent.includes('Live') || badge.innerHTML.includes('Live')) {
                setInterval(() => {
                    badge.style.opacity = '0.7';
                    setTimeout(() => {
                        badge.style.opacity = '1';
                    }, 400);
                }, 3000);
            }
        });
    }
    
    // Enhanced hover effects for cards
    document.querySelectorAll('.feature-card, .event-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Performance optimization: Reduce animations on mobile
    if (window.innerWidth <= 768) {
        document.documentElement.style.setProperty('--animation-duration', '0.2s');
    }
});

// CSS Animation classes
const style = document.createElement('style');
style.textContent = `
    .animate-in {
        animation: slideInUp 0.6s ease-out forwards;
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .feature-card,
    .event-card {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s ease-out;
    }
    
    .feature-card.animate-in,
    .event-card.animate-in {
        opacity: 1;
        transform: translateY(0);
    }
`;
document.head.appendChild(style);

// Initialize Bootstrap components
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap accordion if available
    if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
        // Bootstrap is already loaded from header
        console.debug('Bootstrap components initialized');
    }
});
</script>

<!-- Ensure Bootstrap JS is loaded for accordion functionality -->
<script>
// Fallback Bootstrap initialization for accordion
if (typeof bootstrap === 'undefined') {
    // Load Bootstrap JS if not already loaded
    const bootstrapScript = document.createElement('script');
    bootstrapScript.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js';
    bootstrapScript.onload = function() {
        console.debug('Bootstrap JS loaded successfully');
    };
    document.head.appendChild(bootstrapScript);
}
</script>

<!-- SEO Content Section -->
<section class="seo-content-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="seo-content">
                    <h2 class="h3 fw-bold text-center mb-4">Digital Voting Platform Ghana - BaronCast</h2>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h3 class="h5 fw-semibold mb-3">Mobile Money Voting in Ghana</h3>
                            <p class="text-muted">
                                Experience seamless <strong>digital voting in Ghana</strong> with BaronCast's secure platform. 
                                Vote using <strong>MTN Mobile Money</strong>, <strong>Vodafone Cash</strong>, or <strong>AirtelTigo Money</strong> 
                                for talent shows, competitions, and democratic processes across Ghana.
                            </p>
                        </div>
                        
                        <div class="col-md-6">
                            <h3 class="h5 fw-semibold mb-3">Secure Voting System</h3>
                            <p class="text-muted">
                                Our <strong>secure voting system</strong> ensures fraud prevention with bank-level encryption. 
                                Get <strong>real-time voting results</strong> and <strong>SMS voting receipts</strong> for complete 
                                transparency in every Ghana voting event.
                            </p>
                        </div>
                        
                        <div class="col-md-6">
                            <h3 class="h5 fw-semibold mb-3">Ghana Voting Events</h3>
                            <p class="text-muted">
                                Join thousands participating in <strong>talent show voting</strong>, <strong>competition voting</strong>, 
                                and <strong>event voting in Ghana</strong>. Our platform supports all types of democratic voting 
                                with mobile money integration for easy participation.
                            </p>
                        </div>
                        
                        <div class="col-md-6">
                            <h3 class="h5 fw-semibold mb-3">Why Choose BaronCast Ghana</h3>
                            <p class="text-muted">
                                As <strong>Ghana's leading voting platform</strong>, we provide <strong>fraud-free voting</strong>, 
                                instant mobile money payments, and transparent results. Perfect for organizers seeking a 
                                reliable <strong>digital democracy solution in Ghana</strong>.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Keywords for SEO -->
                    <div class="mt-4 text-center">
                        <small class="text-muted d-none">
                            Keywords: digital voting Ghana, mobile money voting, MTN mobile money, Vodafone Cash, 
                            AirtelTigo Money, talent show voting, competition voting, secure voting system, 
                            real-time voting results, SMS voting receipts, Ghana elections, event voting, 
                            contestant voting, democratic voting, fraud-free voting, Ghana voting app
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section for SEO -->
<section class="faq-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h2 class="h3 fw-bold text-center mb-5">Frequently Asked Questions</h2>
                
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How do I vote using mobile money in Ghana?
                            </button>
                        </h3>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Voting with mobile money on BaronCast is simple! Choose your contestant, select mobile money as payment method, 
                                enter your MTN, Vodafone, or AirtelTigo number, and complete the secure payment. You'll receive an SMS receipt 
                                confirming your vote was counted.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Is BaronCast voting secure and fraud-free?
                            </button>
                        </h3>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Yes! BaronCast uses bank-level encryption and fraud prevention technology. Every vote is verified, 
                                tracked, and secured. Our transparent system provides real-time results and verifiable receipts 
                                for complete trust in the voting process.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                What types of events can use BaronCast for voting?
                            </button>
                        </h3>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>BaronCast supports all types of voting events in Ghana including talent shows, beauty pageants, 
                                music competitions, sports awards, community elections, corporate voting, and any democratic process 
                                requiring secure, transparent voting with mobile money integration.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                How quickly can I see voting results?
                            </button>
                        </h3>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>BaronCast provides real-time voting results that update instantly as votes are cast. 
                                You can watch live leaderboards, track contestant performance, and see transparent 
                                vote counts throughout the entire voting period.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../layout/public_footer.php'; ?>
