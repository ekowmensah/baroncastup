<?php

namespace SmartCast\Services;

class EmailServiceSimple
{
    private $config;
    
    public function __construct()
    {
        $this->config = $this->loadConfig();
    }
    
    /**
     * Send tenant approval email
     */
    public function sendTenantApprovalEmail($tenantData)
    {
        try {
            $subject = "Account Approved - Welcome to SmartCast!";
            $body = $this->getTenantApprovalTemplate($tenantData);
            
            return $this->sendEmail(
                $tenantData['email'],
                $tenantData['name'],
                $subject,
                $body
            );
            
        } catch (\Exception $e) {
            error_log("Email Service Error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Send email using PHP's mail function
     */
    public function sendEmail($toEmail, $toName, $subject, $body, $isHtml = true)
    {
        try {
            // Set headers
            $headers = [];
            $headers[] = 'From: ' . $this->config['from_name'] . ' <' . $this->config['from_email'] . '>';
            $headers[] = 'Reply-To: ' . $this->config['reply_to_name'] . ' <' . $this->config['reply_to_email'] . '>';
            $headers[] = 'X-Mailer: SmartCast';
            
            if ($isHtml) {
                $headers[] = 'MIME-Version: 1.0';
                $headers[] = 'Content-Type: text/html; charset=UTF-8';
            }
            
            // Send email
            $success = mail($toEmail, $subject, $body, implode("\r\n", $headers));
            
            if ($success) {
                return ['success' => true, 'message' => 'Email sent successfully'];
            } else {
                return ['success' => false, 'error' => 'Failed to send email'];
            }
            
        } catch (\Exception $e) {
            error_log("Email Error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get tenant approval email template
     */
    private function getTenantApprovalTemplate($tenantData)
    {
        $loginUrl = APP_URL . '/login';
        $supportEmail = $this->config['support_email'];
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Account Approved - SmartCast</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; font-size: 14px; color: #666; }
                .highlight { background: #e8f4fd; padding: 15px; border-left: 4px solid #667eea; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎉 Welcome to SmartCast!</h1>
                    <p>Your account has been approved</p>
                </div>
                
                <div class='content'>
                    <h2>Dear {$tenantData['name']},</h2>
                    
                    <p>Great news! Your SmartCast account has been approved and is now ready for use.</p>
                    
                    <div class='highlight'>
                        <strong>Account Details:</strong><br>
                        Organization: {$tenantData['name']}<br>
                        Email: {$tenantData['email']}<br>
                        Plan: {$tenantData['plan']}<br>
                        Status: Active
                    </div>
                    
                    <p>You can now access your account and start creating voting events for your organization.</p>
                    
                    <div style='text-align: center;'>
                        <a href='{$loginUrl}' class='button'>Login to Your Account</a>
                    </div>
                    
                    <h3>What's Next?</h3>
                    <ul>
                        <li>Login to your SmartCast dashboard</li>
                        <li>Complete your organization profile</li>
                        <li>Create your first voting event</li>
                        <li>Invite participants and start voting</li>
                    </ul>
                    
                    <p>If you have any questions or need assistance getting started, please don't hesitate to contact our support team at <a href='mailto:{$supportEmail}'>{$supportEmail}</a>.</p>
                    
                    <p>Welcome aboard!</p>
                    
                    <p>Best regards,<br>
                    <strong>The SmartCast Team</strong></p>
                </div>
                
                <div class='footer'>
                    <p>This email was sent to {$tenantData['email']} because your SmartCast account was approved.</p>
                    <p>&copy; " . date('Y') . " SmartCast. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Load email configuration
     */
    private function loadConfig()
    {
        return [
            'from_email' => $_ENV['FROM_EMAIL'] ?? 'noreply@smartcast.com.gh',
            'from_name' => $_ENV['FROM_NAME'] ?? 'SmartCast',
            'reply_to_email' => $_ENV['REPLY_TO_EMAIL'] ?? 'support@smartcast.com.gh',
            'reply_to_name' => $_ENV['REPLY_TO_NAME'] ?? 'SmartCast Support',
            'support_email' => $_ENV['SUPPORT_EMAIL'] ?? 'support@smartcast.com.gh'
        ];
    }
    
    /**
     * Send test email
     */
    public function sendTestEmail($toEmail, $toName = 'Test User')
    {
        $subject = "Test Email from SmartCast";
        $body = "
        <h2>Test Email</h2>
        <p>This is a test email from SmartCast to verify email configuration.</p>
        <p>Time: " . date('Y-m-d H:i:s') . "</p>
        <p>If you received this email, the email service is working correctly.</p>
        ";
        
        return $this->sendEmail($toEmail, $toName, $subject, $body);
    }
}
