<?php
/**
 * Migration: Update Shortcodes to Random Format
 * 
 * This migration updates all existing shortcodes to use the new random format:
 * - 2 letters + 2 numbers (e.g., AA87, BT14)
 * - Random generation for security
 * - Global uniqueness check
 * 
 * Run this migration after deploying the new shortcode generation system.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Models/ContestantCategory.php';

use SmartCast\Core\Database;
use SmartCast\Models\ContestantCategory;

class ShortcodeMigration
{
    private $db;
    private $contestantCategoryModel;
    private $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // 24 letters (no I/O)
    private $numbers = '0123456789'; // 10 numbers
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->contestantCategoryModel = new ContestantCategory();
    }
    
    /**
     * Run the migration
     */
    public function up()
    {
        echo "Starting shortcode migration to random format...\n";
        
        try {
            // Get all existing shortcodes
            $existingCodes = $this->getExistingShortcodes();
            echo "Found " . count($existingCodes) . " existing shortcodes to update.\n";
            
            if (empty($existingCodes)) {
                echo "No shortcodes to migrate.\n";
                return;
            }
            
            // Start transaction
            $this->db->getConnection()->beginTransaction();
            
            $updated = 0;
            $errors = 0;
            
            foreach ($existingCodes as $record) {
                try {
                    // Generate new random shortcode
                    $newShortcode = $this->generateRandomShortcode();
                    
                    // Update the record
                    $this->updateShortcode($record['id'], $newShortcode);
                    
                    echo "Updated ID {$record['id']}: '{$record['short_code']}' -> '{$newShortcode}'\n";
                    $updated++;
                    
                } catch (Exception $e) {
                    echo "Error updating ID {$record['id']}: " . $e->getMessage() . "\n";
                    $errors++;
                }
            }
            
            // Commit transaction
            $this->db->getConnection()->commit();
            
            echo "\nMigration completed successfully!\n";
            echo "Updated: {$updated} shortcodes\n";
            echo "Errors: {$errors} shortcodes\n";
            
        } catch (Exception $e) {
            // Rollback on error
            $this->db->getConnection()->rollback();
            echo "Migration failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    /**
     * Rollback the migration (restore from backup if needed)
     */
    public function down()
    {
        echo "Rollback not implemented. Please restore from database backup if needed.\n";
        echo "The old shortcodes were replaced with new random ones.\n";
    }
    
    /**
     * Get all existing shortcodes
     */
    private function getExistingShortcodes()
    {
        $sql = "
            SELECT id, short_code, contestant_id, category_id 
            FROM contestant_categories 
            WHERE short_code IS NOT NULL 
            AND short_code != ''
            AND active = 1
            ORDER BY id ASC
        ";
        
        return $this->db->select($sql);
    }
    
    /**
     * Generate a random shortcode in the new format
     */
    private function generateRandomShortcode()
    {
        $maxAttempts = 100;
        $attempts = 0;
        
        do {
            // Generate random 2 letters + 2 numbers format
            $shortCode = '';
            
            // Add 2 random letters
            $shortCode .= $this->letters[random_int(0, strlen($this->letters) - 1)];
            $shortCode .= $this->letters[random_int(0, strlen($this->letters) - 1)];
            
            // Add 2 random numbers
            $shortCode .= $this->numbers[random_int(0, strlen($this->numbers) - 1)];
            $shortCode .= $this->numbers[random_int(0, strlen($this->numbers) - 1)];
            
            $attempts++;
            
            // Check if this code is already taken
            if (!$this->isShortcodeTaken($shortCode)) {
                return strtoupper($shortCode);
            }
            
        } while ($attempts < $maxAttempts);
        
        // If we can't find a unique code, try extended format (3L+2N)
        $attempts = 0;
        do {
            $shortCode = '';
            
            // Add 3 random letters
            $shortCode .= $this->letters[random_int(0, strlen($this->letters) - 1)];
            $shortCode .= $this->letters[random_int(0, strlen($this->letters) - 1)];
            $shortCode .= $this->letters[random_int(0, strlen($this->letters) - 1)];
            
            // Add 2 random numbers
            $shortCode .= $this->numbers[random_int(0, strlen($this->numbers) - 1)];
            $shortCode .= $this->numbers[random_int(0, strlen($this->numbers) - 1)];
            
            $attempts++;
            
        } while ($this->isShortcodeTaken($shortCode) && $attempts < $maxAttempts);
        
        if ($this->isShortcodeTaken($shortCode)) {
            throw new Exception("Could not generate unique shortcode after {$maxAttempts} attempts");
        }
        
        return strtoupper($shortCode);
    }
    
    /**
     * Check if shortcode is already taken
     */
    private function isShortcodeTaken($shortCode)
    {
        $sql = "
            SELECT COUNT(*) as count 
            FROM contestant_categories 
            WHERE short_code = :short_code 
            AND active = 1
        ";
        
        $result = $this->db->selectOne($sql, ['short_code' => $shortCode]);
        return $result['count'] > 0;
    }
    
    /**
     * Update shortcode for a specific record
     */
    private function updateShortcode($id, $newShortcode)
    {
        $sql = "
            UPDATE contestant_categories 
            SET short_code = :short_code, 
                updated_at = NOW()
            WHERE id = :id
        ";
        
        return $this->db->execute($sql, [
            'short_code' => $newShortcode,
            'id' => $id
        ]);
    }
    
    /**
     * Create backup of existing shortcodes
     */
    public function createBackup()
    {
        echo "Creating backup of existing shortcodes...\n";
        
        $backupFile = __DIR__ . '/shortcode_backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        $sql = "
            SELECT id, short_code, contestant_id, category_id, created_at, updated_at
            FROM contestant_categories 
            WHERE short_code IS NOT NULL 
            AND short_code != ''
            AND active = 1
        ";
        
        $records = $this->db->select($sql);
        
        $backupContent = "-- Shortcode Backup - " . date('Y-m-d H:i:s') . "\n";
        $backupContent .= "-- Total records: " . count($records) . "\n\n";
        
        foreach ($records as $record) {
            $backupContent .= "-- ID: {$record['id']}, Shortcode: {$record['short_code']}\n";
            $backupContent .= "UPDATE contestant_categories SET short_code = '{$record['short_code']}' WHERE id = {$record['id']};\n";
        }
        
        file_put_contents($backupFile, $backupContent);
        echo "Backup created: {$backupFile}\n";
        
        return $backupFile;
    }
}

// Run the migration
if (php_sapi_name() === 'cli') {
    echo "=== Shortcode Migration to Random Format ===\n\n";
    
    $migration = new ShortcodeMigration();
    
    // Ask for confirmation
    echo "This will update ALL existing shortcodes to the new random format.\n";
    echo "The old shortcodes will be permanently replaced.\n";
    echo "Do you want to continue? (y/N): ";
    
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim(strtolower($line)) === 'y' || trim(strtolower($line)) === 'yes') {
        // Create backup first
        $migration->createBackup();
        
        // Run migration
        $migration->up();
        
        echo "\nMigration completed! All shortcodes have been updated to the new random format.\n";
    } else {
        echo "Migration cancelled.\n";
    }
} else {
    echo "This migration must be run from the command line.\n";
    echo "Usage: php " . __FILE__ . "\n";
}
?>
