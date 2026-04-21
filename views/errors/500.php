<?php include __DIR__ . '/../layout/public_header.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-page py-5">
                <div class="error-icon mb-4">
                    <i class="fas fa-exclamation-triangle fa-5x text-danger"></i>
                </div>
                
                <h1 class="display-1 fw-bold text-danger">500</h1>
                <h2 class="mb-4">Internal Server Error</h2>
                
                <p class="lead text-muted mb-4">
                    Something went wrong on our end. We're working to fix this issue.
                </p>
                
                <?php if (APP_DEBUG && isset($error)): ?>
                    <div class="alert alert-danger text-start mb-4">
                        <h6>Debug Information:</h6>
                        <pre class="mb-0"><?= htmlspecialchars($error) ?></pre>
                    </div>
                <?php endif; ?>
                
                <div class="error-actions">
                    <a href="<?= PUBLIC_URL ?>" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-home me-2"></i>Go Home
                    </a>
                    <button onclick="location.reload()" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-redo me-2"></i>Try Again
                    </button>
                </div>
                
                <div class="mt-5">
                    <h5>What happened?</h5>
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card border-0">
                                <div class="card-body text-center">
                                    <i class="fas fa-server fa-2x text-warning mb-3"></i>
                                    <h6>Server Issue</h6>
                                    <p class="small text-muted">Temporary server problem</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0">
                                <div class="card-body text-center">
                                    <i class="fas fa-tools fa-2x text-info mb-3"></i>
                                    <h6>Under Maintenance</h6>
                                    <p class="small text-muted">System maintenance in progress</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0">
                                <div class="card-body text-center">
                                    <i class="fas fa-clock fa-2x text-success mb-3"></i>
                                    <h6>Temporary</h6>
                                    <p class="small text-muted">Issue will be resolved soon</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-page {
    min-height: 60vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.error-icon {
    opacity: 0.7;
}

.error-actions .btn {
    min-width: 150px;
}

@media (max-width: 768px) {
    .error-actions .btn {
        display: block;
        width: 100%;
        margin-bottom: 1rem;
    }
}

pre {
    font-size: 0.875rem;
    max-height: 200px;
    overflow-y: auto;
}
</style>

<?php include __DIR__ . '/../layout/public_footer.php'; ?>
