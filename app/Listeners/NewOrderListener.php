<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\NewOrderEvent;
use App\Notifications\NewOrder;
use App\Notifications\NewOrderWhatsApp;
use Illuminate\Support\Facades\Notification;

class NewOrderListener
{

    /**
     * Handle the event.
     *
     * @param NewOrderEvent $event
     * @return void
     */

    public function handle(NewOrderEvent $event)
    {
        Notification::send($event->notifyUser, new NewOrder($event->order));
        Notification::send(User::allAdmins($event->order->company->id), new NewOrder($event->order));

        if ($event->notifyUser->mobile != null) {
            Notification::send($event->notifyUser, new NewOrderWhatsApp($event->order));
        }
    }

}
