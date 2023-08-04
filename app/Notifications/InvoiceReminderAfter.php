<?php

namespace App\Notifications;

use Illuminate\Support\HtmlString;

class InvoiceReminderAfter extends BaseNotification
{

    private $invoice;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($invoice)
    {
        $this->invoice = $invoice;
        $this->company = $this->invoice->company;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = [];

        if ($notifiable->email != '') {
            $via = ['mail'];
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
        $invoice_number = $this->invoice->invoice_number;
        $url = route('front.invoice', $this->invoice->hash);
        $url = getDomainSpecificUrl($url, $this->company);
        //$content = __('email.invoiceReminder.text') . ' ' . $this->invoice->due_date->toFormattedDateString() . '<br>' . new HtmlString($invoice_number) . '<br>' . __('email.messages.confirmMessage') . '<br>' . __('email.messages.referenceMessage');

        $line2 = __('email.invoiceReminder.line2').'*Rs.'.$this->invoice->total.'*'.__('email.invoiceReminder.invoice_remain').__('email.invoiceReminder.invoice_number').'*'.$this->invoice->invoice_number.'*'.__('email.invoiceReminder.invoice_symbol');
        $content = __('email.invoiceReminder.line1').PHP_EOL. PHP_EOL .$line2.__('email.invoiceReminder.line3').PHP_EOL.PHP_EOL.__('email.invoiceReminder.line4').$this->company->company_phone.'.'.__('email.invoiceReminder.line5').PHP_EOL.PHP_EOL.__('email.invoiceReminder.line6').PHP_EOL.PHP_EOL.__('email.invoiceReminder.line7').PHP_EOL.PHP_EOL.__('email.invoiceReminder.line8').'. Please click on the link below to view invoice.';
        return parent::build()
            ->subject(__('email.invoiceReminder.subject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.invoiceReminder.action'),
                'notifiableName' => $notifiable->name
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $notifiable->toArray();
    }

}
