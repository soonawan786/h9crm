<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Task;
use Illuminate\Notifications\Messages\MailMessage;

class NewClientTask extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $task;
    private $user;
    private $emailSetting;

    public function __construct(Task $task)
    {
        $this->task = $task;
        $this->emailSetting = EmailNotificationSetting::userAssignTask();
        $this->company = $this->task->company;

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
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $dueDate = (!is_null($this->task->due_date)) ? $this->task->due_date->format($this->company->date_format) : null;

        $content = ucfirst($this->task->heading) . ' #' . $this->task->task_short_code . '<p>
            <b style="color: green">' . __('email.dueOn') . ': ' . $dueDate . '</b>
        </p>';

        return parent::build()
            ->subject(__('email.newClientTask.subject') . ' #' . $this->task->task_short_code . ' - ' . config('app.name') . '.')
            ->greeting(__('email.hello') . ' ' . $notifiable->name . ',')
            ->markdown('mail.task.task-created-client-notification', [
                'content' => $content
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->task->id,
            'created_at' => $this->task->created_at->format('Y-m-d H:i:s'),
            'heading' => $this->task->heading
        ];
    }

}
