<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\LeadFollowUp;

class AutoFollowUpReminder extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $leadFollowup;
    private $emailSetting;

    public function __construct(LeadFollowUp $leadFollowup)
    {
        $this->leadFollowup = $leadFollowup;
        $this->company = $leadFollowup->lead->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'follow-up-reminder')->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = ['database'];

        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = route('leads.show', $this->leadFollowup->lead->id) . '?tab=follow-up';

        $url = getDomainSpecificUrl($url, $this->company);

        $followUpDate = (!is_null($this->leadFollowup->next_follow_up_date)) ? $this->leadFollowup->next_follow_up_date->format($this->company->date_format) : null;

        $content = __('email.followUpReminder.nextFollowUpDate') . ' :- ' . $followUpDate . '<br>' . ucfirst($this->leadFollowup->remark);

        return parent::build()
            ->subject(__('email.followUpReminder.subject') . ' #' . $this->leadFollowup->id . ' - ' . config('app.name') . '.')
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.followUpReminder.action'),
                'notifiableName' => $notifiable->name
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    // phpcs:ignore
    public function toArray($notifiable)
    {
        return [
            'follow_up_id' => $this->leadFollowup->id,
            'id' => $this->leadFollowup->lead->id,
            'created_at' => $this->leadFollowup->created_at->format('Y-m-d H:i:s'),
            'heading' => __('email.followUpReminder.subject'),
        ];
    }

}
