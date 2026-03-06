<?php

namespace App\Event;

use App\Event\AbstractEvent;
use App\Event\EventInterface;
use App\EventType;
use App\Exception\MissingDataException;

class FoulEvent extends AbstractEvent implements EventInterface
{
    public private(set) string $player;
    public private(set) ?string $affected;

    public function __construct(EventType $type, array $data = [])
    {
        parent::__construct($type, $data);

        if (!isset($data['player'])) {
            throw new MissingDataException(['player']);
        }

        $this->player = $data['player'];
        $this->affected = $data['affected'] ?? null;
    }

    public function toArray(): array
    {
        return array_merge_recursive(parent::toArray(), [
            'data' => [
                'player' => $this->player,
                'affected' => $this->affected,
            ]
        ]);
    }
}