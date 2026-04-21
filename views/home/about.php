<?php include __DIR__ . '/../layout/public_header.php'; ?>

<!-- Hero Section -->
<section class="hero-section bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4">About BaronCast</h1>
                <p class="lead mb-4">
                    Revolutionizing digital voting with secure, transparent, and accessible solutions for events, competitions, and democratic processes across Ghana and beyond.
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <span class="badge bg-light text-primary fs-6 px-3 py-2">
                        <i class="fas fa-shield-alt me-2"></i>Secure
                    </span>
                    <span class="badge bg-light text-primary fs-6 px-3 py-2">
                        <i class="fas fa-mobile-alt me-2"></i>Mobile-First
                    </span>
                    <span class="badge bg-light text-primary fs-6 px-3 py-2">
                        <i class="fas fa-chart-line me-2"></i>Real-time
                    </span>
                    <span class="badge bg-light text-primary fs-6 px-3 py-2">
                        <i class="fas fa-globe-africa me-2"></i>Ghana-focused
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fas fa-bullseye text-primary fs-2"></i>
                            </div>
                        </div>
                        <h3 class="text-center mb-4">Our Mission</h3>
                        <p class="text-muted">
                            To democratize voting and make it accessible to everyone through innovative technology. We believe every voice matters and every vote should count, whether it's for a local talent show, university elections, or community decisions.
                        </p>
                        <p class="text-muted mb-0">
                            BaronCast bridges the gap between traditional voting methods and modern digital solutions, ensuring transparency, security, and ease of use for all participants.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fas fa-eye text-success fs-2"></i>
                            </div>
                        </div>
                        <h3 class="text-center mb-4">Our Vision</h3>
                        <p class="text-muted">
                            To become Africa's leading digital voting platform, empowering organizations, institutions, and communities to conduct fair, transparent, and efficient voting processes.
                        </p>
                        <p class="text-muted mb-0">
                            We envision a future where digital voting is the norm, where results are instant, verifiable, and trusted by all stakeholders, contributing to stronger democratic processes across the continent.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Platform Statistics -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">BaronCast by the Numbers</h2>
            <p class="text-muted">Real impact, real results, real trust</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-calendar-alt text-primary fs-2"></i>
                    </div>
                    <h3 class="fw-bold text-primary"><?= $stats['total_events'] ?></h3>
                    <p class="text-muted mb-0">Events Hosted</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-vote-yea text-success fs-2"></i>
                    </div>
                    <h3 class="fw-bold text-success"><?= $stats['total_votes'] ?></h3>
                    <p class="text-muted mb-0">Votes Cast</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-users text-info fs-2"></i>
                    </div>
                    <h3 class="fw-bold text-info"><?= $stats['total_contestants'] ?></h3>
                    <p class="text-muted mb-0">Contestants</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-chart-line text-warning fs-2"></i>
                    </div>
                    <h3 class="fw-bold text-warning"><?= $stats['uptime'] ?></h3>
                    <p class="text-muted mb-0">Platform Uptime</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Key Features -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Why Choose BaronCast?</h2>
            <p class="text-muted">Powerful features designed for the African market</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-mobile-alt text-primary fs-4"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Mobile Money Integration</h5>
                        <p class="text-muted mb-0">
                            Seamless integration with MTN, Vodafone, and AirtelTigo mobile money services. Vote using your phone with secure payment processing.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-shield-alt text-success fs-4"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Bank-Level Security</h5>
                        <p class="text-muted mb-0">
                            Advanced encryption, secure payment processing, and fraud prevention systems protect every vote and transaction.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-chart-bar text-info fs-4"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Real-time Results</h5>
                        <p class="text-muted mb-0">
                            Live vote counting and instant result updates. Watch the competition unfold in real-time with transparent analytics.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-cog text-warning fs-4"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Easy Setup</h5>
                        <p class="text-muted mb-0">
                            Create and launch voting events in minutes. No technical expertise required - our intuitive interface handles everything.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-receipt text-danger fs-4"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Transparent Receipts</h5>
                        <p class="text-muted mb-0">
                            Every vote generates a verifiable receipt. Voters can confirm their votes were counted and organizers can audit results.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="bg-secondary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-headset text-secondary fs-4"></i>
                        </div>
                        <h5 class="fw-bold mb-3">24/7 Support</h5>
                        <p class="text-muted mb-0">
                            Dedicated customer support team available around the clock to ensure your voting events run smoothly.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Use Cases -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Perfect for Every Occasion</h2>
            <p class="text-muted">From small community events to large-scale competitions</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-trophy text-primary"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-2">Talent Shows & Competitions</h5>
                                <p class="text-muted mb-0">Perfect for reality TV shows, talent competitions, beauty pageants, and entertainment events where audience participation drives engagement.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-graduation-cap text-success"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-2">Educational Institutions</h5>
                                <p class="text-muted mb-0">Student government elections, academic awards, course evaluations, and campus-wide polls for universities and schools.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-building text-info"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-2">Corporate Events</h5>
                                <p class="text-muted mb-0">Employee recognition awards, board elections, shareholder voting, and corporate decision-making processes.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-users text-warning"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-2">Community Organizations</h5>
                                <p class="text-muted mb-0">Local government elections, community leader selection, NGO board elections, and public opinion polls.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Technology Stack -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Built with Modern Technology</h2>
            <p class="text-muted">Reliable, scalable, and secure infrastructure</p>
        </div>
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <h4 class="fw-bold mb-4">Robust Architecture</h4>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="fab fa-php text-primary me-2 fs-4"></i>
                            <span class="fw-semibold">PHP Backend</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-database text-success me-2 fs-4"></i>
                            <span class="fw-semibold">MySQL Database</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="fab fa-js-square text-warning me-2 fs-4"></i>
                            <span class="fw-semibold">JavaScript</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="fab fa-bootstrap text-info me-2 fs-4"></i>
                            <span class="fw-semibold">Bootstrap UI</span>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <h5 class="fw-bold mb-3">Payment Integration</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-primary fs-6 px-3 py-2">Paystack</span>
                        <span class="badge bg-success fs-6 px-3 py-2">MTN Mobile Money</span>
                        <span class="badge bg-danger fs-6 px-3 py-2">Vodafone Cash</span>
                        <span class="badge bg-warning fs-6 px-3 py-2">AirtelTigo Money</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Security Features</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                SSL/TLS Encryption
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                PCI DSS Compliant Payments
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Fraud Detection Systems
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Data Privacy Protection
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Regular Security Audits
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                GDPR Compliance Ready
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="fw-bold mb-3">Ready to Transform Your Voting Experience?</h2>
                <p class="lead mb-0">
                    Join thousands of organizations already using BaronCast to conduct fair, transparent, and engaging voting events.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="<?= APP_URL ?>/events" class="btn btn-light btn-lg px-4">
                    <i class="fas fa-rocket me-2"></i>
                    Explore Events
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Contact Information -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Get in Touch</h2>
            <p class="text-muted">We'd love to hear from you and help with your voting needs</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-4 text-center">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="fas fa-envelope text-primary fs-4"></i>
                </div>
                <h5 class="fw-bold mb-2">Email Support</h5>
                <p class="text-muted mb-2">Get help with your voting events</p>
                <a href="mailto:support@BaronCastgh.com" class="text-primary fw-semibold">support@BaronCastgh.com</a>
            </div>
            <div class="col-lg-4 text-center">
                <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="fas fa-phone text-success fs-4"></i>
                </div>
                <h5 class="fw-bold mb-2">Phone Support</h5>
                <p class="text-muted mb-2">Speak with our team directly</p>
                <a href="tel:+233545644749" class="text-success fw-semibold">+233 54 564 4749</a>
            </div>
            <div class="col-lg-4 text-center">
                <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="fas fa-map-marker-alt text-info fs-4"></i>
                </div>
                <h5 class="fw-bold mb-2">Office Location</h5>
                <p class="text-muted mb-2">Visit us in person</p>
                <span class="text-info fw-semibold">Accra, Ghana</span>
            </div>
        </div>
    </div>
</section>

<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.min-vh-50 {
    min-height: 50vh;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-5px);
}

.badge {
    font-weight: 500;
}

@media (max-width: 768px) {
    .display-4 {
        font-size: 2.5rem;
    }
    
    .lead {
        font-size: 1.1rem;
    }
}
</style>

<?php include __DIR__ . '/../layout/public_footer.php'; ?>
