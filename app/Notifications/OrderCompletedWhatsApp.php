<?php

namespace App\Notifications;

use App\Channels\Messages\WhatsAppMessage;
use App\Channels\WhatsAppChannel;
use App\Http\Controllers\UrlShortenerController;
use App\Models\Order;
use Illuminate\Bus\Queueable;
class OrderCompletedWhatsApp extends BaseNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $order;
    private $invoice_number;


    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->company = $this->order->company;
        $this->invoice_number = $this->order->invoice->invoice_number;

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
        $url = route('orders.show', $this->order->id);
        $url = getDomainSpecificUrl($url, $this->company);
        $invoice_link = 'Click this link to View Order '.$url;

        $clientName = 'Dear '. $notifiable->name.', ';

        $content = $clientName. PHP_EOL. PHP_EOL .__('email.order.line1').$this->invoice_number.PHP_EOL. PHP_EOL .__('email.order.line2').date('j F Y g:ia',strtotime($this->order->updated_at)).__('email.order.line3').PHP_EOL.PHP_EOL.__('email.order.line4').$this->order->total.__('email.order.line5').PHP_EOL.PHP_EOL.__('email.order.line6').$this->company->company_phone.__('email.order.phone_symbole').PHP_EOL.PHP_EOL.__('email.order.line7').PHP_EOL.PHP_EOL.__('email.order.line8').PHP_EOL.PHP_EOL.$invoice_link.PHP_EOL.PHP_EOL.'Best Regards, '.PHP_EOL.PHP_EOL.$this->company->company_name.PHP_EOL.PHP_EOL.$this->company->company_phone;
        return (new WhatsAppMessage)
        ->content($content);

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
            'id' => $this->order->id,
            'order_number' => $this->order->order_number
        ];
    }
}
