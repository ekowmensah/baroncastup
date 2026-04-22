<?php

namespace SmartCast\Models;

/**
 * Payout Schedule Model
 */
class PayoutSchedule extends BaseModel
{
    protected $table = 'payout_schedules';
    protected $fillable = [
        'tenant_id', 'frequency', 'minimum_amount', 'auto_payout_enabled',
        'instant_payout_threshold', 'next_payout_date', 'payout_day'
    ];
    
    const FREQUENCY_MANUAL = 'manual';
    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_MONTHLY = 'monthly';
    
    public function getScheduleByTenant($tenantId)
    {
        $schedule = $this->findAll(['tenant_id' => $tenantId], null, 1);
        
        if (empty($schedule)) {
            // Create default schedule
            $this->createDefaultSchedule($tenantId);
            return $this->getScheduleByTenant($tenantId);
        }
        
        return $schedule[0];
    }
    
    public function createDefaultSchedule($tenantId)
    {
        return $this->create([
            'tenant_id' => $tenantId,
            'frequency' => self::FREQUENCY_MONTHLY,
            'minimum_amount' => 10.00,
            'auto_payout_enabled' => 0,
            'instant_payout_threshold' => 1000.00,
            'next_payout_date' => $this->calculateNextPayoutDate(self::FREQUENCY_MONTHLY, 1),
            'payout_day' => 1
        ]);
    }
    
    public function updateSchedule($tenantId, $frequency, $minimumAmount, $autoPayoutEnabled, $instantThreshold = null, $payoutDay = null)
    {
        $schedule = $this->getScheduleByTenant($tenantId);
        
        $updateData = [
            'frequency' => $frequency,
            'minimum_amount' => $minimumAmount,
            'auto_payout_enabled' => $autoPayoutEnabled ? 1 : 0,
            'instant_payout_threshold' => $instantThreshold ?? 1000.00
        ];
        
        // Set payout day based on frequency
        if ($frequency === self::FREQUENCY_MONTHLY) {
            $updateData['payout_day'] = $payoutDay ?? 1; // 1st of month
        } elseif ($frequency === self::FREQUENCY_WEEKLY) {
            $updateData['payout_day'] = $payoutDay ?? 1; // Monday (1 = Monday, 7 = Sunday)
        }
        
        // Calculate next payout date
        $updateData['next_payout_date'] = $this->calculateNextPayoutDate($frequency, $updateData['payout_day']);
        
        return $this->update($schedule['id'], $updateData);
    }
    
    public function calculateNextPayoutDate($frequency, $payoutDay = 1)
    {
        $now = new \DateTime();
        
        switch ($frequency) {
            case self::FREQUENCY_DAILY:
                return $now->modify('+1 day')->format('Y-m-d');
                
            case self::FREQUENCY_WEEKLY:
                // Find next occurrence of the specified day of week
                $dayOfWeek = $payoutDay; // 1 = Monday, 7 = Sunday
                $currentDayOfWeek = $now->format('N');
                
                if ($currentDayOfWeek < $dayOfWeek) {
                    $daysToAdd = $dayOfWeek - $currentDayOfWeek;
                } else {
                    $daysToAdd = 7 - ($currentDayOfWeek - $dayOfWeek);
                }
                
                return $now->modify("+{$daysToAdd} days")->format('Y-m-d');
                
            case self::FREQUENCY_MONTHLY:
                // Find next occurrence of the specified day of month
                $currentDay = $now->format('j');
                
                if ($currentDay < $payoutDay) {
                    // This month
                    $targetDate = $now->setDate($now->format('Y'), $now->format('n'), $payoutDay);
                } else {
                    // Next month
                    $targetDate = $now->modify('first day of next month')->setDate(
                        $now->format('Y'), 
                        $now->format('n'), 
                        min($payoutDay, $now->format('t')) // Handle months with fewer days
                    );
                }
                
                return $targetDate->format('Y-m-d');
                
            case self::FREQUENCY_MANUAL:
            default:
                return null;
        }
    }
    
