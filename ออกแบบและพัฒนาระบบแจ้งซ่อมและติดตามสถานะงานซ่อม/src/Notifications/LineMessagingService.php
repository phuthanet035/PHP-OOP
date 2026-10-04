<?php

namespace App\Notifications;

/**
 * LINE Messaging API Service
 * Implements NotificationChannelInterface matching Diagram 5
 */
class LineMessagingService implements NotificationChannelInterface
{
    private string $channelToken;
    private bool $enabled;
    private string $logFile;

    public function __construct()
    {
        $this->channelToken = trim($_ENV['LINE_CHANNEL_ACCESS_TOKEN'] ?? '');
        $this->enabled = filter_var($_ENV['LINE_NOTIFICATION_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $this->logFile = dirname(__DIR__, 2) . '/storage/logs/line_notifications.log';
    }

    public function send(string $recipient, string $message, ?array $extra = null): bool
    {
        if (!$this->enabled) {
            return false;
        }

        // If channel token is empty, execute in simulation/mock mode and log to storage/logs
        if (empty($this->channelToken) || empty($recipient)) {
            $this->logNotification($recipient, $message, 'MOCK_SIMULATED');
            return true;
        }

        // Production LINE Push Message via cURL
        $payload = [
            'to' => $recipient,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $message
                ]
            ]
        ];

        $response = $this->callApi('https://api.line.me/v2/bot/message/push', $payload);
        $status = ($response['http_code'] ?? 0) === 200 ? 'SUCCESS' : 'FAILED';
        $this->logNotification($recipient, $message, $status, $response);

        return ($response['http_code'] ?? 0) === 200;
    }

    private function callApi(string $endpoint, array $body): array
    {
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->channelToken
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        return [
            'http_code' => $httpCode,
            'body' => $result,
            'error' => $curlError
        ];
    }

    private function logNotification(string $recipient, string $message, string $status, array $response = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = json_encode([
            'timestamp' => $timestamp,
            'recipient' => $recipient ?: 'ALL_ADMINS_TECH',
            'status'    => $status,
            'message'   => $message,
            'response'  => $response,
        ], JSON_UNESCAPED_UNICODE) . PHP_EOL;

        @file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    public static function getRecentLogs(int $limit = 10): array
    {
        $logFile = dirname(__DIR__, 2) . '/storage/logs/line_notifications.log';
        if (!file_exists($logFile)) {
            return [];
        }

        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return [];
        }

        $lines = array_reverse($lines);
        $logs = [];
        $count = 0;

        foreach ($lines as $line) {
            $decoded = json_decode($line, true);
            if ($decoded) {
                $logs[] = $decoded;
                $count++;
                if ($count >= $limit) {
                    break;
                }
            }
        }

        return $logs;
    }
}
