<?php

namespace SmartCast\Models;

/**
 * SMS Template Model
 */
class SmsTemplate extends BaseModel
{
    protected $table = 'sms_templates';
    
    protected $fillable = [
        'name', 'type', 'template', 'variables', 'is_active', 'tenant_id'
    ];
    
    /**
     * Get templates by type
     */
    public function getByType($type, $tenantId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE type = :type AND is_active = 1";
        $params = ['type' => $type];
        
        if ($tenantId) {
            $sql .= " AND (tenant_id = :tenant_id OR tenant_id IS NULL)";
            $params['tenant_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get active templates
     */
    public function getActiveTemplates($tenantId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1";
        $params = [];
        
        if ($tenantId) {
            $sql .= " AND (tenant_id = :tenant_id OR tenant_id IS NULL)";
            $params['tenant_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY type, name";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Create default templates
     */
    public function createDefaultTemplates($tenantId = null)
    {
        $templates = [
            [
                'name' => 'Vote Confirmation',
                'type' => 'vote_confirmation',
                'template' => "Thank you for voting!\n\nNominee: {nominee_name}\nEvent: {event_name}\nCategory: {category_name}\nVotes: {vote_count}\nAmount: {amount}\nReceipt: {receipt_number}\n\nThank you for your participation!",
                'variables' => json_encode(['nominee_name', 'event_name', 'category_name', 'vote_count', 'amount', 'receipt_number']),
                'is_active' => 1,
                'tenant_id' => $tenantId
            ],
            [
                'name' => 'Event Reminder',
                'type' => 'event_reminder',
                'template' => "Don't forget to vote for your favorite nominees in {event_name}!\n\nVoting ends on {end_date}.\n\nVote now: {voting_url}",
                'variables' => json_encode(['event_name', 'end_date', 'voting_url']),
                'is_active' => 1,
                'tenant_id' => $tenantId
            ],
            [
                'name' => 'Top Performers Update',
                'type' => 'custom',
                'template' => "🏆 {event_name} Update!\n\nCongratulations {nominee_name}! You're currently leading in {category_name} with {vote_count} votes.\n\nKeep encouraging your fans to vote!",
                'variables' => json_encode(['event_name', 'nominee_name', 'category_name', 'vote_count']),
                'is_active' => 1,
                'tenant_id' => $tenantId
            ],
            [
                'name' => 'Low Performance Alert',
                'type' => 'custom',
                'template' => "📢 {event_name} - Boost Your Votes!\n\nHi {nominee_name}, you currently have {vote_count} votes in {category_name}.\n\nShare your voting link with fans: {voting_url}",
                'variables' => json_encode(['event_name', 'nominee_name', 'category_name', 'vote_count', 'voting_url']),
                'is_active' => 1,
                'tenant_id' => $tenantId
            ],
            [
                'name' => 'Event Announcement',
                'type' => 'custom',
                'template' => "🎉 {event_name} is now live!\n\nVoting is open from {start_date} to {end_date}.\n\nVote for your favorites: {voting_url}",
                'variables' => json_encode(['event_name', 'start_date', 'end_date', 'voting_url']),
                'is_active' => 1,
                'tenant_id' => $tenantId
            ]
        ];
        
        foreach ($templates as $template) {
            // Check if template already exists
            $existing = $this->findAll([
                'name' => $template['name'],
                'type' => $template['type'],
                'tenant_id' => $tenantId
            ], null, 1);
            
            if (empty($existing)) {
                $this->create($template);
            }
        }
    }
    
    /**
     * Process template with variables
     */
    public function processTemplate($templateId, $variables = [])
    {
        $template = $this->find($templateId);
        if (!$template) {
            throw new \Exception('Template not found');
        }
        
        $message = $template['template'];
        
        // Replace variables in template
        foreach ($variables as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }
        
        return $message;
    }
    
    /**
     * Get template variables
     */
    public function getTemplateVariables($templateId)
    {
        $template = $this->find($templateId);
        if (!$template || empty($template['variables'])) {
            return [];
        }
        
        return json_decode($template['variables'], true) ?: [];
    }
}
