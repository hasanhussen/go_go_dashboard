<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNotification extends Notification
{
    use Queueable;
    protected $user; //يحتوي معلومات الطلب (اعمدة الجدول)
    protected $type;
    protected $support;
    protected $product;
    protected $store;
    protected $order;
    protected $title;
    protected $body;

    /**
     * Create a new notification instance.
     */
    public function __construct(
    $user,
    $type = null,
    $support = null,
    $product = null,
    $store = null,
    $order = null,
    $title = null,
    $body = null
) {
    $this->user = $user;
    $this->type = $type;
    $this->support = $support;
    $this->product = $product;
    $this->store = $store;
    $this->order = $order;
    $this->title = $title;
    $this->body = $body;
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
        if ($this->type === 'admin_support') {
            return [
                'title' => 'شكوى جديدة',
                'body' => 'يوجد شكوى جديدة يرجى الاطلاع عليها ',
                'user_name' => $this->user->name,
                'support_id'=>$this->support->id,
                'subject'=>$this->support->subject,
                'type' => 'admin_support',
                'icon'=> "📩"
            ];
        } 
        elseif ($this->type === 'user_support') {
            return [
                'user_id' => $this->user->id,
                'title' => 'تم الاستجابة للشكوى ',
                'body' => $this->support->reply,
                'support_id'=>$this->support->id,
                'type' => 'user_support',
                'icon'=> "💬" 
            ];
        } elseif ($this->type === 'product_edit') {
            return [
                'user_id' => $this->user->id,
                'product_id'=>$this->product->id,
                'product_name'=>$this->product->name,
                'title' => 'تم تعديل منتج',
                'body' => 'تم تعديل المنتج: '  . $this->product->name . ' من متجر ' . $this->store->name . ' بتاريخ ' . $this->product->updated_at,
                'type' => 'product_edit',
                'icon'=>  "🛠️"
            ];
        } elseif ($this->type === 'store_edit') { 
            return [
                'user_id' => $this->user->id,
                'status' => $this->user->status,
                'store_id'=>$this->store->id,
                'title' => 'تم تعديل متجر',
                'body' => 'تم تعديل المتجر: '  . $this->store->name  . ' بتاريخ ' . $this->store->updated_at,
                'type' => 'store_edit',
                'icon'=> "🏪" 
            ];
        } elseif ($this->type === 'order_accept') { 
            return [
                'order_id' => $this->order->id,
                'status' => $this->order->status,
                'total_price' => $this->order->total_price,
                'title' => 'تم قبول الطلب',
                'body' => 'تمت الموافقة على الطلب رقم ' . $this->order->id,
                'type' => 'order_accept',
                'icon'=> "✅"
            ];
        } 
         else { 
            return [
                'user_id' => $this->user->id,
                'title' => $this->title,
                'body' => $this->body,
                'type' => 'custom_notification',
                'icon'=> "📢"
            ];
        }
    }
}
