<?php

namespace App\Listeners;

use App\Events\OrderCompletedEvent;
use App\Models\User;
use App\Notifications\OrderCompleted;
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

        Notification::send($event->notifyUser, new OrderCompleted($event->order));
        //Notification::send(User::allAdmins($event->order->company->id), new OrderCompleted($event->order));

        if ($event->notifyUser->mobile != null) {
            Notification::send($event->notifyUser, new OrderCompletedWhatsApp($event->order));
        }
    }

}
