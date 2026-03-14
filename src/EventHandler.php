<?php

namespace App;

use App\Broadcast\EventBroadcasterInterface;
use App\Storage\StorageInterface;

readonly class EventHandler
{
    public function __construct(
        private StorageInterface          $storage,
        private EventBroadcasterInterface $broadcaster,
        private StatisticsManager         $statisticsManager,
    ) { }
    
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