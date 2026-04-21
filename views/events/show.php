<?php include __DIR__ . '/../layout/public_header.php'; ?>

<!-- Modern Responsive Hero Section -->
<div class="hero-section-redesigned">
    <!-- Background Layer -->
    <div class="hero-background-layer">
        <?php if ($event['featured_image']): ?>
            <div class="hero-bg-image-new" style="background-image: url('<?= htmlspecialchars(image_url($event['featured_image'])) ?>')"></div>
        <?php else: ?>
            <div class="hero-bg-gradient-new"></div>
        <?php endif; ?>
        <div class="hero-overlay-new"></div>
        
        <!-- Animated Background Elements -->
        <div class="hero-particles">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>
    </div>
    
    <!-- Hero Content -->
    <div class="hero-container">
        <div class="hero-content-wrapper">
            <!-- Status Badge -->
            <div class="status-section">
                <?php
                $currentTime = time();
                $startTime = strtotime($event['start_date']);
                $endTime = strtotime($event['end_date']);
                
                if ($endTime < $currentTime || $event['status'] === 'closed') {
                    $statusClass = 'status-completed';
                    $statusText = '<i class="fas fa-flag-checkered"></i><span>Event Completed</span>';
                } elseif ($startTime > $currentTime) {
                    $statusClass = 'status-upcoming';
                    $statusText = '<i class="fas fa-rocket"></i><span>Coming Soon</span>';
                } elseif ($event['status'] === 'active' && $startTime <= $currentTime && $endTime >= $currentTime) {
                    $statusClass = 'status-live';
                    $statusText = '<i class="fas fa-circle pulse-animation"></i><span>Live Now</span>';
                } else {
                    $statusClass = 'status-default';
                    $statusText = '<span>' . ucfirst($event['status']) . '</span>';
                }
                ?>
                <div class="status-badge-new <?= $statusClass ?>">
                    <?= $statusText ?>
                </div>
            </div>
            
            <!-- Main Title -->
            <div class="title-section">
                <h3 class="hero-title-new"><?= htmlspecialchars($event['name']) ?></h3>
                <?php if ($event['description']): ?>
                    <?php
                    $description = htmlspecialchars($event['description']);
                    $charCount = strlen($description);
                    $shortDescription = substr($description, 0, 160);
                    $remainingDescription = substr($description, 160);
                    $needsReadMore = $charCount > 160;
                    ?>
                    
                    <div class="hero-description-container">
                        <p class="hero-description-new" id="eventDescription">
                            <?= nl2br($shortDescription) ?>
                            <?php if ($needsReadMore): ?>
                                <span id="descriptionDots">...</span>
                                <span id="fullDescription" style="display: none;">
                                    <?= nl2br($remainingDescription) ?>
                                </span>
                            <?php endif; ?>
                        </p>
                        
                        <?php if ($needsReadMore): ?>
                            <button id="readMoreBtn" class="read-more-btn" onclick="toggleDescription()">
                                <i class="fas fa-chevron-down me-1"></i>
                                <span class="btn-text">Read More</span>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Stats Grid -->
        <!--    <div class="stats-section">
                <div class="stats-container">
                    <div class="stat-item-new">
                        <div class="stat-icon-new">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number-new"><?= count($contestants) ?></div>
                            <div class="stat-label-new">Contestants</div>
                        </div>
                    </div>
                    
                    <?php if (!empty($categories)): ?>
                    <div class="stat-item-new">
                        <div class="stat-icon-new">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number-new"><?= count($categories) ?></div>
                            <div class="stat-label-new">Categories</div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($event['results_visible'] && !empty($leaderboard)): ?>
                    <div class="stat-item-new">
                        <div class="stat-icon-new">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number-new"><?= number_format(array_sum(array_column($leaderboard, 'total_votes'))) ?></div>
                            <div class="stat-label-new">Total Votes</div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="stat-item-new">
                        <div class="stat-icon-new">
                            <i class="fas fa-hashtag"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number-new"><?= htmlspecialchars($event['code']) ?></div>
                            <div class="stat-label-new">Event Code</div>
                        </div>
                    </div>
                </div>
            </div> -->
            
            <!-- Action Section -->
            <div class="action-section">
                <?php if ($canVote): ?>
                    <div class="cta-container">
                        <?php 
                        $eventSlug = isset($event['slug']) && !empty($event['slug']) ? $event['slug'] : $event['id'];
                        ?>
                        <a href="<?= APP_URL ?>/events/<?= $eventSlug ?>/vote" class="cta-button-new">
                            <span class="cta-icon"><i class="fas fa-vote-yea"></i></span>
                            <span class="cta-text">Start Voting Now</span>
                            <span class="cta-arrow"><i class="fas fa-arrow-right"></i></span>
                        </a>
                        <p class="cta-subtitle">Choose your favorite contestants and cast your votes</p>
                    </div>
                <?php elseif ($startTime > $currentTime): ?>
                    <div class="countdown-container-new">
                        <h3 class="countdown-title-new">Event starts in:</h3>
                        <div class="countdown-display" data-target="<?= date('c', $startTime) ?>">
                            <div class="countdown-unit">
                                <div class="countdown-number-new" id="days">00</div>
                                <div class="countdown-label-new">Days</div>
                            </div>
                            <div class="countdown-separator-new">:</div>
                            <div class="countdown-unit">
                                <div class="countdown-number-new" id="hours">00</div>
                                <div class="countdown-label-new">Hours</div>
                            </div>
                            <div class="countdown-separator-new">:</div>
                            <div class="countdown-unit">
                                <div class="countdown-number-new" id="minutes">00</div>
                                <div class="countdown-label-new">Minutes</div>
                            </div>
                            <div class="countdown-separator-new">:</div>
                            <div class="countdown-unit">
                                <div class="countdown-number-new" id="seconds">00</div>
                                <div class="countdown-label-new">Seconds</div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="event-completed-new">
                        <div class="completed-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h3 class="completed-title">Event Completed</h3>
                        <?php if ($event['results_visible']): ?>
                            <p class="completed-subtitle">Explore the results and see who won!</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Event Details -->
            <div class="event-details-section">
                <div class="event-dates">
                    <div class="date-item-new">
                        <i class="fas fa-calendar-start"></i>
                        <span><strong>Starts:</strong> <?= date('M j, Y \a\t g:i A', $startTime) ?></span>
                    </div>
                    <div class="date-item-new">
                        <i class="fas fa-calendar-times"></i>
                        <span><strong>Ends:</strong> <?= date('M j, Y \a\t g:i A', $endTime) ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
    <!--    <div class="scroll-indicator-new">
            <div class="scroll-mouse">
                <div class="scroll-wheel"></div>
            </div>
            <div class="scroll-text-new">Scroll to explore</div>
        </div> -->
    </div>
