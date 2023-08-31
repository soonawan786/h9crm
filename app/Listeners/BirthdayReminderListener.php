<?php

namespace App\Listeners;

use App\Events\BirthdayReminderEvent;
use App\Models\User;
use App\Notifications\BirthdayReminder;
use Notification;
use App\Notifications\BirthdayReminderWhatsApp;
class BirthdayReminderListener
{

    /**
     * Handle the event.
     *
     * @param \App\Events\BirthdayReminderEvent $event
     * @return void
     */
    public function handle(BirthdayReminderEvent $event)
    {
        $users = User::allEmployees(null, false, null, $event->company->id);

        Notification::send($users, new BirthdayReminder($event));

        Notification::send($users, new BirthdayReminderWhatsApp($event));
    }

}
