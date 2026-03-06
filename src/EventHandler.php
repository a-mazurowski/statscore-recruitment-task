<?php

namespace App;

use App\Storage\FileStorage;
use App\Storage\StorageInterface;

class EventHandler
{
    private StatisticsManager $statisticsManager;
    
    public function __construct(
        private readonly StorageInterface $storage,
        private readonly EventFactory $eventFactory = new EventFactory(),
        ?StatisticsManager            $statisticsManager = null
    ) {
        $this->statisticsManager = $statisticsManager ?? new StatisticsManager($storage);
    }
    
    public function handleEvent(array $data): array
    {
        $event = ($this->eventFactory)($data);
        
        $this->storage->save($event);

        $this->statisticsManager->updateStatisticsFromEvent($event);
        
        return [
            'status' => 'success',
            'message' => 'Event saved successfully',
            'event' => $event->toArray()
        ];
    }
}