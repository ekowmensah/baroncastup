<?php

namespace SmartCast\Models;

/**
 * Tenant Subscription Model
 */
class TenantSubscription extends BaseModel
{
    protected $table = 'tenant_subscriptions';
    protected $fillable = [
        'tenant_id', 'plan_id', 'status', 'started_at', 'expires_at',
        'cancelled_at', 'auto_renew', 'payment_method', 'last_payment_at', 'next_payment_at'
    ];
    
    /**
     * Get active subscription for a tenant
     */
    public function getActiveSubscription($tenantId)
    {
        $sql = "
            SELECT ts.*, sp.name as plan_name, sp.slug as plan_slug, 
                   sp.price, sp.billing_cycle, sp.max_events, sp.max_contestants_per_event,
                   sp.max_votes_per_event, sp.features,
                   fr.id as fee_rule_id, fr.rule_type, fr.percentage_rate, fr.fixed_amount
            FROM {$this->table} ts
            INNER JOIN subscription_plans sp ON ts.plan_id = sp.id
            LEFT JOIN fee_rules fr ON sp.fee_rule_id = fr.id
            WHERE ts.tenant_id = :tenant_id 
            AND ts.status = 'active'
            ORDER BY ts.created_at DESC
            LIMIT 1
        ";
        
        return $this->db->selectOne($sql, ['tenant_id' => $tenantId]);
    }
    
    /**
     * Get subscription history for a tenant
     */
    public function getSubscriptionHistory($tenantId)
    {
        $sql = "
            SELECT ts.*, sp.name as plan_name, sp.price, sp.billing_cycle
            FROM {$this->table} ts
            INNER JOIN subscription_plans sp ON ts.plan_id = sp.id
            WHERE ts.tenant_id = :tenant_id
            ORDER BY ts.created_at DESC
        ";
        
        return $this->db->select($sql, ['tenant_id' => $tenantId]);
    }
    
