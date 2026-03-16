<?php

namespace App\Broadcast;

use App\Broadcast\EventBroadcasterInterface;
use App\Event\EventInterface;

class DummyLoggerBroadcaster implements EventBroadcasterInterface
{
    private string $logFilePath;

    public function __construct(string $logFilePath = __DIR__ . '/../../storage/broadcast.log')
    {
        $this->logFilePath = $logFilePath;
    }

    public function broadcast(EventInterface $event): void
    {
        $message = sprintf(
            "[%s] BROADCAST: Event '%s' for match '%s' sent to clients. Payload: %s\n",
            date('Y-m-d H:i:s'),
            $event->type->value,
            $event->matchId,
            json_encode($event->toArray())
        );

        file_put_contents($this->logFilePath, $message, FILE_APPEND);
    }
}