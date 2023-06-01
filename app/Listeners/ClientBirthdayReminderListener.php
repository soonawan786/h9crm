<?php

namespace App\Listeners;

use App\Events\ClientBirthdayReminderEvent;
use App\Models\User;
use App\Notifications\ClientBirthdayReminderWhatsApp;
use Illuminate\Support\Facades\Notification;
class ClientBirthdayReminderListener
{

    /**
     * Handle the event.
     *
     * @param \App\Events\BirthdayReminderEvent $event
     * @return void
     */
    public function handle(ClientBirthdayReminderEvent $event)
    {
        $clientId = array_column($event->upcomingBirthdays, 'id');
        $clients = User::whereIn('id',$clientId)->get();
        //client birthday notification
        Notification::send($clients, new ClientBirthdayReminderWhatsApp($event));
    }

}
