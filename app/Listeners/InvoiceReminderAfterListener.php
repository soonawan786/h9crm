<?php

namespace App\Listeners;

use App\Events\InvoiceReminderAfterEvent;
use App\Notifications\InvoiceReminderAferWhatsApp;
use App\Notifications\InvoiceReminderAfter;
use Notification;
//use Illuminate\Support\Facades\Notification;

class InvoiceReminderAfterListener
{

    /**
     * Create the event listener.
     *
     * @return void
     */

    public function handle(InvoiceReminderAfterEvent $event)
    {
        //Notification::send($event->notifyUser, new InvoiceReminderAfter($event->invoice));
        Notification::send($event->notifyUser, new InvoiceReminderAferWhatsApp($event->invoice));
    }

}
