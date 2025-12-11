<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserNotification extends Notification 
{
    use Queueable;
    protected $user; //يحتوي معلومات الطلب (اعمدة الجدول)
    protected $type;
    /**
     * Create a new notification instance.
     */
    public function __construct($user,$type)
    {
        $this->user = $user;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    // public function toMail(object $notifiable): MailMessage
    // {
    //     return (new MailMessage)
    //         ->line('The introduction to the notification.')
    //         ->action('Notification Action', url('/'))
    //         ->line('Thank you for using our application!');
    // }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        if ($this->type === 'accept') {
            return [
                'user_id' => $this->user->id,
                'status' => $this->user->status,
                'title' => '  تم القبول',
                'body' => '  تمت الموافقة على حسابك' . $this->user->name,
                'type' => 'user_accepted',
                'icon'=> "✅"
            ];
        } 
        elseif ($this->type === 'reject') {
            return [
                'user_id' => $this->user->id,
                'status' => $this->user->status,
                'reason' => $this->user->delete_reason ?? 'لا يوجد سبب محدد',
                'title' => 'تم رفض حسابك ',
                'body' => 'تم رفض حسابك ' . $this->user->name . ' بسبب ' . ($this->user->delete_reason ?? 'غير محدد'),
                'type' => 'user_rejected',
                'icon'=> "❌"
            ];
        } elseif ($this->type === 'banned') {
            return [
                'user_id' => $this->user->id,
                'status' => $this->user->status,
                'reason' => $this->user->delete_reason ?? 'لا يوجد سبب محدد',
                'title' => 'تم حظر حسابك',
                'body' => 'تم حظر حسابك ' . $this->user->name . ' حتى ' . $this->user->ban_until . ' بسبب ' . $this->user->ban_reason,
                'type' => 'user_banned',
                'icon'=> "🔒"
            ];
        } elseif ($this->type === 'restored') { // ✅ حالة الاسترجاع
            return [
                'user_id' => $this->user->id,
                'status' => $this->user->status,
                'title' => 'تم استرجاع حسابك ',
                'body' => 'تم استرجاع حسابك ' . $this->user->name . ' من المحذوفات',
                'type' => 'user_restored',
                'icon'=> "♻️"
            ];
        }  else { // حالة unbanned
            return [
                'user_id' => $this->user->id,
                'status' => $this->user->status,
                'reason' => $this->user->delete_reason ?? 'لا يوجد سبب محدد',
                'title' => 'تم الغاء حظر الحساب',
                'body' => 'تم الغاء حظر حسابك  ' . $this->user->name,
                'type' => 'user_unbanned',
                'icon'=> "🔓"
            ];
        }
    }

}

