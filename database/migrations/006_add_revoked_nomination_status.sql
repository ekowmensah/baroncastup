ALTER TABLE nominations
    MODIFY status ENUM('pending','approved','rejected','revoked') NOT NULL DEFAULT 'pending';
