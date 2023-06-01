<?php

namespace App\Notifications;

use App\Channels\Messages\WhatsAppMessage;
use App\Channels\WhatsAppChannel;
use App\Http\Controllers\UrlShortenerController;
use App\Models\Order;
use Illuminate\Bus\Queueable;
class OrderUpdatedWhatsApp extends BaseNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $order;


    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->company = $this->order->company;
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
        if ($this->order) {

            $url = route('orders.show', $this->order->id);
            $url = getDomainSpecificUrl($url, $this->company);
            //$short_url = UrlShortenerController::shorten($url);
            $content = __('email.order.updateText');
            return (new WhatsAppMessage)
            ->content($content.' '.$url);
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
            'id' => $this->order->id,
            'order_number' => $this->order->order_number
        ];
    }
}
