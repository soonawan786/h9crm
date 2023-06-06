<?php

namespace App\Notifications;

use App\Channels\Messages\WhatsAppMessage;
use App\Channels\WhatsAppChannel;
use App\Http\Controllers\UrlShortenerController;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
class NewInvoiceWhatsApp extends BaseNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $invoice;
    private $emailSetting;


    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->company = $this->invoice->company;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [WhatsAppChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toWhatsApp($notifiable)
    {
        if (($this->invoice->project && !is_null($this->invoice->project->client)) || !is_null($this->invoice->client_id)) {
            //dd($this->company->company_phone);

            $url = route('front.invoice', $this->invoice->hash);
            $url = getDomainSpecificUrl($url, $this->company);
            //$short_url = UrlShortenerController::shorten($url);
            $clientName = 'Dear '. $notifiable->name.', ';

            //$content = __('email.invoice.text');
            $content = $clientName. PHP_EOL. PHP_EOL .__('email.invoice.line1').PHP_EOL. PHP_EOL .__('email.invoice.line2').PHP_EOL.PHP_EOL.PHP_EOL.__('email.invoice.symbol_at').$this->company->company_name.__('email.invoice.line3').PHP_EOL.PHP_EOL.__('email.invoice.line4').PHP_EOL.PHP_EOL.__('email.invoice.line5').PHP_EOL.PHP_EOL.'Best Regards, '.PHP_EOL.PHP_EOL.$this->company->company_name.PHP_EOL.PHP_EOL.$this->company->company_phone;
            return (new WhatsAppMessage)
            ->content($content);
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