</div>
<!-- Main Content Section -->
<div class="main-content-modern">
    <div class="container">
        <!-- Section Navigation -->
        <div class="section-nav mb-5">
            <div class="nav-pills-modern">
                <a href="#contestants" class="nav-pill active" data-section="contestants">
                    <i class="fas fa-users me-2"></i>Contestants
                </a>
                <?php if ($event['results_visible'] && !empty($leaderboard)): ?>
                <a href="#leaderboard" class="nav-pill" data-section="leaderboard">
                    <i class="fas fa-trophy me-2"></i>Leaderboard
                </a>
                <?php endif; ?>
                <?php if ($canVote && !empty($bundles)): ?>
                <a href="#voting" class="nav-pill" data-section="voting">
                    <i class="fas fa-vote-yea me-2"></i>Vote Packages
                </a>
                <?php endif; ?>
                <a href="#info" class="nav-pill" data-section="info">
                    <i class="fas fa-info-circle me-2"></i>Event Info
                </a>
            </div>
        </div>

        <!-- Contestants Section -->
        <div id="contestants" class="content-section active">
            <!-- Search Bar -->
            <?php if (!empty($categories) || !empty($contestants)): ?>
                <div class="search-modern mb-5">
                    <div class="search-wrapper">
                        <i class="fas fa-search search-icon-modern"></i>
                        <input type="text" 
                               id="contestantSearch" 
                               placeholder="Search contestants by name or code..." 
                               class="search-input-modern">
                        <button id="clearSearch" class="clear-search-modern" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="searchResults" class="search-results-modern" style="display: none;">
                        <div class="search-stats-modern"></div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Categories Grid -->
            <?php if (!empty($categories)): ?>
                <div class="categories-grid">
                    <?php foreach ($categories as $index => $category): ?>
                    <div class="category-modern" data-category-index="<?= $index ?>">
                        <div class="category-header-modern">
                            <h3 class="category-title-modern">
                                <?= htmlspecialchars($category['name']) ?>
                            </h3>
                            <span class="category-count-modern">
                                <?php 
                                $categoryContestants = array_filter($contestants, function($c) use ($category) {
                                    return $c['category_id'] == $category['id'];
                                });
                                echo count($categoryContestants);
                                ?> contestants
                            </span>
                        </div>
                        
                        <div class="contestants-grid-modern">
                            <?php foreach ($categoryContestants as $contestant): 
                                // Use category-specific photo if available, otherwise use default
                                $displayImage = (!empty($contestant['category_image_url']) && $contestant['use_category_photo']) 
                                    ? $contestant['category_image_url'] 
                                    : $contestant['image_url'];
                            ?>
                            <div class="contestant-card-modern">
                                <div class="contestant-image-container">
                                    <?php if ($displayImage): ?>
                                        <img src="<?= htmlspecialchars(image_url($displayImage)) ?>" 
                                             alt="<?= htmlspecialchars($contestant['name']) ?>"
                                             class="contestant-image-modern">
                                    <?php elseif (!empty($event['featured_image'])): ?>
                                        <img src="<?= htmlspecialchars(image_url($event['featured_image'])) ?>" 
                                             alt="<?= htmlspecialchars($contestant['name']) ?>"
                                             class="contestant-image-modern"
                                             style="opacity: 0.8; border: 2px solid #667eea;">
                                    <?php else: ?>
                                        <div class="contestant-placeholder-modern">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($contestant['short_code'] ?? $contestant['contestant_code']): ?>
                                        <div class="contestant-code-modern">
                                            <?= htmlspecialchars($contestant['short_code'] ?? $contestant['contestant_code']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="contestant-info-modern">
                                    <h4 class="contestant-name-modern"><?= htmlspecialchars($contestant['name']) ?></h4>
                                    
                                    <?php if ($contestant['bio']): ?>
                                        <p class="contestant-bio-modern">
                                            <?= htmlspecialchars(substr($contestant['bio'], 0, 100)) ?>
                                            <?= strlen($contestant['bio']) > 100 ? '...' : '' ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if ($event['results_visible']): ?>
                                        <div class="contestant-stats-modern">
                                            <div class="stat-item-modern">
                                                <i class="fas fa-heart text-danger"></i>
                                                <span><?= number_format($contestant['total_votes'] ?? 0) ?> votes</span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($canVote): ?>
                                        <?php 
                                        $contestantSlug = isset($contestant['slug']) && !empty($contestant['slug']) 
                                            ? $contestant['slug'] 
                                            : strtolower(str_replace([' ', '.', ',', '&', "'"], ['-', '', '', 'and', ''], $contestant['name']));
                                        
                                        $eventSlug = isset($event['slug']) && !empty($event['slug']) 
                                            ? $event['slug'] 
                                            : $event['id'];
                                            
                                        $voteUrl = APP_URL . '/events/' . $eventSlug . '/vote/' . $contestantSlug . '-' . $contestant['id'] . '?category=' . $category['id'];
                                        ?>
                                        <a href="<?= htmlspecialchars($voteUrl) ?>" class="btn-vote-modern">
                                            <i class="fas fa-vote-yea me-2"></i>Vote
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- All Contestants (if no categories) -->
            <?php if (empty($categories) && !empty($contestants)): ?>
                <div class="category-modern">
                    <div class="category-header-modern">
                        <h3 class="category-title-modern">All Contestants</h3>
                        <span class="category-count-modern"><?= count($contestants) ?> contestants</span>
                    </div>
                    
                    <div class="contestants-grid-modern">
                        <?php foreach ($contestants as $contestant): 
                            // Use category-specific photo if available, otherwise use default
                            $displayImage = (!empty($contestant['category_image_url']) && $contestant['use_category_photo']) 
                                ? $contestant['category_image_url'] 
                                : $contestant['image_url'];
                        ?>
                        <div class="contestant-card-modern">
                            <div class="contestant-image-container">
                                <?php if ($displayImage): ?>
                                    <img src="<?= htmlspecialchars(image_url($displayImage)) ?>" 
                                         alt="<?= htmlspecialchars($contestant['name']) ?>"
                                         class="contestant-image-modern">
                                <?php elseif (!empty($event['featured_image'])): ?>
                                    <img src="<?= htmlspecialchars(image_url($event['featured_image'])) ?>" 
                                         alt="<?= htmlspecialchars($contestant['name']) ?>"
                                         class="contestant-image-modern"
                                         style="opacity: 0.8; border: 2px solid #667eea;">
                                <?php else: ?>
                                    <div class="contestant-placeholder-modern">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($contestant['contestant_code']): ?>
                                    <div class="contestant-code-modern">
                                        <?= htmlspecialchars($contestant['contestant_code']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="contestant-info-modern">
                                <h4 class="contestant-name-modern"><?= htmlspecialchars($contestant['name']) ?></h4>
                                
                                <?php if ($contestant['bio']): ?>
                                    <p class="contestant-bio-modern">
                                        <?= htmlspecialchars(substr($contestant['bio'], 0, 100)) ?>
                                        <?= strlen($contestant['bio']) > 100 ? '...' : '' ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ($event['results_visible']): ?>
                                    <div class="contestant-stats-modern">
                                        <div class="stat-item-modern">
                                            <i class="fas fa-heart text-danger"></i>
                                            <span><?= number_format($contestant['total_votes'] ?? 0) ?> votes</span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($canVote): ?>
                                    <?php 
                                    $contestantSlug = isset($contestant['slug']) && !empty($contestant['slug']) 
                                        ? $contestant['slug'] 
                                        : strtolower(str_replace([' ', '.', ',', '&', "'"], ['-', '', '', 'and', ''], $contestant['name']));
                                    
                                    $eventSlug = isset($event['slug']) && !empty($event['slug']) 
                                        ? $event['slug'] 
                                        : $event['id'];
                                        
                                    $voteUrl = APP_URL . '/events/' . $eventSlug . '/vote/' . $contestantSlug . '-' . $contestant['id'];
                                    // Category ID should be included from the query that loaded contestants
                                    if (!empty($contestant['category_id'])) {
                                        $voteUrl .= '?category=' . $contestant['category_id'];
                                    } elseif (!empty($category['id'])) {
                                        // Fallback to current category context
                                        $voteUrl .= '?category=' . $category['id'];
                                    }
                                    ?>
                                    <a href="<?= htmlspecialchars($voteUrl) ?>" class="btn-vote-modern">
                                        <i class="fas fa-vote-yea me-2"></i>Vote
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Leaderboard Section -->
        <?php if ($event['results_visible'] && !empty($leaderboard)): ?>
        <div id="leaderboard" class="content-section">
            <div class="section-header-modern mb-4">
                <h2><i class="fas fa-trophy me-3"></i>Top Performers</h2>
                <p>Current standings and vote counts</p>
            </div>
            
            <div class="leaderboard-modern">
                <?php foreach ($leaderboard as $index => $leader): 
                // Use category-specific photo if available, otherwise use default
                $leaderImage = (!empty($leader['category_image_url']) && $leader['use_category_photo']) 
                    ? $leader['category_image_url'] 
                    : $leader['image_url'];
            ?>
                <div class="leaderboard-item-modern">
                    <div class="position-modern">
                        <?php if ($index === 0): ?>
                            <i class="fas fa-crown text-warning"></i>
                        <?php elseif ($index === 1): ?>
                            <i class="fas fa-medal text-secondary"></i>
                        <?php elseif ($index === 2): ?>
                            <i class="fas fa-medal text-warning"></i>
                        <?php else: ?>
                            <span class="position-number"><?= $index + 1 ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="leader-avatar-modern">
                        <?php if ($leaderImage): ?>
                            <img src="<?= htmlspecialchars(image_url($leaderImage)) ?>" 
                                 alt="<?= htmlspecialchars($leader['name']) ?>">
                        <?php elseif (!empty($event['featured_image'])): ?>
                            <img src="<?= htmlspecialchars(image_url($event['featured_image'])) ?>" 
                                 alt="<?= htmlspecialchars($leader['name']) ?>"
                                 style="opacity: 0.8; border: 2px solid #667eea;">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="leader-info-modern">
                        <h4><?= htmlspecialchars($leader['name']) ?></h4>
                        <div class="leader-stats">
                            <span class="votes"><?= number_format($leader['total_votes'] ?? 0) ?> votes</span>
                        </div>
                    </div>
                    
                    <div class="leader-progress">
                        <?php 
                        $maxVotes = $leaderboard[0]['total_votes'] ?? 1;
                        $percentage = ($leader['total_votes'] ?? 0) / $maxVotes * 100;
                        ?>
                        <div class="progress-bar-modern">
                            <div class="progress-fill" style="width: <?= $percentage ?>%"></div>
                        </div>
                        <span class="percentage"><?= number_format($percentage, 1) ?>%</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Vote Packages Section -->
        <?php if ($canVote && !empty($bundles)): ?>
        <div id="voting" class="content-section">
            <div class="section-header-modern mb-4">
                <h2><i class="fas fa-vote-yea me-3"></i>Vote Packages</h2>
                <p>Choose your voting package and support your favorite contestants</p>
            </div>
            
            <div class="packages-grid-modern">
                <?php foreach ($bundles as $bundle): ?>
                <div class="package-card-modern">
                    <div class="package-header">
                        <h3><?= htmlspecialchars($bundle['name']) ?></h3>
                        <div class="package-price">
                            <span class="currency">GH₵</span>
                            <span class="amount"><?= number_format($bundle['price'], 2) ?></span>
                        </div>
                    </div>
                    
                    <div class="package-details">
                        <div class="votes-count">
                            <i class="fas fa-heart me-2"></i>
                            <?= $bundle['votes'] ?> vote<?= $bundle['votes'] > 1 ? 's' : '' ?>
                        </div>
                        <div class="price-per-vote">
                            GH₵<?= number_format($bundle['price'] / $bundle['votes'], 2) ?> per vote
                        </div>
                    </div>
                    
                    <a href="<?= APP_URL ?>/events/<?= $event['id'] ?>/vote" class="btn-package-modern">
                        Choose Package
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Event Info Section -->
        <div id="info" class="content-section">
            <div class="section-header-modern mb-4">
                <h2><i class="fas fa-info-circle me-3"></i>Event Information</h2>
                <p>Complete details about this event</p>
            </div>
            
            <div class="info-grid-modern">
                <div class="info-card-modern">
                    <div class="info-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="info-content">
                        <h4>Event Period</h4>
                        <p><strong>Start:</strong> <?= date('M j, Y \a\t g:i A', strtotime($event['start_date'])) ?></p>
                        <p><strong>End:</strong> <?= date('M j, Y \a\t g:i A', strtotime($event['end_date'])) ?></p>
                    </div>
                </div>
                
                <div class="info-card-modern">
                    <div class="info-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="info-content">
                        <h4>Participants</h4>
                        <p><strong>Total Contestants:</strong> <?= count($contestants) ?></p>
                        <?php if (!empty($categories)): ?>
                            <p><strong>Categories:</strong> <?= count($categories) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="info-card-modern">
                    <div class="info-icon">
                        <i class="fas fa-vote-yea"></i>
                    </div>
                    <div class="info-content">
                        <h4>Voting</h4>
                        <p><strong>Vote Packages:</strong> <?= count($bundles) ?></p>
                       <!-- <?php if ($event['results_visible'] && !empty($leaderboard)): ?>
                            <p><strong>Total Votes:</strong> <?= number_format(array_sum(array_column($leaderboard, 'total_votes'))) ?></p>
                        <?php endif; ?> -->
                    </div>
                </div>
                
                <div class="info-card-modern">
                    <div class="info-icon">
                        <i class="fas fa-hashtag"></i>
                    </div>
                    <div class="info-content">
                        <h4>Event Code</h4>
                        <p><code class="event-code-display"><?= htmlspecialchars($event['code']) ?></code></p>
                        <small>Use this code to reference the event</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* New Responsive Hero Section */
.hero-section-redesigned {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    overflow: hidden;
}

.hero-background-layer {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.hero-bg-image-new {
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    filter: brightness(0.4) blur(1px);
}

.hero-bg-gradient-new {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
}

.hero-overlay-new {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.85) 0%, rgba(118, 75, 162, 0.85) 100%);
    z-index: 2;
}

/* Animated Background Particles */
.hero-particles {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 2;
    pointer-events: none;
}

.particle {
    position: absolute;
    width: 4px;
    height: 4px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}

.particle:nth-child(1) { top: 20%; left: 20%; animation-delay: 0s; }
.particle:nth-child(2) { top: 60%; left: 80%; animation-delay: 2s; }
.particle:nth-child(3) { top: 40%; left: 40%; animation-delay: 4s; }
.particle:nth-child(4) { top: 80%; left: 10%; animation-delay: 1s; }
.particle:nth-child(5) { top: 10%; left: 90%; animation-delay: 3s; }

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.3; }
    50% { transform: translateY(-20px) rotate(180deg); opacity: 0.8; }
}

