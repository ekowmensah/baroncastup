<?php

namespace SmartCast\Models;

/**
 * ContestantCategory Model
 * Manages many-to-many relationships between contestants and categories
 * Handles USSD shortcodes for voting
 */
class ContestantCategory extends BaseModel
{
    protected $table = 'contestant_categories';
    protected $fillable = [
        'contestant_id', 'category_id', 'short_code', 'display_order', 'active'
    ];
    
    /**
     * Get all contestants in a specific category with their shortcodes
     */
    public function getContestantsByCategory($categoryId)
    {
        $sql = "
            SELECT cc.*, c.name as contestant_name, c.image_url, c.bio,
                   COALESCE(SUM(v.quantity), 0) as total_votes
            FROM contestant_categories cc
            INNER JOIN contestants c ON cc.contestant_id = c.id
            LEFT JOIN votes v ON c.id = v.contestant_id
            WHERE cc.category_id = :category_id AND cc.active = 1 AND c.active = 1
            GROUP BY cc.id
            ORDER BY cc.display_order ASC, total_votes DESC
        ";
        
        return $this->db->select($sql, ['category_id' => $categoryId]);
    }
    
    /**
     * Get all categories for a specific contestant
     */
    public function getCategoriesByContestant($contestantId)
    {
        $sql = "
            SELECT cc.*, cat.name as category_name, cat.description
            FROM contestant_categories cc
            INNER JOIN categories cat ON cc.category_id = cat.id
            WHERE cc.contestant_id = :contestant_id AND cc.active = 1
            ORDER BY cat.display_order ASC
        ";
        
        return $this->db->select($sql, ['contestant_id' => $contestantId]);
    }
    
    /**
     * Find contestant by USSD shortcode within a category
     */
    public function findByShortCode($shortCode, $categoryId = null)
    {
        $sql = "
            SELECT cc.*, 
                   c.name as contestant_name, 
                   c.image_url, 
                   c.event_id,
                   cat.name as category_name,
                   cat.event_id as category_event_id
            FROM contestant_categories cc
            INNER JOIN contestants c ON cc.contestant_id = c.id
            INNER JOIN categories cat ON cc.category_id = cat.id
            WHERE cc.short_code = :short_code AND cc.active = 1 AND c.active = 1
        ";
        
        $params = ['short_code' => $shortCode];
        
        if ($categoryId) {
            $sql .= " AND cc.category_id = :category_id";
            $params['category_id'] = $categoryId;
        }
        
        return $this->db->selectOne($sql, $params);
    }
    
    /**
     * Generate random shortcode in format: 2 letters + 2 numbers (e.g., AA87, BT14)
     * Letters: ABCDEFGHJKLMNPQRSTUVWXYZ (24 chars, no I/O)
     * Numbers: 123456789 (9 chars, no 0)
     * Total combinations: 24² × 9² = 46,656 unique codes
     * Random generation makes codes difficult to guess
     */
    public function generateShortCode($categoryId, $contestantName, $contestantId = null)
    {
        // Character sets (excluding I and O to avoid confusion)
        $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // 24 letters
        $numbers = '123456789'; // 9 numbers (no 0)
        
        $maxAttempts = 100;
        $attempts = 0;
        
        do {
            // Generate random 2 letters + 2 numbers format
            $shortCode = '';
            
            // Add 2 random letters
            $shortCode .= $letters[random_int(0, strlen($letters) - 1)];
            $shortCode .= $letters[random_int(0, strlen($letters) - 1)];
            
            // Add 2 random numbers
            $shortCode .= $numbers[random_int(0, strlen($numbers) - 1)];
            $shortCode .= $numbers[random_int(0, strlen($numbers) - 1)];
            
            $attempts++;
            
            // Check if this code is already taken globally
            if (!$this->isShortCodeTakenGlobally($shortCode)) {
                return strtoupper($shortCode);
            }
            
        } while ($attempts < $maxAttempts);
        
        // If we can't find a unique random code, try with extended format
        $attempts = 0;
        do {
            // Fallback: 3 letters + 2 numbers for more combinations (24³ × 10² = 1,382,400)
            $shortCode = '';
            
            // Add 3 random letters
            $shortCode .= $letters[random_int(0, strlen($letters) - 1)];
            $shortCode .= $letters[random_int(0, strlen($letters) - 1)];
            $shortCode .= $letters[random_int(0, strlen($letters) - 1)];
            
            // Add 2 random numbers
            $shortCode .= $numbers[random_int(0, strlen($numbers) - 1)];
            $shortCode .= $numbers[random_int(0, strlen($numbers) - 1)];
            
            $attempts++;
            
        } while ($this->isShortCodeTakenGlobally($shortCode) && $attempts < $maxAttempts);
        
        // Final fallback with timestamp if all else fails
        if ($this->isShortCodeTakenGlobally($shortCode)) {
            $timestamp = substr(time(), -2);
            $shortCode = $letters[random_int(0, strlen($letters) - 1)] . 
                        $letters[random_int(0, strlen($letters) - 1)] . 
                        $timestamp;
        }
        
        return strtoupper($shortCode);
    }
    
