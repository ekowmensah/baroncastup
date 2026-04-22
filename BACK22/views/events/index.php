<?php include __DIR__ . '/../layout/public_header.php'; ?>

<!-- Hero Section -->
<div class="hero-section bg-gradient-primary text-white py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">
                    <i class="fas fa-trophy me-3"></i>
                    Live Voting Events
                </h1>
                <p class="lead mb-4">Discover and participate in exciting voting competitions. Cast your votes and support your favorite contestants!</p>
                <div class="d-flex gap-3">
                    <div class="text-center">
                        <div class="h3 fw-bold"><?= count($events ?? []) ?></div>
                        <small class="text-white-50">Active Events</small>
                    </div>
                    <div class="text-center">
                        <div class="h3 fw-bold">24/7</div>
                        <small class="text-white-50">Live Voting</small>
                    </div>
                    <div class="text-center">
                        <div class="h3 fw-bold">Real-time</div>
                        <small class="text-white-50">Results</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <div class="hero-icon">
                    <i class="fas fa-vote-yea fa-5x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filter Section -->
<div class="container mb-5">
    <div class="card shadow-sm border-0">
        <div class="card-body py-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="searchEvents" 
                               placeholder="Search events by name or description...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select form-select-lg" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">🟢 Active</option>
                        <option value="upcoming">🟡 Upcoming</option>
                        <option value="ended">🔴 Ended</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-grid">
                        <button class="btn btn-outline-primary btn-lg" onclick="resetFilters()">
                            <i class="fas fa-undo me-2"></i>Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">

