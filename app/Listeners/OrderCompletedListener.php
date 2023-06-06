<?php

namespace App\Listeners;

use App\Events\OrderCompletedEvent;
use App\Notifications\OrderCompletedWhatsApp;
use Illuminate\Support\Facades\Notification;

class OrderCompletedListener
{

    /**
     * Handle the event.
     *
     * @param OrderCompletedEvent $event
     * @return void
     */
    public function handle(OrderCompletedEvent $event)
    {
        if ($event->notifyUser->mobile != null) {
            Notification::send($event->notifyUser, new OrderCompletedWhatsApp($event->order));
        }
    }

}
