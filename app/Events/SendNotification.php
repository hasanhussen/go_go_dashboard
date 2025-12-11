<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $support;

    public function __construct($support)
    {
        $this->support = $support;
    }

    // القناة اللي رح يوصل عليها الإشعار
    public function broadcastOn()
    {
        // return new PrivateChannel('support-notifications');
        return new Channel('support-notifications');
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->support->id,
            'message' => 'تم استلام شكوى جديدة!',
            'user' => $this->support->user->name ?? 'مستخدم',
            
        ];
    }

       public function broadcastAs()
    {
        // اسم الحدث اللي رح توصله الـ frontend
        return 'notification';
    }
}
