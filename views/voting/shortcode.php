<?php include __DIR__ . '/../layout/public_header.php'; ?>

<!-- Hero Section -->
<div class="hero-section bg-gradient-primary text-white py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-3">
                    <i class="fas fa-hashtag me-3"></i>
                    Vote by Shortcode
                </h1>
                <p class="lead mb-4">Enter a nominee's shortcode to quickly find and vote for your favorite contestant!</p>
            </div>
        </div>
    </div>
</div>

<!-- Shortcode Search Section -->
<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-search fa-3x text-primary mb-3"></i>
                        <h3 class="card-title">Find Nominee by Shortcode</h3>
                        <p class="text-muted">Enter the nominee's shortcode to see their details and vote</p>
                    </div>

                    <!-- Search Form -->
                    <form id="shortcodeSearchForm" class="mb-4">
                        <div class="input-group input-group-lg mb-3">
                            <span class="input-group-text bg-primary text-white">
                                <i class="fas fa-hashtag"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   id="shortcodeInput" 
                                   placeholder="Enter shortcode (e.g., ABC123)" 
                                   autocomplete="off"
                                   style="text-transform: uppercase;">
                            <button class="btn btn-primary" type="submit" id="searchBtn">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                        </div>
                        <div class="form-text text-center">
                            <i class="fas fa-info-circle me-1"></i>
                            Shortcodes are usually 3-6 characters long (letters and numbers)
                        </div>
                    </form>

                    <!-- Loading Indicator -->
                    <div id="loadingIndicator" class="text-center d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Searching...</span>
                        </div>
                        <p class="mt-2 text-muted">Searching for nominee...</p>
                    </div>

                    <!-- Search Results -->
                    <div id="searchResults" class="d-none">
                        <!-- Results will be populated here -->
                    </div>

                    <!-- Error Message -->
                    <div id="errorMessage" class="alert alert-danger d-none" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="errorText"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- How It Works Section -->
<div class="container mb-5">
    <div class="row">
        <div class="col-12">
            <div class="card bg-light border-0">
                <div class="card-body p-4">
                    <h4 class="text-center mb-4">
                        <i class="fas fa-question-circle text-primary me-2"></i>
                        How Shortcode Voting Works
                    </h4>
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="step-icon bg-primary text-white rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-hashtag fa-lg"></i>
                            </div>
                            <h6>1. Enter Shortcode</h6>
                            <p class="text-muted small">Type the nominee's unique shortcode in the search box above</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="step-icon bg-success text-white rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-user-check fa-lg"></i>
                            </div>
                            <h6>2. Confirm Nominee</h6>
                            <p class="text-muted small">Review the nominee's details, event, and category information</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="step-icon bg-warning text-white rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-vote-yea fa-lg"></i>
                            </div>
                            <h6>3. Cast Your Vote</h6>
                            <p class="text-muted small">Choose your vote quantity and proceed to payment</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="step-icon bg-info text-white rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-check-circle fa-lg"></i>
                            </div>
                            <h6>4. Vote Confirmed</h6>
                            <p class="text-muted small">Your vote is counted and you'll receive confirmation</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.card {
    border-radius: 15px;
}

.input-group-lg .form-control {
    border-radius: 0 10px 10px 0;
}

.input-group-lg .input-group-text {
    border-radius: 10px 0 0 10px;
}

.input-group-lg .btn {
    border-radius: 0 10px 10px 0;
}

#shortcodeInput {
    font-weight: 600;
    letter-spacing: 1px;
}

