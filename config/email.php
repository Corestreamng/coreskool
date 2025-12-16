<?php
/**
 * Email Configuration and Mailer Class
 * CoreSkool School Management System
 * Uses PHP's built-in mail() function - compatible with cPanel
 */

class Mailer {
    private $from_email;
    private $from_name;
    
    public function __construct() {
        $this->from_email = SMTP_FROM_EMAIL;
        $this->from_name = SMTP_FROM_NAME;
    }
    
    public function send($to, $subject, $body, $altBody = '', $attachments = []) {
        try {
            // Prepare headers
            $headers = [];
            $headers[] = "MIME-Version: 1.0";
            $headers[] = "Content-type: text/html; charset=UTF-8";
            $headers[] = "From: {$this->from_name} <{$this->from_email}>";
            $headers[] = "Reply-To: {$this->from_email}";
            $headers[] = "X-Mailer: PHP/" . phpversion();
            
            // Handle multiple recipients
            $recipients = is_array($to) ? implode(', ', $to) : $to;
            
            // Send email using PHP mail function (cPanel compatible)
            $result = mail($recipients, $subject, $body, implode("\r\n", $headers));
            
            if (!$result) {
                error_log("Email Send Error: Failed to send to {$recipients}");
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Email Send Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function sendBulk($recipients, $subject, $body) {
        $success = 0;
        $failed = 0;
        
        foreach ($recipients as $recipient) {
            if ($this->send($recipient, $subject, $body)) {
                $success++;
            } else {
                $failed++;
            }
            // Small delay to avoid rate limiting
            usleep(100000); // 0.1 second
        }
        
        return ['success' => $success, 'failed' => $failed];
    }
    
    public function getEmailTemplate($title, $content, $buttonText = '', $buttonLink = '') {
        $button = '';
        if ($buttonText && $buttonLink) {
            $button = "<p style='text-align: center; margin: 30px 0;'>
                <a href='{$buttonLink}' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                {$buttonText}
                </a>
            </p>";
        }
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                    padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
                    <h1 style='color: white; margin: 0;'>" . SITE_NAME . "</h1>
                </div>
                <div style='background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;'>
                    <h2 style='color: #667eea;'>{$title}</h2>
                    <div style='margin: 20px 0;'>
                        {$content}
                    </div>
                    {$button}
                    <hr style='border: none; border-top: 1px solid #ddd; margin: 30px 0;'>
                    <p style='font-size: 12px; color: #888; text-align: center;'>
                        &copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.<br>
                        This is an automated message, please do not reply.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
