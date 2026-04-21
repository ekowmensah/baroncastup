<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

use SmartCast\Core\Database;

/**
 * Migration: Add Payout Approval Workflow Columns
 * Adds columns needed for the new payout approval process
 */

try {
    $db = new Database();
    
    echo "Adding payout approval workflow columns...\n";
    
    // Add new columns to payouts table
    $alterQueries = [
        // Add approval workflow columns
        "ALTER TABLE payouts ADD COLUMN IF NOT EXISTS requested_at TIMESTAMP NULL DEFAULT NULL COMMENT 'When payout was requested by organizer'",
        
        "ALTER TABLE payouts ADD COLUMN IF NOT EXISTS approved_by INT NULL DEFAULT NULL COMMENT 'Super admin who approved the payout'",
        "ALTER TABLE payouts ADD COLUMN IF NOT EXISTS approved_at TIMESTAMP NULL DEFAULT NULL COMMENT 'When payout was approved'",
        "ALTER TABLE payouts ADD COLUMN IF NOT EXISTS admin_notes TEXT NULL DEFAULT NULL COMMENT 'Admin notes during approval'",
        
        "ALTER TABLE payouts ADD COLUMN IF NOT EXISTS rejected_by INT NULL DEFAULT NULL COMMENT 'Super admin who rejected the payout'",
        "ALTER TABLE payouts ADD COLUMN IF NOT EXISTS rejected_at TIMESTAMP NULL DEFAULT NULL COMMENT 'When payout was rejected'",
        "ALTER TABLE payouts ADD COLUMN IF NOT EXISTS rejection_reason TEXT NULL DEFAULT NULL COMMENT 'Reason for rejection'",
        
        "ALTER TABLE payouts ADD COLUMN IF NOT EXISTS cancelled_at TIMESTAMP NULL DEFAULT NULL COMMENT 'When payout was cancelled'",
        
        // Add foreign key constraints
        "ALTER TABLE payouts ADD CONSTRAINT IF NOT EXISTS fk_payouts_approved_by 
         FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL",
         
        "ALTER TABLE payouts ADD CONSTRAINT IF NOT EXISTS fk_payouts_rejected_by 
         FOREIGN KEY (rejected_by) REFERENCES users(id) ON DELETE SET NULL",
    ];
    
    foreach ($alterQueries as $query) {
        try {
            $db->query($query);
            echo "✓ Executed: " . substr($query, 0, 80) . "...\n";
        } catch (Exception $e) {
            echo "⚠ Warning: " . $e->getMessage() . "\n";
        }
    }
    
    // Update existing payouts to use new status system
    echo "\nUpdating existing payout statuses...\n";
    
    $statusUpdates = [
        "UPDATE payouts SET status = 'pending' WHERE status = 'queued'",
        "UPDATE payouts SET status = 'paid' WHERE status = 'success'",
        "UPDATE payouts SET requested_at = created_at WHERE requested_at IS NULL",
        "UPDATE payouts SET approved_at = processed_at WHERE status = 'paid' AND approved_at IS NULL",
    ];
    
    foreach ($statusUpdates as $query) {
        try {
            $result = $db->query($query);
            echo "✓ Updated records: $query\n";
        } catch (Exception $e) {
            echo "⚠ Warning: " . $e->getMessage() . "\n";
        }
    }
    
    // Add indexes for better performance
    echo "\nAdding indexes...\n";
    
    $indexQueries = [
        "CREATE INDEX IF NOT EXISTS idx_payouts_status_requested ON payouts(status, requested_at)",
        "CREATE INDEX IF NOT EXISTS idx_payouts_approved_by ON payouts(approved_by)",
        "CREATE INDEX IF NOT EXISTS idx_payouts_rejected_by ON payouts(rejected_by)",
    ];
    
    foreach ($indexQueries as $query) {
        try {
            $db->query($query);
            echo "✓ Created index: " . substr($query, 0, 60) . "...\n";
        } catch (Exception $e) {
            echo "⚠ Warning: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n✅ Payout approval workflow migration completed successfully!\n";
    echo "\nNew payout workflow:\n";
    echo "1. Organizer requests payout → STATUS: 'pending'\n";
    echo "2. Super admin approves → STATUS: 'approved'\n";
    echo "3. System processes payout → STATUS: 'processing' → 'paid'\n";
    echo "4. Or admin rejects → STATUS: 'rejected'\n\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
