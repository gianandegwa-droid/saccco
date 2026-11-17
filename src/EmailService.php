<?php
namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    public static function sendMemberWelcomeEmail(array $member): bool
    {
        $to = trim((string)($member['email'] ?? ''));
        if ($to === '') {
            return false;
        }

        self::sendAsync($member);
        return true;
    }

    private static function sendAsync(array $member): void
    {
        $dataDir = __DIR__ . '/../data';
        if (!is_dir($dataDir)) {
            @mkdir($dataDir, 0755, true);
        }

        $queueFile = $dataDir . '/email_queue.json';
        $queue = [];
        if (file_exists($queueFile)) {
            $queue = json_decode(file_get_contents($queueFile), true) ?? [];
        }

        $queue[] = [
            'member' => $member,
            'timestamp' => time(),
        ];

        @file_put_contents($queueFile, json_encode($queue, JSON_PRETTY_PRINT));

        if (php_sapi_name() !== 'cli') {
            self::startQueueProcessor();
        }
    }

    private static function startQueueProcessor(): void
    {
        $phpBinary = PHP_BINARY ?: (getenv('PHP_BINARY') ?: 'php');
        $scriptPath = __DIR__ . '/../process_email_queue.php';

        if (stripos(PHP_OS, 'WIN') === 0) {
            $command = '"' . str_replace('"', '\\"', $phpBinary) . '" "' . str_replace('"', '\\"', $scriptPath) . '" > NUL 2>&1';
            @exec($command);
            return;
        }

        @exec(escapeshellarg($phpBinary) . ' ' . escapeshellarg($scriptPath) . ' > /dev/null 2>&1 &');
    }



    public static function calculateLoanLimit(float $savingsBalance): float
    {
        $safeSavings = max(0, (float)$savingsBalance);
        $maxLoan = 500000.00;
        return min($safeSavings * 2.5, $maxLoan);
    }

    private static function sendFallbackMail(string $from, string $to, string $subject, string $message): bool
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/plain; charset=UTF-8',
            'From: ' . $from,
            'Reply-To: ' . $from,
        ];

        $sent = @mail($to, $subject, $message, implode("\r\n", $headers));
        if (!$sent) {
            $logFile = __DIR__ . '/../data/email_log.txt';
            $logEntry = '[' . date('c') . '] To: ' . $to . "\n" . $message . "\n---\n";
            @file_put_contents($logFile, $logEntry, FILE_APPEND);
        }

        return $sent;
    }

    private static function getConfig(string $name, ?string $default = null): ?string
    {
        $value = getenv($name);
        if ($value === false || $value === '') {
            $value = $_ENV[$name] ?? $_SERVER[$name] ?? null;
        }

        if (($value === null || $value === '') && file_exists(__DIR__ . '/../.env')) {
            static $dotenv = null;
            if ($dotenv === null) {
                $dotenv = [];
                $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $trimmed = trim($line);
                    if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                        continue;
                    }
                    [$key, $val] = array_pad(explode('=', $trimmed, 2), 2, '');
                    $dotenv[trim($key)] = trim($val, " \t\n\r\0\x0B\"");
                }
            }
            $value = $dotenv[$name] ?? null;
        }

        if ($value === null || $value === '') {
            return $default;
        }

        return (string) $value;
    }
}
