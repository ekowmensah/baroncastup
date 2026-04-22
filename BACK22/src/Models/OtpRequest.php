<?php

namespace SmartCast\Models;

/**
 * OTP Request Model
 */
class OtpRequest extends BaseModel
{
    protected $table = 'otp_requests';
    protected $fillable = [
        'msisdn', 'otp', 'expires_at', 'consumed'
    ];
    
    public function generateOtp($msisdn, $expiryMinutes = 5)
    {
        // Generate 6-digit OTP
        $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Hash the OTP for security
        $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);
        
        // Set expiry time
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiryMinutes} minutes"));
        
        // Clean up old OTPs for this number
        $this->cleanupOldOtps($msisdn);
        
        // Create new OTP request
        $otpId = $this->create([
            'msisdn' => $msisdn,
            'otp' => $hashedOtp,
            'expires_at' => $expiresAt,
            'consumed' => 0
        ]);
        
        return [
            'id' => $otpId,
            'otp' => $otp, // Return plain OTP for sending
            'expires_at' => $expiresAt
        ];
    }
    
    public function verifyOtp($msisdn, $otp)
    {
        // Find active OTP for this number
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE msisdn = :msisdn 
            AND consumed = 0 
            AND expires_at > NOW() 
            ORDER BY created_at DESC 
            LIMIT 1
        ";
        
        $otpRecord = $this->db->selectOne($sql, ['msisdn' => $msisdn]);
        
        if (!$otpRecord) {
            return false;
        }
        
        // Verify OTP
        if (password_verify($otp, $otpRecord['otp'])) {
            // Mark as consumed
            $this->update($otpRecord['id'], ['consumed' => 1]);
            return true;
        }
        
        return false;
    }
    
    public function cleanupOldOtps($msisdn)
    {
        $sql = "DELETE FROM {$this->table} WHERE msisdn = :msisdn AND (consumed = 1 OR expires_at < NOW())";
        return $this->db->query($sql, ['msisdn' => $msisdn]);
    }
    
    public function getActiveOtp($msisdn)
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE msisdn = :msisdn 
            AND consumed = 0 
            AND expires_at > NOW() 
            ORDER BY created_at DESC 
            LIMIT 1
        ";
        
        return $this->db->selectOne($sql, ['msisdn' => $msisdn]);
    }
    
    public function isRateLimited($msisdn, $maxAttempts = 3, $windowMinutes = 60)
    {
        $windowStart = date('Y-m-d H:i:s', strtotime("-{$windowMinutes} minutes"));
        
        $sql = "
            SELECT COUNT(*) as attempt_count 
            FROM {$this->table} 
            WHERE msisdn = :msisdn 
            AND created_at >= :window_start
        ";
        
        $result = $this->db->selectOne($sql, [
            'msisdn' => $msisdn,
            'window_start' => $windowStart
        ]);
        
        return ($result['attempt_count'] ?? 0) >= $maxAttempts;
    }
}
