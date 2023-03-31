<?php

namespace App\Notifications\SuperAdmin;

use App\Models\SlackSetting;
use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use App\Models\PushNotificationSetting;
use App\Models\EmailNotificationSetting;
use App\Models\SuperAdmin\SupportTicketReply;
use Illuminate\Notifications\Messages\SlackMessage;

class NewSupportTicketReply extends BaseNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $ticket;

    public function __construct(SupportTicketReply $ticket)
    {
        $this->emailSetting = EmailNotificationSetting::where('setting_name', 'New Support Ticket Request')->first();
        $this->ticket = $ticket->ticket;
        $this->pushNotification = PushNotificationSetting::first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = ['database'];

        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        if ($this->emailSetting->send_slack == 'yes') {
            array_push($via, 'slack');
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return parent::build()
            ->subject(__('superadmin.supportTicketReply.subject') . ' - ' . ucfirst($this->ticket->subject))
            ->greeting(__('email.hello') . ' ' . ucwords($notifiable->name) . '!')
            ->line(__('superadmin.supportTicketReply.text') . ' # ' . $this->ticket->id)
            ->action(__('superadmin.supportTicketReply.action'), route('superadmin.support-tickets.show', $this->ticket->id))
            ->line(__('email.thankyouNote'));
    }

    public function toSlack($notifiable)
    {
        if ($notifiable->isEmployee) {
            $slack = SlackSetting::first();

            if (count($notifiable->employee) > 0 && (!is_null($notifiable->employee[0]->slack_username) && ($notifiable->employee[0]->slack_username != ''))) {
                return (new SlackMessage())
                    ->from(config('app.name'))
                    ->image($slack->slack_logo_url)
                    ->to('@' . $notifiable->employee[0]->slack_username)
                    ->content('*' . __('email.supportTicketReply.subject') . '*' . "\n" . ucfirst($this->ticket->subject) . "\n" . __('modules.tickets.requesterName') . ' - ' . ucwords($this->ticket->requester->name));
            }

            return (new SlackMessage())
                ->from(config('app.name'))
                ->image($slack->slack_logo_url)
                ->content('This is a redirected notification. Add slack username for *' . ucwords($notifiable->name) . '*');
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    // phpcs:ignore
    public function toArray($notifiable)
    {
        return $this->ticket->toArray();
    }

}