<?php if (!empty($events)): ?>
    <?php
    // Group events by calculated status with pagination
    $groupedEvents = [
        'active' => [],
        'upcoming' => [],
        'ended' => []
    ];
    
    foreach ($events as $event) {
        // Calculate event status based on dates
        $currentTime = time();
        $startTime = strtotime($event['start_date']);
        $endTime = strtotime($event['end_date']);
        
        if ($endTime < $currentTime || $event['status'] === 'closed') {
            $calculatedStatus = 'ended';
        } elseif ($startTime > $currentTime) {
            $calculatedStatus = 'upcoming';
        } elseif ($event['status'] === 'active' && $startTime <= $currentTime && $endTime >= $currentTime) {
            $calculatedStatus = 'active';
        } else {
            $calculatedStatus = $event['status'];
        }
        
        $event['calculatedStatus'] = $calculatedStatus;
        $groupedEvents[$calculatedStatus][] = $event;
    }
    
    // Sort each group appropriately
    // Active events: by most votes (engagement)
    usort($groupedEvents['active'], function($a, $b) {
        return ($b['total_votes'] ?? 0) - ($a['total_votes'] ?? 0);
    });
    
    // Upcoming events: by start date (nearest first)
    usort($groupedEvents['upcoming'], function($a, $b) {
        return strtotime($a['start_date']) - strtotime($b['start_date']);
    });
    
    // Ended events: by end date (most recently ended first)
    usort($groupedEvents['ended'], function($a, $b) {
        return strtotime($b['end_date']) - strtotime($a['end_date']);
    });
    
    // Pagination settings
    $paginationLimits = [
        'active' => 8,
        'upcoming' => 4,
        'ended' => 4
    ];
    
    // Split events for initial display and "load more"
    $displayEvents = [];
    $hiddenEvents = [];
    
    foreach ($groupedEvents as $status => $events) {
        $limit = $paginationLimits[$status];
        $displayEvents[$status] = array_slice($events, 0, $limit);
        $hiddenEvents[$status] = array_slice($events, $limit);
    }
    ?>
    
    <!-- Live Events Section -->
    <?php if (!empty($groupedEvents['active'])): ?>
        <div class="mb-5">
            <div class="d-flex align-items-center mb-4">
                <div class="section-icon bg-success text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fas fa-circle pulse"></i>
                </div>
                <div>
                    <h3 class="mb-1 text-success">
                        <i class="fas fa-broadcast-tower me-2"></i>
                        Live Events
                    </h3>
                    <p class="text-muted mb-0">
                        Showing <?= count($displayEvents['active']) ?> of <?= count($groupedEvents['active']) ?> live events
                        <?php if (count($groupedEvents['active']) > count($displayEvents['active'])): ?>
                            • <?= count($hiddenEvents['active']) ?> more available
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="row" id="liveEventsContainer">
                <?php foreach ($displayEvents['active'] as $event): ?>
                    <?php include 'event_card_template.php'; ?>
                <?php endforeach; ?>
            </div>
            
            <!-- Hidden events for load more -->
            <div class="row d-none" id="liveEventsHidden">
                <?php foreach ($hiddenEvents['active'] as $event): ?>
                    <?php include 'event_card_template.php'; ?>
                <?php endforeach; ?>
            </div>
            
            <?php if (!empty($hiddenEvents['active'])): ?>
                <div class="text-center mt-4">
                    <button class="btn btn-outline-success load-more-btn" data-section="live" data-target="liveEventsHidden" data-container="liveEventsContainer">
                        <i class="fas fa-plus me-2"></i>
                        Load More Live Events (<?= count($hiddenEvents['active']) ?> remaining)
                    </button>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Upcoming Events Section -->
    <?php if (!empty($groupedEvents['upcoming'])): ?>
        <div class="mb-5">
            <div class="d-flex align-items-center mb-4">
                <div class="section-icon bg-warning text-dark rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <h3 class="mb-1 text-warning">
                        <i class="fas fa-calendar-plus me-2"></i>
                        Upcoming Events
                    </h3>
                    <p class="text-muted mb-0">
                        Showing <?= count($displayEvents['upcoming']) ?> of <?= count($groupedEvents['upcoming']) ?> upcoming events
                        <?php if (count($groupedEvents['upcoming']) > count($displayEvents['upcoming'])): ?>
                            • <?= count($hiddenEvents['upcoming']) ?> more available
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="row" id="upcomingEventsContainer">
                <?php foreach ($displayEvents['upcoming'] as $event): ?>
                    <?php include 'event_card_template.php'; ?>
                <?php endforeach; ?>
            </div>
            
            <!-- Hidden events for load more -->
            <div class="row d-none" id="upcomingEventsHidden">
                <?php foreach ($hiddenEvents['upcoming'] as $event): ?>
                    <?php include 'event_card_template.php'; ?>
                <?php endforeach; ?>
            </div>
            
            <?php if (!empty($hiddenEvents['upcoming'])): ?>
                <div class="text-center mt-4">
                    <button class="btn btn-outline-warning load-more-btn" data-section="upcoming" data-target="upcomingEventsHidden" data-container="upcomingEventsContainer">
                        <i class="fas fa-plus me-2"></i>
                        Load More Upcoming Events (<?= count($hiddenEvents['upcoming']) ?> remaining)
                    </button>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Ended Events Section -->
    <?php if (!empty($groupedEvents['ended'])): ?>
        <div class="mb-5">
            <div class="d-flex align-items-center mb-4">
                <div class="section-icon bg-danger text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fas fa-stop"></i>
                </div>
                <div>
                    <h3 class="mb-1 text-danger">
                        <i class="fas fa-flag-checkered me-2"></i>
                        Ended Events
                    </h3>
                    <p class="text-muted mb-0">
                        Showing <?= count($displayEvents['ended']) ?> of <?= count($groupedEvents['ended']) ?> completed events
                        <?php if (count($groupedEvents['ended']) > count($displayEvents['ended'])): ?>
                            • <?= count($hiddenEvents['ended']) ?> more available
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="row" id="endedEventsContainer">
                <?php foreach ($displayEvents['ended'] as $event): ?>
                    <?php include 'event_card_template.php'; ?>
                <?php endforeach; ?>
            </div>
            
            <!-- Hidden events for load more -->
            <div class="row d-none" id="endedEventsHidden">
                <?php foreach ($hiddenEvents['ended'] as $event): ?>
                    <?php include 'event_card_template.php'; ?>
                <?php endforeach; ?>
            </div>
            
            <?php if (!empty($hiddenEvents['ended'])): ?>
                <div class="text-center mt-4">
                    <button class="btn btn-outline-danger load-more-btn" data-section="ended" data-target="endedEventsHidden" data-container="endedEventsContainer">
                        <i class="fas fa-plus me-2"></i>
                        Load More Ended Events (<?= count($hiddenEvents['ended']) ?> remaining)
                    </button>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
