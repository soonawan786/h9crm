<?php

namespace App\Notifications;

use App\Channels\Messages\WhatsAppMessage;
use App\Channels\WhatsAppChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;

class TwoFactorCodeWhatsApp extends BaseNotification
{


    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    //phpcs:ignore
    public function via($notifiable)
    {
        return [WhatsAppChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */

     public function toWhatsApp($notifiable)
    {

        $this->company = $notifiable->company;

        $twoFaCode = $notifiable->userAuth->two_factor_code .PHP_EOL;

        $content = __('email.twoFactor.line1') . PHP_EOL . $twoFaCode . PHP_EOL . __('email.twoFactor.line2') . PHP_EOL . __('email.twoFactor.line3');

        return (new WhatsAppMessage)
            ->content($content);
    }

}
