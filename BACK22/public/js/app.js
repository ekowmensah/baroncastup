// SmartCast JavaScript Application

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Confirm delete actions
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-delete') || e.target.closest('.btn-delete')) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                // If confirmed, submit the form or follow the link
                var form = e.target.closest('form');
                if (form) {
                    form.submit();
                } else {
                    window.location.href = e.target.href || e.target.closest('a').href;
                }
            }
        }
    });

    // Form validation
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // File upload preview
    document.addEventListener('change', function(e) {
        if (e.target.type === 'file' && e.target.accept && e.target.accept.includes('image')) {
            var file = e.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.getElementById(e.target.dataset.preview || 'image-preview');
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
            }
        }
    });

    // Search functionality
    var searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var searchTerm = this.value.toLowerCase();
            var searchableItems = document.querySelectorAll('.searchable-item');
            
            searchableItems.forEach(function(item) {
                var text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // Real-time vote updates (if on voting page with event data)
    if (document.querySelector('.voting-interface') && document.querySelector('[data-event-id]')) {
        startVoteUpdates();
    }

    // Admin dashboard auto-refresh
    if (document.querySelector('.admin-dashboard')) {
        setInterval(refreshDashboardStats, 30000); // Refresh every 30 seconds
    }
});

// Voting functionality
function selectContestant(contestantId) {
    // Remove previous selections
    document.querySelectorAll('.contestant-card').forEach(function(card) {
        card.classList.remove('selected');
    });
    
    // Select current contestant
    var selectedCard = document.querySelector(`[data-contestant-id="${contestantId}"]`);
    if (selectedCard) {
        selectedCard.classList.add('selected');
    }
    
    // Update hidden input
    var contestantInput = document.getElementById('contestant_id');
    if (contestantInput) {
        contestantInput.value = contestantId;
    }
    
    // Enable vote button
    var voteButton = document.getElementById('vote-button');
    if (voteButton) {
        voteButton.disabled = false;
    }
}

function selectBundle(bundleId, votes, price) {
    // Remove previous selections
    document.querySelectorAll('.bundle-card').forEach(function(card) {
        card.classList.remove('selected');
    });
    
    // Select current bundle
    var selectedCard = document.querySelector(`[data-bundle-id="${bundleId}"]`);
    if (selectedCard) {
        selectedCard.classList.add('selected');
    }
    
    // Update hidden inputs
    var bundleInput = document.getElementById('bundle_id');
    if (bundleInput) {
        bundleInput.value = bundleId;
    }
    
    // Update display
    var voteCountDisplay = document.getElementById('vote-count-display');
    var priceDisplay = document.getElementById('price-display');
    
    if (voteCountDisplay) {
        voteCountDisplay.textContent = votes;
    }
    
    if (priceDisplay) {
        priceDisplay.textContent = `GH₵${price}`;
    }
}

function processVote() {
    var contestantId = document.getElementById('contestant_id').value;
    var bundleId = document.getElementById('bundle_id').value;
    var msisdn = document.getElementById('msisdn').value;
    
    if (!contestantId || !bundleId || !msisdn) {
        alert('Please select a contestant, vote bundle, and enter your phone number.');
        return;
    }
    
    // Show loading state
    var voteButton = document.getElementById('vote-button');
    var originalText = voteButton.innerHTML;
    voteButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    voteButton.disabled = true;
    
    // Submit vote
    var formData = new FormData();
    formData.append('contestant_id', contestantId);
    formData.append('bundle_id', bundleId);
    formData.append('msisdn', msisdn);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showVoteSuccess(data);
        } else {
            showVoteError(data.message || 'Vote failed. Please try again.');
        }
    })
    .catch(error => {
        showVoteError('Network error. Please check your connection and try again.');
    })
    .finally(() => {
        // Restore button state
        voteButton.innerHTML = originalText;
        voteButton.disabled = false;
    });
}

function showVoteSuccess(data) {
    var successHtml = `
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Vote Successful!</strong> Your vote has been recorded.
            ${data.receipt ? `<br><small>Receipt: ${data.receipt}</small>` : ''}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    var alertContainer = document.getElementById('alert-container');
    if (alertContainer) {
        alertContainer.innerHTML = successHtml;
    }
    
    // Reset form
    resetVotingForm();
    
    // Update vote counts if available
    if (data.newVoteCount) {
        updateVoteCount(data.contestantId, data.newVoteCount);
    }
}

function showVoteError(message) {
    var errorHtml = `
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Vote Failed!</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    var alertContainer = document.getElementById('alert-container');
    if (alertContainer) {
        alertContainer.innerHTML = errorHtml;
    }
}

function resetVotingForm() {
    // Clear selections
    document.querySelectorAll('.contestant-card, .bundle-card').forEach(function(card) {
        card.classList.remove('selected');
    });
    
    // Clear inputs
    document.getElementById('contestant_id').value = '';
    document.getElementById('bundle_id').value = '';
    document.getElementById('msisdn').value = '';
    
    // Disable vote button
    document.getElementById('vote-button').disabled = true;
}

function updateVoteCount(contestantId, newCount) {
    var voteCountElement = document.querySelector(`[data-contestant-id="${contestantId}"] .vote-count`);
    if (voteCountElement) {
        voteCountElement.textContent = newCount;
        voteCountElement.classList.add('animate__animated', 'animate__pulse');
    }
}

function startVoteUpdates() {
    // Check if we have the required element before starting
    var initialCheck = document.querySelector('[data-event-id]');
    if (!initialCheck) {
        console.log('No event element found, skipping vote updates');
        return;
    }
    
    // Update vote counts every 10 seconds
    setInterval(function() {
        var eventElement = document.querySelector('[data-event-id]');
        if (!eventElement) {
            console.log('Event element disappeared, stopping updates');
            return;
        }
        
        var eventId = eventElement.dataset.eventId;
        
        fetch(`/api/events/${eventId}/results`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.results) {
                data.results.forEach(function(result) {
                    updateVoteCount(result.contestant_id, result.total_votes);
                });
            }
        })
        .catch(error => {
            console.log('Failed to update vote counts:', error);
        });
    }, 10000);
}

function refreshDashboardStats() {
    // Refresh dashboard statistics
    fetch('/admin/api/stats')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update stat cards
            Object.keys(data.stats).forEach(function(key) {
                var element = document.getElementById(`stat-${key}`);
                if (element) {
                    element.textContent = data.stats[key];
                }
            });
        }
    })
    .catch(error => {
        console.log('Failed to refresh dashboard stats:', error);
    });
}

// Utility functions
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'GHS'
    }).format(amount);
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        var toast = new bootstrap.Toast(document.getElementById('copy-toast'));
        toast.show();
    });
}