    /**
     * Create subscription for tenant
     */
    public function createSubscription($tenantId, $planId, $billingCycle = 'monthly')
    {
        $planModel = new SubscriptionPlan();
        $plan = $planModel->find($planId);
        
        if (!$plan) {
            throw new \Exception('Plan not found');
        }
        
        $this->db->beginTransaction();
        
        try {
            // Cancel any existing active subscriptions
            $this->cancelActiveSubscriptions($tenantId);
            
            // Calculate expiration date
            $expiresAt = $this->calculateExpirationDate($billingCycle);
            $nextPaymentAt = $this->calculateNextPaymentDate($billingCycle);
            
            // Create new subscription
            $subscriptionId = $this->create([
                'tenant_id' => $tenantId,
                'plan_id' => $planId,
                'status' => 'active',
                'expires_at' => $expiresAt,
                'next_payment_at' => $nextPaymentAt,
                'auto_renew' => 1
            ]);
            
            // Update tenant's current plan
            $tenantModel = new Tenant();
            $tenantModel->update($tenantId, [
                'current_plan_id' => $planId,
                'subscription_status' => 'active',
                'subscription_expires_at' => $expiresAt
            ]);
            
            // Assign fee rule to tenant if plan has one
            if ($plan['fee_rule_id']) {
                $this->assignFeeRuleToTenant($tenantId, $plan['fee_rule_id']);
            }
            
            $this->db->commit();
            return $subscriptionId;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Cancel active subscriptions for a tenant
     */
    private function cancelActiveSubscriptions($tenantId)
    {
        $sql = "
            UPDATE {$this->table} 
            SET status = 'cancelled', cancelled_at = NOW()
            WHERE tenant_id = :tenant_id AND status = 'active'
        ";
        
        $this->db->query($sql, ['tenant_id' => $tenantId]);
    }
    
    /**
     * Calculate expiration date based on billing cycle
     */
    private function calculateExpirationDate($billingCycle)
    {
        switch ($billingCycle) {
            case 'monthly':
                return date('Y-m-d H:i:s', strtotime('+1 month'));
            case 'yearly':
                return date('Y-m-d H:i:s', strtotime('+1 year'));
            case 'lifetime':
                return date('Y-m-d H:i:s', strtotime('+50 years')); // Effectively never expires
            case 'free':
                return null; // Free plans don't expire
            default:
                return date('Y-m-d H:i:s', strtotime('+1 month'));
        }
    }
    
    /**
     * Calculate next payment date
     */
    private function calculateNextPaymentDate($billingCycle)
    {
        if ($billingCycle === 'free' || $billingCycle === 'lifetime') {
            return null;
        }
        
        return $this->calculateExpirationDate($billingCycle);
    }
    
    /**
     * Assign fee rule to tenant based on their plan
     */
    private function assignFeeRuleToTenant($tenantId, $feeRuleId)
    {
        // Check if tenant already has a specific fee rule
        $feeRuleModel = new FeeRule();
        $existingRule = $feeRuleModel->findAll(['tenant_id' => $tenantId], null, 1);
        
        if (empty($existingRule)) {
            // Create a tenant-specific fee rule based on the plan's fee rule
            $planFeeRule = $feeRuleModel->find($feeRuleId);
            
            if ($planFeeRule) {
                $feeRuleModel->create([
                    'tenant_id' => $tenantId,
                    'event_id' => null,
                    'rule_type' => $planFeeRule['rule_type'],
                    'percentage_rate' => $planFeeRule['percentage_rate'],
                    'fixed_amount' => $planFeeRule['fixed_amount'],
                    'active' => 1
                ]);
            }
        }
    }
    
    /**
     * Check if tenant can create more events (only counts published events, not drafts)
     */
    public function canCreateEvent($tenantId)
    {
        $subscription = $this->getActiveSubscription($tenantId);
        
        if (!$subscription) {
            return false;
        }
        
        // If max_events is null, unlimited events allowed
        if (is_null($subscription['max_events'])) {
            return true;
        }
        
        // Count current published events only (exclude drafts)
        $eventModel = new Event();
        $sql = "SELECT COUNT(*) as count FROM events WHERE tenant_id = :tenant_id AND status != 'draft'";
        $result = $eventModel->getDatabase()->selectOne($sql, ['tenant_id' => $tenantId]);
        $currentPublishedEvents = $result['count'] ?? 0;
        
        return $currentPublishedEvents < $subscription['max_events'];
    }
    
    /**
     * Check if tenant can add more contestants to an event
     */
    public function canAddContestant($tenantId, $eventId)
    {
        $subscription = $this->getActiveSubscription($tenantId);
        
        if (!$subscription) {
            return false;
        }
        
        // If max_contestants_per_event is null, unlimited contestants allowed
        if (is_null($subscription['max_contestants_per_event'])) {
            return true;
        }
        
        // Count current contestants for this event
        $contestantModel = new Contestant();
        $currentContestants = $contestantModel->count(['event_id' => $eventId]);
        
        return $currentContestants < $subscription['max_contestants_per_event'];
    }
    
    /**
     * Get subscription limits for a tenant
     */
    public function getTenantLimits($tenantId)
    {
        $subscription = $this->getActiveSubscription($tenantId);
        
        if (!$subscription) {
            return null;
        }
        
        // Count current published events only (exclude drafts)
        $eventModel = new Event();
        $sql = "SELECT COUNT(*) as count FROM events WHERE tenant_id = :tenant_id AND status != 'draft'";
        $result = $eventModel->getDatabase()->selectOne($sql, ['tenant_id' => $tenantId]);
        $currentPublishedEvents = $result['count'] ?? 0;
        
        return [
            'plan_name' => $subscription['plan_name'],
            'max_events' => $subscription['max_events'],
            'current_events' => $currentPublishedEvents,
            'events_remaining' => is_null($subscription['max_events']) ? 'Unlimited' : max(0, $subscription['max_events'] - $currentPublishedEvents),
            'max_contestants_per_event' => $subscription['max_contestants_per_event'],
            'max_votes_per_event' => $subscription['max_votes_per_event'],
            'unlimited_events' => is_null($subscription['max_events']),
            'unlimited_contestants' => is_null($subscription['max_contestants_per_event']),
            'unlimited_votes' => is_null($subscription['max_votes_per_event']),
            'expires_at' => $subscription['expires_at'],
            'status' => $subscription['status']
        ];
    }
    
    /**
     * Upgrade/Downgrade subscription
     */
    public function changeSubscription($tenantId, $newPlanId, $changeReason = null)
    {
        // Check if there's already an active transaction
        $inTransaction = $this->db->inTransaction();
        
        if (!$inTransaction) {
            $this->db->beginTransaction();
        }
        
        try {
            $currentSubscription = $this->getActiveSubscription($tenantId);
            $oldPlanId = $currentSubscription ? $currentSubscription['plan_id'] : null;
            
            // Cancel existing subscriptions first
            $this->cancelActiveSubscriptions($tenantId);
            
            // Get the new plan details
            $planModel = new SubscriptionPlan();
            $newPlan = $planModel->find($newPlanId);
            
            if (!$newPlan) {
                throw new \Exception('Selected plan not found');
            }
            
            // Calculate expiration date
            $expiresAt = $this->calculateExpirationDate($newPlan['billing_cycle']);
            $nextPaymentAt = $this->calculateNextPaymentDate($newPlan['billing_cycle']);
            
            // Create new subscription record
            $subscriptionId = $this->create([
                'tenant_id' => $tenantId,
                'plan_id' => $newPlanId,
                'status' => 'active',
                'started_at' => date('Y-m-d H:i:s'),
                'expires_at' => $expiresAt,
                'next_payment_at' => $nextPaymentAt,
                'auto_renew' => 1
            ]);
            
            // Update tenant record
            $tenantModel = new Tenant();
            $tenantModel->update($tenantId, [
                'current_plan_id' => $newPlanId,
                'subscription_status' => 'active',
                'subscription_expires_at' => $expiresAt
            ]);
            
            // Assign fee rule if plan has one
            if ($newPlan['fee_rule_id']) {
                $this->assignFeeRuleToTenant($tenantId, $newPlan['fee_rule_id']);
            }
            
            // Log the plan change
            $this->logPlanChange($tenantId, $oldPlanId, $newPlanId, $changeReason);
            
            if (!$inTransaction) {
                $this->db->commit();
            }
            
            return $subscriptionId;
            
        } catch (\Exception $e) {
            if (!$inTransaction) {
                $this->db->rollback();
            }
            throw $e;
        }
    }
    
    /**
     * Log plan changes for audit trail
     */
    private function logPlanChange($tenantId, $oldPlanId, $newPlanId, $changeReason = null)
    {
        $sql = "
            INSERT INTO tenant_plan_history 
            (tenant_id, old_plan_id, new_plan_id, change_reason)
            VALUES (:tenant_id, :old_plan_id, :new_plan_id, :change_reason)
        ";
        
        $this->db->query($sql, [
            'tenant_id' => $tenantId,
            'old_plan_id' => $oldPlanId,
            'new_plan_id' => $newPlanId,
            'change_reason' => $changeReason
        ]);
    }
}
