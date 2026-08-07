<?php
// Vyapar Setu Email Helper Simulation

function sendEmail($to, $subject, $body) {
    // Simulated mail function for XAMPP / Local development
    $logMsg = date('[Y-m-d H:i:s]') . " MAIL TO: $to | SUBJECT: $subject\nBODY:\n$body\n\n";
    $logFile = BASE_PATH . '/logs/mail.log';
    $dir = dirname($logFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($logFile, $logMsg, FILE_APPEND);
    return true;
}
