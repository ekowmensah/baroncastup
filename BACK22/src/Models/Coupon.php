<?php

namespace SmartCast\Models;

/**
 * Coupon Model - For discount codes and promotions
 */
class Coupon extends BaseModel
{
    protected $table = 'coupons';
    protected $fillable = [
        'tenant_id', 'event_id', 'code', 'type', 'value', 'minimum_amount',
        'usage_limit', 'used_count', 'expires_at', 'active', 'created_by'
    ];
    
    // Note: This table doesn't exist in the schema, but the coupon_code column 
    // in transactions suggests it should exist. We'll create it virtually.
    
    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_FIXED = 'fixed';
    const TYPE_FREE_VOTES = 'free_votes';
    
    public function validateCoupon($code, $tenantId, $eventId = null, $amount = 0)
    {
        // Since the coupons table doesn't exist in the schema, 
        // we'll implement a simple validation system
        
        $validCoupons = $this->getValidCoupons($tenantId, $eventId);
        
        foreach ($validCoupons as $coupon) {
            if (strtoupper($coupon['code']) === strtoupper($code)) {
                // Check if coupon is still valid
                if ($this->isCouponValid($coupon, $amount)) {
                    return [
                        'valid' => true,
                        'coupon' => $coupon,
                        'discount' => $this->calculateDiscount($coupon, $amount)
                    ];
                }
            }
        }
        
        return [
            'valid' => false,
            'error' => 'Invalid or expired coupon code'
        ];
    }
    
    private function getValidCoupons($tenantId, $eventId = null)
    {
        // Since we don't have a coupons table, return predefined coupons
        // In a real implementation, this would query the coupons table
        
        return [
            [
                'id' => 1,
                'code' => 'WELCOME10',
                'type' => self::TYPE_PERCENTAGE,
                'value' => 10,
                'minimum_amount' => 5.00,
                'usage_limit' => 1000,
                'used_count' => 0,
                'expires_at' => '2025-12-31 23:59:59',
                'active' => 1,
                'tenant_id' => $tenantId,
                'event_id' => null
            ],
            [
                'id' => 2,
                'code' => 'SAVE5',
                'type' => self::TYPE_FIXED,
                'value' => 5.00,
                'minimum_amount' => 20.00,
                'usage_limit' => 500,
                'used_count' => 0,
                'expires_at' => '2025-12-31 23:59:59',
                'active' => 1,
                'tenant_id' => $tenantId,
                'event_id' => $eventId
            ],
            [
                'id' => 3,
                'code' => 'FREEVOTE',
                'type' => self::TYPE_FREE_VOTES,
                'value' => 1,
                'minimum_amount' => 0,
                'usage_limit' => 100,
                'used_count' => 0,
                'expires_at' => '2025-12-31 23:59:59',
                'active' => 1,
                'tenant_id' => $tenantId,
                'event_id' => null
            ]
        ];
    }
    
    private function isCouponValid($coupon, $amount)
    {
        // Check if active
        if (!$coupon['active']) {
            return false;
        }
        
        // Check expiry
        if (strtotime($coupon['expires_at']) < time()) {
            return false;
        }
        
        // Check usage limit
        if ($coupon['usage_limit'] > 0 && $coupon['used_count'] >= $coupon['usage_limit']) {
            return false;
        }
        
        // Check minimum amount
        if ($amount < $coupon['minimum_amount']) {
            return false;
        }
        
        return true;
    }
    
    public function calculateDiscount($coupon, $amount)
    {
        switch ($coupon['type']) {
            case self::TYPE_PERCENTAGE:
                $discount = ($amount * $coupon['value']) / 100;
                return min($discount, $amount); // Can't discount more than the total
                
            case self::TYPE_FIXED:
                return min($coupon['value'], $amount);
                
            case self::TYPE_FREE_VOTES:
                // For free votes, return the value as bonus votes
                return [
                    'type' => 'free_votes',
                    'votes' => $coupon['value'],
                    'monetary_discount' => 0
                ];
                
            default:
                return 0;
        }
    }
    
    public function applyCoupon($code, $transactionId, $tenantId, $eventId = null, $amount = 0)
    {
        $validation = $this->validateCoupon($code, $tenantId, $eventId, $amount);
        
        if (!$validation['valid']) {
            return $validation;
        }
        
        $coupon = $validation['coupon'];
        $discount = $validation['discount'];
        
        // In a real implementation, we would:
        // 1. Update the coupon usage count
        // 2. Log the coupon usage
        // 3. Apply the discount to the transaction
        
        return [
            'success' => true,
            'coupon_code' => $code,
            'discount' => $discount,
            'final_amount' => is_array($discount) ? $amount : max(0, $amount - $discount)
        ];
    }
    
    public function getCouponUsage($code, $tenantId)
    {
        // In a real implementation, this would query the database
        return [
            'code' => $code,
            'total_usage' => 0,
            'usage_limit' => 1000,
            'remaining_uses' => 1000
        ];
    }
    
    public function generateCouponCode($prefix = '', $length = 8)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = $prefix;
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        
        return $code;
    }
    
    public function getCouponStats($tenantId, $eventId = null)
    {
        // Mock stats since we don't have the actual table
        return [
            'total_coupons' => 3,
            'active_coupons' => 3,
            'expired_coupons' => 0,
            'total_usage' => 0,
            'total_discount_given' => 0.00
        ];
    }
    
    // Methods for when the coupons table is actually created
    
    public function createCoupon($tenantId, $data)
    {
        $couponData = array_merge($data, [
            'tenant_id' => $tenantId,
            'code' => $data['code'] ?? $this->generateCouponCode(),
            'used_count' => 0,
            'active' => 1
        ]);
        
        // This would work when the coupons table exists
        // return $this->create($couponData);
        
        return ['message' => 'Coupons table not implemented in schema'];
    }
    
    public function deactivateCoupon($couponId)
    {
        // return $this->update($couponId, ['active' => 0]);
        return ['message' => 'Coupons table not implemented in schema'];
    }
    
    public function incrementUsage($couponId)
    {
        // $coupon = $this->find($couponId);
        // if ($coupon) {
        //     return $this->update($couponId, ['used_count' => $coupon['used_count'] + 1]);
        // }
        return ['message' => 'Coupons table not implemented in schema'];
    }
}
