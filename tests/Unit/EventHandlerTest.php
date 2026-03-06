<?php

namespace Tests;

use App\EventHandler;
use App\Exception\MissingDataException;
use App\Exception\UndefinedEventException;
use App\FileStorage;
use App\StatisticsManager;
use PHPUnit\Framework\TestCase;

class EventHandlerTest extends TestCase
{
    private string $testFile;
    private string $testStatsFile;
    
    protected function setUp(): void
    {
        $this->testFile = sys_get_temp_dir() . '/test_events_' . uniqid() . '.txt';
        $this->testStatsFile = sys_get_temp_dir() . '/test_stats_' . uniqid() . '.txt';
    }
    
    protected function tearDown(): void
    {
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
        if (file_exists($this->testStatsFile)) {
            unlink($this->testStatsFile);
        }
    }
    
    public function testHandleFoulEvent(): void
    {
        $handler = new EventHandler($this->testFile);
        
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
        $handler = new EventHandler($this->testFile);

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
        $handler = new EventHandler($this->testFile);

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
        
        $handler = new EventHandler($this->testFile);
        
        $handler->handleEvent([]);
    }
    
    public function testEventIsSavedToFile(): void
    {
        $storage = new FileStorage($this->testFile);
        $handler = new EventHandler($this->testFile);
        
        $eventData = [
            'type' => 'goal',
            'scorer' => 'Jane Smith',
            'team_id' => 'arsenal',
            'match_id' => 'm1',
            'minute' => 23,
        ];
        
        $handler->handleEvent($eventData);
        
        $this->assertFileExists($this->testFile);
        $savedEvents = $storage->getAll();
        $this->assertCount(1, $savedEvents);
        $this->assertEquals('goal', $savedEvents[0]['type']);
    }
    
    public function testHandleFoulEventUpdatesStatistics(): void
    {
        $statisticsManager = new StatisticsManager($this->testStatsFile);
        $handler = new EventHandler($this->testFile, $statisticsManager);
        
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
        $statisticsManager = new StatisticsManager($this->testStatsFile);
        $handler = new EventHandler($this->testFile, $statisticsManager);
        
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
        
        $statisticsManager = new StatisticsManager($this->testStatsFile);
        $handler = new EventHandler($this->testFile, $statisticsManager);
        
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