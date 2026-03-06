<?php

namespace App;

use App\Event\FoulEvent;
use App\Event\GoalEvent;

enum EventType: string
{
    case FOUL = 'foul';
    case GOAL = 'goal';

    public function className(): string
    {
        return match ($this) {
            self::FOUL => FoulEvent::class,
            self::GOAL => GoalEvent::class,
        };
    }
}
