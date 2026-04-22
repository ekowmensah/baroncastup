<?php

namespace SmartCast\Models;

/**
 * Vote Model
 */
class Vote extends BaseModel
{
    protected $table = 'votes';
    protected $fillable = [
        'transaction_id', 'tenant_id', 'event_id', 'contestant_id', 'category_id', 'quantity'
    ];
    
    public function castVote($transactionId, $tenantId, $eventId, $contestantId, $categoryId, $quantity = 1)
    {
        // Ensure quantity is within reasonable bounds
        $originalQuantity = intval($quantity);
        $quantity = max(1, min(10000, $originalQuantity));
        
        error_log("VOTE DEBUG - Original quantity: " . $originalQuantity . ", Bounded quantity: " . $quantity);
        
        $data = [
            'transaction_id' => $transactionId,
            'tenant_id' => $tenantId,
            'event_id' => $eventId,
            'contestant_id' => $contestantId,
            'category_id' => $categoryId,
            'quantity' => $quantity
        ];
        
        error_log("Casting vote with quantity: " . $quantity);
        error_log("Vote data being inserted: " . print_r($data, true));
        
        try {
            $voteId = $this->create($data);
            error_log("Vote created with ID: " . $voteId);
            
            // Verify what was actually inserted
            $insertedVote = $this->find($voteId);
            error_log("Actual inserted vote: " . print_r($insertedVote, true));
            
            if ($insertedVote['quantity'] != $quantity) {
                error_log("WARNING: Vote quantity mismatch! Expected: " . $quantity . ", Got: " . $insertedVote['quantity']);
            }
        } catch (\Exception $e) {
            error_log("Vote insertion error: " . $e->getMessage());
            throw $e;
        }
        
        // Update leaderboard cache with category information
        $this->updateLeaderboardCache($eventId, $contestantId, $categoryId);
        
        return $voteId;
    }
    
    /**
     * Get vote by transaction ID
     */
    public function getByTransactionId($transactionId)
    {
        $sql = "
            SELECT v.*, c.name as contestant_name, cat.name as category_name, e.name as event_name
            FROM votes v
            LEFT JOIN contestants c ON v.contestant_id = c.id
            LEFT JOIN categories cat ON v.category_id = cat.id
            LEFT JOIN events e ON v.event_id = e.id
            WHERE v.transaction_id = :transaction_id
            LIMIT 1
        ";
        
        return $this->db->selectOne($sql, ['transaction_id' => $transactionId]);
    }
    
    public function getVotesByEvent($eventId, $limit = null)
    {
        $sql = "
            SELECT v.*, c.name as contestant_name, c.contestant_code,
                   t.amount, t.msisdn, t.created_at as vote_time
            FROM votes v
            INNER JOIN contestants c ON v.contestant_id = c.id
            INNER JOIN transactions t ON v.transaction_id = t.id
            WHERE v.event_id = :event_id
            ORDER BY v.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->select($sql, ['event_id' => $eventId]);
    }
    
    public function getVotesByContestant($contestantId)
    {
        $sql = "
            SELECT v.*, t.amount, t.msisdn, t.created_at as vote_time
            FROM votes v
            INNER JOIN transactions t ON v.transaction_id = t.id
            WHERE v.contestant_id = :contestant_id
            ORDER BY v.created_at DESC
        ";
        
        return $this->db->select($sql, ['contestant_id' => $contestantId]);
    }
    
