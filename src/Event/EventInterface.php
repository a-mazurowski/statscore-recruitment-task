<?php

namespace App\Event;

use App\EventType;

interface EventInterface
{
    public EventType $type {
        get;
    }

    public string $matchId {
        get;
    }

    public string $teamId {
        get;
    }

    public int $minute {
        get;
    }
    public ?int $second {
        get;
    }
    public int $timestamp {
        get;
    }

    public function toArray(): array;
}