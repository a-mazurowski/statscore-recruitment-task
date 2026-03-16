<?php

namespace App\Event;

use App\Exception\MissingDataException;

abstract class AbstractEvent implements EventInterface
{
    public protected(set) EventType $type;
    public protected(set) string $matchId;
    public protected(set) string $teamId;
    public protected(set) int $minute;
    public protected(set) ?int $second;
    public protected(set) int $timestamp;

    public function __construct(EventType $type, array $data = []) {
        if (!isset($data['match_id']) || !isset($data['team_id']) || !isset($data['minute']) ) {
            throw new MissingDataException(['match_id', 'team_id', 'minute']);
        }

        $this->type = $type;
        $this->matchId = $data['match_id'];
        $this->teamId = $data['team_id'];
        $this->minute = $data['minute'];
        $this->second = $data['second'] ?? null;
        $this->timestamp = time();

    }
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'timestamp' => $this->timestamp,
            'data' => [
                'match_id' => $this->matchId,
                'team_id' => $this->teamId,
                'minute' => $this->minute,
                'second' => $this->second,
            ],
        ];
    }
}