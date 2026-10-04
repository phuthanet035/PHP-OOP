<?php

namespace App\Notifications;

/**
 * Strategy Pattern Interface for Notification Channels
 * Matching Class Diagram 5
 */
interface NotificationChannelInterface
{
    public function send(string $recipient, string $message, ?array $extra = null): bool;
}
