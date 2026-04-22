<?php include __DIR__ . '/../layout/public_header.php'; ?>

<div class="row">
    <!-- Event Details -->
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <?php if ($event['featured_image']): ?>
                <img src="<?= htmlspecialchars(image_url($event['featured_image'])) ?>" 
                     class="card-img-top" 
                     style="height: 300px; object-fit: cover;"
                     alt="<?= htmlspecialchars($event['name']) ?>">
            <?php endif; ?>
            
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h1 class="card-title mb-2"><?= htmlspecialchars($event['name']) ?></h1>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-<?= $event['status'] === 'active' ? 'success' : 'secondary' ?> fs-6">
                                <?= ucfirst($event['status']) ?>
                            </span>
                            <small class="text-muted">
                                <i class="fas fa-money-bill me-1"></i>
                                <?= htmlspecialchars($event['code']) ?>
                            </small>
                        </div>
                    </div>
                    
                    <?php if ($canVote): ?>
                        <a href="<?= APP_URL ?>/events/<?= $event['id'] ?>/vote" 
                           class="btn btn-primary btn-lg">
                            <i class="fas fa-vote-yea me-2"></i>Vote Now
                        </a>
                    <?php endif; ?>
                </div>
                
                <?php if ($event['description']): ?>
                    <p class="card-text"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                <?php endif; ?>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h6><i class="fas fa-calendar-alt text-primary me-2"></i>Event Period</h6>
                        <p class="mb-0">
                            <strong>Start:</strong> <?= date('M j, Y H:i', strtotime($event['start_date'])) ?><br>
                            <strong>End:</strong> <?= date('M j, Y H:i', strtotime($event['end_date'])) ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-info-circle text-info me-2"></i>Voting Status</h6>
                        <p class="mb-0">
                            <?php if ($canVote): ?>
                                <span class="text-success">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Voting is currently open
                                </span>
                            <?php elseif (strtotime($event['start_date']) > time()): ?>
                                <span class="text-warning">
                                    <i class="fas fa-clock me-1"></i>
                                    Voting starts <?= date('M j, Y H:i', strtotime($event['start_date'])) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-danger">
                                    <i class="fas fa-times-circle me-1"></i>
                                    Voting has ended
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Search Section -->
        <?php if (!empty($categories) || !empty($contestants)): ?>
            <div class="search-container">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" 
                           id="contestantSearch" 
                           placeholder="Search by contestant name or shortcode..." 
                           class="search-input">
                    <button id="clearSearch" class="clear-search" style="display: none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="searchResults" class="search-results" style="display: none;">
                    <div class="search-stats"></div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Categories and Contestants -->
        <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $index => $category): ?>
            <div class="category-section" data-category-index="<?= $index ?>">
                <div class="category-header" onclick="toggleCategory(<?= $index ?>)" data-category="<?= $index ?>">
                    <div>
                        <h2 class="category-title">
                            <?= htmlspecialchars($category['name']) ?>
                        </h2>
                        <span style="font-size: 0.9rem; opacity: 0.8;">
                            <?php 
                            $categoryContestants = array_filter($contestants, function($c) use ($category) {
                                return $c['category_id'] == $category['id'];
                            });
                            echo count($categoryContestants);
                            ?> contestants
                        </span>
                    </div>
                    <div class="category-toggle" id="toggle-<?= $index ?>">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
                
                <div class="category-content" id="content-<?= $index ?>">
                    <div class="contestants-list">
                        <?php foreach ($categoryContestants as $contestant): ?>
                        <div class="contestant-card">
                            <!-- Contestant Image -->
                            <?php if ($contestant['image_url']): ?>
                                <img src="<?= htmlspecialchars(image_url($contestant['image_url'])) ?>" 
                                     alt="<?= htmlspecialchars($contestant['name']) ?>"
                                     class="contestant-image">
                            <?php else: ?>
                                <div class="contestant-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Contestant Content -->
                            <div class="contestant-content">
                                <div class="contestant-name"><?= htmlspecialchars($contestant['name']) ?></div>
                                
                                <?php if ($contestant['short_code'] ?? $contestant['contestant_code']): ?>
                                    <div class="contestant-code"><?= htmlspecialchars($contestant['short_code'] ?? $contestant['contestant_code']) ?></div>
                                <?php endif; ?>
                                
                                <?php if ($contestant['bio']): ?>
                                    <div class="contestant-bio">
                                        <?= htmlspecialchars(substr($contestant['bio'], 0, 150)) ?>
                                        <?= strlen($contestant['bio']) > 150 ? '...' : '' ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="contestant-meta">
                                    <?php if ($event['results_visible']): ?>
                                        <div class="vote-stats">
                                            <i class="fas fa-heart"></i>
                                            <span><?= number_format($contestant['total_votes'] ?? 0) ?> votes</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Vote Button -->
                            <?php if ($canVote): ?>
                                <a href="<?= APP_URL ?>/events/<?= $event['id'] ?>/vote" class="vote-button">
                                    <i class="fas fa-vote-yea"></i>
                                    Vote Now
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <!-- All Contestants (if no categories) -->
        <?php if (empty($categories) && !empty($contestants)): ?>
            <div class="category-section" data-category-index="0">
                <div class="category-header" onclick="toggleCategory(0)" data-category="0">
                    <div>
                        <h2 class="category-title">All Contestants</h2>
                        <span style="font-size: 0.9rem; opacity: 0.8;">
                            <?= count($contestants) ?> contestants
                        </span>
                    </div>
                    <div class="category-toggle" id="toggle-0">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
                
                <div class="category-content" id="content-0">
                    <div class="contestants-list">
                        <?php foreach ($contestants as $contestant): ?>
                        <div class="contestant-card">
                            <!-- Contestant Image -->
                            <?php if ($contestant['image_url']): ?>
                                <img src="<?= htmlspecialchars(image_url($contestant['image_url'])) ?>" 
                                     alt="<?= htmlspecialchars($contestant['name']) ?>"
                                     class="contestant-image">
                            <?php else: ?>
                                <div class="contestant-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Contestant Content -->
                            <div class="contestant-content">
                                <div class="contestant-name"><?= htmlspecialchars($contestant['name']) ?></div>
                                
                                <?php if ($contestant['contestant_code']): ?>
                                    <div class="contestant-code"><?= htmlspecialchars($contestant['contestant_code']) ?></div>
                                <?php endif; ?>
                                
                                <?php if ($contestant['bio']): ?>
                                    <div class="contestant-bio">
                                        <?= htmlspecialchars(substr($contestant['bio'], 0, 150)) ?>
                                        <?= strlen($contestant['bio']) > 150 ? '...' : '' ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="contestant-meta">
                                    <?php if ($event['results_visible']): ?>
                                        <div class="vote-stats">
                                            <i class="fas fa-heart"></i>
                                            <span><?= number_format($contestant['total_votes'] ?? 0) ?> votes</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Vote Button -->
                            <?php if ($canVote): ?>
                                <a href="<?= APP_URL ?>/events/<?= $event['id'] ?>/vote" class="vote-button">
                                    <i class="fas fa-vote-yea"></i>
                                    Vote Now
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="sticky-sidebar">
        <!-- Vote Bundles -->
        <?php if ($canVote && !empty($bundles)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-shopping-cart text-success me-2"></i>
                        Vote Packages
                    </h6>
                </div>
                <div class="card-body">
                    <?php foreach ($bundles as $bundle): ?>
                        <div class="d-flex justify-content-between align-items-center p-3 border rounded mb-2">
                            <div>
                                <strong><?= htmlspecialchars($bundle['name']) ?></strong>
                                <br>
                                <small class="text-muted"><?= $bundle['votes'] ?> vote<?= $bundle['votes'] > 1 ? 's' : '' ?></small>
                            </div>
                            <div class="text-end">
                                <div class="h6 mb-0 text-success">$<?= number_format($bundle['price'], 2) ?></div>
                                <small class="text-muted">
                                    $<?= number_format($bundle['price'] / $bundle['votes'], 2) ?>/vote
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <a href="<?= APP_URL ?>/events/<?= $event['id'] ?>/vote" 
                       class="btn btn-success w-100 mt-3">
                        <i class="fas fa-vote-yea me-2"></i>Start Voting
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Leaderboard -->
        <?php if ($event['results_visible'] && !empty($leaderboard)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-trophy text-warning me-2"></i>
                        Leaderboard
                    </h6>
                </div>
                <div class="card-body">
                    <?php foreach (array_slice($leaderboard, 0, 5) as $index => $leader): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="position-badge me-3">
                                <?php if ($index === 0): ?>
                                    <i class="fas fa-crown text-warning"></i>
                                <?php elseif ($index === 1): ?>
                                    <i class="fas fa-medal text-secondary"></i>
                                <?php elseif ($index === 2): ?>
                                    <i class="fas fa-medal text-warning"></i>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark"><?= $index + 1 ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex-grow-1">
                                <div class="fw-bold"><?= htmlspecialchars($leader['name']) ?></div>
                                <small class="text-muted">
                                    GH₵<?= number_format($eventStats['vote_price'], 2) ?> per votes
                                </small>
                            </div>
                            
                            <?php if ($leader['image_url']): ?>
                                <img src="<?= htmlspecialchars(image_url($leader['image_url'])) ?>" 
                                     class="rounded-circle" 
                                     width="40" height="40"
                                     alt="<?= htmlspecialchars($leader['name']) ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($leaderboard) > 5): ?>
                        <button class="btn btn-outline-primary btn-sm w-100" onclick="showFullLeaderboard()">
                            <i class="fas fa-list me-2"></i>View Full Leaderboard
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Event Info -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle text-info me-2"></i>
                    Event Information
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Total Contestants</small>
                    <strong><?= count($contestants) ?></strong>
                </div>
                
                <?php if (!empty($categories)): ?>
                    <div class="mb-3">
                        <small class="text-muted d-block">Categories</small>
                        <strong><?= count($categories) ?></strong>
                    </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <small class="text-muted d-block">Vote Packages</small>
                    <strong><?= count($bundles) ?></strong>
                </div>
                
                <div class="mb-0">
                    <small class="text-muted d-block">Event Code</small>
                    <code><?= htmlspecialchars($event['code']) ?></code>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

<style>
/* Search Styles */
.search-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    overflow: hidden;
}

.search-box {
    position: relative;
    padding: 15px;
}

.search-input {
    width: 100%;
    padding: 12px 45px 12px 45px;
    border: 2px solid #e9ecef;
    border-radius: 25px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.search-input:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-icon {
    position: absolute;
    left: 30px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
    font-size: 1rem;
}

.clear-search {
    position: absolute;
    right: 30px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    padding: 5px;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.clear-search:hover {
    background: #f0f0f0;
    color: #666;
}

.search-results {
    padding: 0 15px 15px;
    border-top: 1px solid #e9ecef;
}

.search-stats {
    padding: 10px 0;
    font-size: 0.9rem;
    color: #666;
}

/* Category Accordion Styles */
.category-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 15px;
    overflow: hidden;
}

.category-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.category-header:hover {
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
}

.category-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin: 0;
    color: white;
}

.category-toggle {
    font-size: 1rem;
    transition: transform 0.3s ease;
}

.category-toggle.collapsed {
    transform: rotate(-90deg);
}

.category-content {
    padding: 20px;
    display: block;
}

.category-content.collapsed {
    display: none;
}

/* Contestant List Styles */
.contestants-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.contestant-card {
    background: #f8f9fa;
    border: 2px solid transparent;
    border-radius: 10px;
    padding: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    color: inherit;
    display: flex;
    align-items: center;
    gap: 15px;
    width: 100%;
}

.contestant-card:hover {
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    text-decoration: none;
    color: inherit;
}

.contestant-image {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    flex-shrink: 0;
}

.contestant-placeholder {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ddd, #f0f0f0);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #999;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    flex-shrink: 0;
}

.contestant-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.contestant-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.contestant-code {
    background: #667eea;
    color: white;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 0.75rem;
    display: inline-block;
    font-weight: 500;
    width: fit-content;
}

.contestant-bio {
    font-size: 0.85rem;
    color: #666;
    line-height: 1.3;
    margin: 0;
}

.contestant-meta {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.vote-stats {
    display: flex;
    align-items: center;
    gap: 4px;
    color: #ff4444;
    font-weight: 500;
    font-size: 0.8rem;
}

.vote-button {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 18px;
    font-size: 0.8rem;
    font-weight: 600;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    flex-shrink: 0;
    min-width: 100px;
    text-decoration: none;
    text-align: center;
}

.contestant-card:hover .vote-button {
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

/* Highlight styles */
.contestant-card.search-highlight {
    border-color: #ffc107 !important;
    background: #fff9e6 !important;
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3) !important;
}

.search-match {
    background: #ffc107;
    color: #000;
    padding: 1px 3px;
    border-radius: 3px;
    font-weight: 600;
}

.category-header.has-results {
    background: linear-gradient(135deg, #ffc107 0%, #ff8f00 100%) !important;
    color: #000 !important;
}

.category-header.has-results .category-title {
    color: #000 !important;
}

/* Sticky Sidebar */
.sticky-sidebar {
    position: sticky;
    top: 20px;
    max-height: calc(100vh - 40px);
    overflow-y: auto;
    padding-right: 5px;
}

/* Custom scrollbar for sidebar */
.sticky-sidebar::-webkit-scrollbar {
    width: 6px;
}

.sticky-sidebar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.sticky-sidebar::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.sticky-sidebar::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Legacy styles for sidebar */
.position-badge {
    width: 30px;
    text-align: center;
}

@media (max-width: 991px) {
    /* Disable sticky on tablets and mobile */
    .sticky-sidebar {
        position: static;
        max-height: none;
        overflow-y: visible;
        padding-right: 0;
    }
}

@media (max-width: 768px) {
    .contestant-card {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .contestant-content {
        align-items: center;
    }
    
    .contestant-meta {
        justify-content: center;
    }
    
    .contestant-image, .contestant-placeholder {
        width: 80px;
        height: 80px;
    }
    
    .search-input {
        padding: 10px 40px 10px 40px;
        font-size: 0.9rem;
    }
}
</style>

<script>
let searchTimeout;
let allContestants = [];

function toggleCategory(index) {
    const allContents = document.querySelectorAll('.category-content');
    const allToggles = document.querySelectorAll('.category-toggle');
    const currentContent = document.getElementById('content-' + index);
    const currentToggle = document.getElementById('toggle-' + index);
    
    // Check if current category is already open
    const isCurrentOpen = !currentContent.classList.contains('collapsed');
    
    // Close all categories first
    allContents.forEach(content => {
        content.classList.add('collapsed');
    });
    allToggles.forEach(toggle => {
        toggle.classList.add('collapsed');
    });
    
    // If the clicked category was closed, open it
    if (!isCurrentOpen) {
        currentContent.classList.remove('collapsed');
        currentToggle.classList.remove('collapsed');
    }
}

function initializeContestants() {
    // Build searchable contestants array
    allContestants = [];
    const contestantCards = document.querySelectorAll('.contestant-card');
    
    contestantCards.forEach((card, index) => {
        const nameElement = card.querySelector('.contestant-name');
        const codeElement = card.querySelector('.contestant-code');
        const categoryIndex = card.closest('.category-section').dataset.categoryIndex;
        
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

function performSearch(searchTerm) {
    const searchResults = document.getElementById('searchResults');
    const searchStats = document.querySelector('.search-stats');
    
    // Clear previous highlights
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
        // Show search results
        searchResults.style.display = 'block';
        searchStats.innerHTML = `<i class="fas fa-search me-2"></i>Found ${matches.length} result${matches.length !== 1 ? 's' : ''} for "${searchTerm}"`;
        
        // Group matches by category
        const categoriesWithMatches = new Set();
        
        matches.forEach(match => {
            // Highlight the contestant card
            match.element.classList.add('search-highlight');
            
            // Highlight matching text
            if (match.name.toLowerCase().includes(searchTerm.toLowerCase())) {
                highlightText(match.nameElement, searchTerm);
            }
            if (match.shortCode.toLowerCase().includes(searchTerm.toLowerCase())) {
                highlightText(match.codeElement, searchTerm);
            }
            
            categoriesWithMatches.add(match.categoryIndex);
        });
        
        // Close all categories first
        const allContents = document.querySelectorAll('.category-content');
        const allToggles = document.querySelectorAll('.category-toggle');
        const allHeaders = document.querySelectorAll('.category-header');
        
        allContents.forEach(content => content.classList.add('collapsed'));
        allToggles.forEach(toggle => toggle.classList.add('collapsed'));
        allHeaders.forEach(header => header.classList.remove('has-results'));
        
        // Open categories with matches and highlight headers
        categoriesWithMatches.forEach(categoryIndex => {
            const content = document.getElementById('content-' + categoryIndex);
            const toggle = document.getElementById('toggle-' + categoryIndex);
            const header = document.querySelector(`[data-category="${categoryIndex}"]`);
            
            if (content && toggle) {
                content.classList.remove('collapsed');
                toggle.classList.remove('collapsed');
            }
            if (header) {
                header.classList.add('has-results');
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
        // No matches found
        searchResults.style.display = 'block';
        searchStats.innerHTML = `<i class="fas fa-search me-2"></i>No results found for "${searchTerm}"`;
    }
}

function clearSearchHighlights() {
    // Remove highlight classes
    document.querySelectorAll('.contestant-card.search-highlight').forEach(card => {
        card.classList.remove('search-highlight');
    });
    
    // Remove text highlights
    document.querySelectorAll('.contestant-name, .contestant-code').forEach(element => {
        removeHighlight(element);
    });
    
    // Remove category highlights
    document.querySelectorAll('.category-header.has-results').forEach(header => {
        header.classList.remove('has-results');
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
    
    // Reset to default state (first category open)
    const categories = document.querySelectorAll('.category-content');
    const toggles = document.querySelectorAll('.category-toggle');
    
    categories.forEach((content, index) => {
        if (index === 0) {
            content.classList.remove('collapsed');
            toggles[index].classList.remove('collapsed');
        } else {
            content.classList.add('collapsed');
            toggles[index].classList.add('collapsed');
        }
    });
    
    searchInput.focus();
}

function showFullLeaderboard() {
    // This would show a modal with the full leaderboard
    alert('Full leaderboard feature would be implemented here');
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    const categories = document.querySelectorAll('.category-content');
    const toggles = document.querySelectorAll('.category-toggle');
    const searchInput = document.getElementById('contestantSearch');
    const clearButton = document.getElementById('clearSearch');
    
    // Ensure category indices are properly set
    document.querySelectorAll('.category-section').forEach((section, index) => {
        if (!section.dataset.categoryIndex) {
            section.dataset.categoryIndex = index;
        }
    });
    
    // Close all categories except the first one
    categories.forEach((content, index) => {
        if (index > 0) {
            content.classList.add('collapsed');
            toggles[index].classList.add('collapsed');
        }
    });
    
    // Initialize contestants data
    initializeContestants();
    
    // Search functionality
    if (searchInput && clearButton) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.trim();
            
            // Show/hide clear button
            clearButton.style.display = searchTerm ? 'block' : 'none';
            
            // Debounce search
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(searchTerm);
            }, 300);
        });
        
        // Clear search functionality
        clearButton.addEventListener('click', clearSearch);
        
        // Enter key to search
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                performSearch(this.value.trim());
            }
        });
    }
});

// Auto-refresh leaderboard if results are visible
<?php if ($event['results_visible']): ?>
setInterval(function() {
    // This would refresh the leaderboard via AJAX
    console.log('Refreshing leaderboard...');
}, 30000); // Every 30 seconds
<?php endif; ?>
</script>

<?php include __DIR__ . '/../layout/public_footer.php'; ?>
