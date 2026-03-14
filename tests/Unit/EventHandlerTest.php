<?php

namespace Tests;

use App\Broadcast\EventBroadcasterInterface;
use App\Event\EventHandler;
use App\Exception\MissingDataException;
use App\Exception\UndefinedEventException;
use App\Statistics\StatisticsManager;
use App\Storage\FileStorage;
use App\Storage\SqliteStorage;
use App\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

class EventHandlerTest extends TestCase
{
    private array $files;

    private StorageInterface $storage;

    private EventBroadcasterInterface $broadcaster;

    private StatisticsManager $statisticsManager;

    private EventHandler $handler;

    
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

        $this->statisticsManager = new StatisticsManager($this->storage);
        $this->broadcaster = $this->createMock(EventBroadcasterInterface::class);
        $this->handler = new EventHandler($this->storage, $this->broadcaster, $this->statisticsManager);
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
        $eventData = [
            'type' => 'foul',
            'player' => 'John Doe',
            'team_id' => 'arsenal',
            'match_id' => 'm1',
            'minute' => 23,
            'second' => 34
        ];
        
        $result = $this->handler->handleEvent($eventData);
        
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('foul', $result['event']['type']);
        $this->assertArrayHasKey('timestamp', $result['event']);
    }

    public function testHandleGoalEvent(): void
    {
        $eventData = [
            'type' => 'goal',
            'scorer' => 'John Doe',
            'team_id' => 'arsenal',
            'match_id' => 'm1',
            'minute' => 23,
            'second' => 34
        ];

        $result = $this->handler->handleEvent($eventData);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('goal', $result['event']['type']);
        $this->assertArrayHasKey('timestamp', $result['event']);
    }

    public function testHandleInvalidEvent(): void
    {
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

        $this->handler->handleEvent($eventData);
    }
    
    public function testHandleEventWithoutType(): void
    {
        $this->expectException(UndefinedEventException::class);
        $this->expectExceptionMessage('Event type is required');
                
        $this->handler->handleEvent([]);
    }
    
    public function testEventIsSavedToFile(): void
    {
        $eventData = [
            'type' => 'goal',
            'scorer' => 'Jane Smith',
            'team_id' => 'arsenal',
            'match_id' => 'm1',
            'minute' => 23,
        ];
        
        $this->handler->handleEvent($eventData);
        
        $this->assertFileExists($this->files[0]);
        $savedEvents = $this->storage->getAll();
        $this->assertCount(1, $savedEvents);
        $this->assertEquals('goal', $savedEvents[0]['type']);
    }
    
    public function testHandleFoulEventUpdatesStatistics(): void
    {
        $eventData = [
            'type' => 'foul',
            'player' => 'William Saliba',
            'team_id' => 'arsenal',
            'match_id' => 'm1',
            'minute' => 45,
            'second' => 34
        ];
        
        $result = $this->handler->handleEvent($eventData);
        
        // Check that event was saved successfully
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('foul', $result['event']['type']);
        
        // Check that statistics were updated
        $teamStats = $this->statisticsManager->getTeamStatistics('m1', 'arsenal');
        $this->assertArrayHasKey('fouls', $teamStats);
        $this->assertEquals(1, $teamStats['fouls']);
    }
    
    public function testHandleMultipleFoulEventsIncrementsStatistics(): void
    {
        
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
        
        $this->handler->handleEvent($eventData1);
        $this->handler->handleEvent($eventData2);
        
        // Check that statistics were incremented correctly
        $teamStats = $this->statisticsManager->getTeamStatistics('match_1', 'team_a');
        $this->assertEquals(2, $teamStats['fouls']);
    }
    
    public function testHandleFoulEventWithoutRequiredFields(): void
    {
        $this->expectException(MissingDataException::class);
        $this->expectExceptionMessage('Data key/s "match_id", "team_id", "minute" are required');
        
        $eventData = [
            'type' => 'foul',
            'player' => 'John Doe',
            'minute' => 45,
            'second' => 34
            // Missing match_id and team_id
        ];
        
        $this->handler->handleEvent($eventData);
    }
}