/* Hero Container */
.hero-container {
    position: relative;
    z-index: 3;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    text-align: center;
    color: white;
}

.hero-content-wrapper {
    display: flex;
    flex-direction: column;
    gap: 2rem;
    align-items: center;
}

/* Status Section */
.status-section {
    animation: fadeInUp 0.8s ease-out;
}

.status-badge-new {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 12px 24px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
    backdrop-filter: blur(15px);
    border: 2px solid rgba(255, 255, 255, 0.2);
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.status-live {
    background: rgba(40, 167, 69, 0.9);
    border-color: rgba(40, 167, 69, 0.5);
    box-shadow: 0 0 30px rgba(40, 167, 69, 0.5);
}

.status-upcoming {
    background: rgba(255, 193, 7, 0.9);
    color: #000;
    border-color: rgba(255, 193, 7, 0.5);
    box-shadow: 0 0 30px rgba(255, 193, 7, 0.5);
}

.status-completed {
    background: rgba(220, 53, 69, 0.9);
    border-color: rgba(220, 53, 69, 0.5);
    box-shadow: 0 0 30px rgba(220, 53, 69, 0.5);
}

.pulse-animation {
    animation: pulse-glow 2s infinite;
}

@keyframes pulse-glow {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.7; transform: scale(1.1); }
}

