<?php

namespace App\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class ErrorNotificationHandler extends AbstractProcessingHandler
{
    public function __construct()
    {
        parent::__construct(Level::Error);
    }

    protected function write(LogRecord $record): void
    {
        $appName = config('app.name');
        $env     = config('app.env');
        $level   = $record->level->name;

        $body = $record->message;
        if (!empty($record->context['exception']) && $record->context['exception'] instanceof \Throwable) {
            $e     = $record->context['exception'];
            $body .= "\n" . $e->getFile() . ':' . $e->getLine();
            $body .= "\n" . substr($e->getTraceAsString(), 0, 800);
        }
        $body = substr($body, 0, 1800);

        $this->notifyDiscord($appName, $env, $level, $body);
        $this->notifyMail($appName, $env, $level, $body);
    }

    private function notifyDiscord(string $appName, string $env, string $level, string $body): void
    {
        $url = env('ERROR_DISCORD_WEBHOOK_URL');
        if (!$url) return;

        $content = ":rotating_light: **[{$level}] {$appName}** ({$env})\n```\n{$body}\n```";
        $payload = json_encode(['content' => $content]);

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST          => true,
                CURLOPT_POSTFIELDS    => $payload,
                CURLOPT_HTTPHEADER    => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT       => 5,
            ]);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Throwable) {}
    }

    private function notifyMail(string $appName, string $env, string $level, string $body): void
    {
        $to     = env('ERROR_NOTIFICATION_MAIL');
        $apiKey = env('RESEND_API_KEY');
        $from   = env('MAIL_FROM_ADDRESS', 'noreply@example.com');
        if (!$to || !$apiKey) return;

        $payload = json_encode([
            'from'    => $from,
            'to'      => [$to],
            'subject' => "[{$level}] {$appName} ({$env}) エラー通知",
            'html'    => '<pre style="font-family:monospace">' . htmlspecialchars($body) . '</pre>',
        ]);

        try {
            $ch = curl_init('https://api.resend.com/emails');
            curl_setopt_array($ch, [
                CURLOPT_POST          => true,
                CURLOPT_POSTFIELDS    => $payload,
                CURLOPT_HTTPHEADER    => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey,
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT       => 5,
            ]);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Throwable) {}
    }
}
