<?php

namespace App\Listeners;

use App\Events\NewEmployeeEvent;
use App\Notifications\NewUserWhatsApp;
use Illuminate\Support\Facades\Notification;

class NewEmployeeListener
{

    public function handle(NewEmployeeEvent $event)
    {
        Notification::send($event->user, new NewUserWhatsApp($event->user));
    }

}
