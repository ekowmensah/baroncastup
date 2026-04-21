<?php

namespace SmartCast\Services;

/**
 * Email Service Factory
 * Automatically selects the best available email service
 */
class EmailServiceFactory
{
    /**
     * Create the best available email service
     * 
     * @return EmailService|EmailServiceSimple
     */
    public static function create()
    {
        // Check if PHPMailer is available
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            try {
                return new EmailService();
            } catch (\Throwable $e) {
                // PHPMailer available but configuration issues
                error_log("EmailService failed, falling back to EmailServiceSimple: " . $e->getMessage());
                return new EmailServiceSimple();
            }
        } else {
            // PHPMailer not available, use simple service
            error_log("PHPMailer not available, using EmailServiceSimple");
            return new EmailServiceSimple();
        }
    }
    
    /**
     * Check if PHPMailer is available
     * 
     * @return bool
     */
    public static function isPHPMailerAvailable()
    {
        return class_exists('PHPMailer\PHPMailer\PHPMailer');
    }
    
    /**
     * Get email service status
     * 
     * @return array
     */
    public static function getStatus()
    {
        $status = [
            'phpmailer_available' => self::isPHPMailerAvailable(),
            'service_type' => null,
            'error' => null
        ];
        
        try {
            $service = self::create();
            $status['service_type'] = get_class($service);
        } catch (\Throwable $e) {
            $status['error'] = $e->getMessage();
        }
        
        return $status;
    }
}
