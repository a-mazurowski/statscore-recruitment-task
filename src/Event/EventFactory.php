<?php

namespace App\Event;

use App\Exception\UndefinedEventException;

class EventFactory
{
    /**
     * @param array $data
     * @return EventInterface
     */
    function __invoke(array $data): EventInterface
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