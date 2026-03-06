<?php

namespace App\Event;

use App\Event\AbstractEvent;
use App\Event\EventInterface;
use App\EventType;
use App\Exception\MissingDataException;

class GoalEvent extends AbstractEvent implements EventInterface
{
    public private(set) string $scorer;
    public private(set) ?string $assist;

    public function __construct(EventType $type, array $data = [])
    {
        parent::__construct($type, $data);

        if (!isset($data['scorer'])) {
            throw new MissingDataException(['scorer']);
        }

        $this->scorer = $data['scorer'];
        $this->assist = $data['assist'] ?? null;
    }

    public function toArray(): array
    {
        return array_merge_recursive(parent::toArray(), [
            'data' => [
                'scorer' => $this->scorer,
                'assist' => $this->assist,
            ]
        ]);
    }
}