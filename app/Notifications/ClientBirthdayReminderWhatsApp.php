<?php

namespace App\Notifications;

use App\Channels\Messages\WhatsAppMessage;
use App\Channels\WhatsAppChannel;
use Illuminate\Notifications\Messages\MailMessage;

class ClientBirthdayReminderWhatsApp extends BaseNotification
{

    private $birthDays;
    private $count;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($event)
    {
        $this->birthDays = $event;
        $this->count = count($this->birthDays->upcomingBirthdays);
        $this->company = $this->birthDays->company;
    }

    /**
     * Get the notification's delivery channels.
     *
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
     * @return MailMessage
     */
    // phpcs:ignore
    public function toWhatsApp($notifiable)
    {
        $clientName = 'Dear '.$notifiable->name.PHP_EOL;

        $content = $clientName. PHP_EOL. PHP_EOL .__('email.BirthdayReminder.line1').PHP_EOL. PHP_EOL .__('email.BirthdayReminder.line2').$this->company->company_name.__('email.BirthdayReminder.line3').PHP_EOL.PHP_EOL.__('email.BirthdayReminder.line4').PHP_EOL.PHP_EOL.__('email.BirthdayReminder.line5').PHP_EOL.PHP_EOL.__('email.BirthdayReminder.line6').PHP_EOL.PHP_EOL.'Warmest regards, '.PHP_EOL.PHP_EOL.$this->company->company_name.PHP_EOL.PHP_EOL.$this->company->company_phone;

        return (new WhatsAppMessage)
            ->content($content);
    }

    public function toArray()
    {
        return ['birthday_name' => $this->birthDays->upcomingBirthdays];
    }

}