<?php else: ?>
    <div class="text-center py-5">
        <i class="fas fa-calendar-times fa-4x text-muted mb-4"></i>
        <h3 class="text-muted mb-3">No Active Events</h3>
        <p class="text-muted mb-4">There are currently no public voting events available.</p>
        
        <?php if (isset($_SESSION['user_id'])): ?>
        <!--    <a href="<?= APP_URL ?>/admin/events/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create Your First Event
            </a> -->
        <?php else: ?>
            <a href="<?= APP_URL ?>/register" class="btn btn-primary">
                <i class="fas fa-user-plus me-2"></i>Get Started
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

</div> <!-- Close container -->

<!-- Event Statistics -->
<?php if (!empty($events)): ?>
<div class="container">
    <div class="row mt-5">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h4 class="text-primary mb-0"><?= count($events) ?></h4>
                            <small class="text-muted">Active Events</small>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-success mb-0">
                                <?= count(array_filter($events, function($e) { 
                                    return strtotime($e['start_date']) <= time() && 
                                           strtotime($e['end_date']) >= time() && 
                                           $e['status'] === 'active'; 
                                })) ?>
                            </h4>
                            <small class="text-muted">Currently Voting</small>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-warning mb-0">
                                <?= count(array_filter($events, function($e) { 
                                    return strtotime($e['start_date']) > time(); 
                                })) ?>
                            </h4>
                            <small class="text-muted">Upcoming</small>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-info mb-0">24/7</h4>
                            <small class="text-muted">Platform Availability</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> <!-- Close container -->
<?php endif; ?>

<style>
.hover-shadow {
    transition: box-shadow 0.3s ease-in-out;
}

/* Ultra Compact & Stylish Design */
.event-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
    line-height: 1.2;
}

.live-badge {
    display: inline-block;
    background: linear-gradient(45deg, #ff6b6b, #ee5a24);
    color: white;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 8px;
    margin-left: 8px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.ultra-stats .stats-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 4px;
    font-size: 0.8rem;
    color: #555;
}

.stat-item {
    font-weight: 500;
}

.stat-divider {
    color: #bbb;
    font-weight: bold;
}

.mini-dates .date-row {
    font-size: 0.75rem;
    color: #777;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.date-label {
    font-weight: 600;
    color: #555;
}

.date-separator {
    color: #ccc;
}

.compact-actions {
    display: flex;
    gap: 1px;
    align-items: center;
    margin: 0;
    padding: 0;
    margin-bottom: 0 !important;
}

.vote-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 6px 14px;
    border-radius: 18px;
    text-decoration: none;
    font-size: 0.8rem;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    line-height: 1.2;
}

.vote-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    color: white;
}

.results-btn {
    background: transparent;
    color: #667eea;
    border: 1px solid #667eea;
    padding: 6px 12px;
    border-radius: 16px;
    text-decoration: none;
    font-size: 0.8rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.results-btn:hover {
    background: #667eea;
    color: white;
}

.results-btn.primary {
    background: #667eea;
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
}

.coming-soon {
    background: #ffeaa7;
    color: #d63031;
    padding: 6px 12px;
    border-radius: 16px;
    font-size: 0.8rem;
    font-weight: 600;
}

/* Card Enhancements */
.event-card {
    border: none;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.event-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.hover-shadow:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    transform: translateY(-2px);
}

.event-card {
    transition: all 0.3s ease-in-out;
}

.event-meta {
    border-top: 1px solid #eee;
    padding-top: 1rem;
}

/* Professional Modern Styling */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.hero-section {
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.1;
}

.hero-icon {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

/* Event Cards Professional Styling */
.event-card-hover {
    transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    border-radius: 16px !important;
    overflow: hidden;
}

.event-card-hover:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15) !important;
}

.event-image {
    height: 200px;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.event-card-hover:hover .event-image {
    transform: scale(1.1);
}

.card-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(102, 126, 234, 0.9), rgba(118, 75, 162, 0.9));
    opacity: 0;
    transition: opacity 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.event-card-hover:hover .card-overlay {
    opacity: 1;
}

.overlay-content {
    transform: translateY(20px);
    transition: transform 0.3s ease;
}

