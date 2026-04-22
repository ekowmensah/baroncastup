<?php

namespace SmartCast\Models;

/**
 * Tenant Setting Model
 */
class TenantSetting extends BaseModel
{
    protected $table = 'tenant_settings';
    protected $fillable = [
        'tenant_id', 'setting_key', 'setting_value'
    ];
    
    // Default settings
    const SETTING_OTP_REQUIRED = 'otp_required';
    const SETTING_LEADERBOARD_LAG = 'leaderboard_lag_seconds';
    const SETTING_THEME_JSON = 'theme_json';
    const SETTING_MAX_VOTES_PER_MSISDN = 'max_votes_per_msisdn';
    const SETTING_FRAUD_DETECTION_ENABLED = 'fraud_detection_enabled';
    const SETTING_WEBHOOK_ENABLED = 'webhook_enabled';
    const SETTING_EMAIL_NOTIFICATIONS = 'email_notifications_enabled';
    const SETTING_SMS_NOTIFICATIONS = 'sms_notifications_enabled';
    const SETTING_AUTO_APPROVE_EVENTS = 'auto_approve_events';
    const SETTING_MINIMUM_PAYOUT = 'minimum_payout_amount';
    
    public function getSetting($tenantId, $key, $default = null)
    {
        $setting = $this->findAll([
            'tenant_id' => $tenantId,
            'setting_key' => $key
        ], null, 1);
        
        if (empty($setting)) {
            return $default;
        }
        
        return $this->parseSettingValue($setting[0]['setting_value']);
    }
    
    public function setSetting($tenantId, $key, $value)
    {
        $existingSetting = $this->findAll([
            'tenant_id' => $tenantId,
            'setting_key' => $key
        ], null, 1);
        
        $settingValue = $this->formatSettingValue($value);
        
        if (empty($existingSetting)) {
            return $this->create([
                'tenant_id' => $tenantId,
                'setting_key' => $key,
                'setting_value' => $settingValue
            ]);
        } else {
            return $this->update($existingSetting[0]['id'], [
                'setting_value' => $settingValue
            ]);
        }
    }
    
    public function getAllSettings($tenantId)
    {
        $settings = $this->findAll(['tenant_id' => $tenantId]);
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $this->parseSettingValue($setting['setting_value']);
        }
        
        // Add default values for missing settings
        $defaults = $this->getDefaultSettings();
        foreach ($defaults as $key => $value) {
            if (!isset($result[$key])) {
                $result[$key] = $value;
            }
        }
        
        return $result;
    }
    
    public function updateMultipleSettings($tenantId, $settings)
    {
        foreach ($settings as $key => $value) {
            $this->setSetting($tenantId, $key, $value);
        }
        
        return true;
    }
    
    public function getDefaultSettings()
    {
        return [
            self::SETTING_OTP_REQUIRED => false,
            self::SETTING_LEADERBOARD_LAG => 30,
            self::SETTING_THEME_JSON => [
                'primary_color' => '#007bff',
                'secondary_color' => '#6c757d',
                'success_color' => '#28a745',
                'danger_color' => '#dc3545'
            ],
            self::SETTING_MAX_VOTES_PER_MSISDN => 10000,
            self::SETTING_FRAUD_DETECTION_ENABLED => true,
            self::SETTING_WEBHOOK_ENABLED => false,
            self::SETTING_EMAIL_NOTIFICATIONS => true,
            self::SETTING_SMS_NOTIFICATIONS => false,
            self::SETTING_AUTO_APPROVE_EVENTS => false,
            self::SETTING_MINIMUM_PAYOUT => 10.00
        ];
    }
    
    public function initializeDefaultSettings($tenantId)
    {
        $defaults = $this->getDefaultSettings();
        
        foreach ($defaults as $key => $value) {
            $this->setSetting($tenantId, $key, $value);
        }
        
        return true;
    }
    
    private function parseSettingValue($value)
    {
        if ($value === null) {
            return null;
        }
        
        // Try to decode as JSON first
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }
        
        // Handle boolean strings
        if (strtolower($value) === 'true') {
            return true;
        }
        if (strtolower($value) === 'false') {
            return false;
        }
        
        // Handle numeric strings
        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float)$value : (int)$value;
        }
        
        // Return as string
        return $value;
    }
    
    private function formatSettingValue($value)
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }
        
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        
        return (string)$value;
    }
    
    public function getThemeSettings($tenantId)
    {
        $theme = $this->getSetting($tenantId, self::SETTING_THEME_JSON, []);
        
        if (empty($theme)) {
            return $this->getDefaultSettings()[self::SETTING_THEME_JSON];
        }
        
        return $theme;
    }
    
    public function updateThemeSettings($tenantId, $themeData)
    {
        $currentTheme = $this->getThemeSettings($tenantId);
        $updatedTheme = array_merge($currentTheme, $themeData);
        
        return $this->setSetting($tenantId, self::SETTING_THEME_JSON, $updatedTheme);
    }
    
    public function isOtpRequired($tenantId)
    {
        return $this->getSetting($tenantId, self::SETTING_OTP_REQUIRED, false);
    }
    
    public function isFraudDetectionEnabled($tenantId)
    {
        return $this->getSetting($tenantId, self::SETTING_FRAUD_DETECTION_ENABLED, true);
    }
    
    public function getMaxVotesPerMsisdn($tenantId)
    {
        return $this->getSetting($tenantId, self::SETTING_MAX_VOTES_PER_MSISDN, 10000);
    }
    
    public function getLeaderboardLag($tenantId)
    {
        return $this->getSetting($tenantId, self::SETTING_LEADERBOARD_LAG, 30);
    }
    
    public function getMinimumPayoutAmount($tenantId)
    {
        return $this->getSetting($tenantId, self::SETTING_MINIMUM_PAYOUT, 10.00);
    }
    
    public function exportSettings($tenantId)
    {
        $settings = $this->getAllSettings($tenantId);
        
        return [
            'tenant_id' => $tenantId,
            'exported_at' => date('Y-m-d H:i:s'),
            'settings' => $settings
        ];
    }
    
    public function importSettings($tenantId, $settingsData)
    {
        if (!isset($settingsData['settings']) || !is_array($settingsData['settings'])) {
            throw new \Exception('Invalid settings data format');
        }
        
        return $this->updateMultipleSettings($tenantId, $settingsData['settings']);
    }
    
    public function resetToDefaults($tenantId)
    {
        // Delete all existing settings
        $this->db->delete($this->table, 'tenant_id = :tenant_id', ['tenant_id' => $tenantId]);
        
        // Initialize with defaults
        return $this->initializeDefaultSettings($tenantId);
    }
}
