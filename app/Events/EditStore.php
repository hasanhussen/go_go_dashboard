<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EditStore implements ShouldBroadcast
{
   use Dispatchable, InteractsWithSockets, SerializesModels;

    public $store;

    public function __construct($store)
    {
        $this->store = $store;
    }

    // القناة اللي رح يوصل عليها الإشعار
    public function broadcastOn()
    {
        return new PrivateChannel('store-admin-notifications');
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->store->id,
            'subject' => $this->store->name,
            'message' => 'تم تعديل متجر!',
            'type'=> 'store_edited',
        ];
    }

       public function broadcastAs()
    {
        return 'store_notification';
    }
}
