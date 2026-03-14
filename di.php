<?php

use App\Broadcast\DummyLoggerBroadcaster;
use App\Broadcast\EventBroadcasterInterface;
use App\EventHandler;
use App\StatisticsManager;
use App\Storage\SqliteStorage;
use App\Storage\StorageInterface;

return [
    'database_path' => __DIR__ . '/storage/database.sqlite',
    StorageInterface::class => \DI\create(SqliteStorage::class)
        ->constructor(
            \DI\get('database_path'),
        ),
    EventBroadcasterInterface::class => \DI\autowire(DummyLoggerBroadcaster::class),
    EventHandler::class => \DI\autowire(EventHandler::class),
    StatisticsManager::class => \DI\autowire(StatisticsManager::class),
];