<?php

namespace App\Storage;

use App\Event\EventInterface;

class FileStorage implements StorageInterface
{
    public function __construct(private string $filePath, private string $eventFile = 'events.txt', private string $statsFile = 'stats.txt')
    {
        if (!is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }
    }
    
    public function save(EventInterface $event): void
    {
        $line = json_encode($event->toArray()) . PHP_EOL;
        file_put_contents($this->filePath . DIRECTORY_SEPARATOR . $this->eventFile, $line, FILE_APPEND | LOCK_EX);
    }
    
    public function getAll(): array
    {
        if (!file_exists($this->filePath . DIRECTORY_SEPARATOR . $this->eventFile)) {
            return [];
        }
        
        $content = file_get_contents($this->filePath . DIRECTORY_SEPARATOR . $this->eventFile);
        $lines = explode(PHP_EOL, trim($content));
        
        return array_map(function($line) {
            return json_decode($line, true);
        }, array_filter($lines));
    }

    public function updateStatistics(string $matchId, string $teamId, string $type, int $value = 1): void
    {
        $stats = $this->readStatisticFile();

        if (!isset($stats[$matchId])) {
            $stats[$matchId] = [];
        }

        if (!isset($stats[$matchId][$teamId])) {
            $stats[$matchId][$teamId] = [];
        }

        if (!isset($stats[$matchId][$teamId][$type])) {
            $stats[$matchId][$teamId][$type] = 0;
        }

        $stats[$matchId][$teamId][$type] += $value;

        file_put_contents(
            $this->filePath . DIRECTORY_SEPARATOR . $this->statsFile,
            json_encode($stats, JSON_PRETTY_PRINT),
            LOCK_EX
        );
    }

    public function getStatistics(string $matchId, ?string $teamId = null): array
    {
        $stats = $this->readStatisticFile();

        if ($teamId) {
            return $stats[$matchId][$teamId] ?? [];
        }

        return $stats[$matchId] ?? [];
    }

    private function readStatisticFile(): array
    {
        if (!file_exists($this->filePath . DIRECTORY_SEPARATOR . $this->statsFile)) {
            return [];
        }

        $content = file_get_contents($this->filePath . DIRECTORY_SEPARATOR . $this->statsFile);
        return json_decode($content, true) ?? [];
    }

}