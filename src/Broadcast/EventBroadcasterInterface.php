<?php

namespace App\Broadcast;

use App\Event\EventInterface;

interface EventBroadcasterInterface
{
    public function broadcast(EventInterface $event): void;
}