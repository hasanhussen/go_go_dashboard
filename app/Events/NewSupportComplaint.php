<?php

namespace App\Events;

use App\Models\Complaint;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewSupportComplaint implements ShouldBroadcast
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
        return new PrivateChannel('support-admin-notifications');
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->support->id,
            'subject' => $this->support->subject,
            'message' => 'تم استلام شكوى جديدة!',
            'type'=> 'new_support_ticket',
        ];
    }

       public function broadcastAs()
    {
        return 'admin_notification';
    }
}

