<?php

namespace SmartCast\Models;

/**
 * Subscription Plan Model
 */
class SubscriptionPlan extends BaseModel
{
    protected $table = 'subscription_plans';
    protected $fillable = [
        'name', 'slug', 'description', 'price', 'billing_cycle',
        'max_events', 'max_contestants_per_event', 'max_votes_per_event',
        'features', 'fee_rule_id', 'is_popular',
        'is_active', 'sort_order', 'trial_days'
    ];
    
    /**
     * Get all active plans ordered by sort_order
     */
    public function getActivePlans()
    {
        $sql = "
            SELECT sp.*, fr.rule_type, fr.percentage_rate, fr.fixed_amount
            FROM {$this->table} sp
            LEFT JOIN fee_rules fr ON sp.fee_rule_id = fr.id
            WHERE sp.is_active = 1
            ORDER BY sp.sort_order ASC
        ";
        
        return $this->db->select($sql);
    }
    
    /**
     * Get plan with features
     */
    public function getPlanWithFeatures($planId)
    {
        $plan = $this->find($planId);
        if (!$plan) {
            return null;
        }
        
        $features = $this->getPlanFeatures($planId);
        $plan['plan_features'] = $features;
        
        return $plan;
    }
    
    /**
     * Get plan features
     */
    public function getPlanFeatures($planId)
    {
        $sql = "
            SELECT * FROM plan_features 
            WHERE plan_id = :plan_id 
            ORDER BY sort_order ASC
        ";
        
        return $this->db->select($sql, ['plan_id' => $planId]);
    }
    
    /**
     * Get plan by slug
     */
    public function findBySlug($slug)
    {
        return $this->findAll(['slug' => $slug], null, 1)[0] ?? null;
    }
    
    /**
     * Check if plan allows unlimited events
     */
    public function hasUnlimitedEvents($planId)
    {
        $plan = $this->find($planId);
        return $plan && is_null($plan['max_events']);
    }
    
    /**
     * Check if plan allows unlimited contestants
     */
    public function hasUnlimitedContestants($planId)
    {
        $plan = $this->find($planId);
        return $plan && is_null($plan['max_contestants_per_event']);
    }
    
    /**
     * Get plan limits for a tenant
     */
    public function getPlanLimits($planId)
    {
        $plan = $this->find($planId);
        if (!$plan) {
            return null;
        }
        
        return [
            'max_events' => $plan['max_events'],
            'max_contestants_per_event' => $plan['max_contestants_per_event'],
            'max_votes_per_event' => $plan['max_votes_per_event'],
            'unlimited_events' => is_null($plan['max_events']),
            'unlimited_contestants' => is_null($plan['max_contestants_per_event']),
            'unlimited_votes' => is_null($plan['max_votes_per_event'])
        ];
    }
    
