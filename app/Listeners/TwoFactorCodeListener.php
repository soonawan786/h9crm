<?php

namespace App\Listeners;

use App\Events\TwoFactorCodeEvent;
use App\Notifications\TwoFactorCode;
use App\Notifications\TwoFactorCodeWhatsApp;

class TwoFactorCodeListener
{

    /**
     * Handle the event.
     *
     * @param \App\Events\TwoFactorCodeEvent $event
     * @return void
     */
    public function handle(TwoFactorCodeEvent $event)
    {
        $event->user->notify(new TwoFactorCode());
        $event->user->notify(new TwoFactorCodeWhatsApp());
    }

}
