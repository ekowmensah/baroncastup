<div class="contestant-card" data-contestant-id="<?= $contestant['id'] ?>"
     onclick="selectContestant(<?= $contestant['id'] ?>)">
    
    <div class="contestant-image">
        <?php if ($contestant['image_url']): ?>
            <img src="<?= htmlspecialchars(image_url($contestant['image_url'])) ?>" 
                 alt="<?= htmlspecialchars($contestant['name']) ?>"
                 class="img-fluid">
        <?php else: ?>
            <div class="placeholder-image">
                <i class="fas fa-user"></i>
            </div>
        <?php endif; ?>
        
        <div class="selection-overlay">
            <div class="selection-icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        
        <?php if ($contestant['short_code']): ?>
            <div class="contestant-code">
                <?= htmlspecialchars($contestant['short_code']) ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="contestant-info">
        <h5 class="contestant-name"><?= htmlspecialchars($contestant['name']) ?></h5>
        
        <?php if ($contestant['category_name']): ?>
            <div class="contestant-category">
                <i class="fas fa-tag me-1"></i>
                <?= htmlspecialchars($contestant['category_name']) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($contestant['bio']): ?>
            <p class="contestant-bio">
                <?= htmlspecialchars(substr($contestant['bio'], 0, 100)) ?>
                <?= strlen($contestant['bio']) > 100 ? '...' : '' ?>
            </p>
        <?php endif; ?>
        
        <div class="contestant-stats">
            <div class="stat-item">
                <i class="fas fa-heart text-danger me-1"></i>
                <span><?= number_format($contestant['total_votes'] ?? 0) ?> votes</span>
            </div>
        </div>
    </div>
</div>
