<?php

namespace SmartCast\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
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
     * Send super admin notification for new tenant registration
     */
    public function sendNewTenantNotificationToSuperAdmin($tenantData)
    {
        try {
            $subject = "New Tenant Registration - Approval Required";
            $body = $this->getNewTenantNotificationTemplate($tenantData);
            
            // Get super admin email from config
            $superAdminEmail = $this->config['super_admin_email'];
            $superAdminName = $this->config['super_admin_name'];
            
            return $this->sendEmail(
                $superAdminEmail,
                $superAdminName,
                $subject,
                $body
            );
            
        } catch (\Exception $e) {
            error_log("Email Service Error (Super Admin Notification): " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Send email using PHPMailer
     */
    public function sendEmail($toEmail, $toName, $subject, $body, $isHtml = true)
    {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['smtp_username'];
            $mail->Password = $this->config['smtp_password'];
            $mail->SMTPSecure = $this->config['smtp_encryption'];
            $mail->Port = $this->config['smtp_port'];
            
            // Recipients
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addAddress($toEmail, $toName);
            $mail->addReplyTo($this->config['reply_to_email'], $this->config['reply_to_name']);
            
            // Content
            $mail->isHTML($isHtml);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            if ($isHtml) {
                $mail->AltBody = strip_tags($body);
            }
            
            $mail->send();
            
            return ['success' => true, 'message' => 'Email sent successfully'];
            
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            return ['success' => false, 'error' => $mail->ErrorInfo];
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
     * Get new tenant notification template for super admin
     */
    private function getNewTenantNotificationTemplate($tenantData)
    {
        $baseUrl = defined('APP_URL') ? APP_URL : 'http://localhost/smartcast';
        $approvalUrl = $baseUrl . '/superadmin/tenants/pending';
        $tenantDetailsUrl = $baseUrl . '/superadmin/tenants';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>New Tenant Registration - SmartCast</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #ff6b6b; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
                .button.secondary { background: #6c757d; }
                .footer { text-align: center; margin-top: 30px; font-size: 14px; color: #666; }
                .highlight { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
                .tenant-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #ddd; }
                .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
                .detail-label { font-weight: bold; color: #555; }
                .detail-value { color: #333; }
                .urgent { color: #dc3545; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🚨 New Tenant Registration</h1>
                    <p>Approval Required</p>
                </div>
                
                <div class='content'>
                    <div class='highlight'>
                        <p class='urgent'>⚠️ A new organization has registered and is waiting for approval.</p>
                    </div>
                    
                    <h2>Registration Details</h2>
                    
                    <div class='tenant-details'>
                        <div class='detail-row'>
                            <span class='detail-label'>Organization Name:</span>
                            <span class='detail-value'>{$tenantData['name']}</span>
                        </div>
                        <div class='detail-row'>
                            <span class='detail-label'>Email Address:</span>
                            <span class='detail-value'>{$tenantData['email']}</span>
                        </div>
                        <div class='detail-row'>
                            <span class='detail-label'>Phone Number:</span>
                            <span class='detail-value'>{$tenantData['phone']}</span>
                        </div>
                        <div class='detail-row'>
                            <span class='detail-label'>Selected Plan:</span>
                            <span class='detail-value'>{$tenantData['plan']}</span>
                        </div>
                        <div class='detail-row'>
                            <span class='detail-label'>Registration Date:</span>
                            <span class='detail-value'>" . date('F j, Y \a\t g:i A') . "</span>
                        </div>
                        <div class='detail-row'>
                            <span class='detail-label'>Status:</span>
                            <span class='detail-value' style='color: #ffc107; font-weight: bold;'>Pending Approval</span>
                        </div>
                    </div>
                    
                    <h3>Required Actions</h3>
                    <ul>
                        <li>Review the organization's registration details</li>
                        <li>Verify the legitimacy of the organization</li>
                        <li>Check for any duplicate registrations</li>
                        <li>Approve or reject the registration request</li>
                    </ul>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$approvalUrl}' class='button'>Review Pending Registrations</a>
                        <a href='{$tenantDetailsUrl}' class='button secondary'>Manage All Tenants</a>
                    </div>
                    
                    <div class='highlight'>
                        <p><strong>Note:</strong> The organization will not be able to access their account until you approve their registration. They have been notified that their account is pending approval.</p>
                    </div>
                    
                    <p>Please review and process this registration request as soon as possible to ensure a good user experience.</p>
                    
                    <p>Best regards,<br>
                    <strong>SmartCast System</strong></p>
                </div>
                
                <div class='footer'>
                    <p>This is an automated notification from the SmartCast platform.</p>
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
            'smtp_host' => $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com',
            'smtp_port' => $_ENV['SMTP_PORT'] ?? 587,
            'smtp_username' => $_ENV['SMTP_USERNAME'] ?? '',
            'smtp_password' => $_ENV['SMTP_PASSWORD'] ?? '',
            'smtp_encryption' => $_ENV['SMTP_ENCRYPTION'] ?? 'tls',
            'from_email' => $_ENV['FROM_EMAIL'] ?? 'noreply@smartcast.com.gh',
            'from_name' => $_ENV['FROM_NAME'] ?? 'SmartCast',
            'reply_to_email' => $_ENV['REPLY_TO_EMAIL'] ?? 'support@smartcast.com.gh',
            'reply_to_name' => $_ENV['REPLY_TO_NAME'] ?? 'SmartCast Support',
            'support_email' => $_ENV['SUPPORT_EMAIL'] ?? 'support@smartcast.com.gh',
            'super_admin_email' => $_ENV['SUPER_ADMIN_EMAIL'] ?? 'admin@smartcast.com.gh',
            'super_admin_name' => $_ENV['SUPER_ADMIN_NAME'] ?? 'Super Admin'
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
    
    /**
     * Send test super admin notification email
     */
    public function sendTestSuperAdminNotification()
    {
        $testTenantData = [
            'name' => 'Test Organization Ltd.',
            'email' => 'test@example.com',
            'phone' => '+233123456789',
            'plan' => 'Professional Plan',
            'tenant_id' => 999,
            'user_id' => 999
        ];
        
        return $this->sendNewTenantNotificationToSuperAdmin($testTenantData);
    }
}
