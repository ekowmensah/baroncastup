<?php

namespace SmartCast\Controllers;

use SmartCast\Core\Controller;
use SmartCast\Models\OtpRequest;
use SmartCast\Models\User;

/**
 * OTP Controller
 * Handles OTP generation and verification for payment security
 */
class OtpController extends Controller
{
    private $otpModel;
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->otpModel = new OtpRequest();
        $this->userModel = new User();
    }
    
    /**
     * Send OTP for payment verification
     * POST /api/otp/send-payment-otp
     */
    public function sendPaymentOtp()
    {
        try {
            $data = $this->getJsonInput();
            
            // Validate phone number
            $phone = $data['phone'] ?? '';
            if (empty($phone)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Phone number is required'
                ], 400);
            }
            
            // Format phone number
            $phone = $this->formatPhoneNumber($phone);
            
            // Check if user is registered (skip OTP for registered users)
            if ($this->isRegisteredUser($phone)) {
                return $this->json([
                    'success' => true,
                    'skip_otp' => true,
                    'message' => 'Registered user - OTP not required',
                    'phone' => $phone
                ]);
            }
            
            // Check rate limiting
            if ($this->otpModel->isRateLimited($phone, 3, 60)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Too many OTP requests. Please try again in 1 hour.',
                    'error_code' => 'RATE_LIMITED'
                ], 429);
            }
            
            // Generate OTP
            $otpData = $this->otpModel->generateOtp($phone, 5);
            
            // Send OTP via SMS
            $smsSent = $this->sendOtpSms($phone, $otpData['otp']);
            
            if (!$smsSent) {
                return $this->json([
                    'success' => false,
                    'message' => 'Failed to send OTP. Please check your phone number.',
                    'error_code' => 'SMS_DELIVERY_FAILED'
                ], 500);
            }
            
            // Log OTP request
            error_log("OTP sent to {$phone}: {$otpData['otp']} (expires: {$otpData['expires_at']})");
            
            return $this->json([
                'success' => true,
                'message' => 'OTP sent successfully to ' . $this->maskPhoneNumber($phone),
                'expires_at' => $otpData['expires_at'],
                'expires_in_seconds' => 300,
                'phone' => $phone,
                'otp_id' => $otpData['id']
            ]);
            
        } catch (\Exception $e) {
            error_log("Send OTP error: " . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
                'error_code' => 'OTP_SEND_ERROR'
            ], 500);
        }
    }
    
    /**
     * Verify OTP for payment
     * POST /api/otp/verify-payment-otp
     */
    public function verifyPaymentOtp()
    {
        try {
            $data = $this->getJsonInput();
            
            // Validate input
            $phone = $data['phone'] ?? '';
            $otp = $data['otp'] ?? '';
            
            if (empty($phone) || empty($otp)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Phone number and OTP are required'
                ], 400);
            }
            
            // Format phone number
            $phone = $this->formatPhoneNumber($phone);
            
            // Verify OTP
            $isValid = $this->otpModel->verifyOtp($phone, $otp);
            
            if (!$isValid) {
                return $this->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP. Please try again.',
                    'error_code' => 'INVALID_OTP'
                ], 400);
            }
            
            // Generate session token for verified phone
            $sessionToken = $this->generateSessionToken($phone);
            
            // Store verification in database
            $verificationId = $this->storeOtpVerification($phone, $sessionToken);
            
            // Store in session
            $_SESSION['payment_otp_verified'] = true;
            $_SESSION['payment_phone'] = $phone;
            $_SESSION['payment_session_token'] = $sessionToken;
            $_SESSION['payment_verification_id'] = $verificationId;
            $_SESSION['payment_verification_expires'] = time() + 600; // 10 minutes
            
            return $this->json([
                'success' => true,
                'message' => 'Phone number verified successfully',
                'session_token' => $sessionToken,
                'phone' => $phone,
                'expires_at' => date('Y-m-d H:i:s', time() + 600),
                'expires_in_seconds' => 600
            ]);
            
        } catch (\Exception $e) {
            error_log("Verify OTP error: " . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Failed to verify OTP. Please try again.',
                'error_code' => 'OTP_VERIFY_ERROR'
            ], 500);
        }
    }
    
    /**
     * Check if phone number belongs to registered user
     */
    private function isRegisteredUser($phone)
    {
        $sql = "SELECT id FROM users WHERE phone = :phone LIMIT 1";
        $user = $this->db->selectOne($sql, ['phone' => $phone]);
        return !empty($user);
    }
    
    /**
     * Send OTP via SMS
     */
    private function sendOtpSms($phone, $otp)
    {
        // TODO: Integrate with SMS gateway (Hubtel SMS, Twilio, etc.)
        // For now, log the OTP (development mode)
        
        $message = "Your SmartCast verification code is: {$otp}. Valid for 5 minutes. Do not share this code.";
        
        // In development, just log it
        if (defined('APP_DEBUG') && APP_DEBUG === true) {
            error_log("SMS to {$phone}: {$message}");
            return true;
        }
        
        // Production: Send via SMS gateway
        try {
            // Example: Hubtel SMS API
            // $smsService = new HubtelSmsService();
            // return $smsService->sendSms($phone, $message);
            
            // For now, return true (implement actual SMS sending)
            return true;
            
        } catch (\Exception $e) {
            error_log("SMS sending error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate session token for verified phone
     */
    private function generateSessionToken($phone)
    {
        return hash('sha256', $phone . time() . bin2hex(random_bytes(16)));
    }
    
    /**
     * Store OTP verification in database
     */
    private function storeOtpVerification($phone, $sessionToken)
    {
        // Get the latest OTP request for this phone
        $otpRequest = $this->otpModel->getActiveOtp($phone);
        
        $sql = "INSERT INTO payment_otp_verifications (
            phone_number, otp_request_id, verified_at, expires_at, 
            session_token, created_at
        ) VALUES (
            :phone, :otp_request_id, NOW(), DATE_ADD(NOW(), INTERVAL 10 MINUTE),
            :session_token, NOW()
        )";
        
        $this->db->query($sql, [
            'phone' => $phone,
            'otp_request_id' => $otpRequest['id'] ?? null,
            'session_token' => $sessionToken
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Validate session token
     */
    public function validateSessionToken($phone, $sessionToken)
    {
        $sql = "SELECT * FROM payment_otp_verifications 
                WHERE phone_number = :phone 
                AND session_token = :token 
                AND expires_at > NOW() 
                AND used_for_payment = 0
                ORDER BY created_at DESC 
                LIMIT 1";
        
        $verification = $this->db->selectOne($sql, [
            'phone' => $phone,
            'token' => $sessionToken
        ]);
        
        return !empty($verification);
    }
    
    /**
     * Mark session token as used
     */
    public function markSessionTokenUsed($sessionToken, $paymentTransactionId)
    {
        $sql = "UPDATE payment_otp_verifications 
                SET used_for_payment = 1, 
                    payment_transaction_id = :payment_id,
                    updated_at = NOW()
                WHERE session_token = :token";
        
        return $this->db->query($sql, [
            'token' => $sessionToken,
            'payment_id' => $paymentTransactionId
        ]);
    }
    
    /**
     * Format phone number
     */
    private function formatPhoneNumber($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Handle Ghana phone numbers
        if (strlen($phone) === 10 && substr($phone, 0, 1) === '0') {
            $phone = '233' . substr($phone, 1);
        } elseif (strlen($phone) === 9) {
            $phone = '233' . $phone;
        }
        
        return $phone;
    }
    
    /**
     * Mask phone number for display
     */
    private function maskPhoneNumber($phone)
    {
        if (strlen($phone) > 6) {
            return substr($phone, 0, 3) . '****' . substr($phone, -3);
        }
        return $phone;
    }
}
