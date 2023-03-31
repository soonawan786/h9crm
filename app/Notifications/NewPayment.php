<?php

namespace App\Notifications;

use App\Models\Company;
use App\Models\EmailNotificationSetting;
use App\Models\Payment;

class NewPayment extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $payment;
    private $emailSetting;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
        $this->company = $this->payment->company;

        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'payment-notification')->first();
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
     * @return \Illuminate\Notifications\Messages\MailMessage|void
     */
    public function toMail($notifiable)
    {
        if (($this->payment->project_id && $this->payment->project->client_id != null) || ($this->payment->invoice_id && $this->payment->invoice->client_id != null)) {

            $url = route('payments.show', $this->payment->id);
            $url = getDomainSpecificUrl($url, $this->company);

            $content = __('email.payment.text');

            return parent::build()
                ->subject(__('email.payment.subject') . ' - ' . config('app.name') . '.')
                ->markdown('mail.email', [
                    'url' => $url,
                    'content' => $content,
                    'themeColor' => $this->company->header_color,
                    'actionText' => __('email.payment.action'),
                    'notifiableName' => $notifiable->name
                ]);
        }
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
        return $this->payment->toArray();
    }

}
