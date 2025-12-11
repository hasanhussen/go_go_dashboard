<?php

namespace App\Services;

use App\Jobs\SendNotificationJob;
use App\Models\User;
use App\Notifications\AdminNotification;
use App\Notifications\UserNotification;
use App\Notifications\StoreNotification;
use App\Notifications\MealNotification;
use App\Notifications\OrderNotification;
use Illuminate\Support\Facades\Notification;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class NotificationService
{
    protected $firebase;

    public function __construct()
    {
        $this->firebase = (new Factory)
            ->withServiceAccount(config('services.firebase.credentials'))
            ->createMessaging();
    }

    /**
     * ارسال اشعار لكل المستخدمين
     */
    public function sendToAllUsers($title, $body, $type, $data = [])
    {

         User::select('id')->chunk(100, function($users) use ($title, $body, $type, $data) {
            $ids = $users->pluck('id')->toArray();
            SendNotificationJob::dispatch($ids, $title, $body, $type, $data);
        });

        // $users = User::all();
        // $this->sendLaravelNotification($users, $title, $body, $type, $data);
        $this->sendFirebaseTopic('users', $title, $body, $type, $data);
    }

    /**
     * ارسال اشعار لكل عمال التوصيل
     */
    public function sendToDelivery($title, $body, $type, $data = [])
    {
         User::role('delivery')->select('id')->chunk(100, function($users) use ($title, $body, $type, $data) {
            $ids = $users->pluck('id')->toArray();
            SendNotificationJob::dispatch($ids, $title, $body, $type, $data);
        });
        // $users = User::role('delivery')->get();
        // $this->sendLaravelNotification($users, $title, $body, $type, $data);
        $this->sendFirebaseTopic('delivery', $title, $body, $type, $data);
    }

    /**
     * ارسال اشعار لكل التجار
     */
    public function sendToStores($title, $body, $type, $data = [])
    {
        User::role('owner')->select('id')->chunk(100, function($users) use ($title, $body, $type, $data) {
            $ids = $users->pluck('id')->toArray();
            SendNotificationJob::dispatch($ids, $title, $body, $type, $data);
        });
        // $users = User::role('owner')->get();
        // $this->sendLaravelNotification($users, $title, $body, $type, $data);
        $this->sendFirebaseTopic('owner', $title, $body, $type, $data);
    }

    /**
     * دالة مساعدة لإرسال Laravel Notifications
     */
    // protected function sendLaravelNotification($users, $title, $body, $type, $data = [])
    // {
    //     foreach($users as $user){
    //         Notification::send($user, new AdminNotification(
    //             $user,
    //             $type,
    //             $data['support'] ?? null,
    //             $data['meal'] ?? null,
    //             $data['store'] ?? null,
    //             $title,
    //             $body
    //         ));
    //     }
    // }

    /**
     * دالة مساعدة لإرسال Firebase Topic
     */
    protected function sendFirebaseTopic($topic, $title, $body, $type, $data = [])
    {
        $message = CloudMessage::new()
            ->withNotification([
                'title' => $title,
                'body'  => $body,
            ])
            ->withData(array_merge(['type' => $type], $data))
            ->toTopic($topic);

        $this->firebase->send($message);
    }

    public function sendNotificationByType($type, $title, $body, $data = [])
{
    switch($type){
        case 'users':
            $this->sendToAllUsers($title, $body, $type, $data);
            break;
        case 'workers':
            $this->sendToDelivery($title, $body, $type, $data);
            break;
        case 'owners':
            $this->sendToStores($title, $body, $type, $data);
            break;
        default:
            throw new \Exception("نوع الإشعار غير معروف: $type");
    }
}

public function sendToUser($user,$title,$body,$data = []){
    
    $fcmTokens = $user->fcmTokens()->pluck('token')->toArray();

    if ($user && count($fcmTokens) > 0) {
    $firebase = (new Factory)
        ->withServiceAccount(config('services.firebase.credentials'))
        ->createMessaging();

    foreach ($fcmTokens as $token) {
        $message = [
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data
        ];

        $firebase->send($message);
    }

   
   

    //  switch($notification){
    //     case 'user':
    //         Notification::send($user, new UserNotification($item, $type));
    //         break;
    //     case 'store':
    //         Notification::send($user, new StoreNotification($item, $type));
    //         break;
    //     case 'meal':
    //         Notification::send($user, new MealNotification($item, $type));
    //         break;
    //     case 'order':
    //         Notification::send($user, new OrderNotification($item, $type));
    //         break;
    //     case 'admin':
    //     Notification::send($user, new AdminNotification(
    //         $user,
    //         title: $title,
    //         body: $body
    //     ));

    //         break;
    //     default:
    //         throw new \Exception("نوع الإشعار غير معروف: $type");
    // }
}

}

}