/* Title Section */
.title-section {
    animation: fadeInUp 0.8s ease-out 0.2s both;
}

.hero-title-new {
    font-size: clamp(2.5rem, 8vw, 5rem);
    font-weight: 900;
    line-height: 1.1;
    margin: 0 0 1rem 0;
    text-shadow: 2px 2px 20px rgba(0, 0, 0, 0.8);
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-description-new {
    font-size: clamp(1rem, 3vw, 1.3rem);
    line-height: 1.6;
    opacity: 0.95;
    max-width: 800px;
    margin: 0 auto;
    text-shadow: 1px 1px 10px rgba(0, 0, 0, 0.5);
}

.hero-description-container {
    max-width: 800px;
    margin: 0 auto;
}

.read-more-btn {
    background: rgba(255, 255, 255, 0.1);
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 8px 20px;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    margin-top: 1rem;
    display: inline-flex;
    align-items: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.read-more-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.read-more-btn.expanded {
    background: rgba(220, 53, 69, 0.8);
    border-color: rgba(220, 53, 69, 0.5);
}

.read-more-btn.expanded:hover {
    background: rgba(220, 53, 69, 0.9);
    border-color: rgba(220, 53, 69, 0.7);
}

.read-more-btn i {
    transition: transform 0.3s ease;
}

.read-more-btn:hover i {
    transform: translateY(-1px);
}

/* Stats Section */
.stats-section {
    animation: fadeInUp 0.8s ease-out 0.4s both;
}

.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1.5rem;
    max-width: 800px;
    margin: 0 auto;
}

.stat-item-new {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: rgba(186, 71, 0, 0.84);
    backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 20px;
    transition: all 0.3s ease;
}

.stat-item-new:hover {
    transform: translateY(-5px);
    background: rgba(0, 0, 0, 0.4);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
}

.stat-icon-new {
    width: 50px;
    height: 50px;
    border-radius: 15px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
    color: white;
}

.stat-content {
    text-align: left;
}

.stat-number-new {
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 0.25rem;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.8);
    color: white !important;
}

