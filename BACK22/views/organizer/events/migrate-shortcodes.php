<?php 
$content = ob_start(); 
?>

<!-- Shortcode Migration -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-sync-alt me-2"></i>
                        Migrate Shortcodes to Random Format
                    </h4>
                    <p class="text-muted mb-0">Update existing shortcodes to the new secure random format</p>
                </div>
                
                <div class="card-body">
                    <?php if ($existing_count > 0): ?>
                        <!-- Migration Needed -->
                        <div class="alert alert-warning">
                            <h5 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Migration Required
                            </h5>
                            <p class="mb-0">
                                Found <strong><?= number_format($existing_count) ?></strong> shortcodes that need to be updated to the new random format.
                            </p>
                        </div>
                        
                        <!-- Current vs New Format -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-danger">
                                    <div class="card-header bg-danger text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-times me-2"></i>
                                            Old Format (Insecure)
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Examples:</strong></p>
                                        <ul class="mb-3">
                                            <li><code>T1SA001</code> - Predictable pattern</li>
                                            <li><code>T1SAR002</code> - Sequential numbering</li>
                                            <li><code>EVTSA003</code> - Easy to guess</li>
                                        </ul>
                                        <div class="alert alert-danger mb-0">
                                            <small>
                                                <i class="fas fa-shield-alt me-1"></i>
                                                <strong>Security Risk:</strong> Predictable codes can be guessed by malicious users
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-check me-2"></i>
                                            New Format (Secure)
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Examples:</strong></p>
                                        <ul class="mb-3">
                                            <li><code><?= $stats['sample_codes'][0] ?? 'AA87' ?></code> - Random generation</li>
                                            <li><code><?= $stats['sample_codes'][1] ?? 'BT14' ?></code> - Unpredictable</li>
                                            <li><code><?= $stats['sample_codes'][2] ?? 'MX42' ?></code> - Difficult to guess</li>
                                        </ul>
                                        <div class="alert alert-success mb-0">
                                            <small>
                                                <i class="fas fa-lock me-1"></i>
                                                <strong>Secure:</strong> Random codes are impossible to predict or guess
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Migration Process -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-cogs me-2"></i>
                                    What This Migration Will Do
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-search text-primary me-2"></i>Scan & Identify</h6>
                                        <ul class="mb-3">
                                            <li>Find all existing shortcodes</li>
                                            <li>Identify non-random formats</li>
                                            <li>Count total records to update</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-sync text-warning me-2"></i>Update & Secure</h6>
                                        <ul class="mb-3">
                                            <li>Generate new random codes</li>
                                            <li>Ensure global uniqueness</li>
                                            <li>Update database records</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Important Notes
                                    </h6>
                                    <ul class="mb-0">
                                        <li><strong>Backup:</strong> A backup will be created automatically</li>
                                        <li><strong>Rollback:</strong> Changes can be reverted if needed</li>
                                        <li><strong>Downtime:</strong> No system downtime required</li>
                                        <li><strong>Safety:</strong> Database transaction ensures data integrity</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Migration Actions -->
                        <div class="text-center">
                            <form method="POST" onsubmit="return confirmMigration()">
                                <button type="submit" class="btn btn-success btn-lg me-3">
                                    <i class="fas fa-play me-2"></i>
                                    Start Migration
                                </button>
                                <a href="<?= ORGANIZER_URL ?>/shortcode-stats" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Back to Stats
                                </a>
                            </form>
                        </div>
                        
                    <?php else: ?>
                        <!-- No Migration Needed -->
                        <div class="alert alert-success">
                            <h5 class="alert-heading">
                                <i class="fas fa-check-circle me-2"></i>
                                All Shortcodes Up to Date
                            </h5>
                            <p class="mb-0">
                                All existing shortcodes are already using the secure random format. No migration is needed.
                            </p>
                        </div>
                        
                        <div class="text-center">
                            <a href="<?= ORGANIZER_URL ?>/shortcode-stats" class="btn btn-primary btn-lg">
                                <i class="fas fa-chart-bar me-2"></i>
                                View Statistics
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmMigration() {
    const confirmed = confirm(
        'Are you sure you want to migrate all shortcodes?\n\n' +
        'This will:\n' +
        '• Replace all existing shortcodes with new random ones\n' +
        '• Create a backup of current codes\n' +
        '• Update <?= number_format($existing_count) ?> records\n\n' +
        'This action cannot be easily undone. Continue?'
    );
    
    if (confirmed) {
        // Show loading state
        const button = event.target;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Migrating...';
        button.disabled = true;
    }
    
    return confirmed;
}
</script>

<style>
.card-header {
    border-bottom: 2px solid rgba(0,0,0,0.1);
}

.alert-heading {
    margin-bottom: 0.5rem;
}

code {
    font-size: 1.1em;
    font-weight: bold;
    padding: 2px 6px;
    background-color: rgba(0,0,0,0.1);
    border-radius: 3px;
}

.btn-lg {
    border-radius: 25px;
    padding: 12px 30px;
    font-weight: 600;
}
</style>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../../layout/organizer_layout.php';
?>
