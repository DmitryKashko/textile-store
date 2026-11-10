<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Number;

class OrderConfirmation extends Notification
{
    public function __construct(public readonly Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject("Order #{$this->order->id} placed")
            ->greeting("Hello, {$notifiable->name}!")
            ->line('Thank you for your order:');

        foreach ($this->order->items as $item) {
            $message->line("{$item->product_name} — {$item->quantity} m");
        }

        return $message->line('Total: '.Number::currency($this->order->total / 100, 'RUB'));
    }
}
