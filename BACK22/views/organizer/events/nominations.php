<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-user-check me-2"></i>
            <?= $event ? 'Self-Nominations' : 'All Self-Nominations' ?>
        </h2>
        <p class="text-muted mb-0">
            <?= $event ? htmlspecialchars($event['name']) : 'Review public nominations across your events' ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($event): ?>
            <a href="<?= APP_URL ?>/events/<?= htmlspecialchars($event['code'] ?: $event['id']) ?>/nominate" class="btn btn-outline-primary" target="_blank">
                <i class="fas fa-external-link-alt me-1"></i>Public Form
            </a>
            <a href="<?= ORGANIZER_URL ?>/events/<?= $event['id'] ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Event
            </a>
        <?php else: ?>
            <a href="<?= ORGANIZER_URL ?>/events" class="btn btn-outline-secondary">
                <i class="fas fa-calendar-alt me-1"></i>Events
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Pending</div>
                        <div class="fs-3 fw-bold"><?= number_format($stats['pending'] ?? 0) ?></div>
                    </div>
                    <i class="fas fa-clock fa-2x text-warning"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Approved</div>
                        <div class="fs-3 fw-bold"><?= number_format($stats['approved'] ?? 0) ?></div>
                    </div>
                    <i class="fas fa-check-circle fa-2x text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-secondary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Revoked</div>
                        <div class="fs-3 fw-bold"><?= number_format($stats['revoked'] ?? 0) ?></div>
                    </div>
                    <i class="fas fa-ban fa-2x text-secondary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-danger h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Rejected</div>
                        <div class="fs-3 fw-bold"><?= number_format($stats['rejected'] ?? 0) ?></div>
                    </div>
                    <i class="fas fa-times-circle fa-2x text-danger"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($event): ?>
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label">Nomination URL</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="nominationUrl" value="<?= APP_URL ?>/events/<?= htmlspecialchars($event['code'] ?: $event['id']) ?>/nominate" readonly>
                        <button type="button" class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText(document.getElementById('nominationUrl').value)">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <span class="badge bg-<?= !empty($event['self_nomination_enabled']) ? 'success' : 'secondary' ?> p-2">
                        <?= !empty($event['self_nomination_enabled']) ? 'Self-nomination enabled' : 'Self-nomination disabled' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Submissions</h5>
        <div class="btn-group btn-group-sm">
            <?php
            $baseUrl = $event ? ORGANIZER_URL . '/events/' . $event['id'] . '/nominations' : ORGANIZER_URL . '/nominations';
            ?>
            <a href="<?= $baseUrl ?>" class="btn btn-outline-secondary <?= empty($selectedStatus) ? 'active' : '' ?>">All</a>
            <a href="<?= $baseUrl ?>?status=pending" class="btn btn-outline-warning <?= $selectedStatus === 'pending' ? 'active' : '' ?>">Pending</a>
            <a href="<?= $baseUrl ?>?status=approved" class="btn btn-outline-success <?= $selectedStatus === 'approved' ? 'active' : '' ?>">Approved</a>
            <a href="<?= $baseUrl ?>?status=revoked" class="btn btn-outline-secondary <?= $selectedStatus === 'revoked' ? 'active' : '' ?>">Revoked</a>
            <a href="<?= $baseUrl ?>?status=rejected" class="btn btn-outline-danger <?= $selectedStatus === 'rejected' ? 'active' : '' ?>">Rejected</a>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($nominations)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <h5>No nominations found</h5>
                <p class="mb-0">When people submit themselves, they will appear here.</p>
            </div>
        <?php else: ?>
            <div class="nomination-list">
                <?php foreach ($nominations as $nomination): ?>
                    <?php
                    $statusClass = [
                        'pending' => 'warning',
                        'approved' => 'success',
                        'revoked' => 'secondary',
                        'rejected' => 'danger'
                    ][$nomination['status']] ?? 'secondary';
                    $currentCategoryValid = !empty($nomination['category_name']);
                    $nominationCategories = $categoriesByEvent[$nomination['event_id']] ?? $categories ?? [];
                    ?>
                    <div class="nomination-item">
                        <div class="nomination-photo">
                            <?php if (!empty($nomination['photo_url'])): ?>
                                <img src="<?= htmlspecialchars(image_url($nomination['photo_url'])) ?>" alt="<?= htmlspecialchars($nomination['name']) ?>">
                            <?php else: ?>
                                <div class="photo-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="nomination-main">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($nomination['name']) ?></h5>
                                    <div class="text-muted small">
                                        <?= htmlspecialchars($nomination['event_name']) ?>
                                        <span class="mx-1">/</span>
                                        <?= htmlspecialchars($nomination['category_name'] ?: ($nomination['category_name_snapshot'] ?: 'Category needs review')) ?>
                                    </div>
                                </div>
                                <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($nomination['status']) ?></span>
                            </div>

                            <?php if (!empty($nomination['bio'])): ?>
                                <p class="mt-3 mb-2"><?= nl2br(htmlspecialchars($nomination['bio'])) ?></p>
                            <?php endif; ?>

                            <div class="row small text-muted g-2 mb-3">
                                <div class="col-md-4">
                                    <i class="fas fa-phone me-1"></i>
                                    <?= htmlspecialchars($nomination['phone'] ?: 'No phone') ?>
                                </div>
                                <div class="col-md-4">
                                    <i class="fas fa-envelope me-1"></i>
                                    <?= htmlspecialchars($nomination['email'] ?: 'No email') ?>
                                </div>
                                <div class="col-md-4">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?= date('M j, Y g:i A', strtotime($nomination['created_at'])) ?>
                                </div>
                            </div>

                            <details class="edit-details mt-3">
                                <summary>Edit contestant details</summary>
                                <form method="POST" action="<?= ORGANIZER_URL ?>/nominations/<?= $nomination['id'] ?>/update" enctype="multipart/form-data" class="edit-nomination-form mt-3">
                                    <?= $csrfField ?? '' ?>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small">Contestant Name *</label>
                                            <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($nomination['name']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">Category *</label>
                                            <select name="category_id" class="form-select form-select-sm" required>
                                                <?php foreach ($nominationCategories as $category): ?>
                                                    <option value="<?= (int)$category['id'] ?>" <?= (int)$category['id'] === (int)$nomination['category_id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($category['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">Phone</label>
                                            <input type="text" name="phone" class="form-control form-control-sm" value="<?= htmlspecialchars($nomination['phone'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">Email</label>
                                            <input type="email" name="email" class="form-control form-control-sm" value="<?= htmlspecialchars($nomination['email'] ?? '') ?>">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small">Bio</label>
                                            <textarea name="bio" class="form-control form-control-sm" rows="3"><?= htmlspecialchars($nomination['bio'] ?? '') ?></textarea>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label small">Replace Photo</label>
                                            <input type="file" name="photo" class="form-control form-control-sm" accept="image/*">
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end">
                                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                                <i class="fas fa-save me-1"></i>Save Details
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </details>

                            <?php if (in_array($nomination['status'], ['rejected', 'revoked'], true)): ?>
                                <div class="alert alert-light border mt-3 mb-0">
                                    <strong><?= $nomination['status'] === 'revoked' ? 'Revoked' : 'Rejected' ?> reason:</strong>
                                    <?= htmlspecialchars($nomination['rejection_reason'] ?: 'No reason provided') ?>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex flex-wrap gap-2 align-items-start mt-3">
                                <?php if ($nomination['status'] === 'approved' && !empty($nomination['contestant_id'])): ?>
                                    <a href="<?= ORGANIZER_URL ?>/contestants/<?= $nomination['contestant_id'] ?>" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-user me-1"></i>View Contestant
                                    </a>
                                    <form method="POST" action="<?= ORGANIZER_URL ?>/nominations/<?= $nomination['id'] ?>/revoke" class="d-flex gap-2">
                                        <?= $csrfField ?? '' ?>
                                        <input type="text" name="reason" class="form-control form-control-sm" placeholder="Revocation reason">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Revoke this approved nomination and hide the contestant?')">
                                            <i class="fas fa-ban me-1"></i>Revoke
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <?php if ($currentCategoryValid || !empty($nominationCategories)): ?>
                                        <form method="POST" action="<?= ORGANIZER_URL ?>/nominations/<?= $nomination['id'] ?>/approve" class="d-flex gap-2 align-items-center">
                                            <?= $csrfField ?? '' ?>
                                            <?php if (!$currentCategoryValid): ?>
                                                <select name="category_id" class="form-select form-select-sm" required>
                                                    <option value="">Choose replacement category</option>
                                                    <?php foreach ($nominationCategories as $category): ?>
                                                        <option value="<?= (int)$category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            <?php endif; ?>
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this nomination and make the contestant live?')">
                                                <i class="fas fa-check me-1"></i><?= $nomination['status'] === 'revoked' ? 'Re-approve' : 'Approve' ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-danger small">Add a valid category before approving.</span>
                                    <?php endif; ?>

                                    <?php if ($nomination['status'] === 'pending'): ?>
                                        <form method="POST" action="<?= ORGANIZER_URL ?>/nominations/<?= $nomination['id'] ?>/reject" class="d-flex gap-2">
                                            <?= $csrfField ?? '' ?>
                                            <input type="text" name="reason" class="form-control form-control-sm" placeholder="Rejection reason">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Reject this nomination?')">
                                                <i class="fas fa-times me-1"></i>Reject
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.nomination-list {
    display: grid;
    gap: 1rem;
}

.nomination-item {
    display: grid;
    grid-template-columns: 96px 1fr;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    background: #fff;
}

.nomination-photo img,
.photo-placeholder {
    width: 96px;
    height: 96px;
    border-radius: 8px;
    object-fit: cover;
}

.photo-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
    color: #9ca3af;
    font-size: 2rem;
}

@media (max-width: 768px) {
    .nomination-item {
        grid-template-columns: 1fr;
    }

    .nomination-photo img,
    .photo-placeholder {
        width: 100%;
        height: 180px;
    }
}
</style>