    /**
     * Get plans for pricing display
     */
    public function getPlansForPricing()
    {
        $plans = $this->getActivePlans();
        
        foreach ($plans as &$plan) {
            $plan['plan_features'] = $this->getPlanFeatures($plan['id']);
            $plan['features_json'] = !empty($plan['features']) ? json_decode($plan['features'], true) : [];
            
            // Format price display
            if ($plan['price'] == 0) {
                $plan['price_display'] = 'Free';
            } else {
                $plan['price_display'] = '$' . number_format($plan['price'], 2);
                if ($plan['billing_cycle'] !== 'lifetime') {
                    $plan['price_display'] .= '/' . $plan['billing_cycle'];
                }
            }
            
            // Format limits display
            $plan['events_display'] = is_null($plan['max_events']) ? 'Unlimited' : $plan['max_events'];
            $plan['contestants_display'] = is_null($plan['max_contestants_per_event']) ? 'Unlimited' : number_format($plan['max_contestants_per_event']);
            $plan['votes_display'] = is_null($plan['max_votes_per_event']) ? 'Unlimited' : number_format($plan['max_votes_per_event']);
            
            // Get fee rule information
            if ($plan['fee_rule_id']) {
                $feeRule = $this->db->selectOne(
                    "SELECT name, percentage_rate, fixed_amount, description FROM fee_rules WHERE id = :id AND active = 1",
                    ['id' => $plan['fee_rule_id']]
                );
                
                if ($feeRule) {
                    $plan['fee_rule'] = $feeRule;
                    
                    // Format fee display
                    if ($feeRule['percentage_rate'] > 0 && $feeRule['fixed_amount'] > 0) {
                        $plan['fee_display'] = number_format($feeRule['percentage_rate'], 1) . '% + $' . number_format($feeRule['fixed_amount'], 2);
                    } elseif ($feeRule['percentage_rate'] > 0) {
                        $plan['fee_display'] = number_format($feeRule['percentage_rate'], 1) . '%';
                    } elseif ($feeRule['fixed_amount'] > 0) {
                        $plan['fee_display'] = '$' . number_format($feeRule['fixed_amount'], 2);
                    } else {
                        $plan['fee_display'] = 'No fees';
                    }
                } else {
                    $plan['fee_display'] = 'Standard fees apply';
                }
            } else {
                $plan['fee_display'] = 'Standard fees apply';
            }
        }
        
        return $plans;
    }
    
