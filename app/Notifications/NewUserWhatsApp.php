<?php

namespace App\Notifications;

use App\Channels\Messages\WhatsAppMessage;
use App\Channels\WhatsAppChannel;
use App\Models\EmailNotificationSetting;
use App\Models\User;
use Illuminate\Notifications\Messages\SlackMessage;

class NewUserWhatsApp extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */

    public function __construct(User $user)
    {
        $this->company = $user->company;
    }

    /**
     * Get the notification's delivery channels.
     *t('mail::layout')
     * @param mixed $notifiable
     * @return array
     */
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
        $clientName = 'Dear '.$notifiable->name.',';

        $content = $clientName. PHP_EOL. PHP_EOL .__('email.newUser.line1').$this->company->company_name .__('email.newUser.line2').PHP_EOL.PHP_EOL.'At '.$this->company->company_name.__('email.newUser.line3').PHP_EOL.PHP_EOL.__('email.newUser.line4').PHP_EOL.PHP_EOL.__('email.newUser.line5').PHP_EOL.PHP_EOL.__('email.newUser.line6').$this->company->company_name.__('email.newUser.line7').PHP_EOL.PHP_EOL.__('email.newUser.line8').PHP_EOL.PHP_EOL.'Best regards, '.PHP_EOL.PHP_EOL.$this->company->company_name.PHP_EOL.PHP_EOL.$this->company->company_phone;
        return (new WhatsAppMessage)
        ->content($content);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    //phpcs:ignore
    public function toArray($notifiable)
    {
        return $notifiable->toArray();
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return SlackMessage
     */
}
