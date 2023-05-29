<?php

namespace App\Notifications;

use App\Channels\Messages\WhatsAppMessage;
use App\Channels\WhatsAppChannel;
use App\Http\Controllers\UrlShortenerController;
use Illuminate\Notifications\Messages\MailMessage;

class BirthdayReminderWhatsApp extends BaseNotification
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
        $list = '';
        $n = 1;
        foreach ($this->birthDays as $birthDay) {
            $list  .= $n++.'. '.$birthDay['name'].PHP_EOL ;
        }

        $url = route('dashboard');
        $url = getDomainSpecificUrl($url, $this->company);
        $short_url = UrlShortenerController::shorten($url);

        $content = __('email.BirthdayReminder.text') . PHP_EOL . $list;

        return (new WhatsAppMessage)
            ->content($this->count . ' ' . __('email.BirthdayReminder.subject').PHP_EOL.$content.' '.$short_url);
    }

    public function toArray()
    {
        return ['birthday_name' => $this->birthDays->upcomingBirthdays];
    }

}
