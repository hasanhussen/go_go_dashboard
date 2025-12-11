<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class Unban extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:unban';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'unban users, stores, and meals whose ban duration has expired';

    /**
     * Execute the console command.
     */
        public function handle()
    {
        $now = Carbon::now('UTC');
        $notificationService = new \App\Services\NotificationService();
        $models = [
          \App\Models\User::class,
          \App\Models\Store::class,
          \App\Models\Meal::class,
         ];
         foreach ($models as $model) {
            $bannedItems = $model::whereNotNull('ban_until')
                ->whereNotNull('ban_reason')
                ->get();

            foreach ($bannedItems as $item) {
                $banEnd = $item->ban_until;
                if ($now->startOfDay()->greaterThanOrEqualTo(Carbon::parse($banEnd)->startOfDay())) {
                    $item->status = "1";
                    $item->ban_reason = null;
                    $item->ban_until = null;
                    $item->save();

                     $user = null;
                if ($item instanceof \App\Models\User) {
                    $user = $item;
                } elseif ($item instanceof \App\Models\Store) {
                    $user = $item->user ?? null;
                } elseif ($item instanceof \App\Models\Meal) {
                    $user = $item->store->user ?? null;
                }

                    $type = match (true) {
                        $item instanceof \App\Models\User => 'user_unbanned',
                        $item instanceof \App\Models\Store => 'store_unbanned',
                        $item instanceof \App\Models\Meal => 'meal_unbanned',
                        default => 'unbanned',
                    };

                    $notificationService->sendToUser( $user,'تم رفع الحظر ✅', 
                    ($item instanceof \App\Models\Store ? 'تم رفع الحظر عن متجرك ' : ($item instanceof \App\Models\Meal ? 'تم رفع الحظر عن منتجك ' : 'تم رفع الحظر عن حسابك ')) . ($item->name ?? ''),
                    [
                            'type' => $type,
                            'id' => (string) $item->id,
                    ] );

                    // 🔔 Laravel Notification
                    if ($item instanceof \App\Models\Store) {
                        \Illuminate\Support\Facades\Notification::send($user, new \App\Notifications\StoreNotification($item, 'unbanned'));
                    } elseif ($item instanceof \App\Models\Meal) {
                        \Illuminate\Support\Facades\Notification::send($user, new \App\Notifications\MealNotification($item, 'unbanned'));
                    } else{
                         \Illuminate\Support\Facades\Notification::send($user, new \App\Notifications\UserNotification($item, 'unbanned'));
                    }
              
                }
            }
    }
}
}
