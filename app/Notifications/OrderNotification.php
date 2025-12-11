<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderNotification extends Notification
{
    use Queueable;
    protected $order; //يحتوي معلومات الطلب (اعمدة الجدول)
    protected $type; // نوع الإشعار (accept أو reject)
    /**
     * Create a new notification instance.
     */
    public function __construct($order,$type)
    {
        $this->order = $order;
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
                'order_id' => $this->order->id,
                'status' => $this->order->status,
                'total_price' => $this->order->total_price,
                'title' => 'تم قبول طلبك',
                'body' => 'تمت الموافقة على طلبك رقم ' . $this->order->id,
                'type' => 'order_accepted',
                'icon'=> "✅"
            ];
        } elseif ($this->type === 'order_on_the_way') {
            return [
                'order_id' => $this->order->id,
                'status' => $this->order->status,
                'total_price' => $this->order->total_price,
                'title' => 'طلبك قيدالتوصيل',
                'body' => ' طلبك رقم ' . $this->order->id . 'قيد التوصيل',
                'type' => 'order_on_the_way',
                'icon'=> "🛵"
            ];
        } elseif ($this->type === 'order_on_site') {
            return [
                'order_id' => $this->order->id,
                'status' => $this->order->status,
                'total_price' => $this->order->total_price,
                'title' => 'عامل التوصيل في الموقع',
                'body' => ' يرجى استلام طلبك رقم ' . $this->order->id,
                'type' => 'order_on_site',
                'icon'=> "📍"
            ];
        } elseif ($this->type === 'order_delivered') { // ✅ حالة الاسترجاع
            return [
                'order_id' => $this->order->id,
                'status' => $this->order->status,
                'total_price' => $this->order->total_price,
                'title' => 'تم التسليم',
                'body' => ' تم تسليم طلبك رقم ' . $this->order->id,
                'type' => 'order_delivered',
                'icon'=> "📦"
            ];
        } elseif ($this->type === 'new_order') { // ✅ حالة الاسترجاع
            return [
                'order_id' => $this->order->id,
                'status' => $this->order->status,
                'total_price' => $this->order->total_price,
                'title' => 'طلب جديد',
                'body' => ' هناك طلب بانتظارالاستلام',
                'type' => 'new_order',
                'icon'=> "🛎️"
            ];
        }
        elseif ($this->type === 'order_assign') { // ✅ حالة الاسترجاع
            return [
                'order_id' => $this->order->id,
                'status' => $this->order->status,
                'total_price' => $this->order->total_price,
                'title' => 'هناك مهمة لك ',
                'body' => 'تم إسناد اليك الطلب رقم ' . $this->order->id,
                'type' => 'order_assign',
                'icon'=> "🛎️"
            ];
        }
        else{
            return [
                'order_id' => $this->order->id,
                'status' => $this->order->status,
                'total_price' => $this->order->total_price,
                'reason' => $this->order->delete_reason ?? 'لا يوجد سبب محدد',
                'title' => 'تم رفض طلبك ',
                'body' => 'تم رفض طلبك رقم ' . $this->order->id . ' بسبب ' . ($this->order->delete_reason ?? 'غير محدد'),
                'type' => 'order_rejected',
                'icon'=> "❌"
            ];
        }

    }

    // public function toArray(object $notifiable): array
    // {
    //     return [
    //         //
    //     ];
    // }
}
