<?php include __DIR__ . '/../layout/public_header.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-page py-5">
                <div class="error-icon mb-4">
                    <i class="fas fa-search fa-5x text-muted"></i>
                </div>
                
                <h1 class="display-1 fw-bold text-primary">404</h1>
                <h2 class="mb-4">Page Not Found</h2>
                
                <p class="lead text-muted mb-4">
                    Sorry, the page you are looking for doesn't exist or has been moved.
                </p>
                
                <div class="error-actions">
                    <a href="<?= PUBLIC_URL ?>" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-home me-2"></i>Go Home
                    </a>
                    <a href="<?= PUBLIC_URL ?>/events" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-calendar me-2"></i>View Events
                    </a>
                </div>
                
                <div class="mt-5">
                    <h5>What can you do?</h5>
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card border-0">
                                <div class="card-body text-center">
                                    <i class="fas fa-home fa-2x text-primary mb-3"></i>
                                    <h6>Go to Homepage</h6>
                                    <p class="small text-muted">Return to the main page</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0">
                                <div class="card-body text-center">
                                    <i class="fas fa-calendar fa-2x text-success mb-3"></i>
                                    <h6>Browse Events</h6>
                                    <p class="small text-muted">Check out active voting events</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0">
                                <div class="card-body text-center">
                                    <i class="fas fa-envelope fa-2x text-info mb-3"></i>
                                    <h6>Contact Support</h6>
                                    <p class="small text-muted">Get help from our team</p>
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
    opacity: 0.5;
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
</style>

<?php include __DIR__ . '/../layout/public_footer.php'; ?>