.nominee-card {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.nominee-card:hover {
    border-color: #667eea;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
}

.vote-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 25px;
    padding: 12px 30px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.vote-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.step-icon {
    transition: transform 0.3s ease;
}

.step-icon:hover {
    transform: scale(1.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('shortcodeSearchForm');
    const input = document.getElementById('shortcodeInput');
    const searchBtn = document.getElementById('searchBtn');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const searchResults = document.getElementById('searchResults');
    const errorMessage = document.getElementById('errorMessage');
    const errorText = document.getElementById('errorText');

    // Helper function to get proper image URL
    function getImageUrl(imagePath) {
        console.log('getImageUrl input:', imagePath);
        
        if (!imagePath) {
            console.log('getImageUrl: No image path provided');
            return null;
        }
        
        const appUrl = '<?= APP_URL ?>';
        console.log('APP_URL:', appUrl);
        
        // If it's already a full URL, return as is
        if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
            console.log('getImageUrl: Already full URL');
            return imagePath;
        }
        
        // If it starts with APP_URL, return as is
        if (imagePath.startsWith(appUrl)) {
            console.log('getImageUrl: Already has APP_URL');
            return imagePath;
        }
        
        // Clean up the path - remove leading slashes and normalize
        let cleanPath = imagePath.replace(/^\/+/, '');
        
        // If it's a relative path starting with /, add APP_URL
        if (imagePath.startsWith('/')) {
            const result = appUrl + imagePath;
            console.log('getImageUrl: Added APP_URL to absolute path:', result);
            return result;
        }
        
        // Otherwise, assume it's a relative path and add APP_URL with leading slash
        const result = appUrl + '/' + cleanPath;
        console.log('getImageUrl: Added APP_URL to relative path:', result);
        return result;
    }

    // Auto-uppercase input
    input.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        searchNominee();
    });

    function searchNominee() {
        const shortcode = input.value.trim();
        
        if (!shortcode) {
            showError('Please enter a shortcode');
            return;
        }

        if (shortcode.length < 2) {
            showError('Shortcode must be at least 2 characters long');
            return;
        }

        // Show loading
        showLoading();
        hideError();
        hideResults();

        // Make AJAX request
        console.log('Making request to:', '<?= APP_URL ?>/api/shortcode-lookup');
        fetch('<?= APP_URL ?>/api/shortcode-lookup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ shortcode: shortcode })
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            return response.text(); // Get as text first to see what we're getting
        })
        .then(text => {
            console.log('Raw response:', text);
            try {
                const data = JSON.parse(text);
                hideLoading();
                
                if (data.success) {
                    showResults(data.nominee);
                } else {
                    showError(data.message || 'Nominee not found');
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response was:', text);
                hideLoading();
                showError('Server returned invalid response. Check console for details.');
            }
        })
        .catch(error => {
            hideLoading();
            showError('An error occurred while searching. Please try again.');
            console.error('Fetch error:', error);
        });
    }

    function showLoading() {
        loadingIndicator.classList.remove('d-none');
        searchBtn.disabled = true;
        searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Searching...';
    }

    function hideLoading() {
        loadingIndicator.classList.add('d-none');
        searchBtn.disabled = false;
        searchBtn.innerHTML = '<i class="fas fa-search me-2"></i>Search';
    }

    function showResults(nominee) {
        console.log('Nominee data:', nominee);
        console.log('Original image_url:', nominee.image_url);
        
        const processedImageUrl = getImageUrl(nominee.image_url);
        console.log('Processed image_url:', processedImageUrl);
        
        const resultsHtml = `
            <div class="nominee-card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center mb-3 mb-md-0">
                        ${nominee.image_url ? 
                            `<img src="${processedImageUrl}" alt="${nominee.name}" class="img-fluid rounded-circle" style="width: 100px; height: 100px; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                             <div class="bg-light rounded-circle align-items-center justify-content-center" style="width: 100px; height: 100px; margin: 0 auto; display: none;">
                                <i class="fas fa-user fa-2x text-muted"></i>
                             </div>` :
                            `<div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; margin: 0 auto;">
                                <i class="fas fa-user fa-2x text-muted"></i>
                            </div>`
                        }
                    </div>
                    <div class="col-md-6">
                        <h4 class="mb-2">${nominee.name}</h4>
                        <p class="text-muted mb-1">
                            <i class="fas fa-trophy me-2"></i>
                            <strong>Event:</strong> ${nominee.event_name}
                        </p>
                        <p class="text-muted mb-1">
                            <i class="fas fa-tag me-2"></i>
                            <strong>Category:</strong> ${nominee.category_name}
                        </p>
                        <p class="text-muted mb-0">
                            <i class="fas fa-hashtag me-2"></i>
                            <strong>Shortcode:</strong> <span class="badge bg-primary">${nominee.short_code}</span>
                        </p>
                        ${nominee.bio ? `<p class="text-muted mt-2 small">${nominee.bio}</p>` : ''}
                    </div>
                    <div class="col-md-3 text-center">
                        <button class="btn btn-primary vote-btn" onclick='proceedToVote(${JSON.stringify(nominee)})'>
                            <i class="fas fa-vote-yea me-2"></i>
                            Vote Now
                        </button>
                        <p class="text-muted small mt-2">
                            Vote Price: <strong>GH₵${nominee.vote_price}</strong>
                        </p>
                    </div>
                </div>
            </div>
        `;
        
        searchResults.innerHTML = resultsHtml;
        searchResults.classList.remove('d-none');
    }

    function showError(message) {
        errorText.textContent = message;
        errorMessage.classList.remove('d-none');
    }

    function hideError() {
        errorMessage.classList.add('d-none');
    }

    function hideResults() {
        searchResults.classList.add('d-none');
    }

    // Focus on input when page loads
    input.focus();
});

function proceedToVote(nominee) {
    // Generate slugs for the URL
    const contestantSlug = generateSlug(nominee.name) + '-' + nominee.contestant_id;
    const eventSlug = nominee.event_code ? nominee.event_code.toLowerCase() : (generateSlug(nominee.event_name) + '-' + nominee.event_id);
    
    // Redirect to the voting page with proper URL structure
    window.location.href = `<?= APP_URL ?>/events/${eventSlug}/vote/${contestantSlug}?category=${nominee.category_id}`;
}

function generateSlug(string) {
    return string
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .substring(0, 50);
}
</script>

<?php include __DIR__ . '/../layout/public_footer.php'; ?>