.event-card-hover:hover .overlay-content {
    transform: translateY(0);
}

.badge-status {
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.event-stats {
    background: rgba(0, 0, 0, 0.02);
    border-radius: 12px;
    padding: 1rem;
    margin: 1rem 0;
}

.stat-number {
    font-size: 1.25rem;
    font-weight: 700;
}

.stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 500;
}

.event-dates {
    background: linear-gradient(45deg, #f8f9fa, #e9ecef);
    border-radius: 8px;
    padding: 0.75rem;
}

/* Responsive optimizations */
@media (min-width: 992px) {
    .event-card .card {
        min-height: auto;
    }
}

@media (min-width: 768px) and (max-width: 991px) {
    .event-card .card {
        min-height: auto;
    }
}

@media (max-width: 767px) {
    .event-card .card {
        min-height: auto;
    }
    
    .hero-section {
        padding: 3rem 0 !important;
    }
    
    .display-4 {
        font-size: 2rem !important;
    }
}

/* Button enhancements */
.btn-lg {
    border-radius: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-2px);
}

/* Search section styling */
.input-group-lg .input-group-text {
    border-radius: 12px 0 0 12px;
}

.input-group-lg .form-control {
    border-radius: 0 12px 12px 0;
    border-left: none;
}

.form-select-lg {
    border-radius: 12px;
}

/* Ended event styling */
.ended-event {
    color: #dc3545;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 4px 8px;
    background: rgba(220, 53, 69, 0.1);
    border-radius: 4px;
    border: 1px solid rgba(220, 53, 69, 0.2);
}

.badge-status {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 6px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

/* Search enhancements */
.search-highlight {
    background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
    color: #333;
    padding: 2px 4px;
    border-radius: 3px;
    font-weight: 600;
}

.event-card {
    transition: all 0.3s ease;
}

.event-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

#searchEvents {
    transition: opacity 0.2s ease;
}

#searchEvents:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.no-search-results {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Section styling */
.section-icon {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.section-icon .pulse {
    animation: pulse 2s infinite;
}

/* Event sections */
.mb-5 {
    border-bottom: 1px solid rgba(0,0,0,0.05);
    padding-bottom: 2rem;
}

.mb-5:last-child {
    border-bottom: none;
}

/* Section headers */
h3 {
    font-weight: 700;
    letter-spacing: -0.5px;
}

/* Enhanced card hover effects */
.event-card-hover:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.15);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Load More button styling */
.load-more-btn {
    font-weight: 600;
    padding: 12px 24px;
    border-radius: 25px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.load-more-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.load-more-btn:disabled {
    transform: none;
    box-shadow: none;
}

.load-more-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.load-more-btn:hover::before {
    left: 100%;
}

/* Card hover overlay styling */
.card-hover-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 10;
}

.event-card-hover:hover .card-hover-overlay {
    opacity: 1;
    visibility: visible;
}

.hover-content {
    text-align: center;
    transform: translateY(20px);
    transition: transform 0.3s ease;
}

.event-card-hover:hover .hover-content {
    transform: translateY(0);
}

