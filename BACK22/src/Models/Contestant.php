<?php

namespace SmartCast\Models;

/**
 * Contestant Model
 */
class Contestant extends BaseModel
{
    protected $table = 'contestants';
    protected $fillable = [
        'tenant_id', 'event_id', 'name', 'contestant_code', 'image_url',
        'bio', 'display_order', 'active', 'created_by'
    ];
    
    public function isContestantInCategory($contestantId, $categoryId)
    {
        $sql = "
            SELECT COUNT(*) as count
            FROM contestant_categories cc
            WHERE cc.contestant_id = :contestant_id 
            AND cc.category_id = :category_id 
            AND cc.active = 1
        ";
        
        $result = $this->db->selectOne($sql, [
            'contestant_id' => $contestantId,
            'category_id' => $categoryId
        ]);
        
        return ($result['count'] ?? 0) > 0;
    }
    
    public function getContestantsByEvent($eventId)
    {
        $sql = "
            SELECT c.*, 
                   cat.name as category_name,
                   cc.short_code,
                   cc.category_id,
                   cc.category_image_url,
                   cc.use_category_photo,
                   COALESCE(SUM(v.quantity), 0) as total_votes
            FROM contestants c
            LEFT JOIN contestant_categories cc ON c.id = cc.contestant_id AND cc.active = 1
            LEFT JOIN categories cat ON cc.category_id = cat.id
            LEFT JOIN votes v ON c.id = v.contestant_id AND (v.category_id = cc.category_id OR v.category_id IS NULL)
            WHERE c.event_id = :event_id AND c.active = 1
            GROUP BY c.id, cc.id
            ORDER BY cc.display_order ASC, c.name ASC
        ";
        
        return $this->db->select($sql, ['event_id' => $eventId]);
    }
    
    public function getContestantsByCategory($categoryId)
    {
        $sql = "
            SELECT c.*, cc.short_code,
                   COALESCE(SUM(v.quantity), 0) as total_votes
            FROM contestants c
            INNER JOIN contestant_categories cc ON c.id = cc.contestant_id
            LEFT JOIN votes v ON c.id = v.contestant_id AND (v.category_id = cc.category_id OR v.category_id IS NULL)
            WHERE cc.category_id = :category_id AND c.active = 1 AND cc.active = 1
            GROUP BY c.id
            ORDER BY cc.display_order ASC, c.name ASC
        ";
        
        return $this->db->select($sql, ['category_id' => $categoryId]);
    }
    
    public function findByCode($code)
    {
        $sql = "SELECT * FROM {$this->table} WHERE contestant_code = :code AND active = 1";
        return $this->db->selectOne($sql, ['code' => $code]);
    }
    
    public function addToCategory($contestantId, $categoryId, $shortCode)
    {
        $data = [
            'contestant_id' => $contestantId,
            'category_id' => $categoryId,
            'short_code' => $shortCode,
            'active' => 1
        ];
        
        return $this->db->insert('contestant_categories', $data);
    }
    
    public function removeFromCategory($contestantId, $categoryId)
    {
        return $this->db->delete(
            'contestant_categories',
            'contestant_id = :contestant_id AND category_id = :category_id',
            [
                'contestant_id' => $contestantId,
                'category_id' => $categoryId
            ]
        );
    }
    
    public function getContestantCategories($contestantId)
    {
        $sql = "
            SELECT cc.*, cat.name as category_name
            FROM contestant_categories cc
            INNER JOIN categories cat ON cc.category_id = cat.id
            WHERE cc.contestant_id = :contestant_id AND cc.active = 1
        ";
        
        return $this->db->select($sql, ['contestant_id' => $contestantId]);
    }
    
    public function activate($contestantId)
    {
        return $this->update($contestantId, ['active' => 1]);
    }
    
    public function deactivate($contestantId)
    {
        return $this->update($contestantId, ['active' => 0]);
    }
    
    public function getVoteCount($contestantId)
    {
        $sql = "SELECT COALESCE(SUM(quantity), 0) as total FROM votes WHERE contestant_id = :id";
        $result = $this->db->selectOne($sql, ['id' => $contestantId]);
        return $result['total'] ?? 0;
    }
    
    public function updateDisplayOrder($contestantId, $order)
    {
        return $this->update($contestantId, ['display_order' => $order]);
    }
    
    public function generateContestantCode($tenantId, $eventId = null)
    {
        // Generate a unique contestant code
        $prefix = 'T' . $tenantId;
        
        // Find the next available number
        $sql = "
            SELECT contestant_code 
            FROM {$this->table} 
            WHERE tenant_id = :tenant_id 
            AND contestant_code LIKE :pattern 
            ORDER BY contestant_code DESC 
            LIMIT 1
        ";
        
        $pattern = $prefix . '%';
        $result = $this->db->selectOne($sql, [
            'tenant_id' => $tenantId,
            'pattern' => $pattern
        ]);
        
        if ($result) {
            // Extract number and increment
            $lastCode = $result['contestant_code'];
            $number = (int) substr($lastCode, strlen($prefix));
            $nextNumber = $number + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
    }
}
