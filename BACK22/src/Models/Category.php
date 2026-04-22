<?php

namespace SmartCast\Models;

/**
 * Category Model
 */
class Category extends BaseModel
{
    protected $table = 'categories';
    protected $fillable = [
        'event_id', 'tenant_id', 'name', 'description', 'created_by', 'display_order'
    ];
    
    public function getCategoriesByEvent($eventId)
    {
        $sql = "
            SELECT c.*, 
                   COUNT(cc.contestant_id) as contestant_count
            FROM categories c
            LEFT JOIN contestant_categories cc ON c.id = cc.category_id AND cc.active = 1
            WHERE c.event_id = :event_id
            GROUP BY c.id
            ORDER BY c.display_order ASC, c.name ASC
        ";
        
        return $this->db->select($sql, ['event_id' => $eventId]);
    }
    
    public function getCategoryWithContestants($categoryId)
    {
        $category = $this->find($categoryId);
        if (!$category) return null;
        
        $contestantModel = new Contestant();
        $contestants = $contestantModel->getContestantsByCategory($categoryId);
        
        $category['contestants'] = $contestants;
        return $category;
    }
    
    public function updateDisplayOrder($categoryId, $order)
    {
        return $this->update($categoryId, ['display_order' => $order]);
    }
    
    public function getCategoryResults($categoryId)
    {
        $sql = "
            SELECT 
                c.id as contestant_id,
                c.name as contestant_name,
                c.contestant_code,
                c.image_url,
                cc.short_code,
                COALESCE(SUM(v.quantity), 0) as total_votes,
                COUNT(v.id) as vote_transactions
            FROM contestant_categories cc
            INNER JOIN contestants c ON cc.contestant_id = c.id
            LEFT JOIN votes v ON c.id = v.contestant_id
            WHERE cc.category_id = :category_id 
            AND cc.active = 1 
            AND c.active = 1
            GROUP BY c.id
            ORDER BY total_votes DESC, c.name ASC
        ";
        
        return $this->db->select($sql, ['category_id' => $categoryId]);
    }
}
