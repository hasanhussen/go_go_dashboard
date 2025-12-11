<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StoreNotification extends Notification
{
    use Queueable;
    protected $store; //يحتوي معلومات المتجر (اعمدة الجدول)
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct($store,$type)
    {
        $this->store = $store;
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
                'store_id' => $this->store->id,
                'status' => $this->store->status,
                'title' => 'تم قبول متجرك',
                'body' => 'تمت الموافقة على متجرك ' . $this->store->name,
                'type' => 'store_accepted',
                'icon'=> "✅"
            ];
        } elseif ($this->type === 'reject') {
            return [
                'store_id' => $this->store->id,
                'status' => $this->store->status,
                'reason' => $this->store->delete_reason ?? 'لا يوجد سبب محدد',
                'title' => 'تم رفض متجرك ',
                'body' => 'تم رفض متجرك ' . $this->store->name . ' بسبب ' . ($this->store->delete_reason ?? 'غير محدد'),
                'type' => 'store_rejected',
                'icon'=> "❌"
            ];
        } elseif ($this->type === 'banned') {
            return [
                'store_id' => $this->store->id,
                'status' => $this->store->status,
                'reason' => $this->store->delete_reason ?? 'لا يوجد سبب محدد',
                'title' => 'تم حظر متجرك ❌',
                'body' => 'تم حظر متجرك ' . $this->store->name . ' حتى ' . $this->store->ban_until . ' بسبب ' . $this->store->ban_reason,
                'type' => 'store_banned',
                'icon'=> "🔒"
            ];
        } elseif ($this->type === 'restored') { // ✅ حالة الاسترجاع
            return [
                'store_id' => $this->store->id,
                'status' => $this->store->status,
                'title' => 'تم استرجاع متجرك ',
                'body' => 'تم استرجاع متجرك ' . $this->store->name . ' من المحذوفات',
                'type' => 'store_restored',
                'icon'=> "♻️"
            ];
        } else { // حالة unbanned
            return [
                'store_id' => $this->store->id,
                'status' => $this->store->status,
                'reason' => $this->store->delete_reason ?? 'لا يوجد سبب محدد',
                'title' => 'تم الغاء حظر متجرك',
                'body' => 'تم الغاء حظر متجرك و إتاحة زيارته ' . $this->store->name,
                'type' => 'store_unbanned',
                'icon'=> "🔓"
            ];
        }
    }
}
