<?php

namespace App\Notifications;

use App\Models\UserChat;
use Illuminate\Notifications\Messages\MailMessage;

class NewChat extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $userChat;

    public function __construct(UserChat $userChat)
    {
        $this->userChat = $userChat;
        $this->company = $this->userChat->company;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    // phpcs:ignore
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    // phpcs:ignore
    public function toMail($notifiable): MailMessage
    {
        $content = __('email.notificationIntro');

        return parent::build()
            ->markdown('mail.email', [
                'url' => route('messages.index'),
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.notificationAction'),
                'notifiableName' => $notifiable->name
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
            'id' => $this->userChat->id,
            'user_one' => $this->userChat->user_one,
            'from_name' => $this->userChat->fromUser->name,
        ];
    }

}
