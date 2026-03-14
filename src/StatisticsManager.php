<?php

namespace App;

use App\Event\EventInterface;
use App\Storage\StorageInterface;

readonly class StatisticsManager
{
    public function __construct(private StorageInterface $storage)
    {}

    public function updateStatisticsFromEvent(EventInterface $event): void
    {
        $type = match ($event->type) {
            EventType::FOUL => 'fouls',
            EventType::GOAL => 'goals',
        };
        $this->storage->updateStatistics($event->matchId, $event->teamId, $type);
    }
    
    public function getTeamStatistics(string $matchId, string $teamId): array
    {
        return $this->storage->getStatistics($matchId, $teamId);
    }
    
    public function getMatchStatistics(string $matchId): array
    {
        return $this->storage->getStatistics($matchId);
    }
}
