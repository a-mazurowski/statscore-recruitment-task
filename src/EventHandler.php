<?php

namespace App;

use App\Broadcast\EventBroadcasterInterface;
use App\Storage\StorageInterface;

class EventHandler
{
    private StatisticsManager $statisticsManager;
    
    public function __construct(
        private readonly StorageInterface $storage,
        private readonly EventBroadcasterInterface $broadcaster,
        ?StatisticsManager            $statisticsManager = null
    ) {
        $this->statisticsManager = $statisticsManager ?? new StatisticsManager($storage);
    }
    
    public function handleEvent(array $data): array
    {
        $event = new EventFactory()($data);
        
        $this->storage->save($event);

        $this->statisticsManager->updateStatisticsFromEvent($event);

        $this->broadcaster->broadcast($event);
        
        return [
            'status' => 'success',
            'message' => 'Event saved successfully',
            'event' => $event->toArray()
        ];
    }
}