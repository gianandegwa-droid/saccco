<?php
require __DIR__ . '/vendor/autoload.php';

$lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || strpos($line, '#') === 0) continue;
    [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
    $env[trim($key)] = trim($value, " \t\n\r\0\x0B\"");
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = $env['SACCO_SMTP_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $env['SACCO_SMTP_USERNAME'];
    $mail->Password = $env['SACCO_SMTP_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int) $env['SACCO_SMTP_PORT'];
    $mail->setFrom($env['SACCO_SMTP_FROM'], 'MemberLink SACCO');
    $mail->addAddress('victorolejela@gmail.com');
    $mail->Subject = 'Test email from MemberLink SACCO';
    $mail->Body = 'Hello Victor, this is a live test email from the SACCO system. If you received it, the Gmail SMTP setup is working correctly.';
    $mail->AltBody = 'Hello Victor, this is a live test email from the SACCO system. If you received it, the Gmail SMTP setup is working correctly.';
    $mail->send();
    echo "EMAIL_SENT_OK\n";
} catch (Exception $e) {
    echo 'EMAIL_SEND_FAILED: ' . $e->getMessage() . "\n";
    exit(1);
}