.btn-details {
    background: rgba(255, 255, 255, 0.95);
    border: none;
    padding: 12px 24px;
    border-radius: 25px;
    font-weight: 600;
    color: #333;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.btn-details:hover {
    background: white;
    color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.btn-details i {
    font-size: 1.1em;
}

/* Ensure overlay doesn't interfere with status badge */
.badge-status {
    z-index: 15;
    position: relative;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality with debouncing for better performance
    const searchInput = document.getElementById('searchEvents');
    const eventCards = document.querySelectorAll('.event-card');
    const allContainers = ['liveEventsContainer', 'upcomingEventsContainer', 'endedEventsContainer'];
    let searchTimeout;
    
    // Real-time search with debouncing
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // Add loading state
        searchInput.style.opacity = '0.7';
        
        // Debounce search for better performance
        searchTimeout = setTimeout(() => {
            performSearch(searchTerm);
            searchInput.style.opacity = '1';
        }, 150); // 150ms delay for smooth typing
    });
    
    function performSearch(searchTerm) {
        let visibleCount = 0;
        let sectionCounts = { live: 0, upcoming: 0, ended: 0 };
        
        eventCards.forEach(card => {
            // Get searchable content from the card
            const eventName = card.querySelector('.event-title')?.textContent.toLowerCase() || '';
            const eventDesc = card.querySelector('.card-text')?.textContent.toLowerCase() || '';
            const tenantName = card.querySelector('.tenant-name')?.textContent.toLowerCase() || '';
            const statsText = card.querySelector('.stats-row')?.textContent.toLowerCase() || '';
            
            // Search in multiple fields
            const isMatch = searchTerm === '' || 
                           eventName.includes(searchTerm) || 
                           eventDesc.includes(searchTerm) ||
                           tenantName.includes(searchTerm) ||
                           statsText.includes(searchTerm);
            
            if (isMatch) {
                card.style.display = '';
                card.style.opacity = '1';
                card.style.transform = 'scale(1)';
                visibleCount++;
                
                // Count by section
                const status = card.dataset.status;
                if (status === 'active') sectionCounts.live++;
                else if (status === 'upcoming') sectionCounts.upcoming++;
                else if (status === 'ended') sectionCounts.ended++;
                
                // Highlight matching text (optional enhancement)
                if (searchTerm !== '') {
                    highlightSearchTerm(card, searchTerm);
                } else {
                    removeHighlights(card);
                }
            } else {
                card.style.display = 'none';
                card.style.opacity = '0.5';
                card.style.transform = 'scale(0.95)';
            }
        });
        
        // Show/hide section headers based on visible cards
        updateSectionVisibility(sectionCounts, searchTerm);
        
        // Show/hide "no results" message
        updateSearchResults(visibleCount, searchTerm);
    }
    
    function updateSectionVisibility(sectionCounts, searchTerm) {
        // Show/hide sections based on whether they have visible cards
        const liveSection = document.querySelector('#liveEventsContainer').closest('.mb-5');
        const upcomingSection = document.querySelector('#upcomingEventsContainer').closest('.mb-5');
        const endedSection = document.querySelector('#endedEventsContainer').closest('.mb-5');
        
        if (liveSection) {
            liveSection.style.display = (searchTerm === '' || sectionCounts.live > 0) ? '' : 'none';
        }
        if (upcomingSection) {
            upcomingSection.style.display = (searchTerm === '' || sectionCounts.upcoming > 0) ? '' : 'none';
        }
        if (endedSection) {
            endedSection.style.display = (searchTerm === '' || sectionCounts.ended > 0) ? '' : 'none';
        }
    }
    
    function highlightSearchTerm(card, searchTerm) {
        const titleElement = card.querySelector('.event-title');
        if (titleElement) {
            const originalText = titleElement.textContent;
            const highlightedText = originalText.replace(
                new RegExp(`(${searchTerm})`, 'gi'),
                '<mark class="search-highlight">$1</mark>'
            );
            if (highlightedText !== originalText) {
                titleElement.innerHTML = highlightedText;
            }
        }
    }
    
    function removeHighlights(card) {
        const titleElement = card.querySelector('.event-title');
        if (titleElement) {
            const text = titleElement.textContent; // This removes HTML tags
            titleElement.textContent = text;
        }
    }
    
    function updateSearchResults(visibleCount, searchTerm) {
        // Remove existing no-results message
        const existingMessage = document.querySelector('.no-search-results');
        if (existingMessage) {
            existingMessage.remove();
        }
        
        // Show no results message if needed
        if (visibleCount === 0 && searchTerm !== '') {
            const noResultsDiv = document.createElement('div');
            noResultsDiv.className = 'no-search-results text-center py-5';
            noResultsDiv.innerHTML = `
                <div class="text-muted">
                    <i class="fas fa-search fa-3x mb-3"></i>
                    <h5>No events found</h5>
                    <p>No events match your search for "<strong>${searchTerm}</strong>"</p>
                    <button class="btn btn-outline-primary" onclick="clearSearch()">
                        <i class="fas fa-times me-2"></i>Clear Search
                    </button>
                </div>
            `;
            
            // Insert after the last section or at the beginning if no sections visible
            const container = document.querySelector('.container');
            const lastSection = container.querySelector('.mb-5:last-of-type');
            if (lastSection) {
                lastSection.insertAdjacentElement('afterend', noResultsDiv);
            } else {
                // If no sections are visible, insert after the search section
                const searchSection = document.querySelector('.card.shadow-sm.border-0');
                searchSection.insertAdjacentElement('afterend', noResultsDiv);
            }
        }
        
        // Update search input placeholder with results count
        if (searchTerm !== '') {
            searchInput.placeholder = `Found ${visibleCount} event${visibleCount !== 1 ? 's' : ''}...`;
        } else {
            searchInput.placeholder = 'Search events by name or description...';
        }
    }
    
    // Clear search function
    window.clearSearch = function() {
        searchInput.value = '';
        searchInput.dispatchEvent(new Event('input'));
        searchInput.focus();
    };
    
    // Enhanced keyboard shortcuts
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            clearSearch();
        }
    });
    
    // Filter functionality
    const filterLinks = document.querySelectorAll('[data-filter]');
    
    filterLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const filter = this.dataset.filter;
            
            eventCards.forEach(card => {
                const eventId = card.dataset.eventId;
                // This would need to be enhanced with actual event status data
                card.style.display = '';
            });
        });
    });
    
    // Status filter functionality
    const statusFilter = document.getElementById('statusFilter');
    statusFilter.addEventListener('change', function() {
        const selectedStatus = this.value;
        
        eventCards.forEach(card => {
            const cardStatus = card.dataset.status;
            
            if (!selectedStatus || cardStatus === selectedStatus) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
    
    // Load More functionality for each section
    const loadMoreButtons = document.querySelectorAll('.load-more-btn');
    
    loadMoreButtons.forEach(button => {
        button.addEventListener('click', function() {
            const section = this.dataset.section;
            const targetId = this.dataset.target;
            const containerId = this.dataset.container;
            
            const hiddenContainer = document.getElementById(targetId);
            const mainContainer = document.getElementById(containerId);
            const hiddenCards = hiddenContainer.querySelectorAll('.event-card');
            
            // Show loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
            this.disabled = true;
            
            // Animate the reveal of hidden cards
            setTimeout(() => {
                // Move cards from hidden container to main container
                hiddenCards.forEach((card, index) => {
                    setTimeout(() => {
                        // Clone the card to main container
                        const clonedCard = card.cloneNode(true);
                        clonedCard.style.opacity = '0';
                        clonedCard.style.transform = 'translateY(20px)';
                        mainContainer.appendChild(clonedCard);
                        
                        // Animate the card in
                        setTimeout(() => {
                            clonedCard.style.transition = 'all 0.5s ease';
                            clonedCard.style.opacity = '1';
                            clonedCard.style.transform = 'translateY(0)';
                        }, 50);
                    }, index * 100); // Stagger the animation
                });
                
                // Hide the load more button
                setTimeout(() => {
                    this.style.transition = 'all 0.3s ease';
                    this.style.opacity = '0';
                    this.style.transform = 'scale(0.8)';
                    
                    setTimeout(() => {
                        this.remove();
                    }, 300);
                }, hiddenCards.length * 100 + 200);
                
                // Update section header count
                updateSectionCount(section, hiddenCards.length);
                
            }, 500);
        });
    });
    
    function updateSectionCount(section, addedCount) {
        // Find the section header and update the count text
        let sectionSelector;
        switch(section) {
            case 'live':
                sectionSelector = '#liveEventsContainer';
                break;
            case 'upcoming':
                sectionSelector = '#upcomingEventsContainer';
                break;
            case 'ended':
                sectionSelector = '#endedEventsContainer';
                break;
        }
        
        const sectionContainer = document.querySelector(sectionSelector);
        const sectionHeader = sectionContainer.closest('.mb-5').querySelector('p.text-muted');
        
        if (sectionHeader) {
            // Update the text to remove the "more available" part
            const currentText = sectionHeader.textContent;
            const newText = currentText.replace(/• \d+ more available/, '');
            sectionHeader.textContent = newText.trim();
        }
    }
});

// Reset filters function
function resetFilters() {
    const searchInput = document.getElementById('searchEvents');
    const statusFilter = document.getElementById('statusFilter');
    
    // Clear inputs
    searchInput.value = '';
    statusFilter.value = '';
    
    // Reset placeholder
    searchInput.placeholder = 'Search events by name or description...';
    
    // Remove no-results message
    const noResultsMessage = document.querySelector('.no-search-results');
    if (noResultsMessage) {
        noResultsMessage.remove();
    }
    
    // Show all sections
    const sections = document.querySelectorAll('.mb-5');
    sections.forEach(section => {
        section.style.display = '';
    });
    
    // Show all cards and reset their styles
    document.querySelectorAll('.event-card').forEach(card => {
        card.style.display = '';
        card.style.opacity = '1';
        card.style.transform = 'scale(1)';
        
        // Remove highlights
        const titleElement = card.querySelector('.event-title');
        if (titleElement) {
            const text = titleElement.textContent;
            titleElement.textContent = text;
        }
    });
    
    // Focus search input
    searchInput.focus();
}

// Live countdown timer for all events
function updateCountdowns() {
    const cards = document.querySelectorAll('.event-card');
    
    cards.forEach(card => {
        const status = card.dataset.status;
        
        // Find the countdown stat item (last stat-item in ultra-stats)
        const statsRow = card.querySelector('.ultra-stats .stats-row');
        if (!statsRow) return;
        
        const statItems = statsRow.querySelectorAll('.stat-item');
        if (statItems.length === 0) return;
        
        const countdownItem = statItems[statItems.length - 1]; // Last stat item
        
        // Get dates from data attributes
        const startDateStr = card.dataset.startDate;
        const endDateStr = card.dataset.endDate;
        
        if (!startDateStr || !endDateStr) return;
        
        const now = new Date();
        const startDate = new Date(startDateStr);
        const endDate = new Date(endDateStr);
        
        if (status === 'active') {
            // Calculate time left until end
            const diff = endDate - now;
            const totalHours = Math.floor(diff / (1000 * 60 * 60));
            const totalMinutes = Math.floor(diff / (1000 * 60));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            if (diff < 0) {
                countdownItem.textContent = 'Ended';
                countdownItem.className = 'stat-item text-danger';
            } else if (totalHours < 1) {
                // Less than 1 hour - show minutes and seconds
                countdownItem.textContent = minutes + 'm ' + seconds + 's left';
                countdownItem.className = 'stat-item text-danger';
            } else if (totalHours < 24) {
                // Less than 24 hours - show hours, minutes, seconds
                countdownItem.textContent = hours + 'h ' + minutes + 'm ' + seconds + 's';
                countdownItem.className = 'stat-item text-warning';
            } else {
                // More than 24 hours - show days
                const daysLeft = Math.ceil(totalHours / 24);
                countdownItem.textContent = daysLeft + ' Day' + (daysLeft !== 1 ? 's' : '') + ' Left';
                countdownItem.className = 'stat-item';
            }
        } else if (status === 'upcoming') {
            // Calculate time until start
            const diff = startDate - now;
            const totalHours = Math.floor(diff / (1000 * 60 * 60));
            const totalMinutes = Math.floor(diff / (1000 * 60));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            if (diff < 0) {
                countdownItem.textContent = 'Starting Soon';
                countdownItem.className = 'stat-item text-success';
            } else if (totalHours < 1) {
                // Less than 1 hour - show minutes and seconds
                countdownItem.textContent = minutes + 'm ' + seconds + 's';
                countdownItem.className = 'stat-item text-success';
            } else if (totalHours < 24) {
                // Less than 24 hours - show hours, minutes, seconds
                countdownItem.textContent = hours + 'h ' + minutes + 'm ' + seconds + 's';
                countdownItem.className = 'stat-item text-warning';
            } else {
                // More than 24 hours - show days
                const daysToStart = Math.ceil(totalHours / 24);
                countdownItem.textContent = 'Starts in ' + daysToStart + ' day' + (daysToStart !== 1 ? 's' : '');
                countdownItem.className = 'stat-item text-info';
            }
        }
    });
}

// Wait for DOM to be fully loaded, then start countdown
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        updateCountdowns();
        setInterval(updateCountdowns, 1000); // Update every second
    });
} else {
    // DOM already loaded
    updateCountdowns();
    setInterval(updateCountdowns, 1000); // Update every second
}
</script>

<?php include __DIR__ . '/../layout/public_footer.php'; ?>
