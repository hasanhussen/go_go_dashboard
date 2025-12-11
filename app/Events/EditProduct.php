<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EditProduct implements ShouldBroadcast
{
   use Dispatchable, InteractsWithSockets, SerializesModels;
   

    public $product;

    public function __construct($product)
    {
        $this->product = $product;
    }

    // القناة اللي رح يوصل عليها الإشعار
    public function broadcastOn()
    {
        return new PrivateChannel('product-admin-notifications');
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->product->id,
            'subject' => $this->product->name,
            'message' => 'تم تعديل منتج!',
            'type'=> 'product_edited',
        ];
    }

       public function broadcastAs()
    {
        return 'product_notification';
    }
}