    /**
     * Get shortcode generation statistics for random format
     */
    public function getShortCodeStats()
    {
        $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // 24 letters
        $numbers = '123456789'; // 9 numbers (no 0)
        
        // Calculate limits for different formats
        $standardLimit = pow(24, 2) * pow(9, 2); // 46,656 (2 letters + 2 numbers)
        $extendedLimit = pow(24, 3) * pow(9, 2); // 1,119,744 (3 letters + 2 numbers)
        $totalLimit = $standardLimit + $extendedLimit; // 1,166,400
        
        // Get current counts
        $standardCount = $this->db->selectOne("
            SELECT COUNT(*) as count 
            FROM contestant_categories 
            WHERE short_code IS NOT NULL 
            AND short_code != ''
            AND LENGTH(short_code) = 4
            AND short_code REGEXP '^[ABCDEFGHJKLMNPQRSTUVWXYZ]{2}[0-9]{2}$'
        ")['count'] ?? 0;
        
        $extendedCount = $this->db->selectOne("
            SELECT COUNT(*) as count 
            FROM contestant_categories 
            WHERE short_code IS NOT NULL 
            AND short_code != ''
            AND LENGTH(short_code) = 5
            AND short_code REGEXP '^[ABCDEFGHJKLMNPQRSTUVWXYZ]{3}[0-9]{2}$'
        ")['count'] ?? 0;
        
        $totalUsed = $standardCount + $extendedCount;
        
        return [
            'format' => '2 Letters + 2 Numbers (e.g., AA87, BT14)',
            'letters' => $letters,
            'numbers' => $numbers,
            'limits' => [
                'standard' => $standardLimit,
                'extended' => $extendedLimit,
                'total' => $totalLimit
            ],
            'usage' => [
                'standard_used' => $standardCount,
                'extended_used' => $extendedCount,
                'total_used' => $totalUsed
            ],
            'remaining' => [
                'standard' => max(0, $standardLimit - $standardCount),
                'extended' => max(0, $extendedLimit - $extendedCount),
                'total' => max(0, $totalLimit - $totalUsed)
            ],
            'percentages' => [
                'standard_used' => $standardLimit > 0 ? round(($standardCount / $standardLimit) * 100, 2) : 0,
                'extended_used' => $extendedLimit > 0 ? round(($extendedCount / $extendedLimit) * 100, 4) : 0,
                'total_used' => $totalLimit > 0 ? round(($totalUsed / $totalLimit) * 100, 4) : 0
            ],
            'current_mode' => $standardCount < $standardLimit ? 'Standard (2L+2N)' : 'Extended (3L+2N)',
            'generation_type' => 'Random (Difficult to Guess)',
            'sample_codes' => $this->generateSampleCodes()
        ];
    }
    
    /**
     * Generate sample codes for demonstration
     */
    private function generateSampleCodes($count = 10)
    {
        $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $numbers = '0123456789';
        $samples = [];
        
        for ($i = 0; $i < $count; $i++) {
            $code = '';
            $code .= $letters[random_int(0, strlen($letters) - 1)];
            $code .= $letters[random_int(0, strlen($letters) - 1)];
            $code .= $numbers[random_int(0, strlen($numbers) - 1)];
            $code .= $numbers[random_int(0, strlen($numbers) - 1)];
            $samples[] = $code;
        }
        
        return $samples;
    }
    
    /**
     * Check if shortcode is already taken globally across all tenants and events
     */
    public function isShortCodeTaken($shortCode, $categoryId = null)
    {
        // Check global uniqueness across ALL tenant events
        $sql = "
            SELECT COUNT(*) as count 
            FROM contestant_categories cc
            INNER JOIN categories cat ON cc.category_id = cat.id
            INNER JOIN events e ON cat.event_id = e.id
            WHERE cc.short_code = :short_code AND cc.active = 1
        ";
        
        $params = ['short_code' => $shortCode];
        
        // Optional: exclude current category if updating existing assignment
        if ($categoryId) {
            $sql .= " AND cc.category_id != :category_id";
            $params['category_id'] = $categoryId;
        }
        
        $result = $this->db->selectOne($sql, $params);
        
        return $result['count'] > 0;
    }
    
    /**
     * Check if shortcode is taken globally (simpler version)
     */
    public function isShortCodeTakenGlobally($shortCode)
    {
        $sql = "
            SELECT COUNT(*) as count 
            FROM contestant_categories 
            WHERE short_code = :short_code AND active = 1
        ";
        
        $result = $this->db->selectOne($sql, ['short_code' => $shortCode]);
        
        return $result['count'] > 0;
    }
    
    /**
     * Assign contestant to category with auto-generated unique shortcode
     * Each contestant gets a unique shortcode for each category
     */
    public function assignContestantToCategory($contestantId, $categoryId, $customShortCode = null)
    {
        // Get contestant name for shortcode generation
        $contestant = $this->db->selectOne(
            "SELECT name FROM contestants WHERE id = :id",
            ['id' => $contestantId]
        );
        
        if (!$contestant) {
            throw new \Exception("Contestant not found");
        }
        
        // Check if assignment already exists
        $existing = $this->db->selectOne(
            "SELECT id, short_code FROM contestant_categories WHERE contestant_id = :contestant_id AND category_id = :category_id",
            ['contestant_id' => $contestantId, 'category_id' => $categoryId]
        );
        
        // Generate or validate shortcode
        if ($customShortCode) {
            // Validate custom shortcode is not taken globally (excluding current contestant)
            $sql = "SELECT COUNT(*) as count FROM contestant_categories 
                    WHERE short_code = :short_code AND contestant_id != :contestant_id AND active = 1";
            $result = $this->db->selectOne($sql, [
                'short_code' => $customShortCode,
                'contestant_id' => $contestantId
            ]);
            
            if ($result['count'] > 0) {
                throw new \Exception("Shortcode '{$customShortCode}' is already taken globally across all events");
            }
            $shortCode = strtoupper($customShortCode);
        } else {
            // Generate globally unique shortcode
            $shortCode = $this->generateShortCode($categoryId, $contestant['name'], $contestantId);
        }
        
        if ($existing) {
            // Update existing assignment - preserve existing shortcode unless custom one provided
            $updateData = ['active' => 1];
            if ($customShortCode) {
                // Only update shortcode if custom one is explicitly provided
                $updateData['short_code'] = $shortCode;
            } elseif (!$existing['short_code']) {
                // Generate shortcode only if existing is empty
                $updateData['short_code'] = $shortCode;
            }
            // Otherwise, preserve existing shortcode (don't include in updateData)
            return $this->update($existing['id'], $updateData);
        } else {
            // Create new assignment with unique shortcode
            return $this->create([
                'contestant_id' => $contestantId,
                'category_id' => $categoryId,
                'short_code' => $shortCode,
                'active' => 1
            ]);
        }
    }
    
    /**
     * Remove contestant from category
     */
    public function removeContestantFromCategory($contestantId, $categoryId)
    {
        return $this->db->update(
            'contestant_categories',
            ['active' => 0],
            'contestant_id = :contestant_id AND category_id = :category_id',
            [
                'contestant_id' => $contestantId,
                'category_id' => $categoryId
            ]
        );
    }
    
    /**
     * Get leaderboard for a category
     */
    public function getCategoryLeaderboard($categoryId, $limit = 10)
    {
        $sql = "
            SELECT cc.short_code, c.name as contestant_name, c.image_url,
                   COALESCE(SUM(v.quantity), 0) as total_votes,
                   COUNT(DISTINCT v.id) as vote_count
            FROM contestant_categories cc
            INNER JOIN contestants c ON cc.contestant_id = c.id
            LEFT JOIN votes v ON c.id = v.contestant_id
            WHERE cc.category_id = :category_id AND cc.active = 1 AND c.active = 1
            GROUP BY cc.id
            ORDER BY total_votes DESC, c.name ASC
            LIMIT :limit
        ";
        
        return $this->db->select($sql, [
            'category_id' => $categoryId,
            'limit' => $limit
        ]);
    }
    
    /**
     * Get USSD voting menu for a category
     */
    public function getUSSDMenu($categoryId)
    {
        $sql = "
            SELECT cc.short_code, c.name as contestant_name
            FROM contestant_categories cc
            INNER JOIN contestants c ON cc.contestant_id = c.id
            WHERE cc.category_id = :category_id AND cc.active = 1 AND c.active = 1
            ORDER BY cc.display_order ASC, c.name ASC
        ";
        
        return $this->db->select($sql, ['category_id' => $categoryId]);
    }
    
    /**
     * Get all shortcodes for a specific contestant across all categories
     * This shows how the same nominee has different codes per category
     */
    public function getContestantShortCodes($contestantId)
    {
        $sql = "
            SELECT cc.short_code, cc.category_id, cat.name as category_name,
                   c.name as contestant_name
            FROM contestant_categories cc
            INNER JOIN categories cat ON cc.category_id = cat.id
            INNER JOIN contestants c ON cc.contestant_id = c.id
            WHERE cc.contestant_id = :contestant_id AND cc.active = 1
            ORDER BY cat.name ASC
        ";
        
        return $this->db->select($sql, ['contestant_id' => $contestantId]);
    }
    
    /**
     * Bulk assign contestant to multiple categories with unique shortcodes
     */
    public function assignContestantToMultipleCategories($contestantId, $categoryIds)
    {
        $results = [];
        
        foreach ($categoryIds as $categoryId) {
            try {
                $result = $this->assignContestantToCategory($contestantId, $categoryId);
                $results[] = [
                    'category_id' => $categoryId,
                    'success' => true,
                    'assignment_id' => $result
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'category_id' => $categoryId,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Update category-specific photo for a contestant in a category
     */
    public function updateCategoryPhoto($contestantId, $categoryId, $categoryImageUrl)
    {
        $sql = "
            UPDATE contestant_categories 
            SET category_image_url = :category_image_url,
                use_category_photo = 1
            WHERE contestant_id = :contestant_id AND category_id = :category_id
        ";
        
        return $this->db->query($sql, [
            'category_image_url' => $categoryImageUrl,
            'contestant_id' => $contestantId,
            'category_id' => $categoryId
        ]);
    }
    
    /**
     * Update display order for a specific contestant in a specific category
     */
    public function updateDisplayOrderForCategory($contestantId, $categoryId, $displayOrder)
    {
        error_log("updateDisplayOrderForCategory: contestantId={$contestantId}, categoryId={$categoryId}, displayOrder={$displayOrder}");
        
        // First, check if the record exists
        $checkSql = "SELECT * FROM contestant_categories WHERE contestant_id = :contestant_id AND category_id = :category_id";
        $checkParams = [
            'contestant_id' => $contestantId,
            'category_id' => $categoryId
        ];
        
        error_log("updateDisplayOrderForCategory: Checking if record exists...");
        error_log("updateDisplayOrderForCategory: Check SQL = " . $checkSql);
        error_log("updateDisplayOrderForCategory: Check params = " . json_encode($checkParams));
        
        try {
            $checkStmt = $this->db->query($checkSql, $checkParams);
            $existingRecord = $checkStmt->fetch();
            
            error_log("updateDisplayOrderForCategory: Existing record = " . json_encode($existingRecord));
            
            if (!$existingRecord) {
                error_log("updateDisplayOrderForCategory: No record found! Let's check what records exist for this contestant and category separately...");
                
                // Check what records exist for this contestant
                $contestantRecords = $this->db->query("SELECT * FROM contestant_categories WHERE contestant_id = :contestant_id", ['contestant_id' => $contestantId]);
                error_log("updateDisplayOrderForCategory: Records for contestant {$contestantId}: " . json_encode($contestantRecords->fetchAll()));
                
                // Check what records exist for this category
                $categoryRecords = $this->db->query("SELECT * FROM contestant_categories WHERE category_id = :category_id", ['category_id' => $categoryId]);
                error_log("updateDisplayOrderForCategory: Records for category {$categoryId}: " . json_encode($categoryRecords->fetchAll()));
                
                return false;
            }
            
        } catch (\Exception $e) {
            error_log("updateDisplayOrderForCategory: Error checking existing record: " . $e->getMessage());
            throw $e;
        }
        
        $sql = "
            UPDATE contestant_categories 
            SET display_order = :display_order 
            WHERE contestant_id = :contestant_id AND category_id = :category_id
        ";
        
        $params = [
            'display_order' => $displayOrder,
            'contestant_id' => $contestantId,
            'category_id' => $categoryId
        ];
        
        error_log("updateDisplayOrderForCategory: SQL = " . $sql);
        error_log("updateDisplayOrderForCategory: params = " . json_encode($params));
        
        try {
            $stmt = $this->db->query($sql, $params);
            $rowCount = $stmt->rowCount();
            
            error_log("updateDisplayOrderForCategory: rowCount = " . $rowCount);
            
            // Check if the update was successful OR if the value is already correct
            if ($rowCount > 0) {
                return true; // Update was successful
            } else {
                // Check if the current value is already what we want
                $checkCurrentSql = "SELECT display_order FROM contestant_categories WHERE contestant_id = :contestant_id AND category_id = :category_id";
                $checkCurrentStmt = $this->db->query($checkCurrentSql, [
                    'contestant_id' => $contestantId,
                    'category_id' => $categoryId
                ]);
                $currentRecord = $checkCurrentStmt->fetch();
                
                if ($currentRecord && $currentRecord['display_order'] == $displayOrder) {
                    error_log("updateDisplayOrderForCategory: Value already correct, no update needed");
                    return true; // Value is already correct
                } else {
                    error_log("updateDisplayOrderForCategory: Update failed and value is not correct");
                    return false; // Actual failure
                }
            }
        } catch (\Exception $e) {
            error_log("updateDisplayOrderForCategory: Exception = " . $e->getMessage());
            error_log("updateDisplayOrderForCategory: Stack trace = " . $e->getTraceAsString());
            throw $e;
        }
    }
    
}
