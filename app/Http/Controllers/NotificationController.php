<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Support;
use App\Notifications\AdminNotification;
use Illuminate\Notifications\DatabaseNotification;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Notification;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
     public function index(Request $request)
    {
        // جلب كل الادمنين و المحررين
        $admin= User::role('admin')->orderBy('created_at', 'asc')->first();
    if (!$admin) {
        return back()->with('error', 'لا يوجد مستخدم بدور مدير النظام.');
    }

        // جلب كل الإشعارات الخاصة بهم
        $notifications = DatabaseNotification::where('notifiable_id', $admin->id)->where('type', "App\Notifications\AdminNotification")
                                             ->orderBy('created_at', 'desc')
                                             ->paginate(20);

         $admin->unreadNotifications->markAsRead();

        return view('admin.notifications.index', compact('notifications'));
    }

    public function sendNotificationToAll(Request $request){
    $notificationService = new \App\Services\NotificationService();

        $type = $request->input('type'); 
        $title = $request->input('title');
        $body = $request->input('body');

    $notificationService->sendNotificationByType($type, $title, $body);

    return back()->with('success', ' تم إرسال الإشعار بنحاج ');

    }

        public function sendNotification(Request $request,$user_id){

        $title = $request->input('title');
        $body = $request->input('body');
        $user = User::findOrFail($user_id);

        $notificationService = new \App\Services\NotificationService();
        $notificationService->sendToUser(
        $user,
        $title,
        $body,
        [
        'type' => 'custom_notification',
        'user_id' => (string) $user_id,  // لازم قيم الـ data تكون نصوص
        ]);
             
        Notification::send($user, new AdminNotification(
                $user,
                title: $title,
                body: $body));

                return back()->with('success', ' تم إرسال الإشعار بنحاج ');

    }

    public function unreadCount()
{
    // $user = auth()->user();
    $user = Auth::user();

    $unreadNotificationscount = $user->unreadNotifications()->count();

    $unreadSupportcount =  Support::where('status', 'new')->count();

    return response()->json([
        'unreadNotifications_count' => $unreadNotificationscount,
        'unreadSupportc_count' => $unreadSupportcount,
]);
}

}
