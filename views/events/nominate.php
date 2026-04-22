<?php include __DIR__ . '/../layout/public_header.php'; ?>

<section class="nomination-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="nomination-shell">
                    <div class="row g-0">
                        <div class="col-lg-5">
                            <div class="nomination-event-panel">
                                <?php if (!empty($event['featured_image'])): ?>
                                    <img src="<?= htmlspecialchars(image_url($event['featured_image'])) ?>" alt="<?= htmlspecialchars($event['name']) ?>" class="event-art">
                                <?php else: ?>
                                    <div class="event-art event-art-fallback">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="event-panel-copy">
                                    <span class="eyebrow">Self-nomination</span>
                                    <h1><?= htmlspecialchars($event['name']) ?></h1>
                                    <p><?= htmlspecialchars($event['description'] ?: 'Choose a category and submit your contestant details for organizer review.') ?></p>
                                    <?php if (!empty($event['nomination_end_at'])): ?>
                                        <div class="deadline">
                                            <i class="fas fa-clock"></i>
                                            Closes <?= date('M j, Y g:i A', strtotime($event['nomination_end_at'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="nomination-form-panel">
                                <div class="form-heading">
                                    <a href="<?= APP_URL ?>/events/<?= htmlspecialchars($event['code'] ?: $event['id']) ?>" class="back-link">
                                        <i class="fas fa-arrow-left"></i>
                                        Back to event
                                    </a>
                                    <h2>Nominate Yourself</h2>
                                    <p>Your submission will become a contestant profile after organizer approval.</p>
                                </div>

                                <form method="POST" action="<?= APP_URL ?>/events/<?= htmlspecialchars($event['code'] ?: $event['id']) ?>/nominate" enctype="multipart/form-data">
                                    <?= $csrfField ?? '' ?>
                                    <input type="text" name="website" class="nomination-hp" tabindex="-1" autocomplete="off">

                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category *</label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Choose a category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= (int)$category['id'] ?>">
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="name" class="form-label">Contestant Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" maxlength="255" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="bio" class="form-label">Short Bio</label>
                                        <textarea class="form-control" id="bio" name="bio" rows="4" maxlength="1000" placeholder="Tell voters a little about yourself"></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">Phone</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" maxlength="50">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" maxlength="255">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="photo" class="form-label">Photo</label>
                                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                        <div class="form-text">JPG, PNG, GIF, or WebP. Keep it clear and square if possible.</div>
                                    </div>

                                    <div class="form-check mb-4">
                                        <input class="form-check-input" type="checkbox" id="consent" name="consent" value="1" required>
                                        <label class="form-check-label" for="consent">
                                            I confirm that this information is accurate and I have permission to submit it.
                                        </label>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-paper-plane me-2"></i>
                                        Submit Nomination
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.nomination-page {
    padding: 130px 0 70px;
    background: #f5f7fb;
}

.nomination-shell {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 24px 70px rgba(22, 34, 51, 0.14);
}

.nomination-event-panel {
    min-height: 100%;
    background: #111827;
    color: #fff;
    position: relative;
}

.event-art {
    width: 100%;
    height: 270px;
    object-fit: cover;
    display: block;
}

.event-art-fallback {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #3758f9 0%, #00a3a3 100%);
    font-size: 4rem;
}

.event-panel-copy {
    padding: 2rem;
}

.eyebrow {
    display: inline-block;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0;
    color: #93c5fd;
    font-weight: 700;
    margin-bottom: 0.75rem;
}

.event-panel-copy h1 {
    font-size: 2rem;
    line-height: 1.15;
    font-weight: 800;
    margin-bottom: 1rem;
}

.event-panel-copy p {
    color: rgba(255, 255, 255, 0.78);
}

.deadline {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 1rem;
    padding: 0.65rem 0.85rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
}

.nomination-form-panel {
    padding: 2.2rem;
}

.form-heading {
    margin-bottom: 1.5rem;
}

.form-heading h2 {
    font-weight: 800;
    color: #162033;
}

.form-heading p {
    color: #667085;
    margin-bottom: 0;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    color: #3758f9;
    text-decoration: none;
    font-weight: 700;
    margin-bottom: 1rem;
}

.nomination-hp {
    position: absolute;
    left: -9999px;
    opacity: 0;
}

@media (max-width: 991px) {
    .nomination-page {
        padding-top: 105px;
    }

    .event-art {
        height: 220px;
    }
}
</style>

<?php include __DIR__ . '/../layout/public_footer.php'; ?>
