<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Notifications\OrderConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderConfirmation implements ShouldQueue
{
    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [10, 60];

    public function handle(OrderPlaced $event): void
    {
        $order = $event->order->loadMissing('user', 'items');

        $order->user->notify(new OrderConfirmation($order));
    }
}
