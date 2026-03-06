<?php

namespace App\Storage;

use App\Event\EventInterface;
use PDO;

class SqliteStorage implements StorageInterface
{
    private PDO $pdo;

    public function __construct(string $databasePath)
    {
        $newDatabase = !file_exists($databasePath);

        $this->pdo = new PDO('sqlite:' . $databasePath);

        if ($newDatabase) {
            $this->pdo->exec(file_get_contents(__DIR__ . '/../../init/database.sql'));
        }

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function save(EventInterface $event): void
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare('INSERT INTO events (match_id, team_id, type, payload) VALUES (?, ?, ?, ?)');
            $stmt->execute([
                $event->matchId,
                $event->teamId,
                $event->type->value,
                json_encode($event->toArray())
            ]);

            $this->pdo->commit();

        } catch (\PDOException) {
            $this->pdo->rollBack();
        }
    }

    public function updateStatistics(string $matchId, string $teamId, string $type, int $value = 1): void
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO statistics (match_id, team_id, event_type, value) 
                VALUES (:match_id, :team_id, :event_type, :value)
                ON CONFLICT(match_id, team_id, event_type) DO UPDATE SET 
                    value = value + :value
            ');

            $stmt->execute([
                ':match_id' => $matchId,
                ':team_id' => $teamId,
                ':event_type' => $type,
                ':value' => $value
            ]);

            $this->pdo->commit();

        } catch (\PDOException) {
            $this->pdo->rollBack();
        }
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM events ORDER BY id');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function(array $row) {
            $row['payload'] = json_decode($row['payload'], true);
            return $row;
        }, $rows);
    }

    public function getStatistics(string $matchId, ?string $teamId = null): array
    {
        $sql = 'SELECT * FROM statistics WHERE match_id = :match_id';
        $params = [
            ':match_id' => $matchId
        ];

        if ($teamId !== null) {
            $sql .= ' AND team_id = :team_id';
            $params[':team_id'] = $teamId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($stats as $stat) {
            $result[$stat['team_id']][$stat['event_type']] = $stat['value'];
        }

        return $teamId ? $result[$teamId] : $result;
    }
}