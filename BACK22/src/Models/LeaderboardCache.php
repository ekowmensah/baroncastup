<?php

namespace SmartCast\Models;

/**
 * Leaderboard Cache Model
 */
class LeaderboardCache extends BaseModel
{
    protected $table = 'leaderboard_cache';
    protected $fillable = [
        'event_id', 'contestant_id', 'category_id', 'total_votes'
    ];
    
    public function updateCache($eventId, $contestantId, $totalVotes)
    {
        $existing = $this->findAll([
            'event_id' => $eventId,
            'contestant_id' => $contestantId
        ], null, 1);
        
        if (!empty($existing)) {
            return $this->update($existing[0]['id'], [
                'total_votes' => $totalVotes
            ]);
        } else {
            return $this->create([
                'event_id' => $eventId,
                'contestant_id' => $contestantId,
                'total_votes' => $totalVotes
            ]);
        }
    }
    
    public function getLeaderboard($eventId, $categoryId = null, $limit = 10)
    {
        $sql = "
            SELECT lc.*, c.name, c.contestant_code, c.image_url,
                   cat.name as category_name
            FROM {$this->table} lc
            INNER JOIN contestants c ON lc.contestant_id = c.id
            INNER JOIN categories cat ON lc.category_id = cat.id
            WHERE lc.event_id = :event_id AND c.active = 1
        ";
        
        $params = ['event_id' => $eventId];
        
        if ($categoryId) {
            $sql .= " AND lc.category_id = :category_id";
            $params['category_id'] = $categoryId;
        }
        
        $sql .= " ORDER BY lc.total_votes DESC, c.name ASC LIMIT {$limit}";
        
        return $this->db->select($sql, $params);
    }
    
    public function refreshCache($eventId)
    {
        // Clear existing cache for this event
        $this->clearCache($eventId);
        
        // Rebuild with category-specific data
        $sql = "
            INSERT INTO {$this->table} (event_id, contestant_id, category_id, total_votes, updated_at)
            SELECT 
                v.event_id,
                v.contestant_id,
                v.category_id,
                COALESCE(SUM(v.quantity), 0) as total_votes,
                NOW()
            FROM votes v
            WHERE v.event_id = :event_id AND v.category_id IS NOT NULL
            GROUP BY v.event_id, v.contestant_id, v.category_id
        ";
        
        return $this->db->query($sql, ['event_id' => $eventId]);
    }
    
    public function clearCache($eventId)
    {
        return $this->db->delete($this->table, 'event_id = :event_id', ['event_id' => $eventId]);
    }
}
