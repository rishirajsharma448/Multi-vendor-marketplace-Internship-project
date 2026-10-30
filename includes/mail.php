<?php
// Vyapar Setu Email & SMS Dispatch Helper

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to, $subject, $body) {
    $senderEmail = getenv('SMTP_FROM') ?: (getenv('MAIL_FROM_ADDRESS') ?: (defined('SMTP_FROM') && !empty(SMTP_FROM) ? SMTP_FROM : (defined('SMTP_USER') && filter_var(SMTP_USER, FILTER_VALIDATE_EMAIL) ? SMTP_USER : 'noreply@vyaparsetu.com')));
    $senderName = getenv('MAIL_FROM_NAME') ?: 'Vyapar Setu';
    
    $smtpHost = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
    $smtpPort = defined('SMTP_PORT') ? SMTP_PORT : 587;
    $smtpUser = defined('SMTP_USER') ? trim(SMTP_USER) : '';
    $smtpPass = defined('SMTP_PASS') ? trim(SMTP_PASS) : '';

    if (empty($smtpUser) || empty($smtpPass)) {
        // Unconfigured SMTP; log mail locally and return false for instant local handling
        $logMsg = date('[Y-m-d H:i:s]') . " LOCAL MAIL LOG (No SMTP Configured) | TO: $to | SUBJECT: $subject\nBODY:\n$body\n\n";
        $baseDir = defined('BASE_PATH') ? BASE_PATH : __DIR__ . '/..';
        $logFile = $baseDir . '/logs/mail.log';
        $dir = dirname($logFile);
        if (!is_dir($dir)) { mkdir($dir, 0777, true); }
        file_put_contents($logFile, $logMsg, FILE_APPEND);
        return false;
    }

    $mail = new PHPMailer(true);
    $sent = false;

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Timeout    = 3;
        $mail->Host       = $smtpHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $smtpPort;

        // Disable SSL certificate verification for local XAMPP environments
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Recipients
        $mail->setFrom($senderEmail, $senderName);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(false); // Plain text
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        $sent = true;
    } catch (Exception $e) {
        $sent = false;
        // Log detailed PHPMailer error
        $errorMsg = date('[Y-m-d H:i:s]') . " PHPMailer Error: " . $mail->ErrorInfo . " (Exception: " . $e->getMessage() . ")\n";
        $baseDir = defined('BASE_PATH') ? BASE_PATH : __DIR__ . '/..';
        $logFile = $baseDir . '/logs/mail.log';
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($logFile, $errorMsg, FILE_APPEND);
    }

    // Log mail output for tracking
    $logMsg = date('[Y-m-d H:i:s]') . " MAIL DISPATCHED | STATUS: " . ($sent ? "SUCCESS" : "FAILED") . " | TO: $to | SUBJECT: $subject\nBODY:\n$body\n\n";
    $baseDir = defined('BASE_PATH') ? BASE_PATH : __DIR__ . '/..';
    $logFile = $baseDir . '/logs/mail.log';
    $dir = dirname($logFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($logFile, $logMsg, FILE_APPEND);

    return $sent;
}


function sendSMS($phone, $message) {
    // Log SMS output for record tracking
    $logMsg = date('[Y-m-d H:i:s]') . " SMS DISPATCHED TO: $phone | MESSAGE:\n$message\n\n";
    $baseDir = defined('BASE_PATH') ? BASE_PATH : __DIR__ . '/..';
    $logFile = $baseDir . '/logs/sms.log';
    $dir = dirname($logFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($logFile, $logMsg, FILE_APPEND);
    return true;
}

