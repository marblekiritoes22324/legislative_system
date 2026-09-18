<?php
// config/mailer.php — Official PHPMailer Integration for Manila City Hall LIS
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/phpmailer/Exception.php';
require_once __DIR__ . '/phpmailer/PHPMailer.php';
require_once __DIR__ . '/phpmailer/SMTP.php';

/**
 * Sends a 6-digit login OTP email to a user.
 * 
 * @param string $recipientEmail Target email address
 * @param string $recipientName Recipient full name
 * @param string $otpCode 6-digit one-time PIN
 * @return array ['success' => bool, 'error' => string|null, 'simulated' => bool]
 */
function send_login_otp_email($recipientEmail, $recipientName, $otpCode) {
    // Read SMTP settings from Environment or local fallbacks
    $smtpHost = getenv('SMTP_HOST') ?: (getenv('MAIL_HOST') ?: 'smtp.gmail.com');
    $smtpPort = (int)(getenv('SMTP_PORT') ?: (getenv('MAIL_PORT') ?: 587));
    $smtpUser = getenv('SMTP_USER') ?: (getenv('MAIL_USERNAME') ?: 'christiancaspe19@gmail.com');
    $smtpPass = getenv('SMTP_PASS') ?: (getenv('MAIL_PASSWORD') ?: 'gozzdconmswaeecl');
    $smtpFrom = getenv('SMTP_FROM') ?: (getenv('MAIL_FROM') ?: 'christiancaspe19@gmail.com');
    $fromName = 'Lungsod ng Maynila — Legislative System';

    // If no SMTP password is configured yet, safely simulate OTP delivery
    if (empty($smtpPass)) {
        error_log("[OTP Simulation] Sent OTP $otpCode to $recipientEmail ($recipientName)");
        return [
            'success' => true,
            'simulated' => true,
            'message' => 'OTP generated (SMTP credentials not yet set in environment; code logged safely).'
        ];
    }

    $mail = new PHPMailer(true);

    try {
        // Server configuration
        $mail->isSMTP();
        $mail->Host       = $smtpHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->SMTPSecure = ($smtpPort === 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $smtpPort;
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 10; // 10s timeout to prevent hanging

        // Sender & Recipient
        $mail->setFrom($smtpFrom, $fromName);
        $mail->addAddress($recipientEmail, $recipientName);
        $mail->addReplyTo($smtpUser, $fromName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Your Login Verification Code: $otpCode — Manila City Hall LIS";

        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset='UTF-8'>
          <title>Verification Code</title>
        </head>
        <body style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #1e293b;'>
          <div style='max-width: 520px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
            <!-- Header Banner -->
            <div style='background: linear-gradient(135deg, #0B2E59 0%, #1e40af 100%); padding: 28px 24px; text-align: center; color: #ffffff;'>
              <h1 style='margin: 0; font-size: 20px; font-weight: 700; letter-spacing: 0.5px;'>LUNGSOD NG MAYNILA</h1>
              <p style='margin: 4px 0 0 0; font-size: 13px; opacity: 0.85;'>Legislative Management &amp; Information System</p>
            </div>
            
            <!-- Body -->
            <div style='padding: 32px 28px;'>
              <p style='font-size: 15px; margin: 0 0 16px 0;'>Hello <strong>" . htmlspecialchars($recipientName) . "</strong>,</p>
              <p style='font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 24px 0;'>
                A login request was initiated for your Manila City Hall Administrator account. Please use the following 6-digit One-Time PIN (OTP) to complete your sign in:
              </p>
              
              <!-- OTP Box -->
              <div style='text-align: center; margin: 28px 0;'>
                <div style='display: inline-block; background: #f1f5f9; border: 2px dashed #0B2E59; border-radius: 12px; padding: 16px 36px;'>
                  <span style='font-family: monospace; font-size: 34px; font-weight: 800; letter-spacing: 8px; color: #0B2E59;'>{$otpCode}</span>
                </div>
              </div>
              
              <!-- Expiry Alert -->
              <div style='background: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; padding: 12px 16px; margin-bottom: 24px;'>
                <p style='margin: 0; font-size: 13px; color: #b45309; line-height: 1.5;'>
                  ⏱️ This code will expire in <strong>10 minutes</strong>. Never share this code with anyone, including IT staff.
                </p>
              </div>
              
              <p style='font-size: 12px; color: #94a3b8; line-height: 1.5; margin: 0;'>
                If you did not request this login attempt, please secure your credentials immediately and contact the Manila City Hall IT Department.
              </p>
            </div>
            
            <!-- Footer -->
            <div style='background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 24px; text-align: center; font-size: 11px; color: #64748b;'>
              &copy; " . date('Y') . " City Government of Manila. All rights reserved.<br>
              Padre Burgos Ave, Ermita, Manila, 1000 Metro Manila
            </div>
          </div>
        </body>
        </html>
        ";

        $mail->AltBody = "Your Manila City Hall login verification code is: $otpCode. Valid for 10 minutes. Do not share this code.";

        $mail->send();
        return ['success' => true, 'simulated' => false];
    } catch (Exception $e) {
        error_log("[PHPMailer Error] " . $mail->ErrorInfo);
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}
