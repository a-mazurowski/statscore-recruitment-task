<?php

namespace App\Storage;

use App\Event\EventInterface;
use App\EventType;

interface StorageInterface
{
    public function save(EventInterface $event): void;

    public function updateStatistics(string $matchId, string $teamId, string $type, int $value = 1): void;

    public function getAll(): array;

    public function getStatistics(string $matchId, ?string $teamId = null): array;
}