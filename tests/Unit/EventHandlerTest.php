<?php

namespace Tests;

use App\EventHandler;
use App\Exception\MissingDataException;
use App\Exception\UndefinedEventException;
use App\StatisticsManager;
use App\Storage\FileStorage;
use App\Storage\SqliteStorage;
use App\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

class EventHandlerTest extends TestCase
{
    private array $files;

    private StorageInterface $storage;
    
    protected function setUp(): void
    {
        if (getenv('STORAGE_METHOD') === 'sqlite') {
            $databaseFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid() . '.sqlite';
            $this->files = [$databaseFile];

            $this->storage = new SqliteStorage($databaseFile);
        } else {
            $eventFile = 'test_events_' . uniqid() . '.txt';
            $statsFile = 'test_stats_' . uniqid() . '.txt';

            $this->files = [
                sys_get_temp_dir() . DIRECTORY_SEPARATOR . $eventFile,
                sys_get_temp_dir() . DIRECTORY_SEPARATOR . $statsFile,
            ];

            $this->storage = new FileStorage(sys_get_temp_dir(), $eventFile, $statsFile);
        }
    }
    
    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
    
    public function testHandleFoulEvent(): void
    {
        $handler = new EventHandler($this->storage);
        
        $eventData = [
            'type' => 'foul',
            'player' => 'John Doe',
            'team_id' => 'arsenal',
            'match_id' => 'm1',
            'minute' => 23,
            'second' => 34
        ];
        
        $result = $handler->handleEvent($eventData);
        
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('foul', $result['event']['type']);
        $this->assertArrayHasKey('timestamp', $result['event']);
    }

    public function testHandleGoalEvent(): void
    {
        $handler = new EventHandler($this->storage);

        $eventData = [
            'type' => 'goal',
            'scorer' => 'John Doe',
            'team_id' => 'arsenal',
            'match_id' => 'm1',
            'minute' => 23,
            'second' => 34
        ];

        $result = $handler->handleEvent($eventData);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('goal', $result['event']['type']);
        $this->assertArrayHasKey('timestamp', $result['event']);
    }

    public function testHandleInvalidEvent(): void
    {
        $handler = new EventHandler($this->storage);

        $invalidType = bin2hex(random_bytes(16));

        $eventData = [
            'type' => $invalidType,
            'scorer' => 'John Doe',
            'team_id' => 'arsenal',
            'match_id' => 'm1',
            'minute' => 23,
            'second' => 34
        ];

        $this->expectException(UndefinedEventException::class);
        $this->expectExceptionMessage('Event "'.$invalidType.'" is not defined');

        $result = $handler->handleEvent($eventData);
    }
    
    public function testHandleEventWithoutType(): void
    {
        $this->expectException(UndefinedEventException::class);
        $this->expectExceptionMessage('Event type is required');
        
        $handler = new EventHandler($this->storage);
        
        $handler->handleEvent([]);
    }
    
    public function testEventIsSavedToFile(): void
    {
        $handler = new EventHandler($this->storage);
        
        $eventData = [
            'type' => 'goal',
            'scorer' => 'Jane Smith',
            'team_id' => 'arsenal',
            'match_id' => 'm1',
            'minute' => 23,
        ];
        
        $handler->handleEvent($eventData);
        
        $this->assertFileExists($this->files[0]);
        $savedEvents = $this->storage->getAll();
        $this->assertCount(1, $savedEvents);
        $this->assertEquals('goal', $savedEvents[0]['type']);
    }
    
    public function testHandleFoulEventUpdatesStatistics(): void
    {
        $statisticsManager = new StatisticsManager($this->storage);
        $handler = new EventHandler($this->storage, statisticsManager: $statisticsManager);
        
        $eventData = [
            'type' => 'foul',
            'player' => 'William Saliba',
            'team_id' => 'arsenal',
            'match_id' => 'm1',
            'minute' => 45,
            'second' => 34
        ];
        
        $result = $handler->handleEvent($eventData);
        
        // Check that event was saved successfully
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('foul', $result['event']['type']);
        
        // Check that statistics were updated
        $teamStats = $statisticsManager->getTeamStatistics('m1', 'arsenal');
        $this->assertArrayHasKey('fouls', $teamStats);
        $this->assertEquals(1, $teamStats['fouls']);
    }
    
    public function testHandleMultipleFoulEventsIncrementsStatistics(): void
    {
        $statisticsManager = new StatisticsManager($this->storage);
        $handler = new EventHandler($this->storage, statisticsManager: $statisticsManager);
        
        $eventData1 = [
            'type' => 'foul',
            'player' => 'John Doe',
            'team_id' => 'team_a',
            'match_id' => 'match_1',
            'minute' => 15,
            'second' => 34
        ];
        
        $eventData2 = [
            'type' => 'foul',
            'player' => 'Jane Smith',
            'team_id' => 'team_a',
            'match_id' => 'match_1',
            'minute' => 30,
            'second' => 34
        ];
        
        $handler->handleEvent($eventData1);
        $handler->handleEvent($eventData2);
        
        // Check that statistics were incremented correctly
        $teamStats = $statisticsManager->getTeamStatistics('match_1', 'team_a');
        $this->assertEquals(2, $teamStats['fouls']);
    }
    
    public function testHandleFoulEventWithoutRequiredFields(): void
    {
        $this->expectException(MissingDataException::class);
        $this->expectExceptionMessage('Data key/s "match_id", "team_id", "minute" are required');
        
        $statisticsManager = new StatisticsManager($this->storage);
        $handler = new EventHandler($this->storage, statisticsManager: $statisticsManager);
        
        $eventData = [
            'type' => 'foul',
            'player' => 'John Doe',
            'minute' => 45,
            'second' => 34
            // Missing match_id and team_id
        ];
        
        $handler->handleEvent($eventData);
    }
}