<?php

namespace SmartCast\Helpers;

// Load env helper
require_once __DIR__ . '/env.php';

/**
 * USSD Helper Functions
 */
class UssdHelper
{
    /**
     * Get USSD configuration
     */
    private static function getConfig()
    {
        static $config = null;
        
        if ($config === null) {
            $configFile = __DIR__ . '/../../config/ussd_config.php';
            $config = file_exists($configFile) ? require $configFile : [
                'base_code' => '711',
                'format' => '*{base}*{tenant}#',
                'tenant_code_length' => 2,
                'max_tenants' => 99
            ];
        }
        
        return $config;
    }
    
    /**
     * Get the base USSD code
     * 
     * @return string (e.g., '920')
     */
    public static function getBaseCode()
    {
        $config = self::getConfig();
        return $config['base_code'];
    }
    
    /**
     * Format a full USSD code for a tenant
     * 
     * @param string $tenantCode The tenant's code (e.g., '01')
     * @return string The full USSD code (e.g., '*920*01#')
     */
    public static function formatUssdCode($tenantCode)
    {
        $config = self::getConfig();
        $baseCode = $config['base_code'];
        $format = $config['format'];
        
        return str_replace(
            ['{base}', '{tenant}'],
            [$baseCode, $tenantCode],
            $format
        );
    }
    
    /**
     * Get the base USSD code with formatting for display
     * 
     * @return string (e.g., '*920*')
     */
    public static function getBaseCodeFormatted()
    {
        $baseCode = self::getBaseCode();
        return "*{$baseCode}*";
    }
    
    /**
     * Extract tenant code from service code
     * 
     * Handles multiple formats:
     * - Hubtel format: "711*734" (no asterisks/hash)
     * - Full format: "*711*734#"
     * - Message format: "*711*734#"
     * 
     * @param string $serviceCode USSD code in any format
     * @return string|null Tenant code (e.g., '734') or null if invalid
     */
    public static function extractTenantCode($serviceCode)
    {
        $baseCode = self::getBaseCode();
        
        // Try multiple patterns to handle different formats
        $patterns = [
            // Hubtel ServiceCode format: "711*734"
            '/' . preg_quote($baseCode, '/') . '\*(\d+)/',
            // Full format: "*711*734#"
            '/\*' . preg_quote($baseCode, '/') . '\*(\d+)#/',
            // Partial format: "*711*734"
            '/\*' . preg_quote($baseCode, '/') . '\*(\d+)/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $serviceCode, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Validate tenant code format
     * 
     * @param string $tenantCode
     * @return bool
     */
    public static function isValidTenantCode($tenantCode)
    {
        // Accept codes from 1 to 999 (flexible length)
        return preg_match('/^\d{1,3}$/', $tenantCode) && $tenantCode >= 1 && $tenantCode <= 999;
    }
    
    /**
     * Get maximum number of tenants
     * 
     * @return int
     */
    public static function getMaxTenants()
    {
        $config = self::getConfig();
        return $config['max_tenants'];
    }
    
    /**
     * Get tenant code length
     * 
     * @return int
     */
    public static function getTenantCodeLength()
    {
        $config = self::getConfig();
        return $config['tenant_code_length'];
    }
    
    /**
     * Pad tenant code to required length
     * 
     * @param int|string $code
     * @return string
     */
    public static function padTenantCode($code)
    {
        $length = self::getTenantCodeLength();
        return str_pad($code, $length, '0', STR_PAD_LEFT);
    }
}
