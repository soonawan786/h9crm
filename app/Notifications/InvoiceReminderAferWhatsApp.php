<?php

namespace App\Notifications;

use App\Channels\Messages\WhatsAppMessage;
use App\Channels\WhatsAppChannel;

class InvoiceReminderAferWhatsApp extends BaseNotification
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
    // phpcs:ignore
    public function via($notifiable)
    {
        return [WhatsAppChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toWhatsApp($notifiable)
    {
        $url = route('front.invoice', $this->invoice->hash);
        $url = getDomainSpecificUrl($url, $this->company);
        $invoice_link = 'Click this link to View Invoice '.$url;
        $clientName = 'Dear '. $notifiable->name.', ';
        $line2 = __('email.invoiceReminder.line2').'*Rs.'.$this->invoice->total.'*'.__('email.invoiceReminder.invoice_remain').__('email.invoiceReminder.invoice_number').'*'.$this->invoice->invoice_number.'*'.__('email.invoiceReminder.invoice_symbol');
        $content = $clientName. PHP_EOL. PHP_EOL .__('email.invoiceReminder.line1').PHP_EOL. PHP_EOL .$line2.__('email.invoiceReminder.line3').PHP_EOL.PHP_EOL.__('email.invoiceReminder.line4').$this->company->company_phone.'.'.__('email.invoiceReminder.line5').PHP_EOL.PHP_EOL.__('email.invoiceReminder.line6').PHP_EOL.PHP_EOL.__('email.invoiceReminder.line7').PHP_EOL.PHP_EOL.__('email.invoiceReminder.line8').PHP_EOL.PHP_EOL.$invoice_link.PHP_EOL.PHP_EOL.'Best regards, '.PHP_EOL.PHP_EOL.$this->company->company_name.PHP_EOL.PHP_EOL.$this->company->company_phone;
        return (new WhatsAppMessage)
            ->content($content);
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
        return $notifiable->toArray();
    }

}
