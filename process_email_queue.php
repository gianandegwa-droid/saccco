<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/EmailService.php';

use App\EmailService;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$env = [];
if (file_exists(__DIR__ . '/.env')) {
    foreach (file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $env[trim($key)] = trim($value, " \t\n\r\0\x0B\"");
    }
}

$queueFile = __DIR__ . '/data/email_queue.json';
if (!file_exists($queueFile)) {
    exit(0);
}

$queue = json_decode(file_get_contents($queueFile), true) ?? [];
if (empty($queue)) {
    exit(0);
}

$processed = [];
foreach ($queue as $index => $item) {
    $member = $item['member'] ?? [];
    $to = trim((string)($member['email'] ?? ''));
    
    if ($to === '') {
        continue;
    }

    $firstName = trim((string)($member['first_name'] ?? ''));
    $lastName = trim((string)($member['last_name'] ?? ''));
    $name = trim($firstName . ' ' . $lastName) ?: 'Member';

    $savings = (float)($member['savings_balance'] ?? 0);
    $loanLimit = EmailService::calculateLoanLimit($savings);

    $subject = 'Welcome to MemberLink SACCO';
    $body = "Hello {$name},\r\n\r\n" .
        "Welcome to MemberLink SACCO. Your membership application has been approved and you are now part of the savings and credit cooperative.\r\n\r\n" .
        "Current savings: KES " . number_format($savings, 2) . "\r\n" .
        "Estimated loan eligibility: KES " . number_format($loanLimit, 2) . "\r\n\r\n" .
        "We are glad to have you as a member.\r\n\r\n" .
        "Regards,\r\n" .
        "MemberLink SACCO";

    $smtpHost = $env['SACCO_SMTP_HOST'] ?? getenv('SACCO_SMTP_HOST') ?: 'smtp.gmail.com';
    $smtpPort = (int)($env['SACCO_SMTP_PORT'] ?? getenv('SACCO_SMTP_PORT') ?: 587);
    $smtpUser = $env['SACCO_SMTP_USERNAME'] ?? getenv('SACCO_SMTP_USERNAME');
    $smtpPass = $env['SACCO_SMTP_PASSWORD'] ?? getenv('SACCO_SMTP_PASSWORD');
    $from = $env['SACCO_SMTP_FROM'] ?? getenv('SACCO_SMTP_FROM') ?: 'no-reply@memberlink.local';

    if (!empty($smtpUser) && !empty($smtpPass)) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $smtpPort;
            $mail->SMTPKeepAlive = false;
            $mail->Timeout = 10;
            $mail->SMTPOptions = [
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true],
            ];
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($from, 'MemberLink SACCO');
            $mail->addAddress($to, $name);
            $mail->Subject = $subject;
            $mail->Body = nl2br($body);
            $mail->AltBody = $body;
            $mail->isHTML(true);
            $mail->send();
            $processed[] = $index;
        } catch (Exception $e) {
            file_put_contents(__DIR__ . '/data/email_log.txt', '[' . date('c') . '] SMTP_ERROR: ' . $e->getMessage() . " To: $to\n$body\n---\n", FILE_APPEND);
            $processed[] = $index;
        }
    } else {
        file_put_contents(__DIR__ . '/data/email_log.txt', '[' . date('c') . '] To: ' . $to . "\n" . $body . "\n---\n", FILE_APPEND);
        $processed[] = $index;
    }
}

if (!empty($processed)) {
    $remaining = [];
    foreach ($queue as $index => $item) {
        if (!in_array($index, $processed, true)) {
            $remaining[] = $item;
        }
    }
    if (empty($remaining)) {
        @unlink($queueFile);
    } else {
        file_put_contents($queueFile, json_encode($remaining, JSON_PRETTY_PRINT));
    }
}

