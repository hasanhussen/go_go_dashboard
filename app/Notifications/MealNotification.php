<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MealNotification extends Notification
{
    use Queueable;
    protected $meal; //يحتوي معلومات المتجر (اعمدة الجدول)
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct($meal,$type)
    {
        $this->meal = $meal;
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
                'meal_id' => $this->meal->id,
                'status' => $this->meal->status,
                "store_id"=>$this->meal->store_id,
                'title' => 'تم قبول المنتج ',
                'body' => 'تمت الموافقة على منتجك ' . $this->meal->name,
                'type' => 'meal_accepted',
                'icon'=> "✅"
            ];
        } elseif ($this->type === 'reject') {
            return [
                'meal_id' => $this->meal->id,
                'status' => $this->meal->status,
                "store_id"=>$this->meal->store_id,
                'reason' => $this->meal->delete_reason ?? 'لا يوجد سبب محدد',
                'title' => 'تم رفض المنتج ',
                'body' => 'تم رفض منتجك ' . $this->meal->name . ' بسبب ' . ($this->meal->delete_reason ?? 'غير محدد'),
                'type' => 'meal_rejected',
                'icon'=> "❌"
            ];
        } elseif ($this->type === 'banned') {
            return [
                'meal_id' => $this->meal->id,
                'status' => $this->meal->status,
                "store_id"=>$this->meal->store_id,
                'reason' => $this->meal->delete_reason ?? 'لا يوجد سبب محدد',
                'title' => 'تم حظر المنتج ❌',
                'body' => 'تم حظر منتجك ' . $this->meal->name . ' حتى ' . $this->meal->ban_until . ' بسبب ' . $this->meal->ban_reason,
                'type' => 'meal_banned',
                'icon'=> "🔒"
            ];
        } elseif ($this->type === 'restored') { // ✅ حالة الاسترجاع
            return [
                'meal_id' => $this->meal->id,
                'status' => $this->meal->status,
                "store_id"=>$this->meal->store_id,
                'title' => 'تم استرجاع المنتج ',
                'body' => 'تم استرجاع منتجك ' . $this->meal->name . ' من المحذوفات',
                'type' => 'meal_restored',
                'icon'=> "♻️"
            ];
        } else { // حالة unbanned
            return [
                'meal_id' => $this->meal->id,
                'status' => $this->meal->status,
                "store_id"=>$this->meal->store_id,
                'reason' => $this->meal->delete_reason ?? 'لا يوجد سبب محدد',
                'title' => 'تم الغاء حظر المنتج ✅',
                'body' => 'تم الغاء حظر منتجك و إتاحة زيارته ' . $this->meal->name,
                'type' => 'meal_unbanned',
                'icon'=> "🔓"
            ];
        }
    }
}
