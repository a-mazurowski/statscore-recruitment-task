<?php

namespace App\Event;

enum EventType: string
{
    case FOUL = 'foul';
    case GOAL = 'goal';

    /**
     * @return class-string<EventInterface>
     */
    public function className(): string
    {
        return match ($this) {
            self::FOUL => FoulEvent::class,
            self::GOAL => GoalEvent::class,
        };
    }
}