    /**
     * Create a new plan with features
     */
    public function createPlanWithFeatures($planData, $features = [])
    {
        $this->db->beginTransaction();
        
        try {
            // Create the plan
            $planId = $this->create($planData);
            
            // Add features
            if (!empty($features)) {
                $this->addPlanFeatures($planId, $features);
            }
            
            $this->db->commit();
            return $planId;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Add features to a plan
     */
    public function addPlanFeatures($planId, $features)
    {
        foreach ($features as $feature) {
            $sql = "
                INSERT INTO plan_features 
                (plan_id, feature_key, feature_name, feature_value, is_boolean, sort_order)
                VALUES (:plan_id, :key, :name, :value, :is_boolean, :sort_order)
            ";
            
            $this->db->query($sql, [
                'plan_id' => $planId,
                'key' => $feature['key'],
                'name' => $feature['name'],
                'value' => $feature['value'] ?? null,
                'is_boolean' => $feature['is_boolean'] ?? 0,
                'sort_order' => $feature['sort_order'] ?? 0
            ]);
        }
    }
    
    /**
     * Update plan features
     */
    public function updatePlanFeatures($planId, $features)
    {
        $this->db->beginTransaction();
        
        try {
            // Delete existing features
            $this->db->query("DELETE FROM plan_features WHERE plan_id = :plan_id", ['plan_id' => $planId]);
            
            // Add new features
            $this->addPlanFeatures($planId, $features);
            
            $this->db->commit();
            
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Get plan usage statistics
     */
    public function getPlanUsageStats($planId)
    {
        $sql = "
            SELECT 
                COUNT(ts.id) as total_subscriptions,
                COUNT(CASE WHEN ts.status = 'active' THEN 1 END) as active_subscriptions,
                COUNT(CASE WHEN ts.status = 'expired' THEN 1 END) as expired_subscriptions,
                COUNT(CASE WHEN ts.status = 'cancelled' THEN 1 END) as cancelled_subscriptions
            FROM tenant_subscriptions ts
            WHERE ts.plan_id = :plan_id
        ";
        
        return $this->db->selectOne($sql, ['plan_id' => $planId]);
    }
    
    /**
     * Check if plan can be deleted (no active subscriptions)
     */
    public function canDeletePlan($planId)
    {
        $sql = "
            SELECT COUNT(*) as active_count
            FROM tenant_subscriptions ts
            WHERE ts.plan_id = :plan_id 
            AND ts.status IN ('active', 'trial')
        ";
        
        $result = $this->db->selectOne($sql, ['plan_id' => $planId]);
        return ($result['active_count'] ?? 0) == 0;
    }
    
    /**
     * Update plan and apply changes to all subscribers
     */
    public function updatePlanWithSubscriberSync($planId, $planData, $features = [])
    {
        $this->db->beginTransaction();
        
        try {
            // Update the plan
            $this->update($planId, $planData);
            
            // Update features if provided
            if (!empty($features)) {
                $this->updatePlanFeatures($planId, $features);
            }
            
            // Apply changes to all active subscribers
            $this->syncPlanChangesToSubscribers($planId, $planData);
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Sync plan changes to all active subscribers
     */
    private function syncPlanChangesToSubscribers($planId, $planData)
    {
        // Get all tenants with active subscriptions to this plan
        $sql = "
            SELECT DISTINCT t.id as tenant_id, ts.id as subscription_id
            FROM tenants t
            INNER JOIN tenant_subscriptions ts ON t.id = ts.tenant_id
            WHERE ts.plan_id = :plan_id 
            AND ts.status IN ('active', 'trial')
        ";
        
        $subscribers = $this->db->select($sql, ['plan_id' => $planId]);
        
        foreach ($subscribers as $subscriber) {
            // Update tenant's current plan reference
            $tenantUpdateSql = "
                UPDATE tenants 
                SET current_plan_id = :plan_id,
                    updated_at = NOW()
                WHERE id = :tenant_id
            ";
            
            $this->db->query($tenantUpdateSql, [
                'plan_id' => $planId,
                'tenant_id' => $subscriber['tenant_id']
            ]);
            
            // Log the automatic plan update
            $this->logAutomaticPlanUpdate($subscriber['tenant_id'], $planId);
        }
        
        // If fee rule changed, update tenant fee rules
        if (isset($planData['fee_rule_id'])) {
            $this->updateSubscriberFeeRules($planId, $planData['fee_rule_id']);
        }
    }
    
    /**
     * Update fee rules for all subscribers when plan fee rule changes
     * Note: With the new plan-based fee system, we don't create tenant-specific rules anymore.
     * The fee rules are automatically applied based on the tenant's active subscription plan.
     */
    private function updateSubscriberFeeRules($planId, $newFeeRuleId)
    {
        // With the new plan-based fee system, we don't need to create tenant-specific fee rules
        // The FeeRule::getApplicableFeeRule() method will automatically use the plan's fee rule
        // for tenants subscribed to this plan.
        
        // Optional: Clean up any old tenant-specific rules that might exist from the previous system
        if ($newFeeRuleId) {
            $this->cleanupOldTenantSpecificRules($planId);
        }
    }
    
    /**
     * Clean up old tenant-specific fee rules for subscribers of this plan
     */
    private function cleanupOldTenantSpecificRules($planId)
    {
        // Get all tenants subscribed to this plan
        $sql = "
            SELECT DISTINCT t.id as tenant_id
            FROM tenants t
            INNER JOIN tenant_subscriptions ts ON t.id = ts.tenant_id
            WHERE ts.plan_id = :plan_id 
            AND ts.status IN ('active', 'trial')
        ";
        
        $subscribers = $this->db->select($sql, ['plan_id' => $planId]);
        
        foreach ($subscribers as $subscriber) {
            // Remove any old tenant-specific fee rules (from the old system)
            $deleteSql = "
                DELETE FROM fee_rules 
                WHERE tenant_id = :tenant_id 
                AND event_id IS NULL
            ";
            
            $this->db->query($deleteSql, ['tenant_id' => $subscriber['tenant_id']]);
        }
    }
    
    /**
     * Log automatic plan updates for audit trail
     */
    private function logAutomaticPlanUpdate($tenantId, $planId)
    {
        $sql = "
            INSERT INTO tenant_plan_history 
            (tenant_id, old_plan_id, new_plan_id, changed_by, change_reason, effective_date)
            VALUES (:tenant_id, :old_plan_id, :new_plan_id, NULL, 'Automatic plan update', NOW())
        ";
        
        $this->db->query($sql, [
            'tenant_id' => $tenantId,
            'old_plan_id' => $planId,
            'new_plan_id' => $planId
        ]);
    }
}
