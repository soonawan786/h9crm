<?php

namespace App\Listeners;

use App\Events\NewInvoiceEvent;
use App\Notifications\NewInvoice;
use App\Notifications\NewInvoiceWhatsApp;
use Illuminate\Support\Facades\Notification;

class NewInvoiceListener
{

    /**
     * Handle the event.
     *
     * @param NewInvoiceEvent $event
     * @return void
     */

    public function handle(NewInvoiceEvent $event)
    {
        if ($event->notifyUser->email != null) {
            Notification::send($event->notifyUser, new NewInvoice($event->invoice));
        }
if ($event->notifyUser->mobile != null) {
            Notification::send($event->notifyUser, new NewInvoiceWhatsApp($event->invoice));
        }

    }

}
