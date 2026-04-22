<?php include __DIR__ . '/../layout/public_header.php'; ?>

<style>
/* Inline CSS for nominee selection */
body { 
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    margin: 0;
    padding: 0;
    background: #f8f9fa;
    color: #333;
}

.nominee-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 15px;
}

.event-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px 15px;
    border-radius: 12px;
    text-align: center;
    margin-bottom: 20px;
}

.event-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 8px;
}

.event-subtitle {
    font-size: 1rem;
    opacity: 0.9;
    margin-bottom: 15px;
}

.event-stats {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 15px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 1.4rem;
    font-weight: 600;
}

.stat-label {
    font-size: 0.8rem;
    opacity: 0.9;
}

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

.nominees-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.nominee-card {
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

.nominee-card:hover {
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    text-decoration: none;
    color: inherit;
}

.nominee-image {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    flex-shrink: 0;
}

.nominee-placeholder {
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

.nominee-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.nominee-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.nominee-code {
    background: #667eea;
    color: white;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 0.75rem;
    display: inline-block;
    font-weight: 500;
    width: fit-content;
}

.nominee-bio {
    font-size: 0.85rem;
    color: #666;
    line-height: 1.3;
    margin: 0;
}

.nominee-meta {
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

.category-badge {
    background: #e9ecef;
    color: #666;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 500;
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
}

.nominee-card:hover .vote-button {
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    margin-bottom: 15px;
    transition: color 0.3s ease;
    font-size: 0.9rem;
}

.back-link:hover {
    color: #5a67d8;
    text-decoration: none;
}

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

/* Highlight styles */
.nominee-card.search-highlight {
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

@media (max-width: 768px) {
    .event-stats {
        flex-direction: column;
        gap: 15px;
    }
    
    .nominee-card {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .nominee-content {
        align-items: center;
    }
    
    .nominee-meta {
        justify-content: center;
    }
    
    .nominee-image, .nominee-placeholder {
        width: 100px;
        height: 100px;
    }
    
    .nominee-placeholder {
        font-size: 2.5rem;
    }
    
    .search-input {
        padding: 10px 40px 10px 40px;
        font-size: 0.9rem;
    }
}
</style>

<div class="nominee-container">
    <!-- Back Link -->
    <a href="<?= APP_URL ?>/events" class="back-link">
        <i class="fas fa-arrow-left"></i>
        Back to Events
    </a>

    <!-- Search Section -->
    <div class="search-container">
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" 
                   id="nomineeSearch" 
                   placeholder="Search by nominee name or shortcode..." 
                   class="search-input">
            <button id="clearSearch" class="clear-search" style="display: none;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="searchResults" class="search-results" style="display: none;">
            <div class="search-stats"></div>
        </div>
    </div>

    <!-- Event Header -->
    <div class="event-header">
        <h1 class="event-title"><?= htmlspecialchars($event['name']) ?></h1>
        <p class="event-subtitle">Choose your favorite nominee to vote for</p>
        <div class="event-stats">
            <div class="stat-item">
                <div class="stat-number"><?= count($contestants) ?></div>
                <div class="stat-label">Nominees</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?= count($contestantsByCategory) ?></div>
                <div class="stat-label">Categories</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?= date('d', strtotime($event['end_date'])) ?></div>
                <div class="stat-label"><?= date('M Y', strtotime($event['end_date'])) ?></div>
            </div>
        </div>
        
        <!-- Results visibility indicator -->
        <div style="margin-top: 15px; text-align: center;">
            <?php if ($event['results_visible']): ?>
                <span class="badge" style="background: #28a745; color: white; padding: 8px 16px; border-radius: 20px;">
                    <i class="fas fa-eye"></i> Results Visible
                </span>
            <?php else: ?>
                <span class="badge" style="background: #6c757d; color: white; padding: 8px 16px; border-radius: 20px;">
                    <i class="fas fa-eye-slash"></i> Results Hidden
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Categories and Nominees -->
    <?php foreach ($contestantsByCategory as $index => $category): ?>
    <div class="category-section" data-category-index="<?= $index ?>">
        <div class="category-header" onclick="toggleCategory(<?= $index ?>)" data-category="<?= $index ?>">
            <div>
                <h2 class="category-title">
                    <?= htmlspecialchars($category['name']) ?>
                </h2>
                <span style="font-size: 0.9rem; opacity: 0.8;">
                    <?= count($category['contestants']) ?> nominees
                </span>
            </div>
            <div class="category-toggle" id="toggle-<?= $index ?>">
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
        
        <div class="category-content" id="content-<?= $index ?>">
            <div class="nominees-list">
                <?php foreach ($category['contestants'] as $contestant): ?>
                <?php
                    // Generate slugs for URLs
                    require_once __DIR__ . '/../../src/Helpers/SlugHelper.php';
                    $eventSlug = \SmartCast\Helpers\SlugHelper::generateEventSlug($event);
                    $contestantSlug = \SmartCast\Helpers\SlugHelper::generateContestantSlug($contestant['name'], $contestant['id']);
                ?>
                <a href="<?= APP_URL ?>/events/<?= $eventSlug ?>/vote/<?= $contestantSlug ?>?category=<?= $category['id'] ?>" class="nominee-card">
                    <!-- Nominee Image -->
                    <?php if ($contestant['image_url']): ?>
                        <img src="<?= htmlspecialchars(image_url($contestant['image_url'])) ?>" 
                             alt="<?= htmlspecialchars($contestant['name']) ?>"
                             class="nominee-image">
                    <?php elseif (!empty($event['featured_image'])): ?>
                        <img src="<?= htmlspecialchars(image_url($event['featured_image'])) ?>" 
                             alt="<?= htmlspecialchars($contestant['name']) ?>"
                             class="nominee-image"
                             style="opacity: 0.8; border: 3px solid #667eea;">
                    <?php else: ?>
                        <div class="nominee-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Nominee Content -->
                    <div class="nominee-content">
                        <div class="nominee-name"><?= htmlspecialchars($contestant['name']) ?></div>
                        
                        <?php if ($contestant['short_code']): ?>
                            <div class="nominee-code"><?= htmlspecialchars($contestant['short_code']) ?></div>
                        <?php endif; ?>
                        
                        <?php if ($contestant['bio']): ?>
                            <div class="nominee-bio">
                                <?= htmlspecialchars(substr($contestant['bio'], 0, 150)) ?>
                                <?= strlen($contestant['bio']) > 150 ? '...' : '' ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="nominee-meta">
                            <div class="vote-stats">
                                <?php if ($event['results_visible']): ?>
                                    <i class="fas fa-heart"></i>
                                    <span><?= number_format($contestant['total_votes'] ?? 0) ?> votes</span>
                                <?php else: ?>
                                    <i class="fas fa-heart" style="color: #ccc;"></i>
                                    <span style="color: #999;">Results Hidden</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Vote Button -->
                    <button class="vote-button">
                        <i class="fas fa-vote-yea"></i>
                        Vote Now
                    </button>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
let searchTimeout;
let allNominees = [];

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
    // If it was already open, it stays closed (all are closed now)
}

function initializeNominees() {
    // Build searchable nominees array
    allNominees = [];
    const nomineeCards = document.querySelectorAll('.nominee-card');
    
    nomineeCards.forEach((card, index) => {
        const nameElement = card.querySelector('.nominee-name');
        const codeElement = card.querySelector('.nominee-code');
        const categoryIndex = card.closest('.category-section').dataset.categoryIndex;
        
        if (nameElement) {
            allNominees.push({
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
    
    const matches = allNominees.filter(nominee => {
        return nominee.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
               nominee.shortCode.toLowerCase().includes(searchTerm.toLowerCase());
    });
    
    console.log('Search term:', searchTerm, 'found', matches.length, 'matches');
    
    if (matches.length > 0) {
        // Show search results
        searchResults.style.display = 'block';
        searchStats.innerHTML = `<i class="fas fa-search me-2"></i>Found ${matches.length} result${matches.length !== 1 ? 's' : ''} for "${searchTerm}"`;
        
        // Group matches by category
        const categoriesWithMatches = new Set();
        console.log('Processing matches:', matches);
        
        matches.forEach(match => {
            // Highlight the nominee card
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
            console.log('Opening category:', categoryIndex); // Debug log
            
            const content = document.getElementById('content-' + categoryIndex);
            const toggle = document.getElementById('toggle-' + categoryIndex);
            const header = document.querySelector(`[data-category="${categoryIndex}"]`);
            
            if (content && toggle) {
                content.classList.remove('collapsed');
                toggle.classList.remove('collapsed');
                console.log('Opened category content and toggle for:', categoryIndex); // Debug log
            } else {
                console.log('Could not find content or toggle for category:', categoryIndex); // Debug log
            }
            
            if (header) {
                header.classList.add('has-results');
                console.log('Added has-results class to header for:', categoryIndex); // Debug log
            } else {
                console.log('Could not find header for category:', categoryIndex); // Debug log
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
    document.querySelectorAll('.nominee-card.search-highlight').forEach(card => {
        card.classList.remove('search-highlight');
    });
    
    // Remove text highlights
    document.querySelectorAll('.nominee-name, .nominee-code').forEach(element => {
        removeHighlight(element);
    });
    
    // Remove category highlights
    document.querySelectorAll('.category-header.has-results').forEach(header => {
        header.classList.remove('has-results');
    });
}

function clearSearch() {
    const searchInput = document.getElementById('nomineeSearch');
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

// Initialize - close all categories except the first one
document.addEventListener('DOMContentLoaded', function() {
    const categories = document.querySelectorAll('.category-content');
    const toggles = document.querySelectorAll('.category-toggle');
    const searchInput = document.getElementById('nomineeSearch');
    const clearButton = document.getElementById('clearSearch');
    
    // Ensure category indices are properly set
    document.querySelectorAll('.category-section').forEach((section, index) => {
        if (!section.dataset.categoryIndex) {
            section.dataset.categoryIndex = index;
        }
        console.log('Category section', index, 'has data-category-index:', section.dataset.categoryIndex);
    });
    
    // Close all categories except the first one
    categories.forEach((content, index) => {
        if (index > 0) {
            content.classList.add('collapsed');
            toggles[index].classList.add('collapsed');
        }
    });
    
    // Initialize nominees data
    initializeNominees();
    console.log('Initialized', allNominees.length, 'nominees for search');
    
    // Search functionality
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
});
</script>

<?php include __DIR__ . '/../layout/public_footer.php'; ?>
