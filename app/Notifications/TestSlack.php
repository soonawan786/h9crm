<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\SlackMessage;

class TestSlack extends BaseNotification
{


    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    // phpcs:ignore
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    // phpcs:ignore
    public function toMail($notifiable)
    {
        $url = getDomainSpecificUrl(route('login'));
        $content = __('email.notificationIntro');

        return parent::build()
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.notificationAction')
            ]);
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
        return [
            //
        ];
    }

    public function toSlack($notifiable)
    {
        $slack = $notifiable->company->slackSetting;

        if (count($notifiable->employee) > 0 && !is_null($notifiable->employee[0]->slack_username)) {
            return (new SlackMessage())
                ->from(config('app.name'))
                ->image(asset_url('slack-logo/' . $slack->slack_logo))
                ->to('@' . $notifiable->employee[0]->slack_username)
                ->content('This is a test notification.');
        }

        return (new SlackMessage())
            ->from(config('app.name'))
            ->image(asset_url('slack-logo/' . $slack->slack_logo))
            ->content('*' . __('Test slack') . '*' . "\n" .'This is a redirected notification. Add slack username for *' . $notifiable->name . '*');
    }

}
