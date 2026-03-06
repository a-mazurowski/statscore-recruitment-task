<?php

namespace App;

use App\Event\EventInterface;
use App\Exception\UndefinedEventException;

class EventFactory
{
    /**
     * @param $data
     * @return EventInterface
     */
    function __invoke($data): EventInterface
    {
        if (!isset($data['type'])) {
            throw new UndefinedEventException();
        }

        try {
            $eventType = EventType::from($data['type']);
            $event = new ($eventType->className())($eventType, $data);
        } catch(\ValueError) {
            throw new UndefinedEventException($data['type']);
        }

        return $event;
    }
}