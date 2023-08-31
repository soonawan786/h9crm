<?php

namespace App\Listeners;

use App\Events\InvoiceUpdatedEvent;
use App\Events\NewInvoiceEvent;
use App\Notifications\InvoiceUpdated;
use App\Notifications\InvoiceWhatsAppUpdated;
use Illuminate\Support\Facades\Notification;

class InvoiceUpdatedListener
{

    /**
     * Handle the event.
     *
     * @param InvoiceUpdatedEvent $event
     * @return void
     */

    public function handle(InvoiceUpdatedEvent $event)
    {
        Notification::send($event->notifyUser, new InvoiceUpdated($event->invoice));
//whatsapp notification
        Notification::send($event->notifyUser, new InvoiceWhatsAppUpdated($event->invoice));

    }

}
