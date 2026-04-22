<?php 
// Set the content for the layout
$content = ob_start(); 
?>

<!-- Shortcode Generation Statistics -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-hashtag me-2"></i>
                        Smart Shortcode Generation System
                    </h4>
                    <p class="text-muted mb-0">Advanced 4-char to 5-char progression system</p>
                </div>
                
                <div class="card-body">
                    <!-- System Overview -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-cogs me-2"></i>
                                        System Configuration
                                    </h5>
                                    <div class="row">
                                        <div class="col-6">
                                            <strong>Format:</strong><br>
                                            <code class="text-white"><?= $stats['format'] ?></code>
                                        </div>
                                        <div class="col-6">
                                            <strong>Generation:</strong><br>
                                            <span class="fs-6"><?= $stats['generation_type'] ?></span>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-6">
                                            <strong>Letters:</strong><br>
                                            <code class="text-white small"><?= $stats['letters'] ?></code>
                                        </div>
                                        <div class="col-6">
                                            <strong>Numbers:</strong><br>
                                            <code class="text-white"><?= $stats['numbers'] ?></code>
                                        </div>
                                    </div>
                                    <hr class="bg-white">
                                    <small>
                                        <i class="fas fa-info-circle me-1"></i>
                                        Excludes I and O to avoid confusion with 1 and 0
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-line me-2"></i>
                                        Current Status
                                    </h5>
                                    <div class="row">
                                        <div class="col-6">
                                            <strong>Mode:</strong><br>
                                            <span class="fs-5"><?= $stats['current_mode'] ?></span>
                                        </div>
                                        <div class="col-6">
                                            <strong>Sample Code:</strong><br>
                                            <code class="text-white fs-5"><?= $stats['sample_codes'][0] ?? 'AA87' ?></code>
                                        </div>
                                    </div>
                                    <hr class="bg-white">
                                    <small>
                                        <i class="fas fa-lightbulb me-1"></i>
                                        Automatically switches to 5-char when 4-char is exhausted
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Capacity Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-layer-group me-2"></i>
                                        Standard Format (2L+2N)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Total Capacity:</span>
                                        <strong><?= number_format($stats['limits']['standard']) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Used:</span>
                                        <strong class="text-primary"><?= number_format($stats['usage']['standard_used']) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span>Remaining:</span>
                                        <strong class="text-success"><?= number_format($stats['remaining']['standard']) ?></strong>
                                    </div>
                                    
                                    <div class="progress mb-2">
                                        <div class="progress-bar bg-primary" 
                                             style="width: <?= $stats['percentages']['standard_used'] ?>%">
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <?= $stats['percentages']['standard_used'] ?>% used
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0">
                                        <i class="fas fa-layer-group me-2"></i>
                                        Extended Format (3L+2N)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Total Capacity:</span>
                                        <strong><?= number_format($stats['limits']['extended']) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Used:</span>
                                        <strong class="text-warning"><?= number_format($stats['usage']['extended_used']) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span>Remaining:</span>
                                        <strong class="text-success"><?= number_format($stats['remaining']['extended']) ?></strong>
                                    </div>
                                    
                                    <div class="progress mb-2">
                                        <div class="progress-bar bg-warning" 
                                             style="width: <?= $stats['percentages']['extended_used'] ?>%">
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <?= $stats['percentages']['extended_used'] ?>% used
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-calculator me-2"></i>
                                        Total System
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Total Capacity:</span>
                                        <strong><?= number_format($stats['limits']['total']) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Used:</span>
                                        <strong class="text-info"><?= number_format($stats['usage']['total_used']) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span>Remaining:</span>
                                        <strong class="text-success"><?= number_format($stats['remaining']['total']) ?></strong>
                                    </div>
                                    
                                    <div class="progress mb-2">
                                        <div class="progress-bar bg-info" 
                                             style="width: <?= min(100, $stats['percentages']['total_used']) ?>%">
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <?= $stats['percentages']['total_used'] ?>% used
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sample Code Generation -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-eye me-2"></i>
                                        Sample Random Shortcodes
                                    </h5>
                                    <small class="text-muted">Examples of randomly generated codes (each generation is unique)</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach ($stats['sample_codes'] as $index => $code): ?>
                                            <div class="col-md-2 col-sm-4 col-6 mb-3">
                                                <div class="text-center p-3 bg-light rounded">
                                                    <div class="fw-bold text-primary">#<?= $index + 1 ?></div>
                                                    <code class="fs-5"><?= $code ?></code>
                                                    <div class="small text-muted"><?= strlen($code) ?> chars</div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Technical Details -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-cog me-2"></i>
                                        How It Works
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <ol class="mb-0">
                                        <li class="mb-2">
                                            <strong>Start with 4-character codes</strong><br>
                                            <small class="text-muted">Uses base-34 conversion for sequential generation</small>
                                        </li>
                                        <li class="mb-2">
                                            <strong>Track usage count</strong><br>
                                            <small class="text-muted">Monitors how many codes have been generated</small>
                                        </li>
                                        <li class="mb-2">
                                            <strong>Auto-switch to 5-character</strong><br>
                                            <small class="text-muted">When 4-char space is exhausted (1,336,336 codes)</small>
                                        </li>
                                        <li class="mb-0">
                                            <strong>Continue with 5-character</strong><br>
                                            <small class="text-muted">Provides 45,435,424 additional unique codes</small>
                                        </li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-shield-alt me-2"></i>
                                        Safety Features
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <ul class="mb-0">
                                        <li class="mb-2">
                                            <strong>Global uniqueness check</strong><br>
                                            <small class="text-muted">Ensures no duplicate codes across all events</small>
                                        </li>
                                        <li class="mb-2">
                                            <strong>Collision detection</strong><br>
                                            <small class="text-muted">Automatically handles conflicts with retry logic</small>
                                        </li>
                                        <li class="mb-2">
                                            <strong>Fallback system</strong><br>
                                            <small class="text-muted">Timestamp-based codes if all else fails</small>
                                        </li>
                                        <li class="mb-0">
                                            <strong>Character exclusion</strong><br>
                                            <small class="text-muted">No I/O characters to avoid 1/0 confusion</small>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <a href="<?= ORGANIZER_URL ?>/migrate-shortcodes" class="btn btn-warning me-2">
                                <i class="fas fa-sync-alt me-2"></i>
                                Migrate Existing Codes
                            </a>
                            <button onclick="window.location.reload()" class="btn btn-primary me-2">
                                <i class="fas fa-refresh me-2"></i>
                                Refresh Statistics
                            </button>
                            <a href="<?= ORGANIZER_URL ?>/events" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to Events
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.progress {
    height: 8px;
}

.card-header {
    border-bottom: 2px solid rgba(0,0,0,0.1);
}

code {
    font-size: 1.1em;
    font-weight: bold;
}

.bg-light {
    border: 1px solid #dee2e6;
}

.text-primary { color: #0d6efd !important; }
.text-warning { color: #fd7e14 !important; }
.text-info { color: #0dcaf0 !important; }
</style>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../../layout/organizer_layout.php';
?>