    public function getTenantsForAutoPayout()
    {
        $sql = "
            SELECT ps.*, tb.available, tb.tenant_id, t.name as tenant_name
            FROM {$this->table} ps
            INNER JOIN tenant_balances tb ON ps.tenant_id = tb.tenant_id
            INNER JOIN tenants t ON ps.tenant_id = t.id
            WHERE ps.auto_payout_enabled = 1
            AND ps.next_payout_date <= CURDATE()
            AND tb.available >= ps.minimum_amount
            AND t.active = 1
        ";
        
        return $this->db->select($sql);
    }
    
    public function getTenantsForInstantPayout()
    {
        $sql = "
            SELECT ps.*, tb.available, tb.tenant_id, t.name as tenant_name
            FROM {$this->table} ps
            INNER JOIN tenant_balances tb ON ps.tenant_id = tb.tenant_id
            INNER JOIN tenants t ON ps.tenant_id = t.id
            WHERE ps.auto_payout_enabled = 1
            AND tb.available >= ps.instant_payout_threshold
            AND t.active = 1
        ";
        
        return $this->db->select($sql);
    }
    
    public function updateNextPayoutDate($tenantId)
    {
        $schedule = $this->getScheduleByTenant($tenantId);
        
        $nextDate = $this->calculateNextPayoutDate($schedule['frequency'], $schedule['payout_day']);
        
        return $this->update($schedule['id'], [
            'next_payout_date' => $nextDate
        ]);
    }
    
    public function canRequestPayout($tenantId, $amount)
    {
        $schedule = $this->getScheduleByTenant($tenantId);
        
        // Check minimum amount
        if ($amount < $schedule['minimum_amount']) {
            return [
                'allowed' => false,
                'reason' => "Minimum payout amount is $" . number_format($schedule['minimum_amount'], 2)
            ];
        }
        
        // Check if manual payouts are allowed (frequency is manual or auto is disabled)
        if ($schedule['frequency'] !== self::FREQUENCY_MANUAL && $schedule['auto_payout_enabled']) {
            return [
                'allowed' => false,
                'reason' => 'Automatic payouts are enabled. Manual requests are not allowed.'
            ];
        }
        
        return ['allowed' => true];
    }
    
    public function getScheduleStats()
    {
        $sql = "
            SELECT 
                frequency,
                COUNT(*) as tenant_count,
                SUM(CASE WHEN auto_payout_enabled = 1 THEN 1 ELSE 0 END) as auto_enabled_count,
                AVG(minimum_amount) as avg_minimum_amount,
                AVG(instant_payout_threshold) as avg_instant_threshold
            FROM {$this->table}
            GROUP BY frequency
        ";
        
        return $this->db->select($sql);
    }
    
    public function getUpcomingPayouts($days = 7)
    {
        $sql = "
            SELECT ps.*, tb.available, t.name as tenant_name, t.email as tenant_email
            FROM {$this->table} ps
            INNER JOIN tenant_balances tb ON ps.tenant_id = tb.tenant_id
            INNER JOIN tenants t ON ps.tenant_id = t.id
            WHERE ps.auto_payout_enabled = 1
            AND ps.next_payout_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
            AND tb.available >= ps.minimum_amount
            AND t.active = 1
            ORDER BY ps.next_payout_date ASC
        ";
        
        return $this->db->select($sql, ['days' => $days]);
    }
    
    public function enableAutoPayout($tenantId, $frequency = self::FREQUENCY_MONTHLY, $minimumAmount = 10.00)
    {
        $schedule = $this->getScheduleByTenant($tenantId);
        
        return $this->update($schedule['id'], [
            'frequency' => $frequency,
            'minimum_amount' => $minimumAmount,
            'auto_payout_enabled' => 1,
            'next_payout_date' => $this->calculateNextPayoutDate($frequency, $schedule['payout_day'])
        ]);
    }
    
    public function disableAutoPayout($tenantId)
    {
        $schedule = $this->getScheduleByTenant($tenantId);
        
        return $this->update($schedule['id'], [
            'auto_payout_enabled' => 0,
            'next_payout_date' => null
        ]);
    }
}