    public function getTotalVotes($eventId, $contestantId = null, $categoryId = null)
    {
        $sql = "SELECT COALESCE(SUM(quantity), 0) as total FROM votes WHERE event_id = :event_id";
        $params = ['event_id' => $eventId];
        
        if ($contestantId) {
            $sql .= " AND contestant_id = :contestant_id";
            $params['contestant_id'] = $contestantId;
        }
        
        if ($categoryId) {
            $sql .= " AND category_id = :category_id";
            $params['category_id'] = $categoryId;
        }
        
        $result = $this->db->selectOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    public function getCategoryVotes($eventId, $categoryId)
    {
        $sql = "
            SELECT 
                c.id,
                c.name,
                c.contestant_code,
                c.image_url,
                COALESCE(SUM(v.quantity), 0) as total_votes,
                COUNT(v.id) as vote_transactions
            FROM contestants c
            LEFT JOIN votes v ON c.id = v.contestant_id AND v.category_id = :category_id
            WHERE c.event_id = :event_id AND c.category_id = :category_id
            GROUP BY c.id, c.name, c.contestant_code, c.image_url
            ORDER BY total_votes DESC, c.name ASC
        ";
        
        return $this->db->select($sql, [
            'event_id' => $eventId,
            'category_id' => $categoryId
        ]);
    }
    
    public function getVoteStats($eventId)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_transactions,
                SUM(quantity) as total_votes,
                COUNT(DISTINCT contestant_id) as contestants_with_votes,
                AVG(quantity) as avg_votes_per_transaction
            FROM votes 
            WHERE event_id = :event_id
        ";
        
        return $this->db->selectOne($sql, ['event_id' => $eventId]);
    }
    
    public function getLeaderboard($eventId, $limit = 10)
    {
        $sql = "
            SELECT 
                c.id,
                c.name,
                c.contestant_code,
                c.image_url,
                cat.name as category_name,
                cc.short_code,
                COALESCE(SUM(v.quantity), 0) as total_votes,
                COUNT(v.id) as vote_count
            FROM contestants c
            LEFT JOIN contestant_categories cc ON c.id = cc.contestant_id AND cc.active = 1
            LEFT JOIN categories cat ON cc.category_id = cat.id
            LEFT JOIN votes v ON c.id = v.contestant_id
            WHERE c.event_id = :event_id AND c.active = 1
            GROUP BY c.id, cc.id
            ORDER BY total_votes DESC, c.name ASC
            LIMIT {$limit}
        ";
        
        return $this->db->select($sql, ['event_id' => $eventId]);
    }
    
    public function updateLeaderboardCache($eventId, $contestantId, $categoryId = null)
    {
        if ($categoryId) {
            // Update category-specific cache
            $totalVotes = $this->getTotalVotes($eventId, $contestantId, $categoryId);
            
            // Check if leaderboard_cache has category_id column
            $hasCategory = $this->db->selectOne("SHOW COLUMNS FROM leaderboard_cache LIKE 'category_id'");
            
            if ($hasCategory) {
                // Use category-aware cache
                $sql = "
                    INSERT INTO leaderboard_cache (event_id, contestant_id, category_id, total_votes, updated_at)
                    VALUES (:event_id, :contestant_id, :category_id, :total_votes, NOW())
                    ON DUPLICATE KEY UPDATE
                    total_votes = VALUES(total_votes),
                    updated_at = NOW()
                ";
                
                return $this->db->query($sql, [
                    'event_id' => $eventId,
                    'contestant_id' => $contestantId,
                    'category_id' => $categoryId,
                    'total_votes' => $totalVotes
                ]);
            }
        }
        
        // Fallback to original behavior (aggregate across all categories)
        $totalVotes = $this->getTotalVotes($eventId, $contestantId);
        
        $sql = "
            INSERT INTO leaderboard_cache (event_id, contestant_id, total_votes, updated_at)
            VALUES (:event_id, :contestant_id, :total_votes, NOW())
            ON DUPLICATE KEY UPDATE
            total_votes = VALUES(total_votes),
            updated_at = NOW()
        ";
        
        return $this->db->query($sql, [
            'event_id' => $eventId,
            'contestant_id' => $contestantId,
            'total_votes' => $totalVotes
        ]);
    }
    
    public function getVotesByTimeRange($eventId, $startDate, $endDate)
    {
        $sql = "
            SELECT 
                DATE(v.created_at) as vote_date,
                COUNT(*) as transaction_count,
                SUM(v.quantity) as vote_count
            FROM votes v
            WHERE v.event_id = :event_id
            AND v.created_at BETWEEN :start_date AND :end_date
            GROUP BY DATE(v.created_at)
            ORDER BY vote_date ASC
        ";
        
        return $this->db->select($sql, [
            'event_id' => $eventId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
}
