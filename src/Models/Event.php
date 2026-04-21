<?php

namespace SmartCast\Models;

/**
 * Event Model
 */
class Event extends BaseModel
{
    protected $table = 'events';
    protected $fillable = [
        'tenant_id', 'name', 'code', 'description', 'featured_image',
        'start_date', 'end_date', 'vote_price', 'active', 'status', 'visibility',
        'admin_status', 'admin_notes', 'created_by', 'results_visible'
    ];
    
    public function getActiveEvents($tenantId = null)
    {
        $conditions = ['status' => 'active'];
        if ($tenantId) {
            $conditions['tenant_id'] = $tenantId;
        }
        
        return $this->findAll($conditions, 'start_date DESC');
    }
    
    public function getPublicEvents()
    {
        $sql = "
            SELECT e.*,
                   COUNT(DISTINCT c.id) as contestant_count,
                   COALESCE(SUM(v.quantity), 0) as total_votes,
                   COALESCE(SUM(CASE WHEN t.status = 'success' THEN t.amount ELSE 0 END), 0) as revenue,
                   DATEDIFF(e.end_date, NOW()) as days_left
            FROM events e
            LEFT JOIN contestants c ON e.id = c.event_id
            LEFT JOIN votes v ON c.id = v.contestant_id
            LEFT JOIN transactions t ON v.transaction_id = t.id
            WHERE e.status = 'active' 
            AND e.visibility = 'public'
            GROUP BY e.id
            ORDER BY e.start_date DESC
        ";
        
        return $this->db->select($sql);
    }
    
    public function getEventsByTenant($tenantId)
    {
        $sql = "
            SELECT e.*,
                   COUNT(DISTINCT c.id) as contestant_count,
                   COALESCE(SUM(v.quantity), 0) as total_votes,
                   COALESCE(SUM(CASE WHEN t.status = 'success' THEN t.amount ELSE 0 END), 0) as revenue
            FROM events e
            LEFT JOIN contestants c ON e.id = c.event_id
            LEFT JOIN votes v ON c.id = v.contestant_id
            LEFT JOIN transactions t ON v.transaction_id = t.id
            WHERE e.tenant_id = :tenant_id
            GROUP BY e.id
            ORDER BY e.created_at DESC
        ";
        
        return $this->db->select($sql, ['tenant_id' => $tenantId]);
    }
    
    public function findByCode($code)
    {
        $sql = "SELECT * FROM {$this->table} WHERE UPPER(code) = UPPER(:code)";
        return $this->db->selectOne($sql, ['code' => $code]);
    }
    
    public function getEventWithCategories($eventId)
    {
        $sql = "
            SELECT e.*, 
                   COUNT(c.id) as category_count,
                   COUNT(cont.id) as contestant_count
            FROM events e
            LEFT JOIN categories c ON e.id = c.event_id
            LEFT JOIN contestants cont ON e.id = cont.event_id
            WHERE e.id = :event_id
            GROUP BY e.id
        ";
        
        return $this->db->selectOne($sql, ['event_id' => $eventId]);
    }
    
    public function getEventResults($eventId)
    {
        $sql = "
            SELECT 
                cont.id,
                cont.name,
                cont.image_url,
                cont.contestant_code,
                cat.name as category_name,
                cc.short_code,
                COALESCE(SUM(v.quantity), 0) as total_votes,
                COALESCE(lc.total_votes, 0) as cached_votes
            FROM contestants cont
            LEFT JOIN contestant_categories cc ON cont.id = cc.contestant_id
            LEFT JOIN categories cat ON cc.category_id = cat.id
            LEFT JOIN votes v ON cont.id = v.contestant_id
            LEFT JOIN leaderboard_cache lc ON cont.id = lc.contestant_id AND lc.event_id = :event_id
            WHERE cont.event_id = :event_id
            GROUP BY cont.id, cat.id
            ORDER BY total_votes DESC, cont.name ASC
        ";
        
        return $this->db->select($sql, ['event_id' => $eventId]);
    }
    
    public function updateStatus($eventId, $status, $userId = null)
    {
        $data = ['status' => $status];
        
        if ($status === 'suspended' && $userId) {
            $data['suspended_by'] = $userId;
            $data['suspended_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'closed') {
            $data['closed_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'archived') {
            $data['archived_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->update($eventId, $data);
    }
    
    public function toggleResultsVisibility($eventId)
    {
        $event = $this->find($eventId);
        if (!$event) return false;
        
        $newVisibility = $event['results_visible'] ? 0 : 1;
        return $this->update($eventId, ['results_visible' => $newVisibility]);
    }
    
    public function isActive($eventId)
    {
        $event = $this->find($eventId);
    }
    
    public function canVote($eventId)
    {
        $event = $this->find($eventId);
        
        if (!$event) {
            return false;
        }
        
        // Check if event is active and within voting period
        $now = date('Y-m-d H:i:s');
        return $event['active'] && 
               $event['status'] === 'active' && 
               $now >= $event['start_date'] && 
               $now <= $event['end_date'];
    }
}
