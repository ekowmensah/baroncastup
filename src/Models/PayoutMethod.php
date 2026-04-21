<?php

namespace SmartCast\Models;

/**
 * Payout Method Model
 */
class PayoutMethod extends BaseModel
{
    protected $table = 'payout_methods';
    protected $fillable = [
        'tenant_id', 'method_type', 'method_name', 'account_details',
        'is_default', 'is_verified', 'verification_data', 'active'
    ];
    
    const TYPE_BANK_TRANSFER = 'bank_transfer';
    const TYPE_MOBILE_MONEY = 'mobile_money';
    const TYPE_PAYPAL = 'paypal';
    const TYPE_STRIPE = 'stripe';
    
    public function getMethodsByTenant($tenantId, $activeOnly = true)
    {
        $conditions = ['tenant_id' => $tenantId];
        
        if ($activeOnly) {
            $conditions['active'] = 1;
        }
        
        return $this->findAll($conditions, 'is_default DESC, created_at DESC');
    }
    
    public function getDefaultMethod($tenantId)
    {
        $method = $this->findAll([
            'tenant_id' => $tenantId,
            'is_default' => 1,
            'active' => 1
        ], null, 1);
        
        return !empty($method) ? $method[0] : null;
    }
    
    public function setDefaultMethod($tenantId, $methodId)
    {
        // First, unset all default methods for this tenant
        $this->db->update(
            $this->table,
            ['is_default' => 0],
            ['tenant_id' => $tenantId]
        );
        
        // Set the new default method
        return $this->update($methodId, ['is_default' => 1]);
    }
    
    public function createMethod($tenantId, $methodType, $methodName, $accountDetails)
    {
        // Validate method type
        $validTypes = [
            self::TYPE_BANK_TRANSFER,
            self::TYPE_MOBILE_MONEY,
            self::TYPE_PAYPAL,
            self::TYPE_STRIPE
        ];
        
        if (!in_array($methodType, $validTypes)) {
            throw new \Exception('Invalid payout method type');
        }
        
        // Validate account details based on method type
        $this->validateAccountDetails($methodType, $accountDetails);
        
        // Check if this is the first method for the tenant (make it default)
        $existingMethods = $this->getMethodsByTenant($tenantId);
        $isDefault = empty($existingMethods) ? 1 : 0;
        
        return $this->create([
            'tenant_id' => $tenantId,
            'method_type' => $methodType,
            'method_name' => $methodName,
            'account_details' => json_encode($accountDetails),
            'is_default' => $isDefault,
            'is_verified' => 0,
            'active' => 1
        ]);
    }
    
    public function updateMethod($methodId, $methodName, $accountDetails)
    {
        $method = $this->find($methodId);
        if (!$method) {
            throw new \Exception('Payout method not found');
        }
        
        // Validate account details
        $this->validateAccountDetails($method['method_type'], $accountDetails);
        
        return $this->update($methodId, [
            'method_name' => $methodName,
            'account_details' => json_encode($accountDetails),
            'is_verified' => 0 // Reset verification when details change
        ]);
    }
    
    public function verifyMethod($methodId, $verificationData = null)
    {
        return $this->update($methodId, [
            'is_verified' => 1,
            'verification_data' => $verificationData ? json_encode($verificationData) : null
        ]);
    }
    
    public function deactivateMethod($methodId)
    {
        $method = $this->find($methodId);
        if (!$method) {
            throw new \Exception('Payout method not found');
        }
        
        // If this was the default method, set another as default
        if ($method['is_default']) {
            $otherMethods = $this->findAll([
                'tenant_id' => $method['tenant_id'],
                'active' => 1,
                'id' => ['!=', $methodId]
            ], 'created_at DESC', 1);
            
            if (!empty($otherMethods)) {
                $this->update($otherMethods[0]['id'], ['is_default' => 1]);
            }
        }
        
        return $this->update($methodId, ['active' => 0, 'is_default' => 0]);
    }
    
    private function validateAccountDetails($methodType, $accountDetails)
    {
        switch ($methodType) {
            case self::TYPE_BANK_TRANSFER:
                if (empty($accountDetails['account_number']) || 
                    empty($accountDetails['bank_name']) || 
                    empty($accountDetails['account_name'])) {
                    throw new \Exception('Bank transfer requires account number, bank name, and account name');
                }
                break;
                
            case self::TYPE_MOBILE_MONEY:
                if (empty($accountDetails['phone_number']) || 
                    empty($accountDetails['provider'])) {
                    throw new \Exception('Mobile money requires phone number and provider');
                }
                break;
                
            case self::TYPE_PAYPAL:
                if (empty($accountDetails['email'])) {
                    throw new \Exception('PayPal requires email address');
                }
                if (!filter_var($accountDetails['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception('Invalid PayPal email address');
                }
                break;
                
            case self::TYPE_STRIPE:
                if (empty($accountDetails['account_id'])) {
                    throw new \Exception('Stripe requires account ID');
                }
                break;
        }
    }
    
    public function getMethodStats($tenantId = null)
    {
        $sql = "
            SELECT 
                method_type,
                COUNT(*) as count,
                SUM(CASE WHEN is_verified = 1 THEN 1 ELSE 0 END) as verified_count,
                SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as active_count
            FROM {$this->table}
        ";
        
        $params = [];
        
        if ($tenantId) {
            $sql .= " WHERE tenant_id = :tenant_id";
            $params['tenant_id'] = $tenantId;
        }
        
        $sql .= " GROUP BY method_type";
        
        return $this->db->select($sql, $params);
    }
    
    public function getFormattedAccountDetails($method)
    {
        $details = json_decode($method['account_details'], true);
        
        switch ($method['method_type']) {
            case self::TYPE_BANK_TRANSFER:
                return [
                    'display' => $details['bank_name'] . ' - ****' . substr($details['account_number'], -4),
                    'full' => $details
                ];
                
            case self::TYPE_MOBILE_MONEY:
                return [
                    'display' => $details['provider'] . ' - ****' . substr($details['phone_number'], -4),
                    'full' => $details
                ];
                
            case self::TYPE_PAYPAL:
                return [
                    'display' => 'PayPal - ' . $details['email'],
                    'full' => $details
                ];
                
            case self::TYPE_STRIPE:
                return [
                    'display' => 'Stripe - ****' . substr($details['account_id'], -4),
                    'full' => $details
                ];
                
            default:
                return [
                    'display' => 'Unknown Method',
                    'full' => $details
                ];
        }
    }
}
