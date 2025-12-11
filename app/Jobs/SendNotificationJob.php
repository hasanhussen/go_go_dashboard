<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\AdminNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userIds;
    protected $title;
    protected $body;
    protected $type;
    protected $data;

    public function __construct($userIds, $title, $body, $type, $data = [])
    {
        $this->userIds = $userIds;
        $this->title = $title;
        $this->body = $body;
        $this->type = $type;
        $this->data = $data;
    }

    public function handle(): void
    {
        $users = User::whereIn('id', $this->userIds)->get();

        foreach ($users as $user) {
            Notification::send(
                $user,
                new AdminNotification(
                    $user,
                    $this->type,
                    $this->data['support'] ?? null,
                    $this->data['product'] ?? null,
                    $this->data['store'] ?? null,
                    $this->data['order'] ?? null,
                    $this->title,
                    $this->body
                )
            );
        }
    }
}
