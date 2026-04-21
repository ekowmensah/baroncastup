<?php

namespace SmartCast\Services;

use SmartCast\Services\EmailServiceSimple;
use SmartCast\Services\SmsService;

class NotificationService
{
    private $emailService;
    private $smsService;
    
    public function __construct()
    {
        $this->emailService = new EmailServiceSimple();
        $this->smsService = new SmsService();
    }
    
    /**
     * Send tenant approval notifications (both email and SMS)
     */
    public function sendTenantApprovalNotifications($tenantData)
    {
        $results = [
            'email' => ['success' => false, 'error' => 'Not attempted'],
            'sms' => ['success' => false, 'error' => 'Not attempted']
        ];
        
        // Send email notification
        try {
            $emailResult = $this->emailService->sendTenantApprovalEmail($tenantData);
            $results['email'] = $emailResult;
        } catch (\Exception $e) {
            $results['email'] = ['success' => false, 'error' => $e->getMessage()];
            error_log("Tenant approval email failed: " . $e->getMessage());
        }
        
        // Send SMS notification (if phone number is available)
        if (!empty($tenantData['phone'])) {
            try {
                $smsResult = $this->sendTenantApprovalSms($tenantData);
                $results['sms'] = $smsResult;
            } catch (\Exception $e) {
                $results['sms'] = ['success' => false, 'error' => $e->getMessage()];
                error_log("Tenant approval SMS failed: " . $e->getMessage());
            }
        } else {
            $results['sms'] = ['success' => false, 'error' => 'No phone number provided'];
        }
        
        return $results;
    }
    
    /**
     * Send tenant approval SMS
     */
    private function sendTenantApprovalSms($tenantData)
    {
        try {
            // Get active SMS gateway
            $gateway = $this->smsService->getActiveGateway();
            if (!$gateway) {
                throw new \Exception('No active SMS gateway configured');
            }
            
            // Format SMS message
            $message = $this->getTenantApprovalSmsMessage($tenantData);
            
            // Send SMS
            $result = $this->smsService->sendSms($gateway, $tenantData['phone'], $message);
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Tenant approval SMS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get tenant approval SMS message template
     */
    private function getTenantApprovalSmsMessage($tenantData)
    {
        $loginUrl = APP_URL . '/login';
        
        return "🎉 Great news! Your SmartCast account has been APPROVED!\n\n" .
               "Organization: {$tenantData['name']}\n" .
               "Plan: {$tenantData['plan']}\n\n" .
               "You can now login and start creating voting events.\n\n" .
               "Login: {$loginUrl}\n\n" .
               "Welcome to SmartCast!";
    }
    
    /**
     * Send tenant rejection notifications
     */
    public function sendTenantRejectionNotifications($tenantData, $reason = '')
    {
        $results = [
            'email' => ['success' => false, 'error' => 'Not attempted'],
            'sms' => ['success' => false, 'error' => 'Not attempted']
        ];
        
        // Send email notification
        try {
            $emailResult = $this->sendTenantRejectionEmail($tenantData, $reason);
            $results['email'] = $emailResult;
        } catch (\Exception $e) {
            $results['email'] = ['success' => false, 'error' => $e->getMessage()];
            error_log("Tenant rejection email failed: " . $e->getMessage());
        }
        
        // Send SMS notification (if phone number is available)
        if (!empty($tenantData['phone'])) {
            try {
                $smsResult = $this->sendTenantRejectionSms($tenantData, $reason);
                $results['sms'] = $smsResult;
            } catch (\Exception $e) {
                $results['sms'] = ['success' => false, 'error' => $e->getMessage()];
                error_log("Tenant rejection SMS failed: " . $e->getMessage());
            }
        } else {
            $results['sms'] = ['success' => false, 'error' => 'No phone number provided'];
        }
        
        return $results;
    }
    
    /**
     * Send tenant rejection email
     */
    private function sendTenantRejectionEmail($tenantData, $reason)
    {
        $subject = "Account Application Update - SmartCast";
        $supportEmail = $_ENV['SUPPORT_EMAIL'] ?? 'support@smartcast.com.gh';
        
        $body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Account Application Update - SmartCast</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #f8f9fa; color: #333; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; border-top: 4px solid #dc3545; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .footer { text-align: center; margin-top: 30px; font-size: 14px; color: #666; }
                .highlight { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Account Application Update</h1>
                    <p>SmartCast Account Review</p>
                </div>
                
                <div class='content'>
                    <h2>Dear {$tenantData['name']},</h2>
                    
                    <p>Thank you for your interest in SmartCast. After reviewing your account application, we are unable to approve it at this time.</p>
                    
                    " . (!empty($reason) ? "
                    <div class='highlight'>
                        <strong>Reason:</strong><br>
                        {$reason}
                    </div>
                    " : "") . "
                    
                    <p>If you believe this decision was made in error or if you have additional information to provide, please contact our support team at <a href='mailto:{$supportEmail}'>{$supportEmail}</a>.</p>
                    
                    <p>We appreciate your understanding and interest in SmartCast.</p>
                    
                    <p>Best regards,<br>
                    <strong>The SmartCast Team</strong></p>
                </div>
                
                <div class='footer'>
                    <p>&copy; " . date('Y') . " SmartCast. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
        
        return $this->emailService->sendEmail(
            $tenantData['email'],
            $tenantData['name'],
            $subject,
            $body
        );
    }
    
    /**
     * Send tenant rejection SMS
     */
    private function sendTenantRejectionSms($tenantData, $reason)
    {
        $gateway = $this->smsService->getActiveGateway();
        if (!$gateway) {
            throw new \Exception('No active SMS gateway configured');
        }
        
        $message = "SmartCast Account Update\n\n" .
                   "Your account application for {$tenantData['name']} could not be approved at this time.\n\n" .
                   (!empty($reason) ? "Reason: {$reason}\n\n" : "") .
                   "Contact support@smartcast.com.gh for assistance.\n\n" .
                   "Thank you for your interest in SmartCast.";
        
        return $this->smsService->sendSms($gateway, $tenantData['phone'], $message);
    }
}
