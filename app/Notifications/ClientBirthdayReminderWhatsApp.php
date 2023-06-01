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
        $text = $this->company->company_name.' wishes you a very happy birthday and thank you for being our valued customer!';

        $content = $clientName.$text;

        return (new WhatsAppMessage)
            ->content($content);
    }

    public function toArray()
    {
        return ['birthday_name' => $this->birthDays->upcomingBirthdays];
    }

}
