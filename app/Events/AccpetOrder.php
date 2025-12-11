<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccpetOrder
{
       use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    // القناة اللي رح يوصل عليها الإشعار
    public function broadcastOn()
    {
        return new PrivateChannel('order-admin-notifications');
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->order->id,
            'subject' => 'تمت الموافقة على الطلب رقم ' . $this->order->id,
            'message' => 'تم قبول الطلب',
            'type'=> 'order_accept',
        ];
    }

       public function broadcastAs()
    {
        return 'order_notification';
    }
}