.stat-label-new {
    font-size: 0.9rem;
    opacity: 0.95;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
    color: white !important;
    text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.6);
}

/* Action Section */
.action-section {
    animation: fadeInUp 0.8s ease-out 0.6s both;
}

.cta-container {
    text-align: center;
}

.cta-button-new {
    display: inline-flex;
    align-items: center;
    gap: 1rem;
    padding: 18px 36px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    text-decoration: none;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 15px 40px rgba(40, 167, 69, 0.4);
    transition: all 0.3s ease;
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.cta-button-new:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 50px rgba(40, 167, 69, 0.6);
    color: white;
}

.cta-icon {
    font-size: 1.2rem;
}

.cta-arrow {
    transition: transform 0.3s ease;
}

.cta-button-new:hover .cta-arrow {
    transform: translateX(5px);
}

.cta-subtitle {
    margin-top: 1rem;
    opacity: 0.8;
    font-size: 1rem;
}

/* Countdown */
.countdown-container-new {
    text-align: center;
}

.countdown-title-new {
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.countdown-display {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.countdown-unit {
    text-align: center;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(15px);
    border-radius: 15px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    min-width: 80px;
}

.countdown-number-new {
    display: block;
    font-size: 2.5rem;
    font-weight: 800;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.6);
}

.countdown-label-new {
    font-size: 0.9rem;
    opacity: 0.8;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: 0.5rem;
}

.countdown-separator-new {
    font-size: 2rem;
    font-weight: 800;
    opacity: 0.6;
}

/* Event Completed */
.event-completed-new {
    text-align: center;
}

.completed-icon {
    font-size: 4rem;
    color: #ffc107;
    margin-bottom: 1rem;
    animation: bounce 2s infinite;
}

.completed-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.completed-subtitle {
    opacity: 0.8;
    font-size: 1.1rem;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    60% { transform: translateY(-5px); }
}

/* Event Details */
.event-details-section {
    animation: fadeInUp 0.8s ease-out 0.8s both;
}

.event-dates {
    display: flex;
    justify-content: center;
    gap: 2rem;
    flex-wrap: wrap;
}

.date-item-new {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(15px);
    border-radius: 50px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    font-size: 0.9rem;
}

/* Scroll Indicator */
.scroll-indicator-new {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    opacity: 0.7;
    animation: fadeInUp 0.8s ease-out 1s both;
}

.scroll-mouse {
    width: 24px;
    height: 40px;
    border: 2px solid rgba(255, 255, 255, 0.5);
    border-radius: 12px;
    margin: 0 auto 0.5rem;
    position: relative;
}

.scroll-wheel {
    width: 4px;
    height: 8px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 2px;
    position: absolute;
    top: 8px;
    left: 50%;
    transform: translateX(-50%);
    animation: scroll-wheel 2s infinite;
}

@keyframes scroll-wheel {
    0% { top: 8px; opacity: 1; }
    100% { top: 24px; opacity: 0; }
}

.scroll-text-new {
    font-size: 0.9rem;
    opacity: 0.8;
}

/* Fade In Animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-container {
        padding: 1rem;
    }
    
    .hero-content-wrapper {
        gap: 1.5rem;
    }
    
    .stats-container {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .stat-item-new {
        padding: 1rem;
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .stat-content {
        text-align: center;
    }
    
    .event-dates {
        flex-direction: column;
        gap: 1rem;
    }
    
    .countdown-display {
        gap: 0.5rem;
    }
    
    .countdown-unit {
        min-width: 60px;
        padding: 0.75rem;
    }
    
    .countdown-number-new {
        font-size: 1.8rem;
    }
    
    .cta-button-new {
        padding: 16px 28px;
        font-size: 1rem;
    }
}

@media (max-width: 480px) {
    .hero-bg-image-new {
        background-attachment: scroll;
    }
    
    .status-badge-new {
        padding: 10px 20px;
        font-size: 0.9rem;
    }
    
    .stat-number-new {
        font-size: 1.5rem;
    }
    
    .completed-icon {
        font-size: 3rem;
    }
}

/* Main Content */
.main-content-modern {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    min-height: 100vh;
    padding: 4rem 0;
}

.section-nav {
    text-align: center;
}

.nav-pills-modern {
    display: inline-flex;
    background: white;
    border-radius: 50px;
    padding: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    gap: 4px;
}

.nav-pill {
    display: flex;
    align-items: center;
    padding: 12px 24px;
    border-radius: 50px;
    text-decoration: none;
    color: #6c757d;
    font-weight: 600;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.nav-pill.active,
.nav-pill:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.content-section {
    display: none;
}

.content-section.active {
    display: block;
}

.section-header-modern {
    text-align: center;
    margin-bottom: 3rem;
}

.section-header-modern h2 {
    font-size: 2.5rem;
    font-weight: 800;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.section-header-modern p {
    font-size: 1.1rem;
    color: #718096;
}

/* Search */
.search-modern {
    max-width: 600px;
    margin: 0 auto;
}

.search-wrapper {
    position: relative;
    background: white;
    border-radius: 50px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.search-input-modern {
    width: 100%;
    padding: 20px 60px 20px 60px;
    border: none;
    font-size: 1.1rem;
    background: transparent;
}

.search-input-modern:focus {
    outline: none;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
}

.search-icon-modern {
    position: absolute;
    left: 25px;
    top: 50%;
    transform: translateY(-50%);
    color: #667eea;
    font-size: 1.2rem;
}

.clear-search-modern {
    position: absolute;
    right: 25px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.clear-search-modern:hover {
    background: #f0f0f0;
    color: #667eea;
}

/* Categories */
.categories-grid {
    display: grid;
    gap: 3rem;
}

.category-modern {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: all 0.3s ease;
}

.category-modern:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
}

.category-header-modern {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    text-align: center;
}

.category-title-modern {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
}

.category-count-modern {
    opacity: 0.9;
    font-weight: 500;
}

.contestants-grid-modern {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
    padding: 2rem;
}

.contestant-card-modern {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.contestant-card-modern:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    border-color: #667eea;
}

.contestant-image-container {
    position: relative;
    padding: 1.5rem 1.5rem 0;
    text-align: center;
}

.contestant-image-modern {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #fff;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.contestant-placeholder-modern {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e9ecef, #f8f9fa);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: #adb5bd;
    border: 4px solid #fff;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    margin: 0 auto;
}

.contestant-code-modern {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.contestant-info-modern {
    padding: 1.5rem;
    text-align: center;
}

.contestant-name-modern {
    font-size: 1.3rem;
    font-weight: 700;
    color: #2d3748;
    margin: 0 0 0.5rem 0;
}

.contestant-bio-modern {
    color: #718096;
    font-size: 0.9rem;
    line-height: 1.5;
    margin: 0 0 1rem 0;
}

.contestant-stats-modern {
    margin: 1rem 0;
}

.stat-item-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #e53e3e;
}

.btn-vote-modern {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 10px 20px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
}

.btn-vote-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-title-modern {
        font-size: 2.5rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .stat-card {
        padding: 1.5rem;
    }
    
    .nav-pills-modern {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .contestants-grid-modern {
        grid-template-columns: 1fr;
        gap: 1.5rem;
        padding: 1.5rem;
    }
    
    .countdown-timer {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .countdown-number-modern {
        font-size: 2rem;
    }
}

/* Leaderboard Styles */
.leaderboard-modern {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.leaderboard-item-modern {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.leaderboard-item-modern:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    border-color: #667eea;
}

.position-modern {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 800;
}

.position-number {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.leader-avatar-modern {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #fff;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.leader-avatar-modern img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #e9ecef, #f8f9fa);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #adb5bd;
    font-size: 1.5rem;
}

.leader-info-modern {
    flex: 1;
}

.leader-info-modern h4 {
    font-size: 1.2rem;
    font-weight: 700;
    color: #2d3748;
    margin: 0 0 0.5rem 0;
}

.leader-stats {
    color: #718096;
    font-size: 0.9rem;
}

.votes {
    color: #e53e3e;
    font-weight: 600;
}

.leader-progress {
    min-width: 150px;
    text-align: right;
}

.progress-bar-modern {
    width: 100%;
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 4px;
    transition: width 0.5s ease;
}

.percentage {
    font-size: 0.9rem;
    font-weight: 600;
    color: #667eea;
}

/* Vote Packages Styles */
.packages-grid-modern {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
}

.package-card-modern {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    position: relative;
}

.package-card-modern:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
    border-color: #28a745;
}

.package-card-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.package-header {
    padding: 2rem 2rem 1rem;
    text-align: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.package-header h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
    margin: 0 0 1rem 0;
}

.package-price {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 0.25rem;
}

.currency {
    font-size: 1.2rem;
    font-weight: 600;
    color: #28a745;
}

.amount {
    font-size: 3rem;
    font-weight: 800;
    color: #28a745;
    line-height: 1;
}

.package-details {
    padding: 1rem 2rem;
    text-align: center;
    border-top: 1px solid #e2e8f0;
}

.votes-count {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.price-per-vote {
    font-size: 0.9rem;
    color: #718096;
}

.btn-package-modern {
    display: block;
    width: calc(100% - 4rem);
    margin: 0 2rem 2rem;
    padding: 1rem;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    text-decoration: none;
    border-radius: 50px;
    font-weight: 700;
    text-align: center;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.btn-package-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
    color: white;
}

/* Event Info Styles */
.info-grid-modern {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.info-card-modern {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 2px solid transparent;
    display: flex;
    gap: 1.5rem;
}

.info-card-modern:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    border-color: #667eea;
}

.info-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.info-content {
    flex: 1;
}

.info-content h4 {
    font-size: 1.3rem;
    font-weight: 700;
    color: #2d3748;
    margin: 0 0 1rem 0;
}

.info-content p {
    color: #718096;
    margin: 0.5rem 0;
    line-height: 1.6;
}

.info-content strong {
    color: #2d3748;
    font-weight: 600;
}

.event-code-display {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 8px 16px;
    border-radius: 8px;
    font-family: 'Courier New', monospace;
    font-weight: 600;
    font-size: 1rem;
}

.info-content small {
    color: #a0aec0;
    font-size: 0.85rem;
}
</style>

<script>
// Modern JavaScript for redesigned page
let searchTimeout;
let allContestants = [];

// Section navigation
function initSectionNavigation() {
    const navPills = document.querySelectorAll('.nav-pill');
    const contentSections = document.querySelectorAll('.content-section');
    
    navPills.forEach(pill => {
        pill.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all pills and sections
            navPills.forEach(p => p.classList.remove('active'));
            contentSections.forEach(s => s.classList.remove('active'));
            
            // Add active class to clicked pill
            this.classList.add('active');
            
            // Show corresponding section
            const targetSection = this.dataset.section;
            const targetElement = document.getElementById(targetSection);
            if (targetElement) {
                targetElement.classList.add('active');
            }
        });
    });
}

// Initialize countdown timer
function initCountdown() {
    const countdownElement = document.querySelector('.countdown-display');
    if (!countdownElement) return;
    
    const targetDate = new Date(countdownElement.dataset.target);
    
    function updateCountdown() {
        const now = new Date().getTime();
        const distance = targetDate.getTime() - now;
        
        if (distance < 0) {
            countdownElement.innerHTML = '<div class="countdown-unit"><div class="countdown-number-new">Event Started!</div></div>';
            return;
        }
        
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        const daysElement = document.getElementById('days');
        const hoursElement = document.getElementById('hours');
        const minutesElement = document.getElementById('minutes');
        const secondsElement = document.getElementById('seconds');
        
        if (daysElement) daysElement.textContent = String(days).padStart(2, '0');
        if (hoursElement) hoursElement.textContent = String(hours).padStart(2, '0');
        if (minutesElement) minutesElement.textContent = String(minutes).padStart(2, '0');
        if (secondsElement) secondsElement.textContent = String(seconds).padStart(2, '0');
    }
    
    updateCountdown();
    setInterval(updateCountdown, 1000); // Update every second
}

// Initialize contestants for search
function initializeContestants() {
    allContestants = [];
    const contestantCards = document.querySelectorAll('.contestant-card-modern');
    
    contestantCards.forEach((card, index) => {
        const nameElement = card.querySelector('.contestant-name-modern');
        const codeElement = card.querySelector('.contestant-code-modern');
        const categoryIndex = card.closest('.category-modern').dataset.categoryIndex || 0;
        
        if (nameElement) {
            allContestants.push({
                index: index,
                categoryIndex: parseInt(categoryIndex),
                name: nameElement.textContent.trim(),
                shortCode: codeElement ? codeElement.textContent.trim() : '',
                element: card,
                nameElement: nameElement,
                codeElement: codeElement
            });
        }
    });
}

// Search functionality
function performSearch(searchTerm) {
    const searchResults = document.getElementById('searchResults');
    const searchStats = document.querySelector('.search-stats-modern');
    
    clearSearchHighlights();
    
    if (!searchTerm.trim()) {
        searchResults.style.display = 'none';
        return;
    }
    
    const matches = allContestants.filter(contestant => {
        return contestant.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
               contestant.shortCode.toLowerCase().includes(searchTerm.toLowerCase());
    });
    
    if (matches.length > 0) {
        searchResults.style.display = 'block';
        searchStats.innerHTML = `<i class="fas fa-search me-2"></i>Found ${matches.length} result${matches.length !== 1 ? 's' : ''} for "${searchTerm}"`;
        
        matches.forEach(match => {
            match.element.classList.add('search-highlight');
            
            if (match.name.toLowerCase().includes(searchTerm.toLowerCase())) {
                highlightText(match.nameElement, searchTerm);
            }
            if (match.shortCode.toLowerCase().includes(searchTerm.toLowerCase())) {
                highlightText(match.codeElement, searchTerm);
            }
        });
        
        // Scroll to first match
        if (matches.length > 0) {
            setTimeout(() => {
                matches[0].element.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }, 300);
        }
    } else {
        searchResults.style.display = 'block';
        searchStats.innerHTML = `<i class="fas fa-search me-2"></i>No results found for "${searchTerm}"`;
    }
}

function highlightText(element, searchTerm) {
    if (!element || !searchTerm) return;
    
    const originalText = element.dataset.originalText || element.textContent;
    if (!element.dataset.originalText) {
        element.dataset.originalText = originalText;
    }
    
    const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
    const highlightedText = originalText.replace(regex, '<span class="search-match">$1</span>');
    element.innerHTML = highlightedText;
}

function removeHighlight(element) {
    if (!element) return;
    
    if (element.dataset.originalText) {
        element.textContent = element.dataset.originalText;
    }
}

function escapeRegex(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function clearSearchHighlights() {
    document.querySelectorAll('.contestant-card-modern.search-highlight').forEach(card => {
        card.classList.remove('search-highlight');
    });
    
    document.querySelectorAll('.contestant-name-modern, .contestant-code-modern').forEach(element => {
        removeHighlight(element);
    });
}

function clearSearch() {
    const searchInput = document.getElementById('contestantSearch');
    const clearButton = document.getElementById('clearSearch');
    const searchResults = document.getElementById('searchResults');
    
    searchInput.value = '';
    clearButton.style.display = 'none';
    searchResults.style.display = 'none';
    
    clearSearchHighlights();
    searchInput.focus();
}

// Toggle description read more/less functionality
function toggleDescription() {
    const dots = document.getElementById('descriptionDots');
    const fullDescription = document.getElementById('fullDescription');
    const readMoreBtn = document.getElementById('readMoreBtn');
    const btnText = readMoreBtn.querySelector('.btn-text');
    const btnIcon = readMoreBtn.querySelector('i');
    
    if (fullDescription.style.display === 'none') {
        // Show full description
        dots.style.display = 'none';
        fullDescription.style.display = 'inline';
        btnText.textContent = 'Read Less';
        btnIcon.className = 'fas fa-chevron-up me-1';
        readMoreBtn.classList.add('expanded');
    } else {
        // Show truncated description
        dots.style.display = 'inline';
        fullDescription.style.display = 'none';
        btnText.textContent = 'Read More';
        btnIcon.className = 'fas fa-chevron-down me-1';
        readMoreBtn.classList.remove('expanded');
    }
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initSectionNavigation();
    initCountdown();
    initializeContestants();
    
    // Search functionality
    const searchInput = document.getElementById('contestantSearch');
    const clearButton = document.getElementById('clearSearch');
    
    if (searchInput && clearButton) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.trim();
            
            clearButton.style.display = searchTerm ? 'block' : 'none';
            
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(searchTerm);
            }, 300);
        });
        
        clearButton.addEventListener('click', clearSearch);
        
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                performSearch(this.value.trim());
            }
        });
    }
    
    // Smooth scroll for navigation
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
});

// Add search highlight styles
const style = document.createElement('style');
style.textContent = `
.search-highlight {
    border-color: #ffc107 !important;
    background: #fff9e6 !important;
    box-shadow: 0 15px 40px rgba(255, 193, 7, 0.3) !important;
}

.search-match {
    background: #ffc107;
    color: #000;
    padding: 2px 4px;
    border-radius: 4px;
    font-weight: 700;
}

.search-results-modern {
    background: white;
    border-radius: 15px;
    padding: 1rem;
    margin-top: 1rem;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

.search-stats-modern {
    color: #667eea;
    font-weight: 600;
}
`;
document.head.appendChild(style);
</script>

<?php include __DIR__ . '/../layout/public_footer.php'; ?>
