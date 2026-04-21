<?php

namespace SmartCast\Models;

/**
 * Event Status History Model
 */
class EventStatusHistory extends BaseModel
{
    protected $table = 'event_status_history';
    protected $fillable = [
        'event_id', 'old_status', 'new_status', 'old_admin_status', 
        'new_admin_status', 'changed_by', 'change_reason', 'notes'
    ];
    
    public function recordStatusChange($eventId, $oldStatus, $newStatus, $changedBy, $reason = null, $notes = null)
    {
        return $this->create([
            'event_id' => $eventId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'old_admin_status' => null,
            'new_admin_status' => null,
            'changed_by' => $changedBy,
            'change_reason' => $reason,
            'notes' => $notes
        ]);
    }
    
    public function recordAdminStatusChange($eventId, $oldAdminStatus, $newAdminStatus, $changedBy, $reason = null, $notes = null)
    {
        return $this->create([
            'event_id' => $eventId,
            'old_status' => null,
            'new_status' => null,
            'old_admin_status' => $oldAdminStatus,
            'new_admin_status' => $newAdminStatus,
            'changed_by' => $changedBy,
            'change_reason' => $reason,
            'notes' => $notes
        ]);
    }
    
    public function recordFullStatusChange($eventId, $oldStatus, $newStatus, $oldAdminStatus, $newAdminStatus, $changedBy, $reason = null, $notes = null)
    {
        return $this->create([
            'event_id' => $eventId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'old_admin_status' => $oldAdminStatus,
            'new_admin_status' => $newAdminStatus,
            'changed_by' => $changedBy,
            'change_reason' => $reason,
            'notes' => $notes
        ]);
    }
    
    public function getEventHistory($eventId)
    {
        $sql = "
            SELECT esh.*, u.email as changed_by_email, e.name as event_name
            FROM {$this->table} esh
            LEFT JOIN users u ON esh.changed_by = u.id
            LEFT JOIN events e ON esh.event_id = e.id
            WHERE esh.event_id = :event_id
            ORDER BY esh.created_at DESC
        ";
        
        return $this->db->select($sql, ['event_id' => $eventId]);
    }
    
    public function getStatusTimeline($eventId)
    {
        $history = $this->getEventHistory($eventId);
        $timeline = [];
        
        foreach ($history as $entry) {
            $timelineEntry = [
                'timestamp' => $entry['created_at'],
                'changed_by' => $entry['changed_by_email'],
                'reason' => $entry['change_reason'],
                'notes' => $entry['notes'],
                'changes' => []
            ];
            
            if ($entry['old_status'] && $entry['new_status']) {
                $timelineEntry['changes'][] = [
                    'type' => 'status',
                    'from' => $entry['old_status'],
                    'to' => $entry['new_status']
                ];
            }
            
            if ($entry['old_admin_status'] && $entry['new_admin_status']) {
                $timelineEntry['changes'][] = [
                    'type' => 'admin_status',
                    'from' => $entry['old_admin_status'],
                    'to' => $entry['new_admin_status']
                ];
            }
            
            $timeline[] = $timelineEntry;
        }
        
        return $timeline;
    }
    
    public function getStatusStats($eventId = null, $tenantId = null)
    {
        $sql = "
            SELECT 
                new_status,
                new_admin_status,
                COUNT(*) as change_count,
                COUNT(DISTINCT event_id) as event_count
            FROM {$this->table} esh
        ";
        
        $params = [];
        $conditions = [];
        
        if ($eventId) {
            $conditions[] = "esh.event_id = :event_id";
            $params['event_id'] = $eventId;
        }
        
        if ($tenantId) {
            $sql .= " INNER JOIN events e ON esh.event_id = e.id";
            $conditions[] = "e.tenant_id = :tenant_id";
            $params['tenant_id'] = $tenantId;
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $sql .= " GROUP BY new_status, new_admin_status ORDER BY change_count DESC";
        
        return $this->db->select($sql, $params);
    }
    
    public function getRecentChanges($tenantId = null, $limit = 20)
    {
        $sql = "
            SELECT esh.*, u.email as changed_by_email, e.name as event_name, e.code as event_code
            FROM {$this->table} esh
            LEFT JOIN users u ON esh.changed_by = u.id
            INNER JOIN events e ON esh.event_id = e.id
        ";
        
        $params = [];
        
        if ($tenantId) {
            $sql .= " WHERE e.tenant_id = :tenant_id";
            $params['tenant_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY esh.created_at DESC LIMIT {$limit}";
        
        return $this->db->select($sql, $params);
    }
    
    public function getEventStatusDuration($eventId)
    {
        $sql = "
            SELECT 
                new_status,
                MIN(created_at) as status_start,
                LEAD(created_at) OVER (ORDER BY created_at) as status_end,
                TIMESTAMPDIFF(SECOND, MIN(created_at), LEAD(created_at) OVER (ORDER BY created_at)) as duration_seconds
            FROM {$this->table}
            WHERE event_id = :event_id AND new_status IS NOT NULL
            GROUP BY new_status, created_at
            ORDER BY created_at
        ";
        
        return $this->db->select($sql, ['event_id' => $eventId]);
    }
    
    public function getChangesByUser($userId, $limit = 50)
    {
        $sql = "
            SELECT esh.*, e.name as event_name, e.code as event_code
            FROM {$this->table} esh
            INNER JOIN events e ON esh.event_id = e.id
            WHERE esh.changed_by = :user_id
            ORDER BY esh.created_at DESC
            LIMIT {$limit}
        ";
        
        return $this->db->select($sql, ['user_id' => $userId]);
    }
    
    public function getStatusChangeFrequency($tenantId = null, $days = 30)
    {
        $since = date('Y-m-d H:i:s', time() - ($days * 24 * 3600));
        
        $sql = "
            SELECT 
                DATE(esh.created_at) as change_date,
                COUNT(*) as change_count,
                COUNT(DISTINCT esh.event_id) as events_changed
            FROM {$this->table} esh
        ";
        
        $params = ['since' => $since];
        
        if ($tenantId) {
            $sql .= " INNER JOIN events e ON esh.event_id = e.id WHERE e.tenant_id = :tenant_id AND";
            $params['tenant_id'] = $tenantId;
        } else {
            $sql .= " WHERE";
        }
        
        $sql .= " esh.created_at >= :since GROUP BY DATE(esh.created_at) ORDER BY change_date DESC";
        
        return $this->db->select($sql, $params);
    }
}
